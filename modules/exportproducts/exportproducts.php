<?php

if (!defined('_PS_VERSION_')){
  exit;
}

class exportProducts extends Module{

  private $_model;

  public function __construct(){
    include_once(_PS_MODULE_DIR_ . 'exportproducts/datamodel.php');
    $this->_model = new productsExportModel();

    if(isset(Context::getContext()->shop->id_shop_group) ){
      $this->_shopGroupId = Context::getContext()->shop->id_shop_group;
    } elseif( isset(Context::getContext()->shop->id_group_shop) ){
      $this->_shopGroupId = Context::getContext()->shop->id_group_shop;
    }

    $this->_shopId = Context::getContext()->shop->id;
    $this->name = 'exportproducts';
    $this->tab = 'export';
    $this->version = '4.1.1';
    $this->author = 'MyPrestaModules';
    $this->need_instance = 0;
    $this->bootstrap = true;
    $this->module_key = "15d42b09042de2f7cb9c610f1871ff1d";

    parent::__construct();

    $this->displayName = $this->l('Products Catalog (CSV, Excel, Xml) Export');
    $this->description = $this->l('Products Catalog (CSV, Excel, Xml) Export module is a convenient module especially designed to perform export operations with the PrestaShop products.');
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    $this->_exportTabInformation = array(
      array(
        'val'      => 'id_product',
        'name'     => $this->l('Product ID'),
        'xml_head' => $this->l('id_product'),
        'tab'      => 'exportTabInformation'
      ),
      array(
        'val'  => 'name',
        'name' => $this->l('Product name'),
        'xml_head' => $this->l('name'),
        'hint' => $this->l('The public name for product.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val'  => 'name_with_combination',
        'name' => $this->l('Product name (incl. combination)'),
        'xml_head' => $this->l('name_with_combination'),
        'hint' => $this->l('The public name with combination for product.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'reference',
        'name' => $this->l('Reference code'),
        'xml_head' => $this->l('reference'),
        'hint' => $this->l('Your internal reference code for this product.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'active',
        'name' => $this->l('Enabled'),
        'xml_head' => $this->l('active'),
        'hint' => $this->l('Value 0 or 1'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'description_short',
        'name' => $this->l('Short description'),
        'xml_head' => $this->l('description_short'),
        'hint' => $this->l('Appears in the product list(s), and at the top of the product page.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'description',
        'name' => $this->l('Description'),
        'xml_head' => $this->l('description'),
        'hint' => $this->l('Appears in the body of the product page.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'tags',
        'name' => $this->l('Tags'),
        'xml_head' => $this->l('tags'),
        'hint' => $this->l('Will be displayed in the tags block when enabled. Tags help customers easily find your products.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'ean13',
        'name' => $this->l('EAN-13 or JAN barcode'),
        'xml_head' => $this->l('ean13'),
        'hint' => $this->l('This type of product code is specific to Europe and Japan, but is widely used internationally. It is a superset of the UPC code: all products marked with an EAN will be accepted in North America.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'upc',
        'name' => $this->l('UPC barcode'),
        'xml_head' => $this->l('upc'),
        'hint' => $this->l('This type of product code is widely used in the United States, Canada, the United Kingdom, Australia, New Zealand and in other countries.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'isbn',
        'name' => $this->l('ISBN'),
        'xml_head' => $this->l('isbn'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'condition',
        'name' => $this->l('Condition'),
        'xml_head' => $this->l('condition'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'new',
        'name' => $this->l('new'),
        'xml_head' => $this->l('new'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'available_for_order',
        'name' => $this->l('Available for order'),
        'xml_head' => $this->l('available_for_order'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'online_only',
        'name' => $this->l('Online only'),
        'xml_head' => $this->l('online_only'),
        'hint' => $this->l('Online only (not sold in your retail store)'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'is_virtual',
        'name' => $this->l('Is virtual product'),
        'xml_head' => $this->l('is_virtual'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'visibility',
        'name' => $this->l('Visibility'),
        'xml_head' => $this->l('visibility'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'cache_is_pack',
        'name' => $this->l('cache_is_pack'),
        'xml_head' => $this->l('cache_is_pack'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'product_link',
        'name' => $this->l('Product url'),
        'xml_head' => $this->l('product_link'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'date_add',
        'name' => $this->l('Date add'),
        'xml_head' => $this->l('date_add'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'date_upd',
        'name' => $this->l('Date update'),
        'xml_head' => $this->l('date_upd'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'id_shop_default',
        'name' => $this->l('Default Shop ID'),
        'xml_head' => $this->l('id_shop_default'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'quantity_discount',
        'name' => $this->l('quantity_discount'),
        'xml_head' => $this->l('quantity_discount'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'redirect_type',
        'name' => $this->l('redirect_type'),
        'xml_head' => $this->l('redirect_type'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'id_product_redirected',
        'name' => $this->l('id_product_redirected'),
        'xml_head' => $this->l('id_product_redirected'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'indexed',
        'name' => $this->l('Indexed'),
        'xml_head' => $this->l('indexed'),
        'tab'   => 'exportTabInformation'
      ),

      array(
        'val' => 'id_color_default',
        'name' => $this->l('id_color_default'),
        'xml_head' => $this->l('id_color_default'),
        'tab'   => 'exportTabInformation'
      ),

      array(
        'val' => 'isFullyLoaded',
        'name' => $this->l('isFullyLoaded'),
        'xml_head' => $this->l('isFullyLoaded'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'pack_items_id',
        'name' => $this->l('Pack items Product ID'),
        'xml_head' => $this->l('pack_items_id'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'pack_items_name',
        'name' => $this->l('Pack items Name'),
        'xml_head' => $this->l('pack_items_name'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'pack_items_id_pack_product_attribute',
        'name' => $this->l('Pack items Product Attribute ID'),
        'xml_head' => $this->l('pack_items_id_pack_product_attribute'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'pack_items_reference',
        'name' => $this->l('Pack items Reference'),
        'xml_head' => $this->l('pack_items_reference'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'pack_items_ean13',
        'name' => $this->l('Pack items EAN13'),
        'xml_head' => $this->l('pack_items_ean13'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'pack_items_upc',
        'name' => $this->l('Pack items UPC'),
        'xml_head' => $this->l('pack_items_upc'),
        'tab'   => 'exportTabInformation'
      )
    );

    $this->_exportTabPrices = array(
      array(
        'val' => 'show_price',
        'name' => $this->l('Show price'),
        'xml_head' => $this->l('show_price'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'wholesale_price',
        'name' => $this->l('Pre-tax wholesale price'),
        'xml_head' => $this->l('wholesale_price'),
        'hint' => $this->l('The wholesale price is the price you paid for the product. Do not include the tax.'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'base_price',
        'name' => $this->l('Pre-tax retail price'),
        'xml_head' => $this->l('base_price'),
        'hint' => $this->l('The pre-tax retail price is the price for which you intend sell this product to your customers. It should be higher than the pre-tax wholesale price: the difference between the two will be your margin.'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'base_price_with_tax',
        'name' => $this->l('Retail price with tax'),
        'xml_head' => $this->l('base_price_with_tax'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'price',
        'name' => $this->l('Final price (pre-tax)'),
        'xml_head' => $this->l('price'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'final_price_with_tax',
        'name' => $this->l('Final price (with-tax)'),
        'xml_head' => $this->l('final_price_with_tax'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'combination_final_price_pre_tax',
        'name' => $this->l('Default Combination Final price (pre-tax)'),
        'xml_head' => $this->l('combination_final_price_pre_tax'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'combination_final_price_with_tax',
        'name' => $this->l('Default Combination Final price (with-tax)'),
        'xml_head' => $this->l('combination_final_price_with_tax'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'tax_rate',
        'name' => $this->l('Tax rate'),
        'xml_head' => $this->l('tax_rate'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'id_tax_rules_group',
        'name' => $this->l('Tax rules group ID'),
        'xml_head' => $this->l('id_tax_rules_group'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'unit_price_ratio',
        'name' => $this->l('Unit price ratio'),
        'xml_head' => $this->l('unit_price_ratio'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'unit_price',
        'name' => $this->l('Unit price (tax excl.)'),
        'xml_head' => $this->l('unit_price'),
        'hint' => $this->l('When selling a pack of items, you can indicate the unit price for each item of the pack. For instance, "per bottle" or "per pound".'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'unity',
        'name' => $this->l('Unit price (per)'),
        'xml_head' => $this->l('unity'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'ecotax',
        'name' => $this->l('Ecotax (tax incl.)'),
        'xml_head' => $this->l('ecotax'),
        'hint' => $this->l('The ecotax is a local set of taxes intended to "promote ecologically sustainable activities via economic incentives". It is already included in retail price: the higher this ecotax is, the lower your margin will be.'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'on_sale',
        'name' => $this->l('Display the on sale icon'),
        'xml_head' => $this->l('on_sale'),
        'hint' => $this->l('Display the on sale icon on the product page, and in the text found within the product listing. Value 0 or 1'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'id_specific_price',
        'name' => $this->l('Specific Price ID'),
        'xml_head' => $this->l('id_specific_price'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price',
        'name' => $this->l('Specific fixed prices'),
        'xml_head' => $this->l('specific_price'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_reduction',
        'name' => $this->l('Specific price reduction'),
        'xml_head' => $this->l('specific_price_reduction'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_reduction_type',
        'name' => $this->l('Specific price reduction type'),
        'xml_head' => $this->l('specific_price_reduction_type'),
        'hint' => $this->l('Reduction type (amount or percentage)'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_from',
        'name' => $this->l('Specific price Available from (date)'),
        'xml_head' => $this->l('specific_price_from'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_to',
        'name' => $this->l('Specific price Available to (date)'),
        'xml_head' => $this->l('specific_price_to'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_from_quantity',
        'name' => $this->l('Specific price Starting at (unit)'),
        'xml_head' => $this->l('specific_price_from_quantity'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_id_group',
        'name' => $this->l('Specific price Group ID'),
        'xml_head' => $this->l('specific_price_id_group'),
        'tab'   => 'exportTabPrices'
      ),
    );

    $this->_exportTabSeo = array(
      array(
        'val' => 'link_rewrite',
        'name' => $this->l('Friendly URL'),
        'xml_head' => $this->l('link_rewrite'),
        'tab'   => 'exportTabSeo'
      ),
      array(
        'val' => 'meta_title',
        'name' => $this->l('Meta title'),
        'xml_head' => $this->l('meta_title'),
        'hint' => $this->l('Public title for the product\'s page, and for search engines. Leave blank to use the product name. The number of remaining characters is displayed to the left of the field.'),
        'tab'   => 'exportTabSeo'
      ),
      array(
        'val' => 'meta_description',
        'name' => $this->l('Meta description'),
        'xml_head' => $this->l('meta_description'),
        'hint' => $this->l('This description will appear in search engines. You need a single sentence, shorter than 160 characters (including spaces).'),
        'tab'   => 'exportTabSeo'
      ),
      array(
        'val' => 'meta_keywords',
        'name' => $this->l('Meta keywords'),
        'xml_head' => $this->l('meta_keywords'),
        'hint' => $this->l('Keywords for HTML header, separated by commas.'),
        'tab'   => 'exportTabSeo'
      ),
    );

    $this->_exportTabAssociations = array(
      array(
        'val' => 'categories_ids',
        'name' => $this->l('Associated categories Ids'),
        'xml_head' => $this->l('categories_ids'),
        'hint' => $this->l('Each associated category id separated by a semicolon'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'separated_categories',
        'name' => $this->l('Categories tree ( each category tree in a separate field )'),
        'xml_head' => $this->l('separated_categories'),
        'hint' => $this->l(''),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'categories_names',
        'name' => $this->l('Associated categories name'),
        'xml_head' => $this->l('categories_names'),
        'hint' => $this->l('Each associated category name separated by a semicolon'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'id_category_default',
        'name' => $this->l('Category Default ID'),
        'xml_head' => $this->l('id_category_default'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'category_default_name',
        'name' => $this->l('Category Default Name'),
        'xml_head' => $this->l('category_default_name'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'id_product_accessories',
        'name' => $this->l('Accessories Product ID'),
        'xml_head' => $this->l('id_product_accessories'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'id_manufacturer',
        'name' => $this->l('Manufacturer ID'),
        'xml_head' => $this->l('id_manufacturer'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'manufacturer_name',
        'name' => $this->l('Manufacturer'),
        'xml_head' => $this->l('manufacturer_name'),
        'tab'   => 'exportTabAssociations'
      ),
    );

    $this->_exportTabShipping = array(
      array(
        'val' => 'width',
        'name' => $this->l('Package width'),
        'xml_head' => $this->l('width'),
        'tab'   => 'shipping'
      ),
      array(
        'val' => 'height',
        'name' => $this->l('Package height'),
        'xml_head' => $this->l('height'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'depth',
        'name' => $this->l('Package depth'),
        'xml_head' => $this->l('depth'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'weight',
        'name' => $this->l('Package weight'),
        'xml_head' => $this->l('weight'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'additional_shipping_cost',
        'name' => $this->l('Additional shipping fees'),
        'xml_head' => $this->l('additional_shipping_cost'),
        'hint' => $this->l('Additional shipping fees (for a single item)'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'id_carriers',
        'name' => $this->l('Product Carriers ID'),
        'xml_head' => $this->l('id_carriers'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'additional_delivery_times',
        'name' => $this->l('Delivery Time'),
        'xml_head' => $this->l('additional_delivery_times'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'delivery_in_stock',
        'name' => $this->l('Delivery time of in-stock products'),
        'xml_head' => $this->l('delivery_in_stock'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'delivery_out_stock',
        'name' => $this->l('Delivery time of out-of-stock products with allowed orders'),
        'xml_head' => $this->l('delivery_out_stock'),
        'tab'   => 'exportTabShipping'
      ),
    );

    $this->_exportTabCombinations = array();

    foreach( AttributeGroup::getAttributesGroups( ContextCore::getContext()->language->id ) as $attribute ){
      $this->_exportTabCombinations[] = array(
        'val' => 'Attribute_'.$attribute['id_attribute_group'],
        'name' => $this->l('Attribute ') . $attribute['name'],
        'xml_head' => $this->l('Attribute_').$attribute['name'],
        'tab'   => 'exportTabCombinations'
      );
    }

    $combinationsTab = array(
      array(
        'val' => 'id_product_attribute',
        'name' => $this->l('Product Combinations ID'),
        'xml_head' => $this->l('id_product_attribute'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_name',
        'name' => $this->l('Combinations (Attribute - value pair)'),
        'xml_head' => $this->l('combinations_name'),
        'hint' => $this->l('Each combination name separated by a semicolon'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_value',
        'name' => $this->l('Attribute value (each value in separate field)'),
        'xml_head' => $this->l('combinations_value'),
        'hint' => $this->l('Each attribute value will be in a separate field'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_reference',
        'name' => $this->l('Combinations Reference code'),
        'xml_head' => $this->l('combinations_reference'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_price',
        'name' => $this->l('Combinations Impact on price (pre-tax)'),
        'xml_head' => $this->l('combinations_price'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_price_with_tax',
        'name' => $this->l('Combinations Impact on price (with-tax)'),
        'xml_head' => $this->l('combinations_price_with_tax'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_unit_price_impact',
        'name' => $this->l('Combinations Impact on unit price'),
        'xml_head' => $this->l('combinations_unit_price_impact'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_wholesale_price',
        'name' => $this->l('Combinations wholesale price'),
        'xml_head' => $this->l('combinations_wholesale_price'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'cache_default_attribute',
        'name' => $this->l('Default Product Combination ID '),
        'xml_head' => $this->l('cache_default_attribute'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_ean13',
        'name' => $this->l('Combinations EAN-13 or JAN barcode'),
        'xml_head' => $this->l('combinations_ean13'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_upc',
        'name' => $this->l('Combinations UPC barcode'),
        'xml_head' => $this->l('combinations_upc'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_isbn',
        'name' => $this->l('Combinations ISBN'),
        'xml_head' => $this->l('combinations_isbn'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_ecotax',
        'name' => $this->l('Combination Ecotax (tax excl.)'),
        'xml_head' => $this->l('combinations_ecotax'),
        'hint' => $this->l('Overrides the ecotax from the "Prices" tab.'),
        'tab'   => 'exportTabCombinations'
      ),

      array(
        'val' => 'combinations_location',
        'name' => $this->l('Combinations location'),
        'xml_head' => $this->l('combinations_location'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_weight',
        'name' => $this->l('Combinations Impact on weight'),
        'xml_head' => $this->l('combinations_weight'),
        'tab'   => 'exportTabCombinations'
      ),
    );

    $this->_exportTabCombinations = array_merge($this->_exportTabCombinations, $combinationsTab);

    $this->_exportTabQuantities = array(
      array(
        'val' => 'quantity',
        'name' => $this->l('Quantity'),
        'xml_head' => $this->l('quantity'),
        'hint' => $this->l('Available quantities for sale'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'total_quantity',
        'name' => $this->l('Total Quantity (inc. combinations)'),
        'xml_head' => $this->l('total_quantity'),
        'hint' => $this->l('Total combinations quantity'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'minimal_quantity',
        'name' => $this->l('Minimum quantity'),
        'xml_head' => $this->l('minimal_quantity'),
        'hint' => $this->l('The minimum quantity to buy this product (set to 1 to disable this feature)'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'location',
        'name' => $this->l('Stock location'),
        'xml_head' => $this->l('location'),
        'hint' => $this->l(''),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'low_stock_threshold',
        'name' => $this->l('Low stock level'),
        'xml_head' => $this->l('low_stock_threshold'),
        'hint' => $this->l(''),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'low_stock_alert',
        'name' => $this->l('Low stock email alert'),
        'xml_head' => $this->l('location'),
        'hint' => $this->l('Send me an email when the quantity is below or equals this level'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'out_of_stock',
        'name' => $this->l('When out of stock'),
        'xml_head' => $this->l('out_of_stock'),
        'hint' => $this->l('0 - Deny orders, 1 - Allow orders, 2 - Default'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'available_now',
        'name' => $this->l('Displayed text when in-stock'),
        'xml_head' => $this->l('available_now'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'available_later',
        'name' => $this->l('Displayed text when backordering is allowed'),
        'xml_head' => $this->l('available_later'),
        'hint' => $this->l('If empty, the message "in stock" will be displayed.'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'advanced_stock_management',
        'name' => $this->l('advanced_stock_management'),
        'xml_head' => $this->l('advanced_stock_management'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'depends_on_stock',
        'name' => $this->l('depends_on_stock'),
        'xml_head' => $this->l('depends_on_stock'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'pack_stock_type',
        'name' => $this->l('pack_stock_type'),
        'xml_head' => $this->l('pack_stock_type'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'available_date',
        'name' => $this->l('Availability date'),
        'xml_head' => $this->l('available_date'),
        'hint' => $this->l('The next date of availability for this product when it is out of stock.'),
        'tab'   => 'exportTabQuantities'
      ),
    );

    $this->_exportTabImages = array(
      array(
        'val' => 'images',
        'name' => $this->l('Product Image urls'),
        'xml_head' => $this->l('images'),
        'tab'   => 'exportTabImages'
      ),
      array(
        'val' => 'cover_image_url',
        'name' => $this->l('Product Cover Image Url'),
        'xml_head' => $this->l('cover_image_url'),
        'tab'   => 'exportTabImages'
      ),
      array(
        'val' => 'images_value',
        'name' => $this->l('Product Image urls (each value in separate field)'),
        'xml_head' => $this->l('images_value'),
        'hint' => $this->l('Each image url will be in a separate field'),
        'tab'   => 'exportTabImages'
      ),
      array(
        'val'  => 'image_cover',
        'name' => $this->l('Product Cover Image'),
        'xml_head' => $this->l('image_cover'),
        'tab'   => 'exportTabImages'
      ),
      array(
        'val' => 'image_caption',
        'name' => $this->l('Product Image caption'),
        'xml_head' => $this->l('image_caption'),
        'tab'   => 'exportTabImages'
      ),

    );

    $this->_exportTabFeatures = array(
      array(
        'val' => 'features',
        'name' => $this->l('All Features'),
        'hint' => $this->l('Each product feature would be exported in a separate column!'),
        'tab'   => 'exportTabFeatures'
      ),
    );
    foreach( Feature::getFeatures( Context::getContext()->language->id ) as $feature ){
      $this->_exportTabFeatures[] = array(
        'val' => 'FEATURE_'.$feature['name'],
        'name' => $this->l('Feature ') . $feature['name'],
        'xml_head' => $this->l('FEATURE_').$feature['name'],
        'tab'   => 'exportTabFeatures'
      );
    }

    $this->_exportTabCustomization = array(
      array(
        'val' => 'customizable',
        'name' => $this->l('Customizable'),
        'xml_head' => $this->l('customizable'),
        'tab'   => 'exportTabCustomization'
      ),
      array(
        'val' => 'uploadable_files',
        'name' => $this->l('File fields'),
        'xml_head' => $this->l('uploadable_files'),
        'hint' => $this->l('Number of upload file fields to be displayed to the user.'),
        'tab'   => 'exportTabCustomization'
      ),
      array(
        'val' => 'text_fields',
        'name' => $this->l('Text fields'),
        'xml_head' => $this->l('text_fields'),
        'hint' => $this->l('Number of text fields to be displayed to the user.'),
        'tab'   => 'exportTabCustomization'
      ),
      array(
        'val' => 'customization_field_type',
        'name' => $this->l('Customization Fields Type'),
        'xml_head' => $this->l('customization_field_type'),
        'hint' => $this->l(''),
        'tab'   => 'exportTabCustomization'
      ),
      array(
        'val' => 'customization_field_name',
        'name' => $this->l('Customization Fields Label'),
        'xml_head' => $this->l('customization_field_name'),
        'hint' => $this->l(''),
        'tab'   => 'exportTabCustomization'
      ),
      array(
        'val' => 'customization_field_required',
        'name' => $this->l('Customization Fields Is Required'),
        'xml_head' => $this->l('customization_field_required'),
        'hint' => $this->l(''),
        'tab'   => 'exportTabCustomization'
      ),
    );

    $this->_exportTabAttachments = array(
      array(
        'val' => 'id_attachments',
        'name' => $this->l('Attachments ID'),
        'xml_head' => $this->l('id_attachments'),
        'tab'   => 'exportTabAttachments'
      ),
      array(
        'val' => 'attachments_name',
        'name' => $this->l('Attachments Name'),
        'xml_head' => $this->l('attachments_name'),
        'tab'   => 'exportTabAttachments'
      ),
      array(
        'val' => 'attachments_description',
        'name' => $this->l('Attachments Description'),
        'xml_head' => $this->l('attachments_description'),
        'tab'   => 'exportTabAttachments'
      ),
      array(
        'val' => 'attachments_file',
        'name' => $this->l('Attachments file URL'),
        'xml_head' => $this->l('attachments_file'),
        'tab'   => 'exportTabAttachments'
      ),
      array(
        'val' => 'cache_has_attachments',
        'name' => $this->l('cache_has_attachments'),
        'xml_head' => $this->l('cache_has_attachments'),
        'tab'   => 'exportTabAttachments'
      ),
    );

    $this->_exportTabSuppliers = array(
      array(
        'val'  => 'suppliers_ids',
        'name' => $this->l('Supplier Ids'),
        'xml_head' => $this->l('suppliers_ids'),
        'hint' => $this->l('Each supplier ID separated by a semicolon'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'suppliers_name',
        'name' => $this->l('Suppliers'),
        'xml_head' => $this->l('suppliers_name'),
        'hint' => $this->l('Each supplier name separated by a semicolon'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'id_supplier',
        'name' => $this->l('Default supplier ID'),
        'xml_head' => $this->l('id_supplier'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'supplier_name',
        'name' => $this->l('Default supplier name'),
        'xml_head' => $this->l('supplier_name'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'supplier_reference',
        'name' => $this->l('Default supplier reference'),
        'xml_head' => $this->l('supplier_reference'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'supplier_price',
        'name' => $this->l('Default supplier Unit price tax excluded'),
        'xml_head' => $this->l('supplier_price'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'supplier_price_currency',
        'name' => $this->l('Default supplier Unit price currency'),
        'xml_head' => $this->l('supplier_price_currency'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'separate_supplier_id',
        'name' => $this->l('Supplier ID'),
        'xml_head' => $this->l('separate_supplier_id'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'separate_supplier_name',
        'name' => $this->l('Supplier Name'),
        'xml_head' => $this->l('separate_supplier_name'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'separate_supplier_reference',
        'name' => $this->l('Supplier Reference'),
        'xml_head' => $this->l('separate_supplier_reference'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'separate_supplier_price',
        'name' => $this->l('Supplier Price Tax.Excl'),
        'xml_head' => $this->l('separate_supplier_price'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'separate_supplier_currency',
        'name' => $this->l('Supplier Currency'),
        'xml_head' => $this->l('separate_supplier_currency'),
        'tab'   => 'exportTabSuppliers'
      ),
    );
  }

  public function install()
  {
    Configuration::updateValue('GOMAKOIL_PRODUCTS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_MANUFACTURERS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_SUPPLIERS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    if ( !parent::install() ) {
      return false;
    }

    Configuration::updateGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_TASKS_KEY', md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME')));
    if( !$this->installDb() ){
      return false;
    }
    $this->_createTab();
    return true;
  }

  public function uninstall(){
    Configuration::deleteByName('GOMAKOIL_PRODUCTS_CHECKED');
    Configuration::deleteByName('GOMAKOIL_MANUFACTURERS_CHECKED');
    Configuration::deleteByName('GOMAKOIL_SUPPLIERS_CHECKED');

    Configuration::deleteByName('GOMAKOIL_PRODUCTS_EXPORT_TASKS_KEY');

    $this->uninstallDb();
    $this->_removeTab();

    return parent::uninstall();
  }

  private function _createTab()
  {
    $tab = new Tab();
    $tab->active = 1;
    $tab->class_name = 'AdminProductsExport';
    $tab->name = array();
    foreach (Language::getLanguages(true) as $lang)
      $tab->name[$lang['id_lang']] = 'Products Export';
    $tab->id_parent = -1;
    $tab->module = $this->name;
    $tab->add();
  }

  private function _removeTab()
  {
    $id_tab = (int)Tab::getIdFromClassName('AdminProductsExport');
    if ($id_tab)
    {
      $tab = new Tab($id_tab);
      $tab->delete();
    }
  }

  public function upgradeExport_3_7_0()
  {
    $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'productsexport_tasks(
			`id_task` int(11) NOT NULL AUTO_INCREMENT,
      `description` varchar(255) NOT NULL,
      `export_settings` varchar(255) NOT NULL,
      `hour` int(11) NOT NULL,
      `day` int(11) NOT NULL,
      `month` int(11) NOT NULL,
      `day_of_week` int(11) NOT NULL,
      `last_start` varchar(45) NOT NULL,
      `last_finish` varchar(45) NOT NULL,
      `active` int(1) NOT NULL,
      `one_shot` int(1) NOT NULL,
      `id_shop` int(11) NOT NULL,
      `id_shop_group` int(11) NOT NULL,
      PRIMARY KEY (`id_task`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';

    Db::getInstance()->execute($sql);

    Configuration::updateGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_TASKS_KEY', md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME')));

    $this->_createTab();

    return true;
  }

  public function upgradeExport_4_0_0()
  {
    $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'exportproducts_data';
    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'exportproducts_data(
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `row` int(11) NOT NULL,
            `field` varchar(254) NOT NULL,
            `value` text NOT NULL,
            `id_task` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `index2` (`row`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';

    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    $sql = '
      ALTER TABLE ' . _DB_PREFIX_ . 'productsexport_tasks
      ADD COLUMN `progress` VARCHAR(500) NOT NULL AFTER `last_finish`
        ;
    ';

    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    return true;
  }

  public function upgradeExport_3_1_0()
  {
    $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'exported_products';
    Db::getInstance()->execute($sql);

    $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'exported_products(
				id_exported_products int(11) unsigned NOT NULL AUTO_INCREMENT,
			  id_product  int(11) NULL,
		    id_setting int(11) NULL,
				PRIMARY KEY (`id_exported_products`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';

    Db::getInstance()->execute($sql);
    return true;
  }

  public function uninstallDb()
  {
    $sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'exported_products';
    Db::getInstance()->execute($sql);

    $sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'productsexport_tasks';
    Db::getInstance()->execute($sql);
  }

  public function installDb()
  {
    $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'productsexport_tasks';
    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'productsexport_tasks(
			`id_task` int(11) NOT NULL AUTO_INCREMENT,
      `description` varchar(255) NOT NULL,
      `export_settings` varchar(255) NOT NULL,
      `hour` int(11) NOT NULL,
      `day` int(11) NOT NULL,
      `month` int(11) NOT NULL,
      `day_of_week` int(11) NOT NULL,
      `last_start` varchar(45) NOT NULL,
      `last_finish` varchar(45) NOT NULL,
      `progress` varchar(500) NOT NULL,
      `active` int(1) NOT NULL,
      `one_shot` int(1) NOT NULL,
      `id_shop` int(11) NOT NULL,
      `id_shop_group` int(11) NOT NULL,
      PRIMARY KEY (`id_task`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';

    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    // Table  pages
    $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'exported_products';
    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'exported_products(
				id_exported_products int(11) unsigned NOT NULL AUTO_INCREMENT,
				id_product  int(11) NULL,
		    id_setting int(11) NULL,
				PRIMARY KEY (`id_exported_products`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';

    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'exportproducts_data';
    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'exportproducts_data(
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `row` int(11) NOT NULL,
            `field` varchar(254) NOT NULL,
            `value` text NOT NULL,
            `id_task` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `index2` (`row`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';

    $res = Db::getInstance()->execute($sql);
    if( !$res ){
      return false;
    }

    return true;
  }

  public function checkExportRunning()
  {
    $productsExport = false;
    if( Configuration::getGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_RUNNING') ){
      $time = (int)Configuration::getGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_RUNNING');
      if( (time() - $time) < 60 ){
        $productsExport = true;
      }
    }

    return $productsExport;
  }

  public function addLog( $message )
  {
    $write_fd = fopen(_PS_MODULE_DIR_ . 'exportproducts/error.log', 'a+');
    if (@$write_fd !== false){
      fwrite($write_fd, '- ' . $message . "\r\n");
    }
    fclose($write_fd);
  }

  public function updateProgress( $status = false )
  {
    $idTask = Tools::getValue('id_task');

    if( !$idTask ){
      return false;
    }

    $currentExported = Configuration::getGlobalValue('EXPORT_PRODUCTS_CURRENT_COUNT');
    $totalExport = Configuration::getGlobalValue('EXPORT_PRODUCTS_COUNT');

    if( ((int)$currentExported) ){
      $progress = Module::getInstanceByName('exportproducts')->l('Exported products ') . $currentExported . ' of ' . $totalExport;
    }
    else{
      $progress = $currentExported;
    }

    if( $status ){
      $progress = $status;
    }

    $data = array(
      'progress' => $progress
    );

    Db::getInstance()->update('productsexport_tasks', $data, 'id_task = ' . $idTask);
  }

  public function getContent()
  {
    if( Tools::getValue('configure') == 'exportproducts' ){
      $this->context->controller->addCSS($this->_path.'views/css/style.css');
      $this->context->controller->addJS($this->_path.'views/js/main.js');
      $this->context->controller->addJqueryUI('ui.sortable');
    }
    $logo = '<img class="logo_myprestamodules" src="../modules/'.$this->name.'/logo.png" />';
    $name = '<h2 id="bootstrap_products_export">'.$logo.$this->displayName.'</h2>';

    if( Tools::getValue('settings') ){
      $id = Tools::getValue('settings');
    }
    else{
      $id = false;
    }
    return $name.$this->displayForm();
  }

  public function documentationBlock(){
    return $this->display(__FILE__, "views/templates/hook/documentation.tpl");
  }
  
  public function supportBlock(){
    return $this->display(__FILE__, "views/templates/hook/supportForm.tpl");
  }

  public function displayTabModules(){
    return $this->display(__FILE__, 'views/templates/hook/modules.tpl');
  }
  public function listSettings($id){

    $setting = array();
    $all_setting = Tools::unserialize( Configuration::get('GOMAKOIL_ALL_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));

    if($all_setting){
      foreach($all_setting as $value){
        $name_conf = 'GOMAKOIL_NAME_SETTING_'.$value;
        $name =  Configuration::get($name_conf,'',$this->_shopGroupId, Context::getContext()->shop->id);
        $setting[] = array(
          'id'    => $value,
          'name'  => $name

        );
      }
    }
    else{
      $setting = false;
    }

    $this->context->smarty->assign(
      array(
        'id'              => $id,
        'setting'         => $setting,
        'base_url'        => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')
      )
    );
    return $this->display(__FILE__, "views/templates/hook/listSettings.tpl");
  }

  public function getPath()
  {
    return $this->_path;
  }

  public function replaceConfig($id){
    $config = Configuration::get('GOMAKOIL_PRODUCTS_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_PRODUCTS_CHECKED', $config, false, $this->_shopGroupId, Context::getContext()->shop->id);
    $config = Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_MANUFACTURERS_CHECKED', $config, false, $this->_shopGroupId, Context::getContext()->shop->id);
    $config = Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_SUPPLIERS_CHECKED', $config, false, $this->_shopGroupId, Context::getContext()->shop->id);
    $config = Configuration::get('GOMAKOIL_CATEGORIES_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', $config, false,  $this->_shopGroupId, Context::getContext()->shop->id);
    $config = Configuration::get('GOMAKOIL_FIELDS_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', $config, false,  $this->_shopGroupId, Context::getContext()->shop->id);
    $config = Configuration::get('GOMAKOIL_EXTRA_FIELDS_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_EXTRA_FIELDS', $config, false,  $this->_shopGroupId, Context::getContext()->shop->id);
  $config = Configuration::get('GOMAKOIL_EDITED_XML_NAMES_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
  Configuration::updateValue('GOMAKOIL_EDITED_XML_NAMES', $config, false,  $this->_shopGroupId, Context::getContext()->shop->id);
  }

  public function displayForm()
  {
    $class = '';
    $name = '';
    $show = 0;

    if(Tools::getValue('settings')){
      $last_id = Tools::getValue('settings');
      $this->replaceConfig($last_id);
    }
    else{
      $all_setting = array();
      Configuration::updateValue('GOMAKOIL_PRODUCTS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_MANUFACTURERS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_SUPPLIERS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_EXTRA_FIELDS', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_EDITED_XML_NAMES', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      $all_setting =Tools::unserialize( Configuration::get('GOMAKOIL_ALL_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));
      if($all_setting){
        $all_setting = max($all_setting);
        $last_id = $all_setting + 1;
      }
      else{
        $last_id = 1;
      }
    }

    if( Tools::getValue('settingsExport') ){
      $last_id_update = Tools::getValue('settingsExport');
      $this->replaceConfigUpdate($last_id_update);
    }
    else{
      $all_setting_update = array();
      Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED_UPDATE', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      $all_setting_update =Tools::unserialize( Configuration::get('GOMAKOIL_ALL_UPDATE_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));

      if($all_setting_update){
        $all_setting_update = max($all_setting_update);
        $last_id_update = $all_setting_update + 1;
      }
      else{
        $last_id_update = 1;
      }
    }

    $products = Product::getProducts(Context::getContext()->language->id, 0, 300, 'name', 'asc' );
    $manufacturers = Manufacturer::getManufacturers(false, Context::getContext()->language->id, true, false, false, false, true );
    $suppliers = Supplier::getSuppliers(false, Context::getContext()->language->id);
    $selected_products = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));
    $selected_manufacturers = Tools::unserialize(Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));
    $selected_suppliers = Tools::unserialize(Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));
    $selected_categories = Tools::unserialize(Configuration::get('GOMAKOIL_CATEGORIES_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));
    $automaticDescription = '<p>You must save this products export settings before enable automatic export.</p>';
    $show_del = 'hide_block';

    if( Tools::getValue('settings') ){
      $type_file = Configuration::get('GOMAKOIL_TYPE_FILE_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $automaticDescription = '<p>You can place the following URL in your crontab file, or you can click it yourself regularly</p>';
      $automaticDescription .= '<p><strong><a href="'.Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/exportproducts/automatic_export.php?settings='.Tools::getValue('settings').'&id_shop_group='.Context::getContext()->shop->id_shop_group.'&id_shop='.Context::getContext()->shop->id.'&id_lang='.Configuration::get('GOMAKOIL_LANG_CHECKED_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id).'&secure_key='.md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME')).'" onclick="return !window.open($(this).attr(\'href\'));">'.Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/exportproducts/automatic_export.php?settings='.Tools::getValue('settings').'&id_shop_group='.Context::getContext()->shop->id_shop_group.'&id_shop='.Context::getContext()->shop->id.'&id_lang='.Configuration::get('GOMAKOIL_LANG_CHECKED_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id).'&secure_key='.md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME')).'</a></strong></p>';
      $priceSettings = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_PRICE_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id));
      $quantitySettings = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_QUANTITY_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id));
      $visibility = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_VISIBILITY_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id));
      $condition = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CONDITION_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id));

      if($type_file && $type_file == 'csv'){
        $show_del = 'show_block';
      }
      $show =  Configuration::get('GOMAKOIL_SHOW_NAME_FILE_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $name =  Configuration::get('GOMAKOIL_NAME_FILE_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      if($show){
        $class = ' active_block';
      }
    }
    else{
      $priceSettings = false;
      $quantitySettings = false;
      $visibility = false;
      $condition = false;
    }

    $url_base = AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&configure=exportproducts';
    $file_url = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/exportproducts/files/';
    $nameDescription = '<p class="available_url">'.$this->l('The file will be available by link below:').'</p>';
    $nameDescription .= '<p ><strong><a class="href_export_file"  href="" data-file-url="'.$file_url.'"></a></strong></p>';

    $delimiter = array(
      array(
        'id' => ';',
        'name' => ';',
      ),
      array(
        'id' => ',',
        'name' => ',',
      ),
      array(
        'id' => ':',
        'name' => ':',
      ),
      array(
        'id' => '.',
        'name' => '.',
      ),
      array(
        'id' => '/',
        'name' => '/',
      ),
      array(
        'id' => '|',
        'name' => '|',
      ),
      array(
        'id' => '-',
        'name' => '-',
      ),
      array(
        'id' => 'space',
        'name' => 'space',
      ),
      array(
        'id' => 'tab',
        'name' => 'tab',
      ),
    );

    $seperatop = array(
      array(
        'id' => '1',
        'name' => ' " " ',
      ),
      array(
        'id' => '2',
        'name' => ' ` ` ',
      ),
      array(
        'id' => '3',
        'name' => 'no',
      ),
    );

    $round_value = array(
      array(
        'id' => '0',
        'name' => '0',
      ),
      array(
        'id' => '1',
        'name' => '1',
      ),
      array(
        'id' => '2',
        'name' => '2',
      ),
      array(
        'id' => '3',
        'name' => '3',
      ),
      array(
        'id' => '4',
        'name' => '4',
      ),
      array(
        'id' => '5',
        'name' => '5',
      ),
      array(
        'id' => '6',
        'name' => '6',
      ),
    );

    $sort = array(
      array(
        'name' => 'ID',
        'id' => 'id',
      ),
      array(
        'name' => 'Name',
        'id' => 'name',
      ),
      array(
        'name' => 'Price',
        'id' => 'price',
      ),
      array(
        'name' => 'Quantity',
        'id' => 'quantity',
      ),
      array(
        'name' => 'Date add',
        'id' => 'date_add',
      ),
      array(
        'name' => 'Date update',
        'id' => 'date_update',
      )
    );

    if( Tools::isSubmit('add_task') && Tools::getValue('module_tab') == 'schedule_tasks' ){
      $addRes =  $this->_addTask();
    }

    $this->fields_form[0]['form'] = array(
      'tabs' => array(
        'welcome' => $this->l('Welcome'),
        'export' => $this->l('General settings'),
        'filter_products' => $this->l('Filter products'),
        'filter_fields' => $this->l('Filter fields'),
        'automatic_export' => $this->l('Automatic export'),
        'schedule_tasks' => $this->l('Schedule Tasks'),
        'new_settings' => $this->l('Settings'),
//        'documentation' => $this->l('Documentation'),
//        'support' => $this->l('Support'),
        'modules' => $this->l('Related Modules'),
      ),
      'input' => array(
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_welcome',
          'tab' => 'welcome',
          'name' => $this->initFormWelcome(),
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_schedule',
          'tab' => 'schedule_tasks',
          'name' => $this->initFormScheduleTasks(),
        ),
//        array(
//          'type' => 'html',
//          'form_group_class' => 'exportFields',
//          'tab' => 'documentation',
//          'name' => $this->documentationBlock(),
//        ),
//        array(
//          'type' => 'html',
//          'form_group_class' => 'exportFields',
//          'tab' => 'support',
//          'name' => '',
//        ),
        array(
          'type' => 'html',
          'tab' => 'modules',
          'form_group_class' => 'support_tab_content exportFields',
          'name' => $this->displayTabModules()
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_module_hind',
          'tab' => 'export',
          'name' => '<div class="alert alert-info">' . $this->l('If no filter is selected, module will export all products!') . '</div>',
        ),
        array(
          'type' => 'radio',
          'label' => $this->l('Select file format:'),
          'name' => 'format_file',
          'required' => true,
          'form_group_class' => 'form_group_type_file',
          'class' => 'format_file',
          'tab' => 'export',
          'br' => true,
          'values' => array(
            array(
              'id' => 'format_csv',
              'value' => 'csv',
              'label' => $this->l('CSV')
            ),
            array(
              'id' => 'format_xlsx',
              'value' => 'xlsx',
              'label' => $this->l('XLSX')
            ),
            array(
              'id' => 'format_xml',
              'value' => 'xml',
              'label' => $this->l('XML')
            )
          ),
          'desc' => $this->l('Choose a file format you wish to export'),
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Delimiter'),
          'name' => 'delimiter_val',
          'class' => 'delimiter_val',
          'form_group_class' => 'csv_delimiter block_csv_settings '.$show_del,
          'tab' => 'export',
          'options' => array(
            'query' =>$delimiter,
            'id' => 'id',
            'name' => 'name'
          )
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Seperatop'),
          'name' => 'seperatop_val',
          'class' => 'seperatop_val',
          'tab' => 'export',
          'form_group_class' => 'csv_seperatop block_csv_settings '.$show_del,
          'options' => array(
            'query' =>$seperatop,
            'id' => 'id',
            'name' => 'name'
          )
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Feed target'),
          'name' => 'feed_target',
          'tab' => 'export',
          'class' => 'feed_target',
          'options' => array(
            'query' => array(
              array(
                'id' => 'file_system',
                'name' => 'File System',
              ),
              array(
                'id' => 'ftp',
                'name' => 'FTP',
              ),
            ),
            'id' => 'id',
            'name' => 'name'
          ),
          'desc' => $this->l('Choose a feed target'),
        ),
        array(
          'type'     => 'text',
          'label'    => $this->l('FTP Server'),
          'required' => true,
          'name'     => 'ftp_server',
          'form_group_class' => 'ftp_target',
          'tab' => 'export',
        ),
        array(
          'type'     => 'text',
          'label'    => $this->l('User Name'),
          'required' => true,
          'name'     => 'ftp_user',
          'form_group_class' => 'ftp_target',
          'tab' => 'export',
        ),
        array(
          'type'     => 'text',
          'label'    => $this->l('Password'),
          'required' => true,
          'name'     => 'ftp_password',
          'form_group_class' => 'ftp_target',
          'tab' => 'export',
        ),
        array(
          'type'     => 'text',
          'label'    => $this->l('Absolute path to folder'),
          'name'     => 'ftp_folder_path',
          'form_group_class' => 'ftp_target',
          'tab' => 'export',
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Set specific file name'),
          'name' => 'name_export_file',
          'class' => 'name_export_file',
          'tab' => 'export',
          'form_group_class' => 'form_group_class_hide form_group_class_set_name',
          'is_bool' => true,
          'tab' => 'export',
          'values' => array(
            array(
              'id' => 'name_export_file_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'name_export_file_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('You can set name for file or name will be given by system.'),
        ),
        array(
          'type' => 'html',
          'name' => $nameDescription,
          'tab' => 'export',
          'form_group_class' => ' auto_description_ex'.$class,
        ),
        array(
          'type' => 'text',
          'label' => $this->l('Name for exported file'),
          'name' => 'name_file',
          'tab' => 'export',
          'form_group_class' => 'form_group_name_file'.$class,
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Language'),
          'name' => 'id_lang',
          'tab' => 'export',
          'required' => true,
          'form_group_class' => 'form_group_filter_language',
          'default_value' => (int)$this->context->language->id,
          'options' => array(
            'query' => Language::getLanguages(),
            'id' => 'id_lang',
            'name' => 'name',
          ),
          'desc' => $this->l('Choose a language you wish to export'),
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Currency'),
          'name' => 'currency',
          'tab' => 'export',
          'form_group_class' => 'form_group_filter_currency',
          'default_value' => (int)$this->context->currency->id,
          'options' => array(
            'query' => Currency::getCurrencies(),
            'id' => 'id_currency',
            'name' => 'name',
          ),
          'desc' => $this->l('Choose a currency you wish to export'),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Display headers'),
          'name' => 'display_headers',
          'class' => 'display_headers',
          'tab' => 'export',
          'form_group_class' => 'export_display_headers form_left_margin',
          'is_bool' => true,
          'values' => array(
            array(
              'id' => 'display_headers_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'display_headers_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' =>  $this->l('Add a first line in the file with columns names. You can modify the names with the translation tool'),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Strip tags'),
          'name' => 'strip_tags',
          'class' => 'strip_tags',
          'form_group_class' => 'export_strip_tags form_left_margin',
          'tab' => 'export',
          'is_bool' => true,
          'values' => array(
            array(
              'id' => 'strip_tags_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'strip_tags_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('Strip HTML and PHP tags from a description'),
        ),

        array(
          'type' => 'select',
          'label' => $this->l('Separator of decimal points'),
          'name' => 'separator_decimal_points',
          'tab' => 'export',
          'class' => 'separator_decimal_points',
          'form_group_class' => 'separator_decimal_points_block',
          'options' => array(
            'query' => array(
              array('id' => '.', 'name' => '.'),
              array('id' => ',', 'name' => ','),
            ),
            'id' => 'id',
            'name' => 'name'
          ),

        ),

        array(
          'type' => 'select',
          'label' => $this->l('Number of decimal points'),
          'name' => 'round_value',
          'class' => 'round_value',
          'tab' => 'export',
          'form_group_class' => 'round_value_block form_left_margin',
          'options' => array(
            'query' =>$round_value,
            'id' => 'id',
            'name' => 'name'
          ),
          'desc' =>  $this->l('Will be used in the prices and size. You can choose to have 5.12 instead of 5.121123.'),
        ),
        array(
          'type' => 'text',
          'label' => $this->l('Prices decoration'),
          'name' => 'decoration_price',
          'class' => 'decoration_price',
          'tab' => 'export',
          'form_group_class' => 'form_group_decoration_price form_left_margin',
          'desc' =>  $this->l('Will be used in the prices. "[PRICE] USD" will give "13.46 USD", "$[PRICE]" will give "$13.46", live empty to have only number'),
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Sort by'),
          'name' => 'orderby',
          'class' => 'orderby',
          'tab' => 'export',
          'form_group_class' => 'sort_block form_left_margin',
          'options' => array(
            'query' =>$sort,
            'id' => 'id',
            'name' => 'name'
          )
        ),
        array(
          'type' => 'radio',
          'label' => $this->l(' '),
          'name' => 'orderway',
          'tab' => 'export',
          'required' => true,
          'form_group_class' => 'sort_block_orderway form_left_margin',
          'br' => true,
          'values' => array(
            array(
              'id' => 'orderway_asc',
              'value' => 'asc',
              'label' => $this->l('ASC')
            ),
            array(
              'id' => 'orderway_desc',
              'value' => 'desc',
              'label' => $this->l('DESC')
            )
          )
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Each product combinations  in a separate line'),
          'name' => 'separate',
          'class' => 'separate',
          'tab' => 'export',
          'form_group_class' => 'form_group_class_export_sep',
          'is_bool' => true,
          'tab' => 'export',
          'values' => array(
            array(
              'id' => 'separate_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'separate_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('If activated, a line will be created for each attributes of the products'),
        ),
        array(
          'type' => 'checkbox_table',
          'name' => 'products[]',
          'class_block' => 'product_list',
          'label' => $this->l('Filter by product:'),
          'class_input' => 'select_products',
          'lang' => true,
          'tab' => 'filter_products',
          'hint' => '',
          'search' => true,
          'display'=> true,
          'values' => array(
            'query' => $products,
            'id' => 'id_product',
            'name' => 'name',
            'value' => $selected_products
          )
        ),
        array(
          'type'  => 'categories',
          'label' => $this->l('Filter by category'),
          'name'  => 'categories',
          'tab' => 'filter_products',
          'form_group_class' => 'form_group_filter_category',
          'tree'  => array(
            'id'  => 'categories-tree',
            'use_checkbox' => true,
            'use_search' => true,
            'selected_categories' => $selected_categories ? $selected_categories : array()
          ),
        ),
        array(
          'type' => 'checkbox_table',
          'name' => 'manufacturers[]',
          'class_block' => 'manufacturer_list',
          'label' => $this->l('Filter by manufacturer:'),
          'class_input' => 'select_manufacturers',
          'lang' => true,
          'tab' => 'filter_products',
          'hint' => '',
          'search' => true,
          'display'=> true,
          'values' => array(
            'query' => $manufacturers,
            'id' => 'id_manufacturer',
            'name' => 'name',
            'value' => $selected_manufacturers
          )
        ),
        array(
          'type' => 'checkbox_table',
          'name' => 'suppliers[]',
          'class_block' => 'supplier_list',
          'label' => $this->l('Filter by suppliers:'),
          'class_input' => 'select_suppliers',
          'lang' => true,
          'tab' => 'filter_products',
          'hint' => '',
          'search' => true,
          'display'=> true,
          'values' => array(
            'query' => $suppliers,
            'id' => 'id_supplier',
            'name' => 'name',
            'value' => $selected_suppliers
          )
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Only active products'),
          'name' => 'active_products',
          'class' => 'active_products',
          'form_group_class' => 'form_group_class_hide',
          'is_bool' => true,
          'tab' => 'filter_products',
          'values' => array(
            array(
              'id' => 'active_products_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'active_products_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Only inactive products'),
          'name' => 'inactive_products',
          'class' => 'inactive_products',
          'form_group_class' => 'form_group_class_hide',
          'is_bool' => true,
          'tab' => 'filter_products',
          'values' => array(
            array(
              'id' => 'inactive_products_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'inactive_products_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Products with EAN13'),
          'name' => 'ean_products',
          'class' => 'ean_products',
          'form_group_class' => 'form_group_class_hide',
          'is_bool' => true,
          'tab' => 'filter_products',
          'values' => array(
            array(
              'id' => 'ean_products_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'ean_products_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Products with specific prices'),
          'name' => 'specific_prices_products',
          'class' => 'specific_prices_products',
          'form_group_class' => 'form_group_class_hide',
          'is_bool' => true,
          'tab' => 'filter_products',
          'values' => array(
            array(
              'id' => 'specific_prices_products_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'specific_prices_products_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_class_hide mpm-ep-price-filter',
          'tab' => 'filter_products',
          'col' => '12',
          'name' => $this->priceSelection($priceSettings),
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_class_hide mpm-ep-qty-filter',
          'tab' => 'filter_products',
          'col' => '12',
          'name' => $this->quantitySelection($quantitySettings),
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_class_hide mpm-ep-condition-filter',
          'tab' => 'filter_products',
          'col' => '12',
          'name' => $this->conditionBlock($condition),
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_class_hide mpm-ep-visibility-filter',
          'tab' => 'filter_products',
          'col' => '12',
          'name' => $this->visibilityBlock($visibility),
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'exportFields',
          'tab' => 'filter_fields',
          'name' => $this->exportFields(),
        ),
        array(
          'type' => 'hidden',
          'name' => 'id_shop',
        ),
        array(
          'type' => 'hidden',
          'name' => 'last_id',
        ),
        array(
          'type' => 'hidden',
          'name' => 'last_id_update',
        ),
        array(
          'type' => 'hidden',
          'name' => 'base_url',
        ),
        array(
          'type' => 'hidden',
          'name' => 'shopGroupId',
        ),
        array(
          'type' => 'html',
          'tab' => 'automatic_export',
          'name' => '<div class="alert alert-info alert-info-automatic">'.$this->l('Do not forget save settings after automatic export editing') . '</div>',
          'form_group_class' => 'auto_notice',
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Active'),
          'name' => 'automatic',
          'tab' => 'automatic_export',
          'class' => 'automatic',
          'is_bool' => true,
          'values' => array(
            array(
              'id' => 'automatic_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'automatic_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),
        array(
          'type' => 'html',
          'tab' => 'automatic_export',
          'name' => $automaticDescription,
          'form_group_class' => 'auto_description',
        ),
        array(
          'type'  => 'textarea',
          'label' => $this->l('Emails For Products Export Report'),
          'name'  => 'notification_emails',
          'tab' => 'automatic_export',
          'class' => 'notification_emails',
          'hint'  => 'Each email in per line',
          'form_group_class' => 'auto_notif',
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Export not exported'),
          'name' => 'not_exported',
          'class' => 'not_exported',
          'tab' => 'automatic_export',
          'form_group_class' => 'export_not_exported auto_notif',
          'is_bool' => true,
          'values' => array(
            array(
              'id' => 'not_exported_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'not_exported_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),
        array(
          'type' => 'html',
          'tab' => 'new_settings',
          'form_group_class' => 'save_settings_reset_filters',
          'name' => '<div class="url_base_setting"><a href="'.$url_base.'"><i class="icon-refresh process-icon-refresh"></i>'.$this->l('Reset filters').'</a></div>'
        ),
        array(
          'label' => $this->l('Setting name'),
          'type' => 'text',
          'form_group_class' => 'new_settings_form',
          'tab' => 'new_settings',
          'name' => 'save_setting',
        ),
        array(
          'type' => 'html',
          'tab' => 'new_settings',
          'form_group_class' => 'saveSettingsExportButton',
          'name' => '<button type="button" class="btn btn-default saveSettingsExport" style="padding: 4px 30px;font-size: 16px;">'.$this->l('Save').'</button>'
        ),
        array(
          'type' => 'html',
          'tab' => 'new_settings',
          'form_group_class' => 'form_group_settings',
          'name' => $this->listSettings(Tools::getValue('settings'))
        ),
        array(
          'type' => 'html',
          'tab' => 'new_settings',
          'form_group_class' => 'form_group_settings_clear',
          'name' => '<div></div>'
        ),
      ),
    );

    $this->fields_form[1]['form'] = array(
      'input' => array(
        array(
          'type' => 'html',
          'form_group_class' => 'exportButton',
          'name' => '<button type="button" class="btn btn-default export">'.$this->l('Export').'</button>',
        ),
      )
    );

    $helper = new HelperForm();
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->fields_value['last_id'] = $last_id;
    $helper->fields_value['last_id_update'] = $last_id_update;
    $helper->fields_value['id_shop'] = $this->_shopId;
    $helper->fields_value['base_url'] = AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules');
    $helper->fields_value['shopGroupId'] = $this->_shopGroupId;
    $helper->fields_value['id_lang'] = Context::getContext()->language->id;
    $helper->fields_value['search_field'] = '';

    if(Tools::getValue('settings')){
      $config = Configuration::get('GOMAKOIL_LANG_CHECKED_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $type = Configuration::get('GOMAKOIL_TYPE_FILE_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $name_setting = Configuration::get('GOMAKOIL_NAME_SETTING_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $separate = Configuration::get('GOMAKOIL_SEPARATE_SETTING_EX_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $automatic = Configuration::get('GOMAKOIL_PRODUCTS_AUTOMATIC_EXPORT_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $active = Configuration::get('GOMAKOIL_ACTIVE_PRODUCTS_SETTING_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $inactive = Configuration::get('GOMAKOIL_INACTIVE_PRODUCTS_SETTING_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $ean_products = Configuration::get('GOMAKOIL_EAN_PRODUCTS_SETTING_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $specific_prices_products = Configuration::get('GOMAKOIL_SPECIFIC_PRICES_PRODUCTS_SETTING_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $helper->fields_value['delimiter_val'] = Configuration::get('GOMAKOIL_CSV_DELIMITER_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['seperatop_val'] = Configuration::get('GOMAKOIL_CSV_SEPERATOR_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['display_headers'] = Configuration::get('GOMAKOIL_DISPLAY_HEADERS_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['strip_tags'] = Configuration::get('GOMAKOIL_STRIP_TAGS_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['round_value'] = Configuration::get('GOMAKOIL_DESIMAL_POINTS_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['orderby'] = Configuration::get('GOMAKOIL_ORDER_BY_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['orderway'] = Configuration::get('GOMAKOIL_ORDER_WAY_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['not_exported'] = Configuration::get('GOMAKOIL_NOT_EXPORDED_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['decoration_price'] = Configuration::get('GOMAKOIL_DECORATION_PRICE_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['separator_decimal_points'] = Configuration::get('GOMAKOIL_DECIMAL_PRICE_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;

      $helper->fields_value['feed_target'] = Configuration::get('GOMAKOIL_FEED_TARGET_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['ftp_server'] = Configuration::get('GOMAKOIL_FTP_SERVER_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['ftp_user'] = Configuration::get('GOMAKOIL_FTP_USER_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['ftp_password'] = Configuration::get('GOMAKOIL_FTP_PASSWORD_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;
      $helper->fields_value['ftp_folder_path'] = Configuration::get('GOMAKOIL_FTP_FOLDER_PATH_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);;

      $automatic = Tools::unSerialize($automatic);
      $helper->fields_value['automatic'] = $automatic['automatic'];
      $helper->fields_value['notification_emails'] = $automatic['notification_emails'];
      $helper->fields_value['separate'] = $separate;
      $helper->fields_value['save_setting'] = $name_setting;
      $helper->fields_value['id_lang'] = $config;
      $helper->fields_value['currency'] = Configuration::get('GOMAKOIL_CURRENCY_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $helper->fields_value['active_products'] = $active;
      $helper->fields_value['inactive_products'] = $inactive;
      $helper->fields_value['ean_products'] = $ean_products;
      $helper->fields_value['specific_prices_products'] = $specific_prices_products;
      $helper->fields_value['name_file'] = $name;
      $helper->fields_value['name_export_file'] = $show;


      if($type){
        $helper->fields_value['format_file'] = $type;
      }
      else{
        $helper->fields_value['format_file'] = 'xlsx';
      }

    }
    else{
      $helper->fields_value['id_lang'] = Context::getContext()->language->id;
      $helper->fields_value['currency'] = Context::getContext()->currency->id;
      $helper->fields_value['format_file'] = 'xlsx';
      $helper->fields_value['save_setting'] = '';
      $helper->fields_value['separate'] = 0;
      $helper->fields_value['automatic'] = 0;
      $helper->fields_value['active_products'] = 0;
      $helper->fields_value['inactive_products'] = 0;
      $helper->fields_value['ean_products'] = 0;
      $helper->fields_value['specific_prices_products'] = 0;
      $helper->fields_value['notification_emails'] = '';
      $helper->fields_value['name_file'] = '';
      $helper->fields_value['name_export_file'] = 0;
      $helper->fields_value['feed_target'] = 'file_system';
      $helper->fields_value['ftp_server'] = '';
      $helper->fields_value['ftp_user'] = '';
      $helper->fields_value['ftp_password'] = '';
      $helper->fields_value['ftp_folder_path'] = '';
      $helper->fields_value['delimiter_val'] = ',';
      $helper->fields_value['seperatop_val'] = 1;
      $helper->fields_value['display_headers'] = 1;
      $helper->fields_value['orderway'] = 'asc';
      $helper->fields_value['orderby'] = 1;
      $helper->fields_value['round_value'] = '2';
      $helper->fields_value['strip_tags'] = 0;
      $helper->fields_value['not_exported'] = 0;
      $helper->fields_value['decoration_price'] = '[PRICE]';
      $helper->fields_value['separator_decimal_points'] = '.';
    }

    $html = $helper->generateForm($this->fields_form);

    if( Tools::getValue('module_tab') == 'newcronjob' && !Tools::getValue('deleteproductsexport') ){
      $html .= $this->initFormAddTask();
    }

    if( Tools::getValue('statusexportproducts') !== false ){
      $this->_updateTaskStatus();
    }
    if( Tools::getValue('oneshotexportproducts') !== false ){
      $this->_updatetaskOneShot();
    }
    if( Tools::getValue('deleteexportproducts') !== false ){
      $this->_deleteTask();
    }


    if( Tools::isSubmit('add_task') && Tools::getValue('module_tab') == 'schedule_tasks' ){
      if( !$addRes ){
        $html .= $this->initFormAddTask( $this->_errors );
      }
    }

    return $html;
  }

  private function _addTask()
  {
    $values = $this->_getScheduleValues();
    if( !$values['export_settings'] ){
      $this->_errors = $this->l('You must select settings for export!');
      return false;
    }

    $values['id_shop'] = Context::getContext()->shop->id;
    $values['id_shop_group'] = Context::getContext()->shop->id_shop_group;
    $values['active'] = 1;

    if( Tools::getValue('id_task')){
      Db::getInstance(_PS_USE_SQL_SLAVE_)->update('productsexport_tasks', $values, 'id_task='.(int)Tools::getValue('id_task'));
    }
    else{
      Db::getInstance(_PS_USE_SQL_SLAVE_)->insert('productsexport_tasks', $values);
    }

    return true;
  }

  public function initFormWelcome()
  {
    $this->context->controller->addCSS('https://fonts.googleapis.com/css?family=Open+Sans:300,400,600');

    $filePerms = Tools::substr(sprintf('%o', fileperms(_PS_MODULE_DIR_ . 'exportproducts/send.php')), -3);
    $folderPerms = Tools::substr(sprintf('%o', fileperms(_PS_MODULE_DIR_ . 'exportproducts/')), -3);

    $allowUrl = false;
    if( in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) ){
      $allowUrl = true;
    }

    $requirementsOk = false;
    if( $filePerms == 644 && $folderPerms == 755 && $allowUrl == true && class_exists('ZipArchive' ) ){
      $requirementsOk = true;
    }

    $currentVersion = $this->version;
    $lastVersion = Configuration::getGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_VERSION');


    $this->context->smarty->assign(
      array(
        'module_path'           => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/exportproducts/',
        'file_perms'            => $filePerms,
        'folder_perms'          => $folderPerms,
        'products_export_token' => Tools::getAdminTokenLite('AdminProductsExport'),
        'php_zip'               => class_exists('ZipArchive'),
        'max_execution_time'    => ini_get('max_execution_time'),
        'memory_limit'          => ini_get('memory_limit'),
        'allow_url_fopen'       => $allowUrl,
        'requirements_ok'       => $requirementsOk,
        'current_version'       => $currentVersion,
        'last_version'          => $lastVersion,
      )
    );

    return $this->display(__FILE__, 'views/templates/hook/welcome.tpl');
  }

  public function initFormAddTask( $error = false )
  {
    $form = array(
      'form' => array(
        'form' => array(
          'id_form' => 'products_add_task',
          'legend' => array(
            'title' => $this->l('Add cron task'),
            'icon' => 'icon-plus',
          ),
          'error' => '',
          'input' => array(),
          'submit' => array('title' => $this->l('Save'), 'type' => 'submit'),
        ),
      )
    );

    $form['form']['form']['input'][] = array(
      'type' => 'text',
      'name' => 'description',
      'label' => $this->l('Task description'),
      'desc' => $this->l('Enter a description for this task.'),
      'placeholder' => $this->l('My export'),
    );


    $form['form']['form']['input'][] = array(
      'type' => 'select',
      'name' => 'export_settings',
      'label' => $this->l('Export Settings'),
      'desc' => $this->l('Select saved automatically export settings'),
      'options' => array(
        'query' => $this->_getAutomaticSettings(),
        'id' => 'id', 'name' => 'name'
      ),
    );

    $form['form']['form']['input'][] = array(
      'type' => 'select',
      'name' => 'hour',
      'label' => $this->l('Task frequency'),
      'desc' => $this->l('At what time should this task be executed?'),
      'options' => array(
        'query' => $this->_getHoursFormOptions(),
        'id' => 'id', 'name' => 'name'
      ),
    );
    $form['form']['form']['input'][] = array(
      'type' => 'select',
      'name' => 'day',
      'desc' => $this->l('On which day of the month should this task be executed?'),
      'options' => array(
        'query' => $this->_getDaysFormOptions(),
        'id' => 'id', 'name' => 'name'
      ),
    );
    $form['form']['form']['input'][] = array(
      'type' => 'select',
      'name' => 'month',
      'desc' => $this->l('On what month should this task be executed?'),
      'options' => array(
        'query' => $this->_getMonthsFormOptions(),
        'id' => 'id', 'name' => 'name'
      ),
    );
    $form['form']['form']['input'][] = array(
      'type' => 'select',
      'name' => 'day_of_week',
      'desc' => $this->l('On which day of the week should this task be executed?'),
      'options' => array(
        'query' => $this->_getDaysofWeekFormOptions(),
        'id' => 'id', 'name' => 'name'
      ),
    );

    if( $error ){
      $form['form']['form']['error'] = $error;
    }


    $helper = new HelperForm();

    $helper->show_toolbar = false;
    $helper->module = $this;
    $helper->default_form_language = $this->context->language->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
    $helper->submit_action = 'add_task';

    $helper->identifier = $this->identifier;
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
      .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&module_tab=schedule_tasks';
    if( Tools::getValue('id_task') ){
      $helper->currentIndex .= '&id_task='.(int)Tools::getValue('id_task');
    }

    $helper->token = Tools::getAdminTokenLite('AdminModules');

    $helper->tpl_vars['fields_value'] = $this->_getScheduleValues(true);

    return $helper->generateForm($form);
  }

  private function _updateTaskStatus()
  {
    $idTask = (int)Tools::getValue('id_task');
    if( $idTask ){
      Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'productsexport_tasks
            SET `active` = IF (`active`, 0, 1) WHERE `id_task` = \''.(int)$idTask.'\'');

      Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
        .'&token='.Tools::getAdminTokenLite('AdminModules').'&module_tab=schedule_tasks');
    }
  }

  private function _updatetaskOneShot()
  {
    $idTask = (int)Tools::getValue('id_task');
    if( $idTask ){
      Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'productsexport_tasks
            SET `one_shot` = IF (`one_shot`, 0, 1) WHERE `id_task` = \''.(int)$idTask.'\'');

      Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
        .'&token='.Tools::getAdminTokenLite('AdminModules').'&module_tab=schedule_tasks');
    }
  }

  private function _deleteTask()
  {
    $idTask = (int)Tools::getValue('id_task');
    if( $idTask ){
      Db::getInstance()->delete('productsexport_tasks', "id_task=$idTask");
      Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
        .'&token='.Tools::getAdminTokenLite('AdminModules').'&module_tab=schedule_tasks');
    }
  }

  private function _getAutomaticSettings()
  {
    $settings = array();
    $savedSettings = Tools::unserialize(Configuration::get('GOMAKOIL_ALL_SETTINGS',null,Context::getContext()->shop->id_shop_group,Context::getContext()->shop->id));
    $settings[] = array('id' => 0, 'name' => '-');

    if(isset($savedSettings) && $savedSettings){
      foreach( $savedSettings as $value ){
        $automatic_conf = 'GOMAKOIL_PRODUCTS_AUTOMATIC_EXPORT_'.$value;
        $automatic = Configuration::get($automatic_conf,'',$this->_shopGroupId, Context::getContext()->shop->id);
        $automatic = unserialize($automatic);
        if( $automatic['automatic'] ){
          $name_conf = 'GOMAKOIL_NAME_SETTING_'.$value;
          $name =  Configuration::get($name_conf,'',$this->_shopGroupId, Context::getContext()->shop->id);
          $settings[] = array('id' => $value, 'name' => $name);
        }
      }
    }

    return $settings;
  }

  private function _getHoursFormOptions()
  {
    $data = array(array('id' => '-1', 'name' => $this->l('Every hour')));

    for ($hour = 0; $hour < 24; $hour += 1) {
      $data[] = array('id' => $hour, 'name' => date('H:i', mktime($hour, 0, 0, 0, 1)));
    }

    return $data;
  }

  private function _getDaysFormOptions()
  {
    $data = array(array('id' => '-1', 'name' => $this->l('Every day of the month')));

    for ($day = 1; $day <= 31; $day += 1) {
      $data[] = array('id' => $day, 'name' => $day);
    }

    return $data;
  }

  private function _getMonthsFormOptions()
  {
    $data = array(array('id' => '-1', 'name' => $this->l('Every month')));

    for ($month = 1; $month <= 12; $month += 1) {
      $data[] = array('id' => $month, 'name' => $this->l(date('F', mktime(0, 0, 0, $month, 1))));
    }

    return $data;
  }

  private function _getDaysofWeekFormOptions()
  {
    $data = array(array('id' => '-1', 'name' => $this->l('Every day of the week')));

    for ($day = 1; $day <= 7; $day += 1) {
      $data[] = array('id' => $day, 'name' => $this->l(date('l', strtotime('Sunday +' . $day . ' days'))));
    }

    return $data;
  }

  private function _getScheduleValues( $formValues = false )
  {
    if( Tools::getValue('id_task') && $formValues ){
      $sql = '
      SELECT * 
      FROM ' . _DB_PREFIX_ . 'productsexport_tasks as t
      WHERE id_task = "'.(int)Tools::getValue('id_task').'"
    ';

      $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
      return $res[0];
    }
    return array(
      'description' => Tools::safeOutput(Tools::getValue('description', null)),
      'export_settings' => Tools::safeOutput(Tools::getValue('export_settings', 0)),
      'hour' => (int)Tools::getValue('hour', -1),
      'day' => (int)Tools::getValue('day', -1),
      'month' => (int)Tools::getValue('month', -1),
      'day_of_week' => (int)Tools::getValue('day_of_week', -1),
    );
  }

  public function initFormScheduleTasks()
  {
    $html = '';
    $helper = new HelperList();
    $helper->title = $this->l('Cron tasks');
    $helper->table = $this->name;
    $helper->no_link = true;
    $helper->shopLinkType = '';
    $helper->identifier = 'id_task';
    $helper->actions = array('edit', 'delete');

    $values = $this->_getAddedTasks();
    $helper->listTotal = count($values);
    $helper->tpl_vars = array('show_filters' => false);

    $helper->toolbar_btn['new'] = array(
      'href' => $this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
        .'&module_tab=newcronjob&token='.Tools::getAdminTokenLite('AdminModules') . '',
      'desc' => $this->l('Add new task')
    );

    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
      .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&module_tab=newcronjob';
    $helper->fields_value['location_href'] = AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure=exportproducts';

    $token = Configuration::getGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_TASKS_KEY');
    $admin_folder = str_replace(_PS_ROOT_DIR_.'/', null, basename(_PS_ADMIN_DIR_));
    $id_shop = (int)Context::getContext()->shop->id;
    $id_shop_group = (int)Context::getContext()->shop->id_shop_group;

    if (version_compare(_PS_VERSION_, '1.7', '<') == true) {
      $path = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.$admin_folder.'/';
      $schedule_url = $path.Context::getContext()->link->getAdminLink('AdminProductsExport', false);
      $schedule_url .= '&id_shop='.$id_shop.'&id_shop_group='.$id_shop_group.'&secure_key='.$token;
    } else {
      $schedule_url = Context::getContext()->link->getAdminLink('AdminProductsExport', false);
      $schedule_url .= '&id_shop='.$id_shop.'&id_shop_group='.$id_shop_group.'&secure_key='.$token;
    }

    $scheduleTab = false;
    if( Tools::getValue('module_tab') == 'schedule_tasks' || Tools::getValue('module_tab') == 'newcronjob' ){
      $scheduleTab = true;
    }

    $this->context->smarty->assign(
      array(
        'schedule_url'  => $schedule_url,
        'schedule_tab'  => $scheduleTab
      )
    );

    $html .= $this->display(__FILE__, 'views/templates/hook/config.tpl');
    //
    $html .=  $helper->generateList($values, $this->getTasksList());

    return $html;
  }

  private function _getAddedTasks()
  {
    $id_shop = (int)Context::getContext()->shop->id;
    $id_shop_group = (int)Context::getContext()->shop->id_shop_group;

    $sql = '
      SELECT * 
      FROM ' . _DB_PREFIX_ . 'productsexport_tasks as t
      WHERE id_shop = ' . $id_shop . '
      AND id_shop_group = ' . $id_shop_group . '
    ';

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if( $res ){
      foreach( $res as $key => &$task ){
        $task['hour'] = ($task['hour'] == -1) ? $this->l('Every hour') : date('H:i', mktime((int)$task['hour'], 0, 0, 0, 1));
        $task['day'] = ($task['day'] == -1) ? $this->l('Every day') : (int)$task['day'];
        $task['month'] = ($task['month'] == -1) ? $this->l('Every month') : $this->l(date('F', mktime(0, 0, 0, (int)$task['month'], 1)));
        $task['day_of_week'] = ($task['day_of_week'] == -1) ? $this->l('Every day of the week') : $this->l(date('l', strtotime('Sunday +' . $task['day_of_week'] . ' days')));
        $task['last_start'] = ($task['last_start'] == 0) ? $this->l('Never') : date('Y-m-d H:i:s', ($task['last_start']));
        $task['last_finish'] = ($task['last_finish'] == 0) ? $this->l('') : date('Y-m-d H:i:s', ($task['last_finish']));
        $task['progress'] = $task['progress'] ? $task['progress'] : '';
        $task['one_shot'] = (bool)$task['one_shot'];
        $task['active'] = (bool)$task['active'];

        $name_conf = 'GOMAKOIL_NAME_SETTING_'.$task['export_settings'];
        $name =  Configuration::get($name_conf,'',$this->_shopGroupId, Context::getContext()->shop->id);
        $task['export_settings'] = $name;
      }
    }
    return $res;
  }

  public function getTasksList()
  {
    return array(
      'description' => array('title' => $this->l('Task description'), 'type' => 'text', 'orderby' => false),
      'export_settings' => array('title' => $this->l('Export Setting'), 'type' => 'text', 'orderby' => false),
      'hour' => array('title' => $this->l('Hour'), 'type' => 'text', 'orderby' => false),
      'day' => array('title' => $this->l('Day'), 'type' => 'text', 'orderby' => false),
      'month' => array('title' => $this->l('Month'), 'type' => 'text', 'orderby' => false),
      'day_of_week' => array('title' => $this->l('Day of week'), 'type' => 'text', 'orderby' => false),
      'last_start' => array('title' => $this->l('Last export start'), 'type' => 'text', 'orderby' => false),
      'last_finish' => array('title' => $this->l('Last export finish'), 'type' => 'text', 'orderby' => false),
      'progress' => array('title' => $this->l('Import progress'), 'type' => 'text', 'orderby' => false),
      'one_shot' => array('title' => $this->l('One shot'), 'active' => 'oneshot', 'type' => 'bool', 'align' => 'center'),
      'active' => array('title' => $this->l('Active'), 'active' => 'status', 'type' => 'bool', 'align' => 'center', 'orderby' => false),
    );
  }

  public function visibilityBlock($settings){
    $this->context->smarty->assign(
      array(
        'settings'   => $settings,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/blockVisibility.tpl');
  }

  public function conditionBlock($settings){
    $this->context->smarty->assign(
      array(
        'settings'   => $settings,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/blockCondition.tpl');
  }

  public function priceSelection($priceSettings){
    $this->context->smarty->assign(
      array(
        'priceSettings'   => $priceSettings,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/blockSelectionPrice.tpl');
  }

  public function quantitySelection($quantitySettings){

    $this->context->smarty->assign(
      array(
        'quantitySettings'   => $quantitySettings,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/blockSelectionQuantity.tpl');
  }

  public function exportFields()
  {
    $saved_setting = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));
    $edited_xml_names = Tools::unserialize(Configuration::get('GOMAKOIL_EDITED_XML_NAMES','',$this->_shopGroupId, Context::getContext()->shop->id));
    $extra_fields  = Tools::unserialize(Configuration::get('GOMAKOIL_EXTRA_FIELDS','',$this->_shopGroupId, Context::getContext()->shop->id));

    $saved_field_names = array();
    $saved_field_ids = array();
    $selected = array();

    if($saved_setting){
      $all_fields_merged = array_merge($this->_exportTabInformation, $this->_exportTabPrices, $this->_exportTabSeo, $this->_exportTabAssociations, $this->_exportTabShipping, $this->_exportTabCombinations, $this->_exportTabQuantities, $this->_exportTabImages, $this->_exportTabFeatures, $this->_exportTabCustomization, $this->_exportTabAttachments, $this->_exportTabSuppliers);

      foreach($saved_setting as $field_id => $field_name) {
        foreach ($all_fields_merged as $field) {
          $xml_head = '';
          $is_edited_xml_name = false;

          if ($field_id == $field['val']) {
            if (!empty($edited_xml_names[$field['val']])) {
                $xml_head = $edited_xml_names[$field['val']];
                $is_edited_xml_name = true;
            } elseif (isset($field['xml_head'])) {
                $xml_head = $field['xml_head'];
            }

            $selected[$field['val']] = array('name' => $saved_setting[$field_id],
                                             'tab' => $field['tab'],
                                             'is_extra' => false,
                                             'hint' => isset($field['hint']) ? $field['hint'] : '',
                                             'default_value' => false,
                                             'xml_head' => $xml_head,
                                             'is_edited_xml_name' => $is_edited_xml_name
                                        );

            array_push($saved_field_ids, $field['val']);
          } else if (preg_match('/^extra_field_\d+$/', $field_id)) {

            if (!empty($extra_fields[$field_id])) {
              $xml_head = $extra_fields[$field_id]['name'];
              $is_edited_xml_name = true;
            }

            $selected[$field_id] = array('name' => $saved_setting[$field_id],
                                         'tab' => 'exportTabOrdersData',
                                         'is_extra' => true,
                                         'hint' => isset($field['hint']) ? $field['hint'] : '',
                                         'default_value' => $extra_fields[$field_id]['value'],
                                         'xml_head' => $xml_head,
                                         'is_edited_xml_name' => $is_edited_xml_name
                                        );

            array_push($saved_field_ids, $field_id);
          }
        }
      }

    }

    $all_fields = array(
      'exportTabInformation'    => $this->_exportTabInformation,
      'exportTabPrices'         => $this->_exportTabPrices,
      'exportTabSeo'            => $this->_exportTabSeo,
      'exportTabAssociations'   => $this->_exportTabAssociations,
      'exportTabShipping'       => $this->_exportTabShipping,
      'exportTabCombinations'   => $this->_exportTabCombinations,
      'exportTabQuantities'     => $this->_exportTabQuantities,
      'exportTabImages'         => $this->_exportTabImages,
      'exportTabFeatures'       => $this->_exportTabFeatures,
      'exportTabCustomization'  => $this->_exportTabCustomization,
      'exportTabAttachments'    => $this->_exportTabAttachments,
      'exportTabSuppliers'      => $this->_exportTabSuppliers,
    );

    $this->context->smarty->assign(
      array(
        'url_base'              => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure=ordersexport',
        'saved_field_names'     => $saved_field_names,
        'saved_field_ids'       => $saved_field_ids,
        'selected'              => $selected,
        'all_fields'            => $all_fields,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/selectFieldsExport.tpl');
  }

  public function searchProducts($search,$id_shop, $id_lang)
  {
    $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
    $products = $this->_model->searchProduct($id_shop, $id_lang, $search);
    $products_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    $this->context->smarty->assign(
      array(
        'data'        => $products,
        'items_check' => $products_check,
        'name'        => 'products[]',
        'id'          => 'id_product',
        'title'       => 'name',
        'class'       => 'select_products'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function searchManufacturers($search)
  {
    $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED';
    $items = $this->_model->searchManufacturer($search);
    $items_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'manufacturers[]',
        'id'          => 'id_manufacturer',
        'title'       => 'name',
        'class'       => 'select_manufacturers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function searchSuppliers($search)
  {
    $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED';
    $items = $this->_model->searchSupplier($search);
    $items_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'suppliers[]',
        'id'          => 'id_supplier',
        'title'       => 'name',
        'class'       => 'select_suppliers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showCheckedProducts($id_shop, $id_lang)
  {
    $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
    $products_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    if( !$products_check ){
      $products_check = "";
    }
    $products = $this->_model->showCheckedProducts($id_shop, $id_lang, $products_check);
    $this->context->smarty->assign(
      array(
        'data'        => $products,
        'items_check' => $products_check,
        'name'        => 'products[]',
        'id'          => 'id_product',
        'title'       => 'name',
        'class'       => 'select_products'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showCheckedManufacturers()
  {
    $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED';
    $items_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    if( !$items_check ){
      $items_check = "";
    }
    $items = $this->_model->showCheckedManufacturers($items_check);
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'manufacturers[]',
        'id'          => 'id_manufacturer',
        'title'       => 'name',
        'class'       => 'select_manufacturers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showCheckedSuppliers()
  {
    $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED';
    $items_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    if( !$items_check ){
      $items_check = "";
    }
    $items = $this->_model->showCheckedSuppliers($items_check);
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'suppliers[]',
        'id'          => 'id_supplier',
        'title'       => 'name',
        'class'       => 'select_suppliers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showAllProducts($id_shop, $id_lang)
  {
    $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
    $products_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    $products = $this->_model->showCheckedProducts($id_shop, $id_lang, false);
    $this->context->smarty->assign(
      array(
        'data'        => $products,
        'items_check' => $products_check,
        'name'        => 'products[]',
        'id'          => 'id_product',
        'title'       => 'name',
        'class'       => 'select_products'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showAllManufacturers()
  {
    $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED';
    $items_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    if( !$items_check ){
      $items_check = "";
    }
    $items = $this->_model->showCheckedManufacturers(false);
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'manufacturers[]',
        'id'          => 'id_manufacturer',
        'title'       => 'name',
        'class'       => 'select_manufacturers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showAllSuppliers()
  {
    $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED';
    $items_check = Tools::unserialize(Configuration::get($name_config,'',$this->_shopGroupId, Context::getContext()->shop->id));
    if( !$items_check ){
      $items_check = "";
    }
    $items = $this->_model->showCheckedSuppliers(false);
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'suppliers[]',
        'id'          => 'id_supplier',
        'title'       => 'name',
        'class'       => 'select_suppliers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }
}
