<?php
/**
* Price increment/reduction by groups, categories and more
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*/

class GroupincConfiguration extends ObjectModel
{
    public $id_groupinc_configuration;
    public $name;
    public $type;
    public $mode;
    public $price_calculation;
    public $price_application;
    public $fix;
    public $percentage;
    public $min_result_price;
    public $max_result_price;
    public $threshold_min_price;
    public $threshold_max_price;
    public $threshold_price;
    public $skip_discounts = false;
    public $override_discounts = false;
    public $groups;
    public $customers;
    public $countries;
    public $zones;
    public $categories;
    public $products;
    public $manufacturers;
    public $suppliers;
    public $currencies;
    public $languages;
    public $active = true;
    public $show_as_discount = false;
    public $backoffice = false;
    public $priority;
    public $first_condition = false;
    public $id_shop;
    public $date_add;
    public $date_upd;
    public $date_from;
    public $date_to;
    public $features;
    public $attributes;
    public $product_qty;
    public $show_on_sale = false;
    public $show_prices_drop = false;
    public $show_decimals = false;
    public $filter_prices;
    public $filter_store;
    public $filter_stock;
    public $filter_weight;
    public $min_stock;
    public $max_stock;
    public $min_weight;
    public $max_weight;
    public $cart_amount;
    public $grouped_by;
    public $countdown;
    public $countdown_prodpage;
    public $cd_style;
    public $cd_style_prodpage;
    public $products_excluded;
    public $customers_excluded;
    public $show_text;
    public $addit_text;
    public $show_text_prodpage;
    public $addit_text_prodpage;
    public $higher_discount;
    public $sequential_discount;
    public $fp_product_price;
    public $fp_attribute_price;
    public $cat_default;
    public $group_default;
    public $priorize_existing;

    public $schedule;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'groupinc_configuration',
        'primary' => 'id_groupinc_configuration',
        'multilang' => true,
        'fields' => array(
            'name' =>                   array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100),
            'type' =>                   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'mode' =>                   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'price_calculation' =>      array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'price_application' =>      array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'fix' =>                    array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'percentage' =>             array('type' => self::TYPE_FLOAT),
            'min_result_price' =>       array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'max_result_price' =>       array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'threshold_min_price' =>    array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'threshold_max_price' =>    array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'threshold_price' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'product_qty' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'skip_discounts' =>         array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'override_discounts' =>     array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'groups' =>                 array('type' => self::TYPE_STRING),
            'countries' =>              array('type' => self::TYPE_STRING),
            'products' =>               array('type' => self::TYPE_STRING),
            'customers' =>              array('type' => self::TYPE_STRING),
            'zones' =>                  array('type' => self::TYPE_STRING),
            'categories' =>             array('type' => self::TYPE_STRING),
            'manufacturers' =>          array('type' => self::TYPE_STRING),
            'currencies' =>             array('type' => self::TYPE_STRING),
            'languages' =>              array('type' => self::TYPE_STRING),
            'suppliers' =>              array('type' => self::TYPE_STRING),
            'features' =>               array('type' => self::TYPE_STRING),
            'attributes' =>             array('type' => self::TYPE_STRING),
            'active' =>                 array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'show_as_discount' =>       array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'show_on_sale' =>           array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'show_prices_drop' =>       array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'show_decimals' =>          array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'backoffice' =>             array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'first_condition' =>        array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'priority' =>               array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'filter_prices' =>          array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'filter_store' =>           array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'filter_stock' =>           array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'min_stock' =>              array('type' => self::TYPE_INT, 'copy_post' => false),
            'max_stock' =>              array('type' => self::TYPE_INT, 'copy_post' => false),
            'id_shop' =>                array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'date_add' =>               array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' =>               array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_from' =>              array('type' => self::TYPE_DATE, 'copy_post' => false),
            'date_to' =>                array('type' => self::TYPE_DATE, 'copy_post' => false),
            'schedule' =>               array('type' => self::TYPE_STRING),
            'min_weight' =>             array('type' => self::TYPE_FLOAT, 'copy_post' => false),
            'max_weight' =>             array('type' => self::TYPE_FLOAT, 'copy_post' => false),
            'filter_weight' =>          array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'cart_amount' =>            array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'grouped_by' =>             array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'countdown' =>              array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'countdown_prodpage' =>     array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'products_excluded' =>      array('type' => self::TYPE_STRING),
            'customers_excluded' =>     array('type' => self::TYPE_STRING),
            'show_text' =>              array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'addit_text' =>             array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isAnything'),
            'show_text_prodpage' =>     array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'addit_text_prodpage' =>    array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isAnything'),
            'cd_style' =>               array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isAnything'),
            'cd_style_prodpage' =>      array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isAnything'),
            'higher_discount' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'sequential_discount' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'fp_product_price' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'fp_attribute_price' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'cat_default' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'group_default' =>      array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'priorize_existing' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public function add($autodate = true, $null_values = true)
    {
        $this->id_shop = ($this->id_shop) ? $this->id_shop : Context::getContext()->shop->id;
        $success = parent::add($autodate, $null_values);
        return $success;
    }

