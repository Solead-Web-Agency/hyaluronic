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

function upgrade_module_4_1_6()
{
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_discount` (
            `id_ets_abancart_tracking` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_cart_rule` INT(11) UNSIGNED NOT NULL,
            `use_same_cart` TINYINT(1) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_ets_abancart_tracking`, `id_cart_rule`),
            KEY `idx_use_same_cart` (`use_same_cart`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
    ');
    Db::getInstance()->execute('
        INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_discount`(`id_ets_abancart_tracking`, `id_cart_rule`, `use_same_cart`)
        SELECT `id_ets_abancart_tracking`, `id_cart_rule`, 0 as `use_same_cart`
        FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking`
        WHERE `id_cart_rule` is NOT NULL AND `id_cart_rule` > 0
    ');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_tracking` DROP INDEX `idx_id_cart_rule`;');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_tracking` DROP COLUMN `id_cart_rule`;');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_tracking` CHANGE `id_ets_abancart_reminder` `id_ets_abancart_reminder` INT(11) NOT NULL;');

    return true;
}