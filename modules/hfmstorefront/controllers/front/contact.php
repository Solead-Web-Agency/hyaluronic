<?php
/**
 * Endpoint formulaire de contact headless.
 *   GET                       -> { contacts: [ {id_contact, name} ] }
 *   POST {email, message, id_contact, id_order?, file_name?, file_base64?}
 *        -> { ok:true, thread_token }
 *
 * L'ancien site avait un vrai formulaire (routage service + pièce jointe) ; le headless l'avait
 * remplacé par un simple `mailto:`. Les deux contacts pointent en réalité vers la MÊME boîte, et
 * ont `customer_service = 1` : sur PrestaShop, le message crée donc un FIL EN SERVICE CLIENT
 * (ps_customer_thread + ps_customer_message, 6 871 fils existants, canal bien vivant) — l'email
 * n'est qu'une notification.
 *
 * D'où le choix ici : on PERSISTE d'abord le fil (la demande ne peut pas être perdue et reste
 * visible en back-office), PUIS on notifie par email en best-effort. Une panne SMTP ne fait donc
 * jamais disparaître une demande client — c'était le risque à écarter.
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontContactModuleFrontController extends HfmStorefrontApiController
{
    /** Extensions et taille max : celles du contrôleur natif PrestaShop. */
    const ALLOWED_EXT = ['.txt', '.rtf', '.doc', '.docx', '.pdf', '.zip', '.png', '.jpeg', '.gif', '.jpg'];
    const MAX_BYTES = 2000000;

    public function handleGet()
    {
        $idLang = (int) $this->context->language->id;
        $rows = Db::getInstance()->executeS(
            'SELECT c.id_contact, cl.name
             FROM ' . _DB_PREFIX_ . 'contact c
             INNER JOIN ' . _DB_PREFIX_ . 'contact_lang cl ON (cl.id_contact = c.id_contact AND cl.id_lang = ' . $idLang . ')
             ORDER BY c.position ASC'
        );
        $out = [];
        foreach ((array) $rows as $r) {
            $out[] = ['id_contact' => (int) $r['id_contact'], 'name' => $r['name']];
        }
        return ['contacts' => $out];
    }

    public function handlePost()
    {
        $email = trim((string) $this->in('email'));
        $message = trim((string) $this->in('message'));
        $idContact = (int) $this->in('id_contact');

        if (!Validate::isEmail($email)) {
            return ['error' => 'invalid_email'];
        }
        if ($message === '' || !Validate::isCleanHtml($message)) {
            return ['error' => 'invalid_message'];
        }
        $contact = new Contact($idContact, (int) $this->context->language->id);
        if (!Validate::isLoadedObject($contact)) {
            return ['error' => 'invalid_contact'];
        }

        // Pièce jointe (optionnelle) : mêmes règles que le contrôleur natif.
        $fileName = '';
        $b64 = (string) $this->in('file_base64');
        if ($b64 !== '') {
            $stored = $this->storeAttachment($b64, (string) $this->in('file_name'));
            if (isset($stored['error'])) {
                return $stored;
            }
            $fileName = $stored['file_name'];
        }

        // Client connecté ? (le front impose l'id_customer depuis la session, jamais le navigateur)
        $idCustomer = (int) $this->in('id_customer');

        // 1) PERSISTANCE — la demande est acquise même si l'email échoue ensuite.
        $thread = new CustomerThread();
        $thread->id_shop = (int) $this->context->shop->id;
        $thread->id_lang = (int) $this->context->language->id;
        $thread->id_contact = (int) $contact->id;
        $thread->id_customer = $idCustomer ?: null;
        $thread->id_order = (int) $this->in('id_order') ?: null;
        $thread->email = $email;
        $thread->status = 'open';
        $thread->token = Tools::passwdGen(12);
        if (!$thread->add()) {
            return ['error' => 'thread_failed'];
        }

        $cm = new CustomerMessage();
        $cm->id_customer_thread = (int) $thread->id;
        $cm->message = $message;
        $cm->file_name = $fileName;
        // PrestaShop valide ce champ avec `isIp2Long` : il attend l'IP convertie en entier
        // (ip2long), pas la chaîne pointée — sinon l'ObjectModel refuse l'enregistrement.
        $cm->ip_address = (int) ip2long((string) Tools::getRemoteAddr());
        $cm->user_agent = (string) Tools::substr((string) $this->in('user_agent'), 0, 255);
        if (!$cm->add()) {
            return ['error' => 'message_failed'];
        }

        // 2) NOTIFICATION — best effort : ne doit JAMAIS faire échouer la demande déjà enregistrée.
        $mailSent = $this->notify($contact, $email, $message, $fileName);

        return ['ok' => true, 'thread_token' => $thread->token, 'mail_sent' => $mailSent];
    }

    /** Décode et écrit la pièce jointe. `file_name` natif = uniqid() + 5 derniers caractères (varchar(18)). */
    protected function storeAttachment($b64, $originalName)
    {
        // Tolère un data-URI (data:application/pdf;base64,...).
        if (strpos($b64, ',') !== false && strpos($b64, 'base64') !== false) {
            $b64 = substr($b64, strpos($b64, ',') + 1);
        }
        $bin = base64_decode($b64, true);
        if ($bin === false) {
            return ['error' => 'invalid_file'];
        }
        if (strlen($bin) > self::MAX_BYTES) {
            return ['error' => 'file_too_big'];
        }
        $lower = Tools::strtolower((string) $originalName);
        $ok = in_array(Tools::substr($lower, -4), self::ALLOWED_EXT, true)
            || in_array(Tools::substr($lower, -5), self::ALLOWED_EXT, true);
        if (!$ok) {
            return ['error' => 'invalid_file_extension'];
        }
        $name = uniqid() . Tools::substr($lower, -5);
        if (@file_put_contents(_PS_UPLOAD_DIR_ . basename($name), $bin) === false) {
            return ['error' => 'upload_failed'];
        }
        return ['file_name' => $name];
    }

    /** Notifie la boîte du service concerné. Renvoie false si l'envoi échoue (jamais d'exception). */
    protected function notify(Contact $contact, $from, $message, $fileName)
    {
        try {
            $to = $contact->email ?: (string) Configuration::get('PS_SHOP_EMAIL');
            if (!$to) {
                return false;
            }
            $vars = [
                '{email}' => $from,
                '{message}' => Tools::nl2br($message),
                '{order_name}' => '-',
                '{attached_file}' => $fileName ? $fileName : '',
            ];
            $attachment = null;
            if ($fileName && is_file(_PS_UPLOAD_DIR_ . $fileName)) {
                $attachment = [
                    'content' => (string) file_get_contents(_PS_UPLOAD_DIR_ . $fileName),
                    'name' => $fileName,
                    'mime' => 'application/octet-stream',
                ];
            }
            return (bool) Mail::Send(
                (int) $this->context->language->id,
                'contact',
                (string) Mail::l('Message from contact form', (int) $this->context->language->id) . ' [no_sync]',
                $vars,
                $to,
                $contact->name,
                $from,
                $from,
                $attachment,
                null,
                _PS_MAIL_DIR_,
                false,
                (int) $this->context->shop->id
            );
        } catch (\Throwable $e) {
            // SMTP indisponible : la demande reste enregistrée en Service Client.
            try {
                PrestaShopLogger::addLog('HFM contact: mail KO - ' . $e->getMessage(), 2);
            } catch (\Throwable $e2) {
                // ignore
            }
            return false;
        }
    }
}
