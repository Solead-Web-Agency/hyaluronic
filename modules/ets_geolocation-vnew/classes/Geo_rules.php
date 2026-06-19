<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Geo_rules extends ObjectModel
{
    public $id_shop;
    public $enabled;
    public $countries;
    public $all_countries;
    public $disable_geo;
    public $lang_to_set;
    public $currency_to_set;
    public $block_user;
    public $hidden_products;
    public $priority;
    public $url_redirect;

    public static $definition = [
        'table' => 'ets_geo_rule',
        'primary' => 'id_rule',
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'enabled' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'all_countries' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'disable_geo' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'lang_to_set' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'currency_to_set' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'block_user' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'hidden_products' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'url_redirect' => ['type' => self::TYPE_STRING, 'validate' => 'isAbsoluteUrl'],
            'priority' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
        ],
    ];

    public function __construct($id_rule = null)
    {
        parent::__construct($id_rule);
        if ($this->id) {
            $this->countries = !$this->all_countries ? $this->getCountries() : [];
        }
    }

    public function save($null_values = false, $auto_date = true)
    {
        if (($res = parent::save($null_values, $auto_date)) && Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_geo_country_rule` WHERE `id_rule` = ' . (int) $this->id)) {
            $sql = null;
            if ($this->countries) {
                foreach ($this->countries as $id_country) {
                    $sql .= '(' . (int) $this->id . ', ' . (int) $id_country . '),';
                }
                $res &= ($sql ? Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_geo_country_rule` (`id_rule`,`id_country_rule`) VALUES ' . trim($sql, ',')) : true);
            }
        }

        return $res;
    }

    public function delete()
    {
        if ($res = parent::delete()) {
            $res &= Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_geo_country_rule` WHERE `id_rule` = ' . (int) $this->id);
        }

        return $res;
    }

    /**
     * Find Rule by id_country, if no rule found - Find first one have all_countries = 1 with lowest priority number
     *
     * @param int $id_country
     *
     * @return array|false
     */
    public static function getRulesByIdCountry($id_country = 0)
    {
        if (!$id_country) {
            $id_country = (int) Context::getContext()->country->id;
        }
        $shopId = (int) Context::getContext()->shop->id;
        $sql = '
            SELECT r.*   
            FROM `' . _DB_PREFIX_ . 'ets_geo_rule` r 
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_geo_country_rule` cr ON (r.`id_rule` = cr.`id_rule` AND cr.`id_country_rule` = ' . (int) $id_country . ')  
            WHERE r.`enabled` = 1  AND (cr.id_rule is NOT NULL) AND r.id_shop = ' . $shopId . '
            GROUP BY r.`id_rule` 
            ORDER BY r.`priority`, r.`id_rule` 
        ';
        $row = Db::getInstance()->getRow($sql);
        if (!$row) {
            $sqlAllCountries = sprintf('SELECT * FROM `%sets_geo_rule` WHERE `enabled` = 1 AND `all_countries` = 1 AND `id_shop` = ' . $shopId . ' ORDER BY `priority`', _DB_PREFIX_);
            $row = Db::getInstance()->getRow($sqlAllCountries);
        }

        return $row;
    }

    public function getCountries()
    {
        if (!$res = Db::getInstance()->getValue('SELECT GROUP_CONCAT(`id_country_rule` SEPARATOR ",") FROM `' . _DB_PREFIX_ . 'ets_geo_country_rule` WHERE `id_rule` = ' . (int) $this->id)) {
            return [];
        }

        return explode(',', $res);
    }

    public static function getLists($params)
    {
        $is1760 = version_compare(_PS_VERSION_, '1.7.6', '>=');
        $context = Context::getContext();
        $sql = 'SELECT rl.* , crl.`id_country_rule`, IF(rl.all_countries != 1, GROUP_CONCAT(DISTINCT cl.name SEPARATOR ","), "' . self::l('All') . '") as `countries`, IF(l.name is NOT NULL, l.name, "' . self::l('Auto') . '") as `lang_to_set`, IF(' . ($is1760 ? 'currency_lang.name' : 'currency.name') . ' is NOT NULL, ' . ($is1760 ? 'currency_lang.name' : 'currency.name') . ', "' . self::l('Auto') . '") as `currency_to_set`
                    FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` rl 
                    LEFT JOIN `' . _DB_PREFIX_ . 'ets_geo_country_rule` crl ON rl.`id_rule` = crl.`id_rule` 
                    LEFT JOIN `' . _DB_PREFIX_ . 'lang` l ON (l.id_lang = rl.lang_to_set)
                    LEFT JOIN `' . _DB_PREFIX_ . 'country` c ON (c.id_country = crl.id_country_rule)
                    LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (cl.id_country = c.id_country AND cl.id_lang = ' . (int) $context->language->id . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'currency` currency  ON (currency.id_currency = rl.currency_to_set)
                    ' . ($is1760 ? ' LEFT JOIN `' . _DB_PREFIX_ . 'currency_lang` currency_lang  ON (currency.id_currency = currency_lang.id_currency) ' : '') . '
                    WHERE (1 AND rl.`id_shop` =' . (int) $context->shop->id . ' AND crl.`id_rule` is NOT NULL OR rl.all_countries = 1) ' . (isset($params['filter']) && $params['filter'] ? $params['filter'] : '') . '';
        $sql .= ' GROUP BY rl.`id_rule` ';
        if (isset($params['nb']) && $params['nb']) {
            return ($nb = Db::getInstance()->executeS($sql)) ? count($nb) : 0;
        }
        $sql .= ' ORDER BY ' . (isset($params['sort']) && $params['sort'] ? $params['sort'] : 'priority')
            . (isset($params['start'], $params['limit']) && ($params['start'] !== false) && $params['limit'] ? ' LIMIT ' . (int) $params['start'] . ', ' . (int) $params['limit'] : '');

        $res = Db::getInstance()->executeS($sql);

        return $res;
    }

    public static function l($string)
    {
        return Translate::getModuleTranslation('ets_geolocation', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }

    /**
     * @param int $pId
     *
     * @return \Geo_rules
     */
    public function removeHiddenProductById($pId)
    {
        $list = self::formatCommaSeparatedId($this->hidden_products, true);
        $list = array_filter($list, static function ($v) use ($pId) {
            if ($v == $pId) {
                return false;
            }

            return true;
        });
        $this->hidden_products = self::formatCommaSeparatedId($list);

        return $this;
    }

    /**
     * @param int $pId
     *
     * @return \Geo_rules
     */
    public function addHiddenProductById($pId)
    {
        /** @var array $list */
        $list = self::formatCommaSeparatedId($this->hidden_products, true);
        if (!in_array($pId, $list)) {
            $list[] = $pId;
        }
        $this->hidden_products = self::formatCommaSeparatedId($list);

        return $this;
    }

    /**
     * @param string|array $idList
     * @param bool $returnArray
     *
     * @return string|array
     */
    public static function formatCommaSeparatedId($idList, $returnArray = false)
    {
        $list = is_array($idList) ? $idList : [];
        if (is_string($idList) && ($idList !== '')) {
            $idList = (strpos($idList, ',') !== false) ? explode(',', $idList) : [$idList];
            $list = array_merge($list, $idList);
        }
        $list = array_filter($list, static function ($v) {
            $v = trim($v);

            return Validate::isUnsignedInt($v);
        });
        if ($returnArray) {
            return $list;
        }

        return implode(',', $list);
    }
}
