<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is an object model class used to manage import rules
 */
class ElegantalEasyImportClass extends ElegantalEasyImportObjectModel
{

    public $tableName = 'elegantaleasyimport';
    public static $definition = array(
        'table' => 'elegantaleasyimport',
        'primary' => 'id_elegantaleasyimport',
        'multishop' => true,
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'size' => 255, 'validate' => 'isString', 'required' => true),
            'entity' => array('type' => self::TYPE_STRING, 'size' => 25, 'validate' => 'isString', 'required' => true),
            'lang_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'supplier_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'map' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'map_default_values' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'header_row' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'import_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'csv_file' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'csv_path' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'csv_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'csv_url_username' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'csv_url_password' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'csv_url_method' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'csv_url_post_params' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'ftp_host' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'ftp_port' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'ftp_username' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'ftp_password' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'ftp_file' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'is_cron' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'cron_csv_file_size' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'cron_csv_file_md5' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'product_limit_per_request' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'product_range_to_import' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'email_to_send_notification' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'find_products_by' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'create_new_products' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'update_existing_products' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'update_products_on_all_shops' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'price_modifier' => array('type' => self::TYPE_STRING),
            'min_price_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'multiple_value_separator' => array('type' => self::TYPE_STRING),
            'multiple_subcategory_separator' => array('type' => self::TYPE_STRING),
            'is_associate_all_subcategories' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'is_first_parent_root_for_categories' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'decimal_char' => array('type' => self::TYPE_STRING),
            'shipping_package_size_unit' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'shipping_package_weight_unit' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'base_url_images' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'delete_old_combinations' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'replicate_all_languages' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'enable_new_products_by_default' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'enable_if_have_stock' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'disable_if_no_stock' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'enable_all_products_found_in_csv' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'disable_all_products_not_found_in_csv' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'put_zero_qty_for_products_not_found_in_csv' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'is_utf8_encode' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'last_import_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'error_log' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if ($this->last_import_date == '0000-00-00 00:00:00') {
            $this->last_import_date = null;
        }
        if (!$this->id) {
            $this->is_first_parent_root_for_categories = 1;
        }
        if (method_exists('Shop', 'addTableAssociation')) {
            Shop::addTableAssociation($this->tableName, array('type' => 'shop'));
        }
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
