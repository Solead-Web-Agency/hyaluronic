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

use BridgeAddon\API\Object\Response\Impl\GetPayment\GetPaymentResponse;
use BridgeAddon\API\Object\Response\Impl\GetPayment\TransactionResponse;
use BridgeAddon\Exception\OrderValidationException;
use BridgeAddon\Model\Constant\PaymentStatuses;
use BridgeAddon\Service\PaymentService;
use BridgeAddon\Utils\ServiceContainer;

class BridgeSuccessModuleFrontController extends ModuleFrontController
{
    /** @var PaymentModule */
    public $module;

    /** Prevent init content from Front Controller (case cart created by webhook) */
    public function init()
    {
    }

    public function initContent()
    {
        $orderUrl = Context::getContext()->link->getPageLink(
            'order',
            null,
            null,
            [
                'step' => 1,
            ]
        );
        $idCartBridge = Tools::getValue('id_cart');

        $cart = new Cart($idCartBridge);

        if (
            Validate::isLoadedObject($cart) === false || $this->module->active == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0 || $cart->id_customer == 0
        ) {
            $this->setErrorTemplate($this->module->l('Error with the cart. Please refresh your page.'), $idCartBridge);
            $this->redirectWithNotifications($orderUrl);
        }

        /** @var PaymentService $paymentService */
        $paymentService = ServiceContainer::getInstance()->get(PaymentService::class);
        $this->isTransactionPending($paymentService, (int) $idCartBridge, $orderUrl);

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            $this->setErrorTemplate(
                $this->module->l('Error with the customer. Please verify your order.'),
                $idCartBridge
            );
            $this->redirectWithNotifications($orderUrl);
        }

        $paymentInfo = $paymentService->getPaymentInfo($cart);
        $errorPayment = $paymentInfo->getStatus() === PaymentStatuses::ERROR_NOT_FOUND;

        if ($errorPayment === true || in_array($paymentInfo->getStatus(), PaymentStatuses::SUCCESS_PAYMENTS) === false) {
            $this->setErrorTemplate(
                $this->module->l('Error with the payment status, please try again.'),
                $idCartBridge
            );
            $this->redirectWithNotifications($orderUrl);
        }

        if ($cart->orderExists() === true) {
            $this->redirectToOrder($cart, $customer);
        }

        parent::init();
        parent::initContent();

        /** @var GetPaymentResponse $payment */
        $payment = $paymentInfo->getResponse();
        $transactions = $payment->getTransactions();
        /** @var TransactionResponse $transaction */
        $transaction = reset($transactions);
        try {
            $paymentService->validateOrder($cart, $customer, $paymentInfo->getStatus(), (float) $transaction->getAmount());
        } catch (OrderValidationException $exception) {
            $message = $this->l('Due to a difference between the amount in your shopping cart and the amount 
                paid, the order could not be created.', 'success');
            $contact = $this->getContact();
            if (!empty($contact)) {
                $message .= ' ';
                $message .= sprintf(
                    $this->l('We recommend you to contact us by email at %s.', 'success'),
                    $contact->email
                );
            }
            $this->setErrorTemplate(
                $message,
                $idCartBridge
            );
            $this->redirectWithNotifications($orderUrl);
        } catch (Exception $exception) {
            $this->setErrorTemplate(
                $this->module->l('Technical error, please contact support.', 'success'),
                $idCartBridge
            );
            $this->redirectWithNotifications($orderUrl);
        }

        $idOrder = $this->module->currentOrder;

        $paymentService->saveTransaction($cart, $idOrder, $paymentInfo->getStatus());

        $this->redirectToOrder($cart, $customer);
    }

    private function isTransactionPending(PaymentService $paymentService, $idCart)
    {
        for ($iteration = 1; $iteration < 15; ++$iteration) {
            $bridgeTransaction = $paymentService->getTransaction($idCart, 0, false);
            if ($bridgeTransaction->status === PaymentStatuses::PROCESS_IN_PROGRESS) {
                sleep(2);
            } elseif (Validate::isLoadedObject($bridgeTransaction) && empty($bridgeTransaction->status) === true) {
                $bridgeTransaction->status = PaymentStatuses::PROCESS_IN_PROGRESS;
                $bridgeTransaction->save();

                return;
            } else {
                return;
            }
        }
    }

    protected function setErrorTemplate($errorMsg, $idCart, $retryUrl = false)
    {
        if ($retryUrl) {
            $paramsCallBack = [
                'id_cart' => $idCart,
            ];

            $isProduction = (bool) Configuration::get(Bridge::PRODUCTION_MODE);
            if ($isProduction === false) {
                $paramsCallBack['sandbox'] = 1;
            }

            $urlRetrySuccess = Context::getContext()->link->getModuleLink(
                'bridge',
                'success',
                $paramsCallBack
            );
            Context::getContext()->smarty->assign([
                'url_retry' => $urlRetrySuccess,
            ]);
        }

        $template = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/front/payment_error.tpl';

        Context::getContext()->smarty->assign([
            'error_with_payment' => $errorMsg,
        ]);

        $this->errors[] = Context::getContext()->smarty->fetch($template);
    }

    protected function redirectToOrder($cart, $customer)
    {
        $linkOrder = $this->context->link->getPageLink(
            'order-confirmation',
            null,
            null,
            [
                'id_cart' => $cart->id,
                'id_module' => $this->module->id,
                'id_order' => $this->module->currentOrder,
                'key' => $customer->secure_key,
            ]
        );

        $this->redirectWithNotifications($linkOrder);
    }

    /**
     * @return Contact|null
     */
    protected function getContact()
    {
        $contacts = new PrestaShopCollection(Contact::class);
        /** @var Contact|null $contact */
        $contact = $contacts->getFirst();
        if (empty($contact) || !Validate::isLoadedObject($contact)) {
            return null;
        }

        return $contact;
    }
}
