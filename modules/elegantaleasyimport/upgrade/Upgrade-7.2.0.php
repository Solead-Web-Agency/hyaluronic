<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_2_0($module)
{
    $sql = "ALTER TABLE " . _DB_PREFIX_ . "elegantaleasyimport ADD `min_price_amount` DECIMAL(10, 2) AFTER `price_modifier`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    // Delete settings
    // for each shop
    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
        $shop_groups = Shop::getTree();
        foreach ($shop_groups as $shop_group) {
            foreach ($shop_group['shops'] as $shop) {
                $module->deleteSetting('min_price_amount', $shop['id_shop_group'], $shop['id_shop']);
            }
        }
    }

    // for all shops
    $module->deleteSetting('min_price_amount', "", "");

    return true;
}
