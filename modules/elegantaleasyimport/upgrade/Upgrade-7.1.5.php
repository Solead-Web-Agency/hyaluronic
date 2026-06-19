<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_1_5($module)
{
    $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_category_map` (
        `id_elegantaleasyimport_category_map` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `id_elegantaleasyimport` int(11) unsigned NOT NULL, 
        `type` int(3) unsigned NOT NULL,
        `csv_category` text NOT NULL,
        `shop_category_id` int(10) unsigned,
        PRIMARY KEY  (`id_elegantaleasyimport_category_map`), 
        FOREIGN KEY (`id_elegantaleasyimport`) REFERENCES `" . _DB_PREFIX_ . "elegantaleasyimport` (`id_elegantaleasyimport`) ON DELETE CASCADE 
    ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
        return false;
    }

    return true;
}
