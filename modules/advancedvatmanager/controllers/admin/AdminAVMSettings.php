<?php
/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */
 
class AdminAVMSettingsController extends ModuleAdminController
{
    /**
     * AdminAVMSettingsController::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * AdminAVMSettingsController::initContent()
     * 
     * @return
     */
    public function initContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', false) . '&configure=advancedvatmanager&tab_module=others&module_name=advancedvatmanager&token=' . Tools::getAdminTokenLite('AdminModules'));
        parent::initContent();
    }
}