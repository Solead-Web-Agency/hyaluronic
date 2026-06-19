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

class AdminManufacturersController extends AdminManufacturersControllerCore
{
    public function __construct()
    {
        parent::__construct();
        $ets_seo = Module::getInstanceByName('ets_seo');
        $this->fields_list = array_merge($this->fields_list, $ets_seo->get_fields_list_page('esm'));
    }

    public function initListManufacturer()
    {
      
        $this->addRowAction('view');
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->_select = '
			COUNT(`id_product`) AS `products`, (
				SELECT COUNT(ad.`id_manufacturer`) as `addresses`
				FROM `'._DB_PREFIX_.'address` ad
				WHERE ad.`id_manufacturer` = a.`id_manufacturer`
					AND ad.`deleted` = 0
				GROUP BY ad.`id_manufacturer`) as `addresses`';
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product` p ON (a.`id_manufacturer` = p.`id_manufacturer`)';
        $this->_group = 'GROUP BY a.`id_manufacturer`';

        $this->_select .= ', esm.key_phrase,
            esm.seo_score,
            esm.readability_score';
            
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'ets_seo_manufacturer` esm ON (a.`id_manufacturer` = esm.`id_manufacturer` 
                                                AND esm.`id_shop` = '.(int)$this->context->shop->id.' 
                                                AND esm.`id_lang` = '.(int)$this->context->language->id.')';

        $this->context->smarty->assign('title_list', $this->trans('List of brands', array(), 'Admin.Catalog.Feature'));

        $this->content .= AdminController::renderList();
    }
   
    
    public function processFilter()
    {
        parent::processFilter();
        $ets_seo = Module::getInstanceByName('ets_seo');
        $ets_seo->actionAdminManufacturerChangeFilter($this->_filter, 'esm');
    }
}