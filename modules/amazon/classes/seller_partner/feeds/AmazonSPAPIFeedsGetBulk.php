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
class AmazonSPAPIFeedsGetBulk extends AmazonSellerPartnerAPI
{
    private $feedTypes;
    private $marketplaceIds;
    private $pageSize;
    private $processingStatuses;
    private $createdSince;
    private $createdUntil;
    private $nextToken;

    public function __construct($connector, $data, $logger = null, $devMode = false, $isSandBox = false)
    {
        $this->feedTypes = isset($data['feed_types']) ? $data['feed_types'] : null;
        $this->marketplaceIds = (isset($data['marketplace_ids']) && is_array($data['marketplace_ids']) && !empty($data['marketplace_ids'])) ? $data['marketplace_ids'] : array($connector->getMarketplace());
        $this->pageSize = isset($data['page_size']) ? $data['page_size'] : 10;
        $this->processingStatuses = isset($data['processing_statuses']) ? $data['processing_statuses'] : null;
        $this->createdSince = isset($data['created_since']) ? $data['created_since'] : null;
        $this->createdUntil = isset($data['created_until']) ? $data['created_until'] : null;
        $this->nextToken = isset($data['next_token']) ? $data['next_token'] : null;
        parent::__construct($connector, self::API_TYPE_FEEDS_GET_BULK, $logger, $devMode, $isSandBox);
    }

    /**
     * @return Generator
     */
    public function apiGetAll()
    {
        return $this->getListAll(array(
            'feed_types' => $this->feedTypes,
            'marketplace_ids' => $this->marketplaceIds,
            'page_size' => $this->pageSize,
            'processing_statuses' => $this->processingStatuses,
            'created_since' => $this->createdSince,
            'created_until' => $this->createdUntil,
            'next_token' => $this->nextToken
        ), 'feeds');
    }

    /**
     * @param $payload
     * @return AmazonSPDefFeedsList
     */
    public function parsePayload($payload)
    {
        return new AmazonSPDefFeedsList($payload);
    }
}