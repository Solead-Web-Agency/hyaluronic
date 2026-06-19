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

include_once dirname(__FILE__).'/../classes/ArContactUsMenuMobileConfig.php';

function upgrade_module_1_6_1($module)
{
    $menu = new ArContactUsMenuMobileConfig($module, 'arcumm_');
    $menu->loadDefaults();
    $menu->saveToConfig();
    Configuration::updateValue('ARCUC_POPUP_WIDTH', 360);
    Configuration::updateValue('ARCUM_MENU_WIDTH', 300);
    Configuration::updateValue('ARCUM_SHADOW_SIZE', 30);
    Configuration::updateValue('ARCUM_SHADOW_OPACITY', '0.2');
    
    Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "arcontactus`
	ADD COLUMN `registered_only` TINYINT(3) UNSIGNED NULL DEFAULT '0' AFTER `status`;");
    
    Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "arcontactus_lang`
	ADD COLUMN `subtitle` VARCHAR(255) NULL AFTER `title`;");
    return true;
}
