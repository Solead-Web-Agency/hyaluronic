<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Blockproductsbycountry extends Module
{
    protected $config_form = false;
    protected static $blocked_products = array();

    public function __construct()
    {
        $this->name = 'blockproductsbycountry';
        $this->tab = 'front_office_features';
        $this->version = '1.0.2';
        $this->author = 'Active Design';
        $this->need_instance = 0;
        $this->module_key = '41925b108bef2de9f40c6772b374a146';
        $this->author_address = '0xc0D7cE57752e47305707d7174B9686C0Afb229c3';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Block products by country');
        $this->description = $this->l('The modules allows you to block products based on customer\'s country. You can build rules based on: Individual product, category, manufacturer, supplier.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        
        $this->secondaryControllers = array(
            'AdminBlockProductByCategory' => $this->l('Block products by category'),
            'AdminBlockProductByManufacturer' => $this->l('Block products by manufacturer'),
            'AdminBlockProductBySupplier' => $this->l('Block products by supplier'),
            'AdminBlockProductByCountry' => $this->l('Block products by country'),
        );
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        /* FIX FOR INEXISTENT /override/classes/checkout FOLDER */
        if (Tools::substr(_PS_VERSION_, 0, 3) == '1.7') {
            if (!file_exists(_PS_ROOT_DIR_.'/override/classes/checkout/')) {
                if (!mkdir(_PS_ROOT_DIR_.'/override/classes/checkout/') || !chmod(_PS_ROOT_DIR_.'/override/classes/checkout/', 0755) || file_put_contents(_PS_ROOT_DIR_.'/override/classes/checkout/index.php', '', FILE_APPEND) === false) {
                    return false;
                }
            }
        }
        Configuration::updateValue('BPBC_PRODUCTS', true);
        Configuration::updateValue('BPBC_CATEGORIES', true);
        Configuration::updateValue('BPBC_MANUFACTURERS', true);
        Configuration::updateValue('BPBC_SUPPLIERS', true);
        $array = array();
        foreach (Language::getLanguages() as $lang) {
            /* Texts below shall not be translated, to fill correctly for every language */
            if ($lang['iso_code'] == 'ro') {
                $array[(int)$lang['id_lang']] = 'Ne pare rau, acest produs nu este disponibil in %countries%';
            } elseif ($lang['iso_code'] == 'fr') {
                $array[(int)$lang['id_lang']] = 'Désolé, ce produit n\'est pas disponible dans %countries%';
            } elseif ($lang['iso_code'] == 'it') {
                $array[(int)$lang['id_lang']] = 'Spiacenti, questo prodotto non è disponibile in %countries%';
            } elseif ($lang['iso_code'] == 'es') {
                $array[(int)$lang['id_lang']] = 'Lo sentimos, este producto no está disponible en %countries%';
            } else {
                $array[(int)$lang['id_lang']] = 'Sorry, this product is not available in %countries%';
            }
        }
        Configuration::updateValue('BPBC_TEXT_BLOCKED', $array);
        
        include_once(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('displayProductListReviews') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerTabs();
    }

    public function uninstall()
    {
        Configuration::deleteByName('BPBC_PRODUCTS');
        Configuration::deleteByName('BPBC_CATEGORIES');
        Configuration::deleteByName('BPBC_MANUFACTURERS');
        Configuration::deleteByName('BPBC_SUPPLIERS');
        Configuration::deleteByName('BPBC_TEXT_BLOCKED');
        
        include_once(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall() &&
            $this->unregisterTabs();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $message = "";
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitBlockproductsbycountryModule')) == true) {
            if ($this->postProcess()) {
                $message = $this->displayConfirmation($this->l('Settings saved.'));
            } else {
                $message = $this->displayError($this->l('Could not save settings.'));
            }
        }

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'links' => $this->getAdminLinks(),
        ));

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $message.$output.$this->renderForm();
    }
    
    public function getAdminLinks()
    {
        $link = $this->context->link;
        if (Tools::substr(_PS_VERSION_, 0, 3) == '1.7') {
            $configure_link = $link->getAdminLink('AdminModules', true, array(), array(
                'configure' => $this->name,
                'tab_module' => $this->tab,
                'module_name' => $this->name,
            ));
        } else {
            $configure_link = $link->getAdminLink('AdminModules', true);
            $configure_link .= '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        }
        return array(
            'block_categories' => $link->getAdminLink('AdminBlockProductByCategory'),
            'block_manufacturers' => $link->getAdminLink('AdminBlockProductByManufacturer'),
            'block_suppliers' => $link->getAdminLink('AdminBlockProductBySupplier'),
            'block_countries' => $link->getAdminLink('AdminBlockProductByCountry'),
            'configure' => $configure_link,
        );
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlockproductsbycountryModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active for products'),
                        'name' => 'BPBC_PRODUCTS',
                        'is_bool' => true,
                        'desc' => $this->l('Disabling this will disable all your product blocking rules temporarily.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active for categories'),
                        'name' => 'BPBC_CATEGORIES',
                        'is_bool' => true,
                        'desc' => $this->l('Disabling this will disable all your category blocking rules temporarily.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active for manufacturers'),
                        'name' => 'BPBC_MANUFACTURERS',
                        'is_bool' => true,
                        'desc' => $this->l('Disabling this will disable all your manufacturer blocking rules temporarily.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active for suppliers'),
                        'name' => 'BPBC_SUPPLIERS',
                        'is_bool' => true,
                        'desc' => $this->l('Disabling this will disable all your supplier blocking rules temporarily.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Text for blocked products'),
                        'name' => 'BPBC_TEXT_BLOCKED',
                        'lang' => true,
                        'is_bool' => true,
                        'desc' => $this->l('This text will be shown in product page. Use %country% to show customer\'s country, and %countries% to show blocked countries list.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $langs = Language::getLanguages();
        $return = array(
            'BPBC_PRODUCTS' => Configuration::get('BPBC_PRODUCTS'),
            'BPBC_CATEGORIES' => Configuration::get('BPBC_CATEGORIES'),
            'BPBC_MANUFACTURERS' => Configuration::get('BPBC_MANUFACTURERS'),
            'BPBC_SUPPLIERS' => Configuration::get('BPBC_SUPPLIERS'),
        );
        foreach ($langs as $lang) {
            $return['BPBC_TEXT_BLOCKED'][(int)$lang['id_lang']] = Configuration::get('BPBC_TEXT_BLOCKED', (int)$lang['id_lang']);
        }
        return $return;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $langs = Language::getLanguages();

        foreach (array_keys($form_values) as $key) {
            if ($key == 'BPBC_TEXT_BLOCKED') {
                $array = array();
                foreach ($langs as $lang) {
                    $array[(int)$lang['id_lang']] = Tools::getValue($key."_".$lang['id_lang']);
                }
                Configuration::updateValue($key, $array);
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
        return true;
    }
    
    public function registerTabs()
    {
        foreach ($this->secondaryControllers as $class_name => $name) {
            $tab = new Tab;
            
            $tab->class_name = $class_name;
            $tab->id_parent = -1;
            $tab->module = $this->name;
            $tab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $name;
            if (!$tab->add()) {
                return false;
            }
        }
        return true;
    }
    
    public function unregisterTabs()
    {
        foreach (array_keys($this->secondaryControllers) as $class_name) {
            $id_tab = Tab::getIdFromClassName($class_name);
            if ($id_tab) {
                $tab = new Tab((int)$id_tab);
                if (Validate::isLoadedObject($tab)) {
                    if (!$tab->delete()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    public static function getProductsCountriesArray($id_product)
    {
        $selected = self::getBlockedCountriesByProduct($id_product);
        return self::getCountriesArray($selected);
    }
    
    public static function getCountriesArray($selected = array())
    {
        $id_lang = Context::getContext()->language->id;
        $return = Db::getInstance()->executeS("SELECT c.`id_country`, c.`iso_code`, cl.`name` FROM `"._DB_PREFIX_."country` c LEFT JOIN `"._DB_PREFIX_."country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = '".(int)$id_lang."') ORDER BY cl.`name`");
        if (!empty($selected) && is_array($selected)) {
            foreach ($return as &$row) {
                if (in_array((int)$row['id_country'], $selected)) {
                    $row['selected'] = true;
                }
            }
        }
        return $return;
    }
    
    public static function updateProduct($id_product, $blocked_countries = array())
    {
        self::emptyProduct($id_product);
        if ($blocked_countries && is_array($blocked_countries)) {
            self::addProduct($id_product, $blocked_countries);
        }
    }
    
    public static function addProduct($id_product, $blocked_countries)
    {
        $array = array();
        foreach ($blocked_countries as $id_country) {
            $array[] = array(
                'id_product' => (int)$id_product,
                'id_country' => (int)$id_country,
            );
        }
        return Db::getInstance()->insert('bpbc_products', $array);
    }
    
    public static function emptyProduct($id_product)
    {
        return Db::getInstance()->delete('bpbc_products', '`id_product` = "'.(int)$id_product.'"');
    }
    
    public static function isProductBlocked($id_product, $id_country = null)
    {
        if (isset(self::$blocked_products[$id_product])) {
            return self::$blocked_products[$id_product];
        }
        if (empty($id_country)) {
            $id_country = self::getCountry();
        }
        if ($id_country) {
            self::$blocked_products[$id_product] = in_array($id_country, self::getBlockedCountries($id_product));
            return self::$blocked_products[$id_product];
        }
        return false;
    }
    
    public static function getBlockedCountries($id_product)
    {
        $context = Context::getContext();
        $config = array(
            'BPBC_PRODUCTS' => Configuration::get('BPBC_PRODUCTS'),
            'BPBC_CATEGORIES' => Configuration::get('BPBC_CATEGORIES'),
            'BPBC_MANUFACTURERS' => Configuration::get('BPBC_MANUFACTURERS'),
            'BPBC_SUPPLIERS' => Configuration::get('BPBC_SUPPLIERS'),
        );
        $sql = array();
        if ($config['BPBC_PRODUCTS']) {
            $sql['products'] = 'SELECT `id_country` FROM `'._DB_PREFIX_.'bpbc_products` WHERE `id_product` = "'.(int)$id_product.'"';
        }
        if ($config['BPBC_CATEGORIES']) {
            $sql['categories'] = 'SELECT `id_country` FROM `'._DB_PREFIX_.'bpbc_categories` WHERE `id_category` = (SELECT `id_category_default` FROM `'._DB_PREFIX_.'product_shop` WHERE `id_product` = "'.(int)$id_product.'" AND `id_shop` = "'.(int)$context->shop->id.'")';
        }
        if ($config['BPBC_MANUFACTURERS']) {
            $sql['manufacturers'] = 'SELECT `id_country` FROM `'._DB_PREFIX_.'bpbc_manufacturers` WHERE `id_manufacturer` = (SELECT `id_manufacturer` FROM `'._DB_PREFIX_.'product` WHERE `id_product` = "'.(int)$id_product.'")';
        }
        if ($config['BPBC_SUPPLIERS']) {
            $sql['suppliers'] = 'SELECT `id_country` FROM `'._DB_PREFIX_.'bpbc_suppliers` WHERE `id_supplier` = (SELECT `id_supplier` FROM `'._DB_PREFIX_.'product` WHERE `id_product` = "'.(int)$id_product.'")';
        }
        if ($sql) {
            $sql = implode(' UNION ', $sql);
        } else {
            return array();
        }
        $sql = Db::getInstance()->executeS($sql);
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_country'];
            }
            return array_unique($return);
        }
        return array();
    }
    
    public static function getBlockedCountriesByProduct($id_product)
    {
        $sql = Db::getInstance()->executeS('SELECT `id_country` FROM `'._DB_PREFIX_.'bpbc_products` WHERE `id_product` = "'.(int)$id_product.'"');
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_country'];
            }
            return $return;
        }
        return array();
    }
    
    public function getBlockedCountriesByCategory($id_category)
    {
        $sql = Db::getInstance()->executeS('SELECT `id_country` FROM `'._DB_PREFIX_.'bpbc_categories` WHERE `id_category` = "'.(int)$id_category.'"');
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_country'];
            }
            return $return;
        }
        return array();
    }
    
    public function getBlockedCountriesByManufacturer($id_manufacturer)
    {
        $sql = Db::getInstance()->executeS('SELECT `id_country` FROM `'._DB_PREFIX_.'bpbc_manufacturers` WHERE `id_manufacturer` = "'.(int)$id_manufacturer.'"');
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_country'];
            }
            return $return;
        }
        return array();
    }
    
    public function getBlockedCountriesBySupplier($id_supplier)
    {
        $sql = Db::getInstance()->executeS('SELECT `id_country` FROM `'._DB_PREFIX_.'bpbc_suppliers` WHERE `id_supplier` = "'.(int)$id_supplier.'"');
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_country'];
            }
            return $return;
        }
        return array();
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name ||
            Tools::getValue('configure') == $this->name ||
            Tools::getValue('controller') == 'AdminProducts' ||
            Tools::getValue('controller') == 'AdminBlockProductByCategory' ||
            Tools::getValue('controller') == 'AdminBlockProductByManufacturer' ||
            Tools::getValue('controller') == 'AdminBlockProductBySupplier' ||
            Tools::getValue('controller') == 'AdminBlockProductByCountry') {
            // PS9: jQuery chargé par défaut (addJquery supprimé)
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
            $this->context->controller->addCSS($this->_path.'views/css/sweetalert.css');
            $this->context->controller->addJS($this->_path.'views/js/sweetalert.min.js');
            // PS9: jQuery chargé par défaut (addJquery supprimé)
            $this->context->controller->addCSS($this->_path.'views/css/multiselect.css');
            
            $all_label = $this->l('All countries');
            $selected_label = $this->l('Selected countries');
            if (Tools::getValue('controller') == 'AdminBlockProductByCountry') {
                $all_label = $this->l('All');
                $selected_label = $this->l('Selected');
            }
            
            Media::addJsDef(array(
                'products_link_title' => $this->l('Block products'),
                'products_link_message' => sprintf($this->l('Products can be blocked in their edit section, in the tab called "%s"'), $this->displayName),
                'search_label' => $this->l('Search...'),
                'multiselect_js_path' => $this->_path.'views/js/multiselect.js',
                'quicksearch_js_path' => $this->_path.'views/js/quicksearch.js',
                'multiselect_all_label' => $all_label,
                'multiselect_selected_label' => $selected_label,
            ));
        }
    }
    
    public function hookDisplayAdminProductsExtra($params)
    {
        if (empty($params['id_product'])) {
            $id_product = (int)Tools::getValue('id_product');
        } else {
            $id_product = (int)$params['id_product'];
        }
        $this->context->smarty->assign(array(
            'countries' => self::getProductsCountriesArray($id_product),
        ));
        if (Tools::substr(_PS_VERSION_, 0, 3) == '1.6') {
            $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/product_16.tpl');
        } else {
            $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/product.tpl');
        }
        return $output;
    }
    
    public function hookActionProductUpdate($params)
    {
        $id_product = (int)$params['id_product'];
        $blocked_countries = Tools::getValue('blocked_countries');
        
        return self::updateProduct($id_product, $blocked_countries);
    }
    
    public function hookDisplayHeader($params)
    {
        // PS9: jQuery est chargé par défaut, addJquery() a été supprimé
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addJS($this->_path.'/views/js/tooltipster.bundle.min.js');
        $this->context->controller->addCSS($this->_path.'/views/css/tooltipster.bundle.min.css');
        if ($id_product = (int)Tools::getValue('id_product')) {
            if (self::isProductBlocked($id_product)) {
                Media::addJsDef(array(
                    'bpbc_blocked' => true,
                    'bpbc_blocked_text' => $this->getBlockedText($id_product),
                ));
            }
        }
    }
    
    public function hookDisplayProductListReviews($params)
    {
        if (!empty($params['product']) && !empty($params['product']['blocked_country']) && !empty($params['product']['id_product'])) {
            $id_product = (int)$params['product']['id_product'];
            $this->context->smarty->assign(array(
                'bpbc_blocked_text' => $this->getBlockedText($id_product),
            ));
            return $this->context->smarty->fetch($this->local_path.'views/templates/hook/product-list.tpl');
        }
    }
    
    public function hookDisplayProductAdditionalInfo($params)
    {
        if (Tools::getValue('action') == 'quickview') {
            if (!empty($params['product']) && !empty($params['product']['blocked_country']) && !empty($params['product']['id_product'])) {
                $id_product = (int)$params['product']['id_product'];
                $this->context->smarty->assign(array(
                    'bpbc_blocked_text' => $this->getBlockedText($id_product),
                    'quickview' => true,
                ));
                return $this->context->smarty->fetch($this->local_path.'views/templates/hook/product-list.tpl');
            }
        }
    }
    
    public function getBlockedText($id_product = null)
    {
        $context = Context::getContext();
        $text = Configuration::get('BPBC_TEXT_BLOCKED', (int)$context->language->id);
        if (strpos($text, '%country%') !== false) {
            $id_country = self::getCountry();
            if (!$id_country) {
                $text = str_replace('%country%', $this->l('your country'), $text);
            } else {
                $country_name = Country::getNameById($this->context->language->id, $id_country);
                $text = str_replace('%country%', $country_name, $text);
            }
        }
        if (strpos($text, '%countries%') !== false) {
            $id_country = self::getCountry();
            if ($id_product) {
                $blocked_countries = self::getBlockedCountries($id_product);
                if (!$blocked_countries) {
                    $text = str_replace('%countries%', $this->l('your country'), $text);
                } else {
                    $countries_name = self::getCountriesName($blocked_countries);
                    $text = str_replace('%countries%', implode(', ', $countries_name), $text);
                }
            } else {
                if (!$id_country) {
                    $text = str_replace('%countries%', $this->l('your country'), $text);
                } else {
                    $country_name = Country::getNameById($this->context->language->id, $id_country);
                    $text = str_replace('%countries%', $country_name, $text);
                }
            }
        }
        return $text;
    }
    
    public static function getCountriesName($countries, $id_lang = null)
    {
        if (is_null($id_lang)) {
            $context = Context::getContext();
            $id_lang = $context->language->id;
        }
        $sql = Db::getInstance()->executeS('SELECT `name` FROM `'._DB_PREFIX_.'country_lang` WHERE `id_country` IN ('.pSQL(implode(', ', $countries)).') AND `id_lang` = "'.(int)$id_lang.'" ORDER BY `name` ASC');
        $return = array();
        if ($sql) {
            foreach ($sql as $row) {
                $return[] = $row['name'];
            }
        }
        return $return;
    }
    
    public static function getCountry()
    {
        $context = Context::getContext();
        if (!empty($context->country)) {
            if (!empty($context->country->id)) {
                return $context->country->id;
            }
        }
        return false;
    }
    
    public static function updateCategoryRules($id_category, $selected_countries = array())
    {
        Db::getInstance()->delete('bpbc_categories', '`id_category` = "'.(int)$id_category.'"');
        if ($selected_countries) {
            $array = array();
            foreach ($selected_countries as $id_country) {
                $array[] = array(
                    'id_category' => (int)$id_category,
                    'id_country' => (int)$id_country,
                );
            }
            if ($array) {
                return Db::getInstance()->insert('bpbc_categories', $array);
            }
        }
        return true;
    }
    
    public static function updateManufacturerRules($id_manufacturer, $selected_countries = array())
    {
        Db::getInstance()->delete('bpbc_manufacturers', '`id_manufacturer` = "'.(int)$id_manufacturer.'"');
        if ($selected_countries) {
            $array = array();
            foreach ($selected_countries as $id_country) {
                $array[] = array(
                    'id_manufacturer' => (int)$id_manufacturer,
                    'id_country' => (int)$id_country,
                );
            }
            if ($array) {
                return Db::getInstance()->insert('bpbc_manufacturers', $array);
            }
        }
        return true;
    }
    
    public static function updateSupplierRules($id_supplier, $selected_countries = array())
    {
        Db::getInstance()->delete('bpbc_suppliers', '`id_supplier` = "'.(int)$id_supplier.'"');
        if ($selected_countries) {
            $array = array();
            foreach ($selected_countries as $id_country) {
                $array[] = array(
                    'id_supplier' => (int)$id_supplier,
                    'id_country' => (int)$id_country,
                );
            }
            if ($array) {
                return Db::getInstance()->insert('bpbc_suppliers', $array);
            }
        }
        return true;
    }
    
    public static function updateCountryRules($id_country, $selected = array())
    {
        Db::getInstance()->delete('bpbc_products', '`id_country` = "'.(int)$id_country.'"');
        if (!empty($selected['products'])) {
            $array = array();
            foreach ($selected['products'] as $id_product) {
                $array[] = array(
                    'id_country' => (int)$id_country,
                    'id_product' => (int)$id_product,
                );
            }
            Db::getInstance()->insert('bpbc_products', $array);
        }
        Db::getInstance()->delete('bpbc_categories', '`id_country` = "'.(int)$id_country.'"');
        if (!empty($selected['categories'])) {
            $array = array();
            foreach ($selected['categories'] as $id_category) {
                $array[] = array(
                    'id_country' => (int)$id_country,
                    'id_category' => (int)$id_category,
                );
            }
            Db::getInstance()->insert('bpbc_categories', $array);
        }
        Db::getInstance()->delete('bpbc_manufacturers', '`id_country` = "'.(int)$id_country.'"');
        if (!empty($selected['manufacturers'])) {
            $array = array();
            foreach ($selected['manufacturers'] as $id_manufacturer) {
                $array[] = array(
                    'id_country' => (int)$id_country,
                    'id_manufacturer' => (int)$id_manufacturer,
                );
            }
            Db::getInstance()->insert('bpbc_manufacturers', $array);
        }
        Db::getInstance()->delete('bpbc_suppliers', '`id_country` = "'.(int)$id_country.'"');
        if (!empty($selected['suppliers'])) {
            $array = array();
            foreach ($selected['suppliers'] as $id_supplier) {
                $array[] = array(
                    'id_country' => (int)$id_country,
                    'id_supplier' => (int)$id_supplier,
                );
            }
            Db::getInstance()->insert('bpbc_suppliers', $array);
        }
        return true;
    }
    
    public function getConfigureTpl()
    {
        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'links' => $this->getAdminLinks(),
        ));

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        
        return $output;
    }
    
    public static function getProductsArray($id_lang = null)
    {
        if (is_null($id_lang)) {
            $context = Context::getContext();
            $id_lang = $context->language->id;
        }
        $return = Db::getInstance()->executeS('SELECT p.`id_product`, CONCAT("#", p.`id_product`, " - ", p.`reference`, " - ", pl.`name`) as `name` FROM `'._DB_PREFIX_.'product` p LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = "'.(int)$id_lang.'") GROUP BY p.`id_product`');
        return $return;
    }
    
    public static function getCategoriesArray($id_lang = null)
    {
        if (is_null($id_lang)) {
            $context = Context::getContext();
            $id_lang = $context->language->id;
        }
        $return = Db::getInstance()->executeS('SELECT c.`id_category`, CONCAT("#", c.`id_category`, " - ", cl.`name`) as `name` FROM `'._DB_PREFIX_.'category` c LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = c.`id_category` AND cl.`id_lang` = "'.(int)$id_lang.'") GROUP BY c.`id_category`');
        return $return;
    }
    
    public static function getManufacturersArray()
    {
        $return = Db::getInstance()->executeS('SELECT m.`id_manufacturer`, CONCAT("#", m.`id_manufacturer`, " - ", m.`name`) as `name` FROM `'._DB_PREFIX_.'manufacturer` m');
        return $return;
    }
    
    public static function getSuppliersArray()
    {
        $return = Db::getInstance()->executeS('SELECT s.`id_supplier`, CONCAT("#", s.`id_supplier`, " - ", s.`name`) as `name` FROM `'._DB_PREFIX_.'supplier` s');
        return $return;
    }
    
    public static function getBlockedProductsByCountry($id_country)
    {
        $sql = Db::getInstance()->executeS('SELECT `id_product` FROM `'._DB_PREFIX_.'bpbc_products` WHERE `id_country` = "'.(int)$id_country.'"');
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_product'];
            }
            return $return;
        }
        return array();
    }
    
    public static function getBlockedCategoriesByCountry($id_country)
    {
        $sql = Db::getInstance()->executeS('SELECT `id_category` FROM `'._DB_PREFIX_.'bpbc_categories` WHERE `id_country` = "'.(int)$id_country.'"');
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_category'];
            }
            return $return;
        }
        return array();
    }
    
    public static function getBlockedManufacturersByCountry($id_country)
    {
        $sql = Db::getInstance()->executeS('SELECT `id_manufacturer` FROM `'._DB_PREFIX_.'bpbc_manufacturers` WHERE `id_country` = "'.(int)$id_country.'"');
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_manufacturer'];
            }
            return $return;
        }
        return array();
    }
    
    public static function getBlockedSuppliersByCountry($id_country)
    {
        $sql = Db::getInstance()->executeS('SELECT `id_supplier` FROM `'._DB_PREFIX_.'bpbc_suppliers` WHERE `id_country` = "'.(int)$id_country.'"');
        if ($sql) {
            $return = array();
            foreach ($sql as $row) {
                $return[] = (int)$row['id_supplier'];
            }
            return $return;
        }
        return array();
    }
}
