<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_5_2($module)
{
    unset($module);

    $sql = "ALTER TABLE " . _DB_PREFIX_ . "elegantaleasyimport ADD `enable_all_products_found_in_csv` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `enable_new_products_by_default`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    $sql = "ALTER TABLE " . _DB_PREFIX_ . "elegantaleasyimport ADD `disable_all_products_not_found_in_csv` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `enable_all_products_found_in_csv`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    return true;
}
