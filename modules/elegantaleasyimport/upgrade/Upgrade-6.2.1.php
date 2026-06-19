<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_2_1($module)
{
    unset($module);

    $sql = "ALTER TABLE " . _DB_PREFIX_ . "elegantaleasyimport ADD `enable_new_products_by_default` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `replicate_all_languages`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    return true;
}
