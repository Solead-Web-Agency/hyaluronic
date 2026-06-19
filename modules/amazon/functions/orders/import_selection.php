<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
/* Is ajax/cron file */
require_once(dirname(__FILE__) . '/../env.php');
require_once(dirname(__FILE__) . '/../import.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonFunctionOrderImportSelection extends AmazonImportOrder
{
    protected $orderIds;
    protected $generatedOrders = array();

    public function __construct()
    {
        parent::__construct();

        if (!$this->functionAuthorization()) {
            die('Wrong Token');
        }

        $this->orderIds = AmazonTools::getValue('order_id');
        $this->generatedOrders = AmazonTools::getValue('generated_data');
    }

    protected function initializeImport()
    {
        register_shutdown_function(array('AmazonImportOrder', 'jsonDisplayExit'));
        $this->validation();
    }

    private function validation()
    {
        $orders_ids = $this->orderIds;
        if (!is_array($orders_ids) || !count($orders_ids)) {
            die($this->l('Nothing to import...'));
        }

        if (count($orders_ids) > 50) {
            die($this->l('Only allow the import of 50 orders at a time'));
        }
    }

    public function importSelectedOrders()
    {
        $this->initializeImport();
        $this->preCheck();

        $origin_currency = $this->context->currency->iso_code;
        $id_currency = (int)$this->context->currency->id;
        $amazonOrders = $this->getOrdersByIds();
        $this->importList($amazonOrders, $this->orderIds, $id_currency, $origin_currency);
    }

    protected function getOrdersByIds()
    {
        if ($this->amzIsDemo) {
            $result = array();
            foreach ($this->generatedOrders as $generatedOrder) {
                $result[] = new AmazonSPDefOrder($generatedOrder);
            }

            return $result;
        }

        return $this->getSpApiOrder()->apiListAllByOrderIds($this->orderIds);
    }

    protected function getOrderItemsDemo($order)
    {
        if ($order->isDemo) {
            return $this->fakeOrderItems($order);
        } else {
            return $this->getOrderItemsApi($order);
        }
    }

    private function fakeOrderItems($amazonOrderId)
    {
        $itemSkeleton = [
            "OrderItemId" => '67221586634938',
            "ASIN" => 'B01MDP1234',
            "SellerSKU" => 'CF3256-XXS-2019',
            "Title" => '2019 Reebok CF1234 -3 - XXS - Gold',
            "QuantityOrdered" => 1,
            "ItemPrice" => [
                "Amount" => 2,
                "CurrencyCode" => 'EUR'
            ],
            "ShippingPrice" => [
                "Amount" => 4,
                "CurrencyCode" => 'EUR'
            ],
            "ShippingDiscount" => [
                "Amount" => 0,
                "CurrencyCode" => 'EUR'
            ],
            "PromotionDiscount" => [
                "Amount" => 0,
                "CurrencyCode" => 'EUR'
            ],
            "ItemTax" => [
                "Amount" => 0,
                "CurrencyCode" => 'EUR'
            ],
            "ShippingTax" => [
                "Amount" => 0,
                "CurrencyCode" => 'EUR'
            ],
            "BuyerInfo" => [
                "GiftMessageText" => '',
                "BuyerCustomizedInfo" => [
                    // "CustomizedURL" => 'https://zme-caps.amazon.com/t/r9wRP12Fr07V/-I8sCVOVvRHhl0RIjm2P7sfedaBbYtAEUPbRbtA6fhY/12'
                    "CustomizedURL" => ''
                ],
                "GiftWrapPrice" => [
                    "Amount" => 0,
                    "CurrencyCode" => 'EUR',
                ],
                "GiftWrapTax" => [
                    "Amount" => 0,
                    "CurrencyCode" => 'EUR',
                ],
            ],
            "QuantityShipped" => 0,       //Unshipped
            // "QuantityShipped" => 1,     //Shipped
            "PromotionId" => '',
            "PriceDesignation" => 0,
        ];

        $nItems = array(
            array(
                'OrderItemId' => "67221586634111",
                'SellerSKU' => 'CF3251, M-2019',
                // 'SellerSKU' => 'CF3251-M-2019',
                // 'SellerSKU' => 'pencil-meta-s',
                'DeemedResellerCategory' => "IOSS",
                'Title' => '2019 Reebok CF1234 44450444123 - M - Gold - Red',
                'IossNumber' => 'IM4420001201',
                'QuantityOrdered' => 1,
                'QuantityShipped' => 0,
                // 'QuantityShipped' => 2,
                'ItemPrice' => [
                    "Amount" => 170,
                    // "Amount" => 39.99,
                    "CurrencyCode" => 'EUR'
                ],
                "ShippingPrice" => [
                    "Amount" => 10,
                    // "Amount" => 1.5,
                    "CurrencyCode" => 'EUR'
                ],
                "ShippingDiscount" => [
                    "Amount" => 0,
                    // "Amount" => 1.5,
                    "CurrencyCode" => 'EUR'
                ],
                "PromotionDiscount" => [
                    "Amount" => 20,
                    "CurrencyCode" => 'EUR'
                ],
                "ItemTax" => [
                    "Amount" => 0,
                    "CurrencyCode" => 'EUR'
                ],
                "ShippingTax" => [
                    "Amount" => 0,
                    "CurrencyCode" => 'EUR'
                ],
                "BuyerInfo" => [
                    "GiftWrapTax" => [
                        "Amount" => 0,
                        "CurrencyCode" => '',
                    ],
                ],
            ),
//            array(
//                'OrderItemId' => "67221586634112",
//                'SellerSKU' => 'CF3256-XXL1-2019',
//                // 'SellerSKU' => 'CF3256-XXS-2019',
//                // 'SellerSKU' => 'pencil-meta-sl',
//                'Title' => 'Pencil S and L',
//                "ItemPrice" => [
//                    "Amount" => 4.8,
//                    "CurrencyCode" => 'EUR'
//                ],
//                "ShippingPrice" => [
//                    "Amount" => 6.4,
//                    "CurrencyCode" => 'EUR'
//                ],
//                'QuantityOrdered' => 3,
//                'QuantityShipped' => 0,
//                // 'QuantityShipped' => 3,
//                "ShippingDiscount" => [
//                    "Amount" => 1.6,
//                    "CurrencyCode" => 'EUR'
//                ],
//                "PromotionDiscount" => [
//                    "Amount" => 0,
//                    "CurrencyCode" => 'EUR'
//                ],
//                "ItemTax" => [
//                    "Amount" => 1.6,
//                    "CurrencyCode" => 'EUR'
//                ],
//                "ShippingTax" => [
//                    "Amount" => 1.6,
//                    "CurrencyCode" => 'EUR'
//                ],
//                "BuyerInfo" => [
//                    "GiftWrapTax" => [
//                        "Amount" => 0,
//                        "CurrencyCode" => 'EUR',
//                    ],
//                ],
//            ),
//            array(
//                'OrderItemId' => "67221586634113",
//                'SellerSKU' => 'CF3251-L-2019',
//                "ItemPrice" => [
//                    "Amount" => 10,
//                    "CurrencyCode" => 'EUR'
//                ],
//                "ShippingPrice" => [
//                    "Amount" => 4,
//                    "CurrencyCode" => 'EUR'
//                ],
//                'QuantityOrdered' => 1,
//                'QuantityShipped' => 1,
//            ),
        );

        $items = [];
        foreach ($nItems as $oItem) {
            $nItem = $itemSkeleton;
            foreach ($oItem as $key => $value) {
                $nItem[$key] = $value;
            }
            $items[] = $nItem;
        }

        $result = new AmazonSPDefOrderItemsList($items, $amazonOrderId, null);

        return $result->OrderItems;
    }

    public function getOrders()
    {
        $amzOrders = array();
        $this->initSpConnector();
        $mpOrderIds = explode(',', AmazonTools::getValue('mpOrderId'));
        foreach ($this->getSpApiOrder()->apiListAllByOrderIds($mpOrderIds) as $amzOrder) {
            $amzOrders[] = $amzOrder;
        }
        echo json_encode($amzOrders);
        exit;
    }

    public function clearAllPreviousFailedOrders()
    {
        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        $tokens = AmazonTools::getValue('amazon_token');
        if (!AmazonTools::checkToken($tokens)) {
            die($this->l('Wrong Token'));
        }

        if (AmazonCronFailedOrder::removeAllCronFailedOrders()) {
            echo json_encode(array('success' => true, 'message' => 'Cleared cron failed orders'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Error while clearing cron failed orders'));
        }
    }
}

$amzOrderImportSelection = new AmazonFunctionOrderImportSelection();
if (AmazonTools::getValue('danger')) {
    $amzOrderImportSelection->getOrders();
} elseif (AmazonTools::getValue('action') == 'clear_failed_orders') {
    $amzOrderImportSelection->clearAllPreviousFailedOrders();
} else {
    $amzOrderImportSelection->importSelectedOrders();
}
