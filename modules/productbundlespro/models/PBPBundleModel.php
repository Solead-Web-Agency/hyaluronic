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

class PBPBundleModel extends ObjectModel
{
    /** @var Unique integer Bundle ID */
    public $id_pbp_bundle;

    /** @var integer Bundle ID */
    public $id_bundle;

    /** @var integer Tab ID */
    public $id_tab;

    /** @var integer Product ID */
    public $id_product;

    /** @var integer Product ID */
    public $id_shop;

    /** @var boolean Enabled */
    public $enabled;

    /** @var boolean Allow product selection */
    public $allow_selection;

    /** @var boolean Bundle discount enabled */
    public $discount_enabled;

    /** @var string Bundle discount type (percentage|money) */
    public $discount_type = 'percentage';

    /** @var integer Bundle discount amount */
    public $discount_amount = 0;

    /** @var float Parent product discount amount */
    public $parent_product_discount_amount = 0.00;

    /** @var float Parent product discount type */
    public $parent_product_discount_type = 'percentage';


    /** @var boolean Position */
    public $position;


    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'pbp_bundle',
        'primary' => 'id_pbp_bundle',
        'fields' => array(
            'id_bundle' => array('type' => self::TYPE_INT),
            'id_tab' => array('type' => self::TYPE_INT),
            'id_product' => array('type' => self::TYPE_INT),
            'id_shop' => array('type' => self::TYPE_INT),
            'enabled' => array('type' => self::TYPE_INT),
            'allow_selection' => array('type' => self::TYPE_INT),
            'discount_enabled' => array('type' => self::TYPE_INT),
            'discount_type' => array('type' => self::TYPE_STRING),
            'discount_amount' => array('type' => self::TYPE_FLOAT),
            'parent_product_discount_amount' => array('type' => self::TYPE_FLOAT),
            'parent_product_discount_type' => array('type' => self::TYPE_STRING),
            'position' => array('type' => self::TYPE_INT)
        )
    );

    public function loadSingle($id_bundle, $id_product)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_bundle = ' . (int)$id_bundle);
        $sql->where('id_product = ' . (int)$id_product);
        $row = Db::getInstance()->getRow($sql);

        if (!empty($row)) {
            $this->hydrate($row);
        }
    }

    public function load($id_bundle)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_bundle = ' . (int)$id_bundle);
        $row = Db::getInstance()->getRow($sql);

        if (!empty($row)) {
            $this->hydrate($row);
        }
    }

    public function getByID($id_bundle, $enabled_only = false)
    {
        $cache_id = 'pbpbundlemodel::getbyid_' . $id_bundle . (int)$enabled_only;

        $bundle_collection = Cache::retrieve($cache_id);
        if (!empty($bundle_collection)) {
            return $bundle_collection;
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_bundle = ' . (int)$id_bundle);
        if ($enabled_only) {
            $sql->where('enabled = 1');
        }
        $sql->orderBy('position');
        $results = Db::getInstance()->executeS($sql);

        if (!empty($results)) {
            /* populate bundle with products */
            $bundle_collection = $this->hydrateCollection('PBPBundleModel', $results);
            foreach ($bundle_collection as &$bundle) {
                $pbp_product = new PBPProductModel();
                $bundle->products = $pbp_product->loadByBundle($bundle->id_bundle);

                $bundle->tab = new PBPTabLangModel();
                $bundle->tab->load($bundle->id_tab, Context::getContext()->language->id);
            }
            Cache::store($cache_id, $bundle_collection);
            return $bundle_collection;
        } else {
            return array();
        }
    }

    public function getByProductTab($id_product, $id_tab)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_tab = ' . (int)$id_tab);
        $results = Db::getInstance()->executeS($sql);

        if (!empty($results)) {
            /* populate bundle with products */
            $bundle_collection = $this->hydrateCollection('PBPBundleModel', $results);
            foreach ($bundle_collection as &$bundle) {
                $pbp_product = new PBPProductModel();
                $bundle->products = $pbp_product->loadByBundle($bundle->id_bundle);
            }
            return $bundle_collection;
        } else {
            return false;
        }
    }


    /**
     * @param $id_product
     * @param bool $enabled_only
     * @param $id_shop
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getByProduct($id_product, $enabled_only = false, $id_shop)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int)$id_product);
        if ($enabled_only) {
            $sql->where('enabled = 1');
        }

        if ((int)$id_shop > 0) {
            $sql->where('id_shop = '.(int)$id_shop);
        }

        $sql->orderBy('position');
        $results = Db::getInstance()->executeS($sql);

        if (!empty($results)) {
            /* populate bundle with products */
            $bundle_collection = $this->hydrateCollection('PBPBundleModel', $results);
            foreach ($bundle_collection as &$bundle) {
                $pbp_product = new PBPProductModel();
                $bundle->products = $pbp_product->loadByBundle($bundle->id_bundle);

                $bundle->tab = new PBPTabLangModel();
                $bundle->tab->load($bundle->id_tab, Context::getContext()->language->id);
            }
            return $bundle_collection;
        } else {
            return array();
        }
    }

    /**
     * Get new bundle ID
     * @return int
     */
    public static function getNewBundleID()
    {
        $sql = new DbQuery();
        $sql->select('MAX(id_bundle) AS max_id');
        $sql->from(self::$definition['table']);
        $row = Db::getInstance()->getRow($sql);
        return (int)$row['max_id'] + 1;
    }
}
