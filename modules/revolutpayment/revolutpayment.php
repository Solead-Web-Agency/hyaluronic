<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutApi.php';
require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutDatabaseHelper.php';
require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutPRBSettingsHelper.php';
require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutApplePayOnBoardingHelper.php';

class RevolutPayment extends PaymentModule
{
    use RevolutDatabaseHelper;

    public static $free_shipping_options = array(
        array(
            'id' => 'flat_rate:0',
            'amount' => "0",
            'description' => "",
            'label' => "SHIPPING"
        )
    );

    /**
     * RevolutPayment constructor.
     *
     * Set the information about this module
     */
    public function __construct()
    {
        $this->name = 'revolutpayment';
        $this->tab = 'payments_gateways';
        $this->version = '2.2.4';
        $this->author = 'Revolut';
        $this->controllers = array('payment', 'validation');
        $this->revolutpay_tittle = 'Revolut Pay' . ' - ' . $this->l('Get Cashback');
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        $this->module_key = '7161109d5988905e258ab64bcf4b092e';
        $this->description = 'Revolut Business’ payment gateway has the unique advantage of being integrated 
        directly with your Revolut Business account';
        $this->confirmUninstall = 'Are you sure you want to uninstall this module?';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->isPs17 = version_compare(_PS_VERSION_, '1.7', '>');
        $this->available_currency_list = array('BGN', 'AED', 'AUD', 'BHD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'ILS', 'ISK', 'JPY', 'KWD', 'MXN', 'NOK', 'NZD', 'OMR', 'PLN', 'QAR', 'RON', 'RUB', 'SAR', 'SEK', 'SGD', 'THB', 'TRY', 'UAH', 'USD', 'ZAR');
        $this->card_payments_currency_list = array('AED', 'AUD', 'BHD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ISK', 'JPY', 'KWD', 'NOK', 'NZD', 'OMR', 'PLN', 'QAR', 'RON', 'SAR', 'SEK', 'SGD', 'TRY', 'UAH', 'USD', 'ZAR');
        $this->support_link = 'https://www.revolut.com/en-HR/business/help/merchant-accounts/';
        $this->support_link .= 'payments/in-which-currencies-can-i-accept-payments';
        $this->ps_support_link = 'https://addons.prestashop.com/en/contact-us?id_product=50148';
        $payWidgetIsEnabled = false;
        $cardWidgetIsEnabled = false;
        $paymentRequestIsEnabled = false;
        $checkoutWidgetDisplayType = 1;
        $paymentDescription = '';
        $authorizeOnly = false;
        $checkoutDisplayName = 'Pay with card (via Revolut)';
        $customStatus = false;
        $autoRefunds = false;

        $apiKeyLive = Configuration::get('REVOLUT_P_APIKEY_LIVE');
        $apiKeySandbox = Configuration::get('REVOLUT_P_APIKEY');
        $isSandBoxMode = Configuration::get('REVOLUT_P_SANDBOX');

        if (Configuration::get('REVOLUT_PAY_METHOD_ENABLE') == 1
            && (($isSandBoxMode && !empty($apiKeySandbox)) || !empty($apiKeyLive))
        ) {
            $payWidgetIsEnabled = true;
        }

        if (Configuration::get('REVOLUT_CARD_METHOD_ENABLE') == 1
            && (($isSandBoxMode && !empty($apiKeySandbox)) || !empty($apiKeyLive))
        ) {
            $cardWidgetIsEnabled = true;
        }

        if (Configuration::get('REVOLUT_PRB_METHOD_ENABLE') == 1 && !empty($apiKeyLive)) {
            $paymentRequestIsEnabled = true;
        }

        if (Configuration::get('REVOLUT_P_AUTHORIZE_ONLY') == 1) {
            $authorizeOnly = true;
        }

        if (Configuration::get('REVOLUT_P_WIDGET_TYPE') != null) {
            $checkoutWidgetDisplayType = Configuration::get('REVOLUT_P_WIDGET_TYPE');
        }
        if (Configuration::get('REVOLUT_P_CUSTOM_STATUS') == 1) {
            $customStatus = true;
        }
        if (Configuration::get('REVOLUT_P_AUTO_REFUNDS') == 1) {
            $autoRefunds = true;
        }

        $mode = $isSandBoxMode ? 'sandbox' : 'live';

        $this->revolutApi = new RevolutApi($mode, $apiKeyLive, $apiKeySandbox, $this->version);
        $this->payWidgetIsEnabled = $payWidgetIsEnabled;
        $this->cardWidgetIsEnabled = $cardWidgetIsEnabled;
        $this->paymentRequestIsEnabled = $paymentRequestIsEnabled;
        $this->moduleEnable = $this->payWidgetIsEnabled || $this->cardWidgetIsEnabled;
        $this->authorizeOnly = $authorizeOnly;
        $this->displayName = 'Revolut';
        $this->checkoutWidgetDisplayType = $checkoutWidgetDisplayType;
        $this->customStatus = $customStatus;
        $this->autoRefunds = $autoRefunds;
        $this->isSandBoxMode = $isSandBoxMode;

        $this->payment_widget_types = array(
            1 => $this->l('Direct'),
            2 => $this->l('Payment Page'),
            3 => $this->l('Popup'),
        );

        parent::__construct();

        if (Configuration::get('REVOLUT_P_DESCRIPTION_' . $this->context->language->id) != null) {
            $paymentDescription = Configuration::get('REVOLUT_P_DESCRIPTION_' . $this->context->language->id);
        }

        if (Configuration::get('REVOLUT_P_TITLE_' . $this->context->language->id) != null) {
            $checkoutDisplayName = Configuration::get('REVOLUT_P_TITLE_' . $this->context->language->id);
        }

        $this->checkoutDisplayName = $checkoutDisplayName;
        $this->paymentDescription = $paymentDescription;
        $this->prbSettingsHelper = new RevolutPRBSettingsHelper($this);

        //check required setups
        $this->installDb();
        $this->installOrderState();
    }

    /**
     * Install this module and register the following Hooks:
     *
     * @return bool
     */
    public function install()
    {
        // make default configuration
        $default_revolut_title = $this->l('Pay with card (via Revolut)');
        $default_revolut_description = $this->l('Pay with your credit or debit card');
        $languages = Language::getLanguages();

        foreach ($languages as $language) {
            if (Configuration::get('REVOLUT_P_TITLE_' . $language['id_lang']) == null) {
                Configuration::updateValue('REVOLUT_P_TITLE_' . $language['id_lang'], $default_revolut_title);
            }
            if (Configuration::get('REVOLUT_P_DESCRIPTION_' . $language['id_lang']) == null) {
                Configuration::updateValue('REVOLUT_P_DESCRIPTION_' . $language['id_lang'], $default_revolut_description);
            }
        }

        if (Configuration::get('REVOLUT_PAY_METHOD_ENABLE') == null) {
            Configuration::updateValue('REVOLUT_PAY_METHOD_ENABLE', 1);
        }

        if (Configuration::get('REVOLUT_CARD_METHOD_ENABLE') == null) {
            Configuration::updateValue('REVOLUT_CARD_METHOD_ENABLE', 1);
        }

        $default_revolut_widget_type = 1; // stands for Direct option
        if (Configuration::get('REVOLUT_P_WIDGET_TYPE') == null) {
            Configuration::updateValue('REVOLUT_P_WIDGET_TYPE', $default_revolut_widget_type);
        }

        $default_revolut_custom_status = 1;
        if (Configuration::get('REVOLUT_P_CUSTOM_STATUS') == null) {
            Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS', $default_revolut_custom_status);
        }

        $default_revolut_auto_refund = 0;
        if (Configuration::get('REVOLUT_P_AUTO_REFUNDS') == null) {
            Configuration::updateValue('REVOLUT_P_AUTO_REFUNDS', $default_revolut_auto_refund);
        }
        $default_revolut_custom_status_refund = Configuration::get('PS_OS_REFUND');
        if (Configuration::get('REVOLUT_P_CUSTOM_STATUS_REFUND') == null) {
            Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS_REFUND', $default_revolut_custom_status_refund);
        }
        $default_revolut_custom_status_capture = Configuration::get('PS_OS_PAYMENT');
        if (Configuration::get('REVOLUT_P_CUSTOM_STATUS_CAPTURE') == null) {
            Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS_CAPTURE', $default_revolut_custom_status_capture);
        }

        $default_revolut_webhook_status_authorised = Configuration::get('PS_OS_PAYMENT');
        if (Configuration::get('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED') == null) {
            Configuration::updateValue(
                'REVOLUT_P_WEBHOOK_STATUS_AUTHORISED',
                $default_revolut_webhook_status_authorised
            );
        }

        return parent::install()
            && $this->installOrderState()
            && $this->registerHook('header')
            && $this->registerHook('paymentReturn')
            && $this->registerHook('actionOrderStatusUpdate')
            && $this->registerHook('actionOrderSlipAdd')
            && $this->registerHook('actionProductCancel')
            && $this->registerHook('actionRevolutWebhook')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('payment')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('displayRightColumnProduct')
            && $this->registerHook('displayExpressCheckout')
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->installDb();
    }

    public function initAutomaticSetup()
    {
        //init api again with updated settings
        $apiKeyLive = Configuration::get('REVOLUT_P_APIKEY_LIVE');
        $apiKeySandbox = Configuration::get('REVOLUT_P_APIKEY');
        $isSandBoxMode = Configuration::get('REVOLUT_P_SANDBOX');

        $mode = $isSandBoxMode ? 'sandbox' : 'live';
        $this->revolutApi = new RevolutApi($mode, $apiKeyLive, $apiKeySandbox, $this->version);

        //try to automatically setup Apply pay domain
        new RevolutApplePayOnBoardingHelper($this);
        $this->setWebHook();
    }

    public function setWebHook()
    {
        $default_revolut_webhook_url = Context::getContext()->link->getModuleLink('revolutpayment', 'webhook', array());
        $res = $this->revolutApi->setRevolutWebhookUrl($default_revolut_webhook_url);
        if ($res) {
            $this->setSuccessMessage($this->l('Webhook Setup is successful.'));
            return true;
        }

        $this->setErrorMessage($this->l('Can not setup Revolut webhook url.'), []);
        return false;
    }

    /**
     * Create Revolut orders table
     *
     * @return bool
     */
    public function installDb()
    {
        $result = Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'revolut_payment_orders` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`id_order` INT NULL ,
				`id_cart` INT NULL ,
				`id_revolut_order` VARCHAR(250) NOT NULL ,
				`public_id` VARCHAR(250) NOT NULL ,
				`save_card` TINYINT NOT NULL DEFAULT "0" ,
				PRIMARY KEY (`id`),
				UNIQUE (`id_revolut_order`),
				UNIQUE (`id_order`)
			) ENGINE = ' . _MYSQL_ENGINE_ . ';'
        );

