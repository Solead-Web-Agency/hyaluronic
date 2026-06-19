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

use Vivawallet\VivawalletPhp\Api\TransactionClient;
use Vivawalletsmartcheckout\Helpers\Config;
use Vivawalletsmartcheckout\Helpers\Database;
use Vivawalletsmartcheckout\Loggers\Logger;

class AdminVivawalletsmartcheckoutCaptureController extends ModuleAdminController
{
    /**
     * {@inheritDoc}
     */
    public function initContent()
    {
        parent::initContent();

        $parameters = Tools::getAllValues();
        $errorCode = $this->validateFields($parameters);
        if (empty($errorCode)) {
            $order = new Order($parameters['order_id']);
            $orderShopId = Shop::isFeatureActive() ? (int) $order->id_shop : null;
            $credentials = $this->module->getCredentials(true, $orderShopId);
            $transactionClient = new TransactionClient($this->module->getBearerAuthentication($credentials));
            $orderCurrency = Database::getOrderCurrencyByOrderId((int) $parameters['order_id']);
            $arguments = [
                $parameters['transaction_id'],
                (int) $parameters['amount'],
                [
                    'currencyCode' => $orderCurrency,
                ],
            ];

            if ((int) $parameters['is_capture_action']) {
                $transactionResponse = $transactionClient->captureAuthorizedTransaction(...$arguments);
                if ($transactionResponse->isSuccessful() && isset($transactionResponse->getBody()->transactionId)) {
                    $order = new Order($parameters['order_id']);
                    if (!empty($order) && Validate::isLoadedObject($order)) {
                        $smartCheckoutOrder = Database::getSmartCheckoutOrderByPrestashopOrder($order);
                        Database::insertTransaction(
                            (string) $smartCheckoutOrder['vivawallet_order_code'],
                            $smartCheckoutOrder['is_demo'] == 1 ? 'demo' : 'live',
                            (string) $transactionResponse->getBody()->transactionId,
                            (float) number_format($parameters['amount'] / 100, 2, '.', ''),
                            'capture'
                        );
                        $orderPayments = OrderPayment::getByOrderReference($order->reference);
                        foreach ($orderPayments as $orderPayment) {
                            $orderPayment->transaction_id = (string) $transactionResponse->getBody()->transactionId;
                            $orderPayment->update();
                        }
                        $order->setCurrentState((int) Configuration::get(Config::getFromDatabase('app.form.fields.preferred_status')));
                        echo json_encode(['success' => true]);
                    }
                } else {
                    Logger::log(
                        [
                            'call' => 'captureTransaction',
                            'arguments' => $arguments,
                            'response' => $transactionResponse->all(),
                        ],
                        'vivaPayments'
                    );
                    $message = $this->module->l('An error occurred.');
                    if (isset($transactionResponse->getBody()->message)) {
                        $message .= " ({$transactionResponse->getBody()->message})";
                    }
                    echo json_encode(['error' => true, 'message' => $message]);
                }
            } else {
                $sourceCode = Config::getFromDatabase("app.form.fields.{$this->module->getEnvironment(true, $orderShopId)}.source", null, $orderShopId);
                $arguments = [$parameters['transaction_id'], (int) $parameters['amount'], $orderCurrency, $sourceCode];

                $transactionResponse = $transactionClient->voidAuthorizedTransaction(...$arguments);
                if ($transactionResponse->isSuccessful() && isset($transactionResponse->getBody()->transactionId)) {
                    $order = new Order($parameters['order_id']);
                    if (!empty($order) && Validate::isLoadedObject($order)) {
                        $smartCheckoutOrder = Database::getSmartCheckoutOrderByPrestashopOrder($order);
                        Database::insertTransaction(
                            (string) $smartCheckoutOrder['vivawallet_order_code'],
                            $smartCheckoutOrder['is_demo'] == 1 ? 'demo' : 'live',
                            (string) $transactionResponse->getBody()->transactionId,
                            (float) number_format($parameters['amount'] / 100, 2, '.', ''),
                            'void'
                        );
                        $order->setCurrentState((int) Configuration::get('PS_OS_CANCELED'));
                        echo json_encode(['success' => true]);
                    }
                } else {
                    Logger::log(
                        [
                            'call' => 'voidTransaction',
                            'arguments' => $arguments,
                            'response' => $transactionResponse->all(),
                        ],
                        'vivaPayments'
                    );
                    $message = $this->module->l('An error occurred.');
                    if (isset($transactionResponse->getBody()->message)) {
                        $message .= " ({$transactionResponse->getBody()->message})";
                    }
                    echo json_encode(['error' => true, 'message' => $message]);
                }
            }
        } else {
            Logger::log($errorCode, 'captureValidation');
            echo json_encode(['error' => true, 'message' => $this->module->getErrorMessage($errorCode)]);
        }
        exit;
    }

    /**
     * Validate refund fields
     *
     * @param array $parameters
     *
     * @return string
     */
    private function validateFields(array $parameters): string
    {
        $errorCode = '';
        try {
            $prestashopOrder = new Order($parameters['order_id']);
        } catch (PrestaShopException $exception) {
            Logger::log(['exception' => $exception->getMessage(), 'class' => __CLASS__], 'retrievePrestashopOrder');
        }

        if (!empty($prestashopOrder)
            && Validate::isLoadedObject($prestashopOrder)
            && $this->module->name === $parameters['payment_method']
        ) {
            $transactions = Database::getTransactionsByOrder($prestashopOrder);
            $transactions = array_column($transactions, null, 'transaction_id');
            if (isset($transactions[$parameters['transaction_id']])) {
                $transactionRow = $transactions[$parameters['transaction_id']];
                if ($transactionRow['transaction_type'] == 'preauthorization') {
                    $amountWithDecimals = number_format((float) ($parameters['amount'] / 100), 2, '.', '');
                    if ($amountWithDecimals <= 0 || $amountWithDecimals > $transactionRow['transaction_amount']) {
                        $errorCode = 'notValidAmount';
                    }
                } else {
                    $errorCode = 'notValidTransactionId';
                }
            } else {
                $errorCode = 'transactionIdNotFound';
            }
        } else {
            $errorCode = 'generalError';
        }

        return $errorCode;
    }
}
