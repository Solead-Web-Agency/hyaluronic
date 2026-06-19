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
require_once(dirname(__FILE__) . '/AmazonFunction.php');
require_once(dirname(__FILE__) . '/../classes/amazon.order.class.php');
if (!defined('_PS_VERSION_')) { exit; }

class AmazonListOrder extends AmazonFunction
{
    public static $errors = array();
    public static $warnings = array();
    public static $orders = array();
    public static $ordersRaw = array();

    protected $logChannel = AmazonLogger::CHANNEL_ORDER_LISTING;
    private $amzEmployeeID;
    private $demo;

    public function __construct()
    {
        parent::__construct();

        $this->amazon_features = $this->getAmazonFeatures();
        $this->amzEmployeeID = Amazon::getAmzEmployeeId();
        $this->demo = AmazonTools::getValue('demo_mode');
    }

    public static function jsonDisplayExit($demoMode = false)
    {
        $buffer = ob_get_clean();
        $output = $buffer ?: '';

        echo json_encode(array(
            'orders' => AmazonListOrder::$orders,
            'count' => count(AmazonListOrder::$orders),
            'error' => (bool)count(AmazonListOrder::$errors),
            'errors' => AmazonListOrder::$errors,
            'warning' => count(AmazonListOrder::$warnings) > 0,
            'warnings' => AmazonListOrder::$warnings,
            'generated_data' => $demoMode ? AmazonListOrder::$ordersRaw : array(),
            'output' => $output,
        ));
    }

    public function dispatch()
    {
        register_shutdown_function(array('AmazonListOrder', 'jsonDisplayExit'), $this->demo);

        /*if (!$this->functionAuthorization()) {
            die('Wrong Token');
        }*/

        $spConnector = $this->initSpConnector();
        if ($spConnector && $spConnector->isAuthenticated()) {
            $this->displayList();
        } else {
            self::$errors[] = 'Missing region!';
        }
    }

    protected function getOrderList()
    {
        return $this->demo ? $this->getOrderListDemo() : $this->getOrderListApi();
    }

    private function getOrderListApi()
    {
        $spApiOrders = new AmazonSPAPIOrders(
            $this->spConnector,
            array(),
            $this->logger,
            $this->moduleFeatures->dev_mode
        );

        // Today - 5 minutes de temps de latence afin d'�viter les erreur de synchro dus a l'heure systeme
        // Cannot make it work with ISO8601 (+00:00 timezone format), use Zulu (Z) and gmdate() instead.
        $dateFormat = 'Y-m-d\TH:i:s\Z';
        $date1 = str_replace('-', '/', AmazonTools::getValue('datepickerFrom'));
        $date2 = str_replace('-', '/', AmazonTools::getValue('datepickerTo'));
        if (date('Ymd', strtotime($date2)) >= date('Ymd') || empty($date2)) {
            $date1 = gmdate($dateFormat, strtotime($date1));
            $date2 = gmdate($dateFormat, strtotime('now - 15 min'));
        } else {
            $date1 = gmdate($dateFormat, strtotime($date1 . ' 00:00:00'));
            $date2 = gmdate($dateFormat, strtotime($date2 . ' 23:59:59'));
        }

        return $spApiOrders->apiListAll(
            $date1,
            $date2,
            null,
            null,
            $this->resolveInputOrderStatus(AmazonTools::getValue('statuses')),
            $this->resolveInputFulfillmentChannel(AmazonTools::getValue('channel'))
        );
    }

    private function getOrderListDemo()
    {
        return $this->fakeOrderList()->Orders;
    }