        $result &= Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'revolut_cards` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`customer_id` INT NOT NULL ,
				`card_number` VARCHAR(25) NOT NULL,
				`expire_month` INT NOT NULL ,
				`expire_year` INT NOT NULL ,
				PRIMARY KEY (`id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ';'
        );

        //update module version, this will indicate that module tables are updated during update
        Configuration::updateValue('REVOLUT_MODULE_VERSION', $this->version);

        return $result;
    }

    /**
     * Uninstall this module and remove it from all hooks
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Create order state
     *
     * @return boolean
     */
    public function installOrderState()
    {
        if (!Configuration::get('REVOLUT_OS_WAITING')) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'En attente de paiement Revolut';
                } else {
                    $order_state->name[$language['id_lang']] = 'Awaiting Revolut payment';
                }
            }
            $order_state->send_email = false;
            $order_state->color = '#4169E1';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            $order_state->module_name = $this->name;
            if ($order_state->add()) {
                $source = _PS_MODULE_DIR_ . 'revolutpayment/logo.png';
                $destination = _PS_ROOT_DIR_ . '/img/os/' . (int)$order_state->id . '.gif';
                copy($source, $destination);
            }

            if (Shop::isFeatureActive()) {
                $shops = Shop::getShops();
                foreach ($shops as $shop) {
                    Configuration::updateValue('REVOLUT_OS_WAITING', (int)$order_state->id, false, null, (int)$shop['id_shop']);
                }
            } else {
                Configuration::updateValue('REVOLUT_OS_WAITING', (int)$order_state->id);
            }
        }

        return true;
    }

    /**
     * Returns a string containing the HTML necessary to
     * generate a configuration screen on the admin
     *
     * @return string
     */
    public function getContent()
    {
        $this->prbSettingsHelper->processPRBSettings();
        $this->postProcess();
        $this->initAutomaticSetup();

        return $this->displayRevolutSettingsPage();
    }

    public function postProcess()
    {
        if (!Tools::isSubmit('submit' . $this->name)) {
            return false;
        }

        $payWidgetIsEnabled = (string)Tools::getValue('REVOLUT_PAY_METHOD_ENABLE');
        $cardWidgetIsEnabled = (string)Tools::getValue('REVOLUT_CARD_METHOD_ENABLE');
        $revolutSandbox = (string)Tools::getValue('REVOLUT_P_SANDBOX');
        $revolutAuthorizeOnly = (string)Tools::getValue('REVOLUT_P_AUTHORIZE_ONLY');
        $revolutApiKey = (string)Tools::getValue('REVOLUT_P_APIKEY');
        $revolutApiKeyLive = (string)Tools::getValue('REVOLUT_P_APIKEY_LIVE');
        $revolutWidgetType = (int)Tools::getValue('REVOLUT_P_WIDGET_TYPE');
        $revolutCustomStatus = (string)Tools::getValue('REVOLUT_P_CUSTOM_STATUS');
        $revolutAutoRefunds = (string)Tools::getValue('REVOLUT_P_AUTO_REFUNDS');
        $revolutCustomStatusRefund = (string)Tools::getValue('REVOLUT_P_CUSTOM_STATUS_REFUND');
        $revolutCustomStatusCapture = (string)Tools::getValue('REVOLUT_P_CUSTOM_STATUS_CAPTURE');
        $revolutWebhookStatusAuthorised = (string)Tools::getValue('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED');

        $languages = Language::getLanguages();
        $checkTitleIsEmpty = false;

        foreach ($languages as $language) {
            $title = Tools::getValue('REVOLUT_P_TITLE_' . $language['id_lang']);
            $description = Tools::getValue('REVOLUT_P_DESCRIPTION_' . $language['id_lang']);
            if (empty($title) && !$checkTitleIsEmpty) {
                $checkTitleIsEmpty = true;
            }

            Configuration::updateValue('REVOLUT_P_TITLE_' . $language['id_lang'], $title);
            Configuration::updateValue('REVOLUT_P_DESCRIPTION_' . $language['id_lang'], $description);
        }

        if ($checkTitleIsEmpty) {
            $this->setErrorMessage($this->l('Title is required.'), []);
        }

        Configuration::updateValue('REVOLUT_PAY_METHOD_ENABLE', $payWidgetIsEnabled);
        Configuration::updateValue('REVOLUT_CARD_METHOD_ENABLE', $cardWidgetIsEnabled);
        Configuration::updateValue('REVOLUT_P_SANDBOX', $revolutSandbox);
        Configuration::updateValue('REVOLUT_P_AUTHORIZE_ONLY', $revolutAuthorizeOnly);
        Configuration::updateValue('REVOLUT_P_APIKEY', $revolutApiKey);
        Configuration::updateValue('REVOLUT_P_APIKEY_LIVE', $revolutApiKeyLive);
        Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS', $revolutCustomStatus);
        Configuration::updateValue('REVOLUT_P_AUTO_REFUNDS', $revolutAutoRefunds);
        Configuration::updateValue('REVOLUT_P_WIDGET_TYPE', $revolutWidgetType);
        Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS_REFUND', $revolutCustomStatusRefund);
        Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS_CAPTURE', $revolutCustomStatusCapture);
        Configuration::updateValue('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED', $revolutWebhookStatusAuthorised);

        $this->setSuccessMessage($this->l('Settings updated.'));
    }

    /**
     * Returns configuration form
     * generate a configuration screen on the admin
     *
     * @return html
     */
    public function displayRevolutSettingsPage()
    {
        $this->context->controller->addCSS($this->_path . '/views/css/select2.min.css');
        $this->context->controller->addJS($this->_path . '/views/js/select2.min.js');

        $this->context->controller->addCSS($this->_path . '/views/css/admin.css');
        $this->context->controller->addJS($this->_path . '/views/js/admin.js');
        $languages = Language::getLanguages();
        $id_current_lang = $this->context->language->id;
        $order_statuses = $this->getOrderStatuses();

        $REVOLUT_P_TITLE = array();
        $REVOLUT_P_DESCRIPTION = array();

        foreach ($languages as $language) {
            $REVOLUT_P_TITLE[$language['id_lang']] = Configuration::get('REVOLUT_P_TITLE_' . $language['id_lang']);
            $REVOLUT_P_DESCRIPTION[$language['id_lang']] = Configuration::get('REVOLUT_P_DESCRIPTION_' . $language['id_lang']);
        }

        $this->context->smarty->assign(
            array(
                'REVOLUT_P_TITLE' => $REVOLUT_P_TITLE,
                'REVOLUT_P_DESCRIPTION' => $REVOLUT_P_DESCRIPTION,
                'REVOLUT_PAY_METHOD_ENABLE' => Configuration::get('REVOLUT_PAY_METHOD_ENABLE'),
                'REVOLUT_CARD_METHOD_ENABLE' => Configuration::get('REVOLUT_CARD_METHOD_ENABLE'),
                'REVOLUT_P_SANDBOX' => Configuration::get('REVOLUT_P_SANDBOX'),
                'REVOLUT_P_AUTHORIZE_ONLY' => Configuration::get('REVOLUT_P_AUTHORIZE_ONLY'),
                'REVOLUT_P_APIKEY' => Configuration::get('REVOLUT_P_APIKEY'),
                'REVOLUT_P_APIKEY_LIVE' => Configuration::get('REVOLUT_P_APIKEY_LIVE'),
                'REVOLUT_P_AUTO_REFUNDS' => Configuration::get('REVOLUT_P_AUTO_REFUNDS'),
                'REVOLUT_P_CUSTOM_STATUS' => Configuration::get('REVOLUT_P_CUSTOM_STATUS'),
                'REVOLUT_P_CUSTOM_STATUS_REFUND' => Configuration::get('REVOLUT_P_CUSTOM_STATUS_REFUND'),
                'REVOLUT_P_CUSTOM_STATUS_CAPTURE' => Configuration::get('REVOLUT_P_CUSTOM_STATUS_CAPTURE'),
                'REVOLUT_P_WEBHOOK_STATUS_AUTHORISED' => Configuration::get('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED'),
                'REVOLUT_P_WIDGET_TYPE' => Configuration::get('REVOLUT_P_WIDGET_TYPE'),
                'REVOLUT_PRB_LOCATIONS' => Tools::jsonEncode(explode(',', Configuration::get('REVOLUT_PRB_LOCATION_VALUES'))),
                'languages' => $languages,
                'id_current_lang' => $id_current_lang,
                'order_statuses' => $order_statuses,
                'prb_settings_form' => $this->prbSettingsHelper->renderPRBSettingsForm(),
                'payment_widget_types' => $this->payment_widget_types,
                'isPs17' => $this->isPs17,
                'section' => Tools::getValue('section'),
                'module_dir' => _MODULE_DIR_
            )
        );

        return $this->display(__FILE__, '/views/templates/admin/configuration.tpl');
    }

    /**
     * Get order statuses
     *
     * @param
     * @return array|void
     */
    public function getOrderStatuses()
    {
        $order_statuses = array(
            array(
                'id' => 0,
                'name' => $this->l('Choose status')
            )
        );
        $prestashop_order_statuses = OrderState::getOrderStates($this->context->language->id);

        foreach ($prestashop_order_statuses as $prestashop_order_status) {
            $order_statuses[] = array(
                'id' => $prestashop_order_status['id_order_state'],
                'name' => $prestashop_order_status['name']
            );
        }

        return $order_statuses;
    }

    /**
     * Add css & js to head
     *
     * @param
     * @return array|void
     */
    public function hookHeader()
    {
        $this->context->controller->addJquery();

        Media::addJsDef(
            array(
                'cardWidgetIsEnabled' => $this->cardWidgetIsEnabled,
                'payWidgetIsEnabled' => $this->payWidgetIsEnabled,
                'checkoutWidgetDisplayType' => $this->checkoutWidgetDisplayType
            )
        );

        if ($this->isPs17) {
            $this->hookHeaderV17();
        } else {
            $this->hookHeaderV16();
        }
    }

    public function deactivateUnknownAddresses()
    {
        $id_customer = 0;

        if (isset($this->context->cart->id_customer) && !empty($this->context->cart->id_customer)) {
            $id_customer = $this->context->cart->id_customer;
        }

        if (empty($id_customer) && isset($this->context->customer->id) && !empty($this->context->customer->id)) {
            $id_customer = $this->context->customer->id;
        }

        if (empty($id_customer)) {
            return false;
        }

        $id_addresses = Db::getInstance()->executeS('SELECT id_address FROM `' . _DB_PREFIX_ . 'address` WHERE firstname="unknown" AND lastname="unknown" AND alias="unknown" AND address1="unknown" AND id_customer=' . (int)$id_customer);

        if (empty($id_addresses)) {
            return false;
        }

        foreach ($id_addresses as $id_address) {
            $this->context->cart->updateAddressId(
                (int)$id_address['id_address'],
                Address::getFirstCustomerAddressId($id_customer)
            );
        }

        foreach ($this->context->cart->getProducts() as &$product) {
            $this->context->cart->setProductAddressDelivery(
                $product['id_product'],
                $product['id_product_attribute'],
                $product['id_address_delivery'],
                $this->context->cart->id_address_delivery
            );
        }

        return Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'address` set active = 0, deleted = 1 WHERE firstname="unknown" AND lastname="unknown" AND alias="unknown" AND address1="unknown" AND id_customer=' . (int)$id_customer);
    }

