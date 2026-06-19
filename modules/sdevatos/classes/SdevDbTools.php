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

namespace ScaleDEV\SdevAtos;

require_once(dirname(__FILE__).'../../autoload.php');

use \Db;

class SdevDbTools
{
    /**
     * Get if the column exists or not.
     *
     * @param string $table - Column table to check.
     * @param string $column_name - Column name.
     * @return bool
     */
    public static function isColumnExists($table, $column_name)
    {
        $columns = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.pSQL($table).'`');
        foreach ($columns as $column) {
            if ($column['Field'] == $column_name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a column to a table.
     *
     * @param string $table - Table name.
     * @param string $column - Column name.
     * @param string $type - Column type.
     * @param int $size - Size of column value.
     * @param bool $is_nullable - Define if the column value can be null or not.
     * @param null|string $default - Define if the column value has a default value or not.
     * @return bool
     */
    public static function addColumn($table, $column, $type = 'VARCHAR', $size = 255, $is_nullable = false, $default = null)
    {
        if ((bool)self::isColumnExists($table, $column)) {
            return true;
        }

        $query = 'ALTER TABLE `'._DB_PREFIX_.pSQL($table).'`
            ADD `'.pSQL($column).'` '.pSQL($type)
            .($size ? '('.(int)$size.')' : null)
            .' '.($is_nullable ? 'NULL' : 'NOT NULL')
            .($default !== null ? ' DEFAULT '.pSQL($default) : null);
        return (bool)Db::getInstance()->execute($query);
    }

    /**
     * Remove a column to a table.
     *
     * @param string $table - Table name.
     * @param string $column - Column name.
     * @return bool
     */
    public static function removeColumn($table, $column)
    {
        if ((bool)self::isColumnExists($table, $column)) {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . pSQL($table) . '` DROP COLUMN `' . pSQL($column) . '`';

            return (bool)Db::getInstance()->execute($sql);
        }

        return true;
    }
}
