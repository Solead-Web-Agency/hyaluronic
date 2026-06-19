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
class AmazonSellerPartnerRegion
{
    protected static $mkpsByRegion = array(
        AmazonSellerPartnerConstant::MKP_REGION_EU => array(
            'sp_region' => AmazonSellerPartnerConstant::MKP_REGION_EU,
            'marketplaces' => array(
                // supposed to be EU in the old version
                array('mkp' => AmazonSellerPartnerConstant::MKP_FR, 'iso' => 'fr', 'oauth' => 'https://sellercentral-europe.amazon.com/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_ES, 'iso' => 'es', 'oauth' => 'https://sellercentral-europe.amazon.com/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_DE, 'iso' => 'de', 'oauth' => 'https://sellercentral-europe.amazon.com/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_IT, 'iso' => 'it', 'oauth' => 'https://sellercentral-europe.amazon.com/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_UK, 'iso' => 'uk', 'oauth' => 'https://sellercentral-europe.amazon.com/'),  // UK should be gb, use uk to match the `platform`
                array('mkp' => AmazonSellerPartnerConstant::MKP_NL, 'iso' => 'nl', 'oauth' => 'https://sellercentral.amazon.nl/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_SE, 'iso' => 'se', 'oauth' => 'https://sellercentral.amazon.se/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_PL, 'iso' => 'pl', 'oauth' => 'https://sellercentral.amazon.pl/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_BE, 'iso' => 'be', 'oauth' => 'https://sellercentral.amazon.com.be/'), // https://developer-docs.amazon.com/sp-api/docs/seller-central-urls
                array('mkp' => AmazonSellerPartnerConstant::MKP_ZA, 'iso' => 'za', 'oauth' => 'https://sellercentral.amazon.co.za/'),
            ),
        ),
        AmazonSellerPartnerConstant::MKP_REGION_NA => array(
            'sp_region' => AmazonSellerPartnerConstant::MKP_REGION_NA,
            'marketplaces' => array(
                array('mkp' => AmazonSellerPartnerConstant::MKP_CA, 'iso' => 'ca', 'oauth' => 'https://sellercentral.amazon.ca/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_US, 'iso' => 'us', 'oauth' => 'https://sellercentral.amazon.com/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_MX, 'iso' => 'mx', 'oauth' => 'https://sellercentral.amazon.com.mx/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_BR, 'iso' => 'br', 'oauth' => 'https://sellercentral.amazon.com.br/'),      
            ),
        ),
        AmazonSellerPartnerConstant::MKP_REGION_FE => array(
            'sp_region' => AmazonSellerPartnerConstant::MKP_REGION_FE,
            'marketplaces' => array(
                array('mkp' => AmazonSellerPartnerConstant::MKP_SG, 'iso' => 'sg', 'oauth' => 'https://sellercentral.amazon.sg/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_AU, 'iso' => 'au', 'oauth' => 'https://sellercentral.amazon.com.au/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_JP, 'iso' => 'jp', 'oauth' => 'https://sellercentral.amazon.co.jp/'),      
            ),
        ),
        AmazonSellerPartnerConstant::MKP_REGION_EU_2 => array(
            'sp_region' => AmazonSellerPartnerConstant::MKP_REGION_EU,
            'marketplaces' => array(
                array('mkp' => AmazonSellerPartnerConstant::MKP_AE, 'iso' => 'ae', 'oauth' => 'https://sellercentral.amazon.ae/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_SA, 'iso' => 'sa', 'oauth' => 'https://sellercentral-europe.amazon.com/'),   // SA doesn't have oauth, use of EU instead
                array('mkp' => AmazonSellerPartnerConstant::MKP_EG, 'iso' => 'eg', 'oauth' => 'https://sellercentral-europe.amazon.com/'),   // EG doesn't have oauth, use of EU instead
                array('mkp' => AmazonSellerPartnerConstant::MKP_TR, 'iso' => 'tr', 'oauth' => 'https://sellercentral.amazon.com.tr/'),
                array('mkp' => AmazonSellerPartnerConstant::MKP_IN, 'iso' => 'in', 'oauth' => 'https://sellercentral.amazon.in/'),      
            ),
        ),
    );

    protected $region;

    protected $sellerId;
    protected $refreshToken;
    protected $accessToken;

    private $isAuthenticated;
    private $marketplaces;

    public function __construct($region, $sellerId, $refreshToken, $marketplaces = array())
    {
        $this->region = $region;
        $this->sellerId = $sellerId;
        $this->refreshToken = $refreshToken;
        $this->marketplaces = $marketplaces;

        $this->isAuthenticated = $this->sellerId && $this->refreshToken;
    }

    public function regionEU()
    {
        return in_array($this->getRegion(), array(
            AmazonSellerPartnerConstant::MKP_REGION_EU,
            AmazonSellerPartnerConstant::MKP_REGION_EU_2,
        ));
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function resolveSpRegion()
    {
        return self::$mkpsByRegion[$this->getRegion()]['sp_region'];
    }

    public function getSellerId()
    {
        return $this->sellerId;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    public function getMarketplaces()
    {
        return $this->marketplaces;
    }

    public function allIsoCodesInRegion()
    {
        return array_column(self::$mkpsByRegion[$this->getRegion()]['marketplaces'], 'iso');
    }

    public static function isEUMarketPlace($region)
    {
        return $region == AmazonSellerPartnerConstant::MKP_REGION_EU;
    }
}
