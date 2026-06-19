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

namespace BridgeAddon\Service;

use BridgeAddon\API\Client\Client;
use BridgeAddon\API\Exception\ClientException;
use BridgeAddon\API\Exception\RequestException;
use BridgeAddon\API\Factory\RequestFactory;
use BridgeAddon\API\Object\Front\CreatePaymentFrontResponse;
use BridgeAddon\API\Object\Request\Impl\CreatePaymentRequestObject;
use BridgeAddon\API\Object\Request\Impl\GetPaymentRequestObject;
use BridgeAddon\API\Object\Response\Impl\CreatePayment\CreatePaymentErrorResponse;
use BridgeAddon\API\Object\Response\Impl\CreatePayment\CreatePaymentResponse;
use BridgeAddon\API\Object\Response\Impl\GetPayment\GetPaymentResponse;
use BridgeAddon\Entity\BridgeTransaction;
use BridgeAddon\Exception\OrderValidationException;
use BridgeAddon\Model\Constant\PaymentStatuses;
use BridgeAddon\Model\PaymentResponseStatusModel;
use BridgeAddon\Repository\PaymentRepository;
use BridgeClasslib\Extensions\ProcessLogger\ProcessLoggerHandler;
use Cart;
use Configuration;
use PrestaShopLogger;

