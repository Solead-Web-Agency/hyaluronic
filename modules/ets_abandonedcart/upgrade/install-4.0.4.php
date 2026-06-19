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

function upgrade_module_4_0_4()
{
    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."ets_abancart_email_template`
        ADD `folder_name` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL
        ");
    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."ets_abancart_email_template_lang`
        ADD `temp_path` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL
        ");
    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."ets_abancart_reminder`
        ADD `allow_multi_discount` TINYINT(1) DEFAULT 0,
        ADD `id_ets_abancart_form` int(10) DEFAULT NULL,
        ADD `header_bg` VARCHAR(50) DEFAULT NULL,
		ADD `popup_body_bg` VARCHAR(50) DEFAULT NULL,
		ADD `popup_width` INT(10) DEFAULT 0,
		ADD `border_radius` INT(10) DEFAULT 0,
		ADD `border_width` INT(10) DEFAULT 0,
		ADD `border_color` VARCHAR(50) DEFAULT NULL,
		ADD `header_text_color` VARCHAR(50) DEFAULT NULL,
		ADD `header_height` INT(10) DEFAULT 0,
		ADD `header_font_size` INT(10) DEFAULT 0,
		ADD `font_size` INT(10) DEFAULT 0,
		ADD `close_btn_color` VARCHAR(50) DEFAULT NULL,
		ADD `padding` INT(10) DEFAULT 0,
		ADD `popup_height` INT(10) DEFAULT 0,
		ADD `vertical_align` VARCHAR(50) DEFAULT NULL,
		ADD `overlay_bg` VARCHAR(50) DEFAULT NULL,
		ADD `overlay_bg_opacity` DECIMAL(4,2) DEFAULT 1
        ");
    Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_campaign` 
                ADD `has_product_in_cart` TINYINT(1) DEFAULT 1
        ');
    return true;
}