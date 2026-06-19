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

class MigrationProMapping extends ObjectModel
{
    public $id;
    public $group;
    public $type;
    public $source_id;
    public $source_name;
    public $mapping;

    public static $definition = array(
        'table' => 'migrationpro_mapping',
        'primary' => 'id_mapping',
        'fields' => array(
            'group' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'type' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'source_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'source_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'mapping' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId')
        ),
    );

    public static function listMapping($list = false, $keyAsSourceId = false)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('migrationpro_mapping');
        $mappings = array();
        $rows = Db::getInstance()->executeS($sql);
        if (!$list) {
            return $rows;
        }

        if ($keyAsSourceId) {
            foreach ($rows as $row) {
                $mappings[$row['group']][$row['type']][$row['source_id']] = $row['mapping'];
            }
        } else {
            foreach ($rows as $row) {
                $mappings[$row['group']][$row['type']][] = array(
                    'id_mapping' => $row['id_mapping'],
                    'source_id' => $row['source_id'],
                    'source_name' => $row['source_name'],
                    'mapping' => $row['mapping']
                );
            }
        }

        return $mappings;
    }

    public static function getMapTypeCount($entity_type)
    {
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'migrationpro_mapping as map WHERE map.type=\'' . pSQL($entity_type) . '\' ';
        return Db::getInstance()->getValue($sql);
    }
}
