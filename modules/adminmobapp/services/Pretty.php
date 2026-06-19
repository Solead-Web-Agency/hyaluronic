<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Pretty extends ModuleFrontController
{
    public static function getDetail($id_product, $id_language)
    {
        $context = Context::getContext();
        $all_products = array();
        $all_products = Pretty::getAllPro($id_language, $id_product);
        $all_products = Product::getProductsProperties($id_language, $all_products);
        $all_products = Pretty::getExtraFields($all_products, $context);
        $result = Pretty::getProductDetails($all_products, $id_language);
        return $result;
    }

    public static function getAllPro(
        $id_lang,
        $id_product,
        $start = 0,
        $limit = 1,
        $order_by = 'id_product',
        $order_way = 'ASC',
        $id_category = false,
        $only_active = false
    ) {
        $only_active = false;
        $context = Context::getContext();
        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront'))) {
            $front = false;
        }

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }
        if ($order_by == 'id_product' ||
            $order_by == 'price' ||
            $order_by == 'date_add' ||
            $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        } elseif ($order_by == 'position') {
            $order_by_prefix = 'c';
        }
        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }
        $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)' .
                ($id_category ? 'LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c ON (c.`id_product` = p.`id_product`)' : '') . '
                WHERE pl.`id_lang` = ' . (int) $id_lang .
                    ($id_category ? ' AND c.`id_category` = ' . (int) $id_category : '') .
                    ($id_product ? ' AND p.`id_product` = ' . (int) $id_product : '') .
                    ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
                    ($only_active ? ' AND product_shop.`active` = 1' : '') . '
                ORDER BY ' . (isset($order_by_prefix) ? pSQL($order_by_prefix) . '.' : '') . '`' . pSQL($order_by) . '` ' . pSQL($order_way) .
                ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($order_by == 'price') {
            Tools::orderbyPrice($rq, $order_way);
        }
        foreach ($rq as &$row) {
            $row = Product::getTaxesInformations($row);
        }
        $imagesArray = array();
        foreach ($rq as $k => $value) {
            unset($imagesArray);
            $id_product = $value['id_product'];
            $p = new Product($value['id_product']);

            $id_image = Product::getCover($id_product);
            $image = new Image($id_image['id_image']);
            $cover = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
            $rq[$k]['cover_image_url'] = $cover;
            $temp_images = $p->getImages((int) $context->language->id);

            foreach ($temp_images as $key => $we) {
                $key = $key;
                $image = new Image($we['id_image']);
                $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
                $imagesArray[] = $image_url;
            }

            $link = new Link();
            $url = $link->getProductLink($id_product);

            if (empty($imagesArray)) {
                $imagesArray = [];
            }
            $stock_status = StockAvailable::outOfStock($id_product);
            $default_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
            $default_stock = $default_stock;
            if ($stock_status == 2) {
                $stock_status = Configuration::get('PS_ORDER_OUT_OF_STOCK');
            }
            // $p_qty = StockAvailable::getQuantityAvailableByProduct($id_product);
            // if ($p_qty < 1) {
            
            $rq[$k]['order_status'] = $stock_status;
            $rq[$k]['images_link'] = $imagesArray;
            $rq[$k]['url'] = $url;
            $rq[$k]['default_currency_sign'] = $context->currency->sign;
            $rq[$k]['default_currency_iso_code'] = $context->currency->iso_code;
            $rq[$k]['default_currency_name'] = $context->currency->name;
            if (!(int) Tools::getValue('id_customer')) {
                $id_customer = 0;
            } else {
                $id_customer = (int)Tools::getValue('id_customer');
            }
            if ($id_customer) {
                $sql = new DbQuery();
                $sql->select('*');
                $sql->from('fmm_mobilesycn_wish');
                $sql->where('id_customer = '.(int)$id_customer.' AND id_product = '. (int)$id_product);
                $wish_list = Db::getInstance()->executeS($sql);

                if ($wish_list) {
                    $rq[$k]['wish'] = 1;
                } else {
                    $rq[$k]['wish'] = 0;
                }
            } else {
                $rq[$k]['wish'] = 0;
            }
        }

        return $rq;
    }

    public static function getProductDetails($all_products, $id_language)
    {
        if (is_array($all_products) && !empty($all_products)) {
            foreach ($all_products as $k => $value) {
                $combination = array();
                $id_product = $value['id_product'];
                $price = $value['price'];
                $all_products[$k]['display_price'] = Tools::displayPrice($price);

                $price_tax_exc = Tools::displayPrice($value['price_tax_exc']);
                $all_products[$k]['display_price_tax_exc'] = $price_tax_exc;

                $price_without_reduction = Tools::displayPrice($value['price_without_reduction']);
                $all_products[$k]['display_price_without_reduction'] = $price_without_reduction;
                
                //$p = new Product($id_product);
                $attributes_options = array();
                $attributes = Pretty::getAttrGroup($id_product, $id_language);
                if ($attributes['groups'] != null) {
                    $key_index = 0;
                    foreach ($attributes['groups'] as $id_group => $g_k_value) {
                        $item = array();
                        $attributes_options[$key_index]['name'] = $g_k_value['name'];
                        $attributes_options[$key_index]['group_id'] = $id_group;
                        foreach ($g_k_value['attributes'] as $key => $attributes_items) {
                            if ($g_k_value['group_type'] != 'color') {
                                $item[] = array(
                                    'name' => $g_k_value['name'],
                                    'id' => $key,
                                    'value' => $attributes_items
                                );
                            } else {
                                $color_code = '';
                                if (isset($attributes['colors'][$key]['value'])) {
                                    $color_code = $attributes['colors'][$key]['value'];
                                }
                                $item[] = array(
                                    'name' => $g_k_value['name'],
                                    'id' => $key,
                                    'value' => $attributes_items,
                                    'code' => $color_code
                                );
                            }
                        }
                        $attributes_options[$key_index]['values'] = $item;
                        $key_index++;
                    }
                }
                if ($attributes['combinations'] != null) {
                    $key_index = 0;
                    foreach ($attributes['combinations'] as $id_attributs => $attribuuts) {
                        $combination[$key_index]['quantity'] = $attribuuts['quantity'];
                        $combination[$key_index]['price'] = $attribuuts['price'];
                        $combination[$key_index]['id_product_attribute'] = $id_attributs;
                        $combination[$key_index]['display_price'] = Tools::displayPrice($attribuuts['price']);
                        $listing_attr = '';

                        foreach ($attribuuts['attributes'] as $id_attribute) {
                            $listing_attr .= (int) $id_attribute . '_';
                        }
                        $combination[$key_index]['combination_code'] = $listing_attr;

                        
                        $attribute_detail = new Combination($id_attributs);
                        $attributes_n = $attribute_detail->getAttributesName($id_language);
                        $combination_name = '';
                        foreach ($attributes_n as $attribute) {
                            if (!empty($combination_name)) {
                                $combination_name .= ' - ';
                            }
                            $combination_name .= $attribute['name'];
                        }

                        $combination[$key_index]['combination_name'] = $combination_name;
                        $combination_image = $attribute_detail->getWsImages();

                        if (!empty($combination_image) || isset($combination_image[0]['id'])) {
                            
                            $image_id = $combination_image[0]['id'];
                            $image = new Image($image_id);
                            $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().'.jpg';

                            $combination[$key_index]['combination_image'] = $image_url;
                        }
                        $key_index++;
                    }
                }
                $all_products[$k]['combinations'] = $combination;
                $all_products[$k]['options'] = $attributes_options;
            }
        } else {
            $all_products = array();
        }

        return $all_products;
    }

    public static function getAttrGroup($id_product, $id_language)
    {
        $group = array();
        $p = new Product($id_product);
        $color = array();
        $combination = array();
        $groups_attr = $p->getAttributesGroups($id_language);
        $return = Pretty::getProductKeyVal($color, $group, $combination, $groups_attr, $p);
        return $return;
    }

    public static function makeArray($groups, $colors, $combination)
    {
        return array(
            'groups' => $groups,
            'colors' => $colors,
            'combinations' => $combination
        );
    }

    public static function getProductKeyVal($colors, $groups, $combination, $groups_attr, $p)
    {
        if (is_array($groups_attr) && $groups_attr) {
            foreach ($groups_attr as $key) {
                if (!isset($groups[$key['id_attribute_group']])) {
                    $groups[$key['id_attribute_group']] = array(
                        'group_name' => $key['group_name'],
                        'default' => -1,
                        'group_type' => $key['group_type'],
                        'name' => $key['group_name'],
                    );
                }
                if (isset($key['is_color_group'])
                    && $key['is_color_group']
                    && (isset($key['attribute_color']) && $key['attribute_color'])
                    || (file_exists(_PS_COL_IMG_DIR_ . $key['id_attribute'] . '.jpg'))) {
                    $colors[$key['id_attribute']]['name'] = $key['attribute_name'];
                    $colors[$key['id_attribute']]['value'] = $key['attribute_color'];
    
                    if (!isset($colors[$key['id_attribute']]['attributes_quantity'])) {
                        $colors[$key['id_attribute']]['attributes_quantity'] = 0;
                    }
                    $colors[$key['id_attribute']]['attributes_quantity'] =
                    $colors[$key['id_attribute']]['attributes_quantity']+(int) $key['quantity'];
                }
                $combination[$key['id_product_attribute']]['attributes'][] =
                (int) $key['id_attribute'];

                $priceDisplay = Product::getTaxCalculationMethod(0);
                if (!$priceDisplay || $priceDisplay == 2) {
                    $combination_price = $p->getPrice(true, $key['id_product_attribute']);
                } else {
                    $combination_price = $p->getPrice(false, $key['id_product_attribute']);
                }
                
                $g_attr = $key['id_attribute_group'];

                $groups[$g_attr]['attributes'][$key['id_attribute']] = $key['attribute_name'];
                if ($key['default_on'] && $groups[$key['id_attribute_group']]['default'] == -1) {
                    $groups[$key['id_attribute_group']]['default'] = (int) $key['id_attribute'];
                }
                if (!isset($groups[$key['id_attribute_group']]['attributes_quantity'][$key['id_attribute']])) {
                    $groups[$key['id_attribute_group']]['attributes_quantity'][$key['id_attribute']] = 0;
                }
                $attr_idd = $key['id_attribute_group'];
                $groups[$attr_idd]['attributes_quantity'][$key['id_attribute']] =
                $groups[$attr_idd]['attributes_quantity'][$key['id_attribute']]+(int) $key['quantity'];

                $combination[$key['id_product_attribute']]['quantity'] = (int) $key['quantity'];
                $combination[$key['id_product_attribute']]['price'] = $combination_price;
                $combination[$key['id_product_attribute']]['minimal_quantity'] = (int) $key['minimal_quantity'];
            }
        }
        $array = Pretty::makeArray($groups, $colors, $combination);
        return $array;
    }

    public static function getExtraFields($all_products, $context)
    {
        if (!(int) Tools::getValue('id_customer')) {
            $id_customer = 0;
        } else {
            $id_customer = (int)Tools::getValue('id_customer');
        }
        if (empty($all_products)) {
            return $all_products = null;
        }
        $imagesArray = array();

        foreach ($all_products as $k => $value) {
            unset($imagesArray);
            $id_product = $value['id_product'];
            if ($id_customer) {
                $sql = new DbQuery();
                $sql->select('*');
                $sql->from('fmm_mobilesycn_wish');
                $sql->where('id_customer = '.(int)$id_customer.' AND id_product = '. (int)$id_product);
                $wish_list = Db::getInstance()->executeS($sql);

                if ($wish_list) {
                    $all_products[$k]['wish'] = 1;
                } else {
                    $all_products[$k]['wish'] = 0;
                }
            } else {
                $all_products[$k]['wish'] = 0;
            }
            $id_product = $value['id_product'];
            $p = new Product($value['id_product']);

            $id_image = Product::getCover($id_product);
            $image = new Image($id_image['id_image']);
            $cover = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
            $all_products[$k]['cover_image_url'] = $cover;
            $temp_images = $p->getImages((int) $context->language->id);

            foreach ($temp_images as $key => $we) {
                $key = $key;
                $image = new Image($we['id_image']);
                $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
                $imagesArray[] = $image_url;
            }

            $link = new Link();
            $url = $link->getProductLink($id_product);
            if (empty($imagesArray)) {
                $imagesArray = '';
            }
            
            $stock_status = StockAvailable::outOfStock($id_product);
            // $p_qty = StockAvailable::getQuantityAvailableByProduct($id_product);
            // if ($p_qty < 1) {
            $default_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
            $default_stock = $default_stock;
            if ($stock_status == 2) {
                $stock_status = Configuration::get('PS_ORDER_OUT_OF_STOCK');
            }
            $sql_stock = 'SELECT out_of_stock FROM ' . _DB_PREFIX_ . 'stock_available WHERE id_product = ' . (int)$id_product;
            $out_of_stock_status = Db::getInstance()->getValue($sql_stock);

            $all_products[$k]['out_of_stock_status'] = $out_of_stock_status;
            $all_products[$k]['order_status'] = $stock_status;
            $all_products[$k]['images_link'] = $imagesArray;
            $all_products[$k]['url'] = $url;
            $all_products[$k]['default_currency_sign'] = $context->currency->sign;
            $all_products[$k]['default_currency_iso_code'] = $context->currency->iso_code;
            $all_products[$k]['default_currency_name'] = $context->currency->name;
        }
        return $all_products;
    }
}
