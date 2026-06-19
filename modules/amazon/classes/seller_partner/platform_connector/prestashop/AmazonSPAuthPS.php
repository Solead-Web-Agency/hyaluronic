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
class AmazonSPAuthPS extends AmazonSPConnectorPSMkp
{
    const STATUS_SUCCESS = 1;
    const STATUS_WRONG_REGION = 1001;
    const STATUS_MALFORMED_INPUT = 1002;
    const STATUS_MISSING_MWS_TOKEN = 1003;
    const STATUS_UPDATE_CONFIG_FAILED = 2001;
    const STATUS_PROPAGATE_CONFIG_FAILED = 2002;

    public function buildOAuthCentralUrl($callbackUri)
    {
        $queryParams = array(
            // Our central app accepts the domain without protocol and trailing slash
            'amazon_url' => preg_replace('/^https:\/\//', '', rtrim($this->oauthUrl, '/')),
            'state' => $this->generateState(),
            'callback_uri' => $callbackUri,
        );
        if (AmazonSellerPartnerConstant::AMZ_APP_IS_DRAFT) {
            $queryParams['version'] = 'beta';
        }

        return AmazonSellerPartnerConstant::OAUTH_CENTRAL_URL . '?' . http_build_query($queryParams);
    }

    protected function generateState()
    {
        $carryingData = http_build_query(array('region' => $this->getRegion()));
        return base64_encode(self::statePrefix() . $carryingData); // TODO: Validation: Need to build Amazon state
    }

    protected static function statePrefix()
    {
        return 'prestashop::' . filemtime(__FILE__) . '::';
    }

    /**
     * Authorized seller partner information with "Authorized button"
     * @param $state
     * @param $sellerId
     * @param $accessToken
     * @param $refreshToken
     * @param $mkpId
     * @return int
     */
    public static function verifyState($state, $sellerId, $accessToken, $refreshToken, $mkpId)
    {
        if ($state && $sellerId && $accessToken && $refreshToken) {
            $stateCarryingData = self::parseState($state);
            if (count($stateCarryingData) && isset($stateCarryingData['region'])) {
                $region = $stateCarryingData['region'];
                $isVerify = self::saveVerifyInfo($sellerId, $refreshToken, $mkpId, $region);

                $spRegion = new self($region, $sellerId, $refreshToken, $mkpId);
                if ($isVerify != self::STATUS_SUCCESS && !$spRegion->afterVerifyState($sellerId)) {
                    return self::STATUS_PROPAGATE_CONFIG_FAILED;
                }

                return self::STATUS_SUCCESS;
            }
        }

        return self::STATUS_MALFORMED_INPUT;
    }

    protected static function parseState($state)
    {
        $stateDecoded = base64_decode($state);  // TODO: Validation: Need to get Amazon state
        $statePrefix = self::statePrefix();

        if (strpos($stateDecoded, $statePrefix) === 0) {
            // Seems like a correct state
            $carryingData = str_replace($statePrefix, '', $stateDecoded);
            parse_str($carryingData, $data);

            return is_array($data) ? $data : array();
        }

        return array();
    }

    // Save authorization data (seller id) into legacy fields (all active mkps (PS lang) in region)
    protected function afterVerifyState($sellerId)
    {
        $psLangToIso = AmazonConfiguration::get(AmazonConstant::CONFIG_LANG_TO_REGION);
        if (is_array($psLangToIso)) {
            $success = true;
            foreach ($psLangToIso as $psIdLang => $countryIso) {
                if (in_array($countryIso, $this->allIsoCodesInRegion())) {
                    $success = $success && self::updateAmzCredentialsIntoPsLang($psIdLang, $sellerId);
                }
            }

            return $success;
        }

        return false;
    }

    public static function updateAmzCredentialsIntoPsLang($psIdLang, $sellerId)
    {
        $tobeModifiedConfiguration = array(
            AmazonConstant::MERCHANT_ID => $sellerId,
        );

        $success = true;
        foreach ($tobeModifiedConfiguration as $configKey => $value) {
            $savedData = AmazonConfiguration::get($configKey);
            if (is_array($savedData)) {
                $savedData[$psIdLang] = $value;
            } else {
                $savedData = array($psIdLang => $value);
            }
            $success = $success && AmazonConfiguration::updateValue($configKey, $savedData);
        }

        return $success;
    }

    /**
     * Override authorize - For dev mode
     * @param $sellerId
     * @param $refreshToken
     * @param $mkpId
     * @return int
     * @throws Exception
     */
    public static function updateAuthValues($sellerId, $refreshToken, $mkpId)
    {
        if ($sellerId && $refreshToken) {
            $spAuth = AmazonSPAuthPS::initFromMarketplace($mkpId);
            $region = $spAuth->getRegion();
            return self::saveVerifyInfo($sellerId, $refreshToken, $mkpId, $region);
        }

        return self::STATUS_MALFORMED_INPUT;
    }

    /**
     * Save Authorized information
     * @param $sellerId
     * @param $refreshToken
     * @param $mkpId
     * @param $region
     * @return int
     */
    private static function saveVerifyInfo($sellerId, $refreshToken, $mkpId, $region)
    {
        $oldData = AmazonConfiguration::get(AmazonSPConnectorPSMkp::DBConfigurationKey($region));
        if (!in_array($region, array_keys(self::$mkpsByRegion))) {
            return self::STATUS_WRONG_REGION;
        }

        if (AmazonSPConnectorPSRegion::belongToUnifiedEUStatic($region)) {
            $saveData = array(
                'seller_id' => $sellerId,
                'refresh_token' => $refreshToken
            );
        } else {
            $oldData[$mkpId] = array(
                'seller_id' => $sellerId,
                'refresh_token' => $refreshToken
            );
            $saveData = $oldData;
        }

        $isUpdate = AmazonConfiguration::updateValue(AmazonSPConnectorPSMkp::DBConfigurationKey($region), $saveData);
        if (!$isUpdate) {
            return self::STATUS_UPDATE_CONFIG_FAILED;
        }

        return self::STATUS_SUCCESS;
    }
}
