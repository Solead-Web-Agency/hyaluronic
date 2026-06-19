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

class PBPBundleHelper extends ObjectModel
{
    public static function getBundleChildProducts($id_product)
    {
        $sql = new DbQuery();
        $sql->select('pbp_p.*');
        $sql->from('pbp_bundle', 'pbp_b');
        $sql->innerJoin('pbp_product', 'pbp_p', 'pbp_b.id_bundle = pbp_p.id_bundle');
        $sql->where('pbp_b.id_product = ' . (int)$id_product);

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get name with fallback
     * @param $id_bundle
     * @param $id_lang
     * @return string
     * @throws PrestaShopDatabaseException
     */
    public static function getName($id_bundle, $id_lang)
    {
        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        $sql = new DbQuery();
        $sql->select('id_lang, name');
        $sql->from(PBPBundleLangModel::$definition['table']);
        $sql->where('id_bundle = ' . (int)$id_bundle);
        $sql->where('id_lang = ' . (int)$id_lang.' OR id_lang = '.(int)$id_lang_default);
        $sql->where('name <> ""');
        $rows = Db::getInstance()->executeS($sql);

        $name = '';
        if (!empty($rows)) {
            foreach ($rows as $row) {
                if ($row['id_lang'] == $id_lang && $row['name'] != '') {
                    return $row['name'];
                }
                if ($row['name'] != '') {
                    $name = $row['name'];
                }
            }
        }
        return $name;
    }

    public static function getNames($id_bundle, $create_hash = true)
    {
        $sql = new DbQuery();
        $sql->select('id_lang, name');
        $sql->from(PBPBundleLangModel::$definition['table']);
        $sql->where('id_bundle = ' . (int)$id_bundle);
        $rows = Db::getInstance()->executeS($sql);

        $languages = Language::getLanguages();
        $names = array();
        if ($create_hash) {
            foreach($languages as $language) {
                $found = false;
                $name = '';
                foreach ($rows as $row) {
                    if ($row['id_lang'] == $language['id_lang']) {
                        $name = $row['name'];
                        $found = true;
                    }
                }
                if ($found) {
                    $names[$language['id_lang']] = $name;
                } else {
                    $names[$language['id_lang']] = '';
                }
            }
            return $names;
        } else {
            return $rows;
        }
    }

    /**
     * Delete all entried by Bundle ID
     * @param $id_bundle
     */
    public static function deleteByBundle($id_bundle)
    {
        DB::getInstance()->delete(PBPBundleModel::$definition['table'], 'id_bundle=' . (int)$id_bundle);
        PBPBundleLangModel::deleteBundle($id_bundle);
    }

    /**
     * Delete by product and Bundle
     * @param $id_product
     * @param $id_bundle
     */
    public static function deleteByProductBundle($id_product, $id_bundle)
    {
        DB::getInstance()->delete(PBPBundleModel::$definition['table'], 'id_bundle=' . (int)$id_bundle . ' AND id_product = ' . (int)$id_product);
    }
}
