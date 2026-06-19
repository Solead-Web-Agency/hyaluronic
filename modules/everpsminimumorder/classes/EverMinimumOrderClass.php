<?php
/**
 * Project : everpsminimumorder
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverMinimumOrderClass extends ObjectModel
{
    public $id_everpsminimumorder;
    public $id_shop;
    public $id_country;
    public $id_group;
    public $amount;
    public $use_tax;
    public $active;

    public static $definition = array(
        'table' => 'everpsminimumorder',
        'primary' => 'id_everpsminimumorder',
        'multilang' => false,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'id_country' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'id_group' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'amount' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isFloat'),
            'use_tax' => array('type' => self::TYPE_INT, 'validate' => 'isBool', 'required' => true),
            'active' => array('type' => self::TYPE_INT, 'validate' => 'isBool', 'required' => true),
        )
    );

    public static function getAvailableCountries()
    {
        if ($res = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'country` WHERE `active` = 1'
        )) {
            return $res;
        }
    }

    // Should return new object, would be easier
    public static function getRuleByShopCountryGroup($id_shop, $id_country, $id_group)
    {
        if ($res = Db::getInstance()->getValue(
            'SELECT `id_everpsminimumorder` FROM `'._DB_PREFIX_.'everpsminimumorder`
            WHERE id_country = '.(int)$id_country.'
            AND id_group = '.(int)$id_group.'
            AND id_shop = '.(int)$id_shop.'
            AND `active` = 1'
        )) {
            return new self($res);
        }
    }

    public static function getAllGroups($id_lang)
    {
        $result = Db::getInstance()->executeS('
        SELECT *
        FROM '._DB_PREFIX_.'group_lang WHERE id_lang = '.(int)$id_lang);
        return $result;
    }
}
