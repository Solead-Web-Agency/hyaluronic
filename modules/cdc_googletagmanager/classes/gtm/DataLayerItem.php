<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 * @package   cdc_googletagmanager
 */

if (!defined('_PS_VERSION_')) { exit; }

if (!defined('_CDCGTM_DIR_'))
    define('_CDCGTM_DIR_', dirname(__FILE__).'/../..');

include_once(_CDCGTM_DIR_.'/classes/AbstractDataLayerObject.php');
include_once(_CDCGTM_DIR_.'/services/Gtm_Product.php');

/**
 * Represent GTM Datalayer Item
 */
class Gtm_DataLayerItem extends AbstractDataLayerObject
{
    public $item_id;
    public $item_name;
    public $item_reference;
    public $index;
    public $item_brand;
    public $item_category;
    public $item_category2;
    public $item_category3;
    public $item_category4;
    public $item_list_id;
    public $item_list_name;
    public $item_variant;
    public $price;
    public $price_tax_exc;
    public $price_tax_inc;
    public $wholesale_price;
    public $quantity;
    public $google_business_vertical = 'retail';


    /**
     * Gtm_DataLayerItem constructor.
     *
     * @param Cdc_GoogleTagManager $gtm
     * @param ProductCore $product
     * @param array|null $list
     */
    public function __construct($gtm, $product, $list = null) {
        $product = $product instanceof Product ? $product : (object)$product;
        $variant = null;

        // set correct ID
        if(empty($product->id)) {
            $product->id = !empty($product->id_product) ? $product->id_product : 0;
            if(!$product->id || !is_numeric($product->id)) {
                // error no product id
                throw new Exception("[DataLayerItem] Given product is not valid: ".json_encode($product));
            }
        }
        $id_product = (int) $product->id;

        $id_product_attribute = !empty($product->id_product_attribute) ? (int) $product->id_product_attribute : (int) PrestashopUtils::getProductAttributeId($product);
        if($id_product_attribute) {
            $variant = new Combination($id_product_attribute);
            if(!Validate::isLoadedObject($variant)) {
                $variant = null;
                $id_product_attribute = 0;
            }
        }


        // product ID
        $this->item_id = (string) Gtm_Product::getInstance()->getProductId($product, $variant);


        // reference
        if(!empty($variant->reference)) {
            $this->item_reference = (string) $variant->reference;
        } else {
            $this->item_reference = (string) $product->reference;
        }


        // product name
        $this->item_name = Gtm_Product::getInstance()->getProductName($product);


        // variant name
        if($id_product_attribute) {
            $this->item_variant = $gtm->cleanString(PrestashopUtils::getAttributeSmall($id_product_attribute, $gtm->getDataLanguage()));
        }


        // item list
        $this->item_list_name = !empty($list['name']) ? $list['name'] : null;
        $this->item_list_id = !empty($list['id']) ? $list['id'] : null;
        $this->index = !empty($list['index']) ? $list['index'] : null;


        // categories
        $category = new Category($product->id_category_default);
        $this->item_category = $gtm->getPageCategoryNameHierarchy($category, 1);
        $this->item_category2 = $gtm->getPageCategoryNameHierarchy($category, 2);
        $this->item_category3 = $gtm->getPageCategoryNameHierarchy($category, 3);
        $this->item_category4 = $gtm->getPageCategoryNameHierarchy($category, 4);


        // manufacturer
        if($product->id_manufacturer) {
            $manufacturer_name = $gtm->cleanString(Manufacturer::getNameById((int)$product->id_manufacturer));
            $this->item_brand = $manufacturer_name;
        }


        // quantity
        $this->quantity = $this->getQuantity($product, $id_product, $id_product_attribute, $gtm->display_product_stock);


        // product price
        if(!empty($product->id_order) && !empty($product->unit_price_tax_excl)) {
            // price from order
            $this->price_tax_inc = (string) $product->unit_price_tax_incl;
            $this->price_tax_exc = (string) $product->unit_price_tax_excl;
        } else {
            // compute price
            $qtityForPrice = (!empty($product->product_quantity) || !empty($product->cart_quantity)) ? $this->quantity : 1;
            //$this->price = (string) Product::getPriceStatic($id_product, true, $id_product_attribute, _CDCGTM_PRICE_DECIMAL_, null, false, true, $qtityForPrice);
            $this->price_tax_inc = (string) Product::getPriceStatic($id_product, true, $id_product_attribute, _CDCGTM_PRICE_DECIMAL_, null, false, true, $qtityForPrice);
            $this->price_tax_exc = (string) Product::getPriceStatic($id_product, false, $id_product_attribute, _CDCGTM_PRICE_DECIMAL_, null, false, true, $qtityForPrice);
        }

        $this->price = $gtm->main_price_with_tax ? $this->price_tax_inc : $this->price_tax_exc;
        if(!$gtm->display_product_price_tax_detail) {
            $this->price_tax_inc = null;
            $this->price_tax_exc = null;
        }


        // wholesale price
        if($gtm->display_wholesale_price) {
            if ($variant && $variant->wholesale_price > 0) {
                $wholesale_price = $variant->wholesale_price;
            } else {
                $wholesale_price = $product->wholesale_price;
            }
            $this->wholesale_price = (string)round((float)$wholesale_price, _CDCGTM_PRICE_DECIMAL_);
        }
    }

    /**
     * Return product quantity for datalayer
     * @param $product
     * @param $id_product
     * @param $id_product_attribute
     * @param $display_product_stock
     * @return int
     */
    protected function getQuantity($product, $id_product, $id_product_attribute, $display_product_stock) {
        $quantity = 1;

        // order quantity
        if(!empty($product->product_quantity)) {
            $quantity = (int) $product->product_quantity;
        }
        // cart quantity
        elseif(!empty($product->cart_quantity)) {
            $quantity = (int) $product->cart_quantity;
        }
        // stock
        elseif($display_product_stock) {
            if(!empty($product->quantity)) {
                $quantity = (int) $product->quantity;
            } else {
                $quantity = (int) StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
            }
        }

        return $quantity;
    }

}
