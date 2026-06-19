<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 * @package   cdc_googletagmanager
 *
 * Project Name : Google Tag Manager Enhanced Ecommerce (UA) Tracking
 * Created By  : Comptoir du Code
 * Created On  : 2016-06-02
 * Support : https://addons.prestashop.com/contact-community.php?id_product=23806
 *
 * Based on Google recommendations
 *  - https://developers.google.com/tag-manager/devguide
 *  - https://developers.google.com/tag-manager/enhanced-ecommerce
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_CDCGTM_DIR_'))
    define('_CDCGTM_DIR_', dirname(__FILE__));

include_once(_CDCGTM_DIR_.'/classes/CdcGtmOrderLog.php');
include_once(_CDCGTM_DIR_.'/classes/CdcGtmDataLayer.php');
include_once(_CDCGTM_DIR_.'/services/CdcTools.php');
include_once(_CDCGTM_DIR_.'/services/PrestashopUtils.php');
include_once(_CDCGTM_DIR_.'/classes/DataLayer.php');

define('_CDCGTM_PRICE_DECIMAL_', 2);

// fix json old PHP version
if (version_compare(phpversion(), '5.4.0', '<')) {
    define('JSON_UNESCAPED_SLASHES',      64);   // Since PHP 5.4.0
    define('JSON_PRETTY_PRINT',           128);  // Since PHP 5.4.0
}

class cdc_googletagmanager extends Module
{
    public $author_address;

    // <config>
    public $order_id_field = "id"; // [id,reference]
    // </config>

	const PAGE_PRODUCT = 'product';
    const PAGE_HOME = 'index';
    const PAGE_SEARCH = 'search';
    const PAGE_CATEGORY = 'category';
    const PAGE_CART = 'order';
    const PAGE_CART_17 = 'cart';
    const PAGE_OPC = 'orderopc';
    const PAGE_OPC_SUPERCHECKOUT = 'supercheckout';
    const PAGE_PAYMENT = 'payment';
    const PAGE_ORDERCONFIRMATION = 'orderconfirmation';
    public $controller = null;

    private $debug_enabled = null;
    private $debug_stack = array();
    public $events_to_debug = null;
    public $shop_id = null;

    // maximum products added in datalayer in a given page (0 to disable)
    public $max_products_datalayer = null;

    private $async_user_info = false;
    private $userid_enable = null;

    private $hookDisplayAfterTitleTagTriggered = false;
    private $gtm_enable = null;
    private $gtm_id = null;
    private $dataLayer = null;
    private $preDataLayer = null; // datalayer sent before the main datalayer
    private $opc_enabled = false;
    private $ee_generated = false;
    private $displayOrderConfirmation = false;
    private $displayOrderConfirmationOrderObj = null;
    private $rootCategory = null;
    public $display_variant_id;
    public $customer_informations = false;
    public $remarketing_enable = null;
    public $remarketing_generated = false;
    public $product_identifier = null;
    public $product_id_format = null;
    public $category_hierarchy = false;
    public $display_wholesale_price = false;
    public $main_price_with_tax = false;
    public $display_product_price_tax_detail = true;
    public $display_product_stock = false;
    public $variant_id_separator = '-';
    public $product_name_field = null;
    public $product_id_field = null;
    public $category_name_field = null;
    public $data_language = null;
    public $enable_shipping_event;
    public $enable_payment_event;

    // order validation page
    private $isOrderValidationPage;
    public $orderValidationDetails;

    // event sent when datalayer ready
    private $eventDatalayerReady = "datalayer_ready";

    // mod test to check if hooks are present
    private $mod_test_hooks_enabled = false;

    // configuration variables Google Customer Reviews
    private $greviews_config = array(
        'GCR_ENABLE', 'GCR_BADGE_CODE', 'GCR_MERCHANT_ID',
        'GCR_ORDER_CODE', 'GCR_DELIVERY_DAYS', 'GCR_BADGE_POSITION');


    // configs are set in construct
    public $conf = array();

	/* Initialiaze Default Settings */
	public function __construct()
	{
        $this->name = 'cdc_googletagmanager';
        $this->tab = 'analytics_stats';
        $this->author = 'Comptoir du Code';
        $this->version = '5.7.3';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '8.2.99');
        $this->module_key = '5d5819d54f9765c68428b1cf8b357338';
        $this->author_address = '0xb1c5eebc8be3c1aa53e8953238bb8f04c55b287f';

        // configs
        $this->conf = array(
            // available id types
            'id_types' => array('id', 'reference', 'ean13', 'upc'),

            // available display for variant id
            'display-variant-id' => array(
                'when-set' => $this->l('Display variant ID only when set'),
                'always' => $this->l('Always display variant ID (0 if no variant)'),
                'never' => $this->l('Never display variant ID')
            ),

            'customer-informations-choices' => array(
                'never' => $this->l('Never display customer information'),
                'purchase' => $this->l('Display customer information on order confirmation'),
                'always' => $this->l('Display customer information whenever it\'s possible')
            ),
        );

        parent::__construct();

        $this->displayName = $this->l('CdC Google Tag Manager Enhanced Ecommerce');
        $this->description = $this->l('Integration of Google Tag Manager - Enhanced Ecommerce (GA4 + UA) Tracking and Google Customer reviews');

        // load config
        $this->gtm_id = self::getConfigValue('GTMID');
        $this->gtm_enable = self::getConfigValue('ENABLE');
        $this->remarketing_enable = self::getConfigValue('REMARKETING_ENABLE');
        $this->product_identifier = self::getConfigValue('REMARKETING_PRODUCTID');
        $this->display_variant_id = self::getConfigValue('DISPLAY_VARIANT_ID');
        $this->variant_id_separator = self::getConfigValue('VARIANT_ID_SEPARATOR', '-', true);
        $this->max_products_datalayer = self::getConfigValue('MAX_CAT_ITEMS');
        $this->category_hierarchy = (bool) self::getConfigValue('CATEGORY_HIERARCHY', false);
        $this->display_wholesale_price = (bool) self::getConfigValue('DISPLAY_WHOLESALE_PRICE', false);
        $this->main_price_with_tax = (bool) self::getConfigValue('MAIN_PRICE_WITH_TAX', false);
        $this->display_product_price_tax_detail = (bool) self::getConfigValue('DISPLAY_PROD_TAX_DETAIL', true);
        $this->display_product_stock = (bool) self::getConfigValue('DISPLAY_PRODUCT_STOCK', false);
        $this->product_id_field = self::getConfigValue('PRODUCT_ID_FIELD');
        $this->product_id_format = self::getConfigValue('PRODUCT_ID_FORMAT');
        $this->product_name_field = self::getConfigValue('PRODUCT_NAME_FIELD');
        $this->category_name_field = self::getConfigValue('CATEGORY_NAME_FIELD');
        $this->data_language = (int) self::getConfigValue('DATA_LANGUAGE');
        $this->enable_shipping_event = (int) self::getConfigValue('SHIPPING_EVENT', 1);
        $this->enable_payment_event = (int) self::getConfigValue('PAYMENT_EVENT', 1);
        $this->events_to_debug = self::getConfigValue('DATALAYER_DEBUG_EVENTS');

        $this->userid_enable = self::getConfigValue('ENABLE_USERID');
        if($this->userid_enable) {
            $this->async_user_info = (int) self::getConfigValue('ASYNC_USER_INFO');
        }
        $this->customer_informations = self::getConfigValue('CUSTOMER_INFORMATIONS');

        // retro compatibility
        if(!$this->customer_informations) {
            $this->customer_informations = 'purchase';
        } elseif($this->customer_informations == 1) {
            $this->customer_informations = 'always';
        }


        $this->controller = $this->getController();
        $this->dataLayer = new Gtm_DataLayer($this);
        $this->preDataLayer = null;

        $this->shop_id = (int)Context::getContext()->shop->id;
        //Configuration::deleteByName(self::getConfigName('CUSTOM_HOOKS'));

        // init debugger
        $this->debugManager();
        if($this->debug_enabled) {
            $this->addDebug("v".$this->version." | GTM ". ($this->gtm_enable ? 'on' : 'off')." | ID: ".$this->gtm_id);
        }

        // debug hook installation
        if(Tools::getIsset('cdcgtm_debug_hooks')) {
            $this->debugHooksMod();
        }

