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
class AmazonSPAPIFeedsGetById extends AmazonSPAPIFeeds
{
    protected $responseWrapper = 'payload';

    private $feedId;

    public function __construct($connector, $feedId, $logger = null, $devMode = false, $isSandBox = false)
    {
        $this->feedId = $feedId;
        parent::__construct($connector, self::API_TYPE_FEEDS_GET_BY_ID, $logger, $devMode, $isSandBox);
    }

    public function apiGet()
    {
        return $this->doRequest(array('feed_id' => $this->feedId));
    }

    public function parsePayload($payload)
    {
        if (isset($payload->feedId)) {
            return new AmazonSPDefFeed($payload);
        }
        if (isset($payload->resource_url) || isset($payload->url)) {
            return new AmazonSPDefFeedDocument($payload);
        }
        return AmazonSellerPartnerResponse::serverError($payload);
    }
}