    public function hookHeaderV17()
    {
        if ($this->context->controller->php_self == 'product' || $this->context->controller->php_self == 'cart') {
            $this->context->controller->registerJavascript(
                'revolut-js',
                $this->revolutApi->api_base_url . '/embed.js',
                array('server' => 'remote', 'position' => 'bottom', 'priority' => 1)
            );

            $this->context->controller->registerJavascript(
                $this->name . '-blockUI',
                'modules/' . $this->name . '/views/js/jquery.blockUI.js'
            );

            $this->context->controller->registerJavascript(
                $this->name . '-library',
                'modules/' . $this->name . '/views/js/revolut.min.js'
            );

            $this->context->controller->registerJavascript(
                $this->name . '-payment-request',
                'modules/' . $this->name . '/views/js/version17/revolut.payment.request.js'
            );
            $this->context->controller->registerStylesheet(
                $this->name . 'blockui-style',
                'modules/' . $this->name . '/views/css/blockui.css'
            );
        }

        if ($this->context->controller->php_self == 'order' || Tools::getValue('module') == 'revolutpayment') {
            $logoPath = _MODULE_DIR_ . $this->name . '/views/img/';
            $visa_mc_logo = $logoPath . 'visa_mc.png';
            $revpay_logo = $logoPath . 'r-pay.svg';

            Media::addJsDef(
                array(
                    'visa_mc_logo' => $visa_mc_logo,
                    'revpay_logo' => $revpay_logo,
                    'logo_path' => $logoPath,
                )
            );
            
            $this->context->controller->registerJavascript(
                'revolut-js',
                $this->revolutApi->api_base_url . '/embed.js',
                array('server' => 'remote', 'position' => 'bottom', 'priority' => 1)
            );

            $this->context->controller->registerJavascript(
                $this->name . '-blockUI',
                'modules/' . $this->name . '/views/js/jquery.blockUI.js'
            );

            $this->context->controller->registerStylesheet(
                $this->name . 'blockui-style',
                'modules/' . $this->name . '/views/css/blockui.css'
            );

            $this->context->controller->registerStylesheet(
                $this->name . '-style',
                'modules/' . $this->name . '/views/css/revolut.css'
            );

            $paymentJsPath = '/views/js/version17/revolut.payment.js';

            $this->context->controller->registerJavascript(
                $this->name . '-payment',
                'modules/' . $this->name . $paymentJsPath
            );
        }
    }

