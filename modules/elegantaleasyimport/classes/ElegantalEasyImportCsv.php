<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is an object model class used to manage CSV rows saved in database
 */
class ElegantalEasyImportCsv extends ElegantalEasyImportObjectModel
{

    public $tableName = 'elegantaleasyimport_csv';
    public static $definition = array(
        'table' => 'elegantaleasyimport_csv',
        'primary' => 'id_elegantaleasyimport_csv',
        'fields' => array(
            'id_elegantaleasyimport' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_reference' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'csv_row' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        ),
    );

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
