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

function upgrade_module_4_0_5($object)
{
    $object->registerHook('actionFrontControllerInitAfter');
    $object->registerHook('actionFrontControllerAfterInit');
    $object->registerHook('moduleRoutes');
    
    try {
        $object->uninstallOverrides();
        $object->installOverrides();
    }
    catch (Exception $ex){

    }

    $object->_uninstallTab();
    $object->_installTab();
    EtsAbancartEmailTemplate::updateOldEmailTemplate();
    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."ets_abancart_email_template_lang`
        DROP COLUMN email_content;
        ");
    $sql = array();
    $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_abancart_form` (
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
            PRIMARY KEY (`id_ets_abancart_form`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_abancart_form_submit` (
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

    $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_abancart_form_lang` (
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
    $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_abancart_field` (
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
    $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_abancart_field_lang` (
            `id_ets_abancart_field` INT(10) UNSIGNED NOT NULL,
            `id_lang` INT(10) NOT NULL,
            `name` VARCHAR(191) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `placeholder` VARCHAR(191) DEFAULT NULL,
            `content` TEXT DEFAULT NULL,
            PRIMARY KEY (`id_ets_abancart_field`,`id_lang`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_abancart_field_value` (
            `id_ets_abancart_field_value` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_submit` INT(10) NOT NULL DEFAULT 0,
            `id_customer` INT(10) NOT NULL DEFAULT 0,
            `id_ets_abancart_field` INT(10) UNSIGNED NOT NULL,
            `id_lang` INT(10) NOT NULL DEFAULT 0,
            `id_currency` INT(10) NOT NULL DEFAULT 0,
            `id_country` INT(10) NOT NULL DEFAULT 0,
            `id_cart` INT(11) NOT NULL DEFAULT 0,
            `id_ets_abancart_reminder` INT(11) NOT NULL DEFAULT 0,
            `is_leaving_website` TINYINT(1) DEFAULT 0,
            `value` TEXT DEFAULT NULL,
            `file_name` VARCHAR(191) DEFAULT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_ets_abancart_field_value`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";

    foreach ($sql as $item){
        Db::getInstance()->executeS($item);
    }
    EtsAbancartDefines::getInstance($object)->installDefaultLeadConfigs();

    return true;
}