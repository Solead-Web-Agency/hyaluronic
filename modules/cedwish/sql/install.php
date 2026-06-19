<?php
/**
 * 2007-2020 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$sql = array();

/**
 * module name.
 */
$name = 'cedwish';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $name . '_profile` (
  `id_' . $name . '_profile` int(11) AUTO_INCREMENT NOT NULL,
  `manufacturers` text DEFAULT NULL,
  `categories` text DEFAULT NULL,
  `name` varchar(256) NOT NULL,
  `price_from` decimal(10,2) DEFAULT NULL,
  `price_to` decimal(10,2) DEFAULT NULL,
  `suppliers` text DEFAULT NULL,
  `shipping_setting` longtext DEFAULT NULL,
  `attribute_mapping` longtext DEFAULT NULL,
  `default_mapping` longtext DEFAULT NULL,
  `product_setting` longtext DEFAULT NULL,
  `default_shipping_prices` longtext DEFAULT NULL,
  `warehouse_to_shippings` longtext DEFAULT NULL,
  `id_shop` int(11) NOT NULL,
  `status` int(3) NOT NULL,
  PRIMARY KEY  (`id_' . $name . '_profile`),
  INDEX (`id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $name . '_profile_product` (
  `id_' . $name . '_profile_product` int(11) AUTO_INCREMENT NOT NULL,
  `id_' . $name . '_profile` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY  (`id_' . $name . '_profile_product`),
  INDEX (`id_' . $name . '_profile`),
  INDEX (`id_shop`),
  INDEX (`id_product`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $name . '_product` (
  `id_' . $name . '_product` int(11) AUTO_INCREMENT NOT NULL,
  `marketplace_id` varchar(200) DEFAULT NULL,
  `variation_id` varchar(200) DEFAULT NULL,
  `id_product` int(11) NOT NULL,
  `id_product_attribute` int(11) DEFAULT NULL,
  `status` text DEFAULT NULL,
  `error` text DEFAULT NULL,
  `marketplace_data` longtext DEFAULT NULL,
  `product_level_data` longtext DEFAULT NULL,
  `enabled` int(11) DEFAULT 1,
  `id_profile` int(11) DEFAULT NULL,
  PRIMARY KEY  (`id_' . $name . '_product`),
  INDEX(`id_product`),
  INDEX(`id_product_attribute`),
  INDEX(`marketplace_id`),
  INDEX(`variation_id`),
  INDEX(`marketplace_id`),
  INDEX(`id_profile`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $name . '_order` (
  `id_' . $name . '_order` int(11) AUTO_INCREMENT NOT NULL,
  `marketplace_order_id` varchar(200) DEFAULT NULL,
  `order_error` longtext DEFAULT NULL,
  `released_at` datetime DEFAULT NULL,
  `state` varchar(200) DEFAULT NULL,
  `store_order_id` int(11) DEFAULT NULL,
  `wish_order` longtext NOT NULL,
  PRIMARY KEY  (`id_' . $name . '_order`),
  INDEX(`store_order_id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $name . '_queue` (
  `id_' . $name . '_queue` int(11) NOT NULL AUTO_INCREMENT,
  `queue_type` varchar(255) NOT NULL,
  `queued_items` longtext,
  `priority` int(11) DEFAULT 1,
  PRIMARY KEY (`id_' . $name . '_queue`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $name . '_return_setting` (
  `id_' . $name . '_return_setting` int(11) NOT NULL AUTO_INCREMENT,
  `wish_product_id` varchar(100) NOT NULL,
  `region` varchar(2) NOT NULL,
  `warehouse_id` varchar(100) NOT NULL,
  PRIMARY KEY (`id_' . $name . '_return_setting`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $name . '_warehouse` (
  `id_' . $name . '_warehouse` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(200) NOT NULL,
  `ship_to_name` text NOT NULL,
  `name` varchar(200) NOT NULL,
  `destination_countries` varchar(200) NOT NULL,
  `shipping_type` varchar(20) NOT NULL,
  `address` varchar(200) NOT NULL,
  `city` varchar(200) NOT NULL,
  `state` varchar(200) NOT NULL,
  `country_code` varchar(200) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `street_address1` text NOT NULL,
  `street_address2` text NOT NULL,
  PRIMARY KEY (`id_' . $name . '_warehouse`),
  INDEX(id)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_. $name . "_batch` (
  `id_" . $name . "_batch` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` varchar(200) DEFAULT NULL,
  `download_link` text DEFAULT NULL,
  `start_run_time` varchar(200) DEFAULT NULL,
  `end_run_time` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `wish_limit` varchar(200) DEFAULT NULL,
  `show_rejected` tinyint(1) DEFAULT NULL,
  `warehouse_name` varchar(200) DEFAULT NULL,
  `wish_sort` varchar(200) DEFAULT NULL,
  `since` date DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `error` text DEFAULT NULL,
  PRIMARY KEY (`id_" . $name . "_batch`)
);";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_.$name."_color_mapping` (
  `id_".$name."_color_mapping` int(11) NOT NULL AUTO_INCREMENT,
  `wish_option_id` varchar(200) NOT NULL,
  `store_option_id` int(11) NOT NULL,
  `mapped_options` longtext NOT NULL,
  PRIMARY KEY (`id_".$name."_color_mapping`)
);";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_.$name."_return_warehouse` (
  `id_".$name."_return_warehouse` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(200) NOT NULL,
  `ship_to` text NOT NULL,
  `warehouse_name` varchar(200) NOT NULL,
  `region` varchar(200) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `email` varchar(300) NOT NULL,
  `city` varchar(200) NOT NULL,
  `state` varchar(200) NOT NULL,
  `country` varchar(200) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `street_address1` text NOT NULL,
  `street_address2` text NOT NULL,
  PRIMARY KEY (`id_".$name."_return_warehouse`)
);";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_.$name."_feed` (
  `id_".$name."_feed` int(11) NOT NULL AUTO_INCREMENT,
  `success_count` int(11) DEFAULT NULL,
  `failure_count` int(11) DEFAULT NULL,
  `processed_count` int(11) DEFAULT NULL,
  `job_id` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `uploader_id` varchar(100) DEFAULT NULL,
  `response` text DEFAULT NULL,
   PRIMARY KEY  (`id_".$name."_feed`)
);";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_.$name."_brand` (
  `id_'.$name.'brand` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id_".$name."brand`)
);";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $name . "_product_brand` (
                    `id_product` int(11) NOT NULL,
                    `id_wish_brand` varchar(100) NOT NULL,
                    INDEX(`id_product`)
                );";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
