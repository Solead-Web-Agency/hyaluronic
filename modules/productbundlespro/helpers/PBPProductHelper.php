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

class PBPProductHelper
{
    /**
     * Get Product info such as price, attribute p[rice based on Product ID and attributes array (group)
     * @param $id_product
     * @param $group
     */
    public static function getProductInfo($id_product, $group, $id_product_attribute = 0)
    {
        if (!empty($group)) {
            $id_product_attribute = Product::getIdProductAttributesByIdAttributes((int)$id_product, $group);
        }

        $product_obj = new Product($id_product);
        $product = [];
        $product['id_product'] = $id_product;
        $product['id_product_attribute'] = $id_product_attribute;
        $product['out_of_stock'] = $product_obj->out_of_stock;
        $product['id_category_default'] = $product_obj->id_category_default;
        $product['link_rewrite'] = ''; //$product_obj->link_rewrite;
        $product['ean13'] = $product_obj->ean13;
        $product['minimal_quantity'] = $product_obj->minimal_quantity;
        $product['unit_price_ratio'] = $product_obj->unit_price_ratio;

        $product_properties = Product::getProductProperties(Context::getContext()->language->id, $product, null);
        $product_properties['base_price_exc_tax'] = $product_obj->price;
        return $product_properties;
    }

    /**
     * Determine if a product is available
     * @param $product Product
     * @return bool
     */
    public static function isProductAvailable($product)
    {
        if (!PBPConfigHelper::isStockManagementEnabled()) {
            return true;
        }

        if (Product::isAvailableWhenOutOfStock($product->out_of_stock)) {
            return true;
        }

        if (!$product->active || !$product->available_for_order || $product->quantity <= 0) {
            return false;
        } else {
            return true;
        }
	}
	
    /**
     * format order to appropriate number of decimals
     * @param $price
     * @return float
     */
    public static function formatPrice($price)
    {
        return Tools::ps_round($price, Configuration::get('PS_PRICE_DISPLAY_PRECISION'));
    }	
}
