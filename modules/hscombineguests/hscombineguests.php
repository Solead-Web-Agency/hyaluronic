<?php
/**
 * Combine guests for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class HsCombineGuests extends Module
{
    const CLASS_PARENT_TAB = 'AdminParentCustomer';
    const CLASS_CONTROLLER_COMBINE_GUEST = 'AdminCombineGuest';

    /**
     * Constant path js
     */
    const PATH_JS = 'views/js/';

    /**
     * Constant path css
     */
    const PATH_CSS = 'views/css/';

    /**
     * A list of translatable texts
     * @var array
     */
    public $i18n = array();
    public $configuration_keys = array(
        'HS_CUSTOMER_NOTIFY' => 'isInt',
        'HSCG_CRON_JOB' => 'isInt',
    );

    /**
     * construct
     */
    public function __construct()
    {
        $this->name = 'hscombineguests';
        $this->tab = 'administration';
        $this->version = '2.1.0';
        $this->author = 'PrestaMonster';

        parent::__construct();
        $this->initTranslations();
        if (defined('_PS_ADMIN_DIR_')) {
            $this->assignAdminUrls();
        }
        $this->module_key = '7e31f31925dec30dc491e972e9c1a09a';
        $this->displayName = $this->l('Combine guests');
        $this->description = $this->l('Combine guests into a customer');
    }

    /**
     * install module
     * @return boolean
     */
    public function install()
    {
        $success = array();
        $success[] = parent::install();
        $success[] = $this->_installModuleTab();
        $success[] = $this->registerHooks();
        $success[] = $this->installConfigs();
        $success[] = $this->enableSSL();
        return array_sum($success) >= count($success);
    }
    
    protected function registerHooks()
    {
        $success = array();
        $success[] = $this->registerHook('displayAdminCustomers');
        $success[] = $this->registerHook('displayAdminListBefore');
        $success[] = $this->registerHook('displayBackOfficeHeader');
        $success[] = $this->registerHook('actionAdminControllerSetMedia');
        return array_sum($success) >= count($success);
    }
    
    public function installConfigs()
    {
        $success = array();
        $success[] = Configuration::updateValue('HS_CUSTOMER_NOTIFY', 1);
        $success[] = Configuration::updateValue('HSCG_CRON_JOB', 0);
        return array_sum($success) >= count($success);
    }

    /**
     * uninstall module
     * @return boolean
     */
    public function uninstall()
    {
        $success = array();
        $success[] = parent::uninstall();
        $success[] = $this->uninstallModuleTab(self::CLASS_CONTROLLER_COMBINE_GUEST);
        return array_sum($success) >= count($success);
    }

    /**
     * Install tab admin CLASS_CONTROLLER_COMBINE_GUEST
     * @return boolean
     */
    protected function _installModuleTab()
    {
        $flag = true;
        if (self::CLASS_PARENT_TAB) {
            $id_tab = (int) Tab::getIdFromClassName(self::CLASS_PARENT_TAB);
            if (empty($id_tab) || !$this->installModuleTab(self::CLASS_CONTROLLER_COMBINE_GUEST, $this->l('Combine Guests'), $id_tab)) {
                $flag = false;
            }
        }
        return $flag;
    }

    /**
     * install tab admin CLASS_CONTROLLER_COMBINE_GUEST
     * @param string $tab_class
     * @param string $tab_name
     * @param int $id_tab_parent
     * @param int $position
     * @return boolean
     */
    private function installModuleTab($tab_class, $tab_name, $id_tab_parent = -1, $position = 0)
    {
        $tab = new Tab();
        $name = array();
        foreach (Language::getLanguages(false) as $language) {
            $name[$language['id_lang']] = $tab_name;
        }
        $tab->name = $name;
        $tab->class_name = (string) $tab_class;
        $tab->module = $this->name;
        if ($id_tab_parent != null) {
            $tab->id_parent = (int) $id_tab_parent;
        }
        if ((int) $position > 0) {
            $tab->position = (int) $position;
        }
        return $tab->add(true);
    }

    /**
     * Method uninstall tab
     * */
    private function uninstallModuleTab($tab_class)
    {
        $id_tab = Tab::getIdFromClassName($tab_class);
        $flag = false;
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            $flag = $tab->delete();
        }
        return $flag;
    }

    /**
     * We don't have the config page, so we will redirect to admin guests page.
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::CLASS_CONTROLLER_COMBINE_GUEST));
    }

    /**
     * Get relative path to js files of module
     * @return string
     */
    public function getJsPath()
    {
        return $this->_path . self::PATH_JS;
    }

    /**
     * Get relative path to css files of module
     * @return string
     */
    public function getCssPath()
    {
        return $this->_path . self::PATH_CSS;
    }

    /**
     * Dedicated callback to upgrading process
     * @param type $version
     * @return boolean
     */
    public function upgrade($version)
    {
        $success = array();
        switch ($version) {
            case '1.2':
                $success[] = ($this->registerHook('displayAdminCustomers') && $this->registerHook('displayBackOfficeHeader'));
                break;
            case '1.7.0':
                $success[] = $this->registerHook('actionAdminControllerSetMedia');
                $success[] = $this->enableSSL();
                break;
            case '2.0.0':
                $success[] = $this->registerHook('displayAdminListBefore');
                break;
            default:
                break;
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * show a setting form in the top of list accessory group.
     *
     * @return html
     */
    public function hookDisplayAdminListBefore()
    {
        $controller_name = Tools::getValue('controller');
        if ($controller_name === self::CLASS_CONTROLLER_COMBINE_GUEST) {
            return $this->renderForm();
        }
    }

    /**
     * Render form settings
     * @return html
     */
    protected function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->i18n['settings'],
                    'image' => $this->_path.'logo.gif',
                ),
                'input' => array(
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['send_email_to_customer_after_combining'],
                        'name' => 'HS_CUSTOMER_NOTIFY',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['send_email_to_customer_after_combining'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HS_CUSTOMER_NOTIFY_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HS_CUSTOMER_NOTIFY_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['automatically_combine_customer_guest_accounts'],
                        'desc' => $this->i18n['cron_job_url'],
                        'name' => 'HSCG_CRON_JOB',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['automatically_combine_customer_guest_accounts'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSCG_CRON_JOB_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSCG_CRON_JOB_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->i18n['save'],
                    'name' => 'submitCombineGuestsSetting'
                )
            ),
        );

        if (!$this->isPrestashop16()) {
            foreach ($fields_form['form']['input'] as $key => $input) {
                if (empty($input)) {
                    unset($fields_form['form']['input'][$key]);
                }
            }
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = '';
        $helper->currentIndex = $this->context->link->getAdminLink(self::CLASS_CONTROLLER_COMBINE_GUEST, false);
        $helper->token = Tools::getAdminTokenLite(self::CLASS_CONTROLLER_COMBINE_GUEST);
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigurationValues(),
        );

        return $helper->generateForm(array($fields_form));
    }
    
    protected function getConfigurationValues()
    {
        $fields_values = array(
            'HS_CUSTOMER_NOTIFY' => Tools::getValue('HS_CUSTOMER_NOTIFY', Configuration::get('HS_CUSTOMER_NOTIFY')),
            'HSCG_CRON_JOB' => Tools::getValue('HSCG_CRON_JOB', Configuration::get('HSCG_CRON_JOB')),
        );
        return $fields_values;
    }
    
    public function hookActionAdminControllerSetMedia()
    {
        if (!Module::isEnabled($this->name)) {
            return;
        }
        if ($this->context->controller instanceof AdminCombineGuestController || $this->context->controller instanceof AdminCustomersController || Tools::getValue('controller') === 'AdminCustomers') {
            if (method_exists($this->context->controller, 'addJquery')) {
                $this->context->controller->addJqueryPlugin('typewatch');
                $this->context->controller->addJS(array(
                    $this->getJsPath() . 'combine_guest.js'
                ));
                $this->context->controller->addCSS(array(
                    $this->getCssPath() . 'combine_guest.css'
                ));
            }
        }
    }
    
    /**
     * display block search guest customer in page customer detail
     * @param Array $params
     * array (
     *      [id_customer]=>int
     *      [smarty] => object
     * )
     * @return html
     */
    public function hookDisplayAdminCustomers($params)
    {
        $combine_guest_url = array(
            'url' => $this->urls
        );
        $this->context->smarty->assign(array(
            'combine_guest_url' => Tools::jsonEncode($combine_guest_url),
            'is_prestashop_176' => $this->isPrestashop176(),
            'is_prestashop_178' => $this->isPrestashop178(),
            'is_prestashop_1617' => $this->isPrestashop1617(),
            'id_customer' => $params['id_customer'],
        ));
        return $this->display($this->name . '.php', 'block_seach_customer.tpl');
    }

    public function enableSSL()
    {
        $success = true;
        if (Configuration::get('PS_SSL_ENABLED') && !Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
            $success = Configuration::updateValue('PS_SSL_ENABLED_EVERYWHERE', 1);
        }
        return $success;
    }

    /**
     * combine an Ajax URL for the default controller of module
     * @param string $action
     * @return string full Ajax Url
     */
    public function getTargetUrl($controller = '', $action = '', $ajax = true)
    {
        $params = array();
        $params['ajax'] = $ajax;
        $action = trim($action);
        if (!empty($action)) {
            $params['action'] = $action;
        }
        $query = array();
        foreach ($params as $key => $value) {
            $query[] = $key . '=' . $value;
        }
        return $this->context->link->getAdminLink($controller) . '&' . implode('&', $query);
    }

    /**
     * Assign all possible urls to access from javascript, backend only
     */
    public function assignAdminUrls()
    {
        $this->urls = array(
            'searchCustomers' => $this->getTargetUrl(self::CLASS_CONTROLLER_COMBINE_GUEST, 'searchCustomers')
        );
    }

    /**
     * hook into Back Office header position
     * @return assign template
     */
    public function hookDisplayBackOfficeHeader()
    {
        if ($this->context->controller instanceof AdminCombineGuestController) {
            return $this->display($this->name . '.php', 'backofficeheader_combine_guest.tpl');
        }
    }

    /**
     * All translatable texts which can be easy to use in Smarty or any module controllers
     * <br />
     * For example:<br />
     * - controller<br />
     * $this->module->i18n[text_1]
     */
    public function initTranslations()
    {
        $source = basename(__FILE__, '.php');
        $cron_url = $this->getCronUrl();
        $this->i18n = array(
            'cron_job_url' => sprintf($this->l('Cron job Url: %s . For setting a cron job, please read more it in the module document.', $source), $cron_url),
            'combine_elected' => $this->l('Combine Selected', $source),
            'combine_selected_items' => $this->l('Combine selected items?', $source),
            'ids' => $this->l('IDs', $source),
            'first_name' => $this->l('First Name', $source),
            'last_name' => $this->l('Last Name', $source),
            'email' => $this->l('Email', $source),
            'total' => $this->l('Total', $source),
            'has_customer_account' => $this->l('Has customer account (?)', $source),
            'the_setting_has_been_updated_successfully' => $this->l('The setting has been updated successfully ', $source),
            'combine_guests_to_customer_sucessfully' => $this->l('Combine guests to customer sucessfully!', $source),
            'errors' => $this->l('Errors', $source),
            'as_you_registered_in_our_shop' => $this->l('As you registered in our shop', $source),
            'customers_have_been_combined_fail' => $this->l('Customers have been combined fail.', $source),
            'unknown' => $this->l('Unknown', $source),
            'send_email_to_customer_after_combining' => $this->l('Send email to customer after combining', $source),
            'never' => $this->l('Never', $source),
            'save' => $this->l('save', $source),
            'enabled' => $this->l('Enabled', $source),
            'disabled' => $this->l('Disabled', $source),
            'settings' => $this->l('Module Settings', $source),
            'automatically_combine_customer_guest_accounts' => $this->l('Automatically combine customer/guest accounts.', $source),
            'the_current_this_page_only_shows_guest_accounts' => $this->l('The current, this page only shows guest accounts, if you want to show customer accounts in this page, please change this option to Yes.', $source),
            'show_combine_customer_accounts_in_this_page' => $this->l('Show & combine customer accounts in this page', $source),
            
            'about_us' => $this->l('About us', $source),
            'just_combined_your_accounts' => $this->l('Just combined your account(s)', $source),
            'created_by' => $this->l('Created by', $source),
            'current_version' => $this->l('Current version:', $source),
            'documentation' => $this->l('Documentation', $source),
            'rate_us' => $this->l('Rate us', $source),
            'need_help' => $this->l('Need to help?', $source),
            'all_modules' => $this->l('All modules developed by ', $source),
        );
        $this->context->smarty->assign('hs_cg_i18n', $this->i18n);
    }

    public function isPrestashop1617()
    {
        return version_compare(_PS_VERSION_, '1.7.6', '<') && version_compare(_PS_VERSION_, '1.6', '>=');
    }
    
    public function isPrestashop176()
    {
        return (int) version_compare(_PS_VERSION_, '1.7.6', '>=');
    }
    public function isPrestashop178()
    {
        return (int) version_compare(_PS_VERSION_, '1.7.8', '>=');
    }
    
    /**
     * Check prestashop current version is 1.6.
     *
     * @return boolean
     */
    public function isPrestashop16()
    {
        return version_compare(_PS_VERSION_, '1.6') === 1;
    }
    /**
     * Get relative path to images files of module.
     *
     * @return string
     */

    /**
     * Get relative path to document file of module.
     * @return string
     */
    public function getDocumentPath()
    {
        return $this->_path . 'readme_en.pdf';
    }
    
    /**
     * Render cron jobs url
     * @return string
     */
    protected function getCronUrl()
    {
        $params = array(
            'token' => Tools::hash($this->name),
        );
        return $this->context->link->getModuleLink($this->name, 'cron', $params);
    }
}
