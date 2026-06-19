<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

/**
 * @return bool
 */
function upgrade_module_4_2_2()
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` DROP KEY `id_guest`;');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` DROP `id_guest`;');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_reminder` CHANGE `apply_discount` `apply_discount` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` DROP KEY `id_shop`;');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` DROP INDEX `idx_group_tracking_displayed`, ADD UNIQUE `idx_group_tracking_displayed` (`id_ets_abancart_reminder`, `day`, `month`, `year`, `id_shop`) USING BTREE;');
    Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON ac.`id_ets_abancart_campaign`=ar.`id_ets_abancart_campaign`
        SET ar.`discount_option`=\'no\'
        WHERE 
            ac.`id_ets_abancart_campaign` is NOT NULL
            AND ac.`has_product_in_cart` != ' . EtsAbancartCampaign::HAS_SHOPPING_CART_YES . '
    ');

    return true;
}