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

include_once dirname(__FILE__).'/../classes/ArContactUsButtonMobileConfig.php';
include_once dirname(__FILE__).'/../classes/ArContactUsPromptMobileConfig.php';
include_once dirname(__FILE__).'/../classes/ArContactUsCallbackConfig.php';

function upgrade_module_1_7_3($module)
{
    $module->registerHook('displayAdminNavBarBeforeEnd');
    $model = new ArContactUsButtonMobileConfig($module, 'arcubm_');
    $model->loadDefaults();
    $model->saveToConfig();
    
    $model = new ArContactUsPromptMobileConfig($module, 'arcuprm_');
    $model->loadDefaults();
    $model->saveToConfig();
    
    $model = new ArContactUsCallbackConfig($module, 'arcuc_');
    if ($model->phone_mask_on) {
        $model->maskedinput = 1;
        $model->saveToConfig();
    }
    
    Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "arcontactus_callback`
	ADD COLUMN `name` VARCHAR(255) NULL DEFAULT NULL AFTER `phone`,
	ADD COLUMN `email` VARCHAR(255) NULL DEFAULT NULL AFTER `name`,
        ADD COLUMN `referer` VARCHAR(255) NULL DEFAULT NULL AFTER `email`,
        ADD COLUMN `checked` TINYINT UNSIGNED NULL DEFAULT NULL AFTER `id_shop`");
    
    Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "arcontactus`
	ADD COLUMN `data` TEXT NULL DEFAULT NULL AFTER `id_shop`;");
    
    return true;
}
