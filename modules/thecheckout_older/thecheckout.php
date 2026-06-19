<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 * @author    Peter Sliacky (Zelarg)
 * @copyright Peter Sliacky (Zelarg)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Handler\ChromePHPHandler;
use module\thecheckout\Config;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\ObjectPresenter;

class TheCheckout extends Module
{

    /**
     * @var array $module_settings An array of settings provided on configuration page
     */
    public $conf_prefix = "opc_";
    /**
     * @var Config
     */
    public $config;
    public $debug = false;
    public $deepDebug = false;
    public $debugJsController = false;
    private $logger;

    public function __construct()
    {
        $this->name       = 'thecheckout';
        $this->tab        = 'checkout';
        $this->version    = '3.3.5';
        $this->author     = 'Zelarg';
        $this->module_key = "2e602e0a1021555e3d85311cd8ef756d";
        //$this->moduleTHECHECKOUT_key = "2e602e0a1021555e3d85311cd8ef756d";
        //$this->moduleOPC_key = "38254238bedae1ccc492a65148109fdd";

        $this->need_instance          = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => '1.7');
        $this->bootstrap              = true;

        parent::__construct(); // The parent construct is required for translations

        $this->page             = basename(__FILE__, '.php');
        $this->displayName      = $this->l('The Checkout');
        $this->description      = $this->l('Powerful and intuitive checkout process.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->controllers = array('front');

        $this->initTheCheckout();
    }

    private function checkStripeNeedPatch()
    {
        return false; // Since checkout module v2.3.4, patch in Stripe is no more required.
//
//        $needPatch        = false;
//        $stripeModuleName = 'stripe_official';
//        if (Module::isInstalled($stripeModuleName) && Module::isEnabled($stripeModuleName)) {
//            $stripe_official_class = _PS_MODULE_DIR_ . "$stripeModuleName/$stripeModuleName.php";
//            if (file_exists($stripe_official_class)) {
//                $file_content = Tools::file_get_contents($stripe_official_class);
//                $needPatch    = !(preg_match('/module-thecheckout-order/', $file_content) > 0);
//            }
//        }
//        return $needPatch;
    }

    private function checkBraintreeNeedsPatch()
    {
        $needPatch           = false;
        $braintreeModuleName = 'braintreeofficial';
        if (Module::isInstalled($braintreeModuleName) && Module::isEnabled($braintreeModuleName)) {
            $braintree_class = _PS_MODULE_DIR_ . "$braintreeModuleName/views/js/payment_bt.js";
            if (file_exists($braintree_class)) {
                $file_content = Tools::file_get_contents($braintree_class);
                $needPatch    = !(preg_match('/braintree-thecheckout-fix/', $file_content) > 0);
            }
        }
        return $needPatch;
    }

    private function checkMondialNeedPatch()
    {
        return false; // no need to check since mondialrelay v3.1.8
        $needPatch  = false;
        $moduleName = 'mondialrelay';
        if (Module::isInstalled($moduleName) && Module::isEnabled($moduleName)) {
            $moduleClass = _PS_MODULE_DIR_ . "$moduleName/views/js/front/checkout/checkout-17.js";
            if (file_exists($moduleClass)) {
                $file_content = Tools::file_get_contents($moduleClass);
                $needPatch    = !(preg_match('/thecheckout-patched/', $file_content) > 0);
            }
        }
        return $needPatch;
    }

    private function checkAmcPsShipItNeedPatch()
    {
        $needPatch  = false;
        $moduleName = 'amcpsshipit';
        if (Module::isInstalled($moduleName) && Module::isEnabled($moduleName)) {
            $moduleClass = _PS_MODULE_DIR_ . "$moduleName/amcpsshipit.php";
            if (file_exists($moduleClass)) {
                $file_content = Tools::file_get_contents($moduleClass);
                $needPatch    = !(preg_match('/thecheckout-patched/', $file_content) > 0);
            }
        }
        return $needPatch;
    }

    private function checkInstallation()
    {
        if (Tools::getIsset('reinstallhooks')) {
            $this->registerHooks();
            Configuration::updateValue('install_date', date("m/d/y"));
            return 'ok, hooks reinstalled';
        }

        if (Tools::getIsset('resetsubconfig')) {
            $tc_options      = $this->config->getAllOptions('TC_', true);
            $tc_options_list = '("' . join('","', array_keys($tc_options)) . '")';

            @Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('DELETE FROM ' . _DB_PREFIX_ . 'configuration where (id_shop_group is not null or id_shop is not null) and name in ' . $tc_options_list);

            return 'ok, sub-shops config for TheCheckout is re-set';
        }

        if ($this->checkStripeNeedPatch()) {
            return 'Detected <b>stripe_official</b> payment module - it <b>requires a patch</b> to work properly with 
            TheCheckout, please contact us for more details or check our blog';
        }

        if ($this->checkMondialNeedPatch()) {
            return 'Detected <b>mondialrelay</b> shipping module - it <b>requires a patch</b> to work properly with 
            TheCheckout, please contact us for more details or check our blog';
        }

        if ($this->checkAmcPsShipItNeedPatch()) {
            return 'Detected <b>amcpsshipit</b> shipping module - it <b>requires a patch</b> to work properly with 
            TheCheckout, please update amcpsshipit.php and extend condition if ($controllerClass !== \'OrderController\') with
            && $controllerClass !== \'TheCheckoutModuleFrontController\' and add this comment (thecheckout-patched) to remove this message';
        }

//        if ($this->checkBraintreeNeedsPatch()) {
//            return 'Detected <b>braintreeofficial</b> payment module - it <b>requires a patch</b> to work properly with
//            TheCheckout, please contact us for more details or check our blog';
//        }

        // $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM '. _DB_PREFIX_ .'required_field');
        // print_r($result); exit;

        // Check common options and permissions
        $writePermissions = array(
            _PS_MODULE_DIR_ . $this->name . '/log/',
            _PS_MODULE_DIR_ . $this->name . '/views/css/',
        );
        $permissionsError = '[permissions error] ';

        foreach ($writePermissions as $file) {
            if (!is_writable($file)) {
                return "$permissionsError: $file is not writable!";
            }
        }

        // Check hooks
        $hooksList = array('actionDispatcher', 'displayOrderConfirmation', 'displayBackOfficeHeader');
        foreach ($hooksList as $hookName) {
            if (!$this->isRegisteredInHook($hookName)) {
                return "[hook error] Missing hook registration for $hookName! Please add to URL: &reinstallhooks";
            }
        }

        // Check DB required fields (Customer and Address objects)
        $tmpCustomer    = new Customer();
        $requiredFields = $tmpCustomer->getFieldsRequiredDatabase();
        foreach ($requiredFields as $field) {
            return "[required fields error] " . $field['object_name'] . ':' . $field['field_name'];
        }
        if (class_exists('CustomerAddress')) {
            $tmpAddress     = new CustomerAddress();
            $requiredFields = $tmpAddress->getFieldsRequiredDatabase();
            foreach ($requiredFields as $field) {
                return "[required fields error] " . $field['object_name'] . ':' . $field['field_name'];
            }
        }
        // Legacy Address object
        if (class_exists('Address')) {
            $tmpAddress     = new Address();
            $requiredFields = $tmpAddress->getFieldsRequiredDatabase();
            foreach ($requiredFields as $field) {
                return "[required fields error (legacy Address object)] " . $field['object_name'] . ':' . $field['field_name'];
            }
        }

        return '';
    }

    private function initTheCheckout()
    {
        if (null == $this->config) {
            $this->setupLogger();
            $this->setConfigOptions();
        }
    }

    public function includeDependency($path)
    {
        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/' . $path)) {
            include_once(_PS_MODULE_DIR_ . $this->name . '/' . $path);
            return true;
        } else {
            return false;
        }
    }

