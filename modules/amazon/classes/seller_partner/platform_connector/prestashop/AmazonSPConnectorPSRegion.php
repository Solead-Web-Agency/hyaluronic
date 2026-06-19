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

class AmazonSPConnectorPSRegion extends AmazonSellerPartnerRegion implements IAmazonSellerPartnerConnector, IAmazonSellerPartnerLicense
{
    /** @var Amazon */
    protected $module;
    protected $psLanguages = array();

    /** @var IAmazonSellerPartnerQuotaHandler */
    protected $quotaHandler;

    public function __construct($region, $module = null)
    {
        $this->module = $module;
        $this->region = $region;

        // Resolve marketplaces & PS languages
        $marketplaces = array();
        $psLanguages = array();
        $activeLanguages = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_ACTIVE);
        $allMarketplacesInRegion = array_column(self::$mkpsByRegion[$region]['marketplaces'], 'mkp');

        if ($activeMarketplaces = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_TO_AMZ_MKP_ID)) {
            $activeMarketplaces = array_filter($activeMarketplaces);
            foreach ($activeMarketplaces as $psLang => $mkp) {
                if (isset($activeLanguages[$psLang]) && $activeLanguages[$psLang]
                    && in_array($mkp, $allMarketplacesInRegion)) {
                    $marketplaces[] = $mkp;
                    $psLanguages[] = $psLang;
                }
            }
            $this->psLanguages = $psLanguages;
        }

        $config = AmazonConfiguration::get(self::DBConfigurationKey($region), null, Shop::getContextShopGroupID(true),Shop::getContextShopID(true));
        if ($config && is_array($config)) {
            if (isset($config['seller_id'], $config['refresh_token'])) {
                parent::__construct(
                    $region,
                    $config['seller_id'],
                    $config['refresh_token'],
                    $marketplaces
                );

                $this->quotaHandler = new AmazonSellerPartnerQuotaHandler();
            }
        }
    }

    public function belongToUnifiedEU()
    {
        return self::belongToUnifiedEUStatic($this->getRegion());
    }

    public static function belongToUnifiedEUStatic($region)
    {
        return $region === AmazonSellerPartnerConstant::MKP_REGION_EU && self::isUnifiedEU();
    }

    public static function isUnifiedEU()
    {
        return !AmazonConfiguration::get(AmazonConstant::CONFIG_AMZ_EU_SEPARATE_SELLER_ACC);
    }

    public function getActivePSLanguages()
    {
        return $this->psLanguages;
    }

    public function getPlatform()
    {
        return 'Prestashop';
    }

    public function getUnauthorizedMessage()
    {
        return 'Unauthorized, please authorize first in the configuration page!';
    }

    public static function DBConfigurationKey($region)
    {
        return AmazonSPConnectorPSConstant::DB_CONFIG_KEY . '_' . Tools::strtoupper($region);
    }

    public function getQuotaHandler()
    {
        return $this->quotaHandler;
        /*return null;*/
    }

    // PS does not rotate endpoint
    public function getEndpointRotation()
    {
        return null;
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

    public function getPlatformVersion()
    {
        return _PS_VERSION_;
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
