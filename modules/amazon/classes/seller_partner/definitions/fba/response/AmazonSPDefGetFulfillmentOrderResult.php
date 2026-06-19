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
class AmazonSPDefGetFulfillmentOrderResult extends AmazonSPDefObject
{
    // Required
    /** @var AmazonSPDefFulfillmentOrder */
    public $fulfillmentOrder;
    /** @var AmazonSPDefFulfillmentOrderItem[] */
    public $fulfillmentOrderItems;
    /** @var AmazonSPDefReturnItem[] */
    public $returnItems;
    /** @var AmazonSpDefReturnAuthorization[] */
    public $returnAuthorizations;

    // Optional
    /** @var AmazonSPDefFulfillmentShipment[] */
    public $fulfillmentShipments;

    protected static $complexChildren = array(
        'fulfillmentOrder' => AmazonSPDefFulfillmentOrder::class,
    );

    protected static $listOfComplexChildren = array(
        'fulfillmentOrderItems' => AmazonSPDefFulfillmentOrderItem::class,
        'fulfillmentShipments' => AmazonSPDefFulfillmentShipment::class,
        'returnItems' => AmazonSPDefReturnItem::class,
        'returnAuthorizations' => AmazonSpDefReturnAuthorization::class,
    );

    public function hasItems()
    {
        return count($this->fulfillmentOrderItems) > 0;
    }

    public function getFirstItem()
    {
        return $this->hasItems() ? $this->fulfillmentOrderItems[0] : null;
    }

    public function hasShipment()
    {
        return count($this->fulfillmentShipments) > 0;
    }

    public function hasShipmentNotShipped()
    {
        foreach ($this->fulfillmentShipments as $fulfillmentShipment) {
            if ($fulfillmentShipment->fulfillmentShipmentStatus != AmazonSPDefFulfillmentShipment::SHIPMENT_STATUS_SHIPPED) {
                return true;
            }
        }

        return false;
    }

    public function getAnyExistedShipmentEstimatedArrival()
    {
        foreach ($this->fulfillmentShipments as $fulfillmentShipment) {
            $estimatedArrival = $fulfillmentShipment->estimatedArrivalDate;
            if ($estimatedArrival) {
                return $estimatedArrival;
            }
        }

        return '';
    }

    public function getAnyExistedShipmentPackageTrackingNumber()
    {
        foreach ($this->fulfillmentShipments as $fulfillmentShipment) {
            $trackingNumber = $fulfillmentShipment->getAnyExistedPackageTrackingNumber();
            if ($trackingNumber) {
                return $trackingNumber;
            }
        }

        return '';
    }

    public function getAnyExistedShipmentPackageCarrierCode()
    {
        foreach ($this->fulfillmentShipments as $fulfillmentShipment) {
            $carrierCode = $fulfillmentShipment->getAnyExistedPackageCarrierCode();
            if ($carrierCode) {
                return $carrierCode;
            }
        }

        return '';
    }

    public function getAnyExistedShipmentPackageEstimatedArrival()
    {
        foreach ($this->fulfillmentShipments as $fulfillmentShipment) {
            $estimatedArrival = $fulfillmentShipment->getAnyExistedPackageEstimatedArrival();
            if ($estimatedArrival) {
                return $estimatedArrival;
            }
        }

        return '';
    }
}
