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
defined('_PS_VERSION_') or exit;
/**
 * Class EtsGeoDbHelper
 *
 * @since 1.1.6
 */
class EtsGeoDbHelper
{
    /**
     * @var \EtsGeoDbHelper
     */
    private static $_instance;

    /**
     * @var \Db|\DbCore|\DbPDOCore
     */
    private $_db;

    /**
     * EtsGeoDbHelper constructor.
     */
    public function __construct()
    {
        $this->getDb();
    }

    /**
     * @return \EtsGeoDbHelper
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
    public function getDb()
    {
        if (!$this->_db instanceof \DbCore) {
            $this->_db = \Db::getInstance(_PS_USE_SQL_SLAVE_);
        }

        return $this->_db;
    }

    /**
     * static method of getDb
     *
     * @return \Db|\DbCore|\DbPDOCore
     */
    public static function db()
    {
        return self::getInstance()->getDb();
    }

    /**
     * @param int $idLang
     * @param bool $active
     * @param bool $containStates
     *
     * @return array|false
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getListCountriesNotBlock($idLang, $active = false, $containStates = false)
    {
        $sql = '
		SELECT c.`id_country`,c.`iso_code`,cl.`name`, bu.`block_user`, bu.`disable_geo`  
		FROM `' . _DB_PREFIX_ . 'country` c ' . Shop::addSqlAssociation('country', 'c') . '
		LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $idLang . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'zone` z ON (z.`id_zone` = c.`id_zone`)
		LEFT JOIN (
		    SELECT `id_country_rule`,`block_user`,`disable_geo` 
		     FROM `' . _DB_PREFIX_ . 'ets_geo_country_rule` as gcrl 
		     JOIN `' . _DB_PREFIX_ . 'ets_geo_rule` grl ON (grl.`id_rule` = gcrl.`id_rule`)
		     WHERE `block_user`= 1 OR `disable_geo` = 1 
            GROUP BY id_country_rule 
        ) bu ON bu.`id_country_rule` = c.`id_country`
		WHERE 1' . ($active ? ' AND c.active = 1' : '') . ($containStates ? ' AND c.`contains_states` = ' . (int) $containStates : '') . ' AND (bu.`block_user` != 1 AND bu.`disable_geo` != 1 OR bu.`block_user` is NULL) 
		ORDER BY cl.name ASC';

        return $this->getDb()->executeS($sql);
    }

    /**
     * @param string|null $orderBy
     * @param bool $desc
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array|bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getStates($orderBy = null, $desc = false, $limit = null, $offset = null)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'state`';
        if ($orderBy && in_array(strtolower($orderBy), ['id_state', 'id_country', 'id_zone', 'name', 'iso_code', 'tax_behavior', 'active'])) {
            $sql .= ' ORDER BY ' . strtolower($orderBy) . ($desc ? ' DESC' : ' ASC');
        }
        if ($limit && Validate::isUnsignedInt($limit)) {
            $sql .= ' LIMIT ' . $limit;
            if ($offset && Validate::isUnsignedInt($offset)) {
                $sql .= ' OFFSET ' . $offset;
            }
        }

        return $this->getDb()->executeS($sql);
    }

    /**
     * @return int|null
     */
    public function getMatchingUrlShopId()
    {
        $host = Tools::getHttpHost();
        $request_uri = rawurldecode($_SERVER['REQUEST_URI']);

        $sql = 'SELECT s.id_shop, CONCAT(su.physical_uri, su.virtual_uri) AS uri, su.domain, su.main
                    FROM `' . _DB_PREFIX_ . 'shop_url` su
                    LEFT JOIN `' . _DB_PREFIX_ . 'shop` s ON (s.id_shop = su.id_shop)
                    WHERE (su.domain = \'' . pSQL($host) . '\' OR su.domain_ssl = \'' . pSQL($host) . '\')
                        AND s.active = 1
                        AND s.deleted = 0
                    ORDER BY LENGTH(CONCAT(su.physical_uri, su.virtual_uri)) DESC';

        try {
            $result = Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            return null;
        }

        foreach ($result as $row) {
            // A shop matching current URL was found
            if (preg_match('#^' . preg_quote($row['uri'], '#') . '#i', $request_uri)) {
                return (int) $row['id_shop'];
            }
        }

        return null;
    }

    // <editor-fold desc="Install & uninstall" defaultstate="collapsed">
    /**
     * SQL statements to exec when running un-install
     *
     * @return bool
     */
    public function executeUninstall()
    {
        if (!$this->getDb()->execute('DELETE FROM `' . _DB_PREFIX_ . 'address` WHERE id_address IN (SELECT id_address FROM `' . _DB_PREFIX_ . 'ets_geo_address_detected`)')) {
            return false;
        }

        return $this->getDb()->execute('DROP TABLE IF EXISTS 
			    `' . _DB_PREFIX_ . 'ets_geo_rule`,
				`' . _DB_PREFIX_ . 'ets_geo_country_rule`,
				`' . _DB_PREFIX_ . 'ets_geo_visit`,
				`' . _DB_PREFIX_ . 'ets_geo_visit_day`,
				`' . _DB_PREFIX_ . 'ets_geo_currency`,
				`' . _DB_PREFIX_ . 'ets_geo_address_detected`');
    }

    /**
     * SQL statements to exec when running install
     *
     * @return bool
     */
    public function executeInstall()
    {
        $sql = ['CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_geo_rule` (
                    `id_rule` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `id_shop` int(11) NOT NULL,
                    `enabled` tinyint(1)unsigned NOT NULL DEFAULT \'1\',
                    `disable_geo` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
                    `lang_to_set` int(11) unsigned NOT NULL DEFAULT \'0\',
                    `currency_to_set` int(11) unsigned NOT NULL DEFAULT \'0\',
                    `block_user` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
                    `all_countries` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
                    `hidden_products` text DEFAULT NULL,
                    `url_redirect` varchar(255) NOT NULL,
                    `priority` varchar(50) NOT NULL DEFAULT \'1\',
                    PRIMARY KEY (`id_rule`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
            'CREATE INDEX idx_id_shop ON `' . _DB_PREFIX_ . 'ets_geo_rule` (id_shop);',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_geo_country_rule`(
    `id_rule` int(11) unsigned NOT NULL DEFAULT \'0\',
                    `id_country_rule` int(11) unsigned NOT NULL DEFAULT \'0\',
                    PRIMARY KEY (`id_rule`,`id_country_rule`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_geo_visit`(
    `id_country` int(11) unsigned NOT NULL DEFAULT \'0\',
                    `day` int(2) unsigned NOT NULL DEFAULT \'0\',
                    `month` int(2) unsigned NOT NULL DEFAULT \'0\',
                    `year` int(4) unsigned NOT NULL DEFAULT \'0\',
                    `visit` int(11) unsigned NOT NULL DEFAULT \'0\',
                    `last_ip` varchar(50) NULL,
                    `last_visit_time` datetime NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_geo_visit_day`(
    `day` int(2) unsigned NOT NULL DEFAULT \'0\',
                    `month` int(2) unsigned NOT NULL DEFAULT \'0\',
                    `year` int(4) unsigned NOT NULL DEFAULT \'0\',
                    `ip_visit` varchar(50) NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_geo_address_detected`(
    `id_address` int(4) unsigned NOT NULL,
                    `iso_code_country` varchar(4) NOT NULL,
                    PRIMARY KEY (`id_address`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
            'CREATE INDEX idx_iso_code_country ON `' . _DB_PREFIX_ . 'ets_geo_address_detected` (iso_code_country);',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_geo_currency`(
                    `iso_code_country` varchar(4) NOT NULL,
                    `iso_code_currency` varchar(4) NOT NULL,
                    PRIMARY KEY (`iso_code_country`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;', ];
        foreach ($sql as $str) {
            $rs = $this->getDb()->execute($str);
            if (!$rs) {
                return false;
            }
        }

        return $this->getDb()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ets_geo_currency` VALUES("NZ", "NZD"),("CK", "NZD"),("NU", "NZD"),("PN", "NZD"),("TK", "NZD"),("AU", "AUD"),("CX", "AUD")
                ,("CC", "AUD"),("HM", "AUD"),("KI", "AUD"),("NR", "AUD"),("NF", "AUD"),("TV", "AUD"),("AS", "EUR"),("AD", "EUR"),("AT", "EUR"),("BE", "EUR"),("FI", "EUR")
                ,("FR", "EUR"),("GF", "EUR"),("TF", "EUR"),("DE", "EUR"),("GR", "EUR"),("GP", "EUR"),("IE", "EUR"),("IT", "EUR"),("LU", "EUR"),("MQ", "EUR"),("YT", "EUR")
                ,("MC", "EUR"),("NL", "EUR"),("PT", "EUR"),("RE", "EUR"),("WS", "EUR"),("SM", "EUR"),("SI", "EUR"),("ES", "EUR"),("VA", "EUR"),("GS", "GBP"),("GB", "GBP")
                ,("JE", "GBP"),("IO", "USD"),("GU", "USD"),("MH", "USD"),("FM", "USD"),("MP", "USD"),("PW", "USD"),("PR", "USD"),("TC", "USD"),("US", "USD"),("UM", "USD")
                ,("VG", "USD"),("VI", "USD"),("HK", "HKD"),("CA", "CAD"),("JP", "JPY"),("AF", "AFN"),("AL", "ALL"),("DZ", "DZD"),("AI", "XCD"),("AG", "XCD"),("DM", "XCD")
                ,("GD", "XCD"),("MS", "XCD"),("KN", "XCD"),("LC", "XCD"),("VC", "XCD"),("AR", "ARS"),("AM", "AMD"),("AW", "ANG"),("AN", "ANG"),("AZ", "AZN"),("BS", "BSD")
                ,("BH", "BHD"),("BD", "BDT"),("BB", "BBD"),("BY", "BYR"),("BZ", "BZD"),("BJ", "XOF"),("BF", "XOF"),("GW", "XOF"),("CI", "XOF"),("ML", "XOF"),("NE", "XOF")
                ,("SN", "XOF"),("TG", "XOF"),("BM", "BMD"),("BT", "INR"),("IN", "INR"),("BO", "BOB"),("BW", "BWP"),("BV", "NOK"),("NO", "NOK"),("SJ", "NOK"),("BR", "BRL")
                ,("BN", "BND"),("BG", "BGN"),("BI", "BIF"),("KH", "KHR"),("CM", "XAF"),("CF", "XAF"),("TD", "XAF"),("CG", "XAF"),("GQ", "XAF"),("GA", "XAF"),("CV", "CVE")
                ,("KY", "KYD"),("CL", "CLP"),("CN", "CNY"),("CO", "COP"),("KM", "KMF"),("CD", "CDF"),("CR", "CRC"),("HR", "HRK"),("CU", "CUP"),("CY", "CYP"),("CZ", "CZK")
                ,("DK", "DKK"),("FO", "DKK"),("GL", "DKK"),("DJ", "DJF"),("DO", "DOP"),("TP", "IDR"),("ID", "IDR"),("EC", "ECS"),("EG", "EGP"),("SV", "SVC"),("ER", "ETB")
                ,("ET", "ETB"),("EE", "EEK"),("FK", "FKP"),("FJ", "FJD"),("PF", "XPF"),("NC", "XPF"),("WF", "XPF"),("GM", "GMD"),("GE", "GEL"),("GI", "GIP"),("GT", "GTQ")
                ,("GN", "GNF"),("GY", "GYD"),("HT", "HTG"),("HN", "HNL"),("HU", "HUF"),("IS", "ISK"),("IR", "IRR"),("IQ", "IQD"),("IL", "ILS"),("JM", "JMD"),("JO", "JOD")
                ,("KZ", "KZT"),("KE", "KES"),("KP", "KPW"),("KR", "KRW"),("KW", "KWD"),("KG", "KGS"),("LA", "LAK"),("LV", "LVL"),("LB", "LBP"),("LS", "LSL"),("LR", "LRD")
                ,("LY", "LYD"),("LI", "CHF"),("CH", "CHF"),("LT", "LTL"),("MO", "MOP"),("MK", "MKD"),("MG", "MGA"),("MW", "MWK"),("MY", "MYR"),("MV", "MVR"),("MT", "MTL")
                ,("MR", "MRO"),("MU", "MUR"),("MX", "MXN"),("MD", "MDL"),("MN", "MNT"),("MA", "MAD"),("EH", "MAD"),("MZ", "MZN"),("MM", "MMK"),("NA", "NAD"),("NP", "NPR")
                ,("NI", "NIO"),("NG", "NGN"),("OM", "OMR"),("PK", "PKR"),("PA", "PAB"),("PG", "PGK"),("PY", "PYG"),("PE", "PEN"),("PH", "PHP"),("PL", "PLN"),("QA", "QAR")
                ,("RO", "RON"),("RU", "RUB"),("RW", "RWF"),("ST", "STD"),("SA", "SAR"),("SC", "SCR"),("SL", "SLL"),("SG", "SGD"),("SK", "SKK"),("SB", "SBD"),("SO", "SOS")
                ,("ZA", "ZAR"),("LK", "LKR"),("SD", "SDG"),("SR", "SRD"),("SZ", "SZL"),("SE", "SEK"),("SY", "SYP"),("TW", "TWD"),("TJ", "TJS"),("TZ", "TZS"),("TH", "THB")
                ,("TO", "TOP"),("TT", "TTD"),("TN", "TND"),("TR", "TRY"),("TM", "TMT"),("UG", "UGX"),("UA", "UAH"),("AE", "AED"),("UY", "UYU"),("UZ", "UZS"),("VU", "VUV")
                ,("VE", "VEF"),("VN", "VND"),("YE", "YER"),("ZM", "ZMK"),("ZW", "ZWD"),("AX", "EUR"),("AO", "AOA"),("AQ", "AQD"),("BA", "BAM"),("GH", "GHS")
                ,("GG", "GGP"),("IM", "GBP"),("ME", "EUR"),("PS", "JOD"),("BL", "EUR"),("SH", "GBP"),("MF", "ANG"),("PM", "EUR"),("RS", "RSD")
                ,("USAF", "USD");
            ');
    }
    // </editor-fold>

    // <editor-fold desc="Unused any where" defaultstate="collapsed">
    /* DELME : these methods moved some where from this module */
    private function getNameLangByIdLang($id_lang, $name_from = null)
    {
        if (!$id_lang) {
            return false;
        }
        $sql_name_full = 'SELECT l.`name`
                            FROM `' . _DB_PREFIX_ . 'lang` l
                            LEFT JOIN `' . _DB_PREFIX_ . 'lang_shop` ls ON (l.`id_lang` = ls.`id_lang` AND ls.`id_shop`= ' . (int) $this->context->shop->id . ' )
                            WHERE l.`id_lang` = ' . (int) $id_lang . ' AND l.`active`=1 ';
        if (!$name_full = Db::getInstance()->getRow($sql_name_full)) {
            return false;
        }
        $name_full = explode('(', $name_full['name']);
        if ($name_from) {
            return $name_full[0];
        }
        $name = str_replace(')', '', $name_full[1]);

        return $name;
    }
    // </editor-fold>
}
