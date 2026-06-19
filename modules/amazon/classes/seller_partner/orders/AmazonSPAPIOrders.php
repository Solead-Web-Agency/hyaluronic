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
class AmazonSPAPIOrders extends AmazonSellerPartnerAPIPayloadWrapper
{
    protected $marketplaces = array();

    /**
     * @param IAmazonSellerPartnerConnector&IAmazonSellerPartnerLicense $connector
     * @param $marketplaces
     * @param $logger
     * @param $devMode
     * @param $isSandbox
     */
    public function __construct($connector, $marketplaces = array(), $logger = null, $devMode = false, $isSandbox = false)
    {
        $this->marketplaces = $marketplaces ?: $connector->getMarketplaces();
        parent::__construct($connector, self::API_TYPE_ORDERS_GET_LIST, $logger, $devMode, $isSandbox || self::SANDBOX);
    }

    public function apiListAllByOrderIds($amzOrderIds)
    {
        return $this->apiListAll(null, null, null, null, null, null, $amzOrderIds);
    }

    public function apiListAll(
        $createAfter = null,
        $createBefore = null,
        $lastUpdatedAfter = null,
        $lastUpdateBefore = null,
        $orderStatuses = null,
        $fulfillmentChannels = null,
        $amzOrderIds = null,
        $maxResultsPerPage = 100
    )
    {
        $amzOrderIds = $amzOrderIds ? array_values($amzOrderIds) : $amzOrderIds;
        $request = new AmazonSPDefOrderRequest(array(
            'AmazonOrderIds' => $amzOrderIds,
        ));
        if ($request->validate()) {
            $requestBody = $this->mergeSandboxTestCase(array(
                'marketplace_ids' => $this->marketplaces,
                'created_after' => $createAfter,
                'created_before' => $createBefore,
                'last_updated_after' => $lastUpdatedAfter,
                'last_updated_before' => $lastUpdateBefore,
                'order_statuses' => $orderStatuses,
                'fulfillment_channels' => $fulfillmentChannels,
                'amazon_order_ids' => $amzOrderIds,
                'max_results_per_page' => $maxResultsPerPage,
            ));
            return $this->getListAll($requestBody, 'Orders');
        }

        return AmazonSellerPartnerResponse::badRequest(400, 'Invalid request body!');
    }

    public function apiList(
        $createAfter = null,
        $createBefore = null,
        $lastUpdatedAfter = null,
        $lastUpdateBefore = null,
        $orderStatuses = null,
        $fulfillmentChannels = null,
        $amzOrderIds = null,
        $maxResultsPerPage = 100,
        $nextToken = null
    )
    {
        $amzOrderIds = $amzOrderIds ? array_values($amzOrderIds) : $amzOrderIds;
        $request = new AmazonSPDefOrderRequest((object)array(
            'AmazonOrderIds' => $amzOrderIds,
        ));
        if ($request->validate()) {
            $request = $this->mergeSandboxTestCase(array(
                'marketplace_ids' => $this->marketplaces,
                'created_after' => $createAfter,
                'created_before' => $createBefore,
                'last_updated_after' => $lastUpdatedAfter,
                'last_updated_before' => $lastUpdateBefore,
                'order_statuses' => $orderStatuses,
                'fulfillment_channels' => $fulfillmentChannels,
                'amazon_order_ids' => $amzOrderIds,
                'max_results_per_page' => $maxResultsPerPage,
                'next_token' => $nextToken,
            ));
            return $this->doRequest($request);
        }

        return AmazonSellerPartnerResponse::badRequest(400, 'Invalid request body!');
    }

    /**
     * @param $payload
     * @return AmazonSPDefOrdersList
     */
    public function parsePayload($payload)
    {
        return new AmazonSPDefOrdersList(
            $payload->Orders,
            isset($payload->NextToken) ? $payload->NextToken : '',
            isset($payload->LastUpdatedBefore) ? $payload->LastUpdatedBefore : '',
            isset($payload->CreatedBefore) ? $payload->CreatedBefore : ''
        );
    }

    private function mergeSandboxTestCase($requestBody)
    {
        /**
         * Possible sandbox test case:
         * - 400
         * - TEST_CASE_ORDER_STATUS_PARTIAL_SHIPPED
         * - TEST_CASE_200
         * - TEST_CASE_400
         */
        if ($this->isSandBox) {
            $requestBody['test_case'] = 400;
        }

        return $requestBody;
    }
}
