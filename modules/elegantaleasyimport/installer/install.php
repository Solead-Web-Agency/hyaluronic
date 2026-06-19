<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This file returns array of sql queries that are required to be executed during module installation.
 */
$sql = array();

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport` (
    `id_elegantaleasyimport` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `entity` varchar(25) NOT NULL DEFAULT 'product',
    `lang_id` int(11) NOT NULL,
    `supplier_id` int(11),
    `map` text,
    `map_default_values` text,
    `header_row` int(3) NOT NULL DEFAULT '1',
    `import_type` int(3) NOT NULL,
    `csv_file` varchar(255),
    `csv_path` varchar(255),
    `csv_url` text,
    `csv_url_username` varchar(255),
    `csv_url_password` varchar(255),
    `csv_url_method` varchar(10),
    `csv_url_post_params` text,
    `ftp_host` varchar(255),
    `ftp_port` varchar(10) DEFAULT '21',
    `ftp_username` varchar(255),
    `ftp_password` varchar(255),
    `ftp_file` varchar(255),
    `is_cron` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `cron_csv_file_size` int(11) unsigned,
    `cron_csv_file_md5` varchar(255),
    `product_limit_per_request` int(10) NOT NULL DEFAULT '5',
    `product_range_to_import` varchar(255),
    `email_to_send_notification` varchar(255),
    `find_products_by` varchar(20) NOT NULL,
    `create_new_products` tinyint(1) unsigned NOT NULL DEFAULT '1',
    `update_existing_products` tinyint(1) unsigned NOT NULL DEFAULT '1',
    `update_products_on_all_shops` tinyint(1) unsigned NOT NULL,
    `price_modifier` varchar(255),
    `min_price_amount` DECIMAL(10, 2),
    `multiple_value_separator` varchar(5) NOT NULL,
    `multiple_subcategory_separator` varchar(5),
    `is_associate_all_subcategories` tinyint(1) unsigned NOT NULL DEFAULT '1',
    `is_first_parent_root_for_categories` tinyint(1) unsigned NOT NULL DEFAULT '1',
    `decimal_char` varchar(1) NOT NULL,
    `shipping_package_size_unit` varchar(5) NOT NULL,
    `shipping_package_weight_unit` varchar(5) NOT NULL,
    `base_url_images` varchar(255),
    `delete_old_combinations` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `replicate_all_languages` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `enable_new_products_by_default` tinyint(1) unsigned NOT NULL DEFAULT '1',
    `enable_if_have_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `disable_if_no_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `enable_all_products_found_in_csv` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `disable_all_products_not_found_in_csv` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `put_zero_qty_for_products_not_found_in_csv` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `is_utf8_encode` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `last_import_date` DATETIME,
    `error_log` text,
    `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
    PRIMARY KEY  (`id_elegantaleasyimport`) 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_shop` (
    `id_elegantaleasyimport` int(11) unsigned NOT NULL, 
    `id_shop` int(11) unsigned NOT NULL,
    PRIMARY KEY (`id_elegantaleasyimport`, `id_shop`),
    FOREIGN KEY (`id_elegantaleasyimport`) REFERENCES `" . _DB_PREFIX_ . "elegantaleasyimport` (`id_elegantaleasyimport`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_csv` (
    `id_elegantaleasyimport_csv` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `id_elegantaleasyimport` int(11) unsigned NOT NULL, 
    `id_reference` varchar(255) NOT NULL, 
    `csv_row` text,
    PRIMARY KEY  (`id_elegantaleasyimport_csv`), 
    FOREIGN KEY (`id_elegantaleasyimport`) REFERENCES `" . _DB_PREFIX_ . "elegantaleasyimport` (`id_elegantaleasyimport`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_category_map` (
    `id_elegantaleasyimport_category_map` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `id_elegantaleasyimport` int(11) unsigned NOT NULL, 
    `type` int(3) unsigned NOT NULL,
    `csv_category` text NOT NULL,
    `shop_category_id` int(10) unsigned,
    PRIMARY KEY  (`id_elegantaleasyimport_category_map`), 
    FOREIGN KEY (`id_elegantaleasyimport`) REFERENCES `" . _DB_PREFIX_ . "elegantaleasyimport` (`id_elegantaleasyimport`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_export` (
    `id_elegantaleasyimport_export` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `entity` varchar(25) NOT NULL,
    `columns` text,
    `column_override_values` text,
    `file_path` varchar(255) NOT NULL,
    `file_format` varchar(10) NOT NULL,
    `multiple_value_separator` varchar(5) NOT NULL,
    `multiple_subcategory_separator` varchar(5),
    `currency_id` int(11) NOT NULL,
    `shop_ids` varchar(255),
    `category_ids` text,
    `supplier_ids` text,
    `manufacturer_ids` text,
    `exclude_product_ids` text,
    `product_status` tinyint(1) unsigned,
    `price_modifier` varchar(255),
    `price_range` varchar(255),
    `quantity_range` varchar(255),
    `order_by` varchar(255) NOT NULL,
    `order_direction` varchar(255) NOT NULL DEFAULT 'ASC',
    `last_export_date` DATETIME,
    `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
    CONSTRAINT `file_path` UNIQUE (`file_path`), 
    PRIMARY KEY  (`id_elegantaleasyimport_export`) 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_export_shop` (
    `id_elegantaleasyimport_export` int(11) unsigned NOT NULL, 
    `id_shop` int(11) unsigned NOT NULL,
    PRIMARY KEY (`id_elegantaleasyimport_export`, `id_shop`),
    FOREIGN KEY (`id_elegantaleasyimport_export`) REFERENCES `" . _DB_PREFIX_ . "elegantaleasyimport_export` (`id_elegantaleasyimport_export`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

return $sql;
