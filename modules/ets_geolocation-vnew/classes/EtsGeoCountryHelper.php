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
 **/
if (!class_exists('\EtsGeoDbHelper')) {
    require_once __DIR__ . '/EtsGeoDbHelper.php';
}

/**
 * Class EtsGeoCountryHelper
 */
class EtsGeoCountryHelper
{
    /**
     * @var \EtsGeoCountryHelper
     */
    private static $_instance;

    /**
     * @var \Context|\ContextCore
     */
    private static $_context;

    /**
     * @return \EtsGeoCountryHelper
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @return \Db|\DbCore|\DbPDOCore
     */
    public static function db()
    {
        return \EtsGeoDbHelper::db();
    }

    /**
     * Get id_country from ISO code.
     *
     * @param string $isoCode
     * @param bool $activeOnly
     *
     * @return int|null
     */
    public static function getIdByIso($isoCode, $activeOnly = false)
    {
        //        $sql = sprintf('SELECT `id_country` FROM `%scountry` WHERE iso_code = "%s"', _DB_PREFIX_, pSQL(strtoupper($isoCode)));
        //        $id = self::db()->getValue($sql);
        $id = \Country::getByIso($isoCode, $activeOnly);

        return false !== $id ? (int) $id : null;
    }

    /**
     * Get iso code from country ID
     *
     * @param int $idCountry
     * @param bool $activeOnly
     *
     * @return string|null
     */
    public static function getIsoById($idCountry, $activeOnly = false)
    {
        $iso = self::db()->getValue('SELECT iso_code FROM `' . _DB_PREFIX_ . 'country` WHERE id_country=' . (int) $idCountry . ($activeOnly ? ' AND active = 1' : ''));

        return false !== $iso ? (string) $iso : null;
    }

