<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Team <sales@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @product  Ced
 * @package   CedWish
 */

require_once _PS_MODULE_DIR_ . 'cedwish/classes/profile.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/api.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/feed.php';
require_once _PS_MODULE_DIR_ . 'cedwish/lib/StripTags.php';

class CedWishProduct extends ObjectModel
{
    protected $mapped_options = array();

    public static $definition = array(
        'table' => 'cedwish_product',
        'primary' => 'id_cedwish_product',
        'multilang' => false,
        'fields' => array(
            'id_cedwish_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'marketplace_id' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'variation_id' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'int'
            ),
            'id_product_attribute' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'int'
            ),
            'status' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'error' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'marketplace_data' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'product_level_data' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'enabled' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'int'
            ),
            'id_profile' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'int'
            ),
        ),
    );
    public $id_cedwish_product;
    public $marketplace_id;
    public $id_product;
    public $id_product_attribute;
    public $status;
    public $error;
    public $marketplace_data;
    public $variation_id;
    public $product_level_data;
    public $enabled;
    public $id_profile;
    protected $mp_lang = false;

    public function __construct($id_cedwish_product = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id_cedwish_product, $idLang, $idShop);
        $this->mp_lang = (int)Configuration::get('CED_WISH_LANG_ID');
    }

    public function createProduct($product)
    {
        $mp_product = new CedWishproduct();
        foreach ($product as $key => $value) {
            if (!is_array($value)) {
                $mp_product->{$key} = $value;
            }
        }
        $mp_product->add();
        if (isset($product['subcategorias']) && !empty($product['subcategorias'])) {
            foreach ($product['subcategorias'] as $subproduct) {
                $this->createProduct($subproduct);
            }
        }
    }

    public function assignToProfile($id_product, $product)
    {
        if ($id_product) {
            $categories = $product->id_category_default;
            $manufacturers = $product->id_manufacturer;
            $suppliers = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                "SELECT id_supplier FROM `" . _DB_PREFIX_ . "product_supplier` 
                WHERE id_product = '" . (int)$id_product . "'"
            );

            $price = $product->price;

            $query = new DbQuery();
            $query->select('id_cedwish_profile');
            $query->from('cedwish_profile');
            if ($categories) {
                $query->where("categories LIKE '%" . pSQL('"' . $categories . '"') . "%'");
            }
            if ($manufacturers) {
                $query->where("manufacturers LIKE '%" . pSQL('"' . $manufacturers . '"') . "%'");
            }

            if ($suppliers) {
                $query->where("manufacturers LIKE '%" . pSQL('"' . $suppliers . '"') . "%'");
            }

            if ($price) {
                $query->where("price_to >= '" . (float)$price . "' AND price_from < '" . (float)$price . "' ");
            }
            $id_cedwish_profile = Db::getInstance()->getValue($query);
            if ($id_cedwish_profile) {
                $data = array(
                    'id_cedwish_profile' => $id_cedwish_profile,
                    'id_product' => $id_product,
                    'id_shop' => Context::getContext()->shop->id,
                );
                return Db::getInstance()->insert(
                    'cedwish_profile_product',
                    $data,
                    false,
                    true,
                    Db::ON_DUPLICATE_KEY
                );
            }
        }
        return false;
    }

    public function getWishInfoByProductId($productId)
    {
        $api = new CedWishApi();
        return $api->getProduct($productId);
    }

    public function deleteProduct($ids)
    {
        $result = array();
        $api = new CedWishApi();
        foreach ($ids as $id_product) {
            $marketplace_ids = Db::getInstance()->executeS(
                "SELECT id_cedwish_product, marketplace_id FROM `"._DB_PREFIX_."cedwish_product` 
                        WHERE id_product = '".(int)$id_product."' AND marketplace_id > 0 "
            );

            if (!empty($marketplace_ids)) {
                foreach ($marketplace_ids as $marketplace_id) {
                    $response = $api->deleteProduct(trim($marketplace_id['marketplace_id']));
                    if (isset($response['success']) && $response['success']) {
                        $product = new CedWishProduct((int)$marketplace_id['id_cedwish_product']);
                        $product->marketplace_id = 0;
                        $product->status = 'REMOVED';
                        try {
                            $product->update();
                        } catch (PrestaShopDatabaseException $e) {
                            $result[] = 'Some Error occurred ';
                        } catch (PrestaShopException $e) {
                            $result[] = 'Some Error occurred ';
                        }
                        $result[] = 'Product Deleted Successfully. '.$response['message'];
                    } elseif (isset($response['message'])) {
                        $result[] = $response['message'];
                    } else {
                        $result[] = 'Some Error occurred ';
                    }
                }
            }
        }
        return $result;
    }

    public function syncProduct($ids)
    {
        $result = array();
        foreach ($ids as $id) {
            $sql = new DbQuery();
            $sql->select('marketplace_id,id_product_attribute,id_product');
            $sql->from('cedwish_product');
            $sql->where(
                'id_product="'.(int)$id.'" AND (id_product_attribute="0") AND (enabled = 1)'
            );
            $product = Db::getInstance()->getRow($sql);
            if (!empty($product)) {
                $marketplace_id = $product['marketplace_id'];
                $item = $this->getWishInfoByProductId($marketplace_id);
                if (isset($item['code']) && ($item['code']==0)) {
                    $item = $item['data'];
                    if (isset($item['variations'])
                        && is_array($item['variations'])
                        && !empty($item['variations'])
                        && (count($item['variations'])==1)
                        && ($item['variations']['0']['sku']==$item['parent_sku'])
                    ) {
                        $this->addProductError(
                            (int)$id,
                            0,
                            $item['id'],
                            array(),
                            $item['variations']['0']['status'],
                            $item['variations']['0']['id']
                        );
                        $result['success'][] = $id;
                    } elseif (isset($item['variations'])
                        && is_array($item['variations'])
                        && !empty($item['variations'])
                    ) {
                        $this->addProductError(
                            (int)$id,
                            0,
                            $item['id'],
                            array(),
                            $item['status']
                        );
                        $result['success'][] = $id;
                        foreach ($item['variations'] as $variation) {
                            $product_by_sku = self::getProductBySKU($variation['sku']);
                            if (isset($product_by_sku['id_product']) &&
                                ($product_by_sku['id_product'] == $id)) {
                                $this->addProductError(
                                    $id,
                                    $product_by_sku['id_product_attribute'],
                                    $variation['product_id'],
                                    array(),
                                    $variation['status'],
                                    $variation['id']
                                );
                            }
                        }
                    }
                } elseif (isset($item['message'])) {
                    $result['error'][] = $item['message'];
                } else {
                    $result['error'][] = 'Failed to sync Item';
                }
            } else {
                $result['error'][] = 'No valid item sync';
            }
        }
        return $result;
    }

    public function updateProductStatus($ids, $status)
    {
        $ids = array_map('intval', $ids);
        $result = array(
            'success' => array(),
            'error' => array(),
        );
        $sql = new DbQuery();
        $sql->select('marketplace_id,id_product_attribute,id_product');
        $sql->from('cedwish_product');
        $sql->where(
            "id_product IN ('".implode("', '", $ids)."') AND (id_product_attribute=0) AND (enabled = 1)"
        );
        try {
            $updates = array();
            $products = Db::getInstance()->executeS($sql);
            foreach ($products as $product) {
                $updates[] = array(
                    'id' => $product['marketplace_id'],
                    'status' => $status
                );
            }
            if (!empty($updates)) {
                $api = new CedWishApi();
                $item = $api->massUpdateProduct($updates);
                if (isset($item['code'])
                    && ($item['code']==0)
                ) {
                    $feed = new CedWishFeed();
                    $feed->addNewJob($item['data']);
                    $result['success'][] = 'Items '. Tools::strtoupper($status) .' request send successfully.';
                } elseif (isset($item['message'])) {
                    $result['error'][] = $item['message'];
                } else {
                    $result['error'][] = ' failed to '. Tools::strtoupper($status) .' items';
                }
            } else {
                $result['error'][] = 'No valid item sync';
            }
        } catch (PrestaShopDatabaseException $e) {
            $result['error'][] = $e->getMessage();
        }
        return $result;
    }

    public function updateProductStockAndPrice($ids, $both = 'Price and stock')
    {
        $ids = array_map('intval', $ids);
        $result = array(
            'success' => array(),
            'error' => array(),
        );
        $sql = new DbQuery();
        $sql->select('marketplace_id,id_product_attribute,id_product,variation_id,status');
        $sql->from('cedwish_product');
        $sql->where(
            "id_product IN ('".implode("', '", $ids)."') 
            AND (marketplace_id !='') AND (variation_id !='') AND (enabled = 1)"
        );
        try {
            $updates = array();
            $products = Db::getInstance()->executeS($sql);
            if (!empty($products)) {
                $id_products = array_column($products, 'id_product');
                $id_products = array_count_values($id_products);
                foreach ($products as $product) {
                    $inventories = $this->getInventories(
                        array(
                            'id_product' => (int)$product['id_product'],
                            'id_product_attribute' => (int)$product['id_product_attribute']
                        )
                    );

                    $quantity_value = $this->manageQuantityThreshold(
                        StockAvailable::getQuantityAvailableByProduct(
                            (int)$product['id_product'],
                            (int)$product['id_product_attribute']
                        )
                    );

                    $status = self::getProductStatus(
                        $quantity_value,
                        $product['status']
                    );

                    if (isset($updates[(int)$product['id_product']])
                        && (int)$product['id_product_attribute']
                    ) {
                        if ($both == 'stock') {
                            $updates[(int)$product['id_product']]['variations'][] = array(
                                'id' => $product['variation_id'],
                                'inventories' => $inventories,
                                'quantity_value' => $quantity_value,
                                'status' => $status
                            );
                        } elseif ($both == 'price') {
                            $updates[(int)$product['id_product']]['variations'][] = array(
                                'id' => $product['variation_id'],
                                'inventories' => $inventories,
                                'quantity_value' => $quantity_value,
                                'price' => self::getMarketplacePrice(
                                    (int)$product['id_product'],
                                    (int)$product['id_product_attribute'],
                                    false
                                )
                            );
                        } else {
                            $updates[(int)$product['id_product']]['variations'][] = array(
                                'id' => $product['variation_id'],
                                'inventories' => $inventories,
                                'quantity_value' => $quantity_value,
                                'status' => $status,
                                'price' => self::getMarketplacePrice(
                                    (int)$product['id_product'],
                                    (int)$product['id_product_attribute'],
                                    false
                                )
                            );
                        }
                    } elseif (!isset($updates[(int)$product['id_product']])) {
                        $updates[(int)$product['id_product']] = array(
                            'id' => $product['marketplace_id'],
                            'variations' => array()
                        );
                    }

                    if (isset($id_products[(int)$product['id_product']])
                        && ($id_products[(int)$product['id_product']]==1)
                    ) {
                        if ($both == 'stock') {
                            $updates[(int)$product['id_product']] = array(
                                'id' => $product['marketplace_id'],
                                'variations' => array(
                                    array(
                                        'id' => $product['variation_id'],
                                        'inventories' => $inventories,
                                        'quantity_value' => $quantity_value,
                                        'status' => $status
                                    )
                                )
                            );
                        } elseif ($both == 'price') {
                            $updates[(int)$product['id_product']] = array(
                                'id' => $product['marketplace_id'],
                                'variations' => array(
                                    array(
                                        'id' => $product['variation_id'],
                                        'inventories' => $inventories,
                                        'quantity_value' => $quantity_value,
                                        'price' => self::getMarketplacePrice(
                                            (int)$product['id_product'],
                                            (int)$product['id_product_attribute'],
                                            false
                                        )
                                    )
                                )
                            );
                        } else {
                            $updates[(int)$product['id_product']] = array(
                                'id' => $product['marketplace_id'],
                                'variations' => array(
                                    array(
                                        'id' => $product['variation_id'],
                                        'inventories' => $inventories,
                                        'quantity_value' => $quantity_value,
                                        'status' => $status,
                                        'price' => self::getMarketplacePrice(
                                            (int)$product['id_product'],
                                            (int)$product['id_product_attribute'],
                                            false
                                        )
                                    )
                                )
                            );
                        }
                    }
                }
            } else {
                $result['error'][] = 'No Valid Item to sync.';
            }
            if (!empty($updates)) {
                $api = new CedWishApi();
                $updates = array_values($updates);
                $item = $api->massUpdateProduct($updates);

                if (isset($item['code'])
                    && ($item['code']==0)
                ) {
                    $feed = new CedWishFeed();
                    $feed->addNewJob($item['data']);
                    $result['success'] = 'Items '.$both.' request send successfully.';
                } elseif (isset($item['message'])) {
                    $result['error'][] = $item['message'];
                } else {
                    $result['error'][] = ' failed to disabled items';
                }
            }
        } catch (PrestaShopDatabaseException $e) {
            $result['error'][] = $e->getMessage();
        }
        return $result;
    }

    public function upload($product_ids, $allowed_attributes_ids = array())
    {
        $errors = array();
        $included_ids = array();
        foreach ($product_ids as $product_id) {
            try {
                $product = new Product((int)$product_id, true, (int)$this->mp_lang);
                if (($product->active==0) && !(int)Configuration::get('CED_WISH_SEND_DISABLE_ITEM')) {
                    $errors[] = 'Item skipped due to inactive state';
                    $this->addProductError(
                        $product->id,
                        0,
                        0,
                        $errors
                    );
                }
                if ($product && $product->id) {
                    $product_info = (array)$product;
                    $mp_product = $this->getProductData(
                        (int)$product->id,
                        $product,
                        $product_info,
                        $allowed_attributes_ids
                    );

                    if (isset($mp_product['success']) && !$mp_product['success']) {
                        if (isset($mp_product['msg']) && $mp_product['msg']) {
                            $errors[] = $mp_product['msg'];
                        } else {
                            $errors[] = 'Item skipped due to inactive state';
                        }
                        $this->addProductError(
                            $product->id,
                            0,
                            0,
                            $errors
                        );
                        continue;
                    }

                    if (!empty($mp_product)) {
                        $validation_error = array();
                        $is_valid = $this->validate($mp_product, $validation_error, true);
                        if (!$is_valid) {
                            $this->addProductError(
                                $product->id,
                                0,
                                0,
                                $validation_error
                            );
                        } else {
                            $product_level_data = $this->getProductLevelData(
                                (int)$product->id,
                                0
                            );
                            if (!empty($product_level_data)) {
                                foreach ($product_level_data as $field => $value) {
                                    if ((isset($mp_product[$field]) && !$mp_product[$field])
                                        || !isset($mp_product[$field])
                                    ) {
                                        if (in_array($field, array('tags'))) {
                                            $mp_product[$field] = explode(",", $value);
                                        } else {
                                            $mp_product[$field] = $value;
                                        }
                                    }
                                }
                            }

                            $mp_product['inventory'] = $this->manageQuantityThreshold(
                                StockAvailable::getQuantityAvailableByProduct((int)$product->id)
                            );

                            if (isset($mp_product['unit']) && ($mp_product['unit']=='None')) {
                                unset($mp_product['unit']);
                            }


                            if (!isset($mp_product['tags']) || (isset($mp_product['tags']) && !$mp_product['tags'])) {
                                $mp_product['tags'] = array();
                            }


                            if (isset($mp_product['tags']) && is_string($mp_product['tags'])) {
                                $mp_product['tags'] = explode(", ", $mp_product['tags']);
                                if (empty($mp_product['tags'])) {
                                    unset($mp_product['tags']);
                                }
                            } elseif (isset($mp_product['tags']) && !is_array($mp_product['tags'])) {
                                unset($mp_product['tags']);
                            } elseif (is_array($mp_product['tags']) && empty($mp_product['tags'])) {
                                unset($mp_product['tags']);
                            }

                            if (isset($mp_product['tags']) && !empty($mp_product['tags'])) {
                                $mp_product['tags'] = array_slice($mp_product['tags'], 0, 10);
                            }


                            if (isset($mp_product['video']) && $mp_product['video']) {
                                $mp_product['video'] = array(
                                    'url' => $mp_product['video']
                                );
                            } else {
                                unset($mp_product['video']);
                            }

                            $api = new CedWishApi();
                            if (isset($mp_product['id']) && $mp_product['id']) {
                                if (isset($mp_product['variations']) && !empty($mp_product['variations'])) {
                                    $variation_ids = array_column($mp_product['variations'], 'id');
                                    foreach ($variation_ids as $index => $variation_id) {
                                        if ($variation_id=='is_new') {
                                            $result = $api->createVariation(
                                                $mp_product['id'],
                                                $mp_product['variations'][$index]
                                            );
                                            if (isset($result['code']) && ($result['code']==0)) {
                                                $result = $result['data'];
                                                $product_by_sku = self::getProductBySKU($result['sku']);
                                                if (isset($product_by_sku['id_product']) &&
                                                    ($product_by_sku['id_product'] == (int)$product->id)) {
                                                    $this->addProductError(
                                                        (int)$product->id,
                                                        $product_by_sku['id_product_attribute'],
                                                        $result['product_id'],
                                                        array(),
                                                        $result['status'],
                                                        $result['id']
                                                    );
                                                }
                                                $mp_product['variations'][$index]['id'] = $result['id'];
                                            } else {
                                                if (isset($result['message']) && $result['message']) {
                                                    $this->addProductError(
                                                        (int)$product->id,
                                                        0,
                                                        $mp_product['id'],
                                                        $result['message'],
                                                        '',
                                                        ''
                                                    );
                                                }
                                                unset($mp_product['variations'][$index]);
                                                $mp_product['variations'] = array_values($mp_product['variations']);
                                            }
                                        }
                                    }
                                }

                                $response = $api->updateProduct($mp_product['id'], $mp_product);

                                /**
                                For case when item id not on wish any more
                                 */
                                if ($response['code']=='1003') {
                                    unset($mp_product['id']);
                                    $response = $api->createProduct($mp_product);
                                }
                            } else {
                                if (isset($mp_product['variations']) && !empty($mp_product['variations'])) {
                                    $variation_ids = array_column($mp_product['variations'], 'id');
                                    foreach ($variation_ids as $index => $variation_id) {
                                        if ($variation_id=='is_new') {
                                            if (isset($mp_product['variations'][$index])
                                                && !empty($mp_product['variations'][$index])
                                            ) {
                                                unset($mp_product['variations'][$index]['id']);
                                            }
                                        }
                                    }
                                }
                                $response = $api->createProduct($mp_product);
                            }

                            if (isset($response['code']) && ($response['code']==0)) {
                                $included_ids[] = (int)$product->id;
                                if (is_array($response['data']) && !empty($response['data'])) {
                                    $item = $response['data'];
                                    if (isset($item['variations'])
                                        && is_array($item['variations'])
                                        && !empty($item['variations'])
                                    ) {
                                        if ((count($item['variations'])==1)
                                            && isset($item['parent_sku'])
                                            && isset($item['variations']['0']['sku'])
                                            && ($item['parent_sku'] == $item['variations']['0']['sku'])
                                        ) {
                                            $this->addProductError(
                                                (int)$product->id,
                                                0,
                                                $item['id'],
                                                array(),
                                                $item['status'],
                                                $item['variations']['0']['id']
                                            );
                                        } else {
                                            foreach ($item['variations'] as $variation) {
                                                $product_by_sku = self::getProductBySKU($variation['sku']);
                                                if (isset($product_by_sku['id_product']) &&
                                                    ((int)$product_by_sku['id_product'] == (int)$product->id)) {
                                                    $this->addProductError(
                                                        (int)$product->id,
                                                        $product_by_sku['id_product_attribute'],
                                                        $variation['product_id'],
                                                        array(),
                                                        $variation['status'],
                                                        $variation['id']
                                                    );
                                                }
                                            }
                                            $this->addProductError(
                                                (int)$product->id,
                                                0,
                                                $item['id'],
                                                array(),
                                                $item['status'],
                                                $item['id']
                                            );
                                        }
                                    }
                                } elseif ($response['message']) {
                                    $errors[] = $response['message'];
                                    $this->addProductError(
                                        $product->id,
                                        0,
                                        0,
                                        $errors
                                    );
                                }
                            } elseif (isset($response['message']) && $response['message']) {
                                $errors[] = $response['message'];
                                $this->addProductError(
                                    $product->id,
                                    0,
                                    0,
                                    $errors
                                );
                            } else {
                                $errors[] = 'Some Error while uploading items';
                            }
                        }
                    } else {
                        $errors[] = 'Invalid Product Id ' . $product->id;
                    }
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($included_ids)) {
            return array('success' => true, 'msg' => 'Items created.' . implode(', ', $included_ids));
        } elseif (!empty($errors)) {
            return array('success' => false, 'msg' => implode(', ', $errors));
        } else {
            return array('success' => false, 'msg' => 'No Valid items to upload. Please see error in items');
        }
    }

    public function massUpdate($product_ids, $allowed_attributes_ids = array())
    {
        $return = array();
        $mp_products = array();
        $api = new CedWishApi();
        foreach ($product_ids as $product_id) {
            try {
                $product = new Product((int)$product_id, true, (int)$this->mp_lang);
                if ($product && $product->id) {
                    $product_info = (array)$product;
                    $mp_product = $this->getProductData(
                        (int)$product->id,
                        $product,
                        $product_info,
                        $allowed_attributes_ids
                    );

                    if (isset($mp_product['success']) && !$mp_product['success']) {
                        $errors = array();
                        if (isset($mp_product['msg']) && $mp_product['msg']) {
                            $errors[] = $mp_product['msg'];
                        } else {
                            $errors[] = 'Item skipped due to inactive state';
                        }
                        $this->addProductError(
                            $product->id,
                            0,
                            0,
                            $errors
                        );
                        $return['errors'] = $errors;
                        continue;
                    }

                    if (!empty($mp_product) && isset($mp_product['id']) && $mp_product['id']) {
                        $validation_error = array();
                        $is_valid = $this->validate($mp_product, $validation_error, true);
                        if (!$is_valid) {
                            $this->addProductError(
                                $product->id,
                                0,
                                0,
                                $validation_error
                            );
                        } else {
                            $product_level_data = $this->getProductLevelData(
                                (int)$product->id,
                                0
                            );
                            if (!empty($product_level_data)) {
                                foreach ($product_level_data as $field => $value) {
                                    if ((isset($mp_product[$field]) && !$mp_product[$field])
                                        || !isset($mp_product[$field])
                                    ) {
                                        $mp_product[$field] = $value;
                                    }
                                }
                            }

                            $mp_product['inventory'] = $this->manageQuantityThreshold(
                                StockAvailable::getQuantityAvailableByProduct((int)$product->id)
                            );

                            if (isset($mp_product['unit']) && ($mp_product['unit']=='None')) {
                                unset($mp_product['unit']);
                            }


                            if (!isset($mp_product['tags']) || (isset($mp_product['tags']) && !$mp_product['tags'])) {
                                $mp_product['tags'] = array();
                            }


                            if (isset($mp_product['tags']) && is_string($mp_product['tags'])) {
                                $mp_product['tags'] = explode(", ", $mp_product['tags']);
                                if (empty($mp_product['tags'])) {
                                    unset($mp_product['tags']);
                                }
                            } elseif (isset($mp_product['tags']) && !is_array($mp_product['tags'])) {
                                unset($mp_product['tags']);
                            } elseif (is_array($mp_product['tags']) && empty($mp_product['tags'])) {
                                unset($mp_product['tags']);
                            }

                            if (isset($mp_product['tags']) && !empty($mp_product['tags'])) {
                                $mp_product['tags'] = array_slice($mp_product['tags'], 0, 10);
                            }

                            if (isset($mp_product['video']) && $mp_product['video']) {
                                $mp_product['video'] = array(
                                    'url' => $mp_product['video']
                                );
                                /* TODO remove once fixed with correct format */
                                unset($mp_product['video']);
                            } elseif (isset($mp_product['video'])) {
                                unset($mp_product['video']);
                            }

                            if (isset($mp_product['id']) && $mp_product['id']) {
                                if (isset($mp_product['variations']) && !empty($mp_product['variations'])) {
                                    $variation_ids = array_column($mp_product['variations'], 'id');
                                    foreach ($variation_ids as $index => $variation_id) {
                                        if ($variation_id=='is_new') {
                                            $result = $api->createVariation(
                                                $mp_product['id'],
                                                $mp_product['variations'][$index]
                                            );
                                            if (isset($result['code']) && ($result['code']==0)) {
                                                $result = $result['data'];
                                                $product_by_sku = self::getProductBySKU($result['sku']);
                                                if (isset($product_by_sku['id_product']) &&
                                                    ($product_by_sku['id_product'] == (int)$product->id)) {
                                                    $this->addProductError(
                                                        (int)$product->id,
                                                        $product_by_sku['id_product_attribute'],
                                                        $result['product_id'],
                                                        array(),
                                                        $result['status'],
                                                        $result['id']
                                                    );
                                                }
                                                $mp_product['variations'][$index]['id'] = $result['id'];
                                            } else {
                                                if (isset($result['message']) && $result['message']) {
                                                    $this->addProductError(
                                                        (int)$product->id,
                                                        0,
                                                        $mp_product['id'],
                                                        $result['message'],
                                                        '',
                                                        ''
                                                    );
                                                }
                                                unset($mp_product['variations'][$index]);
                                                $mp_product['variations'] = array_values($mp_product['variations']);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $mp_products[] = $mp_product;
                    } else {
                        $return['errors'][] = 'Invalid Product Id ' . $product->id;
                    }
                }
            } catch (Exception $e) {
                $return['errors'][] = $e->getMessage();
                continue;
            }
        }

        $response = $api->massUpdateProduct($mp_products);
        if (isset($response['code']) && ($response['code']==0)) {
            $data = $response['data'];
            $feed = new CedWishFeed();
            $feed->state = $data['status'];
            $feed->processed_count = $data['total_count'];
            $feed->job_id = $data['id'];
            $feed->start_time = pSQL(str_replace("T", " ", $data['created_at']));
            $feed->success_count = $data['processed_count'];
            $feed->response = pSQL(json_encode($data));
            $feed->uploader_id = 'bulk_process';
            $feed->failure_count = 0;
            try {
                $feed->add();
                $return['success'] = 'Update Requested with Job ID '.$data['id'];
            } catch (PrestaShopDatabaseException $e) {
                $return['errors'][] = $e->getMessage();
            } catch (PrestaShopException $e) {
                $return['errors'][] = $e->getMessage();
            }
        } elseif (isset($response['message']) && $response['message']) {
            $return['errors'][]  = $response['message'];
        }
        return $return;
    }

    public function toggleFeedEnabled()
    {
        if (!array_key_exists('enabled', $this)) {
            throw new PrestaShopException('property "enabled" is missing in object ' . get_class($this));
        }

        if ($this->id_cedwish_product) {
            $this->setFieldsToUpdate(array('enabled' => true));
            $this->enabled = !(int)$this->enabled;
            return $this->update(false);
        } else {
            if ($this->id_product) {
                $this->setFieldsToUpdate(
                    array(
                        'enabled' => true,
                        'id_product_attribute' => $this->id_product_attribute,
                        'id_product' => $this->id_product,
                    )
                );
                $this->enabled = !(int)$this->enabled;
                return $this->add(false);
            }
        }
    }

    public function getProductData($product_id, $product, $product_info)
    {
        $item = array();
        try {
            $marketplace_row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                "SELECT marketplace_id,variation_id,enabled FROM `" . _DB_PREFIX_ . "cedwish_product` WHERE 
                                `id_product` = '" . (int)$product_id . "' AND `id_product_attribute` = '0'"
            );
            $marketplace_id = false;
            $variation_id = false;
            $enabled = true;
            if (!empty($marketplace_row)) {
                $marketplace_id = $marketplace_row['marketplace_id'];
                $variation_id = $marketplace_row['variation_id'];
                if ((int)$marketplace_row['enabled'] == 0) {
                    $enabled = false;
                }
            }

            if (!$enabled) {
                return array('success' => false, 'msg' => 'This item is not enabled for sync');
            }
            $profile = CedWishProfile::getProfileByProductId((int)$product_id, true);
            if (isset($profile['attribute_mapping']) && $profile['attribute_mapping']) {
                $attributes = json_decode($profile['attribute_mapping'], true);
                $default_values = json_decode($profile['default_mapping'], true);
                $product_setting = json_decode($profile['product_setting'], true);
                if (is_array($attributes) && !empty($attributes)) {
                    foreach ($attributes as $attribute_name => $attribute_mapped) {
                        $item[trim($attribute_name)] = $this->getMappedAttributeValue(
                            $attribute_name,
                            $product,
                            $product_info,
                            $attribute_mapped,
                            $default_values
                        );
                    }

                    $default_shipping_prices =  json_decode($profile['default_shipping_prices'], true);
                    if (!empty($default_shipping_prices)) {
                        $item['default_shipping_prices'] = array_values($default_shipping_prices);
                    }

                    $warehouse_to_shippings =  json_decode($profile['warehouse_to_shippings'], true);
                    if (!empty($warehouse_to_shippings)) {
                        $item['warehouse_to_shippings'] = self::getWishWarehouseToShipping($warehouse_to_shippings);
                    }

                    $combinations = $this->getAttributesResume($product_id, $this->mp_lang);
                    $images = $this->getProductImageUrl($product);

                    if (isset($images['main_image']) && !empty($images['main_image'])) {
                        $item['main_image'] = $images['main_image'];
                    }

                    if (!empty($combinations)) {
                        foreach ($combinations as $combination_index => $combination) {
                            $child_marketplace_id = false;
                            $marketplace_row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                                "SELECT variation_id,enabled FROM `" . _DB_PREFIX_ . "cedwish_product` WHERE 
                                `id_product` = '" . (int)$combination['id_product'] . "' 
                                AND `id_product_attribute` = '".(int)$combination['id_product_attribute']."'"
                            );
                            $enabled = true;
                            if (!empty($marketplace_row)) {
                                $child_marketplace_id = $marketplace_row['variation_id'];
                                if ((int)$marketplace_row['enabled'] == 0) {
                                    $enabled = false;
                                }
                            }

                            if (!$enabled) {
                                continue;
                            }
                            $specific_price = false;
                            $prices = self::getMarketplacePrice(
                                (int)$combination['id_product'],
                                (int)$combination['id_product_attribute'],
                                false,
                                $product_setting,
                                $specific_price
                            );
                            $images = $this->getProductImageUrl($product, (int)$combination['id_product_attribute']);
                            if (isset($images['extra_images']) && !empty($images['extra_images'])) {
                                $item['extra_images'][] = $images['extra_images']['0'];
                            }
                            $item['variations'][$combination_index] = array(
                                'id' => $child_marketplace_id,
                                'inventories' => $this->getInventories($combination),
                                'quantity_value' => $this->manageQuantityThreshold(
                                    StockAvailable::getQuantityAvailableByProduct(
                                        (int)$combination['id_product'],
                                        (int)$combination['id_product_attribute']
                                    )
                                ),
                                'price' => $prices,
                                'sku' => self::getSKU($product, (int)$combination['id_product_attribute']),
                                'gtin' => self::getGTIN($product, (int)$combination['id_product_attribute']),
                                'options' => $this->getOptions($combination),
                                'logistics_details' => $this->getLogisticsDetails(
                                    $product,
                                    $combination,
                                    $product_setting
                                ),
                            );
                            if (trim($child_marketplace_id)) {
                                $item['variations'][$combination_index]['id'] = trim($child_marketplace_id);
                            } else {
                                $item['variations'][$combination_index]['id'] = 'is_new';
                            }
                            if ($marketplace_id) {
                                $item['id'] = $marketplace_id;
                            }
                        }
                    } else {
                        $specific_price = false;
                        $prices = self::getMarketplacePrice(
                            (int)$product_id,
                            0,
                            false,
                            $product_setting,
                            $specific_price
                        );

                        if ($variation_id) {
                            $item['variations'][] = array(
                                'id' => $variation_id,
                                'inventories' => $this->getInventories(
                                    array(
                                        'id_product' => $product_id,
                                        'id_product_attribute' => 0,
                                    )
                                ),
                                'quantity_value' => $this->manageQuantityThreshold(
                                    StockAvailable::getQuantityAvailableByProduct(
                                        (int)$product_id,
                                        0
                                    )
                                ),
                                'price' => $prices,
                                'sku' => self::getSKU($product)
                            );
                            $item['id'] = $marketplace_id;
                        } else {
                            $item['variations'][] = array(
                                'inventories' => $this->getInventories(
                                    array(
                                        'id_product' => $product_id,
                                        'id_product_attribute' => 0,
                                    )
                                ),
                                'quantity_value' => $this->manageQuantityThreshold(
                                    StockAvailable::getQuantityAvailableByProduct(
                                        (int)$product_id,
                                        0
                                    )
                                ),
                                'price' => $prices,
                                'sku' => self::getSKU($product)
                            );
                        }
                    }

                    $item['description'] = $this->sanitiseData($item['description']);

                    $item['parent_sku'] = self::getSKU($product);

                    $specific_price = null;
                    $prices = self::getMarketplacePrice(
                        $product_id,
                        0,
                        false,
                        $product_setting,
                        $specific_price
                    );

                    if (isset($prices['price']) && $prices['price']) {
                        $item['price'] = $prices['price'];
                    }

                    if (isset($prices['offer_price'])
                        && $prices['offer_price']
                        && ($prices['offer_price']!=$prices['price'])
                    ) {
                        $item['price'] = $prices['offer_price'];
                    }

                    if (isset($item['tags'])
                        && is_array($item['tags']) && !empty($item['tags'])
                    ) {
                        if (isset($item['tags']) && isset($item['tags']['1']) && is_array($item['tags']['1'])) {
                            $item['tags'] = $item['tags']['1'];
                        }

                        $item['tags'] = (string)implode(
                            ", ",
                            $item['tags']
                        );
                    }
                    $brand = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        "SELECT id_wish_brand FROM `"._DB_PREFIX_."cedwish_product_brand` 
                        WHERE id_product = '".(int)$product_id."'"
                    );
                    if ($brand) {
                        $item['brand_id'] = $brand;
                    }
                }
            } else {
                return array('success' => false, 'msg' => 'No attribute mapping found there.');
            }
        } catch (Exception $e) {
            return array('success' => false, 'msg' => $e->getMessage(),'trace' => $e->getTraceAsString());
        }
        return $item;
    }

    public function getMappedAttributeValue(
        $field_name,
        $product,
        $product_info,
        $attribute_mapped,
        $default_values,
        $id_product_attribute = 0
    ) {
        if ($field_name
            && $attribute_mapped
            && ($id_product_attribute >= 0)
        ) {
            $ps_field_name = $attribute_mapped;
            $ps_field_name = explode('-', $ps_field_name);
            if (!empty($ps_field_name) && isset($ps_field_name['0']) && isset($ps_field_name['1'])) {
                if ($ps_field_name['0'] == 'system') {
                    return $this->getProductAttributeValue($ps_field_name['1'], $product_info, $attribute_mapped);
                }
                if ($ps_field_name['0'] == 'feature') {
                    return $this->getProductFeatureValue($ps_field_name['1'], (int)$product->id, $attribute_mapped);
                }
            }
        } elseif ($field_name
            && !empty($default_values)
        ) {
            return $default_values[$field_name];
        }
    }

    public function getProductAttributeValue($ps_field_name, $product_info, $mapping_data)
    {
        if (isset($product_info[$ps_field_name]) && $product_info[$ps_field_name]) {
            switch ($ps_field_name) {
                case 'id_manufacturer':
                    $mp_attribute_value = Manufacturer::getNameById($product_info['id_manufacturer']);
                    break;

                case 'description':
                    $mp_attribute_value = $product_info[$ps_field_name];
                    $mp_attribute_value = $this->sanitiseData($mp_attribute_value);
                    break;

                case 'tags':
                    $tags = $product_info['tags'];
                    $mp_attribute_value = isset($tags[$this->mp_lang]) ? $tags[$this->mp_lang] : '';
                    break;
                default:
                    $mp_attribute_value = $product_info[$ps_field_name];
                    break;
            }
            return $mp_attribute_value;
        } elseif (isset($mapping_data['default_value']) && $mapping_data['default_value']) {
            return $mapping_data['default_value'];
        }
    }

    protected function sanitiseData($mp_attribute_value)
    {
        $strip_tags = new \CedWish\StripTags(
            array(
                'allowTags' => array(
                    'br'
                ),
                'allowAttribs' => array()
            )
        );
        $content = html_entity_decode($mp_attribute_value);
        $content = str_replace('</p>', "</p></br>", $content);
        $content = str_replace('</li>', "</li></br>", $content);
        $content = str_replace('</ol>', "</ol></br>", $content);
        $content = preg_replace("/<strong(.*?)>(.*?)<\/strong>/", "<b>$2</b>", $content);
        $strip_tags->setTagsAllowed(['br']);
        $strip_tags->setAttributesAllowed([]);
        $content = $strip_tags->filter($content);
        $mp_attribute_value = html_entity_decode($content);
        $mp_attribute_value = str_replace("</br>", "\n", $mp_attribute_value);
        return $mp_attribute_value;
    }

    public function getProductFeatureValue($id_feature, $id_product, $mapping_data)
    {
        $product_feature_value = '';
        if ($id_product && $id_feature) {
            $sql_db_intance = Db::getInstance(_PS_USE_SQL_SLAVE_);
            $features = $sql_db_intance->executeS('
                SELECT value FROM ' . _DB_PREFIX_ . 'feature_product pf
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON (fl.id_feature = pf.id_feature 
                AND fl.id_lang = ' . (int)$this->mp_lang . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl 
                ON (fvl.id_feature_value = pf.id_feature_value 
                AND fvl.id_lang = ' . (int)$this->mp_lang . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON (f.id_feature = pf.id_feature 
                AND fl.id_lang = ' . (int)$this->mp_lang . ')
                ' . Shop::addSqlAssociation('feature', 'f') . '
                WHERE pf.id_product = ' . (int)$id_product . ' 
                AND fl.id_feature = "' . (int)$id_feature . '" 
                ORDER BY f.position ASC');
            if (!empty($features)) {
                foreach ($features as $feature) {
                    if (isset($feature['value']) && $feature['value']) {
                        $product_feature_value = $feature['value'];
                    }
                }
            }
            return $product_feature_value;
        } elseif (isset($mapping_data['default_value']) && $mapping_data['default_value']) {
            return $mapping_data['default_value'];
        }
    }

    public function getProductImageUrl($product, $id_product_attribute = 0)
    {
        if ($product->link_rewrite
            && is_array($product->link_rewrite)
            && isset($product->link_rewrite[$this->mp_lang])
            && $product->link_rewrite[$this->mp_lang]
        ) {
            $link_rewrite = $product->link_rewrite[$this->mp_lang];
        } else {
            $link_rewrite = $product->link_rewrite;
        }

        $type = Configuration::get('CEDWISH_IMAGE_TYPE');
        $link = new Link();
        $images = Image::getImages($this->mp_lang, $product->id, $id_product_attribute);
        $photos = array();
        if (!empty($images)) {
            foreach ($images as $image) {
                $image_url = $link->getImageLink(
                    $link_rewrite,
                    $image['id_image'],
                    $type
                );
                $image_url = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $image_url;
                if (Tools::strpos($image_url, "localhost")
                    && Configuration::get('CED_WISH_DEMO_IMAGE_FOR_LOCALHOST')
                ) {
                    $image_url = Configuration::get('CED_WISH_DEMO_IMAGE_FOR_LOCALHOST');
                }
                if (Tools::strpos($image_url, "127.0.0.1")
                    && Configuration::get('CED_WISH_DEMO_IMAGE_FOR_LOCALHOST')
                ) {
                    $image_url = Configuration::get('CED_WISH_DEMO_IMAGE_FOR_LOCALHOST');
                }
                if ($image['cover']==1) {
                    $photos['main_image'] = array(
                        'is_clean_image' => false,
                        'url' =>  $image_url,
                        'variation_skus' => array(self::getSKU($product, $id_product_attribute))
                    );
                } else {
                    $photos['extra_images'][] = array(
                        'is_clean_image' => false,
                        'url' =>  $image_url,
                        'variation_skus' => array(self::getSKU($product, $id_product_attribute))
                    );
                }
            }
        }
        return $photos;
    }

    public static function getMarketplacePrice(
        $id_product,
        $id_product_attribute,
        $usereduc,
        $product_offer_settings = array(),
        &$specific_price_output = null,
        $context = null
    ) {
        $product_level_markup = Db::getInstance()->getValue(
            "SELECT product_level_data FROM `" . _DB_PREFIX_ . "cedwish_product` 
            WHERE id_product = '" . (int)$id_product . "' 
            AND id_product_attribute = '" . (int)$id_product_attribute . "'"
        );

        if ($product_level_markup) {
            $product_level_markup = json_decode(Tools::getDescriptionClean($product_level_markup), true);
            if (isset($product_level_markup['fix_increment'])
                && isset($product_level_markup['percent_increment'])
                && $product_level_markup['fix_increment']
                && $product_level_markup['percent_increment']
            ) {
                $product_offer_settings = $product_level_markup;
            }
        }

        if (is_array($product_offer_settings) && !empty($product_offer_settings)) {
            $product_offer_settings = array_filter($product_offer_settings);
        }

        if (!$context) {
            $context = Context::getContext()->cloneContext();
        }

        if (Configuration::get('CED_WISH_CURRENCY_ID')) {
            $id_currency = (int)Configuration::get('CED_WISH_CURRENCY_ID');
        } elseif (Validate::isLoadedObject($context->currency)) {
            $id_currency = (int)$context->currency->id;
        } else {
            $id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        }

        $id_group = Configuration::get('CED_WISH_CUSTOMER_GROUP_ID');

        $price = Product::priceCalculation(
            $context->shop->id,
            $id_product,
            $id_product_attribute,
            $context->country->id,
            0,
            0,
            $id_currency,
            $id_group,
            1,
            true,
            2,
            false,
            $usereduc,
            true,
            $specific_price_output,
            true,
            null,
            false,
            null,
            0
        );

        $offer_price = false;
        $offer_price_start = false;
        $offer_price_end = false;
        if (!empty($specific_price_output)) {
            $offer_price = Product::priceCalculation(
                $context->shop->id,
                $id_product,
                $id_product_attribute,
                $context->country->id,
                0,
                0,
                $id_currency,
                $id_group,
                1,
                true,
                2,
                false,
                true,
                true,
                $specific_price_output,
                true,
                null,
                false,
                null,
                0
            );
            $offer_price_start = isset($specific_price_output['from'])?$specific_price_output['from']:'';
            $offer_price_end = isset($specific_price_output['to'])?$specific_price_output['to']:'';
        } else {
            $offer_price = Product::priceCalculation(
                $context->shop->id,
                $id_product,
                $id_product_attribute,
                $context->country->id,
                0,
                0,
                $id_currency,
                0,
                1,
                true,
                2,
                false,
                true,
                true,
                $specific_price_output,
                true,
                null,
                false,
                null,
                0
            );
            $offer_price_start = isset($specific_price_output['from']) ? $specific_price_output['from']:'';
            $offer_price_end = isset($specific_price_output['to']) ? $specific_price_output['to']:'';
        }

        $final_price = $price;

        if (!empty($product_offer_settings)
            && (isset($product_offer_settings['fix_increment']) || isset($product_offer_settings['percent_increment']))
        ) {
            if (isset($product_offer_settings['fix_increment']) && $product_offer_settings['fix_increment']) {
                $final_price = (float)$price + (float)$product_offer_settings['fix_increment'];
                $offer_price = (float)$offer_price + (float)$product_offer_settings['fix_increment'];
            }
            if (isset($product_offer_settings['percent_increment']) && $product_offer_settings['percent_increment']) {
                $final_price = (float)$final_price * (float)(1 + ($product_offer_settings['percent_increment'] * 0.01));
                $offer_price = (float)$offer_price * (float)(1 + ($product_offer_settings['percent_increment'] * 0.01));
            }
        } else {
            if (Configuration::get('CED_WISH_PRICE_INCR_PER')) {
                $final_price = (float)$price * (float)(1 + (Configuration::get('CED_WISH_PRICE_INCR_PER') * 0.01));
                $offer_price =
                    (float)$offer_price * (float)(1 + (Configuration::get('CED_WISH_PRICE_INCR_PER') * 0.01));
            }
            if (Configuration::get('CED_WISH_PRICE_INCR_FIX')) {
                $final_price = (float)$final_price + (float)Configuration::get('CED_WISH_PRICE_INCR_FIX');
                $offer_price = (float)$offer_price + (float)Configuration::get('CED_WISH_PRICE_INCR_FIX');
            }
        }

        if (!$offer_price) {
            $offer_price = $final_price;
        }

        $offer_price = number_format($offer_price, 2, '.', '');
        $price = number_format($final_price, 2, '.', '');
        $amount = $price;
        if ($offer_price
            && ($offer_price < $price)
            && ($offer_price_start <= date("Y-m-d"))
            && ($offer_price_end >= date("Y-m-d"))
        ) {
            $amount = $offer_price;
        }

        return array(
            'amount' => $amount,
            'currency_code' => Configuration::get('CED_WISH_WISH_CURRENCY')
        );
    }

    protected function validate(&$product, &$validation_error, $is_parent = false)
    {
        if (!empty($product)) {
            if (!(isset($product['gtin']) && preg_match('/^[0-9]{8,14}$/i', $product['gtin']))) {
                unset($product['gtin']);
            }

            if (isset($product['parent_sku']) && (Tools::strlen($product['parent_sku']) > 80)) {
                $validation_error[] = $product['parent_sku']. 'Parent SKU is greater than 80';
            }

            if ($is_parent && isset($product['video']) && !isset($product['video']['url'])) {
                unset($product['video']);
            }

            if (isset($product['variations']) && !empty($product['variations'])) {
                foreach ($product['variations'] as &$variation) {
                    if (!(isset($variation['gtin']) && preg_match('/^[0-9]{8,14}$/i', $variation['gtin']))) {
                        unset($variation['gtin']);
                    }
                    if (isset($product['sku']) && (Tools::strlen($product['sku']) > 80)) {
                        $validation_error[] = $product['sku'] . 'SKU is greater than 80';
                    } elseif ((count($product['variations'])==1) && !isset($product['sku'])) {
                        //$validation_error[] = 'SKU is Required.';
                    }
                }
            }

            if (empty($validation_error)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function addProductError(
        $product_id,
        $id_product_attribute = 0,
        $marketplace_id = 0,
        $error = array(),
        $status = 'Uploaded',
        $variation_id = ""
    ) {
        if ($marketplace_id) {
            Db::getInstance()->execute(
                "INSERT INTO `" . _DB_PREFIX_ . "cedwish_product` 
                        (
                        `id_cedwish_product`,
                        `id_product`,
                        `id_product_attribute`, 
                        `marketplace_id`, 
                        `error`,
                        `variation_id`,
                        `status` 
                        ) VALUES (
                            (SELECT `id_cedwish_product` FROM `" . _DB_PREFIX_ . "cedwish_product` 
                            cfp WHERE cfp.id_product = '" . (int)$product_id . "' AND 
                            cfp.id_product_attribute = '" . (int)$id_product_attribute . "' LIMIT 1), 
                            '" . (int)$product_id . "', 
                            '" . (int)$id_product_attribute . "', 
                            '" . pSQL($marketplace_id) . "', 
                            '" . pSQL(json_encode($error)) . "',
                            '" . pSQL($variation_id) . "',
                            '" . pSQL($status) . "'
                        )  ON DUPLICATE KEY UPDATE 
                            id_cedwish_product=values(id_cedwish_product), 
                            id_product=values(id_product), 
                            id_product_attribute=values(id_product_attribute), 
                            marketplace_id=values(marketplace_id), 
                            error=values(error),
                            variation_id=values(variation_id),
                            status=values(status)"
            );
        } else {
            Db::getInstance()->execute(
                "INSERT INTO `" . _DB_PREFIX_ . "cedwish_product` 
                        (
                        `id_cedwish_product`,
                        `id_product`,
                        `id_product_attribute`, 
                        `error`,
                        `variation_id`,
                        `status`  
                        ) VALUES (
                            (SELECT `id_cedwish_product` FROM `" . _DB_PREFIX_ . "cedwish_product` 
                            cfp WHERE cfp.id_product = '" . (int)$product_id . "' AND 
                            cfp.id_product_attribute = '" . (int)$id_product_attribute . "' LIMIT 1), 
                            '" . (int)$product_id . "', 
                            '" . (int)$id_product_attribute . "', 
                            '" . pSQL(json_encode($error)) . "',
                            '" . pSQL($variation_id) . "',
                            '" . pSQL($status) . "'
                        )  ON DUPLICATE KEY UPDATE 
                            id_cedwish_product=values(id_cedwish_product), 
                            id_product=values(id_product), 
                            id_product_attribute=values(id_product_attribute), 
                            error=values(error),
                            variation_id=values(variation_id),
                            status=values(status)"
            );
        }
    }

    /**
     * @param $product_id
     * @param $id_lang
     * @param string $attribute_value_separator
     * @param string $attribute_separator
     * @return array|bool|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function getAttributesResume(
        $product_id,
        $id_lang,
        $attribute_value_separator = ' - ',
        $attribute_separator = ', '
    ) {
        if (!Combination::isFeatureActive()) {
            return array();
        }

        $combinations = Db::getInstance()->executeS('SELECT pa.*, product_attribute_shop.*
                FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
                WHERE pa.`id_product` = ' . (int)$product_id . '
                GROUP BY pa.`id_product_attribute`');

        if (!$combinations) {
            return false;
        }

        $product_attributes = array();
        foreach ($combinations as $combination) {
            $product_attributes[] = (int)$combination['id_product_attribute'];
        }
        $attribute_id_separator = '#';
        $lang = Db::getInstance()->executeS(
            'SELECT pac.id_product_attribute,agl.`name` AS group_name, 
            GROUP_CONCAT(agl.`id_attribute_group`,
            \'' . pSQL($attribute_value_separator) . '\',al.`name`,\'' . pSQL($attribute_id_separator) . '\',
            al.`id_attribute` ORDER BY agl.`id_attribute_group` SEPARATOR \'' .
            pSQL($attribute_separator) . '\') as combinations ,a.id_attribute_group
            FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND
            al.`id_lang` = ' . (int)$id_lang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = 
            agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)$id_lang . ')
            WHERE pac.id_product_attribute IN (' . implode(',', $product_attributes) . ')
            GROUP BY pac.id_product_attribute'
        );

        foreach ($lang as $k => $row) {
            $temp = explode(', ', $row['combinations']);
            $temp3 = array();
            foreach ($temp as $key => $value) {
                $temp1 = explode(' - ', $value);
                $temp3[$temp1['0']] = trim($temp1['1']);
            }
            $combinations[$k]['combinations'] = $temp3;
        }

        //Get quantity of each variations
        foreach ($combinations as $key => $row) {
            $cache_key = $row['id_product'] . '_' . $row['id_product_attribute'] . '_quantity';
            if (!Cache::isStored($cache_key)) {
                $result = StockAvailable::getQuantityAvailableByProduct(
                    $row['id_product'],
                    $row['id_product_attribute']
                );
                Cache::store(
                    $cache_key,
                    $result
                );
                $combinations[$key]['quantity'] = $result;
            } else {
                $combinations[$key]['quantity'] = Cache::retrieve($cache_key);
            }
        }
        return $combinations;
    }

    public function manageQuantityThreshold($quantity)
    {
        $quantity_threshold = (int)Configuration::get('CED_WISH_INVENTORY_THRESHOLD');
        if ($quantity < $quantity_threshold) {
            return 0;
        }
        return $quantity;
    }

    public function getProductLevelData($product_id, $id_product_attribute)
    {
        $product_level_data = Db::getInstance()->getValue(
            "SELECT `product_level_data` FROM `" . _DB_PREFIX_ . "cedwish_product` WHERE 
            id_product = '" . (int)$product_id . "' AND id_product_attribute = '" . (int)$id_product_attribute . "'"
        );
        if ($product_level_data) {
            return @json_decode($product_level_data, true);
        } else {
            return array();
        }
    }

    public static function getProductName($id_product, $id_product_attribute = null, $id_lang = null, $id_shop = 1)
    {
        // use the lang in the context if $id_lang is not defined
        if (!$id_lang) {
            $id_lang = (int) Context::getContext()->language->id;
        }

        // creates the query object
        $query = new DbQuery();

        // selects different names, if it is a combination
        if ($id_product_attribute) {
            $query->select(
                'IFNULL(CONCAT(pl.name, \' \', GROUP_CONCAT("" , \' - \', al.name SEPARATOR \', \')),pl.name)
                 as name'
            );
        } else {
            $query->select('DISTINCT pl.name as name');
        }

        // adds joins & where clauses for combinations
        if ($id_product_attribute) {
            $query->from('product_attribute', 'pa');
            // $query->join(Shop::addSqlAssociation('product_attribute', 'pa'));
            $query->innerJoin(
                'product_lang',
                'pl',
                'pl.id_product = pa.id_product AND 
                pl.id_lang = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl')
            );
            $query->leftJoin(
                'product_attribute_combination',
                'pac',
                'pac.id_product_attribute = pa.id_product_attribute'
            );
            $query->leftJoin(
                'attribute',
                'atr',
                'atr.id_attribute = pac.id_attribute'
            );
            $query->leftJoin(
                'attribute_lang',
                'al',
                'al.id_attribute = atr.id_attribute AND al.id_lang = ' . (int) $id_lang
            );
            $query->leftJoin(
                'attribute_shop',
                'ats',
                'ats.id_attribute = atr.id_attribute AND ats.id_shop = ' . (int) $id_shop
            );
            $query->leftJoin(
                'attribute_group_lang',
                'agl',
                'agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = ' . (int) $id_lang
            );
            $query->where(
                'pa.id_product = ' . (int) $id_product . ' 
                AND pa.id_product_attribute = ' . (int) $id_product_attribute
            );
        } else {
            $query->from('product_lang', 'pl');
            $query->where('pl.id_product = ' . (int) $id_product);
            $query->where('pl.id_lang = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl'));
        }
        return Db::getInstance()->getValue($query);
    }

    public static function getWishWarehouseToShipping($warehouse_to_shippgings)
    {
        $result = array();
        if (!empty($warehouse_to_shippgings)) {
            foreach ($warehouse_to_shippgings as $warehouse => $warehouse_to_shipping) {
                $warehouse_to_shipping['shipping_details'] = array_values($warehouse_to_shipping['shipping_details']);
                $temp = array(
                    'warehouse_id' => $warehouse
                );
                $warehouse_to_shipping = array_merge($temp, $warehouse_to_shipping);
                if (isset($warehouse_to_shipping['overrides']) && !empty($warehouse_to_shipping['overrides'])) {
                    $overrides = $warehouse_to_shipping['overrides'];
                    unset($warehouse_to_shipping['overrides']);
                    if (!empty($overrides)) {
                        foreach ($overrides as $override) {
                            $destination = $override['destination'];
                            $destination_country = explode("_", $destination);
                            $destination_country_code = $destination_country['0'];
                            if (Tools::strlen($destination_country_code)==2) {
                                foreach ($warehouse_to_shipping['shipping_details'] as &$warehouse_to_ship) {
                                    $warehouse_to_ship['max_delivery_days']
                                        = (int)$warehouse_to_ship['max_delivery_days'];
                                    if (isset($warehouse_to_ship['overrides']) &&
                                        ($warehouse_to_ship['destination']==$destination_country_code)
                                    ) {
                                        $override['destination'] = $destination_country['1'];
                                        $override['max_delivery_days'] = (int)$override['max_delivery_days'];
                                        $warehouse_to_ship['overrides'][] = $override;
                                    } else {
                                        $warehouse_to_ship['overrides'] = array();
                                    }
                                }
                            }
                        }
                    } else {
                        foreach ($warehouse_to_shipping['shipping_details'] as &$warehouse_to_ship) {
                            $warehouse_to_ship['overrides'] = array();
                        }
                    }
                } else {
                    foreach ($warehouse_to_shipping['shipping_details'] as &$warehouse_to_ship) {
                        $warehouse_to_ship['overrides'] = array();
                    }
                }
                foreach ($warehouse_to_shipping['shipping_details'] as &$warehouse_to_ship) {
                    $warehouse_to_ship['max_delivery_days'] = (int)$warehouse_to_ship['max_delivery_days'];
                }
                $result[] = $warehouse_to_shipping;
            }
            return $result;
        }
    }

    public function getInventories($combination)
    {
        $warehouses = Configuration::get('CED_WISH_SELECTED_WAREHOUSES');
        if ($warehouses) {
            $warehouses = json_decode($warehouses, true);
        }
        $inventories = array();
        foreach ($warehouses as $warehouse) {
            $inventories[] = array(
                'inventory' => $this->manageQuantityThreshold(
                    StockAvailable::getQuantityAvailableByProduct(
                        (int)$combination['id_product'],
                        isset($combination['id_product_attribute'])
                            ?(int)$combination['id_product_attribute']:0
                    )
                ),
                'warehouse_id' => $warehouse
            );
        }
        return $inventories;
    }

    public function getOptions($combination_row)
    {
        $combinations = $combination_row['combinations'];
        $sizes = Configuration::get('CED_WISH_SIZE_MAPPING');
        if ($sizes) {
            $sizes = json_decode($sizes, true);
        }
        $colors = Configuration::get('CED_WISH_COLOR_MAPPING');
        if ($colors) {
            $colors = json_decode($colors, true);
        }
        $options = array();
        if (empty($this->mapped_options)) {
            $query = new DbQuery();
            $query->select("*");
            $query->from('cedwish_color_mapping');
            $already_mapped = Db::getInstance()->executeS(
                $query
            );
            if (!empty($already_mapped)) {
                foreach ($already_mapped as $item) {
                    $this->mapped_options[$item['store_option_id']] = $item;
                }
            }
        }

        if (!empty($combinations)) {
            foreach ($combinations as $id_attribute_group => $value) {
                if (isset($this->mapped_options[$id_attribute_group])
                    && !empty($this->mapped_options[$id_attribute_group])
                ) {
                    $mapped_options = isset($this->mapped_options[$id_attribute_group]['mapped_options'])?
                        json_decode($this->mapped_options[$id_attribute_group]['mapped_options'], true):array();
                    $value = explode("#", $value);
                    if (!empty($mapped_options)) {
                        $is_option_mapping_found = false;
                        foreach ($mapped_options as $mapped_option) {
                            if (isset($mapped_option['store_option_value']) &&
                                isset($value[1]) &&
                                ($mapped_option['store_option_value']==$value[1])) {
                                $value = $mapped_option['cedwish_option_value'];
                                $is_option_mapping_found = true;
                                break;
                            }
                        }
                        if (!$is_option_mapping_found) {
                            $value = $value['0'];
                        }
                    } else {
                        $value = $value['0'];
                    }
                } else {
                    $value = explode("#", $value);
                    $value = $value['0'];
                }
                if ($sizes && in_array($id_attribute_group, $sizes)) {
                    $options[] = array(
                        'name' => 'SIZE',
                        'value' =>   $value
                    );
                } elseif ($colors && in_array($id_attribute_group, $colors)) {
                    $options[] = array(
                        'name' => 'COLOR',
                        'value' =>   $value
                    );
                }
            }
        }
        return $options;
    }

    public function getLogisticsDetails($product, $combination, $product_setting)
    {
        $product_setting['restricted_flags'] = array_filter($product_setting['restricted_flags']);

        if (!empty($combination)) {
        }
        $result =  array(
            'height' => $product->height,
            'length' => $product->depth,
            'width' => $product->width,
            'weight' => $product->weight,
            'restricted_flags' => $product_setting['restricted_flags'],
            'origin_country' => $product_setting['origin_country'],
            'customs_hs_code' => $product_setting['customs_hs_code'],
        );
        if (!empty($product_setting['restricted_flags'])) {
            $result['restricted_flags'] = array_values($product_setting['restricted_flags']);
        }
        return $result;
    }

    public static function getProductStatus($qty, $status)
    {
        if (((int)$qty==0) && Configuration::get('CED_WISH_MAKE_INACTIVE_ITEM')) {
            $status = 'DISABLED';
        }
        if (!$status || !in_array($status, array('ENABLED','DISABLED'))) {
            $status = 'ENABLED';
        }
        return $status;
    }

    public static function getGTIN($product, $id_product_attribute = 0)
    {
        $gtin_field = Configuration::get('CED_WISH_ITEM_GTIN');
        $sku_field_value = false;
        if ($gtin_field && ($gtin_field=='ean13')) {
            $sku_field_value = $product->ean13;
        } elseif ($gtin_field && ($gtin_field=='upc')) {
            $sku_field_value = $product->upc;
        }
        if ($id_product_attribute) {
            $query = new DbQuery();
            $query->select($gtin_field);
            $query->from('product_attribute');
            $query->where(
                'id_product="'.(int)$product->id.'" AND id_product_attribute="'.(int)$id_product_attribute.'"'
            );
            $sku_field_value = Db::getInstance()->getValue($query);
        }
        return $sku_field_value;
    }

    public static function getSKU($product, $id_product_attribute = 0)
    {
        $sku_field = Configuration::get('CED_WISH_ITEM_SKU');
        $sku_field_value = false;
        if ($sku_field && ($sku_field=='id_product')) {
            $sku_field_value = $product->id;
        } elseif ($sku_field && ($sku_field=='ean13')) {
            $sku_field_value = $product->ean13;
        } elseif ($sku_field && ($sku_field=='upc')) {
            $sku_field_value = $product->upc;
        } else {
            $sku_field_value = $product->reference;
        }
        if ($id_product_attribute) {
            $query = new DbQuery();
            $query->select($sku_field);
            $query->from('product_attribute');
            $query->where(
                'id_product="'.(int)$product->id.'" AND id_product_attribute="'.(int)$id_product_attribute.'"'
            );
            $sku_field_value = Db::getInstance()->getValue($query);
        }
        return $sku_field_value;
    }

    public static function getProductBySKU($sku_field_value)
    {
        $sku_field = Configuration::get('CED_WISH_ITEM_SKU');
        if ($sku_field && $sku_field_value) {
            $query = new DbQuery();
            $query->select('id_product');
            $query->from('product');
            $query->where(
                '`'.$sku_field.'`="'.pSQL($sku_field_value).'"'
            );
            $product = Db::getInstance()->getValue($query);
            if ($product) {
                return array(
                    'id_product' => $product,
                    'id_product_attribute' => 0
                );
            } else {
                $query = new DbQuery();
                $query->select('id_product,id_product_attribute');
                $query->from('product_attribute');
                $query->where(
                    '`'.$sku_field.'`="'.pSQL($sku_field_value).'"'
                );
                return  Db::getInstance()->getRow($query);
            }
        }
        return array();
    }

    public function getProductSetting()
    {
        return array(array('COLOR_MAPPING' => array()));
    }
}
