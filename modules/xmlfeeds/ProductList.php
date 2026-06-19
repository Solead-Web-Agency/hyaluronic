<?php
/**
 * 2010-2023 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2023 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductList
{
    protected $productListExclude = array();

    public function __construct($productListExclude = array())
    {
        $this->productListExclude = $productListExclude;
    }

    public function getProductsByProductList($productList, $productListExcludeActive)
    {
        $products = $this->getProducts(array_diff($productList, $this->productListExclude));
        $products = array_merge($products, $this->getProductIdList($productList));

        return array_filter(array_diff($products, $productListExcludeActive));
    }

    public function getExcludeProductsByProductList()
    {
        return $this->getProducts($this->productListExclude);
    }

    public function getProductIdList($productListId)
    {
        if (empty($productListId)) {
            return [];
        }

        $lists = Db::getInstance()->executeS('SELECT l.product_id_list
            FROM '._DB_PREFIX_.'blmod_xml_product_list l
            WHERE l.id IN ('.pSQL(implode(',', $productListId)).')');

        $listsUnique = [];

        if (empty($lists)) {
            return $listsUnique;
        }

        foreach ($lists as $l) {
            $listsUnique = array_merge($listsUnique, explode(',', $l['product_id_list']));
        }

        return array_unique($listsUnique);
    }

    public function getProductListWithXmlTags($productListId)
    {
        $list = Db::getInstance()->executeS('SELECT l.id, l.custom_xml_tags
            FROM '._DB_PREFIX_.'blmod_xml_product_list l
            WHERE l.id IN ('.pSQL(implode(',', $productListId)).')');

        if (empty($list)) {
            return [];
        }

        $tags = [];

        foreach ($list as $l) {
            $tags[$l['id']] = $l['custom_xml_tags'];
        }

        return $tags;
    }

    protected function getProducts($productList)
    {
        $products = [];

        if (empty($productList)) {
            return $products;
        }

        $result = Db::getInstance()->executeS('SELECT DISTINCT(lp.product_id)
			FROM `'._DB_PREFIX_.'blmod_xml_product_list_product` lp
			WHERE lp.product_list_id IN ('.pSQL(implode(',', $productList)).')');

        foreach ($result as $p) {
            $products[] = $p['product_id'];
        }

        return $products;
    }
}
