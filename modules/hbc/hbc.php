<?php
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA PL MILOSZ MYSZCZUK VATEU: PL9730945634
 * @copyright 2010-2024 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

class hbc extends Module
{
    public $searchTool;

    function __construct()
    {
        ini_set("display_errors", 0);
        error_reporting(0);
        $this->name = 'hbc';
        $this->tab = 'checkout';
        $this->author = 'MyPresta.eu';
        $this->mypresta_link = 'https://mypresta.eu/modules/front-office-features/hide-products-by-country.html';
        $this->version = '1.5.3';
        $this->module_key = 'f6f563ebd90dfbc524d6eb4b9464ebc6';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Hide products by country');
        $this->description = $this->l('Module allows to hide products you sell depending on customer origin (country)');
        $this->checkforupdates();
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        //for updates purposes only
        //to show notifications
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 16 //
        // ---------- //
        // ---------- //
        $this->mkey = "nlc";
        if (@file_exists('../modules/' . $this->name . '/key.php')) {
            @require_once('../modules/' . $this->name . '/key.php');
        } else {
            if (@file_exists(dirname(__FILE__) . $this->name . '/key.php')) {
                @require_once(dirname(__FILE__) . $this->name . '/key.php');
            } else {
                if (@file_exists('modules/' . $this->name . '/key.php')) {
                    @require_once('modules/' . $this->name . '/key.php');
                }
            }
        }
        if ($form == 1) {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_module_block_settings">
                         ' . ($this->psversion() == 5 ? '<legend style="">' . $this->l('MyPresta updates') . '</legend>' : '') . '
                        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                            <label>' . $this->l('Check updates') . '</label>
                            <div class="margin-form">' . (Tools::isSubmit('submit_settings_updates_now') ? ($this->inconsistency(0) ? '' : '') . $this->checkforupdates(1) : '') . '
                                <button style="margin: 0px; top: -3px; position: relative;" type="submit" name="submit_settings_updates_now" class="button btn btn-default" />
                                <i class="process-icon-update"></i>
                                ' . $this->l('Check now') . '
                                </button>
                            </div>
                            <label>' . $this->l('Updates notifications') . '</label>
                            <div class="margin-form">
                                <select name="mypresta_updates">
                                    <option value="-">' . $this->l('-- select --') . '</option>
                                    <option value="1" ' . ((int)(Configuration::get('mypresta_updates') == 1) ? 'selected="selected"' : '') . '>' . $this->l('Enable') . '</option>
                                    <option value="0" ' . ((int)(Configuration::get('mypresta_updates') == 0) ? 'selected="selected"' : '') . '>' . $this->l('Disable') . '</option>
                                </select>
                                <p class="clear">' . $this->l('Turn this option on if you want to check MyPresta.eu for module updates automatically. This option will display notification about new versions of this addon.') . '</p>
                            </div>
                            <label>' . $this->l('Module page') . '</label>
                            <div class="margin-form">
                                <a style="font-size:14px;" href="' . $this->mypresta_link . '" target="_blank">' . $this->displayName . '</a>
                                <p class="clear">' . $this->l('This is direct link to official addon page, where you can read about changes in the module (changelog)') . '</p>
                            </div>
                            <div class="panel-footer">
                                <button type="submit" name="submit_settings_updates"class="button btn btn-default pull-right" />
                                <i class="process-icon-save"></i>
                                ' . $this->l('Save') . '
                                </button>
                            </div>
                        </form>
                    </fieldset>
                    <style>
                    #fieldset_myprestaupdates {
                        display:block;clear:both;
                        float:inherit!important;
                    }
                    </style>
                </div>
            </div>
            </div>';
        } else {
            if (defined('_PS_ADMIN_DIR_')) {
                if (Tools::isSubmit('submit_settings_updates')) {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') != false) {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = hbcUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (hbcUpdate::version($this->version) < hbcUpdate::version(Configuration::get('updatev_' . $this->name)) && Tools::getValue('ajax', 'false') == 'false') {
                        $this->context->controller->warnings[] = '<strong>' . $this->displayName . '</strong>: ' . $this->l('New version available, check http://MyPresta.eu for more informations') . ' <a href="' . $this->mypresta_link . '">' . $this->l('More details in changelog') . '</a>';
                        $this->warning = $this->context->controller->warnings[0];
                    }
                } else {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = hbcUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                }
                if ($display_msg == 1) {
                    if (hbcUpdate::version($this->version) < hbcUpdate::version(hbcUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version))) {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    } else {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }

    public function inconsistency($var)
    {
        return;
    }

    public static function psversion($part = 1)
    {

        $version = _PS_VERSION_;
        $exp = $explode = explode(".", $version);
        if ($part == 1) {
            return $exp[1];
        }
        if ($part == 2) {
            return $exp[2];
        }
        if ($part == 3) {
            return $exp[3];
        }
    }

    private function installdb()
    {
        $prefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;
        $statements = array();
        $statements[] = "CREATE TABLE IF NOT EXISTS `${prefix}hbc` (" . '`id_hbc` int(10) NOT NULL AUTO_INCREMENT,' . '`id_product` int(10),' . '`country` int(5),' . '`id_shop` INT(4),' . 'PRIMARY KEY (`id_hbc`)' . ")";
        foreach ($statements as $statement) {
            if (!Db:: getInstance()->Execute($statement)) {
                return false;
            }
        }
        return true;
    }

    public function runStatement($statement)
    {

        if (@!Db:: getInstance()->Execute($statement)) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {

        }
        return true;
    }

    public function install()
    {
        if (parent::install() == false OR
            !Configuration::updateValue('update_' . $this->name, '0') OR
            !$this->registerHook('FilterProductSearch') OR
            !$this->registerHook('displayAdminProductsExtra') OR
            !$this->registerHook('actionProductUpdate') OR
            !$this->registerHook('actionDispatcherAfter') OR
            !$this->registerHook('displayHeader') OR
            !$this->registerHook('actionCartSave') OR
            !$this->installdb()) {
            return false;
        }
        return true;
    }

    public static function getRestrictions($id_product)
    {
        return Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'hbc` WHERE id_product = ' . (int)$id_product . ' AND id_shop=' . Context::getContext()->shop->id);
    }

