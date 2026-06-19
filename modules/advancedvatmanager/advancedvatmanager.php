<?php
/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersVAT.php');
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersExemption.php');
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersOrders.php');
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/ValidationEngine.php');
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/AdvancedVatManagerOC.php');

class AdvancedVatManager extends Module
{
    protected $config_form = false;
    private $output;
    public $multistoreCompatibility;
    public $controller_msg;
    public $opc_presteamshop_enabled;

    /**
     * AdvancedVatManager::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $this->name = 'advancedvatmanager';
        $this->tab = 'checkout';
        $this->version = '1.5.5';
        $this->author = 'Liewebs';
        $this->module_key = 'a0ec580e4f441fe99e13f86d087bf8a7';
        $this->author_address = '0xFA5b20b21D2663724fd15b77aBa7B209F032D2cC';
        $this->controllers = array(
            'AdvancedVatManager',
            'AdminCustomersVatManager',
            'AdminCustomersExemptionManager',
            'AdminCustomersOrdersManager',
            'AdminCheckVAT',
            'AdminAVMSettings'
        );
        $this->tax_manager_class = 'AdvancedVatManagerTaxManager';
        $this->bootstrap = true;
        
        //Admin Tab translations
        $this->l('Advanced VAT Manager');
        $this->l('Customer VAT Number Management');
        $this->l('Customer VAT Exemption Management');
        $this->l('Orders Management');
        $this->l('VAT Check Tool');
        $this->l('Settings');
        
        // For order state translations
        $this->l('VAT number pending validation');
        $this->l('VAT number validated');
        
        parent::__construct();

        $this->displayName = $this->l('Advanced VAT Manager');
        $this->description = $this->l('Check the validity of the VAT numbers of the customers who register in the store. Manage customers with VAT numbers and associate them with customer groups automatically. Remove taxes in product prices, manage Brexit taxes policy and many more options.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        
        // Multistore feature from 1.7.8 version
        if (version_compare(_PS_VERSION_, '1.7.8.0', '>=')) {
            $this->multistoreCompatibility = self::MULTISTORE_COMPATIBILITY_YES;
        }
        
        $this->controller_msg = array(
            'COMPANY_VALIDATION_COMMENT' => $this->l('The company name must match the one registered in the VIES or GOV.UK system in case of inserting a VAT number.'),
            'COMPANY_DISPLAY_COMMENT' => $this->l('If you fill in this field, the VAT field will be displayed.'),
            'COMPANY_EMPTY' => $this->l('The company name is empty and is required to validate the company together with the registered VAT number.'),
            'COMPANY_EMPTY_WITH_ALIAS' => $this->l('The address with alias [%s] has the company name empty and is required to validate the company together with the registered VAT number.'),
            'COMPANY_INVALID_WITH_NAME' => $this->l('The %s is invalid and it is not registered in VIES or GOV.UK for the VAT number selected. Please edit address inserting a valid VAT number a save again.'),
            'COMPANY_INVALID' => $this->l('The company name of this address is not valid.'),
            'COMPANY_INVALID_REQUIRED' => $this->l('The company name of this address is not valid and it is required.'),
            'COMPANY_INVALID_WITH_ALIAS' => $this->l('The address with alias [%s] has the company name [%s] invalid.'),
            'COMPANY_NOT_VALIDATED_REQUIRED_WITH_NAME' => $this->l('The %s with name %s has not been validated yet and it is required. Please edit address inserting a valid company name registered in VIES or GOV.UK for the VAT number selected a save again.'),  
            'COMPANY_NOT_VALIDATED_REQUIRED' => $this->l('The company name of this address has not been validated yet. Please insert company name and save again the address form.'),            
            'VAT_NOT_VALID_REQUIRED_WITH_NUMBER' => $this->l('The %s %s is not valid and it is required. Please insert a valid VAT number.'),
            'VAT_NOT_VALID_OPTIONAL_WITH_NUMBER' => $this->l('The %s %s is not valid. Please insert a valid VAT number if you want to be tax exempt.'),
            'VAT_NOT_VALID_OPTIONAL' => $this->l('The VAT number of this address is not valid. If you want to be tax exempt, you should insert a valid VAT number.'),
            'VAT_NOT_VALID_REQUIRED' => $this->l('The VAT number of this address is not valid and It is required. Please edit address inserting a valid VAT number a save again.'),
            'VAT_NOT_VALID_WITH_ALIAS' => $this->l('The address with alias [%s] has not valid VAT number.'),
            'VAT_NOT_VALID_OPTIONAL_WITH_ALIAS' => $this->l('The address with alias [%s] has not valid VAT number. If you want to be tax exempt, you should insert a valid VAT number.'),
            'VAT_NOT_VALID_REQUIRED_WITH_ALIAS' => $this->l('The address with alias [%s] has not valid VAT number and it is required.'),
            'VAT_NOT_VALIDATED_REQUIRED_WITH_NUMBER' => $this->l('The %s with number %s has not been validated yet and it is required. Please edit address inserting a valid VAT number a save again.'),
            'VAT_NOT_VALIDATED_REQUIRED' => $this->l('You should validate the VAT number of this address, please insert VAT number and save again.'),
            'VAT_NOT_VALIDATED_OPTIONAL' => $this->l('The VAT number of this address has not been validated yet, please insert VAT number and save again if you want to be tax exempt.'),
            'VAT_NOT_VALIDATED_OPTIONAL_WITH_ALIAS' => $this->l('The address with alias [%s] has a VAT number inserted without has been validated yet. You should save address form again to validate it and get tax exempt.'),
            'VAT_NOT_VALIDATED_REQUIRED_WITH_ALIAS' => $this->l('The address with alias [%s] has to be validated with a valid VAT number.'),    
            'VAT_EMPTY_REQUIRED' => $this->l('The VAT number is empty and is required to validate the address.'),
            'VAT_EMPTY_OPTIONAL' => $this->l('The VAT number is empty. If you want to be tax exempt, you should insert a valid VAT number.'),
            'SYSTEM_FAILS' => $this->l('The VAT number is pending to be validated because the API system failed at the time of registration of this address and could not be validated correctly. Please, save the data of the address again to proceed to the validation of the VAT number again.'),
            'SYSTEM_FAILS_WITH_ALIAS' => $this->l('The address with alias [%s] has the VAT number [%s] pending to be validated because the API system failed at the time of registration of this address and could not be validated correctly. Please go to the mentioned address and save the data of this address again to proceed to the validation of the VAT number again.')
        );
        
        // Check if the module OPC by Presteamshop is enabled and interface V5 activated.
        $this->opc_presteamshop_enabled = Module::isEnabled('onepagecheckoutps') && method_exists(Module::getInstanceByName('onepagecheckoutps'), 'isCheckoutBetaEnabled') && Module::getInstanceByName('onepagecheckoutps')->isCheckoutBetaEnabled() && (Configuration::get('OPC_ENABLE_DEBUG_NEW_CHECKOUT') == 0 || Configuration::get('OPC_ENABLE_DEBUG_NEW_CHECKOUT') == 1 && in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('OPC_IP_CHECKOUT_BETA'))));
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        //Check module validation
        if (!Validate::isModuleName('advancedvatmanager')) {
            return false;
        }
        
        //Check module VAT Number is enabled to disable it
        if (Module::isEnabled('vatnumber')) {
            Module::disableByName('vatnumber');        
        }
        //Check if the PS version is older than 1.6.0.0
        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $this->_errors[] = $this->l('This module is not compatible with this Prestashop version.');
            return false;
        }
        
        // Checks if php minimum required extensions is loaded
        $extensions = get_loaded_extensions();
        $minimum_ext = array('soap', 'curl');
        $missing_ext = array_diff($minimum_ext,$extensions);
        if (count($missing_ext)) {
            $this->_errors[] = sprintf($this->l('The minimum extensions [%s] are not installed or loaded. Contact with your hosting provider to get it.'), implode(',', $missing_ext));
            return false;
        }
        
        // Make backup of override directory
        AdvancedVatManagerOC::backupOverrideFolder($this->name);
        
        // Uninstall override methods from thirty party modules compatibles with this module to avoid override conflicts.
        $this->uninstallThirtyPartyModulesOverridesForCompatibility();
        
        if (
            !parent::install() ||
            !$this->installTab('AdvancedVatManager','Advanced VAT Manager', false, 'business_center') ||
            !$this->installTab('AdminCustomersVatManager','Customer VAT Number Management', 'AdvancedVatManager',  'credit_card') ||
            !$this->installTab('AdminCustomersExemptionManager','Customer VAT Exemption Management', 'AdvancedVatManager',  'group_add') ||
            !$this->installTab('AdminCustomersOrdersManager','Orders Management', 'AdvancedVatManager', 'shopping_cart') ||
            !$this->installTab('AdminCheckVAT','VAT Check Tool', 'AdvancedVatManager', 'check_circle') ||
            !$this->installTab('AdminAVMSettings','Settings', 'AdvancedVatManager', 'tune') ||
            //Install Configuration tables
            !Configuration::updateValue('ADVANCEDVATMANAGER_ADMINVALIDATION', 1) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_FRONTVALIDATION', 1) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_DISPLAY_WARNING_MESSAGE_ADDRESS', 1) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION', 1) || 
            !Configuration::updateValue('ADVANCEDVATMANAGER_COUNTRY', $this->getEUCountriesID()) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_EMAIL_ADDRESS', Configuration::get('PS_SHOP_EMAIL')) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_SENDAPIALERT', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_SEND_CUSTOMER_EMAIL_API_FAIL_ORDER', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_SENDEMAILVALIDATION', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_VALIDATION_MODE', 'smart') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_VATFIELD', 'optional') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_ALLOWDUPLICATED', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_FIELD_LABEL', '') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_FIELD_LEGEND', '') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_BLACKLIST', '') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_SAVETOUPPERCASE', 1) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_VALIDATION_TYPE', 'api') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_COMPANY_VALIDATION', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_VATEXEMPTION', 1) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION', '') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_DELETE_PREVIOUS_GROUPS', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_DEFAULT_GROUP_ASSIGNATION',0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_LOCAL_COUNTRY', Configuration::get('PS_COUNTRY_DEFAULT')?(int)Configuration::get('PS_COUNTRY_DEFAULT'):'') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_VALIDATION_SYSTEM', '1-way') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_MERCHANT_VAT', '') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_LEGAL_TEXT', '') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_INVOICE_NOTE', '') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_BREXIT_ENABLED', 1) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_BREXIT_VATEXEMPT_LESSTHAN135GBP', 1) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_ASSIGN_GROUP', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SENDEMAIL_DUPLICATED', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_DELETE_DUPLICATED', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_DELETE_INVALID', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_DELETE_EMPTY', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SCANFROMLAST', 0) ||             
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID_COMPANY', 0) || 
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY_COMPANY', 0) || 
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_AUTOINSERT_COMPANY', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SKIPAPISYSTEMFAIL', 0) ||  
            !Configuration::updateValue('ADVANCEDVATMANAGER_CHANGE_DEFAULT_GROUP_WITHADDRESS', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_ADDRESS_TYPE', 'both') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL', '') ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS', 0) ||
            !Configuration::updateValue('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS', 0) ||
            !$this->registerHook('displayBackOfficeHeader') ||
            !$this->registerHook('displayFooter') ||
            !$this->registerHook('displayExpressCheckout') ||
            !$this->registerHook('displayInvoiceLegalFreeText') ||           
            !$this->registerHook('displayProductPriceBlock') ||
            !$this->registerHook('createAccountForm') ||         
            !$this->registerHook('actionAdminControllerSetMedia') ||
            !$this->registerHook('actionFrontControllerSetMedia') ||
            !$this->registerHook('actionValidateCustomerAddressForm') ||
            !$this->registerHook('actionDispatcher') ||
            !$this->registerHook('actionBeforeSubmitAccount') ||
            !$this->registerHook('actionObjectDeleteAfter') ||
            !$this->registerHook('actionObjectAddAfter') ||
            !$this->registerHook('actionObjectUpdateAfter') ||
            !$this->registerHook('actionPDFInvoiceRender') ||
            !$this->registerHook('actionTaxManager') ||
            !$this->registerHook('actionCartSave') ||
            !$this->registerHook('actionValidateOrder') ||
            !$this->registerHook('actionOrderHistoryAddAfter') ||
            !$this->registerHook('actionCheckoutRender') ||
            !$this->registerHook('actionSetInvoice') ||
            // Compatibility hooks with module One Page Checkout Prestashop by Presteamshop start
            !$this->registerHook('actionOpcValidatePayment') ||
            !$this->registerHook('actionStepBeforePaymentOPC') ||
            !$this->registerHook('actionOpcCustomerAddressFormFields') ||
            !$this->registerHook('actionOpcValidateVatNumber') ||
            // Compatibility hooks with module One Page Checkout Prestashop by Presteamshop end
            !$this->registerHook('taxManager')
        )
        {   
            return false; 
        }
        /* Install configuration values for Customer group assignation by countries */
        $countries = json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY'));
        if (!empty($countries)) {
            foreach ($countries as $country) {
                Configuration::updateValue('ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_'.$country, 0);
            }
        }
        
        /* Install database */
        include(dirname(__file__) . '/sql/install.php');
        include(dirname(__file__) . '/sql/install_uk.php');// Install UK states
        
        // Remove override OrderController and use Hook actionCheckoutRender instead
        if (version_compare(_PS_VERSION_, '1.7.8.0', '>=')) {
            $this->removeOverride('OrderController');        
        }
        
