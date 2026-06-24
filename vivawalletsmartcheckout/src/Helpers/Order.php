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

namespace Vivawalletsmartcheckout\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Vivawalletsmartcheckout\Loggers\Logger;

class Order
{
    /**
     * @param $order
     * @param $orderCode
     *
     * @return void
     */
    public static function processSuccessfulOrder($order, $orderCode)
    {
        if (property_exists($order, 'note')) {
            $module = \Module::getInstanceByName(Config::get('app.module.attributes.name'));
            $note = "{$module->displayName} {$module->l('orderCode')}: "
                . "$orderCode\n";
            if (stripos($order->note, $note) === false) {
                $order->note .= $note;
            }
            try {
                $order->update();
            } catch (\PrestaShopException $exception) {
                Logger::log(
                    ['class' => __CLASS__, 'exception' => $exception->getMessage()],
                    'updatePrestashopOrder'
                );
            }
        }
    }

    /**
     * Get link of order failure page
     *
     * @param array $parameters
     *
     * @return mixed
     */
    public static function getOrderFailureLink(array $parameters = [])
    {
        return \Context::getContext()->link->getModuleLink(
            Config::get('app.module.attributes.name'),
            'failure',
            array_filter(
                [
                    's' => $parameters['orderCode'] ?? null,
                    't' => $parameters['transactionId'] ?? null,
                    'eventId' => $parameters['eventId'] ?? null,
                    'lang' => $parameters['language'] ?? null,
                ],
                function ($value) {
                    return !empty($value) || $value === 0;
                }
            ),
            true
        );
    }

    /**
     * Get link of order confirmation page
     *
     * @param $language
     * @param $order
     *
     * @return mixed
     */
    public static function getOrderConfirmationLink($language, $order = null)
    {
        $module = \Module::getInstanceByName(Config::get('app.module.attributes.name'));
        $languageParts = explode('-', $language);
        $languageId = isset($languageParts[0]) ? \Language::getIdByIso($languageParts[0]) : null;

        return \Context::getContext()->link->getPageLink(
            'order-confirmation',
            true,
            !empty($languageId) ? $languageId : null,
            [
                'id_cart' => !empty($order) ? $order->id_cart : \Context::getContext()->cart->id,
                'id_order' => !empty($order) ? (int) $order->id : 0,
                'id_module' => (int) $module->id,
                'key' => \Context::getContext()->customer->secure_key,
            ]
        );
    }

    /**
     * Create order in prestashop admin panel
     *
     * @param \Cart $cart
     * @param float $amount
     * @param string $currencyIsoCode
     * @param int $orderStateId
     * @param string $transactionId
     * @param string $secureKey
     */
    public static function createPrestashopOrder(
        \Cart $cart,
        float $amount,
        string $currencyIsoCode,
        int $orderStateId,
        string $transactionId,
        string $secureKey
    ) {
        $order = null;
        $module = \Module::getInstanceByName(Config::get('app.module.attributes.name'));
        if ($module->active
            && 0 != $cart->id_customer
            && 0 != $cart->id_address_delivery
            && 0 != $cart->id_address_invoice
            && $secureKey == $cart->secure_key
        ) {
            if (!self::orderExistsForCart($cart)) {
                $currencyId = (!empty($currencyIsoCode) ? \Currency::getIdByIsoCode($currencyIsoCode) : 0);
                $successValidateOrder = $module->validateOrder(
                    (int) $cart->id,
                    $orderStateId,
                    $amount,
                    $module->displayName,
                    null,
                    !empty($transactionId) ? ['transaction_id' => $transactionId] : [],
                    !empty($currencyId) ? $currencyId : null,
                    false,
                    !empty($cart->secure_key) ? $cart->secure_key : false
                );
                if ($successValidateOrder && !empty($module->currentOrder)) {
                    $order = new \Order($module->currentOrder);
                }
            } else {
                $order = new \Order(\Order::getOrderByCartId($cart->id));
            }
        }

        if (empty($order) || !\Validate::isLoadedObject($order)) {
            Logger::log(
                ['error' => 'Create Order unsuccessful', 'cart_id' => $cart->id],
                'createPrestashopOrder'
            );
        }

        return $order;
    }

