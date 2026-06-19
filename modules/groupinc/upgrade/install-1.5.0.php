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

function upgrade_module_1_5_0($module)
{
    Db::getInstance()->execute(
        'ALTER TABLE `'._DB_PREFIX_.'groupinc_configuration`
            ADD `grouped_by` tinyint(1) unsigned NULL,
            ADD `countdown` tinyint(1) unsigned NULL,
            ADD `countdown_prodpage` tinyint(1) unsigned NULL,
            ADD `products_excluded` TEXT NULL,
            ADD `customers_excluded` TEXT NULL,
            ADD `show_text` tinyint(1) unsigned,
            ADD `show_text_prodpage` tinyint(1) unsigned,
            ADD `higher_discount` tinyint(1) unsigned,
            ADD `cart_amount` decimal(10,3) NULL DEFAULT "0.000";'
    );

    Db::getInstance()->Execute('
        CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'groupinc_configuration_lang` (
            `id_groupinc_configuration` int unsigned NOT NULL,
            `id_lang` int unsigned NOT NULL,
            `addit_text` TEXT NULL,
            `addit_text_prodpage` TEXT NULL,
            `cd_style` TEXT NULL,
            `cd_style_prodpage` TEXT NULL,
        PRIMARY KEY (`id_groupinc_configuration`, `id_lang`),
        KEY `id_groupinc_configuration` (`id_groupinc_configuration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

    $module->registerHook('displayProductPriceBlock');
    $module->registerHook('actionProductPriceCalculation');

    return true;
}
