<?php
/**
* 2007-2017 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2017 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

if (!class_exists('TinyCache')) {
    include(dirname(__FILE__).'/classes/TinyCache.php');
}

class Worldline extends PaymentModule
{
    protected $js_path = null;
    protected $css_path = null;
    protected static $lang_cache;
    /** @var protected string cache filled with informations */
    protected $cache_path;

    public function __construct()
    {
        $this->name = 'worldline';
        $this->version = '3.2.0';
        $this->tab = 'payments_gateways';
        $this->author = 'PrestaShop';
        $this->module_key = 'aa0ae9dfc53a7764232ffd3b42d4346f';
        $this->author_address = '0x64aa3c1e4034d07015f639b0e171b0d7b27d01aa';
        $this->page = basename(__FILE__, '.php');
        parent::__construct();
        $this->displayName = $this->l('Worldline');
        $this->description = $this->l('Use WORLDLINE to allow your customers to pay by Credit Card');
        $this->bootstrap = true;

        $this->js_path = $this->_path.'views/js/';
        $this->css_path = $this->_path.'views/css/';

        $this->cache_path = $this->local_path.'cache/';
        TinyCache::setPath($this->cache_path);
        $this->getLang();
    }

    public function install()
    {
        $query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'atos_test` (';
        $query .= ' `id` int(11) NOT NULL AUTO_INCREMENT,';
        $query .= ' `id_cart` varchar(250) NOT NULL,';
        $query .= ' `id_transaction` varchar(250) NOT NULL,';
        $query .= '  PRIMARY KEY (`id`)';
        $query .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

        $token = uniqid(rand(), true);
        Configuration::updateValue('ATOS_TOKEN', $token);
        unset($token);
        Configuration::updateValue('ATOS_MODE', '0');
        Configuration::updateValue('ATOS_2X_PAYMENT', '0');
        Configuration::updateValue('ATOS_3X_PAYMENT', '0');
        Configuration::updateValue('ATOS_4X_PAYMENT', '0');
        Configuration::updateValue('ATOS_3DS', '1');
        Configuration::updateValue('ATOS_2X_FROM', '0');
        Configuration::updateValue('ATOS_3X_FROM', '0');
        Configuration::updateValue('ATOS_4X_FROM', '0');
        Configuration::updateValue('ATOS_3DS_FROM', '0');
        Configuration::updateValue('ATOS_ERROR_MAIL', 'nothing');
        Configuration::updateValue('ATOS_PSP_BRAND', 'sogenactif');
        Configuration::updateValue('ATOS_ERROR_MAILS', Configuration::get('PS_SHOP_EMAIL'));
        Configuration::updateValue('ATOS_TRANSACTION_REFERENCE', '0');

        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            if (parent::install() === false
            || $this->registerHook('orderConfirmation') === false
            || $this->registerHook('paymentOptions') === false
            || Db::getInstance()->Execute($query) === false) {
                return false;
            }
        } else {
            if (parent::install() === false
            || $this->registerHook('orderConfirmation') === false
            || $this->registerHook('payment') === false
            || Db::getInstance()->Execute($query) === false) {
                return false;
            }
        }

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('ATOS_3DS_FROM');
        Configuration::deleteByName('ATOS_2X_FROM');
        Configuration::deleteByName('ATOS_3X_FROM');
        Configuration::deleteByName('ATOS_4X_FROM');
        Configuration::deleteByName('ATOS_3DS');
        Configuration::deleteByName('ATOS_2X_PAYMENT');
        Configuration::deleteByName('ATOS_3X_PAYMENT');
        Configuration::deleteByName('ATOS_4X_PAYMENT');
        Configuration::deleteByName('ATOS_MODE');
        Configuration::deleteByName('ATOS_TOKEN');
        Configuration::deleteByName('ATOS_MERCHANT_ID');
        Configuration::deleteByName('ATOS_SECRET_KEY');
        Configuration::deleteByName('ATOS_VERSION_KEY');
        Configuration::deleteByName('ATOS_TRANSACTION_REFERENCE');

        $this->unregisterHook('payment');
        $this->unregisterHook('paymentOptions');

        if (parent::uninstall() === false
            || $this->unregisterHook('orderConfirmation') === false) {
            return false;
        }

        return true;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('atos_mode')) {
            if (Tools::getIsset('atos_merchant_id')) {
                Configuration::updateValue('ATOS_MERCHANT_ID', Tools::getValue('atos_merchant_id'));
            }
            if (Tools::getIsset('atos_secret_key')) {
                Configuration::updateValue('ATOS_SECRET_KEY', Tools::getValue('atos_secret_key'));
            }
            if (Tools::getIsset('atos_version_key')) {
                Configuration::updateValue('ATOS_VERSION_KEY', Tools::getValue('atos_version_key'));
            }
            Configuration::updateValue('ATOS_PSP_BRAND', Tools::getValue('worldline_bank_selector'));
            Configuration::updateValue('ATOS_MODE', Tools::getValue('atos_mode'));
            Configuration::updateValue('ATOS_2X_PAYMENT', Tools::getValue('2x'));
            Configuration::updateValue('ATOS_2X_FROM', Tools::getValue('2x_from'));
            Configuration::updateValue('ATOS_3X_PAYMENT', Tools::getValue('3x'));
            Configuration::updateValue('ATOS_3X_FROM', Tools::getValue('3x_from'));
            Configuration::updateValue('ATOS_4X_PAYMENT', Tools::getValue('4x'));
            Configuration::updateValue('ATOS_4X_FROM', Tools::getValue('4x_from'));
            Configuration::updateValue('ATOS_ERROR_MAIL', Tools::getValue('radios'));
            Configuration::updateValue('ATOS_ERROR_MAILS', Tools::getValue('error_mails'));
            Configuration::updateValue('ATOS_3DS', Tools::getValue('3DS'));
            if (Tools::getValue('atos_mode') == 1) {
                Configuration::updateValue('ATOS_TRANSACTION_REFERENCE', Tools::getValue('TF'));
            } else {
                Configuration::updateValue('ATOS_TRANSACTION_REFERENCE', '1');
            }
        }
    }

    public function getContent()
    {
        $this->postProcess();
        $this->loadAsset();
        $this->getLang();

        // API FAQ Update
        include_once('classes/APIFAQClass.php');
        $api = new APIFAQ();
        $faq = $api->getData($this->module_key, $this->version);


        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            // Clean the code use tpl file for html
            $tab = '&tab_module='.$this->tab;
            $token_mod = '&token='.Tools::getAdminTokenLite('AdminModules');
            $token_pos = '&token='.Tools::getAdminTokenLite('AdminModulesPositions');
            $token_trad = '&token='.Tools::getAdminTokenLite('AdminTranslations');

            $index = 'index.php?controller=';
            $index_mod = 'index.php?controller=AdminModules';
            $this->context->smarty->assign(array(
                'module_active' => (bool) $this->active,
                'module_trad' => $index.'AdminTranslations'.$token_trad.'&type=modules&lang=',
                'module_hook' => $index_mod.'Positions'.$token_pos.'&show_modules='.$this->id,
                'module_back' => $index_mod.$token_mod.$tab.'&module_name='.$this->name,
                'module_form' => $index_mod.'&configure='.$this->name.$token_mod.$tab.'&module_name='.$this->name,
                'module_reset' => $index_mod.$token_mod.'&module_name='.$this->name.'&reset'.$tab,
                'lang_select' => self::$lang_cache,
            ));
            // Clean memory
            unset($tab, $token_mod, $token_pos, $token_trad);
        }

        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $lang = 'EN';   // Language for documentation in back-office
        if (Context::getContext()->language->iso_code == 'fr' || Context::getContext()->language->iso_code == 'FR') {
            $lang = 'FR';
        }

        $this->context->smarty->assign(array(
            'apifaq' => $faq,
            'tracking_url_install' => '?utm_source=modulePS&utm_medium=installation&utm_campaign='.$this->name,
            'requestUri'       => Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']),
            'module_name'      => $this->name,
            'module_path'      => $this->_path,
            'module_display'   => $this->displayName,
            'ps_version'       => (bool) version_compare(_PS_VERSION_, '1.6', '>'),
            'atos_mode'        => Configuration::get('ATOS_MODE'),
            'atos_token'       => Configuration::get('ATOS_TOKEN'),
            'atos_merchant_id' => Configuration::get('ATOS_MERCHANT_ID'),
            'atos_secret_key'  => Configuration::get('ATOS_SECRET_KEY'),
            'atos_version_key' => Configuration::get('ATOS_VERSION_KEY'),
            '2x_payment'       => Configuration::get('ATOS_2X_PAYMENT'),
            '3x_payment'       => Configuration::get('ATOS_3X_PAYMENT'),
            '4x_payment'       => Configuration::get('ATOS_4X_PAYMENT'),
            '3ds'              => Configuration::get('ATOS_3DS'),
            'transactionRef'   => Configuration::get('ATOS_TRANSACTION_REFERENCE'),
            'default_currency' => $currency->getSign(),
            '2x_from'          => Configuration::get('ATOS_2X_FROM'),
            '3x_from'          => Configuration::get('ATOS_3X_FROM'),
            '4x_from'          => Configuration::get('ATOS_4X_FROM'),
            '3ds_from'         => Configuration::get('ATOS_3DS_FROM'),
            'error_mail'       => Configuration::get('ATOS_ERROR_MAIL'),
            'error_mails'      => Configuration::get('ATOS_ERROR_MAILS'),
            'psp_brand'        => Configuration::get('ATOS_PSP_BRAND'),
            'guide_link'       => 'docs/Doc_Worldline_'.$lang.'.pdf',
            'submit'           => Tools::getIsset('submit'),
            'token'            => Tools::getValue('token'),
        ));

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    private function getCart()
    {
        $context = Context::getContext();

        return new Cart($context->cart->id);
    }

    /*
     * function that build the data string that is send to atos (1x payment only)
     * @return string
     */
    private function getAtosData($cart, $token)
    {
        $cart_total   = (int) sprintf('%f', $cart->getOrderTotal() * 100);
        $customer     = new Customer($cart->id_customer);
        $currency     = new Currency($cart->id_currency);
        $currency_num = $currency->iso_code_num;
        if (Tools::strlen($currency_num) == 2) {
            $currency_num = '0'.$currency_num;
        }

        $atos_mode = Configuration::get('ATOS_MODE');
        $merchant_id = '';

        // If we are in production mode
        if ($atos_mode) {
            $id_cart = $cart->id;
            $cart_id = $cart->id.$token;
            $merchant_id = Configuration::get('ATOS_MERCHANT_ID');
        } else {
            // Concat cart id with token in test mode, because cart id has to be unique for each merchant id
            $id_cart = $cart->id;
            $cart_id = $cart->id.$token;
            $merchant_id = '002001000000001';
        }

        $version_key = $this->getVersionKey($atos_mode);
        $language = new Language(Context::getContext()->cart->id_lang);
        $iso = strtolower(substr($language->language_code, 0, 2));

        $protocol = Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://';

        // Define the page to redirect after payment
        $return_page = $protocol.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;
        $return_page .= 'index.php?controller=order-confirmation&id_cart=';
        $return_page .= $id_cart.'&key='.$customer->secure_key.'&id_module='.$this->id;

        // Define the page called to save the order
        $ipn_page = $this->context->link->getModuleLink($this->name, 'validation');

        // Genrate transactionreference parm or not -> see with sogenactif
        if (Configuration::get('ATOS_TRANSACTION_REFERENCE')) {
            $optionnal_parm = '|transactionReference='.$cart_id;
        } else { // if transaction reference is generated by the bank we need to send cart id
            $optionnal_parm = '|orderId='.$id_cart;
        }

        $parm = 'merchantId='.$merchant_id.'|currencyCode='.$currency_num;
        $parm .= '|amount='.$cart_total.'|normalReturnUrl='.$return_page;
        $parm .= '|automaticResponseUrl='.$ipn_page.$optionnal_parm;
        $parm .= '|keyVersion='.$version_key;
        $parm .= '|paymentMeanBrandList=BCMC,VISA,CB,MASTERCARD,PAYLIB,AMEX'.'|customerLanguage='.$iso;
        $parm .= '|customerContact.email='.$customer->email;
        $parm .= '|transactionOrigin=' . substr('PSHP_' . str_replace('.', '', _PS_VERSION_) . '_PSH_' . str_replace('.', '', $this->version), 0, 20);
        if (Configuration::get('ATOS_3DS') == 0 || $cart_total / 100 < Configuration::get('ATOS_3DS_FROM')) {
            $parm .= '|fraudData.bypass3DS=All';
        }

        // MODE DEBUG, PAGE DE RETOUR ATOS = validation.php
        // Pour voir les traces du fichier validation.php
        // $parm = 'merchantId='.$merchant_id.'|currencyCode='.$currency_num.'|amount='.$cart_total.'|normalReturnUrl='.$ipn_page.'|transactionReference='.$cart_id.'|keyVersion='.$version_key;

        return $parm;
    }

    /*
     * function that build the data string for nX payment that is send to atos
     * @return string
     */
    private function getAtosDataNx($n, $cart, $token)
    {
        $cart_total = (int) sprintf('%f', $cart->getOrderTotal() * 100);
        $date1 = date('Ymd');

        // Init instalment parameters
        $parm = '|paymentPattern=INSTALMENT|instalmentData.number='.$n.'|instalmentData.datesList='.$date1;

        $atos_mode = Configuration::get('ATOS_MODE');
        $id_cart = $cart->id;
        $ref = $cart->id.$token;
        // If in test mode, concat cart id with token. Transaction id must be unique for each merchant id
        if (!$atos_mode) {
            $ref .= $token;
        }

        // Loop to generate instalment references
        for ($x = 2; $x <= $n; $x++) {
            $i = $x - 1;
            $date = date('Ymd', strtotime('+'.$i.' month'));
            $parm .= ','.$date;
            if (!$atos_mode) {
                $ref .= ','.$cart->id.$token.$x;
            } else {
                $ref .= ','.$cart->id.$token.$x;
            }
        }
        $parm .= '|instalmentData.transactionReferencesList='.$ref;
        if ($n == 2) {
            $remainder = $cart_total % 2;
            $amount1 = floor($cart_total/2);
            $amount2 = $amount1 + $remainder;

            $parm .= '|instalmentData.amountsList='.$amount1.','.$amount2;
        } elseif ($n == 3) {
            $remainder = $cart_total % 3;

            $amount1 = floor($cart_total/3);
            $amount2 = $amount1;
            $amount3 = $amount1 + $remainder;

            $parm .= '|instalmentData.amountsList='.$amount1.','.$amount2.','.$amount3;
        } elseif ($n == 4) {
            $remainder = $cart_total % 4;

            $amount1 = floor($cart_total/4);
            $amount2 = $amount1;
            $amount3 = $amount1;
            $amount4 = $amount1 + $remainder;

            $parm .= '|instalmentData.amountsList='.$amount1.','.$amount2.','.$amount3.','.$amount4;
        }

        return $parm;
    }

    public function hookPayment()
    {
        $cart   = $this->getCart();
        if ($cart->getOrderTotal() < 1.00) {
            return $this->display(__FILE__, 'views/templates/front/payment.tpl');
        }
        $this->context->controller->addCSS($this->css_path.$this->name.'.css');
        $atos_mode = Configuration::get('ATOS_MODE');

        $secret_key = worldline::getSecretKey($atos_mode);

        $token = rand(1, 99).rand(1, 99);
        $data   = utf8_encode($this->getAtosData($cart, $token));
        $data2x = false;
        $data3x = false;
        $data4x = false;

        if (Configuration::get('ATOS_2X_PAYMENT') == 1 &&
          $cart->getOrderTotal() >= Configuration::get('ATOS_2X_FROM')
        ) {
            $data2x = $data.$this->getAtosDataNx(2, $cart, $token);
        }

        if (Configuration::get('ATOS_3X_PAYMENT') == 1 &&
          $cart->getOrderTotal() >= Configuration::get('ATOS_3X_FROM')
        ) {
            $data3x = $data.$this->getAtosDataNx(3, $cart, $token);
        }

        if (Configuration::get('ATOS_4X_PAYMENT') == 1 &&
          $cart->getOrderTotal() >= Configuration::get('ATOS_4X_FROM')
        ) {
            $data4x = $data.$this->getAtosDataNx(4, $cart, $token);
        }

        // in opc mode the hookpayment is called two times, to avoid it we check PS_ORDER_PROCESS_TYPE
        // In test mode, we save the generated cart + token in database to get it after payment to save the order
        $token_cart = pSQL((int)$cart->id.$token);
        if ((int)Configuration::get('PS_ORDER_PROCESS_TYPE') === 1) {
            $check_opc = (int)Tools::isSubmit('ajax');
            if ($check_opc === 0) {
                $query = 'DELETE FROM '._DB_PREFIX_.'atos_test WHERE id_cart = '.(int)$cart->id.';
                INSERT INTO '._DB_PREFIX_.'atos_test VALUES (NULL, "'.(int)$cart->id.'", "'.$token_cart.'");';
                Db::getInstance()->Execute($query);
            }
        } else {
            $query = 'DELETE FROM '._DB_PREFIX_.'atos_test WHERE id_cart = '.(int)$cart->id.';
            INSERT INTO '._DB_PREFIX_.'atos_test VALUES (NULL, "'.(int)$cart->id.'", "'.$token_cart.'");';
            Db::getInstance()->Execute($query);
        }

        $seal = hash('sha256', $data.$secret_key);
        $seal2x = hash('sha256', $data2x.$secret_key);
        $seal3x = hash('sha256', $data3x.$secret_key);
        $seal4x = hash('sha256', $data4x.$secret_key);
        $connector_url = $this->getApiUrl((bool) $atos_mode);

        if (Configuration::get('ATOS_PSP_BRAND') == "sogenactif") {
            $logoPath = $this->_path . 'views/img/sogenactif.png';
        } else {
            $logoPath = $this->_path . 'views/img/worldline-logo.png';
        }

        $this->context->smarty->assign(array(
            'psp_brand'      => ucwords(Configuration::get('ATOS_PSP_BRAND')),
            'logoPath'       => $logoPath,
            'data'           => $data,
            'data2x'         => $data2x,
            'data3x'         => $data3x,
            'data4x'         => $data4x,
            'seal'           => $seal,
            'seal2x'         => $seal2x,
            'seal3x'         => $seal3x,
            'seal4x'         => $seal4x,
            'connector_url'  => $connector_url,
            'module_name'    => $this->name,
            'module_path'    => $this->_path,
            'module_display' => $this->displayName,
            'ps_version'     => (bool) version_compare(_PS_VERSION_, '1.6', '>'),
        ));

        return $this->display(__FILE__, 'views/templates/front/payment.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = $this->getExternalPaymentOptions($params['cart']);

        return $payment_options;
    }

    public function getExternalPaymentOptions($cart)
    {
        $result = array();
        $token = rand(1, 99).rand(1, 99);
        $token_cart = pSQL((int)$cart->id.$token);

        // In test mode, we save the generated cart + token in database to get it after payment to save the order
        $query = 'DELETE FROM '._DB_PREFIX_.'atos_test WHERE id_cart = '.(int)$cart->id.';
        INSERT INTO '._DB_PREFIX_.'atos_test VALUES (NULL, "'.(int)$cart->id.'", "'.$token_cart.'");';
        Db::getInstance()->Execute($query);

        if (Configuration::get('ATOS_PSP_BRAND') == "sogenactif") {
            $logoPath = $this->_path . 'views/img/sogenactif17.png';
        } else {
            $logoPath = $this->_path . 'views/img/worldline-logo.png';
        }

        $payment_url = $this->getApiUrl((bool) Configuration::get('ATOS_MODE'));

        //Payment 1x
        $data = $this->getAtosData($cart, $token);
        $externalOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $externalOption->setCallToActionText(
            $this->l('Pay by credit card with : ').Configuration::get('ATOS_PSP_BRAND')
        )->setAction($payment_url)
        ->setInputs(array(
            'Data' =>array(
                'name' => 'Data', 'type' => 'hidden', 'value' => $data
            ),
            'InterfaceVersion' => array(
                'name' => 'InterfaceVersion', 'type' => 'hidden', 'value' => 'HP_2.9'
            ),
            'Seal' => array(
                'name' => 'Seal', 'type' => 'hidden', 'value' => hash('sha256', $data.$this->getSecretKey())
            ),
        ))->setLogo(Media::getMediaPath($logoPath));
        array_push($result, $externalOption);
        //End Payment 1x

        if (Configuration::get('ATOS_2X_PAYMENT') == 1 &&
          $cart->getOrderTotal() >= Configuration::get('ATOS_2X_FROM')
        ) {
            $data2x = $data.$this->getAtosDataNx(2, $cart, $token);
            $externalOption2x = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

            $externalOption2x
                ->setCallToActionText($this->l('Pay by credit card with ').Configuration::get('ATOS_PSP_BRAND').' (2x Payment)')
                ->setAction($payment_url)
                ->setInputs(array(
                    'Data' => array(
                        'name' => 'Data', 'type' => 'hidden', 'value' => $data2x
                    ),
                    'InterfaceVersion' => array(
                        'name' => 'InterfaceVersion', 'type' => 'hidden', 'value' => 'HP_2.9'
                    ),
                    'Seal' => array(
                        'name' => 'Seal', 'type' => 'hidden', 'value' => hash('sha256', $data.$this->getSecretKey())
                    ),
                ))
                ->setLogo(Media::getMediaPath($logoPath));

            array_push($result, $externalOption2x);
        }

        if (Configuration::get('ATOS_3X_PAYMENT') == 1 &&
          $cart->getOrderTotal() >= Configuration::get('ATOS_3X_FROM')
        ) {
            $data3x = $data.$this->getAtosDataNx(3, $cart, $token);
            $externalOption3x = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

            $externalOption3x
                ->setCallToActionText($this->l('Pay by credit card with ').Configuration::get('ATOS_PSP_BRAND').' (3x Payment)')
                ->setAction($payment_url)
                ->setInputs(array(
                    'Data' => array(
                        'name' => 'Data', 'type' => 'hidden', 'value' => $data3x
                    ),
                    'InterfaceVersion' => array(
                        'name' => 'InterfaceVersion', 'type' => 'hidden', 'value' => 'HP_2.9'
                    ),
                    'Seal' => array(
                        'name' => 'Seal', 'type' => 'hidden', 'value' => hash('sha256', $data.$this->getSecretKey())
                    ),
                ))
                ->setLogo(Media::getMediaPath($logoPath));

            array_push($result, $externalOption3x);
        }

        if (Configuration::get('ATOS_4X_PAYMENT') == 1 &&
          $cart->getOrderTotal() >= Configuration::get('ATOS_4X_FROM')
        ) {
            $data4x = $data.$this->getAtosDataNx(4, $cart, $token);
            $externalOption4x = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

            $externalOption4x
                ->setCallToActionText($this->l('Pay by credit card with ').Configuration::get('ATOS_PSP_BRAND').' (4x Payment)')
                ->setAction($payment_url)
                ->setInputs(array(
                    'Data' => array(
                        'name' => 'Data', 'type' => 'hidden', 'value' => $data4x
                    ),
                    'InterfaceVersion' => array(
                        'name' => 'InterfaceVersion', 'type' => 'hidden', 'value' => 'HP_2.9'
                    ),
                    'Seal' => array(
                        'name' => 'Seal', 'type' => 'hidden', 'value' => hash('sha256', $data.$this->getSecretKey())
                    ),
                ))
                ->setLogo(Media::getMediaPath($logoPath));

            array_push($result, $externalOption4x);
        }

        return $result;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getSecretKey($atos_mode = null)
    {
        if (is_null($atos_mode)) {
            $atos_mode = Configuration::get('ATOS_MODE');
        }

        if ($atos_mode) {
            return Configuration::get('ATOS_SECRET_KEY');
        } else {
            return '002001000000001_KEY1';
        }
    }

    public static function getVersionKey($atos_mode = null)
    {
        if (is_null($atos_mode)) {
            $atos_mode = Configuration::get('ATOS_MODE');
        }

        if ($atos_mode) {
            return Configuration::get('ATOS_VERSION_KEY');
        } else {
            return '1';
        }
    }

    /**
     * Get Language
     * @return array Lang
     */
    private function getLang()
    {
        $cache = TinyCache::getCache('language');
        if (!empty($cache)) {
            self::$lang_cache = TinyCache::getCache('language');
            return;
        }

        if (self::$lang_cache === null) {
            if ($languages = Language::getLanguages()) {
                foreach ($languages as $row) {
                    $exprow = explode(' (', $row['name']);
                    $subtitle = (isset($exprow[1]) ? trim(Tools::substr($exprow[1], 0, -1)) : '');
                    self::$lang_cache[$row['iso_code']] = array(
                        'id'       => (int)$row['id_lang'],
                        'title'    => trim($exprow[0]),
                        'subtitle' => $subtitle,
                    );
                }
                // Cache Data
                TinyCache::setCache('language', self::$lang_cache);
                // Clean memory
                unset($row, $exprow, $subtitle, $languages);
            }
        }
    }

    /**
     * Loads asset resources
     */
    public function loadAsset()
    {
        $css_compatibility = $js_compatibility = array();

        // Load CSS
        $css = array(
            $this->css_path.'bootstrap-select.min.css',
            $this->css_path.'bootstrap-dialog.min.css',
            $this->css_path.'bootstrap.vertical-tabs.min.css',
            $this->css_path.'DT_bootstrap.css',
            $this->css_path.'fix.css',
            $this->css_path.'faq.css',
        );

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $css_compatibility = array(
                $this->css_path.'bootstrap.min.css',
                $this->css_path.'bootstrap.extend.css',
                $this->css_path.'bootstrap-responsive.min.css',
                $this->css_path.'font-awesome.min.css',
                $this->css_path.'faq.css',
            );
            $css = array_merge($css_compatibility, $css);
        }
        $this->context->controller->addCSS($css, 'all');

        // Load JS
        $js = array(
            $this->js_path.'bootstrap-select.min.js',
            $this->js_path.'bootstrap-dialog.js',
            $this->js_path.'faq.js',
            $this->js_path.$this->name.'.js',
        );

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $js_compatibility = array(
                $this->js_path.'bootstrap.min.js',
            );
            $js = array_merge($js_compatibility, $js);
        }
        $this->context->controller->addJS($js);

        // Clean memory
        unset($js, $css, $js_compatibility, $css_compatibility);
    }

    /* Fonction to validate the order */
    public function validate($id_cart, $id_order_state, $amount_paid, $transactionReference = null)
    {
        if (Configuration::get('ATOS_TRANSACTION_REFERENCE')) {
            $id_cart = $this->getIdCartByKey($id_cart);
            $id_cart_key = $id_cart;
        } else {
            $id_cart = $id_cart;
            $id_cart_key = $transactionReference;
        }

        $cart = new Cart((int)$id_cart);
        $customer = new Customer((int)$cart->id_customer);

        $this->validateOrder(
            $id_cart,
            $id_order_state,
            $amount_paid,
            $this->displayName,
            null,
            array('transaction_id' => $id_cart_key),
            null,
            false,
            $customer->secure_key
        );

        unset($this->context->cart, $this->context->cookie->id_cart, $this->context->id_cart, $cart, $customer);
    }

    /* Only in test mode, get the generated cart id that math the given cart id + token */
    private function getIdCartByKey($transaction_id)
    {
        $query = 'SELECT id_cart FROM '._DB_PREFIX_.'atos_test WHERE id_transaction = "'.(int) $transaction_id.'"';

        return Db::getInstance()->getValue($query);
    }

    public function hookOrderConfirmation($params)
    {
        if ($params['order']->module != $this->name) {
            return;
        }

        if ($params['order']->valid) {
            $this->context->smarty->assign(array(
                'status' => 'ok',
                'id_order' => $params['order']->id,
                'shop_name' => Configuration::get('PS_SHOP_NAME'),
                'contact_url' => $this->context->link->getPageLink('contact', true),
            ));
        } else {
            $this->context->smarty->assign(array(
                'status' => 'failed',
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
        }

        return $this->display(__FILE__, 'views/templates/front/hookorderconfirmation.tpl');
    }

    /**
     * Get the API url according to the configuration
     *
     * @param bool $atos_mode
     *
     * @return string
     */
    private function getApiUrl($atos_mode)
    {
        if ($atos_mode) {
            return Configuration::get('ATOS_PSP_BRAND') === 'sogenactif' ?
                'https://payment-webinit.sogenactif.com/paymentInit' :
                'https://payment-webinit.sips-services.com/paymentInit';
        }

        return 'https://payment-webinit.simu.sips-services.com/paymentInit';
    }
}
