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
if (!defined('_PS_VERSION_')) { exit; }

require_once(dirname(__FILE__).'/../classes/amazon.orderhistory.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.product.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');

class AmazonMultiChannel extends AmazonOrder
{
    const AMAZON_FBA_MULTICHANNEL = 'MAFN';
    const AMAZON_FBA_AMAZON = 'AFN';
    const AMAZON_FBA_MERCHANT = 'MFN';
    const AMAZON_FBA_STATUS_SUBMITTED = 'submited';
    const AMAZON_FBA_STATUS_RECEIVED = 'received';
    const AMAZON_FBA_STATUS_INVALID = 'invalid';
    const AMAZON_FBA_STATUS_PLANNING = 'planning';
    const AMAZON_FBA_STATUS_PROCESSING = 'processing';
    const AMAZON_FBA_STATUS_CANCELLED = 'cancelled';
    const AMAZON_FBA_STATUS_COMPLETE = 'complete';
    const AMAZON_FBA_STATUS_COMPLETEPARTIALLED = 'completepartialled';
    const AMAZON_FBA_STATUS_UNFULFILLABLE = 'unfulfillable';

    public static $errors                   = array();
    public $marketPlaceChannelStatus = null;

    protected static $allowed_deliveries_for_countries_iso_codes = 
        array('CA', 'MX', 'US', 'IN', 'JP', 'CN', 'DE', 'BE', 'FR', 'IE', 'IT', 'LU', 'NL', 'PT', 'GB', 'AU', 'BE', 'BG', 'CY', 'DK', 'EE', 'FI', 'FR', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'PL', 'CZ', 'RO', 'SK', 'SI', 'SE','SA', 'PL',
              'ES', 'AE', 'BR', 'SG', 'EG'
    );

    public function __construct($id = null, $id_lang = null)
    {
        $this->context = Context::getContext();

        parent::__construct($id, $id_lang);

        AmazonContext::restore($this->context);

        // Init
        //
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $id_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');

            $group = new Group($id_group);

            if (!Validate::isLoadedObject($group)) {
                $id_group = null;
            }

            if (!$id_group || !is_numeric($id_group)) {
                $id_group = Configuration::get('PS_CUSTOMER_GROUP');
            }

            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = $id_group;
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        if ($id) {
            $this->_getMpStatus();
        }
    }

    public function getSellerFulfillmentOrderId()
    {
        return $this->id . '-' . $this->reference;
    }

    /**
     * @return void
     * @throws PrestaShopDatabaseException
     */
    private function _getMpStatus()
    {
        if ($this->amazon_order_info->is_standard_feature_available) {
            if ($this->amazon_order_info->getOrderInfo()) {
                // For compatibility
                $this->marketPlaceOrderId = $this->amazon_order_info->mp_order_id;
                $this->marketPlaceOrderStatus = $this->amazon_order_info->mp_status;
                $this->marketPlaceChannel = $this->amazon_order_info->channel;
                $this->marketPlaceChannelStatus = $this->amazon_order_info->channel_status;

                return;
            }
        }

        // For compatibility
        if (!Tools::strlen($this->marketPlaceChannel) && AmazonTools::fieldExists(_DB_PREFIX_.'orders', 'mp_order_id')) {
            $sql = 'SELECT `mp_order_id`, `mp_status`, `mp_channel`, `mp_channel_status` FROM `'._DB_PREFIX_.'orders`
                    WHERE `id_order` = "'.(int)$this->id.'" LIMIT 1 ;';

            if ($result = Db::getInstance()->executeS($sql)) {
                $result = array_shift($result);

                if (Tools::strlen($result['mp_order_id'])) {
                    $this->marketPlaceOrderId = $result['mp_order_id'];
                }
                if (Tools::strlen($result['mp_status'])) {
                    $this->marketPlaceOrderStatus = $result['mp_status'];
                }
                if (Tools::strlen($result['mp_channel'])) {
                    $this->marketPlaceChannel = $result['mp_channel'];
                }
                if (Tools::strlen($result['mp_channel_status'])) {
                    $this->marketPlaceChannelStatus = $result['mp_channel_status'];
                }
            }
        }
    }

