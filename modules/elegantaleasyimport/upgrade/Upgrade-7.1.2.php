<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_1_2($module)
{
    // setting each shop
    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
        $shop_groups = Shop::getTree();
        foreach ($shop_groups as $shop_group) {
            foreach ($shop_group['shops'] as $shop) {
                $module->deleteSetting('is_add_product_to_root_category', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('add_product_to_custom_categories', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('is_discount_tax_included', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('is_add_default_tax_rule', $shop['id_shop_group'], $shop['id_shop']);
            }
        }
    }

    // setting for all shops
    $module->deleteSetting('is_add_product_to_root_category', "", "");
    $module->deleteSetting('add_product_to_custom_categories', "", "");
    $module->deleteSetting('is_discount_tax_included', "", "");
    $module->deleteSetting('is_add_default_tax_rule', "", "");

    return true;
}
