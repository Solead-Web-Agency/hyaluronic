<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_9_4($module)
{
    // setting each shop
    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
        $shop_groups = Shop::getTree();
        foreach ($shop_groups as $shop_group) {
            foreach ($shop_group['shops'] as $shop) {
                $module->deleteSetting('max_csv_file_size', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('max_cron_csv_file_size', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('is_use_file_function', $shop['id_shop_group'], $shop['id_shop']);
                $module->deleteSetting('is_disable_url_rewrite', $shop['id_shop_group'], $shop['id_shop']);
            }
        }
    }

    // setting for all shops
    $module->deleteSetting('max_csv_file_size', "", "");
    $module->deleteSetting('max_cron_csv_file_size', "", "");
    $module->deleteSetting('is_use_file_function', "", "");
    $module->deleteSetting('is_disable_url_rewrite', "", "");

    return true;
}
