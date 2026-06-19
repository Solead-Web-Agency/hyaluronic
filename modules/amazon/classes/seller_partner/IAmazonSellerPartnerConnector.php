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
interface IAmazonSellerPartnerConnector
{
    /**
     * @return string
     */
    public function getRefreshToken();

    /**
     * @return string
     */
    public function getRegion();

    public function resolveSpRegion();

    /**
     * @return string
     */
    public function getSellerId();

    /**
     * @return array
     */
    public function getMarketplaces();

    /**
     * @return string
     */
    public function getPlatform();

    /**
     * @return string
     */
    public function getUnauthorizedMessage();

    /**
     * @return IAmazonSellerPartnerQuotaHandler
     */
    public function getQuotaHandler();

    /**
     * @return IAmazonSellerPartnerEndpointRotation|null
     */
    public function getEndpointRotation();
}
