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

if (!defined('_PS_VERSION_'))
    exit;
if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'groupinc/classes/GroupincConfiguration.php');

class GroupInc extends Module
{
    private $errors = array();
    private $success;
    protected static $_prices = array();
    protected static $_pricesLevel2 = array();

    public function __construct()
    {
        $this->name = 'groupinc';
        $this->tab = 'front_office_features';
        $this->version = '1.6.2';
        $this->author = 'idnovate';
        $this->module_key = 'f98f7f28a084f6b59d6f0b1daa57450b';
        $this->addons_id_product = '7422';
        $this->module_path = $this->_path;

        parent::__construct();

        $this->displayName = $this->l('Price increment/reduction by groups, categories and more');
        $this->description = $this->l('Increase or reduce your catalog product price with flexible and configurable conditions by categories, products, groups, customers, countries, zones, manufacturers and suppliers');


        $parent_class_name = version_compare(_PS_VERSION_, '1.7', '<') ? 'AdminPriceRule' : 'AdminCatalog';

        $this->tabMenu = array(
            'class_name' => 'AdminGroupinc',
            'parent_class_name' => $parent_class_name,
            'name' => $this->l('Increments and Discounts'),
            'visible' => true,
        );

        /* Backward compatibility */
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }
    }

    public function copyOverrideFolder()
    {
        if (!is_writable(_PS_MODULE_DIR_.$this->name)) {
            return false;
        }

        $version_override_folder = _PS_MODULE_DIR_.$this->name.'/override_'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2);
        $override_folder = _PS_MODULE_DIR_.$this->name.'/override';

        if (file_exists($override_folder) && is_dir($override_folder)) {
            $this->recursiveRmdir($override_folder);
        }

        if (is_dir($version_override_folder)) {
            $this->copyDir($version_override_folder, $override_folder);
        }

        return true;
    }

    protected function copyDir($src, $dst)
    {
        if (is_dir($src)) {
            $dir = opendir($src);
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->copyDir($src.'/'.$file, $dst.'/'.$file);
                    } else {
                        copy($src.'/'.$file, $dst.'/'.$file);
                    }
                }
            }
            closedir($dir);
        }
    }

    protected function recursiveRmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        $this->recursiveRmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }

        if (!$this->install(false)) {
            return false;
        }

        return true;
    }

    public function install()
    {
        if (!$this->copyOverrideFolder()) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->registerHook('displayAdminProductsExtra');
        }

        return parent::install()
            && $this->initSQLGI()
            && $this->addTab($this->tabMenu)
            && $this->registerHook('header')
            && $this->registerHook('footer')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('actionProductPriceCalculation')
            && $this->registerHook('displayGroupincBlock') // custom hook
            && Configuration::updateValue('GI_GROUP_VALUES', '')
            && (version_compare(_PS_VERSION_, '1.5', '>=') || (version_compare(_PS_VERSION_, '1.5', '<') && !$this->installGIOverride()));
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('GI_GROUP_VALUES')
            && (version_compare(_PS_VERSION_, '1.5', '>=') || (version_compare(_PS_VERSION_, '1.5', '<') && !$this->removeGIOverride()))
            && $this->removeTab($this->tabMenu)
            && $this->uninstallSQL();
    }

    public function installGIOverride()
    {
        // Make sure the environment is OK
        if (!is_dir(dirname(__FILE__).'/../../override/classes/'))
            mkdir(dirname(__FILE__).'/../../override/classes/', 0777, true);

        if (version_compare(_PS_VERSION_, '1.5', '<'))
        {
            if (file_exists(dirname(__FILE__).'/../../override/classes/Group.php'))
            {
                if (!md5_file(dirname(__FILE__).'/../../override/classes/Group.php') == md5_file(dirname(__FILE__).'/override_14/classes/Group.php'))
                    $this->errors[] = '/override/classes/Group.php';
            }
            if (!copy(dirname(__FILE__).'/override_14/classes/Group.php', dirname(__FILE__).'/../../override/classes/Group.php'))
                $this->errors[] = '/override/classes/Group.php';
        }

        if (count($this->errors))
            die('<div class="conf warn">
                    <img src="../img/admin/warn2.png" alt="" title="" />'.
                $this->l('The module was successfully installed but the following file already exist. Please, merge the file manually.').'<br />'.
                implode('<br />', $this->errors).
                '</div>');

        return true;
    }

    public function removeGIOverride()
    {
        // Make sure the environment is OK
        if (!is_dir(dirname(__FILE__).'/../../override/classes/'))
            mkdir(dirname(__FILE__).'/../../override/classes/', 0777, true);

        if (version_compare(_PS_VERSION_, '1.5', '<'))
        {
            if (file_exists(dirname(__FILE__).'/../../override/classes/Group.php'))
            {
                if (!md5_file(dirname(__FILE__).'/../../override/classes/Group.php') == md5_file(dirname(__FILE__).'/override_14/classes/Group.php'))
                    return false;
            }
            if (!unlink(dirname(__FILE__).'/../../override/classes/Group.php'))
                return false;
        }

        return true;
    }

    private function postValidation()
    {
        $replaced_array = array();
        foreach (Tools::getValue('reduction') as $key => $value)
        {
            $value = str_replace(',', '.', $value);
            if (is_numeric($value))
                $replaced_array[$key] = $value;
            else
            {
                $replaced_array[$key] = 0;
                $this->errors[] = $this->l('Please define a correct percentage');
            }
        }

        if (!count($this->errors))
            $this->success = true;

        Configuration::updateValue('GI_GROUP_VALUES', serialize($replaced_array));

        /* clean cache to show all prices properly */
        if (class_exists(Tools::clearCache())) {
            Tools::clearCache();
        }
    }

    public function getContent()
    {
        $warnings_to_show = '';
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            if (Configuration::get('PS_DISABLE_NON_NATIVE_MODULE')) {
                $warnings_to_show = $warnings_to_show . $this->displayError($this->l('You have to disable the option Disable non native modules at ADVANCED PARAMETERS - PERFORMANCE'));
               }

            if (Configuration::get('PS_DISABLE_OVERRIDES')) {
                $warnings_to_show = $warnings_to_show . $this->displayError($this->l('You have to disable the option Disable all overrides at ADVANCED PARAMETERS - PERFORMANCE'));
               }
        }

        if (!empty($warnings_to_show)) {
            $this->context->smarty->assign(array(
                'performance_link' => $this->context->link->getAdminLink('AdminPerformance'),
            ));
            return $warnings_to_show . $this->display(__FILE__, 'views/templates/admin/admin_warnings.tpl');
        }

        // check if the tab was not created in the installation
        $id_tab = Tab::getIdFromClassName($this->tabMenu['class_name']);
        if (!$id_tab) {
            $this->addTab($this->tabMenu);
        }

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            if (Tools::isSubmit('submitForm')) {
                $this->postValidation();
            }

            $this->context->smarty->assign(array(
                'displayName'    => $this->displayName,
                'group_values'    => unserialize(Configuration::get('GI_GROUP_VALUES')),
                'groups'        => Group::getGroups($this->context->language->id),
                'errors'        => $this->errors,
                'success'        => $this->success,
                'gi_path'         => $this->_path,
            ));

            return $this->display(__FILE__, 'views/templates/admin/admin_form.tpl');
        } else {
            return Tools::redirectAdmin('index.php?controller=' . $this->tabMenu['class_name'] . '&token=' . Tools::getAdminTokenLite($this->tabMenu['class_name']));
        }
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


    public function hookActionProductPriceCalculation(&$params)
    {
        /*$context = Context::getContext();
        if (isset($context->controller) && $context->controller->controller_type == 'admin') {
            if (Tools::isSubmit('newPrice')) {
                return false;
            }
        }*/
		if (self::botDetected()) {
				return; // Arrête l'exécution pour les bots
			}

        if (isset($_SERVER['REQUEST_URI'])) {
            $url_name = $_SERVER['REQUEST_URI'];
        } else {
            $url_name = "";
        }

        $path = parse_url($url_name, PHP_URL_PATH);
        $parts = explode('/', $path);

        $context = Context::getContext();
        if (isset($context->controller) && $context->controller->controller_type == 'admin') {
            if (Tools::isSubmit('newPrice')) {
                if (isset($parts[9])) {
                    if ((int)$params['id_product'] == (int)$parts[8]) {
                        return (float)Tools::getValue('newPrice');
                    }
                }                              
            }
        }
        
        $id_shop = $params['id_shop'];
        $id_product = $params['id_product'];
        $id_product_attribute = $params['id_product_attribute'];
        $id_country = $params['id_country'];
        $id_state = $params['id_state'];
        $zipcode = $params['zipcode'];
        $id_currency = $params['id_currency'];
        $id_group = $params['id_group'];
        $quantity = $params['quantity'];
        $use_tax = $params['use_tax'];
        $decimals = $params['decimals'];
        $only_reduc = $params['only_reduc'];
        $use_reduc = $params['use_reduc'];
        $with_ecotax = $params['with_ecotax'];
        $specific_price = $params['specific_price'];
        $use_group_reduction = $params['use_group_reduction'];
        $id_customer = $params['id_customer'];
        $use_customer_price = $params['use_customer_price'];
        $id_cart = $params['id_cart'];
        $real_quantity = $params['real_quantity'];
        $id_customization = $params['id_customization'];

        static $address = null;
        static $context = null;

        if ($context == null) {
            $context = Context::getContext()->cloneContext();
        }

        if ($address === null) {
            if (is_object($context->cart) && $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
                $id_address = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
                $address = new Address($id_address);
            } else {
                $address = new Address();
            }
        }

        if ($id_shop !== null && $context->shop->id != (int)$id_shop) {
            $context->shop = new Shop((int)$id_shop);
        }
        if (!$use_customer_price) {
            $id_customer = 0;
        }
        if ($id_product_attribute == null) {
            $id_product_attribute = Product::getDefaultAttribute($id_product);
        }

        $cache_id = (int)$id_product.'-'.(int)$id_shop.'-'.(int)$id_currency.'-'.(int)$id_country.'-'.$id_state.'-'.$zipcode.'-'.(int)$id_group.
                '-'.(int)$quantity.'-'.(int)$id_product_attribute.'-'.(int)$id_customization.
                '-'.(int)$with_ecotax.'-'.(int)$id_customer.'-'.(int)$use_group_reduction.'-'.(int)$id_cart.'-'.(int)$real_quantity.
                '-'.($only_reduc?'1':'0').'-'.($use_reduc?'1':'0').'-'.($use_tax?'1':'0').'-'.(int)$decimals;

        // reference parameter is filled before any returns
        $cart = new Cart($id_cart);
        if (!isset(self::$_prices['nb_products'])) {
            self::$_prices['nb_products'] = (int)$cart->nbProducts();
        } else if (self::$_prices['nb_products'] != (int)$cart->nbProducts()) {
            self::$_prices = array();
            self::$_prices['nb_products'] = (int)$cart->nbProducts();
        }

        $specific_price = SpecificPrice::getSpecificPrice(
            (int)$id_product,
            $id_shop,
            $id_currency,
            $id_country,
            $id_group,
            $quantity,
            $id_product_attribute,
            $id_customer,
            $id_cart,
            $real_quantity
        );


        if (isset(self::$_prices[$cache_id])) {
            if (isset($specific_price['price']) && $specific_price['price'] > 0) {
                $specific_price['price'] = self::$_prices[$cache_id];
            }

            if (isset(self::$_prices['specific_price'])) {
                $specific_price = self::$_prices['specific_price'];
            }
            return self::$_prices[$cache_id];
        }

        $configs = array();
        $price = false;
        $address->id_country = $id_country;
        $address->id_state = $id_state;
        $address->postcode = $zipcode;
        $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context));
        $product_tax_calculator = $tax_manager->getTaxCalculator();

        $cache_id_2 = $id_product.'-'.$id_shop;
        if (!isset(self::$_pricesLevel2[$cache_id_2]) || !isset(self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute]['wholesale_price'])) {
            $sql = new DbQuery();
            if (Combination::isFeatureActive()) {
                $sql->select('product_shop.`price`, product_shop.`wholesale_price`, product_shop.`ecotax`, pa.`wholesale_price` as attr_wholesale_price');
            } else {
                $sql->select('product_shop.`price`, product_shop.`wholesale_price`, product_shop.`ecotax`');
            }
            $sql->from('product', 'p');
            $sql->innerJoin('product_shop', 'product_shop', '(product_shop.id_product=p.id_product AND product_shop.id_shop = '.(int)$id_shop.')');
            $sql->where('p.`id_product` = '.(int)$id_product);
            if (Combination::isFeatureActive()){
                $sql->select('product_attribute_shop.id_product_attribute, product_attribute_shop.`price` AS attribute_price, product_attribute_shop.default_on');
                $sql->leftJoin('product_attribute', 'pa', 'pa.`id_product` = p.`id_product`');
                $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.id_product_attribute = pa.id_product_attribute AND product_attribute_shop.id_shop = '.(int)$id_shop.')');
            } else {
                $sql->select('0 as id_product_attribute');
            }
            $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            if (is_array($res) && count($res)) {
                foreach ($res as $row) {
                    $array_tmp = array(
                        'price' => $row['price'],
                        'ecotax' => $row['ecotax'],
                        'wholesale_price' => $row['wholesale_price'],
                        'attr_wholesale_price' => (isset($row['attr_wholesale_price']) ? $row['attr_wholesale_price'] : null),
                        'attribute_price' => (isset($row['attribute_price']) ? $row['attribute_price'] : null)
                    );
                    self::$_pricesLevel2[$cache_id_2][(int)$row['id_product_attribute']] = $array_tmp;
                    if (isset($row['default_on']) && $row['default_on'] == 1) {
                        self::$_pricesLevel2[$cache_id_2][0] = $array_tmp;
                    }
                }
            }
        }
        if (!isset(self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute])) {
            return;
        }
        $result = self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute];
        if (!$specific_price || $specific_price['price'] < 0) {
            $price = (float)$result['price'];
        } else {
            $price = (float)$specific_price['price'];
        }
        if (!$specific_price || !($specific_price['price'] >= 0 && $specific_price['id_currency'])) {
            $price = Tools::convertPrice($price, $id_currency);
            if (isset($specific_price['price']) && $specific_price['price'] >= 0) {
                $specific_price['price'] = $price;
            }
        }
        if (is_array($result) && (!$specific_price || !$specific_price['id_product_attribute'] || $specific_price['price'] < 0)) {
            $attribute_price = Tools::convertPrice($result['attribute_price'] !== null ? (float)$result['attribute_price'] : 0, $id_currency);
            if ($id_product_attribute !== false) {
                $price += $attribute_price;
            }
        }
        if ((int)$id_customization) {
            $price += Customization::getCustomizationPrice($id_customization);
        }

        $wholeWithoutTaxes = (float)$result['wholesale_price'];
        if (isset($result['attr_wholesale_price']) && $result['attr_wholesale_price'] != 0) {
            $wholeWithoutTaxes = (float)$result['attr_wholesale_price'];
        }

        if ($id_product_attribute) {
            $comb = new Combination($id_product_attribute);
            if (isset($comb->wholesale_price) && $comb->wholesale_price > 0) {
                $wholeWithoutTaxes = $comb->wholesale_price;
            }
        }

        $retailWithoutTaxes = $price;

        $prod = new Product($id_product);
        $product_suppliers_array = ProductSupplier::getSupplierCollection($id_product);
        $supplier_prices = array();
        foreach ($product_suppliers_array as $supplier) {
            $supplier_prices[] = ProductSupplier::getProductSupplierPrice($id_product, $id_product_attribute, $supplier->id_supplier);
        }

        if (!empty($supplier_prices)) {
            $supplierWithoutTaxes = min($supplier_prices);
        } else {
            $supplierWithoutTaxes = ProductSupplier::getProductSupplierPrice($id_product, $id_product_attribute, $prod->id_supplier);
        }

        $priceDisplay = 1;
        $groupinc_result = array();

        $showDecimals = false;

        if (Module::isEnabled('groupinc')) {
            include_once(_PS_MODULE_DIR_.'groupinc/classes/GroupincConfiguration.php');
            $groupinc = new GroupincConfiguration();
            $configs = $groupinc->getGIConfigurations($id_shop, $id_product, $id_customer, $id_country, $id_state, $id_currency, $context->language->id, true, true, $id_product_attribute, $quantity, false, false, $id_group);
        }

        if (!empty($configs)) {
            foreach ($configs as $conf) {
                if ($conf['show_decimals']) {
                    $showDecimals = true;
                    break;
                }
            }
            if (Configuration::get('GROUPINC_PRIORIZE_MIN')) {
                $groupinc_result = $groupinc->getPriceModified($configs, $id_product, $retailWithoutTaxes, $wholeWithoutTaxes, $specific_price, $product_tax_calculator, $priceDisplay, $use_tax, true, $id_group, $use_group_reduction, $supplierWithoutTaxes);
                if ($groupinc_result) {
                    $id_groupinc_configuration_min = array_keys($groupinc_result, min($groupinc_result));
                    $configs_final = $groupinc->getConfig($id_groupinc_configuration_min[0]);
                    if (!empty($configs_final)) {
                        $groupinc_result = $groupinc->getPriceModified($configs_final, $id_product, $retailWithoutTaxes, $wholeWithoutTaxes, $specific_price, $product_tax_calculator, $priceDisplay, $use_tax, false, $id_group, $use_group_reduction, $supplierWithoutTaxes);
                    }
                }
            } else {
                $groupinc_result = $groupinc->getPriceModified($configs, $id_product, $retailWithoutTaxes, $wholeWithoutTaxes, $specific_price, $product_tax_calculator, $priceDisplay, $use_tax, false, $id_group, $use_group_reduction, $supplierWithoutTaxes);
            }

            if ($groupinc_result && !empty($groupinc_result)) {
                if (isset($groupinc_result['reduction']) && $groupinc_result['reduction'] > 0) {
                    $specific_price = $groupinc_result;
                    if (isset($specific_price['price'])) {
                        $price = $specific_price['price'];
                    }
                } else {
                    $price = $groupinc_result['price'];
                    $specific_price = null;
                }
            } else {
                $price = false;
            }
        } else {
            $price = false;
        }

        $configs = array();
        $sameprice_result = array();
        if (Module::isEnabled('sameprice')) {
            include_once(_PS_MODULE_DIR_.'sameprice/classes/SamepriceConfiguration.php');
            $sameprice = new SamepriceConfiguration();
            $configs = $sameprice->getConfigs($id_shop, $id_product, $id_customer, $id_country, $id_state, $id_currency, $context->language->id, $id_product_attribute);
        }

        if (!empty($configs)) {
            if ($price) {
                $retailWithoutTaxes = $price;
            }

            $sameprice_result = $sameprice->getPriceModified($configs, $id_product, $retailWithoutTaxes, $specific_price, $product_tax_calculator, $id_group, $use_group_reduction);
            if ($sameprice_result && !empty($sameprice_result)) {
                if (isset($sameprice_result['reduction']) && $sameprice_result['reduction'] > 0) {
                    $specific_price = $sameprice_result;
                    if (isset($specific_price['price'])) {
                        $price = $specific_price['price'];
                    }
                } else {
                    $price = $sameprice_result['price'];
                    $specific_price = null;
                }
            }
        }

        if ($use_tax) {
            $price = $product_tax_calculator->addTaxes($price);
        }

        if (($result['ecotax'] || isset($result['attribute_ecotax'])) && $with_ecotax) {
            $ecotax = $result['ecotax'];
            if (isset($result['attribute_ecotax']) && $result['attribute_ecotax'] > 0) {
                $ecotax = $result['attribute_ecotax'];
            }
            if ($id_currency) {
                $ecotax = Tools::convertPrice($ecotax, $id_currency);
            }
            if ($use_tax) {
                $tax_manager = TaxManagerFactory::getManager(
                    $address,
                    (int)Configuration::get('PS_ECOTAX_TAX_RULES_GROUP_ID')
                );
                $ecotax_tax_calculator = $tax_manager->getTaxCalculator();
                $price += $ecotax_tax_calculator->addTaxes($ecotax);
            } else {
                $price += $ecotax;
            }
        }

        $specific_price_reduction = 0;
        if (($only_reduc || $use_reduc) && $specific_price) {
            if (isset($specific_price['reduction_type']) && $specific_price['reduction_type'] == 'amount') {
                $reduction_amount = $specific_price['reduction'];
                if (!$specific_price['id_currency']) {
                    $reduction_amount = Tools::convertPrice($reduction_amount, $id_currency);
                }
                $specific_price_reduction = $reduction_amount;
                if (!$use_tax && $specific_price['reduction_tax']) {
                    $specific_price_reduction = $product_tax_calculator->removeTaxes($specific_price_reduction);
                }
                if ($use_tax && !$specific_price['reduction_tax']) {
                    $specific_price_reduction = $product_tax_calculator->addTaxes($specific_price_reduction);
                }
            } else {
                $specific_price_reduction = $price * $specific_price['reduction'];
            }
            if (isset($specific_price['reduction']) && $specific_price['reduction'] && $specific_price['reduction_type'] == 'percentage') {
                if (!$showDecimals) {
                    $specific_price['reduction'] = Tools::ps_round($specific_price['reduction'], 2);
                }
            }

            if (isset($specific_price['reduction'])) {
                $specific_price['reduction'] = (string)$specific_price['reduction'];
            }
        }

        if ($use_reduc) {
            $price -= $specific_price_reduction;
        }
        if ($only_reduc) {
            $price = Tools::ps_round($specific_price_reduction, $decimals);
        } else {
            $price = Tools::ps_round($price, $decimals);

            if ($price < 0) {
                $price = 0;
            }
        }
        $params['specific_price'] = $specific_price;
        $params['price'] = $price;
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = 0;

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $id_product = (int)Tools::getValue('id_product');
        } else {
            if (isset($params['id_product'])) {
                $id_product = $params['id_product'];
            }
        }

        if ($id_product) {
            include_once(_PS_MODULE_DIR_.'groupinc/controllers/admin/AdminGroupincController.php');
            $groupincCtrl = new AdminGroupincController();
            $contentHtmlReturn = $groupincCtrl->getConfigurations($id_product);
            if (!empty($contentHtmlReturn)) {
                $_html_configs = '<style type="text/css">';
                $_html_configs .= ' .time-column {width: auto;    height: 20px;    display: block;} ';
                $_html_configs .= ' .time-column.valid-date-icon { color: #72c279; } ';
                $_html_configs .= ' .time-column.expired-date-icon { color: #e08f95; } ';
                $_html_configs .= ' .time-column.future-date-icon { color: #f3e838; }';
                $_html_configs .= ' .time-column [class^="icon-"] { font-size: 18px !important; }';
                $_html_configs .= ' </style>';
                $_html_configs .= $contentHtmlReturn;
                $output = $_html_configs;
                return $output;
            }
        }
        return false;
    }

    public function hookDisplayHeader()
    {
        if ($this->checkRulesExist()) {
            $this->context->controller->addCSS($this->_path.'views/css/front.css');
            if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'product') {
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                       $this->context->controller->addJS($this->_path.'views/js/front17.js');
                   } else {
                       $this->context->controller->addJS($this->_path.'views/js/gi_functions_front.js');
                   }
               }
        }
    }

    public function hookDisplayFooter()
    {
        if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'product') {
            if ($this->checkRulesExist()) {
                   if (version_compare(_PS_VERSION_, '1.7', '>=') || version_compare(_PS_VERSION_, '1.6', '<=')) {
                    return $this->display(__FILE__, 'views/templates/front/front.tpl');
                }
            }
        }
    }

    public function hookDisplayProductPriceBlock($params)
    {
        $id_product = 0;
        $id_product_attribute = 0;
        if ($params['type']) {
            if (isset($params['id_product']) || isset($params['product'])) {
                if (isset($params['product']->id)) {
                    $id_product = $params['product']->id;
                    if (isset($params['product']->id_product_attribute)) {
                        $id_product_attribute = $params['product']->id_product_attribute;
                    }
                } else if (isset($params['product']['id_product'])) {
                    $id_product = $params['product']['id_product'];
                    if (isset($params['product']['id_product_attribute'])) {
                        $id_product_attribute = $params['product']['id_product_attribute'];
                    }
                } else if (isset($params['id_product'])) {
                    $id_product = $params['id_product'];
                    if (isset($params['id_product_attribute'])) {
                        $id_product_attribute = $params['id_product_attribute'];
                    }
                }
            } else if (Tools::isSubmit('id_product')) {
                $id_product = (int)Tools::getValue('id_product');
                $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            }

            $context = Context::getContext();
            if (isset($context->controller->php_self) && $context->controller->php_self == 'product') {
                if ($params['type'] == "after_price") {
                    $confs = $this->getConfigsByProduct($id_product, $id_product_attribute);

                    if (!empty($confs)) {
                        $id_lang = $context->cart->id_lang;
                        $countdown_prodpage = "";
                        $textDisplay = "";
                        $cd_style_prodpage = "";
                        foreach ($confs as $conf) {

                            $config = new GroupincConfiguration($conf['id_groupinc_configuration']);
                            if ($config->show_text_prodpage && $config->addit_text_prodpage[$id_lang] != '') {
                                $textDisplay = $config->addit_text_prodpage[$id_lang];
                            }

                            if ($config->countdown_prodpage && $config->cd_style_prodpage[$id_lang] != '') {
                                $cd_style_prodpage = $config->cd_style_prodpage[$id_lang];
                            }

                            if ($config->countdown_prodpage && $config->date_to > 0) {
                                if ($countdown_prodpage == "" || $config->date_to < $countdown_prodpage) {
                                    $countdown_prodpage = $config->date_to;
                                }
                            }

                        }

                        if ($countdown_prodpage || $textDisplay) {
                            if ($cd_style_prodpage) {
                                $text_replace = '<span class="countdown_'.$id_product.'"></span>';
                                if (strpos($cd_style_prodpage, '%countdown%') !== false) {
                                    $cd_style_prodpage = Tools::str_replace_once('%countdown%', $text_replace, $cd_style_prodpage);
                                }
                            }

                            $this->context->smarty->assign(array(
                                'countdown' => $countdown_prodpage,
                                'id_product' => $id_product,
                                'cd_style' => $cd_style_prodpage,
                                'text' => $textDisplay,
                                'today' => date("Y-m-d H:i:s"),
                                'module_dir' => _PS_MODULE_DIR_,
                                'day_txt'=> $this->l('d'),
                                'hour_txt' => $this->l('h'),
                                'minute_txt'=> $this->l('m'),
                                'second_txt' => $this->l('s'),
                            ));
                            return $this->display(__FILE__, 'views/templates/front/cdtextprodpage.tpl');
                        }
                    }
                }
            } else if (isset($context->controller->php_self) && $context->controller->php_self != 'product') {
                if ($params['type'] == "weight") {
                    $countdown = false;
                    $confs = $this->getConfigsByProduct($id_product, $id_product_attribute);
                    if (!empty($confs)) {
                        $context = Context::getContext();
                        $id_lang = $context->cart->id_lang;
                        $textDisplay = "";
                        $cd_style = "";
                        $countdown = "";

                        foreach ($confs as $conf) {
                            $config = new GroupincConfiguration($conf['id_groupinc_configuration']);
                            if ($config->show_text && $config->addit_text[$id_lang] != '') {
                                $textDisplay = $config->addit_text[$id_lang];
                            }

                            if ($config->countdown && $config->date_to > 0) {
                                if ($countdown == "" || $config->date_to < $countdown) {
                                    $countdown = $config->date_to;
                                }
                            } else {
                                $countdown = false;
                            }

                            if ($config->countdown && $config->cd_style[$id_lang] != '') {
                                $cd_style = $config->cd_style[$id_lang];
                            }
                        }

                        if ($countdown || $textDisplay) {
                            if ($cd_style) {
                                $text_replace = '<span class="countdown_'.$id_product.'"></span>';
                                if (strpos($cd_style, '%countdown%') !== false) {
                                    $cd_style = Tools::str_replace_once('%countdown%', $text_replace, $cd_style);
                                }
                            }

                            $this->context->smarty->assign(array(
                                'countdown' => $countdown,
                                'id_product' => $id_product,
                                'cd_style' => $cd_style,
                                'text' => $textDisplay,
                                'today' => date("Y-m-d H:i:s"),
                                'module_dir' => _PS_MODULE_DIR_,
                                'day_txt'=> $this->l('d'),
                                'hour_txt' => $this->l('h'),
                                'minute_txt'=> $this->l('m'),
                                'second_txt' => $this->l('s'),
                            ));
                            return $this->display(__FILE__, 'views/templates/front/cdtext.tpl');
                        }

                    }
                }
            }
        }
    }


    public function getConfigsByProduct($id_product, $id_product_attribute = 0)
    {

        $configs = array();
        if ($id_product) {
            include_once(_PS_MODULE_DIR_.'groupinc/classes/GroupincConfiguration.php');
            $mod = new GroupincConfiguration();
            $configs = $mod->getConfigsCD(Context::getContext()->shop->id, $id_product, $id_product_attribute);
        }
        if (!empty($configs)) {
            return $configs;
        }
        return false;
    }

    private function addTab($tabMenu)
    {
        if (version_compare(_PS_VERSION_, '1.7.1', '<')) {
            /*Create Tab*/
            $id_tab = Tab::getIdFromClassName($tabMenu['class_name']);
            $tabNames = array();

            if(!$id_tab) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $langs = Language::getlanguages(false);

                    foreach ($langs as $l) {
                        $tabNames[$l['id_lang']] = $tabMenu['name'];
                    }

                    $tab = new Tab();
                    $tab->module = $this->name;
                    $tab->name = $tabNames;
                    $tab->class_name = $tabMenu['class_name'];
                    if (isset($tabMenu['parent_class_name'])) {
                        $tab->id_parent = Tab::getIdFromClassName($tabMenu['parent_class_name']);
                    } else {
                        $tab->id_parent = -1;
                    }

                    if(!$tab->save()) {
                        return false;
                    }
                } else {
                    $tab = new Tab();
                    $tab->class_name = $tabMenu['class_name'];
                    $tab->module = $this->name;
                    $languages = Language::getLanguages();
                    foreach ($languages as $language) {
                        $tab->name[$language['id_lang']] = $this->l($tabMenu['name']);
                    }

                    if (isset($tabMenu['parent_class_name'])) {
                        $tab->id_parent = Tab::getIdFromClassName($tabMenu['parent_class_name']);
                    } else {
                        $tab->id_parent = -1;
                    }

                    if(!$tab->add()) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function removeTab($tab)
    {
        if (version_compare(_PS_VERSION_, '1.7.1', '<')) {
            $idTab = Tab::getIdFromClassName($tab['class_name']);

            if ($idTab) {
                $tab = new Tab($idTab);
                $tab->delete();
                return true;
            }
        }

        return true;
    }

    protected function initSQLGI()
    {
        Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `'.pSQL(_DB_PREFIX_.$this->name).'_configuration` (
                `id_groupinc_configuration` int(10) unsigned NOT NULL auto_increment,
                `name` VARCHAR(100) NULL,
                `type` int(1) unsigned NOT NULL DEFAULT "1",
                `mode` int(1) unsigned NOT NULL DEFAULT "1",
                `price_calculation` int(1) unsigned NOT NULL DEFAULT "1",
                `price_application` int(1) unsigned NOT NULL DEFAULT "1",
                `fix` decimal(10,3) NULL DEFAULT "0.000",
                `percentage` decimal(10,3) NULL DEFAULT "0.000",
                `min_result_price` decimal(10,3) NULL DEFAULT "0.000",
                `max_result_price` decimal(10,3) NULL DEFAULT "0.000",
                `threshold_min_price` decimal(10,3) NULL DEFAULT "0.000",
                `threshold_max_price` decimal(10,3) NULL DEFAULT "0.000",
                `threshold_price` int(1) unsigned NOT NULL DEFAULT "1",
                `skip_discounts` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `override_discounts` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `groups` TEXT NULL,
                `customers` TEXT NULL,
                `products` TEXT NULL,
                `countries` TEXT NULL,
                `zones` TEXT NULL,
                `categories` TEXT NULL,
                `manufacturers` TEXT NULL,
                `suppliers` TEXT NULL,
                `languages` TEXT NULL,
                `currencies` TEXT NULL,
                `features` TEXT NULL,
                `attributes` TEXT NULL,
                `product_qty` int(4) unsigned NOT NULL DEFAULT "0",
                `show_as_discount` tinyint(1) unsigned NULL DEFAULT "0",
                `active` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `show_on_sale` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `show_prices_drop` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `show_decimals` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `filter_prices` tinyint(1) unsigned DEFAULT "0",
                `filter_store` tinyint(1) unsigned DEFAULT "0",
                `filter_stock` tinyint(1) unsigned,
                `min_stock` int(10) NULL,
                `max_stock` int(10) NULL,
                `backoffice` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `priority` int(1) unsigned DEFAULT "0",
                `first_condition` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `id_shop` tinyint(1) unsigned NOT NULL DEFAULT "0",
                `schedule` TEXT NULL,
                `filter_weight` tinyint(1) unsigned,
                `min_weight` decimal(10,3) NULL DEFAULT "0.000",
                `max_weight` decimal(10,3) NULL DEFAULT "0.000",
                `date_from` DATETIME,
                `date_to` DATETIME,
                `date_add` DATETIME,
                `date_upd` DATETIME,
                `cart_amount` decimal(10,3) NULL DEFAULT "0.000",
                `grouped_by` tinyint(1) unsigned NULL,
                `products_excluded` TEXT NULL,
                `customers_excluded` TEXT NULL,
                `show_text` tinyint(1) unsigned,
                `show_text_prodpage` tinyint(1) unsigned,
                `countdown` tinyint(1) unsigned NULL,
                `countdown_prodpage` tinyint(1) unsigned NULL,
                `higher_discount` tinyint(1) unsigned NULL,
                `sequential_discount` tinyint(1) unsigned NULL,
                `fp_product_price` tinyint(1) unsigned NULL DEFAULT "1",
                `fp_attribute_price` tinyint(1) unsigned NULL DEFAULT "1",
                `cat_default` tinyint(1) unsigned,
                `group_default` tinyint(1) unsigned,
                `priorize_existing` tinyint(1) unsigned NULL,
            PRIMARY KEY (`id_groupinc_configuration`),
            KEY `id_groupinc_configuration` (`id_groupinc_configuration`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;');

        Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `'.pSQL(_DB_PREFIX_.$this->name).'_configuration_lang` (
                `id_groupinc_configuration` int unsigned NOT NULL,
                `id_lang` int unsigned NOT NULL,
                `addit_text` TEXT NULL,
                `addit_text_prodpage` TEXT NULL,
                `cd_style` TEXT NULL,
                `cd_style_prodpage` TEXT NULL,
            PRIMARY KEY (`id_groupinc_configuration`, `id_lang`),
            KEY `id_groupinc_configuration` (`id_groupinc_configuration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        return true;
    }

    protected function uninstallSQL()
    {
        try {
            Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'.pSQL(_DB_PREFIX_.$this->name).'_configuration'.time().'` AS SELECT * FROM `'.pSQL(_DB_PREFIX_.$this->name).'_configuration`');
        } catch (Exception $e) {
            // nothing to do
        }

        try {
            Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'.pSQL(_DB_PREFIX_.$this->name).'_configuration_lang'.time().'` AS SELECT * FROM `'.pSQL(_DB_PREFIX_.$this->name).'_configuration_lang`');
        } catch (Exception $e) {
            // nothing to do
        }

        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'.pSQL(_DB_PREFIX_.$this->name).'_configuration`');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'.pSQL(_DB_PREFIX_.$this->name).'_configuration_lang`');
        return true;
    }

    protected function checkRulesExist()
    {
        $id_shop = Context::getContext()->shop->id;

        $query = '
                SELECT gi.* FROM `'.pSQL(_DB_PREFIX_.$this->name).'_configuration` gi WHERE gi.`id_shop` = '.(int)$id_shop.'
                AND gi.`active` = 1';
        $rules = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        return ($rules == false) ? false : true;
    }

    public function displayProductsLink($token, $id)
    {
        $this->context->smarty->assign(array(
            //'href' => 'index.php?controller=AdminGroupinc&token='.Tools::getAdminTokenLite('AdminGroupinc').'&id_groupinc_configuration='.$id,
            'href' => 'index.php?controller=AdminGroupinc&token='.Tools::getAdminTokenLite('AdminGroupinc').'&id_groupinc_configuration='.$id.'&viewProducts',
            'action' => $this->l('View products'),
            'token' => $token
        ));
        return $this->display(__FILE__, 'views/templates/admin/view_products.tpl');
    }
}