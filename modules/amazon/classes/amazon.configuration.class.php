<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }

require_once(dirname(__FILE__) . '/../common/configuration.class.php');

class AmazonConfiguration extends CommonConfiguration
{
    public static $module = 'AMAZON';
    public static $configuration_table = 'amazon_configuration';
    public static $old_configuration_table = 'marketplace_configuration';

    private static $getByDirectSql = null;
    private static $debug_configuration = false;
    private static $disable_multi_shop_check = false;

    /**
     * todo: Remove in future because identical with parent. This is added due to outdated CommonConfiguration on other modules (but loaded before)
     * @param string $key
     * @param null $idShopGroup
     * @param null $idShop
     * @return int
     */
    public static function getIdByName($key, $idShopGroup = null, $idShop = null)
    {
        self::setDefinition();
        $configuration = ConfigurationCore::getIdByName($key, $idShopGroup, $idShop);
        self::unsetDefinition();

        return $configuration;
    }

    /**
     * As per her sister: Configuration::deleteByName
     * WARNING: Should not use this function, it clears all the configuration in cache. Reference:
     *  - v4.10.2
     *  - https://common-services-force.monday.com/boards/1971464818/pulses/2847646354
     * @param $configuration_key
     * @return bool
     */
    public static function deleteKey($configuration_key)
    {
        $prestashop_configuration_key = sprintf(static::$module . '_%s', AmazonTools::strtoupper($configuration_key));
        $marketplace_configuration_key = AmazonTools::strtolower($configuration_key);

        $pass = Configuration::deleteByName($prestashop_configuration_key);

        if (AmazonTools::tableExists(_DB_PREFIX_ . self::$configuration_table)) {
            $pass = ($pass && Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . self::$configuration_table . '`
                    WHERE `name`="' . pSQL($prestashop_configuration_key) . '"'));
        }

        if (AmazonTools::tableExists(_DB_PREFIX_ . Amazon::TABLE_MARKETPLACE_CONFIGURATION)) {
            return ($pass && Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . Amazon::TABLE_MARKETPLACE_CONFIGURATION . '`
                    WHERE `marketplace`="' . pSQL(Amazon::MARKETPLACE) . '" AND `configuration`="' . pSQL($marketplace_configuration_key) . '"'));
        }

        return ($pass);
    }

    public static function filter($obj)
    {
        return ($obj);//TODO: filter SimpleXMLElements

        if ($obj instanceof SimpleXMLElement) {
            return (null);
        } elseif (is_object($obj)) {
            foreach ($obj as $key => $val) {
                $obj->{$key} = self::filter($val);
            }
        } elseif (is_array($obj)) {
            foreach ($obj as $key => $val) {
                $obj[$key] = self::filter($val);
            }
        }

        return $obj;
    }

    /**
     * Update Amazon config for all shop
     * @param string $key
     * @param mixed $values
     * @param bool $html
     *
     * @return bool
     * todo: Move this function to CommonConfiguration
     */
    public static function updateGlobalValue($key, $values, $html = false)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            // Version 1.5 and below does not use Configuration::$definition
            // Version 1.4 does not have multi-shop
            $where = "`name` = '{$key}'";
            if (CommonTools::getPsVersion() == '1.5') {
                $where .= Configuration::sqlRestriction(null, null);
            }

            $db = Db::getInstance();
            $table = _DB_PREFIX_ . static::$configuration_table;
            $now = date('Y-m-d H:i:s');

            $row = $db->getRow("SELECT * FROM `{$table}` WHERE {$where}");
            if ($row) {
                $result = $db->execute("UPDATE `{$table}` SET `value` = '" . pSQL($values, $html) . "', `date_upd` = '{$now}' WHERE {$where}");
            } else {
                $result = $db->execute("INSERT INTO `{$table}`(`name`, `value`, `date_add`, `date_upd`) VALUES('{$key}', '" . pSQL($values, $html) . "', '{$now}', '{$now}')");
            }
        } else {
            self::setDefinition();
            $result = parent::updateGlobalValue($key, $values, $html);
            self::unsetDefinition();
        }

        return $result;
    }

    /**
     * Get Amazon config for all shop
     * @param $key
     * @param null $id_lang
     *
     * @return string
     * todo: Move this function to CommonConfiguration
     */
    public static function getGlobalValue($key, $id_lang = null)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $db = Db::getInstance();
            $sql = "SELECT `value` FROM `" . _DB_PREFIX_ . static::$configuration_table . "` WHERE `name` = '" . pSQL($key) . "'";

