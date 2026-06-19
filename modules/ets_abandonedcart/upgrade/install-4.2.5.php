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
function upgrade_module_4_2_5($object)
{
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_display_log` (
          `id_ets_abancart_display_log` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_ets_abancart_display_tracking` int(11) UNSIGNED NOT NULL,
          `id_shop` int(11) UNSIGNED NOT NULL,
          `id_customer` int(11) UNSIGNED NOT NULL,
          `id_guest` int(11) UNSIGNED NOT NULL,
          `id_cart_rule` int(11) UNSIGNED DEFAULT NULL,
          `id_ets_abancart_reminder` int(11) UNSIGNED NOT NULL,
          `customer_name` varchar(191) DEFAULT NULL,
          `email` varchar(191) DEFAULT NULL,
          `display_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
          `closed_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
          `last_display_time` datetime NOT NULL, 
          PRIMARY KEY (`id_ets_abancart_display_log`),
          UNIQUE KEY (`id_ets_abancart_display_tracking`, `id_guest`, `id_customer`, `id_ets_abancart_reminder`) USING BTREE,
          KEY `id_shop` (`id_shop`),
          KEY `id_cart_rule` (`id_cart_rule`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
    ');

    $id_parent = Tab::getIdFromClassName(Ets_abandonedcart::$slugTab . 'Tracking');
    if ($id_parent !== false) {
        $object->_addTab(['id_parent' => $id_parent, 'origin' => 'Display log', 'class' => 'DisplayLog']);
    }
    return true;
}