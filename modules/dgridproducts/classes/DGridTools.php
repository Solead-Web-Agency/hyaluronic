<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    SeoSA <885588@bk.ru>
 * @copyright 2012-2017 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class DGridTools
{
    /**
     * Get all attributes for a given language / group
     *
     * @param int $id_lang Language id
     * @param bool $id_attribute_group Attribute group id
     * @return array Attributes
     */
    public static function getAttributes($id_lang, $id_attribute_group = true)
    {
        if (!Combination::isFeatureActive()) {
            return array();
        }

        return Db::getInstance()->executeS(
            'SELECT agl.`name` as `group`, a.*, al.`name`
            FROM `' . _DB_PREFIX_ . 'attribute` a
            ' . Shop::addSqlAssociation('attribute', 'a') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int)$id_lang . ')' .
            ' LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (
            a.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)$id_lang . ')' .
            ($id_attribute_group ? ' WHERE a.`id_attribute_group` = ' . (int)$id_attribute_group : '') .
            ' GROUP BY a.`id_attribute`' .
            ' ORDER BY al.`name` ASC'
        );
    }

    public static function getAttributesSearch($id_lang, $with_shop = true, $p = 1, $with_values = false)
    {
        $attributes = Db::getInstance()->executeS('
        SELECT DISTINCT f.id_attribute, f.*, fl.*
        FROM `' . _DB_PREFIX_ . 'attribute` f
        ' . ($with_shop ? Shop::addSqlAssociation('attribute', 'f') : '') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` fl ON (
        f.`id_attribute` = fl.`id_attribute` AND fl.`id_lang` = ' . (int)$id_lang . ')
        ORDER BY f.`position` ASC LIMIT ' . (($p - 1) * self::LIMIT_ATTRIBUTES) . ', ' . (int)self::LIMIT_ATTRIBUTES);

        if (is_array($attributes) && count($attributes) && $with_values) {
            foreach ($attributes as &$attribute) {
                $attribute['values'] = FeatureValue::getFeatureValuesWithLang(
                    Context::getContext()->language->id,
                    $attribute['id_feature']
                );
            }
        }

        return $attributes;
    }

    public static function getFieldList()
    {
        return array(
            'id_product' => array(
                //'title' => $this->l('ID'),
                'title' => '',
                'width' => 40,
                'remove_onclick' => true,
                'callback' => 'getLinkProductForList',
                'validate' => ''
            ),
            'image' => array(
                'title' => 'Photo',
                'align' => 'left',
                'image' => 'p',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'remove_onclick' => true,
                'validate' => ''
            ),
            'name_category' => array(
                'title' => 'Cat',
                'type' => 'text',
                'search' => true,
                'filter_key' => 'cl!name',
                'remove_onclick' => true,

                'need_edit' => true,
                'table' => 'category_product',
                'field' => 'id_category',
                'lang' => false,
                'validate' => 'category'
            ),
            'name' => array(
                'title' =>'Name',
                'search' => true,
                'filter_key' => 'b!name',
                'type' => 'text',
                'remove_onclick' => true,

                'need_edit' => true,
                'table' => 'product_lang',
                'field' => 'name',
                'lang' => true,
                'validate' => 'string',
                'width' => 150,
                'shop' => true,
            ),
            'short_description' => array(
                'title' => 'Short desc',
                'remove_onclick' => true,
                'type' => 'short_description',
                'need_edit' => false,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'description' => array(
                'title' => 'Desc',
                'remove_onclick' => true,
                'type' => 'description',
                'need_edit' => false,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'reference' => array(
                'title' => 'Ref',
                'align' => 'left',
                'remove_onclick' => true,
                'need_edit' => true,
                'table' => 'product',
                'field' => 'reference',
                'lang' => false,
                'validate' => 'string',
            ),
            'product_supplier_reference' => array(
                'title' => 'Suplier ref',
                'align' => 'left',
                'remove_onclick' => true,
                'need_edit' => true,
                'table' => 'product_supplier',
                'field' => 'product_supplier_reference',
                'lang' => false,
                'validate' => 'string',
            ),
            'ean13' => array(
                'title' => 'Ean13',
                'align' => 'left',
                'remove_onclick' => true,

                'need_edit' => true,
                'table' => 'product',
                'field' => 'ean13',
                'lang' => false,
                'validate' => 'ean13',
                'maxlength' => 13
            ),
            'upc' => array(
                'title' => 'Upc',
                'align' => 'left',
                'remove_onclick' => true,

                'need_edit' => true,
                'table' => 'product',
                'field' => 'upc',
                'lang' => false,
                'validate' => 'upc',
            ),
            'wholesale_price' => array(
                'title' =>'Wholesale price',
                'type' => 'price',
                'align' => 'left',
                'filter_key' => 'sa!price',
                'remove_onclick' => true,

                'need_edit' => true,
                'table' => (!Shop::isFeatureActive() ? 'product,product_shop' : 'product_shop'),
                'field' => 'wholesale_price',
                'lang' => false,
                'validate' => 'price',
                'shop' => true
            ),
            'price' => array(
                'title' => 'Base price',
                'type' => 'price',
                'align' => 'left',
                'filter_key' => 'sa!price',
                'remove_onclick' => true,

                'need_edit' => true,
                'table' => (!Shop::isFeatureActive() ? 'product,product_shop' : 'product_shop'),
                'field' => 'price',
                'lang' => false,
                'validate' => 'price',
                'shop' => true
            ),
            'price_final' => array(
                'title' => 'Final price',
                'type' => 'price',
                'orderby' => false,
                'align' => 'left',
                'filter_key' => 'price_final',
                'remove_onclick' => true,

                'need_edit' => true,
                'table' =>  'product,product_shop',
                'field' => 'price_final',
                'lang' => false,
                'validate' => 'price',
                'shop' => true,
                'search' => false
            ),
            'combinations' => array(
                'title' => 'Comb-s',
                'remove_onclick' => true,
                'type' => 'combinations',
                'need_edit' => false,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'features' => array(
                'title' => 'Fe-s',
                'remove_onclick' => true,
                'type' => 'features',
                'need_edit' => false,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'meta_tags' => array(
                'title' => 'Meta',
                'remove_onclick' => true,
                'type' => 'meta_tags',
                'need_edit' => false,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'specific_price' => array(
                'title' => '%',
                'remove_onclick' => true,
                'type' => 'specific_price',
                'need_edit' => false,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'additional_setting_product' => array(
                'title' => 'More',
                'remove_onclick' => true,
                'type' => 'additional_setting_product',
                'need_edit' => false,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'sav_quantity' => array(
                'title' => 'Qty',
                'type' => 'int',
                'align' => 'left',
                'filter_key' => 'sav!quantity',
                'orderby' => true,
                'remove_onclick' => true,

                'need_edit' => true,
                'table' => 'stock_available',
                'field' => 'quantity',
                'lang' => false,
                'validate' => 'integer',
                'shop' => true
            ),
            'total_price' => array(
                'title' => 'Total price',
                'need_edit' => false,
                'type' => 'price',
                'remove_onclick' => true,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'active' => array(
                'title' => 'Active',
                'active' => 'status',
                //'filter_key' => $alias.'!active',
                'align' => 'left activ',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'remove_onclick' => true,
                'shop' => true,
                'validate' => ''
            ),
            'date_add' => array(
                'title' => 'Date add',
                'align' => 'left',
                'type' => 'date_add',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'remove_onclick' => true,
                'shop' => true,
                'search' => false,
                'validate' => ''
            ),
            'brand' => array(
                'title' => 'Brand',
                'align' => 'left',
                'type' => 'brand',
                'orderby' => true,
                'remove_onclick' => true,
                'table' => 'product',
                'search' => false,
                'validate' => ''
            ),
            'visibility' => array(
                'title' => 'Visibility',
                'align' => 'left',
                'type' => 'visibility',
                'orderby' => true,
                'remove_onclick' => true,
                'table' => 'product',
                'search' => false,
                'validate' => ''
            ),
            'tag' => array(
                'title' =>'Tag',
                'align' => 'left',
                'type' => 'tag',
                'orderby' => false,
                'class' => 'fixed-width-xxl',
                'remove_onclick' => false,
                'table' => 'product',
                'search' => false,
                'validate' => ''
            ),
            'condition' => array(
                'title' => 'Condition',
                'align' => 'left',
                'table' => 'product,product_shop',
                'type' => 'condition',
                'orderby' => true,
                'remove_onclick' => true,
                'search' => false,
                'validate' => ''
            ),
            'tax_rules' => array(
                'title' => 'Tax rules',
                'align' => 'left',
                'type' => 'id_tax_rules_group',
                'orderby' => true,
                'remove_onclick' => true,
                'table' => 'product',
                'search' => false,
                'validate' => ''
            ),
            'available_for_order' => array(
                'title' => 'Available for order',
                'active' => 'status2',
                'align' => 'center order',
                'table' => 'product,product_shop',
                'type' => 'bool',
                'orderby' => true,
                'remove_onclick' => true,
                'search' => true,
                'shop' => true,
                'validate' => ''
            ),
            'low_stock_threshold' => array(
                'title' => 'Low stock threshold',
                'align' => 'left',
                'remove_onclick' => true,
                'need_edit' => true,
                'table' => 'product,product_shop',
                'field' => 'low_stock_threshold',
                'lang' => false,
                'validate' => 'int',
                'filter_key' => 'a!low_stock_threshold'
            ),
            'location' => array(
                'title' => 'Stock location',
                'search' => false,
               // 'filter_key' => 'b!name',
                'type' => 'loc',
                'remove_onclick' => true,
                'need_edit' => true,
                'table' => 'stock_available, product_attribute,product',
                'field' => 'stock_location',
                'validate' => 'string',
                'width' => 150,
                'shop' => true,
                'lang' => false
            ),
            'weight' => array(
                'title' => 'Weight',
                'type' => 'float',
                'align' => 'left',
                'remove_onclick' => true,
                'need_edit' => true,
                'table' => 'product',
                'field' => 'weight',
                'lang' => false,
                'filter_key' => 'a!weight',
                'validate' => ''
            ),
            'unit_price_ratio' => array(
                'title' => 'Unit price product',
                'align' => 'left',
                'remove_onclick' => true,
                'need_edit' => true,
                'table' => 'product,product_shop',
                'field' => 'unit_price_ratio',
                'lang' => false,
                'filter_key' => 'a!unit_price_ratio',
                'validate' => ''
            ),
            'unity' => array(
                'title' => 'Unity',
                'align' => 'left',
                'remove_onclick' => true,
                'need_edit' => true,
                'table' => 'product,product_shop',
                'field' => 'unity',
                'lang' => false,
                'filter_key' => 'a!unity',
                'validate' => ''
            ),
            'mpn' => array(
                'title' => 'MPN',
                'align' => 'left',
                'remove_onclick' => true,
                'need_edit' => true,
                'table' => 'product',
                'field' => 'mpn',
                'lang' => false,
                'filter_key' => 'a!mpn',
                'validate' => ''
            ),
            'date_upd' => array(
                'title' => 'Date upd',
                'need_edit' => false,
//                'type' => 'price',
                'remove_onclick' => true,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'message' => array(
                'title' => 'Message',
                'need_edit' => false,
//                'type' => 'price',
                'remove_onclick' => true,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
            'employee' => array(
                'title' => 'Employee',
                'need_edit' => false,
//                'type' => 'price',
                'remove_onclick' => true,
                'orderby' => false,
                'search' => false,
                'validate' => ''
            ),
        );
    }

    public static function getVisibleColumnList()
    {
        $config = Tools::jsonDecode(Configuration::get('SEOSA_DGRID_COLUMN'));
        $visible = array();
        foreach (self::getFieldList() as $key => $column) {
            if ((is_object($config) && property_exists($config, $key) && $config->{$key})
                || in_array($key, self::getDefaultColumns())) {
                $visible[$key] = $column;
            }
        }
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            unset($visible['location']);
            unset($visible['low_stock_threshold']);
        }
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            unset($visible['mpn']);
        }
        return $visible;
    }

    public static function getVisibleColumnForm()
    {
        $visible = array();
        foreach (self::getFieldList() as $key => $column) {
            if (!in_array($key, self::getDefaultColumns())) {
                $visible[$key] = $column;
            }
        }
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            unset($visible['location']);
            unset($visible['low_stock_threshold']);
        }
        return $visible;
    }

    public static function getDefaultColumns()
    {
        return array(
            'id_product',
            'name'
        );
    }

    public static function fixColumnLabel($field_list, $name)
    {
        $bad = array(
            'combinations' => 'Combinations',
            'active' => 'Product activity'
        );

        return key_exists($name, $bad) ? $bad[$name] : $field_list[$name]['title'];
    }

    public static function getHintForField($name)
    {
        $hints = array(
            'image' => 'Show "Image" column',
            'name_category' => 'Show "Category" column',
            'short_description' => 'Show short descrption column',
            'description' => 'Show descrption column',
            'reference' => 'Show "Reference" column',
            'ean13' => 'Show "Ean13" column',
            'upc' => 'Show "Upc" column',
            'wholesale_price' => 'The wholesale price is the price you paid for the product. Do not include the tax.',
            'price' => 'Show "Base price" column',
            'price_final' => 'Show "Final price" column',
            'combinations' => 'Show "Combinations" column',
            'features' => 'Show "Features" column',
            'meta_tags' => 'Show "Meta tags" column',
            'specific_price' => 'Show "Specific price" column',
            'additional_setting_product' => 'Show "Additional setting product" column',
            'sav_quantity' => 'Show "Quantity" column',
            'total_price' => 'Show "Total price" column',
            'active' => 'Show "Product activity" column',
            'date_add' => 'Show "Product add column',
            'date_available' => 'Available date',
            'brand' => 'Brands',
            'tag' => 'Tags',
            'tax_rules' => 'Tax rules',
            'condition' => 'Condition',
            'available_for_order'=>'available_for_order',
            'location' => 'Stock location',
            'product_supplier_reference' => '',
            'low_stock_threshold' => '',
            'weight' => 'Weight',
            'unit_price_ratio' => 'Unit price ratio',
            'unity' => 'Unity',
            'mpn' => 'Mpn',
            'employee' => 'Employee',
            'message' => 'Message',
            'date_upd' => 'Date update',
            'visibility' => 'Visibility',
        );
        return $hints[$name];
    }
}
