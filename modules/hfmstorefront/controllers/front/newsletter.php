<?php
/**
 * Endpoint newsletter headless : inscription dans la table native `ps_emailsubscription`.
 *   POST {email, id_lang?}  -> { ok:true, status: subscribed|already }
 *
 * Écrit dans la MÊME table que l'ancien site (module iqitemailsubscriptionconf) : les
 * 1 459 inscrits invités existants et les nouveaux cohabitent, et le back-office / les exports
 * newsletter continuent de fonctionner à l'identique.
 *
 * NB : les clients inscrits via leur compte sont dans `ps_customer.newsletter`, pas ici — on ne
 * touche pas à ce chemin (il passe par l'inscription client).
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontNewsletterModuleFrontController extends HfmStorefrontApiController
{
    public function handlePost()
    {
        if ((string) $this->in('action') === 'unsubscribe') {
            return $this->unsubscribe();
        }
        $email = trim((string) $this->in('email'));
        if ($email === '' || !Validate::isEmail($email)) {
            return ['error' => 'invalid_email'];
        }

        $idShop = (int) $this->context->shop->id;
        $idLang = (int) $this->context->language->id;

        // Déjà inscrit via la table newsletter ?
        $exists = (bool) Db::getInstance()->getValue(
            'SELECT 1 FROM `' . _DB_PREFIX_ . 'emailsubscription`
             WHERE email = \'' . pSQL($email) . '\' AND id_shop = ' . $idShop
        );
        // …ou déjà opt-in via un compte client ? (on ne recrée pas de doublon dans ce cas)
        if (!$exists) {
            $exists = (bool) Db::getInstance()->getValue(
                'SELECT 1 FROM `' . _DB_PREFIX_ . 'customer`
                 WHERE email = \'' . pSQL($email) . '\' AND newsletter = 1 AND id_shop = ' . $idShop
            );
        }
        if ($exists) {
            return ['ok' => true, 'status' => 'already'];
        }

        // IP tronquée à 15 caractères = la colonne native (varchar(15), pensée IPv4).
        $ip = (string) Tools::getRemoteAddr();
        $referer = (string) $this->in('referer');

        $done = (bool) Db::getInstance()->insert('emailsubscription', [
            'id_shop' => $idShop,
            'id_shop_group' => (int) $this->context->shop->id_shop_group,
            'email' => pSQL($email),
            'newsletter_date_add' => date('Y-m-d H:i:s'),
            'ip_registration_newsletter' => pSQL(Tools::substr($ip, 0, 15)),
            'http_referer' => pSQL(Tools::substr($referer, 0, 255)),
            'active' => 1,
            'id_lang' => $idLang,
        ]);

        if (!$done) {
            return ['error' => 'subscribe_failed'];
        }
        return ['ok' => true, 'status' => 'subscribed'];
    }

    /**
     * Désinscription depuis le lien des emails de relance (module hfmrelance) / newsletter.
     * Lien signé : e = e-mail en base64url, s = HMAC-SHA256(e-mail, HFM_RECOVER_SECRET) -> personne
     * ne peut désinscrire l'adresse d'un tiers ni sonder l'existence d'un compte.
     * Effets : exclusion définitive des relances (table hfm_relance_optout), newsletter coupée
     * (ps_emailsubscription + ps_customer.newsletter). Réponse identique que l'adresse soit connue ou non.
     */
    protected function unsubscribe()
    {
        $raw = strtr((string) $this->in('e'), '-_', '+/');
        $email = Tools::strtolower(trim((string) base64_decode($raw . str_repeat('=', (4 - strlen($raw) % 4) % 4), true)));
        $sig = (string) $this->in('s');
        $secret = (string) Configuration::get('HFM_RECOVER_SECRET');
        if ($email === '' || !Validate::isEmail($email) || $secret === ''
            || !hash_equals(hash_hmac('sha256', $email, $secret), $sig)) {
            return ['error' => 'invalid_link'];
        }
        $db = Db::getInstance();
        $db->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hfm_relance_optout` (
            `email` VARCHAR(255) NOT NULL PRIMARY KEY,
            `date_add` DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $db->execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'hfm_relance_optout` (email, date_add)
            VALUES (\'' . pSQL($email) . '\', NOW())');
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'emailsubscription` WHERE LOWER(email) = \'' . pSQL($email) . '\'');
        $db->execute('UPDATE `' . _DB_PREFIX_ . 'customer` SET newsletter = 0 WHERE LOWER(email) = \'' . pSQL($email) . '\'');
        return ['ok' => true];
    }
}
