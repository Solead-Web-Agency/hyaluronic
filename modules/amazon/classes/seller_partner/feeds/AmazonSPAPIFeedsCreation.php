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
class AmazonSPAPIFeedsCreation extends AmazonSPAPIFeeds
{
    private $marketplaceIds;
    private $feedType;
    private $feedContent;
    private $feedOptions;
    private $feedSource;

    public function __construct(
        $connector,
        $marketplaceIds,
        $feedType,
        $feedContent,
        $feedOptions = null,
        $logger = null,
        $devMode = false,
        $isSandBox = false
    ) {
        parent::__construct($connector, self::API_TYPE_FEEDS_SUBMIT, $logger, $devMode, $isSandBox);
        $this->marketplaceIds = $marketplaceIds;
        $this->feedType = $feedType;
        $this->feedContent = $feedContent;
        $this->feedOptions = $feedOptions;
    }

    // I don't want to modify the constructor
    public function setFeedSource($source)
    {
        $this->feedSource = $source;

        return $this;
    }

    public function apiSendFeed()
    {
        $availableFeeds = array(
            self::FEED_TYPE_ORDER_ACKNOWLEDGE,
            self::FEED_TYPE_ORDER_FULFILLMENT,
            self::FEED_TYPE_PRODUCT_DATA,
            self::FEED_TYPE_PRODUCT_IMAGE_DATA,
            self::FEED_TYPE_PRODUCT_PRICING_DATA,
            self::FEED_TYPE_PRODUCT_OVERRIDES_DATA,
            self::FEED_TYPE_PRODUCT_INVENTORY_DATA,
            self::FEED_TYPE_PRODUCT_RELATIONSHIP_DATA,
            self::FEED_TYPE_UPLOAD_VAT_INVOICE,
        );
        if (!in_array($this->feedType, $availableFeeds)) {
            return AmazonSellerPartnerResponse::badRequest(400, 'Incorrect feed!');
        }

        return $this->doRequest(array(
            'marketplace_ids' => $this->marketplaceIds,
            'feed_type' => $this->feedType,
            'feed_content' => $this->feedContent,
            'feed_options' => $this->feedOptions,
            'feed_source' => $this->feedSource,
        ));
    }

    public function parsePayload($payload)
    {
        return new AmazonSPDefCreateFeedResponse($payload);
    }
}
