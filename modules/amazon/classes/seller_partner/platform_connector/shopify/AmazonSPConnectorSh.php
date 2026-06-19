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
class AmazonSPConnectorSh extends AmazonSellerPartnerRegion implements IAmazonSellerPartnerConnector
{
    protected $configuration;
    protected $name;
    protected $marketplace_id;
    protected $region;
    protected $seller_id;
    protected $spapi_refresh_token;

    /** @var IAmazonSellerPartnerQuotaHandler */
    protected $quotaHandler;

    /** @var AmazonSellerPartnerEndpointRotation */
    protected $endpointRotationHandler;

    public function __construct($storeId, $configuration, $overrideParams = array())
    {
        if (!empty($overrideParams)) {
            foreach ($overrideParams as $property => $value) {
                $this->$property = $value;
            }
        } else {
            $connection = UserConfig::getSection($storeId, $configuration, 'amazon');
            foreach ($connection as $property => $value) {
                $this->$property = $value;
            }
        }
        
        parent::__construct($this->getSpRegion(), $this->seller_id, $this->spapi_refresh_token, [$this->marketplace_id]);

        $this->quotaHandler = new AmazonSellerPartnerQuotaHandler();
        $this->endpointRotationHandler = new AmazonSellerPartnerEndpointRotation();
    }

    private function getSpRegion()
    {
        switch ($this->region) {
            case 'na':
                return AmazonSellerPartnerConstant::MKP_REGION_NA;
            case 'eu':
                return AmazonSellerPartnerConstant::MKP_REGION_EU;
            case 'east':
                return AmazonSellerPartnerConstant::MKP_REGION_FE;
            default:
                return $this->region;
        }
    }

    public function getPlatform()
    {
        return 'Shopify';
    }

    public function getUnauthorizedMessage()
    {
        return 'Unauthorized, please authorize first in the Connect tab!';
    }

    public function getQuotaHandler()
    {
        return $this->quotaHandler;
    }

    public function getEndpointRotation()
    {
        return $this->endpointRotationHandler;
    }

    public function getMarketplace()
    {
        return $this->marketplace_id;
    }
}
