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

function upgrade_module_1_5_0()
{
    $preauthorizationStateOptions = \Vivawalletsmartcheckout\Helpers\Config::get('app.order.states.preauthorization');
    try {
        \Db::getInstance()->execute('START TRANSACTION');
        $orderState = new \OrderState(Configuration::get($preauthorizationStateOptions['field']));
        $orderStateExists = \Validate::isLoadedObject($orderState);
        $orderState = $orderStateExists ? $orderState : new OrderState();
        $orderState->name = [];
        foreach (\Language::getLanguages() as $language) {
            $orderState->name[$language['id_lang']] = $preauthorizationStateOptions['name'];
            if (empty($orderState->name[$language['id_lang']])) {
                return false;
            }
            if (!empty($preauthorizationStateOptions['options']['template'])) {
                $orderState->template[$language['id_lang']] = $preauthorizationStateOptions['options']['template'];
            }
        }
        $orderState->invoice = $preauthorizationStateOptions['options']['invoice'] ?? false;
        $orderState->send_email = $preauthorizationStateOptions['options']['sendEmail'] ?? false;
        $orderState->logable = $preauthorizationStateOptions['options']['logable'] ?? true;
        $orderState->paid = $preauthorizationStateOptions['options']['paid'] ?? true;
        $orderState->color = $preauthorizationStateOptions['options']['color'] ?? '#3498D8';
        $orderState->pdf_invoice = $preauthorizationStateOptions['options']['pdfInvoice'] ?? false;
        $orderState->module_name = \Vivawalletsmartcheckout\Helpers\Config::get('app.module.attributes.name');
        $orderState->deleted = false;
        $orderStateExists ? $orderState->update() : $orderState->add();
        \Configuration::updateValue($preauthorizationStateOptions['field'], $orderState->id);
        \Db::getInstance()->execute('COMMIT');
    } catch (Exception $exception) {
        \Db::getInstance()->execute('ROLLBACK');
        return false;
    }

    return true;
}
