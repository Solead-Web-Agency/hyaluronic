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

function upgrade_module_1_3_4($module)
{
    Db::getInstance()->execute(
        'ALTER TABLE `'._DB_PREFIX_.'groupinc_configuration`
        ADD `override_discounts` tinyint(1) unsigned DEFAULT "0" AFTER `skip_discounts`,
        ADD `currencies` TEXT NULL AFTER `suppliers`,
        ADD `languages` TEXT NULL AFTER `suppliers`;'
    );

	$module->installOverrides();

    return $module;
}
