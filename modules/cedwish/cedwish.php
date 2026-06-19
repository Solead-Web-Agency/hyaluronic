<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'cedwish/classes/api.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/queue.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/product.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/profile.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/helper.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/order.php';

class CedWish extends Module
{
    /**
     * @var string[]
     */
    public static $hooksUsedInModule = array(
        'actionProductAdd',
        'actionProductDelete',
        'actionProductUpdate',
        'actionOrderStatusPostUpdate',
        'actionAdminOrdersTrackingNumberUpdate',
        'actionUpdateQuantity',
        'actionCarrierUpdate',
        'updateCarrier'
    );
    protected $module_form_fields = array();
    protected $module_setting_key = 'CED_WISH_';

    public function __construct()
    {
        $this->name = 'cedwish';
        $this->tab = 'market_place';
        $this->version = '3.0.1';
        $this->author = 'CedCommerce';
        $this->need_instance = 0;
        $this->module_key = '3912dc2255d08bb8db35a1236856b6e4';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Wish Integration by CedCommerce');
        $this->description = $this->l(
            'Wish Integration by CedCommerce Facilitates Users to sync their items on Wish marketplace and sell there.'
        );

        $this->confirmUninstall = $this->l('Are you sure, you want to uninstall Wish Integration by Cedcommerce');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * @throws PrestaShopException
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        require_once _PS_MODULE_DIR_ . $this->name . '/sql/install.php';

        require_once _PS_MODULE_DIR_ . $this->name . '/classes/menu.php';
        $menu = new CedWishMenu($this->name);
        $menu->createTabs();

        foreach (self::$hooksUsedInModule as $hook) {
            $this->registerHook($hook);
        }

        /**
         * check for installations done
         */

        CedWishHelper::stepInstallation();

        return parent::install();
    }

