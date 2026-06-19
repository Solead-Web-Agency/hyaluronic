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

class PBPBundleLangModel extends ObjectModel
{
    /** @var integer Primary ID */
    public $id_pbp_bundle_lang;

    /** @var integer Tab ID */
    public $id_bundle;

    /** @var integer Language ID */
    public $id_lang;

    /** @var string Title */
    public $name;

    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'pbp_bundle_lang',
        'primary' => 'id_pbp_bundle_lang',
        'fields' => array(
            'id_bundle' => array('type' => self::TYPE_INT),
            'id_lang' => array('type' => self::TYPE_INT),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 128, 'required' => true)
        )
    );

    /**
     * Load bundle
     * @param $id_bundle
     * @param $id_lang
     * @return bool
     */
    public function load($id_bundle, $id_lang)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_bundle = ' . (int)$id_bundle);
        $sql->where('id_lang = ' . (int)$id_lang);

        $row = Db::getInstance()->getRow($sql);
        if (!empty($row)) {
            $this->hydrate($row);
        } else {
            return false;
        }
    }

    /**
     * @param $id_lang
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public function loadAll($id_lang)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'bl');
        $sql->where('id_lang = ' . (int)$id_lang);
        $results = Db::getInstance()->executeS($sql);

        if (!empty($results)) {
            return $this->hydrateCollection('PBPBundleLangModel', $results);
        } else {
            return array();
        }
    }

    /**
     * Update the bundle name
     */
    public function updateName()
    {
        DB::getInstance()->update('pbp_bundle_lang', array(
            'name' => pSQL($this->name)
        ), 'id_lang = ' . (int)$this->id_lang . ' AND id_bundle =' . (int)$this->id_pbp_bundle);
    }

    /**
     * Delete by Bundle and Language
     * @param $id_bundle
     * @param $id_lang
     * @return bool|void
     */
    public static function deleteBundle($id_bundle, $id_lang = 0)
    {
        if ($id_lang > 0) {
            DB::getInstance()->delete('pbp_bundle_lang', 'id_bundle =' . (int)$id_bundle . ' AND id_lang = ' . (int)$id_lang);
        } else {
            DB::getInstance()->delete('pbp_bundle_lang', 'id_bundle =' . (int)$id_bundle);
        }
    }

}
