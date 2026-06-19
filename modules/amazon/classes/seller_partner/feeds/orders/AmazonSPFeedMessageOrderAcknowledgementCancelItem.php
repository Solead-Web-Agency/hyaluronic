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
class AmazonSPFeedMessageOrderAcknowledgementCancelItem
{
    const REASON_NO_INVENTORY = 'NoInventory';
    const REASON_ADDRESS_UNDELIVERABLE = 'ShippingAddressUndeliverable';
    const REASON_CUSTOMER_EXCHANGE = 'CustomerExchange';
    const REASON_BUYER_CANCELLED = 'BuyerCanceled';
    const REASON_GENERAL_ADJUSTMENT = 'GeneralAdjustment';
    const REASON_CARRIER_CREDIT_DECISION = 'CarrierCreditDecision';
    const REASON_RISKY = 'RiskAssessmentInformationNotValid';
    const REASON_CARRIER_COVERAGE_FAILURE = 'CarrierCoverageFailure';
    const REASON_CUSTOMER_RETURN = 'CustomerReturn';
    const REASON_MERCHANDISE_NOT_RECEIVED = 'MerchandiseNotReceived';
    const REASON_CANNOT_VERIFY_INFORMATION = 'CannotVerifyInformation';
    const REASON_PRICING_ERROR = 'PricingError';
    const REASON_REJECT_ORDER = 'RejectOrder';
    const REASON_WEATHER_DELAY = 'WeatherDelay';

    public $AmazonOrderItemCode;
    public $MerchantOrderItemID;
    public $CancelReason;

    public static function instanceFromInput($input)
    {
        $amzId = isset($input['AmazonOrderItemCode']) && $input['AmazonOrderItemCode']
            ? trim($input['AmazonOrderItemCode']) : '';
        $psId = isset($input['MerchantOrderItemID']) && $input['MerchantOrderItemID']
            ? trim($input['MerchantOrderItemID']) : '';
        $reason = isset($input['CancelReason']) && $input['CancelReason'] ? trim($input['CancelReason']) : '';

        return new self($amzId, $psId, $reason);
    }

    public function __construct($AmazonOrderItemCode, $MerchantOrderItemID, $CancelReason)
    {
        $this->AmazonOrderItemCode = $AmazonOrderItemCode;
        $this->MerchantOrderItemID = $MerchantOrderItemID;
        $this->CancelReason = $CancelReason;
    }

    public function isValidItem()
    {
        return $this->AmazonOrderItemCode && (!$this->CancelReason || in_array($this->CancelReason, array(
                    self::REASON_NO_INVENTORY,
                    self::REASON_ADDRESS_UNDELIVERABLE,
                    self::REASON_CUSTOMER_EXCHANGE,
                    self::REASON_BUYER_CANCELLED,
                    self::REASON_GENERAL_ADJUSTMENT,
                    self::REASON_CARRIER_CREDIT_DECISION,
                    self::REASON_RISKY,
                    self::REASON_CARRIER_COVERAGE_FAILURE,
                    self::REASON_CUSTOMER_RETURN,
                    self::REASON_MERCHANDISE_NOT_RECEIVED,
                    self::REASON_CANNOT_VERIFY_INFORMATION,
                    self::REASON_PRICING_ERROR,
                    self::REASON_REJECT_ORDER,
                    self::REASON_WEATHER_DELAY,
                )));
    }

    /**
     * @param DOMDocument $domDoc
     * @return DOMElement|false
     * @throws DOMException
     */
    public function generateItemXml($domDoc)
    {
        $item = $domDoc->createElement('Item');

        $item->appendChild($domDoc->createElement('AmazonOrderItemCode', $this->AmazonOrderItemCode));
        if ($this->MerchantOrderItemID) {
            $item->appendChild($domDoc->createElement('MerchantOrderItemID', $this->MerchantOrderItemID));
        }
        if ($this->CancelReason) {
            $item->appendChild($domDoc->createElement('CancelReason', $this->CancelReason));
        }

        return $item;
    }
}
