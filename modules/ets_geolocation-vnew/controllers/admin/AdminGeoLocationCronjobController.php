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
 * Class AdminGeoLocationCronjobController
 *
 * @property \Ets_geolocation $module
 * @property \Context|\ContextCore $context
 *
 * @mixin \ModuleAdminControllerCore
 *
 * @since 1.1.8
 */
class AdminGeoLocationCronjobController extends ModuleAdminController
{
    /**
     * AdminGeoLocationCronjobController constructor.
     *
     * @throws \PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $urlLink = EtsGeoDefine::getInstance()->getAdminLink(['control' => 'cronjob']);
        if (!$this->module->is17) {
            $urlLink = $this->module->getAdminBaseLink() . basename(_PS_ADMIN_DIR_) . '/' . $urlLink;
        }
        Tools::redirect($urlLink);
    }
}
