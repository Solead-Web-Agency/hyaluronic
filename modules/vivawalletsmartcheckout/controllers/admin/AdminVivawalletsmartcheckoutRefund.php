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

class AdminVivawalletsmartcheckoutRefundController extends ModuleAdminController
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
            $sourceCode = Config::getFromDatabase("app.form.fields.{$this->module->getEnvironment(true, $orderShopId)}.source", null, $orderShopId);
            $transactionClient = new TransactionClient($this->module->getBearerAuthentication($credentials));
            $orderCurrency = Database::getOrderCurrencyByOrderId((int) $parameters['order_id']);
            $arguments = [$parameters['transaction_id'], (int) $parameters['amount'], $orderCurrency, $sourceCode];
            $transactionResponse = $transactionClient->refundTransaction(...$arguments);
            $success = false;
            if ($transactionResponse->isSuccessful() && isset($transactionResponse->getBody()->transactionId)) {
                $order = new Order($parameters['order_id']);
                if (!empty($order) && Validate::isLoadedObject($order)) {
                    $smartCheckoutOrder = Database::getSmartCheckoutOrderByPrestashopOrder($order);
                    Database::insertTransaction(
                        (string) $smartCheckoutOrder['vivawallet_order_code'],
                        $smartCheckoutOrder['is_demo'] == 1 ? 'demo' : 'live',
                        (string) $transactionResponse->getBody()->transactionId,
                        (float) number_format($parameters['amount'] / 100, 2, '.', ''),
                        'refund'
                    );
                    $orderStateField = ($parameters['is_full_refund'] === 'true')
                        ? (int) Configuration::get('PS_OS_REFUND')
                        : (int) Config::getFromDatabase('app.order.states.partially_refund.field');
                    $order->setCurrentState($orderStateField);
                    $success = true;
                }
            }
            if ($success) {
                $totalTaxAmount = $order->total_paid_tax_incl - $order->total_paid_tax_excl;
                $taxRate = round($totalTaxAmount / $order->total_paid_tax_excl * 100, 2);
                $refundAmount = (float) number_format($parameters['amount'] / 100, 2, '.', '');
                $refundAmountTaxExcl = round($refundAmount / (1 + $taxRate / 100), 2);
                $taxAmount = $refundAmount - $refundAmountTaxExcl;
                $orderDetailsData = [];
                if (empty($parameters['detail_data'])) {
                    $orderDetail = $order->getProductsDetail();
                    foreach ($orderDetail as $orderDetail) {
                        $orderDetailsData[] = [
                            'id_order_detail' => $orderDetail['id_order_detail'],
                            'product_quantity' => $orderDetail['product_quantity'],
                            'order_detail_amount' => $orderDetail['total_price_tax_incl'],
                        ];
                    }
                } else {
                    $orderDetailsData = $parameters['detail_data'];
                }

                if ($parameters['generate_slip'] === 'true') {
                    $refundSlip = new OrderSlip();
                    $refundSlip->id_order = $order->id;
                    $refundSlip->id_customer = $order->id_customer;
                    $refundSlip->amount = $refundAmount;
                    $refundSlip->conversion_rate = $order->conversion_rate;
                    $refundSlip->total_products_tax_incl = $refundAmount;
                    $refundSlip->total_products_tax_excl = $refundAmountTaxExcl;
                    $refundSlip->shipping_cost = false;
                    $refundSlip->total_shipping_tax_incl = 0;
                    $refundSlip->total_shipping_tax_excl = 0;
                    $refundSlip->order_slip_type = 1;
                    $refundSlip->add();

                    foreach ($orderDetailsData as $detail) {
                        $detailAmount = $detail['order_detail_amount'];
                        $detailAmountTaxExcl = round($detailAmount / (1 + $taxRate / 100), 2);
                        Db::getInstance()->insert('order_slip_detail', [
                            'id_order_slip' => $refundSlip->id,
                            'id_order_detail' => $detail['id_order_detail'],
                            'product_quantity' => $detail['product_quantity'],
                            'unit_price_tax_excl' => $detailAmountTaxExcl / $detail['product_quantity'],
                            'unit_price_tax_incl' => $detailAmount / $detail['product_quantity'],
                            'total_price_tax_excl' => $detailAmountTaxExcl,
                            'total_price_tax_incl' => $detailAmount,
                            'amount_tax_excl' => $detailAmountTaxExcl,
                            'amount_tax_incl' => $detailAmount,
                        ]);
                    }
                }
                foreach ($orderDetailsData as $detail) {
                    $detailAmount = $detail['order_detail_amount'];
                    $detailAmountTaxExcl = round($detailAmount / (1 + $taxRate / 100), 2);
                    $orderDetail = new OrderDetail($detail['id_order_detail']);
                    if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                        $orderDetail->total_refunded_tax_incl += $detailAmount;
                        $orderDetail->total_refunded_tax_excl += $detailAmountTaxExcl;
                    }
                    $orderDetail->product_quantity_refunded += $detail['product_quantity'];
                    $orderDetail->product_quantity_reinjected += $detail['product_quantity'];
                    $orderDetail->update();

                    if ($parameters['restock_products'] === 'true') {
                        StockAvailable::updateQuantity(
                            $orderDetail->product_id,
                            $orderDetail->product_attribute_id,
                            $detail['product_quantity'],
                            $orderDetail->id_shop
                        );
                    }
                }
                echo json_encode(['success' => true]);
            } else {
                Logger::log(
                    [
                        'call' => 'refundTransaction',
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
            Logger::log($errorCode, 'refundValidation');
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
                if (in_array($transactionRow['transaction_type'], ['payment', 'capture'])) {
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
