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

class PBPCartProductModel extends ObjectModel
{
    /** @var integer Cart ID */
    public $id_cart;

    /** @var integer Shop ID */
    public $id_shop;

    /** @var integer Product ID */
    public $id_product;

    /** @var integer Product Attribute ID */
    public $id_product_attribute;

    /** @var integer Delivery Address ID */
    public $id_address_delivery;

    /** @var integer Quantity */
    public $quantity;

    /** @var integer Quantity */
    public $date_add;

    /** @var integer Unique ID */
    public $id_parent_pbp_product;

    /** @var integer Product ID */
    public $id_parent_pbp_product_ipa;

    /** @var integer Product Bundle ID */
    public $id_pbp_bundle;


    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'cart_product',
        'primary' => 'id_cart',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT),
            'id_product_attribute' => array('type' => self::TYPE_INT),
            'id_cart' => array('type' => self::TYPE_INT),
            'id_shop' => array('type' => self::TYPE_INT),
            'id_address_delivery' => array('type' => self::TYPE_INT),
            'quantity' => array('type' => self::TYPE_INT),
            'date_add' => array('type' => self::TYPE_DATE),
            'id_parent_pbp_product' => array('type' => self::TYPE_INT),
            'id_parent_pbp_product_ipa' => array('type' => self::TYPE_INT),
            'id_pbp_bundle' => array('type' => self::TYPE_INT)
        )
    );

    public function load($id_cart, $id_product, $ipa, $id_shop)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('cart_product');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$ipa);
        $sql->where('id_shop = ' . (int)$id_shop);
        $row = Db::getInstance()->getRow($sql);

        if (!empty($row)) {
            $this->hydrate($row);
        } else {
            return false;
        }
    }

    public function getParentProduct($id_cart, $id_product, $ipa, $id_shop)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('cart_product');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_parent_pbp_product = ' . (int)$id_product);
        $sql->where('id_parent_pbp_product_ipa = ' . (int)$ipa);
        $sql->where('id_shop = ' . (int)$id_shop);
        $row = Db::getInstance()->executeS($sql);
        if (!empty($row)) {
            $this->hydrate('PBPCartProductModel', $row);
        } else {
            return false;
        }
    }

    public function updateQty()
    {
        DB::getInstance()->update(self::$definition['table'], array(
            'quantity' => $this->quantity
        ), 'id_cart=' . (int)$this->id_cart . ' AND ' . 'id_product=' . (int)$this->id_product . ' AND id_product_attribute=' . (int)$this->id_product_attribute);
    }

    public static function getCartProductsLight($id_cart)
    {
        $sql = 'SELECT id_product, quantity, id_product_attribute, id_parent_pbp_product, id_parent_pbp_product_ipa FROM ' . _DB_PREFIX_ . 'cart_product WHERE id_cart=' . (int)$id_cart;
        $result = DB::getInstance()->executeS($sql);
        return $result;
    }
}
