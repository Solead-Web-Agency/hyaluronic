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

use Vivawalletsmartcheckout\Helpers\Database;
use Vivawalletsmartcheckout\Helpers\Order as VivawalletsmartcheckoutOrder;
use Vivawalletsmartcheckout\Helpers\Transaction;

class VivaWalletSmartCheckoutSuccessModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $orderCode = Tools::getValue('s');
        $environment = Tools::getValue('environment', $this->module->getEnvironment());
        $transactionId = Tools::getValue('t');
        $eventId = Tools::getValue('eventId');
        $language = Tools::getValue('lang');
        $preferredStatus = VivawalletsmartcheckoutOrder::getPreferredOrderStatus();
        $redirectUrl = VivawalletsmartcheckoutOrder::getOrderFailureLink(
            [
                'orderCode' => $orderCode,
                'transactionId' => $transactionId,
                'eventId' => $eventId,
                'language' => $language,
            ]
        );

        // Redirect to failure page if orderCode or transactionId are missing
        if (
            empty($orderCode)
            || empty($transactionId)
        ) {
            $this->redirectTo($redirectUrl);
        }

        $orderData = Database::getSmartCheckoutOrderByCode(
            (string) $orderCode,
            (string) $environment
        );

        if (empty($orderData['cart_id'])) {
            $this->redirectTo($redirectUrl);
        }

        $cart = new Cart((int) $orderData['cart_id']);

        // if order already existed redirect to orderconfirmation
        if (VivawalletsmartcheckoutOrder::orderExistsForCart($cart)) {
            $orderId = $orderData['prestashop_order_id'];
            $order = new Order((int) $orderId);
            if (
                !empty($order)
                && Validate::isLoadedObject($order)
            ) {
                // Order confirmation page
                $redirectUrl = VivawalletsmartcheckoutOrder::getOrderConfirmationLink($language, $order);
                $this->redirectTo($redirectUrl);
            }
        }

        $orderStateIdMapArray = VivawalletsmartcheckoutOrder::getOrderStateMap($preferredStatus);

        $options = [
            'orderCode' => $orderCode,
            'transactionId' => $transactionId,
            'environment' => $environment,
        ];
        $transactionResponse = Transaction::retrieveTransaction($options);
        $transactionStatus = Transaction::getTransactionStatus($options, $transactionResponse);

        // Redirect to failure if transaction status not equal to successful or pending
        if (!in_array($transactionStatus, ['successful', 'pending'])) {
            $this->redirectTo($redirectUrl);
        }

        if (
            isset($transactionResponse->getBody()->transactionTypeId)
            && in_array((int) $transactionResponse->getBody()->transactionTypeId, [1, 80])
        ) {
            $transactionStatus = 'preauthorization';
        }

        $customer = new Customer($cart->id_customer);
        $currencyCode = (string) $orderData['currency'];

        $order = VivawalletsmartcheckoutOrder::createPrestashopOrder(
            $cart,
            (float) $orderData['amount'],
            $currencyCode,
            $orderStateIdMapArray[$transactionStatus],
            in_array($transactionStatus, ['successful', 'preauthorization']) ? (string) $transactionId : '',
            $customer->secure_key
        );

        if (
            !empty($order)
            && Validate::isLoadedObject($order)
        ) {
            Database::updateSmartCheckoutOrder((int) $orderData['id'], (int) $order->id);
            if (in_array($transactionStatus, ['successful', 'preauthorization'])) {
                Database::insertTransaction(
                    (string) $orderCode,
                    (string) $environment,
                    (string) $transactionId,
                    null,
                    $transactionStatus == 'successful' ? 'payment' : $transactionStatus
                );
            }
            VivawalletsmartcheckoutOrder::processSuccessfulOrder(
                $order,
                (string) $orderCode
            );

            // Order confirmation page
            $redirectUrl = VivawalletsmartcheckoutOrder::getOrderConfirmationLink($language, $order);
        }

        $this->redirectTo($redirectUrl);
    }

    /**
     * Redirect and terminate execution.
     */
    private function redirectTo(string $url): void
    {
        Tools::redirect($url);
        exit;
    }
}
