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
function upgrade_module_4_0_1($object)
{

    return
        $object->registerHook('displayBoFormTestMail')
        && Configuration::updateGlobalValue('ETS_ABANCART_AUTO_CLEAR_DISCOUNT', 1)
        && Db::getInstance()->execute('
             ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_reminder` 
                MODIFY COLUMN `day` decimal(8,2) DEFAULT NULL, 
                MODIFY COLUMN `hour` decimal(8,2) DEFAULT NULL,
                MODIFY COLUMN `minute` decimal(8,2) DEFAULT NULL,
                MODIFY COLUMN `reduction_percent` decimal(5,2) DEFAULT NULL,
                MODIFY COLUMN `reduction_amount` decimal(17,2) DEFAULT NULL,
                MODIFY COLUMN `redisplay` decimal(8,2) DEFAULT NULL
        ')
        && Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_tracking` 
                ADD `total_execute_times` INT(11) UNSIGNED  DEFAULT 0 AFTER `display_times`, 
                ADD `id_ets_abancart_campaign` INT(11) UNSIGNED NOT NULL AFTER `id_ets_abancart_reminder`;
        ')
        && Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_email_template` 
                ADD `type_of_campaign` VARCHAR(64) DEFAULT NULL AFTER `template_type`;
        ')
        && Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_cart` 
                ADD `sending_time` DATETIME  DEFAULT NULL AFTER `note_manually`;
        ')
        && Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_campaign` 
                ADD `has_applied_voucher` VARCHAR(10) DEFAULT NULL AFTER `has_placed_orders`,
                ADD `is_all_country` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `has_applied_voucher`, 
                ADD `is_all_lang` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `is_all_country`,
                ADD `last_order_from` DATE NULL AFTER `is_all_lang`, 
                ADD `last_order_to` DATE NULL AFTER `last_order_from`,
                ADD `min_total_order` DECIMAL(20,6) NULL AFTER `has_placed_orders`, 
                ADD `max_total_order` DECIMAL(20,6) NULL AFTER `min_total_order`,
                ADD `purchased_product` TEXT NULL DEFAULT NULL AFTER `last_order_to`, 
                ADD `email_timing_option` TINYINT(1) UNSIGNED DEFAULT 0,
                ADD `not_purchased_product` TEXT NULL DEFAULT NULL AFTER `purchased_product`
            ;
        ')
        && Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_country` (
                `id_ets_abancart_campaign` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_country` int(11) NOT NULL,
                PRIMARY KEY (`id_ets_abancart_campaign`, `id_country`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ')
        && Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_with_lang` (
                `id_ets_abancart_campaign` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_lang` int(11) unsigned NOT NULL,
                PRIMARY KEY (`id_ets_abancart_campaign`, `id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ')
        && Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` 
                ADD `id_ets_abancart_reminder` INT(11) UNSIGNED NOT NULL AFTER `id_customer`, 
                ADD `id_ets_abancart_campaign` INT(11) UNSIGNED NOT NULL AFTER `id_ets_abancart_reminder`,
                DROP PRIMARY KEY, 
                ADD PRIMARY KEY (`id_customer`, `id_ets_abancart_reminder`, `id_ets_abancart_campaign`) USING BTREE,
                DROP `ordered`
                ;
        ')
        ;
}