    public function getRestrictionsByProductAndCountry($id_product, $id_country)
    {
        return Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'hbc` WHERE id_product = ' . (int)$id_product . ' AND id_shop=' . Context::getContext()->shop->id . ' AND country=' . $id_country);
    }

    public static function returnUserCountry()
    {
        $_SERVER['REMOTE_ADDR'] = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);

        $groups_array = explode(',', Configuration::get('HBC_EXCLUDE_GROUP'));
        if (is_array($groups_array)) {
            if (count($groups_array) > 0) {
                foreach (self::getCustomerGroups() AS $g => $k) {
                    if (in_array($g, $groups_array)) {
                        return false;
                    }
                }
            }
        }

        if (isset(Context::getContext()->customer->id)) {
            if (is_int(Context::getContext()->customer->id)) {
                if (Context::getContext()->customer->id > 0) {
                    $customers_array = explode(',', Configuration::get('HBC_EXCLUDE_CUSTOMER'));
                    if (is_array($customers_array)) {
                        if (count($customers_array) > 0) {
                            if (in_array(Context::getContext()->customer->id, $customers_array)) {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        if (in_array(Tools::getRemoteAddr(), explode(";",Configuration::get('PS_GEOLOCATION_WHITELIST')))) {
            return false;
        }

        if ((!in_array(Tools::getRemoteAddr(), array('localhost', '127.0.0.1')) && !in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))) || Configuration::get('HBC_SIMULATE_ON') == true) {
            /* Check if Maxmind Database exists */
            if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_)) {
                $reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_);
                try {
                    $ip = Configuration::get('HBC_SIMULATE_IP');
                    $record = $reader->city((Configuration::get('HBC_SIMULATE_ON') ? (filter_var($ip, FILTER_VALIDATE_IP) ? $ip : Tools::getRemoteAddr()) : Tools::getRemoteAddr()));
                } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                    $record = null;
                }

                if (isset($record->country->isoCode)) {
                    return $record->country->isoCode;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function hookactionDispatcherAfter($params)
    {

        if (!defined('_PS_ADMIN_DIR_')) {
            //if (Tools::getValue('action') == 'update' && Tools::getValue('add') == 1 && Tools::getValue('id_product', 'false') != 'false') {
            $restriction2 = false;
            $cart_check = 0;
            if (Configuration::get('HBC_DELIVERY') == 1) {
                $cart_check = 1;
                if (isset(Context::getContext()->cart->id_address_delivery)) {
                    if (Context::getContext()->cart->id_address_delivery) {
                        $address = new Address(Context::getContext()->cart->id_address_delivery);
                        if (isset($address->id_country)) {
                            $country_address = new Country($address->id_country);
                            $cart_country = $country_address->iso_code;
                        }
                    }
                }
            }



            Foreach (Context::getContext()->cart->getProducts() AS $product) {
                $country = self::returnUserCountry();
                if ($country != false) {
                    $restriction = $this->getRestrictionsByProductAndCountry($product['id_product'], Country::getByIso($country));
                }

                if ($cart_check == 1 && $cart_country != null) {
                    $restriction2 = $this->getRestrictionsByProductAndCountry($product['id_product'], Country::getByIso($cart_country));
                }

                if ($restriction != false || $restriction2 != false) {
                    Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'cart_product` WHERE `id_cart` = ' . (int)Context::getContext()->cart->id . ' AND `id_product` = ' . (int)$product['id_product']);
                }

            }
        }
        //}
    }

    public function hookactionCartSave($params)
    {
        //if (Tools::getValue('action') == 'update' && Tools::getValue('add') == 1 && Tools::getValue('id_product', 'false') != 'false') {
        $restriction2 = false;
        $cart_check = 0;
        if (Configuration::get('HBC_DELIVERY') == 1) {
            $cart_check = 1;
            if (isset(Context::getContext()->cart->id_address_delivery)) {
                if (Context::getContext()->cart->id_address_delivery) {
                    $address = new Address(Context::getContext()->cart->id_address_delivery);
                    if (isset($address->id_country)) {
                        $country_address = new Country($address->id_country);
                        $cart_country = $country_address->iso_code;
                    }
                }
            }
        }
        $country = self::returnUserCountry();
        if ($country != false) {
            $restriction = $this->getRestrictionsByProductAndCountry(Tools::getValue('id_product'), Country::getByIso($country));
            if ($cart_check == 1 && $cart_country != NULL) {
                $restriction2 = $this->getRestrictionsByProductAndCountry(Tools::getValue('id_product'), Country::getByIso($cart_country));
            }
            if ($restriction != false || $restriction2 != false) {
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'cart_product` WHERE `id_cart` = ' . (int)$params['cart']->id . ' AND `id_product` = ' . (int)Tools::getValue('id_product'));
            }
        }
        //}
    }

    public function hookactionProductUpdate($params)
    {
        //$this->hookactionUpdateQuantity($params);
        $_GET['id_product'] = $params['id_product'];
        if (Tools::isSubmit('selectedCountriesHbc')) {
            $to_block = array();
            foreach (Country::getCountries(Context::getContext()->language->id) AS $country) {
                foreach (Tools::getValue('selectedCountriesHbc') AS $hbc) {
                    if ($hbc == $country['id_country']) {
                        $to_block[$hbc] = true;
                    }
                }
            }

            if (count($to_block) > 0) {
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'hbc` WHERE id_product = ' . $_GET['id_product'] . ' AND id_shop=' . $this->context->shop->id);
                foreach ($to_block AS $gr_id => $gr_id_val) {
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'hbc` (`id_product`, `country`, `id_shop`) VALUES("' . (int)$_GET['id_product'] . '", "' . $gr_id . '", "' . $this->context->shop->id . '")');
                }
            }
        } elseif (Tools::getValue('actionhbc') == 'savehbc' && !Tools::isSubmit('selectedCountriesHbc')) {
            Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'hbc` WHERE id_product = ' . $_GET['id_product'] . ' AND id_shop=' . $this->context->shop->id);
        }
    }

    public function hookdisplayAdminProductsExtra($params)
    {
        $restrictions = $this->getRestrictions($params['id_product']);
        $to_block_tpl = array();
        foreach ($restrictions AS $kres => $vres) {
            $to_block_tpl[$vres['country']] = true;
        }
        $this->context->smarty->assign('restrictions', $to_block_tpl);

        return $this->display(__FILE__, 'views/templates/admin/AdminTab.tpl');
    }

    public function hookdisplayHeader($params)
    {
        $restriction2 = false;
        if (Tools::getValue('controller') == "product") {
            $country = self::returnUserCountry();
            $cart_check = 0;
            if (Configuration::get('HBC_DELIVERY') == 1) {
                $cart_check = 1;
                if (isset(Context::getContext()->cart->id_address_delivery)) {
                    if (Context::getContext()->cart->id_address_delivery) {
                        $address = new Address(Context::getContext()->cart->id_address_delivery);
                        if (isset($address->id_country)) {
                            $country_address = new Country($address->id_country);
                            $cart_country = $country_address->iso_code;
                        }
                    }
                }
            }

            if ($country != false) {
                if (Validate::isLanguageIsoCode($country)) {
                    $restriction = $this->getRestrictionsByProductAndCountry(Tools::getValue('id_product'), Country::getByIso($country));
                } else {
                    $restriction = false;
                }

                if ($cart_check == 1 && Validate::isLanguageIsoCode($cart_country)) {
                    $restriction2 = $this->getRestrictionsByProductAndCountry(Tools::getValue('id_product'), Country::getByIso($cart_country));
                } else {
                    $restriction2 = false;
                }

                if ($restriction != false || $restriction2 != false) {
                    Tools::redirect(Context::getContext()->link->getPageLink('index'));
                }
            }
        }
        $hbc_country = hbc::returnUserCountry();
        $this->context->smarty->assign('hbc_country', $hbc_country);
        if (Configuration::get('HBC_DELIVERY') == 1) {
            $cart_check = 1;
            if (isset(Context::getContext()->cart->id_address_delivery)) {
                if (Context::getContext()->cart->id_address_delivery) {
                    $address = new Address(Context::getContext()->cart->id_address_delivery);
                    if (isset($address->id_country)) {
                        $country_address = new Country($address->id_country);
                        $cart_country = $country_address->iso_code;
                        $this->context->smarty->assign('hbc_cart_country', $cart_country);

                    }
                }
            }
        }
        if (Configuration::get('HBC_SIMULATE_ON') == true) {
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/hook/displayHeader.tpl');
        }
    }

    public function hookFilterProductSearch($params)
    {
        $country = self::returnUserCountry();
        $cart_check = 0;
        if (Configuration::get('HBC_DELIVERY') == 1) {
            $cart_check = 1;
            if (isset(Context::getContext()->cart->id_address_delivery)) {
                if (Context::getContext()->cart->id_address_delivery) {
                    $address = new Address(Context::getContext()->cart->id_address_delivery);
                    if (isset($address->id_country)) {
                        $country_address = new Country($address->id_country);
                        $cart_country = Country::getByIso($country_address->iso_code);
                    }
                }
            }
        }
        if ($country != false) {
            $country_by_iso = Country::getByIso($country);
            foreach ($params['searchVariables']['products'] AS $pkey => $pvalue) {
                if (Module::isInstalled('hbc') && !defined('_PS_ADMIN_DIR_') && isset($params['searchVariables']['products'][$pkey]->id_product)) {
                    $restriction = $this->getRestrictionsByProductAndCountry($params['searchVariables']['products'][$pkey]->id_product, $country_by_iso);
                    if ($restriction != false) {
                        unset($params['searchVariables']['products'][$pkey]);
                    }
                    if ($cart_check == 1) {
                        $restriction = $this->getRestrictionsByProductAndCountry($params['searchVariables']['products'][$pkey]->id_product, $cart_country);
                        if ($restriction != false) {
                            unset($params['searchVariables']['products'][$pkey]);
                        }
                    }
                }
            }
        }
        return;
    }

    public function getContent()
    {
        $this->searchTool = new searchToolhbc($this->name, $this->tab);

        if (in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))) {
            $this->context->controller->errors[] = $this->l('You are on localhost, geolocation identifies your country only if your website is on-line.');
        }

        if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_) == false) {
            $this->context->controller->errors[] = $this->l('Module to identify customer country uses geolocation.') . ' ' . $this->l('In order to use Geolocation, please download') . ' ' . '<a href="https://mypresta.eu/prestashop-17/geolite2-city-geolocation-download.html">' . $this->l('this file') . '</a> ' . $this->l('and extract it (using Winrar or Gzip) into the /app/Resources/geoip/ directory.');
        }

        $this->context->smarty->assign(array(
            'form_settings' => $this->displayForm(),
            'form_exclusions' => $this->generateExclusionsForm(),
            'form_mass' => $this->generateFormMassHbc(),
            'form_updates' => $this->checkforupdates(0, 1)
        ));

        return $this->searchTool->initTool() . $this->_postProcess() . $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'hbc/views/templates/admin/configuration.tpl');
    }

    public static function getCustomerGroups()
    {
        $customer_groups = array();
        if (isset(Context::getContext()->cart->id_customer)) {
            if (Context::getContext()->cart->id_customer == 0) {
                $customer_groups[1] = 1;
            } else {
                foreach (Customer::getGroupsStatic(Context::getContext()->cart->id_customer) as $group) {
                    $customer_groups[$group] = 1;
                }
            }
        } elseif (Context::getContext()->customer->is_guest == 1) {
            $customer_groups[1] = 2;
        } else {
            $customer_groups[1] = 1;
        }
        if (count($customer_groups) > 0) {
            return $customer_groups;
        } else {
            return false;
        }
    }

    public function generateExclusionsForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Exclude groups of customers or customers from country verification. These users will see all products'),
                    'icon' => 'icon-wrench',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Exclude groups of customers'),
                        'name' => 'HBC_EXCLUDE_GROUP',
                        'desc' => $this->l('Search for customer groups then select it from suggestions list') . $this->searchTool->searchTool('group', 'HBC_EXCLUDE_GROUP', '', true, Configuration::get('HBC_EXCLUDE_GROUP')),
                        'prefix' => $this->searchTool->searchTool('group', 'HBC_EXCLUDE_GROUP', ''),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Exclude customers'),
                        'name' => 'HBC_EXCLUDE_CUSTOMER',
                        'desc' => $this->l('Search for customer then select it from suggestions list') . $this->searchTool->searchTool('customer', 'HBC_EXCLUDE_CUSTOMER', '', true, Configuration::get('HBC_EXCLUDE_CUSTOMER')),
                        'prefix' => $this->searchTool->searchTool('customer', 'HBC_EXCLUDE_CUSTOMER', ''),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = 'hbcIDExcusions';
        $helper->identifier = 'hbcExcusions';
        $helper->submit_action = 'btnSubmitExcusions';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }


    public function generateFormMassHbc()
    {

        $root = Category::getRootCategory();
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $tree = new HelperTreeCategories('hbc-categories-tree', $this->l('Categories'));
            $tree->setRootCategory($root->id);
            $tree->setUseCheckBox(true);
            $tree->setUseSearch(true);
            $category_tree = $tree->render();
        } else {
            $tree = new Helper();
            $category_tree = $tree->renderCategoryTree(null, array(), 'categoryBox[]', true, true, array(), false, false);
        }

        $options_wtd = array(
            array(
                'id_option' => '1',
                'name' => $this->l('Disable product for selected country')
            ),
            array(
                'id_option' => '2',
                'name' => $this->l('Activate product for selected country')
            ),
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Mass define product\'s visibility settings'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    array(
                        'type' => 'html',
                        'label' => $this->l('Select categories'),
                        'name' => 'HBC_CATEGORIES',
                        'html_content' => $category_tree . $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'hbc/views/templates/admin/button-subcategories.tpl')
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select Manufacturer'),
                        'name' => 'HBC_MANUFACT',
                        'desc' => $this->l('You can define limits for products associated with selected manufacturers.'),
                        'options' => array(
                            'query' => array_merge(array(array('id_manufacturer' => 0, 'name' => $this->l('All manufacturers'))), Manufacturer::getManufacturers(false, $this->context->language->id, true, false, false, false, 'id_manufacturer')),
                            'id' => 'id_manufacturer',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('What you want to do?'),
                        'name' => 'HBC_WTD',
                        'desc' => $this->l('Select one action that you want to with products from selected categories/brands'),
                        'options' => array(
                            'query' => $options_wtd,
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select countries'),
                        'name' => 'HBC_COUNTRY',
                        'multiple' => true,
                        'desc' => $this->l('Select countries where you want to hide selected products'),
                        'options' => array(
                            'query' => Country::getCountries($this->context->language->id, false),
                            'id' => 'id_country',
                            'name' => 'name'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = 'HBC_SETTINGS';
        $helper->identifier = 'identifier_HBC_MASS';
        $helper->submit_action = 'submit_HBC_MASS';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return '<div class="alert alert-info">' . $this->l('Settings defined here for many products are available also on product edit pages') . '</div>' . $helper->generateForm(array($fields_form));
    }


    public function displayForm()
    {
        $options_delivery = array(
            array(
                'id_option' => '0',
                'name' => $this->l('No')
            ),
            array(
                'id_option' => '1',
                'name' => $this->l('Yes - module will check also delivery address of customer and depending on it - hide or show product')
            ),
        );

        $options_priority= array(
            array(
                'id_option' => '0',
                'name' => $this->l('Do not set priority, use both methods')
            ),
            array(
                'id_option' => '1',
                'name' => $this->l('IP number first')
            ),
            array(
                'id_option' => '2',
                'name' => $this->l('Delivery address first')
            ),
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
                        'class' => 't',
                        'label' => $this->l('Simulate visit'),
                        'name' => 'HBC_SIMULATE_ON',
                        'values' => array(
                            array(
                                'id' => 'FHBC_SIMULATE_ON_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'HBC_SIMULATE_ON_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Simulate IP'),
                        'name' => 'HBC_SIMULATE_IP',
                        'desc' => $this->l('If you enabled option to simulate visit - insert here the example of IP address that module will use for geolocation purposes'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Use delivery address'),
                        'name' => 'HBC_DELIVERY',
                        'desc' => $this->l('Module will check delivery address of customer in addition to identification by IP address.'),
                        'options' => array(
                            'query' => $options_delivery,
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Set priority of origin identification methods'),
                        'name' => 'HBC_PRIORITY',
                        'desc' => $this->l('If you will use both identification method of visitor\'s country (IP and delivery address) you can decide which one will be more important to decide about visitor\'s origin. This option is available here because there is a chance that customer location by IP may be different than location used for delivery purposes during checkout'),
                        'options' => array(
                            'query' => $options_priority,
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
                        'desc' => $this->l('Whitelisted IP addresses will see all products. List of whitelisted IP addresses are available to manage in international > localization > geolocation section'),
                        'class' => 't',
                        'label' => $this->l('Activate whitelist'),
                        'name' => 'HBC_WHITELIST_ON',
                        'values' => array(
                            array(
                                'id' => 'FHBC_WHITELIST_ON',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'HBC_WHITELIST_ON_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = 'hbcID';
        $helper->identifier = 'hbc';
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));

    }

    public function getConfigFieldsValues()
    {
        return array(
            'HBC_WHITELIST_ON' => Tools::getValue('HBC_WHITELIST_ON', Configuration::get('HBC_WHITELIST_ON')),
            'HBC_SIMULATE_IP' => Tools::getValue('HBC_SIMULATE_IP', Configuration::get('HBC_SIMULATE_IP')),
            'HBC_SIMULATE_ON' => Tools::getValue('HBC_SIMULATE_ON', Configuration::get('HBC_SIMULATE_ON')),
            'HBC_COUNTRY' => Tools::getValue('HBC_COUNTRY', Configuration::get('HBC_COUNTRY')),
            'HBC_WTD' => Tools::getValue('HBC_WTD', Configuration::get('HBC_WTD')),
            'HBC_MANUFACT' => Tools::getValue('HBC_MANUFACT', Configuration::get('HBC_MANUFACT')),
            'HBC_CATEGORIES' => Tools::getValue('HBC_CATEGORIES', Configuration::get('HBC_CATEGORIES')),
            'HBC_EXCLUDE_CUSTOMER' => Tools::getValue('HBC_EXCLUDE_CUSTOMER', Configuration::get('HBC_EXCLUDE_CUSTOMER')),
            'HBC_EXCLUDE_GROUP' => Tools::getValue('HBC_EXCLUDE_GROUP', Configuration::get('HBC_EXCLUDE_GROUP')),
            'HBC_DELIVERY' => Tools::getValue('HBC_DELIVERY', Configuration::get('HBC_DELIVERY')),
            'HBC_PRIORITY' => Tools::getValue('HBC_PRIORITY', Configuration::get('HBC_PRIORITY')),
        );
    }

    private function _postProcess()
    {

        if (Tools::isSubmit('submit_HBC_MASS')) {

            $where_manufacturer = '';
            $join_manufacturer = '';
            if (Tools::getValue('HBC_MANUFACT', 0) != 0) {
                $join_manufacturer = ' INNER JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = cp.id_product';
                $where_manufacturer = ' AND p.id_manufacturer = "' . (int)Tools::getValue('HBC_MANUFACT') . '"';
            }

            $products_array = array();
            if (is_array(Tools::getValue('categoryBox'))) {
                foreach (Tools::getValue('categoryBox') AS $catid) {
                    $products = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'category_product`  cp' . $join_manufacturer . ' WHERE cp.id_category=' . (int)$catid . ' ' . $where_manufacturer);
                    if ($products) {
                        foreach ($products AS $product) {
                            $products_array[$product['id_product']] = $product['id_product'];
                        }
                    }
                }
            } elseif (Tools::getValue('HBC_MANUFACT', 0) != 0) {
                $products = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'product` p WHERE 1=1 ' . $where_manufacturer);
                if ($products) {
                    foreach ($products AS $product) {
                        $products_array[$product['id_product']] = $product['id_product'];
                    }
                }
            }

            if (is_array($products_array)) {
                if (count($products_array) <= 0) {
                    $this->context->controller->warnings[] = $this->l('No products selected. You must configure filters - select category and/or manufacturer');
                    return;
                }
            }

            if (is_array(Tools::getValue('HBC_COUNTRY'))) {
                if (count(Tools::getValue('HBC_COUNTRY')) <= 0) {
                    $this->context->controller->warnings[] = $this->l('No country selected. To disable or enable product in some countries you need to select at least one country');
                    return;
                }
            } else {
                $this->context->controller->warnings[] = $this->l('No country selected. To disable or enable product in some countries you need to select at least one country');
            }
            if (Tools::getValue('HBC_WTD') == 1) {
                foreach (Tools::getValue('HBC_COUNTRY') AS $country => $id_country) {
                    foreach ($products_array AS $product) {
                        if (Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'hbc` (`id_product`, `country`, `id_shop`) VALUES (' . (int)$product . ', ' . (int)$id_country . ', ' . $this->context->shop->id . ')')) {
                            $this->context->controller->informations[] = $this->l('Product #') . $product . ' ' . $this->l('disabled for country #') . $id_country;
                        }
                    }
                }
            } elseif (Tools::getValue('HBC_WTD') == 2) {
                foreach (Tools::getValue('HBC_COUNTRY') AS $country => $id_country) {
                    foreach ($products_array AS $product) {
                        if (Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'hbc` WHERE `id_product` = ' . $product . ' AND `country`=' . $id_country . ' AND `id_shop`=' . $this->context->shop->id . ' ')) {
                            $this->context->controller->informations[] = $this->l('Product #') . $product . ' ' . $this->l('activated for country #') . $id_country;
                        }
                    }
                }
            }

        }

        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('HBC_WHITELIST_ON', Tools::getValue('HBC_WHITELIST_ON'));
            Configuration::updateValue('HBC_PRIORITY', Tools::getValue('HBC_PRIORITY'));
            Configuration::updateValue('HBC_DELIVERY', Tools::getValue('HBC_DELIVERY'));
            Configuration::updateValue('HBC_SIMULATE_IP', Tools::getValue('HBC_SIMULATE_IP'));
            Configuration::updateValue('HBC_SIMULATE_ON', Tools::getValue('HBC_SIMULATE_ON'));
            return $this->displayConfirmation($this->l('Settings updated'));
        }

        if (Tools::isSubmit('btnSubmitExcusions')) {
            Configuration::updateValue('HBC_EXCLUDE_GROUP', Tools::getValue('HBC_EXCLUDE_GROUP'));
            Configuration::updateValue('HBC_EXCLUDE_CUSTOMER', Tools::getValue('HBC_EXCLUDE_CUSTOMER'));
            return $this->displayConfirmation($this->l('Settings updated'));
        }

    }


}

class hbcUpdate extends hbc
{
    public static function version($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 3) {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 2) {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 1) {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 0) {
            $version = (int)$version . "0000";
        }
        return (int)$version;
    }

    public static function encrypt($string)
    {
        return base64_encode($string);
    }

    public static function verify($module, $key, $version)
    {
        if (ini_get("allow_url_fopen")) {
            if (function_exists("file_get_contents")) {
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module=' . $module . "&version=" . self::encrypt($version) . "&lic=$key&u=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__));
            }
        }
        Configuration::updateValue("update_" . $module, date("U"));
        Configuration::updateValue("updatev_" . $module, $actual_version);
        return $actual_version;
    }
}

if (file_exists(_PS_MODULE_DIR_ . 'hbc/lib/searchTool/searchTool.php')) {
    require_once _PS_MODULE_DIR_ . 'hbc/lib/searchTool/searchTool.php';
}

?>