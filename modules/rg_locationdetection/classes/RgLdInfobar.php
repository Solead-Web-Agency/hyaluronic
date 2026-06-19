<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

class RgLdInfobar extends ObjectModel
{
    public $id_infobar;
    public $id_shop_group = null;
    public $id_shop = null;
    public $width;
    public $height;
    public $background;
    public $border_size;
    public $border_color;
    public $close_button_color;
    public $position;
    public $static;
    public $custom_css;
    public $active;

    /* Lang fields */
    public $content;

    /* Extra fields */
    public $countries = array();

    public static $definition = array(
        'table' => 'rg_locationdetection_infobar',
        'primary' => 'id_infobar',
        'multilang' => true,
        'fields' => array(
            'id_shop_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'width' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 12),
            'height' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 12),
            'background' => array('type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 7),
            'border_size' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 12),
            'border_color' => array('type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 7),
            'close_button_color' => array('type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 7),
            'position' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 6),
            'static' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'custom_css' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),

            /* Lang fields */
            'content' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->id_shop_group = (int)Context::getContext()->shop->id_shop_group;

        if ($id_shop === null) {
            $id_shop = Context::getContext()->shop->id;
            $this->id_shop = $id_shop;
        } else {
            $this->id_shop = $id_shop;
        }

        if ($id) {
            $ids = array();

            if ($countries = RgLdInfobar::getCountriesInInfobar($id)) {
                foreach ($countries as $country) {
                    $ids[] = $country['id_country'];
                }
            }

            $this->countries = $ids;
        }

        return parent::__construct($id, $id_lang, $id_shop);
    }

    public function add($autodate = true, $null_values = false)
    {
        return parent::add($autodate, $null_values) &&
            $this->addCountriesInInfobar();
    }

    public function update($null_values = false)
    {
        return $this->deleteCountriesInInfobar() &&
            $this->addCountriesInInfobar() &&
            parent::update($null_values);
    }

    public function delete()
    {
        return $this->deleteCountriesInInfobar() &&
            parent::delete();
    }

    protected function addCountriesInInfobar()
    {
        if (!$this->countries || !is_array($this->countries)) {
            return false;
        }

        foreach ($this->countries as $id_country) {
            if (!Db::getInstance()->insert('rg_locationdetection_infobar_country', array('id_infobar' => (int)$this->id, 'id_country' => (int)$id_country))) {
                return false;
            }
        }

        return true;
    }

    protected function deleteCountriesInInfobar()
    {
        return Db::getInstance()->delete('rg_locationdetection_infobar_country', 'id_infobar = '.(int)$this->id_infobar);
    }

