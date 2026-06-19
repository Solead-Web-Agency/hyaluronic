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
 * @profile  Ced
 * @package   CedWish
 */

class CedWishProfile extends ObjectModel
{
    public static $definition = array(
        'table' => 'cedwish_profile',
        'primary' => 'id_cedwish_profile',
        'multilang' => false,
        'fields' => array(
            'id_cedwish_profile' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'manufacturers' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'categories' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'price_from' => array(
                'type' => self::TYPE_FLOAT,
                'db_type' => 'float'
            ),
            'price_to' => array(
                'type' => self::TYPE_FLOAT,
                'db_type' => 'float'
            ),
            'suppliers' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'attribute_mapping' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'default_mapping' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'product_setting' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'default_shipping_prices' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'warehouse_to_shippings' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'int'
            ),
            'status' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'int'
            ),
        ),
    );

    public $id_cedwish_profile;
    public $manufacturers;
    public $categories;
    public $name;
    public $price_from;
    public $price_to;
    public $suppliers;
    public $attribute_mapping;
    public $product_setting;
    public $default_mapping;
    public $default_shipping_prices;
    public $warehouse_to_shippings;
    public $id_shop;
    public $status;

    public function __construct($id_cedwish_profile = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id_cedwish_profile, $idLang, $idShop);
    }

    public static function getProfileByProductId($id_product, $active = false)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('cedwish_profile', 'cp');
        $query->innerJoin(
            'cedwish_profile_product',
            'cpp',
            'cpp.id_cedwish_profile = cp.id_cedwish_profile'
        );
        if ($active) {
            $query->where("cpp.id_product='" . (int)$id_product . "' AND status = '1'");
        } else {
            $query->where("cpp.id_product='" . (int)$id_product . "'");
        }
        return Db::getInstance()->getRow($query);
    }

    public function createProfile($profile)
    {
        $mp_profile = new CedWishprofile();
        foreach ($profile as $key => $value) {
            if (!is_array($value)) {
                $mp_profile->{$key} = $value;
            }
        }
        try {
            $mp_profile->add();
        } catch (PrestaShopDatabaseException $e) {
            CedWishHelper::addLog($e->getMessage().$e->getTraceAsString());
        } catch (PrestaShopException $e) {
            CedWishHelper::addLog($e->getMessage().$e->getTraceAsString());
        }
    }

    public function assignProfile($id_profile, $categories, $manufacturers, $price_from, $price_to, $suppliers)
    {
        if (!$id_profile) {
            return false;
        }
        $query = new DbQuery();
        $query->select('DISTINCT(p.id_product)');
        $query->from('product', 'p');
        if (!empty($categories)) {
            if (count($categories)==1) {
                $query->where("id_category_default = '".(int)$categories['0']. "'");
            } else {
                $query->where("id_category_default IN ('" . (implode("', '", $categories)) . "')");
            }
        }
        if (!empty($manufacturers)) {
            if (count($categories)==1) {
                $query->where("id_manufacturer = '".(int)$manufacturers['0']. "'");
            } else {
                $query->where("id_manufacturer IN ('" . (implode("', '", $manufacturers)) . "')");
            }
        }

        if ((float)$price_from) {
            $query->where("price >= '" . (float)$price_from . "'");
        }

        if ((float)$price_to) {
            $query->where("price <= '" . (float)$price_to . "'");
        }

        if (!empty($suppliers)) {
            $query->innerJoin('product_supplier', 'ps', 'ps.id_product = p.id_product');
            if (count($categories)==1) {
                $query->where("id_supplier = '".(int)$suppliers['0']. "'");
            } else {
                $query->where("ps.id_supplier IN ('" . (implode("', '", $suppliers)) . "')");
            }
        }

        try {
            $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            if (!empty($products)) {
                foreach ($products as &$product) {
                    $product['id_cedwish_profile'] = $id_profile;
                    $product['id_shop'] = Context::getContext()->shop->id;
                }

                Db::getInstance()->delete(
                    'cedwish_profile_product',
                    'id_cedwish_profile="'.(int)$id_profile.'"'
                );

                Db::getInstance()->delete(
                    'cedwish_profile_product',
                    'id_product IN ('.$query.')'
                );

                return Db::getInstance()->insert(
                    'cedwish_profile_product',
                    $products
                );
            } else {
                return true;
            }
        } catch (PrestaShopDatabaseException $e) {
            CedWishHelper::addLog($e->getMessage().$e->getTraceAsString());
            return false;
        }
    }

    public static function getProfiles($active = false)
    {
        $query = new DbQuery();
        $query->select('id_cedwish_profile, name');
        $query->from('cedwish_profile');
        if ($active) {
            $query->where("status = '1'");
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    public static function getAttributes()
    {
        return array(
            'name' => array(
                'name' => 'Name',
                'required' => true,
                'type' => 'text',
                'desc' => 'The name of the product.'
            ),
            'description' => array(
                'name' => 'Description',
                'required' => true,
                'type' => 'text',
                'desc' => 'string non-empty The description of the product. "\n" can be used for a new line.'
            ),
            'condition' => array(
                'name' => 'Condition',
                'required' => false,
                'type' => 'select',
                'values' => array(
                    "NEW",
                    "USED",
                    "REFURBISHED"
                ),
                'desc' => 'string non-empty The description of the product. "\n" can be used for a new line.'
            ),
            'video' => array(
                'name' => 'Video',
                'required' => false,
                'type' => 'text',
                'desc' => 'The video of the product.'
            ),
            'gtin' => array(
                'name' => 'GTIN',
                'required' => false,
                'type' => 'text',
                'desc' => 'The Global Trade Item Number of the product.'
            ),
            'tags' => array(
                'name' => 'Tags',
                'required' => false,
                'type' => 'text',
                'desc' => 'The list of tags of the product.'
            ),
            'unit' => array(
                'name' => 'Unit',
                'required' => false,
                'type' => 'select',
                'values' => array(
                    "None",
                    "OUNCE",
                    "POUND",
                    "MILLIGRAM",
                    "GRAM",
                    "KILOGRAM",
                    "FLUID_OUNCE",
                    "PINT",
                    "QUART",
                    "GALLON",
                    "MILLILITER",
                    "CENTILITER",
                    "LITER",
                    "CUBICMETER",
                    "INCH",
                    "FOOT",
                    "YARD",
                    "CENTIMETER",
                    "METER",
                    "SQUARE_FOOT",
                    "SQUARE_METER",
                    "COUNT",
                    "LOAD",
                    "WASH",
                    "ROLL",
                    "POD"
                ),
                'desc' => 'The unit of measurement for the total content of a product. This will be used to 
                display the price per unit, and is the unit of measurement
                that will be used for both the Reference Value and the Quantity Value attributes.'
            ),
            'status' => array(
                'name' => 'Status',
                'required' => false,
                'type' => 'select',
                'values' => array(
                    "ENABLED",
                    "DISABLED"
                ),
                'desc' => 'The status of the product.'
            ),
        );
    }
    public function getDefaultAttributes()
    {
        $defaultAttributes = array();
        $defaultAttributes['name'] = 'system-name';
        $defaultAttributes['gtin'] = 'system-ean13';
        $defaultAttributes['tags'] = 'system-tags';
        $defaultAttributes['description'] = 'system-description';
        return $defaultAttributes;
    }
    public static function getSystemAttributes()
    {
        return array(
            'reference' => 'Reference',
            'name' => 'Name',
            'description' => 'Description',
            'description_short' => 'Short Description',
            'id_manufacturer' => 'Manufacturer',
            'id_tax_rules_group' => 'Tax Rule',
            'price' => 'Price (Tax Excl.)',
            'price_ttc' => 'Price (Tax Incl.)',
            'upc' => 'UPC',
            'ean13' => 'EAN',
            'quantity' => 'Quantity',
            'width' => 'Width',
            'height' => 'Height',
            'depth' => 'Depth',
            'weight' => 'Weight',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'tags' => 'Tags',
        );
    }

    public static function getWarehouses()
    {
        $query = new DbQuery();
        $query->select('id , name');
        $query->from('cedwish_warehouse');
        return Db::getInstance()->executeS($query);
    }
}
