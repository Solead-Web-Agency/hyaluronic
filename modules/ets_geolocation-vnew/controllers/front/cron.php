<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

/**
 * Class Ets_GeolocationCronModuleFrontController
 *
 * @property \Ets_geolocation $module
 * @property \Context|\ContextCore $context
 *
 * @mixin \ModuleFrontControllerCore
 *
 * @since 1.1.8
 */
class Ets_GeolocationCronModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will redirected user to login page during init function. */
    public $auth = false;

    public function init()
    {

        parent::init();
        $_getAjaxResponse = static function ($ok, $msg) {
            return json_encode(['success' => $ok, 'message' => $msg]);
        };
        $isAjax = Tools::getValue('ajax');
        $token = Tools::getValue('secure');
        $cronjobToken = Configuration::getGlobalValue('ETS_GEO_CRONJOB_TOKEN');
        $msg = '';
        if ($isAjax) {
            header('Content-Type: application/json');
        }
        if ($token !== $cronjobToken) {
            $msg = $this->module->l('Token mismatched. Aborted.', 'cronjob');
            if ($isAjax) {
                exit($_getAjaxResponse(false, $msg));
            }
            exit($msg);
        }
        if (!Module::isEnabled('ets_geolocation')) {
            $msg = $this->module->l('Module Ets Geolocation not enabled. Aborted', 'cronjob');
            if ($isAjax) {
                exit($_getAjaxResponse(false, $msg));
            }
            exit($msg);
        }
        try {
            EtsGeoIpCityDatabaseUpdater::getInstance()->runUpdate();
            if ($this->module->isGeoLiteCityAvailable()) {
                $msg .= $this->module->l('Update database successfully', 'cronjob') . PHP_EOL;
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                exit($_getAjaxResponse(false, nl2br($e->getMessage())));
            }
            exit($msg);
        }
        Configuration::updateGlobalValue('ETS_GEO_TIME_LOG_CRONJOB', $timeRun = date('Y-m-d H:i:s'));
        $msg .= $this->module->l('Saving last ran cronjob at: ', 'cronjob') . $timeRun;
        if ($isAjax) {
            exit($_getAjaxResponse(true, nl2br($msg)));
        }
        exit($msg);
    }
}
