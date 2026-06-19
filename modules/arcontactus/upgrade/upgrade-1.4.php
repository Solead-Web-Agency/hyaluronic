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

function upgrade_module_1_4($module)
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'arcontactus`
        ADD COLUMN `always` TINYINT(3) UNSIGNED NULL DEFAULT "1" AFTER `status`,
	ADD COLUMN `d1` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `always`,
	ADD COLUMN `d2` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `d1`,
	ADD COLUMN `d3` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `d2`,
	ADD COLUMN `d4` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `d3`,
	ADD COLUMN `d5` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `d4`,
	ADD COLUMN `d6` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `d5`,
	ADD COLUMN `d7` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `d6`,
	ADD COLUMN `time_from` TIME NULL DEFAULT NULL AFTER `d7`,
	ADD COLUMN `time_to` TIME NULL DEFAULT NULL AFTER `time_from`');
    
    Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'arcontactus` SET always = 1;');
    
    return true;
}
