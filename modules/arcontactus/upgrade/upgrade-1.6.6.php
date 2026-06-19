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

function upgrade_module_1_6_6($module)
{
    Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "arcontactus`
	ADD COLUMN `id_shop` INT(10) UNSIGNED NULL DEFAULT '0' AFTER `position`,
	ADD INDEX `id_shop` (`id_shop`);");
    
    Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "arcontactus_callback`
	ADD COLUMN `id_shop` INT(10) UNSIGNED NULL DEFAULT '0' AFTER `comment`,
	ADD INDEX `id_shop` (`id_shop`);");
    
    Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "arcontactus_prompt`
        ADD COLUMN `id_shop` INT(10) UNSIGNED NULL DEFAULT '0' AFTER `position`,
        ADD INDEX `id_shop` (`id_shop`);");
    
    return true;
}
