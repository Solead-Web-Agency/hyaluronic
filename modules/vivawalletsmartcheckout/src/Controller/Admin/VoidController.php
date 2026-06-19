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

use PrestaShop\PrestaShop\Adapter\Validate;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Vivawalletsmartcheckout\Helpers\Config;
use Vivawalletsmartcheckout\Helpers\Database;
use Vivawalletsmartcheckout\Loggers\Logger;
use Vivawalletsmartcheckout\Service\VoidService;

final class VoidController extends PrestaShopAdminController
{
    public function __construct(
        private VoidService $voidService
    ) {
    }
    #[Route(
        path: '/vivawalletsmartchecnout/void/{orderId}/ajax',
        name: 'vivawalletsmartchecnout_void_ajax',
        requirements: ['orderId' => '\d+'],
        methods: ['POST']
    )]
    public function void(Request $request): JsonResponse
    {
        try {
            $vivaModuleInstance = \Module::getInstanceByName('vivawalletsmartcheckout');

            $requestData = [
                'amount' => (float) $request->request->get('amount', 0),
                'orderId' => (int) $request->request->get('order_id', 0),
                'transactionId' => $request->request->get('transaction_id', 0),
                'isCaptureAction' => $request->request->get('is_capture_action', 0),
                'paymentMethod' => $request->request->get('payment_method', 0),
            ];

            $errorCode = $this->validateFields($requestData);
            if (!empty($errorCode)) {
                Logger::log($errorCode, 'voidValidation');
                return new JsonResponse(['error' => true, 'message' => $vivaModuleInstance->getErrorMessage($errorCode)]);
            }

            $order = new \Order($requestData['orderId']);

            if (!Validate::isLoadedObject($order)) {
                return new JsonResponse(['success' => false, 'error' => 'Cannot load prestashop order'], 400);
            }

            $orderShopId = \Shop::isFeatureActive() ? (int) $order->id_shop : null;
            $sourceCode = Config::getFromDatabase("app.form.fields.{$vivaModuleInstance->getEnvironment(true, $orderShopId)}.source", null, $orderShopId);
            $orderCurrency = Database::getOrderCurrencyByOrderId((int) $requestData['orderId']);
            $arguments = [
                $requestData['transactionId'],
                (int) $requestData['amount'],
                $orderCurrency,
                $sourceCode,
            ];

            if ((int) $requestData['isCaptureAction']) {
                Logger::log(
                    [
                        'call' => 'voidTransaction',
                        'arguments' => $arguments,
                        'response' => 'Wrong action. The action must be void.',
                    ],
                    'vivaPayments'
                );
            }

            $transactionResponse = $this->voidService->voidAuthorizedTransaction($orderShopId, ...$arguments);

            if (
                !$transactionResponse->isSuccessful()
                || !$transactionResponse->getBody()->transactionId
            ) {
                Logger::log(
                    [
                        'call' => 'voidTransaction',
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

            $smartCheckoutOrder = Database::getSmartCheckoutOrderByPrestashopOrder($order);
            Database::insertTransaction(
                (string) $smartCheckoutOrder['vivawallet_order_code'],
                $smartCheckoutOrder['is_demo'] == 1 ? 'demo' : 'live',
                (string) $transactionResponse->getBody()->transactionId,
                (float) number_format($requestData['amount'] / 100, 2, '.', ''),
                'void'
            );
            $order->setCurrentState((int) \Configuration::get('PS_OS_CANCELED'));

            return new JsonResponse(['success' => true], 200);
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
        $errorCode = '';
        try {
            $vivaModuleInstance = \Module::getInstanceByName('vivawalletsmartcheckout');
            $prestashopOrder = new \Order($parameters['orderId']);
        } catch (\PrestaShopException $exception) {
            Logger::log(['exception' => $exception->getMessage(), 'class' => __CLASS__], 'retrievePrestashopOrder');
        }

        if (!empty($prestashopOrder)
            && Validate::isLoadedObject($prestashopOrder)
            && $vivaModuleInstance->name === $parameters['paymentMethod']
        ) {
            $transactions = Database::getTransactionsByOrder($prestashopOrder);
            $transactions = array_column($transactions, null, 'transaction_id');
            if (isset($transactions[$parameters['transactionId']])) {
                $transactionRow = $transactions[$parameters['transactionId']];
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