    public function hookHeaderV16()
    {
        if ($this->context->controller->php_self == 'product' || $this->context->controller->php_self == 'cart') {
            $this->context->controller->addJs($this->revolutApi->api_base_url . '/embed.js', false);
            $this->context->controller->addCSS($this->_path . '/views/css/blockui.css');
            $this->context->controller->addJs($this->_path . '/views/js/revolut.min.js');
            $this->context->controller->addJs($this->_path . '/views/js/jquery.blockUI.js');
            $this->context->controller->addJs($this->_path . '/views/js/version16/revolut.payment.request.js');
        }

        if ($this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'order-opc'
            || Tools::getValue('module') == 'revolutpayment'
        ) {
            Media::addJsDef(
                array(
                    'revolut_locale' => $this->context->language->iso_code
                )
            );

            $this->context->controller->addJs($this->revolutApi->api_base_url . '/embed.js', false);
            $this->context->controller->addCSS($this->_path . '/views/css/blockui.css');
            $this->context->controller->addCSS($this->_path . '/views/css/revolut_v16.css');
            $this->context->controller->addJs($this->_path . '/views/js/jquery.blockUI.js');
            $this->context->controller->addJs($this->_path . '/views/js/version16/revolut.payment.request.js');
            $this->context->controller->addJs($this->_path . '/views/js/version16/revolut.payment.js');
        }
    }

    /**
     * Display this module as a payment option during the checkout for PS version 1.6
     *
     * @param array $params
     * @return string
     */
    public function hookPayment($params)
    {
        /*
         * Verify if this module is active
         */
        if (!$this->active || !$this->moduleEnable) {
            return;
        }

        // create revolut order
        $public_id = $this->createRevolutOrder();

        $this->context->smarty->assign(
            [
                'currency_error' => false,
                'revolut_order_public_id_error' => false,
                'ps_support_link' => $this->ps_support_link,
            ]
        );

        $has_error = false;
        if ($public_id == '') {
            $this->context->smarty->assign(
                [
                    'revolut_order_public_id_error' => true,
                    'ps_support_link' => $this->ps_support_link,
                ]
            );
            $has_error = true;
        }

        if (!$this->checkCurrencySupport()) {
            $this->context->smarty->assign(
                [
                    'currency_error' => true,
                    'selected_currency' => $this->context->currency->iso_code,
                    'support_link' => $this->support_link,
                ]
            );
            $has_error = true;
        }

        $merchant_public_key = $this->getMerchantPublicToken();
        $is_rev_pay_v2 = (int)(!empty($merchant_public_key));

        // address data
        $customer_name = $this->context->customer->firstname . ' ' . $this->context->customer->lastname;
        $customer_email = $this->context->customer->email;
        $address = new Address($this->context->cart->id_address_delivery);
        $phone_number = $address->phone_mobile;
        if (empty($phone_number)) {
            $phone_number = $address->phone;
        }
        $state = new State($address->id_state);
        $country = new Country($address->id_country);
        $cart = $this->context->cart;
        $paymentPageLink = $this->context->link->getModuleLink($this->name, 'payment', array(), true);
        $card_widget_currency_error = !in_array($this->context->currency->iso_code, $this->card_payments_currency_list);

        $this->context->smarty->assign(
            [
                'card_widget_currency_error' => $card_widget_currency_error,
                'selected_currency' => $this->context->currency->iso_code,
                'nbProducts' => $cart->nbProducts(),
                'total' => $cart->getOrderTotal(true, Cart::BOTH),
                'action_link' => Context::getContext()->link->getModuleLink($this->name, 'validation', array()),
                'action' => Context::getContext()->link->getModuleLink($this->name, 'validation', array()),
                'formActionValidation' => Context::getContext()->link->getModuleLink($this->name, 'validation', array()),
                'revolutpayment_include_path' => _PS_MODULE_DIR_ . $this->name . '/',
                'revolutpayment_path' => $this->getPathUri(),
                'public_id' => $public_id,
                'merchant_public_key' => $merchant_public_key,
                'is_rev_pay_v2' => $is_rev_pay_v2,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'paymentPageLink' => $paymentPageLink,
                'address' => $address,
                'state' => $state,
                'country' => $country,
                'revolut_payment_title' => $this->checkoutDisplayName,
                'merchant_type' => $this->revolutApi->mode,
                'payment_description' => $this->paymentDescription,
                'locale' => $this->context->language->iso_code,
                'phone_number' => $phone_number,
                'cardWidgetIsEnabled' => $this->cardWidgetIsEnabled,
                'payWidgetIsEnabled' => $this->payWidgetIsEnabled,
                'checkoutWidgetDisplayType' => $this->checkoutWidgetDisplayType,
                'order_total_amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code),
                'shipping_amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING), $this->context->currency->iso_code),
            ]
        );

        if ($this->checkoutWidgetDisplayType != 1 || $has_error) {
            return $this->display(__FILE__, 'views/templates/hook/version16/payment_page_button.tpl');
        }

        return $this->display(__FILE__, 'views/templates/hook/version16/direct_payment.tpl');
    }

