<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 * @package   cdc_googletagmanager
 */

if (!defined('_PS_VERSION_')) { exit; }

if (!defined('_CDCGTM_DIR_'))
    define('_CDCGTM_DIR_', dirname(__FILE__).'/..');

include_once(_CDCGTM_DIR_.'/classes/AbstractDataLayerObject.php');

include_once(_CDCGTM_DIR_.'/classes/gtm/Ecommerce.php');
include_once(_CDCGTM_DIR_.'/classes/gtm/Refund.php');
include_once(_CDCGTM_DIR_.'/classes/gtm/GoogleTagParams.php');
include_once(_CDCGTM_DIR_.'/classes/gtm/DataLayerItem.php');

/**
 * Represent GTM Datalayer
 */
class Gtm_DataLayer extends AbstractDataLayerObject
{
    private $gtm;

    public $event;
    public $eventCallback;
    public $eventTimeout;
    public $pageCategory;
    public $search_term;
    public $ecommerce;

    public $google_tag_params;
    public $customer;

    public function __construct($gtm) {
        $this->gtm = $gtm;
        $context = Context::getContext();
        $currency = !empty($context->currency->iso_code) ? $context->currency->iso_code : null;

        if (!$this->gtm->remarketing_enable) {
            unset($this->google_tag_params);
        }

        $this->ecommerce = new Gtm_Ecommerce($currency);

        // default event
        $this->event = $gtm->controller;
    }


    /**
     * @param Product|ProductCore $product
     * @param array|null $list
     */
    public function addItem($product, $list = null) {
        try {
            $dataLayerProduct = new Gtm_DataLayerItem($this->gtm, $product, $list);
            $dataLayerProduct->removeNull();

            if(empty($this->ecommerce->items)) {
                $this->ecommerce->items = array();
            }
            $this->ecommerce->items[] = $dataLayerProduct;
        } catch (Exception $e) {
            // error when adding product, skip it
        }
    }


    /**
     * Return JSON representation
     * of this datalayer, for the webpage
     *
     * @param false $debug
     * @return string Json datalayer
     */
    public function toJson($debug = false) {
        $dataLayerJs = "";
        $this->removeNull();

        if(count((array)$this)) {
            $eventCallback = false;
            if(!empty($this->eventCallback)) {
                // remove double quotes before encoding
                $this->eventCallback = str_replace('"', '@@', $this->eventCallback);
                // add function delimiter
                $this->eventCallback = "!##".$this->eventCallback."##!";
                $eventCallback = true;
                if(empty($this->event)) {
                    // create random event to fire callback
                    $this->event = "eventForCallback";
                }
                if(empty($this->eventTimeout)) {
                    $this->eventTimeout = 2000;
                }
            } else {
                // if no callback, remove it from datalayer to avoid GTM errors
                unset($this->eventCallback);
                unset($this->eventTimeout);
            }

            // encode datalayer
            $json_options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
            if($debug) {
                $json_options = $json_options | JSON_PRETTY_PRINT;
            }
            $dataLayerJs = json_encode($this, $json_options);

            if($eventCallback) {
                // remove double quotes
                $dataLayerJs = str_replace('##!"','', str_replace('"!##','', $dataLayerJs));
                // add double quote back
                $dataLayerJs = str_replace('@@', '"', $dataLayerJs);
            }
        }

        return $dataLayerJs;
    }


    /**
     * Add a list of products to datalayer
     * @param $products : product list
     * @param array $list : information about the page list
     */
    public function addProductList($products, $list = null) {
        $max_products = $this->gtm->max_products_datalayer;
        $position = 1;
        if(is_array($products) && !empty($products)) {
            foreach ($products as $p) {
                if(is_array($list)) {
                    $list['index'] = $position++;
                }

                $this->addItem($p, $list);

                // stop loop when max_products_datalayer is reached
                if($max_products && $position > $max_products) {
                    break;
                }
            }
        }

        if(!is_null($list)) {
            $this->ecommerce->item_list_id = isset($list['id']) ? $list['id'] : null;
            $this->ecommerce->item_list_name = isset($list['name']) ? $list['name'] : null;
        }
    }

    public function addCategory($id_category, $products) {
        $list = array(
            'name' => $this->gtm->getCategoryName($id_category),
            'id' => 'cat_'.$id_category
        );

        $this->addProductList($products, $list);
    }


