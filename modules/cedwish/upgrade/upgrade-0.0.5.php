<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_0_5($module)
{
    $sql_queries = array();
    $sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedwish_batch` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
      `job_id` text ,
      `download_link` text ,
      `start_run_time` text ,
      `end_run_time` text ,
      `status` text ,
      `wish_limit` text ,
      `show_rejected` boolean,
      `warehouse_name` text ,
      `wish_sort` text ,
      `since` date ,
      `created_at` date ,
      `error` text ,
      PRIMARY KEY (`id`)
    )";

    $sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedwish_enrolled_products` (
    `id_enrolled` int(11) NOT NULL AUTO_INCREMENT,
      `id` varchar(200) NOT NULL,
      `region` varchar(10) NOT NULL,
      `warehouse_id` varchar(200) NOT NULL,
      PRIMARY KEY (`id_enrolled`)
    )";

    $sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedwish_return_logistics` (
    `id_return_logistics` int(11) NOT NULL AUTO_INCREMENT,
      `variation_id` varchar(200) NOT NULL,
      `id` varchar(200) NOT NULL,
      `height` decimal(10,0) NOT NULL,
      `length` decimal(10,0) NOT NULL,
      `width` decimal(10,0) NOT NULL,
      `weight` decimal(10,0) NOT NULL,
      PRIMARY KEY (`id_return_logistics`)
    )";

    $sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedwish_return_warehouse` (
    `id_return_warehouse` int(11) NOT NULL AUTO_INCREMENT,
      `id` varchar(200) NOT NULL,
      `ship_to` text NOT NULL,
      `warehouse_name` varchar(200) NOT NULL,
      `region` varchar(200) NOT NULL,
      `phone_number` varchar(20) NOT NULL,
      `email` varchar(200) NOT NULL,
      `city` varchar(200) NOT NULL,
      `state` varchar(200) NOT NULL,
      `country` varchar(200) NOT NULL,
      `zipcode` varchar(10) NOT NULL,
      `street_address1` text NOT NULL,
      `street_address2` text NOT NULL,
      PRIMARY KEY (`id_return_warehouse`)
    )";
    $sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedwish_warehouse` (
      `id_warehouse` int(11) NOT NULL AUTO_INCREMENT,
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
      PRIMARY KEY (`id_warehouse`)
    )";

    $sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedwish_brands` (
      `id_brand` int(11) NOT NULL AUTO_INCREMENT,
      `id` varchar(200) NOT NULL,
      `name` varchar(200) NOT NULL,
      `website` varchar(200) NOT NULL,
      PRIMARY KEY (`id_brand`)
    )";

    $sql_queries[] = "ALTER TABLE `" . _DB_PREFIX_ . "cedwish_profileproducts` ADD INDEX(`product_id`);";

    $sql_queries[] = "ALTER TABLE `" . _DB_PREFIX_ . "cedwish_profileproducts` ADD INDEX(`profile_id`);";

    $sql_queries[] = "ALTER TABLE `" . _DB_PREFIX_ . "cedwish_products` ADD `wish_brand_id` VARCHAR(200) NULL";

    $sql_queries[] = "ALTER TABLE `" . _DB_PREFIX_ . "cedwish_products` ADD `brand_name` VARCHAR(200) NULL";

    foreach ($sql_queries as $query) {
        Db::getInstance()->execute($query);
    }

    if (!(int)Tab::getIdFromClassName('AdminCedWishReturns')) {
        $module->installTab(
            'AdminCedWishReturns',
            'Return',
            (int)Tab::getIdFromClassName('AdminCedWish')
        );
    }

    if (!(int)Tab::getIdFromClassName('AdminCedWishWarehouse')) {
        $module->installTab(
            'AdminCedWishWarehouse',
            'Warehouse',
            (int)Tab::getIdFromClassName('AdminCedWish')
        );
    }

    if (!(int)Tab::getIdFromClassName('AdminCedWishReturnWarehouse')) {
        $module->installTab(
            'AdminCedWishReturnWarehouse',
            'Return Warehouse',
            (int)Tab::getIdFromClassName('AdminCedWish')
        );
    }

    if (!(int)Tab::getIdFromClassName('AdminCedWishBatch')) {
        $module->installTab(
            'AdminCedWishBatch',
            'Batches',
            (int)Tab::getIdFromClassName('AdminCedWish')
        );
    }

    return true;
}
