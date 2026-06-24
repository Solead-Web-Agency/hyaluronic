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

namespace Vivawalletsmartcheckout\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Vivawalletsmartcheckout\Helpers\Config;
use Vivawalletsmartcheckout\Helpers\Database;
use Vivawalletsmartcheckout\Loggers\Logger;
use Vivawalletsmartcheckout\Service\RefundService;

final class RefundController extends PrestaShopAdminController
{
    public function __construct(
        private RefundService $refundService
    ) {
    }

    #[Route(
        path: '/vivawalletsmartcheckout/refund/{orderId}/ajax',
        name: 'vivawalletsmartcheckout_refund_ajax',
        requirements: ['orderId' => '\d+'],
        methods: ['POST']
    )]
    public function refund(Request $request): JsonResponse
    {
        try {
            $requestData = [
                'amount' => (float) $request->request->get('amount', 0),
                'orderId' => (int) $request->request->get('order_id', 0),
                'transactionId' => $request->request->get('transaction_id', 0),
                'isFullRefund' => $request->request->get('is_full_refund', false),
                'restockProducts' => $request->request->get('restock_products', false),
                'generateSlip' => $request->request->get('generate_slip', false),
                'paymentMethod' => $request->request->get('payment_method', ''),
            ];

            $errorCode = $this->validateFields($requestData);
            if (!empty($errorCode)) {
                Logger::log($errorCode, 'refundValidation');
                return new JsonResponse(['error' => true, 'message' => $this->getErrorMessage($errorCode)]);
            }

            $message = '';
            $order = new \Order($requestData['orderId']);
            $vivaModuleInstance = \Module::getInstanceByName('vivawalletsmartcheckout');
            $success = false;

            if (!\Validate::isLoadedObject($order)) {
                return new JsonResponse(['success' => false, 'error' => 'Cannot load prestashop order'], 400);
            }

            if ($requestData['amount'] <= 0) {
                return new JsonResponse(['success' => false, 'error' => 'Amount must be > 0'], 400);
            }

            $orderShopId = \Shop::isFeatureActive() ? (int) $order->id_shop : null;
            $sourceCode = Config::getFromDatabase("app.form.fields.{$vivaModuleInstance->getEnvironment(true, $orderShopId)}.source", null, $orderShopId);
            $orderCurrency = Database::getOrderCurrencyByOrderId((int) $order->id);
            $arguments = [$requestData['transactionId'], (int) $requestData['amount'], $orderCurrency, $sourceCode];
            $transactionResponse = $this->refundService->processRefund($orderShopId, ...$arguments);

            if (
                !$transactionResponse->isSuccessful()
                || !$transactionResponse->getBody()->transactionId
            ) {
                \Vivawalletsmartcheckout\Loggers\Logger::log(
                    ['call' => 'refundTransaction', 'args' => [$requestData['transactionId'], $arguments, $sourceCode], 'resp' => $transactionResponse->all()],
                    'vivaPayments'
                );
                $message = $transactionResponse->getBody()->message ?? 'Refund failed';

                return new JsonResponse(['error' => true, 'message' => $message]);
            }

            if (
                !empty($order)
                && \Validate::isLoadedObject($order)
            ) {
                $smartCheckoutOrder = Database::getSmartCheckoutOrderByPrestashopOrder($order);
                Database::insertTransaction(
                    (string) $smartCheckoutOrder['vivawallet_order_code'],
                    $smartCheckoutOrder['is_demo'] == 1 ? 'demo' : 'live',
                    (string) $transactionResponse->getBody()->transactionId,
                    (float) number_format($requestData['amount'] / 100, 2, '.', ''),
                    'refund'
                );
                $orderStateField = ($requestData['isFullRefund'] === 'true')
                    ? (int) \Configuration::get('PS_OS_REFUND')
                    : (int) Config::getFromDatabase('app.order.states.partially_refund.field');
                $order->setCurrentState($orderStateField);
                $success = true;
            }

            if ($success) {
                $totalTaxAmount = $order->total_paid_tax_incl - $order->total_paid_tax_excl;
                $taxRate = round($totalTaxAmount / $order->total_paid_tax_excl * 100, 2);
                $refundAmount = (float) number_format($requestData['amount'] / 100, 2, '.', '');
                $refundAmountTaxExcl = round($refundAmount / (1 + $taxRate / 100), 2);
                $taxAmount = $refundAmount - $refundAmountTaxExcl;
                $orderDetailsData = [];
                if (empty($requestData['detail_data'])) {
                    $orderDetail = $order->getProductsDetail();
                    foreach ($orderDetail as $orderDetail) {
                        $orderDetailsData[] = [
                            'id_order_detail' => $orderDetail['id_order_detail'],
                            'product_quantity' => $orderDetail['product_quantity'],
                            'order_detail_amount' => $orderDetail['total_price_tax_incl'],
                        ];
                    }
                } else {
                    $orderDetailsData = $requestData['detail_data'];
                }

                if ($requestData['generateSlip'] === 'true') {
                    $refundSlip = new \OrderSlip();
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
                        \Db::getInstance()->insert('order_slip_detail', [
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
                    $orderDetail = new \OrderDetail($detail['id_order_detail']);
                    if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                        $orderDetail->total_refunded_tax_incl += $detailAmount;
                        $orderDetail->total_refunded_tax_excl += $detailAmountTaxExcl;
                    }
                    $orderDetail->product_quantity_refunded += $detail['product_quantity'];
                    $orderDetail->product_quantity_reinjected += $detail['product_quantity'];
                    $orderDetail->update();

                    if ($requestData['restockProducts'] === 'true') {
                        \StockAvailable::updateQuantity(
                            $orderDetail->product_id,
                            $orderDetail->product_attribute_id,
                            $detail['product_quantity'],
                            $orderDetail->id_shop
                        );
                    }
                }
            } else {
                Logger::log(
                    [
                        'call' => 'refundTransaction',
                        'arguments' => $arguments,
                        'response' => $transactionResponse->all(),
                    ],
                    'vivaPayments'
                );
                $message = $vivaModuleInstance->l('An error occurred.');

                if (isset($transactionResponse->getBody()->message)) {
                    $message .= " ({$transactionResponse->getBody()->message})";
                }

                return new JsonResponse(['error' => true, 'message' => $message]);
            }

            return new JsonResponse(['error' => false, 'message' => $message]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => true, 'message' => $e->getMessage()]);
        }
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
        $vivaModuleInstance = \Module::getInstanceByName('vivawalletsmartcheckout');

        $errorCode = '';
        try {
            $prestashopOrder = new \Order($parameters['orderId']);
        } catch (\PrestaShopException $exception) {
            Logger::log(['exception' => $exception->getMessage(), 'class' => __CLASS__], 'retrievePrestashopOrder');
        }

        if (!empty($prestashopOrder)
            && \Validate::isLoadedObject($prestashopOrder)
            && $vivaModuleInstance->name === $parameters['paymentMethod']
        ) {
            $transactions = Database::getTransactionsByOrder($prestashopOrder);
            $transactions = array_column($transactions, null, 'transaction_id');
            if (isset($transactions[$parameters['transactionId']])) {
                $transactionRow = $transactions[$parameters['transactionId']];
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

    /**
     * Get error message by errorCode
     *
     * @param string $errorCode
     *
     * @return string
     */
    private function getErrorMessage(string $errorCode): string
    {
        $vivaModuleInstance = \Module::getInstanceByName('vivawalletsmartcheckout');

        switch ($errorCode) {
            case 'notValidAmount':
                return $vivaModuleInstance->l('Amount requested is not valid.');
            case 'notValidTransactionId':
                return $vivaModuleInstance->l('Not Valid Transaction Id.');
            case 'transactionIdNotFound':
                return $vivaModuleInstance->l('Transaction id not found for this order');
        }

        return $vivaModuleInstance->l('An error occurred.');
    }
}
