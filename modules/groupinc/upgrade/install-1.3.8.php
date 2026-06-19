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

function upgrade_module_1_3_8($module)
{
    Db::getInstance()->execute(
        'ALTER TABLE `'._DB_PREFIX_.'groupinc_configuration`
        ADD `backoffice` tinyint(1) unsigned NULL DEFAULT "0",
	    ADD `threshold_min_price` decimal(10,3) NULL DEFAULT "0.000",
		ADD `threshold_max_price` decimal(10,3) NULL DEFAULT "0.000",
		ADD `min_result_price` decimal(10,3) NULL DEFAULT "0.000",
		ADD `max_result_price` decimal(10,3) NULL DEFAULT "0.000",
		ADD `date_from` DATETIME,
		ADD `date_to` DATETIME,
		ADD `threshold_price` int(1) unsigned NOT NULL DEFAULT "1"'
    );

	$module->installOverrides();

    return $module;
}
