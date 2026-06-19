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
/**
 * https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/OrderFulfillment.xsd
 */
class AmazonSPFeedMessageOrderFulfillment extends AmazonSPFeedMessage
{
    protected $amzOrderId;
    protected $fulfillmentDate;

    protected $isAmzCarrier;
    protected $carrierCode;
    protected $carrierName;

    protected $shippingMethod;
    protected $shippingNumber;
    protected $isShopify;

    public function __construct(
        $amzOrderId,
        $fulfillmentDate,
        $isAmzCarrier,
        $carrierCode,
        $carrierName,
        $shippingMethod,
        $shippingNumber,
        $isShopify = false
    ) {
        parent::__construct();

        $this->amzOrderId = $amzOrderId;
        $this->fulfillmentDate = $fulfillmentDate;
        $this->isAmzCarrier = $isAmzCarrier;
        $this->carrierCode = $carrierCode;
        $this->carrierName = $carrierName;
        $this->shippingMethod = $shippingMethod;
        $this->shippingNumber = $shippingNumber;
        $this->isShopify = $isShopify;
    }

    /**
     * @param DOMDocument $domDoc
     * @return DOMElement[]
     * @throws DOMException
     */
    protected function generateRemainingMessage($domDoc)
    {
        $ffData = $domDoc->createElement('FulfillmentData');
        if (!$this->isShopify) {
            if ($this->isAmzCarrier) {
                $carrierCode = $domDoc->createElement('CarrierCode', $this->carrierCode);
                $ffData->appendChild($carrierCode);
            } else {
                $carrierName = $domDoc->createElement('CarrierName', $this->carrierName);
                $ffData->appendChild($carrierName);
            }
        } else {
            if (!empty($this->carrierCode)) {
                $carrierCode = $domDoc->createElement('CarrierCode', $this->carrierCode);
                $ffData->appendChild($carrierCode);
            }
            if ((empty($this->carrierCode) || $this->carrierCode == 'Other') && !empty($this->carrierName)) {
                $carrierName = $domDoc->createElement('CarrierName', $this->carrierName);
                $ffData->appendChild($carrierName);
            }
        }


        if ($this->shippingMethod) {
            $shippingMethod = $domDoc->createElement('ShippingMethod', $this->shippingMethod);
            $ffData->appendChild($shippingMethod);
        }
        if ($this->shippingNumber) {
            $ShipperTrackingNumber = $domDoc->createElement('ShipperTrackingNumber', $this->shippingNumber);
            $ffData->appendChild($ShipperTrackingNumber);
        }

        $orderFf = $domDoc->createElement('OrderFulfillment');
        $orderFf->appendChild($domDoc->createElement('AmazonOrderID', $this->amzOrderId));
        $orderFf->appendChild($domDoc->createElement('FulfillmentDate', $this->fulfillmentDate));
        $orderFf->appendChild($ffData);

        return array($orderFf);
    }

    public function isValidMessage()
    {
        return true;
    }
}
