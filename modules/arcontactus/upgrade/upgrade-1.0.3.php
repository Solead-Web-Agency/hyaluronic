<?php
/**
* 2012-2018 Areama
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Areama <contact@areama.net>
*  @copyright 2018 Areama
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__).'/../classes/ArContactUsPromptConfig.php';

function upgrade_module_1_0_3($module)
{
    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_prompt` (
            `id_prompt` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `status` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
            `position` INT(10) UNSIGNED NULL DEFAULT NULL,
            PRIMARY KEY (`id_prompt`)
        )
        ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');

    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_prompt_lang` (
            `id_prompt` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_lang` INT(11) UNSIGNED NOT NULL,
            `message` TEXT NULL,
            PRIMARY KEY (`id_prompt`, `id_lang`)
        )
        ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
    
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'arcontactus`
            ADD COLUMN `js` TEXT NULL DEFAULT NULL AFTER `link`;');
    
    $module->installPrompts();
    
    $promptConfig = new ArContactUsPromptConfig($module, 'arcupr_');
    $promptConfig->loadDefaults();
    $promptConfig->saveToConfig();
    
    Configuration::updateValue('ARCUB_BUTTON_SIZE', 'large');
    Configuration::updateValue('ARCUM_MENU_SIZE', 'large');
    return true;
}
