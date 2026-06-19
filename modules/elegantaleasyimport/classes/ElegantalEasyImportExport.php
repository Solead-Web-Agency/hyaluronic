<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is an object model class used to manage export rules
 */
class ElegantalEasyImportExport extends ElegantalEasyImportObjectModel
{

    public $tableName = 'elegantaleasyimport_export';
    public static $definition = array(
        'table' => 'elegantaleasyimport_export',
        'primary' => 'id_elegantaleasyimport_export',
        'multishop' => true,
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'size' => 255, 'validate' => 'isString', 'required' => true),
            'entity' => array('type' => self::TYPE_STRING, 'size' => 25, 'validate' => 'isString', 'required' => true),
            'columns' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'column_override_values' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'file_path' => array('type' => self::TYPE_STRING, 'size' => 255, 'validate' => 'isString', 'required' => true),
            'file_format' => array('type' => self::TYPE_STRING, 'size' => 10, 'validate' => 'isString', 'required' => true),
            'multiple_value_separator' => array('type' => self::TYPE_STRING, 'required' => true),
            'multiple_subcategory_separator' => array('type' => self::TYPE_STRING),
            'currency_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'shop_ids' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'category_ids' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'supplier_ids' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'manufacturer_ids' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'exclude_product_ids' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'product_status' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'price_modifier' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'price_range' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'quantity_range' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'order_by' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'order_direction' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'last_export_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
        ),
    );

    /**
     * Export data columns for products
     * @var array
     */
    public static $columnsProduct = array(
        "product_id" => "Product ID",
        "reference" => "Reference",
        "name" => "Name",
        "active" => "Active (0=No, 1=Yes)",
        "description_short" => "Short Description",
        "description" => "Long Description",
        "meta_title" => "Meta title",
        "meta_description" => "Meta description",
        "meta_keywords" => "Meta keywords",
        "link_rewrite" => "URL rewritten",
        "price_tax_excluded" => "Price Tax Exc",
        "price_tax_included" => "Price Tax Inc",
        "wholesale_price" => "Wholesale price",
        "unit_price" => "Unit price",
        "unity" => "Unity",
        "discount_percent" => "Discount percent",
        "discount_amount" => "Discount amount",
        "tax_rule" => "Tax Rule",
        "quantity" => "Quantity",
        "minimal_quantity" => "Minimal Quantity",
        "manufacturer" => "Manufacturer",
        "suppliers" => "Suppliers",
        "default_category" => "Default Category",
        "categories" => "Categories (x,y,z...)",
        "images" => "Image URLs (x,y,z...)",
        "image_captions" => "Image captions (x,y,z...)",
        "features" => "Features (Name:Value:Position)",
        "accessories" => "Accessories (x,y,z...)",
        "carriers" => "Carriers (x,y,z...)",
        "tags" => "Tags (x,y,z...)",
        "attachments" => "Attachments (x,y,z...)",
        "visibility" => "Visibility",
        "available_for_order" => "Available for order (0=No, 1=Yes)",
        "show_price" => "Show price (0=No, 1=Yes)",
        "on_sale" => "On sale (0=No, 1=Yes)",
        "condition" => "Condition",
        "ean" => "EAN13",
        "upc" => "UPC",
        "isbn" => "ISBN",
        "width" => "Width",
        "height" => "Height",
        "depth" => "Depth",
        "weight" => "Weight",
        "action_when_out_of_stock" => "Text when in stock",
        "text_when_in_stock" => "Text when in stock",
        "text_when_backorder" => "Text when backorder",
        "availability_date" => "Availability date",
        "shop_id" => "Shop ID",
        "shop_name" => "Shop name",
        "custom1" => "Custom Column 1",
        "custom2" => "Custom Column 2",
        "custom3" => "Custom Column 3",
        "custom4" => "Custom Column 4",
        "custom5" => "Custom Column 5",
    );

    /**
     * Export data columns for products
     * @var array
     */
    public static $columnsCombination = array(
        "product_id" => "Product ID",
        "product_reference" => "Product Reference",
        "combination_reference" => "Combination Reference",
        "attribute_names" => "Attribute Names (Name:Type:Position)",
        "attribute_values" => "Attribute Values (Value:Position)",
        "supplier_reference" => "Supplier Reference",
        "supplier_price" => "Supplier Price",
        "isbn" => "ISBN",
        "ean" => "EAN",
        "upc" => "UPC",
        "wholesale_price" => "Wholesale Price",
        "impact_on_price" => "Impact on Price",
        "impact_on_price_per_unit" => "Impact on Price per Unit",
        "ecotax" => "Ecotax",
        "quantity" => "Quantity",
        "minimal_quantity" => "Minimal Quantity",
        "impact_on_weight" => "Impact on Weight",
        "default" => "Default (0/1)",
        "available_date" => "Available Date",
        "images" => "Images (x,y,z…)",
        "shop_id" => "Shop ID",
        "shop_name" => "Shop name",
        "custom1" => "Custom Column 1",
        "custom2" => "Custom Column 2",
        "custom3" => "Custom Column 3",
        "custom4" => "Custom Column 4",
        "custom5" => "Custom Column 5",
    );

    /**
     * List of columns that may have translation
     * @var array
     */
    public static $multilangColumns = array(
        'name',
        'description_short',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'link_rewrite'
    );

    /**
     * File pointer resource to the current export file
     * @var Resource
     */
    private $handle = null;

    /**
     * Instance of PHPExcel class being used for current file (if xls or xlsx file format)
     * @var PHPExcel
     */
    private $phpExcel = null;

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if ($this->last_export_date == '0000-00-00 00:00:00') {
            $this->last_export_date = null;
        }
        if (method_exists('Shop', 'addTableAssociation')) {
            Shop::addTableAssociation($this->tableName, array('type' => 'shop'));
        }
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function getColumns()
    {
        $new_columns = array();
        $languages = Language::getLanguages(false);
        $columns = ($this->entity == 'combination') ? self::$columnsCombination : self::$columnsProduct;
        foreach ($columns as $key => $title) {
            if ($this->entity == 'product' && in_array($key, self::$multilangColumns)) {
                foreach ($languages as $lang) {
                    $new_columns[$key . '_' . $lang['id_lang']] = $title . (count($languages) > 1 ? ' ' . Tools::strtoupper($lang['iso_code']) : "");
                }
            } else {
                $new_columns[$key] = $title;
            }
        }
        return $new_columns;
    }

    public function export()
    {
        $result = array();

        try {
            if ($this->file_format == 'xls' || $this->file_format == 'xlsx') {
                $phpExcel_lib = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendors' . DIRECTORY_SEPARATOR . 'PHPExcel-1.8' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'IOFactory.php';
                if (is_file($phpExcel_lib)) {
                    require_once($phpExcel_lib);
                } else {
                    throw new Exception("PHPExcel library could not be loaded.");
                }
                $this->phpExcel = new PHPExcel();
                $this->phpExcel->setActiveSheetIndex(0);
            } else {
                $this->handle = fopen($this->file_path, 'w+');
                if (!$this->handle) {
                    throw new Exception('Cannot open file for writing: ' . $this->file_path);
                }
            }

            $this->writeHeader();

            // Write main body
            $count = 0;
            if ($this->entity == 'product') {
                $count = $this->exportProducts();
            } elseif ($this->entity == 'combination') {
                $count = $this->exportCombinations();
            } else {
                throw new Exception('Wrong entity.');
            }

            $this->writeFooter();

            if ($this->handle) {
                fclose($this->handle);
            }

            if ($count > 0) {
                $result['success'] = true;
                $result['count'] = $count;

                $this->last_export_date = date('Y-m-d H:i:s');
                $this->update();
            } else {
                file_put_contents($this->file_path, " ");
                $result['success'] = false;
                $result['message'] = 'No records found to export.';
            }
        } catch (Exception $ex) {
            $result['success'] = false;
            $result['message'] = $ex->getMessage();
        }

        return $result;
    }

    protected function exportProducts()
    {
        $count = 0;

        $context = Context::getContext();
        $columns = ElegantalEasyImportTools::unserialize($this->columns);
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Columns not selected.');
        }
        $override = ElegantalEasyImportTools::unserialize($this->column_override_values);

        $languages = Language::getLanguages(false);
        $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
        $shop_ids = ElegantalEasyImportTools::unserialize($this->shop_ids);
        if (empty($shop_ids) || (isset($shop_ids[0]) && empty($shop_ids[0])) || (isset($shop_ids[0]) && $shop_ids[0] == 'all' && !Shop::isFeatureActive())) {
            $shop_ids = array(Configuration::get('PS_SHOP_DEFAULT'));
        } elseif (isset($shop_ids[0]) && $shop_ids[0] == 'all' && Shop::isFeatureActive()) {
            $shop_ids = array();
            $shops = Shop::getShops();
            foreach ($shops as $sh) {
                $shop_ids[] = $sh['id_shop'];
            }
        }
        $defaultCurrency = Currency::getDefaultCurrency();
        $ruleCurrency = new Currency($this->currency_id);
        if (!Validate::isLoadedObject($ruleCurrency)) {
            $ruleCurrency = null;
        }
        $exclude_product_ids = $this->exclude_product_ids ? explode(',', $this->exclude_product_ids) : null;
        $category_ids = $this->category_ids ? ElegantalEasyImportTools::unserialize($this->category_ids) : null;
        $supplier_ids = ElegantalEasyImportTools::unserialize($this->supplier_ids);
        if (is_array($supplier_ids) && in_array('all', $supplier_ids)) {
            $supplier_ids = null;
        }
        $manufacturer_ids = ElegantalEasyImportTools::unserialize($this->manufacturer_ids);
        if (is_array($manufacturer_ids) && in_array('all', $manufacturer_ids)) {
            $manufacturer_ids = null;
        }
        $price_from = null;
        $price_to = null;
        if ($this->price_range) {
            if (preg_match("/^([0-9]+(\.[0-9]{1,})?)-([0-9]+(\.[0-9]{1,})?)$/", str_replace(" ", "", $this->price_range), $match)) {
                if ($match[1] < $match[3]) {
                    $price_from = $match[1];
                    $price_to = $match[3];
                }
            }
        }
        $quantity_from = null;
        $quantity_to = null;
        if ($this->quantity_range) {
            if (preg_match("/^(\d+)-(\d+)$/", str_replace(" ", "", $this->quantity_range), $match)) {
                if ($match[1] < $match[2]) {
                    $quantity_from = $match[1];
                    $quantity_to = $match[2];
                }
            }
        }

        $already_loaded_shop_ids = array();

        foreach ($shop_ids as $id_shop) {
            $sql = "SELECT DISTINCT p.`id_product` FROM `" . _DB_PREFIX_ . "product` p 
                INNER JOIN `" . _DB_PREFIX_ . "product_shop` psh ON (psh.`id_product` = p.`id_product`) 
                INNER JOIN `" . _DB_PREFIX_ . "product_lang` pl ON (pl.`id_product` = p.`id_product`) ";
            if ($category_ids) {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "category_product` cp ON (cp.`id_product` = p.`id_product`) ";
            }
            if ($supplier_ids) {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            if (Configuration::get("PS_STOCK_MANAGEMENT") && (!is_null($quantity_from) || !is_null($quantity_to))) {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "stock_available` sa ON (sa.`id_product` = p.`id_product` AND sa.`id_shop` = " . (int) $id_shop . ") ";
            }
            $sql .= "WHERE psh.`id_shop` = " . (int) $id_shop . " AND pl.`id_lang` = " . (int) $id_lang_default . " ";
            $sql .= count($already_loaded_shop_ids) > 0 ? "AND p.`id_product` NOT IN (SELECT `id_product` FROM `" . _DB_PREFIX_ . "product_shop` WHERE `id_shop` IN (" . implode(',', array_map('intval', $already_loaded_shop_ids)) . ")) " : "";
            $sql .= $exclude_product_ids ? "AND p.`id_product` NOT IN (" . implode(',', array_map('intval', $exclude_product_ids)) . ") " : "";
            $sql .= ($this->product_status == 1 || $this->product_status == 0) ? "AND psh.`active` = " . (int) $this->product_status . " " : "";
            $sql .= !is_null($price_from) ? "AND psh.`price` >= " . (float) $price_from . " " : "";
            $sql .= !is_null($price_to) ? "AND psh.`price` <= " . (float) $price_to . " " : "";
            $sql .= $category_ids ? " AND cp.`id_category` IN (" . implode(',', array_map('intval', $category_ids)) . ") " : "";
            $sql .= $supplier_ids ? " AND ps.`id_supplier` IN (" . implode(',', array_map('intval', $supplier_ids)) . ") " : "";
            $sql .= $manufacturer_ids ? " AND p.`id_manufacturer` IN (" . implode(',', array_map('intval', $manufacturer_ids)) . ") " : "";
            $sql .= (Configuration::get("PS_STOCK_MANAGEMENT") && !is_null($quantity_from)) ? "AND sa.`quantity` >= " . (int) $quantity_from . " " : "";
            $sql .= (Configuration::get("PS_STOCK_MANAGEMENT") && !is_null($quantity_to)) ? "AND sa.`quantity` <= " . (int) $quantity_to . " " : "";
            $sql .= "GROUP BY p.`id_product`, pl.`name` ORDER BY " . pSQL($this->order_by) . " " . (($this->order_by != 'RAND()') ? pSQL($this->order_direction) : '');

            $products = Db::getInstance()->executeS($sql);

            $already_loaded_shop_ids[] = $id_shop;

            if (!$products || !is_array($products)) {
                continue;
            }
            foreach ($products as $p) {
                $product = new Product($p['id_product'], true);
                if (!Validate::isLoadedObject($product)) {
                    continue;
                }
                $data = array();
                foreach ($columns as $key => $enabled) {
                    if ($enabled != 1) {
                        continue;
                    }
                    if ($override[$key] != "") {
                        $data[] = $override[$key];
                        continue;
                    }
                    $value = "";
                    switch ($key) {
                        case 'product_id':
                            $value = $product->id;
                            break;
                        case 'reference':
                            $value = $product->reference;
                            break;
                        case 'active':
                            $value = $product->active;
                            break;
                        case 'price_tax_excluded':
                        case 'price_tax_included':
                            $value = $product->base_price;
                            if ($this->currency_id && $this->currency_id != $defaultCurrency->id && $ruleCurrency) {
                                $value = Tools::convertPriceFull($value, $defaultCurrency, $ruleCurrency);
                            }
                            if ($key == 'price_tax_included') {
                                $value = $value * (1 + ($product->tax_rate / 100));
                            }
                            $value = ElegantalEasyImportTools::getModifiedPriceByFormula($value, $this->price_modifier);
                            $value = round($value, 6);
                            break;
                        case 'wholesale_price':
                            $value = $product->wholesale_price;
                            if ($this->currency_id && $this->currency_id != $defaultCurrency->id && $ruleCurrency) {
                                $value = Tools::convertPriceFull($value, $defaultCurrency, $ruleCurrency);
                            }
                            $value = round($value, 6);
                            break;
                        case 'unit_price':
                            $value = ($product->unit_price_ratio != 0 ? $product->base_price / $product->unit_price_ratio : 0);
                            if ($this->currency_id && $this->currency_id != $defaultCurrency->id && $ruleCurrency) {
                                $value = Tools::convertPriceFull($value, $defaultCurrency, $ruleCurrency);
                            }
                            $value = round($value, 6);
                            break;
                        case 'unity':
                            $value = $product->unity;
                            break;
                        case 'discount_percent':
                        case 'discount_amount':
                            $discount = (float) Product::getPriceStatic($product->id, true, null, 6, null, true, true, 1);
                            if ($discount > 0 && $product->base_price > 0) {
                                if ($key == 'discount_percent') {
                                    $value = round(($discount * 100) / $product->base_price) . '%';
                                } else {
                                    $value = $discount;
                                }
                            }
                            break;
                        case 'tax_rule':
                            if ($product->id_tax_rules_group) {
                                $taxRule = new TaxRulesGroup($product->id_tax_rules_group);
                                $value = Validate::isLoadedObject($taxRule) ? $taxRule->name : "";
                            }
                            break;
                        case 'quantity':
                            $value = (int) StockAvailable::getQuantityAvailableByProduct($product->id);
                            break;
                        case 'minimal_quantity':
                            $value = $product->minimal_quantity;
                            break;
                        case 'manufacturer':
                            if ($product->id_manufacturer) {
                                $manufacturer = new Manufacturer($product->id_manufacturer);
                                $value = Validate::isLoadedObject($manufacturer) ? $manufacturer->name : "";
                            }
                            break;
                        case 'suppliers':
                            $sql = "SELECT s.`id_supplier`, s.`name` 
                                FROM `" . _DB_PREFIX_ . "supplier` s 
                                INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON ps.`id_supplier` = s.`id_supplier` 
                                WHERE ps.`id_product` = " . (int) $product->id;
                            $product_suppliers = Db::getInstance()->executeS($sql);
                            if ($product_suppliers) {
                                foreach ($product_suppliers as $product_supplier) {
                                    $value .= $value ? $this->multiple_value_separator : "";
                                    $value .= $product_supplier['name'];
                                }
                            }
                            break;
                        case 'default_category':
                            $category = new Category($product->id_category_default, $id_lang_default);
                            $value = Validate::isLoadedObject($category) ? $category->name : "";
                            break;
                        case 'categories':
                            $id_categories = $product->getCategories();
                            if ($id_categories) {
                                foreach ($id_categories as $id_category) {
                                    $category = new Category($id_category, $id_lang_default);

                                    $parents = $category->getParentsCategories($id_lang_default);
                                    if ($parents) {
                                        $category_path = "";
                                        $parents = array_reverse($parents);
                                        foreach ($parents as $parent) {
                                            $category_path .= $category_path ? $this->multiple_subcategory_separator : "";
                                            $category_path .= $parent['name'];
                                        }
                                        $value .= $value ? $this->multiple_value_separator : "";
                                        $value .= $category_path;
                                    }
                                }
                            }
                            break;
                        case 'images':
                        case 'image_captions':
                            $image_captions = "";
                            $cover_image = Image::getCover($product->id);
                            if ($cover_image) {
                                $value = $context->link->getImageLink($product->link_rewrite[$id_lang_default], $product->id . '-' . $cover_image['id_image']);
                            }
                            $product_images = Image::getImages($id_lang_default, $product->id);
                            if ($product_images) {
                                foreach ($product_images as $image) {
                                    if (!$image['cover']) {
                                        $value .= $value ? $this->multiple_value_separator : "";
                                        $value .= $context->link->getImageLink($product->link_rewrite[$id_lang_default], $product->id . '-' . $image['id_image']);
                                        $image_captions .= $image_captions ? $this->multiple_value_separator : "";
                                        $image_captions .= $image['legend'];
                                    } else {
                                        $image_captions = $image_captions ? $image['legend'] . $this->multiple_value_separator . $image_captions : $image['legend'];
                                    }
                                }
                            }
                            if ($key == 'image_captions') {
                                $value = $image_captions;
                            }
                            break;
                        case 'features':
                            $features = $product->getFeatures();
                            if ($features) {
                                foreach ($features as $feature) {
                                    $featureObj = new Feature($feature['id_feature'], $id_lang_default);
                                    if ($featureObj) {
                                        $featureValue = new FeatureValue($feature['id_feature_value'], $id_lang_default);
                                        if ($featureValue) {
                                            $value .= $value ? $this->multiple_value_separator : "";
                                            $value .= $featureObj->name . ':' . $featureValue->value;
                                        }
                                    }
                                }
                            }
                            break;
                        case 'accessories':
                            $accessories = Product::getAccessoriesLight($id_lang_default, $product->id);
                            if ($accessories) {
                                foreach ($accessories as $accessory) {
                                    $value .= $value ? $this->multiple_value_separator : "";
                                    $value .= $accessory['reference'];
                                }
                            }
                            break;
                        case 'carriers':
                            $carriers = $product->getCarriers();
                            if ($carriers) {
                                foreach ($carriers as $carrier) {
                                    $value .= $value ? $this->multiple_value_separator : "";
                                    $value .= $carrier['name'];
                                }
                            }
                            break;
                        case 'tags':
                            if (isset($product->tags[$id_lang_default]) && $product->tags[$id_lang_default]) {
                                foreach ($product->tags[$id_lang_default] as $tag) {
                                    $value .= $value ? $this->multiple_value_separator : "";
                                    $value .= $tag;
                                }
                            }
                            break;
                        case 'attachments':
                            $attachments = $product->getAttachments($id_lang_default);
                            if ($attachments) {
                                foreach ($attachments as $attachment) {
                                    $value .= $value ? $this->multiple_value_separator : "";
                                    $value .= $context->link->getPageLink('attachment', null, $id_lang_default, array('id_attachment' => $attachment['id_attachment']), false, $id_shop, false);
                                }
                            }
                            break;
                        case 'visibility':
                            $value = $product->visibility;
                            break;
                        case 'available_for_order':
                            $value = $product->available_for_order;
                            break;
                        case 'show_price':
                            $value = $product->show_price;
                            break;
                        case 'on_sale':
                            $value = $product->on_sale;
                            break;
                        case 'condition':
                            $value = $product->condition;
                            break;
                        case 'ean':
                            $value = $product->ean13;
                            break;
                        case 'upc':
                            $value = $product->upc;
                            break;
                        case 'isbn':
                            $value = property_exists($product, 'isbn') ? $product->isbn : "";
                            break;
                        case 'width':
                            $value = $product->width;
                            break;
                        case 'height':
                            $value = $product->height;
                            break;
                        case 'depth':
                            $value = $product->depth;
                            break;
                        case 'weight':
                            $value = $product->weight;
                            break;
                        case 'action_when_out_of_stock':
                            $value = StockAvailable::outOfStock($product->id, $id_shop);
                            break;
                        case 'text_when_in_stock':
                            $value = $product->available_now[$id_lang_default];
                            break;
                        case 'text_when_backorder':
                            $value = $product->available_later[$id_lang_default];
                            break;
                        case 'availability_date':
                            $value = ($product->available_date && $product->available_date != "0000-00-00 00:00:00" && $product->available_date != "0000-00-00") ? $product->available_date : "";
                            break;
                        case 'shop_id':
                            $value = $id_shop;
                            break;
                        case 'shop_name':
                            $shop = new Shop($id_shop);
                            $value = $shop->name;
                            break;
                        case 'custom1':
                        case 'custom2':
                        case 'custom3':
                        case 'custom4':
                        case 'custom5':
                            break;
                        default:
                            foreach (self::$multilangColumns as $mcolumn) {
                                foreach ($languages as $mlang) {
                                    $mkey = $mcolumn . '_' . $mlang['id_lang'];
                                    if ($mkey == $key) {
                                        if (isset($product->{$mcolumn}[$mlang['id_lang']])) {
                                            $value = $product->{$mcolumn}[$mlang['id_lang']];
                                        }
                                        break 2;
                                    }
                                }
                            }
                            break;
                    }
                    $data[] = $value;
                }
                if ($this->writeBody($data) !== false) {
                    $count++;
                }
            }
        }

        return $count;
    }

    protected function exportCombinations()
    {
        $count = 0;

        $context = Context::getContext();
        $columns = ElegantalEasyImportTools::unserialize($this->columns);
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Columns not selected.');
        }
        $override = ElegantalEasyImportTools::unserialize($this->column_override_values);

        $languages = Language::getLanguages(false);
        $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
        $shop_ids = ElegantalEasyImportTools::unserialize($this->shop_ids);
        if (empty($shop_ids) || (isset($shop_ids[0]) && empty($shop_ids[0])) || (isset($shop_ids[0]) && $shop_ids[0] == 'all' && !Shop::isFeatureActive())) {
            $shop_ids = array(Configuration::get('PS_SHOP_DEFAULT'));
        } elseif (isset($shop_ids[0]) && $shop_ids[0] == 'all' && Shop::isFeatureActive()) {
            $shop_ids = array();
            $shops = Shop::getShops();
            foreach ($shops as $sh) {
                $shop_ids[] = $sh['id_shop'];
            }
        }
        $exclude_product_ids = $this->exclude_product_ids ? explode(',', $this->exclude_product_ids) : null;
        $category_ids = $this->category_ids ? ElegantalEasyImportTools::unserialize($this->category_ids) : null;
        $supplier_ids = ElegantalEasyImportTools::unserialize($this->supplier_ids);
        if (is_array($supplier_ids) && in_array('all', $supplier_ids)) {
            $supplier_ids = null;
        }
        $manufacturer_ids = ElegantalEasyImportTools::unserialize($this->manufacturer_ids);
        if (is_array($manufacturer_ids) && in_array('all', $manufacturer_ids)) {
            $manufacturer_ids = null;
        }
        $price_from = null;
        $price_to = null;
        if ($this->price_range) {
            if (preg_match("/^([0-9]+(\.[0-9]{1,})?)-([0-9]+(\.[0-9]{1,})?)$/", str_replace(" ", "", $this->price_range), $match)) {
                if ($match[1] < $match[3]) {
                    $price_from = $match[1];
                    $price_to = $match[3];
                }
            }
        }
        $quantity_from = null;
        $quantity_to = null;
        if ($this->quantity_range) {
            if (preg_match("/^(\d+)-(\d+)$/", str_replace(" ", "", $this->quantity_range), $match)) {
                if ($match[1] < $match[2]) {
                    $quantity_from = $match[1];
                    $quantity_to = $match[2];
                }
            }
        }

        $already_loaded_shop_ids = array();

        foreach ($shop_ids as $id_shop) {
            $sql = "SELECT pa.*, pash.*, p.`reference` AS `product_reference`, ps.supplier_reference, ps.supplier_price, pai.images, GROUP_CONCAT(agl.`name` ORDER BY pac.`id_attribute` ASC SEPARATOR '" . pSQL($this->multiple_value_separator) . "') AS `Attributes`, GROUP_CONCAT(al.`name` ORDER BY pac.`id_attribute` ASC SEPARATOR '" . pSQL($this->multiple_value_separator) . "') AS `Values` 
                FROM `" . _DB_PREFIX_ . "product_attribute` pa 
                INNER JOIN `" . _DB_PREFIX_ . "product_attribute_shop` pash ON pash.`id_product_attribute` = pa.`id_product_attribute` AND pash.`id_shop` = " . (int) $id_shop . "  
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON p.`id_product` = pa.`id_product` 
                INNER JOIN `" . _DB_PREFIX_ . "product_shop` psh ON psh.`id_product` = p.`id_product` AND psh.`id_shop` = " . (int) $id_shop . "  
                LEFT JOIN (SELECT `id_product_attribute`, GROUP_CONCAT(`product_supplier_reference` ORDER BY `id_supplier` ASC SEPARATOR '" . pSQL($this->multiple_value_separator) . "') AS `supplier_reference`, GROUP_CONCAT(`product_supplier_price_te` ORDER BY `id_supplier` ASC SEPARATOR '" . pSQL($this->multiple_value_separator) . "') AS `supplier_price` FROM `" . _DB_PREFIX_ . "product_supplier` GROUP BY `id_product_attribute`) ps ON ps.`id_product_attribute` = pa.`id_product_attribute` 
                LEFT JOIN (SELECT `id_product_attribute`, GROUP_CONCAT(`id_image` SEPARATOR '" . pSQL($this->multiple_value_separator) . "') AS `images` FROM `" . _DB_PREFIX_ . "product_attribute_image` GROUP BY `id_product_attribute`) pai ON pai.`id_product_attribute` = pa.`id_product_attribute` 
                LEFT JOIN `" . _DB_PREFIX_ . "product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute` 
                LEFT JOIN `" . _DB_PREFIX_ . "attribute` a ON a.`id_attribute` = pac.`id_attribute` 
                LEFT JOIN `" . _DB_PREFIX_ . "attribute_lang` al ON al.`id_attribute` = a.`id_attribute` AND al.`id_lang` = " . (int) $id_lang_default . "  
                LEFT JOIN `" . _DB_PREFIX_ . "attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group` 
                LEFT JOIN `" . _DB_PREFIX_ . "attribute_group_lang` agl ON agl.`id_attribute_group` = ag.`id_attribute_group` AND agl.`id_lang` = " . (int) $id_lang_default . " ";
            if (Configuration::get("PS_STOCK_MANAGEMENT") && (!is_null($quantity_from) || !is_null($quantity_to))) {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "stock_available` sa ON (sa.`id_product_attribute` = pa.`id_product_attribute` AND sa.`id_product` = pa.`id_product` AND sa.`id_shop` = " . (int) $id_shop . ") ";
            }
            $sql .= "WHERE pash.`id_shop` = " . (int) $id_shop . " AND psh.`id_shop` = " . (int) $id_shop . " ";
            $sql .= count($already_loaded_shop_ids) > 0 ? "AND p.`id_product` NOT IN (SELECT `id_product` FROM `" . _DB_PREFIX_ . "product_shop` WHERE `id_shop` IN (" . implode(',', array_map('intval', $already_loaded_shop_ids)) . ")) " : "";
            $sql .= $exclude_product_ids ? "AND p.`id_product` NOT IN (" . implode(',', array_map('intval', $exclude_product_ids)) . ") " : "";
            $sql .= ($this->product_status == 1 || $this->product_status == 0) ? "AND psh.`active` = " . (int) $this->product_status . " " : "";
            $sql .= !is_null($price_from) ? "AND psh.`price` >= " . (float) $price_from . " " : "";
            $sql .= !is_null($price_to) ? "AND psh.`price` <= " . (float) $price_to . " " : "";
            $sql .= $category_ids ? "AND p.`id_product` IN (SELECT `id_product` FROM `" . _DB_PREFIX_ . "category_product` WHERE `id_category` IN (" . implode(',', array_map('intval', $category_ids)) . ")) " : "";
            $sql .= $supplier_ids ? "AND p.`id_product` IN (SELECT `id_product` FROM `" . _DB_PREFIX_ . "product_supplier` WHERE `id_supplier` IN (" . implode(',', array_map('intval', $supplier_ids)) . ")) " : "";
            $sql .= $manufacturer_ids ? " AND p.`id_manufacturer` IN (" . implode(',', array_map('intval', $manufacturer_ids)) . ") " : "";
            $sql .= (Configuration::get("PS_STOCK_MANAGEMENT") && !is_null($quantity_from)) ? "AND sa.`quantity` >= " . (int) $quantity_from . " " : "";
            $sql .= (Configuration::get("PS_STOCK_MANAGEMENT") && !is_null($quantity_to)) ? "AND sa.`quantity` <= " . (int) $quantity_to . " " : "";
            $sql .= "GROUP BY pa.`id_product_attribute` ORDER BY pa.`id_product` ASC, pa.`id_product_attribute` ASC";

            $combinations = Db::getInstance()->executeS($sql);

            $already_loaded_shop_ids[] = $id_shop;

            if (!$combinations || !is_array($combinations)) {
                continue;
            }
            foreach ($combinations as $combination) {
                $data = array();
                foreach ($columns as $key => $enabled) {
                    if ($enabled != 1) {
                        continue;
                    }
                    if ($override[$key] != "") {
                        $data[] = $override[$key];
                        continue;
                    }
                    $value = "";
                    switch ($key) {
                        case 'product_id':
                            $value = $combination['id_product'];
                            break;
                        case 'product_reference':
                            $value = $combination['product_reference'];
                            break;
                        case 'combination_reference':
                            $value = $combination['reference'];
                            break;
                        case 'attribute_names':
                            $value = $combination['Attributes'];
                            break;
                        case 'attribute_values':
                            $value = $combination['Values'];
                            break;
                        case 'supplier_reference':
                            $value = $combination['supplier_reference'];
                            break;
                        case 'supplier_price':
                            $value = $combination['supplier_price'];
                            break;
                        case 'ean':
                            $value = $combination['ean13'];
                            break;
                        case 'upc':
                            $value = $combination['upc'];
                            break;
                        case 'isbn':
                            $value = isset($combination['isbn']) ? $combination['isbn'] : "";
                            break;
                        case 'wholesale_price':
                            $value = $combination['wholesale_price'];
                            break;
                        case 'impact_on_price':
                            $value = $combination['price'];
                            break;
                        case 'impact_on_price_per_unit':
                            $value = $combination['unit_price_impact'];
                            break;
                        case 'ecotax':
                            $value = $combination['ecotax'];
                            break;
                        case 'quantity':
                            $value = (int) StockAvailableCore::getQuantityAvailableByProduct($combination['id_product'], $combination['id_product_attribute'], $id_shop);
                            break;
                        case 'minimal_quantity':
                            $value = $combination['minimal_quantity'];
                            break;
                        case 'impact_on_weight':
                            $value = $combination['weight'];
                            break;
                        case 'default':
                            $value = (int) $combination['default_on'];
                            break;
                        case 'available_date':
                            $value = ($combination['available_date'] && $combination['available_date'] != "0000-00-00 00:00:00" && $combination['available_date'] != "0000-00-00") ? $combination['available_date'] : "";
                            break;
                        case 'images':
                            $value = "";
                            if ($combination['images']) {
                                $combination_images = explode($this->multiple_value_separator, $combination['images']);
                                foreach ($combination_images as $combination_image) {
                                    $value .= $value ? $this->multiple_value_separator : "";
                                    $value .= $context->link->getImageLink($combination['product_reference'], $combination['product_id'] . '-' . $combination_image['id_image']);
                                }
                            }
                            break;
                        case 'shop_id':
                            $value = $id_shop;
                            break;
                        case 'shop_name':
                            $shop = new Shop($id_shop);
                            $value = $shop->name;
                            break;
                        case 'custom1':
                        case 'custom2':
                        case 'custom3':
                        case 'custom4':
                        case 'custom5':
                            break;
                        default:
                            break;
                    }
                    $data[] = $value;
                }
                if ($this->writeBody($data) !== false) {
                    $count++;
                }
            }
        }

        return $count;
    }

    protected function writeHeader()
    {
        switch ($this->file_format) {
            case 'csv':
                $this->writeCsvHeader();
                break;
            case 'xml':
                $this->writeXmlHeader();
                break;
            case 'json':
                $this->writeJsonHeader();
                break;
            case 'xls':
            case 'xlsx':
                $this->writeExcelHeader();
                break;
            default:
                break;
        }
    }

    protected function writeCsvHeader()
    {
        $csv_header = array();
        $columns = ElegantalEasyImportTools::unserialize($this->columns);
        $columns_with_title = $this->getColumns();
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Columns not selected.');
        }
        foreach ($columns as $key => $enabled) {
            if ($enabled != 1) {
                continue;
            }
            $csv_header[] = isset($columns_with_title[$key]) ? $columns_with_title[$key] : $key;
        }
        $this->writeCsvBody($csv_header);
    }

    protected function writeXmlHeader()
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString('    ');
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->writeRaw((($this->entity == 'combination') ? '<COMBINATIONS>' : '<PRODUCTS>') . PHP_EOL);
        fwrite($this->handle, $xmlWriter->flush(true));
    }

    protected function writeJsonHeader()
    {
        fwrite($this->handle, "[");
    }

    protected function writeExcelHeader()
    {
        $col = 0;
        $columns = ElegantalEasyImportTools::unserialize($this->columns);
        $columns_with_title = $this->getColumns();
        if (empty($columns) || !is_array($columns)) {
            throw new Exception('Columns not selected.');
        }
        foreach ($columns as $key => $enabled) {
            if ($enabled != 1) {
                continue;
            }
            $title = isset($columns_with_title[$key]) ? $columns_with_title[$key] : $key;
            $this->phpExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $title);
            $col++;
        }
    }

    protected function writeBody($data)
    {
        if (empty($data) || !is_array($data)) {
            throw new Exception('No data to export.');
        }

        $result = false;
        switch ($this->file_format) {
            case 'csv':
                $result = $this->writeCsvBody($data);
                break;
            case 'xml':
                $result = $this->writeXmlBody($data);
                break;
            case 'json':
                $result = $this->writeJsonBody($data);
                break;
            case 'xls':
            case 'xlsx':
                $result = $this->writeExcelBody($data);
                break;
            case 'txt':
                $result = $this->writeTxtBody($data);
                break;
            default:
                break;
        }

        return $result;
    }

    protected function writeCsvBody($data)
    {
        return fputcsv($this->handle, $data, ',', '"');
    }

    protected function writeXmlBody($data)
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString('    ');

        $current = 0;

        $xmlWriter->startElement('PRODUCT');
        $columns = ElegantalEasyImportTools::unserialize($this->columns);
        foreach ($columns as $key => $enabled) {
            if ($enabled != 1) {
                continue;
            }
            $value = isset($data[$current]) ? $data[$current] : "";
            if (empty($value) || !preg_match("/[^A-Za-z0-9\%\@\.\,\:\;\|\-\_\(\)\s]/", $value)) {
                $xmlWriter->writeElement(Tools::strtoupper($key), $value);
            } else {
                $xmlWriter->startElement(Tools::strtoupper($key));
                $xmlWriter->writeCData($value);
                $xmlWriter->endElement();
            }
            $current++;
        }
        $xmlWriter->endElement();

        return fwrite($this->handle, $xmlWriter->flush(true));
    }

    protected function writeJsonBody($data)
    {
        $current = 0;
        $product_array = array();
        $columns = ElegantalEasyImportTools::unserialize($this->columns);
        foreach ($columns as $key => $enabled) {
            if ($enabled != 1) {
                continue;
            }
            $product_array[$key] = isset($data[$current]) ? $data[$current] : "";
            $current++;
        }

        return fwrite($this->handle, json_encode($product_array, JSON_PRETTY_PRINT) . "," . PHP_EOL);
    }

    protected function writeExcelBody($data)
    {
        $col = 0;
        $row = $this->phpExcel->getActiveSheet()->getHighestRow() + 1;
        foreach ($data as $value) {
            $this->phpExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
            $col++;
        }
        return true;
    }

    protected function writeTxtBody($data)
    {
        return fputcsv($this->handle, $data, '|', '"');
    }

    protected function writeFooter()
    {
        switch ($this->file_format) {
            case 'xml':
                $this->writeXmlFooter();
                break;
            case 'json':
                $this->writeJsonFooter();
                break;
            case 'xls':
            case 'xlsx':
                $this->writeExcelFooter();
                break;
            default:
                break;
        }
    }

    protected function writeXmlFooter()
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString('    ');
        $xmlWriter->writeRaw((($this->entity == 'combination') ? '</COMBINATIONS>' : '</PRODUCTS>'));
        fwrite($this->handle, $xmlWriter->flush(true));
    }

    protected function writeJsonFooter()
    {
        // Remove last comma and new line
        $stat = fstat($this->handle);
        ftruncate($this->handle, $stat['size'] - 2);

        // Move file pointer to end
        fseek($this->handle, 0, SEEK_END);

        // Write closing bracket
        fwrite($this->handle, "]");
    }

    protected function writeExcelFooter()
    {
        $writerType = $this->file_format == 'xlsx' ? 'Excel2007' : 'Excel5';
        $writer = PHPExcel_IOFactory::createWriter($this->phpExcel, $writerType);
        $writer->save($this->file_path);
    }
}
