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
class AmazonSPDefPrimeShipment extends AmazonSPDefObject
{
    public $ShipmentId;
    public $AmazonOrderId;
    /** @var AmazonSPDefPrimeItem[] */
    public $ItemList = array();
    /** @var AmazonSPDefPrimeAddress */
    public $ShipFromAddress;
    /** @var AmazonSPDefPrimeAddress */
    public $ShipToAddress;
    /** @var AmazonSPDefPackageDimensions */
    public $PackageDimensions;
    /** @var AmazonSPDefWeight */
    public $Weight;
    /** @var AmazonSPDefCurrencyAmount */
    public $Insurance;
    /** @var AmazonSPDefShippingService */
    public $ShippingService;
    /** @var AmazonSPDefPrimeLabel */
    public $Label;
    public $Status; // Purchased | RefundPending | RefundRejected | RefundApplied
    public $CreatedDate;

    public $SellerOrderId;
    public $TrackingId;
    public $LastUpdatedDate;

    protected static $complexChildren = array(
        'ShipFromAddress' => AmazonSPDefPrimeAddress::class,
        'ShipToAddress' => AmazonSPDefPrimeAddress::class,
        'PackageDimensions' => AmazonSPDefPackageDimensions::class,
        'Weight' => AmazonSPDefWeight::class,
        'Insurance' => AmazonSPDefCurrencyAmount::class,
        'ShippingService' => AmazonSPDefShippingService::class,
        'Label' => AmazonSPDefPrimeLabel::class,
    );

    protected static $listOfComplexChildren = array(
        'ItemList' => AmazonSPDefPrimeItem::class,
    );
}
