<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . '/productbundlespro/lib/bootstrap.php');

function upgrade_module_2_1_0($object)
{
    PBPInstall::addColumn('pbp_cart_productextra', 'discount_type', 'varchar(12)');
    PBPInstall::addColumn('pbp_cart_productextra', 'discount_amount', 'decimal(10,5) NOT NULL DEFAULT "0"');
    PBPInstall::addColumn('pbp_cart_productextra', 'discount_tax', 'varchar(12)');
    PBPInstall::addColumn('pbp_bundle', 'allow_selection', 'tinyint(3) unsigned NOT NULL DEFAULT "0"');

    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pbp_bundle_lang` (
        `id_pbp_bundle_lang` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_bundle` int(10) unsigned NOT NULL DEFAULT \'0\',
        `id_lang` int(10) unsigned NOT NULL DEFAULT \'0\',
        `name` varchar(128) DEFAULT NULL,
        PRIMARY KEY (`id_pbp_bundle_lang`)			
    	)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');
    return true;
}