    public function getPathUri()
    {
        return parent::getPathUri();
    }

    /**
     * Display this module as a payment option during the checkout for PS version 1.7
     *
     * @param array $params
     * @return array|void
     */
    public function hookPaymentOptions($params)
    {
        /*
         * Verify if this module is active
         */
        if (!$this->active || !$this->moduleEnable) {
            return;
        }

        /**
         * Form action URL. The form data will be sent to the
         * validation controller when the user finishes
         * the order process.
         */
        $formActionValidation = $this->context->link->getModuleLink($this->name, 'validation', array(), true);
        $formActionPayment = $this->context->link->getModuleLink($this->name, 'payment', array(), true);

        $has_error = false;

        $this->context->smarty->assign(
            [
                'currency_error' => false,
                'revolut_order_public_id_error' => false,
                'ps_support_link' => $this->ps_support_link,
            ]
        );

        if (!$this->checkCurrencySupport()) {
            $this->context->smarty->assign(
                [
                    'currency_error' => true,
                    'selected_currency' => $this->context->currency->iso_code,
                    'support_link' => $this->support_link,
                ]
            );
            $has_error = true;
        }

        // create revolut order
        $public_id = $this->createRevolutOrder();
        if ($public_id == '') {
            $this->context->smarty->assign(
                [
                    'revolut_order_public_id_error' => true,
                    'ps_support_link' => $this->ps_support_link,
                ]
            );
            $has_error = true;
        }

        $merchant_public_key = $this->getMerchantPublicToken();
        $is_rev_pay_v2 = (int)(!empty($merchant_public_key));
        
        if (Tools::usingSecureMode()) {
            $domain = Tools::getShopDomainSsl(true, true);
        } else {
            $domain = Tools::getShopDomain(true, true);
        }

        // address data
        $customer_name = $this->context->customer->firstname . ' ' . $this->context->customer->lastname;
        $customer_email = $this->context->customer->email;
        $address = new Address($this->context->cart->id_address_delivery);
        $phone_number = $address->phone_mobile;
        if (empty($phone_number)) {
            $phone_number = $address->phone;
        }

        $state = new State($address->id_state);
        $country = new Country($address->id_country);
        $logo = $domain . __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views/img/visa_mc.png';

        $card_widget_currency_error = !in_array($this->context->currency->iso_code, $this->card_payments_currency_list);

        $this->context->smarty->assign(
            array(
                'card_widget_currency_error' => $card_widget_currency_error,
                'selected_currency' => $this->context->currency->iso_code,
                'order_total_amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code),
                'shipping_amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING), $this->context->currency->iso_code),
                'merchant_type' => $this->revolutApi->mode,
                'payment_description' => $this->paymentDescription,
                'revolut_payment_title' => $this->checkoutDisplayName,
                'phone_number' => $phone_number,
                'logo' => $logo,
                'country' => $country,
                'state' => $state,
                'address' => $address,
                'customer_email' => $customer_email,
                'customer_name' => $customer_name,
                'merchant_public_key' => $merchant_public_key,
                'public_id' => $public_id,
                'is_rev_pay_v2' => $is_rev_pay_v2,
                'formActionValidation' => $formActionValidation,
                'formActionPayment' => $formActionPayment,
                'locale' => $this->context->language->iso_code,
            )
        );

        /**
         *  Load form template to be displayed in the checkout step
         */
        $revolutCartPaymentForm = $this->fetch('module:revolutpayment/views/templates/hook/version17/direct_payment.tpl');
        $revolutPayPaymentForm = $this->fetch('module:revolutpayment/views/templates/hook/revolut_pay_button.tpl');

        /**
         * Create a PaymentOption object containing the necessary data
         * to display this module in the checkout
         */
        $revolutCardPaymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $revolutCardPaymentOption->setModuleName($this->checkoutDisplayName)
            ->setCallToActionText($this->checkoutDisplayName)
            ->setLogo(_MODULE_DIR_ . $this->name . '/views/img/visa_mc.png');

        if ($this->checkoutWidgetDisplayType != 2 || $has_error) {
            $revolutCardPaymentOption->setForm($revolutCartPaymentForm);
        }

        if ($this->checkoutWidgetDisplayType == 2) {
            $revolutCardPaymentOption->setAction($formActionPayment);
        } else {
            $revolutCardPaymentOption->setAction($formActionValidation);
        }

        $revolutPayPaymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $revolutPayPaymentOption->setModuleName($this->revolutpay_tittle)
            ->setCallToActionText($this->revolutpay_tittle)
            ->setLogo(_MODULE_DIR_ . $this->name . '/views/img/r-pay.svg')
            ->setAction($formActionValidation)
            ->setForm($revolutPayPaymentForm);

        $payment_options = array();

        if ($this->cardWidgetIsEnabled) {
            $payment_options[] = $revolutCardPaymentOption;
        }

        if ($this->payWidgetIsEnabled) {
            $payment_options[] = $revolutPayPaymentOption;
        }

        return $payment_options;
    }

    /**
     * Display a message in the paymentReturn hook
     *
     * @param array $params
     * @return string
     */
    public function hookPaymentReturn($params)
    {
        /**
         * Verify if this module is enabled
         */
        if (!$this->active || !$this->moduleEnable) {
            return;
        }

        if ($this->isPs17) {
            // 1.7 version has a detailed confimation page there is no need for confirmation message
            //            return $this->fetch('module:revolutpayment/views/templates/hook/payment_return.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
        }
    }

