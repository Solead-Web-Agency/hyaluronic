<?php
/**
 * FMM OrderFields
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php

 * @category   FMM Modules
 * @package    OrderFields
 * @author     Kashif Raza <kashif.raza@unitedsol.net>
 * @copyright  Copyright 2014 © fmemodules.com All right reserved
 */
 
// Security
if (!defined('_PS_VERSION_'))
	exit;

// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
	define('_MYSQL_ENGINE_', 'MyISAM'); 

class FmmOrderFields extends Module {
	
	public function __construct() {
		
		$this->name 		= 'fmmorderfields';
		$this->tab 			= 'administration';
		$this->version 		= '1.0';
		$this->author 		= 'FMM Modules';
		$this->displayName 	= $this->l('FMM Order Totals');
		$this->description 	= $this->l('This will add extra fields in order grid.');
		$this->need_instance = 0;

		parent::__construct();
	}
	
	public function install() {
		return (parent :: install() AND $this->registerHook('displayBackOfficeFooter') AND $this->insertCol());
	}

	public function insertCol() {
		Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('ALTER TABLE `'._DB_PREFIX_.'orders`
						ADD `total_products_quantity` INT(10) unsigned NOT NULL default "0"');
		return true;
	}
	public function remCol() {
		Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('ALTER TABLE `'._DB_PREFIX_.'orders`
						DROP COLUMN `total_products_quantity`');
		return true;
	}



		public function uninstall()
	 {
			  return (parent::uninstall() AND $this->remCol() AND @unlink(_PS_OVERRIDE_DIR_.'controllers/admin/AdminOrdersController.php'));
	 }
	 
	 public function hookDisplayBackOfficeFooter($params)

	{
		$currency = $this->context->currency->sign;
		 if(_PS_VERSION_ >='1.6.0' || _PS_VERSION_ >= '1.6.0.0'){$psVersion = 1;}else{$psVersion = 0;}
		global $smarty;
		$smarty->assign(array(
			'FILTER_CONFIG' => (int)Configuration::get('ORDER_SET_FILTER'),
			'CUR_CURENCY' => $currency,
			'PS_VER' => (int)$psVersion,
		));

		return $this->display(__FILE__, 'order.tpl');

	}
}
