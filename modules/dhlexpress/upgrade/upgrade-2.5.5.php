<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author     PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2021 PrestaShop SA
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade to 2.5.5
 *
 * @param $module Dhlexpress
 * @return bool
 */
function upgrade_module_2_5_5($module)
{
    require_once(dirname(__FILE__).'/../classes/logger/AbstractDhlHandler.php');
    require_once(dirname(__FILE__).'/../classes/logger/DhlNullHandler.php');
    require_once(dirname(__FILE__).'/../classes/logger/DhlFileHandler.php');
    require_once(dirname(__FILE__).'/../classes/logger/DhlLogger.php');
    
    if (Configuration::get('DHL_ENABLE_LOG')) {
        $version = str_replace('.', '_', $module->version);
        $hash = Tools::hash(_PS_MODULE_DIR_.$module->name.'/logs/');
        $file = dirname(__FILE__).'/../logs/dhlexpress_'.$hash.'.log';
        $logger = new DhlLogger('DHL_'.$version.'_CustomProduct', new DhlFileHandler($file));
    } else {
        $logger = new DhlLogger('', new DhlNullHandler());
    }

    $logger->info('Start upgrading v2.5.5');
    Configuration::updateValue('DHL_DEFAULT_ORIGIN_COUNTRY', Country::getByIso('FR'));
    Configuration::updateValue('DHL_INVOICE_SHIPPING_COST', 0);
    // Add new hooks
    if (!$module->isRegisteredInHook('displayAdminProductsExtra')) {
        $module->registerHook('displayAdminProductsExtra');
        $logger->info('Register module on displayAdminProductsExtra');
    } else {
        $logger->info('Module already registered on displayPaymentTop');
    }
    if (!$module->isRegisteredInHook('actionProductUpdate')) {
        $module->registerHook('actionProductUpdate');
        $logger->info('Register module on actionProductUpdate');
    } else {
        $logger->info('Module already registered on actionProductUpdate');
    }
    // Add new table
    $dhlCustomProductQuery = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."dhl_custom_product` (
        `id_dhl_custom_product` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_product` INT(10) NULL DEFAULT 0,
        `short_desc` VARCHAR(64) NULL DEFAULT NULL,
        `id_country_origin` INT(10) NULL DEFAULT 0,
        `hs_code` VARCHAR(50) NULL DEFAULT NULL,
        PRIMARY KEY (`id_dhl_custom_product`)
    )";
    $dhlCustomProduct = Db::getInstance()
                                ->execute($dhlCustomProductQuery);
    if (!$dhlCustomProduct) {
        $logger->error('Cannot create table dhl_custom_product.');

        return false;
    }

   
    return true;
}
