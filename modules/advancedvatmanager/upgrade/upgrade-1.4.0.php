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
function upgrade_module_1_4_0($module)
{
    if (method_exists($module, 'uninstallCompatibilityWithModules')) {
        $module->uninstallCompatibilityWithModules();    
    }
    if (method_exists($module, 'uninstallThirtyPartyModulesOverridesForCompatibility')) {
        $module->uninstallThirtyPartyModulesOverridesForCompatibility();    
    }
    $module->uninstallOverrides();
    $module->installOverrides();
    
    $module->registerHook('displayFooter');
    
    // Compatibility hooks with module One Page Checkout Prestashop by Presteamshop
    $module->registerHook('actionOpcValidatePayment');
    $module->registerHook('actionStepBeforePaymentOPC');
    
    // Include this hook to insert invoice number in database table.
    $module->registerHook('actionSetInvoice');
    $module->registerHook('displayExpressCheckout');
    
    $module->registerHook('actionObjectDeleteAfter');
    $module->registerHook('actionObjectAddAfter');
    $module->registerHook('actionObjectUpdateAfter');
    
    // Unregister hooks
    $module->unregisterHook('actionObjectAddressDeleteAfter');
    $module->unregisterHook('actionObjectAddressAddAfter');
    $module->unregisterHook('actionObjectAddressUpdateAfter');
    
    //Uninstall old tabs
    $module->uninstallTabs();
    
    //Install new tabs
    $module->installTab('AdvancedVatManager','Advanced VAT Manager', false, 'business_center');
    $module->installTab('AdminCustomersVatManager','Customer VAT Management', 'AdvancedVatManager', 'portrait');
    $module->installTab('AdminCustomersOrdersManager','Orders Management', 'AdvancedVatManager', 'euro_symbol');
    $module->installTab('AdminCheckVAT','Check VAT tool', 'AdvancedVatManager', 'beenhere');
    $module->installTab('AdminSettings','Settings', 'AdvancedVatManager', 'tune');
    
    Configuration::updateValue('ADVANCEDVATMANAGER_PRODUCT_CUSTOM_LABEL', '');
    Configuration::updateValue('ADVANCEDVATMANAGER_COMPANY_VALIDATION', 0);
    Configuration::updateValue('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT', 0);
    Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID_COMPANY', 0); 
    Configuration::updateValue('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY_COMPANY', 0); 
    Configuration::updateValue('ADVANCEDVATMANAGER_CRON_AUTOINSERT_COMPANY', 0);
    Configuration::updateValue('ADVANCEDVATMANAGER_DISPLAY_WARNING_MESSAGE_ADDRESS', 1); 
    Configuration::updateValue('ADVANCEDVATMANAGER_NOTALLOW_CHECKOUT_WITHOUT_VALIDATION', 1); 
    
    // Remove old methods
    $module->removeModuleClassMethodOverride($module->name, 'getDeliveryOptionList', 'Cart');
    $module->removeModuleClassMethodOverride($module->name, 'getCartRules', 'Cart');
    $module->removeModuleClassMethodOverride($module->name, 'getPackageList', 'Cart');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/override/classes/Cart_(methods_removed_from_1.4.0).php');
    @unlink(_PS_ROOT_DIR_.'/override/modules/onepagecheckoutps/onepagecheckoutps.php');
    
    
    // Remove tpl files
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/templates/admin/check_vat.tpl');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/templates/admin/delete_vat.tpl');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/templates/admin/scan_panel.tpl');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/templates/admin/send_validation_email.tpl');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/templates/admin/validate_vat_api.tpl');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/templates/admin/validate_vat_manually.tpl');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/templates/admin/validate_vat_vies.tpl');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/templates/admin/view_customer.tpl');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/css/vat_manager.css');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/css/back.css');
    @unlink(_PS_MODULE_DIR_.'/advancedvatmanager/views/js/admin/vat_manager.js');
    
    
    
    //Modify database tables
    $sql = array(
    'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ADD IF NOT EXISTS `id_shop` int(11) DEFAULT 1 AFTER `id_address`;','ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ADD IF NOT EXISTS `validated_company` VARCHAR(1) DEFAULT NULL AFTER `validated`;','ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ADD IF NOT EXISTS `system_check` VARCHAR(1) DEFAULT NULL AFTER `validated_company`;','ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ADD IF NOT EXISTS `brexit` TINYINT(1) DEFAULT 0 AFTER `notax`;', 'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ADD IF NOT EXISTS `id_country` INT(11) DEFAULT 0 AFTER `id_customer`;', 'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ADD IF NOT EXISTS `invoice` INT(11) DEFAULT 0 AFTER `brexit`;','ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ADD IF NOT EXISTS `id_shop` INT(11) DEFAULT 1 AFTER `id_order`;', 'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ADD IF NOT EXISTS `date_upd` datetime NOT NULL AFTER `invoice`;', 'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` DROP COLUMN IF EXISTS `id_address_invoice`;', 'ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` DROP COLUMN IF EXISTS `id_address_delivery`;','ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` DROP COLUMN IF EXISTS `country_iso`;','ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` DROP COLUMN IF EXISTS `vat`;','ALTER TABLE `' . _DB_PREFIX_ . 'advancedvatmanager_orders` DROP COLUMN IF EXISTS `id_country`;');
    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            die('Error while creating Database tables.');
        }
    }
    
    // Install UK states and North Ireland
    include(_PS_MODULE_DIR_.'/advancedvatmanager/sql/install_uk.php');
    
    return true; 
}
