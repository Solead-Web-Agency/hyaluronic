<?php
/**
 * The file is controller. Do not modify the file if you want to upgrade the module in future
 * 
 * @author    Globo Jsc <contact@globosoftware.net>
 * @copyright 2020 Globo., Jsc
 * @license   please read license in file license.txt
 * @link	     http://www.globosoftware.net
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'g_upsellpro/classes/AdminSettings.php');
include_once(_PS_MODULE_DIR_ . 'g_upsellpro/classes/admin/GupselloffersModel.php');

class G_upsellpro extends Module
{
    public function __construct()
    {
        $this->name = 'g_upsellpro';
        $this->tab = 'front_office_features';
        $this->version = '1.0.3';
        $this->author = 'Globo Jsc';
        $this->need_instance = 0;
        $this->bootstrap = 1;
        $this->secure_key = Tools::hash($this->name);
        $this->module_key = 'b70d1cc3897215283c620ea0343ddd10';
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')){
            parent::__construct();
        }
        $this->displayName = $this->l('Cart upsell funnels, Cross Sell, Upsell Bundle');
        $this->description = $this->l('Increase shops’ sales and revenues rapidly by: Bundle product, Cross Selling, Post purchase upsell funnels, Volume Discount, Frequently bought together.');
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')){
            parent::__construct();
        }
    }
    public function install()
    {
        if (Shop::isFeatureActive()){
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        return parent::install()
        && $this->registerHook('displayHeader')
        && $this->registerHook("displayFooter")
        && $this->registerHook('displayFooterProduct')
        && $this->registerHook('displayRightColumnProduct')
        && $this->registerHook('displayReassurance')
        && $this->registerHook('displayContentWrapperBottom')
        && $this->registerHook('displayBackOfficeHeader')
        && $this->registerHook('actionProductSave')
        && $this->registerHook('actionAdminControllerSetMedia')
        && $this->registerHook('actionObjectProductInCartDeleteAfter')
        && $this->registerHook('actionAfterDeleteProductInCart')
        && $this->registerHook('shoppingCart')
        && $this->registerHook('displayHome')
        && $this->registerHook('actionValidateOrder')
        && $this->registerHook('displayGupsellpro')
        && $this->_createTab()
        && $this->_createTables()
        && $this->_installConfigData();
    }
    public function uninstall()
    {
        return parent::uninstall()
        && $this->unregisterHook("displayHeader")
        && $this->unregisterHook("displayFooter")
        && $this->unregisterHook("displayFooterProduct")
        && $this->unregisterHook("displayRightColumnProduct")
        && $this->unregisterHook("displayReassurance")
        && $this->unregisterHook("displayBackOfficeHeader")
        && $this->unregisterHook("displayContentWrapperBottom")
        && $this->unregisterHook('actionProductSave')
        && $this->unregisterHook('actionAdminControllerSetMedia')
        && $this->unregisterHook('actionObjectProductInCartDeleteAfter')
        && $this->unregisterHook('actionAfterDeleteProductInCart')
        && $this->unregisterHook('shoppingCart')
        && $this->unregisterHook('displayHome')
        && $this->unregisterHook('actionValidateOrder')
        && $this->unregisterHook('displayGupsellpro')

        && $this->_removeTabs()
        && $this->_removeTables();
    }
    /*add tabs module*/
    private function _createTab()
    {
        $res = true;
        $tabparent = "AdminGloboupsell";
        $id_parent = Tab::getIdFromClassName($tabparent);
        if(!$id_parent){
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminGloboupsell";
            $tab->name = array();
            foreach (Language::getLanguages(false) as $lang){
                $tab->name[$lang["id_lang"]] = "Upsell Pro";
            }
            $tab->id_parent = 0;
            $tab->module = $this->name;
            $res &= $tab->add();
            $id_parent = (int)$tab->id;
        }
        $subtabs = array(
            array(
                'class'=>'AdminGupsellanalytics',
                'name'=>$this->l('Dashboard'),
                'glid_parent'=> $id_parent
            ),
            array(
                'class'=>'AdminGupselloffers',
                'name'=>$this->l('Offers'),
                'glid_parent'=>$id_parent
            ),
            array(
                'class'=>'AdminGupsellsettings',
                'name'=>$this->l('Settings'),
                'glid_parent'=> $id_parent
            ),
            array(
                'class'=>'AdminGupsellfaqs',
                'name'=>$this->l('FAQs'),
                'glid_parent'=>$id_parent
            ),
        );
        foreach($subtabs as $subtab){
            $idtab = (int)Tab::getIdFromClassName($subtab['class']);
            if($idtab <= 0){
                $tab = new Tab();
                $tab->active = 1;
                $tab->class_name = $subtab['class'];
                $tab->name = array();
                foreach (Language::getLanguages(false) as $lang){
                    $tab->name[(int)$lang["id_lang"]] = $subtab['name'];
                }
                $tab->id_parent = (int)$subtab['glid_parent'];
                $tab->module = $this->name;
                $res &= $tab->add();
            }
        }
        return $res;
    }
    /*remove tabs module*/
    public function _removeTabs()
    {
        $list_tab = array('AdminGupsellsettings','AdminGupselloffers','AdminGupsellanalytics','AdminGupsellfaqs');
        foreach($list_tab as $id_tab){
            $id_tab = (int)Tab::getIdFromClassName($id_tab);
            if ($id_tab)
            {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
        /* Tab Parent .*/
        $gidtabsP = (int)Tab::getIdFromClassName('AdminGloboupsell');
        if ($gidtabsP){
            $tabP = new Tab($gidtabsP);
            $tabP->delete();
        }
        /*#Tab Parent*/
        return true;
    }
    
    public function _installConfigData()
    {
        $res = true;
        $shop_groups_list = array();
		$shops = Shop::getContextListShopID();
        $shop_context = Shop::getContext();
        $languages = Language::getLanguages(false);
        $gupsell_button_adcart = array();
        $gupsell_button_upgrade = array();
        $gupsell_total = array();
        $gupsell_checkout = array();
        $gupsell_updatecart = array();
        $gupsell_nothanks = array();
        $gupsell_floating = array();
        $gupsell_label_mostpopular = array();
        foreach ($languages as $lang) {
            $gupsell_total[(int)$lang['id_lang']] = $this->l('Total');
            $gupsell_button_adcart[(int)$lang['id_lang']] = $this->l('Add to cart');
            $gupsell_button_upgrade[(int)$lang['id_lang']] = $this->l('Update to cart');
            $gupsell_checkout[(int)$lang['id_lang']] = $this->l('Checkout');
            $gupsell_updatecart[(int)$lang['id_lang']] = $this->l('Upgrade Cart');
            $gupsell_nothanks[(int)$lang['id_lang']] = $this->l('No, Thanks');
            $gupsell_floating[(int)$lang['id_lang']] = $this->l('Reveal Offers');
            $gupsell_label_mostpopular[(int)$lang['id_lang']] = $this->l('Most Popular');
        }
        $configs = array(
            'GSELL_MAIN_FLOATING_POSITION' => array('validate'=> 'text', 'value' => 'right'),
            'GSELL_MAIN_POPUP_DELAY' => array('validate'=> 'int', 'value' => '1'),
            'GSELL_MAIN_BUTTON_BACKGROUND' => array('validate'=> 'text', 'value' => '#ffffff'),
            'GSELL_MAIN_BUTTON_COLOR' => array('validate'=> 'text', 'value' => '#333'),
            'GSELL_MAIN_LABEL' => array('validate'=> 'int', 'value' => 'center'),
            'GSELL_SETTING_BUTTON_TOTAL' => array('validate'=> '', 'value' => $gupsell_total),
            'GSELL_SETTING_BUTTON_ADCART' => array('validate'=> '', 'value' => $gupsell_button_adcart),
            'GSELL_SETTING_BUTTON_UPGRADE' => array('validate'=> '', 'value' => $gupsell_button_upgrade),
            'GSELL_SETTING_BUTTON_CHECKOUT' => array('validate'=> '', 'value' => $gupsell_checkout),
            'GSELL_SETTING_BUTTON_UPGRADECART' => array('validate'=> '', 'value' => $gupsell_updatecart),
            'GSELL_SETTING_BUTTON_NOTHANKS' => array('validate'=> '', 'value' => $gupsell_nothanks),
            'GSELL_SETTING_BUTTON_FLOATING' => array('validate'=> '', 'value' => $gupsell_floating),
            'GSELL_SETTING_MOST_POPULAR' => array('validate'=> '', 'value' => $gupsell_label_mostpopular),
            'GSELL_SETTING_CUSTOM_CSS' => array('validate'=> '', 'value' => ''),
            'GSELL_ANALYTIC_TIME' => array('validate'=> '', 'value' => '30'),
            'GSELL_MAIN_MOSTPOPOLAR_BACKGROUND' => array('validate'=> 'text', 'value' => '#C71F5A'),
            'GSELL_MAIN_MOSTPOPOLAR_COLOR' => array('validate'=> 'text', 'value' => '#ffffff'),
            'GSELL_MAIN_MOSTPOPOLAR_BACKGROUND_ITEM' => array('validate'=> 'text', 'value' => '#FFDB75'),
        );
        foreach($configs as $key=>$config) {
            $res &= Configuration::updateValue($key, $config['value']);
            foreach ($shops as $shop_id)
            {
                $shop_group_id = (int)Shop::getGroupFromShop((int)$shop_id, true);
                if (!in_array($shop_group_id, $shop_groups_list))
                    $shop_groups_list[] = (int)$shop_group_id;
                $res &= Configuration::updateValue($key, $config['value'], false, (int)$shop_group_id, (int)$shop_id);
                /* Update global shop context if needed*/
                switch ($shop_context)
                {
                    case Shop::CONTEXT_ALL:
                        $res &= Configuration::updateValue($key, $config['value']);
                        if (count($shop_groups_list))
                        {
                            foreach ($shop_groups_list as $shop_group_id)
                            {
                                $res &= Configuration::updateValue($key, $config['value'], false, (int)$shop_group_id);
                            }
                        }
                        break;
                    case Shop::CONTEXT_GROUP:
                        if (count($shop_groups_list))
                        {
                            foreach ($shop_groups_list as $shop_group_id)
                            {
                                $res &= Configuration::updateValue($key, $config['value'], false, (int)$shop_group_id);
                            }
                        }
                        break;
                }
            }
        }
        return $res;
    }
    public function _createTables()
    {
        $res = true;
        $sqls = array();
        /* upsell list config page  */
        $sqls[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'g_upsellrule` (
                `id_g_upsellrule` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `showin_product` tinyint(1) unsigned NULL DEFAULT  "1",
                `showin_cart` tinyint(1) unsigned NULL DEFAULT  "1",
                `showin_cart_popup` tinyint(1) unsigned NULL DEFAULT  "1",
                `showin_home` tinyint(1) unsigned NULL DEFAULT  "1",
                `showin_collection` tinyint(1) unsigned NULL DEFAULT  "1",
                `qty` int(10) NULL,
                `apply_discount` tinyint(1) unsigned NULL DEFAULT  "0",
                `type_discount` tinyint(1) unsigned NULL DEFAULT  "0",
                `amount_discount` float(10) NULL,
                `id_currency_discount` int(10) NULL,
                `tax_discount` int(10) NULL,
                `display_product` varchar(255) NULL,
                `display_cateids` text NULL,
                `showoffers` tinyint(1) unsigned NULL DEFAULT  "0",
                `remove_product` tinyint(1) unsigned NULL DEFAULT  "0",
                `remove_product_upsell` tinyint(1) unsigned NULL DEFAULT  "0",
                `display_customqty` tinyint(1) unsigned NULL DEFAULT  "0",
                `type_price` tinyint(1) unsigned NULL DEFAULT  "0",
                `customshortcode` varchar(255) NULL,
                `position` int(10) NULL,
                `minimum_amount` text NULL,
                `maximum_amount` text NULL,
                `product_ids` text NULL,
                `product_combin_ids` text NULL,
                `product_displayids` text NULL,
                `product_displaycombin_ids` text NULL,
                `dateadd` datetime NULL,
                `dateup` datetime NULL,
                PRIMARY KEY (`id_g_upsellrule`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';
        $sqls[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'g_upsellrule_lang` (
                    `id_g_upsellrule` int(10) unsigned NOT NULL,
                    `id_lang` int(10) unsigned NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `title` varchar(255) NOT NULL,
                    `description` varchar(255) NOT NULL,
                    `button_popupname` varchar(255) NULL,
                    PRIMARY KEY (`id_g_upsellrule`,`id_lang`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';
        $sqls[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'g_upsellrule_shop` (
                `id_g_upsellrule` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_g_upsellrule`,`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';
        $sqls[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'g_upsellproductcombin` (
                `id_g_upsellproductcombin` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_g_upsellrule` int(10) unsigned NULL,
                `id_product` int(10) NULL,
                `id_combin` int(10) NULL,
                `product_display` varchar(255) NULL,
                `mostpopular` tinyint(1) unsigned NULL DEFAULT  "0",
                `id_shop` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_g_upsellproductcombin`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';
        $sqls[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'g_upsellanytic` (
                `id_g_upsellanytic` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_g_upsellrule` int(10) unsigned NULL,
                `views` int(10) NULL,
                `addcarts` int(10) NULL,
                `transactions` int(10) NULL,
                `sales` float NULL,
                `take_rate` int(10) NULL,
                `date` date NULL,
                `id_shop` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_g_upsellanytic`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';
        $sqls[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'g_upsellcart` (
                `id_g_upsellcart` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_g_upsellrule` int(10) unsigned NULL,
                `id_cart` int(10) NULL,
                `id_product` int(10) NULL,
                `id_combin` int(10) NULL,
                `id_shop` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_g_upsellcart`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';
        foreach($sqls as $sql){
            $res &=Db::getInstance()->execute($sql);
        }
        return (bool)$res;
    }
    public function _removeTables()
    {
        return (bool)Db::getInstance()->execute('
                DROP TABLE IF EXISTS    `' . _DB_PREFIX_ . 'g_upsellrule`,
                                        `' . _DB_PREFIX_ . 'g_upsellrule_lang`,
                                        `' . _DB_PREFIX_ . 'g_upsellrule_shop`,
                                        `' . _DB_PREFIX_ . 'g_upsellproductcombin`,
                                        `' . _DB_PREFIX_ . 'g_upsellanytic`,
                                        `' . _DB_PREFIX_ . 'g_upsellcart`
                                        ;
        ');
    }
    public function hookActionAdminControllerSetMedia()
    {
        $controller = Tools::getValue('controller');
        if ($controller == "AdminGupsellsettings"
        || $controller == "AdminGupselloffers"
        || $controller == "AdminGupsellanalytics"
        || $controller == "AdminGupsellfaqs"){
            $this->context->controller->addCSS(_THEME_DIR_.'js/jquery/plugins/autocomplete/jquery.autocomplete.css');
            $this->context->controller->addJqueryPlugin('autocomplete');
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/admin/adminupsell.css');
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/admin/adminupsell.js');
            if ($controller == "AdminGupsellanalytics") {
                $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/admin/nv.d3.css');
                $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/admin/d3.v3.min.js');
                $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/admin/nv.d3.min.js');
            }
            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->addJqueryPlugin('colorpicker');
            if (!file_exists(_PS_JS_DIR_.'tiny_mce/tiny_mce.js')) {
                $this->context->controller->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
            }else{
                $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/admin/tinymce.inc.js');
            }
            if (!file_exists(_PS_JS_DIR_.'admin/tinymce.inc.js')) {
                $this->context->controller->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
            }else{
                $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/admin/tinymce.inc.js');
            }
            if ($controller == "AdminGupselloffers") {
                $controller = Tools::strtolower(Tools::getValue('controller'));
                $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/front/owl.carousel.min.css');
                $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/front/owl.carousel.min.js');
                $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/front/owl.theme.default.min.css');
                $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/admin/gupsellajax.js');
                $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/admin/gupsellpro.css');
                $this->context->controller->addCSS(_PS_MODULE_DIR_.$this->name.'/views/css/admin/mycss.css');
            }
        }
        return true;
    }
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/front/owl.carousel.min.css');
        $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/front/owl.theme.default.min.css');
        $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/front/owl.carousel.min.js');
        $this->context->controller->addJqueryPlugin('fancybox');
        $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/front/gupsellpro.css');
        $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/front/gupsellpro.js');
        
        $css_files = Tools::scandir(_PS_MODULE_DIR_.$this->name.'/views/css/front/frontcss', 'css');
        if (!empty($css_files)) {
            foreach ($css_files as $css_file) {
                $this->context->controller->addCSS(_PS_MODULE_DIR_.$this->name.'/views/css/front/frontcss/'.$css_file);
            }
        }
    }
    public function hookDisplayContentWrapperBottom($params)
    {
        $cat_id = Tools::getValue('id_category');
        if ($cat_id > 0) {
            return $this->GEThtmlproductsshow(array(0),false,'showin_collection');
        }
    }
    public function hookDisplayReassurance($params)
    {
        if ((int)Tools::getValue('id_product') > 0) {
            return $this->GEThtmlproductsshow(array(0),false,'showin_product');
        }
    }
    public function hookDisplayHome($params)
    {
        return $this->GEThtmlproductsshow(array(0),false,'showin_home');
    }
    public function hookShoppingCart($params)
    {
        return $this->GEThtmlproductsshow(array(0),false,'showin_cart');
    }
    public function GEThtmlproductsshow($checkproductids = array(), $ajax=false, $page = '') {
        $id_shop = (int)$this->context->shop->id;
        $id_lang = (int)$this->context->language->id;
        $popup_shows = array();
        $floating_shows = array();
        $ajax;
        $html ='';
        $upsells = GupselloffersModel::getUpselldisplay($page,0, (int)$id_lang,(int)$id_shop);
        if (isset($upsells) && !empty($upsells)) {
            foreach ($upsells as $upsell) {
                $upsellObj = new GupselloffersModel((int)$upsell['id_g_upsellrule'], (int)$id_lang,(int)$id_shop);
                $productids = GupselloffersModel::getProductcombinidproduct((int)$upsellObj->id_g_upsellrule, 'display', $id_shop);
                $html .= $this->getUpsellProductHtml($productids, $checkproductids,$page,$upsellObj, $ajax, $popup_shows, $floating_shows,$id_shop);
            }
        }
        $this->context->smarty->assign(array(
            'popup_shows' => Tools::jsonEncode($popup_shows),
            'floating_shows' => Tools::jsonEncode($floating_shows),
            'getConfigFieldsValues' => $this->getConfigFieldsValues(),
            'page_name' => Dispatcher::getInstance()->getController(),
        ));
        $html .= $this->display(__file__, '/views/templates/hook/jsoninput.tpl');
        return $html;
    }
    public function getUpsellProductHtml($productids=array(), $checkproductids=array(),$showin='showin_cart',$upsellObj, $ajxcal=false, &$popup_shows, &$floating_shows, $id_shop) {
        $id_lang = (int)$this->context->language->id;
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = Shop::getContextShopGroupID();
        $id_cart = (int)$this->context->cart->id;
        $total_cart = 0;
        $product_carts = array();
        $tpl = '';
        $html = '';
        $gupselltotalprice = 0;
        $gupselltotalpricediscount = 0;
        $check_conditionupsell = false;
        $product_volumes = array();
        $_show_fields = $this->getValueConfigshowin((int)$upsellObj->id_g_upsellrule, $showin, $id_shop_group, $id_shop);
        
        switch ($showin) {
            case 'showin_product':
                $check_conditionupsell = $this->checkProductIdInCart($id_cart, $productids, $upsellObj, $id_shop);
                break;
            case 'showin_cart':
                $check_conditionupsell = $this->checkProductIdInCart($id_cart, $productids, $upsellObj, $id_shop);
            break;
            case 'showin_cart_popup':
                /*check  option display product*/
                switch ($upsellObj->display_product) {
                    case 'specific_product':
                        if ($productids && $checkproductids) {
                            if (in_array($checkproductids['id_product'], $productids) && in_array($checkproductids['ipa'], $checkproductids)) {
                                $check_conditionupsell = true;
                            }
                        }
                        break;
                    case 'collections_product':
                        if ($productids || $checkproductids) {
                            $productObj = new Product((int)$checkproductids['id_product']);
                            /* $value = $productObj->getDefaultCategory();*/
                            $cates = $productObj->getCategories();
                            $categorys = $categorys = explode(',',$upsellObj->display_cateids);
                            if ($cates) {
                                foreach($cates as $cate) {
                                    if (in_array($cate, $categorys)) {
                                        $check_conditionupsell = true;
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    default:
                        $check_conditionupsell = true;
                }
                /*end*/
            break;
            case 'showin_home':
                $check_conditionupsell = $this->checkProductIdInCart($id_cart, $productids, $upsellObj, $id_shop);
            break;
            case 'showin_collection':
                $check_conditionupsell = $this->checkProductIdInCart($id_cart, $productids, $upsellObj, $id_shop);
            break;
            default:
                $check_conditionupsell = false;
        }
        /*new version check product incart*/
        $productcarts = $this->context->cart->getProducts();
        $productidchecksextra = GupselloffersModel::getProductcombinidproduct((int)$upsellObj->id_g_upsellrule, '', $id_shop);
        if ($productcarts && (int)$upsellObj->showoffers == 1) {
            foreach($productcarts as $productextra ) {
                if (in_array($productextra['id_product'], $productidchecksextra)) {
                    $check_conditionupsell = false;
                    break;
                }
            }
        }
        if ($check_conditionupsell) {
            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
            $gupsellvolumeprices = array();
            $showproductids = GupselloffersModel::getProductcombinidproduct((int)$upsellObj->id_g_upsellrule, '', $id_shop);
            $mostpopular    = GupselloffersModel::getProductmostpopular((int)$upsellObj->id_g_upsellrule, '', $id_shop);
            /*update anytic*/
            $date = date('Y-m-d');
            $getConfigFieldsValues = $this->getConfigFieldsValues();
            $views = (int)AdminSettings::getTotalFieldsanytic('views',(int)$upsellObj->id_g_upsellrule,$date, $date,$id_shop);
            AdminSettings::upsellanytic((int)$upsellObj->id_g_upsellrule, $views + 1, 0, 0, 0, 0, $date, $id_shop);
            $version = '';
            if(version_compare(_PS_VERSION_, '1.7.0.0 ', '>=')){
                $version = '17';
            }
            switch ($_show_fields[$showin.'_type']) {
                case 'normal':
                    $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
                break;
                case 'bundle':
                    $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
                break;
                case 'volume':
                    $product_volumes = Tools::jsonDecode($_show_fields[$showin.'_addition'], true);
                    $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
                    switch ($showin) {
                        case 'showin_product':
                            $showproductids = array((int)Tools::getValue('id_product'));
                            break;
                        case 'showin_cart':
                            $sql = ' SELECT `id_product` FROM `'._DB_PREFIX_.'cart_product`  WHERE `id_cart` = '.(int)$id_cart.' ORDER BY rand()';
                            $id_product = Db::getInstance()->getValue($sql);
                            $showproductids = array((int)$id_product);
                        break;
                        case 'showin_cart_popup':
                            $sql = ' SELECT `id_product` FROM `'._DB_PREFIX_.'cart_product`  WHERE `id_cart` = '.(int)$id_cart.' ORDER BY rand()';
                            $id_product = Db::getInstance()->getValue($sql);
                            $showproductids = array((int)$id_product);
                        break;
                        case 'showin_home':
                            $sql = ' SELECT `id_product` FROM `'._DB_PREFIX_.'cart_product`  WHERE `id_cart` = '.(int)$id_cart.' ORDER BY rand()';
                            $id_product = Db::getInstance()->getValue($sql);
                            $showproductids = array((int)$id_product);
                        break;
                        case 'showin_collection':
                            $id_category = (int)Tools::getValue('id_category');
                            $id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT cp.`id_product` as id  FROM `'._DB_PREFIX_.'category_product` cp WHERE cp.`id_category` = '.(int)$id_category.' ORDER BY rand()');
                            $showproductids = array((int)$id_product);
                        break;
                        default:
                            $showproductids = array(0);
                    }
                break;
                case 'frequently':
                    $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
                break;
                default:
                    $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
            }
            
            $gupsellproducts = $this->getProductsProperties($upsellObj, $showproductids, 0, $gupselltotalprice, $gupsellvolumeprices, $gupselltotalpricediscount, $id_lang, $id_shop, 0, false, $product_volumes, $_show_fields[$showin.'_type'], $showin);
            $count_pro = $this->getProductsProperties($upsellObj, $showproductids, 0, $gupselltotalprice, $gupsellvolumeprices,  $gupselltotalpricediscount,$id_lang, $id_shop, 0, true, $product_volumes, $_show_fields[$showin.'_type'], $showin);
            $gupselltotalpricenew = $gupselltotalprice;
            $amountdiscount = 0;
            if ($upsellObj->apply_discount == 1) {
                if ($upsellObj->type_discount != 1) {
                    $amountdiscount = (float)Tools::convertPrice($upsellObj->amount_discount, $upsellObj->id_currency_discount, (int)$this->context->currency->id);
                    $gupselltotalpricenew = $gupselltotalprice - (float)$gupselltotalpricediscount;
                } else {
                    $amountdiscount = (float)$upsellObj->amount_discount;
                    $gupselltotalpricenew = $gupselltotalprice  - $gupselltotalpricediscount;
                }
            }
            $version17 = false;
            if(version_compare(_PS_VERSION_, '1.7.0.0 ', '>='))
                $version17 = true;
            if($id_cart > 0) {
                $total_cart = $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
                $product_carts = $this->context->cart->getProducts(true);
            }
            if ($_show_fields[$showin.'_stype'] =='popup') {
                $popup_shows[] = (int)$upsellObj->id_g_upsellrule;
            } elseif ($_show_fields[$showin.'_stype'] =='floating') {
                $floating_shows[] = (int)$upsellObj->id_g_upsellrule;
            }
            $this->context->smarty->assign(array(
                'html_template' => $_show_fields[$showin.'_stype'],
                'id_shop' => $id_shop,
                'id_lang' => $id_lang,
                'link'    => $this->context->link,
                'gupsellproducts' => $gupsellproducts,
                'count_product'   => $count_pro,
                'upsellObj' => $upsellObj,
                'cart_token'=>Tools::getToken(false),
                'discountactive'=>Tools::getToken(false),
                'ajaxcal'   => $ajxcal,
                'id_currency'          => (int)$this->context->currency->id,
                'gupselltotalprice'    => Tools::convertPriceFull($gupselltotalprice),
                'gupselltotalpricenew' => Tools::convertPriceFull($gupselltotalpricenew),
                'amountdiscount' => $amountdiscount,
                'token'          => Tools::getToken(false),
                'urlajaxmodule'  => $this->context->link->getModuleLink($this->name, 'gupsellpro'),
                'getConfigFieldsValues' => $getConfigFieldsValues,
                'controller'   => Tools::getValue('controller'),
                'priceDisplay' => $priceDisplay,
                'version17'    => $version17,
                'total_cart'   => Tools::displayPrice($total_cart, (int)$this->context->currency->id),
                'product_carts' => $product_carts,
                'mostpopular'   => $mostpopular,
                'volumes'      => $product_volumes,
                'showin'       => $showin,
                'gupsellvolumeprices' => $gupsellvolumeprices,
                'gupselltotalpricediscount' => $gupselltotalpricediscount,
                'cart_show' => $this->context->link->getPageLink(
                    'cart',
                    null,
                    $this->context->language->id,
                    array(
                        'action' => 'show'
                    ),
                    false,
                    null,
                    true
                ),
            ));
            $html .= $this->display(__file__, '/views/templates/hook/'.$tpl);
        }
        return $html;
    }
    
    public function getValueConfigshowin($id_g_upsellrule, $name, $id_shop_group, $id_shop) 
    {
        $_show_fields = array();
        $_show_fields[$name.'_type'] = Tools::getValue($name.'_type', Configuration::get('gupsell_type_'.$name.'_'.$id_g_upsellrule, null, $id_shop_group, $id_shop));
        $_show_fields[$name.'_stype'] = Tools::getValue($name.'_stype', Configuration::get('gupsell_stype_'.$name.'_'.$id_g_upsellrule, null, $id_shop_group, $id_shop));
        $_show_fields[$name.'_position'] = Tools::getValue($name.'_position', Configuration::get('gupsell_position_'.$name.'_'.$id_g_upsellrule, null, $id_shop_group, $id_shop));
        $_show_fields[$name.'_addition'] = Tools::getValue($name.'_addition', Configuration::get('gupsell_addition_'.$name.'_'.$id_g_upsellrule, null, $id_shop_group, $id_shop));
        return $_show_fields;
    }

    public function checkProductIdInCart($id_cart =  0, $productids=array(), $upsellObj, $id_shop)
    {
        if($id_cart <= 0) return array();
        $sql = 'SELECT distinct `id_cart` FROM `' . _DB_PREFIX_ . 'cart_product`
            WHERE id_cart = '.(int)$id_cart;
        switch ($upsellObj->display_product) {
            case 'specific_product':
                if ($productids) {
                    $sql .= ' AND (';
                    foreach ($productids as $kstart => $productid) {
                        $productcombieids = GupselloffersModel::getProductcombininproductid((int)$upsellObj->id_g_upsellrule, $productid, 'display', $id_shop);
                        $productcombieids = implode(',', $productcombieids);
                        if ($kstart==0) {
                            $sql .= '(`id_product`= '.(int)$productid.' AND `id_product_attribute` IN ('.pSql($productcombieids ? $productcombieids: '0').'))';
                        } else {
                            $sql .= ' OR (`id_product`= '.(int)$productid.' AND `id_product_attribute` IN ('.pSql($productcombieids ? $productcombieids: '0').'))';
                        }
                    }
                    $sql .= ') ';
                }
                break;
            case 'collections_product':
                $sql .= ' AND `id_product` IN (
                    SELECT distinct id_product FROM `'._DB_PREFIX_.'category_product` WHERE id_category IN ('.pSql($upsellObj->display_cateids).')
                ) ';
                break;
            default:
                $sql .='';
        }
        $newid_cart = (int)Db::getInstance()->getValue($sql);
        return (int)$newid_cart;
    }
    public function getProductsProperties($upsellObj, $productids, $id_product_attribute = 0, &$gupselltotalprice, &$gupsellvolumeprices, &$gupselltotalpricediscount,$id_lang, $id_shop, $limit = 0, $count=false, $product_volumes=array(), $template='', $showin=''){
        $productids = implode(',',array_map('intval',$productids));

        $product_sort = array();
        $context = $this->context;
        $id_currency = (int)$context->currency->id;
        $nb_days_new_product = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        if (!Validate::isUnsignedInt($nb_days_new_product)) {
            $nb_days_new_product = 20;
        }
        if ($upsellObj->qty < 1) {
            $upsellObj->qty = 1;
        }
        if ((int)Tools::getValue('qty') > 0) {
            $upsellObj->qty = (int)Tools::getValue('qty');
        }
        if ($count) {
            $sql = 'SELECT  COUNT(p.`id_product`)';
        } else {
            $sql = 'SELECT  p.*, stock.`out_of_stock`, IFNULL(stock.`quantity`, 0) as quantity,
            pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
            image_shop.`id_image` id_image, il.`legend`, m.`name` manufacturer_name, product_shop.`price` AS orderprice
            '.(Combination::isFeatureActive() ? ', product_attribute_shop.`minimal_quantity` AS product_attribute_minimal_quantity, IFNULL(product_attribute_shop.`id_product_attribute`,0) id_product_attribute' : '').'';
        }
        $sql .= '  FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                    p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
                )
                '.(Combination::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
                ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.`id_shop`='.(int)$context->shop->id.')':'').'
                '.Product::sqlStock('p', 0).'
                LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
                LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                ON (image_shop.`id_product` = p.`id_product` AND image_shop.`cover`=1 AND image_shop.`id_shop`='.(int)$id_shop.')
                LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
                WHERE p.`id_product` IN('.pSQL($productids).')';
        $sql .= ' AND stock.`quantity`  >= '. (int)$upsellObj->qty;
        if ((int)$upsellObj->type_price > 0) {
            $minimum_amount = Tools::jsonDecode($upsellObj->minimum_amount, true);
            $maximum_amount = Tools::jsonDecode($upsellObj->maximum_amount, true);
            if (isset($minimum_amount[$id_currency]) && (float)$minimum_amount[$id_currency] > 0) {
                $sql .= ' AND product_shop.`price`  > \''.(float)$minimum_amount[$id_currency].'\'';
            }
            if (isset($maximum_amount[$id_currency]) && (float)$maximum_amount[$id_currency] > 0) {
                $sql .= ' AND product_shop.`price`  < \''.(float)$maximum_amount[$id_currency].'\'';
                
            }
        }
        if ($count)
            return (int)Db::getInstance()->getValue($sql);
        $sql .= ($limit > 0 ? ' LIMIT '.(int)$limit : '');
        /*end */
        $results =  Db::getInstance()->executeS($sql);
        $productids = explode(',', $productids);
        /*custom array products*/
        foreach ($productids as $productid) {
            foreach ($results as $result) {
                if ($template == 'volume') {
                    if ($product_volumes) {
                        foreach($product_volumes as $keyvolume=>$product_volume) {
                            if ((int)$productid == (int)$result['id_product']) {
                                $newproduct = new Product((int)$result['id_product'], true, $id_lang, $id_shop);
                                if ((int)$id_product_attribute > 0) {
                                    $result['id_product_attribute'] = (int)$id_product_attribute;
                                }
                                $combins_new = array();
                                $ProductAttributesIds = $newproduct->getProductAttributesIds((int)$result['id_product']);
                                if ($ProductAttributesIds) {
                                    foreach ($ProductAttributesIds as $ProductAttributesId) {
                                        $combins_new[] = $ProductAttributesId['id_product_attribute'];
                                    }
                                }
                                $result['AttributesGroups'] = $this->assignAttributeGroup($newproduct, $combins_new, $result['id_product_attribute'], true );
                                if ((int)Tools::getValue('qty') > 0) {
                                    $product_volume[$showin]['minqty'] = (int)Tools::getValue('qty');
                                }
                                $id_image = (int)$result['id_image'];
                                if($result['id_product_attribute'] > 0){
                                    $attributes = $newproduct->getAttributesGroups((int)$id_lang);
                                    $CombinationImage = $newproduct->getCombinationImageById((int)$result['id_product_attribute'],(int)$id_lang);
                                    $this->Productcombinations($attributes, (int)$result['id_product_attribute'], $result);
                                    if($CombinationImage && isset($CombinationImage['id_image']) && $CombinationImage['id_image'] > 0){
                                        $id_image = (int)$CombinationImage['id_image'];
                                    }
                                }
                                $result['id_image'] = $id_image;
                                $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                                if(!$priceDisplay || $priceDisplay == 2) {
                                    $price = Product::getPriceStatic((int)$result['id_product'], true, (int)$result['id_product_attribute'], 6, null, false, true, (int)$product_volume[$showin]['minqty']) * (int)$product_volume[$showin]['minqty'];
                                    if ($keyvolume  < 1){
                                        $gupselltotalprice = $price;
                                    }
                                    $result['price_new'] = $price;
                                    if ($product_volume[$showin]['discounttype'] == 0) {
                                        $pricediscount = Tools::convertPrice((float)$product_volume[$showin]['discount'] , (int)$product_volume[$showin]['id_currency'], $this->context->currency);
                                        $result['price_new'] = $price - $pricediscount;
                                    } else {
                                        $result['price_new'] = $price - ($price * ((float)$product_volume[$showin]['discount'] / 100));
                                    }
                                    $gupsellvolumeprices[$keyvolume]['price_new'] = $result['price_new'];
                                } elseif($priceDisplay == 1) {
                                    $price = Product::getPriceStatic((int)$result['id_product'], false, (int)$result['id_product_attribute'], 6, null, false, true, (int)$product_volume[$showin]['minqty']) * (int)$product_volume[$showin]['minqty'];
                                    if ($keyvolume  < 1){
                                        $gupselltotalprice = $price;
                                    }
                                    $result['price_tax_exc_new'] = $price;
                                    if ($product_volume[$showin]['discounttype'] == 0) {
                                        $pricediscount = Tools::convertPrice((float)$product_volume[$showin]['discount'] , (int)$product_volume[$showin]['id_currency'], $this->context->currency);
                                        $result['price_tax_exc_new'] = $price - $pricediscount;
                                    } else {
                                        $result['price_tax_exc_new'] = $price - ($price * ((float)$product_volume[$showin]['discount'] / 100));
                                    }
                                    $gupsellvolumeprices[$keyvolume]['price_tax_exc_new'] = $result['price_tax_exc_new'];
                                }
                                $result['price_nonediscount'] = $price;
                                if ($result['id_product'] == $productid) {
                                    $product_sort[] = $result;
                                }
                            }
                        }
                    }
                } else {
                    if ((int)$productid == (int)$result['id_product']) {
                        $newproduct = new Product((int)$result['id_product'], true, $id_lang, $id_shop);
                        $combins = GupselloffersModel::getProductcombininproductid($upsellObj->id_g_upsellrule, (int)$result['id_product'], '', $id_shop);
                        if ((int)$id_product_attribute > 0) {
                            $result['id_product_attribute'] = (int)$id_product_attribute;
                        }
                        if (!in_array($result['id_product_attribute'], $combins)) {
                            $result['id_product_attribute'] = (int) $this->arrayshift($combins);
                        }
                        $result['AttributesGroups'] = $this->assignAttributeGroup($newproduct, $combins, $result['id_product_attribute'] );
                        $id_image = (int)$result['id_image'];
                        if($result['id_product_attribute'] > 0){
                            $attributes = $newproduct->getAttributesGroups((int)$id_lang);
                            $CombinationImage = $newproduct->getCombinationImageById((int)$result['id_product_attribute'],(int)$id_lang);
                            $this->Productcombinations($attributes, (int)$result['id_product_attribute'], $result);
                            if($CombinationImage && isset($CombinationImage['id_image']) && $CombinationImage['id_image'] > 0){
                                $id_image = (int)$CombinationImage['id_image'];
                            }
                        }
                        $result['id_image'] = $id_image;
                        $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                        if(!$priceDisplay || $priceDisplay == 2) {
                            $price = Product::getPriceStatic((int)$result['id_product'], true, (int)$result['id_product_attribute'], 6, null, false, true, (int)$upsellObj->qty) * (int)$upsellObj->qty;
                            $gupselltotalprice += $price;
                            $result['price_new'] = $price;
                            if ($upsellObj->apply_discount == 1) {
                                if ($upsellObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$upsellObj->amount_discount , (int)$upsellObj->id_currency_discount, $this->context->currency);
                                    $result['price_new'] = $price - $pricediscount;
                                    $gupselltotalpricediscount += $pricediscount;
                                } else {
                                    $result['price_new'] = $price - ($price * ((float)$upsellObj->amount_discount / 100));
                                    $gupselltotalpricediscount += ($price * ((float)$upsellObj->amount_discount / 100));
                                }
                            }
                        } elseif($priceDisplay == 1) {
                            $price = Product::getPriceStatic((int)$result['id_product'], false, (int)$result['id_product_attribute'], 6, null, false, true, (int)$upsellObj->qty) * (int)$upsellObj->qty;
                            $gupselltotalprice += $price;
                            $result['price_tax_exc_new'] = $price;
                            if ($upsellObj->apply_discount == 1) {
                                if ($upsellObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$upsellObj->amount_discount , (int)$upsellObj->id_currency_discount, $this->context->currency);
                                    $result['price_tax_exc_new'] = $price - $pricediscount;
                                    $gupselltotalpricediscount += $pricediscount;
                                } else {
                                    $result['price_tax_exc_new'] = $price - ($price * ((float)$upsellObj->amount_discount / 100));
                                    $gupselltotalpricediscount += ($price * ((float)$upsellObj->amount_discount / 100));
                                }
                            }
                        }
                        $result['price_nonediscount'] = $price;
                        if ($result['id_product'] == $productid) {
                            $product_sort[] = $result;
                        }
                    }
                }
            }
        }
        $products = Product::getProductsProperties($id_lang , $product_sort);
        if(version_compare(_PS_VERSION_, '1.7.0.0 ', '>='))
            return $this->getProductsPropertiesPs17($products);
        else return $products;
    }
    public function arrayshift($combins) {
        foreach($combins as $combin) {
            return (int)$combin;
        }
        return 0;
    }
    public function Productcombinations($attributes= array(), $id_product_attribute=0, &$result)
    {
        $result['attributes_name'] = '';
        $combinations = array();
        foreach ($attributes as $attribute)
        {
            $combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
            if (!isset($combinations[$attribute['id_product_attribute']]['attributes']))
            {
                $combinations[$attribute['id_product_attribute']]['attributes'] = '';
            }
            $combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'].' - ';
        }
        if ($combinations && isset($combinations[$id_product_attribute])) {
            $result['attributes_name'] = rtrim($combinations[$id_product_attribute]['attributes'], ' - ');
        }
    }
    public function getProductsPropertiesPs17($products_list)
    {
        $context = Context::getContext();
        $assembler = new ProductAssembler($context);
        $presenterFactory = new ProductPresenterFactory($context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
            new PrestaShop\PrestaShop\Adapter\Image\ImageRetriever(
                $context->link
            ),
            $context->link,
            new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
            new PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
            $context->getTranslator()
        );
        $products_for_template = array();
        foreach ($products_list as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $context->language
            );
        }
        return $products_for_template;
    }
    public function hookDisplayGupsellpro($params){
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $id_box = (int)$params['id'];
        $module = Tools::getValue('module','');
        $id = (int)Tools::getValue('id');
        if($id_box > 0)
            if(($module == 'g_upsellpro' && $id != $id_box) || ($module != 'g_upsellpro')) {
                $upsellsObj = new GupselloffersModel($id_box,(int)$id_lang,(int)$id_shop);
                $html ='';
                if (isset($upsellsObj) && !empty($upsellsObj)) {
                    $productids = GupselloffersModel::getProductcombinidproduct((int)$upsellsObj->id_g_upsellrule, 'display', $id_shop);
                    $html .= $this->getUpsellProductHtml($productids, $productids,'incartcustom',$upsellsObj, false,$id_shop);
                }
                return $html;
            }
            else
                return '';
        else
            return '';
    }
    public function createDiscount($id_product=0, $id_combin, $id_upsellpro, $id_shop=0, $showin='', $id_extra=null) {
        if($id_product <=0 || $id_upsellpro <= 0) return;
        if($id_shop <=0)
            $id_shop = (int)$this->context->shop->id;
        $productObj = new Product((int)$id_product);
        $gupsellproObj = new GupselloffersModel((int)$id_upsellpro);
        if(!Validate::isLoadedObject($productObj) || !$productObj->active || !Validate::isLoadedObject($gupsellproObj))
            return;
        $id_shop_group = Shop::getContextShopGroupID();
        $_show_fields = $this->getValueConfigshowin((int)$gupsellproObj->id_g_upsellrule, $showin, $id_shop_group, $id_shop);
        if ($_show_fields[$showin.'_type'] !='volume') {
            if($gupsellproObj->apply_discount)
            {
                $minimum_amount_currency = (int)$gupsellproObj->id_currency_discount;
                $minimum_amount_tax = (int)$gupsellproObj->tax_discount;
                $cartTotal = $this->context->cart->getOrderTotal($minimum_amount_tax, Cart::ONLY_PRODUCTS); $cartTotal;
                $DISCOUNTS_TYPE = (int)$gupsellproObj->type_discount;
                if($DISCOUNTS_TYPE != 1) 
                    $DISCOUNTS_TYPE = 'AMOUNT';
                else 
                    $DISCOUNTS_TYPE = 'PERCENT';

                $discountval = (float)$gupsellproObj->amount_discount;
                if($discountval > 0){
                    if($this->checkProductDiscount($this->context->cart->id,$id_product) == 0 && $this->checkProductDiscount($this->context->cart->id,$id_combin, 'attributes') == 0){
                        $validtimes = 0;
                        if($validtimes <=0) $validtimes = 1;
                        $coupon = new CartRule();
                        $coupon->quantity = 1;
                        $coupon->quantity_per_user = 1;
                        $coupon->id_discount_type = 2;
                        $coupon->product_restriction = 1;
                        $coupon->reduction_product = (int)$id_product;
                        $coupon->minimum_amount_tax = $minimum_amount_tax;
                        $coupon->minimum_amount_currency = $minimum_amount_currency;
                        if($DISCOUNTS_TYPE !='PERCENT'){
                            $reduction_currency = (int)$gupsellproObj->id_currency_discount;
                            if($reduction_currency <=0) $reduction_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
                            if(isset($coupon->value)) $coupon->value = $discountval;
                            $coupon->reduction_percent = 0;
                            $coupon->reduction_amount = $discountval;
                            $coupon->reduction_currency = (int)$reduction_currency;
                            $coupon->reduction_tax = (int)$gupsellproObj->tax_discount;
                        }else{
                            if(isset($coupon->value))
                                $coupon->value = $discountval;
                            $coupon->reduction_percent = $discountval;
                            $coupon->reduction_amount = 0;
                            $coupon->reduction_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
                        }
                        $coupon->free_gift = 0;
                        $coupon->apply_discount_to = 'specific';
                        $start_date = date('Y-m-d H:i:s');
                        $coupon->date_from = $start_date;
                        $end_date = date('Y-m-d', strtotime('+'.(int)$validtimes.' day', strtotime($start_date)));
                        $coupon->date_to = $end_date;
                        $gen_pass = Tools::strtoupper(Tools::passwdGen(8));
                        $vouchercode = 'UPSELL';
                        $name_v = $vouchercode.'-'.$gen_pass;
                        $coupon->code = $name_v;
                        $coupon->active = 1;
                        $coupon->description = '';
                        $coupon->highlight = 0;
                        foreach (Language::getLanguages() as $lang){
                            $discountname = '';
                            if($discountname == '') $discountname = $this->l('Special Offer');
                                $coupon->name[$lang['id_lang']] = $discountname.' '.$this->l('for').' '.$productObj->name[(int)$lang['id_lang']];
                        }
                        $coupon->add();
                        $cartRuleId = $coupon->id ;
                        $this->context->cart->addCartRule( (int) $cartRuleId);
                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule_group` (`id_cart_rule`, `quantity`)
                        VALUES ('.(int)$cartRuleId.', "1")');
                        $id_product_rule_group = Db::getInstance()->Insert_ID();
                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule` (`id_product_rule_group`, `type`)
                        VALUES ('.(int)$id_product_rule_group.', "products")');
                        $id_product_rule = Db::getInstance()->Insert_ID();
                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule_value` (`id_product_rule`, `id_item`) VALUES ('.(int)$id_product_rule.','.(int)$id_product.')');
                        if ((int)$id_combin >= 0) {
                            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule` (`id_product_rule_group`, `type`)
                            VALUES ('.(int)$id_product_rule_group.', "attributes")');
                            $id_product_rule = Db::getInstance()->Insert_ID();
                            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule_value` (`id_product_rule`, `id_item`) VALUES ('.(int)$id_product_rule.','.(int)$id_combin.')');
                        }
                        return true;
                    }
                }
            }
        } else {
            if (isset($_show_fields[$showin.'_addition'])) {
                $volumes = Tools::jsonDecode($_show_fields[$showin.'_addition'], true);
                $minimum_amount_currency = (int)$volumes[(int)$id_extra][$showin]['id_currency'];
                $minimum_amount_tax = (int)$volumes[(int)$id_extra][$showin]['reduction_tax'];
                $cartTotal = $this->context->cart->getOrderTotal($minimum_amount_tax, Cart::ONLY_PRODUCTS); $cartTotal;
                $DISCOUNTS_TYPE = (int)$volumes[(int)$id_extra][$showin]['discounttype'];
                if($DISCOUNTS_TYPE != 1) 
                    $DISCOUNTS_TYPE = 'AMOUNT';
                else 
                    $DISCOUNTS_TYPE = 'PERCENT';
    
                $discountval = (float)$volumes[(int)$id_extra][$showin]['discount'];
                if($discountval > 0){
                    if($this->checkProductDiscount($this->context->cart->id,$id_product) == 0 && $this->checkProductDiscount($this->context->cart->id,$id_combin, 'attributes') == 0){
                        $validtimes = 0;
                        if($validtimes <=0) $validtimes = 1;
                        $coupon = new CartRule();
                        $coupon->quantity = 1;
                        $coupon->quantity_per_user = 1;
                        $coupon->id_discount_type = 2;
                        $coupon->product_restriction = 1;
                        $coupon->reduction_product = (int)$id_product;
                        $coupon->minimum_amount_tax = $minimum_amount_tax;
                        $coupon->minimum_amount_currency = $minimum_amount_currency;
                        if($DISCOUNTS_TYPE !='PERCENT'){
                            $reduction_currency = (int)$gupsellproObj->id_currency_discount;
                            if($reduction_currency <=0) $reduction_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
                            if(isset($coupon->value)) $coupon->value = $discountval;
                            $coupon->reduction_percent = 0;
                            $coupon->reduction_amount = $discountval;
                            $coupon->reduction_currency = (int)$minimum_amount_currency;
                            $coupon->reduction_tax = (int)$volumes[(int)$id_extra][$showin]['reduction_tax'];
                        }else{
                            if(isset($coupon->value))
                                $coupon->value = $discountval;
                            $coupon->reduction_percent = $discountval;
                            $coupon->reduction_amount = 0;
                            $coupon->reduction_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
                        }
                        $coupon->free_gift = 0;
                        $coupon->apply_discount_to = 'specific';
                        $start_date = date('Y-m-d H:i:s');
                        $coupon->date_from = $start_date;
                        $end_date = date('Y-m-d', strtotime('+'.(int)$validtimes.' day', strtotime($start_date)));
                        $coupon->date_to = $end_date;
                        $gen_pass = Tools::strtoupper(Tools::passwdGen(8));
                        $vouchercode = 'UPSELL';
                        $name_v = $vouchercode.'-'.$gen_pass;
                        $coupon->code = $name_v;
                        $coupon->active = 1;
                        $coupon->description = '';
                        $coupon->highlight = 0;
                        foreach (Language::getLanguages() as $lang){
                            $discountname = '';
                            if($discountname == '') $discountname = $this->l('Special Offer');
                                $coupon->name[$lang['id_lang']] = $discountname.' '.$this->l('for').' '.$productObj->name[(int)$lang['id_lang']];
                        }
                        $coupon->add();
                        $cartRuleId = $coupon->id ;
                        $this->context->cart->addCartRule( (int) $cartRuleId);
                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule_group` (`id_cart_rule`, `quantity`)
                        VALUES ('.(int)$cartRuleId.', "1")');
                        $id_product_rule_group = Db::getInstance()->Insert_ID();
                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule` (`id_product_rule_group`, `type`)
                        VALUES ('.(int)$id_product_rule_group.', "products")');
                        $id_product_rule = Db::getInstance()->Insert_ID();
                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule_value` (`id_product_rule`, `id_item`) VALUES ('.(int)$id_product_rule.','.(int)$id_product.')');
                        if ((int)$id_combin >= 0) {
                            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule` (`id_product_rule_group`, `type`)
                            VALUES ('.(int)$id_product_rule_group.', "attributes")');
                            $id_product_rule = Db::getInstance()->Insert_ID();
                            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule_value` (`id_product_rule`, `id_item`) VALUES ('.(int)$id_product_rule.','.(int)$id_combin.')');
                        }
                        return true;
                    }
                }
            }
        }
    }
    public function checkProductDiscount($id_cart , $id_product = 0, $type='products') {
        if ($type =='attributes' && $id_product == 0) {
            return 0;
        }
        $sql = 'SELECT count(id_item)
                FROM '._DB_PREFIX_.'cart_rule_product_rule_value
                WHERE id_product_rule IN(
                    SELECT id_product_rule
                    FROM '._DB_PREFIX_.'cart_rule_product_rule
                    WHERE id_product_rule_group IN(
                        SELECT DISTINCT id_product_rule_group
                        FROM '._DB_PREFIX_.'cart_rule_product_rule_group
                        WHERE id_cart_rule IN (
                            SELECT DISTINCT id_cart_rule
                                FROM '._DB_PREFIX_.'cart_cart_rule
                                WHERE id_cart = '.(int)$id_cart.'
                        )
                    )
                    AND type = "'.pSQL($type).'"
                )
                AND id_item = '.(int)$id_product.'
                ';
        return (int)Db::getInstance()->getValue($sql);
    }
    public function getConfigFieldsValues()
    {
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = Shop::getContextShopGroupID();
        $languages = Language::getLanguages(false);
        $adminSettings = AdminSettings::getUpsellSettings((int)$id_shop_group,  $languages, (int)$id_shop);
        return $adminSettings;
    }
    public function updateUpsellcart($id_product=0, $id_combin, $id_upsellpro, $remove_productin_cart, $id_shop=0) 
    {
        if($id_product <=0 || $id_upsellpro <= 0) return;
        $res = true;
        if($id_shop <=0)
            $id_shop = (int)$this->context->shop->id;
        $productObj = new Product((int)$id_product);
        $gupsellproObj = new GupselloffersModel((int)$id_upsellpro);
        if(!Validate::isLoadedObject($productObj) || !$productObj->active || !Validate::isLoadedObject($gupsellproObj))
            return;
        $checkcart = AdminSettings::upsellcartproduct($id_upsellpro, (int)$this->context->cart->id, $id_product, $id_combin, $id_shop);
        if ($checkcart <= 0) {
            $date = date('Y-m-d');
            /*update anytic*/
            if ($remove_productin_cart > 0) {
                $transactions = (int)AdminSettings::getTotalFieldsanytic('transactions',(int)$id_upsellpro,$date, $date,$id_shop);
                $res &= AdminSettings::upsellanytic((int)$id_upsellpro, 0, 0, $transactions + 1, 0, 0, $date, $id_shop);
            } else {
                $addcarts = (int)AdminSettings::getTotalFieldsanytic('addcarts',(int)$id_upsellpro,$date, $date,$id_shop);
                $res &= AdminSettings::upsellanytic((int)$id_upsellpro, 0, $addcarts + 1, 0, 0, 0, $date, $id_shop);
            }
        }
        return true;
    }
    public function hookactionObjectProductInCartDeleteAfter($params) 
    {
        $id_cart = (int)$params['id_cart'];
        $id_product = (int)$params['id_product'];
        $id_product_attribute = (int)$params['id_product_attribute'];
        $id_shop = (int)$this->context->shop->id;
        $res = true;
        if ($id_cart > 0) {
           $upsellcarts =  AdminSettings::upgetsellcartproduct($id_cart, $id_product, $id_product_attribute, $id_shop);
           if ($upsellcarts) {
                foreach ($upsellcarts as $upsellcart) {
                    $id_g_upsellproductcombin = 0;
                    $gupsellproObj = new GupselloffersModel((int)$upsellcart['id_g_upsellrule']);
                    if ((int)$gupsellproObj->remove_product_upsell > 0) {
                        switch ($gupsellproObj->display_product) {
                            case 'specific_product':
                                $id_g_upsellproductcombin = GupselloffersModel::getProductcombininproductidAndidcombin((int)$gupsellproObj->id_g_upsellrule, $id_product, $id_product_attribute, 'display', $id_shop);
                                break;
                            case 'collections_product':
                                $categorys = Tools::jsonDecode($gupsellproObj->display_cateids, true);
                                $sql = 'SELECT distinct `id_product` FROM `'._DB_PREFIX_.'category_product` WHERE `id_category` IN ('.pSql(implode(',',$categorys)).') AND `id_product` ='.(int)$id_product;
                                $id_g_upsellproductcombin = (int)Db::getInstance()->getValue($sql);
                                break;
                            default:
                                $sql ='';
                                $id_g_upsellproductcombin = 1;
                        }
                        if ($id_g_upsellproductcombin) {
                            $res &= $this->context->cart->deleteProduct((int)$upsellcart['id_product'], (int)$upsellcart['id_combin']);
                        }
                    }
                }
           }
        }
        return $res;
    }
    public function hookactionDeleteProductInCartAfter($params) 
    {
        $id_cart = (int)$params['id_cart'];
        $id_product = (int)$params['id_product'];
        $id_product_attribute = (int)$params['id_product_attribute'];
        $id_shop = (int)$this->context->shop->id;
        $res = true;
        if ($id_cart > 0) {
           $upsellcarts =  AdminSettings::upgetsellcartproduct($id_cart, $id_product, $id_product_attribute, $id_shop);
           if ($upsellcarts) { 
                foreach ($upsellcarts as $upsellcart) {
                    $id_g_upsellproductcombin = 0;
                    $gupsellproObj = new GupselloffersModel((int)$upsellcart['id_g_upsellrule']);
                    if ((int)$gupsellproObj->remove_product_upsell > 0) {
                        switch ($gupsellproObj->display_product) {
                            case 'specific_product':
                                $id_g_upsellproductcombin = GupselloffersModel::getProductcombininproductidAndidcombin((int)$gupsellproObj->id_g_upsellrule, $id_product, $id_product_attribute, 'display', $id_shop);
                                break;
                            case 'collections_product':
                                $categorys = Tools::jsonDecode($gupsellproObj->display_cateids, true);
                                $sql = 'SELECT distinct `id_product` FROM `'._DB_PREFIX_.'category_product` WHERE `id_category` IN ('.pSql(implode(',',$categorys)).') AND `id_product` ='.(int)$id_product;
                                $id_g_upsellproductcombin = (int)Db::getInstance()->getValue($sql);
                                break;
                            default:
                                $sql ='';
                                $id_g_upsellproductcombin = 1;
                        }
                        if ($id_g_upsellproductcombin) {
                            $res &= $this->context->cart->deleteProduct((int)$upsellcart['id_product'], (int)$upsellcart['id_combin']);
                        }
                    }
                }
           }
        }
        return $res;
    }
    public function hookactionAfterDeleteProductInCart($params) 
    {
        $id_cart = (int)$params['id_cart'];
        $id_product = (int)$params['id_product'];
        $id_product_attribute = (int)$params['id_product_attribute'];
        $id_shop = (int)$this->context->shop->id;
        $res = true;
        if ($id_cart > 0) {
           $upsellcarts =  AdminSettings::upgetsellcartproduct($id_cart, $id_product, $id_product_attribute, $id_shop);
           if ($upsellcarts) {
                foreach ($upsellcarts as $upsellcart) {
                    $id_g_upsellproductcombin = 0;
                    $gupsellproObj = new GupselloffersModel((int)$upsellcart['id_g_upsellrule']);
                    if ((int)$gupsellproObj->remove_product_upsell > 0) {
                        switch ($gupsellproObj->display_product) {
                            case 'specific_product':
                                $id_g_upsellproductcombin = GupselloffersModel::getProductcombininproductidAndidcombin((int)$gupsellproObj->id_g_upsellrule, $id_product, $id_product_attribute, 'display', $id_shop);
                                break;
                            case 'collections_product':
                                $categorys = Tools::jsonDecode($gupsellproObj->display_cateids, true);
                                $sql = 'SELECT distinct `id_product` FROM `'._DB_PREFIX_.'category_product` WHERE `id_category` IN ('.pSql(implode(',',$categorys)).') AND `id_product` ='.(int)$id_product;
                                $id_g_upsellproductcombin = (int)Db::getInstance()->getValue($sql);
                                break;
                            default:
                                $sql .='';
                        }
                        if ($id_g_upsellproductcombin) {
                            $res &= $this->context->cart->deleteProduct((int)$upsellcart['id_product'], (int)$upsellcart['id_combin']);
                        }
                    }
                }
           }
        }
        return $res;
    }
    public function hookactionValidateOrder($params) 
    {
        $id_shop = (int)$this->context->shop->id;
        if (!(isset($params['cart'])) || !(isset($params['order'])) || !($cart = $params['cart']) || !($order = $params['order'])) return;
        $cart;$order;
        $id_order = (int)$params['order']->id;
        if ($id_order > 0) {
            $orderObj = new Order((int)$id_order);
            if (Validate::isLoadedObject($orderObj)) {
                $products = $orderObj->getProducts();
                if (isset($products)) {
                    foreach($products as $product){
                        $upsellcarts =  AdminSettings::upgetsellcartproduct((int)$cart->id, (int)$product['product_id'], (int)$product['product_attribute_id'], $id_shop);
                        if ($upsellcarts) {
                            $reduction_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
                            $totalproduct_tax_ex = Tools::convertPrice((float)$product['total_price_tax_excl'], new Currency($reduction_currency), $this->context->currency);
                            $amount = (float)$totalproduct_tax_ex;
                            $date = date('Y-m-d');
                            foreach ($upsellcarts as $upsellcart) {
                                $sales = (float)AdminSettings::getTotalFieldsanytic('sales',(int)$upsellcart['id_g_upsellrule'],$date, $date,$id_shop);
                                AdminSettings::upsellanytic((int)$upsellcart['id_g_upsellrule'], 0, 0, 0, $sales + $amount, 0, $date, $id_shop);
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
    /***/
    public function assignAttributeGroup($product , $combies= array(), $id_combie, $all=false) 
    {
        $attributes = $product->getAttributesGroups((int)$this->context->language->id);
        $combinations = array();
        foreach ($attributes as $attribute)
        {
            if ($all) {
                if (in_array($attribute['id_product_attribute'], $combies)) {
                    $combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
                    if (!isset($combinations[$attribute['id_product_attribute']]['attributes']))
                    {
                        $combinations[$attribute['id_product_attribute']]['attributes'] = '';
                    }
                    $combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['group_name'].' : '.$attribute['attribute_name'].' , ';
                }
            } else {
                $combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
                if (!isset($combinations[$attribute['id_product_attribute']]['attributes']))
                {
                    $combinations[$attribute['id_product_attribute']]['attributes'] = '';
                }
                $combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['group_name'].' : '.$attribute['attribute_name'].' , ';
            }
        }
        
        foreach ($combinations as &$combination)
        {
            $selected = '';
            if ($id_combie == $combination['id_product_attribute'])
                $selected = 'selected';

            $combination['attributes'] = rtrim($combination['attributes'], ' , ');
            $combination['selected'] = $selected;
            $combination['combination_price'] = Tools::disPlayprice($product->getPriceStatic((int)$product->id,true,(int)$combination['id_product_attribute']), (int)$this->context->currency->id);
        }
        if(isset($combinations) && !empty($combinations))
        {
            return $combinations;
        }
        return array();
    }
}