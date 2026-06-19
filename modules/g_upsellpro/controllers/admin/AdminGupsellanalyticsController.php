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
class AdminGupsellanalyticsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->display = 'edit';
        $this->bootstrap = true;
        parent::__construct();
        $this->meta_title = $this->l('Analytic');
		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
    }
    public function initContent()
	{
		$this->display = 'Dashboard';
        $this->content = '';
        $this->initTabModuleList();
		$this->initToolbar();
		$this->initPageHeaderToolbar();
        $controller = Tools::getValue('controller');
        $link = $this->context->link;
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
		$this->toolbar_title[] = $this->l('Analytic');
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
        $useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https:/'.'/' : 'http:/'.'/';
        $base_url = $protocol_content.Tools::getHttpHost().__PS_BASE_URI__;
        $getConfigFieldsValues = $this->getConfigFieldsValues();
        $date_from = date('Y-m-d',strtotime('-'.(int)$getConfigFieldsValues['GSELL_ANALYTIC_TIME'].' day', strtotime(date('Y-m-d'))));
        $date_to = date('Y-m-d',strtotime('+1 day', strtotime(date('Y-m-d'))));
        $viewsdatas = AdminSettings::getFieldsanytic('views',$date_from,$date_to,$id_shop);
        $mainchartdatas = array(
            array(
                'values' => $viewsdatas,
                'key' => $this->l('Views'),
                'color' => "rgb(23, 119, 182)"
            ),
        );
        if(version_compare(_PS_VERSION_, '1.6', '>=')){
            Media::addJsDef(array(
                'mainchartdatas' => $mainchartdatas,
                'gchart_date_format' => Context::getContext()->language->date_format_lite,
                'currency_format' => $this->context->currency->format,
                'currency_sign' => $this->context->currency->sign,
                'currency_blank' => $this->context->currency->blank,
                'priceDisplayPrecision' => $this->context->currency->blank,
            ));
        }
        $listupsellAnalytic_addcarts = (int) AdminSettings::getTotalFieldsanytic('addcarts', 0,$date_from ,$date_to ,$id_shop);
        $listupsellAnalytic_views = (int)AdminSettings::getTotalFieldsanytic('views', 0,$date_from ,$date_to ,$id_shop);
        $listupsellAnalytic_transactions = (float)AdminSettings::getTotalFieldsanytic('transactions', 0,$date_from,$date_to,$id_shop);
        /*assign tpl*/
        $this->context->smarty->assign(
            array(
                'link' => $link,
                'base_url' => $base_url,
                'total_views' => (int)AdminSettings::getTotalFieldsanytic('views', 0,$date_from ,$date_to ,$id_shop),
                'addcarts' => (int) AdminSettings::getTotalFieldsanytic('addcarts', 0,$date_from ,$date_to ,$id_shop),
                'transactions' => (float)AdminSettings::getTotalFieldsanytic('transactions', 0,$date_from,$date_to,$id_shop),
                'sales' => Tools::displayPrice(Tools::convertPriceFull((float)AdminSettings::getTotalFieldsanytic('sales', 0,$date_from,$date_to,$id_shop), new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT')), $this->context->currency)),
                'take_rate' => (int)$listupsellAnalytic_views > 0 ? Tools::ps_round((((int)$listupsellAnalytic_addcarts + (int)$listupsellAnalytic_transactions) / (int)$listupsellAnalytic_views) * 100, _PS_PRICE_COMPUTE_PRECISION_) .'%' : 0,
                'getConfigFieldsValues' => $getConfigFieldsValues,
            ));
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Analytic'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'gupsell_analytic',
                    'name' => 'gupsell_analytic',
                ),
            )
        );
        return parent::renderForm();
    }
    
    public function postProcess()
    {
        if((bool)Tools::isSubmit('Changeanalytic') == true) {
            $action = Tools::getValue('getaction');
            $id_shop = $this->context->shop->id;
            $getConfigFieldsValues = $this->getConfigFieldsValues();
            $date_from = date('Y-m-d',strtotime('-'.(int)$getConfigFieldsValues['GSELL_ANALYTIC_TIME'].' day', strtotime(date('Y-m-d'))));
            $date_to = date('Y-m-d',strtotime('+1 day', strtotime(date('Y-m-d'))));
            $viewsdatas = AdminSettings::getFieldsanytic($action,$date_from,$date_to,$id_shop);
            $color = "rgb(23, 119, 182)";
            switch($action) {
                case 'views':
                    $color = "rgb(23, 119, 182)";
                break;
                case 'addcarts':
                    $color = "rgb(107, 57, 156)";
                break;
                case 'transactions':
                    $color = "rgb(44, 161, 33)";
                break;
                case 'sales':
                    $color = "rgb(230, 20, 9)";
                break;
                case 'take_rate':
                    $color = "rgb(255, 127, 0)";
                break;
                default:
            break;
            }
            $mainchartdatas = array(
                array(
                    'values' => $viewsdatas,
                    'key' => $action,
                    'color' => $color
                ),
            );
            if(version_compare(_PS_VERSION_, '1.6', '>=')){
                Media::addJsDef(array(
                    'mainchartdatas' => $mainchartdatas,
                    'gchart_date_format' => Context::getContext()->language->date_format_lite,
                    'currency_format' => $this->context->currency->format,
                    'currency_sign' => $this->context->currency->sign,
                    'currency_blank' => $this->context->currency->blank,
                    'priceDisplayPrecision' => $this->context->currency->blank,
                ));
            }
            
            echo Tools::jsonEncode($mainchartdatas);
            die();
        } elseif ((bool)Tools::isSubmit('listupsellAnalytic') == true) {
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
            $listupsellAnalytics = GupselloffersModel::listupsell(array(), $number_start, $number_end, $id_lang,  $id_shop);
            $date_from = date('Y-m-d',strtotime('-30 day', strtotime(date('Y-m-d'))));
            $date_to = date('Y-m-d');
            if ($listupsellAnalytics) {
                foreach ($listupsellAnalytics as &$listupsellAnalytic) {
                    $addcarts = (int)AdminSettings::getTotalFieldsanytic('addcarts', $listupsellAnalytic['id_g_upsellrule'],$date_from,$date_to,$id_shop);
                    $transactions = (float)AdminSettings::getTotalFieldsanytic('transactions', $listupsellAnalytic['id_g_upsellrule'],$date_from,$date_to,$id_shop);
                    $views = (int)AdminSettings::getTotalFieldsanytic('views', $listupsellAnalytic['id_g_upsellrule'],$date_from,$date_to,$id_shop);
                    $take_rate = (int)$views != 0 ? (((int)$addcarts + (float)$transactions) / (int)$views) * 100 : 0;
                    $listupsellAnalytic['views'] = $views;
                    $listupsellAnalytic['addcarts'] = $addcarts;
                    $listupsellAnalytic['sales'] = Tools::displayPrice(Tools::convertPriceFull((float)AdminSettings::getTotalFieldsanytic('sales', $listupsellAnalytic['id_g_upsellrule'],$date_from,$date_to,$id_shop), new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT')), $this->context->currency));
                    $listupsellAnalytic['transactions'] = $transactions;
                    $listupsellAnalytic['take_rate'] = Tools::ps_round($take_rate, _PS_PRICE_COMPUTE_PRECISION_) .'%';
                }
            }
            $result = array(
                'error'   =>0,
                'warrning'=>'',
                'listupsellAnalytics' => $listupsellAnalytics,
                'number_start' => $number_start,
                'number_end' => $number_end,
                'for_pages'  => $number_page,
                'page_active'=> (int)$page_active == 0 ? 1 : (int)$page_active,
                'count' => $count,
            );
            die(Tools::jsonEncode($result));
        } elseif ((bool)Tools::isSubmit('updateTime') == true) {
            $id_shop = (int)$this->context->shop->id;
            $id_lang = (int)$this->context->language->id;
            $time = (int)Tools::getValue('time');
            $res = true;
            $shop_context = Shop::getContext();
            $bool_fields = array(
                'GSELL_ANALYTIC_TIME',
            );
            $shop_groups_list = array();
			$shops = Shop::getContextListShopID();
			foreach ($shops as $shop_id)
			{
				$shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);
				if (!in_array($shop_group_id, $shop_groups_list))
					$shop_groups_list[] = $shop_group_id;
                foreach($bool_fields as $bool_field)
				    $res &= Configuration::updateValue($bool_field, $time, false, $shop_group_id, $shop_id);
            }
			switch ($shop_context)
			{
				case Shop::CONTEXT_ALL:
                    foreach($bool_fields as $bool_field)
					    $res &= Configuration::updateValue($bool_field, $time);
                    if (count($shop_groups_list))
					{
						foreach ($shop_groups_list as $shop_group_id)
						{
						    foreach($bool_fields as $bool_field)
							     $res &= Configuration::updateValue($bool_field, $time, false, $shop_group_id);
                        }
					}
					break;
				case Shop::CONTEXT_GROUP:
					if (count($shop_groups_list))
					{
						foreach ($shop_groups_list as $shop_group_id)
						{
						    foreach($bool_fields as $bool_field)
							     $res &= Configuration::updateValue($bool_field, $time, false, $shop_group_id);
                        }
					}
					break;
            }
            $result = array(
                'error'   =>0,
                'warrning'=> $this->l('Update Succesfull'),
            );
            die(Tools::jsonEncode($result));
        }
        parent::postProcess();
    }
    
    public function getConfigFieldsValues()
    {
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = Shop::getContextShopGroupID();
        $languages = Language::getLanguages(false);
        $adminSettings = AdminSettings::getUpsellSettings((int)$id_shop_group,  $languages, (int)$id_shop);
        return $adminSettings;
    }
}
