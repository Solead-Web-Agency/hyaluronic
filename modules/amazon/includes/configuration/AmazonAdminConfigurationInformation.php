<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }
/**
 * todo: Migrate all admin configuration > Information to this class
 */
class AmazonAdminConfigurationInformation
{
    const TRANS_PING = 'ping';

    private $shopUrl;
    private $idLang;
    private $instantToken;
    private $errorClassName;

    private $seemToBeConfigured = true;
    private $needMigration = false;

    private $translation = array();

    public function __construct($shopUrl, $idLang, $instantToken, $errorClassName, $seemToBeConfigured, $needMigration)
    {
        $this->shopUrl = $shopUrl;
        $this->idLang = $idLang;
        $this->instantToken = $instantToken;
        $this->errorClassName = $errorClassName;
        $this->seemToBeConfigured = $seemToBeConfigured;
        $this->needMigration = $needMigration;
    }

    public function setTranslation($translation)
    {
        $this->translation = $translation;
    }

    public function infoInitContent()
    {
        return array_filter(array(
            'ping' => $this->amazonPing(),
        ));
    }

    private function amazonPing()
    {
        if (!$this->needMigration && $this->seemToBeConfigured) {
            $mkpToTry = '';
            // Get any authenticated marketplace
            $mkpIDs = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_TO_AMZ_MKP_ID);
            if ($mkpIDs && is_array($mkpIDs)) {
                foreach ($mkpIDs as $mkpID) {
                    if ($mkpID) {
                        $spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($mkpID);
                        if ($spMkp->isAuthenticated()) {
                            $mkpToTry = $mkpID;
                            break;
                        }       
                    }
                }
            }

            // If there is no configured marketplace, don't need to ping
            if ($mkpToTry) {
                $queryStr = array(
                    'instant_token' => $this->instantToken,
                    'action' => 'service-status',
                    'marketplace_id' => $mkpToTry,
                );
                $ping_url = $this->shopUrl . 'functions/check.php?' . http_build_query($queryStr);
                $ping_debug_url = AmazonTools::getHttpHost(true, true) . $ping_url . '&debug=1';

                return array(
                    'message' => sprintf($this->translation[self::TRANS_PING], $ping_debug_url),
                    'level' => $this->errorClassName,
                    'display' => false,
                    'tutorial' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PING),
                    'script' => array(
                        'name' => 'service_check_url',
                        'url' => $ping_url
                    ),
                );
            }
        }

        return array();
    }
}
