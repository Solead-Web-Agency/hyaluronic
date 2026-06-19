<?php
/**
 * 2007-2022 ETS-Soft
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
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class EtsAbancartForm extends ObjectModel
{
    public $id_shop;
    public $btn_bg_color;
    public $btn_bg_hover_color;
    public $btn_text_color;
    public $btn_text_hover_color;
    public $enable_captcha;
    public $captcha_type;
    public $captcha_site_key_v2;
    public $captcha_secret_key_v2;
    public $captcha_site_key_v3;
    public $captcha_secret_key_v3;
    public $disable_captcha_lic;
    public $display_thankyou_page;
    public $position;
    public $enable;
    public $is_init;

    //Multi lang
    public $name;
    public $alias;
    public $description;
    public $btn_title;
    public $thankyou_page_title;
    public $thankyou_page_alias;
    public $thankyou_page_content;

    public static $definition = array(
        'table' => 'ets_abancart_form',
        'primary' => 'id_ets_abancart_form',
        'multilang' => true,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'btn_bg_color' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'btn_bg_hover_color' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'btn_text_color' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'btn_text_hover_color' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'enable_captcha' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'captcha_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'captcha_site_key_v2' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'captcha_secret_key_v2' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'captcha_site_key_v3' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'captcha_secret_key_v3' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'disable_captcha_lic' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'display_thankyou_page' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'is_init' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'enable' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            //Multi lang
            'name' => array('type' => self::TYPE_STRING, 'lang'=>true, 'validate' => 'isString'),
            'description' => array('type' => self::TYPE_HTML,'lang'=>true, 'validate' => 'isCleanHtml'),
            'alias' => array('type' => self::TYPE_STRING,'lang'=>true, 'validate' => 'isString'),
            'btn_title' => array('type' => self::TYPE_STRING,'lang'=>true, 'validate' => 'isString'),
            'thankyou_page_title' => array('type' => self::TYPE_STRING,'lang'=>true, 'validate' => 'isString'),
            'thankyou_page_alias' => array('type' => self::TYPE_STRING,'lang'=>true, 'validate' => 'isString'),
            'thankyou_page_content' => array('type' => self::TYPE_HTML,'lang'=>true, 'validate' => 'isCleanHtml'),
        )
    );

    public static function getFormByAlias($alias,$id_lang=null, $active=true, $excerptIds = array())
    {
        $context = Context::getContext();
        if(!$id_lang){
            $id_lang = (int)$context->language->id;
        }
        if($excerptIds){
            $excerptIds = array_map('intval', $excerptIds);
        }
        return Db::getInstance()->getRow("
            SELECT f.*, fl.* FROM `"._DB_PREFIX_."ets_abancart_form` f 
            LEFT JOIN `"._DB_PREFIX_."ets_abancart_form_lang` fl ON fl.id_ets_abancart_form=f.id_ets_abancart_form AND fl.id_lang=".(int)$id_lang." 
            WHERE f.id_shop=".(int)$context->shop->id." AND fl.alias='".pSQL($alias)."'".($active ? " AND f.enable=1" : "").($excerptIds ? " AND f.id_ets_abancart_form NOT IN (".implode(',', $excerptIds).")":""));
    }

    public static function getTotalForm($active = false)
    {
        return (int)Db::getInstance()->getValue("SELECT COUNT(*) FROM `"._DB_PREFIX_."ets_abancart_form` WHERE id_shop=".Context::getContext()->shop->id.($active ? " AND enable=1" : ""));
    }

    public static function getAllForms($active = false, $getFields = false)
    {
        $context = Context::getContext();
        $forms = Db::getInstance()->executeS("
            SELECT f.*, fl.* FROM `"._DB_PREFIX_."ets_abancart_form` f 
            LEFT JOIN `"._DB_PREFIX_."ets_abancart_form_lang` fl ON fl.id_ets_abancart_form=f.id_ets_abancart_form AND fl.id_lang=".(int)$context->language->id." 
            WHERE f.id_shop=".(int)$context->shop->id.($active ? " AND f.enable=1" : ""));

        if($getFields){
            foreach ($forms as &$form){
                $form['link'] = EtsAbancartForm::getLinkLeadForm($form['alias']);
                $form['fields'] = EtsAbancartField::getAllFields(true, $form['id_ets_abancart_form'], $context->language->id);
            }
        }
        return $forms;
    }

    public static function getFormById($idForm, $getFields = false)
    {
        if ($getFields){
            //
        }
        $context = Context::getContext();
        $formItem = Db::getInstance()->getRow("
            SELECT f.*, fl.* FROM `"._DB_PREFIX_."ets_abancart_form` f 
            LEFT JOIN `"._DB_PREFIX_."ets_abancart_form_lang` fl ON fl.id_ets_abancart_form=f.id_ets_abancart_form AND fl.id_lang=".(int)$context->language->id." 
            WHERE f.id_ets_abancart_form=".(int)$idForm);
        if($formItem){
            $formItem['link'] = EtsAbancartForm::getLinkLeadForm($formItem['alias']);
            $formItem['fields'] = EtsAbancartField::getAllFields(true, $formItem['id_ets_abancart_form'], $context->language->id);
        }
        return $formItem;
    }

    public static function getLinkLeadForm($alias)
    {
        return Context::getContext()->shop->getBaseURL().'lead/'.urlencode($alias);
    }

    public static function getThankyouPageByAlias($alias, $id_lang=null, $activeForm=false, $activeThankyouPage = false, $excerptIds = array())
    {
        $context = Context::getContext();
        if(!$id_lang){
            $id_lang = $context->language->id;
        }
        $where = "";
        if($activeForm){
            $where .= " AND f.enable=1";
        }
        if($activeThankyouPage){
            $where .= " AND f.display_thankyou_page=1";
        }
        if($excerptIds){
            $excerptIds = array_map('intval', $excerptIds);
            if($excerptIds)
                $where .= " AND f.id_ets_abancart_form NOT IN(".implode(',', $excerptIds).")";
        }
        return Db::getInstance()->getRow("
                SELECT * FROM `"._DB_PREFIX_."ets_abancart_form` f 
                JOIN `"._DB_PREFIX_."ets_abancart_form_lang` fl ON fl.id_ets_abancart_form = f.id_ets_abancart_form AND fl.id_lang=".(int)$id_lang."
                WHERE f.id_shop=".(int)$context->shop->id." AND fl.`thankyou_page_alias`='".pSQL($alias)."' ".(string)$where);
    }

    public static function getMaxId()
    {
        return (int)
        Db::getInstance()->getValue("SELECT MAX(id_ets_abancart_form) FROM `"._DB_PREFIX_."ets_abancart_form`");
    }

    public static function getLeadFormUrl($id=null,$alias=null, $id_lang=null)
    {
        $context = Context::getContext();
        if(!$id_lang){
            $id_lang = $context->language->id;
        }
        if ($id){
            $form = new EtsAbancartForm($id, $id_lang);
            if($form && $form->id){
                return $context->shop->getBaseURL(true).'lead/'.$form->alias;
            }
        }
        elseif ($alias){
            return $context->shop->getBaseURL(true).'lead/'.$alias;
        }
        return null;
    }
    public static function getThankyouPageUrl($id_form=null,$alias=null, $id_lang=null)
    {
        $context = Context::getContext();
        if(!$id_lang){
            $id_lang = $context->language->id;
        }
        if ($id_form){
            $form = new EtsAbancartForm($id_form, $id_lang);
            if($form && $form->id){
                return $context->shop->getBaseURL(true).'thank/'.$form->thankyou_page_alias;
            }
        }
        elseif ($alias){
            return $context->shop->getBaseURL(true).'thank/'.$alias;
        }
        return null;
    }

    public static function getLeadFormCookie($id, $context = null)
    {
        if(!$context){
            $context = Context::getContext();
        }
        if (isset($context->cookie->{'ets_ac_is_summited_lead_form_'.$id}) && $context->cookie->{'ets_ac_is_summited_lead_form_'.$id}){
            return $context->cookie->{'ets_ac_is_summited_lead_form_'.$id};
        }
        return null;
    }
    public static function setLeadFormCookie($id, $context = null)
    {
        if(!$context){
            $context = Context::getContext();
        }
        $context->cookie->{'ets_ac_is_summited_lead_form_'.$id} = 1;
        return $context;
    }
}