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

class PBPCartProductExtraHelper extends ObjectModel
{

    /**
     * Get cart product extra object model which is a child of any product bundle
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return PBPCartProductExtraModel
     */
    public static function loadChild($id_product, $id_product_attribute, $id_cart)
    {
        $pbp_cart_productextra = new PBPCartProductExtraModel();
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $sql->where('id_product_parent > 0');
        $row = Db::getInstance()->getRow($sql);

        if (!empty($row)) {
            $pbp_cart_productextra->hydrate($row);
        }
        return $pbp_cart_productextra;
    }

    /**
     * Get Parents matching a specific product and product attribute
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getAllParents($id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product_parent = 0');
        $result = Db::getInstance()->executeS($sql);

        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }


    /**
     * Get Parents matching a specific product and product attribute
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getAllParentsByProduct($id_product, $id_product_attribute, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $sql->where('id_product_parent = 0');
        $result = Db::getInstance()->executeS($sql);

        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get Children matching a product and product attribute
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getAllChildrenByProduct($id_product, $id_product_attribute, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $sql->where('id_product_parent > 0');
        $result = Db::getInstance()->executeS($sql);

        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get total quantity of product
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return mixed
     */
    public static function getProductCount($id_product, $id_product_attribute, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('SUM(quantity) AS total_qty');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $row = Db::getInstance()->getRow($sql);
        return $row['total_qty'];
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
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $sql->where('id_product_parent = ' . (int)$id_product_parent);
        $sql->where('id_product_attribute_parent = ' . (int)$id_product_attribute_parent);
        $row = Db::getInstance()->getRow($sql);
        return $row['total_qty'];
    }

    /**
     * Get specific child by specific parent
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_product_parent
     * @param $id_product_attribute_parent
     * @param $id_cart
     * @return array|bool|null|object
     */
    public static function getChildByParent($id_product, $id_product_attribute, $id_product_parent, $id_product_attribute_parent, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $sql->where('id_product_parent = ' . (int)$id_product_parent);
        $sql->where('id_product_attribute_parent = ' . (int)$id_product_attribute_parent);
        $row = Db::getInstance()->getRow($sql);
        return $row;
    }

    /**
     * Get child products based primary
     * @param $id_pbp_cart_productextra
     * @param $id_cart
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getChildren($id_pbp_cart_productextra, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_pbp_cart_productextra_parent = ' . (int)$id_pbp_cart_productextra);

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get specific child by specific parent
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_product_parent
     * @param $id_product_attribute_parent
     * @param $id_cart
     * @return array|bool|null|object
     */
    public static function getAllChildrenByParent($id_product_parent, $id_product_attribute_parent, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product_parent = ' . (int)$id_product_parent);
        $sql->where('id_product_attribute_parent = ' . (int)$id_product_attribute_parent);

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllByProductID($id_product, $id_product_attribute, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product = ' . (int)$id_product);

        if ($id_product_attribute > 0) {
            $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        }

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllByProduct($id_product, $id_product_attribute, $id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pbp_cart_productextra');
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->where('id_product_parent = ' . (int)$id_product);

        if ($id_product_attribute > 0) {
            $sql->where('id_product_attribute_parent = ' . (int)$id_product_attribute);
        }

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Delete by primary key
     * @param $id_pbp_cart_product_extra
     * @param $id_cart
     */
    public static function deleteById($id_pbp_cart_product_extra, $id_cart)
    {
        DB::getInstance()->delete('pbp_cart_productextra', 'id_pbp_cart_productextra = ' . (int)$id_pbp_cart_product_extra.' AND id_cart = '.$id_cart);
    }

    /**
     * Delet all children by the parent primary key
     * @param $id_pbp_cart_productextra_parent
     * @param $id_cart
     */
    public static function deleteAllChildren($id_pbp_cart_productextra_parent, $id_cart)
    {
        DB::getInstance()->delete('pbp_cart_productextra', 'id_pbp_cart_productextra_parent =' . (int)$id_pbp_cart_productextra_parent . ' AND id_cart = ' . $id_cart);
    }

    /**
     * Delete all parent products and associated child products from the cart extra table
     * @param $parents
     * @param $id_cart
     * @param $limit
     */
    public static function deleteBundlesFromParentsArray($parents, $id_cart, $limit)
    {
        $count = 0;
        foreach ($parents as $parent) {
            PBPCartProductExtraHelper::deleteById($parent['id_pbp_cart_productextra'], $id_cart);
            PBPCartProductExtraHelper::deleteAllChildren($parent['id_pbp_cart_productextra'], $id_cart);
            $count++;
            if ($count >= $limit && $limit > 0) {
                break;
            }
        }
    }

    /**
     * Delete all parent products and associated child products from the cart extra table
     * @param $products
     * @param $id_cart
     * @param $limit
     */
    public static function deleteFromProductsArray($products, $id_cart, $limit)
    {
        $count = 0;
        foreach ($products as $product) {
            PBPCartProductExtraHelper::deleteById($product['id_pbp_cart_productextra'], $id_cart);
            $count++;
            if ($count >= $limit && $limit > 0) {
                break;
            }
        }
    }

    /**
     * Delete a product
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_cart
     * @return bool|void
     */
    public static function deleteProduct($id_product, $id_product_attribute, $id_cart)
    {
        DB::getInstance()->delete('pbp_cart_productextra', 'id_product=' . (int)$id_product . ' AND id_product_attribute = ' . (int)$id_product_attribute . ' AND id_cart = ' . (int)$id_cart);
    }
}
