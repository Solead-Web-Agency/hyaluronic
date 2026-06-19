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

use Vivawallet\VivawalletPhp\Api\WebhookClient;
use Vivawalletsmartcheckout\Helpers\Config;
use Vivawalletsmartcheckout\Helpers\Database;
use Vivawalletsmartcheckout\Helpers\Order as VivawalletsmartcheckoutOrder;
use Vivawalletsmartcheckout\Helpers\Transaction;

/**
 * Handles apm notifications
 *
 * Class VivaWalletSmartCheckoutWebhookModuleFrontController
 */
class VivaWalletSmartCheckoutWebhookModuleFrontController extends ModuleFrontController
{
    private const NO_REDIRECT_PAYMENT_METHODS = [15, 16, 32, 56];
    public function initContent()
    {
        parent::initContent();

        if ($this->isGetRequest()) {
            $this->handleVerification();
        }
    }

    public function postProcess()
    {
        parent::postProcess();

        if ($this->isPostRequest()) {
            $this->handleNotification();
        }
    }

    /**
     * Get post request data as an object
     *
     * @return mixed
     */
    private function getPostRequest()
    {
        return json_decode(Tools::file_get_contents('php://input'), false, 512, JSON_BIGINT_AS_STRING);
    }

    private function isGetRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    private function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    private function handleVerification(): void
    {
        $mid = Tools::getValue('mid');
        $credentials = !empty($mid) ? $this->module->getConfigurationsByMid($mid) : [];
        $webhookClient = new WebhookClient($this->module->getBearerAuthentication($credentials));
        $webhookResponse = $webhookClient->getVerificationToken();
        $token = $webhookResponse->isSuccessful() && !empty($webhookResponse->getBody()->key)
            ? $webhookResponse->getBody()->key
            : '';
        echo json_encode(['key' => $token]);
        exit;
    }

    private function sendResponse(int $code): void
    {
        http_response_code($code);
        exit;
    }

