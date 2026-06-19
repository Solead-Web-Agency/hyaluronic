<?php
/**
 * CREA4YOU CONFIDENTIAL
 * _____________________
 *
 * [2011] - [2019]
 *
 * @author Youness EL GHAZI <contact@@crea4you.fr>
 *
 * All Rights Reserved.
 *
 * NOTICE:  All information contained herein is, and remains
 * the property of Crea4You - Youness EL GHAZI and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Crea4You Youness EL GHAZI.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Crea4You Youness EL GHAZI.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/InputForm.php';

class ShopProtector extends Module
{
    protected $config_form = false;
    const SHORTCUT_KEYS = array('a', 'c', 'p', 'q', 's', 'u', 'v', 'w', 'x', 'z');

    public function __construct()
    {
        $this->name = 'shopprotector';
        $this->tab = 'front_office_features';
        $this->version = '1.0.7';
        $this->author = 'Crea4You';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7');
        $this->bootstrap = true;
        $this->module_key = '9c73a0f87fdaf84403132e0d9be40f19';

        parent::__construct();

        $this->displayName = $this->l('Shop Protector');
        $this->description = $this->l('Protect your shop from theft of products images, texts and other contents.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Shop Protector ?');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SHOPPROTECTOR_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitShopProtectorModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitShopProtectorModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => C4ySpInputForm::getConfigFormValues($this),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array(C4ySpInputForm::getForm($this)));
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $new_settings = C4ySpInputForm::getConfigFormValues($this);

        // Mouse settings
        $new_settings['SHOPPROTECTOR_MOUSE_ALLOW_IP_ADDRESS']     = trim(Tools::getValue('SHOPPROTECTOR_MOUSE_ALLOW_IP_ADDRESS'));
        $new_settings['SHOPPROTECTOR_MOUSE_DISABLE_RIGHT_CLICK']  = (bool) Tools::getValue('SHOPPROTECTOR_MOUSE_DISABLE_RIGHT_CLICK');
        $new_settings['SHOPPROTECTOR_MOUSE_DISABLE_ONLY_PICTURE'] = (bool) Tools::getValue('SHOPPROTECTOR_MOUSE_DISABLE_ONLY_PICTURE');
        $new_settings['SHOPPROTECTOR_MOUSE_DISABLE_SELECTION']    = (bool) Tools::getValue('SHOPPROTECTOR_MOUSE_DISABLE_SELECTION');
        $new_settings['SHOPPROTECTOR_MOUSE_DISABLE_DRAG_DROP']    = (bool) Tools::getValue('SHOPPROTECTOR_MOUSE_DISABLE_DRAG_DROP');

        // Keyboard settings
        foreach (ShopProtector::SHORTCUT_KEYS as $sk) {
            $key = Tools::strtoupper($sk);
            $new_settings['SHOPPROTECTOR_SHORTCUT_KEY_'.$key] = (bool) Tools::getValue('SHOPPROTECTOR_SHORTCUT_KEY_'.$key);
        }

        // Alert settings
        $new_settings['SHOPPROTECTOR_ALERT_ENABLE']  = (bool) Tools::getValue('SHOPPROTECTOR_ALERT_ENABLE');
        $new_settings['SHOPPROTECTOR_MODAL_TITLE']   = trim(Tools::getValue('SHOPPROTECTOR_MODAL_TITLE'));
        $new_settings['SHOPPROTECTOR_MODAL_MESSAGE'] = trim(Tools::getValue('SHOPPROTECTOR_MODAL_MESSAGE'));
        
        if (Configuration::updateValue('SHOPPROTECTOR_SETTINGS', serialize($new_settings))) {
            $this->context->smarty->assign('success_form', true);
            return true;
        } else {
            $this->context->smarty->assign('success_form', false);
            return false;
        }
    }

    /**
     * Add the CSS & JavaScript files on the Front
     */
    public function hookHeader()
    {
        $settings = C4ySpInputForm::getConfigFormValues($this);
        // CSS assets
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        if (!empty($settings['SHOPPROTECTOR_ALERT_ENABLE'])) {
            $this->context->controller->addCSS($this->_path.'/views/css/tingle.min.css');
        }
        // JS assets
        $this->context->controller->addJS($this->_path.'/views/js/protector.js');
        if (!empty($settings['SHOPPROTECTOR_ALERT_ENABLE'])) {
            $this->context->controller->addJS($this->_path.'/views/js/tingle.min.js');
        }
    }

    public function hookDisplayFooter()
    {
        $settings = C4ySpInputForm::getConfigFormValues($this);

        $this->smarty->assign(array(
            "sp_disable_right_click" => (int) (!empty($settings["SHOPPROTECTOR_MOUSE_DISABLE_RIGHT_CLICK"]) && $this->isIpAllowed($settings)),
            "sp_disable_selection"   => (int) (!empty($settings['SHOPPROTECTOR_MOUSE_DISABLE_SELECTION']) && $this->isIpAllowed($settings)),
            "sp_disable_drag_drop"   => (int) (!empty($settings['SHOPPROTECTOR_MOUSE_DISABLE_DRAG_DROP']) && $this->isIpAllowed($settings)),
            "sp_show_modal"          => (int) !empty($settings['SHOPPROTECTOR_ALERT_ENABLE']),
            "sp_modal_title"         => $settings['SHOPPROTECTOR_MODAL_TITLE'],
            "sp_modal_message"       => $settings['SHOPPROTECTOR_MODAL_MESSAGE'],
            "sp_forbidden_keys"      => $this->forbiddenCharacters($settings),
            "module_path"            => $this->_path
        ));
        return $this->display(__FILE__, '/views/templates/hook/footer.tpl');
    }

    private function isIpAllowed($settings)
    {
        return (empty($settings["SHOPPROTECTOR_MOUSE_ALLOW_IP_ADDRESS"]) ||
               strpos($settings["SHOPPROTECTOR_MOUSE_ALLOW_IP_ADDRESS"], $_SERVER['REMOTE_ADDR']) === false);
    }

    /**
     * Return an array populated by forbidden characters
     *
     * @param Array $settings
     * @return Array
     */
    private function forbiddenCharacters($settings)
    {
        $forbidden = array();
        foreach (ShopProtector::SHORTCUT_KEYS as $sk) {
            $key = Tools::strtoupper($sk);
            if (!empty($settings['SHOPPROTECTOR_SHORTCUT_KEY_'.$key])) {
                $forbidden[] = $key;
            }
        }
        return $forbidden;
    }
}