    /**
     *
     */
    public function uninstall()
    {
        require_once _PS_MODULE_DIR_ . $this->name . '/sql/uninstall.php';

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        if (((bool)Tools::isSubmit('submit' . Tools::ucfirst($this->name) . 'Module')) == true) {
            $this->postProcess();
            if (!Configuration::get('CED_WISH_DEFAULT_SHIPPING_AMOUNT')) {
                $output = $this->displayError(
                    $this->l('Please Add Default Shipping Price to be used.')
                );
            } elseif (!Configuration::get('CED_WISH_ORIGIN_COUNTRY')) {
                $output = $this->displayError(
                    $this->l('Please Select Default Origin Country.')
                );
            } else {
                $output .= $this->displayConfirmation($this->l("Setting Saved Successfully."));
            }
        }
        if (Tools::getIsset('check_config') && Tools::getValue('check_config')) {
            $access_token = Configuration::get('CED_WISH_ACCESS_TOkEN');
            $warehouse = Configuration::get('CED_WISH_SELECTED_WAREHOUSES');
            if (!$warehouse) {
                $output = $this->displayError(
                    $this->l('Please Fetch and select warehouses first and then create Profile(s)')
                );
            }
            if (!$access_token) {
                $output = $this->displayError(
                    $this->l('Please Configure First and then create Profile(s)')
                );
            }
        }
        return $output . $this->renderForm();
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            if (is_array(Tools::getValue($key))) {
                Configuration::updateValue($key, json_encode(Tools::getValue($key)));
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    protected function getConfigFormValues()
    {
        $configValues = array();
        $this->module_form_fields = array(
            'API_MODE',
            'ACCESS_TOKEN',
            'REFRESH_TOKEN',
            'EXPIRY_TIME',
            'MERCHANT_ID',
            'SELECTED_WAREHOUSES',
            'ITEM_SKU',
            'DEBUG_MODE_ENABLE',
            'ITEM_GTIN',
            'ITEM_BRAND',
            'SEND_DISABLE_ITEM',
            'MAKE_INACTIVE_ITEM',
            'SIZE_MAPPING',
            'COLOR_MAPPING',
            'STATUS_MAPPING',
            'CURRENCY_ID',
            'LANG_ID',
            'ORDER_EMAIL',
            'IMAGE_TYPE',
            'PRICE_INCR_PER',
            'PRICE_INCR_FIX',
            'ORDER_CREATE_STATUS',
            'CREATE_ORDER',
            'ORDER_INVOICE',
            'CUSTOMER_GROUP_ID',
            'ORDER_CARRIER',
            'ORDER_PAYMENT',
            'ORDER_CREATE_STATUS',
            'SHIPMENT_CREATE_STATUS',
            'SHIPMENT_CREATE',
            'CARRIER_MAPPING',
            'ORIGIN_COUNTRY',
            'CRON_SECURE_KEY',
            'CANCEL_ORDER',
            'CRON_CHUNK_SIZE',
            'WISH_CURRENCY',
            'ORDER_ID_ORDER_REFERENCE',
            'STOCK_CRON_LAST_EXECUTION',
            'DEFAULT_SHIPPING_AMOUNT',
            'DEMO_IMAGE_FOR_LOCALHOST',
            'INVENTORY_THRESHOLD'
        );

        foreach ($this->module_form_fields as $field) {
            if (in_array($field, array('SELECTED_WAREHOUSES', 'COLOR_MAPPING', 'SIZE_MAPPING'))) {
                $configValues[$this->module_setting_key . $field] = json_decode(
                    Configuration::get($this->module_setting_key . $field),
                    true
                );
            } elseif ($this->module_setting_key . $field == 'CED_WISH_CRON_SECURE_KEY') {
                if (Configuration::get('CED_WISH_CRON_SECURE_KEY')) {
                    $configValues['CED_WISH_CRON_SECURE_KEY'] = Configuration::get('CED_WISH_CRON_SECURE_KEY');
                } else {
                    $configValues['CED_WISH_CRON_SECURE_KEY'] = Tools::passwdGen(10);
                }
            } elseif ($this->module_setting_key . $field == 'CED_WISH_CRON_CHUNK_SIZE') {
                if (Configuration::get('CED_WISH_CRON_CHUNK_SIZE')) {
                    $configValues['CED_WISH_CRON_CHUNK_SIZE'] = Configuration::get('CED_WISH_CRON_CHUNK_SIZE');
                } else {
                    $configValues['CED_WISH_CRON_CHUNK_SIZE'] = 25;
                }
            } else {
                $configValues[$this->module_setting_key . $field] =
                    Configuration::get($this->module_setting_key . $field);
            }
        }

        return $configValues;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . Tools::ucfirst($this->name) . 'Module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $fields_value = $this->getConfigFormValues();

        if (isset($fields_value['CED_WISH_SIZE_MAPPING'])) {
            $fields_value['CED_WISH_SIZE_MAPPING[]'] = $fields_value['CED_WISH_SIZE_MAPPING'];
            unset($fields_value['CED_WISH_SIZE_MAPPING']);
        } else {
            $fields_value['CED_WISH_SIZE_MAPPING[]'] = [];
            unset($fields_value['CED_WISH_SIZE_MAPPING']);
        }

        if (isset($fields_value['CED_WISH_COLOR_MAPPING'])) {
            $fields_value['CED_WISH_COLOR_MAPPING[]'] = $fields_value['CED_WISH_COLOR_MAPPING'];
            unset($fields_value['CED_WISH_COLOR_MAPPING']);
        } else {
            $fields_value['CED_WISH_COLOR_MAPPING[]'] = [];
            unset($fields_value['CED_WISH_COLOR_MAPPING']);
        }

        $helper->tpl_vars = array(
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        $this->context->smarty->assign(
            array(
                'image_path' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/'
            )
        );
        $token_generation = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/configuration/general_help.tpl'
        );
        return $token_generation . $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $config_form = array();
        $config_form['0'] = $this->getGeneralForm();
        $config_form['1'] = $this->getProductForm();
        $config_form['2'] = $this->getOrderForm();
        $config_form['3'] = $this->getCronForm();
        return $config_form;
    }

    protected function getGeneralForm()
    {
        $this->context->smarty->assign(
            array(
                'auth_check_token' => Tools::getAdminTokenLite('AdminCedWishSetting'),
                'CED_WISH_ACCESS_TOKEN' => Configuration::get('CED_WISH_ACCESS_TOKEN'),
                'CED_WISH_REFRESH_TOKEN' => Configuration::get('CED_WISH_REFRESH_TOKEN'),
            )
        );
        $token_generation = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/configuration/general_settings.tpl'
        );
        $general_config_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('API Setting(s)'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 4,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('API Mode.'),
                        'name' => $this->module_setting_key . 'API_MODE',
                        'label' => $this->l('API URL'),
                        'options' => array(
                            'query' => array(
                                array('value' => 0, 'label' => 'Choose API Mode'),
                                array('value' => 2, 'label' => 'Sandbox Mode'),
                                array('value' => 1, 'label' => 'Live Mode'),
                            ),
                            'id' => 'value',
                            'name' => 'label',
                        )
                    ),
                    array(
                        'col' => 4,
                        'type' => 'html',
                        'name' => $token_generation
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        if (Configuration::get($this->module_setting_key . 'ACCESS_TOKEN')) {
            $general_config_form['form']['input'][] = array(
                'col' => 3,
                'type' => 'text',
                'desc' => $this->l(
                    'Do not change them, remove them when you want to reauthorize'
                ),
                'name' => $this->module_setting_key . 'ACCESS_TOKEN',
                'label' => $this->l('Access Token'),
            );
        }
        if (Configuration::get($this->module_setting_key . 'REFRESH_TOKEN')) {
            $general_config_form['form']['input'][] = array(
                'col' => 3,
                'type' => 'text',
                'desc' => $this->l(
                    'Do not change them, remove them when you want to reauthorize'
                ),
                'name' => $this->module_setting_key . 'REFRESH_TOKEN',
                'label' => $this->l('Refresh Token'),
            );
        }

        if (Configuration::get($this->module_setting_key . 'MERCHANT_ID')) {
            $general_config_form['form']['input'][] = array(
                'col' => 3,
                'type' => 'text',
                'readonly' => true,
                'desc' => $this->l(
                    'Do not change them, remove them when you want to reauthorize'
                ),
                'name' => $this->module_setting_key . 'MERCHANT_ID',
                'label' => $this->l('Merchant ID'),
            );
        }
        $warehouses_list = array();
        $query = new DbQuery();
        $query->select('id , name');
        $query->from('cedwish_warehouse');
        try {
            if ($result = Db::getInstance()->executeS($query)) {
                $warehouses_list = $result;
            }
        } catch (PrestaShopDatabaseException $e) {
        }

        if (empty($warehouses_list)) {
            $apiHelper = new CedWishApi();
            $response = $apiHelper->getMerchantWarehouses();
            if (isset($response['code']) && ($response['code'] == 0) && !empty($response['data'])) {
                $response = $response['data'];
                Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "cedwish_warehouse` ");
                $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'cedwish_warehouse` (
                        `id_cedwish_warehouse`,
                        `id`,
                        `shipping_type`,
                        `name`,
                        `address`,
                        `destination_countries`,
                        `ship_to_name`,
                        `city`,
                        `state`,
                        `country_code`,
                        `zipcode`,
                        `street_address1`,
                        `street_address2`
                        ) VALUES ';
                foreach ($response as $warehouse) {
                    if (!isset($warehouse['destination_countries'])) {
                        $warehouse['destination_countries'] = array();
                    }
                    if (!isset($warehouse['shipping_type'])) {
                        $warehouse['shipping_type'] = '';
                    }

                    if (isset($warehouse['name'])) {
                        $warehouse['warehouse_type_name'] = $warehouse['name'];
                    }

                    if (!isset($warehouse['address'])) {
                        $warehouse['address'] = array(
                            'ship_to_name' => '',
                            'city' => '',
                            'state' => '',
                            'country_code' => '',
                            'zipcode' => '',
                            'street_address1' => '',
                            'street_address2' => '',
                        );
                    }

                    $sql .= "(
                            NULL,
                            '" . pSQL($warehouse['id']) . "',
                            '" . pSQL($warehouse['shipping_type']) . "',
                            '" . pSQL($warehouse['warehouse_type_name']) . "',
                            '" . pSQL(json_encode($warehouse['address'])) . "',
                            '" . pSQL(json_encode($warehouse['destination_countries'])) . "',
                            '" . pSQL($warehouse['address']['ship_to_name']) . "',
                            '" . pSQL($warehouse['address']['city']) . "',
                            '" . pSQL($warehouse['address']['state']) . "',
                            '" . pSQL($warehouse['address']['country_code']) . "',
                            '" . pSQL($warehouse['address']['zipcode']) . "',
                            '" . pSQL($warehouse['address']['street_address1']) . "',
                            '" . pSQL($warehouse['address']['street_address2']) . "'
                        ), ";
                    $warehouses_list[] = array(
                        'id' => $warehouse['id'],
                        'name' => $warehouse['warehouse_type_name'],
                    );
                }
                $sql = rtrim($sql, ", ");
                Db::getInstance()->execute($sql);
            }
        }

