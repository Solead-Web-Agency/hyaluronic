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
 * @param $object Ets_abandonedcart
 * @return bool
 */
function upgrade_module_4_2_0($object)
{
    Db::getInstance()->execute('
        DELETE `at` 
        FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` `at`
        INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON ar.id_ets_abancart_reminder = `at`.id_ets_abancart_reminder
        INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign
        WHERE ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_POPUP) . '\' 
            OR ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_BAR) . '\'
            OR ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER) . '\';
    ');

    Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` WHERE id_ets_abancart_reminder=0;');

    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` (
            `id_ets_abancart_display_tracking` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` int(11) UNSIGNED NOT NULL,
            `id_ets_abancart_reminder` int(11) UNSIGNED NOT NULL,
            `day` tinyint(2) UNSIGNED NOT NULL,
            `month` tinyint(2) UNSIGNED NOT NULL,
            `year` smallint(4) UNSIGNED NOT NULL,
            `number_of_displayed` int(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_ets_abancart_display_tracking`),
            UNIQUE KEY `idx_group_tracking_displayed` (`id_ets_abancart_reminder`,`day`,`month`,`year`),
            KEY `id_shop` (`id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
    ');

    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` (
            `id_ets_abancart_display_tracking` int(11) UNSIGNED NOT NULL,
            `id_cart_rule` int(11) UNSIGNED NOT NULL,
            `id_cart` int(11) UNSIGNED NOT NULL,
            `id_guest` int(11) UNSIGNED NOT NULL,
            `use_same_cart` tinyint(1) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_ets_abancart_display_tracking`,`id_cart_rule`,`id_cart`),
            KEY `id_guest` (`id_guest`),
            KEY `use_same_cart` (`use_same_cart`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
    ');

    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_tracking` DROP `has_closed`;');

    Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ets_abancart_campaign` SET has_applied_voucher=\'' . pSQL(EtsAbancartCampaign::APPLIED_VOUCHER_BOTH) . '\' WHERE has_applied_voucher is NULL OR (has_applied_voucher!=\'' . pSQL(EtsAbancartCampaign::APPLIED_VOUCHER_YES) . '\' AND has_applied_voucher!=\'' . pSQL(EtsAbancartCampaign::APPLIED_VOUCHER_NO) . '\')');

    $id_parent = Tab::getIdFromClassName(Ets_abandonedcart::$slugTab . 'Tracking');
    if ($id_parent !== false) {
        $object->_addTab(['id_parent' => $id_parent, 'origin' => 'Email tracking', 'class' => 'EmailTracking']);
        $object->_addTab(['id_parent' => $id_parent, 'origin' => 'Display tracking', 'class' => 'DisplayTracking']);
        $object->_addTab(['id_parent' => $id_parent, 'origin' => 'Discounts', 'class' => 'Discounts']);
    }

    return true;
}