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
class AmazonSPDefCreateFulfillmentOrderRequest extends AmazonSPDefRequest
{
    // Require
    public $marketplaceId;
    public $sellerFulfillmentOrderId;
    public $displayableOrderId;
    public $displayableOrderDate;
    public $displayableOrderComment;
    public $shippingSpeedCategory;  // enum: Standard | Expedited | Priority | ScheduledDelivery
    /** @var AmazonSPDefAddress */
    public $destinationAddress;
    /** @var AmazonSPDefCreateFulfillmentOrderItem[] */
    public $items;

    // Optional
    /** @var AmazonSPDefDeliveryWindow */
    public $deliveryWindow;
    public $fulfillmentAction;  // enum: Ship | Hold
    public $fulfillmentPolicy;  // enum: FillOrKill | FillAll | FillAllAvailable
    /** @var AmazonSPDefCODSettings */
    public $codSettings;
    public $shipFromCountryCode;
    public $notificationEmails = array();
    /** @var AmazonSPDefFeatureSettings[] */
    public $featureConstraints;

    protected static $primitiveRequired = array(
        'marketplaceId',
        'sellerFulfillmentOrderId',
        'displayableOrderId',
        'displayableOrderDate',
        'displayableOrderComment',
        'shippingSpeedCategory',
    );

    protected static $complexChildren = array(
        'deliveryWindow' => AmazonSPDefDeliveryWindow::class,
        'destinationAddress' => AmazonSPDefAddress::class,
        'codSettings' => AmazonSPDefCODSettings::class,
    );

    protected static $listOfComplexChildren = array(
        'featureConstraints' => AmazonSPDefFeatureSettings::class,
        'items' => AmazonSPDefCreateFulfillmentOrderItem::class,
    );

    protected static $nonPrimitiveOptionalChildrenOnRequest = array(
        'deliveryWindow',
        'codSettings',
        'featureConstraints',
    );

    public function validate()
    {
        return parent::validate() && $this->validateShippingSpeedCategory()
            && $this->destinationAddress && $this->destinationAddress->validate()
            && $this->validateItems();
    }

    public function toAPI()
    {
        return array(
            'marketplaceId' => $this->marketplaceId,
            'sellerFulfillmentOrderId' => $this->sellerFulfillmentOrderId,
            'displayableOrderId' => $this->displayableOrderId,
            'displayableOrderDate' => $this->displayableOrderDate,
            'displayableOrderComment' => $this->displayableOrderComment,
            'shippingSpeedCategory' => $this->shippingSpeedCategory,
            'destinationAddress' => $this->destinationAddress->toAPI(),
            'items' => array_map(function ($item) {
                return $item->toAPI();
            }, $this->items),
            'notificationEmails' => $this->notificationEmails,
        );
    }

    protected function validateShippingSpeedCategory()
    {
        return in_array($this->shippingSpeedCategory, array(
            'Standard',
            'Expedited',
            'Priority',
            'ScheduledDelivery'
        ));
    }

    protected function validateItems()
    {
        if (!count($this->items)) {
            return false;
        }

        foreach ($this->items as $item) {
            if (!$item->validate()) {
                return false;
            }
        }

        return true;
    }
}
