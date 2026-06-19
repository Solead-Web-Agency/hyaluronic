<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_1_7($module)
{
    $sql = "ALTER TABLE `" . _DB_PREFIX_ . "elegantaleasyimport` ADD `product_limit_per_request` int(10) NOT NULL DEFAULT '5' AFTER `cron_csv_file_md5`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }

    $settings = $module->getSettings();
    $csv_limit = (isset($settings['csv_limit']) && $settings['csv_limit'] > 0 && $settings['csv_limit'] < 100) ? $settings['csv_limit'] : 5;
    $cron_csv_limit = (isset($settings['cron_csv_limit']) && $settings['cron_csv_limit'] > 0 && $settings['cron_csv_limit'] < 1000) ? $settings['cron_csv_limit'] : 50;
    $models = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "elegantaleasyimport`");
    if ($models) {
        foreach ($models as $model) {
            if ($model['is_cron']) {
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "elegantaleasyimport` SET `product_limit_per_request` = " . (int) $cron_csv_limit . " WHERE `id_elegantaleasyimport` = " . (int) $model['id_elegantaleasyimport']);
            } else {
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "elegantaleasyimport` SET `product_limit_per_request` = " . (int) $csv_limit . " WHERE `id_elegantaleasyimport` = " . (int) $model['id_elegantaleasyimport']);
            }
        }
    }

    $sql = "ALTER TABLE `" . _DB_PREFIX_ . "elegantaleasyimport` ADD `multiple_subcategory_separator` varchar(5) AFTER `multiple_value_separator`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }
    if (isset($settings['multiple_subcategory_separator']) && $settings['multiple_subcategory_separator']) {
        Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "elegantaleasyimport` SET `multiple_subcategory_separator` = '" . pSQL($settings['multiple_subcategory_separator']) . "' WHERE `id_elegantaleasyimport` > 0");
    }

    $sql = "ALTER TABLE `" . _DB_PREFIX_ . "elegantaleasyimport` ADD `is_associate_all_subcategories` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `multiple_subcategory_separator`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }
    if (isset($settings['is_assign_all_categories_in_multiple_subcategory_tree']) && !$settings['is_assign_all_categories_in_multiple_subcategory_tree']) {
        Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "elegantaleasyimport` SET `is_associate_all_subcategories` = 0 WHERE `id_elegantaleasyimport` > 0");
    }

    $sql = "ALTER TABLE `" . _DB_PREFIX_ . "elegantaleasyimport` ADD `is_first_parent_root_for_categories` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `is_associate_all_subcategories`";
    if (Db::getInstance()->execute($sql) == false) {
        throw new Exception(Db::getInstance()->getMsgError());
    }
    if (isset($settings['is_first_parent_should_be_root_for_multiple_categories']) && !$settings['is_first_parent_should_be_root_for_multiple_categories']) {
        Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "elegantaleasyimport` SET `is_first_parent_root_for_categories` = 0 WHERE `id_elegantaleasyimport` > 0");
    }

    // Delete settings
    // for each shop
    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
        $shop_groups = Shop::getTree();
        foreach ($shop_groups as $shop_group) {
            foreach ($shop_group['shops'] as $shop) {
                $module->deleteSetting('csv_limit', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('cron_csv_limit', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('multiple_subcategory_separator', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('is_assign_all_categories_in_multiple_subcategory_tree', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('is_first_parent_should_be_root_for_multiple_categories', $shop['id_shop_group'], $shop['id_shop']);
            }
        }
    }

    // for all shops
    $module->deleteSetting('csv_limit', "", "");
    $module->deleteSetting('cron_csv_limit', "", "");
    $module->deleteSetting('multiple_subcategory_separator', "", "");
    $module->deleteSetting('is_assign_all_categories_in_multiple_subcategory_tree', "", "");
    $module->deleteSetting('is_first_parent_should_be_root_for_multiple_categories', "", "");

    return true;
}
