<?php
 /**
 * NOTICE OF LICENSE 
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    MigrationPro
 * @copyright Copyright (c) 2012-2023 MigrationPro
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @package   MigrationPro: OpenCart to PrestaShop Migrate tool
 */

class MigrationProProcess extends ObjectModel
{
    public $id;
    public $type;
    public $total;
    public $imported;
    public $id_source;
    public $error;
    public $error_count;
    public $point;
    public $time_start;
    public $finish;

    public static $definition = array(
        'table'   => 'migrationpro_process',
        'primary' => 'id_process',
        'fields'  => array(
            'type'       => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'total'      => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'imported'   => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_source'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'error'      => array('type' => self::TYPE_INT, 'required' => true),
            'error_count'      => array('type' => self::TYPE_INT,  'required' => true),
            'point'      => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'time_start' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'finish'     => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true)
        ),
    );

    public static function getActiveProcessObject()
    {
        $query = new DbQuery();
        $query->select('p.id_process');
        $query->from('migrationpro_process', 'p');
        $query->where('p.finish = 0');
        $query->orderBy('p.id_process ASC');
        $result = Db::getInstance()->getValue($query);
        if (!$result) {
            return false;
        }

        return new MigrationProProcess($result);
    }

    public static function calculateImportedDataPercent()
    {
        $query = 'SELECT SUM(imported) / SUM(total) * 100 AS percent FROM ' . _DB_PREFIX_ . 'migrationpro_process';
        $result = Db::getInstance()->getValue($query);


        if (!$result) {
            return 0;
        } else {
            return (int)$result;
        }
    }

    public static function getAll()
    {
        $query = new DbQuery();
        $query->select('p.*');
        $query->from('migrationpro_process', 'p');
        $query->orderBy('p.id_process ASC');
        $result = Db::getInstance()->executeS($query);

        return $result;
    }
}
