<?php
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2021 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

require_once _PS_MODULE_DIR_ . 'pbc/models/pricebc.php';

class pbc extends Module
{
    function __construct()
    {
        $this->name = 'pbc';
        $this->tab = 'administratior';
        $this->author = 'MyPresta.eu';
        $this->version = '1.5.4';
        $this->mypresta_link = 'https://mypresta.eu/modules/ordering-process/increase-prices-by-country.html';
        $this->module_key = '1abb6178d31e1832f0a64e1b4e3bc62a';
        parent::__construct();
        $this->bootstrap = true;
        $this->displayName = $this->l('Increase / decrease price by country');
        $this->description = $this->l('Module allows to increase / decrease product prices by country based on geolocation (ip identification)');
        $this->checkforupdates(0, 0);
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
                        $actual_version = pbcUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (pbcUpdate::version($this->version) < pbcUpdate::version(Configuration::get('updatev_' . $this->name)) && Tools::getValue('ajax', 'false') == 'false') {
                        $this->context->controller->warnings[] = '<strong>' . $this->displayName . '</strong>: ' . $this->l('New version available, check http://MyPresta.eu for more informations') . ' <a href="' . $this->mypresta_link . '">' . $this->l('More details in changelog') . '</a>';
                        $this->warning = $this->context->controller->warnings[0];
                    }
                } else {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = pbcUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                }
                if ($display_msg == 1) {
                    if (pbcUpdate::version($this->version) < pbcUpdate::version(pbcUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version))) {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    } else {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }

    function install()
    {
        if (!parent::install()
            || !$this->installdb()
            || !$this->createMenu()
            || !$this->registerHook('displayHeader')
        ) {
            return false;
        }

        return true;
    }
	public static function botDetected() {
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
			$googleBotsPatterns = [
				'googlebot',      // Googlebot standard
				'adsbot-google',  // Google AdWords
				'apis-google',    // Google APIs
				'mediapartners-google',  // Google AdSense
				'feedfetcher-google',    // Google Feedfetcher
				'google web preview',    // Google Instant Previews
				'google-read-aloud',     // Google Read Aloud
				'duplexweb-google',      // Google Duplex
				'google favicon',        // Google Favicon
				'google',                // Motif générique pour inclure d'autres bots Google
			];

			foreach ($googleBotsPatterns as $pattern) {
				if (strpos($userAgent, $pattern) !== false) {
					return true;  // C'est un bot de Google
				}
			}
		}