    /**
     * @param int $days
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function orderList($days = 30)
    {
        $result = array();
        $sql = null;

        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'orders` o 
            LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'` mp ON (o.`id_order` = mp.`id_order`)
            WHERE `date_add` > DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)
            ORDER by `date_add` ASC';

            if (!($result = Db::getInstance()->executeS($sql))) {
                $result = array();
            }
        }

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode".Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                "SQL1: $sql\n",
                "orderList returned\n",
                $result
            ));
        }

        return ($result);
    }

    /**
     * @param $ps_status
     * @param int $days
     *
     * @return array|bool|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function ordersByStatus($ps_status, $days = 30, $id_order = null)
    {
        if (!is_array($ps_status) || !count($ps_status)) {
            return(false);
        }
        $result = array();
        $result1 = array();
        $result2 = array();

        $statuses = rtrim(implode(', ', $ps_status), ', ');

        $amazon_channels = sprintf('"%s", "%s"', AmazonMultiChannel::AMAZON_FBA_AMAZON, self::AMAZON_FBA_MULTICHANNEL);

        if (is_numeric($id_order)) {
            $filter = ' AND o.`id_order` = '.(int)$id_order;
        } else {
            $filter = null;
        }
        
        if (AmazonTools::fieldExists(_DB_PREFIX_.'orders', 'mp_channel')) {
            $sql = 'SELECT o.`id_order`, o.`reference`, o.`mp_channel_status`, o.`shipping_number`, o.`mp_order_id`, o.`date_add` FROM `'._DB_PREFIX_.'orders` o WHERE
            `mp_channel` IN ('.$amazon_channels.')
            AND (SELECT oh.id_order_state FROM `'._DB_PREFIX_.'order_history` oh WHERE o.id_order = oh.id_order ORDER BY oh.date_add DESC, oh.id_order_history DESC LIMIT 1) IN ('.pSQL($statuses).')
            AND `date_add` > DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)'.$filter.'
            ORDER by `date_add` ASC';

            if (!($result1 = Db::getInstance()->executeS($sql))) {
                $result1 = array();
            }
        }

        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
            $sql = 'SELECT o.`id_order`, o.`reference`, mp.`channel_status` as mp_channel_status, o.`shipping_number`, mp.`mp_order_id`, o.`date_add` FROM `'._DB_PREFIX_.'orders` o 
            LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'` mp ON (o.`id_order` = mp.`id_order`)
            WHERE mp.`channel` IN ('.$amazon_channels.')
            AND (SELECT oh.id_order_state FROM `'._DB_PREFIX_.'order_history` oh WHERE o.id_order = oh.id_order ORDER BY oh.date_add DESC, oh.id_order_history DESC LIMIT 1) IN ('.pSQL($statuses).')
            AND `date_add` > DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)'.$filter.'
            ORDER by `date_add` ASC';

            if (!($result2 = Db::getInstance()->executeS($sql))) {
                $result2 = array();
            }
        }

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode".Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                "SQL: $sql\n",
                "ordersByStatus returned\n",
                $result1,
                $result2
            ));
        }

        if (is_array($result1) && count($result1)) {
            $result = $result1;
        }
        if (is_array($result2) && count($result2)) {
            $result = array_merge($result, $result2);
        }
        return ($result);
    }

    /**
     * @param $id_order
     *
     * @return bool|Order
     */
    public static function isEligible($id_order)
    {
        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            self::pd('Unable to load Order');
            return false;
        }
        
        $id_lang = $order->id_lang;
        // Check FBA-MultiChannel Eligibility
        $products = $order->getProducts();

        if (!$products || !is_array($products) || !count($products)) {
            self::pd("Order returned no products ($id_order)");
            return false;
        }

        $carriers_multichannel = AmazonConfiguration::get('CARRIER_MULTICHANNEL');
        self::pd("Order id_lang: $order->id_lang", "Multichannel Carriers:", print_r($carriers_multichannel, true));

        if (!isset($carriers_multichannel[$id_lang]) || !is_array($carriers_multichannel[$id_lang])
            || !is_array($carriers_multichannel[$id_lang]['amazon'])
            || !count($carriers_multichannel[$id_lang]['amazon'])) {

            $error = 'FBA Multi-Channel Carrier Mapping is not or not correctly configured';
            self::$errors[] = $error;
            self::pd($error);

            return (false);
        }

        $pass = false;
        foreach ($carriers_multichannel[$id_lang]['prestashop'] as $key => $prestashop_id_carrier) {
            if ($prestashop_id_carrier == $order->id_carrier) {
                $pass = true;
                break;
            }
        }

