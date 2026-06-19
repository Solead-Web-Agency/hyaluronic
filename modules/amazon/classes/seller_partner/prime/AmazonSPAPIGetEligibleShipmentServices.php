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

class AmazonSPAPIGetEligibleShipmentServices extends AmazonSellerPartnerAPIPayloadWrapper
{
    /** @var AmazonSPDefGetEligibleShipmentServicesRequest */
    private $body;

    public function __construct($connector, $body, $logger, $devMode = false, $isSandBox = false)
    {
        parent::__construct($connector, self::API_TYPE_PRIME_GET_ELIGIBLE_SHIPMENT_SERVICES, $logger, $devMode, $isSandBox);
        $this->body = $body;
    }

    public function apiGet()
    {
        if ($this->body->validate()) {
            return $this->doRequest($this->body);
        }

        return AmazonSellerPartnerResponse::badRequest(400, 'Invalid request body!');
    }

    public function parsePayload($payload)
    {
        return new AmazonSPDefGetEligibleShipmentServicesResult($payload);
    }
}
