<?php
/**
 * Copyright Bridge
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to tech@202-ecommerce.com so we can send you a copy immediately.
 *
 * @author    202 ecommerce <tech@202-ecommerce.com>
 * @copyright Bridge
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

use BridgeAddon\API\Logger\ApiLogger;
use BridgeAddon\API\Object\Front\WebHookResponse;
use BridgeAddon\API\Object\Response\Impl\GetPayment\GetPaymentResponse;
use BridgeAddon\API\Object\Response\Impl\GetPayment\TransactionResponse;
use BridgeAddon\Entity\BridgeTransaction;
use BridgeAddon\Exception\OrderValidationException;
use BridgeAddon\Model\Constant\PaymentStatuses;
use BridgeAddon\Model\Constant\WebHookIP;
use BridgeAddon\Service\PaymentService;
use BridgeAddon\Utils\ServiceContainer;

class BridgeWebhookModuleFrontController extends ModuleFrontController
{
    /** @var \PaymentModule */
    public $module;

    /** @var ApiLogger */
    private $logger;

    /** @var string */
    private $previousState;

    public function initContent()
    {
        $this->logger = ApiLogger::getInstance();
        $this->checkAccessToken();

        $idShop = Context::getContext()->cart->id_shop;
        Configuration::updateValue(Bridge::WEBHOOK_CONTACTED, true, null, null, $idShop);

        $webHookResponse = new WebHookResponse();

        if (Bridge::IS_FILE_LOGGER_ACTIVE) {
            $this->logger->logResponse($this, $webHookResponse->getBodyContent());
        }

        $typeWebHook = $webHookResponse->getTypeWebHook();
        if (empty($typeWebHook) === true) {
            $this->endResponse('Error while retrieving type of transaction.');
        }

        if ($typeWebHook !== 'payment.transaction.updated') {
            $this->endResponse(sprintf(
                'Transaction type : %s - nothing done.',
                $typeWebHook
            ));
        }

        $transactionId = $webHookResponse->getTransactionId();
        if (empty($transactionId) === true) {
            $this->endResponse('Error while retrieving transaction ID.');
        }

        $statusPayment = $webHookResponse->getStatuspayment();
        if (empty($statusPayment) === true) {
            $this->endResponse('No transaction status in content.');
        }

        if (Bridge::IS_FILE_LOGGER_ACTIVE) {
            $this->logger->logResponse('Response status given : ' . $statusPayment, []);
        }

        /** @var PaymentService $paymentService */
        $paymentService = ServiceContainer::getInstance()->get(PaymentService::class);
        $bridgeTransaction = $paymentService->getTransaction(0, $transactionId, false);

        $this->previousState = $bridgeTransaction->status;
        $this->isTransactionPending($transactionId);

        $cart = new Cart($bridgeTransaction->id_cart);

        if (Validate::isLoadedObject($cart) === false) {
            $this->revertStateInProcess($bridgeTransaction);
            $this->endResponse(sprintf(
                'Error : no cart associated to transaction - %s',
                $transactionId
            ));
        }

        $orderExists = $cart->orderExists();
        if (in_array(strtoupper($statusPayment), PaymentStatuses::SUCCESS_PAYMENTS) === false) {
            $this->revertStateInProcess($bridgeTransaction);
            if ($orderExists === true) {
                $this->endResponse('Transaction not updated : not a success status.');
            } else {
                $this->endResponse('Order not created : not a success status.');
            }
        }

        if ($orderExists === true) {
            $messageUpdate = $paymentService->setPaymentStatus($cart, $statusPayment);

            $this->endResponse($messageUpdate);
        }

        $customer = new Customer($cart->id_customer);

        if (Validate::isLoadedObject($customer) === false) {
            $this->revertStateInProcess($bridgeTransaction);
            $this->endResponse(sprintf(
                'Error : while loading customer account - %s',
                $transactionId
            ));
        }

        parent::initContent();

        $paymentInfo = $paymentService->getPaymentInfo($cart);
        /** @var GetPaymentResponse $payment */
        $payment = $paymentInfo->getResponse();

        if (!($payment instanceof GetPaymentResponse)) {
            $this->revertStateInProcess($bridgeTransaction);
            $this->endResponse(sprintf(
                'Error : while loading payment info - %s',
                $transactionId
            ), 500);
        }
        $transactions = $payment->getTransactions();
        /** @var TransactionResponse $transaction */
        $transaction = reset($transactions);
        try {
            $paymentService->validateOrder($cart, $customer, $statusPayment, (float) $transaction->getAmount());
        } catch (OrderValidationException $exception) {
            $this->endResponse($exception->getMessage(), 500);
        } catch (Exception $exception) {
            $this->endResponse($exception->getMessage(), 500);
        }

        $idOrder = $this->module->currentOrder;

        $paymentService->saveTransaction($cart, $idOrder, $statusPayment);

        $this->endResponse('Order created and transaction updated');
    }

    /**
     * Check if transaction is pending on success controller (in the same time)
     */
    private function isTransactionPending($transactionId)
    {
        /** @var PaymentService $paymentService */
        $paymentService = ServiceContainer::getInstance()->get(PaymentService::class);
        for ($iteration = 1; $iteration < 15; ++$iteration) {
            $bridgeTransaction = $paymentService->getTransaction(0, $transactionId, false);
            if ($bridgeTransaction->status === PaymentStatuses::PROCESS_IN_PROGRESS) {
                sleep(2);
            } elseif (Validate::isLoadedObject($bridgeTransaction) && empty($bridgeTransaction->status) === true) {
                $bridgeTransaction->status = PaymentStatuses::PROCESS_IN_PROGRESS;
                $bridgeTransaction->save();
            } else {
                return;
            }
        }
    }

    /**
     * Replace previous state before processing
     *
     * @param BridgeTransaction $bridgeTransaction
     *
     * @throws PrestaShopException
     */
    protected function revertStateInProcess(BridgeTransaction $bridgeTransaction)
    {
        $bridgeTransaction->status = $this->previousState;
        $bridgeTransaction->save();
    }

    protected function endResponse($message, $responseCode = 200)
    {
        if (Bridge::IS_FILE_LOGGER_ACTIVE) {
            $this->logger->logResponse($this, $message);
        }
        http_response_code($responseCode);
        $this->ajaxDie($message);
    }

    /**
     * Check if secure key is correct in URL
     */
    protected function checkAccessToken()
    {
        if (false === Tools::isSubmit('secure_key') || Tools::getValue('secure_key') !== $this->module->secure_key) {
            $this->endResponse('Access not allowed (secure key invalid).');
        }

        $ipServer = Tools::getRemoteAddr();
        if (in_array($ipServer, WebHookIP::AUTHORIZED_IP) === false) {
            if (Bridge::IS_FILE_LOGGER_ACTIVE) {
                $this->logger->logResponse($this, 'IP not allowed for WebHooks: ' . $ipServer);
            }
            $this->endResponse('Access not allowed (server not authorized).');
        }
    }
}
