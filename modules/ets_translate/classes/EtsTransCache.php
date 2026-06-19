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

class EtsTransCache extends ObjectModel
{
    public $id_ets_trans_cache;
    public $cache_type;
    public $name;
    public $file_path;
    public $file_type;
    public $nb_translated;
    public $is_oneclick;
    public $status;
    public $date_add;
    public $date_upd;
    public $id_shop;

    public static $definition = array(
        'table' => 'ets_trans_cache',
        'primary' => 'id_ets_trans_cache',
        'fields' => array(
            'cache_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'file_path' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'file_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString'
            ),
            'status' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'nb_translated' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'is_oneclick' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
        )
    );
}