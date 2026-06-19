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
class AmazonSPAPIFulfillmentOrders extends AmazonSellerPartnerAPIPayloadWrapper
{
    public function __construct($connector, $logger = null, $devMode = false, $isSandBox = false)
    {
        parent::__construct(
            $connector, self::API_TYPE_FBA_LIST_ALL_FULFILLMENT_ORDERS,
            $logger, $devMode, $isSandBox || self::SANDBOX
        );
    }

    /**
     * @param $date
     * @return Generator
     */
    public function apiGetAll($date)
    {
        // format date
        if ($date) {
            $date = gmdate('Y-m-d\TH:i:s\Z', strtotime($date));
        }
        
        return $this->getListAll(
            array('query_start_date' => $date),
            'fulfillmentOrders', 20, true
        );
    }

    /**
     * @param $payload
     * @return AmazonSPDefFulfillmentOrdersList
     */
    public function parsePayload($payload)
    {
        return new AmazonSPDefFulfillmentOrdersList($payload);
    }
}
