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
abstract class AmazonSPAPIFeeds extends AmazonSellerPartnerAPI
{
    const FEED_TYPE_ORDER_ACKNOWLEDGE = 'POST_ORDER_ACKNOWLEDGEMENT_DATA';
    const FEED_TYPE_ORDER_FULFILLMENT = 'POST_ORDER_FULFILLMENT_DATA';
    const FEED_TYPE_PRODUCT_DATA = 'POST_PRODUCT_DATA';
    const FEED_TYPE_PRODUCT_IMAGE_DATA = 'POST_PRODUCT_IMAGE_DATA';
    const FEED_TYPE_PRODUCT_PRICING_DATA = 'POST_PRODUCT_PRICING_DATA';
    const FEED_TYPE_PRODUCT_OVERRIDES_DATA = 'POST_PRODUCT_OVERRIDES_DATA';
    const FEED_TYPE_PRODUCT_INVENTORY_DATA = 'POST_INVENTORY_AVAILABILITY_DATA';
    const FEED_TYPE_PRODUCT_RELATIONSHIP_DATA = 'POST_PRODUCT_RELATIONSHIP_DATA';
    const FEED_TYPE_UPLOAD_VAT_INVOICE = 'UPLOAD_VAT_INVOICE';
}