    /**
     * Hook actionOrderStatusUpdate
     *
     * @param
     * @return array|void
     */
    public function hookActionOrderStatusUpdate($hook_data)
    {
        /**
         * Verify if this module is enabled
         */
        if (!$this->active || !$this->moduleEnable || !$this->customStatus) {
            return;
        }

        $new_order_status_id = $hook_data['newOrderStatus']->id;
        $id_order = $hook_data['id_order'];
        $order = new Order((int)$id_order);

        $capture_revolut_order = false;
        $refund_revolut_order = false;
        $cancel_revolut_order = false;

        if ($new_order_status_id == Configuration::get('REVOLUT_P_CUSTOM_STATUS_CAPTURE')) {
            $capture_revolut_order = true;
        }
        if ($new_order_status_id == Configuration::get('REVOLUT_P_CUSTOM_STATUS_REFUND')) {
            $refund_revolut_order = true;
        }

        if ($new_order_status_id == Configuration::get('PS_OS_CANCELED')) {
            $cancel_revolut_order = true;
        }

        if (!$capture_revolut_order && !$refund_revolut_order && !$cancel_revolut_order) {
            return;
        }

        $revolut_order = $this->getRevolutOrder($id_order);

        if (!empty($revolut_order['id_revolut_order'])) {
            // capture revolut order
            if ($capture_revolut_order) { // capture no update ps order status
                $result = $this->revolutApi->captureRevolutOrder($revolut_order['id_revolut_order']);

                if (!$result) {
                    $this->setErrorMessage($this->l('An error occured while capturing the Revolut order.'), $result);
                } else {
                    $update_params = array(
                        'merchant_order_id' => $revolut_order['id_order']
                    );
                    $this->revolutApi->updateRevolutOrder($revolut_order['id_revolut_order'], $update_params);
                }
            }

            if ($cancel_revolut_order) {
                $result = $this->revolutApi->cancelRevolutOrder($revolut_order['id_revolut_order']); // cancel order
                if (empty($result['public_id'])) {
                    $this->setErrorMessage($this->l('An error occured while canceling the Revolut order.'), $result);
                }
            }

            // refund revolut order
            if ($refund_revolut_order && $this->autoRefunds) {
                // check existed partial refund
                $order_slips = OrderSlip::getOrdersSlip($order->id_customer, $id_order);

                $total_refunded_shipping = 0;
                $total_refunded_products = 0;

                if (count($order_slips)) {
                    foreach ($order_slips as $order_slip) {
                        $total_refunded_shipping += $order_slip['total_shipping_tax_incl'];
                        $total_refunded_products += $order_slip['total_products_tax_incl'];
                    }
                }

                // when request repeat, order slip can be duplicated
                if ($total_refunded_shipping > $order->total_shipping) {
                    $total_refunded_shipping = $order->total_shipping;
                }

                $total_refunded = $total_refunded_shipping + $total_refunded_products;
                $total_refundable = $order->total_paid - $total_refunded;

                if ($total_refundable > 0) {
                    $params = array(
                        'amount' => $this->createRevolutAmount($total_refundable, $this->context->currency->iso_code),
                        'currency' => $this->context->currency->iso_code,
                        'merchant_order_id' => $id_order,
                        'description' => 'Prestashop order status change to Refunded'
                    );
                    $result = $this->revolutApi->refundRevolutOrder($revolut_order['id_revolut_order'], $params);
                    if (empty($result['public_id'])) {
                        $this->setErrorMessage($this->l('An error occured while refunding the Revolut order.'), $result);
                    }
                }
            }
        }
    }

    public function setErrorMessage($errorMessage, $errorDetail)
    {
        if (!isset($this->context->controller->errors)) {
            return;
        }

        if (isset($errorDetail['errorId']) && !empty($errorDetail['errorId'])) {
            $errorMessage .= 'Error ID: ' . $errorDetail['errorId'];
        }

        if (isset($errorDetail['errorMsg']) && !empty($errorDetail['errorMsg'])) {
            $errorMessage .= 'Error ID: ' . $errorDetail['errorMsg'];
        }

        $this->context->controller->errors[] = $errorMessage;
    }

    public function setSuccessMessage($message)
    {
        if (!isset($this->context->controller->confirmations)) {
            return;
        }

        $this->context->controller->confirmations[] = $message;
    }

    /**
     * Hook actionOrderSlipAdd
     *
     * @param
     * @return array|void
     */
    public function hookActionOrderSlipAdd($hook_data)
    {
        if (!$this->active || !$this->moduleEnable || !$this->autoRefunds) {
            return;
        }

        // for refund shipping
        $shipping_cost_amount = 0;
        if (Tools::getIsset(Tools::getValue('partialRefundShippingCost'))
            && Tools::getValue('partialRefundShippingCost') > 0
        ) {
            $shipping_cost_amount = Tools::getValue('partialRefundShippingCost');
        }

        if (count($hook_data['productList']) == 0 && $shipping_cost_amount == 0) {
            return;
        }

        // refund products
        $id_order = $hook_data['order']->id;
        $amount = 0;
        foreach ($hook_data['productList'] as $product) {
            $amount += $product['amount'];
        }

        // refund shipping
        if ($shipping_cost_amount > 0) {
            if (!Tools::getValue('TaxMethod')) {
                $tax = new Tax();
                $tax->rate = $hook_data['order']->carrier_tax_rate;
                $tax_calculator = new TaxCalculator(array($tax));
                $amount += $tax_calculator->addTaxes($shipping_cost_amount);
            } else {
                $amount += $shipping_cost_amount;
            }
        }

        $revolut_order = $this->getRevolutOrder($id_order);
        if (isset($revolut_order['id_revolut_order']) && $revolut_order['id_revolut_order'] != '') {
            $params = array(
                'amount' => $this->createRevolutAmount($amount, $this->context->currency->iso_code),
                'currency' => $this->context->currency->iso_code,
                'merchant_order_id' => $id_order,
                'description' => 'Prestashop order partially refund'
            );
            $result = $this->revolutApi->refundRevolutOrder($revolut_order['id_revolut_order'], $params);
            if (empty($result['public_id'])) {
                $this->setErrorMessage($this->l('An error occured while refunding the Revolut order.'), $result);
            }
        }
    }

    /**
     * Hook hookActionFrontControllerSetMedia
     *
     * @param
     * @return void
     */
    public function hookActionFrontControllerSetMedia()
    {
        $this->deactivateUnknownAddresses();
    }

    /**
     * Hook hookActionProductCancel
     *
     * @param
     * @return array|void
     */
    public function hookActionProductCancel($hook_data)
    {
        if (!$this->active || !$this->moduleEnable || !$this->autoRefunds) {
            return;
        }

        if (Tools::getIsset($_POST) && count($_POST) > 0) {
            $id_order = Tools::getValue('id_order');
            $quantity = 0;
            $price = 0;

            if (Tools::getIsset(Tools::getValue('cancelQuantity')) && count(Tools::getValue('cancelQuantity')) > 0) {
                foreach (Tools::getValue('cancelQuantity') as $cancel_quantity) {
                    $quantity += $cancel_quantity;
                }
            }
            if (Tools::getIsset($_POST['product_price_tax_incl'])) {
                $price = Tools::getValue('product_price_tax_incl');
            }

            $refund_amount = $quantity * $price;

            if ($refund_amount > 0) {
                // do refund
                $revolut_order = $this->getRevolutOrder($id_order);
                if (isset($revolut_order['id_revolut_order']) && $revolut_order['id_revolut_order'] != '') {
                    $params = array(
                        'amount' => $this->createRevolutAmount($refund_amount, $this->context->currency->iso_code),
                        'currency' => $this->context->currency->iso_code,
                        'merchant_order_id' => $id_order,
                        'description' => 'Prestashop order standard refund'
                    );
                    $result = $this->revolutApi->refundRevolutOrder($revolut_order['id_revolut_order'], $params);
                    if (empty($result['public_id'])) {
                        $this->setErrorMessage($this->l('An error occured while refunding the Revolut order.'), $result);
                    }
                }
            }
        }
    }

    public function hookDisplayProductPriceBlock(array $params)
    {
        if (!$this->isPs17 || $params['type'] != 'price') {
            return false;
        }

        return $this->hookDisplayRightColumnProduct([]);
    }

