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
class Link extends LinkCore
{
      
    /*
    * module: ets_seo
    * date: 2026-06-23 12:18:22
    * version: 2.4.2
    */
    public function getLangLink($idLang = null, Context $context = null, $idShop = null)
    {
        $langLink = parent::getLangLink($idLang, $context, $idShop);
        if (!$context) {
            $context = Context::getContext();
        }
        if (!$idLang) {
            $idLang = $context->language->id;
        }
        if(Language::isMultiLanguageActivated($idShop) && (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL') && $idLang == (int)Configuration::get('PS_LANG_DEFAULT')){
            return '';
        }
        return $langLink;
    }
}