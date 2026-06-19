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
require_once dirname(__FILE__) . '/../../../AmazonSellerPartnerQuotaHandler.php';

class AmazonSPConnectorPSMkp extends AmazonSellerPartnerMarketplace implements IAmazonSellerPartnerConnector, IAmazonSellerPartnerLicense
{
    /** @var Amazon */
    protected $module;

    protected $psLanguage = 0;

    /** @var Currency|null */
    protected $currency;

    /** @var IAmazonSellerPartnerQuotaHandler */
    protected $quotaHandler;

    public function __construct($region, $sellerId, $refreshToken, $marketplaceId)
    {
        parent::__construct($region, $sellerId, $refreshToken, $marketplaceId);
        $this->quotaHandler = new AmazonSellerPartnerQuotaHandler();
    }

    public static function initFromMarketplace($mkpID, $module = null, $auths = null)
    {
        if (!$mkpID) {
            throw new Exception("Empty marketplace ID");
        }

        return self::init($mkpID, $module, $auths);
    }

    public static function initFromCountryIso($iso, $module = null)
    {
        if (!$iso) {
            throw new Exception("Empty marketplace ISO");
        }

        $marketPlaceId = AmazonTools::lang2MarketplaceId($iso);
        return self::init($marketPlaceId, $module);
    }

    /**
     * @param $mkpId
     * @param $module
     * @return static
     * @throws Exception
     */
    protected static function init($mkpId, $module, $auths = null)
    {
        foreach (self::$mkpsByRegion as $region => $regionData) {
            foreach ($regionData['marketplaces'] as $marketplace) {
                if ($marketplace['mkp'] == $mkpId) {
                    $config = AmazonConfiguration::get(self::DBConfigurationKey($region),null, Shop::getContextShopGroupID(true),Shop::getContextShopID(true));
                    $sellerId = '';
                    $refreshToken = '';

                    if ($auths) { // Dev mode: Check Connectivity
                        $sellerId = isset($auths['seller_id']) ? $auths['seller_id'] : '';
                        $refreshToken = isset($auths['refresh_token']) ? $auths['refresh_token'] : '';
                    } else {
                        if ($config && is_array($config)) {
                            if (isset($config[$mkpId], $config[$mkpId]['seller_id'], $config[$mkpId]['refresh_token'])) {
                                $sellerId = $config[$mkpId]['seller_id'];
                                $refreshToken = $config[$mkpId]['refresh_token'];
                            } elseif (isset($config['seller_id'], $config['refresh_token'])) {
                                // Fallback to the unified configuration
                                $sellerId = $config['seller_id'];
                                $refreshToken = $config['refresh_token'];
                            }
                        }
                    }

                    // Create instance with as much data as possible
                    $instance = new static($region, $sellerId, $refreshToken, $mkpId);
                    $instance->iso = $marketplace['iso'];
                    $instance->oauthUrl = $marketplace['oauth'];
                    $instance->psLanguage = self::initPSLanguage($mkpId);
                    $instance->currency = self::initCurrency($instance->psLanguage);
                    $instance->module = $module;

                    return $instance;
                }
            }
        }

        throw new Exception("Amazon: Cannot init marketplace by 'mkp' parameter: $mkpId");
    }

    /**
     * @param $psIdLang
     * @return Currency|null
     */
    protected static function initCurrency($psIdLang)
    {
        $currencies = AmazonConfiguration::get('CURRENCY');

        if (isset($currencies[$psIdLang])) {
            $currency = new Currency(Currency::getIdByIsoCode($currencies[$psIdLang]));
            if (Validate::isLoadedObject($currency)) {
                return $currency;
            }
        }

        $currency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));
        if (Validate::isLoadedObject($currency)) {
            return $currency;
        }

        return null;
    }

    protected static function initPSLanguage($mkpId)
    {
        if ($activeMarketplaces = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_TO_AMZ_MKP_ID)) {
            $activeMarketplaces = array_filter($activeMarketplaces);
            $psLanguage = array_search($mkpId, $activeMarketplaces);
            if ($psLanguage !== false) {
                return $psLanguage;
            }
        }

        return 0;
    }

    public function belongToUnifiedEU()
    {
        return AmazonSPConnectorPSRegion::belongToUnifiedEUStatic($this->getRegion());
    }

    public function getPSLanguage()
    {
        return $this->psLanguage;
    }

    public function getActivePSLanguages()
    {
        return array($this->psLanguage);
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return 'Prestashop';
    }

    /**
     * @return string
     */
    public function getUnauthorizedMessage()
    {
        return 'Unauthorized, please authorize first in the configuration page!';
    }

    /**
     * @return Currency|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param $region
     * @return string
     */
    public static function DBConfigurationKey($region)
    {
        return AmazonSPConnectorPSConstant::DB_CONFIG_KEY . '_' . Tools::strtoupper($region);
    }

    /**
     * @return AmazonSellerPartnerQuotaHandler|IAmazonSellerPartnerQuotaHandler
     */
    public function getQuotaHandler()
    {
        return $this->quotaHandler;
        /*return null;*/
    }

    /**
     * PS does not rotate endpoint
     * @return null
     */
    public function getEndpointRotation()
    {
        return null;
    }

    /**
     * Build platform data for Amazon Web Service
     *
     * @return array[]
     */
    public function buildMarketplacePlatform()
    {
        $platforms = array();
        if (self::isEUMarketPlace($this->getRegion()) && $this->getMarketplaceId() != AmazonSellerPartnerConstant::MKP_UK) {
            $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
            foreach ($marketPlaceIds as $marketPlaceId) {
                // UK Exception
                // Ignore current marketplace
                if (in_array($marketPlaceId, array(AmazonSellerPartnerConstant::MKP_UK, $this->getMarketplaceId()))) {
                    continue;
                }
                $platforms[] = $marketPlaceId;
            }
        }
        return array(
            'auth' => array(
                'MerchantID' => $this->getSellerId(),
                'MarketplaceID' => $this->getMarketplaceId(),
            ),
            'params' => array(
                'Currency' => $this->getCurrency()->iso_code,
                'Country' => $this->getIso(),
            ),
            'platforms' => $platforms,
        );
    }

    public function getPlatformVersion()
    {
        return _PS_VERSION_;
    }

    public function getConnectorVersion()
    {
        if ($this->module) {
            return $this->module->version;
        }

        $module = Module::getInstanceByName('amazon');
        if ($module) {
            return $module->version;
        }

        return '';
    }

    public function getDomain()
    {
        $shop = Context::getContext()->shop;
        if ($domainSSL = $shop->domain_ssl) {
            return $domainSSL;
        }

        if ($domain = $shop->domain) {
            return $domain;
        }

        return $shop->getBaseURL();
    }

    public function getLicense()
    {
        return AmazonConfiguration::get(AmazonConstant::CONFIG_PS_ORDER_ID);
    }

    public function getIP()
    {
        $host = gethostname();
        if ($host) {
            $ip = gethostbyname($host);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                return $ip;
            }
        }

        return '';
    }
}
