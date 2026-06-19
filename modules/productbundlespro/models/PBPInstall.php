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

class PBPInstall
{

    public static function installDB()
    {
        $return = true;
        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pbp_bundle` (
				`id_pbp_bundle` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_bundle` mediumint(8) unsigned NOT NULL,
				`id_tab` mediumint(8) unsigned NOT NULL,
				`id_product` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,				
				`enabled` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
                `allow_selection` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
                `discount_enabled` tinyint(3) unsigned DEFAULT \'0\',
                `discount_type` varchar(16) DEFAULT NULL,
				`discount_amount` decimal(10,2) unsigned DEFAULT \'0.00\',
                `parent_product_discount_type` varchar(16) DEFAULT NULL,
                `parent_product_discount_amount` decimal(10,2) unsigned DEFAULT \'0.00\',				
				`position` smallint(5) unsigned NOT NULL DEFAULT \'0\',
			PRIMARY KEY (`id_pbp_bundle`)
		)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pbp_bundle_lang` (
            `id_pbp_bundle_lang` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_bundle` int(10) unsigned NOT NULL DEFAULT \'0\',
            `id_lang` int(10) unsigned NOT NULL DEFAULT \'0\',
            `name` varchar(128) DEFAULT NULL,
            PRIMARY KEY (`id_pbp_bundle_lang`)			
		)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pbp_product` (
				`id_pbp_product` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
				`id_bundle` mediumint(8) unsigned NOT NULL,
				`id_product` int(10) unsigned NOT NULL,
				`discount_type` varchar(12) NOT NULL,
				`discount_amount` decimal(10,5) NOT NULL,
				`discount_tax` varchar(12) NOT NULL,
				`qty` int(10) unsigned NOT NULL DEFAULT \'1\',
				`allow_oos` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
				`position` smallint(5) unsigned NOT NULL DEFAULT \'0\',
			PRIMARY KEY (`id_pbp_product`)
		)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pbp_tabs_lang` (
				`id_pbp_tab` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
				`id_tab` mediumint(8) unsigned NOT NULL,
				`id_lang` mediumint(8) unsigned NOT NULL,
				`title` varchar(128) NOT NULL,
			PRIMARY KEY (`id_pbp_tab`)
		)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pbp_product_option` (
			  `id_option` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `id_product` int(10) unsigned NOT NULL,
			  `disabled_addtocart` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
			PRIMARY KEY (`id_option`)
		)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pbp_cart_productextra` (
                `id_pbp_cart_productextra` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_pbp_cart_productextra_parent` int(11) unsigned NOT NULL DEFAULT \'0\',
                `id_product_parent` int(11) unsigned NOT NULL,
                `id_product_attribute_parent` int(11) unsigned NOT NULL,
                `id_customization_parent` int(11) unsigned NOT NULL,
                `date_add_parent` datetime NOT NULL,
                `id_cart` int(11) unsigned NOT NULL,
                `id_product` int(11) unsigned NOT NULL,
                `id_product_attribute` int(11) NOT NULL,
                `quantity` int(11) NOT NULL,
				`discount_type` varchar(12) NOT NULL,
				`discount_amount` decimal(10,5) NOT NULL DEFAULT \'0\',
				`discount_tax` varchar(12) NOT NULL,                
                `id_pbp_bundle` int(11) unsigned NOT NULL DEFAULT \'0\',
            PRIMARY KEY (`id_pbp_cart_productextra`)			
		)ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        self::addColumn('cart_product', 'id_pbp_bundle', 'INT UNSIGNED DEFAULT 0');
        self::addColumn('cart_product', 'id_parent_pbp_product', 'INT UNSIGNED DEFAULT 0');
        self::addColumn('cart_product', 'id_parent_pbp_product_ipa', 'INT UNSIGNED DEFAULT 0');

        Configuration::updateValue('pbp_bundle_discount_combinable', 1);
    }

    public static function uninstall()
    {
        self::dropTable('pbp_bundle');
        self::dropTable('pbp_bundle_lang');
        self::dropTable('pbp_cart_productextra');
        self::dropTable('pbp_product');
        self::dropTable('pbp_product_option');
        self::dropTable('pbp_tabs_lang');
    }

    public static function addColumn($table, $name, $type)
    {
        try {
            $return = Db::getInstance()->execute('ALTER TABLE  `' . _DB_PREFIX_ . bqSQL($table) . '` ADD  `' . bqSQL($name) . '` ' . bqSQL($type));
        } catch (Exception $e) {
            $return = true;
        }
    }

    public static function dropTable($table_name)
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $table_name . '`';
        DB::getInstance()->execute($sql);
    }
}
