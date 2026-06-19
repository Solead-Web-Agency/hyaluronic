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

class EtsAbancartFormSubmit extends ObjectModel
{
    public $id_ets_abancart_form;
    public $id_ets_abancart_reminder;
    public $id_customer;
    public $id_lang;
    public $id_currency;
    public $id_country;
    public $id_cart;
    public $is_leaving_website;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'ets_abancart_form_submit',
        'primary' => 'id_ets_abancart_form_submit',
        'fields' => array(
            'id_ets_abancart_form' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_ets_abancart_reminder' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_lang' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_country' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'is_leaving_website' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_upd' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        )
    );

    public static function getFormSubmitData($idForm, $start=0, $limit=50)
    {
        $formSubmits = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."ets_abancart_form_submit` WHERE `id_ets_abancart_form`=".(int)$idForm.($limit ? " LIMIT ".(int)$start.",".(int)$limit : ""));
        foreach ($formSubmits as &$item){
            $item['field_values'] = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."ets_abancart_field_value` WHERE id_ets_abancart_form_submit=".(int)$item['id_ets_abancart_form_submit']);
        }
        if(isset($item)){
            unset($item);
        }
        return $formSubmits;
    }

    public static function getTotalFormSubmit($idForm)
    {
        return (int)Db::getInstance()->getValue("SELECT COUNT(*) FROM `"._DB_PREFIX_."ets_abancart_form_submit` WHERE `id_ets_abancart_form`=".(int)$idForm);
    }
}