            if (CommonTools::getPsVersion() == '1.5') {
                $result = $db->getValue($sql . Configuration::sqlRestriction(null, null));
            } else {
                $result = $db->getValue($sql);
            }
        } else {
            self::setDefinition();
            $result = parent::getGlobalValue($key, $id_lang);
            self::unsetDefinition();
        }

        return $result;
    }

    public static function get($configuration_key, $id_lang = null, $id_shop_group = null, $id_shop = null, $default = false)
    {

        if (self::isDebugConfigurationEnabled()) {
            CommonTools::p(sprintf('%s:#%d get: %s' . "\n", basename(__FILE__), __LINE__, $configuration_key));
        }

        /*if (!self::getByDirectSql() && !self::$disable_multi_shop_check) { // Todo: Remove disable shop check condition
            if (self::isDebugConfigurationEnabled()) {
                CommonTools::p(sprintf('%s:#%d !getByDirectSq: %s' . "\n", basename(__FILE__), __LINE__, self::getByDirectSql()));
            }
            return parent::get($configuration_key, $id_lang, $id_shop_group, $id_shop, $default);
        }*/

        if (CommonTools::tableExists(_DB_PREFIX_ . static::$configuration_table)) {
            $sql = "SELECT `value` FROM `" . _DB_PREFIX_ . static::$configuration_table . "`
                    WHERE `name` = '" . pSQL(Tools::strtoupper(sprintf(static::$module . '_%s', $configuration_key))) . "'";
            $db = DB::getInstance();
            if (CommonTools::getPsVersion() != '1.4' && !self::$disable_multi_shop_check) { // Todo: Remove disable shop check condition
                $id_shop = Shop::getContextShopID(true);
                $id_shop_group = Shop::getContextShopGroupID(true);
                $sql .= Configuration::sqlRestriction($id_shop_group, $id_shop);
            }

            $result = $db->getValue($sql);

            if ($result !== false) {
                $convertToArray = mb_substr($result, 0, 1) == '{' || (strpos($result, '{') && strpos($result, '}'));
                if ($result == 'true' || $result == 'false') { // Check true/false text value
                    return filter_var($result, FILTER_VALIDATE_BOOLEAN);
                }
                $result = json_decode(self::returnValue($result), $convertToArray);
            }

            if (self::isDebugConfigurationEnabled()) {
                CommonTools::p(sprintf('%s:#%d SQL: %s' . "\n", basename(__FILE__), __LINE__, $sql));
                CommonTools::p(sprintf('%s:#%d Result: %s' . "\n", basename(__FILE__), __LINE__, $result));
            }
            
            return $result;
        }

        return $default;
    }

    public static function updateValue(
        $configuration_key,
        $data,
        $html = false,
        $id_shop_group = null,
        $id_shop = null
    )
    {
        $key = Tools::strtoupper(sprintf(static::$module . '_%s', $configuration_key));
        if (!Validate::isConfigName($key)) {
            die(Tools::displayError('Not a configuration name!'));
        }

        // Get multi-shop id for modern versions
        if (CommonTools::getPsVersion() != '1.4') {
            $id_shop = !$id_shop ? Shop::getContextShopID(true) : $id_shop;
            $id_shop_group = !$id_shop_group ? Shop::getContextShopGroupID(true) : $id_shop_group;
        }

        // json_encode data
        $data = json_encode($data);
        $id_shop_group = (int)$id_shop_group ? (int)$id_shop_group : null;
        $id_shop = (int)$id_shop ? (int)$id_shop : null;
        $now = date('Y-m-d H:i:s');

        // Update database
        $db = Db::getInstance();

        // 1 - Try to update to module specific table static:$configuration_table
        $mConfigTbl = _DB_PREFIX_ . static::$configuration_table;
        if (CommonTools::tableExists($mConfigTbl)) {
            if (CommonTools::getPsVersion() == '1.7') {
                return self::_updateValuePs17($key, $data, $html, $id_shop_group, $id_shop);
            } elseif (CommonTools::getPsVersion() == '1.6') {
                // Fix hard code bug in PS-1.6.1.1: ConfigurationCore:446
                self::setDefinition();
                if (!Configuration::hasKey($key, null, $id_shop_group, $id_shop)) {
                    if (!Configuration::getIdByName($key, $id_shop_group, $id_shop)) {
                        $update_data = array(
                            'name' => pSQL($key),
                            'value' => pSQL($data),
                            'date_add' => pSQL($now),
                            'date_upd' => pSQL($now),
                            'id_shop_group' => $id_shop_group,
                            'id_shop' => $id_shop
                        );
                        $db->insert(static::$configuration_table, $update_data, true);
                    }
                }
                $result = Configuration::updateValue($key, $data, $html, $id_shop_group, $id_shop);
                self::unsetDefinition();
            } elseif (CommonTools::getPsVersion() == '1.5') {
                $where = '`name` = "' . pSQL($key) . '"' . Configuration::sqlRestriction($id_shop_group, $id_shop);
                $row = $db->getRow('SELECT * FROM `' . pSQL($mConfigTbl) . '` WHERE ' . $where);
                if ($row) {
                    $idRow = $row['id_configuration'];
                    $result = $db->execute('UPDATE `' . pSQL($mConfigTbl) . '` 
                            SET `value` = "' . pSQL($data, $html) . '", `date_upd` = "' . pSQL($now) . '" 
                            WHERE `id_configuration` = ' . (int)$idRow);
                } else {
                    $result = $db->execute('
                            INSERT INTO `' . pSQL($mConfigTbl) . '`(
                                `id_shop_group`,
                                `id_shop`,
                                `name`,
                                `value`,
                                `date_add`,
                                `date_upd`
                            ) VALUES (
                                ' . (int)$id_shop_group . ',
                                ' . (int)$id_shop . ',
                                "' . pSQL($key) . '",
                                "' . pSQL($data, $html) . '",
                                "' . pSQL($now) . '",
                                "' . pSQL($now) . '"
                            )
                        ');
                }
            } else {
                // DbCore in Ps1.4 does not have update() or insert()
                $where = '`name` = "' . pSQL($key) . '"';
                $row = $db->getRow('SELECT * FROM `' . pSQL($mConfigTbl) . '` WHERE ' . $where);
                if ($row) {
                    $result = $db->execute('UPDATE `' . pSQL($mConfigTbl) . '` 
                            SET `value` = "' . pSQL($data, $html) . '", `date_upd` = "' . pSQL($now) . '" 
                            WHERE ' . $where);
                } else {
                    $result = $db->execute('
                            INSERT INTO `' . pSQL($mConfigTbl) . '`(
                                `name`,
                                `value`,
                                `date_add`,
                                `date_upd`
                            ) VALUES(
                                "' . pSQL($key) . '",
                                "' . pSQL($data, $html) . '",
                                "' . pSQL($now) . '",
                                "' . pSQL($now) . '"
                            )
                        ');
                }
            }

            return $result;
        }

        // Old table need to save with base64_encode
        $marketplace_configuration_key = Tools::strtolower($configuration_key);
        $data_encode = base64_encode($data);

        // 2 - Update on general table if specific table not exist
        if (CommonTools::tableExists(_DB_PREFIX_ . self::$configuration_table)) {
            $sql = 'REPLACE INTO `' . pSQL(_DB_PREFIX_ . self::$configuration_table) . '`(
                    `marketplace`,
                    `configuration`,
                    `value`
                ) VALUES(
                    "' . pSQL(static::$module) . '",
                    "' . pSQL($marketplace_configuration_key) . '",
                    "' . pSQL($data_encode) . '"
                )';

            return Db::getInstance()->execute($sql);
        }

        // 3 - Last try on ps table
        if (CommonTools::getPsVersion() == '1.4') {
            return Configuration::updateValue($key, $data_encode, $html);
        } else {
            return Configuration::updateValue($key, $data_encode, $html, $id_shop_group, $id_shop);
        }
    }

    private static function getByDirectSql()
    {
        if (is_null(self::$getByDirectSql)) {
            if (self::isDebugConfigurationEnabled()) {
                CommonTools::p(sprintf('%s:#%d getByDirectSql is null' . "\n", basename(__FILE__), __LINE__));
            }
            self::$getByDirectSql = (bool)Configuration::get(AmazonConstant::CONFIG_GET_BY_DIRECT_SQL);
        }

        if (self::isDebugConfigurationEnabled()) {
            CommonTools::p(sprintf('%s:#%d getByDirectSql: %d' . "\n", basename(__FILE__), __LINE__, self::$getByDirectSql));
        }

        return self::$getByDirectSql;
    }

    public static function setDebugConfiguration($debug_mode = false)
    {
        self::$debug_configuration = (bool)$debug_mode;
    }

    private static function isDebugConfigurationEnabled()
    {
        return (bool)self::$debug_configuration;
    }

    /**
     * This fix issue of PS context shop not correct in Module method
     * Todo: Check and remove in future when ps fix this issue
     * @param $uncheck_multishop
     * @return bool
     */
    public static function setMultiShopCheck($amazonFeatures)
    {
        if (isset($amazonFeatures['uncheck_multishop'])) {
            self::$disable_multi_shop_check = (bool)$amazonFeatures['uncheck_multishop'];
        }
    }
}
