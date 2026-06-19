<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

class RgLdCountry extends ObjectModel
{
    public $id_country;
    public $country_iso_code;
    public $currency_iso_code;
    public $currency_name;
    public $lang_iso_code_first;
    public $lang_name_first;
    public $lang_iso_code_second;
    public $lang_name_second;
    public $lang_iso_code_third;
    public $lang_name_third;
    public $id_carrier;
    public $active;

    /* Lang fields */
    public $name;

    public static $definition = array(
        'table' => 'rg_locationdetection_country',
        'primary' => 'id_country',
        'multilang' => true,
        'fields' => array(
            'country_iso_code' => array('type' => self::TYPE_STRING, 'validate' => 'isLanguageIsoCode', 'required' => true, 'size' => 3),
            'currency_iso_code' => array('type' => self::TYPE_STRING, 'validate' => 'isLanguageIsoCode', 'required' => true, 'size' => 3),
            'currency_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'lang_iso_code_first' => array('type' => self::TYPE_STRING, 'validate' => 'isLanguageIsoCode', 'required' => true, 'size' => 2),
            'lang_name_first' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'lang_iso_code_second' => array('type' => self::TYPE_STRING, 'validate' => 'isLanguageIsoCode', 'size' => 2),
            'lang_name_second' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 32),
            'lang_iso_code_third' => array('type' => self::TYPE_STRING, 'validate' => 'isLanguageIsoCode', 'size' => 2),
            'lang_name_third' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 32),
            'id_carrier' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),

            /* Lang fields */
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
        ),
    );

    public static function getCountries($id_lang, $active = null, $where = false, $orderBy = false, $orderWay = false)
    {
        $sql = new DbQuery();
        $sql->select('c.*, cl.`name`, ca.`name` AS `carrier_name`');
        $sql->from(self::$definition['table'], 'c');
        $sql->leftJoin('rg_locationdetection_country_lang', 'cl', 'cl.`id_country` = c.`id_country` AND cl.`id_lang` = '.(int)$id_lang);
        $sql->leftJoin('carrier', 'ca', 'ca.`id_carrier` = c.`id_carrier`');

        if ($active !== null) {
            $sql->where('c.`active` = '.(int)$active.($where ? RgLdTools::cleanQuotes(pSQL($where)) : ''));
        } elseif ($where) {
            $sql->where(RgLdTools::cleanQuotes(pSQL(Tools::substr($where, 5, Tools::strlen($where)))));
        }

        if ($orderBy) {
            $sql->orderBy(pSQL($orderBy).' '.pSQL($orderWay));
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql->build());
    }

    public static function getCountryByIsoCode($iso_code, $id_lang, $active = true)
    {
        $sql = new DbQuery();
        $sql->select('c.*, cl.`name`, ca.`name` AS `carrier_name`');
        $sql->from(self::$definition['table'], 'c');
        $sql->leftJoin('rg_locationdetection_country_lang', 'cl', 'cl.`id_country` = c.`id_country` AND cl.`id_lang` = '.(int)$id_lang);
        $sql->leftJoin('carrier', 'ca', 'ca.`id_carrier` = c.`id_carrier`');
        $sql->where('c.`country_iso_code` = \''.pSQL($iso_code).'\''.($active ? ' AND c.`active` = '.(int)$active : ''));

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql->build());
    }

    public static function isoCodeExists($iso_code)
    {
        $sql = new DbQuery();
        $sql->select('1');
        $sql->from(self::$definition['table']);
        $sql->where('`country_iso_code` = \''.pSQL($iso_code).'\'');

        return (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql->build());
    }

    public static function bulkEnable($ids)
    {
        if ($ids) {
            return Db::getInstance()->update(self::$definition['table'], array('active' => 1), 'id_country IN ('.implode(',', array_map('intval', $ids)).')');
        }

        return false;
    }

    public static function bulkDisable($ids)
    {
        if ($ids) {
            return Db::getInstance()->update(self::$definition['table'], array('active' => 0), 'id_country IN ('.implode(',', array_map('intval', $ids)).')');
        }

        return false;
    }

    public static function bulkDelete($ids)
    {
        $return = false;

        if ($ids) {
            $return = true;

            foreach ($ids as $id_country) {
                $country = new RgLdCountry((int)$id_country);
                $country->delete();
            }
        }

        return $return;
    }
}
