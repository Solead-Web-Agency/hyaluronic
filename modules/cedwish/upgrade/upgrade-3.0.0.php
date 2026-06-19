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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_0_0($module)
{
    require_once _PS_MODULE_DIR_ . $module->name . '/classes/menu.php';
    require_once _PS_MODULE_DIR_ . $module->name . '/classes/helper.php';
    $menu = new CedWishMenu($module->name);
    $helper = new CedWishHelper();
    if ($module) {
        try {
            /**
             * Registering new hooks for module start
             */
            foreach (CedWish::$hooksUsedInModule as $hook) {
                $module->registerHook($hook);
            }
            /**
             * Registering new hooks for module start
             */

            /**
             *   update configuration from old start here
             */
            $config_to_update = array(
                'CED_WISH_API_MODE' => 'CEDWISH_MODE',
                'CED_WISH_ACCESS_TOKEN' => 'CEDWISH_API_ACCESS_TOKEN',
                'CED_WISH_REFRESH_TOKEN' => 'CEDWISH_API_REFRESH_TOKEN',
                'CED_WISH_SELECTED_WAREHOUSES' => 'CEDWISH_ALLOWED_WAREHOUSES',
                'CED_WISH_MERCHANT_ID' => 'CEDWISH_MERCHANT_ID',
                'CED_WISH_LANG_ID' => 'CEDWISH_LANGUAGE_STORE',
                'CED_WISH_EXPIRY_TIME' => 'CEDWISH_TOKEN_EXPIRY_DATE',
                'CED_WISH_WISH_CURRENCY' => 'CEDWISH_CURRENCY_STORE',
                'CED_WISH_ORDER_EMAIL' => 'CEDWISH_ORDER_EMAIL',
                'CED_WISH_ORIGIN_COUNTRY' => 'CEDWISH_ORDER_ORIGIN_COUNTRY',
                'CED_WISH_ORDER_CREATE_STATUS' => 'CEDWISH_ORDER_STATE_IMPORT',
                'CED_WISH_SHIPMENT_CREATE_STATUS' => 'CEDWISH_ORDER_STATE_SHIPPED',
                'CED_WISH_ORDER_CARRIER' => 'CEDWISH_ORDER_CARRIER',
                'CED_WISH_ORDER_PAYMENT' => 'CEDWISH_ORDER_PAYMENT',
                'CED_WISH_CARRIER_MAPPING' => 'CED_WISH_CARRIER_MAPPING',
                'CED_WISH_CRON_SECURE_KEY' => 'CEDWISH_CRON_SECURE_KEY'
            );

            foreach ($config_to_update as $key => $value) {
                if (Configuration::get($value)) {
                    Configuration::updateValue($key, Configuration::get($value));
                }
            }
            $status = array(
                array(
                    'order_status' => Configuration::get('CEDWISH_ORDER_STATE_REFUNDED'),
                    'marketplace_status' => 'REFUNDED'

                ),
                array(
                    'order_status' => Configuration::get('CEDWISH_ORDER_STATE_SHIPPED'),
                    'marketplace_status' => 'SHIPPED'
                ),
                array(
                    'order_status' => Configuration::get('CEDWISH_ORDER_STATE_IMPORT'),
                    'marketplace_status' => 'APPROVED'
                ),
            );
            Configuration::updateValue('CED_WISH_SHIPMENT_CREATE', 'ORDER_STATUS');
            Configuration::updateValue('CED_WISH_STATUS_MAPPING', json_encode($status));

            /**
             *   update configuration from old end here
             */

            $sql = array();
            if (empty(Db::getInstance()->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "cedwish_profile_old'"))
            ) {
                $old_profiles = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedwish_profile`");
                if (!empty($old_profiles)) {
                    $new_profiles = array();
                    foreach ($old_profiles as $old_profile) {
                        $new_profiles[] = array(
                            'name' => $old_profile['profile_name'],
                            'categories' => Tools::getDescriptionClean($old_profile['profile_category']),
                            'default_mapping' => Tools::getDescriptionClean($old_profile['profile_default_values']),
                            'attribute_mapping' =>Tools::getDescriptionClean($old_profile['profile_attribute_mapping']),
                            'shipping_setting' => Tools::getDescriptionClean($old_profile['profile_shipping']),
                            'id_shop' => Context::getContext()->shop->id,
                            'status' => $old_profile['profile_status'],
                        );
                    }
                }

                Db::getInstance()->execute(
                    "RENAME TABLE `" . _DB_PREFIX_ . "cedwish_profile` TO `" . _DB_PREFIX_ . "cedwish_profile_old`"
                );
                $profile_sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_profile` (
                  `id_' . $module->name . '_profile` int(11) AUTO_INCREMENT NOT NULL,
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
                  PRIMARY KEY  (`id_' . $module->name . '_profile`),
                  INDEX (`id_shop`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
                Db::getInstance()->execute($profile_sql);
                try {
                    Db::getInstance()->insert(
                        $module->name . '_profile',
                        $new_profiles
                    );
                } catch (PrestaShopDatabaseException $e) {
                    $helper->addLog($e->getMessage() . $e->getTraceAsString());
                }
            } elseif (!Db::getInstance()->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "cedwish_profile_old'")) {
                Db::getInstance()->execute(
                    "RENAME TABLE `" . _DB_PREFIX_ . "cedwish_profile` TO `" . _DB_PREFIX_ . "cedwish_profile_old`"
                );
                $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_profile` (
                  `id_' . $module->name . '_profile` int(11) AUTO_INCREMENT NOT NULL,
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
                  PRIMARY KEY  (`id_' . $module->name . '_profile`),
                  INDEX (`id_shop`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            }

            if (empty(Db::getInstance()->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "cedwish_profileproducts_old'"))
            ) {
                $new_pps = array();
                $old_pps = Db::getInstance()->executeS(
                    "SELECT * FROM `" . _DB_PREFIX_ . "cedwish_profileproducts`"
                );
                if (!empty($old_pps)) {
                    foreach ($old_pps as $old_pp) {
                        $new_pps[] = array(
                            'id_cedwish_profile' => $old_pp['profile_id'],
                            'id_product' => $old_pp['product_id'],
                            'id_shop' => Context::getContext()->shop->id,
                        );
                    }
                }

                Db::getInstance()->execute(
                    "RENAME TABLE `" . _DB_PREFIX_ . "cedwish_profileproducts` 
                    TO `" . _DB_PREFIX_ . "cedwish_profileproducts_old`"
                );
                $pp_sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_profile_product` (
                  `id_' . $module->name . '_profile_product` int(11) AUTO_INCREMENT NOT NULL,
                  `id_' . $module->name . '_profile` int(11) NOT NULL,
                  `id_product` int(11) NOT NULL,
                  `id_shop` int(11) NOT NULL,
                  PRIMARY KEY  (`id_' . $module->name . '_profile_product`),
                  INDEX (`id_' . $module->name . '_profile`),
                  INDEX (`id_product`),
                  INDEX (`id_shop`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
                Db::getInstance()->execute($pp_sql);
                try {
                    if (!empty($new_pps)) {
                        Db::getInstance()->insert(
                            $module->name . '_profile_product',
                            $new_pps
                        );
                    }
                } catch (PrestaShopDatabaseException $e) {
                    $helper->addLog($e->getMessage() . $e->getTraceAsString());
                }
            } else {
                $pp_sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_profile_product` (
                  `id_' . $module->name . '_profile_product` int(11) AUTO_INCREMENT NOT NULL,
                  `id_' . $module->name . '_profile` int(11) NOT NULL,
                  `id_product` int(11) NOT NULL,
                  `id_shop` int(11) NOT NULL,
                  PRIMARY KEY  (`id_' . $module->name . '_profile_product`),
                  INDEX (`id_' . $module->name . '_profile`),
                  INDEX (`id_product`),
                  INDEX (`id_shop`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
                Db::getInstance()->execute($pp_sql);
            }

            $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_product` (
              `id_' . $module->name . '_product` int(11) AUTO_INCREMENT NOT NULL,
              `marketplace_id` varchar(200) DEFAULT NULL,
              `variation_id` varchar(200) DEFAULT NULL,
              `id_product` int(11) NOT NULL,
              `id_product_attribute` int(11) DEFAULT NULL,
              `status` text DEFAULT NULL,
              `error` text DEFAULT NULL,
              `marketplace_data` longtext DEFAULT NULL,
              `product_level_data` longtext DEFAULT NULL,
              `enabled` int(3) DEFAULT 1,
              `id_profile` int(11) DEFAULT NULL,
              PRIMARY KEY  (`id_' . $module->name . '_product`),
              INDEX(`id_product`),
              INDEX(`id_product_attribute`),
              INDEX(`marketplace_id`),
              INDEX(`variation_id`),
              INDEX(`marketplace_id`),
              INDEX(`id_profile`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


            if (empty(Db::getInstance()->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "cedwish_orders_old'"))
            ) {
                $old_orders = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedwish_orders`");
                $new_orders = array();
                if (!empty($old_orders)) {
                    foreach ($old_orders as $old_order) {
                        $new_orders[] = array(
                            'marketplace_order_id' => $old_order['wish_order_id'],
                            'released_at' => $old_order['order_date'],
                            'store_order_id' => $old_order['prestashop_order_id'],
                            'wish_order' => Tools::getDescriptionClean($old_order['order_data']),
                            'order_error' => '',
                            'state' => $old_order['status'],
                        );
                    }
                }

                Db::getInstance()->execute(
                    "RENAME TABLE `" . _DB_PREFIX_ . "cedwish_orders` TO `" . _DB_PREFIX_ . "cedwish_orders_old`"
                );
                $order_sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_order` (
                  `id_' . $module->name . '_order` int(11) AUTO_INCREMENT NOT NULL,
                  `marketplace_order_id` varchar(200) DEFAULT NULL,
                  `order_error` longtext DEFAULT NULL,
                  `released_at` datetime DEFAULT NULL,
                  `state` varchar(200) DEFAULT NULL,
                  `store_order_id` int(11) DEFAULT NULL,
                  `wish_order` longtext NOT NULL,
                  PRIMARY KEY  (`id_' . $module->name . '_order`),
                  INDEX(`store_order_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
                Db::getInstance()->execute($order_sql);
                try {
                    if (!empty($new_orders)) {
                        Db::getInstance()->insert(
                            $module->name . '_order',
                            $new_orders
                        );
                    }
                } catch (PrestaShopDatabaseException $e) {
                    $helper->addLog($e->getMessage() . $e->getTraceAsString());
                }
            } else {
                $order_sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_order` (
                  `id_' . $module->name . '_order` int(11) AUTO_INCREMENT NOT NULL,
                  `marketplace_order_id` varchar(200) DEFAULT NULL,
                  `order_error` longtext DEFAULT NULL,
                  `released_at` datetime DEFAULT NULL,
                  `state` varchar(200) DEFAULT NULL,
                  `store_order_id` int(11) DEFAULT NULL,
                  `wish_order` longtext NOT NULL,
                  PRIMARY KEY  (`id_' . $module->name . '_order`),
                  INDEX(`store_order_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
                Db::getInstance()->execute($order_sql);
            }

            $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_queue` (
              `id_' . $module->name . '_queue` int(11) NOT NULL AUTO_INCREMENT,
              `queue_type` varchar(255) NOT NULL,
              `queued_items` longtext,
              `priority` int(11) DEFAULT 1,
              PRIMARY KEY (`id_' . $module->name . '_queue`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

            $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_return_setting` (
              `id_' . $module->name . '_return_setting` int(11) NOT NULL AUTO_INCREMENT,
              `wish_product_id` varchar(100) NOT NULL,
              `region` varchar(2) NOT NULL,
              `warehouse_id` varchar(100) NOT NULL,
              PRIMARY KEY (`id_' . $module->name . '_return_setting`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

            Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $module->name . '_warehouse`');

            $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $module->name . '_warehouse` (
              `id_' . $module->name . '_warehouse` int(11) NOT NULL AUTO_INCREMENT,
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
              PRIMARY KEY (`id_' . $module->name . '_warehouse`),
              INDEX(id)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

            Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $module->name . '_batch`');

            $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $module->name . "_batch` (
              `id_" . $module->name . "_batch` int(11) NOT NULL AUTO_INCREMENT,
              `job_id` text DEFAULT NULL,
              `download_link` text DEFAULT NULL,
              `start_run_time` text DEFAULT NULL,
              `end_run_time` text DEFAULT NULL,
              `status` text DEFAULT NULL,
              `wish_limit` text DEFAULT NULL,
              `show_rejected` tinyint(1) DEFAULT NULL,
              `warehouse_name` text DEFAULT NULL,
              `wish_sort` text DEFAULT NULL,
              `since` date DEFAULT NULL,
              `created_at` date DEFAULT NULL,
              `error` text DEFAULT NULL,
              PRIMARY KEY (`id_" . $module->name . "_batch`)
            );";

            $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $module->name . "_color_mapping` (
              `id_" . $module->name . "_color_mapping` int(11) NOT NULL AUTO_INCREMENT,
              `wish_option_id` text NOT NULL,
              `store_option_id` int(11) NOT NULL,
              `mapped_options` longtext NOT NULL,
              PRIMARY KEY (`id_" . $module->name . "_color_mapping`)
            );";

            $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $module->name . "_feed` (
              `id_" . $module->name . "_feed` int(11) NOT NULL AUTO_INCREMENT,
              `success_count` int(11) DEFAULT NULL,
              `failure_count` int(11) DEFAULT NULL,
              `processed_count` int(11) DEFAULT NULL,
              `job_id` varchar(100) DEFAULT NULL,
              `state` varchar(50) DEFAULT NULL,
              `start_time` datetime DEFAULT NULL,
              `uploader_id` varchar(100) DEFAULT NULL,
              `response` text DEFAULT NULL,
               PRIMARY KEY  (`id_" . $module->name . "_feed`)
            );";

            if (!empty(Db::getInstance()->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "cedwish_brands_old'"))
            ) {
                $old_brands = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedwish_brands_old`");
                $new_brands = array();
                if (!empty($old_brands)) {
                    foreach ($old_brands as $old_brand) {
                        $new_brands[] = array(
                            'id' => $old_brand['id'],
                            'name' => pSQL(Tools::getDescriptionClean($old_brand['name'])),
                        );
                    }
                }

                Db::getInstance()->execute(
                    "RENAME TABLE `" . _DB_PREFIX_ . "cedwish_brands` TO `" . _DB_PREFIX_ . "cedwish_brands_old`"
                );
                $brand_sql =  "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $module->name . "_brand` (
                  `id_" . $module->name . "_brand` int(11) NOT NULL AUTO_INCREMENT,
                  `id` varchar(200) NOT NULL,
                  `name` varchar(200) NOT NULL,
                  PRIMARY KEY (`id_" . $module->name . "_brand`)
                );";
                Db::getInstance()->execute($brand_sql);
                try {
                    if (!empty($new_brands)) {
                        Db::getInstance()->insert(
                            $module->name . '_brand',
                            $new_brands
                        );
                    }
                } catch (PrestaShopDatabaseException $e) {
                    $helper->addLog($e->getMessage() . $e->getTraceAsString());
                }
            } else {
                $brand_sql =  "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $module->name . "_brand` (
                  `id_" . $module->name . "_brand` int(11) NOT NULL AUTO_INCREMENT,
                  `id` varchar(200) NOT NULL,
                  `name` varchar(200) NOT NULL,
                  PRIMARY KEY (`id_" . $module->name . "_brand`)
                );";
                Db::getInstance()->execute($brand_sql);
            }

            $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $module->name . "_product_brand` (
                    `id_product` int(11) NOT NULL,
                    `id_wish_brand` varchar(100) NOT NULL,
                    INDEX(`id_product`)
                );";

            foreach ($sql as $query) {
                if (Db::getInstance()->execute($query) == false) {
                    $helper->addLog($e->getMessage() . $e->getTraceAsString());
                }
            }

            if ((int)Tab::getIdFromClassName('AdminCedWishFailedOrder')) {
                $menu->uninstallTab('AdminCedWishFailedOrder');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishBatch')) {
                $menu->uninstallTab('AdminCedWishBatch');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishOptionMapping')) {
                $menu->uninstallTab('AdminCedWishOptionMapping');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishReturnWarehouse')) {
                $menu->uninstallTab('AdminCedWishReturnWarehouse');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishReturns')) {
                $menu->uninstallTab('AdminCedWishReturns');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishLogs')) {
                $menu->uninstallTab('AdminCedWishLogs');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishWarehouse')) {
                $menu->uninstallTab('AdminCedWishWarehouse');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishFeeds')) {
                $menu->uninstallTab('AdminCedWishFeeds');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishConfig')) {
                $menu->uninstallTab('AdminCedWishConfig');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishOrder')) {
                $menu->uninstallTab('AdminCedWishOrder');
            }
            if ((int)Tab::getIdFromClassName('AdminCedWishBulk')) {
                $menu->uninstallTab('AdminCedWishBulk');
            }

            if ((int)Tab::getIdFromClassName('AdminCedWishProduct')) {
                $menu->uninstallTab('AdminCedWishProduct');
            }

            if ((int)Tab::getIdFromClassName('AdminCedWishProfile')) {
                $menu->uninstallTab('AdminCedWishProfile');
            }

            if ((int)Tab::getIdFromClassName('AdminCedWish')) {
                $menu->uninstallTab('AdminCedWish');
            }
            $menu->createTabs();
            $exists = Db::getInstance()->executeS(
                "SHOW COLUMNS FROM `"._DB_PREFIX_."cedwish_order` LIKE 'ps_order_id'"
            );
            if (!empty($exists)) {
                Db::getInstance()->execute(
                    "ALTER TABLE `"._DB_PREFIX_."cedwish_order` CHANGE `ps_order_id` `store_order_id` int(11)"
                );
            }
            return true;
        } catch (Exception $e) {
            $helper->addLog($e->getMessage() . $e->getTraceAsString());
            return true;
        }
    }
}
