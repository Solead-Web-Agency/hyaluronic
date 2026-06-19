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

class AdminSuppliersController extends AdminSuppliersControllerCore
{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_select .= ', ess.key_phrase,
            ess.seo_score,
            ess.readability_score';
        
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'ets_seo_supplier` ess ON (a.`id_supplier` = ess.`id_supplier` 
                                                AND ess.`id_shop` = '.(int)$this->context->shop->id.' 
                                                AND ess.`id_lang` = '.(int)$this->context->language->id.')';
        $ets_seo = Module::getInstanceByName('ets_seo');
        $this->fields_list = array_merge($this->fields_list, $ets_seo->get_fields_list_page('ess'));
    }

    public function processFilter()
    {
        parent::processFilter();
        $ets_seo = Module::getInstanceByName('ets_seo');
        $ets_seo->actionAdminSupplierChangeFilter($this->_filter, 'ess');
    }
}