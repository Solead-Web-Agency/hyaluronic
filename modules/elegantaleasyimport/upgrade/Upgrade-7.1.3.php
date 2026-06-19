<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_1_3($module)
{
    unset($module);
    $sql = "ALTER TABLE " . _DB_PREFIX_ . "elegantaleasyimport DROP COLUMN `delete_old_images`; ";
    $sql .= "ALTER TABLE " . _DB_PREFIX_ . "elegantaleasyimport DROP COLUMN `delete_old_features`; ";
    if (Db::getInstance()->execute($sql) == false) {
        //throw new Exception(Db::getInstance()->getMsgError());
        return false;
    }
    return true;
}
