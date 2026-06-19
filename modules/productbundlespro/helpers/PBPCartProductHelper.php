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

class PBPCartProductHelper extends ObjectModel
{
    /**
     * Get all parent products ini the cart
     * @param $id_cart
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllParentProducts($id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('cart_product');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_parent_pbp_product = 0');

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get the totla quantity of a child product belonging to a specific parent
     * @param $id_product_parent
     * @param $id_product_attribute_parent
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return mixed
     */
    public static function getChildCountFromParent($id_product_parent, $id_product_attribute_parent, $id_product, $id_product_attribute, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('SUM(quantity) AS total_qty');
        $sql->from('cart_product');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $sql->where('id_parent_pbp_product = ' . (int)$id_product_parent);
        $sql->where('id_parent_pbp_product_ipa = ' . (int)$id_product_attribute_parent);
        $row = Db::getInstance()->getRow($sql);
        return $row['total_qty'];
    }

    public static function getCartProduct($id_product, $id_product_attribute, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('cart_product');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $row = Db::getInstance()->getRow($sql);
        return $row;
    }

    /**
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return mixed
     */
    public static function getTotalQuantity($id_product, $id_product_attribute, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('SUM(quantity) AS total_qty');
        $sql->from('cart_product');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $row = Db::getInstance()->getRow($sql);
        return $row['total_qty'];
    }

}
