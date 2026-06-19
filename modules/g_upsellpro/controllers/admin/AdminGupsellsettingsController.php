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
class AdminGupsellsettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->display = 'edit';
        $this->bootstrap = true;
        parent::__construct();
        $this->meta_title = $this->l('Settings');
		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
    }
    public function initContent()
	{
        $controller = Tools::getValue('controller');
        $link = $this->context->link;
		$this->display = 'Settings';
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
		$this->toolbar_title[] = $this->l('Settings');
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
        $useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https:/'.'/' : 'http:/'.'/';
        $base_url = $protocol_content.Tools::getHttpHost().__PS_BASE_URI__;
        /*assign tpl*/
        $this->context->smarty->assign(
            array(
                'link' => $link,
                'base_url' => $base_url,
            ));
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'gupsell_settings',
                    'name' => 'gupsell_settings',
                ),
            )
        );
        $this->fields_value = $this->getConfigFieldsValues();
        return parent::renderForm();
    }
    public function getConfigFieldsValues()
    {
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = Shop::getContextShopGroupID();
        $languages = Language::getLanguages(false);
        $adminSettings = AdminSettings::getUpsellSettings((int)$id_shop_group,  $languages, (int)$id_shop);
        return $adminSettings;
    }
    public function postProcess()
	{
        if (Tools::isSubmit('saveGupsellConfig'))
        {
            $res = true;
            $shop_context = Shop::getContext();
            $bool_fields = array(
                'GSELL_MAIN_BUTTON_BACKGROUND',
                'GSELL_MAIN_BUTTON_COLOR',
                'GSELL_MAIN_LABEL',
                'GSELL_SETTING_BUTTON_TOTAL',
                'GSELL_SETTING_BUTTON_ADCART',
                'GSELL_SETTING_BUTTON_UPGRADE',
                'GSELL_SETTING_BUTTON_CHECKOUT',
                'GSELL_SETTING_BUTTON_UPGRADECART',
                'GSELL_SETTING_BUTTON_NOTHANKS',
                'GSELL_SETTING_CUSTOM_CSS',
                'GSELL_MAIN_FLOATING_POSITION',
                'GSELL_MAIN_POPUP_DELAY',
                'GSELL_SETTING_BUTTON_FLOATING',
                'GSELL_SETTING_MOST_POPULAR',
                'GSELL_MAIN_MOSTPOPOLAR_BACKGROUND',
                'GSELL_MAIN_MOSTPOPOLAR_COLOR',
                'GSELL_MAIN_MOSTPOPOLAR_BACKGROUND_ITEM',
            );
            $shop_groups_list = array();
			$shops = Shop::getContextListShopID();
			foreach ($shops as $shop_id)
			{
				$shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);
				if (!in_array($shop_group_id, $shop_groups_list))
					$shop_groups_list[] = $shop_group_id;
                foreach($bool_fields as $bool_field)
				    $res &= Configuration::updateValue($bool_field, Tools::getValue($bool_field), false, $shop_group_id, $shop_id);
            }
			switch ($shop_context)
			{
				case Shop::CONTEXT_ALL:
                    foreach($bool_fields as $bool_field)
					    $res &= Configuration::updateValue($bool_field, Tools::getValue($bool_field));
                    if (count($shop_groups_list))
					{
						foreach ($shop_groups_list as $shop_group_id)
						{
						    foreach($bool_fields as $bool_field)
							     $res &= Configuration::updateValue($bool_field, Tools::getValue($bool_field), false, $shop_group_id);
                        }
					}
					break;
				case Shop::CONTEXT_GROUP:
					if (count($shop_groups_list))
					{
						foreach ($shop_groups_list as $shop_group_id)
						{
						    foreach($bool_fields as $bool_field)
							     $res &= Configuration::updateValue($bool_field, Tools::getValue($bool_field), false, $shop_group_id);
                        }
					}
					break;
            }
            
            if (!$res) {
				$this->_html .= $this->module->displayError($this->l('The configuration could not be updated.'));
            } else {
                $adminSettings = $this->getConfigFieldsValues();
                $this->parseMyCss($adminSettings);
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminGupsellsettings', true).'&conf=4');
            }
        }
        parent::postProcess();
    }
    
    /*clear cache */
    public function clearcache()
    {
        $clearSmartyCache = Tools::clearSmartyCache();
        $clearXMLCache = Tools::clearXMLCache();
        $clearXMLCache;
        $clearSmartyCache;
    }

    /*css my font*/
    public function parseMyCss($adminSettings=array()){
        $filename = 'mycss';
        $useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https:/'.'/' : 'http:/'.'/';
        $base_url    = $protocol_content.Tools::getHttpHost().__PS_BASE_URI__;

        $css_dir  = _PS_MODULE_DIR_.'g_upsellpro/views/css/front/';
        $css_addir = _PS_MODULE_DIR_.'g_upsellpro/views/css/admin/';
        if (!is_dir($css_dir.'frontcss/')){
            @mkdir($css_dir.'frontcss/', 0755);
        }
        if(!file_exists($css_dir.'/index.php'))
            @copy(_PS_MODULE_DIR_.'g_upsellpro/index.php', $css_dir.'frontcss/index.php');
        $file = $css_dir.'frontcss/'.$filename.'.css';
        $fields_value = array('fields_value'=>$adminSettings, 'base_url' => $base_url);
        Context::getContext()->smarty->assign($fields_value);
        $tpl = _PS_MODULE_DIR_.'g_upsellpro/views/templates/admin/cssbase.tpl';
        $cssfile = Context::getContext()->smarty->fetch($tpl);
        $handle  = fopen($file, 'w+');
        fwrite($handle, self::minify_css($cssfile));
        fclose($handle);
        $file   = $css_addir .$filename.'.css';
        $handle = fopen($file, 'w+');
        fwrite($handle, self::minify_css($cssfile));
        fclose($handle);
    }
    
    public static function  minify_css($input) {
        if(method_exists('Tools','minifyCSS')){
            return Tools::minifyCSS($input);
        }else{
            if(trim($input) === "") return $input;
            return preg_replace(
                array(
                    // Remove comment(s)
                    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                    // Remove unused white-space(s)
                    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                    // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                    '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                    // Replace `:0 0 0 0` with `:0`
                    '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                    // Replace `background-position:0` with `background-position:0 0`
                    '#(background-position):0(?=[;\}])#si',
                    // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                    '#(?<=[\s:,\-])0+\.(\d+)#s',
                    // Minify string value
                    '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                    '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                    // Minify HEX color code
                    '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                    // Replace `(border|outline):none` with `(border|outline):0`
                    '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                    // Remove empty selector(s)
                    '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
                ),
                array(
                    '$1',
                    '$1$2$3$4$5$6$7',
                    '$1',
                    ':0',
                    '$1:0 0',
                    '.$1',
                    '$1$3',
                    '$1$2$4$5',
                    '$1$2$3',
                    '$1:0',
                    '$1$2'
                ),
            $input);
        }
    }
}
