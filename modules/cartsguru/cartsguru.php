<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @author    Carts Guru <prestashop@carts.guru>
 * @copyright Since 2017 Carts Guru
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/* Change precision for json prices with decimals (prices) */
if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set('serialize_precision', -1);
}

// Enable / Disable Debug mode in this module
define('CARTSGURU_DEBUG_MODE', 0);

/* Load Shared */
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/shared/BehaviorInterface.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/shared/Constants.php';

/* Load Helper */
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/helper.php';

/* Load Models */
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/customer.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/tracker.php';

/* Load Behaviors */
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/aggregateCart.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/aggregateContact.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/aggregateOrder.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/deleteCoupons.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/deleteHooks.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/deleteScripts.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/getContacts.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/getCoupons.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/getHooks.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/getOrders.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/getScripts.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/postCoupons.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/postHooks.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/postScripts.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/behavior/registerPlugin.php';

/* Load Converters */
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/converter/abstractBase.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/converter/abstractSales.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/converter/cart.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/converter/contact.php';
require_once _PS_ROOT_DIR_ . '/modules/cartsguru/model/converter/order.php';

class CartsGuru extends Module
{
    const CARTSGURU_HMAC_ALGORITHM = 'sha256';

    const CARTSGURU_DATE_FORMAT = 'Y-m-d\TH:i:sP';

    public $views_url;

    public $id_shop_group;

    public $id_shop;

    private $module_url;

    private $images_url;

    private $id_language;

    private $behaviors;

    private $helper;

    private $tracker;

    private $shop_list;

