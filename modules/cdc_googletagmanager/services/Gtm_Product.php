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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Gtm_Product
{
    private static $instance = null;
    protected $gtm;

    /**
     * Gtm_Product constructor.
     */
    private function __construct() {
        $this->gtm = Module::getInstanceByName('cdc_googletagmanager');
    }

    /**
     * @return Gtm_Product instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Gtm_Product();
        }
        return self::$instance;
    }


    /**
     * Return the product identifier for GA EE datalayer
     *
     * @param   $product
     * @param   $id_product_attribute
     * @return
     */
    public function getProductId($product, $combination) {
        return $this->getProductIdentifier($product, $combination, $this->gtm->product_id_field);
    }

    /**
     * Return the product identifier for remarketing
     *
     * @param  $product
     * @return
     */
    public function remarketingGetProductIdentifier($product) {
        $id_product_attribute = PrestashopUtils::getProductAttributeId($product, $this->gtm->display_variant_id);
        $combination = null;
        if($id_product_attribute) {
            $combination = new Combination($id_product_attribute);
        }

        return $this->getProductIdentifier($product, $combination, $this->gtm->product_identifier);
    }

    /**
     * Return the product identifier of the given product
     *
     * @param  array $product
     * @return string product identifier
     */
    public function getProductIdentifier($product, $combination = null, $id_type = null) {
        $product = is_object($product) ? $product : (object)$product;
        $product_identifier = null;

        if(empty($id_type) || !in_array($id_type, $this->gtm->conf['id_types'])) {
            $id_type = 'id';
        }

        // combination
        if(!is_null($combination) && Validate::isLoadedObject($combination)) {
            $id_product_attribute = $combination->id;
        } else {
            $combination = null;
            $id_product_attribute = null;
        }

        // if id product is "id_product" instead of "id"
        if(empty($product->id) && !empty($product->id_product)) {
            $product->id = $product->id_product;
        }

        // get product identifier
        switch ($id_type) {
            case 'id':
                $product_id = !empty($product->id) ? $product->id : 0;
                $product_identifier = null;

                // display product variant
                if($this->gtm->display_variant_id != 'never') {
                    if(!$id_product_attribute && !empty($product->id_product_attribute)) {
                        $id_product_attribute = $product->id_product_attribute;
                    }

                    if($id_product_attribute) {
                        $product_identifier = $product_id.$this->gtm->variant_id_separator.$id_product_attribute;
                    } elseif($this->gtm->display_variant_id == 'always') {
                        $product_identifier = $product_id.$this->gtm->variant_id_separator.Product::getDefaultAttribute($product_id);
                    }
                }

                $product_identifier = $product_identifier ?: ''.$product_id;
                break;

            case 'reference':
            case 'ean13':
            case 'upc':
                if($combination && !empty($combination->$id_type)) {
                    $product_identifier = $combination->$id_type;
                } elseif(!empty($product->$id_type)) {
                    $product_identifier = $product->$id_type;
                }

                break;

            default:
                $product_identifier = 'ERROR';
                break;
        }

        // format product identifier
        if(!empty($this->gtm->product_id_format)) {
            $formattedProductIdentifier = $this->gtm->product_id_format;
            if (strpos($formattedProductIdentifier, '{ID}') !== false) {
                $formattedProductIdentifier = str_replace('{ID}', $product_identifier, $formattedProductIdentifier);
            }
            if (strpos($formattedProductIdentifier, '{lang}') !== false) {
                $formattedProductIdentifier = str_replace('{lang}', Context::getContext()->language->iso_code, $formattedProductIdentifier);
            }
            if (strpos($formattedProductIdentifier, '{LANG}') !== false) {
                $formattedProductIdentifier = str_replace('{LANG}', Tools::strtoupper(Context::getContext()->language->iso_code), $formattedProductIdentifier);
            }

            $product_identifier = $formattedProductIdentifier;
        }

        return $product_identifier;
    }


    /**
     * Return the product name
     *
     * @param  $product
     * @param  $id_lang
     * @return String
     */
    public function getProductName($product, $product_name_field = null) {
        $base_product = new Product($product->id);
        $product_name_field = empty($product_name_field) ? $this->gtm->product_name_field : $product_name_field;
        $id_lang = $this->gtm->getDataLanguage();

        switch ($product_name_field) {
            case 'link_rewrite':
                if(!empty($base_product->link_rewrite)) {
                    if(is_array($base_product->link_rewrite) && count($base_product->link_rewrite)) {
                        $product_name = !empty($base_product->link_rewrite[$id_lang]) ? $base_product->link_rewrite[$id_lang] : $base_product->link_rewrite[0];
                    } else {
                        $product_name = $base_product->link_rewrite;
                    }
                }
                break;

            case 'id':
                $product_name = $base_product->id;
                break;

            default:
                $product_name = $this->gtm->cleanString(Product::getProductName($base_product->id, 0, $id_lang));
                break;
        }

        $product_name = trim($product_name);

        if(empty($product_name)) {
            $product_name = 'Unknown product name';
        }
        return $product_name;
    }
}