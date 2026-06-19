<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_1_5_0($module)
{
    if (method_exists($module, 'uninstallCompatibilityWithModules')) {
        $module->uninstallCompatibilityWithModules();    
    }
    if (method_exists($module, 'uninstallThirtyPartyModulesOverridesForCompatibility')) {
        $module->uninstallThirtyPartyModulesOverridesForCompatibility();    
    }
    $module->uninstallOverrides();
    $module->installOverrides();  
    
    $module->unregisterHook('header');
    $module->unregisterHook('displayHeader');
    
    $module->registerHook('actionOrderHistoryAddAfter');
    
    $module->uninstallTabs();
    $module->installTab('AdvancedVatManager','Advanced VAT Manager', false, 'business_center');
    $module->installTab('AdminCustomersVatManager','Customer VAT Management', 'AdvancedVatManager',  'portrait');
    $module->installTab('AdminCustomersExemptionManager','Customer Exemption Management', 'AdvancedVatManager',  'group_add');
    $module->installTab('AdminCustomersOrdersManager','Orders Management', 'AdvancedVatManager', 'euro_symbol');
    $module->installTab('AdminCheckVAT','VAT Check Tool', 'AdvancedVatManager', 'beenhere');
    $module->installTab('AdminAVMSettings','Settings', 'AdvancedVatManager', 'tune');
    
    Configuration::updateValue('ADVANCEDVATMANAGER_DELETE_PREVIOUS_GROUPS', 0);
    Configuration::updateValue('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_PENDING_STATUS', 0);
    Configuration::updateValue('ADVANCEDVATMANAGER_DISPLAY_VAT_VALIDATION_VALID_STATUS', 0);
    Configuration::updateValue('ADVANCEDVATMANAGER_SEND_CUSTOMER_EMAIL_API_FAIL_ORDER', 0);
    
    // Creates the Order state from this module if it is not exists
    $module->createOrderStatus();
    
    //Modify database tables
    $sql = array(
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` MODIFY COLUMN `id_advancedvatmanager_customers` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` MODIFY COLUMN `id_shop` int(11) UNSIGNED NOT NULL DEFAULT 1;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` MODIFY COLUMN `id_customer` int(11) UNSIGNED NOT NULL;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` MODIFY COLUMN `id_address` int(11) UNSIGNED NOT NULL UNIQUE;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` MODIFY COLUMN `id_shop` int(11) UNSIGNED DEFAULT 1;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` MODIFY COLUMN `validated` TINYINT(1) UNSIGNED DEFAULT 0;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` MODIFY COLUMN `validated_company` TINYINT(1) UNSIGNED;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` MODIFY COLUMN `system_check` TINYINT(1) UNSIGNED DEFAULT NULL;',  
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` MODIFY COLUMN `id_advancedvatmanager_orders` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` MODIFY COLUMN `id_order` int(11) UNSIGNED NOT NULL UNIQUE;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` MODIFY COLUMN `id_shop` int(11) UNSIGNED NOT NULL DEFAULT 1;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` MODIFY COLUMN `id_customer` int(11) UNSIGNED NOT NULL;',
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` MODIFY COLUMN `brexit` TINYINT(1) UNSIGNED DEFAULT 0;',
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'advancedvatmanager_customers_exemption` (
        `id_advancedvatmanager_customers_exemption` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_customer` int(11) UNSIGNED NOT NULL UNIQUE,
        `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
        `date_upd` datetime NOT NULL,
        `date_add` datetime NOT NULL,
        PRIMARY KEY  (`id_advancedvatmanager_customers_exemption`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'advancedvatmanager_customers_exemption_shop` (
        `id_advancedvatmanager_customers_exemption` int(11) UNSIGNED NOT NULL,
        `id_shop` int(11) UNSIGNED NOT NULL,
          PRIMARY KEY (`id_advancedvatmanager_customers_exemption`,`id_shop`),
          KEY `id_shop` (`id_shop`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;' 
    );
    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            die('Error while creating Database tables.');
        }
    }
      
    return true; 
}