    public function __construct()
    {
        $this->name = 'cartsguru';
        $this->tab = 'advertising_marketing';
        $this->version = '2.4.8';
        $this->author = 'Carts Guru';
        $this->module_key = 'f841e8edc4514a141082e10c797c7c57';

        $this->views_url = _PS_ROOT_DIR_ . '/' . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views';
        $this->module_url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name;
        $this->images_url = $this->module_url . '/views/img/';

        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6.0.1',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        $this->helper = new CGHelper();

        $this->tracker = new CGTracker($this->helper, Context::getContext());

        $this->id_shop_group = null;
        $this->id_shop = null;
        $this->id_language = null;
        $this->shop_list = null;
        if (Shop::isFeatureActive()) {
            $this->id_shop_group = (int) Shop::getContextShopGroupID();
            $this->id_shop = (int) Shop::getContextShopID();
            $this->id_language = (int) Context::getContext()->language->id;
            $this->shop_list = [Context::getContext()->shop->id];
        }

        $this->behaviors = [
            'aggregateCart' => new AggregateCart(),
            'aggregateContact' => new AggregateContact(),
            'aggregateOrder' => new AggregateOrder(),
            'registerPlugin' => new RegisterPlugin(),
        ];

        parent::__construct();

        $this->displayName = $this->l('Carts Guru');
        $this->description = $this->l('Your multichannel solution for easy recovering your abandoned shopping carts.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function getIdShopGroup()
    {
        return $this->id_shop_group;
    }

    public function getIdShop()
    {
        return $this->id_shop;
    }

    public function getIdLanguage()
    {
        return $this->id_language;
    }

    /**
     * Install && Uninstall
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install()) {
            return false;
        }

        if (!$this->registerHook('displayBackOfficeHeader')) {
            return false;
        }

        Configuration::updateValue(
            'CARTS_GURU_SETTINGS_RAW_ENABLED',
            1,
            false,
            $this->id_shop_group,
            $this->id_shop
        );

        Configuration::updateValue(
            'CARTS_GURU_SETTINGS_PRODUCT_CATALOG_ENABLED',
            1,
            false,
            $this->id_shop_group,
            $this->id_shop
        );

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        Configuration::deleteByName('CARTS_GURU_SETTINGS_SITE_ID');
        Configuration::deleteByName('CARTS_GURU_SETTINGS_AUTH_KEY');
        Configuration::deleteByName('CARTS_GURU_API_URL');

        Configuration::deleteByName('CARTS_GURU_HOOKS_CART_URL');
        Configuration::deleteByName('CARTS_GURU_HOOKS_CONTACT_URL');
        Configuration::deleteByName('CARTS_GURU_HOOKS_ORDER_URL');
        Configuration::deleteByName('CARTS_GURU_REGISTER_SCRIPT_SRC');

        Configuration::deleteByName('CARTS_GURU_SETTINGS_RAW_ENABLED');
        Configuration::deleteByName('CARTS_GURU_SETTINGS_PRODUCT_CATALOG_ENABLED');

        return true;
    }

    /**
     * Main getContent
     */
    public function getContent()
    {
        $this->log('cartsguru > getContent');

        $cSI = 'CARTS_GURU_SETTINGS_SITE_ID';
        $cAK = 'CARTS_GURU_SETTINGS_AUTH_KEY';
        $cAU = 'CARTS_GURU_API_URL';

        $output = null;

        if (Tools::isSubmit('submitConnect')) {
            $siteId = Tools::getValue('siteid');
            $authKey = Tools::getValue('authkey');
            $reset = Tools::getValue('reset-plugin');

            if ($reset) {
                Configuration::updateValue($cSI, null, false, $this->id_shop_group, $this->id_shop);
                Configuration::updateValue($cAK, null, false, $this->id_shop_group, $this->id_shop);
                Configuration::updateValue($cAK, null, false, $this->id_shop_group, $this->id_shop);

                $this->log('Reset Credentials');
                $output .= $this->displayConfirmation($this->l('Reset credentials'));
            } elseif (Validate::isGenericName($siteId) && Validate::isGenericName($authKey)) {
                $siteId = Tools::strtolower($siteId);
                $authKey = Tools::strtolower($authKey);

                $context = [
                    'siteId' => $siteId,
                    'authKey' => $authKey,
                    'idShop' => $this->id_shop,
                    'idShopGroup' => $this->id_shop_group,
                ];

                Configuration::updateValue($cSI, $siteId, false, $this->id_shop_group, $this->id_shop);
                Configuration::updateValue($cAK, $authKey, false, $this->id_shop_group, $this->id_shop);

                $registration = $this->behaviors['registerPlugin']->execute($context);
                if (false === $registration || !isset($registration->data) || $registration->data->siteId != $siteId) {
                    $this->log('Adminhtml/Admin - Execute : KO');

                    $this->log('API registration failed');
                    $output .= $this->displayError($this->l('Connection error'));
                } else {
                    $apiUrl = $registration->data->apiUrl;

                    Configuration::updateValue($cAU, $apiUrl, false, $this->id_shop_group, $this->id_shop);

                    $this->log('API registration successfull');
                    $output .= $this->displayConfirmation($this->l('Successfully connected'));
                }
            } else {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            }
        }

        return $output . $this->displayForm();
    }

    /**
     * Configuration Form
     */

     // @TODO : REVISE
    public function displayForm()
    {
        $html = '';

        $employee = $this->context->employee;
        $countries = CountryCore::getCountries($employee->id_lang, false, false, false);
        $countries_array = [];
        foreach ($countries as $country) {
            $countries_array[$country['id_country']] = $country['name'];
        }

        $cSI = 'CARTS_GURU_SETTINGS_SITE_ID';
        $cAK = 'CARTS_GURU_SETTINGS_AUTH_KEY';
        $siteId = Configuration::get($cSI, false, $this->id_shop_group, $this->id_shop);
        $authKey = Configuration::get($cAK, false, $this->id_shop_group, $this->id_shop);

        $configs = [
            'CARTS_GURU_SETTINGS_SITE_ID' => Tools::getValue('siteid', $siteId),
            'CARTS_GURU_SETTINGS_AUTH_KEY' => Tools::getValue('authkey', $authKey),
            'CARTS_GURU_API_SUCCESS' => Configuration::get('CARTS_GURU_API_SUCCESS'),
        ];

        $options = [
           'isSubmitSuccess' => (int) $configs['CARTS_GURU_API_SUCCESS'] == 1,
           'canConfigure' => (version_compare(_PS_VERSION_, '1.5.0', '<') ||
                              !Shop::isFeatureActive() ||
                              Shop::getContext() == Shop::CONTEXT_SHOP),
        ];

        $formUrl = $this->context->link->getAdminLink('AdminModules', false);
        $formUrl .= '&configure=' . $this->name . '&tab_module=' . $this->tab;
        $formUrl .= '&module_name=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

        $variables = [
           'siteid' => $configs['CARTS_GURU_SETTINGS_SITE_ID'] ? $configs['CARTS_GURU_SETTINGS_SITE_ID'] : '',
           'authkey' => $configs['CARTS_GURU_SETTINGS_AUTH_KEY'] ? $configs['CARTS_GURU_SETTINGS_AUTH_KEY'] : '',
           'formUrl' => $formUrl,
           'imagesUrl' => $this->images_url,
           'activeView' => '',
           'countries' => $countries_array,
        ];

        if ($options['canConfigure'] == false) {
            $variables['activeView'] = 'view-no-store-selected';
        } elseif ($options['isSubmitSuccess'] == true) {
            $variables['activeView'] = 'view-success';
        } elseif (Tools::isSubmit('submitHasNoAccount') || Tools::isSubmit('submitSubscribe')) {
            $variables['activeView'] = 'view-try-it';
        } elseif (!empty($configs['CARTS_GURU_SETTINGS_SITE_ID']) && !empty($configs['CARTS_GURU_SETTINGS_SITE_ID'])) {
            $variables['activeView'] = 'view-have-account';
        }

        $employee = $this->context->employee;
        $shop = $this->context->shop;
        $phoneNumber = Configuration::get('PS_SHOP_PHONE', null, null, $shop->id);

        $storeInformation = [
            'country' => Configuration::get('PS_COUNTRY_DEFAULT', null, null, $shop->id),
            'phoneNumber' => $phoneNumber ? $phoneNumber : '',
            'website' => _PS_BASE_URL_,
            'email' => $employee->email,
            'lastname' => $employee->lastname,
            'firstname' => $employee->firstname,
            'language' => Language::getIsoById((int) $employee->id_lang),
         ];

        $variables = array_merge($variables, $storeInformation);

        $this->context->smarty->assign($variables);
        $html .= $this->context->smarty->fetch($this->views_url . '/templates/admin/welcome.tpl');

        return $html;
    }

    /**
     * Register Hooks
     */
    public function registerCartsHooks($remoteUrl)
    {
        if (!$this->registerHook('actionCartSave', $this->shop_list)) {
            return false;
        }

        $cCU = 'CARTS_GURU_HOOKS_CART_URL';
        Configuration::updateValue($cCU, $remoteUrl, false, $this->id_shop_group, $this->id_shop);

        return true;
    }

    public function registerContactsHooks($remoteUrl)
    {
        if (!$this->registerHook('actionObjectCustomerAddAfter', $this->shop_list)
            || !$this->registerHook('actionObjectCustomerUpdateAfter', $this->shop_list)
            || !$this->registerHook('actionObjectAddressAddAfter', $this->shop_list)
            || !$this->registerHook('actionObjectAddressUpdateAfter', $this->shop_list)) {
            return false;
        }

        if (Module::isInstalled('ps_emailsubscription')
            && version_compare(Module::getInstanceByName('ps_emailsubscription')->version, '2.6.0', '>=')
            && !$this->registerHook('actionNewsletterRegistrationAfter', $this->shop_list)) {
            return false;
        }

        $cCU = 'CARTS_GURU_HOOKS_CONTACT_URL';
        Configuration::updateValue($cCU, $remoteUrl, false, $this->id_shop_group, $this->id_shop);

        return true;
    }

    public function registerOrdersHooks($remoteUrl)
    {
        if (!$this->registerHook('actionValidateOrder', $this->shop_list)
            || !$this->registerHook('actionOrderStatusPostUpdate', $this->shop_list)) {
            return false;
        }

        $cOU = 'CARTS_GURU_HOOKS_ORDER_URL';
        Configuration::updateValue($cOU, $remoteUrl, false, $this->id_shop_group, $this->id_shop);

        return true;
    }

    public function registerScriptsHooks($src)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            if (!$this->registerHook('displayBeforeBodyClosingTag', $this->shop_list)) {
                return false;
            }
        } elseif (version_compare(_PS_VERSION_, '1.5.0', '>=')) {
            if (!$this->registerHook('displayHeader', $this->shop_list)) {
                return false;
            }
        }

        $cSrc = 'CARTS_GURU_REGISTER_SCRIPT_SRC';
        Configuration::updateValue($cSrc, $src, false, $this->id_shop_group, $this->id_shop);

        return true;
    }

