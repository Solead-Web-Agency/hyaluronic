<?php
/**
 * The file is controller. Do not modify the file if you want to upgrade the module in future
 * 
 * @author    Globo Jsc <contact@globosoftware.net>
 * @copyright 2020 Globo., Jsc
 * @license   please read license in file license.txt
 * @link	     http://www.globosoftware.net
 */

include_once(_PS_MODULE_DIR_ . 'g_upsellpro/classes/AdminSettings.php');
include_once(_PS_MODULE_DIR_ . 'g_upsellpro/classes/admin/GupselloffersModel.php');
class AdminGupselloffersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->display = 'edit';
        $this->bootstrap = true;
        parent::__construct();
        $this->meta_title = $this->l('Offers');
		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
    }
    public function initContent()
	{
        $controller = Tools::getValue('controller');
        $link = $this->context->link;
		$this->display = 'Offers';
        $this->content = '';
        $this->initTabModuleList();
		$this->initToolbar();
        $this->initPageHeaderToolbar();
        $this->content .= AdminSettings::tabsHTML($controller, $link);
        $this->content .= $this->renderForm();
        $this->context->smarty->assign(array(
                'content'  => $this->content,
                'url_post' => self::$currentIndex.'&token='.$this->token,
    		));
        if(version_compare(_PS_VERSION_,'1.6') == 1){
    		$this->context->smarty->assign(array(
    			'show_page_header_toolbar' => $this->show_page_header_toolbar,
    			'page_header_toolbar_title' => $this->page_header_toolbar_title,
    			'page_header_toolbar_btn' => $this->page_header_toolbar_btn
    		));
        }
	}
    public function initTabModuleList(){
        if(version_compare(_PS_VERSION_,'1.5.4.0') == -1)
            return true;
        else
            return parent::initTabModuleList();
    }
    public function initToolBarTitle()
	{
		$this->toolbar_title[] = $this->module->displayName;
		$this->toolbar_title[] = $this->l('Offers');
	}
    public function initPageHeaderToolbar()
	{
        if(version_compare(_PS_VERSION_,'1.6') == 1){
		  parent::initPageHeaderToolbar();
        }
	}
    public function renderForm()
    {
        $link = $this->context->link;
        $id_shop = (int)$this->context->shop->id;
        $id_lang = (int)$this->context->language->id;
        $currency = $this->context->currency;
        $id_shop_group = Shop::getContextShopGroupID();
        $productidshtml = '';
        $productidshtmldisplay = '';
        $productids = '';
        $display_productids ='';
        $upsellminamount = array();
        $upsellmaxamount = array();
        $_show_fields = array();
        $useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https:/'.'/' : 'http:/'.'/';
        $base_url = $protocol_content.Tools::getHttpHost().__PS_BASE_URI__;
        $Currencies = Currency::getCurrencies();
        $id_upselloffers = (int)Tools::getValue('id_upselloffers');
        $upselloffersObj = new GupselloffersModel($id_upselloffers);
        $gupsell_catsdisplay = $this->searchProductByCollection($this->customGetNestedCategories($id_shop,null,$id_lang,false));
        
        $extra_setting_shows = array(
            'showin_product' => $this->l('Product page'),
            'showin_cart' => $this->l('Cart summary'),
            'showin_cart_popup' => $this->l('Cart popup'),
            'showin_home' => $this->l('Home page'),
            'showin_collection' => $this->l('Category page'),
        );
        $extra_setting_shows_settings = array_keys($extra_setting_shows);
        foreach($extra_setting_shows_settings as $extra_setting_show){
            $_show_fields[$extra_setting_show.'_type']  = Tools::getValue($extra_setting_show.'_type', 'normal');
            $_show_fields[$extra_setting_show.'_stype'] = Tools::getValue($extra_setting_show.'_stype', 'inpage');
            $_show_fields[$extra_setting_show.'_position'] = Tools::getValue($extra_setting_show.'_position', 'bottom-right');
            $_show_fields[$extra_setting_show.'_addition'] = Tools::getValue($extra_setting_show.'_addition', Tools::jsonEncode(array()));
        }
        if (Validate::isLoadedObject($upselloffersObj)) {
            $productidsold = GupselloffersModel::getProductcombinidproduct($id_upselloffers, '', $id_shop);
            if (isset($productidsold) && !empty($productidsold)) {
                $productids = implode(',', $productidsold);
                $productcombieids = GupselloffersModel::getProductcombin($id_upselloffers, $productids, '', $id_shop);
                $mostpopular    = GupselloffersModel::getProductmostpopular($id_upselloffers, '', $id_shop);
                $productidshtml = $this->producthtml($productidsold, $productcombieids, '', $mostpopular);
            }
            $displayproductidsold = GupselloffersModel::getProductcombinidproduct($id_upselloffers, 'display', $id_shop);
            if (isset($displayproductidsold) && !empty($displayproductidsold)) {
                $display_productids =  implode(',', $displayproductidsold);
                $displayproductcombieids = GupselloffersModel::getProductcombin($id_upselloffers, $display_productids, 'display', $id_shop);
                $productidshtmldisplay = $this->producthtml($displayproductidsold, $displayproductcombieids, 'displayfor');
            }
            if (isset($upselloffersObj->minimum_amount) && !empty($upselloffersObj->minimum_amount)) {
                $upsellminamount = Tools::jsonDecode($upselloffersObj->minimum_amount, true);
            }
            if (isset($upselloffersObj->maximum_amount) && !empty($upselloffersObj->maximum_amount)) {
                $upsellmaxamount = Tools::jsonDecode($upselloffersObj->maximum_amount, true);
            }
            if (isset($upselloffersObj->display_cateids) && !empty($upselloffersObj->display_cateids)) {
                $gupsell_catsdisplay = $this->searchProductByCollection($this->customGetNestedCategories($id_shop,null,$id_lang,false), explode(',',$upselloffersObj->display_cateids));
            }
            foreach($extra_setting_shows_settings as $extra_setting_show){
                $_show_fields[$extra_setting_show.'_type'] = Tools::getValue($extra_setting_show.'_type', Configuration::get('gupsell_type_'.$extra_setting_show.'_'.$id_upselloffers, null, $id_shop_group, $id_shop));
                $_show_fields[$extra_setting_show.'_stype'] = Tools::getValue($extra_setting_show.'_stype', Configuration::get('gupsell_stype_'.$extra_setting_show.'_'.$id_upselloffers, null, $id_shop_group, $id_shop));
                $_show_fields[$extra_setting_show.'_position'] = Tools::getValue($extra_setting_show.'_position', Configuration::get('gupsell_position_'.$extra_setting_show.'_'.$id_upselloffers, null, $id_shop_group, $id_shop));
                $_show_fields[$extra_setting_show.'_addition'] = Tools::getValue($extra_setting_show.'_addition', Configuration::get('gupsell_addition_'.$extra_setting_show.'_'.$id_upselloffers, null, $id_shop_group, $id_shop));
                
            }
        }
        $date_from = date('Y-m-d',strtotime('-30 day', strtotime(date('Y-m-d'))));
        $date_to = date('Y-m-d');
        $listupsellAnalytic_addcarts = (int) AdminSettings::getTotalFieldsanytic('addcarts', 0,$date_from ,$date_to ,$id_shop);
        $listupsellAnalytic_views = (int)AdminSettings::getTotalFieldsanytic('views', 0,$date_from ,$date_to ,$id_shop);
        $listupsellAnalytic_transactions = (float)AdminSettings::getTotalFieldsanytic('transactions', 0,$date_from,$date_to,$id_shop);
        /*assign tpl*/
        $this->context->smarty->assign(
            array(
                'link' => $link,
                'base_url' => $base_url,
                'management' => Tools::getValue('management'),
                'addnewid' => (int)Tools::getValue('addnewid'),
                'gupsellcurrencies' => $Currencies,
                'gupsellid_currency' => $currency,
                'gupselllangisocode' => $this->context->language->iso_code,
                'addnewid' => (int)Tools::getValue('addnewid'),
                'id_upselloffers' => (int)$id_upselloffers,
                'upselloffersObj' => $upselloffersObj,
                'productidshtml' => $productidshtml,
                'productidshtmldisplay' => $productidshtmldisplay,
                'upsellminamount' => $upsellminamount,
                'upsellmaxamount' => $upsellmaxamount,
                'productids' => $productids,
                'display_productids' => $display_productids,
                'gupsell_catsdisplay' => $gupsell_catsdisplay,
                'total_views' => $listupsellAnalytic_views,
                'addcarts' => $listupsellAnalytic_addcarts,
                'transactions' => $listupsellAnalytic_transactions,
                'sales' => Tools::displayPrice(Tools::convertPriceFull((float)AdminSettings::getTotalFieldsanytic('sales', 0,$date_from,$date_to,$id_shop), new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT')), $this->context->currency)),
                'take_rate' => (int)$listupsellAnalytic_views > 0 ? Tools::ps_round((((int)$listupsellAnalytic_addcarts + (int)$listupsellAnalytic_transactions) / (int)$listupsellAnalytic_views) * 100, _PS_PRICE_COMPUTE_PRECISION_) .'%' : 0,
                'extra_setting_shows' => $extra_setting_shows,
                '_show_fields' => $_show_fields,
            ));
        $input   = array();
        
        $input[] = array(
            'type' => 'gupsell_open',
            'name' => 'gupsellopen',
            'class' =>'gupsellopen'
            );

        $input[] =  array(
            'type' => 'gupsell_dashboard',
            'name' => 'gupsell_dashboard',
        );
        if (Tools::getValue('management') =='helperform') {

            if (Shop::isFeatureActive() ) {
                $input[] = array(
                    'type' => 'shop',
                    'class' => 'checkBoxShopAsso_globo',
                    'name' => 'checkBoxShopAsso',
                    );
            }

            $input[] = array(
                'type' => 'gupsell_end',
                'name' => 'gupsellend',
                'class' =>'gupsellend'
                );
        
        }
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Offers'),
                'icon' => 'icon-cogs'
            ),
            'input' => $input,
        );
        return parent::renderForm();
    }
    
    public function postProcess()
    {
        $id_lang = (int)$this->context->language->id;
        $id_shop = (int)$this->context->shop->id;
        if((bool)Tools::isSubmit('searchProduct') == true) {
            $results = array();
            $results = $this->searchProduct('');
            echo $results;
            die();
        } elseif ((bool)Tools::isSubmit('searchProductdisplayfor') == true) {
            $results = array();
            $results = $this->searchProduct('displayfor');
            echo $results;
            die();
        } elseif ((bool)Tools::isSubmit('searchCollection') == true) {
            $results = array();
            $results = Tools::jsonEncode($this->searchCollection());
            echo $results;
            die();
        } elseif ((bool)Tools::isSubmit('SubmitSave') == true) {
            $id_lang = (int)$this->context->language->id;
            $id_shop = (int)$this->context->shop->id;
            $langs = Language::getLanguages(false);
            $_error = '';
            $id_upselloffers = (int)Tools::getValue('id_upselloffers');
            $productids = Tools::getValue('productids');
            $mostpopular = Tools::getValue('mostpopular');
            $gupsell_combinids = Tools::getValue('gupsell-combin-ids');
            $display_productids = Tools::getValue('gupsell-displayforproduct-ids');
            $gupsell_combindisplayids = Tools::getValue('gupsell-displayforcombin-ids');
            $gupsellSettings = array(
                'name' => Tools::getValue('name'),
                'title' => Tools::getValue('title'),
                'description' => Tools::getValue('description'),
                'button_popupname' => Tools::getValue('button_popupname'),
                'position' => (int)Tools::getValue('gupsellposition'),
                'showin_product' => (int)Tools::getValue('showin_product'),
                'showin_cart' => (int)Tools::getValue('showin_cart'),
                'showin_cart_popup' => (int)Tools::getValue('showin_cart_popup'),
                'showin_home' => (int)Tools::getValue('showin_home'),
                'showin_collection' => (int)Tools::getValue('showin_collection'),
                'qty' => (int)Tools::getValue('gupsell_min_qty')  > 0 ? (int)Tools::getValue('gupsell_min_qty') : 1,
                'apply_discount' => (int)Tools::getValue('gupsell_applydiscount'),
                'type_discount' => Tools::getValue('gupsell_applydiscounttype'),
                'amount_discount' => Tools::getValue('amount_discount'),
                'id_currency_discount' => (int)Tools::getValue('id_currency_discount'),
                'tax_discount' => (int)Tools::getValue('tax_discount'),
                'display_product' => Tools::getValue('gupsell_display'),
                'display_cateids' => Tools::getValue('gupsell-collections-ids'),
                'remove_product' => Tools::getValue('gupsell_upsellremoved'),
                'remove_product_upsell' => Tools::getValue('gupsell_productremoved_upsell'),
                'display_customqty' => (int)Tools::getValue('gupsell_customqty'),
                'type_price' => Tools::getValue('gupsell_specified_range'),
                'minimum_amount' => Tools::getValue('minamount'),
                'maximum_amount' => Tools::getValue('maxamount'),
                'showoffers'     => (int)Tools::getValue('showoffers'),
                'product_ids' => $productids,
                'product_combin_ids' => $gupsell_combinids,
                'product_displayids' => $display_productids,
                'product_displaycombin_ids' => $gupsell_combindisplayids,
                'dateadd' => date("Y-m-d H:i:s", time()),
            );
            if (!$id_upselloffers) {
                $gupsellSettings['dateup'] = date("Y-m-d H:i:s", time());
            }
            if (!isset($gupsellSettings['name'][$id_lang]) || empty($gupsellSettings['name'][$id_lang])) {
                $_error = $this->l('This field Name is required.');
            } elseif (!isset($gupsellSettings['title'][$id_lang]) || empty($gupsellSettings['title'][$id_lang])) {
                $_error = $this->l('This field Title is required.');
            } elseif (empty($productids)) {
                $_error = $this->l('This Product is required.');
            } elseif ($gupsellSettings['display_product'] == 'specific_product' && empty($display_productids)) {
                $_error = $this->l('This Product Display is required.');
            } elseif ($gupsellSettings['display_product'] == 'collections_product' && empty($gupsellSettings['display_cateids'])) {
                $_error = $this->l('This Category Display is required.');
            }
            /*add or update*/
            $gupselloffersObj = new GupselloffersModel((int)$id_upselloffers);
            foreach ($gupsellSettings as $key=>$vals) {
                if ($key == 'name' || $key == 'title' || $key == 'description') {
                    foreach ($langs as $value_lang) {
                        if ($key == 'name') {
                            $gupselloffersObj->name[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['name'][$id_lang];
                        } elseif($key == 'title') {
                            $gupselloffersObj->title[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['title'][$id_lang];
                        } elseif($key == 'description') {
                            $gupselloffersObj->description[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['description'][$id_lang];
                        } elseif($key == 'button_popupname') {
                            $gupselloffersObj->button_popupname[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['button_popupname'][$id_lang];
                        }
                    }
                } elseif ($key == 'minimum_amount' 
                || $key == 'maximum_amount') {
                    if (!isset($vals) || empty($vals)) {
                        $vals = array();
                    }
                    $gupselloffersObj->$key = Tools::jsonEncode($vals);
                } else {
                    $gupselloffersObj->$key = $vals;
                }
            }
            if (empty($_error)) {
                $url_final = '';
                $updateproduct = false;
                if ((int)$id_upselloffers > 0) {
                    if($gupselloffersObj->update()) {
                        $updateproduct = $this->updateConfigdata($gupselloffersObj);
                        if (Tools::getValue('Submittype') != 'saveandstay') {
                            $url_final = "&management=helperform&updateoffers=1&id_upselloffers=".(int)$gupselloffersObj->id."&conf=4";
                        }
                        $results = array(
                            'error'   => 0,
                            'warrning'=> '',
                            'url' => $this->context->link->getAdminLink('AdminGupselloffers').$url_final,
                        );
                    }
                } else {
                    if($gupselloffersObj->save()) {
                        $updateproduct = $this->updateConfigdata($gupselloffersObj);
                        if (Tools::getValue('Submittype') != 'saveandstay') {
                            $url_final = "&management=helperform&updateoffers=1&id_upselloffers=".(int)$gupselloffersObj->id."&conf=3";
                        }
                        $results = array(
                            'error'   => 0,
                            'warrning'=> '',
                            'url' => $this->context->link->getAdminLink('AdminGupselloffers').$url_final,
                        );
                    }
                }
                if ($updateproduct) {
                    $this->actionProductupsell($gupselloffersObj, $productids, $gupsell_combinids, $display_productids, $gupsell_combindisplayids, $mostpopular, $id_shop);
                }
                
            } else {
                $results = array(
                    'error'   => 1,
                    'warrning'=> $_error,
                    'url' => "",
                );
            }
            die(Tools::jsonEncode($results));
        } elseif ((bool)Tools::isSubmit('submitAddproductUpsell') == true) {
            $langs = Language::getLanguages(false);
            $id_lang = (int)$this->context->language->id;
            $gupselladdpros = Tools::getValue('gupselladdpro');
            $_error = '';
            foreach ($langs as $value_lang) {
                if ($id_lang == $value_lang["id_lang"] && empty($gupselladdpros['name'][$value_lang["id_lang"]])) {
                    $_error = $this->l('This field Product Name is required.');
                }
                $gupselladdpros['name'][$value_lang["id_lang"]] = !empty($gupselladdpros['name'][$value_lang["id_lang"]]) ? $gupselladdpros['name'][$value_lang["id_lang"]] : $gupselladdpros['name'][$id_lang];
            }
            $productObj = new Product();
            foreach ($langs as $value_lang) {
                if ($gupselladdpros['name'][$value_lang["id_lang"]] == '' || !Validate::isCatalogName($gupselladdpros['name'][$value_lang["id_lang"]]) || Tools::strlen($gupselladdpros['name'][$value_lang["id_lang"]]) > 128) {
                    $productObj->name[$value_lang["id_lang"]] = $gupselladdpros['name'][$value_lang["id_lang"]];
                } else {
                    $productObj->name[$value_lang["id_lang"]] = Tools::substr($gupselladdpros['name'][$value_lang["id_lang"]], 0, 128);
                }
                $productObj->link_rewrite[$value_lang["id_lang"]] = Tools::link_rewrite(Tools::substr($gupselladdpros['name'][$id_lang], 0, 128));
                if ($gupselladdpros['description'][$value_lang["id_lang"]] != '') {
                    $productObj->description[$value_lang["id_lang"]] = $gupselladdpros['description'][$value_lang["id_lang"]];
                }
                if ($gupselladdpros['shortdescription'][$value_lang["id_lang"]]!= '') {
                    if(Tools::strlen($gupselladdpros['shortdescription'][$value_lang["id_lang"]]) <= 800){
                        $productObj->description_short[$value_lang["id_lang"]] = $gupselladdpros['shortdescription'][$value_lang["id_lang"]];
                    }else
                        $productObj->description_short[$value_lang["id_lang"]] = Tools::substr($gupselladdpros['shortdescription'][$value_lang["id_lang"]],0,799);
                }
            }
            $productObj->price = (float)$gupselladdpros['price'];
            $productObj->quantity = 99;
            $idcart_defaults = $productObj->getDefaultCategory();
            if (is_array($idcart_defaults)) {
                $productObj->id_category_default = (int)$idcart_defaults['id_category_default'];
            }
            if (empty($_error)) {
                if ($productObj->save()) {
                    StockAvailable::setQuantity((int)$productObj->id, 0, 99, (int) $this->context->shop->id);
                    $Tailfiles    = array('.png', '.jpg', '.gif');
                    if ($_FILES != 'undefined'  && isset($_FILES['image_product']) && $_FILES['image_product']['error'] == 0 && $_FILES['image_product']['size'] != 0
                        && isset($_FILES['image_product']['tmp_name']) && !empty($_FILES['image_product']['tmp_name'])) {
                        $k = false;
                        foreach ($Tailfiles as $Tailfile) {
                            $pos = strpos($_FILES['image_product']['name'], $Tailfile);
                            if ($pos !== false) {
                                $k = $this->addimageProduct($_FILES, $productObj);
                            }
                        }
                        if ($k != true) {
                            die (Tools::jsonEncode(array('error'=>1, 'warrning'=>$this->l('ERROR: File not in format (.png, .jpg, .gif)'))));
                        }
                    }
                    $results = array(
                        'error' => 0,
                        'id_product' => (int)$productObj->id,
                        'warrning'=> $this->l('SUCCESS: Add new Product Successful'),
                    );
                } else {
                    $results = array(
                        'error'   => 1,
                        'warrning'=> $this->l('ERROR: An error occurred while add new Product'),
                    );
                }
            } else {
                $results = array(
                    'error'   => 1,
                    'warrning'=> $_error,
                );
            }
            die(Tools::jsonEncode($results));
        } elseif ((bool)Tools::isSubmit('adProductGif') == true) {
            $display = Tools::getValue('display');
            $id_product = (int)Tools::getValue('id_product');
            $combies_value = Tools::getValue('combies');
            $combies = array();
            if ($combies_value != '') {
                $combies = explode(',', $combies_value);
            }
            $productids = array('0'=>$id_product);
            $html = $this->producthtml($productids, $combies,$display);
            echo $html;
            die();
        } elseif ((bool)Tools::isSubmit('adCollectionGifHTML') == true) {
            $id_cat = (int)Tools::getValue('id_cat');
            $id_cats = array('0'=>$id_cat);
            $html = $this->cathtml($id_cats);
            echo $html;
            die();
        } elseif ((bool)Tools::isSubmit('listupsell') == true) {
            $id_shop = (int)$this->context->shop->id;
            $id_lang = (int)$this->context->language->id;
            $number_start = (int)Tools::getValue('number_start');
            $number_end = (int)Tools::getValue('number_end');
            $count   = GupselloffersModel::Countlistupsell($id_lang,  $id_shop);
            $page_active  = (int)Tools::getValue('page_active');
            $number_page  = (int)Tools::ceilf((int)$count / (int)$number_end) ;
            $number_start        = 0;
            if ($page_active > 1) 
                $number_start = $number_end * ($page_active - 1);
            $listupsells = GupselloffersModel::listupsell(array(), $number_start, $number_end, $id_lang,  $id_shop);
            if ($listupsells) {
                foreach($listupsells as &$listupsell) {
                    $listupsell['products_array'] = array();
                    $listupsell['productsdisplay_array'] = array();
                    $listupsell['cat_array'] = array();
                    if ($listupsell['product_ids'] !='') {
                        $product_ids = explode(',', $listupsell['product_ids']);
                        foreach ($product_ids as $product_id) {
                            $productObj = new Product((int)$product_id, false, $id_lang, $id_shop);
                            $img_cover = $productObj->getCover((int)$product_id);
                            $img = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($productObj->link_rewrite, (int)$img_cover['id_image']));
                            
                            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true)
                                $linkAdminpro = $this->context->link->getAdminLink('AdminProducts', true, array('id_product'=>(int) $productObj->id, 'updateproduct=1'));
                            else 
                                $linkAdminpro = $this->context->link->getAdminLink('AdminProducts') . '&id_product=' . (int) $productObj->id . '&updateproduct';
                            $listupsell['products_array'][] = array(
                                'name'=>$productObj->name,
                                'image'=> $img,
                                'url' => $linkAdminpro,
                            );
                        }
                    }
                    if ($listupsell['product_displayids'] !='') {
                        $product_displayids = explode(',', $listupsell['product_displayids']);
                        foreach ($product_displayids as $product_displayid) {
                            $productObj = new Product((int)$product_displayid, false, $id_lang, $id_shop);
                            $img_cover = $productObj->getCover((int)$product_displayid);
                            $img = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($productObj->link_rewrite, (int)$img_cover['id_image']));
                            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true)
                                $linkAdminpro = $this->context->link->getAdminLink('AdminProducts', true, array('id_product'=>(int) $productObj->id, 'updateproduct=1'));
                            else 
                                $linkAdminpro = $this->context->link->getAdminLink('AdminProducts') . '&id_product=' . (int) $productObj->id . '&updateproduct';
                            $listupsell['productsdisplay_array'][] = array(
                                'name'=>$productObj->name,
                                'image'=> $img,
                                'url' => $linkAdminpro,
                            );
                        }
                    }
                    if ($listupsell['display_cateids'] !='') {
                        $display_cateids = explode(',', $listupsell['display_cateids']);
                        $this->image_dir = _PS_CAT_IMG_DIR_;
                        foreach ($display_cateids as $display_cateid) {
                            $catObj = new Category((int)$display_cateid, $id_lang, $id_shop);
                            $id_image = Tools::file_exists_cache($this->image_dir . $catObj->id_category . '.jpg') ? (int) $catObj->id_category : Language::getIsoById($id_lang) . '-default';$id_image;
                            $listupsell['cat_array'][] = array(
                                'name'=> $catObj->name,
                                'image' => $this->context->link->getCatImageLink($catObj->name, $catObj->id_category),
                                'url' => $this->context->link->getAdminLink('AdminCategories', true, [],['id_category' => $catObj->id, 'viewcategory' => 1]) ,
                            );
                        }
                    }
                    $listupsell['extra_setting_shows'] = array(
                        'showin_product' => $this->l('Product page'),
                        'showin_cart' => $this->l('Cart summary'),
                        'showin_cart_popup' => $this->l('Cart popup'),
                        'showin_home' => $this->l('Home page'),
                        'showin_collection' => $this->l('Collection page'),
                    );
                }
            }
            $result = array(
                'error'   =>0,
                'warrning'=>'',
                'listupsells' => $listupsells,
                'number_start' => $number_start,
                'number_end' => $number_end,
                'for_pages'  => $number_page,
                'page_active'=> (int)$page_active == 0 ? 1 : (int)$page_active,
                'count' => GupselloffersModel::Countlistupsell($id_lang,  $id_shop),
            );
            die(Tools::jsonEncode($result));
        } elseif ((bool)Tools::isSubmit('deleteUpsell') == true) {
            $id_lang = (int)$this->context->language->id;
            $id_shop = (int)$this->context->shop->id;
            $id_upselloffers = (int)Tools::getValue('id_upselloffers');
            $res = true;
            $GupselloffersModel = new GupselloffersModel((int)$id_upselloffers,null,$id_shop);
            if (Validate::isLoadedObject($GupselloffersModel)) {
                if (!$GupselloffersModel->delete()) {
                        $results = array(
                            'error'   => 1,
                            'warrning'=> $this->l('An error occurred!'),
                        );
                        die(Tools::jsonEncode($results));
                } else {
                    $res &= GupselloffersModel::deleteProductcombin((int)$id_upselloffers, '',$id_shop);
                    $res &= GupselloffersModel::deleteProductcombin((int)$id_upselloffers, 'display',$id_shop);
                    if ($res) {
                        $results = array(
                            'error'   => 0,
                            'warrning'=> $this->l('SUCCESS: deleteed.'),
                            'url' => $this->context->link->getAdminLink('AdminGupselloffers').'&conf=1',
                        );
                        die(Tools::jsonEncode($results));
                    }
                }
            }
            $results = array(
                'error'   => 1,
                'warrning'=> $this->l('An error occurred!'),
            );
            die(Tools::jsonEncode($results));
        } elseif ((bool)Tools::isSubmit('GethtmlPreview') == true) {
            echo ($this->getUpsellProductHtmlPreview());die;
        } elseif((bool)Tools::isSubmit('GethtmlAddition') == true){
            $html = '';
            $additions  = Tools::getValue("additions");
            $showpage   = Tools::getValue("showpage");
            $number   = (int)Tools::getValue("number") + 1;
            $number_qty   = (int)Tools::getValue("number") == 0 ? 1 : (int)Tools::getValue("number") + 1;
            $type   = Tools::getValue("type");
            if ($additions != '') {
                $additions = Tools::jsonDecode($additions, true);
            } else {
                $additions = array();
            }
            
            $this->context->smarty->assign(array(
                'additions' => $additions,
                'html'      => 'Addition',
                'showpage'  => $showpage,
                'gupsellcurrencies' => Currency::getCurrencies(),
                'number' => $number,
                'type'   => $type,
                'number_qty' => $number_qty
            ));
            $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_.'g_upsellpro/views/templates/admin/extrahtml.tpl');
            echo ($html);die;
        } elseif (Tools::isSubmit('jSonvalue')== true){
            $volumes = Tools::getValue('volumes');
            $volumenew=array();
            if ($volumes) {
                foreach($volumes as $volume) {
                    $volumenew[] = $volume;
                }
            }
            echo (Tools::jsonEncode($volumenew));die;
        } elseif (Tools::isSubmit('Resetproduct')== true) {
            $gupselltotalprice = 0;
            $gupselltotalpricenew = 0;
            $id_attribute = Tools::getValue('id_attribute');
            $qty = (int)Tools::getValue('qty');
            $type_template = Tools::getValue('type_template');
            $html = '';
            $id_product  = (int)Tools::getValue('id_product');
            $mostpopular = Tools::getValue('mostpopular');
            
            $gupsell_idproextra = (int)Tools::getValue('gupsell_idproextra');
            $gupsell_showinpage = Tools::getValue('gupsell_showinpage');
            $gupsell_type = Tools::getValue('gupsell_type');
            $volumes = Tools::getValue('volumes');
            $val_mostpopular = 0;
            
            $newvolumes = array();
            $minqty = 0;
            $discounttype = 0;
            $discount = 0;
            $id_currency = 0;
            $reduction_tax = 0;$reduction_tax ;
            $gupselltotalpricediscount = 0;
            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
            $getConfigFieldsValues = $this->module->getConfigFieldsValues();
            if (is_array($mostpopular) && $mostpopular && isset($mostpopular[0])) {
                $val_mostpopular = (int)$mostpopular[0];
            }
            $newvolumes_extras = array();
            if ($volumes !='') {
                $newvolumes = Tools::jsonDecode($volumes, true);
                $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
                $val_mostpopular = isset($newvolumes[$gupsell_idproextra][$gupsell_showinpage]['mostpopular']) ? (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['mostpopular'] : 0 ;
                if ($val_mostpopular) {
                    $val_mostpopular = $id_product;
                }
                $newvolumes_extras[] = $newvolumes[$gupsell_idproextra];
            }
            if ($id_attribute) {
                $upsellObj = $this->objPreview();
                $gupsellvolumeprices = array();
                $products = $this->getProductsProperties($upsellObj, $id_product, $id_attribute, $gupselltotalprice, $gupsellvolumeprices, $gupselltotalpricediscount,$id_lang, $id_shop, 0,false, $newvolumes_extras, $gupsell_type, $gupsell_showinpage);
                $showproductids = Tools::getValue('productids');
                $count_pro = (int)$this->getProductsProperties($upsellObj, $showproductids, $id_attribute, $gupselltotalprice, $gupsellvolumeprices, $gupselltotalpricediscount,$id_lang,$id_shop,0, true, $newvolumes_extras, $gupsell_type, $gupsell_showinpage);
                $amountdiscount = 0;
                if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                    if ($upsellObj->apply_discount == 1) {
                        if ($upsellObj->type_discount != 1) {
                            $amountdiscount = (float)Tools::convertPrice($upsellObj->amount_discount, $upsellObj->id_currency_discount, (int)$this->context->currency->id);
                            $gupselltotalpricenew = $gupselltotalprice - (float)$gupselltotalpricediscount;
                        } else {
                            $amountdiscount = (float)$upsellObj->amount_discount;
                            $gupselltotalpricenew = $gupselltotalprice  - $gupselltotalpricediscount;
                        }
                    }
                } else {
                    if ($discounttype != 1) {
                        $amountdiscount = (float)Tools::convertPrice($discount, $id_currency, (int)$this->context->currency->id);
                        $gupselltotalpricenew = $gupselltotalprice - (float)$gupselltotalpricediscount;
                    } else {
                        $amountdiscount = (float)$discount;
                        $gupselltotalpricenew = $gupselltotalprice  - $gupselltotalpricediscount;
                    }
                }
                $version17 = false;
                if(version_compare(_PS_VERSION_, '1.7.0.0 ', '>='))
                    $version17 = true;
                $this->context->smarty->assign(array(
                    'id_shop' => $id_shop,
                    'id_lang' => $id_lang,
                    'link' => $this->context->link,
                    'gupsellproducts' => $products,
                    'upsellObj' => $upsellObj,
                    'count_product' => $count_pro,
                    'cart_token'=>Tools::getToken(false),
                    'discountactive'=>Tools::getToken(false),
                    'ajaxcal' => false,
                    'id_currency' => (int)$this->context->currency->id,
                    'gupselltotalprice' => Tools::convertPriceFull($gupselltotalprice),
                    'gupselltotalpricenew' => Tools::convertPriceFull($gupselltotalpricenew),
                    'amountdiscount' => $amountdiscount,
                    'token'          => Tools::getToken(false),
                    'urlajaxmodule'  => $this->context->link->getModuleLink($this->module->name, 'gupsellpro'),
                    'getConfigFieldsValues' => $getConfigFieldsValues,
                    'controller' => Tools::getValue('controller'),
                    'priceDisplay' => $priceDisplay,
                    'version17' => $version17,
                    'qty' => $qty,
                    'type_template' => $type_template,
                    'mostpopular'   => $val_mostpopular,
                    'volumes'    => $newvolumes,
                    'numberkey' => $gupsell_idproextra,
                    'showin'    => $gupsell_showinpage,
                    'gupselltotalpricediscount' => $gupselltotalpricediscount,
                    'cart_show' =>$this->context->link->getPageLink(
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
                if(version_compare(_PS_VERSION_, '1.7.0.0 ', '>='))
                    $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_.'g_upsellpro/views/templates/hook/miniatures/product17.tpl');
                else
                    $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_.'g_upsellpro/views/templates/hook/miniatures/product.tpl');
            }
            $results = array(
                'error' => 0,
                'html' => $html
            );
            echo Tools::jsonEncode($results);
            die();
        } elseif(Tools::isSubmit('CheckproductsaddCart')){
            $id_shop = (int)$this->context->shop->id;
            $sampleproducts = Tools::getValue('sampleproducts');
            $totalprice = 0;
            $totalprice_dc = 0;
            
            $gupsell_idproextra = (int)Tools::getValue('gupsell_idproextra');
            $gupsell_showinpage = Tools::getValue('gupsell_showinpage');
            $gupsell_type = Tools::getValue('gupsell_type');
            $volumes = Tools::getValue('volumes');
            $text_discount = '';
            $text_discountprice = 0;
            $newvolumes = array();
            $minqty = 0;
            $discounttype = 0;
            $discount = 0;
            $id_currency = 0;
            $reduction_tax = 0;
            if ($volumes !='') {
                $newvolumes = Tools::jsonDecode($volumes, true);
                $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
            }
            if ($sampleproducts !='') {
                $gupsellproObj = $this->objPreview();
                $allproducts = explode(',',$sampleproducts);
                if ($allproducts) {
                    foreach ($allproducts as $allproduct) {
                        $products = explode('|',$allproduct);
                        if ($products) {
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$products['0'], true, (int)$products['1'], 6, null, false, true, (int)$products['2']) * (int)$products['2'];
                                $totalprice += $totalpriceold;
                                
                                if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                                    if ((int)$gupsellproObj->apply_discount == 1) {
                                        if ((int)$gupsellproObj->type_discount == 0) {
                                            $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                            $totalprice_dc += $totalpriceold - $pricediscount;
                                            $text_discountprice += $pricediscount;
                                            $text_discount = Tools::displayPrice($pricediscount);
                                        } else {
                                            $totalprice_dc += $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                            $text_discountprice += ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                            $text_discount = (float)$gupsellproObj->amount_discount .$this->l('%', 'g_upsellpro');
                                        }
                                    } else {
                                        $totalprice_dc += $totalpriceold;
                                    }
                                } else {
                                    if ((int)$discounttype == 0) {
                                        $pricediscount = Tools::convertPrice((float)$discount , (int)$id_currency, $this->context->currency);
                                        $totalprice_dc += $totalpriceold - $pricediscount;
                                        $text_discountprice += $pricediscount;
                                        $text_discount = Tools::displayPrice($pricediscount);
                                    } else {
                                        $totalprice_dc += $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                                        $text_discountprice += ($totalpriceold * ((float)$discount / 100));
                                        $text_discount = (float)$discount .$this->l('%', 'g_upsellpro');
                                    }
                                }
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$products['0'], false, (int)$products['1'], 6, null, false, true, (int)$products['2']) * (int)$products['2'];
                                $totalprice += $totalpriceold;
                                if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                                    if ($gupsellproObj->apply_discount == 1) {
                                        if ($gupsellproObj->type_discount == 0) {
                                            $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                            $totalprice_dc += $totalpriceold - $pricediscount;
                                            $text_discountprice += $pricediscount;
                                            $text_discount = Tools::displayPrice($pricediscount);
                                        } else {
                                            $totalprice_dc += $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                            $text_discountprice += ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                            $text_discount = (float)$gupsellproObj->amount_discount .$this->l('%', 'g_upsellpro');
                                        }
                                    } else {
                                        $totalprice_dc += $totalpriceold;
                                    }
                                } else {
                                    if ($discounttype == 0) {
                                        $pricediscount = Tools::convertPrice((float)$discount, (int)$id_currency, $this->context->currency);
                                        $totalprice_dc += $totalpriceold - $pricediscount;
                                        $text_discountprice += $pricediscount;
                                        $text_discount = Tools::displayPrice($pricediscount);
                                    } else {
                                        $totalprice_dc += $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                                        $text_discountprice += ($totalpriceold * ((float)$discount / 100));
                                        $text_discount = (float)$discount .$this->l('%', 'g_upsellpro');
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $results = array(
                'error'         => 0,
                'totalprice'    => Tools::displayPrice(Tools::convertPriceFull($totalprice)),
                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                'text_discount' => $text_discount,
                'text_discountprice' => Tools::displayPrice(Tools::convertPriceFull($text_discountprice)),
            );
            echo Tools::jsonEncode($results);
            die();
        } elseif(Tools::isSubmit('MathQty')){
            $id_product = (int)Tools::getValue('id_product');
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            $qtyOld = (int)Tools::getValue('qtyOld');
            $gupsell_idupsell_rule = (int)Tools::getValue('gupsell_idupsell_rule');
            $totalpriceold = 0;
            $totalprice_dc = 0;
            $mathqty = Tools::getValue('mathqty');
            $gupsell_idproextra = (int)Tools::getValue('gupsell_idproextra');
            $gupsell_showinpage = Tools::getValue('gupsell_showinpage');
            $gupsell_type = Tools::getValue('gupsell_type');
            $volumes = Tools::getValue('volumes');
            if ($id_product > 0 && $gupsell_idupsell_rule > 0) {
                $gupsellproObj = $this->objPreview();
                if (!($product = new Product((int)$id_product, true, $this->context->language->id))) {
                    $results = array(
                        'error' => 1,
                        'warrning'=>$this->l('Invalid product', 'g_upsellpro'),
                    );
                    echo Tools::jsonEncode($results);
                    die();
                }
                // Don't try to use a product if not instanciated before due to errors
                if (isset($product) && $product->id) {
                    if ($id_product_attribute != 0) {
                        if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty((int)$id_product_attribute, (int)$qtyOld)) {
                            $results = array(
                                'error' => 1,
                                'warrning'=>$this->l('There is not enough product in stock.', 'g_upsellpro'),
                            );
                            echo Tools::jsonEncode($results);
                            die();
                        }
                    } elseif (!$product->checkQty((int)$qtyOld)) {
                        $results = array(
                            'error' => 1,
                            'warrning'=>$this->l('There is not enough product in stock.', 'g_upsellpro'),
                        );
                        echo Tools::jsonEncode($results);
                        die();
                    }
                } else {
                    $results = array(
                        'error' => 1,
                        'warrning'=>$this->l('Invalid product', 'g_upsellpro'),
                    );
                    echo Tools::jsonEncode($results);
                    die();
                }
                
                switch ($mathqty) {
                    case 'up':
                        if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld + 1);
    
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld + 1);
                            }
                            
                            $totalprice_dc = $totalpriceold;
                            if ($gupsellproObj->apply_discount == 1) {
                                if ($gupsellproObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                    $totalprice_dc = $totalpriceold - $pricediscount;
                                } else {
                                    $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                }
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        } else {
                            $newvolumes = array();
                            $minqty = 0;
                            $discounttype = 0;
                            $discount = 0;
                            $id_currency = 0;
                            $reduction_tax = 0;
                            if ($volumes !='') {
                                $newvolumes = Tools::jsonDecode($volumes, true);
                                $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                                $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                                $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                                $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                                $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
                            }
                            $priceDisplay     = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld + 1);
    
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld + 1);
                            }
                            
                            $totalprice_dc = $totalpriceold;
                            if ($discounttype == 0) {
                                $pricediscount = Tools::convertPrice((float)$discount , (int)$id_currency, $this->context->currency);
                                $totalprice_dc = $totalpriceold - $pricediscount;
                            } else {
                                $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        }
                    case 'down':
                        if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                            $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if ($gupsellproObj->qty > $qtyOld - 1 || $minimal_qty > $qtyOld - 1) {
                                if(!$priceDisplay || $priceDisplay == 2) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                                } elseif($priceDisplay == 1) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                                }
                                
                                $totalprice_dc = $totalpriceold;
                                if ($gupsellproObj->apply_discount == 1) {
                                    if ($gupsellproObj->type_discount == 0) {
                                        $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                        $totalprice_dc = $totalpriceold - $pricediscount;
                                    } else {
                                        $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                    }
                                }
                                $results = array(
                                    'error' => 1,
                                    'warrning'=> $this->l('You must add a minimum quantity of ' .  (int)$gupsellproObj->qty, 'g_upsellpro'),
                                    'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                    'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                                );
                                echo Tools::jsonEncode($results);
                                die();
                            }
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld - 1);
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld - 1);
                            }
                            $totalprice_dc = $totalpriceold;
                            if ($gupsellproObj->apply_discount == 1) {
                                if ($gupsellproObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount, (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                    $totalprice_dc = $totalpriceold - $pricediscount;
                                } else {
                                    $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                }
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        } else {
                            $newvolumes = array();
                            $minqty = 0;
                            $discounttype = 0;
                            $discount = 0;
                            $id_currency = 0;
                            $reduction_tax = 0;
                            if ($volumes !='') {
                                $newvolumes = Tools::jsonDecode($volumes, true);
                                $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                                $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                                $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                                $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                                $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
                            }
                            $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if ($minqty > $qtyOld - 1 || $minimal_qty > $qtyOld - 1) {
                                if(!$priceDisplay || $priceDisplay == 2) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$minqty;
                                } elseif($priceDisplay == 1) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$minqty;
                                }
                                
                                $totalprice_dc = $totalpriceold;
                                $results = array(
                                    'error' => 1,
                                    'warrning'=> $this->l('You must add a minimum quantity of ' .  (int)$minqty, 'g_upsellpro'),
                                    'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                    'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                                );
                                echo Tools::jsonEncode($results);
                                die();
                            }
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld - 1);
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld - 1);
                            }
                            $totalprice_dc = $totalpriceold;
                            if ($discounttype == 0) {
                                $pricediscount = Tools::convertPrice((float)$discount, (int)$discount, $this->context->currency);
                                $totalprice_dc = $totalpriceold - $pricediscount;
                            } else {
                                $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        }
                    default:
                        if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                            $newvolumes = array();
                            $minqty = 0;
                            $discounttype = 0;
                            $discount = 0;
                            $id_currency = 0;
                            $reduction_tax = 0;
                            if ($volumes !='') {
                                $newvolumes = Tools::jsonDecode($volumes, true);
                                $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                                $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                                $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                                $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                                $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
                            }
                            $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if ($minqty > $qtyOld || $minimal_qty > $qtyOld) {
                                if(!$priceDisplay || $priceDisplay == 2) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$minqty;
                                } elseif($priceDisplay == 1) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$minqty;
                                }

                                $totalprice_dc = $totalpriceold;
                                $results = array(
                                    'error'   => 1,
                                    'warrning'=> $this->l('You must add a minimum quantity of ' .  (int)$minqty, 'g_upsellpro'),
                                    'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                    'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                                );
                                echo Tools::jsonEncode($results);
                                die();
                            }
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$qtyOld;
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$qtyOld;
                            }
                            
                            $totalprice_dc = $totalpriceold;
                            if ($gupsellproObj->type_discount == 0) {
                                $pricediscount = Tools::convertPrice((float)$discount , (int)$id_currency, $this->context->currency);
                                $totalprice_dc = $totalpriceold - $pricediscount;
                            } else {
                                $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        } else {
                            $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if ($gupsellproObj->qty > $qtyOld || $minimal_qty > $qtyOld) {
                                if(!$priceDisplay || $priceDisplay == 2) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                                } elseif($priceDisplay == 1) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                                }

                                $totalprice_dc = $totalpriceold;
                                if ($gupsellproObj->apply_discount == 1) {
                                    if ($gupsellproObj->type_discount == 0) {
                                        $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                        $totalprice_dc = $totalpriceold - $pricediscount;
                                    } else {
                                        $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                    }
                                }
                                $results = array(
                                    'error' => 1,
                                    'warrning'=> $this->l('You must add a minimum quantity of ' .  (int)$gupsellproObj->qty, 'g_upsellpro'),
                                    'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                    'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                                );
                                echo Tools::jsonEncode($results);
                                die();
                            }
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$qtyOld;
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$qtyOld;
                            }
                            
                            $totalprice_dc = $totalpriceold;
                            if ($gupsellproObj->apply_discount == 1) {
                                if ($gupsellproObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                    $totalprice_dc = $totalpriceold - $pricediscount;
                                } else {
                                    $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                }
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        }
                }
            } else {
                $results = array(
                    'error' => 1,
                    'warrning'=>$this->l('Invalid product', 'g_upsellpro'),
                );
                echo Tools::jsonEncode($results);
                die();
            }
        } 
        parent::postProcess();
    }
    public function updateConfigdata($upsellObj) {
        /* Update Block title and template*/
        $shop_groups_list = array();
        $shops = Shop::getContextListShopID();
        $shop_context = Shop::getContext();
        $res = true;
        
        $extra_setting_shows = array(
            'showin_product',
            'showin_cart',
            'showin_cart_popup',
            'showin_home',
            'showin_collection',
        );
        foreach ($shops as $shop_id)
        {
            $shop_group_id = (int)Shop::getGroupFromShop((int)$shop_id, true);
            if (!in_array($shop_group_id, $shop_groups_list))
                $shop_groups_list[] = (int)$shop_group_id;
            foreach($extra_setting_shows as $keysetting_show){
                $res &= Configuration::updateValue('gupsell_type_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_type'), false, (int)$shop_group_id, (int)$shop_id);
                $res &= Configuration::updateValue('gupsell_stype_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_stype'), false, (int)$shop_group_id, (int)$shop_id);
                $res &= Configuration::updateValue('gupsell_position_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_position'), false, (int)$shop_group_id, (int)$shop_id);
                $res &= Configuration::updateValue('gupsell_addition_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_addition'), false, (int)$shop_group_id, (int)$shop_id);
            }
        }
        switch ($shop_context)
        {
            case Shop::CONTEXT_ALL:
                foreach($extra_setting_shows as $keysetting_show){
                    $res &= Configuration::updateValue('gupsell_type_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_type'));
                    $res &= Configuration::updateValue('gupsell_stype_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_stype'));
                    $res &= Configuration::updateValue('gupsell_position_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_position'));
                    $res &= Configuration::updateValue('gupsell_addition_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_addition'));
                }
                if (count($shop_groups_list))
                {
                    foreach ($shop_groups_list as $shop_group_id)
                    {
                        foreach($extra_setting_shows as $keysetting_show){
                            $res &= Configuration::updateValue('gupsell_type_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_type'), false, (int)$shop_group_id);
                            $res &= Configuration::updateValue('gupsell_stype_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_stype'), false, (int)$shop_group_id);
                            $res &= Configuration::updateValue('gupsell_position_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_position'), false, (int)$shop_group_id);
                            $res &= Configuration::updateValue('gupsell_addition_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_addition'), false, (int)$shop_group_id);
                        }
                    }
                }
                break;
            case Shop::CONTEXT_GROUP:
                if (count($shop_groups_list))
                {
                    foreach ($shop_groups_list as $shop_group_id)
                    {
                        foreach($extra_setting_shows as $keysetting_show){
                            $res &= Configuration::updateValue('gupsell_type_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_type'), false, (int)$shop_group_id);
                            $res &= Configuration::updateValue('gupsell_stype_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_stype'), false, (int)$shop_group_id);
                            $res &= Configuration::updateValue('gupsell_position_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_position'), false, (int)$shop_group_id);
                            $res &= Configuration::updateValue('gupsell_addition_'.$keysetting_show.'_'.$upsellObj->id, Tools::getValue($keysetting_show.'_addition'), false, (int)$shop_group_id);
                        }
                    }
                }
                break;
        }
        return $res;
    }
    public function objPreview()
    {
        $id_lang = (int)$this->context->language->id;
        $upsellObj = new GupselloffersModel();
        $langs = Language::getLanguages(false);
        $productids = Tools::getValue('productids');
        $gupsell_combinids = Tools::getValue('gupsell-combin-ids');
        $display_productids = Tools::getValue('gupsell-displayforproduct-ids');
        $gupsell_combindisplayids = Tools::getValue('gupsell-displayforcombin-ids');
        $gupsellSettings = array(
            'id_g_upsellrule' => Tools::getValue('id_upselloffers'),
            'name' => Tools::getValue('name'),
            'title' => Tools::getValue('title'),
            'description' => Tools::getValue('description'),
            'button_popupname' => Tools::getValue('button_popupname'),
            'poisition' => (int)Tools::getValue('poisition'),
            'showin_product' => (int)Tools::getValue('showin_product'),
            'showin_cart' => (int)Tools::getValue('showin_cart'),
            'showin_cart_popup' => (int)Tools::getValue('showin_cart_popup'),
            'showin_home' => (int)Tools::getValue('showin_home'),
            'showin_collection' => (int)Tools::getValue('showin_collection'),
            'qty' => (int)Tools::getValue('gupsell_min_qty')  > 0 ? (int)Tools::getValue('gupsell_min_qty') : 1,
            'apply_discount' => (int)Tools::getValue('gupsell_applydiscount'),
            'type_discount' => Tools::getValue('gupsell_applydiscounttype'),
            'amount_discount' => Tools::getValue('amount_discount'),
            'id_currency_discount' => (int)Tools::getValue('id_currency_discount'),
            'tax_discount' => (int)Tools::getValue('tax_discount'),
            'display_product' => Tools::getValue('gupsell_display'),
            'display_cateids' => Tools::getValue('gupsell-collections-ids'),
            'remove_product' => Tools::getValue('gupsell_upsellremoved'),
            'remove_product_upsell' => Tools::getValue('gupsell_productremoved_upsell'),
            'display_customqty' => (int)Tools::getValue('gupsell_customqty'),
            'type_price' => Tools::getValue('type_price'),
            'minimum_amount' => Tools::getValue('minamount'),
            'maximum_amount' => Tools::getValue('maxamount'),
            'product_ids' => $productids,
            'product_combin_ids' => $gupsell_combinids,
            'product_displayids' => $display_productids,
            'product_displaycombin_ids' => $gupsell_combindisplayids,
            'dateadd' => date("Y-m-d H:i:s", time()),
        );
        foreach ($gupsellSettings as $key=>$vals) {
            if ($key == 'name' || $key == 'title' || $key == 'description') {
                foreach ($langs as $value_lang) {
                    if ($key == 'name') {
                        $upsellObj->name[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['name'][$id_lang];
                    } elseif($key == 'title') {
                        $upsellObj->title[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['title'][$id_lang];
                    } elseif($key == 'description') {
                        $upsellObj->description[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['description'][$id_lang];
                    } elseif($key == 'button_popupname') {
                        $upsellObj->button_popupname[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['button_popupname'][$id_lang];
                    }
                }
            } elseif ($key == 'minimum_amount' 
            || $key == 'maximum_amount') {
                if (!isset($vals) || empty($vals)) {
                    $vals = array();
                }
                $upsellObj->$key = Tools::jsonEncode($vals);
            } else {
                $upsellObj->$key = $vals;
            }
        }
        return $upsellObj;
    }
    public function getUpsellProductHtmlPreview() 
    {
        $id_lang = (int)$this->context->language->id;
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = Shop::getContextShopGroupID();
        $html = '';
        $upsellObj = new GupselloffersModel();
        $langs = Language::getLanguages(false);
        $productids = Tools::getValue('productids');
        $gupsell_combinids = Tools::getValue('gupsell-combin-ids');
        $display_productids = Tools::getValue('gupsell-displayforproduct-ids');
        $gupsell_combindisplayids = Tools::getValue('gupsell-displayforcombin-ids');
        $showin = Tools::getValue('option');
        $mostpopular = Tools::getValue('mostpopular');
        $val_mostpopular = 0;
        if (is_array($mostpopular) && $mostpopular && isset($mostpopular[0])) {
            $val_mostpopular = (int)$mostpopular[0];
        }
        $id_cart = 0;
        $gupsellSettings = array(
            'id_g_upsellrule' => Tools::getValue('id_upselloffers'),
            'name' => Tools::getValue('name'),
            'title' => Tools::getValue('title'),
            'description' => Tools::getValue('description'),
            'button_popupname' => Tools::getValue('button_popupname'),
            'poisition' => (int)Tools::getValue('poisition'),
            'showin_product' => (int)Tools::getValue('showin_product'),
            'showin_cart' => (int)Tools::getValue('showin_cart'),
            'showin_cart_popup' => (int)Tools::getValue('showin_cart_popup'),
            'showin_home' => (int)Tools::getValue('showin_home'),
            'showin_collection' => (int)Tools::getValue('showin_collection'),
            'qty' => (int)Tools::getValue('gupsell_min_qty')  > 0 ? (int)Tools::getValue('gupsell_min_qty') : 1,
            'apply_discount' => (int)Tools::getValue('gupsell_applydiscount'),
            'type_discount' => Tools::getValue('gupsell_applydiscounttype'),
            'amount_discount' => Tools::getValue('amount_discount'),
            'id_currency_discount' => (int)Tools::getValue('id_currency_discount'),
            'tax_discount' => (int)Tools::getValue('tax_discount'),
            'display_product' => Tools::getValue('gupsell_display'),
            'display_cateids' => Tools::getValue('gupsell-collections-ids'),
            'remove_product' => Tools::getValue('gupsell_upsellremoved'),
            'remove_product_upsell' => Tools::getValue('gupsell_productremoved_upsell'),
            'display_customqty' => (int)Tools::getValue('gupsell_customqty'),
            'type_price' => Tools::getValue('type_price'),
            'minimum_amount' => Tools::getValue('minimum_amount'),
            'maximum_amount' => Tools::getValue('maximum_amount'),
            'product_ids' => $productids,
            'product_combin_ids' => $gupsell_combinids,
            'product_displayids' => $display_productids,
            'product_displaycombin_ids' => $gupsell_combindisplayids,
            'dateadd' => date("Y-m-d H:i:s", time()),
        );
        foreach ($gupsellSettings as $key=>$vals) {
            if ($key == 'name' || $key == 'title' || $key == 'description') {
                foreach ($langs as $value_lang) {
                    if ($key == 'name') {
                        $upsellObj->name[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['name'][$id_lang];
                    } elseif($key == 'title') {
                        $upsellObj->title[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['title'][$id_lang];
                    } elseif($key == 'description') {
                        $upsellObj->description[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['description'][$id_lang];
                    } elseif($key == 'button_popupname') {
                        $upsellObj->button_popupname[$value_lang["id_lang"]] = !empty($vals[$value_lang["id_lang"]]) ? $vals[$value_lang["id_lang"]] : $gupsellSettings['button_popupname'][$id_lang];
                    }
                }
            } elseif ($key == 'minimum_amount' 
            || $key == 'maximum_amount') {
                if (!isset($vals) || empty($vals)) {
                    $vals = array();
                }
                $upsellObj->$key = Tools::jsonEncode($vals);
            } else {
                $upsellObj->$key = $vals;
            }
        }
        $version = '';
        if(version_compare(_PS_VERSION_, '1.7.0.0 ', '>=')){
            $version = '17';
        }
        $_show_fields = $this->module->getValueConfigshowin((int)$upsellObj->id_g_upsellrule, $showin, $id_shop_group, $id_shop);
        $product_volumes = array();
        switch ($_show_fields[$showin.'_type']) {
            case 'normal':
                $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
            break;
            case 'bundle':
                $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
            break;
            case 'volume':
                $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
                $product_volumes = Tools::jsonDecode($_show_fields[$showin.'_addition'], true);
                $productids = $this->isProductexample($id_shop);
            break;
            case 'frequently':
                $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
            break;
            default:
                $tpl = 'productlist_'.$_show_fields[$showin.'_type'].$version.'.tpl';
        }
        $gupselltotalprice = 0;
        $gupsellvolumeprices = array();
        $gupselltotalpricediscount = 0;
        $priceDisplay    = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
        $gupsellproducts = $this->getProductsProperties($upsellObj, $productids,0, $gupselltotalprice, $gupsellvolumeprices, $gupselltotalpricediscount, $id_lang, $id_shop, 0, false, $product_volumes, $_show_fields[$showin.'_type'], $showin);
        $count_pro       = (int)$this->getProductsProperties($upsellObj, $productids,0, $gupselltotalprice, $gupsellvolumeprices, $gupselltotalpricediscount, $id_lang, $id_shop, 0, true, $product_volumes, $_show_fields[$showin.'_type'], $showin);
        $gupselltotalpricenew = $gupselltotalprice;
        $amountdiscount  = 0;
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
        $total_cart = 0;
        $product_carts = array();
        if($id_cart > 0) {
            $total_cart = $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
            $product_carts = $this->context->cart->getProducts(true);
        }
        $this->context->smarty->assign(array(
            'html_template' => $_show_fields[$showin.'_stype'],
            'id_shop' => $id_shop,
            'id_lang' => $id_lang,
            'link'    => $this->context->link,
            'gupsellproducts' => $gupsellproducts,
            'count_product'   => $count_pro,
            'upsellObj' => $upsellObj,
            'cart_token'=> '',
            'discountactive'=> '',
            'token'   => '',
            'ajaxcal' => true,
            'id_currency'       => (int)$this->context->currency->id,
            'gupselltotalprice' => Tools::convertPriceFull($gupselltotalprice),
            'gupselltotalpricenew' => Tools::convertPriceFull($gupselltotalpricenew),
            'amountdiscount' => $amountdiscount,
            'urlajaxmodule'  => $this->context->link->getModuleLink($this->module->name, 'gupsellpro'),
            'getConfigFieldsValues' => $this->getConfigFieldsValues(),
            'controller'   => Tools::getValue('controller'),
            'priceDisplay' => $priceDisplay,
            'version17'    => $version17,
            'total_cart'   => Tools::displayPrice($total_cart, (int)$this->context->currency->id),
            'product_carts'=> $product_carts,
            'mostpopular'  => $val_mostpopular,
            'volumes'      => $product_volumes,
            'showin'       => $showin,
            'gupsellvolumeprices' => $gupsellvolumeprices,
            'gupselltotalpricediscount' => $gupselltotalpricediscount,
            'cart_show'    =>$this->context->link->getPageLink(
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
        $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_.'g_upsellpro/views/templates/admin/previews/'.$tpl);
        return $html;
    }
    public function getConfigFieldsValues()
    {
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = Shop::getContextShopGroupID();
        $languages = Language::getLanguages(false);
        $adminSettings = AdminSettings::getUpsellSettings((int)$id_shop_group,  $languages, (int)$id_shop);
        return $adminSettings;
    }
    
    public function isProductexample($id_shop=0)
    {
        $id_shop;
        $id = Db::getInstance()->getValue('
            SELECT p.`id_product`
            FROM `' . _DB_PREFIX_ . 'product` p
            ' . Shop::addSqlAssociation('product', 'p') . '
            WHERE p.`active` = 1 ORDER BY p.`id_product` DESC');
        return (int)$id;
    }
    public function getProductsProperties($upsellObj, $productids, $id_product_attribute = 0, &$gupselltotalprice, &$gupsellvolumeprices, &$gupselltotalpricediscount, $id_lang, $id_shop, $limit = 0, $count=false, $product_volumes=array(), $template='', $showin=''){
        
        if ($productids =='') {
            return array();
        }
        $product_sort = array();
        $context = $this->context;
        $id_currency = (int)$context->currency->id;
        $nb_days_new_product = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        $combins = Tools::jsonDecode(Tools::getValue('gupsell-combin-ids'), true);
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
        $sql .= ' FROM `'._DB_PREFIX_.'product` p
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
        if ($count) {
            return (int)Db::getInstance()->getValue($sql);
        }
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
                                $combins_new = array();
                                $ProductAttributesIds = $newproduct->getProductAttributesIds((int)$result['id_product']);
                                if ($ProductAttributesIds) {
                                    foreach ($ProductAttributesIds as $ProductAttributesId) {
                                        $combins_new[] = $ProductAttributesId['id_product_attribute'];
                                    }
                                }
                                if ((int)Tools::getValue('qty') > 0) {
                                    $product_volume[$showin]['minqty'] = (int)Tools::getValue('qty');
                                }
                                $result['AttributesGroups'] = $this->module->assignAttributeGroup($newproduct, $combins_new, $result['id_product_attribute'], true );
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
                                        $gupselltotalpricediscount += $pricediscount;
                                    } else {
                                        $result['price_new'] = $price - ($price * ((float)$product_volume[$showin]['discount'] / 100));
                                        $gupselltotalpricediscount += ($price * ((float)$product_volume[$showin]['discount'] / 100));
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
                                        $gupselltotalpricediscount += $pricediscount;
                                    } else {
                                        $result['price_tax_exc_new'] = $price - ($price * ((float)$product_volume[$showin]['discount'] / 100));
                                        $gupselltotalpricediscount += ($price * ((float)$product_volume[$showin]['discount'] / 100));
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
                        if ((int)$id_product_attribute > 0) {
                            $result['id_product_attribute'] = (int)$id_product_attribute;
                        }
                        $combins_new = array();
                        if ($combins && isset($combins[(int)$result['id_product']])) {
                            $combins_new = explode(',', $combins[(int)$result['id_product']]);
                            if (!in_array($result['id_product_attribute'], $combins_new)) {
                                $result['id_product_attribute'] = (int) $this->module->arrayshift($combins_new);
                            }
                        }
                        $result['AttributesGroups'] = $this->module->assignAttributeGroup($newproduct, $combins_new, $result['id_product_attribute'] );
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
        if (!$context->customer) {
            $context->customer = new Customer();
        }
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
    public function actionProductupsell (
    $gupselloffersObj,
    $productids,
    $gupsell_attribute,
    $display_productids,
    $gupsell_attributedisplay,
    $mostpopular,
    $id_shop)
    {
        $res = true;
        /*product upsell*/
        $delete_oldproduct = true;
        $check_oldidupsell = GupselloffersModel::getProductcombinidupsell((int)$gupselloffersObj->id, '', $id_shop);
        if ($check_oldidupsell > 0) {
            $delete_oldproduct = GupselloffersModel::deleteProductcombin((int)$gupselloffersObj->id,'',$id_shop);
        }
        if ($delete_oldproduct && !empty($productids)) {
            $productids = explode(',', $productids);
            $gupsell_attribute = Tools::jsonDecode($gupsell_attribute, true);
            foreach ($productids as $productid) {
                if ((int)$productid > 0) {
                    $active_mostpopular = 0;
                    if (is_array($mostpopular) && in_array($productid,$mostpopular)) {
                        $active_mostpopular = 1;
                    }
                    if (isset($gupsell_attribute[$productid]) && !empty($gupsell_attribute[$productid])) {
                        $gupsell_attribute_news = explode(',', $gupsell_attribute[$productid]);
                        foreach ($gupsell_attribute_news as $gupsell_attribute_new) {
                            if ($gupsell_attribute_new) {
                                $res &=GupselloffersModel::addProductcombin((int)$gupselloffersObj->id, (int)$productid, (int)$gupsell_attribute_new, '', $id_shop, $active_mostpopular);
                            }
                        }
                    } else {
                        $gupsell_idattribute = (int)Product::getDefaultAttribute($productid);
                        $res &=GupselloffersModel::addProductcombin((int)$gupselloffersObj->id, (int)$productid, (int)$gupsell_idattribute, '', $id_shop, $active_mostpopular);
                    }
                }
            }
        }
        /*display product upsell */
        $check_oldidupselldisplay = GupselloffersModel::getProductcombinidupsell((int)$gupselloffersObj->id, 'display', $id_shop);
        if ($check_oldidupselldisplay > 0) {
            $delete_oldproductdisplay = GupselloffersModel::deleteProductcombin((int)$gupselloffersObj->id, 'display',$id_shop);
        }
        $delete_oldproductdisplay = true;
        if ($delete_oldproductdisplay && !empty($productids)) {
            $displayproductids = explode(',', $display_productids);
            $gupsell_attributedisplay = Tools::jsonDecode($gupsell_attributedisplay, true);
            foreach ($displayproductids as $displayproductid) {
                if ((int)$displayproductid > 0) {
                    if (isset($gupsell_attributedisplay[$displayproductid]) && !empty($gupsell_attributedisplay[$displayproductid])) {
                        $gupsell_attributedisplay_news = explode(',', $gupsell_attributedisplay[$displayproductid]);
                        foreach ($gupsell_attributedisplay_news as $gupsell_attributedisplay_new) {
                            if ($gupsell_attributedisplay_new) {
                                $res &=GupselloffersModel::addProductcombin((int)$gupselloffersObj->id, (int)$displayproductid, (int)$gupsell_attributedisplay_new, 'display', $id_shop);
                            }
                        }
                    } else {
                        $gupsell_idattributedisplay = (int)Product::getDefaultAttribute($displayproductid);
                        $res &=GupselloffersModel::addProductcombin((int)$gupselloffersObj->id, (int)$displayproductid, (int)$gupsell_idattributedisplay, 'display', $id_shop);
                    }
                }
            }
        }
        return $res;
    }
    /*add img product*/
    public function addimageProduct($file, $productObj)
    {
        $product_has_images = (bool) Image::getImages((int)$this->context->language->id, (int)$productObj->id);
        if ($product_has_images) {
            return false;
        }
        $image = new Image();
        $image->id_product = (int)$productObj->id;
        $image->position = Image::getHighestPosition($productObj->id) + 1;
        $image->cover = true;
        if ($image->add()) {
            $product_has_images = true;
            $image->associateTo(Shop::getContextListShopID());
            if (!$this->copyImg($productObj->id, $image->id, $file, 'products', true)) {
                $image->delete();
            }
        }
        return true;
    }
    public function copyImg($id_product, $id_image = null, $file, $entity = 'products', $regenerate = true) {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));
        $imageobj = new Image($id_image);
        $path = $imageobj->getPathForCreation();
        $orig_tmpfile = $tmpfile;
        $tmpfile = $file['image_product']['tmp_name'];
        // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
        if (!ImageManager::checkImageMemoryLimit($tmpfile)) {
            @unlink($tmpfile);
            return false;
        }
        $tgt_width = $tgt_height = 0;
        $src_width = $src_height = 0;
        $error = 0;
        ImageManager::resize($tmpfile, $path . '.jpg', null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height);
        $images_types = ImageType::getImagesTypes($entity, true);
        if ($regenerate) {
            $path_infos = array();
            $path_infos[] = array(
                $tgt_width,
                $tgt_height,
                $path . '.jpg');
            foreach ($images_types as $image_type) {
                $tmpfile = self::get_best_path($image_type['width'], $image_type['height'], $path_infos);
                if (ImageManager::resize($tmpfile, $path . '-' . Tools::stripslashes($image_type['name']) . '.jpg', $image_type['width'], $image_type['height'], 'jpg', false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height)) {
                    // the last image should not be added in the candidate list if it's bigger than the original image
                    if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                        $path_infos[] = array(
                            $tgt_width,
                            $tgt_height,
                            $path . '-' . Tools::stripslashes($image_type['name']) . '.jpg');
                    }
                    if ($entity == 'products') {
                        if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '.jpg')) {
                            unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '.jpg');
                        }
                        if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '_' . (int)$this->context->shop->id . '.jpg')) {
                            unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '_' . (int)$this->context->shop->id . '.jpg');
                        }
                    }
                }
                if (in_array($image_type['id_image_type'], $watermark_types)) {
                    Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_product));
                }
            }
        }
        unlink($orig_tmpfile);
        return true;
    }
    private static function get_best_path($tgt_width, $tgt_height, $path_infos) {
        $path_infos = array_reverse($path_infos);
        $path = '';
        foreach ($path_infos as $path_info) {
            list($width, $height, $path) = $path_info;
            if ($width >= $tgt_width && $height >= $tgt_height) {
                return $path;
            }
        }
        return $path;
    }
    /*get html product upsell -> display*/
    public function producthtml($productids, $productcombieids,$display='', $mostpopular=0)
    {
        $productcombieids;
        $productidshtml ='';
        $link = $this->context->link;
        foreach($productids as $productid) {
            if ((int)$productid > 0) {
                $productObj = new Product((int)$productid, false,(int)$this->context->language->id,(int)$this->context->shop->id);
                $id_img = Product::getCover($productObj->id);
                $img = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($productObj->link_rewrite, (int)$id_img['id_image']));
                $url = $link->getAdminLink('AdminProducts') . '&id_product=' . (int) $productObj->id . '&updateproduct';
                $this->context->smarty->assign(array(
                    'combination_html' => 'producthtml',
                    'productObj' => $productObj,
                    'Productcombiehtml' => '',
                    'display' => $display,
                    'img' => $img,
                    'url' => $url,
                    'mostpopular' => $mostpopular
                ));
                
                $productidshtml .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . "g_upsellpro/views/templates/admin/combination_html.tpl");
                
            }
        }
        return $productidshtml;
    }
    /*get html collection upsell -> display*/
    public function cathtml($cats)
    {
        $cathtml ='';
        $this->image_dir = _PS_CAT_IMG_DIR_;
        foreach($cats as $cat) {
            if ((int)$cat > 0) {
                $catObj = new Category((int)$cat,(int)$this->context->language->id,(int)$this->context->shop->id);
               
                $catObj->id_image = Tools::file_exists_cache($this->image_dir . $catObj->id_category . '.jpg') ? (int) $catObj->id_category : Language::getIsoById((int)$this->context->language->id) . '-default';
                $catObj->url_image = $this->context->link->getCatImageLink($catObj->name, $catObj->id_category);
                $catObj->legend = 'no picture';
                $this->context->smarty->assign(array(
                    'combination_html' => 'cathtml',
                    'cat' => $catObj,
                    'img' => $catObj->url_image,
                    'url' => '',
                ));
                
                $cathtml .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . "g_upsellpro/views/templates/admin/combination_html.tpl");
                
            }
        }
        return $cathtml;
    }
    
    /** Product Sreach Ajax **/
    public function searchProduct($display='')
    {
        $link = $this->context->link;
        $query = Tools::getValue('q', false);
        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, image_shop.`id_image` id_image,image.`id_image`, il.`legend`, p.`cache_default_attribute`
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('pl').')
                LEFT JOIN `'._DB_PREFIX_.'image` image ON image.`id_product` = p.`id_product` AND image.`cover`=1
                LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                    ON (image_shop.cover=1 AND image_shop.id_shop='.(int)$this->context->shop->id.')
                LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$this->context->language->id.')
                WHERE';
        if (!$query or $query == '' or  Tools::strlen($query) < 1) {
            $sql .=' p.active = 1';
        } else {
            $sql .=' (pl.`name` LIKE \'%'.pSQL($query).'%\' OR p.`reference` LIKE \'%'.pSQL($query).'%\' OR  p.`id_product`='.(int)$query.') AND p.active = 1';
        }
        $sql .=' GROUP BY p.id_product limit '.(int)Tools::getValue('search_number').','.((int)Tools::getValue('search_number') + 15);
        $items = Db::getInstance()->executeS($sql);
        if ($items) {
            $tmp = array();
            foreach ($items as $item)
            {
                $product = new Product($item['id_product']);
                $_html = $this->Productcombiehtml($product, array(), $display);
                $img = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($item['link_rewrite'], (int)$item['id_image']));
                $url = $link->getProductLink($item['id_product'],null,null,null,null,(int)$this->context->shop->id);
                $price = Tools::disPlayprice($product->getPriceStatic((int)$product->id,true));
                $tmp[] = trim($item['name']).'|'.(int)($item['id_product']).'|'. $img .'|'.$price. '|' . $url .'|' . $display .'|'. $_html ."\n";
            }
            echo Tools::jsonEncode($tmp);
            die;
        } else {
            return '';
        }
    }
    public function Productcombiehtml($product , $combies= array(),$display='',$checkedall=true)
    {
        $attributes = $product->getAttributesGroups((int)$this->context->language->id);
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
        foreach ($combinations as &$combination)
        {
            $checked = '';
            $combination['attributes'] = rtrim($combination['attributes'], ' - ');
            $id_combin_default = (int)Product::getDefaultAttribute((int)$product->id);
            if ((isset($combies) && in_array((int)$combination['id_product_attribute'], $combies)) || ($checkedall && $display!='') || ($checkedall && (int)$id_combin_default == (int)$combination['id_product_attribute'] && $display=='')) {
                //$checked = 'checked="checked"';
            }
            $combination['checked'] =  $checked;
            $combination['combination_price'] = Tools::disPlayprice($product->getPriceStatic((int)$product->id,true,(int)$combination['id_product_attribute']));
        }
        $_html = '';
        if(isset($combinations) && !empty($combinations))
        {
            $this->context->smarty->assign(array(
                'combinations'  => $combinations,
                'combination_html' => 'check_box',
                'display' => $display,
                'id_product' => (int)$product->id,
            ));
            $_html = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "g_upsellpro/views/templates/admin/combination_html.tpl");
        }
        return $_html;
    }
    public function searchCollection () 
    {
        $shop_id = (int)$this->context->shop->id;
        $id_lang = (int)$this->context->language->id;
        return $this->customGetNestedCategories($shop_id, null, $id_lang, true, null, true, '', '', '');
    }
    public function searchProductByCollection($categories, $categoriesids=array())
    {
        $html = '';
        foreach ($categories as $category) {
            $shop = (object) Shop::getShop((int)$category['id_shop']);
            if (in_array($category['id_category'],$categoriesids)) {
                $cat = new Category((int)$category['id_category'], (int)$this->context->language->id);
                
                $url = $this->context->link->getCategoryLink((int)$category['id_category'],null,null,null,null,(int)$this->context->shop->id);
                $id_image = Tools::file_exists_cache($this->image_dir . (int)$category['id_category'] . '.jpg') ? (int)$category['id_category'] : Language::getIsoById((int)$this->context->language->id) . '-default';$id_image;
                $url_image = $this->context->link->getCatImageLink($cat->name,  (int)$category['id_category'] );
                $this->context->smarty->assign(array(
                    'cat'    => $cat,
                    'url'    => $url,
                    'img'    => $url_image,
                    'shop'   => $shop,
                    'combination_html' => 'cathtml',
                ));
                $tpl    = _PS_MODULE_DIR_.'g_upsellpro/views/templates/admin/combination_html.tpl';
                $html  .= Context::getContext()->smarty->fetch($tpl);
                if (isset($category['children']) && !empty($category['children'])) {
                    $html .= $this->searchProductByCollection($category['children'], $categoriesids);
                }
            }
        }
        return $html;
    }
    /*getnest Category*/
    public function customGetNestedCategories($shop_id, $root_category = null, $id_lang = false, $active = false, $groups = null, $use_shop_restriction = true, $sql_filter = '', $sql_sort = '', $sql_limit = '')
    {
        $sql_filter;
        $categories = array();
        if (isset($root_category) && !Validate::isInt($root_category)) {
            die(Tools::displayError());
        }
        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }
        if (isset($groups) && Group::isFeatureActive() && !is_array($groups)) {
            $groups = (array)$groups;
        }
        $query = Tools::getValue('q', false);
        $search_number = (int)Tools::getValue('search_number', false);
        if (!$query or $query == '' or  Tools::strlen($query) < 1) {
            $sql_limit  =  (int)$search_number .','. ((int)$search_number + 15);
        } else {
            $sql_limit  =  (int)$search_number .','. ((int)$search_number + 15);
        }
        $sql_limit  = '';
        $this->image_dir = _PS_CAT_IMG_DIR_;

        $cache_id = 'Category::getNestedCategories_'.md5((int)$shop_id.(int)$root_category.(int)$id_lang.(int)$active.(int)$active
            .(isset($groups) && Group::isFeatureActive() ? implode('', $groups) : ''));
        if (!Cache::isStored($cache_id)) {
            $_groups = implode(',',array_map('intval', explode(',', $groups)));
            $sql = '
                SELECT c.*, cl.*
                FROM `'._DB_PREFIX_.'category` c
                INNER JOIN `'._DB_PREFIX_.'category_shop` category_shop ON (category_shop.`id_category` = c.`id_category` AND category_shop.`id_shop` = "'.(int)$shop_id.'")
                LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_shop` = "'.(int)$shop_id.'")
                WHERE 1 '.($id_lang ? 'AND cl.`id_lang` = '.(int)$id_lang : '');
                
            if (!$query or $query == '' or  Tools::strlen($query) < 1) {
                $sql .= '';
            } else {
                $sql .= ' AND (cl.`name` LIKE \'%'.pSQL($query).'%\' OR cl.`link_rewrite` LIKE \'%'.pSQL($query).'%\' OR  c.`id_category`='.(int)$query.')';
            }
            $sql .= ''.($active ? ' AND (c.`active` = 1 OR c.`is_root_category` = 1)' : '').'
                '.(isset($groups) && Group::isFeatureActive() ? ' AND cg.`id_group` IN ('.pSQL($_groups).')' : '').'
                '.(!$id_lang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '').'
                '.($sql_sort != '' ? pSQL($sql_sort) : ' ORDER BY c.`level_depth` ASC').'
                '.($sql_sort == '' && $use_shop_restriction ? ', category_shop.`position` ASC' : '');
            if ($sql_limit !='') {
                $sql .= ' limit '. pSQL($sql_limit);
            }
            $result = Db::getInstance()->executeS($sql);
            
            foreach ($result as &$row) {
                $row['id_image'] = Tools::file_exists_cache($this->image_dir . $row['id_category'] . '.jpg') ? (int) $row['id_category'] : Language::getIsoById($id_lang) . '-default';
                $row['url_image'] = $this->context->link->getCatImageLink($row['name'], $row['id_category']);
                $row['legend'] = 'no picture';
            }
            $categories = $result;
        }
        return $categories;
    }
}