    public function toggleStatus()
    {
        parent::toggleStatus();
        return Db::getInstance()->execute('
        UPDATE `'._DB_PREFIX_.bqSQL($this->def['table']).'`
        SET `date_upd` = NOW()
        WHERE `'.bqSQL($this->def['primary']).'` = '.(int)$this->id);
    }

    public function delete()
    {
        if (parent::delete()) {
            return $this->deleteImage();
        }
    }

    public static function getConfig($id_config)
    {
        $query = '
                 SELECT gi.* FROM `'._DB_PREFIX_.'groupinc_configuration` gi WHERE gi.`id_groupinc_configuration` = '.(int)$id_config;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    public function getPricesDropConfigurations($id_shop = 0, $id_customer = 0, $id_country = 0, $id_state = 0, $id_currency = 0, $id_lang = 0, $id_product_attribute = 0)
    {
        $query = '';
        $today = date("Y-m-d H:i:s");
        $array_configurations_result = array();

        $query = '
                 SELECT gi.* FROM `'._DB_PREFIX_.'groupinc_configuration` gi
                 WHERE gi.`id_shop` = '.(int)$id_shop.'
                 AND gi.show_prices_drop = 1
                 AND gi.`active` = 1 ';

        $datefilters = ' AND (date_from <= "'.$today. '" OR date_from = "0000-00-00 00:00:00") AND (date_to >= "'.$today.'" OR date_to = "0000-00-00 00:00:00")';

        $query = $query.$datefilters;

        $orderby = ' ORDER BY gi.`priority`, gi.`id_groupinc_configuration` ASC';

        $query = $query.$orderby;

        $configs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($configs === false) {
            return false;
        }

        $customer = new Customer($id_customer);
        $customer_groups = $customer->getGroupsStatic($customer->id);
        $country = new Country($id_country);
        $zone = 0;

        if ($id_state > 0) {
            $zone = State::getIdZone($id_state);
        } else if ($id_country != null && $id_country > 0) {
            $country = new Country($id_country);
            $zone = $country->getIdZone($id_country);
        }

        foreach ($configs as $conf) {
            if (!GroupincConfiguration::checkExceptions($conf, $id_product, $id_customer)) {
                continue;
            }

            if (!GroupincConfiguration::isShowableBySchedule($conf)) {
                continue;
            }

            if (!GroupincConfiguration::checkStockPriceWeight($conf, $id_product, $id_product_attribute, $ptc)) {
                continue;
            }

            if (!GroupincConfiguration::checkCartAmount($conf, $id_product, $id_product_attribute)) {
                continue;
            }

            if ($conf['currencies'] == 'all') {
                $conf['currencies'] = '';
            }
            if ($conf['languages'] == 'all') {
                $conf['languages'] = '';
            }
            if ($conf['groups'] == 'all') {
                $conf['groups'] = '';
            }
            if ($conf['customers'] == 'all') {
                $conf['customers'] = '';
            }
            if ($conf['countries'] == 'all') {
                $conf['countries'] = '';
            }
            if ($conf['zones'] == 'all') {
                $conf['zones'] = '';
            }

            if ($conf['currencies'] == '' && $conf['languages'] == '' && $conf['groups'] == '' && $conf['customers'] == '' && $conf['countries'] == '' && $conf['zones'] == '' && $conf['manufacturers'] == '' && $conf['suppliers'] == '') {
                $array_configurations_result[] = $conf;
                continue;
            }

            $filter_currencies = true;
            if ($conf['currencies'] !== '') {
                $currencies_array = explode(';', $conf['currencies']);
                if (!in_array($id_currency, $currencies_array)) {
                    $filter_currencies = false;
                }
            }
            $filter_languages = true;
            if ($conf['languages'] !== '') {
                $languages_array = explode(';', $conf['languages']);
                if (!in_array($id_lang, $languages_array)) {
                    $filter_languages = false;
                }
            }

            $filter_groups = true;
            $filter_customers = true;
            if ($conf['groups'] !== '' && $conf['customers'] == '') {
                $groups_array = explode(';', $conf['groups']);
                foreach ($customer_groups as $group) {
                    if (!in_array($group, $groups_array)) {
                        $filter_groups = false;
                    } else {
                        $filter_groups = true;
                        break;
                    }
                }
                if (!$filter_groups) {
                    $filter_customers = false;
                }
            } else if ($conf['groups'] == '' && $conf['customers'] !== '') {
                $customers_array = explode(';', $conf['customers']);
                if (!in_array($id_customer, $customers_array)) {
                    $filter_customers = false;
                }
            } else if ($conf['groups'] !== '' && $conf['customers'] !== '') {
                $groups_array = explode(';', $conf['groups']);
                foreach ($customer_groups as $group) {
                    if (!in_array($group, $groups_array)) {
                        $filter_groups = false;
                    } else {
                        $filter_groups = true;
                    }
                }
                if (!$filter_groups) {
                    $customers_array = explode(';', $conf['customers']);
                    if (!in_array($id_customer, $customers_array)) {
                        $filter_customers = false;
                    } else {
                        $filter_customers = true;
                    }
                } else {
                    $customers_array = explode(';', $conf['customers']);
                    if (!in_array($id_customer, $customers_array)) {
                        $filter_customers = false;
                    }
                }
            }

            $filter_countries = true;
            if ($conf['countries'] !== '') {
                $countries_array = explode(';', $conf['countries']);

                if (!in_array($id_country, $countries_array)) {
                    $filter_countries = false;
                }
            }

            $filter_zones = true;
            if ($conf['zones'] !== '') {
                $zones_array = explode(';', $conf['zones']);
                if (!in_array($zone, $zones_array)) {
                    $filter_zones = false;
                }
            }

            if ($filter_currencies && $filter_languages && $filter_groups && $filter_customers && $filter_countries && $filter_zones) {
                $array_configurations_result[] = $conf;
                continue;
            }
        }

        if (count($array_configurations_result) > 0) {
            return $array_configurations_result;
        } else {
            return false;
        }
    }


    public static function botDetected()
    {
       // return (
         //   isset($_SERVER['HTTP_USER_AGENT'])
           //     && preg_match('/bot|crawl|slurp|googlebot|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])
       // );
			return false;
    }

    public static function getCountrybyURL($url = null)
    {
        if ($url) {
            $url_explode = explode('/', $url);
            if (!empty($url_explode)) {
                $isoLang = $url_explode[1];
                if ($isoLang == 'en') {
                    $isoLang = 'gb';
                }
                if (!empty($isoLang) && strlen($isoLang) == 2) {
                    return Country::getByIso($isoLang, false);
                }
            }
        }
        return Configuration::get('PS_COUNTRY_DEFAULT');
    }

    public static function getGIConfigurations($id_shop = 0, $id_product = 0, $id_customer = 0, $id_country = 0, $id_state = 0, $id_currency = 0, $id_lang = 0, $taxes = false, $discounts = false, $id_product_attribute = 0, $quantity = 0, $qd = false, $onsale = false, $id_group = 0)
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $url_name = $_SERVER['REQUEST_URI'];
        } else {
            $url_name = "";
        }

        if (empty($url_name) || strpos($url_name, 'fastbay') !== false || strpos($url_name, 'facetedsearch') !== false || strpos($url_name, 'daneaproduct') !== false) {
            return false;
        }

        $cache_key = 'Groupinc::getConfigurations_'.(int)$id_shop.'_'.(int)$id_product.'_'.(int)$id_customer.'_'.$id_country.'_'.$id_state.'_'.$id_currency.'_'.(int)$id_lang.'_'.(int)$taxes.'_'.(int)$discounts.'_'.$id_product_attribute.'_'.$quantity.'_'.(int)$qd.'_'.(int)$discounts;

        if (Cache::isStored($cache_key)) {
            return Cache::retrieve($cache_key);
        }

        $context = Context::getContext();

        if (empty($id_shop) || (int)$id_shop == 0) {
            $id_shop = $context->shop->id;
        }

        $query = '';
        $today = date("Y-m-d H:i:s");

        $query = '
                 SELECT gi.* FROM `'._DB_PREFIX_.'groupinc_configuration` gi ';

        $datefilters = ' WHERE (date_from <= "'.$today. '" OR date_from = "0000-00-00 00:00:00") AND (date_to >= "'.$today.'" OR date_to = "0000-00-00 00:00:00")';

        $query = $query.$datefilters;

        $query = $query.' AND gi.`id_shop` = '.(int)$id_shop.' AND gi.`active` = 1 ';

        if ($qd) {
            $query .= ' AND product_qty > 1 ';
        }

        if ($onsale) {
            $query .= ' AND show_on_sale = 1 ';
        }

        if ($id_customer) {
            $sql_customers = ' AND (gi.customers = "" OR FIND_IN_SET("'.(int)$id_customer.'", REPLACE(gi.customers, ";", ",")) > 0)';
            $query = $query.$sql_customers;
        }

        if (Customer::isBanned($id_customer)) {
            $id_customer = 0;
        }

        $customer_groups = Customer::getGroupsStatic($id_customer);
        if ($id_customer == 0) {
            $customer_groups = array();
            $customer_groups[] = 1;
        }

        if ($id_group) {
            $customer_groups[] = $id_group;
        } else {
            $customer_groups = Customer::getGroupsStatic($id_customer);
            if ($id_customer == 0) {
                $customer_groups = array();
                $customer_groups[] = 1;
            }
        }

        $sql_groups = ' AND (gi.groups = "" ';
        foreach ($customer_groups as $cgroup) {
            $sql_groups = $sql_groups. ' OR FIND_IN_SET('.$cgroup.', REPLACE(gi.groups, ";", ",")) > 0';
        }
        $sql_groups = $sql_groups.')';
        $query = $query.$sql_groups;

        $id_manufacturer = GroupincConfiguration::getManufacturerbyIdProduct($id_product);
        /*if ($id_manufacturer) {
            $sql_manufacturers = ' AND (gi.manufacturers = "" OR FIND_IN_SET('.$id_manufacturer.', REPLACE(gi.manufacturers, ";", ",")) > 0)';
            $query = $query.$sql_manufacturers;
        }*/

        $product_suppliers_array = ProductSupplier::getSupplierCollection($id_product);
        $sql_suppliers = ' AND (gi.suppliers = "" ';
        foreach ($product_suppliers_array as $supplier) {
            $sql_suppliers = $sql_suppliers. ' OR FIND_IN_SET('.(int)$supplier->id_supplier.', REPLACE(gi.suppliers, ";", ",")) > 0';
        }
        $sql_suppliers = $sql_suppliers.')';
        $query = $query.$sql_suppliers;

        $query = $query.' AND (gi.currencies = "" OR FIND_IN_SET('.(int)$id_currency.', REPLACE(gi.currencies, ";", ",")) > 0)';
        $query = $query.' AND (gi.languages = "" OR FIND_IN_SET('.(int)$id_lang.', REPLACE(gi.languages, ";", ",")) > 0)';

        //$query = $query.' AND (gi.zones = "" OR FIND_IN_SET('.$zone.', REPLACE(gi.zones, ";", ",")) > 0)';
        //$query = $query.' AND (gi.products = "" OR FIND_IN_SET('.$id_product.', REPLACE(gi.products, ";", ",")) > 0)';

        $configs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (empty($configs) || $configs === false) {
            return false;
        }

        $context = Context::getContext();
        if (empty($id_customer)) {
            if (isset($context->customer) && !empty($context->customer)) {
                $id_customer = $context->customer->id;
            }
        }

        $id_lang = $context->language->id;

        if (empty($id_currency)) {
            if (isset($context->currency) && !empty($context->currency)) {
                $id_currency = $context->currency->id;
            }
        }

        if (isset($context->cart) && isset($context->cart->id_address_delivery) && $context->cart->id_address_delivery != 0) {
            $id_address_delivery = $context->cart->id_address_delivery;
            $address = new Address($id_address_delivery);
            $id_country = $address->id_country;
            $id_state = $address->id_state;
        } else {
            $address = new Address();
            $address->id_country = $context->country->id;
        }

        $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context));
        $ptc = $tax_manager->getTaxCalculator();

        if ($id_country == 0) {
            $id_country = $context->country->id;
        }

        // google merchant backoffice feed
        if (Tools::getValue('sCountryIso')) {
            $id_country = Country::getByIso(Tools::getValue('sCountryIso'), true);
        }

        // bot crawler management to change the id_country
        if (GroupincConfiguration::botDetected()) {
            // parse URL
            $url_to_parse = $_SERVER['REQUEST_URI'];
            if ($url_to_parse) {
                $id_country = GroupincConfiguration::getCountrybyURL($url_to_parse);
            }
        }

        $categories = Product::getProductCategories($id_product);
        $product_features = Product::getFeaturesStatic((int)$id_product);

        if ((int)$id_product_attribute > 0) {
            $product = new Product($id_product);
            $product_attribute_combinations = $product->getAttributeCombinationsById((int)$id_product_attribute, $id_lang);
        } else {
            $product_attribute_combinations = array();
        }

        $zone = 0;
        if ($id_state > 0) {
            $zone = State::getIdZone($id_state);
        } else if ($id_country != null && $id_country > 0) {
            $zone = Country::getIdZone($id_country);
        }

        $configs_priority = array();
        foreach ($configs as $key => $row) {
            $configs_priority[$key] = $row['priority'];
        }
        array_multisort($configs_priority, SORT_ASC, $configs);

        $array_configurations_result = array();

        foreach ($configs as $conf) {
            if (!GroupincConfiguration::checkExceptions($conf, $id_product, $id_customer)) {
                continue;
            }

            if (!GroupincConfiguration::isShowableBySchedule($conf)) {
                continue;
            }

            if (!GroupincConfiguration::checkStockPriceWeight($conf, $id_product, $id_product_attribute, $ptc)) {
                continue;
            }

            if (!GroupincConfiguration::checkCartAmount($conf, $id_product, $id_product_attribute)) {
                continue;
            }

            if (!$qd) {
                if ($conf['product_qty'] > 1) {
                    if ($conf['grouped_by'] > 0) {
                        if (!GroupincConfiguration::isProductItemsCart($conf, $quantity, $id_product, $id_product_attribute)) {
                            continue;
                        }
                    } else {
                        $id_cart = $context->cart->id;
                        $result = GroupincConfiguration::getQuantityProductInCart($id_cart, $id_product, $id_product_attribute);

                        if ($result['quantity'] > 0 && $result['quantity'] < $conf['product_qty']) {
                            continue;
                        } else if (Tools::getValue('quantity_wanted') && Tools::getValue('quantity_wanted') > 1) {
                            if ($conf['product_qty'] > Tools::getValue('quantity_wanted')) {
                                continue;
                            }
                        } else if (empty($result)) {
                            continue;
                        }
                    }
                } else if ($conf['skip_discounts']) {
                    $products = GroupincConfiguration::getProductsFromIdCart(Context::getContext()->cart->id);
                    if (is_array($products)) {
                        foreach ($products as $p) {
                            if ($p['id_product'] == $id_product) {
                                $quantity = $p['quantity'];
                                break;
                            }
                        }

                        if ($quantity > 1) {
                            $specific_price = SpecificPrice::getSpecificPrice(
                                (int)$id_product,
                                $id_shop,
                                $id_currency,
                                $id_country,
                                1,
                                $quantity,
                                $id_product_attribute,
                                $id_customer,
                                Context::getContext()->cart->id,
                                1
                            );

                            if ($specific_price) {
                                continue;
                            }
                        }
                    }
                }
            }

            /* retrocompatibility with old rules which have the value 'all' in the database */
            if ($conf['currencies'] == 'all') {
                $conf['currencies'] = '';
            }
            if ($conf['languages'] == 'all') {
                $conf['languages'] = '';
            }
            if ($conf['groups'] == 'all') {
                $conf['groups'] = '';
            }
            if ($conf['products'] == 'all') {
                $conf['products'] = '';
            }
            if ($conf['customers'] == 'all') {
                $conf['customers'] = '';
            }
            if ($conf['countries'] == 'all') {
                $conf['countries'] = '';
            }
            if ($conf['zones'] == 'all') {
                $conf['zones'] = '';
            }
            if ($conf['categories'] == 'all') {
                $conf['categories'] = '';
            }
            if ($conf['manufacturers'] == 'all') {
                $conf['manufacturers'] = '';
            }
            if ($conf['suppliers'] == 'all') {
                $conf['suppliers'] = '';
            }
            if ($conf['features'] == 'all') {
                $conf['features'] = '';
            }
            if ($conf['attributes'] == 'all') {
                $conf['attributes'] = '';
            }

            if ($conf['attributes'] == '' && $conf['features'] == '' && $conf['currencies'] == '' && $conf['languages'] == '' && $conf['groups'] == '' && $conf['products'] == '' && $conf['customers'] == '' && $conf['countries'] == '' && $conf['zones'] == '' && $conf['categories'] == '' && $conf['manufacturers'] == '' && $conf['suppliers'] == '') {
                $array_configurations_result[] = $conf;
                if ($conf['first_condition'] && !$qd) {
                    break;
                } else {
                    continue;
                }
            }

            $filter_currencies = true;
            if ($conf['currencies'] !== '') {
                $currencies_array = explode(';', $conf['currencies']);
                if (!in_array($id_currency, $currencies_array)) {
                    $filter_currencies = false;
                    continue;
                }
            }
            $filter_languages = true;
            if ($conf['languages'] !== '') {
                $languages_array = explode(';', $conf['languages']);
                if (!in_array($id_lang, $languages_array)) {
                    $filter_languages = false;
                    continue;
                }
            }

            $filter_manufacturers = true;
            if ($conf['manufacturers'] !== '') {
                $manufacturers_array = explode(';', $conf['manufacturers']);
                if (!in_array($id_manufacturer, $manufacturers_array)) {
                    $filter_manufacturers = false;
                    continue;
                }
            }

            $filter_countries = true;
            if ($conf['countries'] !== '') {
                $countries_array = explode(';', $conf['countries']);
                if (!in_array($id_country, $countries_array)) {
                    $filter_countries = false;
                    continue;
                }
            }

            $filter_zones = true;
            if ($conf['zones'] !== '') {
                $zones_array = explode(';', $conf['zones']);
                if (!in_array($zone, $zones_array)) {
                    $filter_zones = false;
                    continue;
                }
            }

            $filter_groups = true;
            $filter_customers = true;
            if ($conf['group_default'] == 1) {
                $customer = new Customer($id_customer);
                $customer_groups = array($customer->getDefaultGroupId($id_customer));
            }

            if ($conf['groups'] !== '' && $conf['customers'] == '') {
                $groups_array = explode(';', $conf['groups']);
                foreach ($customer_groups as $group) {
                    if (!in_array($group, $groups_array)) {
                        $filter_groups = false;
                    } else {
                        $filter_groups = true;
                        break;
                    }
                }
                if (!$filter_groups) {
                    $filter_customers = false;
                }
            } else if ($conf['groups'] == '' && $conf['customers'] !== '') {
                $customers_array = explode(';', $conf['customers']);
                if (!in_array($id_customer, $customers_array)) {
                    $filter_customers = false;
                }
            } else if ($conf['groups'] !== '' && $conf['customers'] !== '') {
                $groups_array = explode(';', $conf['groups']);
                foreach ($customer_groups as $group) {
                    if (!in_array($group, $groups_array)) {
                        $filter_groups = false;
                    } else {
                        $filter_groups = true;
                    }
                }
                if (!$filter_groups) {
                    $customers_array = explode(';', $conf['customers']);
                    if (!in_array($id_customer, $customers_array)) {
                        $filter_customers = false;
                    } else {
                        $filter_customers = true;
                    }
                } else {
                    $customers_array = explode(';', $conf['customers']);
                    if (!in_array($id_customer, $customers_array)) {
                        $filter_customers = false;
                    }
                }
            }

            $filter_categories = true;
            $filter_products = true;

            if (@unserialize($conf['categories']) !== false) {
                $categories_array = unserialize($conf['categories']);
            } else {
                $categories_array = explode(';', $conf['categories']);
            }

            if ($conf['cat_default'] == 1) {
                $product = new Product($id_product);
                $categories = array($product->getDefaultCategory());
            }

            if ($conf['categories'] !== '' && $conf['products'] == '') {
                foreach ($categories as $category) {
                    if (in_array($category, $categories_array)) {
                        $filter_categories = true;
                        $filter_products = true;
                        break;
                    } else {
                        $filter_categories = false;
                    }
                }
                if (!$filter_categories) {
                    $filter_products = false;
                }
            } else if ($conf['categories'] == '' && $conf['products'] !== '') {
                $products_array = explode(';', $conf['products']);
                if (!in_array($id_product, $products_array)) {
                    $filter_products = false;
                    $filter_categories = true;
                }
            } else if ($conf['categories'] !== '' && $conf['products'] !== '') {
                foreach ($categories as $category) {
                    if (!in_array($category, $categories_array)) {
                        $filter_categories = false;
                    } else {
                        $filter_categories = true;
                        break;
                    }
                }
                if (!$filter_categories) {
                    $products_array = explode(';', $conf['products']);
                    if (!in_array($id_product, $products_array)) {
                        $filter_products = false;
                    } else {
                        $filter_products = true;
                    }
                } else {
                    $products_array = explode(';', $conf['products']);
                    if (!in_array($id_product, $products_array)) {
                        $filter_products = false;
                    }
                }
            }

            $filter_features = false;
            $array_features_selected = Tools::jsonDecode($conf['features'], true);

            $flag_features = 0;
            if (!empty($array_features_selected) && count($array_features_selected) > 0) {
                foreach ($product_features as $pf) {
                    if (isset($array_features_selected[$pf['id_feature']])) {
                        $array_f = explode(";", $array_features_selected[$pf['id_feature']]);
                        if (in_array($pf['id_feature_value'], $array_f)) {
                            $flag_features++;
                        }
                    }
                }
            } else {
                $filter_features = true;
            }

            if ($flag_features > 0 && $flag_features == count($array_features_selected)) {
                $filter_features = true;
            }

            $filter_attributes = false;
            if (Module::isEnabled('attributewizardpro')) {
                $array_attributes_selected = json_decode($conf['attributes'], true);
                $counter = 0;
                foreach ($products as $key => $prod) {
                    if (!empty($prod['instructions_id']) && $prod['id_product_attribute'] == $id_product_attribute) {
                        $array_instructions = explode(',', $prod['instructions_id']);
                        foreach ($array_attributes_selected as $a) {
                            $a_sel = explode(";", $a);
                            $result = array_intersect($array_instructions, $a_sel);
                            if (count($result) > 0) {
                                $counter++;
                                continue;
                            }
                        }

                        if ($counter == count($array_attributes_selected)) {
                            $filter_attributes = true;
                        }
                    }
                }
            } else {
                $flag_attributes = 0;
                if ($id_product_attribute == 0 && empty($conf['attributes'])) {
                    $filter_attributes = true;
                } else {
                    $array_attributes_selected = json_decode($conf['attributes'], true);
                    if (!empty($array_attributes_selected)) {
                        foreach ($product_attribute_combinations as $key => $prod_attr_comb) {
                            //if (isset($array_attributes_selected[(int)$prod_attr_comb['id_attribute_group']])) {
                            if (isset($array_attributes_selected[(int)$prod_attr_comb['id_attribute_group']])) {
                                $array_a = explode(";", $array_attributes_selected[(int)$prod_attr_comb['id_attribute_group']]);
                                if (in_array((int)$prod_attr_comb['id_attribute'], $array_a)) {
                                    $filter_attributes = true;
                                    break;
                                    //$flag_attributes++;
                                }
                            }
                        }
                    } else {
                        $filter_attributes = true;
                    }
                }
            }

            if ($id_product == -1 && $discounts) {
                $filter_products = true;
                $filter_categories = true;
            }

            $filter_suppliers = true;
            if ($conf['suppliers'] !== '') {
                $filter_suppliers = false;
                $suppliers_array = explode(';', $conf['suppliers']);
                if (!empty($product_suppliers_array)) {
                    foreach ($product_suppliers_array as $ps) {
                        if (in_array($ps->id_supplier, $suppliers_array)) {
                            $filter_suppliers = true;
                            break;
                        }
                    }
                }
            }

            if ($id_product == -1 && $discounts) {
                $filter_products = true;
                $filter_categories = true;
            }

/*$logger = new FileLogger(0);
$logger->setFilename(_PS_ROOT_DIR_.'/log/debug.log');
$logger->logDebug("idproduct: ".print_r($id_product, true));
$logger->logDebug("conf name: ".print_r($conf['name'], true));
$logger->logDebug("filter_groups: ".print_r($filter_groups, true));
$logger->logDebug("filter_customers: ".print_r($filter_customers, true));
$logger->logDebug("filter_countries: ".print_r($filter_countries, true));
$logger->logDebug("filter_zones: ".print_r($filter_zones, true));
$logger->logDebug("filter_categories: ".print_r($filter_categories, true));
$logger->logDebug("filter_products: ".print_r($filter_products, true));
$logger->logDebug("filter_manufacturers: ".print_r($filter_manufacturers, true));
$logger->logDebug("filter_suppliers: ".print_r($filter_suppliers, true));
$logger->logDebug("filter_attributes: ".print_r($filter_attributes, true));
$logger->logDebug("filter_features: ".print_r($filter_features, true));
$logger->logDebug("filter_currencies: ".print_r($filter_currencies, true));
$logger->logDebug("filter_languages: ".print_r($filter_languages, true));*/

            if ($filter_currencies && $filter_languages && $filter_attributes && $filter_features && $filter_groups && $filter_customers && $filter_countries && $filter_zones && $filter_categories && $filter_products && $filter_manufacturers && $filter_suppliers) {
                $array_configurations_result[] = $conf;
                if ($conf['first_condition'] && !$qd) {
                    break;
                } else {
                    continue;
                }
            }
        }

        Cache::store($cache_key, $array_configurations_result);

        if (count($array_configurations_result) > 0) {
            return $array_configurations_result;
        } else {
            return false;
        }
    }

    public function getQuantityDiscounts($configs, $id_product, $quantity_discounts, $retailWithTaxes, $retailWithoutTaxes, $ptc, $id_product_attribute = 0)
    {
        $groupinc_specific_price = array();
        $groupinc_quantity_percent = 0;
        $price_to_compare = 0;
        $price_modified_return = 0;

        if (empty($configs)) {
            return array();
        }

        $product = new Product($id_product);
        $wholeWithoutTaxes = $product->wholesale_price;
        $context = Context::getContext();
        $priceDisplay = Product::getTaxCalculationMethod((int)$context->customer->id);

        $supplierWithoutTaxes = ProductSupplier::getProductSupplierPrice($id_product, $id_product_attribute, $product->id_supplier);

        $wholeWithTaxes = $ptc->addTaxes($wholeWithoutTaxes);
        $supplierWithTaxes = $ptc->addTaxes($supplierWithoutTaxes);
        $retailWithTaxes = $ptc->addTaxes($retailWithoutTaxes);

        if (isset($context->customer) && !empty($context->customer)) {
            $id_customer = $context->customer->id;
        } else {
            $id_customer = 0;
        }
        if (isset($context->language) && !empty($context->language)) {
            $id_lang = $context->language->id;
        } else {
            $id_lang = 1;
        }

        if (isset($context->currency) && !empty($context->currency)) {
            $id_currency = $context->currency->id;
        } else {
            $id_currency = 1;
        }

        if (isset($context->cart) && !empty($context->cart)) {
            $id_cart = $context->cart->id;
        } else {
            $id_cart = 0;
        }

        if (isset($context->shop) && !empty($context->shop)) {
            $id_shop = $context->shop->id;
        } else {
            $id_shop = 1;
        }

        $id_country = 0;
        if ($id_country == 0 && isset($context->country)) {
            $id_country = $context->country->id;
        }

        $specific_price = SpecificPrice::getSpecificPrice(
            (int)$id_product,
            $id_shop,
            $id_currency,
            $id_country,
            1,
            1,
            $id_product_attribute,
            $id_customer,
            $id_cart,
            1
        );

        foreach ($configs as $gi) {
            $giconfig = new GroupincConfiguration($gi['id_groupinc_configuration']);

            if ($giconfig->skip_discounts && !empty($quantity_discounts)) {
                continue;
            }

            if ($giconfig->skip_discounts) {
                if (!empty($specific_price)) {
                    continue;
                }
            }

            $tax = 1;
            if ($giconfig->price_application == 0) {
                $price_modified_return = $wholeWithoutTaxes;
                $tax = 0;
            } else if ($giconfig->price_application == 1) {
                $price_modified_return = $retailWithoutTaxes;
                $tax = 0;
            } else if ($giconfig->price_application == 2) {
                $price_modified_return = $wholeWithTaxes;
            } else if ($giconfig->price_application == 3) {
                $price_modified_return = $retailWithTaxes;
            }

            if (!empty($specific_price)) {
                $price_modified_return = $price_modified_return * (1 - $specific_price['reduction']);
            }

            $groupinc_specific_price['id_product'] = $id_product;
            $groupinc_specific_price['id_shop'] = Context::getContext()->shop->id;
            $groupinc_specific_price['reduction_tax'] = $tax;
            $groupinc_specific_price['id_currency'] = Context::getContext()->cart->id_currency;
            $groupinc_specific_price['id_product_attribute'] = (int)$id_product_attribute;
            $groupinc_specific_price['price'] = "-1.000000";
            $groupinc_specific_price['price_groupinc'] = $retailWithTaxes;
            $groupinc_specific_price['from_quantity'] = $giconfig->product_qty;
            $groupinc_specific_price['from'] = $giconfig->date_from;
            $groupinc_specific_price['to'] = $giconfig->date_to;
            $groupinc_specific_price['id_specific_price_rule'] = "0";
            $groupinc_specific_price['id_shop_group'] = "0";
            $groupinc_specific_price['label'] = $giconfig->name;

            if ($giconfig->show_decimals) {
                $dec = 4;
            } else {
                $dec = 3;
            }

            if ($giconfig->mode == 2) {
                $groupinc_specific_price['reduction_type'] = 'percentage';
                if ($price_modified_return > $giconfig->fix) {
                    $groupinc_specific_price['reduction'] = Tools::ps_round(1 - ($giconfig->fix / $price_modified_return), $dec);
                } else {
                    if ($price_modified_return > 0) {
                        $groupinc_specific_price['reduction'] = Tools::ps_round((($giconfig->fix - $price_modified_return) / $price_modified_return) * -1, $dec);
                    }
                }
            } else if ($giconfig->mode == 0) {
/*                if ($giconfig->price_application == 0 || $giconfig->price_application == 1) {
                    $price_modified_return = $retailWithoutTaxes;
                } else {
                    $price_modified_return = $retailWithTaxes;
                }*/

                $priceIncremented = $this->getPriceIncremented($giconfig, $price_modified_return, $retailWithTaxes, $retailWithoutTaxes, $wholeWithTaxes, $wholeWithoutTaxes, $supplierWithoutTaxes, $supplierWithTaxes);

                $groupinc_specific_price['reduction_type'] = 'percentage';

                if ($price_modified_return > $priceIncremented) {
                    $groupinc_specific_price['reduction'] = Tools::ps_round(1 - ($priceIncremented / $price_modified_return), $dec);
                } else {
                    if ($price_modified_return > 0) {
                        $groupinc_specific_price['reduction'] = 0;
                    }
                }
            } else {
                if ($giconfig->type == 0) {
                    $groupinc_specific_price['reduction_type'] = 'amount';
                    $groupinc_specific_price['reduction'] = $giconfig->fix;
                } else if ($giconfig->type == 1) {
                    $groupinc_specific_price['reduction_type'] = 'percentage';
                    $groupinc_specific_price['reduction'] = $giconfig->percentage / 100;
                    $price_modif = $price_modified_return * $groupinc_specific_price['reduction'];
                } else if ($giconfig->type == 2) {
                    $groupinc_specific_price['reduction_type'] = 'percentage';
                    $priceTemp = $price_modified_return * ($giconfig->percentage / 100);
                    $priceTemp = $priceTemp - $giconfig->fix;
                    $percTemp = Tools::ps_round(($priceTemp / $price_modified_return) * 100, $dec);
                    $groupinc_specific_price['reduction'] = (string)$percTemp / 100;

                    $price_modif = $price_modified_return * $groupinc_specific_price['reduction'];
                }

                if ($giconfig->type == 0) {
                    $price_modif = $price_modified_return - $groupinc_specific_price['reduction'];
                } else {
                    $price_modif = $price_modified_return * (1 - $groupinc_specific_price['reduction']);
                }
            }

            if ($giconfig->min_result_price > 0) {
                if ($price_modif < $giconfig->min_result_price) {
                    $groupinc_specific_price['reduction'] = Tools::ps_round(1 - ($giconfig->min_result_price / $price_modified_return), $dec);
                }
            }

            if ($giconfig->max_result_price > 0) {
                if ($price_modif > $giconfig->max_result_price) {
                    $groupinc_specific_price['reduction'] = Tools::ps_round(1 - ($giconfig->max_result_price / $price_modified_return), $dec);
                }
            }

            if (!empty($giconfig->attributes)) {
                $array_attributes_selected = json_decode($giconfig->attributes, true);
                if (count($array_attributes_selected) > 0) {
                    foreach ($array_attributes_selected as $attr) {
                        $idProductAttribute = $this->getIdProductAttribute($id_product, $attr);
                        if (isset($idProductAttribute['id_product_attribute'])) {
                            $id_pa = $idProductAttribute['id_product_attribute'];
                        } else {
                            $id_pa = 0;
                        }
                        if ($giconfig->price_application == 0) {
                            //$groupinc_specific_price['price'] = $this->getWholesalePrice($id_product, $idProductAttribute);
                            $price_modified_return = $this->getWholesalePrice($id_product, $id_pa);
                        } else if ($giconfig->price_application == 2) {
                            //$groupinc_specific_price['price'] = $ptc->addTaxes($this->getWholesalePrice($id_product, $idProductAttribute));
                            $price_modified_return = $ptc->addTaxes($this->getWholesalePrice($id_product, $id_pa));
                        }

                        if ($giconfig->price_application == 1) {
                            //$groupinc_specific_price['price'] = $retailWithoutTaxes + $this->getImpact($id_product, $idProductAttribute);
                            $price_modified_return = $retailWithoutTaxes + $this->getImpact($id_product, $id_pa);
                        } else if ($giconfig->price_application == 3) {
                            //$groupinc_specific_price['price'] = $retailWithTaxes + $this->getImpact($id_product, $idProductAttribute);
                            $price_modified_return = $retailWithTaxes + $this->getImpact($id_product, $id_pa);
                        }
                        foreach ($idProductAttribute as $a) {
                            $groupinc_specific_price['id_product_attribute'] = $a['id_product_attribute'];
                            $quantity_discounts[] = $groupinc_specific_price;
                        }
                    }
                } else {
                    $quantity_discounts[] = $groupinc_specific_price;
                }
                //$price_modified_return = $groupinc_specific_price['price'];
            } else {
                /*if ($giconfig->override_discounts) {
                    $quantity_discounts = array();
                    $quantity_discounts[] = $groupinc_specific_price;
                } else {*/
                    /*if ($giconfig->skip_discounts) {
                        $quantity_discounts = array();
                    } else {*/
                        $quantity_discounts[] = $groupinc_specific_price;
                   // }
                //}
            }

            /*if ($giconfig->first_condition) {
                break;
            }*/
        }

        foreach ($quantity_discounts as $key => $row)
        {
            $qd_sort[$key] = $row['from_quantity'];
        }
        array_multisort($quantity_discounts, SORT_ASC, $qd_sort);

        if (!$quantity_discounts) {
            $quantity_discounts = array();
        }

        return $quantity_discounts;
    }
    public function getPriceModified($configurations, $id_product, $retailWithoutTaxes, $wholeWithoutTaxes, $specific_price, $product_tax_calculator, $priceDisplay, $use_tax, $analyze, $id_group, $use_group_reduction, $supplierWithoutTaxes)
    {
        $price_modified_return = 0;
        $groupinc_specific_price = array();
        $pricesReturnToCompare = array();

        if (is_string(Context::getContext()->currency)) {
            $currencyConvert = new Currency(Context::getContext()->currency);
        } else {
            $currencyConvert = Context::getContext()->currency;
        }

        $wholeWithoutTaxes = Tools::convertPrice((float)$wholeWithoutTaxes, $currencyConvert);
        $supplierWithoutTaxes = Tools::convertPrice((float)$supplierWithoutTaxes, $currencyConvert);

        $wholeWithTaxes = $product_tax_calculator->addTaxes($wholeWithoutTaxes);
        $retailWithTaxes = $product_tax_calculator->addTaxes($retailWithoutTaxes);
        $supplierWithTaxes = $product_tax_calculator->addTaxes($supplierWithoutTaxes);

        foreach ($configurations as $conf) {
            $giconfig = new GroupincConfiguration($conf['id_groupinc_configuration']);

            if ($giconfig->skip_discounts && $specific_price) {
                if ($specific_price['price'] < 0) {
                    if ($specific_price['reduction_tax'] == 1) {
                        $specific_price['price'] = $retailWithoutTaxes;
                    } else {
                        $specific_price['price'] = $retailWithTaxes;
                    }
                }
                $groupinc_specific_price = $specific_price;
                continue;
            }
            if ($giconfig->mode == 3) {
                $giconfig->price_calculation = 3;
            }
            if ($giconfig->price_application == 0) {
                $price_modified_return = $wholeWithoutTaxes;
            } else if ($giconfig->price_application == 1) {
                $price_modified_return = $retailWithoutTaxes;
            } else if ($giconfig->price_application == 2) {
                $price_modified_return = $wholeWithTaxes;
            } else if ($giconfig->price_application == 3) {
                $price_modified_return = $retailWithTaxes;
            } else if ($giconfig->price_application == 4) {
                $price_modified_return = $supplierWithoutTaxes;
            } else if ($giconfig->price_application == 5) {
                $price_modified_return = $supplierWithTaxes;
            }

            if ($giconfig->show_decimals) {
                $dec = 4;
            } else {
                $dec = 2;
            }

            if ($use_group_reduction) {
                $group_reduction = $this->getGroupReduction($price_modified_return, $id_product, $id_group);

                if (!$giconfig->override_discounts) {
                    if ($group_reduction > 0) {
                        $price_modified_return -= $group_reduction;
                    }
                }
            }

            $higher = false;
            if ($giconfig->higher_discount) {
                if ($specific_price && $specific_price['reduction'] > 0) {
                    $red = 0;
                    if ($specific_price['reduction_type'] == 'percentage' && $giconfig->type == 1) {
                        if ((float)$giconfig->percentage / 100 <= (float)$specific_price['reduction']) {
                            continue;
                        }
                    } else if ($specific_price['reduction_type'] == 'amount' && $giconfig->type == 0) {
                        if ((float)$giconfig->fix <= (float)$specific_price['reduction']) {
                            continue;
                        }
                    } else if ($specific_price['reduction_type'] == 'percentage' && $giconfig->type == 0) {
                        $prResult = $price_modified_return * (1 - (float)$specific_price['reduction']);
                        $giResult = $price_modified_return - (float)$giconfig->fix;
                        if ($giResult >= $prResult) {
                            continue;
                        }
                    } else if ($specific_price['reduction_type'] == 'amount' && $giconfig->type == 1) {
                        $prResult = $price_modified_return - (float)$specific_price['reduction'];
                        $giResult = $price_modified_return * (1 - (float)$giconfig->percentage / 100);
                        if ($giResult >= $prResult) {
                            continue;
                        }
                    }
                    $higher = true;
                }
            }

            if ($higher) {
                $giconfig->override_discounts = 1;
            }

            $groupinc_specific_price['id_product'] = $id_product;
            $groupinc_specific_price['reduction'] = 0;
            $groupinc_specific_price['reduction_type'] = 'percentage';
            $groupinc_specific_price['from_quantity'] = 1;

            if ($giconfig->show_as_discount) { /* show as discount rule */
                if ($giconfig->mode == 1 || $giconfig->mode == 3) {
                    $groupinc_quantity_reduction = 0;
                    if ($giconfig->mode == 3) {
                        $giconfig->percentage = Tools::ps_round((1 - ($retailWithoutTaxes / $retailWithTaxes)) * 100, 2);
                        //$giconfig->percentage = (float)$product_tax_calculator->getTotalRate();
                        $giconfig->type = 1;
                    }

                    $groupinc_quantity_reduction = $price_modified_return * $giconfig->percentage / 100;

                    /*if ($giconfig->price_calculation == 0) {
                        $groupinc_quantity_reduction = $wholeWithoutTaxes * $giconfig->percentage / 100;
                    } else if ($giconfig->price_calculation == 1) {
                        $groupinc_quantity_reduction = $retailWithoutTaxes * $giconfig->percentage / 100;
                    } else if ($giconfig->price_calculation == 2) {
                        $groupinc_quantity_reduction = $wholeWithTaxes * $giconfig->percentage / 100;
                    } else if ($giconfig->price_calculation == 3) {
                        $groupinc_quantity_reduction = $retailWithTaxes * $giconfig->percentage / 100;
                    } else if ($giconfig->price_calculation == 4) {
                        $groupinc_quantity_reduction = $supplierWithoutTaxes * $giconfig->percentage / 100;
                    } else if ($giconfig->price_calculation == 5) {
                        $groupinc_quantity_reduction = $supplierWithTaxes * $giconfig->percentage / 100;
                    }*/

                    if ($giconfig->price_calculation == 6) {
                        if ($retailWithoutTaxes > $wholeWithoutTaxes) {
                            $groupinc_quantity_reduction = ($retailWithoutTaxes - $wholeWithoutTaxes) * $giconfig->percentage / 100;
                        }
                    }
                    else if ($giconfig->price_calculation == 7) {
                        if ($retailWithTaxes > $wholeWithTaxes) {
                            $groupinc_quantity_reduction = ($retailWithTaxes - $wholeWithTaxes) * $giconfig->percentage / 100;
                        }
                    }
                    if ($groupinc_quantity_reduction > 0 && $price_modified_return > 0) {
                        $price_modified_return_reduced = $price_modified_return - $groupinc_quantity_reduction;
                        $giconfig->percentage = Tools::ps_round((1 - ($price_modified_return_reduced / $price_modified_return)) * 100, $dec);
                    }
                }

                $groupinc_specific_price['id_specific_price_rule'] = "0";
                $groupinc_specific_price['id_shop'] = "0";
                $groupinc_specific_price['id_shop_group'] = "0";

                $groupinc_specific_price['from_quantity'] = 1;
                if ($giconfig->product_qty > 1) {
                    $groupinc_specific_price['from_quantity'] = $giconfig->product_qty;
                }

                if ($giconfig->price_application == 2 || $giconfig->price_application == 3 || $giconfig->price_application == 5) {
                    $groupinc_specific_price['reduction_tax'] = 1;
                } else {
                    $groupinc_specific_price['reduction_tax'] = 0;
                }

                $groupinc_specific_price['id_currency'] = 0;
                $groupinc_specific_price['id_product_attribute'] = 0;
                $groupinc_specific_price['label'] = $giconfig->name;
                $groupinc_specific_price['from'] = $giconfig->date_from;
                $groupinc_specific_price['to'] = $giconfig->date_to;

                //if (!isset($groupinc_specific_price['price'])) {
                    $groupinc_specific_price['price'] = $price_modified_return;
                //}

                if ($giconfig->mode == 1 || $giconfig->mode == 3) {
                    if ($giconfig->override_discounts && !$giconfig->skip_discounts) {
                        if ($giconfig->type == 0) {
                            $groupinc_specific_price['reduction_type'] = 'amount';
                            $groupinc_specific_price['reduction'] = $giconfig->fix;
                        } else {
                            $groupinc_specific_price['reduction_type'] = 'percentage';
                            $groupinc_specific_price['reduction'] = $giconfig->percentage / 100;
                        }
                    } else if (!$giconfig->skip_discounts && $specific_price) {
                        if (isset($specific_price['price']) && $specific_price['price'] > 0) {
                            $groupinc_specific_price['price'] = $specific_price['price'];
                        }
                        if ($giconfig->type == 0) {
                            if ($specific_price['reduction_type'] == 'amount') {
                                $groupinc_specific_price['reduction_type'] = 'amount';
                                $groupinc_specific_price['reduction'] = $specific_price['reduction'] + $giconfig->fix;
                            } else {
                                $groupinc_specific_price['reduction_type'] = 'amount';
                                $new_red = Tools::ps_round($specific_price['price'] * $specific_price['reduction'], 6);
                                $groupinc_specific_price['reduction'] = $giconfig->fix + $new_red;
                                /*$groupinc_specific_price['reduction_type'] = 'percentage';
                                $new_red = Tools::ps_round(1 - (($specific_price['price'] - $giconfig->fix) / $specific_price['price']), 6);
                                $groupinc_specific_price['reduction'] = $specific_price['reduction'] + $new_red;*/
                            }
                        }

                        if ($giconfig->type == 1) {
                            if (isset($specific_price['reduction']) && $specific_price['reduction'] > 0) {
                                if ($specific_price['reduction_type'] == 'amount') {
                                    if ($giconfig->sequential_discount) {
                                        if ($specific_price['price'] > 0) {
                                            $price_modified_return = $specific_price['price'];
                                        }
                                        if ($specific_price['reduction_tax'] == 0) {
                                            $price_seq = $price_modified_return - ($product_tax_calculator->addTaxes($specific_price['reduction']));
                                        } else {
                                            $price_seq = $price_modified_return - $specific_price['reduction'];
                                        }

                                        $price_seq_temp = $price_seq * (1 - $giconfig->percentage / 100);
                                        $groupinc_specific_price['reduction'] = (1 - ($price_seq_temp / $price_modified_return));
                                    } else {
                                        $groupinc_specific_price = $specific_price;
                                        $priceToGetDiscount = $price_modified_return;
                                        /*if ($specific_price['reduction_tax'] == 0) {
                                            $priceToGetDiscount = $product_tax_calculator->removeTaxes($price_modified_return);
                                        }*/

                                        $pr = $priceToGetDiscount - $specific_price['reduction'];
                                        $discount_amount = $pr * ($giconfig->percentage / 100);
                                        $groupinc_specific_price['reduction'] = $groupinc_specific_price['reduction'] + $discount_amount;
                                    }
                                } else {
                                    $groupinc_specific_price['reduction_type'] = 'percentage';
                                    if ($specific_price['from_quantity'] > 1) {
                                        $groupinc_specific_price['reduction'] = $giconfig->percentage / 100;

                                        $priceTemp = $groupinc_specific_price['price'] * (1 - $groupinc_specific_price['reduction']);
                                        $priceTemporal = $priceTemp * (1 - $specific_price['reduction']);

                                        $groupinc_specific_price['reduction'] = 1 - ($priceTemporal / $groupinc_specific_price['price']);
                                    } else {
                                        if ($giconfig->sequential_discount) {
                                            if ($specific_price['price'] > 0) {
                                                $price_modified_return = $specific_price['price'];
                                            }
                                            $price_seq = $price_modified_return * (1 - $specific_price['reduction']);
                                            $price_seq_temp = $price_seq * (1 - ($giconfig->percentage / 100));
                                            $groupinc_specific_price['reduction'] = (1 - ($price_seq_temp / $price_modified_return));
                                        } else {
                                            $groupinc_specific_price['reduction'] = $specific_price['reduction'] + $giconfig->percentage / 100;
                                        }
                                    }
                                }
                            } else if (isset($specific_price['reduction'])) {
                                $groupinc_specific_price['reduction_type'] = 'percentage';
                                $groupinc_specific_price['reduction'] = $specific_price['reduction'] + $giconfig->percentage / 100;
                            } else {
                                $groupinc_specific_price['reduction_type'] = 'percentage';
                                $groupinc_specific_price['reduction'] = $giconfig->percentage / 100;
                            }
                        }

                        if ($giconfig->type == 2) {
                            if ($specific_price['reduction'] > 0) {
                                if ($specific_price['reduction_type'] == 'amount') {
                                    $groupinc_specific_price = $specific_price;
                                    $priceToGetDiscount = $price_modified_return;
                                    /*if ($specific_price['reduction_tax'] == 0) {
                                        $priceToGetDiscount = $product_tax_calculator->removeTaxes($price_modified_return);
                                    }*/

                                    $pr = $priceToGetDiscount - $specific_price['reduction'];
                                    $discount_amount = $pr * ($giconfig->percentage / 100);
                                    $groupinc_specific_price['reduction'] = $groupinc_specific_price['reduction'] + $discount_amount;
                                } else {
                                    $groupinc_specific_price['reduction_type'] = 'percentage';
                                    $groupinc_specific_price['reduction'] = $specific_price['reduction'] + $giconfig->percentage / 100;
                                }
                            } else {
                                $groupinc_specific_price['reduction_type'] = 'percentage';
                                $priceTemp = $price_modified_return - $giconfig->fix;
                                $priceTemp = $priceTemp - ($priceTemp * ($giconfig->percentage / 100));
                                $percTemp = Tools::ps_round(($priceTemp / $price_modified_return) * 100, $dec);
                                $groupinc_specific_price['reduction'] = (string)1 - ($percTemp) / 100;

                                $price_modif = $price_modified_return * $groupinc_specific_price['reduction'];
                            }
                        }
                    } else if ($giconfig->skip_discounts && ($specific_price && (isset($specific_price['reduction']) && $specific_price['reduction'] > 0))) {
                        $groupinc_specific_price = $specific_price;
                    } else {
                        if ($giconfig->type == 0) {
                            $groupinc_specific_price['reduction_type'] = 'amount';
                            $groupinc_specific_price['reduction'] = $giconfig->fix;
                        } else if ($giconfig->type == 1) {
                            $groupinc_specific_price['reduction_type'] = 'percentage';
                            $groupinc_specific_price['reduction'] = $giconfig->percentage / 100;
                            $price_modif = $price_modified_return * $groupinc_specific_price['reduction'];
                        } else if ($giconfig->type == 2) {
                            $groupinc_specific_price['reduction_type'] = 'percentage';
                            $priceTemp = $price_modified_return - $giconfig->fix;
                            $priceTemp = $priceTemp - ($priceTemp * ($giconfig->percentage / 100));
                            $percTemp = Tools::ps_round(($priceTemp / $price_modified_return) * 100, $dec);
                            $groupinc_specific_price['reduction'] = (string)1 - ($percTemp) / 100;

                            $price_modif = $price_modified_return * $groupinc_specific_price['reduction'];
                        }
                    }

                    if ($giconfig->type == 0) {
                        $price_modif = $price_modified_return - $groupinc_specific_price['reduction'];
                    } else {
                        $price_modif = $price_modified_return * (1 - $groupinc_specific_price['reduction']);
                    }

                    if ($giconfig->min_result_price > 0) {
                        if ($price_modif < $giconfig->min_result_price) {
                            $groupinc_specific_price['reduction'] = Tools::ps_round(1 - ($giconfig->min_result_price / $price_modified_return), $dec);
                        }
                    }

                    if ($giconfig->max_result_price > 0) {
                        if ($price_modif > $giconfig->max_result_price) {
                            $groupinc_specific_price['reduction'] = Tools::ps_round(1 - ($giconfig->max_result_price / $price_modified_return), $dec);
                        }
                    }
                } else if ($giconfig->mode == 2) {
                    $groupinc_specific_price['reduction_type'] = 'percentage';

                    if ($giconfig->price_application == 2 || $giconfig->price_application == 3 || $giconfig->price_application == 5) {
                        $giconfig->fix = $product_tax_calculator->removeTaxes($giconfig->fix);
                        $price_modified_return = $product_tax_calculator->removeTaxes($price_modified_return);
                    }

                    if ($price_modified_return > $giconfig->fix) {
                        $groupinc_specific_price['reduction'] = Tools::ps_round(1 - ($giconfig->fix / $price_modified_return), 6);
                        $groupinc_specific_price['price'] = $price_modified_return;
                    } else {
                        if ($price_modified_return > 0) {
                            //$groupinc_specific_price['reduction'] = (($giconfig->fix - $price_modified_return) / $price_modified_return) * -1;
                            $groupinc_specific_price['reduction'] = 0;
                            $groupinc_specific_price['price'] = $giconfig->fix;
                        }
                    }
                } else if ($giconfig->mode == 0) {
                    $priceIncremented = 0;
                    if (!($giconfig->skip_discounts && ($specific_price && (isset($specific_price['reduction']) && $specific_price['reduction'] > 0)))) {
                        $priceIncremented = $this->getPriceIncremented($giconfig, $price_modified_return, $retailWithTaxes, $retailWithoutTaxes, $wholeWithTaxes, $wholeWithoutTaxes, $supplierWithoutTaxes, $supplierWithTaxes, $id_product, $id_group, $use_group_reduction);
                    }
                    if ($giconfig->price_application == 3 || $giconfig->price_application == 2 || $giconfig->price_application == 5) {
                        $price_modified_return = $retailWithTaxes;
                    } else {
                        $price_modified_return = $retailWithoutTaxes;
                    }

                    if ($price_modified_return > $priceIncremented) {
                        $groupinc_specific_price['reduction_type'] = 'percentage';
                        $groupinc_specific_price['reduction'] = 1 - (Tools::ps_round($priceIncremented / $price_modified_return, $dec));
                        if ($specific_price && $specific_price['from_quantity'] > 1) {
                            $priceTemp = $groupinc_specific_price['price'] * (1 - $groupinc_specific_price['reduction']);
                            $priceTemporal = $priceTemp * (1 - $specific_price['reduction']);
                            $groupinc_specific_price['reduction'] = 1 - ($priceTemporal / $groupinc_specific_price['price']);
                        } else {
                            $reductionSP = 0;
                            if (!empty($specific_price) && isset($specific_price['reduction'])) {
                                $reductionSP = $specific_price['reduction'];
                            }
                            $groupinc_specific_price['reduction'] = $groupinc_specific_price['reduction'] + $reductionSP;
                        }
                    } else {
                        if ($price_modified_return > 0) {
                            $groupinc_specific_price['reduction_type'] = 'percentage';
                            $groupinc_specific_price['reduction'] = 0;
                            $price_modified_return = $priceIncremented;
                        }
                    }
                }

                if ($giconfig->price_application == 2 || $giconfig->price_application == 3 || $giconfig->price_application == 5) {
                    $price_modified_return = $product_tax_calculator->removeTaxes($price_modified_return);
                }

                if (!$analyze) {
                    if ($giconfig->price_application == 0) {
                        $wholeWithoutTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 1) {
                        $retailWithoutTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 2) {
                        $wholeWithTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 3) {
                        $retailWithTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 4) {
                        $supplierWithoutTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 5) {
                        $supplierWithTaxes = $price_modified_return;
                    }
                }

                if ($analyze) {
                    $pricesReturnToCompare[$giconfig->id_groupinc_configuration] = $price_modified_return * (1 - $groupinc_specific_price['reduction']);
                } else {
                    if ($giconfig->mode != 2) {
                        $groupinc_specific_price['price'] = $price_modified_return;
                    }
                }
            } else {
                $price_existing = 0;
                if ($giconfig->priorize_existing) {
                    if (!empty($specific_price)) {
                        if ($specific_price['price'] > 0) {
                            $price_modified_return = $specific_price['price'];
                        }
                        $price_existing = $price_modified_return;

                        if ($specific_price['reduction_type'] == 'amount') {
                            if ($specific_price['reduction_tax'] == 0) {
                                $price_seq = $price_modified_return - ($product_tax_calculator->addTaxes($specific_price['reduction']));
                            } else {
                                $price_seq = $price_modified_return - $specific_price['reduction'];
                            }
                        } else {
                            $price_seq = $price_modified_return * (1 - $specific_price['reduction']);
                            $price_seq_temp = $price_seq * (1 - ($giconfig->percentage / 100));
                            $groupinc_specific_price['reduction'] = (1 - ($price_seq_temp / $price_modified_return));
                        }
                        $price_seq = Tools::ps_round($price_seq, 2);

                        if (!($giconfig->skip_discounts && ($specific_price && (isset($specific_price['reduction']) && $specific_price['reduction'] > 0)))) {
                            $price_modified_return = $this->getPriceIncremented($giconfig, $price_modified_return, $price_seq, $retailWithoutTaxes, $wholeWithTaxes, $wholeWithoutTaxes, $supplierWithoutTaxes, $supplierWithTaxes, $id_product, $id_group, $use_group_reduction);
                        }
                        $groupinc_specific_price['reduction'] =  (1 - ($price_modified_return / $price_existing));
                    }
                } else {
                    if ($use_group_reduction && $higher) {
                        $retailWithTaxes = $price_modified_return;
                    }
                    if (!($giconfig->skip_discounts && ($specific_price && (isset($specific_price['reduction']) && $specific_price['reduction'] > 0)))) {
                        $price_modified_return = $this->getPriceIncremented($giconfig, $price_modified_return, $retailWithTaxes, $retailWithoutTaxes, $wholeWithTaxes, $wholeWithoutTaxes, $supplierWithoutTaxes, $supplierWithTaxes, $id_product, $id_group, $use_group_reduction);
                    }

                    if ($giconfig->override_discounts && !$giconfig->higher_discount) {
                        $groupinc_specific_price = array();
                    } else {
                        if (!empty($specific_price)) {
                            $groupinc_specific_price = $specific_price;
                            $groupinc_specific_price['price'] = $price_modified_return;
                        }
                    }
                }

                if (!$analyze) {
                    if ($giconfig->price_application == 0) {
                        $wholeWithoutTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 1) {
                        $retailWithoutTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 2) {
                        $wholeWithTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 3) {
                        $retailWithTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 4) {
                        $supplierWithoutTaxes = $price_modified_return;
                    } else if ($giconfig->price_application == 5) {
                        $supplierWithTaxes = $price_modified_return;
                    }
                }

                if ($giconfig->price_application == 2 || $giconfig->price_application == 3 || $giconfig->price_application == 5) {
                    $price_modified_return = $product_tax_calculator->removeTaxes($price_modified_return);
                    if ($price_existing > 0) {
                        $price_existing = $product_tax_calculator->removeTaxes($price_existing);
                    }
                }

                if ($analyze) {
                    $pricesReturnToCompare[$giconfig->id_groupinc_configuration] = $price_modified_return;
                } else {
                    $groupinc_specific_price['price'] = $price_modified_return;
                }

                if ($price_existing > 0) {
                    $groupinc_specific_price['price'] = $price_existing;
                }
            }
            if (!$analyze) {
                $specific_price = $groupinc_specific_price;
            }
        }

        if (isset($groupinc_specific_price['reduction']) && $groupinc_specific_price['reduction'] == "0") {
            $groupinc_specific_price['reduction_type'] = "";
        }

        if (empty($groupinc_specific_price)) {
            return array();
        }
        if ($analyze) {
            return $pricesReturnToCompare;
        } else {
            if (isset($groupinc_specific_price['reduction'])) {
                $groupinc_specific_price['reduction'] = (string)$groupinc_specific_price['reduction'];
            }

            return $groupinc_specific_price;
        }
    }

    public function getPriceIncremented($giconfig, $price_modified_return, $retailWithTaxes, $retailWithoutTaxes, $wholeWithTaxes, $wholeWithoutTaxes, $supplierWithoutTaxes = 0, $supplierWithTaxes = 0, $id_product = 0, $id_group = 0, $use_group_reduction = 0)
    {
        $groupinc_quantity_fix = 0;
        $price_modified_return = 0;

        if (is_string(Context::getContext()->currency)) {
            $currencyConvert = new Currency(Context::getContext()->currency);
        } else {
            $currencyConvert = Context::getContext()->currency;
        }

        $giconfig->fix = Tools::convertPrice($giconfig->fix, $currencyConvert);

        if ($giconfig->mode == 2) { // fixed price import
            $price_modified_return = $giconfig->fix;
        } else {
            if ($giconfig->type == 0) { // fix mode
                if ($giconfig->mode == 0) {
                    $groupinc_quantity_fix += $giconfig->fix;
                } else {
                    $groupinc_quantity_fix -= $giconfig->fix;
                }
                if ($giconfig->price_application == 0) {
                    $price_modified_return = $wholeWithoutTaxes + $groupinc_quantity_fix;
                } else if ($giconfig->price_application == 1) {
                    $price_modified_return = $retailWithoutTaxes + $groupinc_quantity_fix;
                } else if ($giconfig->price_application == 2) {
                    $price_modified_return = $wholeWithTaxes + $groupinc_quantity_fix;
                } else if ($giconfig->price_application == 3) {
                    $price_modified_return = $retailWithTaxes + $groupinc_quantity_fix;
                } else if ($giconfig->price_application == 4) {
                    $price_modified_return = $supplierWithoutTaxes + $groupinc_quantity_fix;
                } else if ($giconfig->price_application == 5) {
                    $price_modified_return = $supplierWithTaxes + $groupinc_quantity_fix;
                }
            } else if ($giconfig->type == 1) { // percentage mode
                if ($giconfig->price_calculation == 0) {
                    $groupinc_quantity_percent = $wholeWithoutTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 1) {
                    $groupinc_quantity_percent = $retailWithoutTaxes * ($giconfig->percentage / 100);
                } else if ($giconfig->price_calculation == 2) {
                    $groupinc_quantity_percent = $wholeWithTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 3) {
                    $groupinc_quantity_percent = $retailWithTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 4) {
                    $groupinc_quantity_percent = $supplierWithoutTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 5) {
                    $groupinc_quantity_percent = $supplierWithTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 6) {
                    $groupinc_quantity_percent = ($retailWithoutTaxes - $wholeWithoutTaxes) * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 7) {
                    $groupinc_quantity_percent = ($retailWithTaxes - $wholeWithTaxes) * $giconfig->percentage / 100;
                }
                if ($giconfig->price_application == 0) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $wholeWithoutTaxes + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $wholeWithoutTaxes - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 1) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $retailWithoutTaxes + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $retailWithoutTaxes - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 2) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $wholeWithTaxes + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $wholeWithTaxes - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 3) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $retailWithTaxes + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $retailWithTaxes - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 4) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $supplierWithoutTaxes + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $supplierWithoutTaxes - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 5) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $supplierWithTaxes + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $supplierWithTaxes - $groupinc_quantity_percent;
                    }
                }
            } else if ($giconfig->type == 2) { // fix + percentage mode
                if ($giconfig->price_calculation == 0) {
                    $groupinc_quantity_percent = $wholeWithoutTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 1) {
                    $groupinc_quantity_percent = $retailWithoutTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 2) {
                    $groupinc_quantity_percent = $wholeWithTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 3) {
                    $groupinc_quantity_percent = $retailWithTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 4) {
                    $groupinc_quantity_percent = $supplierWithoutTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 5) {
                    $groupinc_quantity_percent = $supplierWithTaxes * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 6) {
                    $groupinc_quantity_percent = ($retailWithoutTaxes - $wholeWithoutTaxes) * $giconfig->percentage / 100;
                } else if ($giconfig->price_calculation == 7) {
                    $groupinc_quantity_percent = ($retailWithTaxes - $wholeWithTaxes) * $giconfig->percentage / 100;
                }

                if ($giconfig->price_application == 0) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $wholeWithoutTaxes + $giconfig->fix + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $wholeWithoutTaxes - $giconfig->fix - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 1) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $retailWithoutTaxes + $giconfig->fix + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $retailWithoutTaxes - $giconfig->fix - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 2) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $wholeWithTaxes + $giconfig->fix + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $wholeWithTaxes - $giconfig->fix - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 3) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $retailWithTaxes + $giconfig->fix + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $retailWithTaxes - $giconfig->fix - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 4) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $supplierWithoutTaxes + $giconfig->fix + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $supplierWithoutTaxes - $giconfig->fix - $groupinc_quantity_percent;
                    }
                } else if ($giconfig->price_application == 5) {
                    if ($giconfig->mode == 0) {
                        $price_modified_return = $supplierWithTaxes + $giconfig->fix + $groupinc_quantity_percent;
                    } else {
                        $price_modified_return = $supplierWithTaxes - $giconfig->fix - $groupinc_quantity_percent;
                    }
                }
            }
        }
        if ($giconfig->min_result_price > 0) {
            if ($price_modified_return < $giconfig->min_result_price) {
                $price_modified_return = $giconfig->min_result_price;
            }
        }

        if ($giconfig->max_result_price > 0) {
            if ($price_modified_return > $giconfig->max_result_price) {
                $price_modified_return = $giconfig->max_result_price;
            }
        }

        if ($use_group_reduction) {
            $group_reduction = $this->getGroupReduction($price_modified_return, $id_product, $id_group);

            if (!$giconfig->override_discounts) {
                if ($group_reduction > 0) {
                    $price_modified_return -= $group_reduction;
                }
            }
        }
        return $price_modified_return;
    }

    public function getProductsLite($id_lang, $only_active = false, $front = false, Context $context = null, $manufacturers = false, $suppliers = false, $categories = false)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $sql = 'SELECT p.`id_product`, CONCAT(p.`reference`, " - ", pl.`name`) as name FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')'.
                ($categories ? ' LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product` ' : '').'
                WHERE pl.`id_lang` = '.(int)$id_lang.
                    ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
                    ($manufacturers ? ' AND p.`id_manufacturer` IN ('.$manufacturers.')' : '').
                    ($suppliers ? ' AND p.`id_supplier` IN ('.$suppliers.')' : '').
                    ($categories ? ' AND cp.`id_category` IN ('.$categories.')' : '').
                    ($only_active ? ' AND product_shop.`active` = 1' : '');

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return ($rq);
    }

    public static function getProductsFromIdCart($id_cart = 0, $id_product = 0)
    {
        $context = Context::getContext();
        if (isset($context->controller->controller_type) && in_array($context->controller->controller_type, array('admin'))) {
            return array();
        }

        if (empty($id_cart) && isset($context->cart)) {
            $id_cart = (int)$context->cart->id;
        }

        if (empty($id_cart) && isset($context->cart)) {
            $id_cart = (int)$context->cookie->id_cart;
        }

        $sql =
            'SELECT cp.*, p.`id_supplier`, p.`id_manufacturer` FROM `'._DB_PREFIX_.'cart_product` cp LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product` WHERE id_cart = '.(int)$id_cart;

        if ($id_product > 0) {
            $sql .= ' AND cp.id_product = '.$id_product;
        }

        return Db::getInstance()->executeS($sql);
    }

    public function getIdProductAttribute($id_product, $id_attribute)
    {
        $sql =
            'SELECT pac.id_product_attribute
                FROM `'._DB_PREFIX_.'product_attribute_combination` pac
                LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
                WHERE pa.id_product = '.$id_product.' AND pac.id_attribute = '.$id_attribute;
        $result = Db::getInstance()->executeS($sql);

        if (!empty($result)) {
            return $result;
        } else {
            return 0;
        }
    }

    public function getWholesalePrice($id_product, $id_product_attribute)
    {
        $sql =
            'SELECT pa.wholesale_price
                FROM `'._DB_PREFIX_.'product_attribute` pa
                WHERE pa.id_product = '.$id_product.' AND pa.id_product_attribute = '.$id_product_attribute;
        $result = Db::getInstance()->getRow($sql);
        if (!empty($result)) {
            return $result['wholesale_price'];
        } else {
            return 0;
        }
    }

    public function getImpact($id_product, $id_product_attribute)
    {
        $sql =
            'SELECT pa.price
                FROM `'._DB_PREFIX_.'product_attribute` pa
                WHERE pa.id_product = '.$id_product.' AND pa.id_product_attribute = '.$id_product_attribute;
        $result = Db::getInstance()->getRow($sql);
        if (!empty($result)) {
            return $result['price'];
        } else {
            return 0;
        }
    }

    public function getProductsPricesDrop($conf, $id_shop, $id_lang)
    {
        $productsReturn = array();
        if ($conf['products_excluded'] == '' && $conf['products'] == '' && $conf['manufacturers'] == '' && $conf['categories'] == '' && $conf['suppliers'] == '' && $conf['attributes'] && $conf['features'] && !$conf['filter_stock'] && !$conf['filter_prices']) {
            return $this->getProductsLite($id_lang, true, false);
        }
        $categories = false;
        $manufacturers = false;
        $suppliers = false;
        if ($conf['products'] != '') {
            $products = explode(";", $conf['products']);
        } else {
            if ($conf['categories'] != '') {
                if (@unserialize($conf['categories']) !== false) {
                    $categories = implode(',', unserialize($conf['categories']));
                } else {
                    $categories = implode(',', explode(';', $conf['categories']));
                }
            }
            if ($conf['manufacturers'] != '') {
                $manufacturers = implode(',',explode(";", $conf['manufacturers']));
            }
            if ($conf['suppliers']) {
                $suppliers = implode(',',explode(";", $conf['suppliers']));
            }
            $products = $this->getProductsLite($id_lang, true, false, null, $manufacturers, $suppliers, $categories);
            if ($conf['features']) {
                $array_features_selected = Tools::jsonDecode($conf['features'], true);
                $array_features_string = implode(',', $array_features_selected);
                if (!empty($products)) {
                    $prodsReturn = implode(',', array_column($products, 'id_product'));
                    if ($prodsReturn == "") {
                        foreach ($prods as $prod) {
                            $prodsReturnArr[] = $prod['id_product'];
                        }
                        if (!empty($prodsReturnArr)) {
                            $prodsReturn = implode(',', $prodsReturnArr);
                        }
                    }
                }
                foreach ($array_features_selected as $feat) {
                    $query = '
                        SELECT id_product FROM `'._DB_PREFIX_.'feature_product`
                            WHERE id_feature_value = '.$feat.' and id_product in ('.$prodsReturn.')';
                    $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
                    foreach ($products as $prod) {
                        $prodsReturnArr[] = $prod['id_product'];
                    }
                    if (!empty($prodsReturnArr)) {
                        $prodsReturn = implode(',', $prodsReturnArr);
                    }
                }
            }
            $prods = array();
            foreach ($products as $p) {
                $prods[] = $p['id_product'];
            }
            $products = $prods;
        }

        $products_excl_array = array();
        if ($conf['products_excluded']) {
            $products_excl_array = explode(';', $conf['products_excluded']);
        }

        foreach ($products as $p) {
            if (!GroupincConfiguration::checkStockPriceWeight($conf, $p)) {
                continue;
            }
            if (!empty($products_excl_array)) {
                if (in_array($p, $products_excl_array)) {
                    continue;
                }
            }
            $productsReturn[] = (int)$p;
        }
        return $productsReturn;
    }

    public static function isShowableBySchedule($configuration)
    {
        $schedule = Tools::jsonDecode($configuration['schedule']);
        $dayOfWeek = date('w') - 1;
        if ($dayOfWeek < 0) {
            $dayOfWeek = 6;
        }
        if (is_array($schedule)) {
            if (is_object($schedule[$dayOfWeek]) && $schedule[$dayOfWeek]->isActive === true) {
                if ($schedule[$dayOfWeek]->timeFrom <= date('H:i') && $schedule[$dayOfWeek]->timeTill > date('H:i')) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public static function isProductItemsCart($configuration, $quantity, $id_product, $id_product_attribute)
    {
        /* configuration grouped_by: 0-> non grouped / 1-> product / 2-> category / 3-> manufacturer / 4-> supplier */
        if ($quantity > 1 && $quantity >= $configuration['product_qty']) {
            return true;
        }

        $product_count_flag = false;

        if (!empty(Context::getContext()->cart) && Context::getContext()->cart->id) {
            $products = GroupincConfiguration::getProductsFromIdCart(Context::getContext()->cart->id);
        }
        $ret = false;
        if (!empty($products)) {
            switch ($configuration['grouped_by']) {
                case 0: // non grouped
                    break;
                case 1:
                    $ret = GroupincConfiguration::checkProductsGrouped($products, $configuration, $id_product, $id_product_attribute);
                    break;
                case 2:
                    $ret = GroupincConfiguration::checkCategoriesGrouped($products, $configuration, $id_product, $id_product_attribute);
                    break;
                case 3:
                    $ret = GroupincConfiguration::checkManufacturersGrouped($products, $configuration, $id_product, $id_product_attribute);
                    break;
                case 4:
                    $ret = GroupincConfiguration::checkSuppliersGrouped($products, $configuration, $id_product, $id_product_attribute);
                    break;
                default:
                    break;
            }
        }
        return $ret;
    }

    protected static function checkProductsGrouped($products, $conf, $id_product, $id_product_attribute)
    {
        if ($conf['products'] !== '') {
            $products_array = explode(';', $conf['products']);
        } else {
            $products_array = array();
        }

        $totalQty = 0;

        if (!empty($products)) {
            foreach ($products as $p) {
                $found_in_cart = false;
                if (!empty($products_array)) {
                    if (in_array($p['id_product'], $products_array)) {
                        $found_in_cart = true;
                    }
                }

                if ($p['id_product'] == $id_product || $found_in_cart) {
                    $totalQty += (int)$p['quantity'];
                }
            }
        }
        if ($totalQty >= $conf['product_qty']) {
            return true;
        }
        return false;
    }

    protected static function checkCategoriesGrouped($products, $configuration, $id_product, $id_product_attribute)
    {
        $categories_array = array();
        if ($configuration['categories'] !== '') {
            if (@unserialize($configuration['categories']) !== false) {
                $categories_array = unserialize($configuration['categories']);
            } else {
                $categories_array = explode(';', $configuration['categories']);
            }
        }

        $totalQty = 0;

        foreach ($products as $p) {
            $found_in_category = false;
            if (!empty($categories_array) && $configuration['categories'] != '') {
                $categories = Product::getProductCategories($p['id_product']);
                foreach ($categories as $category) {
                    if (in_array($category, $categories_array)) {
                        $found_in_category = true;
                    }
                }
            } else {
                $found_in_category = true;
            }

            if (($p['id_product'] == $id_product && $p['id_product_attribute'] == $id_product_attribute) || $found_in_category) {
                $totalQty += (int)$p['quantity'];
            }
        }

        if ($totalQty >= $configuration['product_qty']) {
            return true;
        }
        return false;
    }

    protected static function checkManufacturersGrouped($products, $configuration, $id_product, $id_product_attribute)
    {
        if ($configuration['manufacturers'] !== '') {
            $manufacturers_array = explode(';', $configuration['manufacturers']);
        } else {
            $manufacturers = Manufacturer::getManufacturers(false, Context::getContext()->language->id, false);
            if (!empty($manufacturers)) {
                foreach ($manufacturers as $manufacturer) {
                    $manufacturers_array[] = $manufacturer['id_manufacturer'];
                }
            }
        }

        $totalQty = 0;
        foreach ($products as $p) {
            $same_manufacturer = false;
            if (!empty($manufacturers_array)) {
                if (in_array($p['id_manufacturer'], $manufacturers_array)) {
                    $same_manufacturer = true;
                }
            }

            if (($p['id_product'] == $id_product && $p['id_product_attribute'] == $id_product_attribute) || $same_manufacturer) {
                $totalQty += (int)$p['quantity'];
            }
        }

        /* secuencia múltiple */
        /*        $isMultiple = 1;
        if ($totalQty > 1) {
            $isMultiple = $totalQty % $configuration['product_qty'];
        }
        if ($totalQty == $configuration['product_qty'] || $isMultiple == 0) {
        */

        if ($totalQty >= $configuration['product_qty']) {
            return true;
        }
        return false;
    }

    protected static function checkSuppliersGrouped($products, $configuration, $id_product, $id_product_attribute)
    {
        if ($configuration['suppliers'] !== '') {
            $suppliers_array = explode(';', $configuration['suppliers']);
        } else {
            $suppliers_array = Supplier::getSuppliers(false, Context::getContext()->language->id, false);
        }
        $totalQty = 0;
        foreach ($products as $p) {
            $same_supplier = false;
            if (!empty($suppliers_array)) {
                if (in_array($p['id_supplier'], $suppliers_array)) {
                    $same_supplier = true;
                }
            }

            if (($p['id_product'] == $id_product && $p['id_product_attribute'] == $id_product_attribute) || $same_supplier) {
                $totalQty += (int)$p['quantity'];
            }
        }
        if ($totalQty >= $configuration['product_qty']) {
            return true;
        }
        return false;
    }

    public static function getProductProperties($id_lang, $row)
    {
        $context = Context::getContext();
        if (isset($context->controller->controller_type) && in_array($context->controller->controller_type, array('admin'))) {
            return $row;
        }

        $id_shop = 1;
        $id_customer = 0;
        $id_lang = 0;
        $id_currency = 0;

        if (isset($context->shop) && !empty($context->shop)) {
            $id_shop = $context->shop->id;
        }

        if (isset($context->customer) && !empty($context->customer)) {
            $id_customer = $context->customer->id;
        }

        if (isset($context->language) && !empty($context->language->id)) {
            $id_lang = $context->language->id;
        }

        if (isset($context->currency) && !empty($context->currency)) {
            $id_currency = $context->currency->id;
        }

        $id_country = 0;
        $id_state = 0;
        if (isset($context->cart) && $context->cart->id_address_delivery > 0) {
            $address = new Address($context->cart->id_address_delivery);
            $id_country = $address->id_country;
            $id_state = $address->id_state;
        }

        if ($id_country == 0 && isset($context->country)) {
            $id_country = $context->country->id;
        }

        $configs_onsale_show_discounts = GroupincConfiguration::getGIConfigurations($id_shop, $row['id_product'], $id_customer, $id_country, $id_state, $id_currency, $id_lang, true, true, 0, 0, false, true);

        if (!empty($configs_onsale_show_discounts)) {
            $row['on_sale'] = 1;
        }
        return $row;
    }

    public static function changeProductProperties($product = null)
    {
        $groupinc = new GroupincConfiguration();
        $context = Context::getContext();
        $id_shop = $context->shop->id;
        $id_currency = $context->currency->id;
        $id_customer = $context->customer->id;

        $id_country = 0;
        $id_state = 0;
        if (isset($context->cart) && $context->cart->id_address_delivery > 0) {
            $address = new Address($context->cart->id_address_delivery);
            $id_country = $address->id_country;
            $id_state = $address->id_state;
        }

        if ($id_country == 0) {
            $id_country = $context->country->id;
        }

        $onsaleconf = $groupinc->getGIConfigurations($id_shop, $product->id, $id_customer, $id_country, $id_state, $id_currency, $context->language->id, true, true, 0, false, true);
        if (!empty($onsaleconf)) {
            $product->on_sale = 1;
        }
        return $product;
    }

    public static function checkCartAmount($conf, $id_product = 0, $id_product_attribute = 0)
    {
        if ((float)$conf['cart_amount'] > 0) {
            if (empty(Context::getContext()->cart)) {
                $cart = new Cart(Context::getContext()->cookie->id_cart);
            } else {
                $cart = Context::getContext()->cart;
            }

            if ($cart && $cart->id) {
                $products = GroupincConfiguration::getProductsFromIdCart($cart->id);

                $total = 0;
                foreach ($products as $product) {
                    $price = 0;
                    $p = new Product($product['id_product']);
                    $price += $p->price;
                    if ($product['id_product_attribute']) {
                        $comb = new Combination($product['id_product_attribute']);
                        $price += $comb->price;
                    }
                    $total += $price * $product['quantity'];
                }

                if ($total < (float)$conf['cart_amount']) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }


    public static function checkStockPriceWeight($conf, $id_product = 0, $id_product_attribute = 0, $ptc = null)
    {
        $product = null;
        if ($conf['filter_stock']) {
            $product = new Product($id_product);
            if (!empty($conf['attributes']) && $id_product_attribute) {
                $stock = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
            } else {
                $stock = Product::getQuantity($id_product);
            }

            if (($conf['max_stock'] > 0 && $conf['min_stock'] > 0) || ($conf['max_stock'] > 0 && $conf['min_stock'] <= 0)) {
                if ((int)$stock < $conf['min_stock'] || (int)$stock > $conf['max_stock']) {
                    return false;
                }
            } else if ($conf['max_stock'] <= 0 && $conf['min_stock'] <= 0) {
                if ((int)$stock > 0) {
                    return false;
                }
            }
            /*if (($conf['max_stock'] > 0 && $conf['min_stock'] > 0) || ($conf['max_stock'] > 0 && $conf['min_stock'] <= 0)) {
                if ((int)$stock < $conf['min_stock'] || (int)$stock > $conf['max_stock']) {
                    return false;
                }
            } else if ($conf['max_stock'] <= 0 && $conf['min_stock'] <= 0) {
                if ((int)$stock > 0 || ($stock > $conf['max_stock'] && $stock < $conf['min_stock'])) {
                    return false;
                } else if ($conf['max_stock'] == 0 && $conf['min_stock'] == 0) {
                    if ($stock != 0) {
                        return false;
                    }
                }
            }*/
        }

        if ($conf['filter_weight']) {
            if (empty($product)) {
                $product = new Product($id_product);
            }
            $weight = $product->weight;
            if ($product->hasAttributes()) {
                $combination = new Combination($id_product_attribute);
                $weight += $combination->weight;
            }

            if ($weight < $conf['min_weight'] || ($conf['max_weight'] > 0 && $weight > $conf['max_weight'])) {
                return false;
            }
        }

        if ($conf['filter_prices']) {
            if (empty($product)) {
                $product = new Product($id_product);
            }
            $price_to_compare = 0;

            if ($id_product && $conf['fp_product_price']) {
                if ($conf['threshold_price'] == 0 || $conf['threshold_price'] == 2) {
                    $price_to_compare = $product->wholesale_price;
                } else if ($conf['threshold_price'] == 1 || $conf['threshold_price'] == 3) {
                    $price_to_compare = $product->price;
                }
            }

            if ($conf['fp_attribute_price']) {
                if ($product->hasAttributes()) {
                    if (!$id_product_attribute) {
                        $id_product_attribute = Product::getDefaultAttribute($id_product);
                    }
                    $comb = new Combination($id_product_attribute);
                    if ($conf['threshold_price'] == 0 || $conf['threshold_price'] == 2) {
                        $price_to_compare += $comb->wholesale_price;
                    } else if ($conf['threshold_price'] == 1 || $conf['threshold_price'] == 3) {
                        $price_to_compare += $comb->price;
                    }
                }
            }

            if ($price_to_compare > 0 && !empty($ptc) && ($conf['threshold_price'] == 2 || $conf['threshold_price'] == 3)) {
                $price_to_compare = Tools::ps_round($ptc->addTaxes($price_to_compare), 2);
            }

            if (is_string(Context::getContext()->currency)) {
                $currencyConvert = new Currency(Context::getContext()->currency);
            } else {
                $currencyConvert = Context::getContext()->currency;
            }

            $threshold_min = Tools::convertPrice((float)$conf['threshold_min_price'], $currencyConvert);
            $threshold_max = Tools::convertPrice((float)$conf['threshold_max_price'], $currencyConvert);
            $price_to_compare = Tools::convertPrice((float)$price_to_compare, $currencyConvert);

            if ((float)$threshold_max == 0 && (float)$threshold_min == 0 && (float)$price_to_compare == 0) {
                return true;
            } else if ((float)$threshold_max == 0 && (float)$threshold_min == 0 && (float)$price_to_compare != 0) {
                return false;
            } else if ((float)$threshold_max != 0 && (float)$threshold_min == 0 && (float)$price_to_compare > (float)$threshold_max) {
                return false;
            } else if ((float)$threshold_max == 0 && (float)$threshold_min != 0 && (float)$price_to_compare < (float)$threshold_min) {
                return false;
            } else if ((float)$threshold_max != 0 && (float)$threshold_min != 0 && ((float)$price_to_compare < (float)$threshold_min || (float)$price_to_compare > (float)$threshold_max)) {
                return false;
            }
        }
        return true;
    }

    protected static function checkExceptions($conf = false, $id_product = false, $id_customer = false)
    {
        if ($conf) {
            if ($conf['customers_excluded']) {
                $customers_excl_array = explode(';', $conf['customers_excluded']);
                if (in_array($id_customer, $customers_excl_array)) {
                    return false;
                }
            }

            if ($conf['products_excluded']) {
                $products_excl_array = explode(';', $conf['products_excluded']);
                if (in_array($id_product, $products_excl_array)) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function getConfigsCD($id_shop = 0, $id_product = 0, $id_product_attribute = 0)
    {
        $context = Context::getContext();
        if (empty($id_shop)) {
            $id_shop = $context->shop->id;
        }

        $query = '';
        $today = date("Y-m-d H:i:s");

        $query = '
                 SELECT gi.* FROM `'._DB_PREFIX_.'groupinc_configuration` gi ';

        $datefilters = ' WHERE (date_from <= "'.$today. '" OR date_from = "0000-00-00 00:00:00") AND (date_to >= "'.$today.'" OR date_to = "0000-00-00 00:00:00")';

        $query = $query.$datefilters;

        $query = $query.' AND gi.`id_shop` = '.(int)$id_shop.' AND gi.`active` = 1 ';

        $configs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);


        if (empty($configs) || $configs === false) {
            return false;
        }

        $id_customer = 0;
        $id_currency = 0;
        $id_country = 0;
        $id_state = 0;
        $zone = 0;

        if (isset($context->customer) && !empty($context->customer)) {
            $id_customer = $context->customer->id;
        }

        if (isset($context->language) && !empty($context->language->id)) {
            $id_lang = $context->language->id;
        }

        if (isset($context->currency) && !empty($context->currency)) {
            $id_currency = $context->currency->id;
        }

        if (isset($context->customer) && !empty($context->customer)) {
            $id_customer = $context->customer->id;
        }


        if (isset($context->cart) && isset($context->cart->id_address_delivery) && $context->cart->id_address_delivery != 0) {
            $id_address_delivery = $context->cart->id_address_delivery;
            $address = new Address($id_address_delivery);
            $id_country = $address->id_country;
            $id_state = $address->id_state;
        } else {
            $address = new Address();
            $address->id_country = $context->country->id;
        }

        $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context));
        $ptc = $tax_manager->getTaxCalculator();

        if ($id_country == 0) {
            $id_country = $context->country->id;
        }

        $customer = new Customer($id_customer);
        $customer_groups = $customer->getGroupsStatic($customer->id);
        $categories = Product::getProductCategories($id_product);
        $product = new Product($id_product);
        $id_manufacturer = $product->id_manufacturer;
        $product_suppliers_array = ProductSupplier::getSupplierCollection($id_product);

        if ($id_state > 0) {
            $zone = State::getIdZone($id_state);
        } else if ($id_country != null && $id_country > 0) {
            $country = new Country($id_country);
            $zone = $country->getIdZone($id_country);
        }

        $configs_priority = array();
        foreach ($configs as $key => $row) {
            $configs_priority[$key] = $row['date_to'];
        }
        array_multisort($configs_priority, SORT_ASC, $configs);

        $array_configurations_result = array();

        foreach ($configs as $conf) {
            if (!GroupincConfiguration::checkExceptions($conf, $id_product, $id_customer)) {
                continue;
            }

            if (!GroupincConfiguration::isShowableBySchedule($conf)) {
                continue;
            }

            if (!GroupincConfiguration::checkStockPriceWeight($conf, $id_product, $id_product_attribute)) {
                continue;
            }

            if (!GroupincConfiguration::checkCartAmount($conf, $id_product)) {
                continue;
            }

            if ($conf['higher_discount'] == 1) {
                $higher = false;
                $specific_price = SpecificPrice::getSpecificPrice(
                            (int)$id_product,
                            $id_shop,
                            $id_currency,
                            $id_country,
                            1,
                            1,
                            $id_product_attribute,
                            $id_customer,
                            Context::getContext()->cart->id,
                            1
                        );

                if ($specific_price && $specific_price['reduction'] > 0) {
                    $red = 0;
                    if ($specific_price['reduction_type'] == 'percentage' && $conf['type'] == 1) {
                        if ((float)$conf['percentage'] / 100 <= (float)$specific_price['reduction']) {
                            continue;
                        }
                    } else if ($specific_price['reduction_type'] == 'amount' && $conf['type'] == 0) {
                        if ((float)$conf['fix'] <= (float)$specific_price['reduction']) {
                            continue;
                        }
                    }
                    $higher = true;
                }
                if ($higher) {
                    continue;
                }
            }

            if ($conf['currencies'] == 'all') {
                $conf['currencies'] = '';
            }
            if ($conf['languages'] == 'all') {
                $conf['languages'] = '';
            }
            if ($conf['groups'] == 'all') {
                $conf['groups'] = '';
            }
            if ($conf['products'] == 'all') {
                $conf['products'] = '';
            }
            if ($conf['customers'] == 'all') {
                $conf['customers'] = '';
            }
            if ($conf['countries'] == 'all') {
                $conf['countries'] = '';
            }
            if ($conf['zones'] == 'all') {
                $conf['zones'] = '';
            }
            if ($conf['categories'] == 'all') {
                $conf['categories'] = '';
            }
            if ($conf['manufacturers'] == 'all') {
                $conf['manufacturers'] = '';
            }
            if ($conf['suppliers'] == 'all') {
                $conf['suppliers'] = '';
            }
            if ($conf['features'] == 'all') {
                $conf['features'] = '';
            }
            if ($conf['attributes'] == 'all') {
                $conf['attributes'] = '';
            }

            if ($conf['attributes'] == '' && $conf['features'] == '' && $conf['currencies'] == '' && $conf['languages'] == '' && $conf['groups'] == '' && $conf['products'] == '' && $conf['customers'] == '' && $conf['countries'] == '' && $conf['zones'] == '' && $conf['categories'] == '' && $conf['manufacturers'] == '' && $conf['suppliers'] == '') {
                $array_configurations_result[] = $conf;
                if ($conf['first_condition']) {
                    break;
                } else {
                    continue;
                }
            }

            $filter_features = false;
            $array_features_selected = Tools::jsonDecode($conf['features'], true);
            $product_features = Product::getFeaturesStatic((int)$id_product);

            $flag_features = 0;
            if (!empty($array_features_selected) && count($array_features_selected) > 0) {
                foreach ($product_features as $pf) {
                    if (isset($array_features_selected[$pf['id_feature']])) {
                        $array_f = explode(";", $array_features_selected[$pf['id_feature']]);
                        if (in_array($pf['id_feature_value'], $array_f)) {
                            $flag_features++;
                            continue;
                        }
                    }
                }
            } else {
                $filter_features = true;
            }

            if ($flag_features > 0) {
                $filter_features = true;
            }

            $filter_attributes = false;
            $array_attributes_selected = json_decode($conf['attributes'], true);
            if (!empty($array_attributes_selected)) {
                $product_attribute_combinations = $product->getAttributeCombinationsById($id_product_attribute, $id_lang);
                foreach ($product_attribute_combinations as $key => $prod_attr_comb) {
                    if (isset($array_attributes_selected[(int)$prod_attr_comb['id_attribute_group']])) {
                        $array_a = explode(";", $array_attributes_selected[(int)$prod_attr_comb['id_attribute_group']]);
                        if (in_array((int)$prod_attr_comb['id_attribute'], $array_a)) {
                            $filter_attributes = true;
                            break;
                        } else {
                            $filter_attributes = false;
                        }
                    }
                }
            } else {
                $filter_attributes = true;
            }

            if ($id_product_attribute == 0 && empty($conf['attributes'])) {
                $filter_attributes = true;
            }

            $filter_currencies = true;
            if ($conf['currencies'] !== '') {
                $currencies_array = explode(';', $conf['currencies']);
                if (!in_array($id_currency, $currencies_array)) {
                    $filter_currencies = false;
                }
            }
            $filter_languages = true;
            if ($conf['languages'] !== '') {
                $languages_array = explode(';', $conf['languages']);
                if (!in_array($id_lang, $languages_array)) {
                    $filter_languages = false;
                }
            }

            $filter_groups = true;
            $filter_customers = true;
            if ($conf['groups'] !== '' && $conf['customers'] == '') {
                $groups_array = explode(';', $conf['groups']);
                foreach ($customer_groups as $group) {
                    if (!in_array($group, $groups_array)) {
                        $filter_groups = false;
                    } else {
                        $filter_groups = true;
                        break;
                    }
                }
                if (!$filter_groups) {
                    $filter_customers = false;
                }
            } else if ($conf['groups'] == '' && $conf['customers'] !== '') {
                $customers_array = explode(';', $conf['customers']);
                if (!in_array($id_customer, $customers_array)) {
                    $filter_customers = false;
                }
            } else if ($conf['groups'] !== '' && $conf['customers'] !== '') {
                $groups_array = explode(';', $conf['groups']);
                foreach ($customer_groups as $group) {
                    if (!in_array($group, $groups_array)) {
                        $filter_groups = false;
                    } else {
                        $filter_groups = true;
                    }
                }
                if (!$filter_groups) {
                    $customers_array = explode(';', $conf['customers']);
                    if (!in_array($id_customer, $customers_array)) {
                        $filter_customers = false;
                    } else {
                        $filter_customers = true;
                    }
                } else {
                    $customers_array = explode(';', $conf['customers']);
                    if (!in_array($id_customer, $customers_array)) {
                        $filter_customers = false;
                    }
                }
            }
            $filter_countries = true;
            if ($conf['countries'] !== '') {
                $countries_array = explode(';', $conf['countries']);

                if (!in_array($id_country, $countries_array)) {
                    $filter_countries = false;
                }
            }

            $filter_zones = true;
            if ($conf['zones'] !== '') {
                $zones_array = explode(';', $conf['zones']);
                if (!in_array($zone, $zones_array)) {
                    $filter_zones = false;
                }
            }
            $filter_categories = true;
            $filter_products = true;

            if (@unserialize($conf['categories']) !== false) {
                $categories_array = unserialize($conf['categories']);
            } else {
                $categories_array = explode(';', $conf['categories']);
            }

            if ($conf['categories'] !== '' && $conf['products'] == '') {
                foreach ($categories as $category) {
                    if (in_array($category, $categories_array)) {
                        $filter_categories = true;
                        $filter_products = true;
                        break;
                    } else {
                        $filter_categories = false;
                    }
                }
                if (!$filter_categories) {
                    $filter_products = false;
                }
            } else if ($conf['categories'] == '' && $conf['products'] !== '') {
                $products_array = explode(';', $conf['products']);
                if (!in_array($id_product, $products_array)) {
                    $filter_products = false;
                    $filter_categories = true;
                }
            } else if ($conf['categories'] !== '' && $conf['products'] !== '') {
                foreach ($categories as $category) {
                    if (!in_array($category, $categories_array)) {
                        $filter_categories = false;
                    } else {
                        $filter_categories = true;
                        break;
                    }
                }
                if (!$filter_categories) {
                    $products_array = explode(';', $conf['products']);
                    if (!in_array($id_product, $products_array)) {
                        $filter_products = false;
                    } else {
                        $filter_products = true;
                    }
                } else {
                    $products_array = explode(';', $conf['products']);
                    if (!in_array($id_product, $products_array)) {
                        $filter_products = false;
                    }
                }
            }

            $filter_manufacturers = true;
            if ($conf['manufacturers'] !== '') {
                $manufacturers_array = explode(';', $conf['manufacturers']);
                if (!in_array($id_manufacturer, $manufacturers_array)) {
                    $filter_manufacturers = false;
                }
            }

            $filter_suppliers = true;
            if ($conf['suppliers'] !== '') {
                $filter_suppliers = false;
                $suppliers_array = explode(';', $conf['suppliers']);
                if (!empty($product_suppliers_array)) {
                    foreach ($product_suppliers_array as $ps) {
                        if (in_array($ps->id_supplier, $suppliers_array)) {
                            $filter_suppliers = true;
                            break;
                        }
                    }
                }
            }

            if ($filter_currencies && $filter_languages && $filter_attributes && $filter_features && $filter_groups && $filter_customers && $filter_countries && $filter_zones && $filter_categories && $filter_products && $filter_manufacturers && $filter_suppliers) {
                $array_configurations_result[] = $conf;
                    continue;
            }
        }

        if (count($array_configurations_result) > 0) {
            return $array_configurations_result;
        } else {
            return false;
        }
    }

    public static function getAdminGIconfigurations($id_shop, $id_product)
    {
        $today = date("Y-m-d H:i:s");

        $query = '
                SELECT gi.* FROM `'._DB_PREFIX_.'groupinc_configuration` gi WHERE gi.`id_shop` = '
                .(int)$id_shop.' AND gi.`active` = 1 AND gi.`backoffice` = 1
                AND (date_from <= "'.$today. '" OR date_from = "0000-00-00 00:00:00")
                AND (date_to >= "'.$today.'" OR date_to = "0000-00-00 00:00:00")
                ORDER BY gi.`priority`,gi.`id_groupinc_configuration`';

        $categories = Product::getProductCategories($id_product);
        $product = new Product($id_product);
        $id_manufacturer = $product->id_manufacturer;
        $product_suppliers_array = ProductSupplier::getSupplierCollection($id_product);

        $configs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($configs === false) {
            return false;
        }

        $array_configurations_result = array();
        foreach ($configs as $conf) {
            /* retrocompatibility with old rules which have the value 'all' in the database */
            if ($conf['products'] == 'all') {
                $conf['products'] = '';
            }
            if ($conf['categories'] == 'all') {
                $conf['categories'] = '';
            }
            if ($conf['manufacturers'] == 'all') {
                $conf['manufacturers'] = '';
            }
            if ($conf['suppliers'] == 'all') {
                $conf['suppliers'] = '';
            }

            if ($conf['filter_stock']) {
                $stock = Product::getQuantity($id_product);

                if ($stock < $conf['min_stock'] || $stock > $conf['max_stock']) {
                    continue;
                }
            }

            if ($conf['products'] == '' && $conf['categories'] == '' && $conf['manufacturers'] == '' && $conf['suppliers'] == '') {
                $array_configurations_result[] = $conf;
                if ($conf['first_condition']) {
                    break;
                } else {
                    continue;
                }
            }

            $filter_categories = true;
            $filter_products = true;

            if (@unserialize($conf['categories']) !== false) {
                $categories_array = unserialize($conf['categories']);
            } else {
                $categories_array = explode(';', $conf['categories']);
            }

            if ($conf['categories'] !== '' && $conf['products'] == '') {
                foreach ($categories as $category) {
                    if (in_array($category, $categories_array)) {
                        $filter_categories = true;
                        $filter_products = true;
                        break;
                    } else {
                        $filter_categories = false;
                    }
                }
                if (!$filter_categories) {
                    $filter_products = false;
                }
            } else if ($conf['categories'] == '' && $conf['products'] !== '') {
                $products_array = explode(';', $conf['products']);
                if (!in_array($id_product, $products_array)) {
                    $filter_products = false;
                    $filter_categories = true;
                }
            } else if ($conf['categories'] !== '' && $conf['products'] !== '') {
                foreach ($categories as $category) {
                    if (!in_array($category, $categories_array)) {
                        $filter_categories = false;
                    } else {
                        $filter_categories = true;
                        break;
                    }
                }
                if (!$filter_categories) {
                    $products_array = explode(';', $conf['products']);
                    if (!in_array($id_product, $products_array)) {
                        $filter_products = false;
                    } else {
                        $filter_products = true;
                    }
                } else {
                    $products_array = explode(';', $conf['products']);
                    if (!in_array($id_product, $products_array)) {
                        $filter_products = false;
                    }
                }
            }

            $filter_manufacturers = true;
            if ($conf['manufacturers'] !== '') {
                $manufacturers_array = explode(';', $conf['manufacturers']);
                if (!in_array($id_manufacturer, $manufacturers_array)) {
                    $filter_manufacturers = false;
                }
            }

            $filter_suppliers = true;
            if ($conf['suppliers'] !== '') {
                $filter_suppliers = false;
                $suppliers_array = explode(';', $conf['suppliers']);
                if (!empty($product_suppliers_array)) {
                    foreach ($product_suppliers_array as $ps) {
                        if (in_array($ps->id_supplier, $suppliers_array)) {
                            $filter_suppliers = true;
                            break;
                        }
                    }
                }
            }

            if ($filter_categories && $filter_products && $filter_manufacturers && $filter_suppliers) {
                $array_configurations_result[] = $conf;
                if ($conf['first_condition']) {
                    break;
                } else {
                    continue;
                }
            }
        }

        if (count($array_configurations_result) > 0) {
            return $array_configurations_result;
        } else {
            return false;
        }
    }

    public function getProductsExport($conf)
    {
        $productsReturn = array();
        if ($conf->products == '' && $conf->manufacturers == '' && $conf->categories == '' && $conf->suppliers == '' && $conf->attributes && $conf->features) {
            return $this->getProductsLite($id_lang, true, false);
        }

        $categories = false;
        $manufacturers = false;
        $suppliers = false;
        $products = false;

        if ($conf->products != '') {
            $products = implode(',', explode(";", $conf->products));
        } else {
            if ($conf->categories != '') {
                if (@unserialize($conf->categories) !== false) {
                    $categories = implode(',', unserialize($conf->categories));
                } else {
                    $categories = implode(',', explode(';', $conf->categories));
                }
            }

            if ($conf->manufacturers != '') {
                $manufacturers = implode(',', explode(";", $conf->manufacturers));
            }

            if ($conf->suppliers) {
                $suppliers = implode(',', explode(";", $conf->suppliers));
            }
        }

        $prods = GroupincConfiguration::getProductsLiteExport(Context::getContext()->language->id, $categories, $manufacturers, $suppliers, $products, (array)$conf);
        foreach ($prods as $p) {
            $productsReturn[] = $p;
        }
        return $productsReturn;
    }

    public static function getProductsLiteExport($id_lang, $categories = false, $manufacturers = false, $suppliers = false, $products = false, $conf = false)
    {
        $context = Context::getContext();
        $id_shop = $context->shop->id;

        $sql = 'SELECT p.`id_product`, p.`reference`, pl.`name`, p.`price`, p.`price` * (1 + (t.`rate` / 100)) as price_with_taxes, p.`wholesale_price` * (1 + (t.`rate` / 100)) as whole_with_taxes';

        if (isset($conf['filter_stock']) && $conf['filter_stock']) {
            $sql .= ', sa.quantity ';
        }

        if (isset($conf['filter_weight']) && $conf['filter_weight']) {
            $sql .= ', weight ';
        }

        $sql .= ' FROM `'._DB_PREFIX_.'product` p '.Shop::addSqlAssociation('product', 'p').'
                    LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')'.
            ($categories ? ' LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product` ' : '');

        $sql .= ' LEFT JOIN `'._DB_PREFIX_.'tax_rules_group` trg ON p.`id_tax_rules_group` = trg.`id_tax_rules_group`
                LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON trg.`id_tax_rules_group` = tr.`id_tax_rules_group`
                LEFT JOIN `'._DB_PREFIX_.'tax` t ON tr.`id_tax` = tr.`id_tax` ';

        if (isset($conf['filter_stock']) && $conf['filter_stock']) {
            $sql .= ' INNER JOIN `'._DB_PREFIX_.'stock_available` sa ON p.id_product = sa.id_product AND id_product_attribute = 0  AND sa.id_shop = '.$id_shop;
        }

        $sql .= ' WHERE pl.`id_lang` = '.(int)$id_lang.' AND product_shop.`visibility` IN ("both", "catalog") '.
            ($manufacturers ? ' AND p.`id_manufacturer` IN ('.$manufacturers.')' : '').
            ($suppliers ? ' AND p.`id_supplier` IN ('.$suppliers.')' : '').
            ($categories ? ' AND cp.`id_category` IN ('.$categories.')' : '').
            ($products ? ' AND p.`id_product` IN ('.$products.')' : '');

        if (isset($conf['filter_stock']) && $conf['filter_stock']) {
            if ($conf['min_stock'] < 0 && $conf['max_stock'] == 0) {
                $sql .= ' AND ( sa.quantity >= '.$conf['min_stock'].' AND sa.quantity <= '.$conf['max_stock'].' )';
            } else if (($conf['max_stock'] == 0 && $conf['min_stock'] == 0) || ($conf['max_stock'] > 0 && $conf['min_stock'] > 0)) {
                $sql .= ' AND ( sa.quantity >= '.$conf['min_stock'].' AND sa.quantity <= '.$conf['max_stock'].' )';
            } else if ($conf['max_stock'] == 0) {
                $sql .= ' AND sa.quantity >= '.$conf['min_stock'];
            } else {
                $sql .= ' AND sa.quantity <= '.$conf['max_stock'];
            }
        }

        if (isset($conf['filter_weight']) && $conf['filter_weight']) {
            $sql .= ' AND ( p.`weight` >= '.$conf['min_weight'].' AND p.`weight` <= '.$conf['max_weight'].' )';
        }

        if (isset($conf['features']) && $conf['features']) {
            $array_features = Tools::jsonDecode($conf['features'], true);
            //$features = implode(',', array_keys($array_features));
            //$feature_values = implode(',', $array_features);
            //$sql .= ' AND (fp.id_feature_value IN ('.$feature_values.') AND fp.id_feature IN ('.$features.')) ';
            foreach ($array_features as $key => $fv) {
                $sql .= ' AND p.id_product in
                            (SELECT fp.id_product
                            FROM `'._DB_PREFIX_.'feature_product` fp
                            LEFT JOIN `'._DB_PREFIX_.'feature_value` fv ON fv.id_feature_value = fp.id_feature_value
                            WHERE fv.id_feature_value = '.pSQL($fv).' AND fv.id_feature = '.pSQL($key).') ';
            }
        }

        if (isset($conf['attributes']) && $conf['attributes']) {
            $array_attributes = Tools::jsonDecode($conf['attributes'], true);
            foreach ($array_attributes as $key => $av) {
                $sql .= ' AND p.id_product in
                            (SELECT pa.id_product
                            FROM `'._DB_PREFIX_.'attribute_group` ag
                            LEFT JOIN `'._DB_PREFIX_.'attribute` a ON ag.id_attribute_group = a.id_attribute_group
                            LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.id_attribute = a.id_attribute
                            LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON pac.id_product_attribute = pa.id_product_attribute
                            WHERE a.id_attribute = '.pSQL($av).' AND a.id_attribute_group = '.pSQL($key).') ';
            }
        }

        $sql .= ' GROUP BY p.id_product';

        /* price alias with HAVING after the group by */
        if (isset($conf['filter_prices']) && $conf['filter_prices']) {
            if ($conf['threshold_price'] == 0) {
                if (($conf['threshold_max_price'] == 0 && $conf['threshold_min_price'] == 0) || ($conf['threshold_max_price'] > 0 && $conf['threshold_min_price'] > 0)) {
                    $sql .= ' having ( p.`wholesale_price` >= '.$conf['threshold_min_price'].' AND p.`wholesale_price` <= '.$conf['threshold_max_price'].' )';
                } else if ($conf['threshold_max_price'] == 0) {
                    $sql .= ' having p.`wholesale_price` >= '.$conf['threshold_min_price'];
                } else {
                    $sql .= ' having p.`wholesale_price` <= '.$conf['threshold_max_price'];
                }
            }

            if ($conf['threshold_price'] == 1) {
                if (($conf['threshold_max_price'] == 0 && $conf['threshold_min_price'] == 0) || ($conf['threshold_max_price'] > 0 && $conf['threshold_min_price'] > 0)) {
                    $sql .= ' having ( p.`price` >= '.$conf['threshold_min_price'].' AND p.`price` <= '.$conf['threshold_max_price'].' )';
                } else if ($conf['threshold_max_price'] == 0) {
                    $sql .= ' having p.`price` >= '.$conf['threshold_min_price'];
                } else {
                    $sql .= ' having p.`price` <= '.$conf['threshold_max_price'];
                }
            }

            if ($conf['threshold_price'] == 2) {
                if (($conf['threshold_max_price'] == 0 && $conf['threshold_min_price'] == 0) || ($conf['threshold_max_price'] > 0 && $conf['threshold_min_price'] > 0)) {
                    $sql .= ' having ( whole_with_taxes >= '.$conf['threshold_min_price'].' AND whole_with_taxes <= '.$conf['threshold_max_price'].' )';
                } else if ($conf['threshold_max_price'] == 0) {
                    $sql .= ' having whole_with_taxes >= '.$conf['threshold_min_price'];
                } else {
                    $sql .= ' having whole_with_taxes <= '.$conf['threshold_max_price'];
                }
            }

            if ($conf['threshold_price'] == 3) {
                if (($conf['threshold_max_price'] == 0 && $conf['threshold_min_price'] == 0) || ($conf['threshold_max_price'] > 0 && $conf['threshold_min_price'] > 0)) {
                    $sql .= ' having ( price_with_taxes >= '.$conf['threshold_min_price'].' AND price_with_taxes <= '.$conf['threshold_max_price'].' )';
                } else if ($conf['threshold_max_price'] == 0) {
                    $sql .= ' having price_with_taxes >= '.$conf['threshold_min_price'];
                } else {
                    $sql .= ' having price_with_taxes <= '.$conf['threshold_max_price'];
                }
            }
        }

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, true);

        foreach ($rq as &$r) {
            $r['type'] = $conf['type'];
            $r['mode'] = $conf['mode'];

            if ($conf['type'] == 1) {
                $amountExport = $conf['percentage'] . '%';
            } else {
                $amountExport = $conf['fix'];
            }
            $r['amount'] = $amountExport;

            $giconfig = new GroupincConfiguration($conf['id_groupinc_configuration']);
            $price_changed = GroupincConfiguration::getPriceIncremented($giconfig, $r['price_with_taxes'], $r['price_with_taxes'], $r['price'], $r['price'], $r['price'], $r['price'], $r['price']);

            $r['new_price'] = $price_changed ;
        }
        return ($rq);
    }

    protected static function getManufacturerbyIdProduct($id_product = 0)
    {
        if ($id_product) {
            $query = 'SELECT id_manufacturer FROM `'._DB_PREFIX_.'product` WHERE id_product = '.$id_product;
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        }
        return false;
    }

    protected static function getAttributePrice($id_product = 0, $id_product_attribute = 0)
    {
        $result = array();
        if ($id_product_attribute) {
            $id_shop = Context::getContext()->shop->id;

            $sql = new DbQuery();
            $sql->select('product_shop.`price`');
            $sql->from('product', 'p');
            $sql->innerJoin('product_shop', 'product_shop', '(product_shop.id_product=p.id_product AND product_shop.id_shop = ' . (int) $id_shop . ')');
            $sql->where('p.`id_product` = ' . (int) $id_product);
            if (Combination::isFeatureActive()) {
                $sql->select('IFNULL(product_attribute_shop.id_product_attribute,0) id_product_attribute, product_attribute_shop.`price` AS attribute_price, product_attribute_shop.default_on');
                $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.id_product = p.id_product AND product_attribute_shop.id_shop = ' . (int) $id_shop . ' AND product_attribute_shop.id_product_attribute = ' . (int) $id_product_attribute . ')');
            } else {
                $sql->select('0 as id_product_attribute');
            }

            $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (is_array($res) && count($res)) {
                foreach ($res as $row) {
                    $result = array(
                        'price' => $row['price'],
                        'attribute_price' => (isset($row['attribute_price']) ? $row['attribute_price'] : null),
                    );
                }
            }
        }
        return $result;
    }

    public static function getQuantityProductInCart($id_cart, $id_product = 0, $id_product_attribute = 0)
    {
        if ($id_product && $id_product_attribute) {
            $sql =
                'SELECT cp.`quantity` FROM `'._DB_PREFIX_.'cart_product` cp LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product` WHERE id_cart = '.(int)$id_cart.' AND cp.`id_product` = '.$id_product.' AND cp.`id_product_attribute` = '.$id_product_attribute;
            return Db::getInstance()->getRow($sql);
        } else {
            $sql =
                'SELECT cp.`quantity` FROM `'._DB_PREFIX_.'cart_product` cp LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product` WHERE id_cart = '.(int)$id_cart.' AND cp.`id_product` = '.$id_product;
            return Db::getInstance()->getRow($sql);
        }
        return 0;
    }

    protected static function getGroupReduction($price = 0, $id_product = 0, $id_group = 0)
    {
        $reduction_from_category = GroupReduction::getValueForProduct($id_product, $id_group);
        if ($reduction_from_category !== false) {
            $group_reduction = $price * (float)$reduction_from_category;
        } else { // apply group reduction if there is no group reduction for this category
            $group_reduction = (($reduc = Group::getReductionByIdGroup($id_group)) != 0) ? ($price * $reduc / 100) : 0;
        }
        return $group_reduction;
    }
}