    /**
     * Set subtotals to the datalayer from a cart
     * @param $cart
     */
    public function setSubtotalsFromCart($cart) {
        if(Validate::isLoadedObject($cart)) {
            $this->ecommerce->total_tax_exc = (float) round($cart->getOrderTotal(false), _CDCGTM_PRICE_DECIMAL_);
            $this->ecommerce->total_tax_inc = (float) round($cart->getOrderTotal(), _CDCGTM_PRICE_DECIMAL_);
            $this->ecommerce->value = $this->gtm->main_price_with_tax ? $this->ecommerce->total_tax_inc : $this->ecommerce->total_tax_exc;
            $this->ecommerce->tax = (float) round($cart->getOrderTotal() - $cart->getOrderTotal(false), _CDCGTM_PRICE_DECIMAL_);
            $this->ecommerce->products = (float) round($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS), _CDCGTM_PRICE_DECIMAL_);
            $this->ecommerce->products_tax_exc = (float) round($cart->getOrderTotal(false, Cart::ONLY_PRODUCTS), _CDCGTM_PRICE_DECIMAL_);
            $this->ecommerce->shipping = (float) round($cart->getOrderTotal(true, Cart::ONLY_SHIPPING), _CDCGTM_PRICE_DECIMAL_);
            $this->ecommerce->shipping_tax_exc = (float) round($cart->getOrderTotal(false, Cart::ONLY_SHIPPING), _CDCGTM_PRICE_DECIMAL_);
            $this->ecommerce->discounts = (float) round($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS), _CDCGTM_PRICE_DECIMAL_);
            $this->ecommerce->discounts_tax_exc = (float) round($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS), _CDCGTM_PRICE_DECIMAL_);
        }
    }

    /**
     * Add subtotals to the datalayer from an order
     * @param $order
     */
    public function addSubtotalsFromOrder($ecommerceObj, $order) {
        $revenue = (float) round($this->gtm->main_price_with_tax ? $order->total_paid_tax_incl : $order->total_paid_tax_excl, _CDCGTM_PRICE_DECIMAL_); // total transaction inc tax and shipping
        $total_tax_exc = (float) round($order->total_paid_tax_excl, _CDCGTM_PRICE_DECIMAL_);
        $total_tax_inc = (float) round($order->total_paid_tax_incl, _CDCGTM_PRICE_DECIMAL_); // total transaction inc tax and shipping
        $tax = (float) round($order->total_paid_tax_incl - $order->total_paid_tax_excl, _CDCGTM_PRICE_DECIMAL_);
        $products = (float) round($order->total_products_wt, _CDCGTM_PRICE_DECIMAL_);
        $products_tax_exc = (float) round($order->total_products, _CDCGTM_PRICE_DECIMAL_);
        $shipping = (float) round($order->total_shipping_tax_incl, _CDCGTM_PRICE_DECIMAL_);
        $shipping_tax_exc = (float) round($order->total_shipping_tax_excl, _CDCGTM_PRICE_DECIMAL_);
        $discounts = (float) round($order->total_discounts_tax_incl, _CDCGTM_PRICE_DECIMAL_);
        $discounts_tax_exc = (float) round($order->total_discounts_tax_excl, _CDCGTM_PRICE_DECIMAL_);

        // add infos to Ecommerce Object
        $ecommerceObj->value = round(empty($ecommerceObj->value) ? $revenue : $ecommerceObj->value + $revenue, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->total_tax_exc = round(empty($ecommerceObj->total_tax_exc) ? $total_tax_exc : $ecommerceObj->total_tax_exc + $total_tax_exc, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->total_tax_inc = round(empty($ecommerceObj->total_tax_inc) ? $total_tax_inc : $ecommerceObj->total_tax_inc + $total_tax_inc, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->tax = round(empty($ecommerceObj->tax) ? $tax : $ecommerceObj->tax + $tax, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->products = round(empty($ecommerceObj->products) ? $products : $ecommerceObj->products + $products, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->products_tax_exc = round(empty($ecommerceObj->products_tax_exc) ? $products_tax_exc : $ecommerceObj->products_tax_exc + $products_tax_exc, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->shipping = round(empty($ecommerceObj->shipping) ? $shipping : $ecommerceObj->shipping + $shipping, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->shipping_tax_exc = round(empty($ecommerceObj->shipping_tax_exc) ? $shipping_tax_exc : $ecommerceObj->shipping_tax_exc + $shipping_tax_exc, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->discounts = round(empty($ecommerceObj->discounts) ? $discounts : $ecommerceObj->discounts + $discounts, _CDCGTM_PRICE_DECIMAL_);
        $ecommerceObj->discounts_tax_exc = round(empty($ecommerceObj->discounts_tax_exc) ? $discounts_tax_exc : $ecommerceObj->discounts_tax_exc + $discounts_tax_exc, _CDCGTM_PRICE_DECIMAL_);

        // add discount code - only one
        if(empty($ecommerceObj->coupon)) {
            $this->addCartRulesToEcommerceObject($order->getCartRules(), $ecommerceObj);
        }
    }

    /**
     * Add cart rules to the datalayer from cart
     * @param $cart
     */
    public function getCartRulesFromCart($cart) {
        if(Validate::isLoadedObject($cart)) {
            $this->addCartRulesToEcommerceObject($cart->getCartRules(), $this->ecommerce);
        }
    }

    /**
     * Given a cartRules array, add it to the Datalayer Ecommerce object
     * @param array $cartRules
     * @param object $ecommerce
     */
    protected function addCartRulesToEcommerceObject($cartRules, $ecommerce) {
        if(is_array($cartRules) && count($cartRules) > 0) {
            $ecommerce->coupon = $cartRules[0]['name'];
            $ecommerce->coupon_id = $cartRules[0]['id_cart_rule'];
            if(isset($cartRules[0]['code'])) {
                $ecommerce->coupon_code = $cartRules[0]['code'];
            } else {
                $cartRule = new CartRule($cartRules[0]['id_cart_rule']);
                if(Validate::isLoadedObject($cartRule)) {
                    $ecommerce->coupon_code = $cartRule->code;
                }
            }
        }
    }


    /**
     * Generate datalayer for order confirmation
     * @param $id_cart
     * @param false $force_resend
     */
    public function generateOrderConfirmationFromCart($id_cart, $force_resend = false) {
        $orders = PrestashopUtils::getOrdersByCartId($id_cart);

        // create action field (ecommerce infos)
        $ecommerce = new stdClass();
        $transactionId = null;

        // store last handled order
        $lastOrder = null;

        $orders_list = array();
        foreach ($orders as $id_order) {
            $id_order = $id_order['id_order'];
            $order = new Order($id_order);

            if(Validate::isLoadedObject($order)) {
                // test if order is not in error and not already sent ?
                if($order->current_state != Configuration::get('PS_OS_ERROR')
                    && ($force_resend || !CdcGtmOrderLog::isSent($id_order, $order->id_shop))) {
                    $lastOrder = $order;

                    $orders_list[] = $id_order;
                    // create log
                    $this->gtm->createGtmOrderLog($id_order, $order->id_shop);
                    $this->gtm->orderValidationDetails[] = array('id_order' => $id_order);

                    // build id if multi order
                    $order_id_field = $this->gtm->order_id_field;
                    $current_ref_order = $order->$order_id_field;
                    if(empty($transactionId)) {
                        // reference empty, set reference
                        $transactionId = (string) $current_ref_order;
                    } else {
                        // reference not empty, add it if not yet added
                        $array_ids = explode(',', $transactionId);
                        if(!in_array($current_ref_order, $array_ids)) {
                            $array_ids[] = $current_ref_order;
                            $transactionId = implode(',', $array_ids);
                        }
                    }

                    $this->addSubtotalsFromOrder($ecommerce, $order);

                    // products
                    $products = $order->getProducts();
                    $this->addProductList($products);
                }
            }
        }

        // add customer informations
        if($this->gtm->customer_informations != 'never' && Validate::isLoadedObject($lastOrder)) {
            $this->customer = $this->getCustomerDataFromOrder($lastOrder);
        }

        // orders found, add infos to DL
        if(count($orders_list) > 0) {
            $ecommerce->transaction_id = $transactionId;

            // add informations to main datalayer
            $this->ecommerce->mergeObject($ecommerce);
            $this->event = "purchase";

            $callback_name = $force_resend ? 'orderresend' : 'orderconfirmation';
            $this->eventCallback = $this->gtm->getCallback($callback_name, array(
                'orders' => $orders_list,
                'id_shop' => $this->gtm->shop_id
            ));
        }

        // generate remarketing infos
        if ($this->gtm->remarketing_enable) {
            $this->generateRemarketingParametersForOrderConfirmation($id_cart);
        }
    }

    /**
     * Add remarketing parameters for order confirmation
     * @param $id_cart
     */
    protected function generateRemarketingParametersForOrderConfirmation($id_cart) {
        $this->google_tag_params = new Gtm_GoogleTagParams();
        $this->google_tag_params->ecomm_prodid = $this->gtm->remarketingGetProductListIdsFromCart($id_cart);
        $this->google_tag_params->ecomm_totalvalue = (float) round($this->gtm->getTotalProductPriceFromCart($id_cart), _CDCGTM_PRICE_DECIMAL_);
        $this->google_tag_params->ecomm_totalvalue_tax_exc = (float) round($this->gtm->getTotalProductPriceFromCart($id_cart, false), _CDCGTM_PRICE_DECIMAL_);
        $this->google_tag_params->ecomm_pagetype = 'purchase';

        // add remarketing items from GA4 datalayer
        if(!empty($this->ecommerce->items)) {
            $items = [];
            foreach ($this->ecommerce->items as $item) {
                $items[] = (object) [
                    'id' => $item->item_id,
                    'price' => $item->price,
                    'quantity' => $item->quantity
                ];
            }
            $this->google_tag_params->items = $items;
        }
    }

    /**
     * Return customer data from order
     * @param $order
     */
    protected function getCustomerDataFromOrder($order) {
        return $this->getCustomerData($order->id_customer, $order->id_address_delivery, $order->reference);
    }

    /**
     * Get customer data
     * @param $id_customer
     * @param null $id_address_delivery
     * @param null $exclude_order_reference
     * @return stdClass
     */
    public function getCustomerData($id_customer, $id_address_delivery = null, $exclude_order_reference = null) {
        $customerData = new stdClass();

        $customer = new Customer($id_customer);
        if(Validate::isLoadedObject($customer)) {
            // keep email for retro-compatibility
            $customerData->email = $customer->email;
            // email_address is the new name for user_data
            $customerData->email_address = $customer->email;
        }

        if(!empty($id_address_delivery)) {
            $address = new Address($id_address_delivery);
            if (Validate::isLoadedObject($address)) {
                $phone = !empty($address->phone_mobile) ? $address->phone_mobile : $address->phone;
                if (!empty($phone)) {
                    $customerData->phone_number = $phone;
                }

                $addressData = new stdClass();
                $addressData->first_name = $address->firstname;
                $addressData->last_name = $address->lastname;
                $addressData->street = $address->address1 . (!empty($address->address2) ? ' ' . $address->address2 : '');
                $addressData->city = $address->city;
                //$addressData->region = '';
                $addressData->postal_code = $address->postcode;

                $country = new Country($address->id_country);
                if (Validate::isLoadedObject($country)) {
                    $addressData->country = $country->iso_code;
                }

                $customerData->address = $addressData;
            }
        }

        $past_orders = PrestashopUtils::countCustomerOtherOrders($id_customer, $exclude_order_reference);
        $customerData->new = $past_orders > 0 ? 0 : 1;
        $customerData->past_orders = $past_orders;

        return $customerData;
    }

    /**
     * @param $order_id
     * @param all|array|null $order_lines
     */
    public function generateRefund($order_id, $order_lines = null) {
        $order = new Order($order_id);
        if(!Validate::isLoadedObject($order)) {
            return false;
        }

        $ecommerce = new stdClass();
        $ecommerce->transaction_id = (string) $order->id;

        // add products in case of partial refund
        if($order_lines !== "all" && is_array($order_lines) && count($order_lines)) {
            $ecommerce->value = 0;
            $ecommerce->tax = 0;
            //$ecommerce->shipping = 0;
            foreach ($order_lines as $order_line) {
                $order_line_detail = new OrderDetail($order_line);
                if(Validate::isLoadedObject($order_line_detail)) {
                    // add order line information
                    $order_line_detail->product_quantity = $order_line_detail->product_quantity_refunded;

                    // add information from base product
                    $product = new Product($order_line_detail->product_id);
                    if(Validate::isLoadedObject($product)) {
                        $product->product_quantity = $order_line_detail->product_quantity;
                        $product->price = $order_line_detail->product_price;
                        $product->unit_price_tax_incl = $order_line_detail->unit_price_tax_incl;
                        $product->unit_price_tax_excl = $order_line_detail->unit_price_tax_excl;
                        $this->addItem($product);
                    }

                    // update subtotals
                    $ecommerce->value += round($order_line_detail->product_quantity * ($this->gtm->main_price_with_tax ? $order_line_detail->unit_price_tax_incl: $order_line_detail->unit_price_tax_excl), _CDCGTM_PRICE_DECIMAL_);
                    $ecommerce->tax += round($order_line_detail->product_quantity * ($order_line_detail->unit_price_tax_incl - $order_line_detail->unit_price_tax_excl), _CDCGTM_PRICE_DECIMAL_);
                }
            }

        } else {
            $this->addSubtotalsFromOrder($ecommerce, $order);
        }

        $this->eventCallback = $this->gtm->getCallback("orderrefund", array('order' => $order->id, 'id_shop' => $order->id_shop));

        $this->ecommerce->mergeObject($ecommerce);
        $this->event = "refund";
    }

}
