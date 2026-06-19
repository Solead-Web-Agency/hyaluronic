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

abstract class AmazonReportCronjob extends AmazonReport
{
    abstract protected function runOrderConfigKey();

    public function initialize()
    {
        // Cron token
        if (!AmazonTools::checkToken($this->token)) {
            self::$errors[] = 'Wrong Token';
            return false;
        }

        if ($this->amzMarketplace) {
            $this->spMkp = AmazonSPConnectorPSMkp::initFromMarketplace($this->amzMarketplace);
        } elseif ($this->amzLang) {
            // todo: Deprecated, use mkp instead
            $this->spMkp = AmazonSPConnectorPSMkp::initFromCountryIso($this->amzLang);
        } else {
            self::$errors[] = 'No selected language, nothing to do...';
            return false;
        }

        return parent::initialize();
    }

    protected function resolveCurrentStepInfo()
    {
        $runOrder = AmazonConfiguration::get($this->runOrderConfigKey());
        $mkp = $this->spMkp->getMarketplaceId();

        if (!$runOrder || !is_array($runOrder)
            || !isset($runOrder[$mkp]) || !$runOrder[$mkp]
            || !is_array($runOrder[$mkp]) || !count($runOrder[$mkp])
            || !isset($runOrder[$mkp]['step'])) {
            $this->step = self::STEP_CREATE_REPORT;
        } else {
            $this->step = $runOrder[$mkp]['step'];
            $this->stepData = $runOrder[$mkp]['data'];
            if (isset($runOrder[$mkp]['last_time'])) {
                $this->lastRequestTime = $runOrder[$mkp]['last_time'];
            }
        }
    }

    /**
     * Save next run information into DB,
     * carry the last request time also if the request is initialization (create report)
     */
    protected function afterResolveNextStep()
    {
        $savedRunOrder = AmazonConfiguration::get($this->runOrderConfigKey());
        $data = array('step' => $this->nextStep, 'data' => $this->nextStepData);
        if ($this->lastRequestTime) {
            $data['last_time'] = $this->lastRequestTime;
        }
        $savedRunOrder[$this->spMkp->getMarketplaceId()] = $data;
        AmazonConfiguration::updateValue($this->runOrderConfigKey(), $savedRunOrder);
    }
}
