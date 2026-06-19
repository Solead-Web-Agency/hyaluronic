<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ZendeskAddon\Controller;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ZendeskFrontController extends \ModuleFrontController
{
    /** @var \Zendesk */
    public $module;

    public function checkAuthorization()
    {
        // Authorization
        $token_string = false;

        if (!$token_string && isset($_SERVER['Authorization'])) {
            $token_string = $_SERVER['Authorization'];
        }

        if (!$token_string && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token_string = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!$token_string && isset($_SERVER['HTTP_X_TOTAUTH'])) {
            $token_string = $_SERVER['HTTP_X_TOTAUTH'];
        }

        if (!$token_string && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $token_string = $headers['Authorization'];
            }
        }

        if (!$token_string || empty($token_string)) {
            $this->showError('Unable to extract authorization header from request');
        }

        $token_string = stripslashes($token_string);

        $secure_key = null;
        $matches = [];

        if (preg_match('/Token token="([A-Z0-9a-z]+)"/', $token_string, $matches)) {
            $secure_key = $matches[1];
        }

        if (empty($secure_key) || $secure_key !== \Configuration::getGlobalValue('ZENDESK_CONNECTOR_KEY')) {
            $this->showError('Secure key is not valid');
        }
    }

    public function showError($error = '')
    {
        $json = ['success' => false, 'message' => $error];

        exit(json_encode($json));
    }

    /**
     * Get order detail from one order
     *
     * @param $order
     *
     * @return mixed $order_detail
     */
    public function getOrderDetail($order, &$phone = '')
    {
        $context = \Context::getContext();

        $order_state = new \OrderState($order->getCurrentState(), (int) $context->language->id);
        $customer = new \Customer((int) $order->id_customer);
        $carrier = new \Carrier((int) $order->id_carrier);
        $orderCarrier = new \OrderCarrier($order->getIdOrderCarrier());
        $trackingNumber = $orderCarrier->tracking_number;
        $currency = new \Currency((int) $order->id_currency, (int) $context->language->id);

        $order_details = [
            'id' => (int) $order->id,
            'reference' => $order->reference,
            'state_color' => $order_state->color,
            'status' => $order_state->name,
            'created' => $order->date_add,
            'updated' => $order->date_upd,
            'customer' => [
                'name' => $customer->firstname . ' ' . $customer->lastname,
                'email' => $customer->email,
                'guest' => (bool) $customer->is_guest,
            ],
            'carrier' => $carrier->name,
            'trackingnumber' => $trackingNumber,
            'trackingurl' => str_replace('@', $trackingNumber, $carrier->url),
            'total' => \Tools::ps_round($order->total_paid_tax_incl, 2) . ' ' . $currency->getSign(),
            'products' => $this->getProductDetails($order),
            'admin_url' => \Context::getContext()->link->getAdminLink('AdminOrders', false),
            'address_invoice' => new \Address((int) $order->id_address_invoice),
            'address_delivery' => new \Address((int) $order->id_address_delivery),
        ];

        $adress_invoice = $order_details['address_invoice'];
        $address_delivery = $order_details['address_delivery'];
        if ($phone == '') {
            if (!empty($adress_invoice->phone) || !empty($adress_invoice->phone_mobile)) {
                $phone = !empty($adress_invoice->phone_mobile) ? $adress_invoice->phone_mobile : $adress_invoice->phone;
            }
            if (!empty($address_delivery->phone) || !empty($address_delivery->phone_mobile)) {
                $phone = !empty($address_delivery->phone_mobile) ? $address_delivery->phone_mobile : $address_delivery->phone;
            }
        }

        // Shop
        $shop = new \Shop((int) $order->id_shop);
        $order_details['shop_name'] = $shop->name;

        // State
        $order_details['state_name'] = $order_state->name;

        return $order_details;
    }

    /**
     *  Filter product details to send only necessary data to ZenDesk
     *  Prevent too much modules data
     *  Only needed attributes to have light response (only necessary)
     *
     * @param mixed $order
     *
     * @return array $productsIn
     */
    private function getProductDetails($order)
    {
        $products = $order->getProducts();
        $productsIn = [];
        $context = \Context::getContext();
        $currency = new \Currency((int) $order->id_currency, (int) $context->language->id);
        foreach ($products as $aProduct => $inProduct) {
            $arrayProd = [];
            $arrayProd['product_quantity'] = $inProduct['product_quantity'];
            $arrayProd['product_name'] = $inProduct['product_name'];
            $price = \Tools::convertPrice($inProduct['total_price_tax_incl'], (int) $order->id_currency, false);
            $arrayProd['product_price'] = \Tools::ps_round($price, 1) . ' ' . $currency->getSign();

            $arrayProd['url'] = \Context::getContext()->link->getProductLink(
                $inProduct, null, null, null, null, null, (int) $inProduct['product_attribute_id']
            );
            if (isset($inProduct['attribute_group_name']) && isset($inProduct['attribute_name'])) {
                $arrayProd['attribute_group_name'] = $inProduct['attribute_group_name'];
                $arrayProd['attribute_name'] = $inProduct['attribute_name'];
            }
            $productsIn[$aProduct] = $arrayProd;
        }

        return $productsIn;
    }
}
