<?php
/**
 * 2007-2020 ETS-Soft
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
 * @copyright  2007-2020 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class EtsTransConfig
{
    public static $instance = null;
    public function __construct()
    {

    }

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new EtsTransConfig();
        }

        return self::$instance;
    }

    public function updatePauseData($translateInfo)
    {
        $pageType = isset($translateInfo['pageType']) ? $translateInfo['pageType'] : null;
        $nbTranslated = isset($translateInfo['nbTranslated']) ? (int)$translateInfo['nbTranslated'] : 0;
        $nbCharTranslated = isset($translateInfo['nbCharTranslated']) ? (int)$translateInfo['nbCharTranslated'] : 0;
        $nbPath = isset($translateInfo['nbPath']) ? (int)$translateInfo['nbPath'] : 0;
        $langSource = isset($translateInfo['langSource']) ? $translateInfo['langSource'] : null;
        $langTarget = isset($translateInfo['langTarget']) ? $translateInfo['langTarget'] : null;
        if(is_array($langTarget)){
            $langTarget = implode(',', $langTarget);
        }
        $fieldOption = isset($translateInfo['fieldOption']) ? $translateInfo['fieldOption'] : null;
        if(!$pageType || !$langSource || !$langTarget || !$fieldOption){
            return false;
        }
        $id_shop = Context::getContext()->shop->id;
        switch ($pageType){
            case 'product':
                $result = Db::getInstance()->executeS("SELECT `id_product` FROM `"._DB_PREFIX_."product_shop` WHERE `id_shop`=".(int)$id_shop." LIMIT ".(int)$nbTranslated.",1");
                $nextId = $result ? $result[0]['id_product'] : 0;
                break;
            case 'category':
                $result = Db::getInstance()->executeS("SELECT `id_category` FROM `"._DB_PREFIX_."category_shop` WHERE `id_shop`=".(int)$id_shop." LIMIT ".(int)$nbTranslated.",1");
                $nextId = $result ? $result[0]['id_category'] : 0;
                break;
            default:
                $nextId = 0;
        }


        if($pageType == 'email' || $pageType == 'module' || $pageType == 'theme'){
            $mailOption = isset($translateInfo['mailOption']) ? $translateInfo['mailOption'] : '';
            if(is_array($mailOption)){
                $mailOption = implode(',', $mailOption);
            }
            $prefix = $pageType == 'theme' &&  isset($translateInfo['sfType']) ? $translateInfo['sfType'] : $pageType;
            $prefix .= '_'.str_replace(',', '', $langTarget);
            $selectedTheme = isset($translateInfo['selected_theme']) ? $translateInfo['selected_theme'] : '';
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_NB_TRANS", $nbTranslated);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_NB_CHAR", $nbCharTranslated);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_LANG_SOURCE", $langSource);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_LANG_TARGET", $langTarget);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_FIELD_OPTION", $fieldOption);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_MAIL_OPTION", $mailOption);
        }
        else{
            if(isset($translateInfo['pcType'])){
                $pageType = $pageType.'_'.$translateInfo['pcType'];
            }
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_NB_TRANS", $nbTranslated);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_NEXT_ID", $nextId);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_NB_CHAR", $nbCharTranslated);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_LANG_SOURCE", $langSource);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_LANG_TARGET", $langTarget);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_FIELD_OPTION", $fieldOption);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_NB_PATH", $nbPath);
        }
        return true;
    }

    public function deletePauseData($pageType, $selectedTheme = 0, $sfType = '', $langTarget='')
    {
        if($pageType == 'email' || $pageType == 'module' || $pageType == 'theme')
        {
            $prefix = $pageType == 'theme' ? $sfType : $pageType;
            switch ($sfType){
                case 'theme':
                    $prefix = 'themes';
                    break;
                case 'module':
                    $prefix = 'modules';
                    break;
                case 'mail':
                    $prefix = 'mails';
                    break;
                case 'back':
                    $prefix = 'back';
                    break;

            }
            if(is_array($langTarget)){
                $langTarget = implode(',', $langTarget);
            }
            $prefix .= '_'.str_replace(',', '', $langTarget);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_NB_TRANS", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_NEXT_ID", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_NB_CHAR", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_LANG_SOURCE", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_LANG_TARGET", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_FIELD_OPTION", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_MAIL_OPTION", null);
        }
        else{
            if($pcType = Tools::getValue('pcType')){
                $pageType = $pageType.'_'.$pcType;
            }
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_NB_TRANS", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_NEXT_ID", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_NB_CHAR", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_LANG_SOURCE", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_LANG_TARGET", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_FIELD_OPTION", null);
            Configuration::updateValue("ETS_TRANS_PAUSE_".$pageType."_NB_PATH", null);
        }

        return true;
    }

    public function hasResumeData($pageType, $selectedTheme = 0, $sfType='',$langTarget='')
    {
        if($pageType == 'email' || $pageType == 'module' || $pageType == 'theme'){
            $prefix = $pageType == 'theme' ? $sfType : $pageType;
            if(is_array($langTarget)){
                $langTarget = implode(',', $langTarget);
            }
            $prefix .= '_'.str_replace(',', '', $langTarget);
            return Configuration::get("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_LANG_SOURCE") ? true : false;
        }
        if($pcType = Tools::getValue('pcType')){
            $pageType = $pageType.'_'.$pcType;
        }
        return Configuration::get("ETS_TRANS_PAUSE_".$pageType."_LANG_SOURCE") ? true : false;
    }

    public function getResumeData($pageType, $selectedTheme = 0, $sfType = '', $langTarget='')
    {
        if($pageType == 'email' || $pageType == 'module' || $pageType == 'theme'){
            $prefix = $pageType == 'theme' ? $sfType : $pageType;
            if(is_array($langTarget)){
                $langTarget = implode(',', $langTarget);
            }
            $prefix .= '_'.str_replace(',', '', $langTarget);
            return array(
                'nb_translated' => Configuration::get("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_NB_TRANS"),
                'nb_char_translated' => Configuration::get("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_NB_CHAR"),
                'lang_source' => Configuration::get("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_LANG_SOURCE"),
                'lang_target' => Configuration::get("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_LANG_TARGET"),
                'field_option' => Configuration::get("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_FIELD_OPTION"),
                'mail_option' => Configuration::get("ETS_TRANS_PAUSE_".$prefix.'_'.$selectedTheme."_MAIL_OPTION"),
            );
        }
        if($pcType = Tools::getValue('pcType')){
            $pageType = $pageType.'_'.$pcType;
        }
        return array(
            'nb_translated' => Configuration::get("ETS_TRANS_PAUSE_".$pageType."_NB_TRANS"),
            'nb_char_translated' => Configuration::get("ETS_TRANS_PAUSE_".$pageType."_NB_CHAR"),
            'lang_source' => Configuration::get("ETS_TRANS_PAUSE_".$pageType."_LANG_SOURCE"),
            'lang_target' => Configuration::get("ETS_TRANS_PAUSE_".$pageType."_LANG_TARGET"),
            'field_option' => Configuration::get("ETS_TRANS_PAUSE_".$pageType."_FIELD_OPTION"),
            'nb_path' => Configuration::get("ETS_TRANS_PAUSE_".$pageType."_NB_PATH"),
        );
    }

    public static function resetKeyConfig()
    {
        $configs = Db::getInstance()->executeS("SELECT `name` FROM `"._DB_PREFIX_."configuration` WHERE `name` LIKE 'ETS_TRANS_%' ");
        if(!$configs){
            return true;
        }
        foreach ($configs as $config){
            Configuration::deleteByName($config['name']);
        }
        return true;
    }
}