        if (!$pass) {
            $error = sprintf('Carrier Mapping not found for this entry - id_order: %d - id_lang: %d - id_carrier: %d', $order->id, $id_lang, $order->id_carrier);
            self::$errors[] = $error;
            self::pd($error);

            return false;
        }

        // Require all the ordered products are FBA
        //
        foreach ($products as $product) {
            if (!AmazonTools::validateSKU($product['reference'])) {
                self::pd("AmazonTools::validateSKU, invalid SKU ({$product['reference']})");

                return false;
            }
            $id_product_attribute = (int)$product['product_attribute_id'] ? $product['product_attribute_id'] : null;

            if (!($options = AmazonProduct::getProductOptions($product['product_id'], $order->id_lang, $id_product_attribute))) {
                self::pd("No product options available for this product ({$product['reference']})");

                return false;
            }

            if (!isset($options['fba']) || !(bool)$options['fba']) {
                self::pd("FBA flag is not set to on ({$product['reference']})");

                return false;
            }
        }

        return ($order);
    }

    /**
     * @return bool
     */
    public function cancelFulfillmentOrder($spConnector, $sandbox = false)
    {
        $fbaOutbound = new AmazonSPAPICancelFulfillmentOrder(
            $spConnector, $this->getSellerFulfillmentOrderId(),
            new AmazonLogger(array(AmazonLogger::CHANNEL_SP_API_FBA, AmazonLogger::SUB_FBA_CANCEL_FULFILLMENT_ORDER)),
            false, $sandbox
        );
        $cancelResponse = $fbaOutbound->apiCancel();
        if ($cancelResponse->hasError()) {
            return $this->handleError($cancelResponse->getErrorMsg());
        }

        $this->updateMpChannel(self::AMAZON_FBA_STATUS_CANCELLED, self::AMAZON_FBA_MULTICHANNEL);

        return true;
    }

    /**
     * @param $status
     * @param null $channel
     *
     * @return bool
     */
    public function updateMpChannel($status, $channel = null)
    {
        $this->amazon_order_info->getOrderInfo();

        $this->marketPlaceChannelStatus = $status; // compat
        if ($channel) {
            $this->marketPlaceChannel = $channel; // compat;
        }

        $this->amazon_order_info->id_order = $this->id;

        if ($channel) {
            $this->amazon_order_info->channel = $channel;
        }
        $this->amazon_order_info->channel_status = $status;

        return($this->amazon_order_info->saveOrderInfo());
    }

    // 2023-01-28: Removed unused `getPackageTrackingDetails`

    /**
     * @param $order_id
     * @param $id_lang
     * @param bool $debug
     *
     * @return false|AmazonSPDefGetFulfillmentOrderResult
     */
    public function getFulfillmentOrder($spConnector, $sandbox = false)
    {
        $amzApi = new AmazonSPAPIGetFulfillmentOrder(
            $spConnector, $this->getSellerFulfillmentOrderId(),
            new AmazonLogger(array(AmazonLogger::CHANNEL_SP_API_FBA, AmazonLogger::SUB_FBA_GET_FULFILLMENT_ORDER)),
            false, $sandbox
        );

        $apiResult = $amzApi->apiGet();
        if ($apiResult->hasError()) {
            return $this->handleError($apiResult->getErrorMsg());
        }

        return $apiResult->getStructuredPayload();
    }

    /**
     * todo: Maybe we should use the `$this->amazon_id_lang` instead of passing `$id_lang` here
     * @param $id_lang
     * @param AmazonLogger $logger
     *
     * @return bool
     */
    public function createFulfillmentOrder($id_lang, $spConnector, $logger, $sandbox = false)
    {
        $carriers_multichannel = AmazonConfiguration::get('CARRIER_MULTICHANNEL');
        $useTax = (bool)((int)AmazonConfiguration::get('TAXES'));
        $specials = (int)AmazonConfiguration::get('SPECIALS');
        $id_order_state = (int)Configuration::get('AMAZON_FBA_MULTICHANNEL_STATE');

        if (!Validate::isLoadedObject($this)) {
            return $this->handleError('Unable to load order');
        }

        $id_order = $this->id;
        if (!$id_order_state) {
            return $this->handleError('Order state for FBA is not yet configured');
        }

        if (!isset($carriers_multichannel[$id_lang]) || !is_array($carriers_multichannel[$id_lang])
            || !is_array($carriers_multichannel[$id_lang]['amazon'])
            || !count($carriers_multichannel[$id_lang]['amazon'])) {
            return $this->handleError('FBA Multi-Channel Carrier Mapping is not or not correctly configured');
        }
        $ShippingSpeedCategory = $this->createFFOrderResolveShippingSpeedCat($carriers_multichannel, $id_lang);
        if (!$ShippingSpeedCategory) {
            return $this->handleError(sprintf('Carrier Mapping not found for this entry - id_order: %d - id_lang: %d - id_carrier: %d', $this->id, $id_lang, $this->id_carrier));
        }

        $currency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));
        $shop_name = Configuration::get('PS_SHOP_NAME');
        $id_customer = (int)$this->id_customer;
        $customer = new Customer($id_customer);
        if (!Validate::isLoadedObject($customer)) {
            return $this->handleError("Unable to find customer: $id_customer");
        }

        $address = new Address($this->id_address_delivery);
        if (!Validate::isLoadedObject($address)) {
            return $this->handleError("Unable to find address - 2: $id_customer");
        }
        $country_iso_code = Country::getIsoById($address->id_country);
        if (AmazonTools::strlen($country_iso_code) && !in_array($country_iso_code, self::$allowed_deliveries_for_countries_iso_codes)) {
            return $this->handleError("Country is not eligible for FBA delivery: $country_iso_code");
        } elseif (!AmazonTools::strlen($country_iso_code)) {
            return $this->handleError("Missing Country ISO Code for order: $id_order");
        }

        $products = $this->getProducts();
        if (!$products || !is_array($products) || !count($products)) {
            return $this->handleError("Empty or wrong cart for order: $id_order");
        }

        // Search for the suitable currency
        $target_currency = $spConnector->getCurrency();
        if (!Validate::isLoadedObject($target_currency)) {
            return $this->handleError("Unable to load currency of marketplace: {$spConnector->getMarketplaceId()}");
        }
        self::pd(
            "target id_currency: {$spConnector->getCurrency()->id} \n",
            "shop id_currency: $currency->id \n"
        );

        if ($this->amazon_order_info->is_standard_feature_available) {
            $this->amazon_order_info->channel = self::AMAZON_FBA_MULTICHANNEL;
            $this->amazon_order_info->sales_channel = AmazonTools::encodeText($shop_name);
            $this->amazon_order_info->ship_category = $ShippingSpeedCategory;
            $this->amazon_order_info->marketplace_id = $spConnector->getMarketplaceId();
        }

        // Check availability of products
        $items = $this->createFFOrderResolveItems(
            $products, $id_lang, $id_order, $useTax, $specials,
            $currency, $target_currency
        );
        if (!$sandbox) {
            $inventorySummaryApi = new AmazonSPAPIGetInventorySummaries(
                $spConnector, $spConnector->getMarketplaceId(),
                '', array_column($items, 'sellerSku'),
                new AmazonLogger(array(AmazonLogger::CHANNEL_SP_API_FBA, AmazonLogger::SUB_FBA_INVENTORY)),
                false, $sandbox
            );
            $summaryResponse = $inventorySummaryApi->apiGetAll();
            if (!$this->createFFOrderValidateInventory($summaryResponse, $items, $id_order)) {
                return false;
            }            
        }

        $AmazonOrder = new AmazonSPDefCreateFulfillmentOrderRequest(array(
            'marketplaceId' => $spConnector->getMarketplaceId(),
            'sellerFulfillmentOrderId' => "$id_order-$this->reference",
            'displayableOrderId' => "$id_order-$this->reference",
            'displayableOrderDate' => gmdate('Y-m-d\TH:i:s\Z', time()),
            'displayableOrderComment' => self::filter(sprintf('Order #%s from %s', $id_order, $shop_name)),
            'shippingSpeedCategory' => $ShippingSpeedCategory,
            'notificationEmails' => $this->createFFOrderResolveNotificationEmailList($customer),
            'destinationAddress' => $this->createFFOrderResolveDestinationAddr($address, $country_iso_code, $id_lang),
            'items' => array_map(function ($item) {
                return $item->sanityItem();
            }, $items),
        ));
        $logger->debug('MCF order', array('order' => $AmazonOrder));

        $fbaOutbound = new AmazonSPAPICreateFulfillmentOrder(
            $spConnector, $AmazonOrder,
            new AmazonLogger(array(AmazonLogger::CHANNEL_SP_API_FBA, AmazonLogger::SUB_FBA_CREATE_FULFILLMENT_ORDER)),
            false, $sandbox
        );
        $creationResponse = $fbaOutbound->apiCreate();
        if ($creationResponse->hasError()) {
            return $this->handleError($creationResponse->getErrorMsg());
        }

        $this->updateMpChannel(self::AMAZON_FBA_STATUS_SUBMITTED, self::AMAZON_FBA_MULTICHANNEL);
        $this->addToHistory(Amazon::getAmzEmployeeId(), $id_order_state, true);

        return true;
    }

    protected function createFFOrderResolveShippingSpeedCat($carriers_multichannel, $id_lang)
    {
        foreach ($carriers_multichannel[$id_lang]['prestashop'] as $key => $prestashop_id_carrier) {
            if ($prestashop_id_carrier == $this->id_carrier) {
                return $carriers_multichannel[$id_lang]['amazon'][$key];
            }
        }

        return '';
    }
    /**
     * @param Customer $customer
     * @return array
     */
    protected function createFFOrderResolveNotificationEmailList($customer)
    {
        $fba_notification = Configuration::get('AMAZON_FBA_NOTIFICATION');
        $shop_email = self::filter(Configuration::get('PS_SHOP_EMAIL'));
        switch ($fba_notification) {
            case Amazon::FBA_NOTIFICATION_CUSTOMER:
                return array($customer->email);
            case Amazon::FBA_NOTIFICATION_SHOP:
                if (AmazonTools::strlen($shop_email)) {
                    return array($shop_email);
                }
                break;
        }

        $emails = array();
        if (AmazonTools::strlen($shop_email)) {
            $emails[] = $shop_email;
        }
        $emails[] = $customer->email;

        return $emails;
    }

    /**
     * @param Address $address
     * @return AmazonSPDefAddress
     */
    protected function createFFOrderResolveDestinationAddr($address, $country_iso_code, $id_lang)
    {
        if ($address->id_state) {
            $state = new State($address->id_state);
            $amzState = $state->iso_code ?: $state->name;
        } else {
            $amzState = Country::getNameById($id_lang, $address->id_country);
        }
        $destinationAddress = new AmazonSPDefAddress(array(
            'name' => self::filter(sprintf('%s %s', $address->firstname, $address->lastname)),
            'city' => self::filter($address->city),
            'postalCode' => self::filter($address->postcode),
            'countryCode' => $country_iso_code,
            'phone' => AmazonTools::strlen($address->phone_mobile) ? self::filter($address->phone_mobile) : self::filter($address->phone),
            'stateOrRegion' => $amzState,
        ));

        if ($address->company) {
            $destinationAddress->addressLine1 = self::filter($address->company);
            $destinationAddress->addressLine2 = self::filter($address->address1);
            $destinationAddress->addressLine3 = self::filter($address->address2);
        } else {
            $destinationAddress->addressLine1 = self::filter($address->address1);
            $destinationAddress->addressLine2 = self::filter($address->address2);
        }

        if (function_exists('filter_var')) {
            foreach ($destinationAddress as $key => $val) {
                $destinationAddress->$key = filter_var($destinationAddress->$key, FILTER_SANITIZE_STRING);
            }
        }

        return $destinationAddress->sanityAddress();
    }

    /**
     * @param $products
     * @param Currency $currency
     * @param Currency $target_currency
     * @return AmazonSPDefCreateFulfillmentOrderItem[]
     */
    protected function createFFOrderResolveItems($products, $id_lang, $id_order, $useTax, $specials, $currency, $target_currency)
    {
        $items = array();
        foreach ($products as $cart_product) {
            $SKU = $cart_product['reference'];
            if (empty($SKU)) {
                $this->handleError(sprintf(
                    'Missing Reference(SKU) for product: %d/%d',
                    $cart_product['product_id'],
                    $cart_product['product_attribute_id']
                ));
                continue;
            }

            $product = new AmazonProduct($SKU, false, $id_lang);
            if (!Validate::isLoadedObject($product)) {
                $this->handleError("Unable to find product: $SKU");
                continue;
            }

            if (!($options = AmazonProduct::getProductOptions((int)$product->id, $id_lang, $product->id_product_attribute))) {
                $this->handleError(sprintf(
                    'Un-eligible product: %d/%d',
                    $cart_product['product_id'],
                    $cart_product['product_attribute_id']
                ));
                continue;
            }

            if (!isset($options['fba']) || !$options['fba']) {
                $this->handleError(sprintf(
                    'Not FBA product: %d/%d',
                    $product->id,
                    $product->id_product_attribute
                ));
                continue;
            }
            $SellerID = sprintf('i-%d-%d-%d', (int)$id_order, (int)$product->id, (int)$product->id_product_attribute);
            $Quantity = isset($cart_product['product_quantity']) ? (int)$cart_product['product_quantity'] : 1;
            $price = $product->getPrice($useTax, $product->id_product_attribute, 6, null, false, !$product->on_sale && $specials);
            $price = $currency->id != $target_currency->id ? Tools::convertPrice($price, $target_currency) : $price;

            $item = new AmazonSPDefCreateFulfillmentOrderItem(array(
                'sellerSku' => self::filter($SKU),
                'sellerFulfillmentOrderItemId' => self::filter($SellerID),
                'quantity' => $Quantity,
                'displayableComment' => self::filter($product->name),
                'perUnitDeclaredValue' => array(
                    'currencyCode' => $target_currency->iso_code,
                    'value' => AmazonTools::ps_round($price, 2),
                ),
            ));
            if (AmazonTools::strlen($this->gift_message)) {
                $item->giftMessage = self::filter($this->gift_message);
                // On Prestashop we can't send per item gift message, thus we send the message for all ordered items.
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param AmazonSellerPartnerResponse $summaryResponse
     * @param AmazonSPDefCreateFulfillmentOrderItem[] $items
     * @return bool
     */
    protected function createFFOrderValidateInventory($summaryResponse, $items, $id_order)
    {
        foreach ($summaryResponse as $summary) {
            if ($summary instanceof AmazonSellerPartnerResponse) {
                return $this->handleError(
                    "Product availability check failed or no items available for order id: $id_order",
                    $summaryResponse->getErrorMsg()
                );
            }

            if ($summary instanceof AmazonSPDefInventorySummary) {
                foreach ($items as $item) {
                    if ($summary->sellerSku == $item->sellerSku && $summary->getFulfillableQuantity() >= $item->quantity) {
                        $item->processed = true;
                    }else if($summary->sellerSku == $item->sellerSku){                        
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf("createFFOrderValidateInventory: SKU: %s has itemQuantity: %s which is different to fulfillableQuantity: %s ", 
                                $item->sellerSku,
                                $item->quantity,
                                $summary->getFulfillableQuantity()
                            ));
                        }
                    }
                }        
            }
        }

        foreach ($items as $item) {
            if (!$item->processed) {
                return $this->handleError(sprintf(
                    'Product availability, not enough stock to fulfill (Product: %s)',
                    $item->sellerSku
                ));
            }
        }

        return true;
    }

    protected function handleError()
    {
        $message = Amazon::dbt(func_get_args());
        self::$errors[] = $message;
        echo $message;

        return false;
    }

    protected function ed()
    {
        echo Amazon::dbt(func_get_args());
    }

    /**
     * @param $text
     *
     * @return string
     */
    public static function filter($text)
    {
        // remove chars rejected by Validate class
        return mb_ereg_replace('[!<>?=+{}_$%&]*$', '', $text);
    }

    /**
     * @param $id_employee
     * @param $id_order_state
     * @param bool $fbaPendingState
     */
    public function addToHistory($id_employee, $id_order_state, $fbaPendingState = false)
    {
        $reinitContext = AmazonTools::reInitContextControllerIfNeed($this->context);
        if ($reinitContext['reinit']) {
            $this->context = $reinitContext['context'];
        }

        // Add History
        $new_history = new AmazonOrderHistory();
        $new_history->id_order = (int)$this->id;
        $new_history->id_employee = (int)$id_employee;
        $this->changeIdOrderState($new_history, $id_order_state, $fbaPendingState);
        $new_history->addWithemail(true);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("AmazonOrderHistory: %s", print_r(get_object_vars($new_history), true)));
        }
    }

    /**
     * @return AmazonSPDefFulfillmentOrder[]
     */
    public function ListAllFulfillmentOrders($date, $spConnector, $sandbox = false)
    {
        $fbaOutbound = new AmazonSPAPIFulfillmentOrders(
            $spConnector,
            new AmazonLogger(array(AmazonLogger::CHANNEL_SP_API_FBA, AmazonLogger::SUB_FBA_GET_FULFILLMENT_ORDERS)),
            false, $sandbox
        );

        $ffOrders = array();
        $apiResponse = $fbaOutbound->apiGetAll($date);
        foreach ($apiResponse as $responseItem) {
            if ($responseItem instanceof AmazonSellerPartnerResponse) {
                self::$errors[] = $responseItem->getErrorMsg();
                break;
            }
            if ($responseItem instanceof AmazonSPDefFulfillmentOrder) {
                $ffOrders[] = $responseItem;
            }
        }

        return $ffOrders;
    }

    /**
     * @param AmazonOrderHistory $new_history
     * @param int $id_order_state
     * @param bool $fbaPendingState
     */
    protected function changeIdOrderState($new_history, $id_order_state, $fbaPendingState)
    {
        $id_order = $this->id;
        $order = new Order((int) $id_order);
        $new_os = new OrderState((int) $id_order_state, $order->id_lang);

        /**
         * When update paid state, PS will create another payment record.
         * Our FBA pending state is a paid state.
         * 
         * If FBA pending state is different from default PS paid state, it will end up double payment records.
         * Unfortunately, the PS code is hard, the only way now is to temporarily mark the FBA pending state as non-paid, then restore after done.
         * Manual alter DB to avoid hooks.
         */
        $specialCase = $fbaPendingState && $new_os->isRemovable();

        if ($specialCase) {
            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'order_state` SET `paid` = 0 WHERE `id_order_state` = ' . (int) $id_order_state);
            sleep(1);
            $new_os->clearCache();
        }

        $new_history->changeIdOrderState($id_order_state, $id_order);

        if ($specialCase) {
            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'order_state` SET `paid` = 1 WHERE `id_order_state` = ' . (int) $id_order_state);
            $new_os->clearCache();
        }
    }
    
    public function getErrors()
    {
        return self::$errors;
    }

    public static function pd()
    {
        if (Amazon::$debug_mode) {
            $backTrace = debug_backtrace();
            $caller = array_shift($backTrace);
            $fileSegment = explode('/', $caller['file']);
            $file = array_pop($fileSegment);

            $debug = array_map(function ($arg) use ($file, $caller) {
                return sprintf('%s(#%d): %s', $file, $caller['line'], $arg);
            }, func_get_args());
            AmazonTools::pre($debug);
        }
    }

    /**
     * todo: Remove
     * @deprecated 
     * @param $targetCountry
     * @return array|string
     */
    protected function searchForCurrencyAndMkp($targetCountry)
    {
        // Load currency from configuration platform
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketPlaceCurrency = AmazonConfiguration::get('CURRENCY');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (!is_array($marketPlaceRegion) || !is_array($marketPlaceCurrency)) {
            return sprintf('Lack of configuration: marketplace regions: %s marketplace currencies: %s', print_r($marketPlaceRegion, true), print_r($marketPlaceCurrency, true));
        }

        $marketLang2Region = array_flip($marketPlaceRegion);
        if (!isset($marketLang2Region[$targetCountry])) {
            return sprintf('Lack of configuration: marketplace regions: %s', print_r($marketLang2Region, true));
        }

        $target_id_lang = $marketLang2Region[$targetCountry];
        $target_id_currency = Currency::getIdByIsoCode($marketPlaceCurrency[$target_id_lang]);
        $target_marketplace = $marketPlaceIds[$target_id_lang];
        if (!(int)$target_id_currency) {
            return sprintf('Missing currency: %s', $marketPlaceCurrency[$target_id_lang]);
        }

        $result = array('lang' => $target_id_lang, 'currency' => $target_id_currency, 'mkp' => $target_marketplace);
        // Override currency if set
        $forceCurrencyId = (int)AmazonConfiguration::get(AmazonConstant::FBA_MC_CURRENCY);
        $forceCurrency = new Currency($forceCurrencyId);
        if (Validate::isLoadedObject($forceCurrency)) {
            $result['currency'] = $forceCurrency->id;
        }

        return $result;
    }
}