    /**
     * Get id_currency from country ISO code.
     *
     * @param string $isoCode
     *
     * @return int|null
     */
    public static function getIdCurrencyByIso($isoCode)
    {
        $currencyIsoCode = Db::getInstance()->getValue('SELECT iso_code_currency FROM `' . _DB_PREFIX_ . 'ets_geo_currency` WHERE iso_code_country = "' . pSQL(Tools::strtoupper($isoCode)) . '"');
        if ($currencyIsoCode) {
            $id = self::db()->getValue('
                SELECT c.id_currency 
                FROM `' . _DB_PREFIX_ . 'currency` c ' . Shop::addSqlAssociation('currency', 'c') . ' 
                WHERE iso_code = "' . pSQL(Tools::strtoupper($currencyIsoCode)) . '"
            ');

            return false !== $id ? (int) $id : null;
        }

        return null;
    }

    /**
     * Get Language object from country ISO code.
     *
     * @param string $isoCode
     *
     * @return \Language|null
     */
    public static function getLangByIso($isoCode)
    {
        $isoCode = Tools::strtolower($isoCode);
        $sql = sprintf(
            'SELECT l.id_lang FROM `%slang` l INNER JOIN `%slang_shop` ls ON (ls.id_shop = %d AND ls.id_lang = l.id_lang) WHERE l.`iso_code` = "%s" OR l.`language_code` LIKE "%%%s"',
            _DB_PREFIX_,
            _DB_PREFIX_,
            Context::getContext()->shop->id,
            pSQL($isoCode),
            pSQL($isoCode)
        );
        $id = self::db()->getValue($sql);
        if ($id !== false) {
            return new \Language($id);
        }

        return null;
    }

    /**
     * Get id_country from Address id.
     *
     * @param int $addressId
     *
     * @return int|null
     */
    public static function getIdByAddressId($addressId)
    {
        $id = self::db()->getValue('SELECT id_country FROM `' . _DB_PREFIX_ . 'address` WHERE id_address=' . (int) $addressId . ' ');

        return false !== $id ? (int) $id : null;
    }

    /**
     * @param int $idLang
     * @param bool $active
     * @param bool $containStates
     * @param bool $listStates
     * @param int|false $idRule
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public static function getCountriesWithOutRule($idLang, $active = false, $containStates = false, $listStates = true, $idRule = false)
    {
        if(!$idRule)
        {
            $sqlAllCountries = sprintf('SELECT * FROM `%sets_geo_rule` WHERE `enabled` = 1 AND `all_countries` = 1 AND `id_shop` = ' . (int)Context::getContext()->shop->id . ' AND id_rule!='.(int)$idRule, _DB_PREFIX_);
            if(Db::getInstance()->getRow($sqlAllCountries))
                return array();
        } 
        $sql = 'SELECT GROUP_CONCAT(DISTINCT `id_country_rule` SEPARATOR \',\') as idsCountry FROM `' . _DB_PREFIX_ . 'ets_geo_country_rule` cr
                 INNER JOIN `' . _DB_PREFIX_ . 'ets_geo_rule` r ON (cr.`id_rule` = r.`id_rule` AND id_shop = ' . (int) self::getContext()->shop->id . ') 
                  WHERE 1 ' . ($idRule ? ' AND r.`id_rule` != ' . (int) $idRule . ' ' : '') . ' ';
        $idsCountry = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        $countries = [];
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT cl.*,c.*, cl.`name` country, z.`name` zone
		FROM `' . _DB_PREFIX_ . 'country` c ' . Shop::addSqlAssociation('country', 'c') . '
		LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $idLang . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'zone` z ON (z.`id_zone` = c.`id_zone`)
		WHERE 1' . ($active ? ' AND c.active = 1' : '') . ($containStates ? ' AND c.`contains_states` = ' . (int) $containStates : '') . '
		    ' . ($idsCountry && isset($idsCountry['idsCountry']) && $idsCountry['idsCountry'] ? ' AND c.`id_country` NOT IN (' . pSQL($idsCountry['idsCountry']) . ') ' : '') . '
		ORDER BY cl.name ASC');

        foreach ($result as $row) {
            $countries[$row['id_country']] = $row;
        }

        if ($listStates) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'state` ORDER BY `name`');
            foreach ($result as $row) {
                if (isset($countries[$row['id_country']]) && $row['active'] == 1) { /* Does not keep the state if its country has been disabled and not selected */
                    $countries[$row['id_country']]['states'][] = $row;
                }
            }
        }

        return $countries;
    }

    /**
     * get auto detected id_address by country ISO code
     *
     * @param string $isoCode
     * @param bool $autoAdd if TRUE - will auto add new one if not found
     *
     * @return int|null
     */
    public static function getAutoDetectedIdAddressByIso($isoCode, $autoAdd = false)
    {
        $sql = '
                    SELECT ad.id_address FROM `' . _DB_PREFIX_ . 'ets_geo_address_detected` ad
                    INNER JOIN `' . _DB_PREFIX_ . 'address` a ON (a.id_address = ad.id_address AND (a.id_customer is NULL OR a.id_customer = 0))
                    WHERE ad.iso_code_country = "' . pSQL($isoCode) . '"
                ';

        $id = self::db()->getValue($sql);

        if (!$id && $autoAdd) {
            return self::addAutoDetectedAddressByIso($isoCode);
        }

        return $id !== false ? (int) $id : null;
    }

    /**
     * @param string $isoCode
     *
     * @return int|false
     */
    public static function addAutoDetectedAddressByIso($isoCode)
    {
        if (!($idCountry = self::getIdByIso($isoCode))) {
            return false;
        }

        $res = self::db()->execute(sprintf("
            INSERT INTO `%saddress` (`id_country`, `id_state`, `id_customer`, `id_manufacturer`, `id_supplier`, `id_warehouse`, `alias`, `company`, `lastname`, `firstname`, `address1`, `address2`, `postcode`, `city`, `other`, `phone`, `phone_mobile`, `vat_number`, `dni`, `date_add`, `date_upd`, `active`, `deleted`) 
            VALUES (%d, NULL, 0, 0, 0, 0, 'auto', '', 'auto', 'auto', 'auto', NULL, NULL, 'auto', '', NULL, NULL, NULL, NULL, NOW(), NOW(), '1', '0')
        ", _DB_PREFIX_, $idCountry));
        if ($res !== false) {
            $id = (int) self::db()->Insert_ID();
            self::db()->execute(sprintf("
                        INSERT INTO `%sets_geo_address_detected` (`id_address`, `iso_code_country`) 
                        VALUES (%d, '%s')
                    ", _DB_PREFIX_, $id, pSQL($isoCode)));

            return $id;
        }

        return false;
    }

    /**
     * @return \Context|\ContextCore
     */
    public static function getContext()
    {
        if (!self::$_context instanceof \ContextCore) {
            self::$_context = Context::getContext();
        }

        return self::$_context;
    }
}
