<?php
/**
* Newsletter Popup Module 
* 2007-2015 logicalit.com
* NOTICE OF LICENSE
*
* Copy Right, all rights reserved: logicalit.com
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customise this module for your
* needs please contact http://www.logicalit.com/contact-us/
*
*    @author logicalit.com
*    @copyright  2007-2015 logicalit.com
*    @version  Release: $Revision: 1.0
*    @license  http://www.logicalit.com/prestashop-modules/en/content/3-terms-and-conditions-of-use
*/


if (!defined('_PS_VERSION_'))
	exit;

class HotJar extends Module
{
	protected $config_form = false;

	public function __construct()
	{
		$this->name = 'hotjar';
		$this->tab = 'administration';
		$this->version = '1.0.0';
		$this->author = 'logicalit.com';
		$this->need_instance = 1;

		if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true)
			$this->bootstrap = true;
        
		parent::__construct();

		$this->displayName = $this->l('Hotjar');
		$this->description = $this->l('This module adds the HotJar tracking code to the header template.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall module?');
	}

	public function install()
	{
		Configuration::updateValue('HJ_TRACKING_CODE', '');

		return parent::install() &&
			$this->registerHook('header');
	}

	public function uninstall()
	{
		Configuration::deleteByName('HJ_TRACKING_CODE');

		return parent::uninstall();
	}

	/**
	 * Load the configuration form
	 */
	public function getContent()
	{
		$output = ''; 
        
        if (Tools::isSubmit('submitUpdate'))
		{
			$code = Tools::getValue('hotjar_code');

           // echo "code: ".$code; exit; 
            
			if (!$code || empty($code))
            { 
				$output .= $this->displayError($this->l('Please enter a valid tracking code.'));
            }
			else
			{
				Configuration::updateValue('HJ_TRACKING_CODE', $code, true);
				$output .= $this->displayConfirmation($this->l('Tracking code successfully updated.'));
			}
		}
        
        $form_link = $this->context->link->getAdminLink('AdminModules').'&configure=hotjar'; 
        $this->context->smarty->assign('form_link', $form_link);
        $this->context->smarty->assign('this_path_ssl', Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/');
		$this->context->smarty->assign('hotjar_code', Configuration::get('HJ_TRACKING_CODE'));

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

		return $output;
	}


	public function hookHeader()
	{
		return Configuration::get('HJ_TRACKING_CODE');
	}

}
