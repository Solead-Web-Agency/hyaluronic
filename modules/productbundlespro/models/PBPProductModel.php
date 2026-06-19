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

class PBPProductModel extends ObjectModel
{
    /** @var integer Unique ID */
    public $id_pbp_product;

    /** @var integer Product ID */
    public $id_product;

    /** @var integer Bundle ID */
    public $id_bundle;

    /** @var string Discount Type */
    public $discount_type;

    /** @var float Discount Amount */
    public $discount_amount;

    /** @var string Discount Tax */
    public $discount_tax;

    /** @var integer Allow OOS */
    public $allow_oos;

    /** @var integer Quantity */
    public $qty;

    /** @var integer Position */
    public $position;


    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'pbp_product',
        'primary' => 'id_pbp_product',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT),
            'id_bundle' => array('type' => self::TYPE_INT),
            'discount_amount' => array('type' => self::TYPE_FLOAT),
            'discount_type' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 12, 'required' => true),
            'discount_tax' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 8, 'required' => true),
            'allow_oos' => array('type' => self::TYPE_BOOL),
            'qty' => array('type' => self::TYPE_INT),
            'position' => array('type' => self::TYPE_INT)
        )
    );

    public function load($id_product, $id_bundle, $id_shop)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_bundle = ' . (int)$id_bundle);
        $row = Db::getInstance()->getRow($sql);
        if (!empty($row)) {
            $this->hydrate($row);
        } else {
            return false;
        }
    }

    /**
     * get raw entries by bundle
     * @param $id_bundle
     * @return array
     */
    public function getByBundle($id_bundle)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'pbp');
        $sql->where('pbp.id_bundle = ' . (int)$id_bundle);
        $result = Db::getInstance()->executeS($sql);

        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    public function loadByBundle($id_bundle, $id_lang = 1)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'pbp');
        $sql->innerJoin('product', 'p', 'pbp.id_product = p.id_product AND p.active = 1');
        $sql->where('pbp.id_bundle = ' . (int)$id_bundle);
        $sql->orderBy('pbp.position');
        $result = Db::getInstance()->executeS($sql);

        if (!empty($result) && count($result) > 0) {
            $collection = $this->hydrateCollection('PBPProductModel', $result);
            foreach ($collection as &$pbp_product) {
                $pbp_product->product = new Product($pbp_product->id_product, false, $id_lang);
            }
            return $collection;
        } else {
            return array();
        }
    }

    /**
     * @param $id_bundle
     * @param int $id_product
     */
    public static function deleteBundleProducts($id_bundle, $id_product = 0)
    {
        if ((int)$id_product > 0) {
            DB::getInstance()->delete(self::$definition['table'], 'id_product=' . (int)$id_product . ' AND id_bundle=' . (int)$id_bundle);
        } else {
            DB::getInstance()->delete(self::$definition['table'], 'id_bundle=' . (int)$id_bundle);
        }
    }
}