    /**
     * Clean cache before checking if cart has already an order to avoid conflicts in Prestashop 1.6
     *
     * @param $cart
     *
     * @return bool
     */
    public static function orderExistsForCart($cart)
    {
        \Cache::clean('Cart::orderExists_' . (int) $cart->id);

        return $cart->orderExists();
    }

    /**
     * Override setCurrentState when in prestashop 1.6 to avoid duplicate order payments
     *
     * @param $order
     * @param int $id_order_state
     * @param int $id_employee
     *
     * @return false|void
     */
    public static function setCurrentState($order, $id_order_state, $id_employee = 0)
    {
        if (empty($id_order_state) || empty($order) || !\Validate::isLoadedObject($order)) {
            return false;
        }
        $order_state = $order->getCurrentOrderState();
        if ((int) $id_order_state === (int) $order_state->id) {
            return false;
        }
        $history = new \OrderHistory();
        $history->id_order = (int) $order->id;
        $history->id_employee = (int) $id_employee;
        $use_existings_payment = !$order->hasInvoice();
        $history->changeIdOrderState((int) $id_order_state, $order, $use_existings_payment);
        $res = \Db::getInstance()->getRow('
            SELECT `invoice_number`, `invoice_date`, `delivery_number`, `delivery_date`
            FROM `' . _DB_PREFIX_ . 'orders`
            WHERE `id_order` = ' . (int) $order->id);
        $order->invoice_date = $res['invoice_date'];
        $order->invoice_number = $res['invoice_number'];
        $order->delivery_date = $res['delivery_date'];
        $order->delivery_number = $res['delivery_number'];
        $order->update();

        $history->addWithemail();
    }

    /**
     * Get Prestashop id_order by id_cart
     *
     * @param int $id_cart
     *
     * @return int
     */
    public static function getPrestaIdOrderByIdCart($id_cart)
    {
        if (empty($id_cart)) {
            return false;
        }
        $query = new \DbQuery();
        $query->select('id_order')
            ->from('orders')
            ->where("id_cart = '" . pSQL($id_cart) . "'");

        return \Db::getInstance()->getValue($query);
    }

    /**
     * Returns a map of logical transaction statuses to PrestaShop order_state IDs.
     *
     * @param $preferredStatus
     *
     * @return array
     */
    public static function getOrderStateMap($preferredStatus)
    {
        return [
            'failed' => (int) \Configuration::get('PS_OS_ERROR'),
            'successful' => (int) $preferredStatus,
            'pending' => (int) Config::getFromDatabase('app.order.states.awaiting_payment.field'),
            'onBackorderNotPaid' => (int) \Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'),
            'onBackorderPaid' => (int) \Configuration::get('PS_OS_OUTOFSTOCK_PAID'),
            'preauthorization' => (int) Config::getFromDatabase('app.order.states.preauthorization.field'),
            ];
    }

    /**
     * Returns the preferred order status from context, configuration or database.
     *
     * @param $fromContext
     * @param $preferredStatusKey
     *
     * @return int
     */
    public static function getPreferredOrderStatus(bool $fromContext = true, ?string $preferredStatusKey = null): int
    {
        if ($fromContext) {
            if (
                isset(\Context::getContext()->cart)
                && isset(\Context::getContext()->cart->id_shop)
                && \Shop::isFeatureActive()
            ) {
                $queryVivaConfig = new \DbQuery();
                $queryVivaConfig->select('value')
                    ->from('configuration')
                    ->where('id_shop = ' . (int) \Context::getContext()->cart->id_shop)
                    ->where('name = "' . pSQL(Config::get('app.form.fields.preferred_status')) . '"');
                $preferredOrderStatus = \Db::getInstance()->getValue($queryVivaConfig);

                if (!empty($preferredOrderStatus)) {
                    return (int) \Configuration::get($preferredOrderStatus);
                }
            }
        }

        if (
            !empty($preferredStatusKey)
        ) {
            return (int) \Configuration::get($preferredStatusKey);
        }

        return (int) \Configuration::get(Config::getFromDatabase('app.form.fields.preferred_status'));
    }
}
