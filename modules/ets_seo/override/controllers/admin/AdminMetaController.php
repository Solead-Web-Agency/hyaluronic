<?php
/**
 * 2007-2021 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2021 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class AdminMetaController extends AdminMetaControllerCore
{

    public function __construct()
    {
        parent::__construct();
        
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'ets_seo_meta` esm ON (a.`id_meta` = esm.`id_meta` 
                                                AND esm.`id_shop` = '.(int)$this->context->shop->id.' 
                                                AND esm.`id_lang` = '.(int)$this->context->language->id.')';
        $ets_seo = Module::getInstanceByName('ets_seo');
        $this->fields_list = array_merge($this->fields_list, $ets_seo->get_fields_list_page('esm'));
        if ((int)Configuration::get('PS_REWRITING_SETTINGS') || (int)Tools::getValue('PS_REWRITING_SETTINGS'))
        {
            if (Configuration::get('PS_REWRITING_SETTINGS')) 
            {
                $this->fields_options['routes']['fields'] = array();
                $seoDef = Ets_Seo_Define::getInstance();
                $config_extra = $seoDef->fields_config()['ps_extra'];

                $this->fields_options['routes']['fields'] = $config_extra;
                $this->addAllRouteFields();
            }
        }

        //Remove generate robot.txt file block
        unset($this->fields_options['robots']);
    }

    public function processFilter()
    {
        parent::processFilter();
        $ets_seo = Module::getInstanceByName('ets_seo');
        $ets_seo->actionAdminMetaChangeFilter($this->_filter, 'esm');
    }

}