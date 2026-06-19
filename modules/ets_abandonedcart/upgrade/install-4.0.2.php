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

function upgrade_module_4_0_2($object)
{
    $object->registerHook('actionNewsletterRegistrationAfter');
    return Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."ets_abancart_reminder` ADD `quantity` int(10) UNSIGNED DEFAULT 0,
            ADD `quantity_per_user` int(10) UNSIGNED DEFAULT 0,
            ADD `reduction_product` int(10) DEFAULT 0,
            ADD `selected_product` VARCHAR(191) DEFAULT NULL,
            ADD `reduction_exclude_special` TINYINT(1) DEFAULT 0,
            ADD `gift_product` int(10) UNSIGNED DEFAULT 0,
            ADD `gift_product_attribute` int(10) UNSIGNED DEFAULT 0,
		    ADD `send_repeat_email` TINYINT(1) DEFAULT 0,
		    ADD `send_email_now` TINYINT(1) DEFAULT 0,
		    ADD `schedule_time` DATE DEFAULT NULL
    ") && Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."ets_abancart_tracking` ADD `ip_address` VARCHAR(50) DEFAULT NULL, ADD `email` VARCHAR(50) DEFAULT NULL")
        && Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."ets_abancart_campaign` ADD `newsletter` tinyint(1) UNSIGNED NOT NULL DEFAULT 2");
}