        // Remove override TaxRulesTaxManager
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->removeOverride('TaxRulesTaxManager');        
        }
        
        // Creates the Order state from this module if it is not exists
        $this->createOrderStatus(); 
             
        return true;
    }
    
    /**
     * AdvancedVatManager::uninstall()
     * 
     * @return
     */
    public function uninstall()
    {
        if (
            !parent::uninstall() ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_ADMINVALIDATION') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_FRONTVALIDATION') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_DISPLAY_WARNING_MESSAGE_ADDRESS') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION') || 
            !Configuration::deleteByName('ADVANCEDVATMANAGER_COUNTRY') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_EMAIL_ADDRESS') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_SENDAPIALERT') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_SEND_CUSTOMER_EMAIL_API_FAIL_ORDER') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_SENDEMAILVALIDATION') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_VALIDATION_MODE') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_VATFIELD') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_ALLOWDUPLICATED') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_FIELD_LABEL') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_FIELD_LEGEND') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_BLACKLIST') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_SAVETOUPPERCASE') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_VALIDATION_TYPE') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_COMPANY_VALIDATION') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT') ||          
            !Configuration::deleteByName('ADVANCEDVATMANAGER_VATEXEMPTION') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_DELETE_PREVIOUS_GROUPS') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_DEFAULT_GROUP_ASSIGNATION') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_LOCAL_COUNTRY') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_VALIDATION_SYSTEM') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_MERCHANT_VAT') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_LEGAL_TEXT') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_INVOICE_NOTE') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_BREXIT_ENABLED') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_BREXIT_VATEXEMPT_LESSTHAN135GBP') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_ASSIGN_GROUP') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_SENDEMAIL_DUPLICATED') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_DELETE_DUPLICATED') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_DELETE_INVALID') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_DELETE_EMPTY') ||          
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_SCANFROMLAST') ||            
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID_COMPANY') || 
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY_COMPANY') || 
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_AUTOINSERT_COMPANY') || 
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CRON_SKIPAPISYSTEMFAIL') || 
            !Configuration::deleteByName('ADVANCEDVATMANAGER_CHANGE_DEFAULT_GROUP_WITHADDRESS') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_ADDRESS_TYPE') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL') ||
            !Configuration::deleteByName('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS') || 
            !Configuration::deleteByName('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS') ||
            !Configuration::deleteByName('VAT_PENDING_VALIDATION_OS') || 
            !Configuration::deleteByName('VAT_VALID_OS') ||         
            !$this->uninstallTabs()
        ) 
        {
            return false;
        }
        
        /* Uninstall configuration values from checkboxes and multiselects */
        Db::getInstance()->execute('
        DELETE FROM `' . _DB_PREFIX_ .'configuration`
        WHERE `name` LIKE "ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_%"');
        Db::getInstance()->execute('
        DELETE FROM `' . _DB_PREFIX_ .'configuration`
        WHERE `name` LIKE "ADVANCEDVATMANAGER_GROUP_VATEXEMPTION_%"');
        
        /* Uninstall database */
        include(dirname(__file__) . '/sql/uninstall.php');
        
        // Delete cookie
        $this->context->cookie->__unset('cart_total');
        
        // Restore modules override methods
        $this->installThirtyPartyModulesOverridesForCompatibility();
        
        // Removes order state created
        $this->deleteOrderState();
        
        return true;
    }
    
    public function enable($force_all = false)
    {  
        return $this->uninstallThirtyPartyModulesOverridesForCompatibility() && parent::enable();
    }
    
    public function disable($force_all = false)
    {
        // Compatiblity between our modules and OPC by Presteamshop
        if (Module::isEnabled('advancedantispamsystem') && !Module::isEnabled('onepagecheckoutps')) {
            $oc = new AdvancedVatManagerOC();
            $oc->removeModuleOverride('advancedantispamsystem', 'OrderController', 'postProcess'); 
        }
        return parent::disable() && $this->installThirtyPartyModulesOverridesForCompatibility();
    }
    
    /**
     * AdvancedVatManager::installThirtyPartyModulesOverridesForCompatibility()
     * Install thirty party override methods.
     * @return
     */
    public function installThirtyPartyModulesOverridesForCompatibility()
    {      
        if (Module::isEnabled('dniverificator')) {
            $dniVerificator = Module::getInstanceByName('dniverificator');
            $dniVerificator->removeOverride('AddressController');
            $dniVerificator->removeOverride('CustomerAddressForm');
            $dniVerificator->addOverride('AddressController');
            $dniVerificator->addOverride('CustomerAddressForm');
        }
        
        // Compatiblity between our modules and OPC by Presteamshop
        if (Module::isEnabled('advancedantispamsystem')) {
            $advancedantispamsystem = Module::getInstanceByName('advancedantispamsystem');
            $advancedantispamsystem->removeOverride('OrderController');
            $advancedantispamsystem->addOverride('OrderController');
        }
        else if (Module::isEnabled('onepagecheckoutps')) {
            $onepagecheckoutps = Module::getInstanceByName('onepagecheckoutps');
            $onepagecheckoutps->removeOverride('OrderController');
            $onepagecheckoutps->addOverride('OrderController');
        }
        
        if (Module::isEnabled('ets_onepagecheckout')) {
            $ets_onepagecheckout = Module::getInstanceByName('ets_onepagecheckout');
            $ets_onepagecheckout->removeOverride('HTMLTemplateOrderSlip');
            $ets_onepagecheckout->addOverride('HTMLTemplateOrderSlip');
        }
        if (Module::isEnabled('eicaptcha')) {
            $eicaptcha = Module::getInstanceByName('eicaptcha');
            $eicaptcha->removeOverride('OrderController');
            $eicaptcha->addOverride('OrderController');
        }
        if (Module::isEnabled('recaptcha')) {
            $recaptcha = Module::getInstanceByName('recaptcha');
            $recaptcha->removeOverride('OrderController');
            $recaptcha->addOverride('OrderController');
        }
        if (Module::isEnabled('paypalfeeplus')) {
            $paypalfeeplus = Module::getInstanceByName('paypalfeeplus');
            $paypalfeeplus->removeOverride('HTMLTemplateOrderSlip');
            $paypalfeeplus->addOverride('HTMLTemplateOrderSlip');
        }
        if (Module::isEnabled('minpurchase')) {
            $minpurchase = Module::getInstanceByName('minpurchase');
            $minpurchase->removeOverride('OrderController');
            $minpurchase->addOverride('OrderController');
        }
        if (Module::isEnabled('hideprice')) {
            $hideprice = Module::getInstanceByName('hideprice');
            $hideprice->removeOverride('OrderController');
            $hideprice->addOverride('OrderController');
        }
        Tools::clearCache();
        return true;
    }
    
    /**
     * AdvancedVatManager::uninstallThirtyPartyModulesOverridesForCompatibility()
     * Uninstall thirty party override methods to make compatibility with this module.
     * @return
     */
    public function uninstallThirtyPartyModulesOverridesForCompatibility()
    {
        $oc = new AdvancedVatManagerOC();        
        if (Module::isEnabled('dniverificator')) { 
            $oc->removeModuleOverride('dniverificator', 'AddressController', 'processSubmitAddress');
            $oc->removeModuleOverride('dniverificator', 'AdminAddressesController', 'processSave');
            $oc->removeModuleOverride('dniverificator', 'AdminAddressesController', 'processAdd');
            $oc->removeModuleOverride('dniverificator', 'Address', 'validateField');
            $oc->removeModuleOverride('dniverificator', 'CustomerAddressForm', 'getTemplateVariables');
        }
        // Compatibility with another modules by Liewebs
        if (Module::isEnabled('advancedantispamsystem')) {
            $oc->removeModuleOverride('advancedantispamsystem', 'OrderController', 'postProcess');
        }
        if (Module::isEnabled('onepagecheckoutps')) {
            $oc->removeModuleOverride('onepagecheckoutps', 'OrderController', 'postProcess');  
        }       
        if (Module::isEnabled('ba_prestashop_invoice')) {
            $oc->removeModuleOverride('ba_prestashop_invoice', 'HTMLTemplateOrderSlip', 'getContent');
            $oc->removeModuleOverride('ba_prestashop_invoice', 'HTMLTemplateOrderSlip', 'baGetTaxBreakdown');
        }
        if (Module::isEnabled('eicaptcha')) {
            $oc->removeModuleOverride('eicaptcha', 'OrderController', 'postProcess');
        }
        if (Module::isEnabled('recaptcha')) {
            $oc->removeModuleOverride('recaptcha', 'OrderController', 'postProcess');
        }
        if (Module::isEnabled('paypalfeeplus')) {
            $oc->removeModuleOverride('paypalfeeplus', 'HTMLTemplateOrderSlip', 'getContent');
        } 
        if (Module::isEnabled('minpurchase')) {
            $oc->removeModuleOverride('minpurchase', 'OrderController', 'postProcess');
        }
        if (Module::isEnabled('hideprice')) {
            $oc->removeModuleOverride('hideprice', 'OrderController', 'postProcess');
        }     
        Tools::clearCache();
        return true;
    }

    /**
     * AdvancedVatManager::installTab()
     * Install admin tabs
     * @param mixed $className - name of the admin class
     * @param mixed $tabName - name of the table
     * @param bool $tabParentName - false or name of parent table
     * @param mixed $icon - name of material icon or null
     * @return
     */
    public function installTab($className, $tabName, $tabParentName = false, $icon = null)
    {
        $tabId = (int) Tab::getIdFromClassName($className);
        if (!$tabId) {
            $tabId = null;
        }
        $tab = new Tab($tabId);
        $tab->class_name = $className;
        $tab->name = array();
        
        if ($icon !== null) {
            $tab->icon = $icon;
        }
     
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->_translate($tabName, $lang['iso_code']);
        }
        if ($tabParentName) {
            $tab->id_parent = (int)Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }
        $tab->module = $this->name;
        return $tab->save();
    }

    /**
     * AdvancedVatManager::uninstallTabs()
     * Uninstall tabs
     * @return bool
     */
    public function uninstallTabs()
    {
        $uninstallTabCompleted = true;

        foreach ($this->controllers as $controllerName) {
            $id_tab = (int) Tab::getIdFromClassName($controllerName);
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                $uninstallTabCompleted = $uninstallTabCompleted && $tab->delete();
            }
        }
        return $uninstallTabCompleted;
    }
    
    /**
     * AdvancedVatManager::_translate()
     * Return the translation for a string given a language iso code
     * @param mixed $string string to translate
     * @param mixed $iso_lang language iso code
     * @param bool $source source file without extension
     * @param bool $js if it's inside a js string
     * @return
     */
    public function _translate($string, $iso_lang, $source = false, $js= false)
    {
        $file = _PS_MODULE_DIR_.$this->name.'/translations/'.$iso_lang.'.php';
        $_MODULE = array();
        if(!file_exists($file)){
            return $string;    
        } 
        else {
            include($file);    
        }

        $key = md5(str_replace('\'', '\\\'', $string));

        $current_key = Tools::strtolower('<{'.$this->name.'}'._THEME_NAME_.'>'.($source?$source:$this->name)).'_'.$key;
        $default_key = Tools::strtolower('<{'.$this->name.'}prestashop>'.($source?$source:$this->name)).'_'.$key;
        $ret = $string;
        
        if (array_key_exists($current_key, $_MODULE)) {
            $ret = Tools::stripslashes($_MODULE[$current_key]);  
        }
        elseif (array_key_exists($default_key, $_MODULE)) {
            $ret = Tools::stripslashes($_MODULE[$default_key]);
        }
        if ($js) {
            $ret = addslashes($ret);    
        }
        return $ret;
     }
     
    /**
     * AdvancedVatManager::getTabsForm()
     * 
     * @return
     */
    public function getTabsForm()
    {
        $addons_support_url = 'https://addons.prestashop.com/'.($this->context->language->iso_code != 'es'?'en/contact-us?id_product=86304':'es/contacte-con-nosotros?id_product=86304');
        $addons_ratings_url = 'https://addons.prestashop.com/ratings.php';
        $catalogue_url = 'https://addons.prestashop.com/'.$this->context->language->iso_code.'/2_community-developer?contributor=70593';
        return array(
            'form' => array(
                'activation_tab' => array(
                    'icon' => 'fas fa-power-off',
                    'name' => $this->l('Activation'),
                    'active' => 1,
                ),
                'settings_tab' => array(
                    'icon' => 'fal fa-cogs',
                    'name' => $this->l('General settings'),
                    'active' => 0,
                ),
                'advanced_settings_tab' => array(
                    'icon' => 'far fa-sliders-h-square',
                    'name' => $this->l('Advanced settings'),
                    'active' => 0,
                ),
                'validation_tab' => array(
                    'icon' => 'far fa-user-check',
                    'name' => $this->l('Validation settings'),
                    'active' => 0,
                ),
                'orderStatus_tab' => array(
                    'icon' => 'fal fa-abacus',
                    'name' => $this->l('Order status management'),
                    'active' => 0,
                ),
                'groupManagement_tab' => array(
                    'icon' => 'fal fa-users',
                    'name' => $this->l('Customer group management'),
                    'active' => 0,
                ),
                'exemption_tab' => array(
                    'icon' => 'fal fa-percentage',
                    'name' => $this->l('VAT exemption settings'),
                    'active' => 0,
                ),
                'invoice_tab' => array(
                    'icon' => 'fal fa-file-invoice',
                    'name' => $this->l('Invoice settings'),
                    'active' => 0,
                ),
                'field_tab' => array(
                    'icon' => 'fal fa-tags',
                    'name' => $this->l('VAT field settings'),
                    'active' => 0,
                ),
                'bretxit_tab' => array(
                    'icon' => 'fab fa-creative-commons-nc-eu',
                    'name' => $this->l('Brexit settings'),
                    'active' => 0,
                ),
                'email_tab' => array(
                    'icon' => 'far fa-envelope',
                    'name' => $this->l('Email settings'),
                    'active' => 0,
                ),
                'checkvatcron_tab' => array(
                    'icon' => 'fal fa-calendar-alt',
                    'name' => $this->l('Cron settings'),
                    'active' => 0,
                ),
                'documentation_tab' => array(
                    'icon' => 'fal fa-book',
                    'name' => $this->l('Documentation'),
                ),
                'compatibility_tab' => array(
                    'icon' => 'fal fa-cubes',
                    'name' => $this->l('Compatibility/Integration with third party modules'),
                ),
                'faq_tab' => array(
                    'icon' => 'fal fa-question',
                    'name' => $this->l('FAQ'),
                ),
                'troubleshooting_tab' => array(
                    'icon' => 'fal fa-laptop-medical',
                    'name' => $this->l('Troubleshooting'),
                ),
                'changelog_tab' => array(
                    'icon' => 'fal fa-code',
                    'name' => $this->l('Changelog'),
                ),
                'license_tab' => array(
                    'icon' => 'fal fa-gavel',
                    'name' => $this->l('License'),
                ),
            ),
            'link_tabs' => array(
                'version' => array(
                    'icon' => 'fal fa-info-circle',
                    'name' => $this->l('Version').' '.$this->version,
                ),
                'support' => array(
                    'icon' => 'fal fa-life-ring',
                    'name' => $this->l('Support system'),
                    'link' => $addons_support_url,
                    'target' => '_blank'
                ),
                'catalogue' => array(
                    'icon' => 'fal fa-cubes',
                    'name' => $this->l('Visit our catalogue of modules'),
                    'link' => $catalogue_url,
                    'target' => '_blank'
                ),
                'rate' => array(
                    'icon' => 'fas fa-star',
                    'name' => $this->l('Leave a feedback and rate the module'),
                    'link' => $addons_ratings_url,
                    'target' => '_blank',
                    'style' => 'background:#363a41;color:#fff;'
                )
            )
        );
    } 

    /**
     * AdvancedVatManager::getContent()
     * Load the configuration form
     * @return
     */
    public function getContent()
    {
        $this->output = '';
        $this->checkAddressFormat();
        $this->checkBrexitCurrency();
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submit_'.$this->name)) == true) {
            $this->postProcess();
            $this->output .= $this->displayConfirmation($this->l('Settings updated successfully.'));            
        }
        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'changelog' => file(_PS_MODULE_DIR_.$this->name.'/Changelog.txt'),
            'checkvat_cron_url' => Tools::getShopDomainSsl(true) . _MODULE_DIR_ . 'advancedvatmanager/checkcustomervat-cron'.(version_compare(_PS_VERSION_, '1.7.0.0', '>=')?'17':'16').'.php?token=' . Tools::substr(Tools::hash('advancedvatmanager'), 0, 12) . '&id_shop=' . $this->context->shop->id.'&id_lang='.$this->context->language->id,
            'getGBPcurrencyAmount' => $this->getGBPcurrencyAmount().(method_exists('Currency', 'getSymbol')?$this->context->currency->getSymbol():$this->context->currency->sign), 
            'license_content' => Tools::file_get_contents(file_exists(_PS_MODULE_DIR_.'advancedvatmanager/license_'.$this->context->language->iso_code.'.html')?_PS_MODULE_DIR_.'advancedvatmanager/license_'.$this->context->language->iso_code.'.html':_PS_MODULE_DIR_.'advancedvatmanager/license_en.html'),
            'faq_content' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/faq.tpl'),
            'troubleshooting_content' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/troubleshooting.tpl'),
            'opc_presteamshop_enabled' => $this->opc_presteamshop_enabled
        ));
        Media::addJsDef(array(
            'add_element' => $this->l('Add'),
        ));
        
        /* Adds Javascript and CSS files */
        $this->context->controller->addCSS('https://pro.fontawesome.com/releases/v5.15.4/css/all.css');
        $this->context->controller->addJqueryPlugin('tagify');
        $this->context->controller->addCSS($this->_path.'views/css/admin/back.css');
        $this->context->controller->addJS($this->_path.'views/js/admin/common_form.js');

        $header  = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        
        return $this->output.$header.$this->renderForm();
    }
    
    /**
     * AdvancedVatManager::renderForm()
     * Create the form that will be displayed in the configuration of your module.
     * @return
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        // Language
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token  = Tools::getAdminTokenLite('AdminModules');
        
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            // vertical tabs
            'vertical_tabs' => $this->getTabsForm(),
            /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        // allow multiple forms to combine with tabs
        $helper->multiple_fieldsets = true;
        return $helper->generateForm($this->getConfigForm());
    }

     /**
      * AdvancedVatManager::getConfigForm()
      * Create the structure of your form.
      * @return
      */
     protected function getConfigForm()
     {
        $switch = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('Disabled')
            )
        );
        // Get country list
        $countries = array();
        $countries_2 = array();
        $customer_groups_2 = array();
        $groupAssignment = array();
        $order_states = array();
        
        $countries_2[] = array('id' => 0, 'name' => $this->l('Disabled'));
        $customer_groups = array();
        $customer_groups_2[] = array('id' => 0, 'name' => $this->l('Disabled')); 
        
        // Gets customer groups
        foreach (Group::getGroups($this->context->language->id) as $group) {
            $customer_groups[] = array('id' => $group['id_group'], 'name' => $group['name']);
            $customer_groups_2[] = array('id' => $group['id_group'], 'name' => $group['name']); 
            if (Configuration::get('PS_CUSTOMER_GROUP') == $group['id_group']) {
                $defaultGroup = $group['name'];
            }
        }
        // Gets Countries
        foreach (Country::getCountries($this->context->language->id, false) as $country) {
            $countries[] = array('id' => $country['id_country'], 'name' => $country['name']);
            $countries_2[] = array('id' => $country['id_country'], 'name' => $country['name']);
            if (Configuration::get('ADVANCEDVATMANAGER_COUNTRY') && in_array($country['id_country'], json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')))) {
                $groupAssignment[] = array('type' => 'select','label' => sprintf($this->l('%s'),$country['name']),'desc' => sprintf($this->l('Customer group assignation for %s.'),$country['name']),'name' => 'ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_'.$country['id_country'],'options' => array('query' => $customer_groups_2,'id' => 'id','name' => 'name'));
            }
        }
        
        // Gets Order states
        $order_states[] = array('id' => 0, 'name' => $this->l('Disabled'));
        foreach (OrderState::getOrderStates($this->context->language->id) as $order_state) {
            $order_states[] = array('id' => $order_state['id_order_state'], 'name' => $order_state['name']);    
        }
        
        
        $groupAssignment[] = array(
            'type' => 'switch',
            'label' => $this->l('Use as default group'),
            'name' => 'ADVANCEDVATMANAGER_DEFAULT_GROUP_ASSIGNATION',
            'desc' => $this->l('This option allows you to assign the group as default customer group. The default customer group will be configured with respect to the last address added by the customer.'),
            'is_bool' => true, 
            'values' => $switch
        );
        $groupAssignment[] = array(
            'type' => 'switch',
            'label' => $this->l('Change default group with address'),
            'name' => 'ADVANCEDVATMANAGER_CHANGE_DEFAULT_GROUP_WITHADDRESS',
            'desc' => $this->l('This option allows you to change the customer default group associated with the country in this section when the customer selects a default address or during the purchase process. If the address selected has not valid VAT number, the system will change to Prestashop customer default group').' ['.$defaultGroup.']',
            'is_bool' => true, 
            'values' => $switch
        ); 
        $groupAssignment[] = array(
            'type' => 'switch',
            'label' => $this->l('Remove customer from another groups'),
            'name' => 'ADVANCEDVATMANAGER_DELETE_PREVIOUS_GROUPS',
            'desc' => $this->l('This option removes the client from the groups where it was previously assigned and leaves it only assigned to the group that is configured by country in this section. The default group will be changed to current group assigned.'),
            'is_bool' => true, 
            'values' => $switch
        );
                                                  
        $config_form = array(
            'activation_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Activation settings'),
                        'icon' => 'fas fa-power-off'
                    ),
                    'description' => $this->l('EU countries are selected by default except the default country of the store, due to the fact that, following the regulation of intra-community operations, the validation of VAT numbers must be carried out for customers who reside outside the country where the store is located and also belong to the Union european. If still, you want to also validate the VAT numbers of customers who reside within the country where the store is located, you must add it to the list of countries for validation. The [Countries for validation] option is only useful to add or remove countries that belong to the European Union or have an agreement with intra-community operations. In a normal situation, that option is predefined and should not be changed. Take in consideration that North of Ireland is included for intra-communitary operations and belongs to United Kingdom (England).'),
                    'input' => array(
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Back-office validation'),
                            'name' => 'ADVANCEDVATMANAGER_ADMINVALIDATION',
                            'is_bool' => true,
                            'desc' => $this->l('Activate validation for VAT numbers in Address section of Back-office.'),
                            'hint' => $this->l('Some modules use controllers belonging to the back-office for the creation of customer addresses through cron tasks or through processes. For the module to be able to validate the VAT numbers of these created addresses, this option must be activated.'),
                            'identifier' => 'id',
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Front-office validation'),
                            'name' => 'ADVANCEDVATMANAGER_FRONTVALIDATION',
                            'multistore_configuration_key' => 'ADVANCEDVATMANAGER_FRONTVALIDATION',
                            'is_bool' => true,
                            'desc' => $this->l('Activate validation for VAT numbers in Address section of Front-office when a customer create or modify an address or during checkout process.'),
                            'identifier' => 'id',
                            'values' => $switch
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->l('Countries for validation'),
                            'size' => 20,
                            'class' => 'advancedvatmanager_country_selector',
                            'desc' => $this->l('Select the countries to activate the validation. You should not modify this list unless the regulations for intra-community operations change and the countries involved are modified or you want to include your local country in the validation.'),
                            'hint' => $this->l('CTRL and click or click and drag for multiple options selection.'),
                            'name' => 'ADVANCEDVATMANAGER_COUNTRY[]',
                            'multiple' => true,
                            'options' => array(
                                'query' => $countries,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'settings_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('General settings'),
                        'icon' => 'fal fa-cogs'
                    ),
                    'description' => $this->l('Take in consideration that for the tax exemption operation to be legally correct, the merchant, in addition to the customer, must be registered with a valid intra-community VAT. The system will validate the intra-community VAT number of the merchant by VIES or GOV.UK before saving it in the database.'),
                    'input' => array(
                            array(
                                'type' => 'text',
                                'col' => 3,
                                'label' => $this->l('Merchant VAT'),
                                'name' => 'ADVANCEDVATMANAGER_MERCHANT_VAT',
                                'desc' => $this->l('Insert the merchant VAT number (with ISO code) which the business is registered for intra-communitary operations. Write full number with iso code (Ex: FR17852960). VAT numbers from United Kingdom are not allowed.'),
                                'hint' => $this->l('This is necessary for 2-way validation system option. Could leave empty if you do not use 2-way validation system.'),
                            ),   
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'advanced_settings_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Advanced settings'),
                        'icon' => 'far fa-sliders-h-square'
                    ),
                    'input' => array(
                            array(
                                'type' => 'switch',
                                'label' => $this->l('Display warning message in address'),
                                'name' => 'ADVANCEDVATMANAGER_DISPLAY_WARNING_MESSAGE_ADDRESS',
                                'is_bool' => true,
                                'desc' => $this->l('Displays warning messages in the address section of the customers account when they do not have a valid VAT number registered, or have to register a VAT number (mandatory field option activated) or have to validate the company field (company validation option activated).'),
                                'hint' => $this->l('Only works in Prestashop 1.7 or higher'),
                                'identifier' => 'id',
                                'values' => $switch
                            ),
                            array(
                                'type' => 'switch',
                                'label' => $this->l('Do not allow checkout when the address is incomplete'),
                                'name' => 'ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION',
                                'is_bool' => true,
                                'desc' => $this->l('This option does not allow the customer to continue with the purchase process if the selected address has the VAT number or company name field that must be validated or filled in (Depending on the validation and VAT field configuration options selected). Useful option for clients who already had registered addresses in the database'),
                                'hint' => $this->l('Only works in Prestashop 1.7 or higher'),
                                'identifier' => 'id',
                                'values' => $switch
                            ),
                            array(
                                'type' => 'textarea',
                                'lang' => true,
                                'autoload_rte' => true,
                                'label' => $this->l('Custom price block'),
                                'name' => 'ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL' ,
                                'desc' => $this->l('Add a custom text in product price block (below price) and it will be displayed when a customer has selected an address with valid VAT number and is tax exempt.')
                            ),
                            
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),            
            'validation_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Validation settings'),
                        'icon' => 'far fa-users-check'
                    ),
                    'description' => $this->l('It is highly recommended to use the 2-way validation system to ensure the legal integrity of the tax exemption in intra-community transactions, verifying both the VAT number of the merchant and the customer. For 2-way validation, it is necessary to set Merchant vat number in general settings. If Merchant vat option is empty, the system will use 1-way validation in any case.'),
                    'input' => array(
                                array(
                                    'type' => 'select',
                                    'label' => $this->l('Validation system'),
                                    'desc' => $this->l('Select the validation system for VAT number. Validation 1-way only checks and validates the customer VAT number, even when the merchant have not a valid VAT number. Validation 2-way sends merchant VAT number information and customer VAT number and verify if both are already registered to make intra-communitary operations with tax exemption.'),
                                    'hint' => $this->l('Only works with [Format + API system] validation system option selected.'),
                                    'name' => 'ADVANCEDVATMANAGER_VALIDATION_SYSTEM',
                                    'options' => array(
                                        'query' => array(
                                            array('id' => '1-way', 'name' => $this->l('Validation 1-way')),
                                            array('id' => '2-way', 'name' => $this->l('Validation 2-way')),
                                        ),
                                        'id' => 'id',
                                        'name' => 'name'
                                    )
                                ),
                                array(
                                    'type' => 'select',
                                    'label' => $this->l('Validation mode'),
                                    'desc' => $this->l('This option set the mode of VAT validation. Strict mode validates VAT numbers with correct format inserted by the customer (Ex: FR01609493). Smart mode validates VAT numbers even the format is not inserted correctly by the customer (Ex: FR-01609493, FR 01609493, FR01.609.493) and save as valid format (Ex: FR01609493)'),
                                    'name' => 'ADVANCEDVATMANAGER_VALIDATION_MODE',
                                    'options' => array(
                                        'query' => array(
                                            array('id' => 'strict', 'name' => $this->l('Strict mode')),
                                            array('id' => 'smart', 'name' => $this->l('Smart mode')),
                                        ),
                                        'id' => 'id',
                                        'name' => 'name'
                                    )
                                ),
                                array(
                                    'type' => 'select',
                                    'label' => $this->l('Validation type'),
                                    'desc' => $this->l('This option set the type of VAT validation. Format validation mode only validates VAT numbers format (Ex: FR01609493). Format + API validation validates VAT number format and verify it into VIES or GOV.UK API system (recommeded for VAT exemption).'),
                                    'name' => 'ADVANCEDVATMANAGER_VALIDATION_TYPE',
                                    'options' => array(
                                        'query' => array(
                                            array('id' => 'format', 'name' => $this->l('Format validation')),
                                            array('id' => 'api', 'name' => $this->l('Format + API system validation')),
                                        ),
                                        'id' => 'id',
                                        'name' => 'name'
                                    )
                                ),
                                array(
                                    'type' => 'switch',
                                    'label' => $this->l('Company validation'),
                                    'name' => 'ADVANCEDVATMANAGER_COMPANY_VALIDATION',
                                    'desc' => $this->l('If it is enabled, the company name field will be validated together with the information obtained from the VIES/GOV.UK system regarding the VAT number. If the customer does not type the company name exactly as it appears in the VIES/GOV.UK system database, the address will not be validated with this VAT number. The previos customers registered in database with company name not validated yet but valid VAT, they will be required to update the address information before making a purchase in the store.'),
                                    'hint' => $this->l('To avoid errors in the address forms, make sure that the "company" field is displayed in the form. If this option is enabled, this field will be as required in address form.'),
                                    'values' => $switch
                                ),
                                array(
                                    'type' => 'switch',
                                    'label' => $this->l('Auto-insert company name'),
                                    'name' => 'ADVANCEDVATMANAGER_COMPANY_AUTOINSERT',
                                    'desc' => $this->l('If enabled, when the customer registers an address with a valid VAT number, the system will insert the registered company name taken from the VIES or GOV.UK system. If the customer has entered another company name in the company field, it will be overwritten with the correct name registered in the system.'),
                                    'hint' => $this->l('This option is not compatible with [Company validation] option enabled.'),
                                    'values' => $switch
                                ),
                                array(
                                    'type' => 'switch',
                                    'label' => $this->l('Allow duplicated VAT'),
                                    'name' => 'ADVANCEDVATMANAGER_ALLOWDUPLICATED',
                                    'desc' => $this->l('This option enable or disable to allow different customer registrations with same VAT numbers.'),
                                    'values' => $switch
                                ),
                                array(
                                    'type' => 'switch',
                                    'label' => $this->l('Skip API system fail'),
                                    'name' => 'ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL',
                                    'desc' => $this->l('This option skips validation in case the VIES/GOV.UK API system fails during the validation process. Customer can continue with address registration and checkout. The VAT number will appear as [Validated] and the system status will appear in red color as [API request processing error] in VAT Management list.'),
                                    'hint' => $this->l('This option only works in API system validation.'),
                                    'values' => $switch
                                ),
                                array(
                                    'type' => 'switch',
                                    'label' => $this->l('Save in Uppercase'),
                                    'name' => 'ADVANCEDVATMANAGER_SAVETOUPPERCASE',
                                    'desc' => $this->l('Enable this option to save VAT number with letters in uppercase when add or update actions is performed by customer in address form. Ex: FR01609493 instead fr01609493'),
                                    'values' => $switch
                                ),
                                array(
                                    'type' => 'tags',
                                    'label' => $this->l('Blacklist'),
                                    'name' => 'ADVANCEDVATMANAGER_BLACKLIST',
                                    'desc' => $this->l('Set VAT numbers into a blacklist to not allow customer registration with some of these numbers. Write one per one separated by comma.'),
                                ), 
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'orderStatus_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Order status management'),
                        'icon' => 'fal fa-abacus'
                    ),
                    'description' => $this->l('The module will insert the status that has been configured in this section, when the customer places an order. In the case of the order status with the valid VAT number, this will be inserted first and just after the next status will be inserted depending on the payment options chosen in the purchase process. In the case of the status of the order with the VAT number pending validation due API process failure, it will be inserted as the last and current status of the order, so that the customer knows that his order is pending validation with the VAT number.'),
                    'input' => array(
                                array(
                                    'type' => 'select',
                                    'label' => $this->l('Order status when VAT is validated'),
                                    'desc' => $this->l('This option configures the status of the order when the VAT number is validated successfully. This status will be displayed at the beginning, just before the default status when ordering. Set [Disable] to disable the option'),
                                    'hint' => $this->l('The module has created it own state called "VAT number validated". But you can use any custom state.'),
                                    'name' => 'ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS',
                                    'options' => array(
                                        'query' => $order_states,
                                        'id' => 'id',
                                        'name' => 'name'
                                    )
                                ),
                                array(
                                    'type' => 'select',
                                    'label' => $this->l('Order status when API system fails'),
                                    'desc' => $this->l('This option configures the status of the order when the API system has failed to validate the customer VAT number when the [Skip validation] option has been activated and the customer has been able to continue with the order. Set [Disable] to disable the option'),
                                    'hint' => $this->l('The module has created it own state called "VAT number pending validation". But you can use any custom state.'),
                                    'name' => 'ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS',
                                    'options' => array(
                                        'query' => $order_states,
                                        'id' => 'id',
                                        'name' => 'name'
                                    )
                                ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'groupManagement_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Customer group assignment by country'),
                        'icon' => 'fal fa-users'
                    ),
                    'description' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/groupassignment_description.tpl'),
                    'input' => $groupAssignment,
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'exemption_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('VAT exemption settings'),
                        'icon' => 'fal fa-percentage'
                    ),
                    'description' => $this->l('The system will proceed to validate all the addresses created by the client as long as it meets the conditions for validation, regardless of whether the type of address has been chosen in the option shown in this section. This option is only to determine if the customer will be tax exempt when choosing the address in the checkout process.'),
                    'input' => array(
                                array(
                                    'type' => 'switch',
                                    'label' => $this->l('VAT exemption'),
                                    'name' => 'ADVANCEDVATMANAGER_VATEXEMPTION',
                                    'desc' => $this->l('This option allows you to remove taxes from customers with a valid VAT number. Works for any type of validation (format or API system).'),
                                    'hint' => $this->l('The prices of the products and the amounts of the shopping cart will be shown without taxes.'),
                                    'values' => $switch
                                ),
                                array(
                                    'type' => 'select',
                                    'label' => $this->l('Address type'),
                                    'name' => 'ADVANCEDVATMANAGER_ADDRESS_TYPE',
                                    'desc' => $this->l('This option allows you to remove taxes from customers with a valid VAT number in determined address type (delivery address, invoice address or both).'),
                                    'hint' => $this->l('If the "invoice address and delivery address" option is chosen, it will be exempt from taxes if one of the two addresses or both have a valid VAT number.'),
                                    'options' => array(
                                        'query' => array(
                                            array('id' => 'id_address_invoice', 'name' => $this->l('Invoice address')),
                                            array('id' => 'id_address_delivery', 'name' => $this->l('Delivery address')),
                                            array('id' => 'both', 'name' => $this->l('Invoice and Delivery address')),
                                        ),
                                        'id' => 'id',
                                        'name' => 'name'
                                    )
                                ),
                                array(
                                    'type' => 'select',
                                    'label' => $this->l('Local country'),
                                    'name' => 'ADVANCEDVATMANAGER_LOCAL_COUNTRY',
                                    'desc' => $this->l('This option allows you to assign your home country where product prices and orders will always be subject to tax for this country.'),
                                    'options' => array(
                                        'query' => $countries_2,
                                        'id' => 'id',
                                        'name' => 'name'
                                    ),
                                ),
                                array(
                                    'type' => 'checkbox',
                                    'size' => 10,
                                    'label' => $this->l('Customer groups exemption'),
                                    'name' => 'ADVANCEDVATMANAGER_GROUP_VATEXEMPTION',
                                    'desc' => $this->l('This option allows you to remove taxes for certain customers groups even if VAT number is valid or not. Leave empty to disable it for all customers groups. If check any group, the tax exemption will be only applied for customer of selected groups. This option is completely independent of the [VAT exemption] option.'),
                                    'multiple' => true,
                                    'values' => array(
                                        'query' => $customer_groups,
                                        'id' => 'id',
                                        'name' => 'name'
                                    ),
                                ),
                                
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'invoice_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Invoice settings'),
                        'icon' => 'fal fa-file-invoice'
                    ),
                    'input' => array(
                        array(
                            'type' => 'textarea',
                            'lang' => true,
                            'label' => $this->l('Invoice legal text'),
                            'name' => 'ADVANCEDVATMANAGER_LEGAL_TEXT',
                            'desc' => $this->l('Add the legal text in invoice from a order with VAT exempt. The text will be added below to default legal text.')
                        ),
                        array(
                            'type' => 'textarea',
                            'lang' => true,
                            'autoload_rte' => true,
                            'label' => $this->l('Invoice note'),
                            'name' => 'ADVANCEDVATMANAGER_INVOICE_NOTE',
                            'desc' => $this->l('Add note in invoice from a order with VAT exempt. The text will be added above to added notes.')
                        ),  
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'field_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('VAT field settings'),
                        'icon' => 'fal fa-tags'
                    ),
                    'description' => $this->l('These options will be applied only for countries selected for validation in [Activation] tab.').'<br />'.$this->l('NOTE: These options are not applied in the validation of VAT numbers from the Back-office.'),
                    'warning' => $this->l('Only the checkout modules that we have integrated to 100% are compatible with the options in this section. Some of these options may not work with other checkout modules, even those that are supported by this module and that we mention in the compatibility list. This is because generally the modules that manage the checkout page use their own options and controllers to manage the fields that are displayed in the checkout process of their module.'),
                    'input' => array(
                                array(
                                    'type' => 'select',
                                    'label' => $this->l('VAT field'),
                                    'desc' => $this->l('Select to convert field in optional or required.'),
                                    'hint' => $this->l('This option works only for countries selected for the validation.'),
                                    'name' => 'ADVANCEDVATMANAGER_VATFIELD',
                                    'options' => array(
                                        'query' => array(
                                            array('id' => 'optional', 'name' => $this->l('Optional field')),
                                            array('id' => 'required', 'name' => $this->l('Required field')),
                                        ),
                                        'id' => 'id',
                                        'name' => 'name'
                                    )
                                ),
                                array(
                                    'type' => 'switch',
                                    'label' => $this->l('Display only for company'),
                                    'name' => 'ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY',
                                    'desc' => $this->l('Activate this option to display VAT number field only when customers insert a company name. Hide the VAT number field when no company name and althought you select this field as required, it will be turn into optional.'),
                                    'hint' => $this->l('This option only works in front office. The company field will be displayed in address form but in backoffice in any case.'),
                                    'values' => $switch
                                ),
                                array(
                                    'type' => 'text',
                                    'lang' => true,
                                    'label' => $this->l('Label'),
                                    'name' => 'ADVANCEDVATMANAGER_FIELD_LABEL',
                                    'desc' => $this->l('Customize the VAT number field label. Leave empty for default label.')
                                ),
                                array(
                                    'type' => 'text',
                                    'lang' => true,
                                    'label' => $this->l('Legend'),
                                    'name' => 'ADVANCEDVATMANAGER_FIELD_LEGEND',
                                    'desc' => $this->l('Customize the VAT number field legend. It is located below to the input. Leave empty for default label.')
                                ), 
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'bretxit_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Brexit settings'),
                        'icon' => 'fab fa-creative-commons-nc-eu'
                    ),
                    'description' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/brexit_description.tpl'),
                    'warning' => sprintf($this->l('The amount of %s is the threshold for VAT exemption in UK VAT numbers. The exchange rate should be updated dialy by CRON task or in currency section.'), $this->getGBPcurrencyAmount().(method_exists('Currency', 'getSymbol')?$this->context->currency->getSymbol():$this->context->currency->sign)),
                    'input' => array(
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Brexit'),
                            'name' => 'ADVANCEDVATMANAGER_BREXIT_ENABLED',
                            'is_bool' => true,
                            'desc' => $this->l('Active Brexit tax rules for exportations to United Kindom country.'),
                            'identifier' => 'id',
                            'values' => $switch
                        ), 
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Vat exemption in companies'),
                            'name' => 'ADVANCEDVATMANAGER_BREXIT_VATEXEMPT_LESSTHAN135GBP',
                            'is_bool' => true,
                            'desc' => $this->l('This option allows to be vat exemption for companies with a valid UK VAT number when cart total products amount is equal to or less than 135 GBP'),
                            'identifier' => 'id',
                            'values' => $switch
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->l('Not allow orders'),
                            'name' => 'ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS',
                            'is_bool' => true,
                            'desc' => $this->l('This option allows you to choose between not allowing purchases of amounts less than 135 GBP or greater than 135 GBP.'),
                            'options' => array(
                                    'query' => array(
                                        array('id' => 0, 'name' => $this->l('Disabled')),
                                        array('id' => 1, 'name' => $this->l('Over 135 GBP')),
                                        array('id' => 2, 'name' => $this->l('Below 135 GBP')),
                                    ),
                                    'id' => 'id',
                                    'name' => 'name'
                            )
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'email_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Email settings'),
                        'icon' => 'far fa-envelope'
                    ),
                    'input' => array(
                        array(
                            'type' => 'tags',
                            'label' => $this->l('Email addresses'),
                            'name' => 'ADVANCEDVATMANAGER_EMAIL_ADDRESS',
                            'desc' => $this->l('You can set different email addresses to receive emails from this module. You must write a correct email address. For multiple addresses, you must write them separated with comma (Ex: test@test.com,test2@test2.com...).'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('API alert email'),
                            'name' => 'ADVANCEDVATMANAGER_SENDAPIALERT',
                            'is_bool' => true,
                            'desc' => $this->l('Send email alerting when customer is registered and VAT validation by API system fails.'),
                            'identifier' => 'id',
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Send email to client when API fails'),
                            'name' => 'ADVANCEDVATMANAGER_SEND_CUSTOMER_EMAIL_API_FAIL_ORDER',
                            'is_bool' => true,
                            'desc' => $this->l('Send email to the customer notifying them that the status of the order is pending to validate the VAT number, when placing an order with an address where the VAT number could not be validated due to an API system failure and the [Skip validation] option has been activated in the validation settings section allowing the customer to continue with the registration of the address and the order.'),
                            'identifier' => 'id',
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Customer validation email'),
                            'name' => 'ADVANCEDVATMANAGER_SENDEMAILVALIDATION',
                            'is_bool' => true,
                            'desc' => $this->l('Send email informing when customer is registered and VAT validation by API system is performed sucessfully.'),
                            'identifier' => 'id',
                            'values' => $switch
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
            'checkvatcron_tab' => array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('CRON settings for checking VAT numbers'),
                        'icon' => 'fal fa-calendar-alt'
                    ),
                    'description' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/checkvatcron.tpl'),
                    'input' => array(
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Continue scan process from last address scanned'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_SCANFROMLAST',
                            'is_bool' => true,
                            'desc' => $this->l('This option must be enabled if you want the system to continue scanning from the last address scanned. This is useful when the last scanning process failed or if you do not want the system to rescan from the first address saved in the database.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Skip API system fail'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_SKIPAPISYSTEMFAIL',
                            'is_bool' => true,
                            'desc' => $this->l('This option skips validation in case the VIES/GOV.UK API system fails during the validation process. The VAT number will appear as [Validated] and the system status will appear in red color as [API request processing error] in VAT Management list.'),
                            'hint' => $this->l('This option only works in API system validation.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Delete empty VAT number'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_DELETE_EMPTY',
                            'is_bool' => true,
                            'desc' => $this->l('Delete customer address with empty VAT number if the field is required.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Delete invalid VAT number'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_DELETE_INVALID',
                            'is_bool' => true,
                            'desc' => $this->l('Delete customer address with invalid VAT number.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Delete duplicated VAT number'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_DELETE_DUPLICATED',
                            'is_bool' => true,
                            'desc' => $this->l('Delete customer address with duplicated VAT number. The system will leave only the first address created and will delete the rest with duplicated VAT number if duplicated VAT is not allowed.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Send email for invalid VAT number'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID',
                            'is_bool' => true,
                            'desc' => $this->l('Sends email to customers with invalid VAT number.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Send email for empty VAT number'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY',
                            'is_bool' => true,
                            'desc' => $this->l('Sends email to customers with empty VAT number if the field is required.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Send email for duplicated VAT number'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_SENDEMAIL_DUPLICATED',
                            'is_bool' => true,
                            'desc' => $this->l('Sends email to customers with duplicated VAT number if duplicated VAT is not allowed.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Customer group assignation'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_ASSIGN_GROUP',
                            'is_bool' => true,
                            'desc' => $this->l('Manages client groups as configured in the [Client Group Assignment] section.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Send email for invalid company name'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID_COMPANY',
                            'is_bool' => true,
                            'desc' => $this->l('Sends email to customers with VAT number valid but company name not registered in VIES or GOV.UK.'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Send email for empty company name'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY_COMPANY',
                            'is_bool' => true,
                            'desc' => $this->l('Sends email to customers with VAT number valid but company name empty'),
                            'values' => $switch
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Auto-insert registered company name'),
                            'name' => 'ADVANCEDVATMANAGER_CRON_AUTOINSERT_COMPANY',
                            'is_bool' => true,
                            'desc' => $this->l('The system will insert the registered company name in VIES or GOV.UK for customers who have a valid VAT number.'),
                            'hint' => $this->l('This action will overwrite the company name that the customer already has saved in the address and replace it with the company name that is registered with VIES or GOV.UK.'),
                            'values' => $switch
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save')
                    )
                ),
            ),
        );
        
        return $config_form;
    }

    /**
     * AdvancedVatManager::getConfigFormValues()
     * Set values for the inputs.
     * @return
     */
    protected function getConfigFormValues()
    {
        $label = array();
        $legend = array();
        $legal_text = array();
        $invoice_note = array();
        $custom_text_product_price = array();
        $config_fields_2 = array();
        /* Table title for multiple languages */
        foreach (Language::getLanguages(false) as $language) {
            $label[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_FIELD_LABEL_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_FIELD_LABEL', $language['id_lang']));
            $legend[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_FIELD_LEGEND_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_FIELD_LEGEND', $language['id_lang']));
            $legal_text[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_LEGAL_TEXT_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_LEGAL_TEXT', $language['id_lang']));
            $invoice_note[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_INVOICE_NOTE_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_INVOICE_NOTE', $language['id_lang']));
            $custom_text_product_price[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL', $language['id_lang']));
        }
        
        $countries = json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY'));
        if (!empty($countries)) {
            foreach ($countries as $country) {
                $config_fields_2['ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_'.$country] = Configuration::get('ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_'.$country);
            }
        }
        
        $config_fields = array(
            'ADVANCEDVATMANAGER_ADMINVALIDATION' => Configuration::get('ADVANCEDVATMANAGER_ADMINVALIDATION'),
            'ADVANCEDVATMANAGER_FRONTVALIDATION' => Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION'),
            'ADVANCEDVATMANAGER_DISPLAY_WARNING_MESSAGE_ADDRESS' => Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WARNING_MESSAGE_ADDRESS'),
            'ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION' => Configuration::get('ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION'),
            'ADVANCEDVATMANAGER_COUNTRY[]' => json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')),
            'ADVANCEDVATMANAGER_EMAIL_ADDRESS' => Configuration::get('ADVANCEDVATMANAGER_EMAIL_ADDRESS'),
            'ADVANCEDVATMANAGER_SENDAPIALERT' => Configuration::get('ADVANCEDVATMANAGER_SENDAPIALERT'),
            'ADVANCEDVATMANAGER_SEND_CUSTOMER_EMAIL_API_FAIL_ORDER' => Configuration::get('ADVANCEDVATMANAGER_SEND_CUSTOMER_EMAIL_API_FAIL_ORDER'),
            'ADVANCEDVATMANAGER_SENDEMAILVALIDATION' => Configuration::get('ADVANCEDVATMANAGER_SENDEMAILVALIDATION'),
            'ADVANCEDVATMANAGER_VALIDATION_MODE' => Configuration::get('ADVANCEDVATMANAGER_VALIDATION_MODE'),
            'ADVANCEDVATMANAGER_VATFIELD' => Configuration::get('ADVANCEDVATMANAGER_VATFIELD'),
            'ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY' => Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY'),
            'ADVANCEDVATMANAGER_ALLOWDUPLICATED' => Configuration::get('ADVANCEDVATMANAGER_ALLOWDUPLICATED'),
            'ADVANCEDVATMANAGER_FIELD_LABEL' => $label,
            'ADVANCEDVATMANAGER_FIELD_LEGEND' => $legend,
            'ADVANCEDVATMANAGER_LEGAL_TEXT' => $legal_text, 
            'ADVANCEDVATMANAGER_INVOICE_NOTE' => $invoice_note,
            'ADVANCEDVATMANAGER_BLACKLIST' => Configuration::get('ADVANCEDVATMANAGER_BLACKLIST'),
            'ADVANCEDVATMANAGER_SAVETOUPPERCASE' => Configuration::get('ADVANCEDVATMANAGER_SAVETOUPPERCASE'),
            'ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL' => Configuration::get('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL'),
            'ADVANCEDVATMANAGER_VALIDATION_TYPE' => Configuration::get('ADVANCEDVATMANAGER_VALIDATION_TYPE'),
            'ADVANCEDVATMANAGER_COMPANY_VALIDATION' => Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION'),
            'ADVANCEDVATMANAGER_COMPANY_AUTOINSERT' => Configuration::get('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT'),
            'ADVANCEDVATMANAGER_VATEXEMPTION' => Configuration::get('ADVANCEDVATMANAGER_VATEXEMPTION'),
            'ADVANCEDVATMANAGER_DEFAULT_GROUP_ASSIGNATION' => Configuration::get('ADVANCEDVATMANAGER_DEFAULT_GROUP_ASSIGNATION'),
            'ADVANCEDVATMANAGER_LOCAL_COUNTRY' => Configuration::get('ADVANCEDVATMANAGER_LOCAL_COUNTRY'),
            'ADVANCEDVATMANAGER_MERCHANT_VAT' => Configuration::get('ADVANCEDVATMANAGER_MERCHANT_VAT'),
            'ADVANCEDVATMANAGER_VALIDATION_SYSTEM' => Configuration::get('ADVANCEDVATMANAGER_VALIDATION_SYSTEM'),
            'ADVANCEDVATMANAGER_GROUP_VATEXEMPTION' => Configuration::get('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION'),
            'ADVANCEDVATMANAGER_DELETE_PREVIOUS_GROUPS' => Configuration::get('ADVANCEDVATMANAGER_DELETE_PREVIOUS_GROUPS'),               
            'ADVANCEDVATMANAGER_BREXIT_ENABLED' => Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED'),  
            'ADVANCEDVATMANAGER_BREXIT_VATEXEMPT_LESSTHAN135GBP' => Configuration::get('ADVANCEDVATMANAGER_BREXIT_VATEXEMPT_LESSTHAN135GBP'), 
            'ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS' => Configuration::get('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS'),         
            'ADVANCEDVATMANAGER_CRON_ASSIGN_GROUP' => Configuration::get('ADVANCEDVATMANAGER_CRON_ASSIGN_GROUP'), 
            'ADVANCEDVATMANAGER_CRON_SENDEMAIL_DUPLICATED' => Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_DUPLICATED'), 
            'ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY' => Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY'),  
            'ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID' => Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID'), 
            'ADVANCEDVATMANAGER_CRON_DELETE_DUPLICATED' => Configuration::get('ADVANCEDVATMANAGER_CRON_DELETE_DUPLICATED'), 
            'ADVANCEDVATMANAGER_CRON_DELETE_INVALID' => Configuration::get('ADVANCEDVATMANAGER_CRON_DELETE_INVALID'), 
            'ADVANCEDVATMANAGER_CRON_DELETE_EMPTY' => Configuration::get('ADVANCEDVATMANAGER_CRON_DELETE_EMPTY'),           
            'ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID_COMPANY' => Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID_COMPANY'),
            'ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY_COMPANY' => Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY_COMPANY'),
            'ADVANCEDVATMANAGER_CRON_AUTOINSERT_COMPANY' => Configuration::get('ADVANCEDVATMANAGER_CRON_AUTOINSERT_COMPANY'),
            'ADVANCEDVATMANAGER_CRON_SCANFROMLAST' => Configuration::get('ADVANCEDVATMANAGER_CRON_SCANFROMLAST'),
            'ADVANCEDVATMANAGER_CRON_SKIPAPISYSTEMFAIL' => Configuration::get('ADVANCEDVATMANAGER_CRON_SKIPAPISYSTEMFAIL'),          
            'ADVANCEDVATMANAGER_CHANGE_DEFAULT_GROUP_WITHADDRESS' => Configuration::get('ADVANCEDVATMANAGER_CHANGE_DEFAULT_GROUP_WITHADDRESS'), 
            'ADVANCEDVATMANAGER_ADDRESS_TYPE' => Configuration::get('ADVANCEDVATMANAGER_ADDRESS_TYPE'),
            'ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS' => Configuration::get('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS'),
            'ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS' => Configuration::get('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS'),
            'ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL' => $custom_text_product_price         
        );
        
        $config_fields = array_merge($config_fields, $config_fields_2);
        
        $all_opts = Group::getGroups($this->context->language->id);
        $id_checkbox_options = array();
        foreach ($all_opts as $option) {
            $id_checkbox_options[] = $option['id_group'];   
        }
        // get checkbox stuff from $_POST
        $id_checkbox_options_post = array();
        foreach ($id_checkbox_options as $opt_id) {
            if (Tools::getValue('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION_' . (int) $opt_id)) {
                $id_checkbox_options_post['ADVANCEDVATMANAGER_GROUP_VATEXEMPTION_' . (int) $opt_id] = true;
            }
        }
        //get checkbox stuff from Configuration
        $id_checkbox_options_config = array();
        
        if (Configuration::get('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION') != '') {
            $config = explode(',', Configuration::get('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION'));
        } 
        else {
            $config = array();
        } 
        foreach ($config as $conf) {
            $id_checkbox_options_config['ADVANCEDVATMANAGER_GROUP_VATEXEMPTION_' . (int) $conf] = true;    
        }
        //return only common values and value from post
        if (((bool) Tools::isSubmit('submit_'.$this->name)) == true) {
            $config_fields = array_merge($config_fields, array_intersect($id_checkbox_options_post, $id_checkbox_options_config));
        } else {
            $config_fields = array_merge($config_fields, $id_checkbox_options_config);
        }             
        return $config_fields;   
    }

    /**
     * AdvancedVatManager::postProcess()
     * Save form data.
     * @return
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $label = array();
        $legend = array();
        $legal_text = array();
        $invoice_note = array();
        $custom_text_product_price = array();
        $all_opts = Group::getGroups($this->context->language->id);
        $checkbox_options = array();
        
        foreach (array_keys($form_values) as $key) {
            $aux = array();
            $ve = new ValidationEngine(Tools::getValue($key));
            if ($key == 'ADVANCEDVATMANAGER_COUNTRY[]') {
                $uk_id = Country::getByIso('GB');
                $countries_id = Tools::getValue('ADVANCEDVATMANAGER_COUNTRY');
                $uk_pos = array_search($uk_id, $countries_id);
                if (Tools::getValue('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 1 && $uk_id) {
                    if ($uk_pos === false) {
                        $countries_id[] = $uk_id;   
                    }  
                }
                Configuration::updateValue('ADVANCEDVATMANAGER_COUNTRY', json_encode($countries_id)); 
            }
            else if ($key == 'ADVANCEDVATMANAGER_MERCHANT_VAT' && Tools::getValue($key) && Configuration::get($key) != Tools::getValue($key)) {
                if ($ve->getrequesterVatIso() != 'GB') {
                    $ve->skip_api_fails = false; // Disable skip validation when API system fails.
                    if ($ve->vatValidationViesOneWay()) {
                        Configuration::updateValue($key, Tools::getValue($key));
                        $this->output .= $this->adminDisplayInformation($ve->getMessage());    
                    }
                    else {
                        $this->output .= $this->displayError($ve->getMessage());       
                    }     
                }
                else {
                    Configuration::updateValue($key, '');
                    $this->output .= $this->displayError($this->l('The merchant VAT number cannot be from the UK.'));          
                }
            }
            else if ($key == 'ADVANCEDVATMANAGER_EMAIL_ADDRESS' && Tools::getValue($key)) {
                $emails = explode(',', Tools::getValue($key));
                foreach ($emails as $email) {
                    if (!Validate::isEmail($email)) {
                        $this->output .= $this->adminDisplayWarning(sprintf($this->l('The email address %s is not a valid format.'), $email));     
                    }
                    else {
                        $aux[] = $email;
                    }
                    Configuration::updateValue($key, implode(',',$aux));        
                }   
            }
            else if ($key == 'ADVANCEDVATMANAGER_BLACKLIST' && Tools::getValue($key)) {
                $blacklist = explode(',', Tools::getValue($key));
                foreach ($blacklist as $element) {
                    if (!Validate::isString($element) || !$ve->basicFormatValidation($element)) {
                        $this->output .= $this->adminDisplayWarning(sprintf($this->l('The number %s is not a valid format.'), $element));     
                    }
                    else {
                        $aux[] = $element;
                    }
                            
                }
                Configuration::updateValue($key, implode(',',$aux));   
            }           
            else {
                Configuration::updateValue($key, Tools::getValue($key));     
            }
        }
        
        /* Input text for multiple languages */
        foreach (Language::getLanguages(false) as $language) {
            $label[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_FIELD_LABEL_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_FIELD_LABEL', $language['id_lang']));
            $legend[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_FIELD_LEGEND_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_FIELD_LEGEND', $language['id_lang']));
            $legal_text[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_LEGAL_TEXT_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_LEGAL_TEXT', $language['id_lang']));
            $invoice_note[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_INVOICE_NOTE_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_INVOICE_NOTE', $language['id_lang']));
            $custom_text_product_price[$language['id_lang']] = Tools::getValue('ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL_' . $language['id_lang'], Configuration::get('ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL', $language['id_lang']));
        }
        foreach ($all_opts as $chbx_options) {
            if (Tools::getValue('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION_' . (int) $chbx_options['id_group'])) {
                $checkbox_options[] = $chbx_options['id_group'];
                Configuration::updateValue('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION', implode(',', $checkbox_options));
            }
        }
        Configuration::updateValue('ADVANCEDVATMANAGER_FIELD_LABEL', $label);
        Configuration::updateValue('ADVANCEDVATMANAGER_FIELD_LEGEND', $legend);
        Configuration::updateValue('ADVANCEDVATMANAGER_LEGAL_TEXT', $legal_text);
        Configuration::updateValue('ADVANCEDVATMANAGER_INVOICE_NOTE', $invoice_note, true);
        Configuration::updateValue('ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL', $custom_text_product_price, true);
        $this->checkAddressFormat();
        $this->checkBrexitCurrency();
        Tools::clearCache();
    }
    
    /**
     * AdvancedVatManager::checkBrexitCurrency()
     * Checks Brexit enabled and pound currency installed
     * @return
     */
    public function checkBrexitCurrency()
    {
        if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 1) {
            if (!Currency::getIdByIsoCode('GBP', $this->context->shop->id, true)) {
                $this->output .= $this->adminDisplayWarning('<strong>[Advanced VAT Manager]</strong> '.$this->l('The GBP(pound) currency should be installed (active or not active) in order to work with Brexit option.'));     
            }
        }
    }
    
    /**
     * AdvancedVatManager::getEUCountriesID()
     * Gets EU Countries ID
     * @return
     */
    public function getEUCountriesID()
    {
        $countries = array();
        foreach (ValidationEngine::$european_countries_iso as $iso) {
            $country_id = Country::getByIso($iso);
            if (Configuration::get('PS_COUNTRY_DEFAULT') && $country_id == (int)Configuration::get('PS_COUNTRY_DEFAULT')) {
                continue;    
            }
            $countries[] = $country_id;    
        }
        foreach (ValidationEngine::$brexit_countries_iso as $iso) {
            $country_id = Country::getByIso($iso);
            if (Configuration::get('PS_COUNTRY_DEFAULT') && $country_id == (int)Configuration::get('PS_COUNTRY_DEFAULT')) {
                continue;    
            }
            $countries[] = Country::getByIso($iso);    
        }

        return json_encode($countries);
    }
    
    /**
     * AdvancedVatManager::getGB135currencyAmount()
     * Gets The currency amount
     * @return
     */
    public function getGBPcurrencyAmount()
    {
        $idGBP = Currency::getIdByIsoCode('GBP', $this->context->shop->id, true);
        $value = 135;// Value of 135 pounds for Brexit rules with vat exemption.
        if ($this->context->currency->id == $idGBP) {
            return Tools::math_round($value, 2);   
        }
        else {
            $currency = new Currency($idGBP);
            if (method_exists($currency, 'getConversionRate')) {
                return Tools::math_round($value/$currency->getConversionRate(), 2);        
            }
            else {
                return Tools::math_round($value/$currency->conversion_rate, 2);   
            }
        }
        return false;       
    }
    
    public function checkAddressFormat()
    {
        if (Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION') ||Configuration::get('ADVANCEDVATMANAGER_ADMINVALIDATION')) {
            $countries = json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY'));
            if (!empty($countries)) {
                foreach ($countries as $idCountry) {
                    $country_name = Country::getNameById($this->context->language->id, $idCountry);
                    $country_iso = Country::getIsoById($idCountry);
                    $addressForm = AddressFormat::getOrderedAddressFields($idCountry);
                    if (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1 && !in_array('company', $addressForm)) {
                        $this->adminDisplayWarning('<strong>[Advanced VAT Manager]</strong> '.sprintf($this->l('The country %s does not have the [company] field in the address format. To avoid errors in the validation of this field, edit the country by adding this field to your address format.'), $country_name));    
                    }
                    if (Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required' && $idCountry != Configuration::get('ADVANCEDVATMANAGER_LOCAL_COUNTRY') && !in_array('vat_number', $addressForm)) {
                        $this->adminDisplayWarning('<strong>[Advanced VAT Manager]</strong> '.sprintf($this->l('The country %s does not have the [vat_number] field in the address format. To avoid errors in the validation of this field, edit the country by adding this field to your address format.'), $country_name));    
                    }
                    if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 1 && $country_iso == 'GB' && !in_array('State:name', $addressForm)) {
                        $this->adminDisplayWarning('<strong>[Advanced VAT Manager]</strong> '.sprintf($this->l('The country %s does not have the [State:name] field in the address format. To avoid errors in the validation of this field, edit the country by adding this field to your address format.'), $country_name));    
                    }                
                }
            }
        }
        return true;
    }
    
    /**
     * AdvancedVatManager::sendEmail()
     * Sends email
     * @param mixed $mode (0 -> VIES failed, 1 -> VIES success, 2 -> Customer with empty VAT, 3 -> Customer with invalid VAT, 4 -> Request customer validation, 5 -> Customer with duplicated VAT. 6 -> Customer with invalid company. 7 -> Customer with empty company. 8 -> Customer with VAT not validated due API system failure.
     * @param mixed $customer (address object)
     * @return
     */
    public function sendEmail($mode, $customer)
    {
        $destinations = array();
        if ($mode == 0 || $mode == 1) {
            $destinations = explode(',', Configuration::get('ADVANCEDVATMANAGER_EMAIL_ADDRESS'));    
        }
        else {
            $destinations[] = $customer['email'];    
        }
        if (!empty($destinations)) {
            $link = new Link();
            $vat_iso_code =  Tools::substr($customer['vat'], 0, 2);
            $vat_number =  Tools::substr($customer['vat'], 2);
            $template_vars = array(
                '{customer_id}' => $customer['id_customer'],
                '{id_address}' => $customer['id_address'],
                '{order_reference}' => isset($customer['order_reference'])?$customer['order_reference']:'',
                '{customer_name}' => $customer['firstname'].' '. $customer['lastname'],
                '{vat}' => $customer['vat'],
                '{country}' => Country::getNameById($this->context->language->id, $customer['id_country']),
                '{date}' => date("Y-m-d H:i:s"),
                '{customer_account}' => $link->getPageLink('my-account'),
                '{customer_account_addr}' => $link->getPageLink('addresses')
            );
            
            if ($mode == 0) {
                $template_name = 'api_failed';
                $subject = Mail::l('VIES or GOV.UK API sytem validation failed - Sent from Advanced VAT Manager module.');    
            }
            else if ($mode == 1) {
                $template_name = 'api_success';
                $subject = Mail::l('VIES or GOV.UK API sytem validation success - Sent from Advanced VAT Manager module.');    
            }
            else if ($mode == 2) {
                $template_name = 'customer_empty_vat';
                $subject = Mail::l('You need to update your address and insert a valid VAT number - Mail from').' '.$this->context->shop->name;    
            }
            else if ($mode == 3) {
                $template_name = 'customer_invalid_vat';
                $subject = Mail::l('Invalid VAT number. You need to validate new VAT number - Mail from').' '.$this->context->shop->name;        
            }
            else if ($mode == 4) {
                $template_name = 'request_validation'; 
                $subject = Mail::l('Validation request. You need to validate VAT number - Mail from').' '.$this->context->shop->name;   
            }
            else if ($mode == 5) {
                $template_name = 'customer_duplicated'; 
                $subject = Mail::l('Duplicated VAT number - Mail from').' '.$this->context->shop->name;   
            }
            else if ($mode == 6) {
                $template_name = 'customer_invalid_company'; 
                $subject = Mail::l('Invalid company name - Mail from').' '.$this->context->shop->name;   
            }
            else if ($mode == 7) {
                $template_name = 'customer_empty_company'; 
                $subject = Mail::l('Empty company name - Mail from').' '.$this->context->shop->name;   
            }
            else if ($mode == 8) {
                $template_name = 'customer_pending_validation'; 
                $subject = Mail::l('VAT number is pending of validation - Mail from').' '.$this->context->shop->name;   
            }
            
            $shop_email = Configuration::get('PS_SHOP_EMAIL');
            $shop_name = Configuration::get('PS_SHOP_NAME');
            $template_dir = _PS_MODULE_DIR_.'advancedvatmanager/mails/';
            foreach ($destinations as $destination) {
                if (Mail::Send(
                        (int)$this->context->language->id, 
                        $template_name, 
                        $subject, 
                        $template_vars, 
                        $destination, 
                        null, 
                        $shop_email, 
                        $shop_name, 
                        null, 
                        null, 
                        $template_dir, 
                        false, 
                        (int)$this->context->shop->id, 
                        null, 
                        null) 
                    !== false) {
                        return true;
                } 
                else {
                    PrestaShopLoggerCore::addLog('Advanced VAT Manager - '.$this->l('There is an error while was sending email. The email has not been sent.'));
                    return false;
                } 
            }
        } 
        else {
            PrestaShopLoggerCore::addLog('Advanced VAT Manager - '.$this->l('The email could not be sent because any email address has been configured.'));
            return false;
        }
    }
    
    /**
     * AdvancedVatManager::checkNotAllowCheckoutByBrexit()
     * Check if purchase is not allowed in Brexit regulations
     * @return false or string message
     */
    public function checkNotAllowCheckoutByBrexit()
    {
        if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS') != 0) {
            if ($this->context->cookie->__isset('customer_vat_data')) {
                $customer_vat_data = json_decode($this->context->cookie->__get('customer_vat_data'), true);
                $currency_amount = $this->getGBPcurrencyAmount().(method_exists('Currency', 'getSymbol')?$this->context->currency->getSymbol():$this->context->currency->sign);
                if ($customer_vat_data['brexit_customer']) {
                    if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS') == 1?$this->context->cart->getOrderTotal(false,Cart::ONLY_PRODUCTS) > $this->getGBPcurrencyAmount():$this->context->cart->getOrderTotal(false,Cart::ONLY_PRODUCTS) < $this->getGBPcurrencyAmount()) { 
                        return sprintf($this->l('This store does not allow purchases %s %s(tax not incl) to the United Kingdom. You cannot continue with the purchase process'), Configuration::get('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS') == 1?$this->l('over'):$this->l('below'), $currency_amount);
                    }
                }
            }   
        }
        return false;
    }
    
    public function getCustomerAddressWithError($id_address)
    {
        if (Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION') == 1 && Configuration::get('ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION') == 1 && $this->context->controller instanceof FrontController) {
            $checkoutWarning = [];
            $address = new Address($id_address);
            if ($id_address) {
                $advancedvatmanager = Module::getInstanceByName('advancedvatmanager');
                if (Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required') { 
                    if (CustomersVAT::checkCustomerVATInvalid($this->context->customer->id, $address->id) ) {
                        $checkoutWarning = ['id_address' => $address->id, 'exception' => $address->vat_number?sprintf($this->controller_msg['VAT_NOT_VALID_REQUIRED_WITH_NUMBER'], $this->l('VAT number'), $address->vat_number):$this->controller_msg['VAT_EMPTY_REQUIRED']];
                    }
                    else if (CustomersVAT::checkCustomerAddressWithoutValidation($this->context->customer->id, $address->id)) {
                        $checkoutWarning = ['id_address' => $address->id, 'exception' => $address->vat_number?sprintf($this->controller_msg['VAT_NOT_VALIDATED_REQUIRED_WITH_NUMBER'],$this->l('VAT number'), $address->vat_number):$this->controller_msg['VAT_EMPTY_REQUIRED']];  
                    }    
                }
                
                if (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1 && $address->vat_number != '') {
                    if (CustomersVAT::checkCustomerCompanyInvalid($this->context->customer->id, $address->id)) {
                        $checkoutWarning = ['id_address' => $address->id, 'exception' => sprintf($this->controller_msg['COMPANY_INVALID_WITH_NAME'], $this->l('Company name'), $address->company)];     
                    }
                    else if (CustomersVAT::checkCustomerCompanyWithoutValidation($this->context->customer->id, $address->id)) {
                        $checkoutWarning = ['id_address' => $address->id, 'exception' => sprintf($this->controller_msg['COMPANY_NOT_VALIDATED_REQUIRED_WITH_NAME'],$this->l('Company name'), $address->company)]; 
                    }
                }
                
                if (Configuration::get('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL') == 0 && CustomersVAT::checkVATWithSystemFails($address->id)) {
                    $checkoutWarning = ['id_address' => $address->id, 'exception' => $this->controller_msg['SYSTEM_FAILS']];
                }
            }
            if (!empty($checkoutWarning)) {
                return $checkoutWarning;
            }
        }
        return false;
    }
    
    /**
     * AdvancedVatManager::checkNotAllowCheckoutByVATandCompanyValidation()
     * Checks if address is not validated with VAT number or company name to not allow checkout process
     * @return bool (false if checkout is allowed) array $message displaying error message disabling checkout process.
     */
    public function checkNotAllowCheckoutByVATandCompanyValidation()
    {
        $message = array();
        if (Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION') == 1 && Configuration::get('ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION') == 1 && $this->context->controller instanceof FrontController) {
            $customer_id = $this->context->cart->id_customer;
            $id_address_delivery = $this->context->cart->id_address_delivery;
            $id_address_invoice = $this->context->cart->id_address_invoice;
            $address_delivery = new Address($id_address_delivery);
            $address_invoice = new Address($id_address_invoice);
            
            if (!$customer_id) {
                return false;
            } 
            if (Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required') { 
                if (CustomersVAT::checkCustomerVATInvalid($customer_id, $id_address_delivery)) {
                    $message[] = $address_delivery->vat_number?sprintf($this->controller_msg['VAT_NOT_VALID_REQUIRED_WITH_NUMBER'], $this->l('VAT number'), $address_delivery->vat_number):$this->controller_msg['VAT_EMPTY_REQUIRED'];  
                }
                else if (CustomersVAT::checkCustomerAddressWithoutValidation($customer_id, $id_address_delivery)) {
                    $message[] = $address_delivery->vat_number?sprintf($this->controller_msg['VAT_NOT_VALIDATED_REQUIRED_WITH_NUMBER'], $this->l('VAT number'), $address_delivery->vat_number):$this->controller_msg['VAT_EMPTY_REQUIRED'];   
                }               
            }
            if ($id_address_invoice != $id_address_delivery && (Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required' && (Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 1 && $address_invoice->company != '') || Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 0)) { 
                if (CustomersVAT::checkCustomerVATInvalid($customer_id, $id_address_invoice)) {
                    $message[] = $address_invoice->vat_number?sprintf($this->controller_msg['VAT_NOT_VALIDATED_REQUIRED_WITH_NUMBER'], $this->l('VAT number'), $address_invoice->vat_number):$this->controller_msg['VAT_EMPTY_REQUIRED'];    
                }  
                else if (CustomersVAT::checkCustomerAddressWithoutValidation($customer_id, $id_address_invoice)) {
                    $message[] = $address_invoice->vat_number?sprintf($this->controller_msg['VAT_NOT_VALIDATED_REQUIRED_WITH_NUMBER'], $this->l('VAT number'), $address_invoice->vat_number):$this->controller_msg['VAT_EMPTY_REQUIRED'];    
                }
            }
            
            if (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1 && ($address_delivery->vat_number != '' || $address_invoice->vat_number != '')) {
                if (CustomersVAT::checkCustomerCompanyInvalid($customer_id, $id_address_delivery)) {
                    $message[] = sprintf($this->controller_msg[$address_delivery->company?'COMPANY_INVALID_WITH_ALIAS':'COMPANY_EMPTY_WITH_ALIAS'], $address_delivery->alias, $address_invoice->company);  
                }
                else if ($id_address_invoice != $id_address_delivery && CustomersVAT::checkCustomerCompanyInvalid($customer_id, $id_address_invoice)) {
                    $message[] = sprintf($this->controller_msg[$address_invoice->company?'COMPANY_INVALID_WITH_ALIAS':'COMPANY_EMPTY_WITH_ALIAS'], $address_delivery->alias, $address_invoice->company); 
                } 
                
                if (CustomersVAT::checkCustomerCompanyWithoutValidation($customer_id, $id_address_delivery)) {
                    $message[] = sprintf($this->controller_msg[$address_delivery->company?'COMPANY_NOT_VALIDATED_REQUIRED_WITH_NAME':'COMPANY_EMPTY_WITH_ALIAS'], $address_delivery->alias, $address_delivery->company);   
                }
                else if ($id_address_invoice != $id_address_delivery && CustomersVAT::checkCustomerCompanyWithoutValidation($customer_id, $id_address_invoice)) {
                    $message[] = sprintf($this->controller_msg[$address_invoice->company?'COMPANY_NOT_VALIDATED_REQUIRED_WITH_NAME':'COMPANY_EMPTY_WITH_ALIAS'], $address_invoice->alias, $address_invoice->company);    
                }
            }
            
            if (Configuration::get('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL') == 0) {
                if (CustomersVAT::checkVATWithSystemFails($id_address_delivery) || CustomersVAT::checkVATWithSystemFails($id_address_invoice)) {
                    $message[] = $this->controller_msg['SYSTEM_FAILS'];  
                }  
            }            
            if (!empty($message)) {
                return array_unique($message);
            }
        }
        return false;
    }
    
    /**
     * AdvancedVatManager::checkModuleOrderStateExists()
     * Checks if the order state created by the module exists 
     * @return
     */
    public function checkModuleOrderStateExists()
    {
        $sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.'order_state WHERE `module_name` = "advancedvatmanager"'.(version_compare(_PS_VERSION_, '1.7.0.0', '>=')?' AND `deleted` = 0;':'');
        $total_states = Db::getInstance()->getValue($sql);
        return (bool)$total_states;
    }
    
    /**
     * AdvancedVatManager::getModuleOrderState()
     * Gets the id of the order state created by the module
     * @return array
     */
    public function getModuleOrderState()
    {
        $sql = 'SELECT `id_order_state` FROM '._DB_PREFIX_.'order_state WHERE `module_name` = "advancedvatmanager"'.(version_compare(_PS_VERSION_, '1.7.0.0', '>=')?' AND `deleted` = 0;':'');
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Create custom OrderState used for payment
     *
     * @param string $configurationKey Configuration key used to store OrderState identifier
     * @param array $nameByLangIsoCode An array of name for all languages, default is en
     * @param string $color Color of the label
     * @param bool $isLogable consider the associated order as validated
     * @param bool $isPaid set the order as paid
     * @param bool $isInvoice allow a customer to download and view PDF versions of his/her invoices
     * @param bool $isShipped set the order as shipped
     * @param bool $isDelivery show delivery PDF
     * @param bool $isPdfDelivery attach delivery slip PDF to email
     * @param bool $isPdfInvoice attach invoice PDF to email
     * @param bool $isSendEmail send an email to the customer when his/her order status has changed
     * @param string $template Only letters, numbers and underscores are allowed. Email template for both .html and .txt
     * @param bool $isHidden hide this status in all customer orders
     * @param bool $isUnremovable Disallow delete action for this OrderState
     * @param bool $isDeleted Set OrderState deleted
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function createOrderState(
        $configurationKey,
        $name,
        $color,
        $isLogable = false,
        $isPaid = false,
        $isInvoice = false,
        $isShipped = false,
        $isDelivery = false,
        $isPdfDelivery = false,
        $isPdfInvoice = false,
        $isSendEmail = false,
        $template = '',
        $isHidden = false,
        $isUnremovable = true,
        $isDeleted = false
    ) {
        $tabNameByLangId = [];

        foreach (Language::getLanguages(false) as $language) {
            $tabNameByLangId[(int) $language['id_lang']] = $this->_translate($name, $language['iso_code']);    
        } 

        $orderState = new OrderState();
        $orderState->module_name = $this->name;
        $orderState->name = $tabNameByLangId;
        $orderState->color = $color;
        $orderState->logable = $isLogable;
        $orderState->paid = $isPaid;
        $orderState->invoice = $isInvoice;
        $orderState->shipped = $isShipped;
        $orderState->delivery = $isDelivery;
        $orderState->pdf_delivery = $isPdfDelivery;
        $orderState->pdf_invoice = $isPdfInvoice;
        $orderState->send_email = $isSendEmail;
        $orderState->hidden = $isHidden;
        $orderState->unremovable = $isUnremovable;
        $orderState->template = $template;
        $orderState->deleted = $isDeleted;
        $result = (bool) $orderState->add();

        if (false === $result) {
            $this->_errors[] = sprintf(
                'Failed to create OrderState %s',
                $configurationKey
            );

            return false;
        }

        $result = (bool) Configuration::updateGlobalValue($configurationKey, (int) $orderState->id);

        if (false === $result) {
            $this->_errors[] = sprintf(
                'Failed to save OrderState %s to Configuration',
                $configurationKey
            );

            return false;
        }

        $orderStateImgPath = $this->getLocalPath() . 'views/img/orderstate/' . $configurationKey . '.png';

        if (false === (bool) Tools::file_exists_cache($orderStateImgPath)) {
            $this->_errors[] = sprintf(
                'Failed to find icon file of OrderState %s',
                $configurationKey
            );

            return false;
        }

        if (false === (bool) Tools::copy($orderStateImgPath, _PS_ORDER_STATE_IMG_DIR_ . $orderState->id . '.gif')) {
            $this->_errors[] = sprintf(
                'Failed to copy icon of OrderState %s',
                $configurationKey
            );

            return false;
        }

        return true;
    }

    /**
     * Delete custom OrderState used for payment
     * We mark them as deleted to not break passed Orders
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    private function deleteOrderState()
    {
        $result = true;
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            if ($this->checkModuleOrderStateExists()) {
                foreach ($this->getModuleOrderState() as $row) {
                    $orderState = new OrderState($row['id_order_state']);
                    $result = $result && (bool)$orderState->delete();
                }
            }
        }
        else {
            $orderStateCollection = new PrestaShopCollection('OrderState');
            $orderStateCollection->where('module_name', '=', $this->name);
            /** @var OrderState[] $orderStates */
            $orderStates = $orderStateCollection->getAll();
    
            foreach ($orderStates as $orderState) {
                $orderState->deleted = true;
                $result = $result && (bool) $orderState->save();
            }
        }
        return $result;
    }
    
    /**
     * AdvancedVatManager::createOrderStatus()
     * Creates order states from this module
     * @return
     */
    public function createOrderStatus()
    {
        if (!$this->checkModuleOrderStateExists()) {
            $this->createOrderState('VAT_PENDING_VALIDATION_OS','VAT number pending validation','#ff5100');
            $this->createOrderState('VAT_VALID_OS','VAT number validated','#00af05');  
        }   
    }
    
    /**
     * AdvancedVatManager::hookActionOpcValidatePayment()
     * Compatibility purposes for module One Page Checkout by Presteamshop
     * @return
     */
     public function hookActionOpcValidatePayment()
     {
        $message = $this->checkNotAllowCheckoutByBrexit();
        if ($message) {
            return array($this->checkNotAllowCheckoutByBrexit());
        }
        $message = $this->checkNotAllowCheckoutByVATandCompanyValidation();
        if ($message) {
            return $message;
        }
     }
     
    /**
     * AdvancedVatManager::hookActionStepBeforePaymentOPC()
     * Compatibility purposes for module One Page Checkout by Presteamshop
     * @return
     */
     public function hookActionStepBeforePaymentOPC()
     {
        if ($this->hookActionNotAllowCheckout) {
            return array('errors' => array($this->context->controller->errors));
        }
     }
     
    /**
     * AdvancedVatManager::hookActionOpcValidateVatNumber()
     * Compatibility purposes for module One Page Checkout by Presteamshop
     * @return
     */
     public function hookActionOpcValidateVatNumber($params)
     {
     }
     
    /**
     * AdvancedVatManager::hookActionOpcCustomerAddressFormFields()
     * Compatibility purposes for module One Page Checkout by Presteamshop
     * @return
     */
    public function hookActionOpcCustomerAddressFormFields($params)
    {
        if (Module::isEnabled($this->name) && version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION') == 1) {
            $formFields = $params['formFields'];
            if (!ValidationEngine::skipVATFieldBycountry($formFields['id_country']->getValue())) {
                $label = Configuration::get('ADVANCEDVATMANAGER_FIELD_LABEL', $this->context->language->id);
                $legend = Configuration::get('ADVANCEDVATMANAGER_FIELD_LEGEND', $this->context->language->id);
                if ($label) {
                    $formFields['vat_number']->setLabel($label);    
                }
                if ($legend) {
                    $formFields['vat_number']->addAvailableValue('comment', $legend); 
                }
                $formFields['company']->addAvailableValue('comment', (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION')?$this->controller_msg['COMPANY_VALIDATION_COMMENT']:'').' '.(Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY')?$this->controller_msg['COMPANY_DISPLAY_COMMENT']:''));
            }
        }
    }
              
    /**
     * AdvancedVatManager::hookActionDispatcher()
     * Hook for actions after reload page
     * @return
     */
    public function hookActionDispatcher()
    {
        // Only for Prestashop 1.7 versions
        if (Module::isEnabled($this->name) && version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if (Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION') == 1 && Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WARNING_MESSAGE_ADDRESS') == 1 && $this->context->controller instanceof FrontController ) {
                // Display messages when an address has no VAT validated or address is not validated yet, only for countries for validation.
                if (Tools::getValue('controller') == 'addresses') {
                    if ($addresses = CustomersVAT::getCustomerAddressHasVATInvalid($this->context->cookie->id_customer)) {
                        foreach ($addresses as $idaddress) {
                            $address = new Address($idaddress['id_address']);
                            $alias = $address->alias;
                            $this->context->controller->warning[] = sprintf($this->controller_msg[(Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required'?'VAT_NOT_VALID_REQUIRED_WITH_ALIAS':'VAT_NOT_VALID_OPTIONAL_WITH_ALIAS')],$alias);    
                        }   
                    }
                    
                    if ($addresses = CustomersVAT::getCustomerAddressWithoutValidation($this->context->cookie->id_customer)) {
                        foreach ($addresses as $idaddress) {
                            $address = new Address($idaddress['id_address']);
                            $alias = $address->alias;
                            if ((Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required' && (Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 1 && $address->company != '') || Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 0) || (Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional' && $address->vat_number != '')) { 
                                $this->context->controller->warning[] = $address->vat_number?sprintf($this->controller_msg[(Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional'?'VAT_NOT_VALIDATED_OPTIONAL_WITH_ALIAS':'VAT_NOT_VALIDATED_REQUIRED_WITH_ALIAS')],$alias):$this->controller_msg[(Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional'?'VAT_EMPTY_OPTIONAL':'VAT_EMPTY_REQUIRED')];    
                            }    
                        }     
                    }
                    
                    if (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1) {
                        if ($addresses = CustomersVAT::getCustomerAddressHasCompanyInvalid($this->context->cookie->id_customer)) {
                            foreach ($addresses as $idaddress) {
                                $alias = $address->alias;
                                $company = $address->company;
                                $this->context->controller->warning[] = sprintf($this->controller_msg[$company?'COMPANY_INVALID_WITH_ALIAS':'COMPANY_EMPTY_WITH_ALIAS'],$alias, $company);       
                            }
                        }
                        if ($addresses = CustomersVAT::getCustomerAddressHasCompanyWithoutValidation($this->context->cookie->id_customer)) {
                            foreach ($addresses as $idaddress) {
                                $address = new Address($idaddress['id_address']);
                                $alias = $address->alias;
                                $company = $address->company;
                                $this->context->controller->warning[] = sprintf($this->controller_msg[$company?'COMPANY_NOT_VALIDATED_REQUIRED_WITH_NAME':'COMPANY_EMPTY_WITH_ALIAS'],$alias, $company);    
                            }
                        }  
                    }                   
                    
                    // Check if the system fails and skip validation is disabled
                    if (Configuration::get('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL') == 0 && $addresses = CustomersVAT::getCustomerAddressesWithSystemFails($this->context->cookie->id_customer)) {
                       foreach ($addresses as $idaddress) {
                            $address = new Address($idaddress['id_address']);
                            $alias = $address->alias;
                            $vat = $address->vat_number;
                            $this->context->controller->warning[] = sprintf($this->controller_msg['SYSTEM_FAILS_WITH_ALIAS'],$alias, $vat);    
                        }  
                    }
                }
                else if (Tools::getValue('controller') == 'address' && !Tools::isSubmit('submitAddress')) {
                    $address = new Address(Tools::getValue('id_address'));
                    
                    if (CustomersVAT::checkCustomerVATInvalid($this->context->cookie->id_customer, $address->id)) {
                        $this->context->controller->warning[] = $this->controller_msg[(Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required'?'VAT_NOT_VALID_REQUIRED':'VAT_NOT_VALID_OPTIONAL')];   
                    }
                    
                    if ((Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required' && (Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 1 && $address->company != '') || Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 0) || (Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional' && $address->vat_number != '')) {
                        if (CustomersVAT::checkCustomerAddressWithoutValidation($this->context->cookie->id_customer, $address->id)) {
                            $this->context->controller->warning[] = $address->vat_number?$this->controller_msg[(Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional'?'VAT_NOT_VALIDATED_OPTIONAL':'VAT_NOT_VALIDATED_REQUIRED')]:$this->controller_msg[(Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional'?'VAT_EMPTY_OPTIONAL':'VAT_EMPTY_REQUIRED')];   
                        }
                    }
                    
                    if (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1) {
                        if (CustomersVAT::checkCustomerCompanyInvalid($this->context->cookie->id_customer, $address->id)) {
                            $this->context->controller->warning[] = $this->controller_msg['COMPANY_INVALID_REQUIRED'];      
                        }
                        if (CustomersVAT::checkCustomerCompanyWithoutValidation($this->context->cookie->id_customer, $address->id) && !empty($address->vat_number)) {
                            $this->context->controller->warning[] = $this->controller_msg['COMPANY_NOT_VALIDATED_REQUIRED'];           
                        }   
                    }
                    
                    // Check if the system fails and skip validation is disabled
                    if (Configuration::get('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL') == 0 && CustomersVAT::checkCustomerAddressWithSystemFails($this->context->cookie->id_customer, $address->id)) {
                        $this->context->controller->warning[] = $this->controller_msg['SYSTEM_FAILS'];
                    }
                }
            }
        }
    }
    
    /**
     * AdvancedVatManager::hookDisplayInvoiceLegalFreeText()
     * 
     * @param mixed $params
     * @return
     */
    public function hookDisplayInvoiceLegalFreeText($params)
    {
        $order = $params['order'];
        $reverseCharge = '';
        $legal_text = '';
        if ($vatOrder = CustomersOrders::getVATOrderInDB($order->id)) {
            $brexit = $vatOrder['brexit'];
            if ((bool)$vatOrder['notax']) {
                // Inserts Legal free text in invoice
                if ((bool)$vatOrder['notax']) {
                    $legal_text = Configuration::get('ADVANCEDVATMANAGER_LEGAL_TEXT', (int)$this->context->language->id, null, (int)$order->id_shop);
                }
                // Inserts Legal free text in invoice
                if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 1 && $brexit && Configuration::get('ADVANCEDVATMANAGER_BREXIT_VATEXEMPT_LESSTHAN135GBP') == 1) {
                    $reverseCharge = PHP_EOL.$this->l('Reverse charge: customer to account for VAT to HMRC');    
                }   
            }
            return Configuration::get('PS_INVOICE_LEGAL_FREE_TEXT', (int)$this->context->language->id, null, (int)$order->id_shop).$reverseCharge.PHP_EOL.$legal_text; 
        }
    }
    
    /**
     * AdvancedVatManager::hookActionPDFInvoiceRender()
     * 
     * @param mixed $params
     * @return
     */
    public function hookActionPDFInvoiceRender($params)
    {
        $order = new Order($params['order_invoice_list'][0]->id_order);
        $note = Configuration::get('ADVANCEDVATMANAGER_INVOICE_NOTE', (int)$this->context->language->id, null, (int)$order->id_shop);
        if ($vatOrder =  CustomersOrders::getVATOrderInDB($order->id)) {
            if ((bool)$vatOrder['notax']) {
                // Inserts Note in invoice
                if ($note) {   
                    $params['order_invoice_list'][0]->note = $note.PHP_EOL.$params['order_invoice_list'][0]->note;
                }
            }         
        }
    }
    
    /**
     * AdvancedVatManager::hookActionValidateCustomerAddressForm()
     * Hook for customer account validation ONLY FOR PS 1.7
     * @param mixed $params
     * @return
     */
    public function hookActionValidateCustomerAddressForm($params)
    {
        if (Module::isEnabled($this->name) && version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if (Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1) {
                $form = $params['form'];
                $vat = $form->getField('vat_number')?$form->getField('vat_number')->getValue():null;
                $ve = new ValidationEngine($vat);
                $id_customer = isset($this->context->customer->id) && $this->context->customer->id?$this->context->customer->id:Tools::getValue('id_customer');  
                $ve->VATValidationProcess(Tools::getValue('id_country'), $id_customer, Tools::getValue('id_address'), $form->getField('company')->getValue());
                if (ValidationEngine::$skip_validation_process === false) {
                    if (ValidationEngine::getVATValidation() && Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1 && ValidationEngine::$company_valid == 0) {
                        $form->getField('company')->addError($ve->getMessage());
                        return false;  
                    }
                    else if (!ValidationEngine::getVATValidation()) {
                        $form->getField('vat_number')->addError($ve->getMessage());
                        return false;        
                    }
                }
            }
        }
    }
    
    /**
     * AdvancedVatManager::hookActionObjectAddressDeleteAfter()
     * Hook executed after deleting address
     * @param mixed $params
     * @return
     */
    public function hookActionObjectDeleteAfter($params)
    {
        if (Module::isEnabled($this->name)) {
            $object = $params['object'];
            if ($object instanceof Address) {
                if (CustomersVAT::checkCustomerAddressExists($object->id_customer, $object->id)) {
                    CustomersVAT::deleteByIDAddress($object->id);
                    // Manage Customer groups
                    ValidationEngine::manageCustomerGroups($object->id_country, $object->id_customer);
                }
            }
            // Deletes customer from module table
            if ($object instanceof Customer) {
                $customer = new CustomersExemption($object->id);
                if (Validate::isLoadedObject($customer)) {
                    $customer->delete();    
                }
            }
        }        
    }

    /**
     * AdvancedVatManager::hookActionObjectAddressAddAfter()
     * Hook executed after adding address
     * @param mixed $params
     * @return
     */
    public function hookActionObjectAddAfter($params)
    {
        if (Module::isEnabled($this->name)) {
            if (Configuration::get('ADVANCEDVATMANAGER_ADMINVALIDATION')== 1 || Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1) {
                $object = $params['object'];
                if ($object instanceof Address) {                   
                    $cv = new CustomersVAT();
                    // If validation process was inited
                    if (ValidationEngine::$init_validation_process && ValidationEngine::$skip_validation_process === false) {
                        $cv->addCustomersVAT(ValidationEngine::getVat(), $object->id_customer, $object->id, (int)ValidationEngine::getVATValidation(), ValidationEngine::$company_valid, Configuration::get('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT')?ValidationEngine::getRegisteredCompanyName():null, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                    }
                    // If validation process was not inited
                    else if (!ValidationEngine::$init_validation_process) {
                        // Ajax process not saving static mehtods
                        $ve = new ValidationEngine($object->vat_number);
                        $result = $ve->VATValidationProcess($object->id_country, $object->id_customer, $object->id, $object->company);
                        if (ValidationEngine::$skip_validation_process === false) {
                            return $cv->addCustomersVAT(ValidationEngine::getVat(), $object->id_customer, $object->id, (int)$result, ValidationEngine::$company_valid, Configuration::get('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT')?ValidationEngine::getRegisteredCompanyName():null, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                        }
                        else {
                            if (CustomersVAT::checkCustomerAddressExists($object->id_customer, $object->id)) {
                                CustomersVAT::deleteByIDAddress($object->id);
                                // Manage Customer groups
                                ValidationEngine::manageCustomerGroups($object->id_country, $object->id_customer);
                            }
                        } 
                    } 
                }                   
            } 
        }   
    }

    /**
     * AdvancedVatManager::hookActionObjectAddressUpdateAfter()
     * Hook executed after updating address
     * @param mixed $params
     * @return
     */
    public function hookActionObjectUpdateAfter($params)
    {
        if (Module::isEnabled($this->name)) {
            if (Configuration::get('ADVANCEDVATMANAGER_ADMINVALIDATION')== 1 || Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1) {
                $object = $params['object'];
                if ($object instanceof Address) {
                    $cv = new CustomersVAT();
                    // Delete address
                    if ($object->deleted == 1) {
                        if (CustomersVAT::checkCustomerAddressExists($object->id_customer, $object->id)) {
                            CustomersVAT::deleteByIDAddress($object->id);
                            // Manage Customer groups
                            ValidationEngine::manageCustomerGroups($object->id_country, $object->id_customer);
                        }  
                    }
                    // If validation process was inited
                    else if (ValidationEngine::$init_validation_process && ValidationEngine::$skip_validation_process === false) {
                        $cv->addCustomersVAT(ValidationEngine::getVat(), $object->id_customer, $object->id, (int)ValidationEngine::getVATValidation(), ValidationEngine::$company_valid, Configuration::get('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT')?ValidationEngine::getRegisteredCompanyName():null, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                    }
                    // If address is skipped and should be deleted from table
                    else if (ValidationEngine::$init_validation_process && ValidationEngine::$skip_validation_process) {
                        CustomersVAT::deleteByIDAddress($object->id);    
                    }
                    // If validation process was not inited
                    else if (!ValidationEngine::$init_validation_process) {
                        // Ajax process not saving static mehtods
                        $ve = new ValidationEngine($object->vat_number);
                        $result = $ve->VATValidationProcess($object->id_country, $object->id_customer, $object->id, $object->company);
                        if (ValidationEngine::$skip_validation_process === false) {
                            return $cv->addCustomersVAT(ValidationEngine::getVat(), $object->id_customer, $object->id, (int)$result, ValidationEngine::$company_valid, Configuration::get('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT')?ValidationEngine::getRegisteredCompanyName():null, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                        }
                        else {
                            if (CustomersVAT::checkCustomerAddressExists($object->id_customer, $object->id)) {
                                CustomersVAT::deleteByIDAddress($object->id);
                                // Manage Customer groups
                                ValidationEngine::manageCustomerGroups($object->id_country, $object->id_customer);
                            }
                        } 
                    } 
                }                   
            } 
        } 
    }
    
    /**
     * AdvancedVatManager::hookcreateAccountForm()
     * ONLY FOR PS 1.6
     * @return
     */
    public function hookcreateAccountForm()
    {
        if (Module::isEnabled($this->name) && version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $this->hookActionFrontControllerSetMedia();
        }
    }
             
    /**
     * AdvancedVatManager::hookActionBeforeSubmitAccount()
     * Hook for actions before submitting customer account ONLY FOR PS 1.6
     * @return
     */
    public function hookActionBeforeSubmitAccount()
    {  
        if (Module::isEnabled($this->name) && version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $ve = new ValidationEngine(Tools::getValue('vat_number'));
            $ve->VATValidationProcess(Tools::getValue('id_country'), $this->context->customer->id, Tools::getValue('id_address'), Tools::getValue('company'));
            if (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1 && ValidationEngine::getVATValidation() && ValidationEngine::$company_valid == 0) {
                $this->context->controller->errors[] = $ve->getMessage();  
                return false;  
            }
            else if (!ValidationEngine::getVATValidation() && ValidationEngine::$skip_validation_process === false) {
                $this->context->controller->errors[] = $ve->getMessage();  
                return false;        
            }
        }
        return true;
    }
    
    /**
     * AdvancedVatManager::hookTaxManager()
     * Hook to manage tax
     * @params array $args
     * @return
     */
    public function hookTaxManager($args)
    {
        $class_file = _PS_MODULE_DIR_.'/'.$this->name.'/'.$this->tax_manager_class.'.php';

        if (!isset($this->tax_manager_class) || !file_exists($class_file)) {
            die(sprintf(Tools::displayError('Incorrect Tax Manager class [%s]'), $this->tax_manager_class));
        }

        require_once($class_file);

        if (!class_exists($this->tax_manager_class)) {
            die(sprintf(Tools::displayError('Tax Manager class not found [%s]'), $this->tax_manager_class));
        }

        $class = $this->tax_manager_class;
        if (call_user_func(array($class, 'isAvailableForThisAddress'), $args['address'])) {
            return new $class();
        }
        return false;
    }
    
    /**
     * AdvancedVatManager::hookActionCartSave()
     * @params array $params
     * @return
     */
    public function hookActionCartSave($params)
    {
        if ($this->context->controller instanceof FrontController) {
            if (Module::isEnabled($this->name) && $this->context->customer->isLogged() && Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 1) {
                if (is_object ($params['cart'])) {
                    $cart = new Cart($params['cart']->id);
                    if ($this->context->cart && $cart->nbProducts()) {
                        // Save cookie of total product prices in cart.
                        $this->context->cookie->__set('cart_total', $cart->getOrderTotal(false,Cart::ONLY_PRODUCTS));
                    }
                    else {
                        $this->context->cookie->__unset('cart_total');
                    }
                }
            }
            else {
                $this->context->cookie->__unset('cart_total');    
            }
        }
    }
    
    /**
     * AdvancedVatManager::hookActionValidateOrder()
     * @params array $params
     * @return
     */
    public function hookActionValidateOrder($params)
    {
        if (Module::isEnabled($this->name) && Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1) {
            $order = $params['order'];
            $customerOrders = new CustomersOrders(); 
            $local_country_iso = Country::getIsoById(Configuration::get('ADVANCEDVATMANAGER_LOCAL_COUNTRY'));
            
            // Save orders if Brexit or Tax exempt conditions.            
            if (ValidationEngine::$notax_customer || ValidationEngine::$brexit_customer) {
                $customerOrders->addCustomersOrders($order, $order->id_customer, ValidationEngine::$notax_customer, $order->invoice_number, ValidationEngine::$brexit_customer);    
            }
            // Save order state with VAT validated status
            if (Configuration::get('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS') && ValidationEngine::$customer_with_vat_valid && CustomersVAT::checkVATWithSystemFails(ValidationEngine::$id_address_used) === false) {
                if ($order->current_state != (int)Configuration::get('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS')) {
                    $order->setCurrentState((int)Configuration::get('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS'));    
                }
            }
            // Send email to customer when API system fails and skip validation option is enabled
            if (Configuration::get('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL') && CustomersVAT::checkVATWithSystemFails(ValidationEngine::$id_address_used) && Configuration::get('ADVANCEDVATMANAGER_SEND_CUSTOMER_EMAIL_API_FAIL_ORDER')) {
                $customer = new Customer($order->id_customer);
                $address = new Address(ValidationEngine::$id_address_used); 
                $customer_info = array('id_customer' => $customer->id, 'firstname' => $customer->firstname, 'lastname' => $customer->lastname, 'id_country' => $address->id_country, 'vat' => $address->vat_number, 'email' => $customer->email, 'id_address' => $address->id, 'order_reference' => $order->reference); 
                $this->sendEmail(8, $customer_info);     
            }
        }
    }
    
    public function hookActionOrderHistoryAddAfter($params)
    {
        if ($this->context->employee === null) {
            // Change order state when system fails and send email to customer.
            if (Configuration::get('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL') && CustomersVAT::checkVATWithSystemFails(ValidationEngine::$id_address_used)) {
                if (Configuration::get('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS') != 0) {  
                    $history = $params['order_history'];
                    $order = new Order($history->id_order);
                    if (Validate::isLoadedObject($order->getCurrentOrderState())) {
                        if ($order->current_state != (int)Configuration::get('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS')) {
                            $order->setCurrentState((int)Configuration::get('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS'));    
                        }   
                    }             
                }
            } 
        }  
    }
    
    /**
     * AdvancedVatManager::hookActionSetInvoice()
     * @params array $params
     * @return
     */
    public function hookActionSetInvoice($params)
    {       
        if (Module::isEnabled($this->name) && Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1) {
            // Update invoice number when it is created.
            if (CustomersOrders::checkCustomerOrderExists((int)$params['Order']->id_customer, (int)$params['Order']->id)) {
                Db::getInstance()->update('advancedvatmanager_orders', array('invoice' => $params['Order']->invoice_number), $where = 'id_order = '.(int)$params['Order']->id);      
            }       
        }        
    }
    /**
     * AdvancedVatManager::hookActionCheckoutRender()
     * @params array $params
     * @return
     */
     
    public function hookActionCheckoutRender($params)
    {
        if (Module::isEnabled($this->name) && version_compare(_PS_VERSION_, '1.7.8.0', '>=') && !$this->opc_presteamshop_enabled) {
            $checkNotAllowCheckout = $this->checkNotAllowCheckoutByBrexit();
            if($checkNotAllowCheckout) {
                $this->context->controller->errors[] = $checkNotAllowCheckout;
                // Remove checkout steps to avoid make an order from customer.
                return $params['checkoutProcess']->setSteps(array());
            }
        }
    }

    /**
     * AdvancedVatManager::hookActionAdminControllerSetMedia()
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     * @return
     */
    public function hookActionAdminControllerSetMedia()
    {
        if (Module::isEnabled($this->name)) {
            if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                $this->context->controller->addCSS($this->_path . 'views/css/tab_icon.css');    
            }
            // Ajax validation in AdminAddress from PS 1.7.7.0
            if (Configuration::get('ADVANCEDVATMANAGER_ADMINVALIDATION')== 1 && Tools::getValue('controller') == 'AdminAddresses' && version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                Media::addJsDef(array(
                    'admin_ajax_url_advancedvatmanager' => $this->context->link->getAdminLink('AdminVATValidation'),
                    'id_address' => Tools::getValue('id_address'),
                    'error_msg' => $this->l('Error checking VAT number.'),
                    )
                );
                $this->context->controller->addJS($this->_path.'/views/js/admin/vatverificator_adminaddresses.js');
            }
            if (in_array(Tools::getValue('controller'), $this->controllers) || Tools::getValue('module_name') == $this->name) {                        
                // jQuery and plugins
                $this->context->controller->addJquery();
            }
        }
    }
    
    /**
     * AdvancedVatManager::hookActionFrontControllerSetMedia()
     * Add the CSS & JavaScript files you want to be loaded in the FO.
     * @return
     */
    public function hookActionFrontControllerSetMedia()
    {
        if (Module::isEnabled($this->name) && Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1) {
            Media::addJsDef(array(
                'ps16' => version_compare(_PS_VERSION_, '1.7.0.0', '<'),
                'controller' => $this->context->controller->php_self?$this->context->controller->php_self:Tools::getValue('controller'),
            ));
            if ($this->context->controller instanceof AddressController || $this->context->controller instanceof AddressesController || $this->context->controller instanceof AuthController || $this->context->controller instanceof OrderOpcController || $this->context->controller instanceof OrderController) {
                Media::addJsDef(array(
                    'ajax_url_addressVAT' => $this->context->link->getModuleLink('advancedvatmanager','AddressVAT'),
                    'vat_field' => configuration::get('ADVANCEDVATMANAGER_VATFIELD'),
                    'display_with_company' => (bool)Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY'),
                    'company_validation' => (bool)Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION'),
                    'label' => Configuration::get('ADVANCEDVATMANAGER_FIELD_LABEL', $this->context->language->id),
                    'legend' => Configuration::get('ADVANCEDVATMANAGER_FIELD_LEGEND', $this->context->language->id),
                    'company_legend' => (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1?$this->l('The company name must match the one registered in the VIES or GOV.UK system in case of inserting a VAT number.'):null).(Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 1?$this->l('If you fill in this field, the VAT field will be displayed.'):null),
                    'vat_number_label' => $this->l('VAT number'),
                    'NOTALLOW_CHECKOUT_WITHOUT_VALIDATION' => Configuration::get('ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION'),
                    'opc_presteamshop_enabled' => $this->opc_presteamshop_enabled
                    )
                );
                
                $this->context->controller->addJS($this->_path.'/views/js/addressManagement.js');  
                $this->context->controller->addCSS($this->_path.'/views/css/front.css');
            }
            if($this->context->controller instanceof ProductController && ValidationEngine::$notax_customer) {
                Media::addJsDef(array(
                    'price_label' => $this->l('Tax exempt'),
                ));
                $this->context->controller->addJS($this->_path.'/views/js/hook/displayProductPriceBlock/fix_price_label.js');
            }    
        }
    }
    
    public function hookDisplayProductPriceBlock($params)
    {
        if (Module::isEnabled($this->name) && Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1) {
            //Display product price labels without taxes and custom text block
            if(ValidationEngine::$notax_customer) {
                if ($params['type'] == 'after_price') {
                    return $this->display(__FILE__, 'views/templates/hook/displayProductPriceBlock.tpl');
                }
            }
        }
    }
    
    public function hookDisplayExpressCheckout()
    {
        if (Module::isEnabled($this->name) && Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1 && Configuration::get('ADVANCEDVATMANAGER_BREXIT_NOTALLOWORDERS') != 0) {
            if (ValidationEngine::$brexit_customer) {
                $this->context->smarty->assign(array(
                    'currency_amount' => $this->getGBPcurrencyAmount().(method_exists('Currency', 'getSymbol')?$this->context->currency->getSymbol():$this->context->currency->sign)    
                ));               
                return $this->display(__FILE__, 'views/templates/hook/displayExpressCheckout.tpl');    
            }
        }
    }
    
    public function hookDisplayBackOfficeHeader()
    {  
        if (Module::isEnabled($this->name) && Configuration::get('ADVANCEDVATMANAGER_ADMINVALIDATION')== 1 || Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION')== 1) {
            $this->checkAddressFormat();
            $this->checkBrexitCurrency();
            // Checks if scan process has been performed first time.
            if (CustomersVAT::getCustomerAddresses() && CustomersVAT::isEmptyVATList()) {
                $this->adminDisplayWarning('<strong>[Advanced VAT Manager]</strong> '.$this->l('The VAT number scan has not yet been carried out in the section [customers -> VAT Management]. Scanning is required to verify and validate VAT numbers which are already in the database before the module was installed.'));    
            }
        }
    }
    
    public function hookDisplayFooter()
    {
        if ($this->context->controller instanceof AddressController || $this->context->controller instanceof AuthController || $this->context->controller instanceof OrderOpcController || $this->context->controller instanceof OrderController) {
            if ( version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                $this->context->controller->addJS($this->_path.'/views/js/addressManagement.js');  
            }
        }
    }
}