class PaymentService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var \PaymentModule
     */
    public $module;

    /**
     * @var ProcessLoggerHandler
     */
    public $logger;

    /**
     * @var string
     */
    protected $idTransaction;

    /**
     * @param Client $client
     * @param RequestFactory $requestFactory
     * @param PaymentRepository $paymentRepository
     * @param ProcessLoggerHandler $logger
     * @param \Bridge $module
     */
    public function __construct(
        Client $client,
        RequestFactory $requestFactory,
        PaymentRepository $paymentRepository,
        ProcessLoggerHandler $logger,
        \Bridge $module
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->paymentRepository = $paymentRepository;
        $this->logger = $logger;
        $this->module = $module;
    }

    /**
     * @param int $idBank - id of bank used for payment
     * @param int $idCart - id of cart payed
     *
     * @return string|bool - false if error while getting URL
     *
     * @throws ClientException
     * @throws RequestException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createPayment($idBank, $idCart)
    {
        $requestObject = new CreatePaymentRequestObject();
        $requestObject->setBankId((int) $idBank);
        $requestObject->setIdCart((int) $idCart);

        $request = $this->requestFactory->createRequestFromObject($requestObject);

        $response = $this->client->call($request, true);

        $frontResponse = new CreatePaymentFrontResponse();
        if ($response instanceof CreatePaymentResponse) {
            $bridgeTransaction = new BridgeTransaction();
            $bridgeTransaction->id_cart = $idCart;
            $bridgeTransaction->id_bank = $idBank;
            $bridgeTransaction->id_transaction = $response->getId();
            $bridgeTransaction->url = $response->getUrl();
            if ($bridgeTransaction->save() === true) {
                return $frontResponse->fillResponse([
                    'success' => true,
                    'url' => $response->getUrl(),
                ]);
            } else {
                $errorMsg = \Db::getInstance()->getMsgError();
                $this->logger->openLogger();
                $this->logger->logError(
                    $errorMsg,
                    (new \ReflectionClass($this))->getShortName(),
                    null,
                    'Payment save transaction error'
                );
                $this->logger->closeLogger();

                return $frontResponse->fillResponse([
                    'success' => false,
                    'url' => '',
                ]);
            }
        }

        if ($response instanceof CreatePaymentErrorResponse) {
            $errorMsg = $response->getMessage();

            $this->logger->openLogger();
            $this->logger->logError(
                $errorMsg,
                (new \ReflectionClass($this))->getShortName(),
                null,
                'Payment creation error'
            );
            $this->logger->closeLogger();
        }

        return $frontResponse->fillResponse([
            'success' => false,
            'url' => '',
        ]);
    }

    /**
     * @param int $idCart
     * @param PaymentService $paymentService
     *
     * @return bool Return if transaction is pending for current cart
     *              If transaction's status is in array of PaymentStatuses::SUCCESS_PAYMENTS then transaction is pending
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function bridgeTransactionPending($idCart, PaymentService $paymentService)
    {
        $bridgeTransaction = $paymentService->getTransaction($idCart);

        $time = $this->getMinutesUntilTransaction($bridgeTransaction->date_upd);

        return $time < 15 && in_array($bridgeTransaction->status, PaymentStatuses::SUCCESS_PAYMENTS);
    }

    /**
     * @return int minutes until last update of transaction
     */
    private function getMinutesUntilTransaction($dateFrom)
    {
        $dateNow = date_create('now');
        $dateFrom = date_create($dateFrom);

        $diff = date_diff($dateFrom, $dateNow);

        return ($diff->days * 24 * 60) + ($diff->h * 60) + ($diff->i);
    }

    /**
     * @param Cart $cart
     *
     * @return PaymentResponseStatusModel
     *
     * @throws ClientException
     * @throws RequestException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getPaymentInfo($cart)
    {
        $bridgeTransaction = $this->getTransaction($cart->id);
        $this->idTransaction = $bridgeTransaction->id_transaction;

        $requestObject = new GetPaymentRequestObject();
        $requestObject->setId($this->idTransaction);

        $request = $this->requestFactory->createRequestFromObject($requestObject);

        $response = $this->client->call($request);

        $paymentInfo = new PaymentResponseStatusModel();
        $paymentInfo->setResponse($response);

        if ($response instanceof GetPaymentResponse) {
            $bridgeTransaction->status = $response->getStatus();
            $bridgeTransaction->save();

            return $paymentInfo->setStatus($bridgeTransaction->status);
        }

        return $paymentInfo->setStatus(PaymentStatuses::ERROR_NOT_FOUND);
    }

    /**
     * @param Cart $cart
     * @param int $idOrder
     * @param string $status
     *
     * @throws \PrestaShopException
     */
    public function saveTransaction($cart, $idOrder, $status)
    {
        $bridgeTransaction = $this->getTransaction($cart->id);
        $this->idTransaction = $bridgeTransaction->id_transaction;

        $bridgeTransaction->id_order = $idOrder;
        $bridgeTransaction->status = $status;
        $bridgeTransaction->save();
    }

    /**
     * @param Cart $cart
     * @param \Customer $customer
     * @param string $status
     * @param float $amount
     *
     * @throws \PrestaShopException
     * @throws OrderValidationException
     */
    public function validateOrder($cart, $customer, $status, $amount)
    {
        $context = \Context::getContext();
        $currency = $context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        if ($amount != $total) {
            $errorMessage = sprintf(
                'Bridge: Totals from API and PS not match: API %s and PS %s',
                (float) $amount,
                (float) $total);
            $this->logger->openLogger();
            $this->logger->logError(
                $errorMessage,
                Cart::class,
                $cart->id
            );
            $this->logger->closeLogger();
            PrestaShopLogger::addLog($errorMessage, 4, null, Cart::class, $cart->id);
            throw new OrderValidationException(sprintf('Failed to validate order for cart %s, amount paid is not valid', $cart->id));
        }
        $this->setTransactionId($cart);

        $extra_vars = [
            'transaction_id' => $this->idTransaction,
        ];

        $pendingName = null !== _PS_OS_BANKWIRE_ ? _PS_OS_BANKWIRE_ : 'PS_OS_BANKWIRE';
        $defaultPending = Configuration::getGlobalValue($pendingName);

        $idStatePayment = Configuration::get(
            \Bridge::STATUS_PENDING_TRANSFERT,
            null,
            null,
            $context->shop->id,
            $defaultPending
        );

        if (in_array(strtoupper($status), PaymentStatuses::DONE_PAYMENTS) === true) {
            $idStatePayment = $this->getReceivedTransfertStatus();
        }

        $this->module->validateOrder(
            $cart->id,
            $idStatePayment,
            $amount,
            $this->module->l('Pay with Bridge', 'PaymentService'),
            null,
            $extra_vars,
            (int) $currency->id,
            false,
            $customer->secure_key
        );
    }

    /**
     * Update Order Payment that as just happen with transaction ID
     *
     * @param Cart $cart
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function setTransactionId($cart)
    {
        $bridgeTransaction = $this->getTransaction($cart->id);
        $this->idTransaction = $bridgeTransaction->id_transaction;
    }

    /**
     * Get the entity BridgeTransaction by Id Cart
     *
     * @param int $idCart
     * @param int $idTransaction
     * @param bool $useCache
     *
     * @return BridgeTransaction
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getTransaction($idCart, $idTransaction = 0, $useCache = true)
    {
        return $this->paymentRepository->getTransaction($idCart, $idTransaction, $useCache);
    }

    /**
     * Get the order linked to one Cart
     *
     * @param int $idCart id of cart concerned
     *
     * @return \Order
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrderByIdCart($idCart)
    {
        return new \Order($this->paymentRepository->getOrderIdOfCart($idCart));
    }

    /**
     * @return int Status payment received saved for shop concerned by the current card
     */
    public function getReceivedTransfertStatus()
    {
        $context = \Context::getContext();
        $idShop = $context->cart->id_shop;

        $receivedName = null !== _PS_OS_WS_PAYMENT_ ? _PS_OS_WS_PAYMENT_ : 'PS_OS_WS_PAYMENT';
        $defaultReceived = Configuration::getGlobalValue($receivedName);

        return Configuration::get(
            \Bridge::STATUS_RECEIVED_TRANSFERT,
            null,
            null,
            $idShop,
            $defaultReceived
        );
    }

    /**
     * Set status of current order with value saved for shop concerned by the cart
     */
    public function setPaymentStatus($cart, $statusPayment)
    {
        /** @var \Order $oderCart */
        $orderCart = $this->getOrderByIdCart($cart->id);

        if ((int) $orderCart->id_cart !== $cart->id) {
            return 'Error : cart linked to transaction does not match to order linked to.';
        }

        /** @var BridgeTransaction $bridgeTransaction */
        $bridgeTransaction = $this->getTransaction($cart->id);
        $bridgeTransaction->status = $statusPayment;
        $bridgeTransaction->save();

        $statusReceived = (int) $this->getReceivedTransfertStatus();

        if (in_array(strtoupper($statusPayment), PaymentStatuses::DONE_PAYMENTS) !== true) {
            return 'Order not in paid statut, nothing done.';
        }

        if ($orderCart->getCurrentState() === $statusReceived) {
            return 'Order is already in this state, nothing done.';
        }

        $orderCart->setCurrentState($statusReceived);

        return 'Transaction updated with paid state.';
    }
}
