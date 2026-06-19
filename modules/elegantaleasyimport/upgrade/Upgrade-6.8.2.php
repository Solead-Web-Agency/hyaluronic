<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_8_2($module)
{
    // setting each shop
    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
        $shop_groups = Shop::getTree();
        foreach ($shop_groups as $shop_group) {
            foreach ($shop_group['shops'] as $shop) {
                $use_file_function = $module->getSetting('use_file_function', 1, $shop['id_shop_group'], $shop['id_shop']);
                $module->setSetting('is_use_file_function', $use_file_function, $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('use_file_function', $shop['id_shop_group'], $shop['id_shop']);

                $add_product_to_root_category = $module->getSetting('add_product_to_root_category', 0, $shop['id_shop_group'], $shop['id_shop']);
                $module->setSetting('is_add_product_to_root_category', $add_product_to_root_category, $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('add_product_to_root_category', $shop['id_shop_group'], $shop['id_shop']);

                $assign_all_categories_in_multiple_subcategory_tree = $module->getSetting('assign_all_categories_in_multiple_subcategory_tree', 1, $shop['id_shop_group'], $shop['id_shop']);
                $module->setSetting('is_assign_all_categories_in_multiple_subcategory_tree', $assign_all_categories_in_multiple_subcategory_tree, $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('assign_all_categories_in_multiple_subcategory_tree', $shop['id_shop_group'], $shop['id_shop']);

                $first_parent_should_be_root_for_multiple_categories = $module->getSetting('first_parent_should_be_root_for_multiple_categories', 1, $shop['id_shop_group'], $shop['id_shop']);
                $module->setSetting('is_first_parent_should_be_root_for_multiple_categories', $first_parent_should_be_root_for_multiple_categories, $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('first_parent_should_be_root_for_multiple_categories', $shop['id_shop_group'], $shop['id_shop']);

                $discount_tax_included = $module->getSetting('discount_tax_included', 1, $shop['id_shop_group'], $shop['id_shop']);
                $module->setSetting('is_discount_tax_included', $discount_tax_included, $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('discount_tax_included', $shop['id_shop_group'], $shop['id_shop']);

                $disable_url_rewrite = $module->getSetting('disable_url_rewrite', 0, $shop['id_shop_group'], $shop['id_shop']);
                $module->setSetting('is_disable_url_rewrite', $disable_url_rewrite, $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('disable_url_rewrite', $shop['id_shop_group'], $shop['id_shop']);

                $debug_mode = $module->getSetting('debug_mode', 0, $shop['id_shop_group'], $shop['id_shop']);
                $module->setSetting('is_debug_mode', $debug_mode, $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('debug_mode', $shop['id_shop_group'], $shop['id_shop']);
            }
        }
    }

    // setting for all shops
    $use_file_function = $module->getSetting('use_file_function', 1, "", "");
    $module->setSetting('is_use_file_function', $use_file_function, "", "");
    $module->deleteSetting('use_file_function', "", "");

    $add_product_to_root_category = $module->getSetting('add_product_to_root_category', 0, "", "");
    $module->setSetting('is_add_product_to_root_category', $add_product_to_root_category, "", "");
    $module->deleteSetting('add_product_to_root_category', "", "");

    $assign_all_categories_in_multiple_subcategory_tree = $module->getSetting('assign_all_categories_in_multiple_subcategory_tree', 1, "", "");
    $module->setSetting('is_assign_all_categories_in_multiple_subcategory_tree', $assign_all_categories_in_multiple_subcategory_tree, "", "");
    $module->deleteSetting('assign_all_categories_in_multiple_subcategory_tree', "", "");

    $first_parent_should_be_root_for_multiple_categories = $module->getSetting('first_parent_should_be_root_for_multiple_categories', 1, "", "");
    $module->setSetting('is_first_parent_should_be_root_for_multiple_categories', $first_parent_should_be_root_for_multiple_categories, "", "");
    $module->deleteSetting('first_parent_should_be_root_for_multiple_categories', "", "");

    $discount_tax_included = $module->getSetting('discount_tax_included', 1, "", "");
    $module->setSetting('is_discount_tax_included', $discount_tax_included, "", "");
    $module->deleteSetting('discount_tax_included', "", "");

    $disable_url_rewrite = $module->getSetting('disable_url_rewrite', 0, "", "");
    $module->setSetting('is_disable_url_rewrite', $disable_url_rewrite, "", "");
    $module->deleteSetting('disable_url_rewrite', "", "");

    $debug_mode = $module->getSetting('debug_mode', 0, "", "");
    $module->setSetting('is_debug_mode', $debug_mode, "", "");
    $module->deleteSetting('debug_mode', "", "");

    return true;
}
