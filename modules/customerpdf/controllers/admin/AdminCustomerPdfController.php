<?php
/**
 * Controleur admin : upload / telechargement / suppression des PDF clients.
 * Le token est valide automatiquement par ModuleAdminController.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminCustomerPdfController extends ModuleAdminController
{
    /** @var int Taille max d'un fichier (10 Mo) */
    const MAX_FILE_SIZE = 10485760;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        if (!$this->module || !Validate::isLoadedObject($this->module)) {
            $this->module = Module::getInstanceByName('customerpdf');
        }
    }

    /**
     * Traite les actions avant tout rendu HTML (postProcess s'execute avant initHeader).
     */
    public function postProcess()
    {
        $action = Tools::getValue('action');

        if (Tools::isSubmit('submitUploadPdf') || $action === 'upload') {
            $this->processUploadPdf();
        } elseif ($action === 'download') {
            $this->processDownloadPdf(); // termine par exit
        } elseif ($action === 'delete') {
            $this->processDeletePdf();
        }

        return parent::postProcess();
    }

    /**
     * Si on arrive sur le controleur sans action valable, on renvoie vers les clients.
     */
    public function initContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers'));
    }

    protected function processUploadPdf()
    {
        $id_customer = (int) Tools::getValue('id_customer');

        if (!$id_customer || !Validate::isLoadedObject(new Customer($id_customer))) {
            $this->setFlash($this->module->l('Client introuvable.', 'admincustomerpdfcontroller'), 'danger');
            $this->redirectBack();
        }

        if (!isset($_FILES['pdf_file']) || !is_array($_FILES['pdf_file'])) {
            $this->setFlash($this->module->l('Aucun fichier recu.', 'admincustomerpdfcontroller'), 'danger');
            $this->redirectBack();
        }

        $file = $_FILES['pdf_file'];

        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash($this->module->l('Erreur lors du transfert du fichier.', 'admincustomerpdfcontroller'), 'danger');
            $this->redirectBack();
        }

        if ((int) $file['size'] <= 0 || (int) $file['size'] > self::MAX_FILE_SIZE) {
            $this->setFlash($this->module->l('Fichier vide ou trop volumineux (max 10 Mo).', 'admincustomerpdfcontroller'), 'danger');
            $this->redirectBack();
        }

        // Verification extension
        $ext = Tools::strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $this->setFlash($this->module->l('Seuls les fichiers PDF sont autorises.', 'admincustomerpdfcontroller'), 'danger');
            $this->redirectBack();
        }

        // Verification du type MIME reel
        $mime = $this->detectMime($file['tmp_name']);
        if ($mime !== 'application/pdf') {
            $this->setFlash($this->module->l('Le fichier n\'est pas un PDF valide.', 'admincustomerpdfcontroller'), 'danger');
            $this->redirectBack();
        }

        $dir = $this->module->getUploadDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $stored_name = sha1(uniqid('cpdf', true)) . '.pdf';
        $dest = $dir . $stored_name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->setFlash($this->module->l('Impossible d\'enregistrer le fichier sur le serveur.', 'admincustomerpdfcontroller'), 'danger');
            $this->redirectBack();
        }

        $original = Tools::substr(pSQL($file['name']), 0, 255);

        $doc_type = Tools::getValue('doc_type');
        if (!in_array($doc_type, CustomerPdf::getDocTypeKeys(), true)) {
            $doc_type = 'autre';
        }

        Db::getInstance()->insert('customer_pdf', [
            'id_customer' => (int) $id_customer,
            'file_name' => pSQL($stored_name),
            'original_name' => $original,
            'doc_type' => pSQL($doc_type),
            'mime_type' => 'application/pdf',
            'file_size' => (int) $file['size'],
            'id_employee' => (int) $this->context->employee->id,
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        $this->setFlash($this->module->l('Document PDF ajoute avec succes.', 'admincustomerpdfcontroller'), 'success');
        $this->redirectBack();
    }

    protected function processDeletePdf()
    {
        $id = (int) Tools::getValue('id_customer_pdf');
        $row = $this->getPdfRow($id);

        if (!$row) {
            $this->setFlash($this->module->l('Document introuvable.', 'admincustomerpdfcontroller'), 'danger');
            $this->redirectBack();
        }

        $path = $this->module->getUploadDir() . $row['file_name'];
        if (is_file($path)) {
            @unlink($path);
        }

        Db::getInstance()->delete('customer_pdf', 'id_customer_pdf = ' . (int) $id);

        $this->setFlash($this->module->l('Document supprime.', 'admincustomerpdfcontroller'), 'success');
        $this->redirectBack();
    }

    protected function processDownloadPdf()
    {
        $id = (int) Tools::getValue('id_customer_pdf');
        $row = $this->getPdfRow($id);

        if (!$row) {
            die($this->module->l('Document introuvable.', 'admincustomerpdfcontroller'));
        }

        $dir = realpath($this->module->getUploadDir());
        $path = realpath($dir . DIRECTORY_SEPARATOR . $row['file_name']);

        // Protection contre la traversee de repertoire
        if ($path === false || $dir === false || strpos($path, $dir) !== 0 || !is_file($path)) {
            die($this->module->l('Fichier indisponible.', 'admincustomerpdfcontroller'));
        }

        $inline = (bool) Tools::getValue('inline');
        $disposition = $inline ? 'inline' : 'attachment';

        // Nettoyage du nom pour eviter l'injection d'en-tetes
        $download_name = str_replace(["\r", "\n", '"'], '', $row['original_name']);
        if ($download_name === '') {
            $download_name = 'document.pdf';
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="' . $download_name . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));

        readfile($path);
        exit;
    }

    /* ----------------------------------------------------------- */

    protected function getPdfRow($id)
    {
        if ($id <= 0) {
            return false;
        }

        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'customer_pdf` WHERE `id_customer_pdf` = ' . (int) $id
        );

        return $row ? $row : false;
    }

    protected function detectMime($tmp_name)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $tmp_name);
                finfo_close($finfo);

                return $mime;
            }
        }

        if (function_exists('mime_content_type')) {
            return mime_content_type($tmp_name);
        }

        // Repli : lecture de la signature %PDF
        $fh = @fopen($tmp_name, 'rb');
        if ($fh) {
            $head = fread($fh, 4);
            fclose($fh);
            if ($head === '%PDF') {
                return 'application/pdf';
            }
        }

        return '';
    }

    protected function setFlash($message, $type = 'info')
    {
        $this->context->cookie->customerpdf_flash = $message;
        $this->context->cookie->customerpdf_flash_type = $type;
        $this->context->cookie->write();
    }

    /**
     * Redirige vers la commande d'origine si fournie, sinon vers la fiche client.
     */
    protected function redirectBack()
    {
        $id_order = (int) Tools::getValue('id_order');
        if ($id_order) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], [
                'id_order' => $id_order,
                'vieworder' => 1,
            ]));
        }

        $id_customer = (int) Tools::getValue('id_customer');
        if (!$id_customer) {
            // Retrouve le client a partir du PDF concerne
            $row = $this->getPdfRow((int) Tools::getValue('id_customer_pdf'));
            if ($row) {
                $id_customer = (int) $row['id_customer'];
            }
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers', true, [], [
            'id_customer' => $id_customer,
            'viewcustomer' => 1,
        ]));
    }
}
