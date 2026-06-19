<?php
/**
* Price increment/Reduction by groups, categories and more
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

function upgrade_module_1_4_2($module)
{

	Db::getInstance()->execute(
        'ALTER TABLE `'._DB_PREFIX_.'groupinc_configuration`
        ADD `show_on_sale` tinyint(1) unsigned NULL DEFAULT "0",
        ADD `filter_prices` tinyint(1) unsigned NULL DEFAULT "0",
        ADD `filter_store` tinyint(1) unsigned DEFAULT "1",
        ADD `filter_stock` tinyint(1) unsigned NULL DEFAULT "0",
        ADD `show_prices_drop` tinyint(1) unsigned NULL DEFAULT "0",
        ADD `show_decimals` tinyint(1) unsigned NULL DEFAULT "0",
        ADD `min_stock` int(10) unsigned NULL DEFAULT "0",
        ADD `max_stock` int(10) unsigned NULL DEFAULT "0"'
	);

	$module->copyOverrideFolder();
	$module->uninstallOverrides();
	$module->installOverrides();
 	return $module;
}
