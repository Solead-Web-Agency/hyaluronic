<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_2_2($module)
{
    unset($module);

    $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_export` (
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
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_export_shop` (
        `id_elegantaleasyimport_export` int(11) unsigned NOT NULL, 
        `id_shop` int(11) unsigned NOT NULL,
        PRIMARY KEY (`id_elegantaleasyimport_export`, `id_shop`),
        FOREIGN KEY (`id_elegantaleasyimport_export`) REFERENCES `" . _DB_PREFIX_ . "elegantaleasyimport_export` (`id_elegantaleasyimport_export`) ON DELETE CASCADE 
    ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    return true;
}
