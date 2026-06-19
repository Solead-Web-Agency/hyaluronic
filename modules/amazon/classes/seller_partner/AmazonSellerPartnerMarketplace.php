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
class AmazonSellerPartnerMarketplace extends AmazonSellerPartnerRegion
{
    protected $marketplace;
    protected $iso;
    protected $oauthUrl;
    // todo: Let children set this up?
    protected $currency;

    public function __construct($region, $sellerId, $refreshToken, $marketplaceId)
    {
        parent::__construct($region, $sellerId, $refreshToken, array($marketplaceId));
        $this->marketplace = $marketplaceId;
    }

    public function getMarketplaces()
    {
        return array($this->getMarketplaceId());
    }

    public function getMarketplaceId()
    {
        return $this->marketplace;
    }

    public function getIso()
    {
        return $this->iso;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}
