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

class PBPControllerCore extends Module
{
    protected $module_ajax_url = '';

    /** @var string  */
    protected $module_bo_ajax_url = '';

    protected $module_config_url = '';
    protected $sibling;
    protected $helper_form;
    protected $helper_list;
    protected $params = array();

    protected $key_tab = 'ModuleProductbundlespro';
    protected $id_lang_default;

    /** @var string */
    protected $module_folder = 'productbundlespro';

    public function __construct($sibling, $params = array())
    {
        $this->id_lang_default = Configuration::get('PS_LANG_DEFAULT', Context::getContext()->shop->id);

        $this->sibling = $sibling;
        if (!empty($params)) {
            $this->params = $params;
        }

        parent::__construct();
        $link = new Link();
        $this->module_ajax_url = $link->getModuleLink('productbundlespro', 'ajax', array());

        if (!empty(Context::getContext()->employee)) {
            $this->module_bo_ajax_url = $link->getAdminLink('AdminAjaxProductBundlesPro');
        }

        switch (Tools::getValue('controller')) {
            case 'AdminModules':
                $this->module_config_url = AdminController::$currentIndex . '&configure=' . $this->sibling->name . '&token=' . Tools::getAdminTokenLite('AdminModules');
                break;
            default:
                if (empty(Context::getContext()->controller)) {
                    break;
                }
                $controller_type = Context::getContext()->controller->controller_type;
                if ($controller_type != 'modulefront' && $controller_type != 'front') {
                    $this->module_config_url = Context::getContext()->link->getAdminLink('AdminModules', true) . '&configure=' . $this->sibling->name;
                }
                break;
        }

        if (AdminController::$currentIndex != '')
            $this->module_tab_url = AdminController::$currentIndex . '&' . 'updateproduct&id_product=' . Tools::getValue('id_product') . '&token=' . Tools::getAdminTokenLite('AdminProducts') . '&key_tab=' . $this->key_tab;
    }

    /**
     * Get the url to the module folder
     * @return string
     */
    protected function getShopBaseUrl()
    {
        if (Tools::getShopDomain() != $_SERVER['HTTP_HOST'])
            $domain = $_SERVER['HTTP_HOST'];
        else
            $domain = Tools::getShopDomain();

        if (empty($_SERVER['HTTPS']) || !$_SERVER['HTTPS'])
            return "http://" . $domain . __PS_BASE_URI__ . 'modules/' . $this->sibling->name . '/';
        else
            return "https://" . $domain . __PS_BASE_URI__ . 'modules/' . $this->sibling->name . '/';
    }

    /**
     * get pth to admin folder
     * @return mixed
     */
    protected function getAdminWebPath()
    {
        $admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);
        return __PS_BASE_URI__ . $admin_webpath;
    }

    /* Protected Methods */
    protected function redirect($url_params)
    {
        $url = AdminController::$currentIndex . '&configure=' . $this->sibling->name . '&' . $url_params . '&token=' . Tools::getAdminTokenLite('AdminModules');
        Tools::redirectAdmin($url);
    }

    protected function setupHelperConfigForm(HelperForm &$helper, $route, $action)
    {
        $helper->module = $this->sibling;
        $helper->name_controller = $this->sibling->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->default_form_language = $this->id_lang_default;
        $helper->allow_employee_form_lang = $this->id_lang_default;
        $helper->title = $this->sibling->displayName;
        $helper->show_toolbar = false;        // false -> remove toolbar
        $helper->toolbar_scroll = false;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->sibling->name;
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->sibling->name . '&route=' . $route . '&action=' . $action;
    }
}
