<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

require_once(dirname(__FILE__).'../../autoload.php');

class SdevAtosObjectModel extends ObjectModel
{
    /** @var null|string|array $unique - A string or an array of unique keys. */
    public static $unique = null;

    /** @var null|string|array $key - A string or an array of keys. */
    public static $key = null;

    /**
     * Create tables.
     *
     * @param mixed $unique - A string or an array of unique keys.
     * @param mixed $key - A string or an array of keys.
     * @return bool
     * @throws Exception
     */
    public static function installSQL()
    {
        try {
            if (static::$unique !== null && !is_string(static::$unique) && !is_array(static::$unique)) {
                throw new Exception('The parameter $unique must be a string or an array, '.gettype(static::$unique).' given !');
            }

            if (static::$key !== null && !is_string(static::$key) && !is_array(static::$key)) {
                throw new Exception('The parameter $key must be a string or an array, '.gettype(static::$key). ' given !');
            }

            $is_success = true;
            if (array_key_exists('table', static::$definition)) {
                $table = static::$definition['table'];
                $has_primary = ((bool)array_key_exists('primary', static::$definition) && static::$definition['primary']);
                $is_multilang = (bool)$has_primary
                    && (((bool)array_key_exists('multilang', static::$definition) && (bool)static::$definition['multilang'])
                    || ((bool)array_key_exists('multilang_shop', static::$definition) && (bool)static::$definition['multilang_shop']));
                $is_multishop = (bool)$has_primary && (bool)array_key_exists('multishop', static::$definition) && (bool)static::$definition['multishop'];
                $query_list = array();

                $main_query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.pSQL($table).'` (';
                if ((bool)$has_primary) {
                    $main_query .= "\n".'`'.pSQL(static::$definition['primary']).'` INT(11) NOT NULL AUTO_INCREMENT';
                }

                if ((bool)$is_multilang) {
                    $lang_query = str_replace(array($table, ' AUTO_INCREMENT'), array($table.'_lang', ''), $main_query);
                    $lang_query .= ','."\n".'`id_lang` INT(11) NOT NULL';
                }

                if ((bool)$is_multishop) {
                    $shop_query = str_replace(array($table, ' AUTO_INCREMENT'), array($table.'_shop', ''), $main_query);
                    $shop_query .= ','."\n".'`id_shop` INT(11) NOT NULL';
                    $shop_query .= ','."\n".'UNIQUE KEY `'.pSQL(static::$definition['primary']).'` (`'.pSQL(static::$definition['primary']).'`, `id_shop`)';
                    $shop_query .= "\n".') ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
                }

                foreach (static::$definition['fields'] as $column => $values) {
                    switch ($values['type']) {
                        case self::TYPE_BOOL:
                            $column_type = 'TINYINT(1)';
                            break;
                        case self::TYPE_DATE:
                            $column_type = 'DATETIME';
                            break;
                        case self::TYPE_FLOAT:
                            $column_type = 'DOUBLE(20,6)';
                            break;
                        case self::TYPE_INT:
                            $column_type = 'INT('.(isset($values['size']) ? (int)$values['size'] : 11).')';
                            break;
                        case self::TYPE_STRING:
                            $column_type = (isset($values['size']) ? 'VARCHAR('.(int)$values['size'].')' : 'TEXT');
                            break;
                    }

                    if (!(bool)$is_multilang || (bool)$is_multilang && (!array_key_exists('lang', $values) || (array_key_exists('lang', $values) && !(bool)$values['lang']))) {
                        $main_query .= ','."\n".'`'.pSQL($column).'` '.pSQL($column_type).' '.(array_key_exists('required', $values) && (bool)$values['required'] ? 'NOT NULL' : 'NULL');
                    } else {
                        $lang_query .= ','."\n".'`'.pSQL($column).'` '.pSQL($column_type).' '.(array_key_exists('required', $values) && (bool)$values['required'] ? 'NOT NULL' : 'NULL');
                    }
                }

                if ((bool)$has_primary) {
                    $main_query .= ','."\n".'PRIMARY KEY (`'.pSQL(static::$definition['primary']).'`)';
                }

                if ((bool)$is_multilang) {
                    $lang_query .= ','."\n".'UNIQUE KEY `'.pSQL(static::$definition['primary']).'` (`'.pSQL(static::$definition['primary']).'`, `id_lang`)';
                    $lang_query .= "\n".') ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
                }

                if (isset(static::$unique) && static::$unique) {
                    if (is_string(static::$unique)) {
                        $unique_query = ','."\n".'UNIQUE KEY `'.pSQL(static::$unique).'` (`'.pSQL(static::$unique).'`)';
                    } elseif (is_array(static::$unique) && !empty(static::$unique)) {
                        $unique_query = '';
                        foreach (static::$unique as $unique_name) {
                            if (is_string($unique_name)) {
                                $unique_query .= ','."\n".'UNIQUE KEY `'.pSQL($unique_name).'` (`'.pSQL($unique_name).'`)';
                            } elseif (is_array($unique_name) && !empty($unique_name)) {
                                $unique_query .= ','."\n".'UNIQUE KEY `'.pSQL($unique_name[0]).'` (`'.pSQL($unique_name[0]).'`';
                                unset($unique_name[0]);
                                foreach ($unique_name as $sub_key_name) {
                                    $unique_query .= ', `'.pSQL($sub_key_name).'`';
                                }
                                $unique_query .= ')';
                            } else {
                                $unique_query = false;
                                break;
                            }
                        }
                    } else {
                        $unique_query = false;
                    }

                    if ($unique_query) {
                        $main_query .= $unique_query;
                    }
                }

                if (isset(static::$key) && static::$key) {
                    if (is_string(static::$key)) {
                        $key_query = ','."\n".'KEY `'.pSQL(static::$key).'` (`'.pSQL(static::$key).'`)';
                    } elseif (is_array(static::$key) && !empty(static::$key)) {
                        $key_query = '';
                        foreach (static::$key as $key_name) {
                            if (is_string($key_name)) {
                                $key_query .= ','."\n".'KEY `'.pSQL($key_name).'` (`'.pSQL($key_name).'`)';
                            } elseif (is_array($key_name) && !empty($key_name)) {
                                $key_query .= ','."\n".'KEY `'.pSQL($key_name[0]).'` (`'.pSQL($key_name[0]).'`';
                                unset($key_name[0]);
                                foreach ($key_name as $sub_key_name) {
                                    $key_query .= ', `'.pSQL($sub_key_name).'`';
                                }
                                $key_query .= ')';
                            } else {
                                $key_query = false;
                                break;
                            }
                        }
                    } else {
                        $key_query = false;
                    }

                    if ($key_query) {
                        $main_query .= $key_query;
                    }
                }

                $main_query .= "\n".') ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
                $query_list[] = $main_query;
                if ((bool)$is_multilang) {
                    $query_list[] = $lang_query;
                }
                if ((bool)$is_multishop) {
                    $query_list[] = $shop_query;
                }

                foreach ($query_list as $query) {
                    if (!Db::getInstance()->execute($query)) {
                        $is_success = false;
                    }
                }
            }

            return (bool)$is_success;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Remove tables.
     *
     * @return bool
     */
    public static function uninstallSQL()
    {
        $is_success = true;
        $table_list = Db::getInstance()->executeS('SHOW TABLES LIKE \''.pSQL(_DB_PREFIX_).pSQL(static::$definition['table']).'%\'');
        if (is_array($table_list) && !empty($table_list)) {
            foreach ($table_list as $table) {
                foreach ($table as $table_name) {
                    if (!Db::getInstance()->execute('DROP TABLE `'.pSQL($table_name).'`')) {
                        $is_success = false;
                    }
                }
            }
        }
        return (bool)$is_success;
    }

    /**
     * Reinstall SQL.
     *
     * @return bool
     * @throws Exception
     */
    public static function resetSQL()
    {
        return (bool)static::uninstallSQL()
            && (bool)static::installSQL();
    }

    /**
     * Get an instance of an object.
     *
     * @return object
     */
    public static function getInstance()
    {
        return new static();
    }

    /**
     * Get an array of an object values.
     *
     * @return array
     */
    public function get()
    {
        $values = array();
        if (array_key_exists('primary', static::$definition) && static::$definition['primary']
            && isset($this->id) && $this->id
        ) {
            $values[static::$definition['primary']] = (int)$this->id;
        }
        foreach (array_keys(static::$definition['fields']) as $property_name) {
            if (isset($this->{$property_name})) {
                $values[$property_name] = $this->{$property_name};
            }
        }
        return (array)$values;
    }

    /**
     * Get all data from the table linked to an object.
     *
     * @return array
     */
    public static function read()
    {
        $return = array();
        $is_multilang = ((bool)array_key_exists('primary', static::$definition) && static::$definition['primary'])
                && (((bool)array_key_exists('multilang', static::$definition) && (bool)static::$definition['multilang'])
                || ((bool)array_key_exists('multilang_shop', static::$definition) && (bool)static::$definition['multilang_shop']));
        $response = Db::getInstance()->executeS(
            'SELECT t.*'.((bool)$is_multilang ? ', tl.*' : null).'
            FROM `'._DB_PREFIX_.pSQL(static::$definition['table']).'` as t
                '.((bool)$is_multilang
                    ? 'LEFT JOIN `'._DB_PREFIX_.pSQL(static::$definition['table']).'_lang`
                        ON t.`'.pSQL(static::$definition['primary']).'` = tl.`'.pSQL(static::$definition['primary']).'`'
                    : null)
        );
        if (is_array($response) && !empty($response)) {
            foreach ($response as $line) {
                $return[$line[static::$definition['primary']]] = $line;
            }
        }
        return (array)$return;
    }

    /**
     * Get a column value by an ID.
     *
     * @param string $column - Column to get value.
     * @param int $id - ID.
     * @return mixed|void
     * @throws Exception
     */
    public static function getColumnById($column, $id)
    {
        try {
            if (!is_string($column)) {
                throw new Exception('The column must be a string, '.gettype($column).' given !');
            }
            if (!is_int($id) && !is_numeric($id)) {
                throw new Exception('The '.static::$definition['primary'].' must be an integer, '.gettype($id).' given !');
            }
            return Db::getInstance()->getValue(
                'SELECT `'.pSQL($column).'`
                FROM `'.pSQL(_DB_PREFIX_.static::$definition['table']).'`
                WHERE `'.pSQL(static::$definition['primary']).'` = '.(int)$id
            );
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Delete an object by an ID.
     *
     * @param int $id - Object ID.
     * @return bool
     * @throws Exception
     */
    public static function deleteById($id)
    {
        try {
            $has_primary = ((bool)array_key_exists('primary', static::$definition) && static::$definition['primary']);
            $is_multishop = (bool)$has_primary && (bool)array_key_exists('multishop', static::$definition) && (bool)static::$definition['multishop'];

            if (is_int($id) || is_numeric($id)) {
                if ($id) {
                    if ($is_multishop) {
                        Db::getInstance()->delete(pSQL(static::$definition['table'].'_shop'), '`'.pSQL(static::$definition['primary']).'` = '.(int)$id);
                    }
                    return (bool)Db::getInstance()->delete(pSQL(static::$definition['table']), '`'.pSQL(static::$definition['primary']).'` = '.(int)$id);
                }
                return false;
            }
            throw new Exception('The '.static::$definition['primary'].' must be an integer, '.gettype($id).' given !');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Adds shop filter.
     *
     * @param array $shops Shops to filter.
     * @return bool
     */
    public function addShops($shops = array())
    {
        $has_primary = ((bool)array_key_exists('primary', static::$definition) && static::$definition['primary']);
        $is_multishop = (bool)$has_primary && (bool)array_key_exists('multishop', static::$definition) && (bool)static::$definition['multishop'];

        if ($is_multishop && is_array($shops)) {
            Db::getInstance()->delete(pSQL(static::$definition['table'].'_shop'), '`'.pSQL(static::$definition['primary']).'` = '.(int)$this->id);
            foreach ($shops as $id_shop) {
                Db::getInstance()->insert(pSQL(static::$definition['table'].'_shop'), array(
                    pSQL(static::$definition['primary']) => (int)$this->id,
                    'id_shop' => (int)$id_shop
                ));
            }
        }

        return true;
    }

    /**
     * Gets shop filters
     *
     * @param int $id - Object ID.
     * @return array
     */
    public static function getShops($id)
    {
        $has_primary = ((bool)array_key_exists('primary', static::$definition) && static::$definition['primary']);
        $is_multishop = (bool)$has_primary && (bool)array_key_exists('multishop', static::$definition) && (bool)static::$definition['multishop'];
        $return = array();

        if ($is_multishop && $id) {
            $shops = Db::getInstance()->executeS(
                'SELECT id_shop
                FROM '.pSQL(_DB_PREFIX_.static::$definition['table'].'_shop').'
                WHERE `'.pSQL(static::$definition['primary']).'` = '.(int)$id
            );

            foreach ($shops as $shop) {
                $return[$shop['id_shop']] = $shop['id_shop'];
            }
        }

        return $return;
    }

    /**
     * Check if shop associated
     *
     * @param int $id - Object ID.
     * @param int $id_shop - ID Shop.
     * @return array
     */
    public static function shopAssociated($id, $id_shop)
    {
        $has_primary = ((bool)array_key_exists('primary', static::$definition) && static::$definition['primary']);
        $is_multishop = (bool)$has_primary && (bool)array_key_exists('multishop', static::$definition) && (bool)static::$definition['multishop'];

        if ($is_multishop && $id && $id_shop) {
            return (bool)Db::getInstance()->getRow(
                'SELECT *
                FROM '.pSQL(_DB_PREFIX_.static::$definition['table'].'_shop').'
                WHERE `'.pSQL(static::$definition['primary']).'` = '.(int)$id.' AND `id_shop` = '.(int)$id_shop
            );
        }

        return true;
    }
}
