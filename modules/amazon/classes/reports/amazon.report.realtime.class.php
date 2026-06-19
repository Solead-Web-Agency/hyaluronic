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


require_once(dirname(__FILE__) . '/../amazon.report.class.php');

abstract class AmazonReportRealTime extends AmazonReport
{
    protected $autoStartOver = false;
    protected $realtimeLatency = 20;

    public function initialize()
    {
        // Instant token
        if (!$this->token || $this->token != Configuration::get(AmazonConstant::GB_CONFIG_INSTANT_TOKEN, null, 0, 0)) {
            self::$errors[] = 'Wrong token';
            return false;
        }

        if ($this->amzMarketplace) {
            $this->spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($this->amzMarketplace);

            return parent::initialize();
        } elseif ($this->amzLang) {
            $marketPlaceIds = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_LANG_TO_AMZ_MKP_ID);
            if (isset($marketPlaceIds[$this->amzLang])) {
                $marketplaceId = trim($marketPlaceIds[$this->amzLang]);
                $this->spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($marketplaceId);

                return parent::initialize();
            }
        }

        self::$errors[] = 'No selected language, nothing to do...';
        return false;
    }

    protected function resolveCurrentStepInfo()
    {
        $this->step = Tools::getValue('step', self::STEP_CREATE_REPORT);
        $this->stepData = Tools::getValue('data', array());
    }

    // Nothing special
    protected function afterResolveNextStep()
    {
    }
}
