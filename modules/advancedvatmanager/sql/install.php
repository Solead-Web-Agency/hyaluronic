<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array(
'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'advancedvatmanager_customers` (
    `id_advancedvatmanager_customers` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_customer` int(11) UNSIGNED NOT NULL,
    `id_address` int(11) UNSIGNED NOT NULL UNIQUE,
    `id_shop` int(11) UNSIGNED NOT NULL DEFAULT 1, 
	`vat` varchar(32),
	`validated` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `validated_company` TINYINT(1) UNSIGNED,
    `system_check` TINYINT(1) UNSIGNED DEFAULT NULL,
    `status` varchar(255) NOT NULL,
    `date_upd` datetime NOT NULL,
    `date_add` datetime NOT NULL,
    PRIMARY KEY  (`id_advancedvatmanager_customers`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;', 
'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'advancedvatmanager_orders` (
    `id_advancedvatmanager_orders` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order` int(11) UNSIGNED NOT NULL UNIQUE,
    `id_shop` int(11) UNSIGNED NOT NULL DEFAULT 1,
    `id_customer` int(11) UNSIGNED NOT NULL,
	`notax` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `brexit` TINYINT(1) UNSIGNED DEFAULT 0,
    `invoice` int(11) UNSIGNED DEFAULT 0,
    `date_upd` datetime NOT NULL,
    `date_add` datetime NOT NULL,
    PRIMARY KEY  (`id_advancedvatmanager_orders`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'advancedvatmanager_customers_exemption` (
    `id_advancedvatmanager_customers_exemption` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_customer` int(11) UNSIGNED NOT NULL UNIQUE,
    `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `date_upd` datetime NOT NULL,
    `date_add` datetime NOT NULL,
    PRIMARY KEY  (`id_advancedvatmanager_customers_exemption`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'advancedvatmanager_customers_exemption_shop` (
    `id_advancedvatmanager_customers_exemption` int(11) UNSIGNED NOT NULL,
    `id_shop` int(11) UNSIGNED NOT NULL,
      PRIMARY KEY (`id_advancedvatmanager_customers_exemption`,`id_shop`),
      KEY `id_shop` (`id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'    
);

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) === false) {
        die('Error while creating Database tables.');
    }
}