    /**
     * Unregister Hooks
     */
    public function unregisterCartsHooks()
    {
        if (!$this->unregisterHook('actionCartSave', $this->shop_list)) {
            return false;
        }

        if (Shop::isFeatureActive()) {
            Configuration::deleteFromContext('CARTS_GURU_HOOKS_CART_URL');
        } else {
            Configuration::deleteByName('CARTS_GURU_HOOKS_CART_URL');
        }

        return true;
    }

    public function unregisterContactsHooks()
    {
        if (!$this->unregisterHook('actionObjectCustomerAddAfter', $this->shop_list)
            || !$this->unregisterHook('actionObjectCustomerUpdateAfter', $this->shop_list)
            || !$this->unregisterHook('actionObjectAddressAddAfter', $this->shop_list)
            || !$this->unregisterHook('actionObjectAddressUpdateAfter', $this->shop_list)) {
            return false;
        }

        if (Shop::isFeatureActive()) {
            Configuration::deleteFromContext('CARTS_GURU_HOOKS_CONTACT_URL');
        } else {
            Configuration::deleteByName('CARTS_GURU_HOOKS_CONTACT_URL');
        }

        return true;
    }

    public function unregisterOrdersHooks()
    {
        if (!$this->unregisterHook('actionValidateOrder', $this->shop_list)
            || !$this->unregisterHook('actionOrderStatusPostUpdate', $this->shop_list)) {
            return false;
        }

        if (Shop::isFeatureActive()) {
            Configuration::deleteFromContext('CARTS_GURU_HOOKS_ORDER_URL');
        } else {
            Configuration::deleteByName('CARTS_GURU_HOOKS_ORDER_URL');
        }

        return true;
    }

