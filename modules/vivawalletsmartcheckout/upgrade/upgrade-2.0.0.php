<?php
/**
 * Copyright since 2007 Viva Wallet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@vivawallet.com so we can send you a copy immediately.
 *
 * @author    Viva Wallet <support@vivawallet.com>
 * @copyright Since 2007 Viva Wallet
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param $module
 *
 * @return bool
 *
 * */
function upgrade_module_2_0_0($module)
{
    try {
        $vivaModuleInstance = $module;

        if (
            !$module
            || $module->name !== 'vivawalletsmartcheckout'
        ) {
            $vivaModuleInstance = \Module::getInstanceByName('vivawalletsmartcheckout');
        }

        if (!$vivaModuleInstance) {
            return false;
        }

        $hookRegistrationCompleted = $vivaModuleInstance->registerHook('displayPaymentReturn')
            && $vivaModuleInstance->registerHook('displayHeader');

        $vivaModuleInstance->unregisterHook('header');
        $vivaModuleInstance->unregisterHook('paymentReturn');

        return (bool) $hookRegistrationCompleted;
    } catch (\Exception $exception) {
        return false;
    }
}
