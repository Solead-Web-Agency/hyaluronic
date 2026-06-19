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
class AmazonSPAPICreateFulfillmentOrder extends AmazonSellerPartnerAPI
{
    /** @var AmazonSPDefCreateFulfillmentOrderRequest */
    private $body;

    public function __construct($connector, $body, $logger = null, $devMode = false, $isSandBox = false)
    {
        parent::__construct($connector, self::API_TYPE_FBA_CREATE_FULFILLMENT_ORDER, $logger, $devMode, $isSandBox);
        $this->body = $body;
    }

    public function apiCreate()
    {
        if ($this->body->validate()) {
            return $this->doRequest($this->body->toAPI());
        }

        return AmazonSellerPartnerResponse::badRequest(400, 'Invalid request body!');
    }

    // This API return nothing
    public function parsePayload($payload)
    {
        return $payload;
    }
}