        // test if order validation page (for performance, only one call / page)
        $this->isOrderValidationPage = $this->isOrderValidationPage();
	}

	/* Installing module */
	public function install()
	{
        $result = parent::install()
            && CdcGtmOrderLog::createTable()
            && CdcGtmDataLayer::createTable()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayAfterTitleTag')
            && $this->registerHook('displayAfterBodyOpeningTag')
            && $this->registerHook('displayBeforeBodyClosingTag')
            && $this->registerHook('actionObjectOrderDetailUpdateAfter')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionOrderStatusUpdate')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('actionCustomerAccountAdd')
            && $this->registerHook('actionAuthentication')
            && $this->registerHook('displayProductPriceBlock');

            // set default configuration
            $this->setDefaultConfiguration();

        // version specific
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $result &= $this->addTab("AdminCdcGoogletagmanagerOrders", "GTM Orders", "AdminParentStats");
            $result &= $this->addTab("AdminCdcGoogletagmanagerDatalayer", "GTM Datalayer", "AdminParentStats");
        } else {
            $result &= $this->addTab("AdminCdcGoogletagmanagerOrders", "GTM Orders", "AdminAdvancedParameters");
            $result &= $this->addTab("AdminCdcGoogletagmanagerDatalayer", "GTM Datalayer", "AdminAdvancedParameters");
        }

        // reset custom hooks check
        Configuration::deleteByName(self::getConfigName('CUSTOM_HOOKS'));

        return $result;
	}

	/* Un installing module */
	public function uninstall()
	{
        $result = parent::uninstall()
            && CdcGtmOrderLog::deleteTable()
            && $this->unregisterHook('displayAfterTitleTag')
            && $this->unregisterHook('displayAfterBodyOpeningTag')
            && $this->unregisterHook('displayBeforeBodyClosingTag')
            && $this->unregisterHook('header')
            && $this->unregisterHook('actionObjectOrderDetailUpdateAfter')
            && $this->unregisterHook('displayBackOfficeHeader')
            && $this->unregisterHook('actionOrderStatusUpdate')
            && $this->unregisterHook('displayOrderConfirmation')
            && $this->unregisterHook('displayProductPriceBlock')
            && $this->deleteTab("AdminCdcGoogletagmanagerOrders")
            && $this->deleteTab("AdminCdcGoogletagmanagerDatalayer")
            && $this->deleteConfiguration();

        return $result;
	}

    /**
     * Delete module configuration
     */
    public function deleteConfiguration() {
        return Configuration::deleteByName(self::getConfigName('CUSTOM_HOOKS'))
            && Configuration::deleteByName(self::getConfigName('REFUNDS_Q'))
            && Configuration::deleteByName(self::getConfigName('ENABLE'))
            && Configuration::deleteByName(self::getConfigName('ENABLE_RESEND'))
            && Configuration::deleteByName(self::getConfigName('DATALAYER_AUTO_CREATE'))
            && Configuration::deleteByName(self::getConfigName('LOAD_GTM_SCRIPT'))
            && Configuration::deleteByName(self::getConfigName('RESEND_DAYS'))
            && Configuration::deleteByName(self::getConfigName('RESEND_EXCLUDE_PAYMENT'))
            && Configuration::deleteByName(self::getConfigName('DATALAYER_DEBUG_EVENTS'))
            && Configuration::deleteByName(self::getConfigName('ENABLE_USERID'))
            && Configuration::deleteByName(self::getConfigName('CUSTOMER_INFORMATIONS'))
            && Configuration::deleteByName(self::getConfigName('ENABLE_GUESTID'))
            && Configuration::deleteByName(self::getConfigName('ASYNC_USER_INFO'))
            && Configuration::deleteByName(self::getConfigName('SHIPPING_EVENT'))
            && Configuration::deleteByName(self::getConfigName('PAYMENT_EVENT'))
            && Configuration::deleteByName(self::getConfigName('DISPLAY_VARIANT_ID'))
            && Configuration::deleteByName(self::getConfigName('VARIANT_ID_SEPARATOR'))
            && Configuration::deleteByName(self::getConfigName('CATEGORY_HIERARCHY'))
            && Configuration::deleteByName(self::getConfigName('DISPLAY_WHOLESALE_PRICE'))
            && Configuration::deleteByName(self::getConfigName('MAIN_PRICE_WITH_TAX'))
            && Configuration::deleteByName(self::getConfigName('DISPLAY_PROD_TAX_DETAIL'))
            && Configuration::deleteByName(self::getConfigName('DISPLAY_PRODUCT_STOCK'))
            && Configuration::deleteByName(self::getConfigName('PRODUCT_NAME_FIELD'))
            && Configuration::deleteByName(self::getConfigName('PRODUCT_ID_FIELD'))
            && Configuration::deleteByName(self::getConfigName('PRODUCT_ID_FORMAT'))
            && Configuration::deleteByName(self::getConfigName('DATA_LANGUAGE'))
            && Configuration::deleteByName(self::getConfigName('CATEGORY_NAME_FIELD'));
    }

    /**
     * Set default values to configuration
     * If force = false : do not override current values
     * If force = true : override current values
     */
    public function setDefaultConfiguration($force = false) {
        $updatedValues = 0;
        $defaultConfig = array(
            'ENABLE' => 1,
            'ENABLE_RESEND' => 0,
            'DATALAYER_AUTO_CREATE' => 0,
            'LOAD_GTM_SCRIPT' => 1,
            'RESEND_DAYS' => 5,
            'RESEND_EXCLUDE_PAYMENT' => 'test',
            'DATALAYER_DEBUG_EVENTS' => 'purchase,refund',
            'ENABLE_USERID' => 0,
            'ENABLE_GUESTID' => 0,
            'CUSTOMER_INFORMATIONS' => 'purchase',
            'ASYNC_USER_INFO' => 0,
            'SHIPPING_EVENT' => 1,
            'PAYMENT_EVENT' => 1,
            'MAX_CAT_ITEMS' => 30,
            'DISPLAY_PROD_TAX_DETAIL' => 1
        );

        foreach($defaultConfig as $key => $defaultValue) {
            if($force || (Configuration::get($this->getConfigName($key)) === false)) {
                Configuration::updateValue( self::getConfigName($key), $defaultValue);
                $updatedValues++;
            }
        }

        return $updatedValues;
    }

    /**
     * add tab
     */
    public function addTab($className, $name, $parentClassName) {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();
        $tab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l($name);
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName($parentClassName);
        return $tab->add();
    }

    /**
     * Delete tab
     */
    protected function deleteTab($className) {
        $id_tab = (int)Tab::getIdFromClassName($className);
        $allTableDeleted = true;
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $allTableDeleted = $tab->delete();
        } else {
            return false;
        }
        return $allTableDeleted;
    }


	/* Handle module settings in admin panel */
	public function getContent()
	{
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addCss($this->_path.'views/css/admin-theme.css');
            $this->context->controller->addCss($this->_path.'views/css/bootsrap.extend.css');
            $this->context->controller->addCss($this->_path.'views/css/font-awesome.min.css');
            $this->context->controller->addJS("https://code.jquery.com/jquery-1.12.4.min.js");
            $this->context->controller->addJS("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js");
        }

		$output = null;

        // set global smarty variable
        $this->smarty->assign(array(
            'form_action' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'gtm_order_logs_url' => $this->context->link->getAdminLink('AdminCdcGoogletagmanagerOrders'),
            'gtm_datalayer_logs_url' => $this->context->link->getAdminLink('AdminCdcGoogletagmanagerDatalayer')
        ));

    	/**
    	 * Manage form submit
    	 */
        if (Tools::isSubmit('submit'.$this->name))
        {
            // General settings
            $this->saveValueToConfiguration("GTMID");
            $this->gtm_id = self::getConfigValue('GTMID');
            $this->saveValueToConfiguration("ENABLE");
            $this->gtm_enable = (int) self::getConfigValue('ENABLE');
            $this->saveValueToConfiguration("OVERRIDE_GTM_TAG");
            $this->saveValueToConfiguration("OVERRIDE_GTM_TAG_NOSCRIPT", true);
            $this->saveValueToConfiguration("LOAD_GTM_SCRIPT");
            $this->saveValueToConfiguration("ENABLE_RESEND");
            $this->saveValueToConfiguration("DATALAYER_AUTO_CREATE");
            $this->saveValueToConfiguration("RESEND_DAYS");
            $this->saveValueToConfiguration("RESEND_EXCLUDE_PAYMENT");
            $this->saveValueToConfiguration("DATALAYER_DEBUG_EVENTS");
            $this->saveValueToConfiguration("MAX_CAT_ITEMS");
            $this->saveValueToConfiguration("DISPLAY_VARIANT_ID");
            $this->saveValueToConfiguration("VARIANT_ID_SEPARATOR");
            $this->saveValueToConfiguration("CATEGORY_HIERARCHY");
            $this->saveValueToConfiguration("DISPLAY_WHOLESALE_PRICE");
            $this->saveValueToConfiguration("MAIN_PRICE_WITH_TAX");
            $this->saveValueToConfiguration("DISPLAY_PROD_TAX_DETAIL");
            $this->saveValueToConfiguration("DISPLAY_PRODUCT_STOCK");
            $this->saveValueToConfiguration("PRODUCT_NAME_FIELD");
            $this->saveValueToConfiguration("PRODUCT_ID_FIELD");
            $this->saveValueToConfiguration("PRODUCT_ID_FORMAT");
            $this->saveValueToConfiguration("CATEGORY_NAME_FIELD");
            $this->saveValueToConfiguration("ENABLE_USERID");
            $this->saveValueToConfiguration("ENABLE_GUESTID");
            $this->saveValueToConfiguration("CUSTOMER_INFORMATIONS");
            $this->saveValueToConfiguration("DATA_LANGUAGE");
            $this->saveValueToConfiguration("ASYNC_USER_INFO");
            $this->saveValueToConfiguration("SHIPPING_EVENT");
            $this->saveValueToConfiguration("PAYMENT_EVENT");

            // remarketing config
            $this->saveValueToConfiguration("REMARKETING_ENABLE");
            $this->remarketing_enable = (int) self::getConfigValue('REMARKETING_ENABLE');
            $this->saveValueToConfiguration("REMARKETING_PRODUCTID");

            // Google customer reviews settings
            foreach ($this->greviews_config as $greviews_config_var) {
                $this->saveValueToConfiguration($greviews_config_var);
            }

            $output .= $this->displayConfirmation($this->l($this->displayName.' module settings have been Updated'));
        }


        // install custom hooks
        elseif(Tools::getIsset('install_hooks')) {
            if($this->installCustomHooks()) {
                $output .= $this->displayConfirmation($this->l('Hooks are correctly installed!'));
            } else {
                $output .= $this->displayError($this->l('Hooks are not correctly installed :-('));
                $this->smarty->assign(array(
                    'troubleshooting' => true
                ));
            }
        }

        // user force installed hooks (done manually)
        elseif(Tools::getIsset('force_installed_hooks')) {
            Configuration::updateValue( self::getConfigName('CUSTOM_HOOKS'), 1);
        }

        // user force installed hooks (done manually)
        elseif(Tools::getIsset('force_check_hooks')) {
            Configuration::updateValue( self::getConfigName('CUSTOM_HOOKS'), 0);
        }

        // empty resend queue
        elseif(Tools::getIsset('empty_resend_queue')) {
            Configuration::updateValue( self::getConfigName('ORDER_RESEND_Q'), '');
            $output .= $this->displayConfirmation($this->l('Order resend queue is now empty'));
        }

        // empty refund queue
        elseif(Tools::getIsset('empty_refund_queue')) {
            Configuration::updateValue( self::getConfigName('REFUNDS_Q'), '');
            $output .= $this->displayConfirmation($this->l('Refund queue is now empty'));
        }

        // if variable custom hook false, redirect to install page
        if(!self::getConfigValue('CUSTOM_HOOKS')) {
            // user uses backoffice with https without https enabled on the website
            if(Tools::usingSecureMode() && !Configuration::get('PS_SSL_ENABLED')) {
                // redirect to non https BO page
                $urlConfigModule = $this->context->link->getAdminLink('AdminModules') . '&configure=' . urlencode($this->name);
                // if link is relative, convert to absolute
                if(Tools::substr( $urlConfigModule, 0, 4 ) != "http") {
                    $urlConfigModule = $this->context->link->getBaseLink(null, false) . basename(_PS_ADMIN_DIR_) . '/' . $urlConfigModule;
                }
                Tools::redirectAdmin($urlConfigModule);
            }

            // url to check if hooks are presents
            $check_url = $this->context->link->getPageLink('index',null,null,null,false,null,true);
            $check_url .= '?cdcgtm_debug_hooks&nocache='.time();

            $check_url_b = $this->context->link->getPageLink('contact',null,null,null,false,null,true);
            $check_url_b .= '?cdcgtm_debug_hooks&nocache='.time();

            $this->smarty->assign(array(
                'multishop' => Shop::isFeatureActive(),
                'check_url' => $check_url,
                'check_url_b' => $check_url_b,
                'smarty_compile' => Configuration::get('PS_SMARTY_FORCE_COMPILE')
            ));
            return $output.$this->display(__FILE__, 'views/templates/admin/install.tpl');
        }


    	return $output.$this->displayForm();
	}


	/**
	 * Get a post / get value and save it to Configuration
	 */
	protected function saveValueToConfiguration($name, $html = false) {
		Configuration::updateValue( self::getConfigName($name), trim( (string)Tools::getValue(self::getConfigName($name)) ), $html);
	}

    public static function getConfigName($name) {
        return 'CDC_GTM_'.Tools::strtoupper($name);
    }

    public static function getConfigValue($name, $default = null, $defaultIfEmpty = false) {
	    $value = Configuration::get(self::getConfigName($name));
	    if($value === false
            || $value === null && $default !== null
            || $defaultIfEmpty && empty($value) && $default !== null) {
	        $value = $default;
        }
        return $value;
    }

    /**
     * Display the configuration form
     */
    public function displayForm()
    {
        $tpl_vars = array(
            'CDC_GTM_ENABLE' => (int) self::getConfigValue('ENABLE'),
            'CDC_GTM_GTMID' => self::getConfigValue('GTMID'),
            'CDC_GTM_OVERRIDE_GTM_TAG' => self::getConfigValue('OVERRIDE_GTM_TAG'),
            'CDC_GTM_OVERRIDE_GTM_TAG_NOSCRIPT' => self::getConfigValue('OVERRIDE_GTM_TAG_NOSCRIPT'),
            'CDC_GTM_LOAD_GTM_SCRIPT' => (int) self::getConfigValue('LOAD_GTM_SCRIPT', 1),
            'CDC_GTM_ENABLE_RESEND' => (int) self::getConfigValue('ENABLE_RESEND'),
            'CDC_GTM_DATALAYER_AUTO_CREATE' => (int) self::getConfigValue('DATALAYER_AUTO_CREATE'),
            'CDC_GTM_RESEND_DAYS' => (int) self::getConfigValue('RESEND_DAYS'),
            'CDC_GTM_RESEND_EXCLUDE_PAYMENT' => self::getConfigValue('RESEND_EXCLUDE_PAYMENT'),
            'CDC_GTM_DATALAYER_DEBUG_EVENTS' => self::getConfigValue('DATALAYER_DEBUG_EVENTS'),
            'CDC_GTM_MAX_CAT_ITEMS' => (int) self::getConfigValue('MAX_CAT_ITEMS'),
            'CDC_GTM_DISPLAY_VARIANT_ID_LIST' => $this->conf['display-variant-id'],
            'CDC_GTM_DISPLAY_VARIANT_ID' => self::getConfigValue('DISPLAY_VARIANT_ID'),
            'CDC_GTM_VARIANT_ID_SEPARATOR' => self::getConfigValue('VARIANT_ID_SEPARATOR'),
            'CDC_GTM_CATEGORY_HIERARCHY' => self::getConfigValue('CATEGORY_HIERARCHY'),
            'CDC_GTM_DISPLAY_WHOLESALE_PRICE' => self::getConfigValue('DISPLAY_WHOLESALE_PRICE'),
            'CDC_GTM_MAIN_PRICE_WITH_TAX' => self::getConfigValue('MAIN_PRICE_WITH_TAX'),
            'CDC_GTM_DISPLAY_PROD_TAX_DETAIL' => self::getConfigValue('DISPLAY_PROD_TAX_DETAIL'),
            'CDC_GTM_DISPLAY_PRODUCT_STOCK' => self::getConfigValue('DISPLAY_PRODUCT_STOCK'),
            'CDC_GTM_PRODUCT_NAME_FIELD' => self::getConfigValue('PRODUCT_NAME_FIELD'),
            'CDC_GTM_PRODUCT_ID_FIELD' => self::getConfigValue('PRODUCT_ID_FIELD'),
            'CDC_GTM_PRODUCT_ID_FORMAT' => self::getConfigValue('PRODUCT_ID_FORMAT'),
            'CDC_GTM_CATEGORY_NAME_FIELD' => self::getConfigValue('CATEGORY_NAME_FIELD'),
            'CDC_GTM_DATA_LANGUAGES' => $this->getLanguages(),
            'CDC_GTM_DATA_LANGUAGE' => self::getConfigValue('DATA_LANGUAGE'),
            'CDC_GTM_ENABLE_USERID' => (int) self::getConfigValue('ENABLE_USERID'),
            'CDC_GTM_ENABLE_GUESTID' => (int) self::getConfigValue('ENABLE_GUESTID'),
            'CDC_GTM_CUSTOMER_INFORMATIONS_ID_LIST' => $this->conf['customer-informations-choices'],
            'CDC_GTM_CUSTOMER_INFORMATIONS' => $this->customer_informations,
            'CDC_GTM_ASYNC_USER_INFO' => (int) self::getConfigValue('ASYNC_USER_INFO'),
            'CDC_GTM_SHIPPING_EVENT' => (int) self::getConfigValue('SHIPPING_EVENT', 1),
            'CDC_GTM_PAYMENT_EVENT' => (int) self::getConfigValue('PAYMENT_EVENT', 1),
            'CDC_GTM_REMARKETING_ENABLE' => self::getConfigValue('REMARKETING_ENABLE'),
            'CDC_GTM_REMARKETING_PRODUCTID' => self::getConfigValue('REMARKETING_PRODUCTID'),
            'CDC_PS_VERSION' => _PS_VERSION_,
            'CDC_GTM_ID_TYPES' => $this->conf['id_types'],
            'ORDER_RESEND_Q' => Configuration::get('CDC_GTM_ORDER_RESEND_Q', null, null, $this->shop_id),
            'REFUNDS_Q' => Configuration::get('CDC_GTM_REFUNDS_Q', null, null, $this->shop_id)
        );

        // G customer reviews configuration
        foreach ($this->greviews_config as $greviews_config_var) {
            $tpl_vars[self::getConfigName($greviews_config_var)] = self::getConfigValue($greviews_config_var);
        }

        // get payment names
        $tpl_vars['paymentNames'] = $this->getPaymentNamesOfLastOrders();


        $this->smarty->assign($tpl_vars);
        return $this->display(__FILE__, 'views/templates/admin/config.tpl');
    }


    /***************************************************************************
     * HOOK
     */

    public function hookDisplayHeader($params)
    {
        // Ajax Cart
        if($this->gtm_enable && !empty($this->gtm_id)) {
            $ajaxCartEnabled = Configuration::get('PS_BLOCK_CART_AJAX', 0);
            $this->context->smarty->assign(array(
                    'ajaxcartEnabled' => $ajaxCartEnabled,
                    'cdcGtmApi' => Context::getContext()->link->getModuleLink('cdc_googletagmanager', 'async', array(), null, null, null, true),
                    'ajaxShippingEvent' => $this->enable_shipping_event,
                    'ajaxPaymentEvent' => $this->enable_payment_event
            ));

            if($ajaxCartEnabled) {
                $this->context->controller->addJS($this->_path.'views/js/ajaxdatalayer.js');
            }
        }
    }

    /**
     * hook immediately after <title></title>
     * add gtm tag
     */
    public function hookDisplayAfterTitleTag($params)
    {
        if($this->hookDisplayAfterTitleTagTriggered) {
            return;
        }

        // test if hook is enabled
        if ($this->mod_test_hooks_enabled) {
            $this->displayHookPresent('displayAfterTitleTag');
        }

        // Google tag manager main Tag
        if ($this->gtm_enable && !empty($this->gtm_id)) {
            $this->generateEnhancedEcommerce();

            if ($this->remarketing_enable) {
                $this->generateRemarketingParameters();
            }

            $this->generateGtmTag();

            $this->context->smarty->assign(array('load_gtm_script' => self::getConfigValue('LOAD_GTM_SCRIPT', 1)));

            $this->hookDisplayAfterTitleTagTriggered = true;
            return $this->display(__FILE__, 'gtm_tag.tpl');
        }
    }


    /**
     * keep retrocompatibility with previous hook name
     * @deprecated 5.0
     * @param $params
     */
    public function hookDisplayAfterTitle($params)
    {
        return $this->hookDisplayAfterTitleTag($params);
    }


    /**
     * hook after opening body
     */
    public function hookDisplayAfterBodyOpeningTag($params)
    {
        // test if hook is enabled
        if($this->mod_test_hooks_enabled) {
            $this->displayHookPresent('displayAfterBodyOpeningTag');
        }

        $content = "";

        // Google tag manager no script
        if($this->gtm_enable && !empty($this->gtm_id)) {
            $this->context->smarty->assign(array('gtm_id' => $this->gtm_id));
            $content .= $this->display(__FILE__, 'gtm_tag_noscript.tpl');
        }

        // return content
        if(!empty($content)) {
            return $content;
        }
    }


    /**
     * hook before </body> closing tag
     */
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        // test if hook is enabled
        if($this->mod_test_hooks_enabled) {
            $this->displayHookPresent('displayBeforeBodyClosingTag');
        }

        $content = "";

        // add JS with datalayer for OPC
        if($this->gtm_enable && !empty($this->gtm_id)) {
            if($this->opc_enabled) {
                if($this->generateEnhancedEcommerce()) {
                    $this->generateGtmTag();
                }
                $content .= $this->display(__FILE__, 'opc.tpl');
            }
        }


        // Google Customer Reviews
        $enable_greviews = (int) self::getConfigValue('GCR_ENABLE');
        if($enable_greviews) {

            // Google Customer Reviews Order confirmation
            $enable_greviews_order = (int) self::getConfigValue('GCR_ORDER_CODE');
            if($enable_greviews_order && $this->isOrderValidationPage) {
                $this->greviewsGenerateOrderConfirmationCode();
                $content .= $this->display(__FILE__, 'greviews_order_confirmation_code.tpl');
            }

            else {
                // Google Customer Reviews Badge
                $enable_greviews_code = (int) self::getConfigValue('GCR_BADGE_CODE');
                if($enable_greviews_code) {
                    $tpl_vars = array();
                    foreach ($this->greviews_config as $greviews_config_var) {
                        $tpl_vars[$greviews_config_var] = self::getConfigValue($greviews_config_var);
                    }
                    $this->context->smarty->assign($tpl_vars);
                    $content .= $this->display(__FILE__, 'greviews_badge_tag.tpl');
                }
            }
        }


        // display debug
        if($this->debug_enabled) {
            // add POST and GET variables
            $this->addDebug("POST");
            $this->addDebug(print_r($_POST, true));
            $this->addDebug("GET");
            $this->addDebug(print_r($_GET, true));
            
            $content .= $this->displayDebug();
        }

         if(!empty($content)) {
            return $content;
        }
    }

    /**
     * Hook for partial refunds
     */
    public function hookActionObjectOrderDetailUpdateAfter($params)
    {
        $orderDetail = $params['object'];

         // if no quantity refunded, no action
        if (empty($orderDetail->product_quantity_refunded)) {
            return;
        }

        $order_id = $orderDetail->id_order;
        $gtmOrderLog = CdcGtmOrderLog::getByOrderId($order_id);
        if(Validate::isLoadedObject($gtmOrderLog)) {
            if(CdcGtmOrderLog::isRefundable($order_id, $gtmOrderLog->id_shop)) {
                $this->addRefund($order_id, $gtmOrderLog->id_shop, $orderDetail->id_order_detail);
            }
        }
    }


    /**
     * HOOK order state change
     * Used for full refund
     * @param type $params : array containing order details
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $order_id = $params['id_order'];
        $newOrderStatus = $params['newOrderStatus']->id;

        $refund_state_id = array(
            Configuration::get('PS_OS_CANCELED'),
            Configuration::get('PS_OS_REFUND'),
            Configuration::get('PS_OS_ERROR')
        );

        $gtmOrderLog = CdcGtmOrderLog::getByOrderId($order_id);
        if(Validate::isLoadedObject($gtmOrderLog)) {
            if(in_array($newOrderStatus, $refund_state_id)) {
                if(CdcGtmOrderLog::isRefundable($order_id, $gtmOrderLog->id_shop)) {
                    $this->addRefund($order_id, $gtmOrderLog->id_shop);
                }
            }
        }
    }


    /**
     * Display GTM tag in backoffice for :
     *  - order refund
     */
    public function hookDisplayBackOfficeHeader()
    {
        // Google tag manager
        if($this->gtm_enable && !empty($this->gtm_id)) {
            // disable async loading. useless in backoffice, no full cache
            $this->async_user_info = 0;

            // recreate missing order logs
            $this->createMissingCdcGtmOrderLogs();

            // check if some orders has to be resent
            if (Configuration::get(static::getConfigName('ENABLE_RESEND'))) {
                $this->resendNotSentOrders();
            }


            $needGtmTagInBo = false;

            // create a datalayer order confirmation from BO / order resend
            if ($this->recreateOrderConfirmationFromBO() || $this->generateEnhancedEcommerceOrderResend()) {
                $needGtmTagInBo = true;
                $this->isOrderValidationPage = true;
            }
            // refund
            elseif ($this->datalayerRefund()) {
                $needGtmTagInBo = true;
            }

            // if refund or order to resend, display GTM Tag in backoffice header
            if($needGtmTagInBo) {
                // change name for event when datalayer is ready
                // to avoid to trigger pageviews in Back Office
                $this->eventDatalayerReady = "bo_".$this->eventDatalayerReady;

                $this->generateGtmTag();
                return $this->display(__FILE__, 'gtm_tag.tpl');
            }
        }
    }


    /**
     * hookDisplayOrderConfirmation
     *
     */
    public function hookDisplayOrderConfirmation(array $params)
    {
        $this->displayOrderConfirmation = true;
        try {
            if(!empty($params['objOrder'])) {
                $this->displayOrderConfirmationOrderObj = json_decode(json_encode($params['objOrder']), true);
            }
        } catch (Exception $e) {
            // nothing
        }
    }

    /**
     * New customer created
     * @param $params
     */
    public function hookActionCustomerAccountAdd($params) {
        try {
            $preDataLayer = new Gtm_DataLayer($this);
            $preDataLayer->event = 'sign_up';
            Context::getContext()->cookie->preDataLayerJs = $preDataLayer->toJson();
        } catch (Exception $e) {
            // if a problem occurs, we do not want to block the customer
        }
    }

    /**
     * Customer authentification
     * @param $params
     */
    public function hookActionAuthentication($params) {
        try {
            $preDataLayer = new Gtm_DataLayer($this);
            $preDataLayer->event = 'login';
            Context::getContext()->cookie->preDataLayerJs = $preDataLayer->toJson();
        } catch (Exception $e) {
            // if a problem occurs, we do not want to block the customer
        }
    }

    /**
     * Add id product and id product attribute
     * to product thumbnail (used for select_item)
     * Only for PS before 1.7
     *
     * @param $params
     */
    public function hookDisplayProductPriceBlock($params) {
        // since PS 1.7, array was replaced by lazyArray
        // thanks to is_array($params['product']), this code
        // is only executed before PS 1.7
        if(!empty($params['type']) && $params['type'] == 'price' && isset($params['product']) && is_array($params['product'])) {
            $id_product = !empty($params['product']['id_product']) ? $params['product']['id_product'] : 0;
            $id_product_attribute = !empty($params['product']['id_product_attribute']) ? $params['product']['id_product_attribute'] : 0;
            $this->smarty->assign(array(
                'cdcgtm_id_product' => $id_product,
                'cdcgtm_id_product_attribute' => $id_product_attribute
            ));
            return $this->display(__FILE__, 'views/templates/hook/displayProductPriceBlock.tpl');
        }
    }



    /***************************************************************************
     * GENERATE GTM TAG
     */

    public function generateGtmTag()
    {
        if(!$this->gtm_enable || empty($this->gtm_id)) {
            return "";
        }

        // hook to update datalayer before generation
        Hook::exec('actionCdcgtmUpdateDatalayer', ['datalayer' => $this->dataLayer]);

        // generate datalayer JSON
        $dataLayerJs = $this->dataLayer->toJson($this->debug_enabled);

        // pre DataLayer
        $preDataLayerJs = null;
        if(is_object($this->preDataLayer)) {
            $preDataLayerJs = $this->preDataLayer->toJson($this->debug_enabled);
        } else {
            // get from cookie
            if(isset(Context::getContext()->cookie->preDataLayerJs)) {
                $preDataLayerJs = Context::getContext()->cookie->preDataLayerJs;
                unset(Context::getContext()->cookie->preDataLayerJs);
            }
        }

        // debug
        if($this->debug_enabled) {
            if($preDataLayerJs) {
                $this->addDebug($preDataLayerJs);
            }

            $this->addDebug($dataLayerJs);

            if($this->async_user_info) {
                $this->addDebug('[ASYNC USER INFOS]');
                $this->addDebug('Loading user infos datalayer...');
            }
        }

        // if order validation, save datalayer in log
        if($this->isOrderValidationPage) {
            $this->saveDataLayerInLog($dataLayerJs);
        }

        // datalayer debug backup
        $this->logDataLayerInDb(isset($this->dataLayer->event) ? $this->dataLayer->event : '', $dataLayerJs);

        // Override GTM Tag
        $override_gtm_tag = self::getConfigValue('OVERRIDE_GTM_TAG');
        if(!empty($override_gtm_tag)) {
            $override_gtm_tag = str_replace('GTM-XXXXXXX', $this->gtm_id, $override_gtm_tag);
            $override_gtm_tag = str_replace('GTM-XXXXXX', $this->gtm_id, $override_gtm_tag);
            $override_gtm_tag = str_replace('[[GTM-ID]]', $this->gtm_id, $override_gtm_tag);
        }

        $override_gtm_tag_noscript = self::getConfigValue('OVERRIDE_GTM_TAG_NOSCRIPT');
        if(!empty($override_gtm_tag_noscript)) {
            $override_gtm_tag_noscript = str_replace('GTM-XXXXXXX', $this->gtm_id, $override_gtm_tag_noscript);
            $override_gtm_tag_noscript = str_replace('GTM-XXXXXX', $this->gtm_id, $override_gtm_tag_noscript);
            $override_gtm_tag_noscript = str_replace('[[GTM-ID]]', $this->gtm_id, $override_gtm_tag_noscript);
            if (strpos($override_gtm_tag_noscript, '<noscript>') === false) {
                $override_gtm_tag_noscript = '<noscript>' . $override_gtm_tag_noscript . '</noscript>';
            }
        }

        // model
        $model = array(
            'gtm_id' => $this->gtm_id,
            'preDataLayer' => $preDataLayerJs,
            'dataLayer' => $dataLayerJs,
            'async_user_info' => $this->async_user_info,
            'async_url' => Context::getContext()->link->getModuleLink('cdc_googletagmanager', 'async', array('action' => 'user'), null, null, null, true),
            'event_datalayer_ready' => $this->eventDatalayerReady,
            'gtm_debug' => $this->debug_enabled,
            'override_gtm_tag' => $override_gtm_tag,
            'override_gtm_tag_noscript' => $override_gtm_tag_noscript
        );

        $this->context->smarty->assign($model);

        return $model;
    }



    /***************************************************************************
     * ENHANCED ECOMMERCE FUNCTIONS
     */

	public function generateEnhancedEcommerce() {
        if(!$this->ee_generated) {
    		// general informations
    		$controller = $this->controller;
    		$this->dataLayer->pageCategory = $controller;

            // add user informations
            if(!$this->async_user_info) {
                $this->dataLayer = $this->addUserInfosToDatalayer($this->dataLayer);
            }

            // order confirmation page
            if($this->isOrderValidationPage) {
                $this->orderConfirmation();
            }
            // other pages
            else {
                // test is OPC
                if($this->isOnePageCheckout()) {
                    $this->opc_enabled = true;
                    $controller = self::PAGE_OPC;
                }

                switch ($controller) {
                    case self::PAGE_HOME:
                        $this->generateEnhancedEcommerceHomepage();
                        break;

                    case self::PAGE_PRODUCT:
                        $this->datalayerPageProduct();
                        break;

                    case self::PAGE_CART:
                    case self::PAGE_CART_17:
                    case self::PAGE_PAYMENT:
                    case self::PAGE_OPC:
                        $this->datalayerCheckout($controller);
                        break;

                    case self::PAGE_CATEGORY:
                        $this->displayCategory();
                        break;

                    case self::PAGE_SEARCH:
                        $this->datalayerSearchResults();
                        break;

                    case 'catesearch':
                        $this->datalayerSearchResults(Tools::getValue('search_query'));
                        break;
                    case 'searchiqit':
                        $this->datalayerSearchResults(Tools::getValue('s'));
                        break;
                    case 'productsearch': // leoproductsearch
                        if(Tools::getValue('module') == 'leoproductsearch') {
                            $this->datalayerSearchResults(Tools::getValue('search_query'));
                        }
                        break;
                }
            }

            $this->ee_generated = true;
            return true;
        }
        return false;
	}


    /**
     * add user id and guest id informations to datalayer
     *
     * return Gtm_DataLayer with user informations
     */
    public function addUserInfosToDatalayer($dataLayer) {
        // user information
        if($this->userid_enable || $this->customer_informations != 'never') {

            if(!empty($this->context->cookie->id_customer)) {
                $dataLayer->userLogged = 1;
                $dataLayer->userId = (string) $this->context->cookie->id_customer;

                // get customer groups
                $customer = new Customer($dataLayer->userId);
                if(Validate::isLoadedObject($customer)) {
                    $groups = $customer->getGroups();
                    $groupsName = array();
                    foreach ($groups as $group_id) {
                        $group = new Group($group_id);
                        if(Validate::isLoadedObject($group)) {
                            $groupsName[] = $group->name[(int)(Configuration::get('PS_LANG_DEFAULT'))];
                        }
                    }
                    $dataLayer->userGroups = $groupsName;

                    // customer informations
                    if($this->customer_informations == 'always') {
                        $dataLayer->customer = $dataLayer->getCustomerData($customer->id);
                    }
                }

            } elseif(Configuration::get($this->getConfigName('ENABLE_GUESTID'))) {
                $dataLayer->userLogged = 0;

                if(!empty($this->context->cookie->id_guest)) {
                    $dataLayer->userId = (string) "guest_".$this->context->cookie->id_guest;
                } else {
                    if(empty($this->context->cookie->user_rand_id)) {
                        $this->context->cookie->user_rand_id = rand(100000,999999);
                    }
                    $dataLayer->userId = (string) "guest_".$this->context->cookie->user_rand_id;
                }
            }
        }

        return $dataLayer;
    }

    /**
     * Order Confirmation page
     * @param int|null $id_cart
     * @param boolean $force_resend
     */
	protected function orderConfirmation($id_cart = null, $force_resend = false) {

        // get orders from cart
        if(!$id_cart) {
          $id_cart = $this->getCartFromOrderConfirmation();
        }

        $this->dataLayer->generateOrderConfirmationFromCart($id_cart, $force_resend);
    }

    /**
     * Return cart ID
     */
    protected function getCartFromOrderConfirmation() {
        $id_cart = null;

        // get cart from hook displayOrderConfirmation
        if(isset($this->displayOrderConfirmationOrderObj['id_cart'])) {
            $id_cart = (int) $this->displayOrderConfirmationOrderObj['id_cart'];
        }

        // get cart from params
        if(!$id_cart) {
            $id_cart = (int)(Tools::getValue('id_cart'));
        }

        // get cart from param order
        if(!$id_cart) {
            $order_id = (int) Tools::getValue('id_order', Tools::getValue('order'));
            $order = new Order($order_id);
            if(Validate::isLoadedObject($order)) {
                $id_cart = $order->id_cart;
            }
        }

        // get cart from last order
        if(!$id_cart) {
            $id_customer = Context::getContext()->customer->id;
            $sql = 'SELECT `id_cart` FROM `' . _DB_PREFIX_ . 'orders`
                WHERE 1
                    ' . Shop::addSqlRestriction()
                . ($id_customer ? ' AND id_customer = ' . (int) $id_customer : '')
                . ' ORDER BY id_order DESC';
            $id_cart = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        return $id_cart;
    }


    /***************************************************************************
     * GOOGLE REMARKETING
     * Official documentation: https://developers.google.com/adwords-remarketing-tag/parameters#retail
     */

    protected function generateRemarketingParameters() {
        if(!$this->remarketing_generated) {
            // general informations
            $controller = $this->controller;

            // order confirmation is handled by generateRemarketingParametersForOrderConfirmation()
            if(!$this->isOrderValidationPage) {
                $this->dataLayer->google_tag_params = new Gtm_GoogleTagParams();
                $pagetype = $controller; // home,searchresults,category,product,cart,purchase,other

                // test is OPC
                if($this->isOnePageCheckout()) {
                    $this->opc_enabled = true;
                    $controller = self::PAGE_OPC;
                }

                switch ($controller) {
                    case self::PAGE_HOME:
                        $pagetype = 'home';
                        break;

                    case self::PAGE_PRODUCT:
                        $product = $this->getDisplayedProduct();
                        if(Validate::isLoadedObject($product)) {
                            $this->dataLayer->google_tag_params->ecomm_prodid = Gtm_Product::getInstance()->remarketingGetProductIdentifier($product);
                            $this->dataLayer->google_tag_params->ecomm_totalvalue = (float) round((float) Product::getPriceStatic($product->id, true, $product->id_product_attribute), _CDCGTM_PRICE_DECIMAL_);
                            $this->dataLayer->google_tag_params->ecomm_totalvalue_tax_exc = (float) round((float) Product::getPriceStatic($product->id, false, $product->id_product_attribute), _CDCGTM_PRICE_DECIMAL_);
                            $this->dataLayer->google_tag_params->ecomm_category = $this->getCategoryName($product->id_category_default);
                        }
                        break;

                    case self::PAGE_PAYMENT:
                    case self::PAGE_CART:
                    case self::PAGE_CART_17:
                    case self::PAGE_OPC:
                        $pagetype = 'cart';
                        $this->dataLayer->google_tag_params->ecomm_prodid = $this->remarketingGetProductListIdsFromCart();
                        $this->dataLayer->google_tag_params->ecomm_totalvalue = (float) round($this->getTotalProductPriceFromCart(), _CDCGTM_PRICE_DECIMAL_);
                        $this->dataLayer->google_tag_params->ecomm_totalvalue_tax_exc = (float) round($this->getTotalProductPriceFromCart(null, false), _CDCGTM_PRICE_DECIMAL_);
                        break;

                    case self::PAGE_CATEGORY:
                        $this->dataLayer->google_tag_params->ecomm_category = $this->getCategoryName((int) Tools::getValue('id_category'));
                        break;
                    case self::PAGE_SEARCH:
                        $pagetype = 'searchresults';
                        break;
                    default:
                        $pagetype = 'other';
                        break;
                }

                $this->dataLayer->google_tag_params->ecomm_pagetype = $pagetype;
            }

            $this->remarketing_generated = true;
        }
    }


    /**
     * Return a list of product id from the cart
     */
    public function remarketingGetProductListIdsFromCart($id_cart = null) {
        $products_list = array();

        // get products list from cart
        $products = $this->getProductListFromCart($id_cart);
        if(is_array($products) && count($products)) {
            foreach ($products as $product) {
                $product_identifier = Gtm_Product::getInstance()->remarketingGetProductIdentifier($product);
                if($product_identifier) {
                    $products_list[] = $product_identifier;
                }
            }
        }

        return $products_list;
    }


    /**
     * Return the sum of each product found on cart
     * $mod total: use the product quantity for each product
     *      unit: use 1 unit for each product
     */
    public function getTotalProductPriceFromCart($id_cart = null, $include_tax = true, $mod = 'total') {
        $product_total = 0.0;

        // price tax exc. or inc.
        $price_field = 'price';
        if($include_tax) {
            $price_field = 'price_wt';
        }

        // get products list from smarty
        $products = $this->getProductListFromCart($id_cart);
        if(is_array($products) && count($products)) {
            foreach ($products as $p) {
                if(isset($p[$price_field])) {
                    // quantity
                    $quantity = 1;
                    if($mod == 'total' && !empty($p['quantity'])) {
                        $quantity = $p['quantity'];
                    }

                    $product_total += $quantity * ((float) $p[$price_field]);
                }
            }
        }

        return $product_total;
    }


    /**
     * Return the name of the category
     * 
     * @param  Category $category
     * @return String category nale
     */
    public function getCategoryName($category, $cat_level = null) {
        $categoryName = '';
        $categoryId = null;
        $id_lang = $this->getDataLanguage();

        // init category
        if(!Validate::isLoadedObject($category)) {
            $categoryId = (int) $category;
            if($categoryId) {
                $category = new Category($categoryId);
            }
        }

        if(!Validate::isLoadedObject($category)) {
            return 'category not found ('.$categoryId.')';
        }

        if(!is_null($cat_level)) {
            while ($category->level_depth > $cat_level) {
                $category = new Category($category->id_parent);
            }
        }

        switch (
            $this->category_name_field) {
            case 'link_rewrite':
                $categoryName = !empty($category->link_rewrite) ? $category->link_rewrite : null;
                break;

            case 'id':
                $categoryName = $category->id;
                break;
            
            default:
                if(Validate::isLoadedObject($category)) {
                    if($this->category_hierarchy && is_null($cat_level)) {
                        // get parents
                        $c_parents = $category->getParentsCategories($id_lang);
                        $c_parents_names = array();
                        foreach ($c_parents as $c_parent) {
                            if(!$c_parent['is_root_category']) {
                                $c_parents_names[] = $this->cleanString($c_parent['name']);
                            }
                        }
                        $c_parents_names = array_reverse($c_parents_names);
                        $categoryName = implode('/', $c_parents_names);
                    }
                    else {
                        $categoryName = $this->cleanString($category->getName());
                    }
                }
                break;
        }

        $categoryName = trim($categoryName);

        if(empty($categoryName)) {
            $categoryName = 'Unknown category name';
        }
        
        return $categoryName;
    }

    /**
     * Set page_cat1, page_cat2, page_cat3 from current category
     * @param $category
     */
    public function getPageCategoryNameHierarchy($category, $level)
    {
        if(!$this->rootCategory) {
            $this->rootCategory = new Category($this->context->shop->id_category);
        }
        $level_depth = $level + (int)$this->rootCategory->level_depth;

        // get category name
        if($category->level_depth >= $level_depth) {
            return $this->getCategoryName($category, $level_depth);
        }
        return null;
    }

    /**
     * Google Customer Reviews
     * Generate Order confirmation code
     * https://support.google.com/merchants/answer/7106244
     */
    protected function greviewsGenerateOrderConfirmationCode() {
        // get order from cart
        $id_cart = (int)(Tools::getValue('id_cart'));
        $orders = PrestashopUtils::getOrdersByCartId((int)($id_cart));

        if($this->debug_enabled) {
            $this->addDebug("[greviewsGenerateOrderConfirmationCode]");
        }

        // create GCR order vars
        $greviews_order_vars = array();
        $greviews_order_vars['merchant_id'] = self::getConfigValue('GCR_MERCHANT_ID');
        $greviews_order_vars['order_id'] = "";
        $greviews_order_vars['customer_email'] = "";
        $greviews_order_vars['delivery_country'] = "";

        $delivery_days = (int) self::getConfigValue('GCR_DELIVERY_DAYS');
        $greviews_order_vars['estimated_delivery_date'] = date ( 'Y-m-d' , strtotime ( $delivery_days.' weekdays' ) );


        $orders_list = array();
        foreach ($orders as $id_order) {
            $id_order = $id_order['id_order'];
            $order = new Order($id_order);

            if(Validate::isLoadedObject($order)) {
                // test if order is not in error and not already sent ?
                if($order->current_state != Configuration::get('PS_OS_ERROR')) {
                    $orders_list[] = $id_order;
                    // first order, get customer info
                    if(count($orders_list) == 1) {
                        $customer = $order->getCustomer();
                        if(Validate::isLoadedObject($customer)) {
                            $greviews_order_vars['customer_email'] = $customer->email;
                        }

                        $delivery_address = new Address($order->id_address_delivery);
                        if(Validate::isLoadedObject($delivery_address)) {
                            $greviews_order_vars['delivery_country'] = Country::getIsoById($delivery_address->id_country);
                        }
                    }

                    // build id if multi order
                    $order_id_field = 'reference';
                    $current_ref_order = $order->$order_id_field;
                    if(empty($greviews_order_vars['order_id'])) {
                        // reference empty, set reference
                        $greviews_order_vars['order_id'] = $current_ref_order;
                    } else {
                        // reference not empty, add it if not yet added
                        $array_ids = explode(',', $greviews_order_vars['order_id']);
                        if(!in_array($current_ref_order, $array_ids)) {
                            $array_ids[] = $current_ref_order;
                            $greviews_order_vars['order_id'] = implode(',', $array_ids);
                        }
                    }

                }
            }
        }

        // return view
        $this->context->smarty->assign(array('greviews' => $greviews_order_vars));
    }


    /**
     * Create GtmOrderLog if not already created
     */
    public static function createGtmOrderLog($id_order, $id_shop) {
        if(CdcGtmOrderLog::countByOrderId($id_order, $id_shop) == 0) {
            $order_log = new CdcGtmOrderLog();
            $order_log->id_order = $id_order;
            $order_log->id_shop = $id_shop;
            $order_log->sent = 0;
            $order_log->resent = 0;
            $order_log->save();
        }
    }

    /**
     * Save datalayer in log
     */
    protected function saveDataLayerInLog($dataLayerjs) {
        if(is_array($this->orderValidationDetails) && count($this->orderValidationDetails)) {
            foreach ($this->orderValidationDetails as $order) {
               $order_log = CdcGtmOrderLog::getByOrderId($order['id_order'], $this->shop_id);
               if(Validate::isLoadedObject($order_log)) {
                    $order_log->datalayer = $dataLayerjs;
                    $order_log->save();
               }
            }
        }
    }


    /**
     * Return checkout step
     *  1 : cart summary
     *  2 : address
     *  3 : shipping
     *  4 : payment
     *  5 : payment options
     */
    protected function getCheckoutStep() {
        $step = 0;

        // Prestashop >= 1.7
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $checkout_process = $this->context->smarty->getTemplateVars('checkout_process');
            if($checkout_process) {
                $checkout_process_reflection = new ReflectionObject($checkout_process);
                $renderable = $checkout_process_reflection->getProperty('renderable');
                $renderable->setAccessible(true);
                $checkout_process_access = $renderable->getValue($checkout_process);
                $reflectionMethod = new ReflectionMethod('CheckoutProcess', 'getSteps');
                $checkout_steps = $reflectionMethod->invoke($checkout_process_access);
                foreach ($checkout_steps as $step_k => $step_val) {
                    if($step_val->isCurrent()) {
                        $step = $step_k;
                        break;
                    }
                }
            }
        }

        // Prestashop <= 1.7
        else {
            if(!$this->opc_enabled) {
                $step = $this->getValue('controller') == 'payment' ? 4 : Tools::getValue('step', 0);
            }
        }

        // transform step [0..n] to [1..n+1]
        $step++;

        // To keep consistancy accross multiple payment methods,
        // regroup payment options in step payment (4)
        if($step > 4) {
          $step = 4;
        }

        return $step;
    }

    /**
     * Enhanced Ecommerce
     * Product Impressions
     * Page Homepage
     * https://developers.google.com/tag-manager/enhanced-ecommerce#product-impressions
     */
    protected function generateEnhancedEcommerceHomepage() {
        $this->dataLayer->event = 'homepage';
    }

    /**
     * Display category page
     */
    protected function displayCategory() {
        $this->dataLayer->addCategory((int) Tools::getValue('id_category'), $this->getProductListFromSmarty());
        $this->dataLayer->event = 'view_item_list';
    }

    /**
     * Get datalayer for category loaded asynchronously
     */
    public function displayCategoryAsync($id_products) {
        if(!empty($id_products)) {
            $products = array();
            foreach ($id_products as $product_identifier) {
                $id_product = explode('-', $product_identifier);
                $product = new Product($id_product[0]);
                if(Validate::isLoadedObject($product)) {
                    $product->id_product_attribute = isset($id_product[1]) ? $id_product[1] : 0;
                    $products[] = $product;
                }
            }

            // generate datalayer
            $dataLayer = new Gtm_DataLayer($this);
            $dataLayer->addCategory((int) Tools::getValue('id_category'), $products);

            return $dataLayer;
        }
    }

    /**
     * Add search informations to datalayer
     */
    protected function datalayerSearchResults($search_query = null) {
        $list = array(
            'name' => 'Search Results',
            'id' => 'search'
        );

        // get products list from smarty
        $products = $this->getProductListFromSmarty();
        $this->dataLayer->addProductList($products, $list);

        // find the search term
        if(!empty($search_query)) {
            $term = $search_query;
        } else {
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $term = $this->context->smarty->getTemplateVars('search_string');
            } else {
                $term = $this->context->smarty->getTemplateVars('search_query');
            }
        }

        // code injection protection
        $this->dataLayer->search_term = $this->cleanString(htmlspecialchars($term));

        $this->dataLayer->event = 'search';
    }


    /**
     * Display cart
     */
    /*protected function datalayerCart() {
        // get products list from smarty
        $cartProducts = $this->getProductListFromCart();
        $this->dataLayer->addProductList($cartProducts, null, $this->max_products_datalayer);

        // specific cart
        $this->dataLayer->setSubtotalsFromCart($this->context->cart);
        $this->dataLayer->getCartRulesFromCart($this->context->cart);
        $this->dataLayer->event = 'view_cart';
    }*/

    /**
     * Display checkout
     */
    protected function datalayerCheckout($controller) {
        // get step
        $step = $this->getCheckoutStep();

        // get products list from smarty
        $cartProducts = $this->getProductListFromCart();
        $this->dataLayer->addProductList($cartProducts);

        // specific checkout
        $this->dataLayer->setSubtotalsFromCart($this->context->cart);
        $this->dataLayer->getCartRulesFromCart($this->context->cart);

        // handle GA4 event
        if($controller == self::PAGE_CART_17
            || (version_compare(_PS_VERSION_, '1.7', '<') && $step == 1) ) {
            $this->dataLayer->event = 'view_cart';
        } else {
            // trigger begin checkout on step 1 or step 2 if step1 was skipped
            switch ($step) {
                case 1:
                    $this->dataLayer->event = 'begin_checkout';
                    // a problem has already been found (502 error) caused by the cookie creation
                    // if it occurs again, a fix will be needed
                    Context::getContext()->cookie->begin_checkout_triggered = 1;
                    break;

                case 2:
                    $this->dataLayer->event = 'checkout_address';

                    // if user skipped the step 1 (he is already authentified)
                    // we still need to trigger begin_checkout
                    if(empty(Context::getContext()->cookie->begin_checkout_triggered)) {
                        $this->preDataLayer = new Gtm_DataLayer($this);
                        $this->preDataLayer->addProductList($cartProducts);
                        $this->preDataLayer->setSubtotalsFromCart($this->context->cart);
                        $this->preDataLayer->getCartRulesFromCart($this->context->cart);
                        $this->preDataLayer->event = 'begin_checkout';
                    } else {
                        unset(Context::getContext()->cookie->begin_checkout_triggered);
                    }
                    break;

                case 3: $this->dataLayer->event = 'checkout_shipping'; break;
                case 4: $this->dataLayer->event = 'checkout_payment'; break;
                default: $this->dataLayer->event = 'checkout_step'.$step; break;
            }
        }
    }


    /**
     * Enhanced Ecommerce
     * Product View
     * Page Product Detail
     * https://developers.google.com/tag-manager/enhanced-ecommerce#details
     */
    protected function datalayerPageProduct() {
        $this->dataLayer->addItem($this->getDisplayedProduct());
        $this->dataLayer->ecommerce->value = !empty($this->dataLayer->ecommerce->items[0]->price) ? $this->dataLayer->ecommerce->items[0]->price : 0;
        $this->dataLayer->event = "view_item";
    }


    /**
     * Refund Order / Products
     * Backoffice refund
     *   - Partial refund
     *   - Update status to "REFUND" OR "CANCELED"
     */
    protected function datalayerRefund() {
        $refundsQueue = Configuration::get('CDC_GTM_REFUNDS_Q', null, null, $this->shop_id);
        if(!empty($refundsQueue)) {
            $ordersToRefund = json_decode($refundsQueue, true);

            // refund orders 1 at a time
            if(is_array($ordersToRefund) && count($ordersToRefund)) {
                $order_lines = reset($ordersToRefund); // get first elem and reset pointer
                $order_id = key($ordersToRefund);
                $this->dataLayer->generateRefund($order_id, $order_lines);
                return true;
            }
        }

        // no refund to send
        return false;
    }


    /**
     * If orders are present in the resend queue,
     * Generate datalayer to resend order
     */
    protected function generateEnhancedEcommerceOrderResend() {
        $cartsResendQueue = Configuration::get('CDC_GTM_ORDER_RESEND_Q', null, null, $this->shop_id);
        if(!empty($cartsResendQueue)) {

            $cartsToResend = json_decode($cartsResendQueue, true);
            // resend orders 1 at a time
            if(is_array($cartsToResend) && count($cartsToResend)) {
                reset($cartsToResend); // set pointer to first elem of array
                $cart_id = key($cartsToResend);

                $this->orderConfirmation($cart_id, true);

                $this->dataLayer->event = "order_resend";
                return true;
            }
        }

        // no refund to send
        return false;
    }



    /**
     * Add a refund (full or partial) to be sent as soon as possible
     */
    protected function addRefund($order_id, $shop_id, $order_detail_id = null) {
        // get refunds queue
        $refundsQueue = Configuration::get('CDC_GTM_REFUNDS_Q', null, null, $shop_id);
        if(empty($refundsQueue)) {
            $refundsQueue = array();
        } else {
            $refundsQueue = json_decode($refundsQueue, true);
        }

        // a full refund is already in the queue, don't add more refund
        if(!empty($refundsQueue[$order_id]) && $refundsQueue[$order_id] == "all") {
            return;
        }

        // full refund
        if(is_null($order_detail_id)) {
            $refundsQueue[$order_id] = "all";
        }
        // partial refund
        else {
            if(!isset($refundsQueue[$order_id])) {
                $refundsQueue[$order_id] = array();
            }
            if(!in_array($order_detail_id, $refundsQueue[$order_id])) {
                $refundsQueue[$order_id][] = $order_detail_id;
            }
        }

        // save
        Configuration::updateValue('CDC_GTM_REFUNDS_Q', json_encode($refundsQueue), false, null, $shop_id);
    }


    /**
     * Add a order to the resend queue, to be sent as soon as possible
     * ORDER_RESEND_Q format :
     * [
     *   CART_ID1:[ORDER_ID1,ORDER_ID2],
     *   CART_ID2:[ORDER_ID3],
     * ]
     */
    public static function addOrderResend($order_id, $shop_id) {
        $order = new Order($order_id);
        if(Validate::isLoadedObject($order)) {
            $id_cart = $order->id_cart;
            if(!$id_cart) {
                return false;
            }

            // get resend queue
            $cartResendQueue = Configuration::get('CDC_GTM_ORDER_RESEND_Q', null, null, $shop_id);
            if(empty($cartResendQueue)) {
                $cartResendQueue = array();
            } else {
                $cartResendQueue = json_decode($cartResendQueue, true);
            }

            // create array of orders
            if(!isset($cartResendQueue[$id_cart]) || !is_array($cartResendQueue[$id_cart])) {
                $cartResendQueue[$id_cart] = array();
            }

            // add order in the queue
            if(!in_array($order_id, $cartResendQueue[$id_cart])) {
                $cartResendQueue[$id_cart][] = $order_id;
            }

            // save
            Configuration::updateValue('CDC_GTM_ORDER_RESEND_Q', json_encode($cartResendQueue), false, null, $shop_id);
        }
    }

    /**
     * Check if some orders has to be resent
     * and add them to the resend queue
     */
    protected function resendNotSentOrders() {
        $numberOfDaysMaxToResendOrders = (int)Configuration::get($this->getConfigName('RESEND_DAYS'));
        if($numberOfDaysMaxToResendOrders < 1) {
            $numberOfDaysMaxToResendOrders = 1;
        }
        $orders = CdcGtmOrderLog::getNotSent(null, $numberOfDaysMaxToResendOrders, 10);
        foreach($orders as $order) {
            cdc_googletagmanager::addOrderResend($order['id_order'], $order['id_shop']);
        }
    }
            


    /**
     * Test if the page is the order validation page
     */
    protected function isOrderValidationPage() {
        $ordervalidationpage = false;
        $controller = $this->controller;
        $module = Tools::getValue('module');

        // hook displayOrderConfirmation called
        // every new order should call this
        if($this->displayOrderConfirmation) {
            $ordervalidationpage = true;
        }

        // main order validation page
        // every payment module should redirect to it
        elseif($controller == self::PAGE_ORDERCONFIRMATION) {
            $ordervalidationpage = true;
        }

        // some payment modules use another typo
        elseif($controller == 'order-confirmation') {
            $ordervalidationpage = true;
        }

        // paypal module
        elseif ($module == 'paypal' && $controller == 'submit') {
            $ordervalidationpage = true;
        }

        // paypalplus module
        elseif ($module == 'paypalplus' && $controller == 'confirmation') {
            $ordervalidationpage = true;
        }

        // paypalplus module bis
        elseif ($module == 'paypal' && $controller == 'submitplus') {
            $ordervalidationpage = true;
        }

        // stripejs module
        elseif ($module == 'stripejs' && $controller == 'confirmation') {
            $ordervalidationpage = true;
        }

        // firstdatapayment module
        elseif ($module == 'firstdatapayment' && $controller == 'success') {
            $ordervalidationpage = true;
        }

        // przelewy24 module
        elseif ($module == 'przelewy24' && $controller == 'paymentSuccessful') {
            $ordervalidationpage = true;
            // get cart id from url, and put it in displayOrderConfirmationOrderObj
            $this->displayOrderConfirmationOrderObj['id_cart'] = (int) Tools::getValue('ga_cart_id');
        }

        elseif ($module == 'przelewy24' && $controller == 'paymentReturn') {
            $ordervalidationpage = true;
            // get cart id from url, and put it in displayOrderConfirmationOrderObj
            $this->displayOrderConfirmationOrderObj['id_cart'] = (int) Tools::getValue('cart_id');
        }

        elseif ($module == 'przelewy24' && $controller == 'paymentConfirmation') {
            $ordervalidationpage = true;
        }

        // mollie module
        elseif ($module == 'mollie' && $controller == 'return') {
            $ordervalidationpage = true;
            // get cart id from url, and put it in displayOrderConfirmationOrderObj
            $this->displayOrderConfirmationOrderObj['id_cart'] = (int) Tools::getValue('cart_id');
        }

        // klarna checkout
        elseif ($module == 'klarnaofficial' && $controller == 'thankyou') {
            $ordervalidationpage = true;
        }

        // sveacheckout
        elseif ($module == 'sveacheckout' && $controller == 'confirmation') {
            $ordervalidationpage = true;
        }

        // paysoncheckout2
        elseif ($module == 'paysoncheckout2' && $controller == 'confirmation') {
            $ordervalidationpage = true;
        }

        // g2apay
        elseif ($module == 'g2apay' && $controller == 'success') {
            $ordervalidationpage = true;
        }

        // iyzico module
        elseif ($module == 'iyzicocheckoutform' && $controller == 'result') {
            $ordervalidationpage = true;
            $this->displayOrderConfirmationOrderObj['id_cart'] = (int) $this->context->cart->id;
        }

        // payu module (waiting validation)
        elseif ($module == 'payu' && $controller == 'success') {
            $ordervalidationpage = true;
        }

        // ecm_checkout
        elseif ($module == 'ecm_checkout' && $controller == 'checkout_end') {
            $ordervalidationpage = true;
        }

        // dotpay
        elseif ($module == 'dotpay' && $controller == 'back') {
            $ordervalidationpage = true;
        }

        // eCard VUB
        elseif ($module == 'ecardvub' && $controller == 'notification') {
            $ordervalidationpage = true;
        }

        if ($ordervalidationpage && !is_array($this->orderValidationDetails)) {
            $this->orderValidationDetails = array();
        }

        if($this->debug_enabled) {
            $this->addDebug("Is purchase: ". ($ordervalidationpage ? "yes" : "no") ." | controller = $controller / module = $module");
            if($ordervalidationpage) {
                $this->addDebug('displayOrderConfirmationOrderObj[id_cart] : ' . (isset($this->displayOrderConfirmationOrderObj['id_cart']) ? $this->displayOrderConfirmationOrderObj['id_cart'] : 'not set'));
            }
        }

        return $ordervalidationpage;
    }

    /**
     * test if the checkout page is one page checkout
     */
    protected function isOnePageCheckout() {
        $opc = false;

        $controller = $this->controller;
        $module = Tools::getValue('module');

        // test on controller name
        if($controller == self::PAGE_OPC
            || $controller == self::PAGE_OPC_SUPERCHECKOUT) {
            $opc = true;
        }

        // test on module name
        elseif($module == "onepagecheckoutps") {
            $opc = true;
        }

        // klarna checkout
        elseif($module == "klarnaofficial" && $controller == "checkoutklarna") {
            $opc = true;
        }
        elseif($module == "klarnaofficial" && $controller == "checkoutklarnakco") {
            $opc = true;
        }

        // st easy checkout
        elseif($module == "steasycheckout" && $controller == "default") {
            $opc = true;
        }
        

        return $opc;
    }


    /**
     * Return the current displayed product
     * @return Product
     */
    protected function getDisplayedProduct() {
        $product_id = (int) $this->getValue('id_product');
        if(!$product_id) {
            $smarty_product = $this->context->smarty->getTemplateVars('product');
            if(Validate::isLoadedObject($smarty_product)) {
                $product_id = (int) $smarty_product->id;
            }
        }
        $product = new Product($product_id);

        // add attribute
        if(Tools::getValue('id_product_attribute')) {
            $product->id_product_attribute = (int) Tools::getValue('id_product_attribute');
        } elseif(!isset($product->id_product_attribute)) {
            $product->id_product_attribute = 0;
        }

        return $product;
    }


    /**
     * Return product list displayed on page
     * Get smarty variable
     */
    protected function getProductListFromSmarty() {
        // get products list from smarty
        $products = $this->context->smarty->getTemplateVars('products');

        // prestashop >= 1.7
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            // get in listing
            if(!$products || !is_array($products)) {
                $listing = $this->context->smarty->getTemplateVars('listing');
                if(isset($listing['products'])) {
                    $products = $listing['products'];
                }
            }

            // get in cart
            if(!$products || !is_array($products)) {
                $cart = $this->context->smarty->getTemplateVars('cart');
                if(isset($cart['products'])) {
                    $products = $cart['products'];
                }
            }
        }

        return $products;
    }

    /**
     * Return product list on cart
     */
    protected function getProductListFromCart($id_cart = null) {
        $products = array();

        if($id_cart) {
            $cart = new Cart($id_cart);
        } else {
            // search in context
            $cart = $this->context->cart;

            // search in parameter
            if(!Validate::isLoadedObject($cart) && Tools::getIsset('id_cart')) {
                $cart = new Cart((int) Tools::getValue('id_cart'));
            }
        }

        // get products from cart
        if(Validate::isLoadedObject($cart)) {
            $products = $cart->getProducts();
        }
        return $products;
    }


    /**
     * Return id product attribute
     * 
     * @param   $product
     * @return int          id_product_attribute
     */
    protected function getProductAttributeId($product) {
        $id_product_attribute = null;
        $product = is_object($product) ? $product : (object)$product;

        if(empty($product->id) && !empty($product->id_product)) {
            $product->id = $product->id_product;
        }

        if(!empty($product->id)) {
            // get product variant
            if (!empty($product->product_attribute_id)) {
                $id_product_attribute = (int) $product->product_attribute_id;
            } elseif (!empty($product->id_product_attribute)) {
                $id_product_attribute = (int) $product->id_product_attribute;
            }

            // force default variant whenever possible
            if ($this->display_variant_id && !$id_product_attribute) {
                $id_product_attribute = Product::getDefaultAttribute($product->id);
            }
        }

        return $id_product_attribute;
    }

    /**
     * Generate datalayer for cart action (add or remove)
     *
     * @param $id_product
     * @param $id_product_attribute
     * @param $action
     * @param $qtity
     */
    public function getDataLayerCartAction($id_product, $id_product_attribute, $action, $qtity) {
        if($action != 'add' && $action != 'remove') {
            return null;
        }

        // get product
        $product = new Product($id_product);
        $product->id_product_attribute = $id_product_attribute;
        $product->cart_quantity = (int) $qtity;

        // generate datalayer
        $dataLayer = new Gtm_DataLayer($this);
        $dataLayer->addItem($product);
        $dataLayer->event = ($action == 'add') ? 'add_to_cart' : 'remove_from_cart';

        // add value
        $productPrice = (float) Product::getPriceStatic($product->id, $this->main_price_with_tax, $id_product_attribute, _CDCGTM_PRICE_DECIMAL_, null, false, true, $qtity);
        $dataLayer->ecommerce->value = (string) round($productPrice * $product->cart_quantity, _CDCGTM_PRICE_DECIMAL_);

        return $dataLayer;
    }

    /**
     * Generate datalayer for product click
     *
     * @param $id_product
     * @param $id_product_attribute
     */
    public function productClick($id_product, $id_product_attribute) {
        // get product
        $product = new Product($id_product);
        $product->id_product_attribute = $id_product_attribute;

        // generate datalayer
        $dataLayer = new Gtm_DataLayer($this);
        $dataLayer->addItem($product);
        $dataLayer->event = 'select_item';

        return $dataLayer;
    }


    /**
     * Return list of languages
     */
    private function getLanguages()
    {
        $languages = array();
        $languages[0] = $this->l('Default - User language');
        $psLangs = Language::getLanguages();
        foreach ($psLangs as $psLang) {
            $languages[$psLang['id_lang']] = $psLang['name'];
        }
        return $languages;
    }

    /**
     * Datalayer language id
     * @return int
     */
    public function getDataLanguage() {
        return $this->data_language ?: (int) $this->context->language->id;
    }

    /**
     * Remove accents from string
     * @see https://stackoverflow.com/a/10790734
     * 
     * @param  string $string
     * @return string
     */
    public function remove_accents($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );

        $string = strtr($string, $chars);

        return $string;
    }

    /**
     * Replace accented characters
     * 
     * @param  string $string
     * @return string
     */
    public function cleanString($string) {
        $string = $this->remove_accents($string);

        // keep only 7 bit printable ASCII chars; remove others
        // not used anymore since we can use unicode with json_encode
        //$string = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $string);

        return $string;
    }

    /**
     * Generate a callback function
     * depending on the type and the params
     */
    public function getCallback($type, $params = array()) {
        $callbackFn = "";
        //$callbackUrl = $this->context->link->getModuleLink($this->name, 'callback', array(), null, null, null, true);

        // function protected in early PS 1.6 versions and before
        $baseurl = '';
        if (version_compare(_PS_VERSION_, '1.6.1.15', '>')) {
            $baseurl = $this->context->shop->getBaseURL(true);
        }

        if(empty($baseurl)) {
            // the old url style works in any cases
            $baseurl = 'https://'.$this->context->shop->domain_ssl;
        }

        if(Tools::substr($baseurl, -1) != '/') {
            $baseurl = $baseurl.'/';
        }
        $callbackUrl = $baseurl.'index.php?fc=module&module=cdc_googletagmanager&controller=callback';
        $callbackParams = array();

        // create callback params
        $callbackParams['type'] = $type;
        if(!is_null($params) && is_array($params) && count($params)) {
            $callbackParams['params'] = $params;
        }

        // if params are not empty, create ajax callback function
        if(count($callbackParams)) {
            $callbackParams = urlencode(base64_encode(json_encode($callbackParams)));
            $callbackUrl .= '&p='.$callbackParams;
            $callbackFn = 'function(containerId) {
                if (containerId == "'.$this->gtm_id.'") {
                    console.log("cdcgtm callback: '.$type.'");
                    var x = new XMLHttpRequest();
                    x.open("GET", "'.$callbackUrl.'", true);
                    x.send();
                }
            }';

            // remove new lines
            $callbackFn = preg_replace( "/\r|\n/", "", $callbackFn);
            // remove white spaces
            $callbackFn = preg_replace('/\s+/', ' ', $callbackFn);
        }

        return $callbackFn;
    }

    /**
     * hook home to display generate the product list associated to home featured, news products and best sellers Modules
     */
    public function isModuleEnabled($name)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>='))
            if(Module::isEnabled($name))
            {
                $module = Module::getInstanceByName($name);
                if(!$module) {
                    return false;
                }
                return $module->isRegisteredInHook('home');
            }
            else
                return false;
        else
        {
            $module = Module::getInstanceByName($name);
            return ($module && $module->active === true);
        }
    }


    /**
     * Return value associated to the key
     * search in :
     *   - POST values
     *   - GET values
     */
    public function getValue($key) {
        return Tools::getValue($key);
    }


    /**
     * Install custom hooks in template files
     */
    public function installCustomHooks() {
        $success = true;

        // Prestashop 1.7
        if(version_compare(_PS_VERSION_, '1.7', '>=')) {
            // displayAfterTitleTag
            $filename = _PS_THEME_DIR_.'templates/_partials/head.tpl';
            if(!CdcTools::stringInFile('{hook h="displayAfterTitleTag"}', $filename)) {
                $file_content = Tools::file_get_contents($filename);
                if(!empty($file_content)) {
                    $matches = preg_split('/(<\/title>)/is', $file_content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                    if(count($matches) == 3) {
                        $new_content = $matches[0] . $matches[1] . "\n{hook h=\"displayAfterTitleTag\"}" . $matches[2];
                        if(!file_put_contents($filename, $new_content)) {
                            $success = false;
                        }
                    } else {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }

        }

        // Prestashop 1.6 / 1.5
        else {
            // displayAfterTitleTag
            $filename = _PS_THEME_DIR_.'header.tpl';
            if(!CdcTools::stringInFile('{hook h="displayAfterTitleTag"}', $filename)) {
                $file_content = Tools::file_get_contents($filename);
                $matches = preg_split('/(<\/title>)/is', $file_content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                if(count($matches) == 3) {
                    $new_content = $matches[0] . $matches[1] . "\n{hook h=\"displayAfterTitleTag\"}" . $matches[2];
                    if(!file_put_contents($filename, $new_content)) {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }

            // displayAfterBodyOpeningTag
            if(!CdcTools::stringInFile('{hook h="displayAfterBodyOpeningTag"}', _PS_THEME_DIR_.'header.tpl')) {
                $file_content = Tools::file_get_contents(_PS_THEME_DIR_.'header.tpl');
                $matches = preg_split('/(<body.*?>)/is', $file_content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                /* ' Fix bug syntax coloration */
                if(count($matches) == 3) {
                    $new_content = $matches[0] . $matches[1] . "\n{hook h=\"displayAfterBodyOpeningTag\"}" . $matches[2];
                    if(!file_put_contents(_PS_THEME_DIR_.'header.tpl', $new_content)) {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }

            // displayBeforeBodyClosingTag
            if(!CdcTools::stringInFile('{hook h="displayBeforeBodyClosingTag"}', _PS_THEME_DIR_.'footer.tpl')) {
                $file_content = Tools::file_get_contents(_PS_THEME_DIR_.'footer.tpl');
                $matches = preg_split('/(<\/body>)/is', $file_content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                if(count($matches) == 3) {
                    $new_content = $matches[0] . "{hook h=\"displayBeforeBodyClosingTag\"}\n" . $matches[1] . $matches[2];
                    if(!file_put_contents(_PS_THEME_DIR_.'footer.tpl', $new_content)) {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }
        }

        // force reinstall hooks
        $this->unregisterHook('displayAfterTitleTag');
        $this->unregisterHook('displayAfterBodyOpeningTag');
        $this->unregisterHook('displayBeforeBodyClosingTag');

        $this->registerHook('displayAfterTitleTag');
        $this->registerHook('displayAfterBodyOpeningTag');
        $this->registerHook('displayBeforeBodyClosingTag');

        return $success;
    }

    /**
     * Enable or disable debug
     * Return true if debug is enabled
     */
    protected function debugManager() {
        // set debug by param
        if(Tools::getIsset('cdcgtm_debug')) {
            $param_debug = (int) Tools::getValue('cdcgtm_debug');
            unset($_GET['cdcgtm_debug']);

            if($param_debug == 1) {
                // enable debug
                $this->debug_enabled = true;
                Context::getContext()->cookie->cdcgtmd = 1;
            } else {
                // disable debug
                $this->debug_enabled = false;
                Context::getContext()->cookie->cdcgtmd = 0;
            }
        }

        // read cookie
        if(is_null($this->debug_enabled)) {
            if(Context::getContext()->cookie->cdcgtmd == 1) {
                $this->debug_enabled = true;
            } else {
                $this->debug_enabled = false;
            }
        }

        return $this->debug_enabled;
    }



    /**
     * Add debug message
     */
    public function addDebug($msg) {
        $this->debug_stack[] = $msg;
    }

    /**
     * Display all debug messages
     */
    public function displayDebug() {
        if(CdcTools::isXmlHttpRequest()) {
            return '';
        }

        $this->smarty->assign(array(
            'debug_stack' => $this->debug_stack
        ));
        return $this->display(__FILE__, 'views/templates/hook/debug.tpl');
    }


    /**
     * Display custom html tag in the page to mention that the hook 
     * is present
     */
    private function displayHookPresent($hookName) {
        $this->smarty->assign(array(
            'hookName' => $hookName
        ));
        echo $this->display(__FILE__, 'views/templates/hook/checkHookExists.tpl');
    }


    /**
     * set debug hooks mod on
     */
    private function debugHooksMod() {
        $this->mod_test_hooks_enabled = true;
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }


    /**
     * create missing Cdc Gtm Order Logs
     * Only create GTM order logs for orders done after the installation of the module
     */
    public function createMissingCdcGtmOrderLogs() {
        if (!Configuration::get(static::getConfigName('DATALAYER_AUTO_CREATE'))) {
            return false;
        }

        // do not lookup on the whole orders table
        $firstOrderToHandle = (int) Configuration::get(static::getConfigName('FIRST_ORDER'));
        if(empty($firstOrderToHandle)) {
            $firstOrderToHandle = (int) Db::getInstance()->getValue('SELECT max(id_order) FROM `'._DB_PREFIX_.'orders`');
            Configuration::updateValue(self::getConfigName('FIRST_ORDER'), $firstOrderToHandle);
        }


        $sql = 'SELECT o.id_order, o.id_shop, o.id_cart FROM `'._DB_PREFIX_.CdcGtmOrderLog::$definition['table'].'` cgol'
            .' RIGHT JOIN `'._DB_PREFIX_.'orders` o ON (o.id_order = cgol.id_order)'
            .' WHERE o.id_order > '.$firstOrderToHandle.' AND cgol.id_cdc_gtm_order_log IS NULL';

        // payment name filter
        $paymentNameToExclude = Configuration::get(static::getConfigName('RESEND_EXCLUDE_PAYMENT'));
        if(!empty($paymentNameToExclude)) {
            $paymentNameToExcludeArr = array_filter(explode(',', Db::getInstance()->escape($paymentNameToExclude))); // array filter removes empty

            // payment names with regex are moved to another where condition
            $paymentNameRegexToExcludeArr = array();
            foreach ($paymentNameToExcludeArr as $k => $paymentNameToExcludeItem) {
                if(strpos($paymentNameToExcludeItem, '\d+') !== false) {
                    $paymentNameRegexToExcludeArr[] = $paymentNameToExcludeItem;
                    unset($paymentNameToExcludeArr[$k]);
                }
            }

            // build sql for payment name
            $paymentNameToExcludeSql = "'".implode("','", $paymentNameToExcludeArr) . "'";
            if(!empty($paymentNameToExcludeSql)) {
                $sql .= ' AND o.payment not in ('.$paymentNameToExcludeSql.')';
            }

            // build sql for payment name with regex
            if(!empty($paymentNameRegexToExcludeArr)) {
                foreach ($paymentNameRegexToExcludeArr as $paymentNameRegexToExclude) {
                    $sql .= ' AND o.payment not REGEXP "'.$paymentNameRegexToExclude.'"';
                }
            }
        }

        $missingOrdersLogs = Db::getInstance()->executeS($sql);

        if(!empty($missingOrdersLogs)) {
            foreach ($missingOrdersLogs as $missingOrder) {
                // test if order is split, and the order is already tracked
                if(!$this->splitOrderAlreadyTracked($missingOrder['id_order'], $missingOrder['id_cart'])) {
                    cdc_googletagmanager::createGtmOrderLog($missingOrder['id_order'], $missingOrder['id_shop']);
                }
            }
        }
    }

    /**
     * recreate a missing datalayer
     */
    private function recreateOrderConfirmationFromBO()
    {
        if(Tools::getIsset('cdcGtm_createDlBo')) {
            $orderLog = new CdcGtmOrderLog(Tools::getValue('id_cdc_gtm_order_log'));
            if(Validate::isLoadedObject($orderLog) && empty($orderLog->datalayer)) {
                $order = new Order(Tools::getValue('order_id'));
                if(Validate::isLoadedObject($order)) {
                    $this->orderConfirmation($order->id_cart, true);
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * get the list of the payment names of the last orders
     * @return array
     */
    private function getPaymentNamesOfLastOrders() {
        $paymentNamesArray = array();

        $sql = 'SELECT DISTINCT(`payment`) as paymentName FROM '
            .'(select p.`payment` from `'._DB_PREFIX_.'orders` p limit 30000) p2;';

        $paymentNames = Db::getInstance()->executeS($sql);
        if(!empty($paymentNames)) {

            // Step 2: Group similar strings and identify patterns
            $patterns = [];
            foreach ($paymentNames as $paymentName) {
                $item = $paymentName['paymentName'];

                // This regex checks for strings ending in numbers
                if (preg_match('/^(.+)\s(\d+)$/', $item, $matches)) {
                    $base = bin2hex($matches[1]); // The base string without the number, we use bin2hex to avoid invalid array key
                    $patterns[$base][] = $matches[2]; // Collect all unique numbers for this base
                } else {
                    // No pattern identified, keep the string as it is
                    $patterns[$item] = $item;
                }
            }

            // Step 3: Generate final list with patterns
            foreach ($patterns as $key => $value) {
                if (is_array($value)) {
                    // If it's an array, we had multiple similar strings; replace numbers with \d+
                    // we decode with hex2bin, encoded previously with bin2hex
                    $paymentNamesArray[] = hex2bin($key) . ' \d+';
                } else {
                    // It's a unique string, add it as is
                    $paymentNamesArray[] = $value;
                }
            }
        }

        return $paymentNamesArray;
    }

    /**
     * In the case of split orders, if one order has already been tracked,
     * do not track all the others orders with the same cart id
     *
     * @param $id_order
     * @param $id_cart
     * @return bool
     */
    public function splitOrderAlreadyTracked($id_order, $id_cart)
    {
        $cartAlreadyTracked = false;
        $sqlCountSplitOrders = 'SELECT count(*) as nb_orders FROM `'._DB_PREFIX_.'orders` WHERE id_cart = '.(int) $id_cart.' AND id_order != '.(int) $id_order;
        $countSplitOrders = Db::getInstance()->getValue($sqlCountSplitOrders);
        if($countSplitOrders > 0) {
            $sisterOrdersSql = 'SELECT id_order FROM `'._DB_PREFIX_.'orders` WHERE id_cart = '.(int) $id_cart.' AND id_order != '.(int) $id_order;

            $sisterOrdersTrackedSql = 'SELECT count(*) FROM `'._DB_PREFIX_.CdcGtmOrderLog::$definition['table'].'`'
                .' WHERE id_order IN ('.$sisterOrdersSql.')';

            $sisterOrdersTracked = (int) Db::getInstance()->getValue($sisterOrdersTrackedSql);
            $cartAlreadyTracked = ($sisterOrdersTracked > 0);
        }

        return $cartAlreadyTracked;
    }

    /**
     * Backup datalayer to db
     * @param $event
     * @param $dataLayerJs
     */
    public function logDataLayerInDb($event, $dataLayerJs)
    {
        if(!empty($this->events_to_debug) ) {
            $eventsToDebugArray = explode(',', $this->events_to_debug);
            if(count($eventsToDebugArray) && in_array($event, $eventsToDebugArray)) {
                (new CdcGtmDataLayer())->logDataLayerInDb($event, $dataLayerJs);
            }
        }
    }

    protected function getController() {
        $controller = Tools::getValue('controller');

        // Allow only alphanumeric characters, dashes, and underscores
        $controller = preg_replace('/[^a-zA-Z0-9\-_]/', '', $controller);

        // Fallback to a safe default if the controller becomes empty
        if (empty($controller)) {
            $controller = 'unknown';
        }

        return $controller;
    }
}