        if (!empty($warehouses_list)) {
            $this->context->smarty->assign(
                array(
                    'warehouses_list' => $warehouses_list,
                    'selected_warehouses' => json_decode(
                        Configuration::get('CED_WISH_SELECTED_WAREHOUSES'),
                        true
                    ),
                    'CEDWISH_TOKEN_EXPIRY_DATE' => Configuration::get('CEDWISH_TOKEN_EXPIRY_DATE'),
                )
            );
            $warehouse_selector = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/configuration/warehouse_selector.tpl'
            );
            $general_config_form['form']['input'][] = array(
                'col' => 4,
                'type' => 'html',
                'label' => $this->l('WAREHOUSES TO SYNC'),
                'name' => $warehouse_selector
            );
        } else {
            $this->context->smarty->assign(
                array(
                    'warehouses_list' => array(),
                    'selected_warehouses' => json_decode(
                        Configuration::get('CED_WISH_SELECTED_WAREHOUSES'),
                        true
                    ),
                    'CEDWISH_TOKEN_EXPIRY_DATE' => Configuration::get('CEDWISH_TOKEN_EXPIRY_DATE'),
                )
            );
            $warehouse_selector = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/configuration/warehouse_selector.tpl'
            );
            $general_config_form['form']['input'][] = array(
                'col' => 4,
                'type' => 'html',
                'label' => $this->l('WAREHOUSES TO SYNC'),
                'name' => $warehouse_selector
            );
        }
        if (Configuration::get($this->module_setting_key . 'EXPIRY_TIME')) {
            $general_config_form['form']['input'][] = array(
                'col' => 3,
                'type' => 'hidden',
                'desc' => $this->l(
                    'Do not change them, remove them when you want to reauthorize'
                ),
                'name' => $this->module_setting_key . 'EXPIRY_TIME',
                'label' => $this->l('EXPIRY_TIME'),
            );
        }

        $general_config_form['form']['input'][] = array(
            'type' => 'switch',
            'label' => $this->l('Debug'),
            'name' => $this->module_setting_key . 'DEBUG_MODE_ENABLE',
            'is_bool' => true,
            'desc' => $this->l('Log data while request send on wish.com.'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('No')
                )
            ),
        );

        return $general_config_form;
    }

    protected function getProductForm()
    {
        $currency_list = Currency::getCurrencies();
        $languages = Language::getLanguages(true);
        $customerGroups = Group::getGroups((int)Context::getContext()->language->id);
        try {
            $image_types = ImageType::getImagesTypes();
        } catch (PrestaShopDatabaseException $e) {
            $image_types = array();
        }
        if (function_exists('getFormattedName')) {
            $image_default_value = ImageType::getFormattedName('large');
        } else {
            $image_default_value = ImageType::getFormatedName('large');
        }
        $attributes = AttributeGroup::getAttributesGroups((int)Context::getContext()->language->id);

        $product_config_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Product Setting(s)'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Send Disabled Items too'),
                        'name' => $this->module_setting_key . 'SEND_DISABLE_ITEM',
                        'is_bool' => true,
                        'desc' => $this->l('Upload Item(s) that are disabled in Prestashop too.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Make Item Inactive When Stock 0'),
                        'name' => $this->module_setting_key . 'MAKE_INACTIVE_ITEM',
                        'is_bool' => true,
                        'desc' => $this->l('Make Item inactive when stock 0.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Send Stock 0 or make item Inactive when stock is less 
                        than set amount.'),
                        'name' => $this->module_setting_key . 'INVENTORY_THRESHOLD',
                        'label' => $this->l('Inventory Threshold'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('If selected items are larger than set amount then processed by cron.'),
                        'name' => $this->module_setting_key . 'CRON_CHUNK_SIZE',
                        'label' => $this->l('Cron Chunk Size'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'required' => true,
                        'desc' => $this->l('Default shipping should be send to all warehouses and > 0.'),
                        'name' => $this->module_setting_key . 'DEFAULT_SHIPPING_AMOUNT',
                        'label' => $this->l('Default Shipping Amount'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Amount of percent with that prices to be increase before sending.'),
                        'name' => $this->module_setting_key . 'PRICE_INCR_PER',
                        'label' => $this->l('Price Markup By Percent'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Amount with that prices to be increase before sending.'),
                        'name' => $this->module_setting_key . 'PRICE_INCR_FIX',
                        'label' => $this->l('Price Markup By Fix Amount'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Language'),
                        'desc' => $this->l('Language in which data need to send .'),
                        'name' => $this->module_setting_key . 'LANG_ID',
                        'required' => false,
                        'default_value' => '',
                        'options' => array(
                            'query' => $languages,
                            'id' => 'id_lang',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Image Type'),
                        'desc' => $this->l('Image type which need to send on marketplace .'),
                        'name' => $this->module_setting_key . 'IMAGE_TYPE',
                        'required' => false,
                        'default_value' => $image_default_value,
                        'options' => array(
                            'query' => $image_types,
                            'id' => 'name',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Customer Group ID'),
                        'desc' => $this->l('To be use for create customer and send prices.'),
                        'name' => $this->module_setting_key . 'CUSTOMER_GROUP_ID',
                        'required' => false,
                        'default_value' => '',
                        'options' => array(
                            'query' => $customerGroups,
                            'id' => 'id_group',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'readonly' => true,
                        'desc' => $this->l('Currency Active on wish.com.'),
                        'name' => $this->module_setting_key . 'WISH_CURRENCY',
                        'label' => $this->l('Currency On Wish'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Currency Of Price'),
                        'desc' => $this->l('Select same currency as wish. If that currency not available on 
                        store, then you can add that with conversion rate with 
                        "International > Localization > Currencies" '),
                        'name' => $this->module_setting_key . 'CURRENCY_ID',
                        'required' => false,
                        'default_value' => '',
                        'options' => array(
                            'query' => $currency_list,
                            'id' => 'id_currency',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Size Attributes'),
                        'desc' => $this->l('Select Attributes you want to send as size if in items.'),
                        'name' => $this->module_setting_key . 'SIZE_MAPPING[]',
                        'required' => false,
                        'multiple' => true,
                        'search' => true,
                        'class' => 'chosen',
                        'default_value' => '',
                        'options' => array(
                            'query' => $attributes,
                            'id' => 'id_attribute_group',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Color Attributes'),
                        'desc' => $this->l('Select Attributes you want to send as color if in items.'),
                        'name' => $this->module_setting_key . 'COLOR_MAPPING[]',
                        'required' => false,
                        'multiple' => true,
                        'class' => 'chosen',
                        'default_value' => '',
                        'options' => array(
                            'query' => $attributes,
                            'id' => 'id_attribute_group',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('SKU on wish'),
                        'desc' => $this->l('Map with field you want to send it as SKU on wish.com.'),
                        'name' => $this->module_setting_key . 'ITEM_SKU',
                        'required' => false,
                        'default_value' => '',
                        'options' => array(
                            'query' => array(
                                array(
                                    'field' => 'reference',
                                    'name' => 'Reference',
                                ),
                                array(
                                    'field' => 'id_product',
                                    'name' => 'Product ID',
                                ),
                                array(
                                    'field' => 'ean13',
                                    'name' => 'EAN',
                                ),
                                array(
                                    'field' => 'upc',
                                    'name' => 'UPC',
                                )
                            ),
                            'id' => 'field',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('GTIN on wish'),
                        'desc' => $this->l('Map with field you want to send it as GTIN on wish.com.'),
                        'name' => $this->module_setting_key . 'ITEM_GTIN',
                        'required' => false,
                        'default_value' => '',
                        'options' => array(
                            'query' => array(
                                array(
                                    'field' => 'ean13',
                                    'name' => 'EAN',
                                ),
                                array(
                                    'field' => 'upc',
                                    'name' => 'UPC',
                                )
                            ),
                            'id' => 'field',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('When using from localhost then you need to add url from any server 
                        because localhost urls are not accepted by wish.com.'),
                        'name' => $this->module_setting_key . 'DEMO_IMAGE_FOR_LOCALHOST',
                        'label' => $this->l('Image URL for localhost uploads'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        return $product_config_form;
    }

    protected function getOrderForm()
    {
        $reference_disable = true;
        if (isset(OrderPayment::$definition['fields']['order_reference']['size'])
            && (OrderPayment::$definition['fields']['order_reference']['size']>=24)
        ) {
            $reference_disable = false;
        }

        $order_statuses = OrderState::getOrderStates((int)Context::getContext()->language->id);
        $this->context->smarty->assign(
            array(
                'order_statuses' => $order_statuses,
                'wish_statuses' => CedWishHelper::getOrderStatuses(),
                'status_mappings' => json_decode(
                    Configuration::get($this->module_setting_key . 'STATUS_MAPPING'),
                    true
                )
            )
        );

        $status_html = $this->display(
            __FILE__,
            'views/templates/admin/configuration/status_mapping.tpl'
        );

        $order_carriers = Carrier::getCarriers(
            (int)Context::getContext()->language->id,
            true,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );

        $this->context->smarty->assign(
            array(
                'order_carriers' => $order_carriers,
                'marketplace_carriers' => CedWishHelper::getCarriers(),
                'countries' => CedWishHelper::getShippableCountries(),
                'carrier_mappings' => json_decode(
                    Configuration::get($this->module_setting_key . 'CARRIER_MAPPING'),
                    true
                )
            )
        );

        $carrier_html = $this->display(
            __FILE__,
            'views/templates/admin/configuration/carrier_mapping.tpl'
        );

        $order_payments = PaymentModule::getInstalledPaymentModules();
        array_unshift($order_payments, array('id_module' => 'cedwish', 'name' => 'cedwish'));

        $countries = Country::getCountries(CedWishHelper::getLanguageId());
        array_unshift(
            $countries,
            array(
                'iso_code' => '',
                'name' => 'Please Select Default Origin Country'
            )
        );
        $order_setting = array(
            array(
                'label' => $this->l('When Tracking Added'),
                'value' => 'TRACKING_ADDED'
            ),
            array(
                'label' => $this->l('When Order Status Is'),
                'value' => 'ORDER_STATUS'
            ),
            array(
                'label' => $this->l('Will Ship Manually'),
                'value' => 'SHIP_MANUALLY'
            )
        );

        $order_config_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Order Setting(s)'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Email to be use to create order when not email from marketplace .'),
                        'name' => $this->module_setting_key . 'ORDER_EMAIL',
                        'label' => $this->l('Email For Customer'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Create Order With Carrier'),
                        'name' => $this->module_setting_key . 'ORDER_CARRIER',
                        'required' => true,
                        'options' => array(
                            'query' => $order_carriers,
                            'id' => 'id_carrier',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Create Order With Payment'),
                        'name' => $this->module_setting_key . 'ORDER_PAYMENT',
                        'required' => true,
                        'options' => array(
                            'query' => $order_payments,
                            'id' => 'name',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Force Create Order'),
                        'name' => $this->module_setting_key . 'CREATE_ORDER',
                        'is_bool' => true,
                        'desc' => $this->l(
                            'Create order even stock not available or product not active on store.'
                        ),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Cancel Order When Cancel On Store'),
                        'name' => $this->module_setting_key . 'CANCEL_ORDER',
                        'is_bool' => true,
                        'desc' => $this->l(
                            'Cancel order on wish.com when order was cancelled on prestashop store by admin.'
                        ),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use Marketplace order id as Reference'),
                        'name' => $this->module_setting_key . 'ORDER_ID_ORDER_REFERENCE',
                        'is_bool' => true,
                        'disabled' => $reference_disable,
                        'desc' => $this->l(
                            'PLEASE MAKE SURE REFERENCE LENGTH IS INCREASE BEFORE ENABLING THIS SETTING TO 24
                            When enable it will add marketplace order id as reference for prestashop order.'
                        ),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'required' => true,
                        'label' => $this->l('Origin Country'),
                        'name' => $this->module_setting_key . 'ORIGIN_COUNTRY',
                        'options' => array(
                            'query' => $countries,
                            'id' => 'iso_code',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Status When Order Imported'),
                        'name' => $this->module_setting_key . 'ORDER_CREATE_STATUS',
                        'options' => array(
                            'query' => $order_statuses,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Create Shipment'),
                        'name' => $this->module_setting_key . 'SHIPMENT_CREATE',
                        'required' => true,
                        'options' => array(
                            'query' => $order_setting,
                            'id' => 'value',
                            'name' => 'label',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Status For Order Create Shipment'),
                        'name' => $this->module_setting_key . 'SHIPMENT_CREATE_STATUS',
                        'options' => array(
                            'query' => $order_statuses,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'col' => 8,
                        'type' => 'html',
                        'label' => $this->l('Carrier Mapping'),
                        'name' => $carrier_html,
                    ),
                    array(
                        'col' => 8,
                        'type' => 'html',
                        'label' => $this->l('Order Status Mapping'),
                        'name' => $status_html,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        return $order_config_form;
    }

    protected function getCronForm()
    {
        $order_cron = Context::getContext()->link->getModuleLink(
            'cedwish',
            'Order',
            array()
        );

        $query = parse_url($order_cron, PHP_URL_QUERY);

        if ($query) {
            $order_cron .= '&secure_key=' . Configuration::get('CED_WISH_CRON_SECURE_KEY');
        } else {
            $order_cron .= '?secure_key=' . Configuration::get('CED_WISH_CRON_SECURE_KEY');
        }

        $queue_cron = Context::getContext()->link->getModuleLink(
            'cedwish',
            'Queue',
            array()
        );

        $query = parse_url($queue_cron, PHP_URL_QUERY);

        if ($query) {
            $queue_cron .= '&secure_key=' . Configuration::get('CED_WISH_CRON_SECURE_KEY');
        } else {
            $queue_cron .= '?secure_key=' . Configuration::get('CED_WISH_CRON_SECURE_KEY');
        }

        $order_status_cron = Context::getContext()->link->getModuleLink(
            'cedwish',
            'OrderStatus',
            array()
        );

        $query = parse_url($order_status_cron, PHP_URL_QUERY);

        if ($query) {
            $order_status_cron .= '&secure_key=' . Configuration::get('CED_WISH_CRON_SECURE_KEY');
        } else {
            $order_status_cron .= '?secure_key=' . Configuration::get('CED_WISH_CRON_SECURE_KEY');
        }

        $stock_cron = Context::getContext()->link->getModuleLink(
            'cedwish',
            'Stock',
            array()
        );

        $query = parse_url($stock_cron, PHP_URL_QUERY);

        if ($query) {
            $stock_cron .= '&secure_key=' . Configuration::get('CED_WISH_CRON_SECURE_KEY');
        } else {
            $stock_cron .= '?secure_key=' . Configuration::get('CED_WISH_CRON_SECURE_KEY');
        }

        $this->context->smarty->assign(array(
            'order_cron_url' => $order_cron,
            'queue_cron_url' => $queue_cron,
            'stock_cron_url' => $stock_cron,
            'order_status_cron' => $order_status_cron,
            'order_cron_execution' => Configuration::get('CED_WISH_ORDER_CRON_LAST_EXECUTION'),
            'queue_cron_execution' => Configuration::get('CED_WISH_QUEUE_CRON_LAST_EXECUTION'),
            'stock_cron_execution' => Configuration::get('CED_WISH_STOCK_CRON_LAST_EXECUTION'),
            'order_status_cron_execution' => Configuration::get('CED_WISH_ORDER_STATUS_CRON_LAST_EXECUTION'),
            'cron_secure_key' => Configuration::get('CED_WISH_CRON_SECURE_KEY')
        ));

        $cron_html = $this->display(
            __FILE__,
            'views/templates/admin/configuration/cron_table.tpl'
        );

        $cron_config_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Cron(s) Setting'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 12,
                        'type' => 'html',
                        'label' => '',
                        'name' => $cron_html,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            )
        );
        return $cron_config_form;
    }

    public function hookActionAdminOrdersTrackingNumberUpdate($params)
    {
        if (Module::isEnabled($this->name)) {
            $result = array();
            if (Configuration::get('CED_WISH_SHIPMENT_CREATE') == 'TRACKING_ADDED') {
                $order = $params['order'];
                $carrier = $params['carrier'];
                try {
                    $id_order = (int)$order->id;
                    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "cedwish_order` WHERE 
                    `store_order_id` = '" . (int)$id_order . "'";
                    $db = Db::getInstance();
                    $wish_order = $db->getRow($sql);
                    if (!empty($wish_order)
                        && isset($wish_order['marketplace_order_id'])
                        && $wish_order['marketplace_order_id']
                    ) {
                        $marketplace_order_id = $wish_order['marketplace_order_id'];
                        $id_carrier = 0;
                        if (isset($carrier->id)) {
                            $id_carrier = $carrier->id;
                        } elseif ($carrier->id_carrier) {
                            $id_carrier = $carrier->id_carrier;
                        }

                        $trackingNumber = $order->shipping_number;
                        $id_order_carrier = $order->getIdOrderCarrier();
                        if (!$trackingNumber && !empty($id_order_carrier)) {
                            $trackingNumber = Db::getInstance()->getValue(
                                "SELECT `tracking_number` FROM `" . _DB_PREFIX_ . "order_carrier` 
                                                WHERE `id_order` = " . $id_order . " 
                                                AND `id_order_carrier` =" . $id_order_carrier
                            );
                        }
                        if (!$id_carrier) {
                            $id_carrier = Configuration::get('CED_WISH_ORDER_CARRIER');
                        }

                        try {
                            $carrier = CedWishHelper::getWishMappedCarrier($id_carrier);
                            if ($carrier
                                && in_array($carrier, array_keys(CedWishHelper::getCarriers()))
                            ) {
                                $data = [
                                    'origin_country' => Configuration::get('CED_WISH_ORIGIN_COUNTRY'),
                                    'shipping_provider' => $carrier,
                                    'tracking_number' => $trackingNumber,
                                ];

                                $api = new CedWishApi();
                                $response = $api->makeShipment($marketplace_order_id, $data);

                                if (isset($response['code']) && ($response['code'] == 0)) {
                                    Db::getInstance()->execute(
                                        "UPDATE `"._DB_PREFIX_."cedwish_order` SET order_error ='',
                                        state ='SHIPPED', wish_order = '".pSQL(json_encode($response['data']))."' 
                                        WHERE id_cedwish_order ='".(int)$wish_order['id_cedwish_order']."'"
                                    );
                                    $result['success'][] = $marketplace_order_id . ' Shipped Successfully';
                                } else {
                                    CedWishQueue::addQueue(
                                        'shipment',
                                        array(
                                            $id_order
                                        ),
                                        4
                                    );

                                    $message = isset($response['message'])
                                        ?'Error While Shipment: ' . $response['message'] : 'Some error while Shipment.';
                                    Db::getInstance()->execute(
                                        "UPDATE `"._DB_PREFIX_."cedwish_order` SET order_error ='".pSQL($message)."' 
                                        WHERE id_cedwish_order ='".(int)$wish_order['id_cedwish_order']."'"
                                    );
                                    $result['error'][] = $message;
                                }
                            } else {
                                $result['error'][] = array(
                                    'success' => false,
                                    'message' => 'Carrier ID ' . $id_carrier . ' is not mapped.'
                                );
                            }
                        } catch (Exception $e) {
                            $result['error'][] = array('success' => false, 'message' => $e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    $result['error'][] = array('success' => false, 'message' => $e->getMessage());
                }
            }
            CedWishHelper::addLog(json_encode($result));
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if (Module::isEnabled($this->name)) {
            if ((Configuration::get('CED_WISH_SHIPMENT_CREATE') == 'ORDER_STATUS')
                && Configuration::get('CED_WISH_SHIPMENT_CREATE_STATUS')
            ) {
                $result = array();
                try {
                    $id_order = (int)$params['id_order'];
                    $newOrderStatus = $params['newOrderStatus'];
                    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "cedwish_order` WHERE 
                    `store_order_id` = '" . (int)$id_order . "'";
                    $db = Db::getInstance();
                    $wish_order = $db->getRow($sql);

                    if (!empty($wish_order)
                        && isset($wish_order['marketplace_order_id'])
                        && $wish_order['marketplace_order_id']
                    ) {
                        $marketplace_order_id = $wish_order['marketplace_order_id'];
                        $order = new Order($id_order);
                        if ($order && $order->getCurrentState()) {
                            $current_order_status = (int)$order->getCurrentState();
                            $order_state_when_shipped = (int)Configuration::get('CED_WISH_SHIPMENT_CREATE_STATUS');
                            $new_order_status = 0;
                            if (isset($newOrderStatus->id)) {
                                $new_order_status = $newOrderStatus->id;
                            } elseif (isset($newOrderStatus->id_order_state)) {
                                $new_order_status = $newOrderStatus->id_order_state;
                            }

                            if (($new_order_status == $order_state_when_shipped)
                                || ($current_order_status == $order_state_when_shipped)
                            ) {
                                $id_carrier = $order->id_carrier;
                                if (!$id_carrier) {
                                    $id_carrier = Configuration::get('CED_WISH_ORDER_CARRIER');
                                }
                                if ($id_carrier) {
                                    $id_order_carrier = $order->getIdOrderCarrier();
                                    if (!empty($id_order_carrier)) {
                                        $trackingNumber = Db::getInstance()->getValue(
                                            "SELECT `tracking_number` FROM `" . _DB_PREFIX_ . "order_carrier` 
                                                WHERE `id_order` = " . $id_order . " 
                                                AND `id_order_carrier` =" . $id_order_carrier
                                        );
                                    }
                                    try {
                                        $carrier = CedWishHelper::getWishMappedCarrier($id_carrier);

                                        if ($carrier
                                            && in_array($carrier, array_keys(CedWishHelper::getCarriers()))
                                        ) {
                                            $data = [
                                                'origin_country' => Configuration::get('CED_WISH_ORIGIN_COUNTRY'),
                                                'shipping_provider' => $carrier,
                                                'tracking_number' => $trackingNumber,
                                            ];

                                            $api = new CedWishApi();
                                            $response = $api->makeShipment($marketplace_order_id, $data);

                                            if (isset($response['code']) && ($response['code']==0)) {
                                                Db::getInstance()->execute(
                                                    "UPDATE `"._DB_PREFIX_."cedwish_order` SET order_error = '',
                                                    state ='SHIPPED', 
                                                    wish_order = '".pSQL(json_encode($response['data']))."' 
                                                    WHERE id_cedwish_order ='".(int)$wish_order['id_cedwish_order']."'"
                                                );
                                                $result['success'][] = $marketplace_order_id . ' Shipped Successfully';
                                            } else {
                                                CedWishQueue::addQueue(
                                                    'shipment',
                                                    array(
                                                        $id_order
                                                    ),
                                                    4
                                                );
                                                $message = isset($response['message'])
                                                    ?'Error While Shipment: ' . $response['message']
                                                    : 'Some error while Shipment.';
                                                Db::getInstance()->execute(
                                                    "UPDATE `"._DB_PREFIX_."cedwish_order` 
                                                    SET order_error ='".pSQL($message)."' 
                                                    WHERE id_cedwish_order ='".(int)$wish_order['id_cedwish_order']."'"
                                                );
                                                $result['error'][] = $message;
                                            }
                                        } else {
                                            $result['error'][] = array(
                                                'success' => false,
                                                'message' => 'Carrier ID ' . $id_carrier . ' is not mapped.'
                                            );
                                        }
                                    } catch (Exception $e) {
                                        $result['error'][] = array('success' => false, 'message' => $e->getMessage());
                                    }
                                }
                            } elseif (Configuration::get('CED_WISH_CANCEL_ORDER')) {
                                $mapped_cancelled_status = CedWishHelper::getWishMappedStateIdByWishState(
                                    'CANCELLED'
                                );
                                if ((int)$new_order_status
                                    && ((int)$new_order_status == (int)$mapped_cancelled_status)
                                ) {
                                    if ($wish_order['id_cedwish_order']) {
                                        $order = new CedWishOrder();
                                        $order->cancelOrder(
                                            array(
                                                (int)$wish_order['id_cedwish_order']
                                            )
                                        );
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    $result['error'][] = array('success' => false, 'message' => $e->getMessage());
                }
                CedWishHelper::addLog(json_encode($result));
            } elseif (Configuration::get('CED_WISH_CANCEL_ORDER') && isset($params['newOrderStatus'])) {
                $newOrderStatus = $params['newOrderStatus'];
                if (isset($newOrderStatus->id)) {
                    $new_order_status = $newOrderStatus->id;
                } elseif (isset($newOrderStatus->id_order_state)) {
                    $new_order_status = $newOrderStatus->id_order_state;
                }
                $mapped_cancelled_status = CedWishHelper::getWishMappedStateIdByWishState('CANCELLED');
                if ((int)$new_order_status && ((int)$new_order_status == (int)$mapped_cancelled_status)) {
                    $id_order = (int)$params['id_order'];
                    $sql = "SELECT id_cedwish_order FROM `" . _DB_PREFIX_ . "cedwish_order` 
                    WHERE `store_order_id` = '" . (int)$id_order . "'";
                    $id_cedwish_order = Db::getInstance()->getValue($sql);
                    if ($id_cedwish_order) {
                        $order = new CedWishOrder();
                        $result = $order->cancelOrder(
                            array(
                                (int)$id_cedwish_order
                            )
                        );
                        CedWishHelper::addLog(json_encode($result));
                    }
                }
            }
        }
    }

    public function hookActionProductAdd($params)
    {
        if (Module::isEnabled($this->name)) {
            $id_product = $params['id_product'];
            $id_product_old = $params['id_product_old'];
            if (!$id_product_old && $id_product) {
                $product = new CedWishProduct();
                $status = $product->assignToProfile($id_product, $params['product']);
                if ($status) {
                    CedWishQueue::addQueue(
                        'product',
                        array(
                            $id_product
                        ),
                        3
                    );
                }
            }
        }
    }

    public function hookActionProductUpdate($params)
    {
        if (Module::isEnabled($this->name)) {
            $id_product = $params['id_product'];
            if ($id_product) {
                $product = new CedWishProduct();
                if (!isset($params['product'])) {
                    $params['product'] = new Product((int)$id_product);
                }
                $product->assignToProfile($id_product, $params['product']);
                $profile = CedWishProfile::getProfileByProductId($id_product);
                if (!empty($profile)) {
                    CedWishQueue::addQueue(
                        'product',
                        array(
                            $id_product
                        ),
                        3
                    );
                }
            }
        }
    }

    public function hookActionUpdateQuantity($params)
    {
        if (Module::isEnabled($this->name)) {
            $id_product = $params['id_product'];
            if ($id_product) {
                $profile = CedWishProfile::getProfileByProductId($id_product);
                if (!empty($profile)) {
                    CedWishQueue::addQueue(
                        'stock',
                        array(
                            $id_product
                        ),
                        1
                    );
                }
            }
        }
    }

    public function hookActionProductDelete($params)
    {
        if (Module::isEnabled($this->name)) {
            $id_product = $params['id_product'];
            if ($id_product) {
                $profile = CedWishProfile::getProfileByProductId($id_product);
                if (!empty($profile)) {
                    CedWishQueue::addQueue(
                        'delete',
                        array(
                            $id_product
                        ),
                        3
                    );
                }
            }
        }
    }

    public function hookUpdateCarrier($params)
    {
        if (Module::isEnabled($this->name)) {
            $this->actionCarrierHookProcess($params);
        }
    }

    public function hookActionCarrierUpdate($params)
    {
        if (Module::isEnabled($this->name)) {
            $this->actionCarrierHookProcess($params);
        }
    }

    protected function actionCarrierHookProcess($params)
    {
        $idCarrierOld = (int)$params['id_carrier'];
        $idCarrierNew = (int)$params['carrier']->id;
        $carrier_mappings = Configuration::get('CED_WISH_CARRIER_MAPPING');
        if ($carrier_mappings) {
            $carrier_mappings = @json_decode($carrier_mappings, true);
            if (is_array($carrier_mappings) && !empty($carrier_mappings)) {
                foreach ($carrier_mappings as &$carrier_mapping) {
                    if (isset($carrier_mapping['id_carrier'])
                        && ((int)$carrier_mapping['id_carrier'] == (int)$idCarrierOld)
                    ) {
                        $carrier_mapping['id_carrier'] = (int)$idCarrierNew;
                    }
                }
                Configuration::updateValue('CED_WISH_CARRIER_MAPPING', json_encode($carrier_mappings));
            }
        }
        $create_order_carrier = Configuration::get('CED_WISH_ORDER_CARRIER');
        if ($create_order_carrier && ($idCarrierOld == $create_order_carrier)) {
            Configuration::updateValue('CED_WISH_ORDER_CARRIER', (int)$idCarrierNew);
        }
    }
}