    public function hookDisplayRightColumnProduct(array $params)
    {
        if (!$this->active || $this->isSandBoxMode || !$this->paymentRequestIsEnabled) {
            return false;
        }

        if (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED') && !$this->context->customer->id) {
            return false;
        }

        $prb_button_locations = explode(',', Configuration::get('REVOLUT_PRB_LOCATION_VALUES'));

        if (!in_array('product', $prb_button_locations)) {
            return false;
        }


        $id_product = (int)Tools::getValue('id_product');
        if (!$id_product) {
            return false;
        }

        $product = new Product($id_product);

        $id_product = $product->id;
        $is_virtual = $product->is_virtual;

        $price = Product::getPriceStatic($id_product);
        $public_id = $this->createRevolutOrder($price);

        $ajax_url = $this->context->link->getBaseLink() . '/modules/' . $this->name . '/ajax.php';

        $this->smarty->assign(
            array(
                'is_product_page' => true,
                'ps_revolut_payment_request_params' => Tools::jsonEncode(
                    array(
                        'is_product_page' => true,
                        'token' => Tools::getToken(false),
                        'revolut_public_id' => $public_id,
                        'ps_cart_id' => $this->context->cart->id,
                        'merchant_type' => $this->revolutApi->mode,
                        'carrier_list' => self::$free_shipping_options,
                        'ajax_url' => $ajax_url,
                        'request_shipping' => !$is_virtual,
                        'button_style' => $this->prbSettingsHelper->getPRBConfigFormValues(),
                    )
                )
            )
        );

        return $this->display(__FILE__, '/views/templates/hook/payment_request.tpl');
    }

    public function hookDisplayExpressCheckout(array $params)
    {
        if (!$this->active || $this->isSandBoxMode || !$this->paymentRequestIsEnabled) {
            return false;
        }

        if (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED') && !$this->context->customer->id) {
            return false;
        }

        $prb_button_locations = explode(',', Configuration::get('REVOLUT_PRB_LOCATION_VALUES'));

        if (!in_array('cart', $prb_button_locations)) {
            return false;
        }

        $public_id = $this->createRevolutOrder();

        $ajax_url = $this->context->link->getBaseLink() . '/modules/' . $this->name . '/ajax.php';

        $this->smarty->assign(
            array(
                'is_product_page' => false,
                'ps_revolut_payment_request_params' => Tools::jsonEncode(
                    array(
                        'is_product_page' => false,
                        'token' => Tools::getToken(false),
                        'revolut_public_id' => $public_id,
                        'ps_cart_id' => $this->context->cart->id,
                        'merchant_type' => $this->revolutApi->mode,
                        'carrier_list' => self::$free_shipping_options,
                        'ajax_url' => $ajax_url,
                        'request_shipping' => !$this->context->cart->isVirtualCart(),
                        'button_style' => $this->prbSettingsHelper->getPRBConfigFormValues(),
                    )
                )
            )
        );

        return $this->display(__FILE__, '/views/templates/hook/payment_request.tpl');
    }

    public function hookDisplayShoppingCartFooter(array $params)
    {
        return $this->hookDisplayExpressCheckout($params);
    }

    /**
     * Hook actionOrderStatusUpdate
     *
     * @param
     * @return array|void
     */
    public function hookActionRevolutWebhook($params)
    {
        if (!empty($params['order_id']) && !empty($params['event'])) {
            if ($params['event'] == 'ORDER_COMPLETED') {
                $revolut_order = Db::getInstance()->getRow(
                    'SELECT UNHEX(`id_revolut_order`) as id_revolut_order, `save_card`, `id_order`'
                    . ' FROM `' . _DB_PREFIX_ . 'revolut_payment_orders`'
                    . ' WHERE UNHEX(`id_revolut_order`) LIKE "' . pSQL($params['order_id']) . '"'
                );

                if (!empty($revolut_order['id_revolut_order']) && !empty($revolut_order['id_order'])) {
                    // update prestashop order status
                    //check if order already got Payment accepted state
                    $new_order_status = Configuration::get('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED');
                    $order = new Order((int)$revolut_order['id_order']);
                    $order_history = $order->getHistory(Configuration::get('PS_LANG_DEFAULT'), $new_order_status);
                    if (!empty($order_history)) {
                        return;
                    }

                    $order_history = new OrderHistory();
                    $order_history->id_order = (int)$revolut_order['id_order'];
                    $order_history->changeIdOrderState($new_order_status, $revolut_order['id_order']);
                    $order_history->addWithemail();
                    $this->updateTransactionId((int)$revolut_order['id_order'], $revolut_order['id_revolut_order']);
                }
            }
        }
    }

    public function createRevolutOrder($amount = null)
    {
        if (!Validate::isLoadedObject($this->context->cart) || $this->context->cart->OrderExists()) {
            $cart = new Cart();
            $cart->id_lang = (int)$this->context->cookie->id_lang;
            $cart->id_currency = (int)$this->context->cookie->id_currency;
            $cart->id_guest = (int)$this->context->cookie->id_guest;
            $cart->id_shop_group = (int)$this->context->shop->id_shop_group;
            $cart->id_shop = $this->context->shop->id;
            $cart->id_address_delivery = 0;
            $cart->id_address_invoice = 0;
            $cart->save();

            $this->context->cart = $cart;
            $this->context->cookie->id_cart = $cart->id;
            $this->context->cookie->write();
            CartRule::autoAddToCart($this->context);
        }

        if (!$amount) {
            $amount = $this->context->cart->getOrderTotal();
        }

        $id_cart = $this->context->cart->id;
        $currency = $this->context->currency->iso_code;
        $revolut_order = $this->getRevolutOrderByIdCart($id_cart);

        if (!empty($revolut_order)) {
            $update_params = array(
                'amount' => $this->createRevolutAmount($amount, $currency),
                'currency' => $currency,
            );

            $public_id = $this->revolutApi->updateRevolutOrder($revolut_order['id_revolut_order'], $update_params);

            $order = $this->revolutApi->apiRequestGet('/orders/' . $revolut_order['id_revolut_order']);
            if (!empty($public_id) && isset($order['state']) && $order['state'] == 'PENDING') {
                return $public_id;
            }
            //if there would be an error on update, remove db record in order to create from scratch
            $this->removeRevolutOrderByIdCart($id_cart);
        }

        $path = '/orders';

        $capture_mode = 'MANUAL';
        if (!$this->authorizeOnly) {
            $capture_mode = 'AUTOMATIC';
        }

        $params = array(
            'amount' => $this->createRevolutAmount($amount, $currency),
            'currency' => $currency,
            'merchant_order_id' => null,
            'customer_email' => null,
            'capture_mode' => $capture_mode
        );

        $response = $this->revolutApi->apiRequest($path, $params);

        if (!isset($response['public_id']) || !isset($response['id'])) {
            // log bug
            PrestaShopLogger::addLog(
                'Error:  Can not create new Revolut order. Please check parameters and try again!',
                3
            );
            return '';
        }

        $id_revolut_order = $response['id'];
        $public_id = $response['public_id'];

        if (!$this->createRevolutPaymentRecord($id_revolut_order, $public_id, $id_cart)) {
            PrestaShopLogger::addLog('Error:  Can not save Revolut order to ' . _DB_PREFIX_ . 'revolut_payment_orders.', 3);
            return '';
        }

        return $public_id;
    }

    public function getMerchantPublicToken()
    {
        $merchant_public_key = Configuration::get("REVOLUT_MERCHANT_PUBLIC_TOKEN_{$this->revolutApi->mode}");

        if (!empty($merchant_public_key)) {
            return $merchant_public_key;
        }

        $response = $this->revolutApi->mgmtApiRequestGet('/public-key/latest', true);

        $merchant_public_key = isset($response['public_key']) ? $response['public_key'] : '';
        if (empty($merchant_public_key)) {
            return '';
        }

        Configuration::updateValue("REVOLUT_MERCHANT_PUBLIC_TOKEN_{$this->revolutApi->mode}", $merchant_public_key);

        return $merchant_public_key;
    }

