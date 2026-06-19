<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PBPTabLangModel extends ObjectModel
{
    /** @var integer Primary ID */
    public $id_pbp_tab;

    /** @var integer Tab ID */
    public $id_tab;

    /** @var integer Language ID */
    public $id_lang;

    /** @var string Title */
    public $title;

    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'pbp_tabs_lang',
        'primary' => 'id_pbp_tab',
        'fields' => array(
            'id_tab' => array('type' => self::TYPE_INT),
            'id_lang' => array('type' => self::TYPE_INT),
            'title' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 80, 'required' => true)
        )
    );

    public function load($id_tab, $id_lang)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_tab = ' . (int)$id_tab);
        $sql->where('id_lang = ' . (int)$id_lang);

        $row = Db::getInstance()->getRow($sql);
        if (!empty($row)) {
            $this->hydrate($row);
        } else {
            return false;
        }
    }

    public function loadAll($id_lang)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'pt');
        $sql->where('id_lang = ' . (int)$id_lang);
        $results = Db::getInstance()->executeS($sql);

        if (!empty($results)) {
            return $this->hydrateCollection('PBPTabLangModel', $results);
        } else {
            return array();
        }
    }

    public static function getNextTabId()
    {
        $sql = new DbQuery();
        $sql->select('MAX(id_tab) AS id_tab');
        $sql->from(self::$definition['table']);
        $row = Db::getInstance()->getRow($sql);
        return $row['id_tab'] + 1;
    }

    public function updateTitle()
    {
        DB::getInstance()->update('pbp_tabs_lang', array(
            'title' => pSQL($this->title)
        ), 'id_lang = ' . (int)$this->id_lang . ' AND id_tab =' . (int)$this->id_tab);
    }

    public static function deleteTab($id_tab)
    {
        DB::getInstance()->delete('pbp_tabs_lang', 'id_tab =' . (int)$id_tab);
        // @Todo: delete foreign records
    }
}
