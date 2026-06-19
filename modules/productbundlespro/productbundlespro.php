<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . '/productbundlespro/lib/bootstrap.php');

class ProductBundlesPro extends Module
{
    public function __construct()
    {
        $this->name = 'productbundlespro';
        $this->tab = 'others';
        $this->version = '2.1.9';
        $this->author = 'Musaffar Patel';
        $this->need_instance = 0;
        $this->module_key = '63ee9e0503bede842a1cbb2fadd20426';
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        parent::__construct();
        $this->displayName = $this->l('Product Bundles Pro');
        $this->description = $this->l('Product Bundles Pro');

        $this->bootstrap = true;
        $this->module_file = __FILE__;

        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        $this->controllers = array(
            'adminAjax' => 'AdminAjaxProductBundlesPro',
        );
    }

    public function install()
    {
        if (parent::install() == false
            || !$this->registerHook('productTabContent')
            || !$this->registerHook('backOfficeHeader')
            || !$this->registerHook('displayLeftColumnProduct')
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('pbpDeleteCartProduct')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayFooter')
            || !$this->registerHook('backOfficeHeader')
            || !$this->registerHook('displayPBP')
            || !$this->registerHook('displayProductButtons')
            || !$this->registerHook('displayFooterProduct')
            || !$this->registerHook('displayProductAdditionalInfo')
            || !$this->registerHook('actionObjectProductInCartDeleteBefore')
            || !$this->registerHook('actionObjectProductInCartDeleteAfter')
            || !$this->installModule()) {
            return false;
        }
        return true;
    }

    public function installModule()
    {
        PBPInstall::installDB();
        $this->installTab();
        return true;
    }

    public function uninstall()
    {
        PBPInstall::uninstall();
        $this->uninstallTab();
        return parent::uninstall();
    }

    /**
     * This method is often use to create an ajax controller
     *
     * @param none
     * @return bool
     */
    public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminAjaxProductBundlesPro';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->name;
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;

        if (!$tab->add()) {
            return false;
        }
        return true;
    }

    /**
     * uninstall tab
     *
     * @param none
     * @return bool
     */
    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminAjaxProductBundlesPro');
        $tab = new Tab($id_tab);

        if (Validate::isLoadedObject($tab)) {
            if (!$tab->delete()) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }


    /**
     * call each controller setMedia method
     */
    public function setMedia()
    {
        (new PBPFrontProductController($this))->setMedia();
        (new PBPAdminConfigMainController($this))->setMedia();
        (new PBPAdminProductTabController($this))->setMedia();
    }

    public function hookActionObjectProductInCartDeleteBefore($params)
    {
    }

    public function hookActionObjectProductInCartDeleteAfter($params)
    {
        $front_cart_controller = new PBPFrontCartController($this);
        $front_cart_controller->hookActionObjectProductInCartDeleteAfter($params);
    }


    /**
     * Module Config Renderer
     * @return mixed
     */
    public function getContent()
    {
        return $this->route();
    }

    /**
     * Here come th hooks
     */

    /**
     * Assets header for BO Pages
     * @param $params
     */
    public function hookBackOfficeHeader($params)
    {
        $this->setMedia();
    }

    /**
     * Admin Product Tab Hook
     * @param $params
     * @return mixed
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $pbp_admin_producttab_controller = new PBPAdminProductTabController($this, $params);
        return $pbp_admin_producttab_controller->route();
    }


    /**
     * @param $params
     */
    public function hookDisplayHeader($params)
    {
        $this->setMedia();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function hookDisplayFooterProduct($params)
    {
        $pbp_front_product_controller = new PBPFrontProductController($this);
        return $pbp_front_product_controller->hookDisplayFooter($params);
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if (Tools::getValue('action') == 'quickview') {
            $pbp_front_product_controller = new PBPFrontProductController($this);
            return $pbp_front_product_controller->hookDisplayFooter($params);
        }
    }


    /**
     * Called when cart is changed by the customer
     * @param $params
     */
    public function hookActionCartSave($params)
    {
        $front_controller = new PBPFrontCartController($this);
        $front_controller->hookActionCartSave($params);
        $front_controller->calculateCartDiscount();
    }

    /**
     * Route to appropriate controller
     * @return mixed
     */
    public function route()
    {
        switch (Tools::getValue('section')) {
            case 'adminproducttab':
                $pbp_admin_producttab_controller = new PBPAdminProductTabController($this, $_POST);
                die($pbp_admin_producttab_controller->route());
        }

        switch (Tools::getValue('route')) {
            case 'pbpadminconfiggeneralcontroller':
                $pbp_admin_config_general_controller = new PBPAdminConfigGeneralController($this);
                die($pbp_admin_config_general_controller->route());

            case 'pbpadminconfigtabscontroller':
                $pbp_admin_config_tabs_controller = new PBPAdminConfigTabsController($this);
                die($pbp_admin_config_tabs_controller->route());

            case 'pbpadminconfigmassassigncontroller':
                $pbp_admin_config_mass_assign_controller = new PBPAdminConfigMassAssignController($this);
                die($pbp_admin_config_mass_assign_controller->route());

            default:
                $pbp_admin_config_main_controller = new PBPAdminConfigMainController($this);
                return $pbp_admin_config_main_controller->route();
        }
    }
}