    public function checkCurrencySupport()
    {
        if (!in_array($this->context->currency->iso_code, $this->available_currency_list)) {
            return false;
        }
        return true;
    }

    public function getRevolutPaymentTitle($id_revolut_order)
    {
        $paymentTittle = $this->displayName;
        $order = $this->revolutApi->apiRequestGet('/orders/' . $id_revolut_order);
        if (isset($order['payments']) && !empty($order['payments'])) {
            $payment = reset($order['payments']);
            if (isset($payment['payment_method']) && !empty($payment['payment_method'])) {
                $paymentMethod = $payment['payment_method'];
                $paymentMethod = $paymentMethod['type'];
                if ($paymentMethod == 'REVOLUT') {
                    $paymentTittle = 'Revolut Pay';
                } elseif ($paymentMethod == 'CARD') {
                    $paymentTittle = 'Card ' . $payment['payment_method']['card']['card_brand'] . ' (via Revolut)';
                } elseif ($paymentMethod == 'APPLE_PAY') {
                    $paymentTittle = 'Apple Pay (via Revolut)';
                } elseif ($paymentMethod == 'GOOGLE_PAY') {
                    $paymentTittle = 'Google Pay (via Revolut)';
                }
            }
        }

        return $paymentTittle;
    }

    public function createPrestaShopOrder($customer)
    {
        $order_status = Configuration::get('REVOLUT_OS_WAITING'); // Awaiting check payment - Payment accepted after capture
        $cart = $this->context->cart;
        //create the order
        $this->validateOrder(
            (int)$this->context->cart->id,
            $order_status,
            (float)$this->context->cart->getOrderTotal(true, Cart::BOTH),
            $this->displayName,
            null,
            null,
            (int)$this->context->currency->id,
            false,
            $customer->secure_key
        );

        $id_new_order = (int)$this->currentOrder;

        if (!$id_new_order) {
            PrestaShopLogger::addLog('Error: Can not create an order', 3);
            return;
        }

        if (!$this->updatePsOrderIdRecord($id_new_order, $cart->id)) {
            // log bug
            PrestaShopLogger::addLog('Error: Can not update PrestaShop order_id to ' . _DB_PREFIX_ . 'revolut_payment_orders', 3);
            return;
        }

        return $id_new_order;
    }

    public function processRevolutOrderResult($id_new_order, $revolut_order)
    {
        $paymentAcceptedOrderState = Configuration::get('PS_OS_PAYMENT');

        for ($i = 0; $i <= 9; $i++) {
            //check if in paralel webhook call already updated the order state
            if ($this->getOrderCurrentState($id_new_order) == $paymentAcceptedOrderState) {
                break;
            }

            $order = $this->revolutApi->apiRequestGet('/orders/' . $revolut_order['id_revolut_order']);
            if (!empty($order['state'])) {
                if ($order['state'] == 'COMPLETED' || $order['state'] == "IN_SETTLEMENT") {
                    if ($order['capture_mode'] == 'AUTOMATIC') {
                        // update order status
                        $this->updateOrderStatus($id_new_order, $paymentAcceptedOrderState);
                        $this->updateTransactionId($id_new_order, $revolut_order['id_revolut_order']);
                    }
                    break;
                } elseif (in_array($order['state'], array('CANCELLED', 'FAILED', 'PENDING'))) {
                    PrestaShopLogger::addLog(
                        'Something went wrong while taking the payment. Payment should be retried. 
                            Payment Status: ' . $order['state'],
                        3
                    );

                    // update order status to error
                    $new_order_status = Configuration::get('PS_OS_ERROR');
                    $this->updateOrderStatus($id_new_order, $new_order_status);
                    $this->addOrderMessage(
                        $id_new_order,
                        'Something went wrong while completing this payment. 
                                                           Please reach out to your customer and ask them to try again. 
                                                           Revolut order state: ' . $order['state']
                    );
                    break;
                } elseif ($order['capture_mode'] == 'MANUAL' && $order['state'] == "AUTHORISED") {
                    // if MANUAL option is selected and the payment is AUTHORISED just end the loop
                    // Merchant will check it manually
                    break;
                } elseif ($i == 9) {
                    // update order note
                    $this->addOrderMessage(
                        $id_new_order,
                        'Payment is taking a bit longer than 
                                            expected to be completed. 
							                If the order is not moved to the "Completed order" 
							                state after 24h, please check your Revolut account to 
							                verify that this payment was taken. 
							                You might need to contact your customer if it wasn’t. 
							                (Revolut Order ID: ' . $revolut_order['id_revolut_order'] . ')'
                    );
                }
            }

            sleep(3);
        }

        // update Revolut order
        $update_params = array(
            'merchant_order_id' => $id_new_order
        );

        $this->revolutApi->updateRevolutOrder($revolut_order['id_revolut_order'], $update_params);
    }

    protected function updateOrderStatus($id_order, $order_status)
    {
        $history = new OrderHistory();
        $history->id_order = (int)$id_order;
        $history->changeIdOrderState($order_status, $id_order);
        $history->addWithemail();
    }

    protected function addOrderMessage($id_new_order, $message)
    {
        $customer_thread_model = new CustomerThread();
        $order_customer_thread = $customer_thread_model->getIdCustomerThreadByEmailAndIdOrder(
            $this->context->customer->email,
            $id_new_order
        );
        $customer_thread_id = 0;
        if ($order_customer_thread) {
            $customer_thread_id = $order_customer_thread;
        } else {
            $customer_thread = new CustomerThread();
            $customer_thread->id_shop = $this->context->customer->id_shop;
            $customer_thread->id_lang = $this->context->customer->id_lang;
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = $this->context->customer->id;
            $customer_thread->id_order = (int)$id_new_order;
            $customer_thread->status = 'open';
            $customer_thread->email = $this->context->customer->email;
            $customer_thread->token = Tools::getToken(false);
            $customer_thread->save();
            $customer_thread_id = $customer_thread->id;
        }
        if ($customer_thread_id) {
            $customer_message = new CustomerMessage();
            $customer_message->id_customer_thread = $customer_thread_id;
            $customer_message->message = $message;
            $customer_message->private = 1;
            $customer_message->save();
        }
    }

    public function getOrderConfirmationLink($id_cart, $secure_key, $id_order)
    {
        return $this->context->link->getBaseLink() . 'index.php?controller=order-confirmation&id_cart='
            . (int)$id_cart . '&id_module='
            . (int)$this->id . '&id_order=' . $id_order . '&key=' . $secure_key;
    }

    public function updateTransactionId($id_order, $id_revolut_order)
    {
        $order = new Order($id_order);
        $payment_method = $this->getRevolutPaymentTitle($id_revolut_order);
        $order->payment = $payment_method;
        $order->save();

        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'order_payment` SET transaction_id="' . pSQL($id_revolut_order) . '" , payment_method="' . pSQL($payment_method) . '"
                                    WHERE order_reference="' . pSQL($order->reference) . '"'
        );
    }

    public function createRevolutAmount($amount, $currency)
    {
        //YEN currency's minor amount is directly YENs, it does not have fractional values.
        if (!in_array($currency, array('JPY', 'jpy'))) {
            $amount = Tools::ps_round($amount * 100);
        }

        return $amount;
    }
}
