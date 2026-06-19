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

class MigrationProPassLog extends ObjectModel
{
    public $id;
    public $mail;
    public $passwd;

    public static $definition = array(
        'table' => 'migrationpro_pass',
        'primary' => 'id',
        'fields' => array(
            'mail' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' =>
                255),
            'id_customer' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedInt', 'required' => true, 'size' =>
                11),
            'passwd' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 255)
        ),
    );

    public static function storeCustomerPass($id_customer, $mail, $pass)
    {

        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'migrationpro_pass SET id_customer=' . (int)$id_customer . ', mail=\'' . pSQL($mail) . '\',passwd=\'' . pSQL($pass) . '\'';
        return Db::getInstance()->execute($sql);
    }

    public static function getUser($mail)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'migrationpro_pass where mail=\'' . pSQL($mail) . '\'';
        return Db::getInstance()->executeS($sql);
    }

    public static function deleteUserById($id)
    {
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'migrationpro_pass where id_customer=' . (int)$id;
        return Db::getInstance()->execute($sql);
    }
}