    protected static function getCountriesInInfobar($id_infobar, $id_lang = null)
    {
        $sql = new DbQuery();
        $sql->select('rc.`id_country`, cl.`name`');
        $sql->from('rg_locationdetection_infobar_country', 'rc');
        $sql->leftJoin('rg_locationdetection_country_lang', 'cl', 'cl.`id_country` = rc.`id_country` AND cl.`id_lang` = '.($id_lang !== null ? (int)$id_lang : (int)Context::getContext()->language->id));
        $sql->where('rc.`id_infobar` = '.(int)$id_infobar);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql->build());
    }

    public static function getInfobars($id_lang, $active = null, $where = false, $orderBy = false, $orderWay = false)
    {
        $infobars = Db::getInstance()->executeS(
            'SELECT i.*, il.`content`
            FROM `'._DB_PREFIX_.'rg_locationdetection_infobar` i
            LEFT JOIN `'._DB_PREFIX_.'rg_locationdetection_infobar_lang` il ON il.`id_infobar` = i.`id_infobar` AND il.`id_lang` = '.(int)$id_lang.'
            LEFT JOIN `'._DB_PREFIX_.'rg_locationdetection_infobar_country` ic ON ic.`id_infobar` = i.`id_infobar`
            LEFT JOIN `'._DB_PREFIX_.'rg_locationdetection_country_lang` cl ON cl.`id_country` = ic.`id_country` AND cl.`id_lang` = '.(int)$id_lang.'
            WHERE (i.`id_shop_group` = '.(int)Context::getContext()->shop->id_shop_group.' AND i.`id_shop` = '.(int)Context::getContext()->shop->id.($active !== null ? ' AND i.`active` = '.(int)$active : '').($where ? RgLdTools::cleanQuotes(pSQL($where)) : '').')
            GROUP BY i.`id_infobar`
            '.($orderBy ? 'ORDER BY '.pSQL($orderBy).($orderWay ? ' '.pSQL($orderWay) : '') : '')
        );

        if ($infobars) {
            $t_infobars = $infobars;

            foreach ($t_infobars as $key => $infobar) {
                $infobars[$key]['countries'] = self::getCountriesInInfobar($infobar['id_infobar'], $id_lang);
                $countries_string = array();

                foreach ($infobars[$key]['countries'] as $v) {
                    $countries_string[] = $v['name'];
                }

                $infobars[$key]['countries_string'] = implode(', ', $countries_string);
            }
        }

        return $infobars;
    }

    public static function getIdInfobarByCountryCode($country_iso_code)
    {
        return Db::getInstance()->getValue(
            'SELECT i.`id_infobar`
            FROM `'._DB_PREFIX_.'rg_locationdetection_infobar` i
            INNER JOIN `'._DB_PREFIX_.'rg_locationdetection_country` c ON (c.`country_iso_code` = \''.pSQL(Tools::strtoupper($country_iso_code)).'\' AND c.`active` = 1)
            INNER JOIN `'._DB_PREFIX_.'rg_locationdetection_infobar_country` ic ON (ic.`id_infobar` = i.`id_infobar` AND ic.`id_country` = c.`id_country`)
            WHERE i.`id_shop_group` = '.(int)Context::getContext()->shop->id_shop_group.'
                AND i.`id_shop` = '.(int)Context::getContext()->shop->id.'
                AND i.`active` = 1'
        );
    }

    public static function getAvailableCountries($id_lang, $id_infobar = false)
    {
        return Db::getInstance()->ExecuteS(
            'SELECT c.`id_country`, cl.`name`
            FROM `'._DB_PREFIX_.'rg_locationdetection_country` c
            LEFT JOIN `'._DB_PREFIX_.'rg_locationdetection_country_lang` cl ON (cl.`id_country` = c.`id_country` AND cl.`id_lang` = '.(int)$id_lang.')
            WHERE c.`id_country` NOT IN (
                SELECT rc.`id_country`
                FROM `'._DB_PREFIX_.'rg_locationdetection_infobar_country` rc, `'._DB_PREFIX_.'rg_locationdetection_infobar` r
                WHERE r.`id_infobar` = rc.`id_infobar`
                    AND r.`id_shop_group` = '.(int)Context::getContext()->shop->id_shop_group.'
                    AND r.`id_shop` = '.(int)Context::getContext()->shop->id.'
                    '.($id_infobar ? 'AND rc.`id_infobar` <> '.(int)$id_infobar : '').'     
                GROUP BY rc.`id_country`)
            ORDER BY cl.`name`'
        );
    }

    public static function bulkEnable($ids)
    {
        if ($ids) {
            return Db::getInstance()->update(self::$definition['table'], array('active' => 1), 'id_infobar IN ('.implode(',', array_map('intval', $ids)).')');
        }

        return false;
    }

    public static function bulkDisable($ids)
    {
        if ($ids) {
            return Db::getInstance()->update(self::$definition['table'], array('active' => 0), 'id_infobar IN ('.implode(',', array_map('intval', $ids)).')');
        }

        return false;
    }

    public static function bulkDelete($ids)
    {
        $return = false;

        if ($ids) {
            $return = true;

            foreach ($ids as $id_infobar) {
                $infobar = new RgLdInfobar((int)$id_infobar);
                $infobar->delete();
            }
        }

        return $return;
    }
}
