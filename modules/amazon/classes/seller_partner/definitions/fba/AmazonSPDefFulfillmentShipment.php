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
class AmazonSPDefFulfillmentShipment extends AmazonSPDefObject
{
    const SHIPMENT_STATUS_PENDING = 'PENDING';
    const SHIPMENT_STATUS_SHIPPED = 'SHIPPED';
    const SHIPMENT_STATUS_CANCELLED_BY_FULFILLER = 'CANCELLED_BY_FULFILLER';
    const SHIPMENT_STATUS_CANCELLED_BY_SELLER = 'CANCELLED_BY_SELLER';

    // Required
    public $amazonShipmentId;
    public $fulfillmentCenterId;
    public $fulfillmentShipmentStatus;  // enum: PENDING | SHIPPED | CANCELLED_BY_FULFILLER | CANCELLED_BY_SELLER
    /** @var AmazonSPDefFulfillmentShipmentItem[] */
    public $fulfillmentShipmentItem;

    // Optional
    public $shippingDate;
    public $estimatedArrivalDate;
    public $shippingNotes = array();
    /** @var AmazonSPDefFulfillmentShipmentPackage[] */
    public $fulfillmentShipmentPackage;

    protected static $listOfComplexChildren = array(
        'fulfillmentShipmentItem' => AmazonSPDefFulfillmentShipmentItem::class,
        'fulfillmentShipmentPackage' => AmazonSPDefFulfillmentShipmentPackage::class,
    );

    public function getAnyExistedPackageTrackingNumber()
    {
        foreach ($this->fulfillmentShipmentPackage as $fulfillmentShipmentPackage) {
            if ($fulfillmentShipmentPackage->trackingNumber) {
                return $fulfillmentShipmentPackage->trackingNumber;
            }
        }

        return '';
    }

    public function getAnyExistedPackageCarrierCode()
    {
        foreach ($this->fulfillmentShipmentPackage as $fulfillmentShipmentPackage) {
            if ($fulfillmentShipmentPackage->carrierCode) {
                return $fulfillmentShipmentPackage->carrierCode;
            }
        }

        return '';
    }

    public function getAnyExistedPackageEstimatedArrival()
    {
        foreach ($this->fulfillmentShipmentPackage as $fulfillmentShipmentPackage) {
            if ($fulfillmentShipmentPackage->estimatedArrivalDate) {
                return $fulfillmentShipmentPackage->estimatedArrivalDate;
            }
        }

        return '';
    }
}
