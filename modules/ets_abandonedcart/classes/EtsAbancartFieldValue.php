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

class EtsAbancartFieldValue extends ObjectModel
{
    public $id_ets_abancart_form_submit;
    public $id_ets_abancart_field;
    public $value;
    public $file_name;

    public static $definition = array(
        'table' => 'ets_abancart_field_value',
        'primary' => 'id_ets_abancart_field_value',
        'fields' => array(
            'id_ets_abancart_form_submit' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_ets_abancart_field' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'value' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'file_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        )
    );

    public static function getFieldValueByIdForm($id_form)
    {
        return Db::getInstance()->executeS("
            SELECT fv.*, f.display_column, f.type FROM `"._DB_PREFIX_."ets_abancart_field_value` fv 
            JOIN `"._DB_PREFIX_."ets_abancart_field` f ON f.id_ets_abancart_field=fv.id_ets_abancart_field
            WHERE f.id_ets_abancart_form=".(int)$id_form);
    }

    public static function getMaxIdSubmit()
    {
        return (int)Db::getInstance()->getValue("SELECT MAX(id_submit) FROM `"._DB_PREFIX_."ets_abancart_field_value`");
    }

}