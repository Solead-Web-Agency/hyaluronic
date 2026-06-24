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

use Vivawalletsmartcheckout\Helpers\Config;

function upgrade_module_1_4_1()
{
    $query = new \DbQuery();
    $query->select('name')
        ->from(Config::get('app.database.tables.transaction_types'));
    $results = \Db::getInstance()->executeS($query);
    $types = !empty($results) ? array_column($results, 'name') : [];
    $typesMissing = array_diff(['payment', 'refund', 'failed', 'init_async_payment', 'preauthorization', 'capture', 'void'], $types);
    $insertTypes = array_map(function ($value) {
        return ['name' => $value];
    }, $typesMissing);

    if (!empty($insertTypes)) {
        try {
            \Db::getInstance()->execute('START TRANSACTION');
            // Perform the inserts
            \Db::getInstance()->insert(
                Config::get('app.database.tables.transaction_types'),
                $insertTypes
            );
            // Commit the transaction if all inserts succeed
            \Db::getInstance()->execute('COMMIT');
        } catch (Exception $e) {
            \Db::getInstance()->execute('ROLLBACK');
            return false;
        }
    }

    $awaitingPaymentStateOptions = \Vivawalletsmartcheckout\Helpers\Config::get('app.order.states.awaiting_payment');
    try {
        \Db::getInstance()->execute('START TRANSACTION');
        $onHoldTab = \Tab::getIdFromClassName('AdminVivawalletsmartcheckoutOnHold');
        if (isset($onHoldTab) && $onHoldTab) {
            $onHoldTab = new \Tab((int) $onHoldTab);
            $onHoldTab->active = false;
            $onHoldTab->update();
        }
        $orderState = new \OrderState(Configuration::get($awaitingPaymentStateOptions['field']));
        $orderStateExists = \Validate::isLoadedObject($orderState);
        $orderState = $orderStateExists ? $orderState : new OrderState();
        $orderState->name = [];
        foreach (\Language::getLanguages() as $language) {
            $orderState->name[$language['id_lang']] = $awaitingPaymentStateOptions['name'];
            if (empty($orderState->name[$language['id_lang']])) {
                return false;
            }
            if (!empty($awaitingPaymentStateOptions['options']['template'])) {
                $orderState->template[$language['id_lang']] = $awaitingPaymentStateOptions['options']['template'];
            }
        }
        $orderState->invoice = $awaitingPaymentStateOptions['options']['invoice'] ?? false;
        $orderState->send_email = $awaitingPaymentStateOptions['options']['sendEmail'] ?? false;
        $orderState->logable = $awaitingPaymentStateOptions['options']['logable'] ?? true;
        $orderState->paid = $awaitingPaymentStateOptions['options']['paid'] ?? true;
        $orderState->color = $awaitingPaymentStateOptions['options']['color'] ?? '#3498D8';
        $orderState->pdf_invoice = $awaitingPaymentStateOptions['options']['pdfInvoice'] ?? false;
        $orderState->module_name = \Vivawalletsmartcheckout\Helpers\Config::get('app.module.attributes.name');
        $orderState->deleted = false;
        $orderStateExists ? $orderState->update() : $orderState->add();
        \Configuration::updateValue($awaitingPaymentStateOptions['field'], $orderState->id);
        \Db::getInstance()->execute('COMMIT');
    } catch (Exception $exception) {
        \Db::getInstance()->execute('ROLLBACK');
        return false;
    }

    return true;
}
