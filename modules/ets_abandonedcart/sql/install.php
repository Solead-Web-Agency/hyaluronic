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

$sql = array();
/*---CAMPAIGN---*/
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign` (
        `id_ets_abancart_campaign` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` int(11) UNSIGNED NOT NULL,
        `campaign_type` varchar(8) COLLATE utf8mb4_general_ci NOT NULL,
        `available_from` date DEFAULT NULL,
        `available_to` date DEFAULT NULL,
        `has_product_in_cart` TINYINT(1) DEFAULT 1,
        `min_total_cart` decimal(20,6) DEFAULT NULL,
        `max_total_cart` decimal(20,6) DEFAULT NULL,
        `has_placed_orders` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
        `min_total_order` decimal(20,6) DEFAULT NULL,
        `max_total_order` decimal(20,6) DEFAULT NULL,
        `has_applied_voucher` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
        `is_all_country` tinyint(1) UNSIGNED NOT NULL DEFAULT \'1\',
        `is_all_lang` tinyint(1) UNSIGNED NOT NULL DEFAULT \'1\',
        `last_order_from` date DEFAULT NULL,
        `last_order_to` date DEFAULT NULL,
        `purchased_product` text COLLATE utf8mb4_general_ci,
        `not_purchased_product` text COLLATE utf8mb4_general_ci,
        `newsletter` tinyint(1) UNSIGNED NOT NULL DEFAULT 2,
        `email_timing_option` TINYINT(1) UNSIGNED DEFAULT 0,
        `enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT \'1\',
        `deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\',
        `date_add` datetime NOT NULL,
		PRIMARY KEY (`id_ets_abancart_campaign`),
		UNIQUE KEY `campaign_type` (`id_ets_abancart_campaign`,`campaign_type`),
		UNIQUE KEY `campaign_type_shop` (`id_ets_abancart_campaign`,`id_shop`,`campaign_type`),
		UNIQUE KEY `campaign_shop` (`id_shop`,`id_ets_abancart_campaign`),
		KEY `id_shop` (`id_shop`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_lang`(
		`id_ets_abancart_campaign` INT(11) UNSIGNED NOT NULL,
		`id_lang`                  INT(11) UNSIGNED NOT NULL,
		`name`                     VARCHAR(191)     NOT NULL,
		PRIMARY KEY (`id_ets_abancart_campaign`, `id_lang`)
	) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
';
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_group` (
	  `id_ets_abancart_campaign` int(11) unsigned NOT NULL,
	  `id_group` int(11) unsigned NOT NULL,
	  PRIMARY KEY (`id_ets_abancart_campaign`, `id_group`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_country` (
		`id_ets_abancart_campaign` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`id_country` int(11) NOT NULL,
		PRIMARY KEY (`id_ets_abancart_campaign`, `id_country`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_campaign_with_lang` (
		`id_ets_abancart_campaign` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`id_lang` int(11) unsigned NOT NULL,
		PRIMARY KEY (`id_ets_abancart_campaign`, `id_lang`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
/*---END CAMPAIGN---*/
/*---REMINDER---*/
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_reminder` (
		`id_ets_abancart_reminder` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`id_ets_abancart_campaign` int(11) UNSIGNED NOT NULL,
		`id_ets_abancart_email_template` int(11) DEFAULT NULL,
		`day` decimal(8,2) DEFAULT NULL,
		`hour` decimal(8,2) DEFAULT NULL,
		`minute` decimal(8,2) DEFAULT NULL,
		`second` int(11) DEFAULT NULL,
		`redisplay` decimal(8,2) DEFAULT NULL,
		`delay_popup_based_on` TINYINT(1) NOT NULL DEFAULT \'0\',
		`free_shipping` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\',
		`discount_option` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT \'no\',
		`discount_code` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
		`apply_discount` varchar(32) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
		`reduction_percent` decimal(5,2) NOT NULL,
		`reduction_amount` decimal(17,2) NOT NULL,
		`id_currency` int(11) UNSIGNED NOT NULL,
		`reduction_tax` int(11) UNSIGNED NOT NULL,
		`apply_discount_in` int(11) UNSIGNED NOT NULL,
		`enable_count_down_clock` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\',
		`quantity` int(10) UNSIGNED DEFAULT 0,
		`quantity_per_user` int(10) UNSIGNED DEFAULT 0,
		`reduction_product` int(10) DEFAULT 0,
		`selected_product` VARCHAR(191) DEFAULT NULL,
		`reduction_exclude_special` TINYINT(1) DEFAULT 0,
		`gift_product` int(10) UNSIGNED DEFAULT 0,
		`gift_product_attribute` int(10) UNSIGNED DEFAULT 0,
		`send_repeat_email` TINYINT(1) DEFAULT 0,
		`schedule_time` DATETIME NULL DEFAULT NULL,
		`allow_multi_discount` TINYINT(1) DEFAULT 0,
		`text_color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
		`background_color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
		`icon_notify` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
		`header_bg` VARCHAR(50) DEFAULT NULL,
		`header_text_color` VARCHAR(50) DEFAULT NULL,
		`header_font_size` INT(10) DEFAULT 0,
		`popup_body_bg` VARCHAR(50) DEFAULT NULL,
		`border_color` VARCHAR(50) DEFAULT NULL,
		`close_btn_color` VARCHAR(50) DEFAULT NULL,
		`vertical_align` VARCHAR(50) DEFAULT NULL,
		`overlay_bg` VARCHAR(50) DEFAULT NULL,
		`overlay_bg_opacity` DECIMAL(4,2) DEFAULT 1,
		`header_height` INT(10) DEFAULT 0,
		`popup_width` INT(10) DEFAULT 0,
		`popup_height` INT(10) DEFAULT 0,
		`border_radius` INT(10) DEFAULT 0,
		`border_width` INT(10) DEFAULT 0,
		`font_size` INT(10) DEFAULT 0,
		`padding` INT(10) DEFAULT 0,
		`enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT \'1\',
		`deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\',
		`date_add` datetime NOT NULL,
		PRIMARY KEY (`id_ets_abancart_reminder`),
		UNIQUE KEY `idx_campaign_reminder` (`id_ets_abancart_reminder`,`id_ets_abancart_campaign`),
		UNIQUE KEY `idx_id_reminder_id_template` (`id_ets_abancart_reminder`,`id_ets_abancart_email_template`),
		INDEX (`id_currency`),
		KEY `idx_id_ets_abancart_campaign` (`id_ets_abancart_campaign`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` (
		  `id_ets_abancart_reminder` int(11) NOT NULL,
		  `id_lang` int(11) NOT NULL,
		  `title` varchar(191) DEFAULT NULL,
		  `content` text COLLATE utf8mb4_general_ci,
		  `discount_name` varchar(191) NOT NULL,
		  PRIMARY KEY (`id_ets_abancart_reminder`,`id_lang`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
/*---END REMINDER---*/

/*---TRACKING---*/
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_tracking`(
        `id_ets_abancart_tracking` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_cart` int(11) UNSIGNED DEFAULT NULL,
        `id_customer` int(11) UNSIGNED DEFAULT NULL,
        `email` varchar(50) DEFAULT NULL,
        `ip_address` varchar(50) DEFAULT NULL,
        `id_ets_abancart_reminder` int(11) NOT NULL,
        `id_shop` int(11) UNSIGNED NOT NULL,
        `display_times` datetime DEFAULT NULL,
        `total_execute_times` int(11) NOT NULL DEFAULT \'0\',
        `delivered` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\',
        `read` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\',
        `deleted` tinyint(1) DEFAULT \'0\',
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        `customer_last_visit` DATETIME NULL DEFAULT NULL,
        PRIMARY KEY (`id_ets_abancart_tracking`),
        UNIQUE KEY `idx_id_cart_rule` (`id_ets_abancart_tracking`) USING BTREE,
        UNIQUE KEY `idx_id_cart_id_tracking` (`id_cart`,`id_ets_abancart_tracking`) USING BTREE,
        UNIQUE KEY `idx_id_customer_id_tracking` (`id_ets_abancart_tracking`,`id_customer`) USING BTREE,
        KEY `idx_id_shop` (`id_shop`),
        KEY `idx_id_ets_abancart_reminder` (`id_ets_abancart_reminder`)
	) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
';

$sql[] = '
CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_discount` (
    `id_ets_abancart_tracking` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart_rule` INT(11) UNSIGNED NOT NULL,
    `use_same_cart` TINYINT(1) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_ets_abancart_tracking`, `id_cart_rule`),
    KEY `idx_use_same_cart` (`use_same_cart`)
) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
';

$sql[] = '
CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` (
    `id_ets_abancart_display_tracking` int(11) UNSIGNED NOT NULL,
    `id_cart_rule` int(11) UNSIGNED NOT NULL,
    `id_cart` int(11) UNSIGNED NOT NULL,
    `use_same_cart` tinyint(1) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_ets_abancart_display_tracking`,`id_cart_rule`,`id_cart`),
    KEY `use_same_cart` (`use_same_cart`)
) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
';

$sql[] = '
CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` (
    `id_ets_abancart_display_tracking` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` int(11) UNSIGNED NOT NULL,
    `id_ets_abancart_reminder` int(11) UNSIGNED NOT NULL,
    `day` tinyint(2) UNSIGNED NOT NULL,
    `month` tinyint(2) UNSIGNED NOT NULL,
    `year` smallint(4) UNSIGNED NOT NULL,
    `number_of_displayed` int(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_ets_abancart_display_tracking`),
    UNIQUE KEY `idx_group_tracking_displayed` (`id_ets_abancart_reminder`,`day`,`month`,`year`, `id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
';
/*---END TRACKING---*/


/*---CRONJOB INDEX---*/
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_email_queue`(
		`id_ets_abancart_email_queue` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`id_shop` int(11) UNSIGNED NOT NULL,
		`id_lang` int(11) UNSIGNED NOT NULL,
		`id_cart` int(11) UNSIGNED DEFAULT NULL,
		`id_customer` int(11) UNSIGNED DEFAULT NULL,
		`id_ets_abancart_reminder` int(11) UNSIGNED NOT NULL,
		`customer_name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
		`email` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
		`subject` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
		`content` text COLLATE utf8mb4_general_ci NOT NULL,
		`sent` tinyint(1) UNSIGNED DEFAULT \'0\',
		`sending_time` datetime DEFAULT NULL,
		`send_count` int(1) UNSIGNED NOT NULL DEFAULT \'0\',
		`date_add` datetime NOT NULL,
		INDEX (`id_shop`,`id_lang`,`id_cart`,`id_customer`,`id_ets_abancart_reminder`, `sent`),
		INDEX(`email`),
		PRIMARY KEY (`id_ets_abancart_email_queue`)
	) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
';

$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_index`(
		`id_cart`               INT(11) UNSIGNED NOT NULL,
        `id_ets_abancart_reminder` int(11) UNSIGNED NOT NULL,
		`id_ets_abancart_campaign` int(11) UNSIGNED NOT NULL,
		`id_shop`               INT(11) UNSIGNED NOT NULL,
		`id_customer`           INT(11) UNSIGNED NOT NULL,
		`firstname`             VARCHAR(191)     NOT NULL,
		`lastname`              VARCHAR(191)     NOT NULL,
		`email`                 VARCHAR(191)     NOT NULL,
		`id_cart_lang`          INT(11) UNSIGNED NOT NULL,
		`total_cart`            DECIMAL(20, 6)   NOT NULL DEFAULT \'0.000000\',
		`cart_date_add`         DATETIME         NOT NULL,
		`date_upd`      DATETIME         NOT NULL,
		PRIMARY KEY (`id_cart`, `id_ets_abancart_reminder`, `id_ets_abancart_campaign`),
		KEY `id_shop` (`id_shop`),
		KEY `id_customer` (`id_customer`),
		KEY `id_cart_lang` (`id_cart_lang`),
		KEY `cart_date_add` (`cart_date_add`)
	) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
';
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_index_customer` (
        `id_ets_abancart_index_customer` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_customer` int(11) UNSIGNED NOT NULL,
        `id_ets_abancart_reminder` int(11) UNSIGNED NOT NULL,
        `id_ets_abancart_campaign` int(11) UNSIGNED NOT NULL,
        `id_shop` int(11) UNSIGNED NOT NULL,
        `firstname` varchar(150) NOT NULL,
        `lastname` varchar(150) NOT NULL,
        `email` varchar(254) NOT NULL,
        `id_lang` int(11) UNSIGNED NOT NULL,
        `customer_date_add` datetime DEFAULT NULL,
        `last_login_time` datetime DEFAULT NULL,
        `newsletter_date_add` datetime DEFAULT NULL,
        `last_date_order` datetime DEFAULT NULL,
        `date_upd` datetime DEFAULT NULL,
        PRIMARY KEY (`id_ets_abancart_index_customer`),
        KEY `id_lang` (`id_lang`),
        KEY `idx_id_shop` (`id_shop`),
        KEY `id_customer` (`id_customer`),
        KEY `id_ets_abancart_reminder` (`id_ets_abancart_reminder`),
        KEY `id_ets_abancart_campaign` (`id_ets_abancart_campaign`),
        KEY `email` (`email`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
/*---END CRONJOB INDEX---*/

/*---CUSTOMER, CART---*/
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_shopping_cart` (
		`id_cart` int(11) unsigned NOT NULL,
		`id_customer` int(11) unsigned NOT NULL,
		`cart_name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
		`date_add` datetime NOT NULL,
		PRIMARY KEY (`id_cart`),
		UNIQUE KEY `id_cart_id_customer` (`id_cart`,`id_customer`),
		KEY `id_customer` (`id_customer`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_unsubscribers` (
		`id_customer` int(11) unsigned NOT NULL,
		`date_add` DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (`id_customer`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
';
/*---END CUSTOMER, CART---*/

/*---TEMPLATE---*/
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_email_template`(
      `id_ets_abancart_email_template` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `id_shop` int(11) UNSIGNED NOT NULL,
      `thumbnail` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
      `template_type` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
      `type_of_campaign` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT \'both\',
      `folder_name` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
      `is_init` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\',
      PRIMARY KEY (`id_ets_abancart_email_template`),
      KEY `id_shop` (`id_shop`)
	) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
';
$sql[] = '
	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_email_template_lang`(
		`id_ets_abancart_email_template` int(10) unsigned NOT NULL,
		`id_lang` int(10) unsigned NOT NULL,
		`name` varchar(191) NOT NULL,
		`temp_path` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
		PRIMARY KEY (`id_ets_abancart_email_template`, `id_lang`)
	) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
';

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_abancart_form` (
            `id_ets_abancart_form` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` INT(10) NOT NULL,
            `btn_bg_color` VARCHAR(50) DEFAULT NULL,
            `btn_bg_hover_color` VARCHAR(50) DEFAULT NULL,
            `btn_text_color` VARCHAR(50) DEFAULT NULL,
            `btn_text_hover_color` VARCHAR(50) DEFAULT NULL,
            `enable_captcha` TINYINT(1) DEFAULT 0,
            `captcha_type` VARCHAR(20) DEFAULT NULL,
            `captcha_site_key_v2` VARCHAR(191) DEFAULT NULL,
            `captcha_secret_key_v2` VARCHAR(191) DEFAULT NULL,
            `captcha_site_key_v3` VARCHAR(191) DEFAULT NULL,
            `captcha_secret_key_v3` VARCHAR(191) DEFAULT NULL,
            `disable_captcha_lic` TINYINT(1) DEFAULT 0,
            `display_thankyou_page` TINYINT(1) DEFAULT 0,
            `position` INT(10) DEFAULT 0,
            `is_init` TINYINT(1) DEFAULT 0,
            `enable` TINYINT(1) DEFAULT 0,
            INDEX(`id_shop`,`is_init`, `enable`, `enable_captcha`, `display_thankyou_page`, `disable_captcha_lic`),
            PRIMARY KEY (`id_ets_abancart_form`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_abancart_form_lang` (
            `id_ets_abancart_form` INT(10) UNSIGNED NOT NULL,
            `id_lang` INT(10) NOT NULL,
            `name` VARCHAR(191) DEFAULT NULL,
            `alias` VARCHAR(191) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `btn_title` VARCHAR(191) DEFAULT NULL,
            `thankyou_page_title` VARCHAR(191) DEFAULT NULL,
            `thankyou_page_alias` VARCHAR(191) DEFAULT NULL,
            `thankyou_page_content` TEXT DEFAULT NULL,
            PRIMARY KEY (`id_ets_abancart_form`,`id_lang`),
            INDEX (`id_lang`),
            INDEX (`alias`),
            INDEX (`thankyou_page_alias`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_abancart_field` (
            `id_ets_abancart_field` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_ets_abancart_form` INT(10) UNSIGNED NOT NULL,
            `type` INT(10) NOT NULL,
            `position` INT(10) DEFAULT 0,
            `required` TINYINT(1) DEFAULT 0,
            `is_contact_name` TINYINT(1) DEFAULT 0,
            `is_contact_email` TINYINT(1) DEFAULT 0,
            `display_column` TINYINT(1) DEFAULT 0,
            `enable` TINYINT(1) DEFAULT 0,
            PRIMARY KEY (`id_ets_abancart_field`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_abancart_field_lang` (
            `id_ets_abancart_field` INT(10) UNSIGNED NOT NULL,
            `id_lang` INT(10) NOT NULL,
            `name` VARCHAR(191) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `placeholder` VARCHAR(191) DEFAULT NULL,
            `content` TEXT DEFAULT NULL,
            PRIMARY KEY (`id_ets_abancart_field`,`id_lang`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_abancart_form_submit` (
            `id_ets_abancart_form_submit` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_ets_abancart_form` INT(10) UNSIGNED NOT NULL,
            `id_customer` INT(10) NOT NULL DEFAULT 0,
            `id_lang` INT(10) NOT NULL DEFAULT 0,
            `id_currency` INT(10) NOT NULL DEFAULT 0,
            `id_country` INT(10) NOT NULL DEFAULT 0,
            `id_cart` INT(11) NOT NULL DEFAULT 0,
            `id_ets_abancart_reminder` INT(11) NOT NULL DEFAULT 0,
            `is_leaving_website` TINYINT(1) DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            INDEX(`id_customer`,`id_lang`, `id_currency`,`id_country`,`id_cart`),
            PRIMARY KEY (`id_ets_abancart_form_submit`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_abancart_field_value` (
            `id_ets_abancart_field_value` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_ets_abancart_field` INT(10) NOT NULL DEFAULT 0,
            `id_ets_abancart_form_submit` INT(10) NOT NULL DEFAULT 0,
            `value` TEXT DEFAULT NULL,
            `file_name` VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY (`id_ets_abancart_field_value`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
$sql[] = '
    CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_mail_log` (
      `id_ets_abancart_email_queue` int(11) UNSIGNED NOT NULL,
      `id_shop` int(11) UNSIGNED NOT NULL,
      `id_lang` int(11) UNSIGNED NOT NULL,
      `id_cart` int(11) UNSIGNED DEFAULT NULL,
      `id_customer` int(11) UNSIGNED DEFAULT NULL,
      `id_ets_abancart_reminder` int(11) UNSIGNED NOT NULL,
      `customer_name` varchar(191) NOT NULL,
      `email` varchar(191) NOT NULL,
      `subject` varchar(191) NOT NULL,
      `content` text NOT NULL,
      `sent_time` datetime NOT NULL,
      `status` tinyint(1) UNSIGNED NOT NULL, 
      PRIMARY KEY (`id_ets_abancart_email_queue`),
      KEY `id_shop` (`id_shop`),
      KEY `id_lang` (`id_lang`),
      KEY `id_cart` (`id_cart`),
      KEY `id_customer` (`id_customer`),
      KEY `id_ets_abancart_reminder` (`id_ets_abancart_reminder`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
';
$sql[] = '
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
';
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

/*---END TEMPLATE---*/

/*---INIT TEMPLATE---*/

$iso_code_default = 'en';
$templates = array('email', 'customer');
$languages = Language::getLanguages(false);
$shops = Shop::getShops(false);
if ($templates) {
    EtsAbancartTools::createMailUploadFolder();
    foreach ($templates as $type) {
        if (($dir = dirname(__FILE__) . '/../views/img/init/' . $type) && is_dir($dir)) {
            if ($type == 'email') {
                $files = array(1, 2, 3, 4, 5);
            } else {
                $files = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
            }
            $ik = 0;
            foreach ($files as $file) {
                $ik++;
                $folderPath = $dir . '/' . (int)$ik;
                foreach ($shops as $shop) {
                    $emailTemp = new EtsAbancartEmailTemplate();
                    $emailTemp->id_shop = (int)$shop['id_shop'];
                    $emailTemp->thumbnail = $type . $ik . '.jpg';
                    $emailTemp->template_type = $type;
                    $emailTemp->type_of_campaign = (($type == 'customer' && in_array($ik, array(1, 4, 6, 8, 11))) || ($type == 'email' && in_array($ik, array(1, 5))) ? 'without_discount' : 'with_discount');
                    $emailTemp->is_init = 1;
                    $emailTemp->folder_name = $type . (int)$ik;
                    if (!is_dir(_ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $emailTemp->folder_name)) {
                        mkdir(_ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $emailTemp->folder_name);

                    }
                    $tempPath = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $emailTemp->folder_name;
                    EtsAbancartTools::copyFolder($folderPath, $tempPath);
                    $emailTemp->temp_path = array();
                    $emailTemp->name = array();
                    foreach ($languages as $l) {
                        if (!file_exists($tempPath . '/index_' . $l['iso_code'] . '.html')) {
                            @copy($tempPath . '/index_en.html', $tempPath . '/index_' . $l['iso_code'] . '.html');
                        }
                        $emailTemp->temp_path[$l['id_lang']] = 'index_' . $l['iso_code'] . '.html';
                        $emailTemp->name[$l['id_lang']] = pSQL(Tools::ucfirst($type)) . ' template ' . (int)$ik;
                    }
                    $emailTemp->add();
                }
            }
        }
    }
}


/*---END INIT TEMPLATE---*/

function format_contents($file)
{
    $content = Tools::file_get_contents($file);
    $shop = Context::getContext()->shop;
    $image = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain) . $shop->getBaseURI() . 'modules/ets_abandonedcart/views/img/origin/bg_email.jpg';
    $content = str_replace('[image]', $image, $content);

    return $content;
}
