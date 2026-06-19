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

function upgrade_module_1_4_5($module)
{
	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET customers = REPLACE(customers,",",";");'
	);

	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET groups = REPLACE(groups,",",";");'
	);

	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET suppliers = REPLACE(suppliers,",",";");'
	);

	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET manufacturers = REPLACE(manufacturers,",",";");'
	);

	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET products = REPLACE(products,",",";");'
	);

	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET languages = REPLACE(languages,",",";");'
	);

	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET currencies = REPLACE(currencies,",",";");'
	);

	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET countries = REPLACE(countries,",",";");'
	);

	Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.'groupinc_configuration`
			SET zones = REPLACE(zones,",",";");'
	);

 	return $module;
}
