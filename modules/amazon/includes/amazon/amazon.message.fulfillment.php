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
class AmazonFulfillmentMessage
{
    // Move all Amazon carrier codes to setting file amazon_carrier_codes.ini

    public $amzId;
    public $psId;
    public $carrierCode;
    public $carrierName;
    public $shippingNumber;
    public $shippingMethod;
    public $timestamp;

    private $isAmzCarrier;

    public function __construct($amzId, $psId, $carrier, $shippingNumber, $shippingMethod, $timestamp)
    {
        $this->amzId = $amzId;
        $this->psId = $psId;
        $this->shippingNumber = $shippingNumber;
        $this->shippingMethod = $shippingMethod;
        $this->timestamp = $timestamp;
        $this->resolveCarrier($carrier);
    }

    public function isAmzCarrier()
    {
        return $this->isAmzCarrier;
    }

    public function hasCarrier()
    {
        return $this->carrierCode || $this->carrierName;
    }

    public function toString()
    {
        $timestamp = $this->timestamp ? date('Y-m-d H:i:s', $this->timestamp) : 'n/a';

        return sprintf(
            'Order: %s | Merchant order id: %d | Shipping number: %s | Carrier: %s, carrier alt./name: %s | Shipping method: %s | Shipping date/time: %s',
            $this->amzId, $this->psId, $this->shippingNumber, $this->carrierCode, $this->carrierName, $this->shippingMethod, $timestamp
        );
    }

    public function getDate()
    {
        $timestamp = $this->timestamp - 600; // sub 10 minutes  --> fix FulfillmentDate error
        if (!$timestamp || !is_numeric($timestamp)) {
            $timestamp = time() - 5400;
            if (!date_default_timezone_get()) {
                date_default_timezone_set('Europe/Helsinki');
            }
        }

        return gmdate('c', $timestamp);
    }

    private function resolveCarrier($carrier)
    {
        require_once dirname(__FILE__) . '/../../classes/amazon.shipping.outgoing.php';
        $carrier = trim($carrier);

        $this->isAmzCarrier = in_array($carrier, AmazonShippingOutgoing::loadCarriersDefaultCache());
        $this->carrierCode = $carrier;
        // Use a custom carry code that replaces the "Other" code
        $this->carrierName = $carrier;
    }
}