		return false; // Ce n'est pas un bot de Google
	}


    public function hookdisplayHeader($params)
    {
		if (self::botDetected()) {
			return; // Arrête l'exécution pour les bots
		}
			$pbc_country = pbc::returnUserCountry();
        $this->context->smarty->assign('pbc_country', $pbc_country);
        $this->context->smarty->assign('pbc_cart_country', $pbc_country);
        if (Configuration::get('PBC_SIMULATE_ON') == true && $pbc_country != false) {
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/displayHeader.tpl');
        }
    }

    private function InDelMenu($what, $controller, $name = null)
    {
        if ($what == 'install') {
            $tab = new Tab();
            $tab->class_name = $controller;
            $tab->id_parent = Tab::getIdFromClassName('AdminCatalog');
            $tab->module = $this->name;
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $tab->name[$lang['id_lang']] = $name;
            }
            if ($tab->save()) {
                return true;
            }
        } elseif ($what == 'uninstall') {
            $tab = new Tab(Tab::getIdFromClassName($controller));
            if ($tab->delete()) {
                return true;
            }
        }

        return true;
    }

    public function createMenu()
    {
        $this->InDelMenu('install', 'AdminPbcList', $this->l('Price by country'));
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        $this->InDelMenu('uninstall', 'AdminPbcListController', $this->l('Price by country'));

        return true;
    }

    private function installdb()
    {
        $prefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;
        $statements = array();
        $statements[] = "CREATE TABLE IF NOT EXISTS `${prefix}pbc` " .
            "(" .
            '`id_pbc` int(10) NOT NULL AUTO_INCREMENT,' .
            '`id_country` int(10) NOT NULL,' .
            '`id_shop` int(10) NOT NULL DEFAULT 1,' .
            '`wtd` int(10) NOT NULL DEFAULT 1,' .
            '`value` DECIMAL(20,6),' .
            '`active` int(1) NOT NULL DEFAULT 0,' .
            ' PRIMARY KEY (`id_pbc`)' .
            ")";

        foreach ($statements as $statement) {
            if (!Db::getInstance()->Execute($statement)) {
                return false;
            }
        }

        return true;
    }

    public static function returnUserCountry()
    {
        if (Configuration::get('PBC_DELIVERY') == 1) {
            if (isset(Context::getContext()->cart->id_address_delivery)) {
                if (Context::getContext()->cart->id_address_delivery) {
                    $address = new Address(Context::getContext()->cart->id_address_delivery);
                    if (isset($address->id_country)) {
                        $country_address = new Country($address->id_country);
                        if ($country_address->id) {
                            return $country_address->iso_code;
                        }
                    }
                }
            }
        }

        $record = false;
        if ((!in_array(Tools::getRemoteAddr(), array('localhost', '127.0.0.1')) && !in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))) || Configuration::get('PBC_SIMULATE_ON') == true) {
            /* Check if Maxmind Database exists */
            if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_)) {
                $reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_);
                try {
                    $ip = Configuration::get('PBC_SIMULATE_IP');
                    $record = $reader->city((Configuration::get('PBC_SIMULATE_ON') ? (filter_var($ip, FILTER_VALIDATE_IP) ? $ip : Tools::getRemoteAddr()) : Tools::getRemoteAddr()));
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

    public function runStatement($statement)
    {
        if (@ !Db::getInstance()->Execute($statement)) {
            return false;
        }

        return true;
    }

    public function psversion($part = 1)
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

    public function displayForm()
    {
        $options_delivery = array(
            array(
                'id_option' => '0',
                'name' => $this->l('No')
            ),
            array(
                'id_option' => '1',
                'name' => $this->l('Yes - module will check also delivery address of customer and depending on it - increase price or not')
            ),
        );

        if (in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))) {
            $this->context->controller->errors[] = $this->l('You are on localhost, geolocation identifies your country only if your website is on-line.');
        }

        if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_) == false) {
            $this->context->controller->errors[] = $this->l('Module to identify customer country uses geolocation.') . ' ' . $this->l('In order to use Geolocation, please download') . ' ' . '<a href="https://mypresta.eu/prestashop-17/geolite2-city-geolocation-download.html">' . $this->l('this file') . '</a> ' . $this->l('and extract it (using Winrar or Gzip) into the /app/Resources/geoip/ directory.');
        } else {
            $this->context->controller->confirmations[] = $this->l('Geodatabase file exists in /app/Resources/geoip/ directory. Geolocation will work properly');
        }

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
                        'name' => 'PBC_SIMULATE_ON',
                        'values' => array(
                            array(
                                'id' => 'FPBC_SIMULATE_ON_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'PBC_SIMULATE_ON_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Simulate IP'),
                        'name' => 'PBC_SIMULATE_IP',
                        'desc' => $this->l('If you enabled option to simulate visit - insert here the example of IP address that module will use for geolocation purposes'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Use delivery address'),
                        'name' => 'PBC_DELIVERY',
                        'desc' => $this->l('Module will check delivery address of customer in addition to identification by IP address.'),
                        'options' => array(
                            'query' => $options_delivery,
                            'id' => 'id_option',
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
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = 'pbcID';
        $helper->identifier = 'pbc';
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form)) . $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/script.tpl');
    }

    public function getConfigFieldsValues()
    {
        return array(
            'PBC_SIMULATE_IP' => Tools::getValue('PBC_SIMULATE_IP', Configuration::get('PBC_SIMULATE_IP')),
            'PBC_SIMULATE_ON' => Tools::getValue('PBC_SIMULATE_ON', Configuration::get('PBC_SIMULATE_ON')),
            'PBC_USE_GEOIP2' => Tools::getValue('PBC_USE_GEOIP2', Configuration::get('PBC_USE_GEOIP2')),
            'PBC_COUNTRY' => Tools::getValue('PBC_COUNTRY', Configuration::get('PBC_COUNTRY')),
            'PBC_WTD' => Tools::getValue('PBC_WTD', Configuration::get('PBC_WTD')),
            'PBC_MANUFACT' => Tools::getValue('PBC_MANUFACT', Configuration::get('PBC_MANUFACT')),
            'PBC_CATEGORIES' => Tools::getValue('PBC_CATEGORIES', Configuration::get('PBC_CATEGORIES')),
            'PBC_EXCLUDE_CUSTOMER' => Tools::getValue('PBC_EXCLUDE_CUSTOMER', Configuration::get('PBC_EXCLUDE_CUSTOMER')),
            'PBC_EXCLUDE_GROUP' => Tools::getValue('PBC_EXCLUDE_GROUP', Configuration::get('PBC_EXCLUDE_GROUP')),
            'PBC_DELIVERY' => Tools::getValue('PBC_DELIVERY', Configuration::get('PBC_DELIVERY')),

        );
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('PBC_DELIVERY', Tools::getValue('PBC_DELIVERY'));
            Configuration::updateValue('PBC_SIMULATE_IP', Tools::getValue('PBC_SIMULATE_IP'));
            Configuration::updateValue('PBC_SIMULATE_ON', Tools::getValue('PBC_SIMULATE_ON'));
            Configuration::updateValue('PBC_USE_GEOIP2', Tools::getValue('PBC_USE_GEOIP2'));
        }
        return (Tools::isSubmit('btnSubmit') ? $this->displayConfirmation($this->l('Settings updated')) : '') . $this->displayForm() . $this->checkforupdates(0, 1);
    }

    public function inconsistency()
    {
        return;
    }
}

class pbcUpdate extends pbc
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

?>