    public function unregisterScriptsHooks()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            if (!$this->unregisterHook('displayBeforeBodyClosingTag', $this->shop_list)) {
                return false;
            }
        } else {
            if (!$this->unregisterHook('displayHeader', $this->shop_list)) {
                return false;
            }
        }

        if (Shop::isFeatureActive()) {
            Configuration::deleteFromContext('CARTS_GURU_REGISTER_SCRIPT_SRC');
        } else {
            Configuration::deleteByName('CARTS_GURU_REGISTER_SCRIPT_SRC');
        }

        return true;
    }

    /**
     *  Hooks
     */

    /**
     * this hook is call many times update the cart
     * The module catch only cart have customer logged
     */
    public function hookActionCartSave($params)
    {
        $cart = $params['cart'];

        if (isset($params['cart']) && Validate::isLoadedObject($params['cart'])) {
            $cart = $params['cart'];
        } elseif (isset(Context::getContext()->cart)
            && Validate::isLoadedObject(Context::getContext()->cart)) {
            $cart = Context::getContext()->cart;
        } else {
            return false;
        }

        if (isset($cart) && (int) $cart->id) {
            $converterCart = new CGCart();
            $cart = new Cart($cart->id);
            $jsonCart = $converterCart->getCart($cart, true);

            if (!$jsonCart) {
                return false;
            }

            if ($this->isJsonSent('Cart', $jsonCart)) {
                return false;
            }

            $behavior = $this->behaviors['aggregateCart'];
            if (null === $behavior) {
                throw new Exception("Behavior doesn\'t exists.");
            }

            $jsonParams = json_encode($jsonCart, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $behavior->execute($jsonParams);

            $this->saveJsonSent('Cart', $jsonCart);

            $this->log('hookActionCartSave');

            return true;
        }

        return false;
    }

    /**
     * Successful create customer
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionObjectCustomerAddAfter($params)
    {
        $this->log('hookActionObjectCustomerAddAfter');

        return $this->hookActionObjectCustomerUpdateAfter($params);
    }

    /**
     * Customer update information
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionObjectCustomerUpdateAfter($params)
    {
        $this->log('hookActionObjectCustomerUpdateAfter');

        $customer = $params['object'];
        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        if (isset($customer) && (int) $customer->id) {
            $converterContact = new CGContact();
            $jsonContact = $converterContact->getContact($customer);

            if (!$jsonContact) {
                return false;
            }

            if ($this->isJsonSent('Contact', $jsonContact)) {
                return false;
            }

            $behavior = $this->behaviors['aggregateContact'];
            if (null === $behavior) {
                throw new Exception("Behavior doesn\'t exists.");
            }

            $jsonParams = json_encode($jsonContact, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $behavior->execute($jsonParams);

            $this->saveJsonSent('Contact', $jsonContact);

            $this->log('hookActionObjectCustomerUpdateAfter');

            return true;
        }

        return false;
    }

    /**
     * Customer add address
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionObjectAddressAddAfter($params)
    {
        $this->log('hookActionObjectAddressAddAfter');

        return $this->hookActionObjectAddressUpdateAfter($params);
    }

    /**
     * Customer update address
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionObjectAddressUpdateAfter($params)
    {
        $this->log('hookActionObjectAddressUpdateAfter');

        $address = $params['object'];
        if (!Validate::isLoadedObject($address)) {
            return false;
        }
        if (isset($address) && (int) $address->id) {
            if ((int) $address->id_customer) {
                $converterContact = new CGContact();
                $jsonContact = $converterContact->getContact($address);

                if (!$jsonContact) {
                    return false;
                }

                if ($this->isJsonSent('Contact', $jsonContact)) {
                    return false;
                }

                $behavior = $this->behaviors['aggregateContact'];
                if (null === $behavior) {
                    throw new Exception("Behavior doesn\'t exists.");
                }

                $jsonParams = json_encode($jsonContact, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $behavior->execute($jsonParams);

                $this->saveJsonSent('Contact', $jsonContact);

                $this->log('hookActionObjectAddressUpdateAfter');
            }
        }

        return true;
    }

    /**
     * Customer update address
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionNewsletterRegistrationAfter($params)
    {
        $this->log('START - hookActionNewsletterRegistrationAfter');

        $newsletter = ['email' => $params['email'], 'id_lang' => $params['cookie']->id_lang];

        $converterContact = new CGContact();
        $jsonContact = $converterContact->getContact($newsletter);

        if ($this->isJsonSent('Newsletter', $jsonContact)) {
            return false;
        }

        $behavior = $this->behaviors['aggregateContact'];
        if (null === $behavior) {
            throw new Exception("Behavior doesn\'t exists.");
        }

        $jsonParams = json_encode($jsonContact, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $behavior->execute($jsonParams);

        $this->saveJsonSent('Newsletter', $jsonContact);

        $this->log('END - hookActionNewsletterRegistrationAfter');

        return true;
    }

    /**
     * When order is validate, indicate it is the reminder permit
     * journal is close
     *
     * @param $params
     */
    public function hookActionValidateOrder($params)
    {
        $this->log('hookActionValidateOrder');

        $order = $params['order'];
        $params['object'] = $order;

        return $this->hookActionOrderStatusPostUpdate($params);
    }

    /**
     * Order update
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        $this->log('hookActionOrderStatusPostUpdate 1');

        if (isset($params['object']) && Validate::isLoadedObject($params['object'])) {
            $order = $params['object'];
        } elseif (isset($params['id_order'])) {
            $order = new Order((int) $params['id_order']);
        } elseif (isset($params['order'])) {
            $order = $params['order'];
        } else {
            return false;
        }

        $this->log('hookActionOrderStatusPostUpdate 2');

        if (isset($order) && (int) $order->id && (int) $order->id_customer) {
            $converterOrder = new CGOrder();
            $jsonOrder = $converterOrder->getOrder($order);

            if (!$jsonOrder) {
                return false;
            }

            if ($this->isJsonSent('Order', $jsonOrder)) {
                return false;
            }

            if (Context::getContext()->cookie->__isset('cartsguru-source')) {
                $order->source = unserialize(Context::getContext()->cookie->__get('cartsguru-source'));
            }

            $behavior = $this->behaviors['aggregateOrder'];
            if (null === $behavior) {
                throw new Exception("Behavior doesn\'t exists.");
            }

            $jsonParams = json_encode($jsonOrder, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $behavior->execute($jsonParams);

            $this->saveJsonSent('Order', $jsonOrder);

            return true;
        }

        return false;
    }

    /**
     * PRESTASHOP_VERSION ALL
     */

    /**
     * Display on each page
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        return $this->hookDisplayHeader($params);
    }

    /**
     * Display on each page
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookDisplayHeader($params)
    {
        $src = Configuration::get('CARTS_GURU_REGISTER_SCRIPT_SRC', false, $this->id_shop_group, $this->id_shop);

        if ($src) {
            $this->tracker->loadData($this->context);
            $data = $this->tracker->getData();

            $var = [];
            $var['header_script_src'] = [
                'src' => $src,
            ];

            $var['token'] = Tools::getToken(false);

            $data->assign($var);
            $html = $this->context->smarty->fetch($this->views_url . '/templates/hook/script.tpl', $data);

            return $html;
        }

        return false;
    }

    /**
     * Display on each page
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookHeader($params)
    {
        return $this->hookDisplayHeader($params);
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BackOffice.
     */
    public function hookDisplayBackOfficeHeader()
    {
        $scripts = '';
        if (Tools::getValue('configure') == $this->name) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->controller->addJquery();
                $this->context->controller->addJS($this->views_url . '/js/admin.js');
                $this->context->controller->addCSS($this->views_url . '/css/admin.css');
            } else {
                Context::getContext()->smarty->assign(
                    [
                        'path' => $this->module_url . '/views',
                    ]
                );

                $tplPath = $this->views_url . '/templates/admin/header.tpl';
                $scripts = Context::getContext()->smarty->fetch($tplPath);
            }
        }

        return $scripts;
    }

    /**
     * Others
     */
    public function log($message, $level = FileLogger::DEBUG)
    {
        $this->helper->log($message, $level);
    }

    /**
     *  Check if the JSON is the same that the last one sent to Carts Guru.
     *
     * @param string $type
     * @param array $json
     *
     * @return string
     */
    private function isJsonSent($type, $json)
    {
        $this->log('cartsguru.php > isJsonSent - ' . $type);

        unset($json['raw']);
        $serializedJson = json_encode($json);
        $cyphered = md5($serializedJson);

        $cookieName = 'cg' . $type;

        if ($this->context->cookie->__isset($cookieName)
            && $this->context->cookie->__get($cookieName) === $cyphered) {
            return true;
        }

        return false;
    }

    /**
     *  Save a JSON from a sent call to Carts Guru on cookie.
     *
     * @param string $type
     * @param array $json
     */
    private function saveJsonSent($type, $json)
    {
        $this->log('cartsguru.php > saveJsonSent - ' . $type);

        unset($json['raw']);
        $serializedJson = json_encode($json);
        $cyphered = md5($serializedJson);

        $cookieName = 'cg' . $type;

        $this->context->cookie->__set($cookieName, $cyphered);
        $this->context->cookie->write();
    }
}
