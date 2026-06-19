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
class AdminGupsellfaqsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->display = 'edit';
        $this->bootstrap = true;
        parent::__construct();
        $this->meta_title = $this->l('FAQS');
		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
    }
    public function initContent()
	{
		$this->display = 'FAQS';
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
		$this->toolbar_title[] = $this->l('FAQS');
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
                'base_url' => $base_url
            ));
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('FAQS'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'gupsell_FAQS',
                    'name' => 'gupsell_FAQS',
                ),
            )
        );
        return parent::renderForm();
    }
}
