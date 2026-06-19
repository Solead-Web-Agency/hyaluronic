<?php
/**
 * Module customerpdf - Documents PDF clients
 * Compatible PrestaShop 1.7.8.9
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerPdf extends Module
{
    public function __construct()
    {
        $this->name = 'customerpdf';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'Mickael';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '1.7.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Documents PDF clients');
        $this->description = $this->l('Permet de joindre des documents PDF a une fiche client et de les consulter depuis la fiche client ou une commande.');
        $this->confirmUninstall = $this->l('Etes-vous sur ? Tous les PDF joints et leurs enregistrements seront supprimes.');
    }

    /**
     * Chemin du dossier de stockage des fichiers
     */
    public function getUploadDir()
    {
        return _PS_MODULE_DIR_ . $this->name . '/uploads/';
    }

    /**
     * Cles valides des types de documents (utilise pour la validation cote controleur)
     */
    public static function getDocTypeKeys()
    {
        return ['devis', 'facture', 'avoir', 'bon_livraison', 'contrat', 'autre'];
    }

    /**
     * Types de documents : cle => libelle traduit
     */
    public function getDocTypes()
    {
        return [
            'devis' => $this->l('Devis'),
            'facture' => $this->l('Facture'),
            'avoir' => $this->l('Avoir'),
            'bon_livraison' => $this->l('Bon de livraison'),
            'contrat' => $this->l('Contrat'),
            'autre' => $this->l('Autre'),
        ];
    }

    /**
     * Classe Bootstrap (couleur du badge) par type de document
     */
    public static function getDocTypeClass($type)
    {
        $map = [
            'devis' => 'label-info',
            'facture' => 'label-success',
            'avoir' => 'label-warning',
            'bon_livraison' => 'label-default',
            'contrat' => 'label-primary',
            'autre' => 'label-default',
        ];

        return isset($map[$type]) ? $map[$type] : 'label-default';
    }

    public function install()
    {
        return parent::install()
            && $this->installDb()
            && $this->createUploadDir()
            && $this->installTab()
            && $this->registerHook('displayAdminCustomers')
            && $this->registerHook('displayAdminOrderSide');
    }

    public function uninstall()
    {
        return $this->uninstallTab()
            && $this->uninstallDb()
            && parent::uninstall();
    }

    protected function installDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'customer_pdf` (
            `id_customer_pdf` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_customer` int(10) unsigned NOT NULL,
            `file_name` varchar(255) NOT NULL,
            `original_name` varchar(255) NOT NULL,
            `doc_type` varchar(32) NOT NULL DEFAULT "autre",
            `mime_type` varchar(128) NOT NULL DEFAULT "application/pdf",
            `file_size` int(10) unsigned NOT NULL DEFAULT 0,
            `id_employee` int(10) unsigned NOT NULL DEFAULT 0,
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_customer_pdf`),
            KEY `id_customer` (`id_customer`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    protected function uninstallDb()
    {
        // Supprime les fichiers physiques avant de droper la table
        $dir = $this->getUploadDir();
        $rows = Db::getInstance()->executeS('SELECT `file_name` FROM `' . _DB_PREFIX_ . 'customer_pdf`');
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $path = $dir . $row['file_name'];
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }

        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'customer_pdf`');
    }

    protected function createUploadDir()
    {
        $dir = $this->getUploadDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_dir($dir)) {
            return false;
        }

        // Protection Apache (2.2 et 2.4)
        $htaccess = $dir . '.htaccess';
        if (!is_file($htaccess)) {
            @file_put_contents(
                $htaccess,
                "Order deny,allow\nDeny from all\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
            );
        }

        $index = $dir . 'index.php';
        if (!is_file($index)) {
            @file_put_contents($index, "<?php\nheader('Location: ../');\nexit;\n");
        }

        return true;
    }

    protected function installTab()
    {
        if (Tab::getIdFromClassName('AdminCustomerPdf')) {
            return true;
        }

        $tab = new Tab();
        $tab->class_name = 'AdminCustomerPdf';
        $tab->module = $this->name;
        $tab->id_parent = -1; // controleur cache (non affiche dans le menu)
        $tab->active = 1;
        $tab->name = [];
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Customer PDF';
        }

        return (bool) $tab->add();
    }

    protected function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminCustomerPdf');
        if ($id_tab) {
            $tab = new Tab($id_tab);

            return (bool) $tab->delete();
        }

        return true;
    }

    /* =========================================================
     *  HOOKS
     * ========================================================= */

    /**
     * Panneau sur la fiche client (BO > Clients > Voir)
     */
    public function hookDisplayAdminCustomers($params)
    {
        $id_customer = isset($params['id_customer']) ? (int) $params['id_customer'] : 0;
        if (!$id_customer) {
            return '';
        }

        return $this->renderPanel($id_customer, 0);
    }

    /**
     * Panneau dans la colonne laterale de la commande (BO > Commandes > Voir)
     */
    public function hookDisplayAdminOrderSide($params)
    {
        return $this->renderPanelFromOrder($params);
    }

    protected function renderPanelFromOrder($params)
    {
        $id_order = isset($params['id_order']) ? (int) $params['id_order'] : 0;
        if (!$id_order) {
            return '';
        }

        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        return $this->renderPanel((int) $order->id_customer, $id_order);
    }

    /**
     * Construit le panneau HTML (liste + formulaire d'ajout)
     */
    protected function renderPanel($id_customer, $id_order = 0)
    {
        $files = $this->getFiles($id_customer);

        // Message flash (succes / erreur) transmis via cookie apres redirection
        $flash = '';
        $flash_type = 'info';
        if (isset($this->context->cookie->customerpdf_flash) && $this->context->cookie->customerpdf_flash) {
            $flash = $this->context->cookie->customerpdf_flash;
            $flash_type = $this->context->cookie->customerpdf_flash_type
                ? $this->context->cookie->customerpdf_flash_type
                : 'info';
            unset($this->context->cookie->customerpdf_flash);
            unset($this->context->cookie->customerpdf_flash_type);
            $this->context->cookie->write();
        }

        $this->context->smarty->assign([
            'customerpdf_files' => $files,
            'customerpdf_id_customer' => $id_customer,
            'customerpdf_id_order' => $id_order,
            'customerpdf_action_url' => $this->context->link->getAdminLink('AdminCustomerPdf'),
            'customerpdf_doc_types' => $this->getDocTypes(),
            'customerpdf_flash' => $flash,
            'customerpdf_flash_type' => $flash_type,
            'customerpdf_compact' => $id_order ? 1 : 0,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/panel.tpl');
    }

    /**
     * Recupere les PDF d'un client avec liens et tailles formatees
     */
    public function getFiles($id_customer)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'customer_pdf`
             WHERE `id_customer` = ' . (int) $id_customer . '
             ORDER BY `date_add` DESC'
        );

        if (!is_array($rows)) {
            return [];
        }

        $link = $this->context->link;
        $types = $this->getDocTypes();
        foreach ($rows as &$row) {
            $id = (int) $row['id_customer_pdf'];
            $type = isset($row['doc_type']) ? $row['doc_type'] : 'autre';
            $row['type_label'] = isset($types[$type]) ? $types[$type] : $types['autre'];
            $row['type_class'] = self::getDocTypeClass($type);
            $row['size_human'] = $this->humanSize((int) $row['file_size']);
            $row['view_link'] = $link->getAdminLink('AdminCustomerPdf', true, [], [
                'action' => 'download',
                'id_customer_pdf' => $id,
                'inline' => 1,
            ]);
            $row['download_link'] = $link->getAdminLink('AdminCustomerPdf', true, [], [
                'action' => 'download',
                'id_customer_pdf' => $id,
            ]);
            $row['delete_link'] = $link->getAdminLink('AdminCustomerPdf', true, [], [
                'action' => 'delete',
                'id_customer_pdf' => $id,
                'id_customer' => (int) $row['id_customer'],
            ]);
        }
        unset($row);

        return $rows;
    }

    protected function humanSize($bytes)
    {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;
        $size = (float) $bytes;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            ++$i;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
