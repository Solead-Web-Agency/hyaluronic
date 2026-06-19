<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

class RgLdRedirection extends ObjectModel
{
    public $id_redirection;
    public $id_shop_group = null;
    public $id_shop = null;
    public $url;
    public $quantity = 0;
    public $full_path;
    public $use_popup;
    public $active;

    /* Lang fields */
    public $popup;

    /* Extra fields */
    public $countries = array();

    public static $definition = array(
        'table' => 'rg_locationdetection_redirection',
        'primary' => 'id_redirection',
        'multilang' => true,
        'fields' => array(
            'id_shop_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'url' => array('type' => self::TYPE_STRING, 'validate' => 'isAbsoluteUrl', 'required' => true),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'full_path' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'use_popup' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),

            /* Lang fields */
            'popup' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
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

            if ($countries = self::getCountriesInRedirection($id)) {
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
            $this->addCountriesInRedirection();
    }

    public function update($null_values = false)
    {
        return $this->deleteCountriesInRedirection() &&
            $this->addCountriesInRedirection() &&
            parent::update($null_values);
    }

    public function delete()
    {
        return $this->deleteCountriesInRedirection() &&
            parent::delete();
    }

    protected function addCountriesInRedirection()
    {
        if (!$this->countries || !is_array($this->countries)) {
            return false;
        }

        foreach ($this->countries as $id_country) {
            if (!Db::getInstance()->insert('rg_locationdetection_redirection_country', array('id_redirection' => (int)$this->id, 'id_country' => (int)$id_country))
            ) {
                return false;
            }
        }

        return true;
    }

    protected function deleteCountriesInRedirection()
    {
        return Db::getInstance()->delete('rg_locationdetection_redirection_country', 'id_redirection = '.(int)$this->id_redirection);
    }

    protected static function getCountriesInRedirection($id_redirection, $id_lang = null)
    {
        $sql = new DbQuery();
        $sql->select('rc.`id_country`, cl.`name`');
        $sql->from('rg_locationdetection_redirection_country', 'rc');
        $sql->leftJoin('rg_locationdetection_country_lang', 'cl', 'cl.`id_country` = rc.`id_country` AND cl.`id_lang` = '.($id_lang !== null ? (int)$id_lang : (int)Context::getContext()->language->id));
        $sql->where('rc.`id_redirection` = '.(int)$id_redirection);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql->build());
    }

    public static function getRedirections($id_lang, $active = null, $where = false, $orderBy = false, $orderWay = false)
    {
        $redirections = Db::getInstance()->executeS(
            'SELECT r.*, rl.`popup`
            FROM `'._DB_PREFIX_.'rg_locationdetection_redirection` r
            LEFT JOIN `'._DB_PREFIX_.'rg_locationdetection_redirection_lang` rl ON rl.`id_redirection` = r.`id_redirection` AND rl.`id_lang` = '.(int)$id_lang.'
            LEFT JOIN `'._DB_PREFIX_.'rg_locationdetection_redirection_country` rc ON rc.`id_redirection` = r.`id_redirection`
            LEFT JOIN `'._DB_PREFIX_.'rg_locationdetection_country_lang` cl ON cl.`id_country` = rc.`id_country` AND cl.`id_lang` = '.(int)$id_lang.'
            WHERE (r.`id_shop_group` = '.(int)Context::getContext()->shop->id_shop_group.' AND r.`id_shop` = '.(int)Context::getContext()->shop->id.($active !== null ? ' AND r.`active` = '.(int)$active : '').($where ? RgLdTools::cleanQuotes(pSQL($where)) : '').')
            GROUP BY r.`id_redirection`
            '.($orderBy ? 'ORDER BY '.pSQL($orderBy).($orderWay ? ' '.pSQL($orderWay) : '') : '')
        );

        if ($redirections) {
            $t_redirections = $redirections;

            foreach ($t_redirections as $key => $redirection) {
                $redirections[$key]['countries'] = self::getCountriesInRedirection($redirection['id_redirection'], $id_lang);
                $countries_string = array();

                foreach ($redirections[$key]['countries'] as $v) {
                    $countries_string[] = $v['name'];
                }

                $redirections[$key]['countries_string'] = implode(', ', $countries_string);
            }
        }

        return $redirections;
    }

    public static function getIdRedirectionByCountryCode($country_iso_code)
    {
        return Db::getInstance()->getValue(
            'SELECT r.`id_redirection`
            FROM `'._DB_PREFIX_.'rg_locationdetection_redirection` r
            INNER JOIN `'._DB_PREFIX_.'rg_locationdetection_country` c ON (c.`country_iso_code` = \''.pSQL(Tools::strtoupper($country_iso_code)).'\' AND c.`active` = 1)
            INNER JOIN `'._DB_PREFIX_.'rg_locationdetection_redirection_country` rc ON (rc.`id_redirection` = r.`id_redirection` AND rc.`id_country` = c.`id_country`)
            WHERE r.`id_shop_group` = '.(int)Context::getContext()->shop->id_shop_group.'
                AND r.`id_shop` = '.(int)Context::getContext()->shop->id.'
                AND r.`active` = 1'
        );
    }

    public static function getAvailableCountries($id_lang, $id_redirection = false)
    {
        return Db::getInstance()->executeS(
            'SELECT c.`id_country`, cl.`name`
            FROM `'._DB_PREFIX_.'rg_locationdetection_country` c
            LEFT JOIN `'._DB_PREFIX_.'rg_locationdetection_country_lang` cl ON (cl.`id_country` = c.`id_country` AND cl.`id_lang` = '.(int)$id_lang.')
            WHERE c.`id_country` NOT IN (
                SELECT rc.`id_country`
                FROM `'._DB_PREFIX_.'rg_locationdetection_redirection_country` rc, `'._DB_PREFIX_.'rg_locationdetection_redirection` r
                WHERE r.`id_redirection` = rc.`id_redirection`
                    AND r.`id_shop_group` = '.(int)Context::getContext()->shop->id_shop_group.'
                    AND r.`id_shop` = '.(int)Context::getContext()->shop->id.'
                    '.($id_redirection ? 'AND rc.`id_redirection` <> '.(int)$id_redirection : '').'     
                GROUP BY rc.`id_country`)
            ORDER BY cl.`name`'
        );
    }

    public static function bulkEnable($ids)
    {
        if ($ids) {
            return Db::getInstance()->update(self::$definition['table'], array('active' => 1), 'id_redirection IN ('.implode(',', array_map('intval', $ids)).')');
        }

        return false;
    }

    public static function bulkDisable($ids)
    {
        if ($ids) {
            return Db::getInstance()->update(self::$definition['table'], array('active' => 0), 'id_redirection IN ('.implode(',', array_map('intval', $ids)).')');
        }

        return false;
    }

    public static function bulkDelete($ids)
    {
        $return = false;

        if ($ids) {
            $return = true;

            foreach ($ids as $id_redirection) {
                $redirection = new RgLdRedirection((int)$id_redirection);
                $return &= $redirection->delete();
            }
        }

        return $return;
    }
}