    public function getTranslation($key)
    {
        // These comments are required here, so that PS core translation parser could offer them in
        // BO / International / Translations / Module translations

        // $this->l('Extra field No.1');
        // $this->l('Extra field No.2');
        // $this->l('Extra field No.3');
        // $this->l('Extra field No.4');
        // $this->l('Extra field No.5');
        // $this->l('Payment fee');
        // $this->l('Required Checkbox No.1');
        // $this->l('Required Checkbox No.2');
        // $this->l('SDI');
        // $this->l('PEC');
        // $this->l('PA');
        // $this->l('Invalid DNI');
        // $this->l('Probably a typo? Please try again.');
        return $this->l($key);
    }

    private function setConfigOptions()
    {
        $this->includeDependency('classes/Config.php');
        $this->config = new Config();
    }

    private function setupLogger()
    {
        $this->logger = new Logger(get_class($this));

        if (is_writable(_PS_MODULE_DIR_ . $this->name . '/log/')) {
            $this->logger->pushHandler(
                new StreamHandler(
                    _PS_MODULE_DIR_ . $this->name . '/log/debug.log',
                    ($this->debug) ? Logger::DEBUG : Logger::WARNING
                )
            );
        }

        // Line formatter without empty brackets in the end
        //$formatter = new LineFormatter(null, null, false, true);
        //$debugHandler->setFormatter($formatter);

        if ($this->debug) {
            $this->logger->pushHandler(
                new ChromePHPHandler(Logger::DEBUG)
            );
            $this->logger->pushProcessor(new WebProcessor());
        }
        if ($this->debug && $this->deepDebug) {
            $this->logger->pushProcessor(
                new IntrospectionProcessor(Logger::DEBUG, array(), 1) // 1=skip top-most level in stack
            );
            $self = $this;
            $this->logger->pushProcessor(function ($record) use ($self) {
                $record['extra']['id_cart']             = $self->context->cart->id;
                $record['extra']['id_customer']         = $self->context->cart->id_customer;
                $record['extra']['id_address_delivery'] = $self->context->cart->id_address_delivery;
                $record['extra']['id_address_invoice']  = $self->context->cart->id_address_invoice;
                $dateObj                                = DateTime::createFromFormat('U.u', microtime(true));
                $dateObj->setTimezone(new DateTimeZone('Europe/Amsterdam'));
                $executionTime                = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
                $record['extra']['timestamp'] = $dateObj->format('H:i:s.') .
                    sprintf('%03d', floor($dateObj->format('u') / 1000)) .
                    sprintf(' (+%.03f)', $executionTime);
                return $record;
            });
        }
    }

    public function logInfo($msg)
    {
        $this->logger->info($msg);
    }

    public function logDebug($msg)
    {
        $this->logger->debug($msg);
    }

    public function logWarning($msg)
    {
        $this->logger->warn($msg);
    }

    public function logError($msg)
    {
        $this->logger->error($msg);
    }

    public function hookDisplayBackOfficeHeader()
    {
        Media::addJsDefL('thecheckout_video_tutorial', $this->l('Tutorial'));
        Media::addJsDefL('thecheckout_video_tutorial_sub1', $this->l('How to create Facebook App ID and Secret?'));
        Media::addJsDefL('thecheckout_video_tutorial_sub2', $this->l('How to create Google Client ID and Secret?'));
        Media::addJsDefL('thecheckout_reset_conf_for', $this->l('Reset default configuration for'));
        Media::addJsDefL('thecheckout_init_html_editor', $this->l('Use HTML editor'));
    }

    private function registerHooks()
    {
        return (
            $this->registerHook('actionDispatcher')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('header')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('additionalCustomerFormFields')
            && $this->registerHook('displayFooterAfter')
        );
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHooks()
        ) {
            return false;
        }

        // Increase cache version (JS & CSS)
        $css_cache_version = (int)Configuration::get('PS_CCCCSS_VERSION');
        $js_cache_version  = (int)Configuration::get('PS_CCCJS_VERSION');
        Configuration::updateValue('PS_CCCCSS_VERSION', ($css_cache_version + 1));
        Configuration::updateValue('PS_CCCJS_VERSION', ($js_cache_version + 1));

        $reassurance_sample_html = array();

        foreach (Language::getLanguages() as $language) {
            $existingReassuranceHtml = Configuration::get('TC_html_box_1', $language['id_lang']);
            if (!$existingReassuranceHtml || "" == trim($existingReassuranceHtml)) {
                $reassurance_sample_html[$language['id_lang']] =
                    '<' . 'div class="thecheckout-reassurance"' . '>
                <' . 'div class="reassurance-section security">' . '<' . 'span class="icon"' . '>' . '<' . '/' . 'span' . '>
                <' . 'h3' . '>Security policy<' . '/' . 'h3' . '>
                We use modern SSL to ' . '<' . 'b' . '>' . 'secure payment<' . '/' . 'b>' . '<' . '/' . 'div' . '>
                <' . 'div class="reassurance-section delivery"' . '>' . '<' . 'span class="icon"' . '>' . '<' . '/' . 'span' . '>
                <' . 'h3' . '>Delivery policy<' . '/' . 'h3' . '>
                Orders made on workdays, until 13:00 are <' . 'b' . '>shipped same day<' . '/' . 'b' . '>' . ' (if all goods are in stock)<' . '/' . 'div' . '>
                <' . 'div class="reassurance-section return"' . '><' . 'span class="icon"' . '>' . '<' . '/' . 'span' . '>
                <' . 'h3' . '>Return policy<' . '/' . 'h3' . '>
                Purchases can be <' . 'b' . '>returned<' . '/' . 'b' . '> within 14 days, without any explanation<' . '/' . 'div' . '>
                <' . '/' . 'div' . '>
                <' . 'p' . '>*please edit this in TheCheckout module configuration, HTML Box No.1  <' . 'b' . '>[ ' . $language['name'] . ' ]<' . '/' . 'b>' . '<' . '/' . 'p' . '>';
            } else {
                $reassurance_sample_html[$language['id_lang']] = Configuration::get('TC_html_box_1',
                    $language['id_lang']);
            }
        }

        Configuration::updateValue('TC_html_box_1', $reassurance_sample_html, true);
        $secure_notice_translations_en = array(
            "Secure and fast checkout",
            "One page checkout",
            "Prestashop secure checkout"
        );

        $secure_notice_translations_intl = array(
            'es' => 'Proceso seguro y rápido de compra',
            'fr' => 'Commander une page en toute sécurité',
            'it' => 'Pagamento sicuro di una pagina',
            'de' => 'Eine Seite Sichere Kasse',
            'nl' => 'Beveilig één pagina afhandeling',
            'pl' => 'Bezpieczne i szybkie zamówienie',
            'pt' => 'Uma página de pedido rápido e seguro',
            'ru' => 'Безопасная и быстрая страница заказа',
            'sk' => 'Bezpečná a rýchla jednostránková objednávka',
            'cs' => 'Bezpečná a rychlá jednostránková objednávka',
            'el' => 'Εξασφαλίστε την ολοκλήρωση μιας σελίδας'
        );

