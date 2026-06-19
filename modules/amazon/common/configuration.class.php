<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

/**
 * Case study:
 * Reduce the usage of `Configuration::deleteByName()`, it causes trouble for sequence getting calls.
 * Reproduce and explanation:
 * - `Configuration::deleteByName()` delete all caches ($_cache, $_new_cache_shop, $_new_cache_group, $_new_cache_global)
 * - `AmazonConfiguration::updateGlobalValue($key)` set just a single `$key` for `$_cache` array
 * - `AmazonConfiguration::get($anotherKey)` return empty / null because `$_cache` only has `$key` as the result of previous call,
 *    any `$anotherKey` which is different than `$key` produces empty output.
 */
if (!defined('_PS_VERSION_')) { exit; }
if (! class_exists('CommonConfiguration')) {
    abstract class CommonConfiguration extends Configuration
    {
        // Override-able
        public static $module;
        public static $configuration_table = 'marketplace_configuration';

        public static $definition_backup;

        // Override Configuration definition
        public static function setDefinition()
        {
            self::$definition_backup = Configuration::$definition;
            Configuration::$definition['table'] = static::$configuration_table;
        }

        // Restore Configuration definition
        public static function unsetDefinition()
        {
            Configuration::$definition = self::$definition_backup;
        }

        public static function getIdByName($key, $idShopGroup = null, $idShop = null)
        {
            self::setDefinition();
            $configuration = parent::getIdByName($key, $idShopGroup, $idShop);
            self::unsetDefinition();

            return $configuration;
        }

        /**
         * Update configuration value
         *
         * @param string $configuration_key
         * @param mixed $data
         * @param bool $html
         * @param null $id_shop_group
         * @param null $id_shop
         *
         * @return bool
         */
        public static function updateValue(
            $configuration_key,
            $data,
            $html = false,
            $id_shop_group = null,
            $id_shop = null
        ) {
            $key = Tools::strtoupper(sprintf(static::$module.'_%s', $configuration_key));
            if (!Validate::isConfigName($key)) {
                die(Tools::displayError('Not a configuration name!'));
            }

            // Get multi-shop id for modern versions
            if (CommonTools::getPsVersion() != '1.4') {
                $id_shop = Shop::getContextShopID(true);
                $id_shop_group = Shop::getContextShopGroupID(true);
            }

            // Sanitize data
            $data          = json_encode($data);
            $id_shop_group = (int)$id_shop_group ? (int)$id_shop_group : null;
            $id_shop       = (int)$id_shop ? (int)$id_shop : null;
            $now           = date('Y-m-d H:i:s');

            // Update database
            $db = Db::getInstance();

            // 1 - Try to update to module specific table static:$configuration_table
            $mConfigTbl = _DB_PREFIX_.static::$configuration_table;
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
                    $where = '`name` = "'.pSQL($key).'"'.Configuration::sqlRestriction($id_shop_group, $id_shop);
                    $row = $db->getRow('SELECT * FROM `'.pSQL($mConfigTbl).'` WHERE '.$where);
                    if ($row) {
                        $idRow = $row['id_configuration'];
                        $result = $db->execute('UPDATE `'.pSQL($mConfigTbl).'` 
                            SET `value` = "'.pSQL($data, $html).'", `date_upd` = "'.pSQL($now).'" 
                            WHERE `id_configuration` = '.(int)$idRow);
                    } else {
                        $result = $db->execute('
                            INSERT INTO `'.pSQL($mConfigTbl).'`(
                                `id_shop_group`,
                                `id_shop`,
                                `name`,
                                `value`,
                                `date_add`,
                                `date_upd`
                            ) VALUES (
                                '.(int)$id_shop_group.',
                                '.(int)$id_shop.',
                                "'.pSQL($key).'",
                                "'.pSQL($data, $html).'",
                                "'.pSQL($now).'",
                                "'.pSQL($now).'"
                            )
                        ');
                    }
                } else {
                    // DbCore in Ps1.4 does not have update() or insert()
                    $where = '`name` = "'.pSQL($key).'"';
                    $row = $db->getRow('SELECT * FROM `'.pSQL($mConfigTbl).'` WHERE '.$where);
                    if ($row) {
                        $result = $db->execute('UPDATE `'.pSQL($mConfigTbl).'` 
                            SET `value` = "'.pSQL($data, $html).'", `date_upd` = "'.pSQL($now).'" 
                            WHERE '.$where);
                    } else {
                        $result = $db->execute('
                            INSERT INTO `'.pSQL($mConfigTbl).'`(
                                `name`,
                                `value`,
                                `date_add`,
                                `date_upd`
                            ) VALUES(
                                "'.pSQL($key).'",
                                "'.pSQL($data, $html).'",
                                "'.pSQL($now).'",
                                "'.pSQL($now).'"
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
            if (CommonTools::tableExists(_DB_PREFIX_.self::$configuration_table)) {
                $sql = 'REPLACE INTO `'.pSQL(_DB_PREFIX_.self::$configuration_table).'`(
                    `marketplace`,
                    `configuration`,
                    `value`
                ) VALUES(
                    "'.pSQL(static::$module).'",
                    "'.pSQL($marketplace_configuration_key).'",
                    "'.pSQL($data_encode).'"
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

        /**
         * Get configuration from default table, if not exist get from ps table
         *
         * @param string $configuration_key
         * @param null $id_lang
         * @param null $id_shop_group
         * @param null $id_shop
         * @param bool $default
         *
         * @return mixed|string
         */
        public static function get(
            $configuration_key,
            $id_lang = null,
            $id_shop_group = null,
            $id_shop = null,
            $default = false
        ) {
            $prestashop_configuration_key = Tools::strtoupper(sprintf(static::$module.'_%s', $configuration_key));
            $marketplace_configuration_key = Tools::strtolower($configuration_key);
            $db = DB::getInstance();

            // Query on child table, use static::$configuration_table
            if (CommonTools::tableExists(_DB_PREFIX_.static::$configuration_table)) {
                $sql = "SELECT `value` FROM `"._DB_PREFIX_.static::$configuration_table."` 
                    WHERE `name` = '".pSQL($prestashop_configuration_key)."'";
                if (CommonTools::getPsVersion() == '1.6' || CommonTools::getPsVersion() == '1.7') {
                    self::setDefinition();
                    if (!isset(self::$_cache[self::$definition['table']]) || (isset(self::$_new_cache_global) && empty(self::$_new_cache_global[$prestashop_configuration_key][0]))) {
                        // Fix for PS 1.7
                        self::loadConfiguration();
                    }
                    $result = Configuration::get(
                        $prestashop_configuration_key,
                        $id_lang,
                        $id_shop_group,
                        $id_shop,
                        $default
                    );
                    self::unsetDefinition();
                } else {
                    if (CommonTools::getPsVersion() == '1.5') {
                        $id_shop = Shop::getContextShopID(true);
                        $id_shop_group = Shop::getContextShopGroupID(true);
                        $result = $db->getValue($sql.Configuration::sqlRestriction($id_shop_group, $id_shop));
                    } else {
                        $result = $db->getValue($sql);
                    }
                }
                if ($result) {
                    $val = self::returnValue($result);
                    if (self::isSerialized($val)) {
                        return unserialize($val);
                    }
                    return json_decode($val, true);;
                    
                }
            }

            // If specific module table does not contain the value, go up to general table
            if (CommonTools::tableExists(_DB_PREFIX_.self::$configuration_table)) {
                $sql = 'SELECT `value`
                    FROM `'.pSQL(_DB_PREFIX_.self::$configuration_table).'`
                    WHERE `marketplace` = "'.pSQL(static::$module).'"
                    AND `configuration` = "'.pSQL($marketplace_configuration_key).'"';
                $result = Db::getInstance()->getRow($sql);

                if ($result && isset($result, $result['value'])) {
                    if (self::isSerialized(self::returnValue($result['value']))) {
                        return unserialize(self::returnValue($result['value']));
                    }
                    return json_decode(self::returnValue($result['value']), true);
                }
            }

            // Old table also doesn't have the value, last try on ps table
            if (CommonTools::getPsVersion() == '1.4') {
                return json_decode(self::returnValue(Configuration::get($prestashop_configuration_key, $id_lang)), true);
            } else {
                $val = self::returnValue(Configuration::get(
                    $prestashop_configuration_key,
                    $id_lang,
                    $id_shop_group,
                    $id_shop,
                    $default
                ));
                if (self::isSerialized($val)) {
                    return unserialize($val);
                }
                return json_decode($val, true);
                
                return null;
            }
        }

        /**
         * Check if value is base64 encoded and returns it decoded
         * @param $configuration
         *
         * @return bool|string
         */
        public static function returnValue($configuration)
        {
            if (!is_string($configuration)) {
                return $configuration;
            }
            if (base64_encode(base64_decode($configuration, true)) === $configuration) {//TODO: Validation: Use to evaluate base64 encoded values, required
                //TODO: Validation: Use to evaluate base64 encoded values, required
                $value = base64_decode($configuration, true); //TODO: Validation: Use to evaluate base64 encoded values, required
            } else {
                //TODO: Validation: Required by test above
                $value = $configuration;
            }
            return($value);
        }

        /**
         * Check if Combination is active
         *
         * @return bool
         */
        public static function combinationIsFeatureActive()
        {
            return CommonTools::getPsVersion() == '1.4' ? true : Combination::isFeatureActive();
        }

        /**
         * Check if Feature if active
         *
         * @return bool
         */
        public static function featureIsFeatureActive()
        {
            return CommonTools::getPsVersion() == '1.4' ? true : Feature::isFeatureActive();
        }

        /**
         * Check if multishop is active
         *
         * @return bool
         */
        public static function shopIsFeatureActive()
        {
            return CommonTools::getPsVersion() == '1.4' ? false : Shop::isFeatureActive();
        }

        /**
         * Update value for PrestaShop version 1.7
         *
         * @param $key
         * @param $value
         * @param $html
         * @param $id_shop_group
         * @param $id_shop
         *
         * @return bool
         */
        protected static function _updateValuePs17($key, $value, $html, $id_shop_group, $id_shop)
        {
            self::setDefinition();
            if (! isset(self::$_cache[self::$definition['table']])) {
                self::loadConfiguration();
            }

            $now = date('Y-m-d H:i:s');
            if (! self::_configHasKeyPs17($key, null, $id_shop_group, $id_shop)) {
                $data = array(
                    'id_shop_group' => (int)$id_shop_group,
                    'id_shop'       => (int)$id_shop,
                    'name'          => pSQL($key),
                    'value'         => pSQL($value),
                    'date_add'      => pSQL($now),
                    'date_upd'      => pSQL($now),
                );
                $result = Db::getInstance()->insert(self::$definition['table'], $data, true);
            } else {
                $result = Db::getInstance()->update(self::$definition['table'], array(
                    'value'     => pSQL($value),
                    'date_upd'  => pSQL($now),
                ), '`name` = \''.$key.'\''.Configuration::sqlRestriction($id_shop_group, $id_shop), 1, true);
            }
            self::set($key, $value, $id_shop_group, $id_shop);
            self::unsetDefinition();

            return $result;
        }

        // Check if cache configurations contain specific key, ConfigurationCore::hasKey - PS1.6
        protected static function _configHasKeyPs17($key, $id_lang = null, $id_shop_group = null, $id_shop = null)
        {
            if (!is_int($key) && !is_string($key)) {
                return false;
            }

            $id_lang = (int)$id_lang;

            if ($id_shop) {
                return isset(self::$_cache[self::$definition['table']][$id_lang]['shop'][$id_shop])
                       && (isset(self::$_cache[self::$definition['table']][$id_lang]['shop'][$id_shop][$key])
                           || array_key_exists($key, self::$_cache[self::$definition['table']][$id_lang]['shop'][$id_shop]));
            } elseif ($id_shop_group) {
                return isset(self::$_cache[self::$definition['table']][$id_lang]['group'][$id_shop_group])
                       && (isset(self::$_cache[self::$definition['table']][$id_lang]['group'][$id_shop_group][$key])
                           || array_key_exists($key, self::$_cache[self::$definition['table']][$id_lang]['group'][$id_shop_group]));
            }

            return isset(self::$_cache[self::$definition['table']][$id_lang]['global'])
                   && (isset(self::$_cache[self::$definition['table']][$id_lang]['global'][$key])
                       ||  array_key_exists($key, self::$_cache[self::$definition['table']][$id_lang]['global']));
        }

         /**
         * @param $data
         * @param $strict
         * @return bool
         */
        private static function isSerialized($data, $strict = true)
        {
            // If it isn't a string, it isn't serialized.
            if (!is_string($data)) {
                return false;
            }
            $data = trim($data);
            if ('N;' === $data) {
                return true;
            }
            if (strlen($data) < 4) {
                return false;
            }
            if (':' !== $data[1]) {
                return false;
            }
            if ($strict) {
                $lastc = substr($data, -1);
                if (';' !== $lastc && '}' !== $lastc) {
                    return false;
                }
            } else {
                $semicolon = strpos($data, ';');
                $brace = strpos($data, '}');
                // Either ; or } must exist.
                if (false === $semicolon && false === $brace) {
                    return false;
                }
                // But neither must be in the first X characters.
                if (false !== $semicolon && $semicolon < 3) {
                    return false;
                }
                if (false !== $brace && $brace < 4) {
                    return false;
                }
            }
            $token = $data[0];
            switch ($token) {
                case 's':
                    if ($strict) {
                        if ('"' !== substr($data, -2, 1)) {
                            return false;
                        }
                    } elseif (!str_contains($data, '"')) {
                        return false;
                    }
                // Or else fall through.
                case 'a':
                case 'O':
                case 'E':
                    return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
                case 'b':
                case 'i':
                case 'd':
                    $end = $strict ? '$' : '';
                    return (bool)preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
            }
            return false;
        }
    }
}