    private function handleNotification(): void
    {
        $mid = Tools::getValue('mid');
        $configurations = !empty($mid) ? $this->module->getConfigurationsByMid($mid) : [];
        $environment = Tools::getValue('environment', !empty($configurations['environment']) ? $configurations['environment'] : $this->module->getEnvironment());
        $successStatus = 200;
        $request = $this->getPostRequest();

        // If is not valid request ignore it
        if (!$this->isValidRequest($request)) {
            $this->sendResponse($successStatus);
        }

        $options = [
            'orderCode' => $request->EventData->OrderCode,
            'transactionId' => $request->EventData->TransactionId,
            'environment' => $environment,
            'credentials' => $configurations,
        ];

        $transactionResponse = Transaction::retrieveTransaction($options);

        // If the transaction type is capture
        if (in_array((int) $transactionResponse->getBody()->transactionTypeId, [0, 81])) {
            $this->sendResponse($successStatus);
        }

        // If the transaction status is not successful
        if (
            !in_array($transactionResponse->getBody()->statusId, Transaction::SUCCESS_STATUS_IDS)
            && !in_array($transactionResponse->getBody()->statusId, Transaction::FAILED_STATUS_IDS)
        ) {
            $this->sendResponse($successStatus);
        }

        // Response 423 for 'card' transaction type which created 10 minutes before
        if (
            $this->shouldDelayWebhook($request->Created, $transactionResponse)
        ) {
            $this->sendResponse(423);
        }

        $orderRecord = Database::getSmartCheckoutOrderByCode(
            (string) $request->EventData->OrderCode,
            (string) $environment
        );

        if (empty($orderRecord['cart_id'])) {
            $this->sendResponse($successStatus);
        }

        $cart = new Cart((int) $orderRecord['cart_id']);
        $preferred_status = VivawalletsmartcheckoutOrder::getPreferredOrderStatus(false, $configurations['preferred_status']);
        $orderStateIdMapArray = VivawalletsmartcheckoutOrder::getOrderStateMap($preferred_status);

        $transactionStatus = Transaction::getTransactionStatus($options, $transactionResponse);
        if (
            (
                $transactionStatus == 'successful'
                && $request->EventTypeId == Config::get('app.viva_payments.webhook_events.success')
            )
            || (
                $transactionStatus == 'failed'
                && $request->EventTypeId == Config::get('app.viva_payments.webhook_events.failure')
            )
        ) {
            $transactionId = !empty($request->EventData->TransactionId)
                ? (string) $request->EventData->TransactionId
                : '';

            // Check if order exists
            if (!VivawalletsmartcheckoutOrder::orderExistsForCart($cart)) {
                // Create only orders which transactionstype is successful
                if ($transactionStatus != 'successful') {
                    $this->sendResponse($successStatus);
                }

                // Check if has preauthorization and update transactionStatus
                if (
                    isset($transactionResponse->getBody()->transactionTypeId)
                    && in_array((int) $transactionResponse->getBody()->transactionTypeId, [1, 80])
                ) {
                    $transactionStatus = 'preauthorization';
                }

                $customer = new Customer($cart->id_customer);
                $currencyCode = (string) $orderRecord['currency'];
                $order = VivawalletsmartcheckoutOrder::createPrestashopOrder(
                    $cart,
                    (float) $orderRecord['amount'],
                    $currencyCode,
                    $orderStateIdMapArray[$transactionStatus],
                    $transactionId,
                    $customer->secure_key
                );
                unset($customer);
                if (!empty($order) && Validate::isLoadedObject($order)) {
                    Database::updateSmartCheckoutOrder((int) $orderRecord['id'], (int) $order->id);
                    Database::insertTransaction(
                        (string) $request->EventData->OrderCode,
                        (string) $environment,
                        $transactionId,
                        null,
                        $transactionStatus == 'successful' ? 'payment' : $transactionStatus
                    );
                    VivawalletsmartcheckoutOrder::processSuccessfulOrder(
                        $order,
                        (string) $request->EventData->OrderCode
                    );
                }
            } else {
                $id_order = VivawalletsmartcheckoutOrder::getPrestaIdOrderByIdCart((int) $orderRecord['cart_id']);
                $order = new Order((int) $id_order);

                if (!empty($order)
                    && Validate::isLoadedObject($order)
                    && !empty($order->current_state)
                    && in_array($order->current_state, [
                            $orderStateIdMapArray['pending'],
                            $orderStateIdMapArray['onBackorderNotPaid'],
                            $orderStateIdMapArray['onBackorderPaid'],
                            $orderStateIdMapArray['failed'],
                            (int) Config::getFromDatabase('app.order.states.pending.field'),
                        ]
                    )
                ) {
                    Database::insertTransaction(
                        (string) $request->EventData->OrderCode,
                        (string) $environment,
                        $transactionId,
                        null,
                        $transactionStatus == 'successful' ? 'payment' : $transactionStatus
                    );
                    VivawalletsmartcheckoutOrder::setCurrentState(
                        $order,
                        $orderStateIdMapArray[$transactionStatus]
                    );
                    $orderPayments = OrderPayment::getByOrderReference($order->reference);
                    foreach ($orderPayments as $orderPayment) {
                        $orderPayment->transaction_id = $transactionId;
                        $orderPayment->update();
                    }
                    Database::updateSmartCheckoutOrder((int) $orderRecord['id'], (int) $id_order);
                }
            }
        } else {
            $this->sendResponse(400);
        }

        $this->sendResponse($successStatus);
    }

    private function isValidRequest($request): bool
    {
        if (!is_object($request)) {
            return false;
        }

        // Only allowed EventTypeId
        if (!in_array($request->EventTypeId, Config::get('app.viva_payments.webhook_events'))) {
            return false;
        }

        // Required EventData fields
        if (
            empty($request->EventData->OrderCode)
            || empty($request->EventData->TransactionId)
            || !isset($request->EventData->TransactionTypeId)
        ) {
            return false;
        }

        // Only allowed prestashop ChannelId
        $expectedChannelId = Config::get('app.viva_payments.channel_id');
        if (
            empty($request->EventData->ChannelId)
            || strcasecmp((string) $request->EventData->ChannelId, $expectedChannelId) !== 0
        ) {
            return false;
        }

        return true;
    }

    private function shouldDelayWebhook($requestCreated, $transactionResponse): bool
    {
        if (in_array($transactionResponse->getBody()->statusId, Transaction::FAILED_STATUS_IDS)) {
            return false;
        }

        if (in_array((int) $transactionResponse->getBody()->transactionTypeId, self::NO_REDIRECT_PAYMENT_METHODS)) {
            return false;
        }

        if (strtotime('+10 minutes UTC', strtotime($requestCreated)) < strtotime('now UTC')) {
            return false;
        }

        return true;
    }
}
