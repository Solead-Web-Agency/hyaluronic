<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_8_2($module)
{
    $sql = "ALTER TABLE " . _DB_PREFIX_ . "elegantaleasyimport ADD `replicate_all_languages` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `delete_old_features`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    // Delete setting each shop
    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
        $shop_groups = Shop::getTree();
        foreach ($shop_groups as $shop_group) {
            foreach ($shop_group['shops'] as $shop) {
                $module->deleteSetting('replicate_all_languages', $shop['id_shop_group'], $shop['id_shop']);
            }
        }
    }

    // Delete setting for all shops
    $module->deleteSetting('replicate_all_languages', "", "");

    return true;
}
