<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}
class AmazonSPDefOrder extends AmazonSPDefObject
{
    const FULFILLMENT_CHANNEL_AMAZON = 'AFN';
    const FULFILLMENT_CHANNEL_SELLER = 'MFN';

    const STATUS_PENDING = 'Pending';
    const STATUS_UNSHIPPED = 'Unshipped';
    const STATUS_PARTIALLY_SHIPPED = 'PartiallyShipped';
    const STATUS_SHIPPED = 'Shipped';
    const STATUS_CANCELED = 'Canceled';
    const STATUS_UNFULFILLABLE = 'Unfulfillable';
    const STATUS_INVOICE_UNCONFIRMED = 'InvoiceUnconfirmed';
    const STATUS_PENDING_AVAILABILITY = 'PendingAvailability';

    public $AmazonOrderId;
    public $SellerOrderId;
    public $PurchaseDate;
    public $LastUpdateDate;
    public $OrderStatus;

    // Optional properties
    public $FulfillmentChannel; // MFN | AFN
    public $SalesChannel;
    public $OrderChannel;
    public $ShipServiceLevel;
    public $ShipmentServiceLevelCategory;
    /** @var AmazonSPDefMoney */
    public $OrderTotal;
    public $NumberOfItemsShipped = 0;
    public $NumberOfItemsUnshipped = 0;
    public $MarketplaceId;
    public $OrderType;  // StandardOrder, LongLeadTimeOrder, Preorder, BackOrder, SourcingOnDemandOrder

    public $EarliestShipDate;
    public $LatestShipDate;
    public $EarliestDeliveryDate;
    public $LatestDeliveryDate;

    public $IsBusinessOrder = false;
    public $IsPrime = false;
    public $IsPremiumOrder = false;
    public $IsReplacementOrder = false;
    public $ReplacedOrderId;

    public $IsAccessPointOrder = false;

    /** @var AmazonSPDefBuyerTaxInformation */
    public $BuyerTaxInformation;
    /** @var AmazonSPDefShippingAddress */
    public $ShippingAddress;
    /** @var AmazonSPDefBuyerInfo */
    public $BuyerInfo;
    /** @var AmazonSPDefShippingAddress */
    public $DefaultShipFromLocationAddress;
    
    // Custom
    public $isDemo = false;

    protected static $complexChildren = array(
        'OrderTotal' => AmazonSPDefMoney::class,
        'ShippingAddress' => AmazonSPDefShippingAddress::class,
        'DefaultShipFromLocationAddress' => AmazonSPDefShippingAddress::class,
        'BuyerInfo' => AmazonSPDefBuyerInfo::class,
        'BuyerTaxInformation' => AmazonSPDefBuyerTaxInformation::class,
    );
}
