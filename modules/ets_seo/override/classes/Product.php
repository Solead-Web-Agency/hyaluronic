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

class Product extends ProductCore
{
    public function getAnchor($id_product_attribute, $with_id = false)
    {
        if(Module::isEnabled('ets_seo')
            && (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')
            && (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS')){
            return '';
        }
        return parent::getAnchor($id_product_attribute, $with_id);
    }

}