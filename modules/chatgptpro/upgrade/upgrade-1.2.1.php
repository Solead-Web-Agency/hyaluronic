<?php
/**
* 2007-2023 Weblir
*
*  @author    weblir <hello@weblir.com>
*  @copyright 2012-2023 weblir
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*  International Registered Trademark & Property of weblir.com
*
*  You are allowed to modify this copy for your own use only. You must not redistribute it. License
*  is permitted for one Prestashop instance only but you can install it on your test instances.
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param chatgptpro $module
 *
 * @return bool|string
 *
 * @throws PrestaShopException
 */
function upgrade_module_1_2_1($module)
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chatgptpro_log` ADD COLUMN `id_lang` INT(12)');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chatgptpro_log` ADD COLUMN `id_category` INT(12) NULL');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chatgptpro_log` ADD COLUMN `id_cms` INT(12) NULL');

    $result = true;
    foreach ([
                'displayAdminProductsMainStepLeftColumnBottom',
            ] as $hookName) {
        if (!$module->isRegisteredInHook($hookName)) {
            $result &= $module->registerHook($hookName);
        }
    }

    /*
    ** We set the new configuration values
    */
    Configuration::updateValue('WEBLIR_CHATGPTPRO_PRODUCT_LOG', '1');

    unset($module);

    return $result;
}