    public function displayList()
    {
        $orders = $this->getOrderList();
        foreach ($orders as $order) {
            if ($order instanceof AmazonSPDefOrder) {
                if ($order->IsReplacementOrder) {
                    AmazonListOrder::$warnings[] = sprintf($this->l("Order ID (%s) has been replaced by ID (%s)"), $order->AmazonOrderId, $order->ReplacedOrderId);
                    continue;
                }
                $this->displayOrder($order);
            } elseif ($order instanceof AmazonSellerPartnerResponse) {
                $this->logger->error('Error during fetching orders', array('response' => print_r($order, true)));
                $warningMessage = sprintf($this->l('Error during fetching orders: %s'), $order->getErrorMsg());
                if ($order->getUpstream()) {
                    $warningMessage = sprintf($this->l('Error during fetching orders: %s'), $order->getUpstream()->getMessage());
                }
                AmazonListOrder::$warnings[] = $warningMessage;
            } else {
                $this->logger->error('Error during fetching orders', array('response' => print_r($order, true)));
                AmazonListOrder::$warnings[] = 'Unknown error during fetching orders';
            }
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        static $lang = null;

        if (!$lang)
            $lang = Amazon::availableLang(Language::getIsoById($this->id_lang));

        return (parent::l($string, basename(__FILE__, '.php'), $lang));
    }

    private function resolveInputOrderStatus($inputStatus)
    {
        $availableStatuses = array(
            AmazonSPDefOrder::STATUS_PENDING,
            AmazonSPDefOrder::STATUS_UNSHIPPED,
            AmazonSPDefOrder::STATUS_PARTIALLY_SHIPPED,
            AmazonSPDefOrder::STATUS_SHIPPED,
            AmazonSPDefOrder::STATUS_CANCELED,
        );
        if ($inputStatus == 'All' || !in_array($inputStatus, $availableStatuses)) {
            return $availableStatuses;
        }

        return $inputStatus;
    }

    private function resolveInputFulfillmentChannel($inputChannel)
    {
        $availableChannels = array(
            AmazonSPDefOrder::FULFILLMENT_CHANNEL_AMAZON,
            AmazonSPDefOrder::FULFILLMENT_CHANNEL_SELLER,
        );
        if ($inputChannel == 'All' || !in_array($inputChannel, $availableChannels)) {
            return $availableChannels;
        }

        return array($inputChannel);
    }

    /**
     * @param AmazonSPDefOrder $order
     * @throws PrestaShopException
     */
    private function displayOrder($order)
    {
        $amzOrderId = $order->AmazonOrderId;
        $psId = AmazonOrder::checkByMpId($amzOrderId);
        self::$ordersRaw[$amzOrderId] = $order;
        self::$orders[$amzOrderId] = array(
            'id' => $amzOrderId,
            'flag_src' => "{$this->images}geo_flags/$order->MarketplaceId.gif",
            // May cause date mismatch.
            // If pass Amazon ISO 8601 datetime, PS throws error because of failure of Validate::isDate($date)
            // If parse date before, the date() function is manipulated by timezone already ---> wrong datetime
            // The only way is to write our own displayDate function, which is expensive. Let's keep it at this time
            'date' => AmazonTools::displayDate(date('Y-m-d H:i:s', strtotime($order->PurchaseDate)), $this->id_lang),
            'imported' => (bool)$psId,
            'ps_id' => $psId,
            'ps_url' => "?tab=AdminOrders&id_order=$psId&vieworder&token=" .
                AmazonTools::getAdminToken('AdminOrders' . (int)Tab::getIdFromClassName('AdminOrders') . $this->amzEmployeeID),
            'status' => $order->OrderStatus,
            'pending' => $order->OrderStatus == AmazonOrder::ORDER_PENDING,
            'canceled' => $order->OrderStatus == AmazonOrder::ORDER_CANCELED,
            'customer' => htmlspecialchars($order->ShippingAddress->Name),  // Empty on sandbox. todo: Construct name by lang
            'shipping' => $order->ShipServiceLevel,
            'is_prime' => $order->IsPrime,
            'fulfillment' => $order->FulfillmentChannel,
            'quantity' => $order->NumberOfItemsUnshipped + $order->NumberOfItemsShipped,
            'total' => AmazonTools::displayPrice(
                $order->OrderTotal->getAmount(),
                Currency::getIdByIsoCode($order->OrderTotal->getCurrencyCode()),
                false,
                $this->context
            ),
        );
    }

    private function fakeOrderList()
    {
        $order = [
            "AmazonOrderId" => sprintf('171-5%s-1%s', date('ymd'), date('His')),
            "SellerOrderId" => '1855',
            // "OrderStatus" => AmazonOrder::ORDER_PENDING,             //Pending
            "OrderStatus" => AmazonOrder::ORDER_UNSHIPPED,              //Unshipped
            // "OrderStatus" => AmazonOrder::ORDER_SHIPPED,             //Unshipped
            // "OrderStatus" => AmazonOrder::ORDER_PARTIALLYSHIPPED,    //PartiallyShipped
            // "OrderStatus" => AmazonOrder::ORDER_CANCELED,
            "PurchaseDate" => date('Y-m-dTH:i:s.287Z'),
            "LastUpdateDate" => '2022-08-23T00:30:33.287Z',
            "OrderType" => 'StandardOrder',
            "OrderTotal" => [
                "CurrencyCode" => 'EUR',
                // "Amount" => 0,
                "Amount" => 160,
            ],
            "ShipServiceLevel" => 'Std US D2D Dom',
            "FulfillmentChannel" => 'MFN',
            // "FulfillmentChannel" => 'AFN'
            "ShipmentServiceLevelCategory" => 'Standard',
            "SalesChannel" => 'Amazon.uk',
            "OrderChannel" => '',
            "NumberOfItemsUnshipped" => '1', //Unshipped
            "NumberOfItemsShipped" => '0',
            // "NumberOfItemsUnshipped" => '5', //Unshipped
            // "NumberOfItemsShipped" => '0',
            // "NumberOfItemsUnshipped" => '0', //Shipped
            // "NumberOfItemsShipped" => '5',
            // "MarketPlaceId" => 'A1F83G8C2ARO7P',
            "MarketplaceId" => 'ATVPDKIKX0DER',
            // "MarketPlaceId" => 'A13V1IB3VIYZZH',
            // Cast boolean to int to transfer via requests
            "IsPrime" => 1,
            "IsPremiumOrder" => 1,
            "IsBusinessOrder" => 1,
            'IsReplacementOrder' => 0,
            'ReplacedOrderId' => '',
            'IsAccessPointOrder' => 0,
            'isDemo' => 1,
            "BuyerInfo" => [
                "BuyerEmail" => 'tran@common-services.com',
                "BuyerName" => 'Someone In The World',
            ],
            "EarliestShipDate" => '2020-12-22 08:00:00',
            "LatestShipDate" => '2020-12-22 07:59:59',
            "EarliestDeliveryDate" => '2020-12-22 08:00:00',
            "LatestDeliveryDate" => '2020-12-22 06:59:59',
            "ShippingAddress" => [
                "Name" => 'Someone In The World',
//                "AddressLine1" => '262 MORRISON STREET',
//                "AddressLine2" => 'Flat #3',
//                "City" => 'EDINBURGH',
//                "PostalCode" => 'EH3 8DT',

//                "AddressLine1" => 'West Coast Crescent, West Bay Condominium, Tower 52, #07-01',
//                "AddressLine2" => 'Flat #3',
//                "City" => '',
//                "StateOrRegion" => '',
//                "PostalCode" => '128036',
//                "CountryCode" => 'SG',


//                "AddressLine1" => '215 impasse des graves',
//                "AddressLine2" => 'Flat #3',
//                "City" => 'Guaynabo',
//                "StateOrRegion" => 'Puerto Rico',
//                "PostalCode" => '00970-7891',
//                "Phone" => '7872423919',
//                "CountryCode" => 'US',

                "AddressLine1" => 'karosseriebau best',
                "AddressLine2" => 'An der Fliede 5',
                "City" => 'Flieden',
                "StateOrRegion" => 'Hessen',
                "PostalCode" => '36103',
                "Phone" => '015112341234',
                "CountryCode" => 'DE',
                "AddressType" => 'Commercial',

//                "AddressLine1" => '横浜市港北区師岡町1128-13',
//                "AddressLine2" => '',
//                "City" => '',
//                "StateOrRegion" => '神奈川県',
//                "PostalCode" => '222-0002',
//                "CountryCode" => 'JP',
//                "CountryCode" => 'FR',
//                "Phone" => '08048796708',
                "Instructions" => '',
            ],
            "DefaultShipFromLocationAddress" => [
                "City" => 'Hopfgarten im Brixental',
                "PostalCode" => '6361',
                "StateOrRegion" => 'Tirol',
                "Phone" => '07121/123456',
                "CountryCode" => 'AT',
                "Name" => 'Bernhard Test',
                "AddressLine1" => 'Meierhofgasse 33',
            ],
            "BuyerTaxInfo" => [
                "TaxClassifications" => [
                    "TaxClassification" => [
                        ["Name" => "CPF", "Value" => "33234347816"],
                        ["Name" => "CPF2", "Value" => "233234347816"]
                    ]
                ]
            ],
//            "TaxRegistrationDetails" => [
//                "member" => [
//                    "taxRegistrationId" => "ATU59968048",
//                    "taxRegistrationAuthority" => [
//                        "country" => "AT",
//                    ],
//                    "taxRegistrationType" => "VAT"
//                ]
//            ]
        ];

        return new AmazonSPDefOrdersList([$order], null, null, null);
    }
}

$amazonOrders = new AmazonListOrder();
$amazonOrders->dispatch();
