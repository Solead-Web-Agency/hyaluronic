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

class AmazonSPAPICatalog extends AmazonSellerPartnerAPI
{
    const IDENTIFIERS_TYPE_EAN = 'EAN'; // European Article Number.
    const IDENTIFIERS_TYPE_UPC = 'UPC'; // Universal Product Code.
    const IDENTIFIERS_TYPE_SKU = 'SKU'; // Stock Keeping Unit. Must be accompanied by `sellerId`.
    const IDENTIFIERS_TYPE_ASIN = 'ASIN'; // Amazon Standard Identification Number.
    const IDENTIFIERS_TYPE_GTIN = 'GTIN'; // Global Trade Item Number.
    const IDENTIFIERS_TYPE_ISBN = 'ISBN'; // International Standard Book Number.
    const IDENTIFIERS_TYPE_JAN = 'JAN'; // Japanese Article Number.
    const IDENTIFIERS_TYPE_MINSAN = 'MINSAN'; // Minsan Code.

    const INCLUDED_DATA_ATTRIBUTES = 'attributes';
    const INCLUDED_DATA_DIMENSIONS = 'dimensions';
    const INCLUDED_DATA_IDENTIFIERS = 'identifiers';
    const INCLUDED_DATA_IMAGES = 'images';
    const INCLUDED_DATA_PRODUCT_TYPES = 'productTypes';
    const INCLUDED_DATA_RELATIONSHIPS = 'relationships';
    const INCLUDED_DATA_SALES_RANKS = 'salesRanks';
    const INCLUDED_DATA_SUMMARY = 'summaries';

    const IDENTIFIERS_MAX = 20;
    const PAGE_SIZE_MAX = 20;

    protected $marketplaces = array();

    public function __construct($connector, $marketplaces, $logger = null, $devMode = false, $isSandBox = false)
    {
        if (!is_array($marketplaces)) {
            $marketplaces = array($marketplaces);
        }
        $this->marketplaces = $marketplaces;
        parent::__construct($connector, self::API_TYPE_CATALOG_SEARCH, $logger, $devMode, $isSandBox);
    }

    public function includedDataAll()
    {
        return array(
            self::INCLUDED_DATA_ATTRIBUTES,
            self::INCLUDED_DATA_DIMENSIONS,
            self::INCLUDED_DATA_IDENTIFIERS,
            self::INCLUDED_DATA_IMAGES,
            self::INCLUDED_DATA_PRODUCT_TYPES,
            self::INCLUDED_DATA_RELATIONSHIPS,
            self::INCLUDED_DATA_SALES_RANKS,
            self::INCLUDED_DATA_SUMMARY,
        );
    }

    /**
     * @param array $identifiers
     * @param string $identifiersType
     * @return AmazonSellerPartnerResponse
     */
    public function apiSearch($identifiers, $identifiersType, $sellerId = null, $includedData = array(self::INCLUDED_DATA_SUMMARY), $pageSize = self::PAGE_SIZE_MAX)
    {
        $identifiers = array_filter($identifiers);
        if (!$identifiers) {
            return AmazonSellerPartnerResponse::badRequest(400, 'Nothing to search!');
        }

        return $this->doRequest(array(
            'marketplace_ids' => $this->marketplaces,
            'identifiers' => $identifiers,
            'identifiers_type' => $identifiersType,
            'seller_id' => $sellerId,
            'included_data' => $includedData,
            'page_size' => $pageSize,
        ));
    }

    public function apiSearchByKeywords($keywords, $locale = null, $includedData = array('summaries'))
    {
        $keywords = array_filter($keywords);
        if (!$keywords) {
            return AmazonSellerPartnerResponse::badRequest(400, 'Nothing to search!');
        }

        return $this->doRequest(array(
            'marketplace_ids' => $this->marketplaces,
            'keywords' => $keywords,
            'keywords_locale' => $locale,
            'included_data' => $includedData,
        ));
    }

    public function parsePayload($payload)
    {
        return new AmazonSPDefItemSearchResults($payload);
    }
}
