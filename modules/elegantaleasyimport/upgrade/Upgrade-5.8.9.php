<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_8_9($module)
{
    $sql = "ALTER TABLE " . _DB_PREFIX_ . "elegantaleasyimport ADD `is_utf8_encode` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `replicate_all_languages`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    $is_utf8_encode = $module->getSetting('is_utf8_encode');
    $sql = "UPDATE " . _DB_PREFIX_ . "elegantaleasyimport SET is_utf8_encode = " . (int) $is_utf8_encode;
    Db::getInstance()->execute($sql);

    // Delete setting for each shop
    if (Shop::isFeatureActive()) {
        $shop_groups = Shop::getTree();
        foreach ($shop_groups as $shop_group) {
            foreach ($shop_group['shops'] as $shop) {
                $module->deleteSetting('is_utf8_encode', $shop['id_shop_group'], $shop['id_shop']);
            }
        }
    }

    // Delete setting for all shops
    $module->deleteSetting('is_utf8_encode', "", "");

    return true;
}