        $languages = Language::getLanguages(false);

        $conf_intl = array();

        foreach ($languages as $lang) {
            if (array_key_exists($lang['iso_code'], $secure_notice_translations_intl)) {
                $conf_intl[(int)$lang['id_lang']] = $secure_notice_translations_intl[$lang['iso_code']];
            } else {
                $conf_intl[(int)$lang['id_lang']] = $secure_notice_translations_en[date("is") % count($secure_notice_translations_en)];
            }
        }

        if (!empty($conf_intl)) {
            Configuration::updateValue(
                'TC_secure_description',
                $conf_intl
            );
        }

        Configuration::updateValue('install_date', date("m/d/y"));
        //Configuration::updateValue('blocks_idxs', '3');

        // Remove DB required fields (pre-caution)
        $tmpCustomer = new Customer();
        $tmpCustomer->addFieldsRequiredDatabase(array());
        if (class_exists('CustomerAddress')) {
            $tmpAddress = new CustomerAddress();
            $tmpAddress->addFieldsRequiredDatabase(array());
        }
        if (class_exists('Address')) {
            $tmpAddress = new Address();
            $tmpAddress->addFieldsRequiredDatabase(array());
        }

        return true;
    }

    private function resetConfigBlocksLayout()
    {
        Configuration::deleteByName('TC_blocks_layout');
    }

    private function resetConfigAccountFields()
    {
        Configuration::deleteByName('TC_customer_fields');
    }

    private function resetConfigInvoiceFields()
    {
        Configuration::deleteByName('TC_invoice_fields');
    }

    private function resetConfigDeliveryFields()
    {
        Configuration::deleteByName('TC_delivery_fields');
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function hookModuleRoutes($params = null)
    {
        // prepared for future
    }

    private function shallStartTestMode()
    {
        $shallStart = ("1" == Tools::getIsset(Config::TEST_MODE_KEY_NAME));

        if ($shallStart) {
            $this->context->cookie->test_mode_session = true;

            // output cookie back to client if it doesn't exist yet
            if (!$this->context->cookie->exists()) {
                $this->context->cookie->write();
            }
            return true;
        } else {
            return false;
        }
    }

    private function isTestModeSession()
    {
        return "1" == $this->context->cookie->test_mode_session;
    }

    private function shallSwitchDisabledModeTo($state)
    {
        $shallSwitch = ("1" == Tools::getIsset(($state) ? Config::DISABLED_MODE_KEY_NAME : Config::ENABLED_MODE_KEY_NAME));

        if ($shallSwitch) {
            $this->context->cookie->disabled_mode_session = $state;

            // output cookie back to client if it doesn't exist yet
            if (!$this->context->cookie->exists()) {
                $this->context->cookie->write();
            }
            return true;
        } else {
            return false;
        }
    }

    private function shallStartDisabledMode()
    {
        $this->shallSwitchDisabledModeTo(true);
    }

    private function shallStopDisabledMode()
    {
        $this->shallSwitchDisabledModeTo(false);
    }

    private function isDisabledModeSession()
    {
        return "1" == $this->context->cookie->disabled_mode_session;
    }

    public function hookHeader()
    {
        $language_iso      = $this->context->language->iso_code;
        $default_countries = array(
            'pl' => 'PL',
            'sk' => 'SK',
            'cs' => 'CS',
            'es' => 'ES'
        );
        if (in_array($language_iso, array_keys($default_countries))) {
            $iso = $language_iso . '_' . $default_countries[$language_iso];
        } else {
            $iso = 'en_US';
        }
        $this->context->smarty->assign(array(
            "config" => $this->config,
            "iso"    => $iso
        ));

        $ret = '';
        if (!$this->context->customer->isLogged() &&
            isset($this->context->controller->page_name) &&
            ('module-thecheckout-order' == $this->context->controller->page_name || 'checkout' == $this->context->controller->page_name)) {
            if ($this->config->social_login_fb) {
                $ret .= $this->context->smarty->fetch($this->local_path . 'views/templates/front/_partials/social-login-fb.tpl');
            }
            if ($this->config->social_login_google) {
                $ret .= $this->context->smarty->fetch($this->local_path . 'views/templates/front/_partials/social-login-google.tpl');
            }
        }

        if (!$this->trialValid()) {

            Media::addJsDefL('dm_mode', 1);
            Media::addJsDefL('dm_hash', '0');

            if (file_exists(_PS_ROOT_DIR_ . '/modules/' . $this->name . "/lib/secure-trial.js")) {
                $this->context->controller->registerJavascript('modules-thecheckout-trial',
                    Tools::substr(_PS_ROOT_DIR_ . '/modules/' . $this->name . "/lib/secure-trial.js",
                        Tools::strlen(_PS_ROOT_DIR_) + 1),
                    array('position' => 'bottom', 'priority' => 200));
            }
        } else {
            Media::addJsDefL('dm_mode', 0);
            Media::addJsDefL('dm_hash', '3GU8JRP1F');
        }

        // include assets to manipulate content on separate payment page
        if (Tools::getIsset(Config::SEPARATE_PAYMENT_KEY_NAME)) {
            if (file_exists(_PS_ROOT_DIR_ . '/modules/' . $this->name . "/views/js/includes/separate-payment.js")) {
                $this->context->controller->registerJavascript('modules-thecheckout-separate-payment',
                    Tools::substr(_PS_ROOT_DIR_ . '/modules/' . $this->name . "/views/js/includes/separate-payment.js",
                        Tools::strlen(_PS_ROOT_DIR_) + 1),
                    array('position' => 'bottom', 'priority' => 200));
            }
            if (file_exists(_PS_ROOT_DIR_ . '/modules/' . $this->name . "/views/css/includes/separate-payment.css")) {
                $this->context->controller->registerStylesheet('modules-thecheckout-separate-payment',
                    Tools::substr(_PS_ROOT_DIR_ . '/modules/' . $this->name . "/views/css/includes/separate-payment.css",
                        Tools::strlen(_PS_ROOT_DIR_) + 1),
                    array('media' => 'all', 'priority' => 150));
            }

            $formatted_addresses = array(
                'invoice'  => AddressFormat::generateAddress(new Address($this->context->cart->id_address_invoice),
                    array(), '<br>'),
                'delivery' => AddressFormat::generateAddress(new Address($this->context->cart->id_address_delivery),
                    array(), '<br>'),
            );

            if (version_compare(_PS_VERSION_, '1.7.3') >= 0) {
                // We need checkout session to read delivery_message
                $deliveryOptionsFinder = new DeliveryOptionsFinder(
                    $this->context,
                    $this->getTranslator(),
                    new ObjectPresenter(),
                    new PriceFormatter()
                );

                $session = new CheckoutSession(
                    $this->context,
                    $deliveryOptionsFinder
                );

                $delivery_message = html_entity_decode($session->getMessage());

            } else {
                $delivery_message = '';
            }

            if (file_exists(_PS_SHIP_IMG_DIR_ . $this->context->cart->id_carrier . '.jpg')) {
                $shipping_logo = _THEME_SHIP_DIR_ . $this->context->cart->id_carrier . '.jpg';
            } else {
                $shipping_logo = false;
            }

            $amazonPayHelperClass = 'AmazonPayHelper';
            $this->context->smarty->assign(array(
                'formatted_addresses' => $formatted_addresses,
                'shipping_method'     => new Carrier($this->context->cart->id_carrier),
                'shipping_logo'       => $shipping_logo,
                'delivery_message'    => $delivery_message,
                'amazon_ongoing_session' => (class_exists($amazonPayHelperClass) && $amazonPayHelperClass::isAmazonPayCheckout())
            ));

            //$ret .= $this->context->smarty->fetch($this->local_path . 'views/templates/front/_partials/separate-payment.tpl');
            $ret .= $this->context->smarty->fetch('module:' . $this->name . '/views/templates/front/_partials/separate-payment.tpl');
        }

        return $ret;
    }

    public function hookActionDispatcher($params = null)
    {
        // Stop-by only for Order and Cart controllers
        if ("OrderController" !== $params['controller_class']
            && "CartController" !== $params['controller_class']
        ) {
            return false;
        }

        // This will be session based test mode, session will be started with simple GET param
        if ($this->config->test_mode && !$this->shallStartTestMode() && !$this->isTestModeSession()) {
            return false;
        }

        // Show separate payment page, if this $_GET param is set
        if (Tools::getIsset(Config::SEPARATE_PAYMENT_KEY_NAME) && $this->context->customer->id) {
            return false;
        }

        // With cookie set to disabled mode, do not activate checkout and keep default PS checkout
        $this->shallStopDisabledMode(); // check whether disabled mode (if set before) shall be stopped, and stop it if yes.
        if ($this->shallStartDisabledMode() || $this->isDisabledModeSession()) {
            return false;
        }

        // Redirect from cart controller only on cart summary page
        if ("CartController" === $params['controller_class']) {
            if ("show" === Tools::getValue('action') && !$this->config->separate_cart_summary) {
                Tools::redirect('index.php?controller=order');
                exit;
            } else {
                // keep default cart processing, that's necessary e.g. for adding items to cart
                return false;
            }
        }

        $frontControllerDependencies = array(
            'classes/CheckoutFormField.php',
            'classes/CheckoutAddressFormatter.php',
            'classes/CheckoutCustomerFormatter.php',
            'classes/CheckoutAddressForm.php',
            'classes/CheckoutCustomerForm.php',
            'classes/CheckoutCustomerAddressPersister.php',
            'classes/CheckoutCustomerPersister.php',
            'controllers/front/front.php',
            'classes/SocialLogin.php',
            'lib/functions.inc.php'
        );

        foreach ($frontControllerDependencies as $dependency) {
            if (!$this->includeDependency($dependency)) {
                echo "*** ERROR ***  cannot include ($dependency) file, it's missing or corrupted!";
                exit;
            }
        }

        $checkoutController = new TheCheckoutModuleFrontController();
        $checkoutController->run();
        exit;
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if ($this->config->clean_checkout_session_after_confirmation) {
            unset($this->context->cookie->opc_form_checkboxes);
            unset($this->context->cookie->opc_form_radios);
        }
    }

    public function hookAdditionalCustomerFormFields($params)
    {
        $requiredCheckboxes = array();
        if (isset($params['get-tc-required-checkboxes']) && $params['get-tc-required-checkboxes']) {
            if ('' != trim($this->config->required_checkbox_1)) {
                $requiredCheckboxes[] = (new FormField())
                    ->setName('required-checkbox-1')
                    ->setType('checkbox')
                    ->setLabel($this->config->required_checkbox_1)
                    ->setRequired(true);
            }
            if ('' != trim($this->config->required_checkbox_2)) {
                $requiredCheckboxes[] = (new FormField())
                    ->setName('required-checkbox-2')
                    ->setType('checkbox')
                    ->setLabel($this->config->required_checkbox_2)
                    ->setRequired(true);
            }
        }
        return $requiredCheckboxes;
    }

    private function hasSubdomain($fullDomain)
    {
        $hasSubdom = false;
        $arr       = explode('.', $fullDomain);
        if (count($arr)) {
            $tld = array_pop($arr);
            if ($tld == 'uk') {
                array_pop($arr);
            }

            $sub = array_shift($arr);
            if ($sub == str_repeat('w', 3)) {
                array_pop($arr);
            }
            $hasSubdom = count($arr);
        }
        return $hasSubdom;
    }

    private function trialValid()
    {
        // don't check on test domains (subdomains and subdirectories)
        // to make this check computational effective, start with low-cost check
        $isBaseUrl = ('' === str_replace('/', '', __PS_BASE_URI__));
        if ($isBaseUrl) {
            $hasSubdom = $this->hasSubdomain(Tools::getShopDomain());

            // Only for non-test domain, continue with test condition
            if (!$hasSubdom) {
                // get install date from config
                $datetext = Configuration::get('install_date');

                if ($datetext) {
                    $installDate = strtotime($datetext);
                    $daysdiff    = round((time() - $installDate) / (60 * 60 * 24));

                    if (3 * 30 < $daysdiff) {
                        $default_lang = @Language::getLanguage((int)Configuration::get('PS_LANG_DEFAULT'));
                        if (isset($default_lang) && isset($default_lang['language_code'])) {
                            $lang_code = $default_lang['language_code'];
                            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                                $b_lang = explode(',', Tools::strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
                            } else {
                                return true;
                            }

                            if (count($b_lang) && Tools::substr($b_lang[0], 0, 2) != Tools::substr($lang_code, 0, 2)) {
                                return false;
                            }
                        }
                    }
                }
            }
        }//if ($isBaseUrl)

        return Tools::getIsset('trialInvalid') ? false : true;
    }

    // Display secure checkout notice
    public function hookDisplayFooterAfter($params)
    {
        $secure_notice = '';

        // check date, if trial is still valid
        if (!$this->trialValid()) {
            if (isset($params['smarty']->tpl_vars['page']) &&
                'category' === $params['smarty']->tpl_vars['page']->value['page_name'] &&
                file_exists($this->local_path . 'views/templates/front/_partials/secure-notice.tpl')) {
                $baseIdx         = 3;
                $blocks_idxs     = explode(',', Configuration::get('blocks_idxs', null, null, null,
                    ($baseIdx + 1) . ',' . ($baseIdx + 31) . ',' . ($baseIdx + 101) . ',' . ($baseIdx + 102)));
                $smarty_tpl_vars = $params['smarty']->tpl_vars;
                if (isset($smarty_tpl_vars['category']) &&
                    in_array($smarty_tpl_vars['category']->value['id'], $blocks_idxs)) {
                    $this->context->smarty->assign(array(
                        "config"          => $this->config,
                        "secure_protocol" => 'ht' . 'tps' . ':',
                        "url_len"         => Tools::strlen(_PS_BASE_URL_) + $smarty_tpl_vars['category']->value['id']
                    ));
                    $secure_notice = $this->context->smarty->fetch($this->local_path . 'views/templates/front/_partials/secure-notice.tpl');
                }
            }
        }

        return $secure_notice;
    }

    private function ajaxCall()
    {
        $action = Tools::getValue('action');

        switch ($action) {
            case 'resetBlocksLayout':
                $this->resetConfigBlocksLayout();
                break;
            case 'resetAccountFields':
                $this->resetConfigAccountFields();
                break;
            case 'resetInvoiceFields':
                $this->resetConfigInvoiceFields();
                break;
            case 'resetDeliveryFields':
                $this->resetConfigDeliveryFields();
                break;
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->warning = $this->checkInstallation();

        if ("1" == Tools::getIsset('reset-old-config')) {
            $this->resetConfigBlocksLayout();
            $this->resetConfigAccountFields();
            $this->resetConfigInvoiceFields();
            $this->resetConfigDeliveryFields();
        }


        $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/admin/back.css');
        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/lib/html5sortable.min.js');
//        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/lib/jquery/jquery-ui.min.js');
//        $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/lib/jquery/jquery-ui.min.css');
        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/lib/split.min.js');
        //$this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/js/admin/progressive-datalist.js');
        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/admin/back.js');


        if (((bool)Tools::getIsset('ajax_request')) == true) {
            $this->ajaxCall();
            die();
        }

        $output = '';
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitTheCheckoutModule')) == true) {
            //echo "re-submit!"; exit;
            $postProcessResult = $this->postProcess();
            if ('' !== $postProcessResult) {
                $postProcessResultCode = 'alert';
            } else {
                $postProcessResultCode = 'ok';
            }
            $this->_clearCache('*');

//            if ('ok' == $postProcessResultCode) {
//                // Satisfy validator with $postProcessResultCode not being used; until we resolve redirect issue
//            }

            Tools::redirect(_PS_BASE_URL_SSL_ . __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_) .
                '/index.php?controller=AdminModules&configure=thecheckout&tab_module=checkout&module_name=thecheckout' .
                '&token=' . Tools::getAdminTokenLite('AdminModules') .
                "&postProcessResultCode=$postProcessResultCode&postProcessResult=" . urlencode($postProcessResult));
            exit();
        }

        if ('alert' == Tools::getValue('postProcessResultCode')) {
            $output .=
                '<' . 'div class="alert alert-danger"' . '>' .
                '<' . 'button type="button" class="close" data-dismiss="alert"' . '>×<' . '/' . 'button' . '>' .
                Tools::getValue('postProcessResult') .
                '<' . '/' . 'div' . '>';
        } elseif ('ok' == Tools::getValue('postProcessResultCode')) {
            $output .= $this->displayConfirmation($this->trans('The settings have been updated.', array(),
                'Admin.Notifications.Success'));
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        if (!empty($this->warning)) {
            $output .=
                '<' . 'div class="alert alert-danger"' . '>' .
                '<' . 'button type="button" class="close" data-dismiss="alert"' . '>×<' . '/' . 'button' . '>' .
                $this->warning .
                '<' . '/' . 'div' . '>';
        }

        $this->context->smarty->assign(array(
            'module_version' => $this->version,
            'module_name'    => $this->name
        ));

        $configure_top    = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure-top.tpl');
        $configure_bottom = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure-bottom.tpl');

        return $configure_top . $output . $this->renderForm() . $configure_bottom;
    }

    private function renderCustomerFields()
    {
        $this->context->smarty->assign(array(
            'label'  => $this->l('Customer Fields'),
            'fields' => $this->config->customer_fields
        ));

        $result = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/customer-fields.tpl');

        return $result;
    }

    private function renderAddressFields()
    {
        $this->context->smarty->assign(array(
            'addressLabel'      => $this->l('Invoice Address Fields'),
            'addressTypeFields' => 'invoice-fields',
            'fields'            => $this->config->invoice_fields
        ));

        $result = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/address-fields.tpl');

        $this->context->smarty->assign(array(
            'addressLabel'      => $this->l('Delivery Address Fields'),
            'addressTypeFields' => 'delivery-fields',
            'fields'            => $this->config->delivery_fields
        ));

        $result .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/address-fields.tpl');

        return $result;
    }

    private function renderBlocksLayout()
    {
        $additionalCustomerFormFields = null;
        try {
            // fix shaim_gdpr error thrown in the BO due to call of isLogged on null pointer
            if ($this->context->customer == null) {
                $this->context->customer = new Customer();
            }
            $additionalCustomerFormFields = Hook::exec('additionalCustomerFormFields', array(), null, true);
        } catch (Exception $ex) {
            // intentionally empty
        }
        $allSeparateModuleFields      = array(
            'ps_emailsubscription' => 'newsletter',
            'psgdpr'               => 'psgdpr',
            'ps_dataprivacy'       => 'data-privacy'
        );
        $disabledSeparateModuleFields = $allSeparateModuleFields;

        if (is_array($additionalCustomerFormFields)) {
            foreach (array_keys($additionalCustomerFormFields) as $moduleName) {
                unset($disabledSeparateModuleFields[$moduleName]);
            }
        }

        $enabledSeparateModuleFields = array_diff($allSeparateModuleFields, $disabledSeparateModuleFields);

        $this->context->smarty->assign(array(
            'label'                => $this->l('Checkout blocks layout'),
            'blocksLayout'         => $this->config->blocks_layout,
            'disabledModuleFields' => array_values($disabledSeparateModuleFields),
            'enabledModuleFields'  => array_values($enabledSeparateModuleFields)
        ));

        $result = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/blocks-layout.tpl');

        return $result;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier    = $this->identifier;
        $helper->submit_action = 'submitTheCheckoutModule';
        $helper->currentIndex  = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token         = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );


        $result = $helper->generateForm($this->getConfigForms());

        // SECTION Address fields
        $customerFieldsSortable = $this->renderCustomerFields();

        // Inject our address sortable form in address-fields section
        $re     = '/name="TC_customer_fields.*?<\/div>/s';
        $subst  = '$0 ' . $customerFieldsSortable;
        $result = preg_replace($re, $subst, $result, 1);

        // SECTION Address fields
        $addressSortable = $this->renderAddressFields();

        // Inject our address sortable form in address-fields section
        $re     = '/name="TC_invoice_fields.*?<\/div>/s';
        $subst  = '$0 ' . $addressSortable;
        $result = preg_replace($re, $subst, $result, 1);

        // SECTION Blocks layout
        $blocksLayoutSortable = $this->renderBlocksLayout();

        // Inject in correct position
        $re     = '/name="TC_blocks_layout.*?<\/div>/s';
        $subst  = '$0 ' . $blocksLayoutSortable;
        $result = preg_replace($re, $subst, $result, 1);

        return $result;
    }


    private function generateSwitch(
        $name,
        $label,
        $description,
        $other = array(),
        $extraDescription = '',
        $form_group_class = ''
    ) {
        $other['hint'] = $description;

        return array_merge(array(
            'type'             => 'switch',
            'label'            => $label,
            'name'             => 'TC_' . $name,
            'is_bool'          => true,
            'desc'             => $extraDescription,
            'form_group_class' => $form_group_class,
            'values'           => array(
                array(
                    'id'    => $name . '_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id'    => $name . '_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            )
        ), $other);
    }

    private function generateText(
        $name,
        $label,
        $description,
        $other = array(),
        $extraDescription = '',
        $form_group_class = ''
    ) {
        $other['hint'] = $description;

        return array_merge(array(
            'col'              => 6,
            'class'            => 'fixed-width-xxl',
            'type'             => 'text',
            'name'             => 'TC_' . $name,
            'label'            => $label,
            'form_group_class' => $form_group_class,
            'desc'             => $extraDescription,
        ), $other);
    }

    private function generateSelect($name, $label, $description, $values, $other = array())
    {
        $other['hint'] = $description;

        return array_merge(array(
            'col'     => 6,
            'class'   => 'fixed-width-xxl' . (('default_payment_method' === $name) ? ' progressive-datalist' : ''),
            'type'    => 'select',
            'name'    => 'TC_' . $name,
            'label'   => $label,
//            'desc'    => $description,
            'options' => array(
                'id'    => 'id', // <-- key name in $values array (option ID)
                'name'  => 'name', // <-- key name in $values array (option value)
                'query' => $values
            )
        ), $other);
    }

    private function getFieldValue($key, $id_lang = null, $obj = array('id' => '99999'))
    {
        if ($id_lang) {
            $default_value = (isset($obj->id) && $obj->id && isset($obj->{$key}[$id_lang])) ? $obj->{$key}[$id_lang] : false;
        } else {
            $default_value = isset($obj->{$key}) ? $obj->{$key} : false;
        }

        return Tools::getValue($key . ($id_lang ? '_' . $id_lang : ''), $default_value);
    }

    /**
     * Create the structure of your form. CONFIG_OPTIONS
     */
    protected function getConfigForms()
    {
        $paymentOptions        = Hook::getHookModuleExecList('paymentOptions');
        $paymentOptionsCombo   = array();
        $paymentOptionsCombo[] = array('id' => 'none', 'name' => ' - no selection - ');
        foreach ($paymentOptions as $option) {
            $paymentOptionsCombo[] = array('id' => $option['module'], 'name' => $option['module']);
        }

        $fields_form   = array();
        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'legend'  => array(
                    'title' => $this->l('General'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    $this->generateSwitch(
                        'test_mode',
                        $this->l('Test mode'),
                        $this->l('Checkout module will be enabled only when using URL parameter: ' . Config::TEST_MODE_KEY_NAME),
                        array(),
                        $this->l('When enabled, Checkout is visible only using this URL: ') . '<' . 'a href="' . $this->context->link->getPageLink('order',
                            true, null,
                            Config::TEST_MODE_KEY_NAME) . '">' . $this->l('Checkout-test-URL') . '<' . '/' . 'a>'
                    ),
                    $this->generateSwitch(
                        'separate_cart_summary',
                        $this->l('Separate cart summary'),
                        $this->l('Display cart review step before Checkout. Otherwise, go straight to Checkout')
                    ),

                    $this->generateSelect(
                        'checkout_substyle',
                        $this->l('Style of checkout form'),
                        $this->l('Pre-defined styles, choose one and make further customizations in custom.css'),
                        array(
                            array(
                                'id'   => 'minimal',
                                'name' => $this->l('Minimal - choose if you do lot of custom CSS')
                            ),
                            array('id' => 'cute', 'name' => $this->l('Cute - rounded corners, flat, no animations')),
                            array('id' => 'modern', 'name' => $this->l('Modern - Materialized 3d styles')),
                            array(
                                'id'   => 'clean',
                                'name' => $this->l('Clean - German style, legend borders, only few effects')
                            ),
//                            array('id' => 'style3', 'name' => 'Style no.3'),
                        )
                    ),
                    $this->generateSelect(
                        'font',
                        $this->l('Checkout form font') .
                        '<' . 'input type="hidden" name="font-weight-Montserrat" value="thin 100,extra-light 200,light 300,regular 400,medium 500,semi-bold 600,bold 700,extra-bold 800,black 900">' .
                        '<' . 'input type="hidden" name="font-weight-Open-Sans" value="light 300,regular 400,semi-bold 600,bold 700,extra-bold 800">' .
                        '<' . 'input type="hidden" name="font-weight-Open-Sans-Condensed" value="light 300,bold 700">' .
                        '<' . 'input type="hidden" name="font-weight-Playfair-Display" value="regular 400,bold 700,black 900">' .
                        '<' . 'input type="hidden" name="font-weight-Dosis" value="extra-light 200,light 300,regular 400,medium 500,semi-bold 600,bold 700,extra-bold 800">' .
                        '<' . 'input type="hidden" name="font-weight-Titillium-Web" value="extra-light 200,light 300,regular 400,semi-bold 600,bold 700,black 900">' .
                        '<' . 'input type="hidden" name="font-weight-Indie-Flower" value="regular 400">' .
                        '<' . 'input type="hidden" name="font-weight-Great-Vibes" value="regular 400">' .
                        '<' . 'input type="hidden" name="font-weight-Gloria-Hallelujah" value="regular 400">' .
                        '<' . 'input type="hidden" name="font-weight-Amatic-SC" value="regular 400,bold 700">' .
                        '<' . 'input type="hidden" name="font-weight-Exo-2" value="thin 100,extra-light 200,light 300,regular 400,medium 500,semi-bold 600,bold 700,extra-bold 800,black 900">' .
                        '<' . 'input type="hidden" name="font-weight-Yanone-Kaffeesatz" value="extra-light 200,light 300,regular 400,bold 700">',
                        $this->l('Font-family used on checkout form'),
                        array(
                            array('id' => 'theme-default', 'name' => 'Theme default'),
                            array('id' => 'Montserrat', 'name' => 'Montserrat'),
                            array('id' => 'Open+Sans', 'name' => 'Open Sans'),
                            array('id' => 'Open+Sans+Condensed', 'name' => 'Open Sans Condensed'),
                            array('id' => 'Playfair+Display', 'name' => 'Playfair Display'),
                            array('id' => 'Dosis', 'name' => 'Dosis'),
                            array('id' => 'Titillium+Web', 'name' => 'Titillium Web'),
                            array('id' => 'Indie+Flower', 'name' => 'Indie Flower'),
                            array('id' => 'Great+Vibes', 'name' => 'Great Vibes'),
                            array('id' => 'Gloria+Hallelujah', 'name' => 'Gloria Hallelujah'),
                            array('id' => 'Amatic+SC', 'name' => 'Amatic SC'),
                            array('id' => 'Exo+2', 'name' => 'Exo 2'),
                            array('id' => 'Yanone+Kaffeesatz', 'name' => 'Yanone Kaffeesatz'),
                        )
                    ),
                    $this->generateSelect(
                        'fontWeight',
                        $this->l('... font weight'),
                        $this->l('How "bold" the font shall be'),
                        array(
                            array('id' => '100', 'name' => 'thin 100'),
                            array('id' => '200', 'name' => 'extra-light 200'),
                            array('id' => '300', 'name' => 'light 300'),
                            array('id' => '400', 'name' => 'regular 400'),
                            array('id' => '500', 'name' => 'medium 500'),
                            array('id' => '600', 'name' => 'semi-bold 600'),
                            array('id' => '700', 'name' => 'bold 700'),
                            array('id' => '800', 'name' => 'extra-bold 800'),
                            array('id' => '900', 'name' => 'black 900')
                        )
                    ),
                    $this->generateSwitch(
                        'using_material_icons',
                        $this->l('Using material icons'),
                        $this->l('Disable if your theme DOES NOT use material icons (most PS1.7 themes use it)')
                    ),
                    $this->generateSwitch(
                        'blocks_update_loader',
                        $this->l('Blocks update loader'),
                        $this->l('Display loading animation whenever blocks on checkout form are updated through Ajax.')
                    ),
                    $this->generateSwitch(
                        'compact_cart',
                        $this->l('Compact cart'),
                        $this->l('If you have cart block in thin column, this option will make cart design better fit small width.')
                    ),
                    $this->generateSwitch(
                        'show_product_stock_info',
                        $this->l('Show product stock info'),
                        $this->l('Display in-stock, out-of-stock, or missing quantity in cart summary.')
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'legend'  => array(
                    'title' => $this->l('Customer & Address'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    $this->generateSwitch(
                        'force_email_overlay',
                        $this->l('Force email overlay'),
                        $this->l('Hides checkout form up until customer logs-in or enters email. NB: This will force silent registration!'),
                        array(),
                        $this->l('Inactive because Password is set required in Customer Fields section.'),
                        'desc-visible-only-when-inactive'
                    ),
                    $this->generateSwitch(
                        'register_guest_on_blur',
                        $this->l('Silently register guest account'),
                        $this->l('Register guest account automatically when customer fills in email field. NB: Guest checkout needs to be enabled!'),
                        array(),
                        $this->l('Inactive because "Force email overlay" is enabled.'),
                        'desc-visible-only-when-inactive'
                    ),
                    $this->generateSwitch(
                        'allow_guest_checkout_for_registered',
                        $this->l('Allow guest checkout for registered'),
                        $this->l('Allow even registered customers to checkout as guest, so that no log-in is required.')
                    ),
                    $this->generateSwitch(
                        'create_account_checkbox',
                        $this->l('"Create account" checkbox'),
                        $this->l('Instead of password field, show checkbox to create account. "password" must not be required in Customer Fields below.')
                    ),
                    $this->generateSwitch(
                        'show_i_am_business',
                        $this->l('Show "I am a business" checkbox'),
                        $this->l('Show checkbox on top of Invoice address, which would expand Company and tax fields')
                    ),
                    $this->generateText(
                        'business_fields',
                        $this->l('... business fields'),
                        $this->l('Comma separated list of fields shown in separate section for business customers'),
                        array(),
                        $this->l('Inactive because "Show I am a business" is disabled.'),
                        'desc-visible-only-when-inactive'
                    ),
                    $this->generateText(
                        'business_disabled_fields',
                        $this->l('... business disabled fields'),
                        $this->l('Comma separated list of fields HIDDEN for business customers (visible only for others)'),
                        array(),
                        $this->l('Inactive because "Show I am a business" is disabled.'),
                        'desc-visible-only-when-inactive'
                    ),
                    $this->generateSwitch(
                        'show_i_am_private',
                        $this->l('Show "I am private customer" checkbox'),
                        $this->l('Show checkbox on top of Invoice address, which would expand (typically) dni field')
                    ),
                    $this->generateText(
                        'private_fields',
                        $this->l('... private customer fields'),
                        $this->l('Comma separated list of fields shown in separate section for private customers'),
                        array(),
                        $this->l('Inactive because "Show I am a private" is disabled.'),
                        'desc-visible-only-when-inactive'
                    ),
                    $this->generateSwitch(
                        'offer_second_address',
                        $this->l('Offer second address'),
                        $this->l('In primary address (invoice), show checkbox to expand secondary address (delivery)')
                    ),
                    $this->generateSwitch(
                        'expand_second_address',
                        $this->l('Auto-expand second address'),
                        $this->l('Make both addresses (invoice + delivery) visible right away')
                    ),
                    $this->generateSwitch(
                        'mark_required_fields',
                        $this->l('Mark required fields (*)'),
                        $this->l('Show red star next to required fields label')
                    ),
                    $this->generateSwitch(
                        'newsletter_checked',
                        $this->l('Newsletter checked by default'),
                        $this->l('Newsletter checkbox will be ticked by default - ps_emailsubscription module must be enabled!')
                    ),
                    $this->generateSwitch(
                        'show_call_prefix',
                        $this->l('Show call prefix'),
                        $this->l('Display call prefix number in front of phone number fields - dynamically changed based on selected country')
                    ),
                    $this->generateSwitch(
                        'initialize_address',
                        $this->l('Initialize Address'),
                        $this->l('On initial load, set the address object - enable if your shipping methods depend on address ID or if you use delivery date/time widget')
                    ),
                    $this->generateSwitch(
                        'show_button_save_personal_info',
                        $this->l('Show "Save" button in Personal Info'),
                        $this->l('Display button to save guest/account before showing shipping and payment methods')
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $enforcedSeparatePaymentModules = array('xps_checkout', 'braintreeofficial');
        $separate_payment_required = false;
        foreach ($enforcedSeparatePaymentModules as $moduleName) {
            if (Module::isInstalled($moduleName) && Module::isEnabled($moduleName)) {
                $separate_payment_required = true;
            }
        }

        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'legend'  => array(
                    'title' => $this->l('Shipping & Payment'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    $this->generateSwitch(
                        'force_customer_to_choose_country',
                        $this->l('Force customer to choose country'),
                        $this->l('Hides shipping methods and de-select country at the beginning, so that customer has to choose country manually')
                    ),
                    $this->generateText(
                        'shipping_required_fields',
                        $this->l('Shipping required fields'),
                        $this->l('Comma separated list of fields that need to be filled-in to show shipping options, e.g.: id_state, postcode, city')
                    ),
                    $this->generateSwitch(
                        'force_customer_to_choose_carrier',
                        $this->l('Force customer to choose carrier'),
                        $this->l('De-select default carrier and force customer to make his own selection')
                    ),
                    $this->generateSwitch(
                        'show_shipping_country_in_carriers',
                        $this->l('Show "shipping to" in carriers'),
                        $this->l('Show shipping country name in carriers selection, for better clarity')
                    ),
                    $this->generateSwitch(
                        'postcode_remove_spaces',
                        $this->l('Remove spaces from postcode'),
                        $this->l('When postcode field is modified, inner-spaces are removed automatically')
                    ),
                    $this->generateSwitch(
                        'show_order_message',
                        $this->l('Show Order Message'),
                        $this->l('Show Textarea for arbitrary order message')
                    ),
                    $this->generateSwitch(
                        'separate_payment',
                        $this->l('Payment options on separate page'),
                        $this->l('Final payment options list will be displayed on separate page. Optional for any payment method, but required if you have: [Prestashop Checkout or Braintree Official]'),
                        array(),
                        ($separate_payment_required ? $this->l('Option enforced, because [Prestashop Checkout or Braintree official] payment module is enabled') : ''),
                        ($separate_payment_required ? 'inactive' : '')
                    ),
                    $this->generateSelect(
                        'default_payment_method',
                        $this->l('Default payment method'),
                        $this->l('Which payment method shall be selected by default'),
                        $paymentOptionsCombo
                    ),
                    $this->generateText(
                        'payment_required_fields',
                        $this->l('Payment required fields'),
                        $this->l('Comma separated list of fields that need to be filled-in to show payment options, e.g.: id_state, lastname'),
                        array(),
                        $this->l('Inactive because "Payment options on separate page" is enabled.'),
                        'desc-visible-only-when-inactive'
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'legend'  => array(
                    'title' => $this->l('Address fields'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'TC_customer_fields',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'TC_invoice_fields',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'TC_delivery_fields',
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'legend'  => array(
                    'title' => $this->l('Layout'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'TC_blocks_layout',
                    ),
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('HTML Box No.1'),
                        'name'         => 'TC_html_box_1',
                        'lang'         => true,
                        'autoload_rte' => '', //'rte' = enable TinyMCE editor, empty = not enabled
                        'class'        => 'tinymce-on-demand',
                        'col'          => 8,
                        'hint'         => $this->trans('Invalid characters:', array(),
                                'Admin.Notifications.Info') . ' &lt;&gt;;=#{}'
                    ),
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('HTML Box No.2'),
                        'name'         => 'TC_html_box_2',
                        'lang'         => true,
                        'autoload_rte' => '', //'rte' = enable TinyMCE editor, empty = not enabled
                        'class'        => 'tinymce-on-demand',
                        'col'          => 8,
                        'hint'         => $this->trans('Invalid characters:', array(),
                                'Admin.Notifications.Info') . ' &lt;&gt;;=#{}'
                    ),
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('HTML Box No.3'),
                        'name'         => 'TC_html_box_3',
                        'lang'         => true,
                        'autoload_rte' => '', //'rte' = enable TinyMCE editor, empty = not enabled
                        'class'        => 'tinymce-on-demand',
                        'col'          => 8,
                        'hint'         => $this->trans('Invalid characters:', array(),
                                'Admin.Notifications.Info') . ' &lt;&gt;;=#{}'
                    ),
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('HTML Box No.4'),
                        'name'         => 'TC_html_box_4',
                        'lang'         => true,
                        'autoload_rte' => '', //'rte' = enable TinyMCE editor, empty = not enabled
                        'class'        => 'tinymce-on-demand',
                        'col'          => 8,
                        'hint'         => $this->trans('Invalid characters:', array(),
                                'Admin.Notifications.Info') . ' &lt;&gt;;=#{}'
                    ),
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('Required Checkbox No.1'),
                        'name'         => 'TC_required_checkbox_1',
                        'desc'         => 'To enable a required checkbox in checkout page, fill-in the checkbox label here. You can add label also with link, for example: <' . 'br' . '><' . 'b' . '>' . 'I agree with &lt;a href="content/3-privacy-policy"&gt;privacy policy&lt;/a&gt;<' . '/b' . '>',
                        'lang'         => true,
                        'autoload_rte' => '', //'rte' = enable TinyMCE editor, empty = not enabled
                        'class'        => 'tinymce-on-demand',
                        'col'          => 8,
                        'hint'         => $this->trans(
                            'Arbitrary checkbox that user needs to confirm to proceed with order, fill in text to enable.',
                            array(),
                            'Admin.Notifications.Info'
                        )
                    ),
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('Required Checkbox No.2'),
                        'name'         => 'TC_required_checkbox_2',
                        'lang'         => true,
                        'autoload_rte' => '', //'rte' = enable TinyMCE editor, empty = not enabled
                        'class'        => 'tinymce-on-demand',
                        'col'          => 8,
                        'hint'         => $this->trans(
                            'Arbitrary checkbox that user needs to confirm to proceed with order, fill in text to enable.',
                            array(),
                            'Admin.Notifications.Info'
                        )
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'legend'  => array(
                    'title' => $this->l('Social login'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    $this->generateSwitch(
                        'social_login_fb',
                        $this->l('Facebook Login'),
                        $this->l('Enable Facebook Login')
                    ),
                    $this->generateText(
                        'social_login_fb_app_id',
                        $this->l('Facebook App ID'),
                        $this->l('App ID from Facebook developers API')
                    ),
                    $this->generateText(
                        'social_login_fb_app_secret',
                        $this->l('Facebook App Secret'),
                        $this->l('App Secret from Facebook developers API')
                    ),
                    $this->generateSwitch(
                        'social_login_google',
                        $this->l('Google Sign-In'),
                        $this->l('Enable Google Sign-In')
                    ),
                    $this->generateText(
                        'social_login_google_client_id',
                        $this->l('Google Client ID'),
                        $this->l('Client ID from Google developers API')
                    ),
                    $this->generateText(
                        'social_login_google_client_secret',
                        $this->l('Google Client Secret'),
                        $this->l('Client Secret from Google developers API')
                    ),
                    $this->generateSelect(
                        'social_login_btn_style',
                        $this->l('Style of login buttons'),
                        $this->l('Pre-defined styles, choose one and make further customizations in custom.css'),
                        array(
                            array('id' => 'light', 'name' => 'Light theme'),
                            array('id' => 'bootstrap', 'name' => 'Bootstrap, full colors'),
                        )
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'legend'  => array(
                    'title' => $this->l('Advanced'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
//                    $this->generateSwitch(
//                        'refresh_minicart',
//                        $this->l('Refresh mini-cart'),
//                        $this->l('On each cart update, also updata mini-cart (available for some themes only)')
//                    ),
                    $this->generateSwitch(
                        'clean_checkout_session_after_confirmation',
                        $this->l('Clean checkout session'),
                        $this->l('Clean remembered status of checkboxes (Terms & conditions, Customer privacy, ...) after order is confirmed')
                    ),
                    $this->generateText(
                        'ps_css_cache_version',
                        $this->l('PS CSS cache version'),
                        $this->l('Increase if changes in CSS files do not reflect on frontend')
                    ),
                    $this->generateText(
                        'ps_js_cache_version',
                        $this->l('PS JS cache version'),
                        $this->l('Increase if changes in JS files do not reflect on frontend')
                    ),
// 30.7.2021 - setting auto_render to false is not necessary anymore due to improvements in PS Checkout payment module.
//                    $this->generateSwitch(
//                        'ps_checkout_auto_render_disabled',
//                        $this->l('PS Checkout Auto Render Disabled'),
//                        $this->l('Set ps_checkout_auto_render_disabled option')
//                    ),
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('Custom CSS'),
                        'name'         => 'TC_custom_css',
                        'lang'         => false,
                        'cols'         => 60,
                        'rows'         => 7,
                        'autoload_rte' => false, //Enable TinyMCE editor for short description
                        'col'          => 6,
                        'hint'         => $this->l('Custom CSS used on checkout page'),
                        'class'        => 'max-size'
                    ),
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('Custom JS'),
                        'name'         => 'TC_custom_js',
                        'lang'         => false,
                        'cols'         => 60,
                        'rows'         => 7,
                        'autoload_rte' => false, //Enable TinyMCE editor for short description
                        'col'          => 6,
                        'hint'         => $this->l('Custom JS, (!) consider that jQuery might be loaded later, use it only in plain JS DOMready handler!'),
                        'class'        => 'max-size'
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $tc_options = $this->config->getAllOptions('TC_', true);

        // hide sensitive data in demo
        if ('demo@demo.com' == $this->context->employee->email) {
            $tc_options['TC_social_login_fb_app_secret']        = '200775a7d7b096e5d2f12c2b1aab9a87';
            $tc_options['TC_social_login_google_client_secret'] = 'K0hWjDCblEGcMwTqEjr-HbdF';
        }

        $other_options = array(
            'XYZ_LIVE_MODE' => Configuration::get('XYZ_LIVE_MODE', true),
        );

        $languages              = Language::getLanguages(false);
        $fields_localized       = array();
        $fields_localized_names = array(
            'TC_html_box_1',
            'TC_html_box_2',
            'TC_html_box_3',
            'TC_html_box_4',
            'TC_required_checkbox_1',
            'TC_required_checkbox_2'
        );
        foreach ($languages as $lang) {
            foreach ($fields_localized_names as $name) {
                $fields_localized[$name][(int)$lang['id_lang']] = Tools::getValue(
                    $name . (int)$lang['id_lang'],
                    Configuration::get($name, (int)$lang['id_lang'])
                );
            }

        }
        return array_merge($tc_options, $other_options, $fields_localized);
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if ('demo@demo.com' == $this->context->employee->email) {
            return 'This is DEMO store, set in <' . 'b' . '>read-only mode<' . '/' . 'b' . '>, settings cannot be updated.';
        }
        $errors = '';

        $form_values = array_merge($this->config->getAllOptions(''), array(
            'XYZ_LIVE_MODE' => Configuration::get('XYZ_LIVE_MODE', true),
        ));

        foreach (array_keys($form_values) as $key) {

            $errors .= $this->config->updateByName($key);
            //echo "updating $key with: ".Tools::getValue($key)."\n\n";
        }

        return $errors;
    }
}
