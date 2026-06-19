<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

require_once('initialize.php');

/**
 * Main class of the module
 */
class ElegantalEasyImport extends ElegantalEasyImportModule
{

    /**
     * ID of this module as product on addons
     * @var int
     */
    protected $productIdOnAddons = 24523;

    /**
     * List of hooks to register
     * @var array
     */
    protected $hooksToRegister = array(
        'displayBackOfficeHeader'
    );

    /**
     * List of tabs (menu) to add during installation
     * @var array
     */
    protected $tabsToAdd = array(
        array(
            'name' => 'Easy Import Products',
            'class' => 'AdminElegantalEasyImport',
            'icon' => 'repeat'
        )
    );

    /**
     * Current model object being edited on back-office
     */
    public $model = null;

    /**
     * List of module settings to be saved as Configuration record
     * @var array
     */
    protected $settings = array(
        'product_ids_to_exclude_from_deactivation' => '',
        'employee_id_for_logging_product_events' => '',
        'text_quantity_dictionary' => '',
        'text_categories_dictionary' => '',
        'text_features_dictionary' => '',
        'text_feature_values_dictionary' => '',
        'security_token_key' => '',
        'is_debug_mode' => 0,
    );

    /**
     * Default map of fields for products
     * @var array
     */
    protected $defaultMapProducts = array(
        'id_reference' => 0,
        'reference' => -1,
        'name' => -1,
        'enabled' => -1,
        'ean_no' => -1,
        'upc_barcode' => -1,
        'isbn' => -1,
        'meta_title' => -1,
        'meta_description' => -1,
        'meta_keywords' => -1,
        'friendly_url' => -1,
        'short_description' => -1,
        'long_description' => -1,
        'wholesale_price' => -1,
        'tax_rules_group' => -1,
        'price_tax_excluded' => -1,
        'price_tax_included' => -1,
        'unit_price' => -1,
        'unity' => -1,
        'delete_existing_discount' => -1,
        'discounted_price' => -1,
        'discount_amount' => -1,
        'discount_percent' => -1,
        'discount_from' => -1,
        'discount_to' => -1,
        'discount_tax_included' => -1,
        'discount_base_price' => -1,
        'discount_starting_unit' => -1,
        'discount_customer_group' => -1,
        'discount_country' => -1,
        'discount_currency' => -1,
        'ecotax' => -1,
        'advanced_stock_management' => -1,
        'depends_on_stock' => -1,
        'warehouse_id' => -1,
        'location_in_warehouse' => -1,
        'quantity' => -1,
        'minimal_quantity' => -1,
        'action_when_out_of_stock' => -1,
        'text_when_in_stock' => -1,
        'text_when_backordering' => -1,
        'availability_date' => -1,
        'categories' => -1,
        'category_1' => -1,
        'category_2' => -1,
        'category_3' => -1,
        'category_4' => -1,
        'category_5' => -1,
        'category_6' => -1,
        'default_category' => -1,
        'delete_existing_images' => -1,
        'product_images' => -1,
        'image_1' => -1,
        'image_2' => -1,
        'image_3' => -1,
        'image_4' => -1,
        'image_5' => -1,
        'image_6' => -1,
        'image_7' => -1,
        'image_8' => -1,
        'image_9' => -1,
        'image_10' => -1,
        'image_captions' => -1,
        'delete_existing_features' => -1,
        'features' => -1,
        'feature_1' => -1,
        'feature_2' => -1,
        'feature_3' => -1,
        'feature_4' => -1,
        'feature_5' => -1,
        'feature_6' => -1,
        'feature_7' => -1,
        'feature_8' => -1,
        'feature_9' => -1,
        'feature_10' => -1,
        'tags' => -1,
        'accessories' => -1,
        'delete_existing_attachments' => -1,
        'attachments' => -1,
        'carriers' => -1,
        'manufacturer' => -1,
        'supplier' => -1,
        'supplier_reference' => -1,
        'supplier_price' => -1,
        'package_width' => -1,
        'package_height' => -1,
        'package_depth' => -1,
        'package_weight' => -1,
        'additional_shipping_cost' => -1,
        'additional_delivery_times' => -1,
        'delivery_in_stock' => -1,
        'delivery_out_stock' => -1,
        'available_for_order' => -1,
        'show_price' => -1,
        'on_sale' => -1,
        'condition' => -1,
        'customizable' => -1,
        'delete_existing_customize_fields' => -1,
        'uploadable_files' => -1,
        'uploadable_files_labels' => -1,
        'text_fields' => -1,
        'text_fields_labels' => -1,
        'visibility' => -1,
        'delete_product' => -1,
    );

    /**
     * Default map of fields for combinations
     * @var array
     */
    protected $defaultMapCombinations = array(
        'id_reference' => 0,
        'attribute_names' => 1,
        'attribute_values' => 2,
        'attribute_1' => -1,
        'attribute_2' => -1,
        'attribute_3' => -1,
        'attribute_4' => -1,
        'attribute_5' => -1,
        'attribute_6' => -1,
        'combination_reference' => -1,
        'combination_id' => -1,
        'advanced_stock_management' => -1,
        'depends_on_stock' => -1,
        'warehouse_id' => -1,
        'location_in_warehouse' => -1,
        'quantity' => -1,
        'minimal_quantity' => -1,
        'wholesale_price' => -1,
        'impact_on_price' => -1,
        'impact_on_weight' => -1,
        'impact_on_unit_price' => -1,
        'images' => -1,
        'image_captions' => -1,
        'supplier_reference' => -1,
        'supplier_price' => -1,
        'ean_no' => -1,
        'upc' => -1,
        'ecotax' => -1,
        'default' => -1,
        'available_date' => -1,
        'delete_existing_discount' => -1,
        'discount_amount' => -1,
        'discount_percent' => -1,
        'discount_from' => -1,
        'discount_to' => -1,
        'discount_tax_included' => -1,
        'discount_base_price' => -1,
        'discount_starting_unit' => -1,
        'discount_customer_group' => -1,
        'discount_country' => -1,
        'discount_currency' => -1,
    );

    /**
     * List of allowed file types for import
     * @var array
     */
    protected $allowedFileTypes = array('csv', 'xls', 'xlsx', 'xml', 'json', 'txt');

    /**
     * Variables to cache data
     * @var array
     */
    protected $categories = array();
    protected $manufacturers = array();
    protected $suppliers = array();
    protected $carriers = array();
    protected $quantity_dictionary = array();
    protected $categories_dictionary = array();
    protected $features_dictionary = array();
    protected $feature_values_dictionary = array();

    /**
     * Import Types
     * @var int
     */
    public static $IMPORT_TYPE_UPLOAD = 1;
    public static $IMPORT_TYPE_PATH = 2;
    public static $IMPORT_TYPE_URL = 3;
    public static $IMPORT_TYPE_FTP = 4;
    public static $IMPORT_TYPE_SFTP = 5;

    /**
     * Constructor method called on each newly-created object
     */
    public function __construct()
    {
        $this->name = 'elegantaleasyimport';
        $this->tab = 'administration';
        $this->version = '7.2.2';
        $this->author = 'ELEGANTAL';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '2429d8c323f7c699758b4ced91d7f5e7';

        parent::__construct();

        $this->displayName = $this->l('Easy Import Products From CSV, EXCEL, XML, JSON, TXT Files');
        $this->description = $this->l('Import/Export products and combinations easily with just a few clicks. You can automate this process by using CRON Job and import product data from directory path, HTTP or FTP location.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (Module::isInstalled('fsproductvideo')) {
            $this->defaultMapProducts['fsproductvideo_url'] = -1;
        }
        if (Module::isInstalled('additionalproductsorder')) {
            $this->defaultMapProducts['additionalproductsorder_ids'] = -1;
        }
        if (Module::isInstalled('jmarketplace')) {
            $this->defaultMapProducts['jmarketplace_seller_id'] = -1;
        }
        if (Module::isInstalled('productaffiliate')) {
            $this->defaultMapProducts['productaffiliate_button_text'] = -1;
            $this->defaultMapProducts['productaffiliate_external_shop_url'] = -1;
        }
        if (Module::isInstalled('advancedcustomfields')) {
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "advanced_custom_fields` WHERE `location` = 'product'";
            $acfs = Db::getInstance()->executeS($sql);
            if ($acfs && is_array($acfs)) {
                foreach ($acfs as $acf) {
                    $this->defaultMapProducts['acf_' . $acf['technical_name']] = -1;
                }
            }
        }
    }

    /**
     * This function plays controller role for the back-office page of the module
     * @return string HTML
     */
    public function getContent()
    {
        $this->setTimeLimit();

        if (_PS_VERSION_ < '1.6') {
            $this->context->controller->addCSS($this->_path . 'views/css/elegantaleasyimport-bootstrap.css', 'all');
            $this->context->controller->addCSS($this->_path . 'views/css/font-awesome.css', 'all');

            if (!in_array(Tools::getValue('event'), array('settings', 'importEdit', 'exportEdit', 'importMapping', 'exportColumns', 'manageCategory'))) {
                $this->context->controller->addJS($this->_path . 'views/js/jquery-1.11.0.min.js');
                $this->context->controller->addJS($this->_path . 'views/js/bootstrap.js');
            }
        }

        $this->context->controller->addCSS($this->_path . 'views/css/elegantaleasyimport-back15.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/elegantaleasyimport-back15.js');

        $this->initModel();

        $html = $this->getRedirectAlerts();

        try {
            if ($event = Tools::getValue('event')) {
                switch ($event) {
                    case 'settings':
                        $html .= $this->settings();
                        break;
                    case 'importEdit':
                        $html .= $this->importEdit();
                        break;
                    case 'importMapping':
                        $html .= $this->importMapping();
                        break;
                    case 'manageCategory':
                        $html .= $this->manageCategory();
                        break;
                    case 'import':
                        $html .= $this->import();
                        break;
                    case 'selectHeaderRow':
                        $html .= $this->selectHeaderRow();
                        break;
                    case 'importCronInfo':
                        $html .= $this->importCronInfo();
                        break;
                    case 'triggerCron':
                        $html .= $this->triggerCron();
                        break;
                    case 'importChangeStatus':
                        $html .= $this->importChangeStatus();
                        break;
                    case 'importErrorLog':
                        $html .= $this->importErrorLog();
                        break;
                    case 'importClearErrorLog':
                        $html .= $this->importClearErrorLog();
                        break;
                    case 'importDuplicate':
                        $html .= $this->importDuplicate();
                        break;
                    case 'importDelete':
                        $html .= $this->importDelete();
                        break;
                    case 'exportList':
                        $html .= $this->exportList();
                        break;
                    case 'exportEdit':
                        $html .= $this->exportEdit();
                        break;
                    case 'exportColumns':
                        $html .= $this->exportColumns();
                        break;
                    case 'export':
                        $html .= $this->export();
                        break;
                    case 'exportChangeStatus':
                        $html .= $this->exportChangeStatus();
                        break;
                    case 'exportDuplicate':
                        $html .= $this->exportDuplicate();
                        break;
                    case 'exportDelete':
                        $html .= $this->exportDelete();
                        break;
                    case 'exportCronInfo':
                        $html .= $this->exportCronInfo();
                        break;
                    default:
                        $html .= $this->importList();
                        break;
                }
            } else {
                $html .= $this->importList();
            }
        } catch (Exception $e) {
            $this->setRedirectAlert($e->getMessage(), 'error');
            $this->redirectAdmin();
        }

        return $html;
    }

    /**
     * Add CSS to Admin Controller to display icon next to menu item
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (_PS_VERSION_ < '1.7') {
            $this->context->controller->addCSS($this->_path . 'views/css/elegantaleasyimport-back-menu.css', 'all');
        }
    }

    /**
     * Initializes current model object and its attributes
     */
    public function initModel($model_id = null)
    {
        $model_id = Tools::getValue('id_elegantaleasyimport', $model_id);
        if ($model_id) {
            $model = new ElegantalEasyImportClass($model_id);
            if (Validate::isLoadedObject($model)) {
                $this->model = $model;
            }
        }
    }

    /**
     * Renders initial page of module for import rule list
     * @return string HTML
     */
    protected function importList()
    {
        // Pagination data
        $total = ElegantalEasyImportClass::model()->countAll();
        $limit = 20;
        $pages = ceil($total / $limit);
        $currentPage = (int) Tools::getValue('page', 1);
        $currentPage = ($currentPage > $pages) ? $pages : $currentPage;
        $halfVisibleLinks = 5;
        $offset = ($total > $limit) ? ($currentPage - 1) * $limit : 0;

        // Sorting records
        $sortableColumns = array(
            'name',
            'is_cron',
            'last_import_date',
            'active',
        );

        $orderBy = in_array(Tools::getValue('orderBy'), $sortableColumns) ? Tools::getValue('orderBy') : 'id_elegantaleasyimport';
        $orderType = Tools::getValue('orderType') == 'asc' ? 'asc' : 'desc';

        $models = ElegantalEasyImportClass::model()->findAll(array(
            'order' => $orderBy . ' ' . $orderType,
            'limit' => $limit,
            'offset' => $offset,
        ));

        foreach ($models as &$model) {
            try {
                if ($model['last_import_date'] == '0000-00-00 00:00:00') {
                    $model['last_import_date'] = null;
                }
                if ($model['is_cron']) {
                    // Get total csv rows
                    $file = ElegantalEasyImportTools::getRealPath($model['csv_file']);
                    $model['total_rows'] = $this->getTotalCsvRows($file);
                    // Get remaining csv rows
                    $model['remaining_rows'] = ElegantalEasyImportCsv::model()->countAll(array(
                        'condition' => array(
                            'id_elegantaleasyimport' => $model['id_elegantaleasyimport'],
                        )
                    ));
                    // Get how many percent is finished
                    if ($model['total_rows'] > 0) {
                        $model['finished_percent'] = (int) ((($model['total_rows'] - $model['remaining_rows']) * 100) / $model['total_rows']);
                    } else {
                        $model['finished_percent'] = 100;
                    }
                }
            } catch (Exception $e) {
                // Do nothing
            }
        }

        $this->context->smarty->assign(
            array(
                'models' => $models,
                'adminUrl' => $this->getAdminUrl(),
                'version' => $this->version,
                'documentationUrls' => $this->getDocumentationUrls(),
                'contactDeveloperUrl' => $this->getContactDeveloperUrl(),
                'rateModuleUrl' => $this->getRateModuleUrl(),
                'pages' => $pages,
                'currentPage' => $currentPage,
                'halfVisibleLinks' => $halfVisibleLinks,
                'orderBy' => $orderBy,
                'orderType' => $orderType,
                'security_token_key' => $this->getSetting('security_token_key'),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/import_list.tpl');
    }

    protected function importRenderSteps($step)
    {
        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'model' => $this->model->getAttributes(),
                'step' => $step,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/import_steps.tpl');
    }

    protected function importEdit()
    {
        $html = "";

        if (Shop::getContext() == Shop::CONTEXT_ALL) {
            $html .= $this->displayWarning($this->l('Warning! You are about to import products to ALL SHOPS. If you intend to import to a particular shop only, please select that shop on top of control panel first.'));
        }

        if (!$this->model) {
            $this->model = new ElegantalEasyImportClass();
            $this->model->header_row = 1;
        }

        if ($this->isPostRequest()) {
            // Validate submitted data
            $errors = $this->model->validateAndAssignModelAttributes();

            if ($this->model->is_cron && $this->model->import_type == self::$IMPORT_TYPE_UPLOAD) {
                $errors[] = $this->l('You cannot use File Upload method for CRON Job.');
            }
            if ($this->model->email_to_send_notification && !Validate::isEmail($this->model->email_to_send_notification) && !Validate::isAbsoluteUrl($this->model->email_to_send_notification)) {
                $errors[] = $this->l('Email to send notification should be either valid email or valid URL.');
            }

            if ($this->model->product_limit_per_request < 1 || $this->model->product_limit_per_request > 10000) {
                $this->model->product_limit_per_request = $this->model->is_cron ? 50 : 5;
            }
            if ($this->model->product_range_to_import) {
                $this->model->product_range_to_import = str_replace(" ", "", $this->model->product_range_to_import);
                if (preg_match("/^(\d+)-(\d+)$/", $this->model->product_range_to_import, $match)) {
                    if ($match[1] > $match[2]) {
                        $this->model->product_range_to_import = "";
                    }
                } else {
                    $this->model->product_range_to_import = "";
                }
            }

            if (empty($errors) && Tools::isSubmit('submitAndNext')) {
                try {
                    $this->downloadImportFile();
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (empty($errors)) {
                $result = empty($this->model->id) ? $this->model->add() : $this->model->update();
                if ($result) {
                    if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                        $this->setRedirectAlert($this->l('Rule saved successfully.'), 'success');
                        $this->redirectAdmin(array(
                            'event' => 'importEdit',
                            'id_elegantaleasyimport' => $this->model->id
                        ));
                    } else {
                        $this->redirectAdmin(array(
                            'event' => 'importMapping',
                            'id_elegantaleasyimport' => $this->model->id,
                        ));
                    }
                } else {
                    $html .= $this->displayError($this->l('Rule could not be saved.') . ' ' . Db::getInstance()->getMsgError());
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = $this->model->getAttributes();

        // Default Values
        if (!$fields_value['id_elegantaleasyimport'] && !$this->isPostRequest()) {
            $fields_value['lang_id'] = (int) Configuration::get('PS_LANG_DEFAULT');
            $fields_value['is_cron'] = 0;
            $fields_value['product_limit_per_request'] = 5;
            $fields_value['find_products_by'] = 'reference';
            $fields_value['create_new_products'] = 1;
            $fields_value['update_existing_products'] = 1;
            $fields_value['update_products_on_all_shops'] = 0;
            $fields_value['decimal_char'] = '.';
            $fields_value['multiple_value_separator'] = '|';
            $fields_value['shipping_package_size_unit'] = 'cm';
            $fields_value['shipping_package_weight_unit'] = 'kg';
            $fields_value['delete_old_combinations'] = 0;
            $fields_value['replicate_all_languages'] = 0;
            $fields_value['enable_new_products_by_default'] = 1;
            $fields_value['enable_if_have_stock'] = 0;
            $fields_value['disable_if_no_stock'] = 0;
            $fields_value['enable_all_products_found_in_csv'] = 0;
            $fields_value['disable_all_products_not_found_in_csv'] = 0;
            $fields_value['put_zero_qty_for_products_not_found_in_csv'] = 0;
            $fields_value['is_utf8_encode'] = 0;
            $fields_value['active'] = 1;
        }

        // Language input
        $languages = $this->getLanguagesForSelect();
        if ($languages && is_array($languages) && count($languages) > 1) {
            $language_input = array(
                'type' => 'select',
                'label' => $this->l('Language'),
                'name' => 'lang_id',
                'options' => array(
                    'query' => $languages,
                    'id' => 'key',
                    'name' => 'value'
                ),
                'desc' => $this->l('Select a language to use for the import'),
            );
        } else {
            $language_input = array(
                'type' => 'hidden',
                'name' => 'lang_id'
            );
        }

        // Update multishop input
        if (Shop::isFeatureActive()) {
            $update_products_on_all_shops_input = array(
                'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                'label' => $this->l('Update products on all shops'),
                'name' => 'update_products_on_all_shops',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'update_products_on_all_shops_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'update_products_on_all_shops_off',
                        'value' => 0,
                        'label' => $this->l('No')
                    )
                ),
                'desc' => $this->l('Products that exist in multiple shops will be updated even if a particular shop is selected'),
            );
        } else {
            $update_products_on_all_shops_input = array(
                'type' => 'hidden',
                'name' => 'update_products_on_all_shops'
            );
        }

        $shipping_units = array();
        if ($ps_weight_unit = Configuration::get('PS_WEIGHT_UNIT')) {
            $shipping_units[] = array('key' => $ps_weight_unit, 'value' => $ps_weight_unit);
            if ($ps_weight_unit == 'kg') {
                $shipping_units[] = array('key' => 'g', 'value' => 'g');
            }
        } else {
            $shipping_units[] = array('key' => 'kg', 'value' => 'kg');
            $shipping_units[] = array('key' => 'g', 'value' => 'g');
        }


        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Step') . ' 1: ' . $this->l('File Upload'),
                    'icon' => 'icon-cloud-upload'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'name' => 'name',
                        'desc' => $this->l('Name is for your reference only'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Import Entity'),
                        'name' => 'entity',
                        'options' => array(
                            'query' => array(
                                array('key' => 'product', 'value' => $this->l('Products')),
                                array('key' => 'combination', 'value' => $this->l('Combinations')),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Choose what kind of entity you would like to import'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Import Method'),
                        'name' => 'import_type',
                        'options' => array(
                            'query' => array(
                                array('key' => self::$IMPORT_TYPE_UPLOAD, 'value' => $this->l('File Upload')),
                                array('key' => self::$IMPORT_TYPE_PATH, 'value' => $this->l('File Path')),
                                array('key' => self::$IMPORT_TYPE_URL, 'value' => $this->l('File From URL')),
                                array('key' => self::$IMPORT_TYPE_FTP, 'value' => $this->l('File From FTP')),
                                array('key' => self::$IMPORT_TYPE_SFTP, 'value' => $this->l('File From SFTP')),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Choose what method you want to use for this import'),
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Upload Import File'),
                        'name' => 'csv_file_upload',
                        'desc' => $this->l('Upload file from your computer.') . ' ' . $this->l('Supported file formats:') . ' ' . implode(', ', $this->allowedFileTypes),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Full Path to Import File'),
                        'name' => 'csv_path',
                        'desc' => $this->l('Enter absolute path to the file on your server.') . ' ' . $this->l('Supported file formats:') . ' ' . implode(', ', $this->allowedFileTypes) . '. ' . $this->l('For example') . ': ' . realpath(dirname(__FILE__) . '/../..') . '/products.csv ' . $this->l('NOTE: You can use this method only if your file is located on the same server as your shop.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('URL to Import File'),
                        'name' => 'csv_url',
                        'desc' => $this->l('Enter HTTP or HTTPS URL to your file.') . ' ' . $this->l('Supported file formats:') . ' ' . implode(', ', $this->allowedFileTypes) . '. ' . $this->l('For example') . ': http://example.com/filename.csv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('HTTP Username'),
                        'name' => 'csv_url_username',
                        'desc' => $this->l('If your file is password protected, enter username for authentication.'),
                    ),
                    array(
                        'type' => 'elegantalpassword',
                        'label' => $this->l('HTTP Password'),
                        'name' => 'csv_url_password',
                        'autocomplete' => false,
                        'desc' => $this->l('If your file is password protected, enter password for authentication.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('HTTP Method for downloading file'),
                        'name' => 'csv_url_method',
                        'options' => array(
                            'query' => array(
                                array('key' => 'GET', 'value' => 'GET'),
                                array('key' => 'POST', 'value' => 'POST'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Choose http method that you want to use for downloading import file from given URL.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('POST request content'),
                        'name' => 'csv_url_post_params',
                        'desc' => $this->l('You can set POST request parameters in the following format:') . ' key1=value1&key2=value2',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('FTP Host'),
                        'name' => 'ftp_host',
                        'desc' => $this->l('Enter FTP server address. This parameter should not have any trailing slashes and should not be prefixed with ftp://  For example: ftp.example.com'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('FTP Port'),
                        'name' => 'ftp_port',
                        'desc' => $this->l('Enter FTP port number. If left empty, default 21 port will be used.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('FTP Username'),
                        'name' => 'ftp_username',
                        'desc' => $this->l('Enter username for the FTP'),
                    ),
                    array(
                        'type' => 'elegantalpassword',
                        'label' => $this->l('FTP Password'),
                        'name' => 'ftp_password',
                        'autocomplete' => false,
                        'desc' => $this->l('Enter password for the FTP'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('FTP File'),
                        'name' => 'ftp_file',
                        'desc' => $this->l('Enter file name located in FTP directory.') . ' ' . $this->l('Supported file formats:') . ' ' . implode(', ', $this->allowedFileTypes) . '. ' . $this->l('For example') . ': example.csv',
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Create CRON Job'),
                        'name' => 'is_cron',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_cron_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_cron_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('CRON job will be enabled for this import so that you can automate importing csv from specified location at scheduled time.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Email to send notification'),
                        'name' => 'email_to_send_notification',
                        'desc' => $this->l('You can enter an email to which a notification will be sent when CRON finishes importing.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of products to process per request'),
                        'name' => 'product_limit_per_request',
                        'desc' => $this->l('You can control the number of products that should be processed per request.') . ' ' . $this->l('It is recommended that you keep it 5 for importing manually and 50 for importing by CRON Job.') . ' ' . $this->l('You should not make it large number which may cause issues on the server because hosting servers do not allow long time for web requests.') . ' ' . $this->l('If you want CRON to import more products, just make it run frequently, for example, every 5 minutes.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Range of products to import'),
                        'name' => 'product_range_to_import',
                        'desc' => $this->l('You can specify the range of products that should be imported in total.') . ' ' . $this->l('Leave this EMPTY to import ALL PRODUCTS.') . ' ' . $this->l('If you want to import specific range of products, enter it in this format:') . ' FROM - TO. ' . $this->l('For example') . ': ' . $this->l('All Products') . ': ' . $this->l('Empty') . ', ' . $this->l('First 100 products') . ': 1 - 100, ' . $this->l('From product 101 to product 500') . ': 101 - 500',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Update products by'),
                        'name' => 'find_products_by',
                        'options' => array(
                            'query' => array(
                                array('key' => 'id', 'value' => 'ID'),
                                array('key' => 'reference', 'value' => 'Reference'),
                                array('key' => 'ean', 'value' => 'EAN'),
                                array('key' => 'supplier_reference', 'value' => 'Supplier Reference'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select product attribute by which you want to update products.') . ' ' . $this->l('Usually Reference is used.') . ' ' . $this->l('You should use ID option IF ONLY product IDs in your import file match product IDs in your shop.'),
                    ),
                    $language_input,
                    array(
                        'type' => 'select',
                        'label' => $this->l('Supplier'),
                        'name' => 'supplier_id',
                        'options' => array(
                            'query' => $this->getSuppliersForSelect(false),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select supplier if you import products from different suppliers and from different files.') . ' ' . $this->l('The products of each supplier will be managed independently even if they use the same product reference for different products.'),
                    ),
                    $update_products_on_all_shops_input,
                    array(
                        'type' => 'text',
                        'label' => $this->l('Base URL/PATH for product images'),
                        'name' => 'base_url_images',
                        'desc' => $this->l('For example') . ' URL: http://example.com/images/csv/' . ' or PATH: ' . realpath(dirname(__FILE__) . '/../..') . '/images/ ' . $this->l('If the image in your import file is filename.jpg, the module will take it from') . ' http://example.com/images/csv/filename.jpg',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Price Modifier'),
                        'name' => 'price_modifier',
                        'desc' => $this->l('You can use arithmetic formula which will be used to modify product price while importing.') . ' ' . $this->l('Examples') . ': *2, /3, +1.11, -0.5',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Min Price Amount'),
                        'name' => 'min_price_amount',
                        'desc' => $this->l('New products that have lower price than the specified min price amount will be skipped during the import and price of existing products will not be updated if lower than min price.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Multiple value separator'),
                        'name' => 'multiple_value_separator',
                        'options' => array(
                            'query' => array(
                                array('key' => '|', 'value' => '|'),
                                array('key' => ',', 'value' => ','),
                                array('key' => ';', 'value' => ';'),
                                array('key' => ':', 'value' => ':'),
                                array('key' => '#', 'value' => '#'),
                                array('key' => '*', 'value' => '*'),
                                array('key' => '-', 'value' => '-'),
                                array('key' => '_', 'value' => '_'),
                                array('key' => '/', 'value' => '/'),
                                array('key' => '>', 'value' => '>'),
                                array('key' => '->', 'value' => '->'),
                                array('key' => '=>', 'value' => '=>'),
                                array('key' => ' ', 'value' => 'Space'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select a character used as delimeter for list type values. For example, images list separator: image1.jpg|image2.jpg|image3.jpg'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Price decimal mark'),
                        'name' => 'decimal_char',
                        'options' => array(
                            'query' => array(
                                array('key' => '.', 'value' => '. (dot)'),
                                array('key' => ',', 'value' => ', (comma)'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('A decimal mark is a symbol used to separate the integer part from the fractional part of product price'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Shipping package size unit'),
                        'name' => 'shipping_package_size_unit',
                        'options' => array(
                            'query' => array(
                                array('key' => 'm', 'value' => 'm'),
                                array('key' => 'cm', 'value' => 'cm'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select unit of length for shipping package'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Shipping package weight unit'),
                        'name' => 'shipping_package_weight_unit',
                        'options' => array(
                            'query' => $shipping_units,
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select unit of weight for shipping package'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Create new products'),
                        'name' => 'create_new_products',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'create_new_products_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'create_new_products_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('New products will be created if they do not already exist'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Enable new products by default'),
                        'name' => 'enable_new_products_by_default',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'enable_new_products_by_default_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'enable_new_products_by_default_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('If this option is enabled, new products will be enabled by default. If this option is disabled, new products will be disabled.') . ' ' . $this->l('This option has no affect if ENABLED column is used on the next step in mapping.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Update existing products'),
                        'name' => 'update_existing_products',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'update_existing_products_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'update_existing_products_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Existing products will be updated'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Enable products that have stock'),
                        'name' => 'enable_if_have_stock',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'enable_if_have_stock_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'enable_if_have_stock_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('All products that have stock will be enabled.') . ' ' . $this->l('This option has no affect if ENABLED column is used on the next step in mapping.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Disable products that have no stock'),
                        'name' => 'disable_if_no_stock',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'disable_if_no_stock_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'disable_if_no_stock_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('All products that have no stock will be disabled.') . ' ' . $this->l('This option has no affect if ENABLED column is used on the next step in mapping.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Enable products found in import file'),
                        'name' => 'enable_all_products_found_in_csv',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'enable_all_products_found_in_csv_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'enable_all_products_found_in_csv_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('If this option is enabled, all products that exist in import file will be enabled.') . ' ' . $this->l('This option has no affect if ENABLED column is used on the next step in mapping.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Disable products not found in import file'),
                        'name' => 'disable_all_products_not_found_in_csv',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'disable_all_products_not_found_in_csv_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'disable_all_products_not_found_in_csv_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('If this option is enabled, all products that do not exist in import file will be disabled.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Delete stock for products not found in import file'),
                        'name' => 'put_zero_qty_for_products_not_found_in_csv',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'put_zero_qty_for_products_not_found_in_csv_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'put_zero_qty_for_products_not_found_in_csv_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('If this option is enabled, all products that do not exist in import file will have 0 quantity.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Delete old combinations'),
                        'name' => 'delete_old_combinations',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'delete_old_combinations_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'delete_old_combinations_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('CAUTION: This will delete old combinations, then import new data.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Replicate all languages'),
                        'name' => 'replicate_all_languages',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'replicate_all_languages_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'replicate_all_languages_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Importing value will be replicated to all other languages for multilang properties'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Enable UTF-8 encoding'),
                        'name' => 'is_utf8_encode',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_utf8_encode_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_utf8_encode_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Encodes an ISO-8859-1 string to UTF-8. You need to enable this option if your file is ISO-8859-1 encoded.'),
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'active',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save & Next'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'submitImportEdit';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'importEdit', 'id_elegantaleasyimport' => $this->model->id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $this->importRenderSteps(1) . $html . $helper->generateForm(array($fields_form));
    }

    protected function importErrorLog()
    {
        if (!$this->model) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin();
        }
        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'model' => $this->model->getAttributes(),
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/import_error_log.tpl');
    }

    protected function importClearErrorLog()
    {
        if (!$this->model) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin();
        }
        $this->model->error_log = "";
        if ($this->model->update()) {
            $this->setRedirectAlert($this->l('Logs cleared successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Logs could not be cleared.'), 'error');
        }
        $this->redirectAdmin();
    }

    protected function importChangeStatus()
    {
        if (!$this->model) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin();
        }
        $this->model->active = $this->model->active == 1 ? 0 : 1;
        if ($this->model->update()) {
            $this->setRedirectAlert($this->l('Status changed successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Status could not be changed.'), 'error');
        }
        $this->redirectAdmin();
    }

    protected function importDuplicate()
    {
        if (!$this->model) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin();
        }

        $model = $this->model;
        $model->id = null;
        $model->id_elegantaleasyimport = null;
        $model->name .= ' (Copy)';
        $model->csv_file = null;
        $model->cron_csv_file_size = null;
        $model->cron_csv_file_md5 = null;
        $model->last_import_date = null;
        $model->error_log = "";
        $model->active = 1;
        if ($model->add()) {
            $this->setRedirectAlert($this->l('Rule duplicated successfully.'), 'success');
            $this->redirectAdmin(array(
                'event' => 'importEdit',
                'id_elegantaleasyimport' => $model->id,
            ));
        } else {
            $this->setRedirectAlert($this->l('Rule could not be duplicated.') . ' ' . Db::getInstance()->getMsgError(), 'error');
        }

        $this->redirectAdmin();
    }

    protected function importDelete()
    {
        if (!$this->model) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin();
        }
        if ($this->model->delete()) {
            ElegantalEasyImportTools::deleteTmpFile($this->model->csv_file);
            $this->setRedirectAlert($this->l('Rule deleted successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Rule could not be deleted.') . ' ' . Db::getInstance()->getMsgError(), 'error');
        }
        $this->redirectAdmin();
    }

    /**
     * Downloads import file according to selected method
     * @return boolean
     * @throws Exception
     */
    public function downloadImportFile()
    {
        $error = null;
        // Get old file name so that we can delete it after downloading new file
        $old_file = $this->model->csv_file;
        // Generate name for new file
        $this->model->csv_file = Tools::passwdGen(8) . '.csv';
        try {
            switch ($this->model->import_type) {
                case self::$IMPORT_TYPE_UPLOAD:
                    $this->downloadFileUploaded();
                    break;
                case self::$IMPORT_TYPE_URL:
                    $this->downloadFileFromUrl();
                    break;
                case self::$IMPORT_TYPE_PATH:
                    $this->downloadFileFromPath();
                    break;
                case self::$IMPORT_TYPE_FTP:
                    $this->downloadFileFromFtp();
                    break;
                case self::$IMPORT_TYPE_SFTP:
                    $this->downloadFileFromSftp();
                    break;
                default:
                    throw new Exception($this->l('Import Method is not valid.'));
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        // If model has old file, delete it
        if ($old_file && !$error) {
            ElegantalEasyImportTools::deleteTmpFile($old_file);
        }

        if ($error) {
            throw new Exception($error);
        }

        return true;
    }

    /**
     * Downloads uploaded file
     * @return string
     * @throws Exception
     */
    protected function downloadFileUploaded()
    {
        if (!isset($_FILES['csv_file_upload']) || empty($_FILES['csv_file_upload']["tmp_name"]) || !is_uploaded_file($_FILES['csv_file_upload']['tmp_name'])) {
            throw new Exception($this->l('File is not uploaded.'));
        }

        // Validate file type
        $extension = Tools::strtolower(pathinfo($_FILES['csv_file_upload']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedFileTypes)) {
            throw new Exception(sprintf($this->l('The file type %s is not allowed. You may import only %s.'), $extension, implode(', ', $this->allowedFileTypes)));
        }

        $local_file = ElegantalEasyImportTools::createPath($this->model->csv_file);

        if (!move_uploaded_file($_FILES['csv_file_upload']["tmp_name"], $local_file)) {
            throw new Exception($this->l('There was an error uploading your file. Please try again.'));
        }

        // If not csv file, convert it to csv
        ElegantalEasyImportTools::convertToCsv($local_file, $extension, $this->model->entity, $this->model->multiple_value_separator);

        return $local_file;
    }

    /**
     * Download file from URL or Path of the current model
     * @return string
     * @throws Exception
     */
    protected function downloadFileFromUrl()
    {
        // Validate file from URL
        if (!$this->model->csv_url || !ElegantalEasyImportTools::isValidUrl($this->model->csv_url)) {
            throw new Exception($this->l('Given URL is not valid.'));
        }

        $local_file = ElegantalEasyImportTools::createPath($this->model->csv_file);
        ElegantalEasyImportTools::downloadFileFromUrl($this->model->csv_url, $local_file, $this->model->csv_url_username, $this->model->csv_url_password, $this->model->csv_url_method, $this->model->csv_url_post_params);

        // Get file size
        $file_size = filesize($local_file);
        if (!$file_size) {
            throw new Exception($this->l('File not found or it is empty.'));
        }

        // Validate file type
        $extension = Tools::strtolower(pathinfo($this->model->csv_url, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedFileTypes)) {
            $mime_type = ElegantalEasyImportTools::getMimeType($local_file);
            switch ($mime_type) {
                case 'text/xml':
                case 'text/html':
                case 'application/xml':
                    $extension = 'xml';
                    break;
                case 'text/csv':
                case 'text/plain':
                case 'application/octet-stream':
                    $extension = 'csv';
                    break;
                case 'application/vnd.ms-excel':
                    $extension = 'xls';
                    break;
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    $extension = 'xlsx';
                    break;
                case 'application/json':
                    $extension = 'json';
                    break;
                default:
                    break;
            }
            if (!in_array($extension, $this->allowedFileTypes)) {
                throw new Exception(sprintf($this->l('The file type %s is not allowed. You may import only %s.'), $extension, implode(', ', $this->allowedFileTypes)));
            }
        }

        ElegantalEasyImportTools::convertToCsv($local_file, $extension, $this->model->entity, $this->model->multiple_value_separator);

        if ($this->model->is_cron) {
            $this->model->cron_csv_file_size = filesize($local_file);
            $this->model->cron_csv_file_md5 = md5_file($local_file);
        }

        return $local_file;
    }

    /**
     * Download file from path of the current model
     * @return string
     * @throws Exception
     */
    protected function downloadFileFromPath()
    {
        if (Tools::substr($this->model->csv_path, 0, 1) != '/') {
            $this->model->csv_path = realpath(_PS_ROOT_DIR_ . '/' . $this->model->csv_path);
        }

        // Validate file from path
        if (!$this->model->csv_path || !is_file($this->model->csv_path) || !is_readable($this->model->csv_path)) {
            throw new Exception($this->l('File not found from given path.'));
        }

        // Validate file type
        $extension = Tools::strtolower(pathinfo($this->model->csv_path, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedFileTypes)) {
            throw new Exception(sprintf($this->l('The file type %s is not allowed. You may import only %s.'), $extension, implode(', ', $this->allowedFileTypes)));
        }

        // Clear cache of old filesize
        clearstatcache(true, $this->model->csv_path);
        // Get file size
        $file_size = filesize($this->model->csv_path);
        if (!$file_size) {
            throw new Exception($this->l('File not found or it is empty.'));
        }

        $local_file = ElegantalEasyImportTools::createPath($this->model->csv_file);

        $file_contents = Tools::file_get_contents($this->model->csv_path);
        if ($file_contents) {
            if (!file_put_contents($local_file, $file_contents)) {
                throw new Exception($this->l('An error occured while saving the file.'));
            }
            // If not csv file, convert it to csv
            ElegantalEasyImportTools::convertToCsv($local_file, $extension, $this->model->entity, $this->model->multiple_value_separator);
        } else {
            throw new Exception($this->l('File not found.'));
        }

        if ($this->model->is_cron) {
            $this->model->cron_csv_file_size = filesize($local_file);
            $this->model->cron_csv_file_md5 = md5_file($local_file);
        }

        return $local_file;
    }

    /**
     * Downloads file from FTP of current model and returns full path
     * @return string
     * @throws Exception
     */
    protected function downloadFileFromFtp()
    {
        if (!$this->model->ftp_host) {
            throw new Exception($this->l('FTP Host is not valid.'));
        }
        if (!$this->model->ftp_username) {
            throw new Exception($this->l('FTP Username is not valid.'));
        }
        if (!$this->model->ftp_password) {
            throw new Exception($this->l('FTP Password is not valid.'));
        }
        if (!$this->model->ftp_file) {
            throw new Exception($this->l('FTP File is not valid.'));
        }

        $ftp_file = $this->model->ftp_file;
        $pathinfo = pathinfo($ftp_file);

        // Validate file type
        $extension = Tools::strtolower($pathinfo['extension']);
        if (!in_array($extension, $this->allowedFileTypes)) {
            throw new Exception(sprintf($this->l('The file type %s is not allowed. You may import only %s.'), $extension, implode(', ', $this->allowedFileTypes)));
        }

        // Connect to FTP server
        $ftp_port = $this->model->ftp_port ? $this->model->ftp_port : 21;
        $ftp_conn = ftp_connect($this->model->ftp_host, $ftp_port);
        if (!$ftp_conn) {
            throw new Exception($this->l('Could not connect to FTP') . ': ' . $this->model->ftp_host . ':' . $ftp_port);
        }

        // Login to FTP server. You can list files this way: ftp_nlist($ftp_conn, ".")
        $ftp_login = ftp_login($ftp_conn, $this->model->ftp_username, $this->model->ftp_password);
        if (!$ftp_login) {
            ftp_close($ftp_conn);
            throw new Exception($this->l('FTP login failed.'));
        }

        if ($pathinfo['filename'] == 'GET_LATEST_FILE') {
            ftp_pasv($ftp_conn, true); // Try with passive mode
            $files = ftp_nlist($ftp_conn, "-t " . $pathinfo['dirname']); // Get list of files, in latest-first order
            if (!$files) {
                ftp_pasv($ftp_conn, false); // Disable passive mode and try again
                $files = ftp_nlist($ftp_conn, "-t " . $pathinfo['dirname']);
                if (!$files) {
                    ftp_close($ftp_conn);
                    throw new Exception('Error getting files list.');
                }
            }
            if ($files && is_array($files)) {
                $filtered_files = preg_grep("/\." . $pathinfo['extension'] . "$/i", $files);
                if ($filtered_files && is_array($filtered_files)) {
                    $ftp_file = reset($filtered_files);
                }
            }
        }

        // Get file size
        $file_size = ftp_size($ftp_conn, $ftp_file);
        if (!$file_size) {
            ftp_close($ftp_conn);
            throw new Exception($this->l('File not found or it is empty.'));
        }

        $local_file = ElegantalEasyImportTools::createPath($this->model->csv_file);

        // Download server file
        ftp_pasv($ftp_conn, true); // Try with passive mode
        if (!ftp_get($ftp_conn, $local_file, $ftp_file, FTP_BINARY)) {
            ftp_pasv($ftp_conn, false); // Disable passive mode and try again
            if (!ftp_get($ftp_conn, $local_file, $ftp_file, FTP_BINARY)) {
                ftp_close($ftp_conn);
                throw new Exception(sprintf($this->l('Error downloading %s'), $ftp_file));
            }
        }

        ftp_close($ftp_conn);

        // If not csv file, convert it to csv
        ElegantalEasyImportTools::convertToCsv($local_file, $extension, $this->model->entity, $this->model->multiple_value_separator);

        if ($this->model->is_cron) {
            $this->model->cron_csv_file_size = filesize($local_file);
            $this->model->cron_csv_file_md5 = md5_file($local_file);
        }

        return $local_file;
    }

    protected function downloadFileFromSftp()
    {
        if (!$this->model->ftp_host) {
            throw new Exception($this->l('FTP Host is not valid.'));
        }
        if (!$this->model->ftp_username) {
            throw new Exception($this->l('FTP Username is not valid.'));
        }
        if (!$this->model->ftp_password) {
            throw new Exception($this->l('FTP Password is not valid.'));
        }
        if (!$this->model->ftp_file) {
            throw new Exception($this->l('FTP File is not valid.'));
        }
        if (!function_exists('ssh2_connect')) {
            throw new Exception($this->l('Function ssh2_connect not found. You need to install it on your hosting server.'));
        }
        // Validate file type
        $extension = Tools::strtolower(pathinfo($this->model->ftp_file, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedFileTypes)) {
            throw new Exception(sprintf($this->l('The file type %s is not allowed. You may import only %s.'), $extension, implode(', ', $this->allowedFileTypes)));
        }
        $sftp_port = $this->model->ftp_port ? $this->model->ftp_port : 22;
        $connection = ssh2_connect($this->model->ftp_host, $sftp_port);
        if (!$connection) {
            throw new Exception($this->l('Unable to connect to SFTP.') . ' ' . $this->model->ftp_host . ':' . $this->model->ftp_port);
        }
        if (!ssh2_auth_password($connection, $this->model->ftp_username, $this->model->ftp_password)) {
            throw new Exception($this->l('SFTP authentication failed.') . ' ' . $this->model->ftp_username . ' : ' . $this->model->ftp_password);
        }
        $stream = ssh2_sftp($connection);
        if (!$stream) {
            throw new Exception($this->l('Unable to create SFTP stream.'));
        }

        $handle = fopen("ssh2.sftp://" . (int) $stream . "/" . $this->model->ftp_file, 'r');
        if (!$handle) {
            throw new Exception($this->l('Failed to read SFTP file.') . ' ' . $this->model->ftp_file);
        }
        $contents = stream_get_contents($handle);
        if (empty($contents)) {
            throw new Exception($this->l('File not found or it is empty.') . ' ' . $this->model->ftp_file);
        }
        $local_file = ElegantalEasyImportTools::createPath($this->model->csv_file);
        $result = file_put_contents($local_file, $contents);
        @fclose($handle);
        if (!$result) {
            throw new Exception(sprintf($this->l('Error downloading %s'), $this->model->ftp_file));
        }

        // If not csv file, convert it to csv
        ElegantalEasyImportTools::convertToCsv($local_file, $extension, $this->model->entity, $this->model->multiple_value_separator);

        if ($this->model->is_cron) {
            $this->model->cron_csv_file_size = filesize($local_file);
            $this->model->cron_csv_file_md5 = md5_file($local_file);
        }

        return $local_file;
    }

    /**
     * Renders & Process mapping form
     * @return string HTML
     */
    protected function importMapping()
    {
        if (!$this->model) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin();
        }

        $file = ElegantalEasyImportTools::getRealPath($this->model->csv_file);
        if (!$file || !is_file($file) || !is_readable($file) || !filesize($file)) {
            $this->setRedirectAlert($this->l('File not found or it is empty.'), 'error');
            $this->redirectAdmin(array(
                'event' => 'importEdit',
                'id_elegantaleasyimport' => $this->model->id
            ));
        }

        $default_map = ($this->model->entity == 'combination') ? $this->defaultMapCombinations : $this->defaultMapProducts;
        $map_keys = array_keys($default_map);
        $csv_header = $this->getCsvHeaderForSelect($file);

        $model_map = ElegantalEasyImportTools::unserialize($this->model->map);

        // Assign map index automatically by matching csv header
        if (empty($model_map)) {
            $model_map = $default_map;
            foreach ($model_map as $model_map_column => $model_map_index) {
                unset($model_map_index); // Not used
                $default_column = Tools::strtolower(preg_replace("/[^A-Za-z0-9?!]/", "", $model_map_column));
                foreach ($csv_header as $csv_header_column) {
                    if ($csv_header_column['key'] >= 0) {
                        $header_column = Tools::strtolower(preg_replace("/[^A-Za-z0-9?!]/", "", $csv_header_column['value']));
                        if ($default_column == $header_column) {
                            $model_map[$model_map_column] = $csv_header_column['key'];
                        }
                    }
                }
            }
        }

        if ($this->isPostRequest()) {
            $map = array();
            $map_default_values = array();
            foreach ($map_keys as $key) {
                if (Tools::isSubmit($key)) {
                    $map[$key] = Tools::getValue($key);
                } else {
                    $map[$key] = '-1';
                }
                if (Tools::isSubmit('default_' . $key)) {
                    $map_default_values[$key] = Tools::getValue('default_' . $key);
                } else {
                    $map_default_values[$key] = '';
                }
            }

            // Save map
            $this->model->map = ElegantalEasyImportTools::serialize($map);
            $this->model->map_default_values = ElegantalEasyImportTools::serialize($map_default_values);
            $this->model->update();

            if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                $this->setRedirectAlert($this->l('Rule saved successfully.'), 'success');
                $this->redirectAdmin(array(
                    'event' => 'importMapping',
                    'id_elegantaleasyimport' => $this->model->id
                ));
            } if (Tools::isSubmit('submitAndManageCategory') && !Tools::isSubmit('submitAndNext')) {
                $this->redirectAdmin(array(
                    'event' => 'manageCategory',
                    'id_elegantaleasyimport' => $this->model->id
                ));
            } else {
                if ($this->model->is_cron) {
                    // Save csv rows in db so that import will start from next execution
                    $this->saveCsvRowsInDb();
                    $this->redirectAdmin(array(
                        'event' => 'importCronInfo',
                        'id_elegantaleasyimport' => $this->model->id
                    ));
                } else {
                    $this->redirectAdmin(array(
                        'event' => 'import',
                        'id_elegantaleasyimport' => $this->model->id
                    ));
                }
            }
        }

        $fields_value = array_merge($default_map, $model_map);
        $model_map_default_values = ElegantalEasyImportTools::unserialize($this->model->map_default_values);

        $inputs = array();
        foreach ($map_keys as $key) {
            $csvHeaderForSelect = $csv_header;
            // Hide 'Ignore this column' option for ID/Reference
            // if ($key == 'id_reference') {
            //    $csvHeaderForSelect = array_slice($csv_header, 1);
            // }
            if ($this->model->entity == 'combination' && in_array($key, array('id_reference'))) {
                $csvHeaderForSelect = array_slice($csv_header, 1);
            }
            if ($this->model->find_products_by == "reference" && $key == "reference") {
                continue;
            }
            $inputs[] = array(
                'type' => 'elegantal_mapping_select',
                'label' => ($key == 'id_reference') ? Tools::strtoupper(str_replace('_', ' ', $this->model->find_products_by)) : Tools::strtoupper(str_replace('_', ' ', $key)),
                'name' => $key,
                'options' => array(
                    'query' => $csvHeaderForSelect,
                    'id' => 'key',
                    'name' => 'value',
                ),
                'map_default_value' => isset($model_map_default_values[$key]) ? $model_map_default_values[$key] : '',
                'multiple_value_separator' => $this->model->multiple_value_separator,
                'desc' => ($key == 'delete_product') ? $this->l('CAUTION: This will delete product') : null,
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Step') . ' 2: ' . $this->l('Match product properties with CSV columns'),
                    'icon' => 'icon-random'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save & Import'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'title' => $this->l('Save & Manage Category'),
                        'name' => 'submitAndManageCategory',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-edit'
                    ),
                    array(
                        'href' => $this->getAdminUrl(),
                        'title' => $this->l('Home'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'importEdit', 'id_elegantaleasyimport' => $this->model->id)),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'submitMapping';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'importMapping', 'id_elegantaleasyimport' => $this->model->id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'model' => $this->model->getAttributes(),
            )
        );

        return $this->importRenderSteps(2) . $this->display(__FILE__, 'views/templates/admin/import_header_row.tpl') . $helper->generateForm(array($fields_form));
    }

    /**
     * Action to change header row number for the CSV file of the current rule
     */
    protected function selectHeaderRow()
    {
        if ($this->model && Tools::isSubmit('header_row')) {
            $this->model->header_row = (int) Tools::getValue('header_row');
            $this->model->header_row = $this->model->header_row >= 0 ? $this->model->header_row : 1;
            $this->model->update();
        }
        $this->redirectAdmin(array(
            'event' => 'importMapping',
            'id_elegantaleasyimport' => $this->model->id,
        ));
    }

    protected function manageCategory()
    {
        if (!$this->model) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin();
        }

        if ($this->isPostRequest()) {
            if (Tools::isSubmit('multiple_subcategory_separator')) {
                $this->model->multiple_subcategory_separator = Tools::getValue('multiple_subcategory_separator');
            }
            if (Tools::isSubmit('is_associate_all_subcategories')) {
                $this->model->is_associate_all_subcategories = (int) Tools::getValue('is_associate_all_subcategories');
            }
            if (Tools::isSubmit('is_first_parent_root_for_categories')) {
                $this->model->is_first_parent_root_for_categories = (int) Tools::getValue('is_first_parent_root_for_categories');
            }

            ElegantalEasyImportCategoryMap::deleteAllByRule($this->model->id);

            $categories_allowed = Tools::getValue('categories_allowed');
            if ($categories_allowed && is_array($categories_allowed)) {
                foreach ($categories_allowed as $allowed_category) {
                    $categoryMap = new ElegantalEasyImportCategoryMap();
                    $categoryMap->id_elegantaleasyimport = $this->model->id;
                    $categoryMap->type = ElegantalEasyImportCategoryMap::$CATEGORIES_ALLOWED;
                    $categoryMap->csv_category = $allowed_category;
                    $categoryMap->add();
                }
            }
            $categories_disallowed = Tools::getValue('categories_disallowed');
            if ($categories_disallowed && is_array($categories_disallowed)) {
                foreach ($categories_disallowed as $disallowed_category) {
                    $categoryMap = new ElegantalEasyImportCategoryMap();
                    $categoryMap->id_elegantaleasyimport = $this->model->id;
                    $categoryMap->type = ElegantalEasyImportCategoryMap::$CATEGORIES_DISALLOWED;
                    $categoryMap->csv_category = $disallowed_category;
                    $categoryMap->add();
                }
            }

            $categories_map_file = Tools::getValue('categories_map_file');
            $categories_map_shop = Tools::getValue('categories_map_shop');
            if ($categories_map_file && is_array($categories_map_file) && $categories_map_shop && is_array($categories_map_shop)) {
                foreach ($categories_map_file as $key => $csv_category) {
                    if (!$csv_category || !isset($categories_map_shop[$key]) || !$categories_map_shop[$key]) {
                        continue;
                    }
                    $categoryMap = new ElegantalEasyImportCategoryMap();
                    $categoryMap->id_elegantaleasyimport = $this->model->id;
                    $categoryMap->type = ElegantalEasyImportCategoryMap::$CATEGORIES_MAP;
                    $categoryMap->csv_category = $csv_category;
                    $categoryMap->shop_category_id = $categories_map_shop[$key];
                    $categoryMap->add();
                }
            }

            $this->model->update();

            if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                $this->setRedirectAlert($this->l('Categories saved successfully.'), 'success');
                $this->redirectAdmin(array(
                    'event' => 'manageCategory',
                    'id_elegantaleasyimport' => $this->model->id
                ));
            } else {
                if ($this->model->is_cron) {
                    $this->saveCsvRowsInDb();
                    $this->redirectAdmin(array(
                        'event' => 'importCronInfo',
                        'id_elegantaleasyimport' => $this->model->id
                    ));
                } else {
                    $this->redirectAdmin(array(
                        'event' => 'import',
                        'id_elegantaleasyimport' => $this->model->id
                    ));
                }
            }
        }

        $file = ElegantalEasyImportTools::getRealPath($this->model->csv_file);
        if (!$file || !is_file($file) || !is_readable($file) || !filesize($file)) {
            throw new Exception($this->l('File not found or it is empty.'));
        }

        $delimiter = $this->identifyCsvDelimiter($file);
        $rootCategory = Category::getRootCategory();
        $map = ElegantalEasyImportTools::unserialize($this->model->map);
        $multiple_value_separator = $this->model->multiple_value_separator;

        if ($map['categories'] < 0 && $map['category_1'] < 0 && $map['category_2'] < 0 && $map['category_3'] < 0 && $map['category_4'] < 0 && $map['category_5'] < 0 && $map['category_6'] < 0) {
            $this->setRedirectAlert($this->l('You can manage categories only when you select categories in mapping.'), 'error');
            $this->redirectAdmin(array(
                'event' => 'importMapping',
                'id_elegantaleasyimport' => $this->model->id,
            ));
        }

        ini_set('auto_detect_line_endings', true);
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new Exception($this->l('Cannot read the CSV file.'));
        }

        // Build categories tree from file categories
        $file_categories_tree = array();
        $row_count = 0;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row_count++;
            if ($this->model->header_row > 0 && $this->model->header_row >= $row_count) {
                continue;
            }
            // Check if non-empty row. Remove spaces & tabs and utf-8 BOM and then check length of line
            $line_str = preg_replace("/[\s\t\"]+/", "", implode('', $data));
            $line_str = str_replace("\xEF\xBB\xBF", "", $line_str);
            if (Tools::strlen($line_str) <= 0) {
                continue;
            }
            if ($this->model->is_utf8_encode) {
                $data = array_map(array('ElegantalEasyImportTools', 'encodeUtf8'), $data);
            }
            $category_names = array();
            if (isset($data[$map['categories']]) && $data[$map['categories']]) {
                $category_names = explode($multiple_value_separator, $data[$map['categories']]);
            }
            for ($i = 1; $i <= 6; $i++) {
                if (isset($data[$map['category_' . $i]]) && $data[$map['category_' . $i]]) {
                    $category_names[] = $data[$map['category_' . $i]];
                }
            }
            if (empty($category_names)) {
                continue;
            }
            $file_categories_tree = ElegantalEasyImportCategoryMap::addCategoriesToTree($file_categories_tree, $category_names, $multiple_value_separator, $this->model->multiple_subcategory_separator);
        }
        fclose($handle);

        $fields_value = ElegantalEasyImportCategoryMap::getCategoryMappingByRule($this->model->id);
        $fields_value['multiple_subcategory_separator'] = $this->model->multiple_subcategory_separator;
        $fields_value['is_associate_all_subcategories'] = $this->model->is_associate_all_subcategories;
        $fields_value['is_first_parent_root_for_categories'] = $this->model->is_first_parent_root_for_categories;
        $selected_categories_allowed = isset($fields_value['categories_allowed']) ? $fields_value['categories_allowed'] : array();
        $selected_categories_disallowed = isset($fields_value['categories_disallowed']) ? $fields_value['categories_disallowed'] : array();
        $selected_categories_map = isset($fields_value['categories_map']) ? $fields_value['categories_map'] : array();
        $selected_categories_map[] = array('csv_category' => "", 'shop_category_id' => ""); // Add one empty mapping for adding new
        $file_categories = ElegantalEasyImportCategoryMap::getCategoriesFromTree($file_categories_tree);
        $shop_categories = ElegantalEasyImportCategoryMap::getCategoriesFromTree(Category::getNestedCategories($rootCategory->id, $this->context->language->id, false));

        $categories_allowed_tree = new HelperTreeCategories('elegantal_categories_allowed');
        $categories_allowed_tree->setInputName('categories_allowed')
            ->setUseSearch(true)
            ->setUseCheckBox(true)
            ->setData($file_categories_tree)
            ->setRootCategory($rootCategory->id)
            ->setSelectedCategories($selected_categories_allowed);

        $categories_disallowed_tree = new HelperTreeCategories('elegantal_categories_disallowed');
        $categories_disallowed_tree->setInputName('categories_disallowed')
            ->setUseSearch(true)
            ->setUseCheckBox(true)
            ->setData($file_categories_tree)
            ->setRootCategory($rootCategory->id)
            ->setSelectedCategories($selected_categories_disallowed);

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Step') . ' 2: ' . $this->l('Manage categories mapping'),
                    'icon' => 'icon-edit'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Multiple subcategory separator'),
                        'name' => 'multiple_subcategory_separator',
                        'options' => array(
                            'query' => array(
                                array('key' => '', 'value' => ' '),
                                array('key' => '/', 'value' => '/'),
                                array('key' => '|', 'value' => '|'),
                                array('key' => '>', 'value' => '>'),
                                array('key' => '->', 'value' => '->'),
                                array('key' => '=>', 'value' => '=>'),
                                array('key' => ':', 'value' => ':'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'hint' => $this->l('For example') . ': ' . ' Home/Fashion/Men, Home/Fashion/Men/T-Shirt, Home/Fashion/Men/T-Shirt/Polo. ' . $this->l('According to this example, you should select / slash.'),
                        'desc' => $this->l('Select separator that is used to separate subcategories.') . ' ' . $this->l('For example, if your categories are written like the following:') . ' Home/Fashion/Men, Home/Fashion/Men/T-Shirt, Home/Fashion/Men/T-Shirt/Polo. ' . $this->l('According to this example, you should select / slash.') . ' ' . $this->l('NOTE that this is DIFFERENT than Multiple Value Separator.') . ' ' . $this->l('In this example, Multiple Value Separator is a comma.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Associate products with all subcategories'),
                        'name' => 'is_associate_all_subcategories',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_associate_all_subcategories_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_associate_all_subcategories_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'hint' => $this->l('For example') . ': ' . ' Home/Fashion/Men, Home/Fashion/Men/T-Shirt. ' . $this->l('In this example, product will be associated with Home and Fashion categories as well.'),
                        'desc' => $this->l('If enabled, this option will make product be associated with all subcategories in the categories path.') . ' ' . $this->l('If you want to associate product only with last categories in subcategory path, disable this option.') . ' ' . $this->l('For example') . ': ' . ' Home/Fashion/Men, Home/Fashion/Men/T-Shirt. ' . $this->l('In this example, product will be associated with Home and Fashion categories as well.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Parent of first category is Root category'),
                        'name' => 'is_first_parent_root_for_categories',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_first_parent_root_for_categories_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_first_parent_root_for_categories_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'hint' => $this->l('Disable this option if your categories tree does not start from Root category.'),
                        'desc' => $this->l('You need to enable this option if categories are in hierarchical order as parent-child tree under Root category.'),
                    ),
                    array(
                        'type' => 'elegantal_categories',
                        'label' => $this->l('Allowed categories from import file'),
                        'name' => 'categories_allowed',
                        'categories_tree' => $categories_allowed_tree->render(),
                        'hint' => $this->l('Products will be imported only from selected categories.') . ' ' . ($selected_categories_allowed ? $this->l('Currently selected categories are:') . ' ' . implode(' ' . $multiple_value_separator . ' ', $selected_categories_allowed) : null),
                        'desc' => $this->l('Select categories that you want to allow for import.') . ' ' . $this->l('If you select categories here, the products will be imported only from selected categories.') . ' ' . $this->l('Leave this empty if you want to import products from all categories.'),
                    ),
                    array(
                        'type' => 'elegantal_categories',
                        'label' => $this->l('Disallowed categories from import file'),
                        'name' => 'categories_disallowed',
                        'categories_tree' => $categories_disallowed_tree->render(),
                        'hint' => $this->l('Products of selected categories will not be imported.') . ' ' . ($selected_categories_disallowed ? $this->l('Currently selected categories are:') . ' ' . implode(' ' . $multiple_value_separator . ' ', $selected_categories_disallowed) : null),
                        'desc' => $this->l('Select categories that you want to disallow for import.') . ' ' . $this->l('If you select categories here, the products of selected categories will not be imported.') . ' ' . $this->l('Leave this empty if you want to import products from all categories.'),
                    ),
                    array(
                        'type' => 'elegantal_categories_map',
                        'label' => $this->l('Categories Mapping'),
                        'file_categories' => $file_categories,
                        'shop_categories' => $shop_categories,
                        'selected_categories_map' => $selected_categories_map,
                        'hint' => $this->l('You can match categories from the import file with the categories of the shop.') . ' ' . $this->l('Selected categories of the shop will be used instead of categories of the import file during the import process.'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save & Import'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(),
                        'title' => $this->l('Home'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'importMapping', 'id_elegantaleasyimport' => $this->model->id)),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'submitManageCategory';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'manageCategory', 'id_elegantaleasyimport' => $this->model->id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'model' => $this->model->getAttributes(),
            )
        );

        return $this->importRenderSteps(2) . $helper->generateForm(array($fields_form));
    }

    /**
     * Render and process import page
     * @return string HTML
     */
    protected function import()
    {
        $limit = (int) $this->model->product_limit_per_request;
        $limit = ($limit > 0 && $limit < 100) ? $limit : 5;
        if (Tools::getValue('ajax')) {
            $result = array();
            try {
                if ($this->model) {
                    $result['success'] = true;
                    if (Tools::getValue('prepareCsvRows')) {
                        $result['count'] = $this->saveCsvRowsInDb();
                    } elseif ($this->model->entity == 'product') {
                        $this->importProductDataFromCsv($limit);
                    } elseif ($this->model->entity == 'combination') {
                        $this->importCombinationDataFromCsv($limit);
                    } else {
                        $result['success'] = false;
                        $result['message'] = $this->l('Unknown import entity.');
                    }
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->l('Record not found.');
                }
            } catch (Exception $e) {
                $result['success'] = false;
                $result['message'] = $e->getMessage();
            }
            die(Tools::jsonEncode($result));
        }

        if (!$this->model) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin();
        }

        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'model' => $this->model->getAttributes(),
                'limit' => $limit,
            )
        );

        return $this->importRenderSteps(3) . $this->display(__FILE__, 'views/templates/admin/import.tpl');
    }

    /**
     * Reads CSV file and saves each row into database in bulk insert query
     * @return int
     * @throws Exception
     */
    public function saveCsvRowsInDb()
    {
        if (!$this->model) {
            throw new Exception($this->l('Record not found.'));
        }
        $this->model->error_log = "";
        $this->model->update();

        $id_shop = $this->context->shop->id;
        $context_shop = Shop::getContext();

        // Delete old csv rows if exists
        $sql = "DELETE FROM `" . _DB_PREFIX_ . "elegantaleasyimport_csv` WHERE `id_elegantaleasyimport` = " . (int) $this->model->id;
        Db::getInstance()->execute($sql);

        $file = ElegantalEasyImportTools::getRealPath($this->model->csv_file);
        if (!$file || !is_file($file) || !is_readable($file) || !filesize($file)) {
            throw new Exception($this->l('File not found or it is empty.'));
        }

        $delimiter = $this->identifyCsvDelimiter($file);

        ini_set('auto_detect_line_endings', true);
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new Exception($this->l('Cannot read the CSV file.'));
        }

        $map = ElegantalEasyImportTools::unserialize($this->model->map);
        $map_default_values = ElegantalEasyImportTools::unserialize($this->model->map_default_values);
        $product_range_from = null;
        $product_range_to = null;
        if (preg_match("/^(\d+)-(\d+)$/", $this->model->product_range_to_import, $match) && $match[1] <= $match[2]) {
            $product_range_from = $match[1];
            $product_range_to = $match[2];
            if ($product_range_from == 0) {
                $product_range_from = 1;
            }
        }

        $row_count = 0;
        $insert_count = 0;
        $current_row = 0;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row_count++;
            if ($this->model->header_row > 0 && $this->model->header_row >= $row_count) {
                continue;
            }
            // Check if non-empty row. Remove spaces & tabs and utf-8 BOM and then check length of line
            $line_str = preg_replace("/[\s\t\"]+/", "", implode('', $data));
            $line_str = str_replace("\xEF\xBB\xBF", "", $line_str);
            if (Tools::strlen($line_str) <= 0) {
                continue;
            }

            if ($this->model->is_utf8_encode) {
                $data = array_map(array('ElegantalEasyImportTools', 'encodeUtf8'), $data);
            }

            $id_reference = "";
            if ($map['id_reference'] >= 0 && isset($data[$map['id_reference']])) {
                $id_reference = trim($data[$map['id_reference']]);
                $id_reference = trim($id_reference, "'");
                $id_reference = trim($id_reference, '"');
                $data[$map['id_reference']] = $id_reference;
            }

            $current_row++;
            if ($product_range_from && $product_range_to) {
                if ($current_row < $product_range_from) {
                    continue;
                } elseif ($current_row > $product_range_to) {
                    break;
                }
            }

            $sql = "INSERT INTO `" . _DB_PREFIX_ . "elegantaleasyimport_csv` (`id_elegantaleasyimport`, `id_reference`, `csv_row`) 
                    VALUES(" . (int) $this->model->id . ", '" . pSQL($id_reference) . "', '" . pSQL(ElegantalEasyImportTools::serialize($data)) . "'); " . PHP_EOL;
            if (Db::getInstance()->execute($sql) == false) {
                throw new Exception(Db::getInstance()->getMsgError() . ' SQL: ' . $sql);
            }
            $insert_count++;
        }
        fclose($handle);

        $id_reference_column = "p.`reference`";
        if ($this->model->find_products_by == 'id') {
            $id_reference_column = "p.`id_product`";
        } elseif ($this->model->find_products_by == 'ean') {
            $id_reference_column = "p.`ean13`";
        } elseif ($this->model->find_products_by == 'supplier_reference') {
            $id_reference_column = "ps.`product_supplier_reference`";
        }

        // Enable products found in csv
        if ($insert_count > 0 && $this->model->enable_all_products_found_in_csv) {
            $sql = "UPDATE `" . _DB_PREFIX_ . "product_shop` psh 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON (psh.`id_product` = p.`id_product`) ";
            if ($this->model->find_products_by == 'supplier_reference' || $this->model->supplier_id) {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "SET psh.`active` = 1 
                WHERE " . pSQL($id_reference_column) . " IN (SELECT c.`id_reference` FROM `" . _DB_PREFIX_ . "elegantaleasyimport_csv` c WHERE c.`id_elegantaleasyimport` = " . (int) $this->model->id . ") ";
            if (!$this->model->update_products_on_all_shops) {
                $sql .= "AND psh.`id_shop` = " . (int) $id_shop . " ";
            }
            if ($this->model->supplier_id) {
                $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id . " ";
            }
            if (Db::getInstance()->execute($sql) == false) {
                throw new Exception(Db::getInstance()->getMsgError() . ' SQL: ' . $sql);
            }
        }

        // Disable products not found in csv
        if ($insert_count > 0 && $this->model->disable_all_products_not_found_in_csv) {
            $sql = "UPDATE `" . _DB_PREFIX_ . "product_shop` psh 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON (psh.`id_product` = p.`id_product`) ";
            if ($this->model->find_products_by == 'supplier_reference' || $this->model->supplier_id) {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "SET psh.`active` = 0 
                WHERE " . pSQL($id_reference_column) . " NOT IN (SELECT c.`id_reference` FROM `" . _DB_PREFIX_ . "elegantaleasyimport_csv` c WHERE c.`id_elegantaleasyimport` = " . (int) $this->model->id . ") ";
            if (!$this->model->update_products_on_all_shops) {
                $sql .= "AND psh.`id_shop` = " . (int) $id_shop . " ";
            }
            if ($this->model->supplier_id) {
                $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id . " ";
            }
            $product_ids_to_exclude = $this->getSetting('product_ids_to_exclude_from_deactivation');
            if ($product_ids_to_exclude) {
                $product_ids_to_exclude = str_replace(' ', '', $product_ids_to_exclude);
                $product_ids_to_exclude = explode(",", $product_ids_to_exclude);
                if ($product_ids_to_exclude && is_array($product_ids_to_exclude)) {
                    $sql .= "AND p.`id_product` NOT IN (" . implode(", ", array_map("intval", $product_ids_to_exclude)) . ")";
                }
            }
            if (Db::getInstance()->execute($sql) == false) {
                throw new Exception(Db::getInstance()->getMsgError() . ' SQL: ' . $sql);
            }
        }

        // Delete stock for products not found in csv
        if ($insert_count > 0 && $this->model->put_zero_qty_for_products_not_found_in_csv) {
            $sql = "SELECT psh.`id_product` FROM `" . _DB_PREFIX_ . "product_shop` psh 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON (psh.`id_product` = p.`id_product`) ";
            if ($this->model->find_products_by == 'supplier_reference' || $this->model->supplier_id) {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "WHERE " . pSQL($id_reference_column) . " NOT IN (SELECT c.`id_reference` FROM `" . _DB_PREFIX_ . "elegantaleasyimport_csv` c WHERE c.`id_elegantaleasyimport` = " . (int) $this->model->id . ") ";
            if (!$this->model->update_products_on_all_shops) {
                $sql .= "AND psh.`id_shop` = " . (int) $id_shop . " ";
            }
            if ($this->model->supplier_id) {
                $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id . " ";
            }
            $rows = Db::getInstance()->executeS($sql);
            if ($rows && is_array($rows)) {
                $shop_ids = array();
                if ($this->model->update_products_on_all_shops) {
                    $shop_groups = Shop::getTree();
                    foreach ($shop_groups as $shop_group) {
                        foreach ($shop_group['shops'] as $shop) {
                            $shop_ids[] = $shop['id_shop'];
                        }
                    }
                }
                Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
                foreach ($rows as $row) {
                    $product = new Product($row['id_product']);
                    if (!Validate::isLoadedObject($product)) {
                        continue;
                    }
                    $combinations = $product->getAttributeCombinations($this->context->language->id);
                    if ($combinations && is_array($combinations)) {
                        foreach ($combinations as $combination) {
                            if (!empty($shop_ids)) {
                                foreach ($shop_ids as $sh_id) {
                                    StockAvailable::setQuantity($row['id_product'], $combination['id_product_attribute'], 0, $sh_id);
                                }
                            } else {
                                StockAvailable::setQuantity($row['id_product'], $combination['id_product_attribute'], 0, $id_shop);
                            }
                        }
                    } else {
                        if (!empty($shop_ids)) {
                            foreach ($shop_ids as $sh_id) {
                                StockAvailable::setQuantity($row['id_product'], null, 0, $sh_id);
                            }
                        } else {
                            StockAvailable::setQuantity($row['id_product'], null, 0, $id_shop);
                        }
                    }
                }
                Shop::setContext($context_shop, $id_shop);
            }
        }

        // Delete combinations only if there are new combinations being imported
        if ($insert_count > 0 && $this->model->entity == 'combination' && $this->model->delete_old_combinations && ($map['attribute_names'] >= 0 || trim($map_default_values['attribute_names'])) && ($map['attribute_values'] >= 0 || trim($map_default_values['attribute_values']))) {
            $sql = "DELETE pac FROM `" . _DB_PREFIX_ . "product_attribute_combination` pac 
                INNER JOIN `" . _DB_PREFIX_ . "product_attribute` pa ON pac.`id_product_attribute` = pa.`id_product_attribute` 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON pa.`id_product` = p.`id_product` ";
            if ($this->model->find_products_by == 'supplier_reference') {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "INNER JOIN `" . _DB_PREFIX_ . "elegantaleasyimport_csv` ec ON " . pSQL($id_reference_column) . " = ec.`id_reference` 
                WHERE ec.`id_elegantaleasyimport` = " . (int) $this->model->id . "; ";
            $sql .= "DELETE pai FROM `" . _DB_PREFIX_ . "product_attribute_image` pai 
                INNER JOIN `" . _DB_PREFIX_ . "product_attribute` pa ON pai.`id_product_attribute` = pa.`id_product_attribute` 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON pa.`id_product` = p.`id_product` ";
            if ($this->model->find_products_by == 'supplier_reference') {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "INNER JOIN `" . _DB_PREFIX_ . "elegantaleasyimport_csv` ec ON " . pSQL($id_reference_column) . " = ec.`id_reference` 
                WHERE ec.`id_elegantaleasyimport` = " . (int) $this->model->id . "; ";
            $sql .= "DELETE sa FROM `" . _DB_PREFIX_ . "stock_available` sa 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON sa.`id_product` = p.`id_product` ";
            if ($this->model->find_products_by == 'supplier_reference') {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "INNER JOIN `" . _DB_PREFIX_ . "elegantaleasyimport_csv` ec ON " . pSQL($id_reference_column) . " = ec.`id_reference` 
                WHERE sa.`id_product_attribute` != 0 AND ec.`id_elegantaleasyimport` = " . (int) $this->model->id . "; ";
            $sql .= "DELETE pas FROM `" . _DB_PREFIX_ . "product_attribute_shop` pas 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON pas.`id_product` = p.`id_product` ";
            if ($this->model->find_products_by == 'supplier_reference') {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "INNER JOIN `" . _DB_PREFIX_ . "elegantaleasyimport_csv` ec ON " . pSQL($id_reference_column) . " = ec.`id_reference` 
                WHERE ec.`id_elegantaleasyimport` = " . (int) $this->model->id . "; ";
            $sql .= "DELETE pa FROM `" . _DB_PREFIX_ . "product_attribute` pa 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON pa.`id_product` = p.`id_product` ";
            if ($this->model->find_products_by == 'supplier_reference') {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "INNER JOIN `" . _DB_PREFIX_ . "elegantaleasyimport_csv` ec ON " . pSQL($id_reference_column) . " = ec.`id_reference` 
                WHERE ec.`id_elegantaleasyimport` = " . (int) $this->model->id . "; ";
            $sql .= "DELETE sp FROM `" . _DB_PREFIX_ . "specific_price` sp 
                INNER JOIN `" . _DB_PREFIX_ . "product` p ON sp.`id_product` = p.`id_product` ";
            if ($this->model->find_products_by == 'supplier_reference') {
                $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
            }
            $sql .= "INNER JOIN `" . _DB_PREFIX_ . "elegantaleasyimport_csv` ec ON " . pSQL($id_reference_column) . " = ec.`id_reference` 
                WHERE sp.`id_product_attribute` != 0 AND ec.`id_elegantaleasyimport` = " . (int) $this->model->id . "; ";
            Db::getInstance()->execute($sql);
        }

        return $insert_count;
    }

    /**
     * Imports product data from CSV rows
     * @param int $limit
     * @return boolean
     * @throws Exception
     */
    public function importProductDataFromCsv($limit)
    {
        if (!$this->model) {
            throw new Exception($this->l('Record not found.'));
        }
        $map = ElegantalEasyImportTools::unserialize($this->model->map);
        if (empty($map)) {
            throw new Exception('Map not found.');
        }
        $map = array_merge($this->defaultMapProducts, $map);
        $map_default_values = ElegantalEasyImportTools::unserialize($this->model->map_default_values);
        $file = ElegantalEasyImportTools::getRealPath($this->model->csv_file);
        $csv_header = $this->getCsvHeaderRow($file);
        $id_shop = $this->context->shop->id;
        $context_shop = Shop::getContext();
        $settings = $this->getSettings();
        $rootCategory = Category::getRootCategory();
        $multiple_value_separator = $this->model->multiple_value_separator;
        $multiple_subcategory_separator = $this->model->multiple_subcategory_separator;
        $update_products_on_all_shops = $this->model->update_products_on_all_shops && Shop::isFeatureActive();
        $category_mapping = ElegantalEasyImportCategoryMap::getCategoryMappingByRule($this->model->id);

        $shop_ids = array();
        if ($update_products_on_all_shops) {
            $shop_groups = Shop::getTree();
            foreach ($shop_groups as $shop_group) {
                foreach ($shop_group['shops'] as $shop) {
                    $shop_ids[] = $shop['id_shop'];
                }
            }
        }

        $languages_all = Language::getLanguages();
        $languages_model = array($this->model->lang_id);
        if ($this->model->replicate_all_languages) {
            foreach ($languages_all as $language) {
                if (!in_array($language['id_lang'], $languages_model)) {
                    $languages_model[] = $language['id_lang'];
                }
            }
        }

        $csvRows = ElegantalEasyImportCsv::model()->findAll(array(
            'condition' => array(
                'id_elegantaleasyimport' => $this->model->id,
            ),
            'limit' => $limit,
        ));

        foreach ($csvRows as $csvRow) {
            $csvRowModel = new ElegantalEasyImportCsv($csvRow['id_elegantaleasyimport_csv']);
            if (!Validate::isLoadedObject($csvRowModel)) {
                continue;
            }

            $line = ElegantalEasyImportTools::unserialize($csvRowModel->csv_row);

            // We don't need this row in database anymore
            $csvRowModel->delete();

            $id_index = $map['id_reference'];

            if (isset($line[$id_index])) {
                $line[$id_index] = trim($line[$id_index]);
            }

            // Check with category mapping
            $categories_attrs = array('categories', 'category_1', 'category_2', 'category_3', 'category_4', 'category_5', 'category_6');
            if ($category_mapping) {
                // Prepare file categories for checking
                $categories_to_check = array();
                $current_category_to_check = "";
                foreach ($categories_attrs as $categories_attr) {
                    if ($map[$categories_attr] >= 0 && isset($line[$map[$categories_attr]]) && $line[$map[$categories_attr]]) {
                        if ($multiple_subcategory_separator) {
                            $category_names = explode($multiple_value_separator, $line[$map[$categories_attr]]);
                            foreach ($category_names as $category_name) {
                                $categories_to_check[] = $category_name;
                            }
                        } else {
                            $current_category_to_check .= $current_category_to_check ? $multiple_value_separator : "";
                            $current_category_to_check .= $line[$map[$categories_attr]];
                            $categories_to_check[] = $current_category_to_check;
                        }
                    }
                }
                // Check if file categories are allowed/disallowed
                if ($categories_to_check) {
                    if (isset($category_mapping['categories_disallowed']) && $category_mapping['categories_disallowed']) {
                        $categories_disallowed_found = false;
                        foreach ($categories_to_check as $category_to_check) {
                            if (in_array($category_to_check, $category_mapping['categories_disallowed'])) {
                                $categories_disallowed_found = true;
                                break;
                            }
                        }
                        if ($categories_disallowed_found) {
                            continue;
                        }
                    }
                    if (isset($category_mapping['categories_allowed']) && $category_mapping['categories_allowed']) {
                        $categories_allowed_found = false;
                        foreach ($categories_to_check as $category_to_check) {
                            if (in_array($category_to_check, $category_mapping['categories_allowed'])) {
                                $categories_allowed_found = true;
                                break;
                            }
                        }
                        if (!$categories_allowed_found) {
                            continue;
                        }
                    }
                }
                // Replace categories if there is category mapping
                if (isset($category_mapping['categories_map']) && $category_mapping['categories_map']) {
                    $categories_attrs[] = 'default_category';
                    $current_category_to_check = "";
                    foreach ($categories_attrs as $categories_attr) {
                        if ($map[$categories_attr] >= 0 && isset($line[$map[$categories_attr]]) && $line[$map[$categories_attr]]) {
                            if ($multiple_subcategory_separator) {
                                $category_names = explode($multiple_value_separator, $line[$map[$categories_attr]]);
                                foreach ($category_names as $key => $category_name) {
                                    $category_map_found = false;
                                    $extracted_subcategories = "";
                                    do {
                                        foreach ($category_mapping['categories_map'] as $categories_map) {
                                            if ($categories_map['csv_category'] == $category_name && $categories_map['shop_category_id']) {
                                                if ($category_map_found) {
                                                    $category_names[$key] .= $multiple_value_separator . $categories_map['shop_category_id'];
                                                } else {
                                                    $category_map_found = true;
                                                    $category_names[$key] = $categories_map['shop_category_id'] . ($extracted_subcategories ? $multiple_subcategory_separator . $extracted_subcategories : "");
                                                }
                                            }
                                        }
                                        if (!$category_map_found) {
                                            $subcategories = explode($multiple_subcategory_separator, $category_name);
                                            $extracted_subcategories = array_pop($subcategories) . ($extracted_subcategories ? $multiple_subcategory_separator . $extracted_subcategories : "");
                                            $category_name = implode($multiple_subcategory_separator, $subcategories);
                                        }
                                    } while (!$category_map_found && $category_name);
                                }
                                $line[$map[$categories_attr]] = implode($multiple_value_separator, $category_names);
                            } else {
                                $current_category_to_check .= $current_category_to_check ? $multiple_value_separator : "";
                                $line_category = $line[$map[$categories_attr]];

                                $category_map_found = false;
                                $extracted_subcategories = "";
                                do {
                                    foreach ($category_mapping['categories_map'] as $categories_map) {
                                        if ($categories_map['csv_category'] == $current_category_to_check . $line_category && $categories_map['shop_category_id']) {
                                            if ($category_map_found) {
                                                $line[$map[$categories_attr]] .= $multiple_value_separator . $categories_map['shop_category_id'];
                                            } else {
                                                $category_map_found = true;
                                                $line[$map[$categories_attr]] = $categories_map['shop_category_id'];
                                            }
                                        }
                                    }
                                    if (!$category_map_found) {
                                        $subcategories = explode($multiple_value_separator, $line[$map[$categories_attr]]);
                                        $extracted_subcategories = array_pop($subcategories) . ($extracted_subcategories ? $multiple_value_separator . $extracted_subcategories : "");
                                        $line[$map[$categories_attr]] = implode($multiple_value_separator, $subcategories);
                                    }
                                } while (!$category_map_found && $line[$map[$categories_attr]]);
                                $line[$map[$categories_attr]] .= (($line[$map[$categories_attr]] && $extracted_subcategories) ? $multiple_value_separator : "") . $extracted_subcategories;
                                $current_category_to_check .= $line_category;
                            }
                        }
                    }
                }
            }

            $products_rows = array();
            if (isset($line[$id_index]) && $line[$id_index]) {
                if ($this->model->find_products_by == 'reference') {
                    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product` p ";
                    if ($this->model->supplier_id) {
                        $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
                    }
                    $sql .= "WHERE p.`reference` = '" . pSQL($line[$id_index]) . "' ";
                    if ($this->model->supplier_id) {
                        $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id;
                    }
                    $products_rows = Db::getInstance()->executeS($sql);
                    if (!$products_rows || empty($products_rows)) {
                        // Find product id by combination reference
                        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `reference` = '" . pSQL($line[$id_index]) . "'";
                        $products_rows = Db::getInstance()->executeS($sql);
                    }
                } elseif ($this->model->find_products_by == 'id') {
                    $products_rows = array(
                        array('id_product' => $line[$id_index])
                    );
                } elseif ($this->model->find_products_by == 'ean') {
                    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product` p ";
                    if ($this->model->supplier_id) {
                        $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
                    }
                    $sql .= "WHERE p.`ean13` = '" . pSQL($line[$id_index]) . "' ";
                    if ($this->model->supplier_id) {
                        $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id;
                    }
                    $products_rows = Db::getInstance()->executeS($sql);
                } elseif ($this->model->find_products_by == 'supplier_reference') {
                    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product` p 
                        INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) 
                        WHERE ps.`product_supplier_reference` = '" . pSQL($line[$id_index]) . "' ";
                    if ($this->model->supplier_id) {
                        $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id;
                    }
                    $products_rows = Db::getInstance()->executeS($sql);
                }
            }

            if (empty($products_rows) || !is_array($products_rows)) {
                $products_rows = array(
                    array('id_product' => null)
                );
            }

            foreach ($products_rows as $product_row) {
                try {
                    $product = null;
                    $id_product_attribute = 0;
                    $product_categories_ids = array();

                    if ($product_row && isset($product_row['id_product']) && $product_row['id_product'] > 0) {
                        $product = new Product($product_row['id_product']);
                    }
                    if ($product_row && isset($product_row['id_product_attribute']) && $product_row['id_product_attribute'] > 0) {
                        $id_product_attribute = (int) $product_row['id_product_attribute'];
                    }

                    if (Validate::isLoadedObject($product)) {
                        if ($this->model->update_existing_products) {
                            $delete_product = ($map['delete_product'] >= 0 && isset($line[$map['delete_product']]) && $line[$map['delete_product']]) ? $line[$map['delete_product']] : $map_default_values['delete_product'];
                            if ($delete_product && $this->isCsvValueTrue($delete_product)) {
                                if ($product->delete() && $settings['employee_id_for_logging_product_events']) {
                                    PrestaShopLogger::addLog($this->l('Product deletion'), 1, null, 'Product', (int) $product->id, true, (int) $settings['employee_id_for_logging_product_events']);
                                }
                                continue;
                            }
                            // Load additional properties to the product
                            $product->quantity = (int) StockAvailable::getQuantityAvailableByProduct($product->id, $id_product_attribute);
                            $product->tax_rate = $product->getTaxesRate(new Address());
                            $product->unit_price = ($product->unit_price_ratio != 0 ? $product->price / $product->unit_price_ratio : 0);
                            $product->out_of_stock = StockAvailable::outOfStock($product->id);
                            $product->depends_on_stock = (int) StockAvailable::dependsOnStock($product->id);
                        } else {
                            $product = null;
                        }
                    } else {
                        if ($this->model->create_new_products && (($map['name'] >= 0 && isset($line[$map['name']]) && trim($line[$map['name']])) || (trim($map_default_values['name'])))) {
                            $product = new Product();
                            // Don't allow new product if price is less than MIN PRICE
                            if ($this->model->min_price_amount > 0) {
                                $price_tax_excluded = ($map['price_tax_excluded'] >= 0 && isset($line[$map['price_tax_excluded']]) && $line[$map['price_tax_excluded']]) ? $line[$map['price_tax_excluded']] : $map_default_values['price_tax_excluded'];
                                $price_tax_included = ($map['price_tax_included'] >= 0 && isset($line[$map['price_tax_included']]) && $line[$map['price_tax_included']]) ? $line[$map['price_tax_included']] : $map_default_values['price_tax_included'];
                                $discounted_price = ($map['discounted_price'] >= 0 && isset($line[$map['discounted_price']]) && $line[$map['discounted_price']]) ? $line[$map['discounted_price']] : $map_default_values['discounted_price'];
                                if (($price_tax_excluded > 0 && $price_tax_excluded < $this->model->min_price_amount) ||
                                    ($price_tax_included > 0 && $price_tax_included < $this->model->min_price_amount) ||
                                    ($discounted_price > 0 && $discounted_price < $this->model->min_price_amount)) {
                                    $product = null;
                                }
                            }
                        } else {
                            $product = null;
                        }
                    }

                    if (!$product) {
                        continue;
                    }

                    foreach ($map as $attr => $index) {
                        // Skip if neither mapped nor provided default value
                        if ($index < 0 && $map_default_values[$attr] === "") {
                            continue;
                        }
                        $value = isset($line[$index]) ? $line[$index] : "";
                        $value_default = isset($map_default_values[$attr]) ? $map_default_values[$attr] : "";
                        $value = ($value === "") ? trim($value_default) : trim($value);
                        switch ($attr) {
                            case 'reference':
                                if ($value && Validate::isReference($value)) {
                                    $product->reference = $value;
                                }
                                break;
                            case 'name':
                                if ($value) {
                                    foreach ($languages_model as $id_lang) {
                                        $product->name[$id_lang] = Tools::substr(preg_replace('/[<>;=#{}]*/', '', $value), 0, 128);
                                    }
                                }
                                break;
                            case 'enabled':
                                $product->active = $this->isCsvValueFalse($value) ? 0 : 1;
                                break;
                            case 'ean_no':
                                if ($value && Validate::isEan13($value)) {
                                    $product->ean13 = $value;
                                } else {
                                    $product->ean13 = "";
                                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('EAN is not valid.') . ' ' . $value . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                                }
                                break;
                            case 'upc_barcode':
                                $product->upc = ($value && Validate::isUpc($value)) ? $value : "";
                                break;
                            case 'isbn':
                                $value = str_replace(',', '.', $value);
                                $value = preg_replace('/[^0-9-]/', '', $value);
                                $product->isbn = $value ? Tools::substr($value, 0, 32) : "";
                                break;
                            case 'meta_title':
                                foreach ($languages_model as $id_lang) {
                                    $product->meta_title[$id_lang] = Tools::substr($value, 0, 128);
                                }
                                break;
                            case 'meta_description':
                                $value = strip_tags($value);
                                foreach ($languages_model as $id_lang) {
                                    $product->meta_description[$id_lang] = Tools::substr($value, 0, 255);
                                }
                                break;
                            case 'meta_keywords':
                                foreach ($languages_model as $id_lang) {
                                    $product->meta_keywords[$id_lang] = Tools::substr($value, 0, 255);
                                }
                                break;
                            case 'friendly_url':
                                if ($value) {
                                    foreach ($languages_model as $id_lang) {
                                        $product->link_rewrite[$id_lang] = Tools::link_rewrite(Tools::substr($value, 0, 128));
                                    }
                                }
                                break;
                            case 'short_description':
                                foreach ($languages_model as $id_lang) {
                                    $product->description_short[$id_lang] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", html_entity_decode($value));
                                }
                                break;
                            case 'long_description':
                                foreach ($languages_model as $id_lang) {
                                    $product->description[$id_lang] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", html_entity_decode($value));
                                }
                                break;
                            case 'wholesale_price':
                                $product->wholesale_price = (float) $this->extractPriceInDefaultCurrency($value);
                                break;
                            case 'tax_rules_group':
                                if (($index >= 0 && !$value) || ($index < 0 && !$value_default && $value_default !== "")) {
                                    $product->id_tax_rules_group = 0;
                                    // If there is no tax rule group, set tax rule group from default
                                    // This is not needed, as it is possible to set default value now
                                    // $product->id_tax_rules_group = (int) Product::getIdTaxRulesGroupMostUsed();
                                } elseif ($value) {
                                    $id_tax_rules_group = (int) TaxRulesGroup::getIdByName($value);
                                    if (!$id_tax_rules_group && Validate::isInt($value)) {
                                        $taxRulesGroup = new TaxRulesGroup($value);
                                        if (Validate::isLoadedObject($taxRulesGroup) && !$taxRulesGroup->deleted) {
                                            $id_tax_rules_group = (int) $value;
                                        }
                                    }
                                    if ($id_tax_rules_group) {
                                        $product->id_tax_rules_group = $id_tax_rules_group;
                                    }
                                }
                                break;
                            case 'price_tax_excluded':
                                $price_value = (float) $this->extractPriceInDefaultCurrency($value);
                                if ($price_value >= $this->model->min_price_amount) {
                                    $product->price = ElegantalEasyImportTools::getModifiedPriceByFormula($price_value, $this->model->price_modifier);
                                }
                                break;
                            case 'price_tax_included':
                                $price_value = (float) $this->extractPriceInDefaultCurrency($value);
                                if ($price_value >= $this->model->min_price_amount) {
                                    $product->price = ElegantalEasyImportTools::getModifiedPriceByFormula($price_value, $this->model->price_modifier);
                                    // Check if Tax Rule Group exists
                                    $taxRulesGroup = new TaxRulesGroup($product->id_tax_rules_group);
                                    if (!Validate::isLoadedObject($taxRulesGroup) || $taxRulesGroup->deleted) {
                                        $product->id_tax_rules_group = 0;
                                    }
                                    // If a tax is already included in price, withdraw it from price
                                    $tax_rate = $product->tax_rate;
                                    if ($product->id_tax_rules_group) {
                                        $address = Address::initialize();
                                        $tax_manager = TaxManagerFactory::getManager($address, $product->id_tax_rules_group);
                                        $tax_calculator = $tax_manager->getTaxCalculator();
                                        $tax_rate = $tax_calculator->getTotalRate();
                                    }
                                    if ($tax_rate) {
                                        $product->price = (float) number_format($product->price / (1 + $tax_rate / 100), 6, '.', '');
                                    }
                                }
                                break;
                            case 'unit_price':
                                $price_value = (float) $this->extractPriceInDefaultCurrency($value);
                                if ($price_value >= $this->model->min_price_amount) {
                                    $product->unit_price = $price_value;
                                }
                                break;
                            case 'unity':
                                $product->unity = $value;
                                break;
                            case 'ecotax':
                                $product->ecotax = Configuration::get('PS_USE_ECOTAX') ? (float) $this->extractPriceInDefaultCurrency($value) : 0;
                                break;
                            case 'advanced_stock_management':
                                $product->advanced_stock_management = $this->isCsvValueTrue($value) ? 1 : 0;
                                break;
                            case 'depends_on_stock':
                                $value = $this->isCsvValueTrue($value) ? 1 : 0;
                                if (!$product->advanced_stock_management) {
                                    $value = 0;
                                }
                                $product->depends_on_stock = $value;
                                break;
                            case 'quantity':
                                $quantity_value = Tools::strtolower($value);
                                if (empty($this->quantity_dictionary)) {
                                    $quantity_dictionary = $this->getSetting('text_quantity_dictionary');
                                    if ($quantity_dictionary) {
                                        $quantity_dictionary = preg_split("/\\r\\n|\\r|\\n/", $quantity_dictionary);
                                        if ($quantity_dictionary && is_array($quantity_dictionary)) {
                                            foreach ($quantity_dictionary as $dict) {
                                                $dict = explode('=>', $dict);
                                                if (isset($dict[0]) && isset($dict[1]) && $dict[0]) {
                                                    $dict[0] = Tools::strtolower(trim($dict[0]));
                                                    $this->quantity_dictionary[$dict[0]] = Tools::strtolower(trim($dict[1]));
                                                }
                                            }
                                        }
                                    }
                                }
                                if (isset($this->quantity_dictionary[$quantity_value]) && array_key_exists($quantity_value, $this->quantity_dictionary)) {
                                    $value = $this->quantity_dictionary[$quantity_value];
                                }
                                $product->quantity = (int) $value;
                                break;
                            case 'minimal_quantity':
                                if ($value && $value >= 1) {
                                    $product->minimal_quantity = (int) $value;
                                } elseif (($index >= 0 && !$value) || ($index < 0 && !$value_default && $value_default !== "")) {
                                    $product->minimal_quantity = 1;
                                }
                                break;
                            case 'action_when_out_of_stock':
                                $value = (int) $value;
                                $product->out_of_stock = ($value === 1 || $value === 0) ? $value : 2;
                                break;
                            case 'text_when_in_stock':
                                foreach ($languages_model as $id_lang) {
                                    $product->available_now[$id_lang] = Tools::substr(preg_replace('/[<>;=#{}]*/', '', $value), 0, 255);
                                }
                                break;
                            case 'text_when_backordering':
                                foreach ($languages_model as $id_lang) {
                                    $product->available_later[$id_lang] = Tools::substr(preg_replace('/[<>;=#{}]*/', '', $value), 0, 255);
                                }
                                break;
                            case 'availability_date':
                                if ($value && strtotime($value)) {
                                    $product->available_date = date('Y-m-d', strtotime($value));
                                } else {
                                    $product->available_date = null;
                                }
                                break;
                            case 'categories':
                            case 'category_1':
                            case 'category_2':
                            case 'category_3':
                            case 'category_4':
                            case 'category_5':
                            case 'category_6':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    $categoryNames = explode($multiple_value_separator, $value);
                                    $categoryNames = array_map('trim', $categoryNames);
                                    foreach ($categoryNames as $categoryName) {
                                        // If multiple_subcategory_separator is set, it means each category is path of categories
                                        if ($multiple_subcategory_separator) {
                                            $categories_arr = explode($multiple_subcategory_separator, $categoryName);
                                            $categories_arr = array_map('trim', $categories_arr);
                                            $count_categories_arr = count($categories_arr);
                                            if ($count_categories_arr > 1) {
                                                $id_parent_category2 = $rootCategory->id;
                                                foreach ($categories_arr as $key => $category_name) {
                                                    $categoryId = $this->getCategoryIdByName($category_name, $id_parent_category2);
                                                    if ($categoryId) {
                                                        $id_parent_category2 = $categoryId;
                                                        if (!in_array($categoryId, $product_categories_ids)) {
                                                            // Assign this cat if enabled by settings OR assign only the last cat
                                                            if ($this->model->is_associate_all_subcategories || ($count_categories_arr == ($key + 1))) {
                                                                $product_categories_ids[] = $categoryId;
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                $categoryId = $this->getCategoryIdByName($categoryName, $rootCategory->id);
                                                if ($categoryId && !in_array($categoryId, $product_categories_ids)) {
                                                    $product_categories_ids[] = $categoryId;
                                                }
                                            }
                                        } else {
                                            $id_parent_category = end($product_categories_ids);
                                            if (!$id_parent_category) {
                                                $id_parent_category = $this->model->is_first_parent_root_for_categories ? $rootCategory->id : null;
                                            }
                                            $categoryId = $this->getCategoryIdByName($categoryName, $id_parent_category);
                                            if ($categoryId && !in_array($categoryId, $product_categories_ids)) {
                                                $product_categories_ids[] = $categoryId;
                                            }
                                        }
                                    }
                                }
                                break;
                            case 'default_category':
                                if ($value) {
                                    // In case value is array, get the last element as default category
                                    $value_arr = explode($multiple_value_separator, $value);
                                    $value_arr = array_map('trim', $value_arr);
                                    if (is_array($value_arr) && count($value_arr) > 1) {
                                        $value = end($value_arr);
                                    }

                                    $value_arr2 = array();
                                    if ($multiple_subcategory_separator) {
                                        // Here $value is already last item of $value_arr
                                        $value_arr2 = explode($multiple_subcategory_separator, $value);
                                        $value_arr2 = array_map('trim', $value_arr2);
                                        $value = end($value_arr2);
                                    }

                                    // Need to find parent category of default category:
                                    $id_parent_category = null;

                                    // If default category column contains multiple categories:
                                    if ((is_array($value_arr) && count($value_arr) > 1) || $multiple_subcategory_separator) {
                                        // There is more than 1 category in array, so the first one must be under Home
                                        if ($this->model->is_first_parent_root_for_categories) {
                                            $id_parent_category = $rootCategory->id;
                                        }
                                        // If multiple_subcategory_separator is set, it means each category is path of categories
                                        if ($multiple_subcategory_separator) {
                                            if (is_array($value_arr2) && count($value_arr2) > 1) {
                                                foreach ($value_arr2 as $key => $value_name) {
                                                    if (!$value_name) {
                                                        continue;
                                                    }
                                                    if ($value_name == $value) {
                                                        break;
                                                    }
                                                    $categoryId = $this->getCategoryIdByName($value_name, $id_parent_category);
                                                    if (!$categoryId) {
                                                        continue;
                                                    }
                                                    $id_parent_category = $categoryId;
                                                }
                                            }
                                        } else {
                                            foreach ($value_arr as $key => $categoryName) {
                                                if (!$categoryName) {
                                                    continue;
                                                }
                                                if ($categoryName == $value) {
                                                    break;
                                                }
                                                $categoryId = $this->getCategoryIdByName($categoryName, $id_parent_category);
                                                if (!$categoryId) {
                                                    continue;
                                                }
                                                $id_parent_category = $categoryId;
                                            }
                                        }
                                    } elseif (isset($line[$map['categories']]) && $line[$map['categories']]) {
                                        // If default category column contains only one category and there is 'categories':
                                        $categoryNames = explode($multiple_value_separator, $line[$map['categories']]);
                                        $categoryNames = array_map('trim', $categoryNames);

                                        $default_category_exists_in_categories = false;

                                        // If there is only one category, parent id should be null. Otherwise first parent will be Home.
                                        $id_parent_category = null;
                                        if (count($categoryNames) > 1 && $this->model->is_first_parent_root_for_categories) {
                                            $id_parent_category = $rootCategory->id;
                                        }

                                        foreach ($categoryNames as $key => $categoryName) {
                                            if (!$categoryName) {
                                                continue;
                                            }
                                            if ($categoryName == $value) {
                                                $default_category_exists_in_categories = true;
                                                break;
                                            }

                                            // If multiple_subcategory_separator is set, it means each category is path of categories
                                            if ($multiple_subcategory_separator) {
                                                $categories_arr = explode($multiple_subcategory_separator, $categoryName);
                                                $categories_arr = array_map('trim', $categories_arr);
                                                $id_parent_category = $rootCategory->id;
                                                if (count($categories_arr) > 1) {
                                                    foreach ($categories_arr as $category_name) {
                                                        if (!$category_name) {
                                                            continue;
                                                        }
                                                        if ($category_name == $value) {
                                                            $default_category_exists_in_categories = true;
                                                            break 2;
                                                        }
                                                        $categoryId = $this->getCategoryIdByName($category_name, $id_parent_category);
                                                        if ($categoryId) {
                                                            $id_parent_category = $categoryId;
                                                        }
                                                    }
                                                }
                                            } else {
                                                $categoryId = $this->getCategoryIdByName($categoryName, $id_parent_category);
                                                if (!$categoryId) {
                                                    continue;
                                                }
                                                $id_parent_category = $categoryId;
                                            }
                                        }

                                        if (!$default_category_exists_in_categories) {
                                            $id_parent_category = $rootCategory->id;
                                        }
                                    }

                                    // If new product, it will be used later, that's why it is not inside if statement
                                    $product->id_category_default = $this->getCategoryIdByName($value, $id_parent_category);
                                }
                                break;
                            case 'manufacturer':
                                if ($value) {
                                    $product->id_manufacturer = $this->getManufacturerIdByName($value);
                                } else {
                                    $product->id_manufacturer = null;
                                }
                                break;
                            case 'package_width':
                            case 'package_height':
                            case 'package_depth':
                                $package_attr = str_replace('package_', '', $attr);
                                if ($value && preg_match("/^([0-9.]*)x([0-9.]*)x([0-9.]*)$/i", $value, $matches)) {
                                    $product->width = ($this->model->shipping_package_size_unit == 'm') ? ((float) $matches[1]) * 100 : (float) $matches[1];
                                    $product->height = ($this->model->shipping_package_size_unit == 'm') ? ((float) $matches[2]) * 100 : (float) $matches[2];
                                    $product->depth = ($this->model->shipping_package_size_unit == 'm') ? ((float) $matches[3]) * 100 : (float) $matches[3];
                                } else {
                                    if ($this->model->shipping_package_size_unit == 'm') { // Convert to cm
                                        $product->{$package_attr} = ((float) $value) * 100;
                                    } else {
                                        $product->{$package_attr} = (float) $value;
                                    }
                                }
                                break;
                            case 'package_weight':
                                if ($this->model->shipping_package_weight_unit == 'g') { // Convert to kg
                                    $product->weight = ((float) $value) / 1000;
                                } else {
                                    $product->weight = (float) $value;
                                }
                                break;
                            case 'additional_shipping_cost':
                                $product->additional_shipping_cost = (float) $this->extractPriceInDefaultCurrency($value);
                                break;
                            case 'additional_delivery_times':
                                $value = (int) $value;
                                $product->additional_delivery_times = ($value === 0 || $value === 2) ? $value : 1;
                                break;
                            case 'delivery_in_stock':
                                foreach ($languages_model as $id_lang) {
                                    $product->delivery_in_stock[$id_lang] = Tools::substr($value, 0, 255);
                                }
                                break;
                            case 'delivery_out_stock':
                                foreach ($languages_model as $id_lang) {
                                    $product->delivery_out_stock[$id_lang] = Tools::substr($value, 0, 255);
                                }
                                break;
                            case 'available_for_order':
                                $product->available_for_order = $this->isCsvValueFalse($value) ? 0 : 1;
                                if ($product->available_for_order) {
                                    $product->show_price = 1;
                                }
                                break;
                            case 'show_price':
                                if (!$product->available_for_order) {
                                    $product->show_price = $this->isCsvValueFalse($value) ? 0 : 1;
                                }
                                break;
                            case 'on_sale':
                                $product->on_sale = $this->isCsvValueTrue($value) ? 1 : 0;
                                break;
                            case 'condition':
                                $value = Tools::strtolower($value);
                                if ($value && in_array($value, array('new', 'used', 'refurbished'))) {
                                    $product->condition = $value;
                                } else {
                                    $product->condition = 'new';
                                }
                                break;
                            case 'customizable':
                                $product->customizable = $this->isCsvValueTrue($value) ? 1 : 0;
                                break;
                            case 'uploadable_files':
                                $product->uploadable_files = (int) $value;
                                break;
                            case 'text_fields':
                                $product->text_fields = (int) $value;
                                break;
                            case 'visibility':
                                $value = $value ? Tools::strtolower($value) : $value;
                                switch ($value) {
                                    case 'everywhere':
                                    case 'both':
                                        $product->visibility = 'both';
                                        break;
                                    case 'catalog only':
                                    case 'catalog':
                                        $product->visibility = 'catalog';
                                        break;
                                    case 'search only':
                                    case 'search':
                                        $product->visibility = 'search';
                                        break;
                                    case 'nowhere':
                                    case 'none':
                                        $product->visibility = 'none';
                                        break;
                                    default:
                                        $product->visibility = 'both';
                                        break;
                                }
                                break;
                            default:
                                break;
                        }
                    }

                    // Make update on all shops
                    if ($update_products_on_all_shops && Shop::getContext() != Shop::CONTEXT_ALL) {
                        Shop::setContext(Shop::CONTEXT_ALL);
                    }

                    if ($map['enabled'] < 0) {
                        if ($this->model->enable_if_have_stock && $product->quantity >= 1) {
                            $product->active = 1;
                        } elseif ($this->model->disable_if_no_stock && $product->quantity <= 0) {
                            $product->active = 0;
                        } elseif (!$product->id && $this->model->enable_new_products_by_default) {
                            $product->active = 1;
                        } elseif (!$product->id && !$this->model->enable_new_products_by_default) {
                            $product->active = 0;
                        }
                    }

                    if ($this->model->find_products_by == 'reference' && isset($line[$id_index]) && empty($product->reference)) {
                        $product->reference = preg_replace('/[<>;={}]*/', '', $line[$id_index]);
                    }
                    if ($this->model->find_products_by == 'ean' && isset($line[$id_index]) && empty($product->ean13) && Validate::isEan13($line[$id_index])) {
                        $product->ean13 = $line[$id_index];
                    }

                    foreach ($languages_all as $language) {
                        if (empty($product->name[$language['id_lang']])) {
                            $product->name[$language['id_lang']] = Tools::link_rewrite(Tools::substr($product->name[$this->model->lang_id], 0, 128));
                        }
                        if (empty($product->link_rewrite[$language['id_lang']])) {
                            $product->link_rewrite[$language['id_lang']] = Tools::link_rewrite(Tools::substr($product->name[$language['id_lang']], 0, 128));
                        }
                    }

                    // If product has no default category, select it from the last category in categories tree
                    if (!empty($product_categories_ids) && ($map['default_category'] < 0 || !isset($line[$map['default_category']]) || !$line[$map['default_category']]) && !$map_default_values['default_category']) {
                        $product->id_category_default = end($product_categories_ids);
                    }

                    // If product has no default category, add it to Home category
                    if (!$product->id_category_default) {
                        $product->id_category_default = $rootCategory->id;
                    }

                    $product->customizable = ($product->uploadable_files > 0 || $product->text_fields > 0) ? 1 : $product->customizable;

                    if ($product->id) {
                        if (!$product->update()) {
                            throw new Exception(Db::getInstance()->getMsgError());
                        }
                        if (_PS_VERSION_ < '1.7' && Shop::isFeatureActive()) {
                            // This is needed to update shop fields. This is not needed in PS 1.7. Probably a bug in PS 1.6.
                            $product->setFieldsToUpdate($product->getFieldsShop());
                            $product->update();
                        }
                        if ($settings['employee_id_for_logging_product_events']) {
                            PrestaShopLogger::addLog($this->l('Product modification'), 1, null, 'Product', (int) $product->id, true, (int) $settings['employee_id_for_logging_product_events']);
                        }
                    } elseif ($product->add()) {
                        if ($this->model->supplier_id && ($map['supplier'] < 0 || !isset($line[$map['supplier']]) || !$line[$map['supplier']]) && !$map_default_values['supplier']) {
                            $supplier_references = null;
                            $supplier_prices = null;
                            if (isset($line[$map['supplier_reference']]) && $line[$map['supplier_reference']]) {
                                $supplier_references = $line[$map['supplier_reference']];
                                if ($map_default_values['supplier_reference']) {
                                    $supplier_references .= $multiple_value_separator . $map_default_values['supplier_reference'];
                                }
                            } elseif ($map_default_values['supplier_reference']) {
                                $supplier_references = $map_default_values['supplier_reference'];
                            }
                            if (isset($line[$map['supplier_price']]) && $line[$map['supplier_price']]) {
                                $supplier_prices = $line[$map['supplier_price']];
                                if ($map_default_values['supplier_price']) {
                                    $supplier_prices .= $multiple_value_separator . $map_default_values['supplier_price'];
                                }
                            } elseif ($map_default_values['supplier_price']) {
                                $supplier_prices = $map_default_values['supplier_price'];
                            }
                            $this->createProductSuppliers($product, $this->model->supplier_id, $supplier_references, $supplier_prices, $multiple_value_separator);
                        }
                        if (in_array($product->visibility, array('both', 'search')) && Configuration::get('PS_SEARCH_INDEXATION')) {
                            Search::indexation(false, $product->id);
                        }
                        if ($settings['employee_id_for_logging_product_events']) {
                            PrestaShopLogger::addLog($this->l('Product addition'), 1, null, 'Product', (int) $product->id, true, (int) $settings['employee_id_for_logging_product_events']);
                        }
                    } else {
                        throw new Exception(Db::getInstance()->getMsgError());
                    }

                    // Continue processing the rest of columns in mapping that require $product->id
                    foreach ($map as $attr => $index) {
                        // Skip if neither mapped nor provided default value
                        if ($index < 0 && $map_default_values[$attr] === "") {
                            continue;
                        }
                        $value = isset($line[$index]) ? $line[$index] : "";
                        $value_default = isset($map_default_values[$attr]) ? $map_default_values[$attr] : "";
                        $value = ($value === "") ? trim($value_default) : trim($value);
                        switch ($attr) {
                            case 'delete_existing_discount':
                                if ($value && $this->isCsvValueTrue($value)) {
                                    SpecificPrice::deleteByProductId($product->id);
                                }
                                break;
                            case 'discount_amount':
                            case 'discount_percent':
                                $is_percentage = (strpos($value, '%') !== false || $attr == 'discount_percent') ? true : false;
                                $discount_from = '0000-00-00 00:00:00';
                                $discount_to = '0000-00-00 00:00:00';
                                $is_discount_tax_included = 1;
                                $discount_base_price = (isset($line[$map['discount_base_price']]) && $line[$map['discount_base_price']]) ? $line[$map['discount_base_price']] : $map_default_values['discount_base_price'];
                                $discount_base_price = (float) $this->extractPriceInDefaultCurrency($discount_base_price);
                                $discount_starting_unit = (isset($line[$map['discount_starting_unit']]) && $line[$map['discount_starting_unit']]) ? $line[$map['discount_starting_unit']] : $map_default_values['discount_starting_unit'];
                                $discount_customer_group = (isset($line[$map['discount_customer_group']]) && $line[$map['discount_customer_group']]) ? $line[$map['discount_customer_group']] : $map_default_values['discount_customer_group'];
                                $discount_country = (isset($line[$map['discount_country']]) && $line[$map['discount_country']]) ? $line[$map['discount_country']] : $map_default_values['discount_country'];
                                $discount_currency = (isset($line[$map['discount_currency']]) && $line[$map['discount_currency']]) ? $line[$map['discount_currency']] : $map_default_values['discount_currency'];
                                if (isset($line[$map['discount_from']]) && $line[$map['discount_from']]) {
                                    $discount_from = date('Y-m-d H:i:s', strtotime($line[$map['discount_from']]));
                                } elseif ($map_default_values['discount_from']) {
                                    $discount_from = date('Y-m-d H:i:s', strtotime($map_default_values['discount_from']));
                                }
                                if (isset($line[$map['discount_to']]) && $line[$map['discount_to']]) {
                                    $discount_to = date('Y-m-d H:i:s', strtotime($line[$map['discount_to']]));
                                } elseif ($map_default_values['discount_to']) {
                                    $discount_to = date('Y-m-d H:i:s', strtotime($map_default_values['discount_to']));
                                }
                                if (isset($line[$map['discount_tax_included']]) && $line[$map['discount_tax_included']] !== "") {
                                    $is_discount_tax_included = $this->isCsvValueFalse($line[$map['discount_tax_included']]) ? 0 : 1;
                                } elseif ($map_default_values['discount_tax_included'] !== "") {
                                    $is_discount_tax_included = $this->isCsvValueFalse($map_default_values['discount_tax_included']) ? 0 : 1;
                                }
                                $this->createProductSpecificPrice($product->id, $value, $is_percentage, $is_discount_tax_included, $discount_from, $discount_to, $discount_base_price, $discount_starting_unit, $discount_customer_group, $discount_country, $discount_currency);
                                break;
                            case 'discounted_price':
                                $discounted_price = (float) $this->extractPriceInDefaultCurrency($value);
                                $is_discount_tax_included = 1;
                                if (isset($line[$map['discount_tax_included']]) && $line[$map['discount_tax_included']] !== "") {
                                    $is_discount_tax_included = $this->isCsvValueFalse($line[$map['discount_tax_included']]) ? 0 : 1;
                                } elseif ($map_default_values['discount_tax_included'] !== "") {
                                    $is_discount_tax_included = $this->isCsvValueFalse($map_default_values['discount_tax_included']) ? 0 : 1;
                                }
                                if ($is_discount_tax_included) {
                                    // Check if Tax Rule Group exists
                                    $taxRulesGroup = new TaxRulesGroup($product->id_tax_rules_group);
                                    if (!Validate::isLoadedObject($taxRulesGroup) || $taxRulesGroup->deleted) {
                                        $product->id_tax_rules_group = null;
                                    }
                                    // If a tax is already included in price, withdraw it from price
                                    $tax_rate = $product->tax_rate;
                                    if ($product->id_tax_rules_group) {
                                        $address = Address::initialize();
                                        $tax_manager = TaxManagerFactory::getManager($address, $product->id_tax_rules_group);
                                        $tax_calculator = $tax_manager->getTaxCalculator();
                                        $tax_rate = $tax_calculator->getTotalRate();
                                    }
                                    if ($tax_rate) {
                                        $discounted_price = (float) number_format($discounted_price / (1 + $tax_rate / 100), 6, '.', '');
                                    }
                                }

                                $discount_base_price = (isset($line[$map['discount_base_price']]) && $line[$map['discount_base_price']]) ? $line[$map['discount_base_price']] : $map_default_values['discount_base_price'];
                                $discount_base_price = (float) $this->extractPriceInDefaultCurrency($discount_base_price);
                                $discount_starting_unit = (isset($line[$map['discount_starting_unit']]) && $line[$map['discount_starting_unit']]) ? $line[$map['discount_starting_unit']] : $map_default_values['discount_starting_unit'];
                                $discount_customer_group = (isset($line[$map['discount_customer_group']]) && $line[$map['discount_customer_group']]) ? $line[$map['discount_customer_group']] : $map_default_values['discount_customer_group'];
                                $discount_country = (isset($line[$map['discount_country']]) && $line[$map['discount_country']]) ? $line[$map['discount_country']] : $map_default_values['discount_country'];
                                $discount_currency = (isset($line[$map['discount_currency']]) && $line[$map['discount_currency']]) ? $line[$map['discount_currency']] : $map_default_values['discount_currency'];
                                $discount_from = '0000-00-00 00:00:00';
                                $discount_to = '0000-00-00 00:00:00';
                                if (isset($line[$map['discount_from']]) && $line[$map['discount_from']]) {
                                    $discount_from = date('Y-m-d H:i:s', strtotime($line[$map['discount_from']]));
                                } elseif ($map_default_values['discount_from']) {
                                    $discount_from = date('Y-m-d H:i:s', strtotime($map_default_values['discount_from']));
                                }
                                if (isset($line[$map['discount_to']]) && $line[$map['discount_to']]) {
                                    $discount_to = date('Y-m-d H:i:s', strtotime($line[$map['discount_to']]));
                                } elseif ($map_default_values['discount_to']) {
                                    $discount_to = date('Y-m-d H:i:s', strtotime($map_default_values['discount_to']));
                                }

                                if ((isset($line[$map['price_tax_excluded']]) || isset($line[$map['price_tax_included']]) || $map_default_values['price_tax_excluded'] || $map_default_values['price_tax_included']) &&
                                    (!isset($line[$map['discount_amount']]) && !isset($line[$map['discount_percent']]) && !$map_default_values['discount_amount'] && !$map_default_values['discount_percent']) &&
                                    $product->price > $discounted_price) {
                                    // Discount amount
                                    $discount_amount = round($product->price - $discounted_price, 6);
                                    $this->createProductSpecificPrice($product->id, $discount_amount, false, false, $discount_from, $discount_to, $discount_base_price, $discount_starting_unit, $discount_customer_group, $discount_country, $discount_currency);
                                } elseif ((!isset($line[$map['price_tax_excluded']]) && !isset($line[$map['price_tax_included']]) && !$map_default_values['price_tax_excluded'] && !$map_default_values['price_tax_included']) &&
                                    (isset($line[$map['discount_amount']]) && $discounted_price > $line[$map['discount_amount']]) || ($map_default_values['discount_amount'] && $discounted_price > $map_default_values['discount_amount'])) {
                                    // Product price
                                    $discount_amount = isset($line[$map['discount_amount']]) ? $line[$map['discount_amount']] : $map_default_values['discount_amount'];
                                    $product->price = round($discounted_price + $discount_amount, 6);
                                    $product->update();
                                } elseif ((!isset($line[$map['price_tax_excluded']]) && !isset($line[$map['price_tax_included']]) && !$map_default_values['price_tax_excluded'] && !$map_default_values['price_tax_included']) &&
                                    (isset($line[$map['discount_percent']]) || $map_default_values['discount_percent'])) {
                                    // Product price
                                    $discount_percent = isset($line[$map['discount_percent']]) ? $line[$map['discount_percent']] : $map_default_values['discount_percent'];
                                    if (preg_match('/([0-9]+\.{0,1}[0-9]*)/', $discount_percent, $match)) {
                                        $discount_percent = $match[0];
                                    }
                                    if ($discount_percent > 0 && $discount_percent < 1) {
                                        $discount_percent = $discount_percent * 100;
                                    }
                                    $product->price = round($discounted_price / (1 - $discount_percent / 100), 6);
                                    $product->update();
                                }
                                break;
                            case 'discount_base_price':
                                if ($value && !isset($line[$map['discount_amount']]) && $map_default_values['discount_amount'] === "" && !isset($line[$map['discount_percent']]) && $map_default_values['discount_percent'] === "") {
                                    $discount_from = '0000-00-00 00:00:00';
                                    $discount_to = '0000-00-00 00:00:00';
                                    $discount_base_price = (isset($line[$map['discount_base_price']]) && $line[$map['discount_base_price']]) ? $line[$map['discount_base_price']] : $map_default_values['discount_base_price'];
                                    $discount_base_price = (float) $this->extractPriceInDefaultCurrency($discount_base_price);
                                    $discount_starting_unit = (isset($line[$map['discount_starting_unit']]) && $line[$map['discount_starting_unit']]) ? $line[$map['discount_starting_unit']] : $map_default_values['discount_starting_unit'];
                                    $discount_customer_group = (isset($line[$map['discount_customer_group']]) && $line[$map['discount_customer_group']]) ? $line[$map['discount_customer_group']] : $map_default_values['discount_customer_group'];
                                    $discount_country = (isset($line[$map['discount_country']]) && $line[$map['discount_country']]) ? $line[$map['discount_country']] : $map_default_values['discount_country'];
                                    $discount_currency = (isset($line[$map['discount_currency']]) && $line[$map['discount_currency']]) ? $line[$map['discount_currency']] : $map_default_values['discount_currency'];
                                    if (isset($line[$map['discount_from']]) && $line[$map['discount_from']]) {
                                        $discount_from = date('Y-m-d H:i:s', strtotime($line[$map['discount_from']]));
                                    } elseif ($map_default_values['discount_from']) {
                                        $discount_from = date('Y-m-d H:i:s', strtotime($map_default_values['discount_from']));
                                    }
                                    if (isset($line[$map['discount_to']]) && $line[$map['discount_to']]) {
                                        $discount_to = date('Y-m-d H:i:s', strtotime($line[$map['discount_to']]));
                                    } elseif ($map_default_values['discount_to']) {
                                        $discount_to = date('Y-m-d H:i:s', strtotime($map_default_values['discount_to']));
                                    }
                                    $this->createProductSpecificPrice($product->id, null, 0, 0, $discount_from, $discount_to, $discount_base_price, $discount_starting_unit, $discount_customer_group, $discount_country, $discount_currency);
                                }
                                break;
                            case 'depends_on_stock':
                                StockAvailable::setProductDependsOnStock($product->id, $product->depends_on_stock);
                                break;
                            case 'warehouse_id':
                                if ($value && $product->advanced_stock_management) {
                                    if (Warehouse::exists($value)) {
                                        $product->warehouse = (int) $value;
                                        $query = new DbQuery();
                                        $query->select('id_warehouse_product_location');
                                        $query->from('warehouse_product_location');
                                        $query->where("id_product = " . (int) $product->id . " AND id_product_attribute = " . (int) $id_product_attribute . " AND id_warehouse = " . (int) $product->warehouse);
                                        $warehouse_product_location = (int) Db::getInstance()->getValue($query);
                                        if ($warehouse_product_location) {
                                            $wpl = new WarehouseProductLocation($warehouse_product_location);
                                            $wpl->location = (isset($line[$map['location_in_warehouse']]) && $line[$map['location_in_warehouse']]) ? $line[$map['location_in_warehouse']] : $map_default_values['location_in_warehouse'];
                                            $wpl->update();
                                        } else {
                                            $wpl = new WarehouseProductLocation();
                                            $wpl->id_product = $product->id;
                                            $wpl->id_product_attribute = $id_product_attribute;
                                            $wpl->id_warehouse = (int) $product->warehouse;
                                            $wpl->location = (isset($line[$map['location_in_warehouse']]) && $line[$map['location_in_warehouse']]) ? $line[$map['location_in_warehouse']] : $map_default_values['location_in_warehouse'];
                                            $wpl->add();
                                        }
                                        StockAvailable::synchronize($product->id);
                                    } else {
                                        $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Warehouse does not exist with this ID') . ' ' . $value . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                                    }
                                }
                                break;
                            case 'quantity':
                                if ($product->advanced_stock_management && $product->depends_on_stock) {
                                    if (empty($product->warehouse)) {
                                        $query = new DbQuery();
                                        $query->select('id_warehouse');
                                        $query->from('warehouse_product_location');
                                        $query->where('id_product = ' . (int) $product->id . ' AND id_product_attribute = ' . (int) $id_product_attribute);
                                        $product->warehouse = (int) Db::getInstance()->getValue($query);
                                    }
                                    if ($product->warehouse) {
                                        $stock_manager = StockManagerFactory::getManager();
                                        $price = str_replace(',', '.', $product->wholesale_price);
                                        if ($price == 0) {
                                            $price = 0.000001;
                                        }
                                        $price = round((float) $price, 6);
                                        $warehouse = new Warehouse($product->warehouse);
                                        if ($stock_manager->addProduct($product->id, $id_product_attribute, $warehouse, (int) $product->quantity, 1, $price, true)) {
                                            StockAvailable::synchronize($product->id);
                                        }
                                    } else {
                                        $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Warehouse is missing') . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                                    }
                                } else {
                                    $out_of_stock = false;
                                    if (Tools::strtolower($value) == 'outofstock' || Tools::strtolower($value) == 'n') {
                                        $out_of_stock = 0;
                                    } elseif (Tools::strtolower($value) == 'backorder') {
                                        $out_of_stock = 1;
                                    }
                                    $tmp_context_shop = Shop::getContext();
                                    Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
                                    if (!empty($shop_ids)) {
                                        foreach ($shop_ids as $sh_id) {
                                            StockAvailable::setQuantity($product->id, $id_product_attribute, (int) $product->quantity, $sh_id);
                                            if ($out_of_stock !== false) {
                                                StockAvailable::setProductOutOfStock($product->id, $out_of_stock, $sh_id, $id_product_attribute);
                                            }
                                        }
                                    } else {
                                        StockAvailable::setQuantity($product->id, $id_product_attribute, (int) $product->quantity, $id_shop);
                                        if ($out_of_stock !== false) {
                                            StockAvailable::setProductOutOfStock($product->id, $out_of_stock, $id_shop, $id_product_attribute);
                                        }
                                    }
                                    // Check if product has combination. If yes, update combination qty
                                    // This is needed to trigger StockAvailable->postSave to upgrade total_quantity_available after
                                    if ($id_product_attribute == 0) {
                                        $combinations = $product->getAttributeCombinations($this->context->language->id);
                                        if ($combinations && is_array($combinations) && is_array($combinations[0]) && isset($combinations[0]['id_product_attribute']) && $this->model->find_products_by == 'reference' && isset($line[$id_index]) && $line[$id_index]) {
                                            // Check if there is combination with the same reference
                                            // If exists, use it as id_product_attribute
                                            // Otherwise just update first combination to trigger StockAvailable->postSave
                                            $row = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `reference` = '" . pSQL($line[$id_index]) . "'");
                                            if ($row && isset($row['id_product']) && isset($row['id_product_attribute']) && $row['id_product'] == $product->id) {
                                                $id_product_attribute_tmp = $row['id_product_attribute'];
                                            } else {
                                                $id_product_attribute_tmp = $combinations[0]['id_product_attribute'];
                                                $product->quantity = (int) $combinations[0]['quantity'];
                                            }
                                            if (!empty($shop_ids)) {
                                                foreach ($shop_ids as $sh_id) {
                                                    StockAvailable::setQuantity($product->id, $id_product_attribute_tmp, (int) $product->quantity, $sh_id);
                                                }
                                            } else {
                                                StockAvailable::setQuantity($product->id, $id_product_attribute_tmp, (int) $product->quantity, $id_shop);
                                            }
                                        }
                                    }
                                    Shop::setContext($tmp_context_shop, $id_shop);
                                }
                                break;
                            case 'action_when_out_of_stock':
                                $tmp_context_shop = Shop::getContext();
                                Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
                                if (!empty($shop_ids)) {
                                    foreach ($shop_ids as $sh_id) {
                                        StockAvailable::setProductOutOfStock((int) $product->id, $product->out_of_stock, $sh_id, (int) $id_product_attribute);
                                    }
                                } else {
                                    StockAvailable::setProductOutOfStock((int) $product->id, $product->out_of_stock, $id_shop, (int) $id_product_attribute);
                                }
                                Shop::setContext($tmp_context_shop, $id_shop);
                                break;
                            case 'delete_existing_images':
                                if ($value && $this->isCsvValueTrue($value)) {
                                    $product->deleteImages();
                                    Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "image_shop WHERE id_image NOT IN (SELECT id_image FROM " . _DB_PREFIX_ . "image)");
                                }
                                break;
                            case 'product_images':
                            case 'image_1':
                            case 'image_2':
                            case 'image_3':
                            case 'image_4':
                            case 'image_5':
                            case 'image_6':
                            case 'image_7':
                            case 'image_8':
                            case 'image_9':
                            case 'image_10':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    $captions = "";
                                    if ($attr == 'product_images') {
                                        if (isset($line[$map['image_captions']]) && $line[$map['image_captions']]) {
                                            $captions = $line[$map['image_captions']];
                                        } elseif ($map_default_values['image_captions']) {
                                            $captions = $map_default_values['image_captions'];
                                        }
                                    }
                                    $this->createProductImages($product, $value, $multiple_value_separator, $captions);
                                }
                                break;
                            case 'delete_existing_features':
                                if ($value && $this->isCsvValueTrue($value)) {
                                    $product->deleteFeatures();
                                }
                                break;
                            case 'features':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    foreach ($languages_model as $id_lang) {
                                        $this->createProductFeatures($product, $value, $multiple_value_separator, $id_lang);
                                    }
                                }
                                break;
                            case 'feature_1':
                            case 'feature_2':
                            case 'feature_3':
                            case 'feature_4':
                            case 'feature_5':
                            case 'feature_6':
                            case 'feature_7':
                            case 'feature_8':
                            case 'feature_9':
                            case 'feature_10':
                                if ($value && $index >= 0 && isset($csv_header[$index]) && $csv_header[$index]) {
                                    $value = $csv_header[$index] . ':' . $value;
                                    if ($value_default) {
                                        $value .= $value ? $multiple_value_separator : '';
                                        $value .= $value_default;
                                    }
                                }
                                if ($value) {
                                    foreach ($languages_model as $id_lang) {
                                        $this->createProductFeatures($product, $value, $multiple_value_separator, $id_lang);
                                    }
                                }
                                break;
                            case 'tags':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    foreach ($languages_model as $id_lang) {
                                        $this->createProductTags($product->id, $value, $multiple_value_separator, $id_lang, $settings['is_debug_mode']);
                                    }
                                }
                                break;
                            case 'accessories':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    $this->createProductAccessories($product, $value, $multiple_value_separator);
                                }
                                break;
                            case 'delete_existing_attachments':
                                if ($value && $this->isCsvValueTrue($value)) {
                                    $attachments = Attachment::getAttachments($this->model->lang_id, $product->id);
                                    if ($attachments && is_array($attachments)) {
                                        foreach ($attachments as $attachment) {
                                            $is_attached_to_other_product = false;
                                            $sql = "SELECT `id_product` FROM `" . _DB_PREFIX_ . "product_attachment` WHERE `id_attachment` = " . (int) $attachment['id_attachment'];
                                            $attachment_products = Db::getInstance()->executeS($sql);
                                            if ($attachment_products && is_array($attachment_products)) {
                                                foreach ($attachment_products as $attachment_product) {
                                                    if ($attachment_product['id_product'] != $product->id) {
                                                        $is_attached_to_other_product = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            if (!$is_attached_to_other_product) {
                                                $attachmentObj = new Attachment((int) $attachment['id_attachment']);
                                                if (!Validate::isLoadedObject($attachmentObj) || !$attachmentObj->delete()) {
                                                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Failed to delete attachment') . ' ID: ' . $attachment['id_attachment'] . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                                                }
                                            }
                                        }
                                        Attachment::deleteProductAttachments($product->id);
                                    }
                                }
                                break;
                            case 'attachments':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    $this->createProductAttachments($product, $value, $multiple_value_separator);
                                }
                                break;
                            case 'carriers':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value !== "") {
                                    $this->createProductCarriers($product, $value, $multiple_value_separator);
                                }
                                break;
                            case 'supplier':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    $supplier_references = null;
                                    $supplier_prices = null;
                                    if (isset($line[$map['supplier_reference']]) && $line[$map['supplier_reference']]) {
                                        $supplier_references = $line[$map['supplier_reference']];
                                        if ($map_default_values['supplier_reference']) {
                                            $supplier_references .= $multiple_value_separator . $map_default_values['supplier_reference'];
                                        }
                                    } elseif ($map_default_values['supplier_reference']) {
                                        $supplier_references = $map_default_values['supplier_reference'];
                                    }
                                    if (isset($line[$map['supplier_price']]) && $line[$map['supplier_price']]) {
                                        $supplier_prices = $line[$map['supplier_price']];
                                        if ($map_default_values['supplier_price']) {
                                            $supplier_prices .= $multiple_value_separator . $map_default_values['supplier_price'];
                                        }
                                    } elseif ($map_default_values['supplier_price']) {
                                        $supplier_prices = $map_default_values['supplier_price'];
                                    }
                                    $this->createProductSuppliers($product, $value, $supplier_references, $supplier_prices, $multiple_value_separator);
                                }
                                break;
                            case 'delete_existing_customize_fields':
                                if ($value && $this->isCsvValueTrue($value)) {
                                    $sql = "DELETE `" . _DB_PREFIX_ . "customization_field`, `" . _DB_PREFIX_ . "customization_field_lang` 
                                        FROM `" . _DB_PREFIX_ . "customization_field` 
                                        INNER JOIN `" . _DB_PREFIX_ . "customization_field_lang` ON `" . _DB_PREFIX_ . "customization_field`.`id_customization_field` = `" . _DB_PREFIX_ . "customization_field_lang`.`id_customization_field` 
                                        WHERE `" . _DB_PREFIX_ . "customization_field`.`id_product` = " . (int) $product->id;
                                    if (Db::getInstance()->execute($sql)) {
                                        Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', Customization::isCurrentlyUsed());
                                    }
                                }
                                break;
                            case 'uploadable_files':
                                if ($value) {
                                    $uploadable_files_labels = (isset($line[$map['uploadable_files_labels']]) && $line[$map['uploadable_files_labels']]) ? $line[$map['uploadable_files_labels']] : $map_default_values['uploadable_files_labels'];
                                    $text_fields = (isset($line[$map['text_fields']]) && $line[$map['text_fields']]) ? $line[$map['text_fields']] : $map_default_values['text_fields'];
                                    $text_fields_labels = (isset($line[$map['text_fields_labels']]) && $line[$map['text_fields_labels']]) ? $line[$map['text_fields_labels']] : $map_default_values['text_fields_labels'];
                                    $this->createProductCustomizableFields($product, $value, $uploadable_files_labels, $text_fields, $text_fields_labels, $multiple_value_separator);
                                }
                                break;
                            case 'text_fields':
                                $uploadable_files = (isset($line[$map['uploadable_files']]) && $line[$map['uploadable_files']]) ? $line[$map['uploadable_files']] : $map_default_values['uploadable_files'];
                                if ($value && !$uploadable_files) {
                                    $text_fields_labels = (isset($line[$map['text_fields_labels']]) && $line[$map['text_fields_labels']]) ? $line[$map['text_fields_labels']] : $map_default_values['text_fields_labels'];
                                    $this->createProductCustomizableFields($product, "", "", $value, $text_fields_labels, $multiple_value_separator);
                                }
                                break;
                            case 'fsproductvideo_url':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    $this->createFsProductVideoUrls($product, $value, $multiple_value_separator);
                                }
                                break;
                            case 'additionalproductsorder_ids':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    $this->createAdditionalproductsorderRelation($product, $value, $multiple_value_separator);
                                }
                                break;
                            case 'jmarketplace_seller_id':
                                if ($value) {
                                    $this->createJmarketplaceSellerRelation($product->id, $value);
                                }
                                break;
                            case 'productaffiliate_external_shop_url':
                                if ($value && (isset($line[$map['productaffiliate_button_text']]) || isset($map_default_values['productaffiliate_button_text']))) {
                                    $productaffiliate_button_text = isset($line[$map['productaffiliate_button_text']]) ? $line[$map['productaffiliate_button_text']] : $map_default_values['productaffiliate_button_text'];
                                    $this->createProductaffiliateRelation($product->id, $this->model->lang_id, $value, $productaffiliate_button_text);
                                }
                                break;
                        }
                    }

                    if ($product->advanced_stock_management == 0 && StockAvailable::dependsOnStock($product->id) == 1) {
                        StockAvailable::setProductDependsOnStock($product->id, 0);
                    }

                    if (!empty($product_categories_ids)) {
                        $product->updateCategories($product_categories_ids);
                    }
                    if ($product->id_category_default) {
                        $category_exists = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "category_product` WHERE `id_category` = " . (int) $product->id_category_default . " AND `id_product` = " . (int) $product->id, false);
                        if (!$category_exists) {
                            $product->addToCategories($product->id_category_default);
                        }
                    }

                    if (Module::isInstalled('advancedcustomfields')) {
                        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "advanced_custom_fields` WHERE `location` = 'product'";
                        $acfs = Db::getInstance()->executeS($sql);
                        if ($acfs && is_array($acfs)) {
                            foreach ($acfs as $acf) {
                                $attr = 'acf_' . $acf['technical_name'];
                                $index = isset($map[$attr]) ? $map[$attr] : -1;
                                $value = null;
                                if ($index >= 0 && isset($line[$index]) && $line[$index]) {
                                    $value = $line[$index];
                                } elseif ($map_default_values[$attr] !== "") {
                                    $value = $map_default_values[$attr];
                                }
                                if ($value) {
                                    $this->createAdvancedcustomfieldsValue($product->id, $acf['technical_name'], $value);
                                }
                            }
                        }
                    }

                    // Set context shop back to its original value
                    Shop::setContext($context_shop, $id_shop);
                } catch (Exception $e) {
                    $error = $e->getMessage() . '. ' . (!empty($product->id) ? 'Product ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $error;
                    if ($settings['is_debug_mode']) {
                        $this->model->last_import_date = date('Y-m-d H:i:s');
                        $this->model->update();
                        throw new Exception($error);
                    }
                }
            }
        }

        $this->model->last_import_date = date('Y-m-d H:i:s');
        $this->model->update();

        // Action to call at the end of the import process if the import was by CRON and there was rows imported above
        if ($this->model->is_cron && $csvRows) {
            $this->actionAfterImport();
        }

        return true;
    }

    /**
     * Imports combination data from CSV rows.
     * It is not possible to make option to delete old combinations,
     * because in combinations CSV, multiple rows of combinations would exist for each product.
     * Every row does not mean individual product.
     * If I delete old combinations during import, then only last combination would remain after import.
     * That is why I made general option to delete old combinations before import starts.
     * @param int $limit
     * @return boolean
     * @throws Exception
     */
    public function importCombinationDataFromCsv($limit)
    {
        if (!$this->model) {
            throw new Exception($this->l('Record not found.'));
        }

        $map = ElegantalEasyImportTools::unserialize($this->model->map);
        if (empty($map)) {
            throw new Exception('Map not found.');
        }
        $map = array_merge($this->defaultMapCombinations, $map);
        $map_default_values = ElegantalEasyImportTools::unserialize($this->model->map_default_values);
        $file = ElegantalEasyImportTools::getRealPath($this->model->csv_file);
        $csv_header = $this->getCsvHeaderRow($file);
        $id_shop = $this->context->shop->id;
        $context_shop = Shop::getContext();
        $settings = $this->getSettings();
        $update_products_on_all_shops = $this->model->update_products_on_all_shops && Shop::isFeatureActive();
        $multiple_value_separator = $this->model->multiple_value_separator;
        $default_lang_id = Configuration::get('PS_LANG_DEFAULT');

        $shop_ids = array();
        if ($update_products_on_all_shops) {
            $shop_groups = Shop::getTree();
            foreach ($shop_groups as $shop_group) {
                foreach ($shop_group['shops'] as $shop) {
                    $shop_ids[] = $shop['id_shop'];
                }
            }
        }

        $groups = array();
        $attributes_groups = AttributeGroup::getAttributesGroups($default_lang_id);
        foreach ($attributes_groups as $group) {
            $groups[$group['name']] = (int) $group['id_attribute_group'];
        }

        $attributes = array();
        foreach (Attribute::getAttributes($default_lang_id) as $attribute) {
            $attributes[$attribute['attribute_group'] . '_' . $attribute['name']] = (int) $attribute['id_attribute'];
        }

        $csvRows = ElegantalEasyImportCsv::model()->findAll(array(
            'condition' => array(
                'id_elegantaleasyimport' => $this->model->id,
            ),
            'limit' => $limit,
        ));

        foreach ($csvRows as $csvRow) {
            $csvRowModel = new ElegantalEasyImportCsv($csvRow['id_elegantaleasyimport_csv']);
            if (!Validate::isLoadedObject($csvRowModel)) {
                continue;
            }

            $line = ElegantalEasyImportTools::unserialize($csvRowModel->csv_row);

            // We don't need this row in database anymore
            $csvRowModel->delete();

            $id_index = $map['id_reference'];

            if (isset($line[$id_index])) {
                $line[$id_index] = trim($line[$id_index]);
            }

            $products_rows = array();
            if (isset($line[$id_index]) && $line[$id_index]) {
                if ($this->model->find_products_by == 'reference') {
                    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product` p ";
                    if ($this->model->supplier_id) {
                        $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
                    }
                    $sql .= "WHERE p.`reference` = '" . pSQL($line[$id_index]) . "' ";
                    if ($this->model->supplier_id) {
                        $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id;
                    }
                    $products_rows = Db::getInstance()->executeS($sql);
                } elseif ($this->model->find_products_by == 'id') {
                    $products_rows = array(
                        array('id_product' => $line[$id_index])
                    );
                } elseif ($this->model->find_products_by == 'ean') {
                    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product` p ";
                    if ($this->model->supplier_id) {
                        $sql .= "INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) ";
                    }
                    $sql .= "WHERE p.`ean13` = '" . pSQL($line[$id_index]) . "' ";
                    if ($this->model->supplier_id) {
                        $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id;
                    }
                    $products_rows = Db::getInstance()->executeS($sql);
                } elseif ($this->model->find_products_by == 'supplier_reference') {
                    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product` p 
                        INNER JOIN `" . _DB_PREFIX_ . "product_supplier` ps ON (ps.`id_product` = p.`id_product`) 
                        WHERE ps.`product_supplier_reference` = '" . pSQL($line[$id_index]) . "' ";
                    if ($this->model->supplier_id) {
                        $sql .= "AND ps.`id_supplier` = " . (int) $this->model->supplier_id;
                    }
                    $products_rows = Db::getInstance()->executeS($sql);
                }
            }

            if (empty($products_rows) || !is_array($products_rows)) {
                continue;
            }

            foreach ($products_rows as $product_row) {
                try {
                    $product = null;
                    $id_product_attribute = 0;

                    if ($product_row && isset($product_row['id_product']) && $product_row['id_product'] > 0) {
                        $product = new Product($product_row['id_product'], false, $default_lang_id);
                    }
                    if ($product_row && isset($product_row['id_product_attribute']) && $product_row['id_product_attribute'] > 0) {
                        $id_product_attribute = (int) $product_row['id_product_attribute'];
                    }

                    if (!Validate::isLoadedObject($product)) {
                        continue;
                    }

                    $product->depends_on_stock = (int) StockAvailable::dependsOnStock($product->id);

                    $csv_attribute_groups = array();
                    $attribute_names = isset($line[$map['attribute_names']]) ? $line[$map['attribute_names']] : "";
                    if ($map_default_values['attribute_names']) {
                        $attribute_names .= $attribute_names ? $multiple_value_separator : '';
                        $attribute_names .= $map_default_values['attribute_names'];
                    }
                    foreach ($map as $attr => $index) {
                        // Skip if neither mapped nor provided default value
                        if ($index < 0 && $map_default_values[$attr] === "") {
                            continue;
                        }
                        switch ($attr) {
                            case 'attribute_1':
                            case 'attribute_2':
                            case 'attribute_3':
                            case 'attribute_4':
                            case 'attribute_5':
                            case 'attribute_6':
                                if ($index >= 0 && isset($line[$index]) && $line[$index] && isset($csv_header[$index]) && $csv_header[$index]) {
                                    $attribute_names .= $attribute_names ? $multiple_value_separator : '';
                                    $attribute_names .= $csv_header[$index] . ':select';
                                }
                                if (isset($map_default_values[$attr]) && $map_default_values[$attr]) {
                                    $attribute_name_default = explode(':', $map_default_values[$attr]);
                                    if (isset($attribute_name_default[0]) && $attribute_name_default[0] && isset($attribute_name_default[1]) && $attribute_name_default[1]) {
                                        $attribute_names .= $attribute_names ? $multiple_value_separator : '';
                                        $attribute_names .= $attribute_name_default[0] . ':select';
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    if ($attribute_names) {
                        $csv_attribute_group_arr = explode($multiple_value_separator, $attribute_names);
                        if (is_array($csv_attribute_group_arr) && count($csv_attribute_group_arr) > 0) {
                            foreach ($csv_attribute_group_arr as $key => $csv_attribute_group_line) {
                                $csv_attribute_group_parts = explode(':', $csv_attribute_group_line);
                                if (is_array($csv_attribute_group_parts) && count($csv_attribute_group_parts) == 4) {
                                    $csv_attribute_group_name = trim($csv_attribute_group_parts[0]);
                                    $csv_attribute_group_public_name = trim($csv_attribute_group_parts[1]);
                                    $csv_attribute_group_type = Tools::strtolower(trim($csv_attribute_group_parts[2]));
                                    $csv_attribute_group_position = (int) $csv_attribute_group_parts[3];
                                } else {
                                    $csv_attribute_group_name = trim($csv_attribute_group_parts[0]);
                                    $csv_attribute_group_public_name = $csv_attribute_group_name;
                                    $csv_attribute_group_type = isset($csv_attribute_group_parts[1]) ? Tools::strtolower(trim($csv_attribute_group_parts[1])) : 'select';
                                    $csv_attribute_group_position = isset($csv_attribute_group_parts[2]) ? (int) $csv_attribute_group_parts[2] : false;
                                }
                                $csv_attribute_groups[$key]['group'] = $csv_attribute_group_name;
                                if (isset($groups[$csv_attribute_group_name])) {
                                    $csv_attribute_groups[$key]['id'] = $groups[$csv_attribute_group_name];
                                    $attributeGroup = new AttributeGroup($groups[$csv_attribute_group_name], $default_lang_id);
                                    if (Validate::isLoadedObject($attributeGroup) && $attributeGroup->public_name != $csv_attribute_group_public_name) {
                                        $attributeGroup->public_name = $csv_attribute_group_public_name;
                                        $attributeGroup->update();
                                    }
                                } else {
                                    $attributeGroup = new AttributeGroup();
                                    $attributeGroup->is_color_group = ($csv_attribute_group_type == 'color') ? 1 : 0;
                                    $attributeGroup->group_type = in_array($csv_attribute_group_type, array('select', 'color', 'radio')) ? $csv_attribute_group_type : 'select';
                                    $attributeGroup->name[$default_lang_id] = $csv_attribute_group_name;
                                    $attributeGroup->public_name[$default_lang_id] = $csv_attribute_group_public_name;
                                    $attributeGroup->position = (!$csv_attribute_group_position) ? AttributeGroup::getHigherPosition() + 1 : $csv_attribute_group_position;
                                    $attributeGroup->add();
                                    if (!empty($shop_ids)) {
                                        $attributeGroup->associateTo($shop_ids);
                                    }
                                    $groups[$csv_attribute_group_name] = $attributeGroup->id;
                                    $csv_attribute_groups[$key]['id'] = $attributeGroup->id;
                                    AttributeGroup::cleanPositions();
                                }
                            }
                        }
                    }

                    $csv_attribute_values = array();
                    $attribute_values = isset($line[$map['attribute_values']]) ? $line[$map['attribute_values']] : "";
                    if ($map_default_values['attribute_values']) {
                        $attribute_values .= $attribute_values ? $multiple_value_separator : '';
                        $attribute_values .= $map_default_values['attribute_values'];
                    }
                    foreach ($map as $attr => $index) {
                        // Skip if neither mapped nor provided default value
                        if ($index < 0 && $map_default_values[$attr] === "") {
                            continue;
                        }
                        switch ($attr) {
                            case 'attribute_1':
                            case 'attribute_2':
                            case 'attribute_3':
                            case 'attribute_4':
                            case 'attribute_5':
                            case 'attribute_6':
                                if ($index >= 0 && isset($line[$index]) && $line[$index] && isset($csv_header[$index]) && $csv_header[$index]) {
                                    $attribute_values .= $attribute_values ? $multiple_value_separator : '';
                                    $attribute_values .= $line[$index];
                                }
                                if (isset($map_default_values[$attr]) && $map_default_values[$attr]) {
                                    $attribute_name_default = explode(':', $map_default_values[$attr]);
                                    if (isset($attribute_name_default[0]) && $attribute_name_default[0] && isset($attribute_name_default[1]) && $attribute_name_default[1]) {
                                        $attribute_values .= $attribute_values ? $multiple_value_separator : '';
                                        $attribute_values .= $attribute_name_default[1];
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    if ($attribute_values) {
                        $csv_attribute_value_arr = explode($multiple_value_separator, $attribute_values);
                        if (is_array($csv_attribute_value_arr) && count($csv_attribute_value_arr) > 0) {
                            foreach ($csv_attribute_value_arr as $key => $csv_attribute_value_line) {
                                if (!isset($csv_attribute_groups[$key])) {
                                    continue;
                                }
                                $attribute_group = $csv_attribute_groups[$key]['group'];
                                $csv_attribute_value_parts = explode(':', $csv_attribute_value_line);
                                $csv_attribute_value_position = isset($csv_attribute_value_parts[1]) ? (int) $csv_attribute_value_parts[1] : false;
                                $csv_attribute_value = str_replace('\n', '', str_replace('\r', '', trim($csv_attribute_value_parts[0])));
                                if (empty($csv_attribute_value)) {
                                    continue;
                                }
                                if (isset($attributes[$attribute_group . '_' . $csv_attribute_value])) {
                                    $csv_attribute_values[$key] = $attributes[$attribute_group . '_' . $csv_attribute_value];
                                } else {
                                    $attributeObj = new Attribute();
                                    $attributeObj->id_attribute_group = $csv_attribute_groups[$key]['id'];
                                    $attributeObj->name[$default_lang_id] = $csv_attribute_value;
                                    $attributeObj->position = (!$csv_attribute_value_position && isset($groups[$attribute_group])) ? Attribute::getHigherPosition($groups[$attribute_group]) + 1 : $csv_attribute_value_position;
                                    $attributeObj->add();
                                    if (!empty($shop_ids)) {
                                        $attributeObj->associateTo($shop_ids);
                                    }
                                    $attributes[$attribute_group . '_' . $csv_attribute_value] = $attributeObj->id;
                                    $csv_attribute_values[$key] = $attributeObj->id;
                                    // After insertion, we clean attribute position and group attribute position
                                    $attributeObj->cleanPositions((int) $attributeObj->id_attribute_group, false);
                                    AttributeGroup::cleanPositions();
                                }
                            }
                        }
                    }

                    $combination_data = array(
                        'combination_reference' => '',
                        'combination_id' => '',
                        'quantity' => null,
                        'minimal_quantity' => 1,
                        'wholesale_price' => 0,
                        'impact_on_price' => 0,
                        'impact_on_weight' => 0,
                        'impact_on_unit_price' => 0,
                        'images' => null,
                        'supplier_reference' => '',
                        'supplier_price' => 0,
                        'ean_no' => '',
                        'upc' => '',
                        'ecotax' => 0,
                        'default' => null,
                        'available_date' => null,
                    );

                    if (isset($line[$map['combination_reference']]) && $line[$map['combination_reference']]) {
                        $combination_data['combination_reference'] = $line[$map['combination_reference']];
                    } elseif ($map_default_values['combination_reference']) {
                        $combination_data['combination_reference'] = $map_default_values['combination_reference'];
                    }
                    if ($combination_data['combination_reference']) {
                        $id_product_attribute = (int) Combination::getIdByReference($product->id, $combination_data['combination_reference']);
                    } else {
                        if (isset($line[$map['combination_id']]) && $line[$map['combination_id']]) {
                            $combination_data['combination_id'] = (int) $line[$map['combination_id']];
                        } elseif ($map_default_values['combination_id']) {
                            $combination_data['combination_id'] = (int) $map_default_values['combination_id'];
                        }
                        if ($combination_data['combination_id']) {
                            $combination_tmp = new Combination($combination_data['combination_id']);
                            if (Validate::isLoadedObject($combination_tmp)) {
                                $id_product_attribute = (int) $combination_data['combination_id'];
                            }
                        }
                    }

                    // If combination does not exist, check if combination with the same attributes exists
                    if (!$id_product_attribute && count($csv_attribute_values) > 0) {
                        $id_product_attribute = (int) $product->productAttributeExists($csv_attribute_values, false, null, true, true);
                    }

                    if ($id_product_attribute) {
                        $existingCombination = new Combination($id_product_attribute);
                        $combination_data = array(
                            'combination_reference' => $existingCombination->reference,
                            'quantity' => null,
                            'minimal_quantity' => $existingCombination->minimal_quantity,
                            'wholesale_price' => $existingCombination->wholesale_price,
                            'impact_on_price' => $existingCombination->price,
                            'impact_on_weight' => $existingCombination->weight,
                            'impact_on_unit_price' => $existingCombination->unit_price_impact,
                            'images' => null,
                            'supplier_reference' => $existingCombination->supplier_reference,
                            'supplier_price' => $existingCombination->supplier_price,
                            'ean_no' => $existingCombination->ean13,
                            'upc' => $existingCombination->upc,
                            'ecotax' => $existingCombination->ecotax,
                            'default' => $existingCombination->default_on,
                            'available_date' => $existingCombination->available_date,
                        );
                    }

                    foreach ($map as $attr => $index) {
                        // Skip if neither mapped nor provided default value
                        if ($index < 0 && $map_default_values[$attr] === "") {
                            continue;
                        }
                        $value = isset($line[$index]) ? $line[$index] : "";
                        $value_default = isset($map_default_values[$attr]) ? $map_default_values[$attr] : "";
                        $value = ($value === "") ? trim($value_default) : trim($value);
                        switch ($attr) {
                            case 'combination_reference':
                                $combination_data['combination_reference'] = $value;
                                break;
                            case 'wholesale_price':
                                $combination_data['wholesale_price'] = (float) str_replace(',', '.', $value);
                                break;
                            case 'impact_on_price':
                                $combination_data['impact_on_price'] = (float) str_replace(',', '.', $value);
                                $combination_data['impact_on_price'] = ElegantalEasyImportTools::getModifiedPriceByFormula($combination_data['impact_on_price'], $this->model->price_modifier);
                                break;
                            case 'impact_on_weight':
                                $combination_data['impact_on_weight'] = (float) str_replace(',', '.', $value);
                                break;
                            case 'impact_on_unit_price':
                                $combination_data['impact_on_unit_price'] = (float) str_replace(',', '.', $value);
                                break;
                            case 'advanced_stock_management':
                                $product->advanced_stock_management = $this->isCsvValueTrue($value) ? 1 : 0;
                                $product->update();
                                break;
                            case 'depends_on_stock':
                                $value = $this->isCsvValueTrue($value) ? 1 : 0;
                                if (!$product->advanced_stock_management) {
                                    $value = 0;
                                }
                                $product->depends_on_stock = $value;
                                StockAvailable::setProductDependsOnStock($product->id, $product->depends_on_stock);
                                break;
                            case 'quantity':
                                $quantity_value = Tools::strtolower($value);
                                if (empty($this->quantity_dictionary)) {
                                    $quantity_dictionary = $this->getSetting('text_quantity_dictionary');
                                    if ($quantity_dictionary) {
                                        $quantity_dictionary = preg_split("/\\r\\n|\\r|\\n/", $quantity_dictionary);
                                        if ($quantity_dictionary && is_array($quantity_dictionary)) {
                                            foreach ($quantity_dictionary as $dict) {
                                                $dict = explode('=>', $dict);
                                                if (isset($dict[0]) && isset($dict[1]) && $dict[0]) {
                                                    $dict[0] = trim($dict[0]);
                                                    $this->quantity_dictionary[$dict[0]] = trim($dict[1]);
                                                }
                                            }
                                        }
                                    }
                                }
                                if (isset($this->quantity_dictionary[$quantity_value]) && array_key_exists($quantity_value, $this->quantity_dictionary)) {
                                    $value = $this->quantity_dictionary[$quantity_value];
                                }
                                $combination_data['quantity'] = (int) $value;
                                break;
                            case 'minimal_quantity':
                                $combination_data['minimal_quantity'] = ($value >= 1) ? (int) $value : 1;
                                break;
                            case 'ecotax':
                                $combination_data['ecotax'] = Configuration::get('PS_USE_ECOTAX') ? (float) str_replace(',', '.', $value) : 0;
                                break;
                            case 'images':
                                if (isset($line[$index]) && $line[$index] && $value_default) {
                                    $value .= $value ? $multiple_value_separator : '';
                                    $value .= $value_default;
                                }
                                if ($value) {
                                    $captions = "";
                                    if (isset($line[$map['image_captions']]) && $line[$map['image_captions']]) {
                                        $captions = $line[$map['image_captions']];
                                    } elseif ($map_default_values['image_captions']) {
                                        $captions = $map_default_values['image_captions'];
                                    }
                                    $combination_data['images'] = $this->createProductImages($product, $value, $multiple_value_separator, $captions);
                                }
                                break;
                            case 'ean_no':
                                if ($value && Validate::isEan13($value)) {
                                    $combination_data['ean_no'] = $value;
                                } else {
                                    $combination_data['ean_no'] = "";
                                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('EAN is not valid.') . ' ' . $value . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                                }
                                break;
                            case 'default':
                                $combination_data['default'] = $this->isCsvValueTrue($value) ? 1 : 0;
                                break;
                            case 'upc':
                                $combination_data['upc'] = ($value && Validate::isUpc($value)) ? $value : "";
                                break;
                            case 'supplier_reference':
                                $combination_data['supplier_reference'] = $value;
                                break;
                            case 'supplier_price':
                                $combination_data['supplier_price'] = (float) $value;
                                break;
                            case 'available_date':
                                if ($value && strtotime($value)) {
                                    $combination_data['available_date'] = date('Y-m-d', strtotime($value));
                                } else {
                                    $combination_data['available_date'] = null;
                                }
                                break;
                            default:
                                break;
                        }
                    }

                    if ($combination_data['default']) {
                        $product->deleteDefaultAttributes();
                    }

                    if ($id_product_attribute) {
                        $product->updateAttribute($id_product_attribute, $combination_data['wholesale_price'], $combination_data['impact_on_price'], $combination_data['impact_on_weight'], $combination_data['impact_on_unit_price'], $combination_data['ecotax'], $combination_data['images'], $combination_data['combination_reference'], $combination_data['ean_no'], $combination_data['default'], null, $combination_data['upc'], $combination_data['minimal_quantity'], $combination_data['available_date'], true, $shop_ids);
                    } elseif (count($csv_attribute_values) > 0) {
                        $id_product_attribute = $product->addCombinationEntity($combination_data['wholesale_price'], $combination_data['impact_on_price'], $combination_data['impact_on_weight'], 0, $combination_data['ecotax'], $combination_data['quantity'], $combination_data['images'], $combination_data['combination_reference'], 0, $combination_data['ean_no'], $combination_data['default'], null, $combination_data['upc'], $combination_data['minimal_quantity'], $shop_ids, $combination_data['available_date']);
                    }

                    if ($id_product_attribute && $combination_data['supplier_reference']) {
                        $supplier_price = $combination_data['supplier_price'] > 0 ? $combination_data['supplier_price'] : 0;
                        $product->addSupplierReference($product->id_supplier, $id_product_attribute, $combination_data['supplier_reference'], $supplier_price);
                    }

                    // Add attributes to the combination
                    if ($id_product_attribute && count($csv_attribute_values) > 0) {
                        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "product_attribute_combination 
                        WHERE id_product_attribute = " . (int) $id_product_attribute);
                        foreach ($csv_attribute_values as $csv_attribute_value) {
                            Db::getInstance()->execute("INSERT IGNORE INTO " . _DB_PREFIX_ . "product_attribute_combination (id_attribute, id_product_attribute) 
                            VALUES (" . (int) $csv_attribute_value . "," . (int) $id_product_attribute . ")", false);
                        }
                    }

                    // Check and make sure default combination is set
                    $product->checkDefaultAttributes();
                    if (!$product->cache_default_attribute) {
                        Product::updateDefaultAttribute($product->id);
                    }

                    $combination_warehouse = (isset($line[$map['warehouse_id']]) && $line[$map['warehouse_id']]) ? $line[$map['warehouse_id']] : $map_default_values['warehouse_id'];
                    if ($combination_warehouse && $product->advanced_stock_management && $id_product_attribute) {
                        if (Warehouse::exists($combination_warehouse)) {
                            $query = new DbQuery();
                            $query->select('id_warehouse_product_location');
                            $query->from('warehouse_product_location');
                            $query->where("id_product = " . (int) $product->id . " AND id_product_attribute = " . (int) $id_product_attribute . " AND id_warehouse = " . (int) $combination_warehouse);
                            $warehouse_product_location = (int) Db::getInstance()->getValue($query);
                            if ($warehouse_product_location) {
                                $wpl = new WarehouseProductLocation($warehouse_product_location);
                                $wpl->location = (isset($line[$map['location_in_warehouse']]) && $line[$map['location_in_warehouse']]) ? $line[$map['location_in_warehouse']] : $map_default_values['location_in_warehouse'];
                                $wpl->update();
                            } else {
                                $wpl = new WarehouseProductLocation();
                                $wpl->id_product = $product->id;
                                $wpl->id_product_attribute = $id_product_attribute;
                                $wpl->id_warehouse = (int) $combination_warehouse;
                                $wpl->location = (isset($line[$map['location_in_warehouse']]) && $line[$map['location_in_warehouse']]) ? $line[$map['location_in_warehouse']] : $map_default_values['location_in_warehouse'];
                                $wpl->add();
                            }
                            StockAvailable::synchronize($product->id);
                        } else {
                            $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Warehouse does not exist with this ID') . ' ' . $combination_warehouse . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                        }
                    }

                    if ($id_product_attribute && !is_null($combination_data['quantity'])) {
                        if ($product->advanced_stock_management && $product->depends_on_stock) {
                            if (empty($combination_warehouse)) {
                                $query = new DbQuery();
                                $query->select('id_warehouse');
                                $query->from('warehouse_product_location');
                                $query->where('id_product = ' . (int) $product->id . ' AND id_product_attribute = ' . (int) $id_product_attribute);
                                $combination_warehouse = (int) Db::getInstance()->getValue($query);
                            }
                            if ($combination_warehouse) {
                                $stock_manager = StockManagerFactory::getManager();
                                $price = str_replace(',', '.', $product->wholesale_price);
                                if ($price == 0) {
                                    $price = 0.000001;
                                }
                                $price = round((float) $price, 6);
                                $warehouse = new Warehouse($combination_warehouse);
                                if ($stock_manager->addProduct($product->id, $id_product_attribute, $warehouse, (int) $combination_data['quantity'], 1, $price, true)) {
                                    StockAvailable::synchronize($product->id);
                                }
                            } else {
                                $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Warehouse is missing') . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                            }
                        } else {
                            Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
                            if (!empty($shop_ids)) {
                                foreach ($shop_ids as $sh_id) {
                                    StockAvailable::setQuantity((int) $product->id, $id_product_attribute, (int) $combination_data['quantity'], $sh_id);
                                }
                            } else {
                                StockAvailable::setQuantity((int) $product->id, $id_product_attribute, (int) $combination_data['quantity'], $id_shop);
                            }
                            Shop::setContext($context_shop, $id_shop);
                        }
                    }

                    if ($product->advanced_stock_management == 0 && StockAvailable::dependsOnStock($product->id) == 1) {
                        StockAvailable::setProductDependsOnStock($product->id, 0);
                    }

                    // Create specific price for combination
                    $discount_amount = (isset($line[$map['discount_amount']]) && $line[$map['discount_amount']]) ? $line[$map['discount_amount']] : $map_default_values['discount_amount'];
                    $discount_percent = (isset($line[$map['discount_percent']]) && $line[$map['discount_percent']]) ? $line[$map['discount_percent']] : $map_default_values['discount_percent'];
                    $discount_base_price = (isset($line[$map['discount_base_price']]) && $line[$map['discount_base_price']]) ? $line[$map['discount_base_price']] : $map_default_values['discount_base_price'];
                    $discount_base_price = (float) $this->extractPriceInDefaultCurrency($discount_base_price);
                    $delete_existing_discount = (isset($line[$map['delete_existing_discount']]) && $line[$map['delete_existing_discount']]) ? $line[$map['delete_existing_discount']] : $map_default_values['delete_existing_discount'];
                    if ($id_product_attribute && $delete_existing_discount && $this->isCsvValueTrue($delete_existing_discount)) {
                        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "specific_price` WHERE `id_product` = " . (int) $product->id . " AND `id_product_attribute` = " . (int) $id_product_attribute);
                    }
                    if ($id_product_attribute && ($discount_percent || $discount_amount || $discount_base_price)) {
                        $discount = 0;
                        $is_percentage = false;
                        if ($discount_percent) {
                            $discount = $discount_percent;
                            $is_percentage = true;
                        } elseif ($discount_amount) {
                            $discount = $discount_amount;
                            if (strpos($discount, '%') !== false) {
                                $is_percentage = true;
                            }
                        }
                        $discount_from = '0000-00-00 00:00:00';
                        $discount_to = '0000-00-00 00:00:00';
                        $is_discount_tax_included = 1;
                        $discount_starting_unit = (isset($line[$map['discount_starting_unit']]) && $line[$map['discount_starting_unit']]) ? $line[$map['discount_starting_unit']] : $map_default_values['discount_starting_unit'];
                        $discount_customer_group = (isset($line[$map['discount_customer_group']]) && $line[$map['discount_customer_group']]) ? $line[$map['discount_customer_group']] : $map_default_values['discount_customer_group'];
                        $discount_country = (isset($line[$map['discount_country']]) && $line[$map['discount_country']]) ? $line[$map['discount_country']] : $map_default_values['discount_country'];
                        $discount_currency = (isset($line[$map['discount_currency']]) && $line[$map['discount_currency']]) ? $line[$map['discount_currency']] : $map_default_values['discount_currency'];
                        if (isset($line[$map['discount_from']]) && $line[$map['discount_from']]) {
                            $discount_from = date('Y-m-d H:i:s', strtotime($line[$map['discount_from']]));
                        } elseif ($map_default_values['discount_from']) {
                            $discount_from = date('Y-m-d H:i:s', strtotime($map_default_values['discount_from']));
                        }
                        if (isset($line[$map['discount_to']]) && $line[$map['discount_to']]) {
                            $discount_to = date('Y-m-d H:i:s', strtotime($line[$map['discount_to']]));
                        } elseif ($map_default_values['discount_to']) {
                            $discount_to = date('Y-m-d H:i:s', strtotime($map_default_values['discount_to']));
                        }
                        if (isset($line[$map['discount_tax_included']]) && $line[$map['discount_tax_included']] !== "") {
                            $is_discount_tax_included = $this->isCsvValueFalse($line[$map['discount_tax_included']]) ? 0 : 1;
                        } elseif ($map_default_values['discount_tax_included'] !== "") {
                            $is_discount_tax_included = $this->isCsvValueFalse($map_default_values['discount_tax_included']) ? 0 : 1;
                        }
                        $this->createProductSpecificPrice($product->id, $discount, $is_percentage, $is_discount_tax_included, $discount_from, $discount_to, $discount_base_price, $discount_starting_unit, $discount_customer_group, $discount_country, $discount_currency, $id_product_attribute);
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage() . '. ' . (!empty($product->id) ? 'Product ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $error;
                    if ($settings['is_debug_mode']) {
                        $this->model->last_import_date = date('Y-m-d H:i:s');
                        $this->model->update();
                        throw new Exception($error);
                    }
                }
            }
        }

        $this->model->last_import_date = date('Y-m-d H:i:s');
        $this->model->update();

        // Action to call at the end of the import process if the import was by CRON and there was rows imported above
        if ($this->model->is_cron && $csvRows) {
            $this->actionAfterImport();
        }

        return true;
    }

    protected function isCsvValueTrue($value)
    {
        $value_lower = Tools::strtolower($value);
        if ($value == 1 || $value_lower == 'yes' || $value_lower == 'true') {
            return true;
        }
        return false;
    }

    protected function isCsvValueFalse($value)
    {
        $value_lower = Tools::strtolower($value);
        if (empty($value) || $value == ' ' || $value == '-' || $value_lower == 'no' || $value_lower == 'false') {
            return true;
        }
        return false;
    }

    /**
     * Returns total number of rows in CSV file.
     * @param string $file
     * @return int
     * @throws Exception
     */
    protected function getTotalCsvRows($file)
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new Exception($this->l('Cannot read the CSV file.'));
        }

        $rows = 0;

        if (function_exists('file')) {
            $rows = count(file($file));
        } else {
            ini_set('auto_detect_line_endings', true);
            $handle = fopen($file, 'r');
            if (!$handle) {
                throw new Exception($this->l('Cannot read the CSV file.'));
            }
            while (fgetcsv($handle) !== false) {
                $rows++;
            }
            fclose($handle);
        }

        // First row is header
        $rows--;

        return $rows;
    }

    protected function getLanguagesForSelect()
    {
        $result = array();
        $languages = Language::getLanguages();
        foreach ($languages as $lang) {
            $result[] = array('key' => $lang['id_lang'], 'value' => $lang['name']);
        }
        return $result;
    }

    protected function getCurrenciesForSelect()
    {
        $result = array();

        // Add default currency first
        $defaultCurrency = Currency::getDefaultCurrency();
        $result[] = array('key' => $defaultCurrency->id, 'value' => Tools::strtoupper($defaultCurrency->iso_code));

        // Add other currencies
        $currencies = Currency::getCurrencies();
        foreach ($currencies as $currency) {
            if ($currency['id_currency'] != $defaultCurrency->id) {
                $result[] = array('key' => $currency['id_currency'], 'value' => Tools::strtoupper($currency['iso_code']));
            }
        }

        return $result;
    }

    protected function getSuppliersForSelect($is_multiple = true)
    {
        $result = array();
        if ($is_multiple) {
            $result[] = array('key' => 'all', 'value' => $this->l('ALL SUPPLIERS'));
        } else {
            $result[] = array('key' => '', 'value' => ' ');
        }
        $suppliers = Supplier::getSuppliers(false, null, false);
        foreach ($suppliers as $s) {
            $result[] = array('key' => $s['id_supplier'], 'value' => $s['name']);
        }
        return $result;
    }

    protected function getManufacturersForSelect()
    {
        $result = array(array('key' => 'all', 'value' => $this->l('ALL MANUFACTURERS')));
        if ($manufacturers = Manufacturer::getManufacturers()) {
            $ids = array();
            foreach ($manufacturers as $manufacturer) {
                if (!in_array($manufacturer['id_manufacturer'], $ids)) {
                    $ids[] = $manufacturer['id_manufacturer'];
                    $result[] = array('key' => $manufacturer['id_manufacturer'], 'value' => $manufacturer['name']);
                }
            }
        }
        return $result;
    }

    protected function getCsvHeaderForSelect($file)
    {
        $result = array(array('key' => -1, 'value' => $this->l('Ignore this column')));
        $header_row = $this->getCsvHeaderRow($file);
        if ($header_row && is_array($header_row)) {
            foreach ($header_row as $key => $value) {
                $result[] = array('key' => $key, 'value' => $value);
            }
        }
        return $result;
    }

    protected function getCsvHeaderRow($file)
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new Exception('Cannot read file: ' . $file);
        }

        ini_set('auto_detect_line_endings', true);
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new Exception($this->l('Cannot read the CSV file.'));
        }

        $header_row = array();

        $delimiter = $this->identifyCsvDelimiter($file);
        $csv_row = array();
        $row_count = 0;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row_count++;
            if ($this->model->header_row == 0 || $this->model->header_row == $row_count) {
                $csv_row = $data;
                break;
            }
        }

        fclose($handle);

        if ($csv_row && is_array($csv_row)) {
            foreach ($csv_row as $key => $value) {
                if ($this->model->is_utf8_encode) {
                    $key = utf8_encode($key);
                    $value = utf8_encode($value);
                }
                $header_row[$key] = str_replace('"', '', $value);
            }
        }

        return $header_row;
    }

    public function identifyCsvDelimiter($file)
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new Exception('Cannot read file: ' . $file);
        }

        ini_set('auto_detect_line_endings', true);
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new Exception($this->l('Cannot read the CSV file.'));
        }

        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        // First line is column titles, so we assume it would have normal values (no line breaks).
        // Even if the first line is empty, it should have delimiters
        $first_line = fgets($handle);

        fclose($handle);

        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($first_line, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

    protected function extractPriceInDefaultCurrency($price)
    {
        if ($this->model->decimal_char == ',') {
            $amount = preg_replace("/[^0-9,]/", "", $price);
            $amount = preg_replace("/,/", ".", $amount);
        } else {
            $amount = preg_replace("/[^0-9.]/", "", $price);
        }

        $currencySigns = array();
        $currencies = Currency::getCurrencies();
        foreach ($currencies as $currency) {
            $currencySigns[Tools::strtoupper($currency['name'])] = $currency['id_currency'];
            $currencySigns[Tools::strtoupper($currency['iso_code'])] = $currency['id_currency'];
            $currencySigns[Tools::strtoupper($currency['sign'])] = $currency['id_currency'];
        }

        $pattern = "/(?:";
        $count = 0;
        foreach ($currencySigns as $currencySign => $id_currency) {
            unset($id_currency); // Not used
            $pattern .= ($count == 0) ? '' : '|';
            $pattern .= (Tools::strlen($currencySign) > 1) ? $currencySign : '[' . $currencySign . ']';
            $count++;
        }
        $pattern .= ")\s*/i";

        if (preg_match($pattern, $price, $match) && isset($match[0]) && Tools::strlen(trim($match[0])) > 0) {
            $defaultCurrency = Currency::getDefaultCurrency();
            $priceCurrency = Currency::getCurrencyInstance($currencySigns[Tools::strtoupper(trim($match[0]))]);
            if (Tools::strtoupper($priceCurrency->iso_code) != Tools::strtoupper($defaultCurrency->iso_code)) {
                $amount = Tools::convertPriceFull($amount, $priceCurrency, $defaultCurrency);
            }
        }

        return round($amount, 6);
    }

    /**
     * Finds category by name, if not found creates new one and returns ID
     * @param string $name
     * @param int $id_parent_category
     * @return int
     */
    protected function getCategoryIdByName($name, $id_parent_category = null)
    {
        $id_shop = $this->context->shop->id;
        $id_category = null;
        $name = Tools::substr(preg_replace('/[<>;=#{}]*/', '', $name), 0, 128);
        $name = trim($name);
        if (empty($name)) {
            return null;
        }

        if (empty($this->categories_dictionary)) {
            $categories_dictionary = $this->getSetting('text_categories_dictionary');
            if ($categories_dictionary) {
                $categories_dictionary = preg_split("/\\r\\n|\\r|\\n/", $categories_dictionary);
                if ($categories_dictionary && is_array($categories_dictionary)) {
                    foreach ($categories_dictionary as $dict) {
                        $dict = explode('=>', $dict);
                        if (isset($dict[0]) && isset($dict[1]) && $dict[0]) {
                            $dict[0] = trim($dict[0]);
                            $this->categories_dictionary[$dict[0]] = trim($dict[1]);
                        }
                    }
                }
            }
        }
        if (isset($this->categories_dictionary[$name]) && array_key_exists($name, $this->categories_dictionary)) {
            if (empty($this->categories_dictionary[$name])) {
                throw new Exception("Products of category " . $name . " are skipped");
            } else {
                $name = $this->categories_dictionary[$name];
            }
        }

        $rootCategory = Category::getRootCategory();

        if ($name == $rootCategory->name && (!$id_parent_category || $id_parent_category == $rootCategory->id)) {
            return $rootCategory->id;
        }

        if (empty($this->categories)) {
            $sql = "SELECT c.`id_category`, cl.`name`, c.`id_parent` 
                FROM `" . _DB_PREFIX_ . "category` c 
                INNER JOIN `" . _DB_PREFIX_ . "category_shop` csh ON (csh.`id_category` = c.`id_category` AND csh.`id_shop` = " . (int) $id_shop . ") 
                LEFT JOIN `" . _DB_PREFIX_ . "category_lang` cl ON c.`id_category` = cl.`id_category` AND cl.id_shop = " . (int) $id_shop . " 
                GROUP BY c.`id_category` 
                ORDER BY c.`level_depth` ASC, csh.`position` ASC";
            $this->categories = Db::getInstance()->executeS($sql);
        }

        // If ID is given instead of name
        if (Validate::isInt($name)) {
            foreach ($this->categories as $category) {
                if ($category['id_category'] == $name) {
                    $id_category = $category['id_category'];
                    break;
                }
            }
            return $id_category;
        }

        if (!is_null($id_parent_category) && $id_parent_category >= 0) {
            foreach ($this->categories as $category) {
                if (Tools::strtolower($category['name']) == Tools::strtolower($name) && $category['id_parent'] == $id_parent_category) {
                    $id_category = $category['id_category'];
                    break;
                }
            }
        } else {
            foreach ($this->categories as $category) {
                if (Tools::strtolower($category['name']) == Tools::strtolower($name)) {
                    $id_category = $category['id_category'];
                    break;
                }
            }
        }

        if (!$id_category) {
            if (!$id_parent_category) {
                $id_parent_category = $rootCategory->id;
            }

            $model = new Category();
            $model->id_parent = $id_parent_category;
            $model->name = array();
            $model->link_rewrite = array();

            $languages = Language::getLanguages(false);
            $link = Tools::link_rewrite(Tools::substr($name, 0, 128));

            foreach ($languages as $lang) {
                $model->name[$lang['id_lang']] = $name;
                $model->link_rewrite[$lang['id_lang']] = $link;
            }

            if ($model->add()) {
                $id_category = $model->id;
                $this->categories[] = array('id_category' => $id_category, 'name' => $name, 'id_parent' => $model->id_parent);
            }
        }

        return $id_category;
    }

    /**
     * Finds supplier by name, if not found creates new one and returns ID
     * @param string $name
     * @return int
     */
    protected function getSupplierIdByName($name)
    {
        $id_supplier = null;

        if (empty($name)) {
            return null;
        }

        if (empty($this->suppliers)) {
            $this->suppliers = Supplier::getSuppliers(false, $this->context->language->id, false, false, false, true);
        }

        foreach ($this->suppliers as $s) {
            if ((Validate::isInt($name) && $s['id_supplier'] == $name) || Tools::strtolower($s['name']) == Tools::strtolower($name)) {
                $id_supplier = $s['id_supplier'];
                break;
            }
        }

        if (!$id_supplier) {
            $model = new Supplier();
            $model->name = $name;
            $model->active = 1;
            if ($model->add()) {
                $id_supplier = $model->id;
                $this->suppliers[] = array('id_supplier' => $id_supplier, 'name' => $name);
            }
        }

        return $id_supplier;
    }

    /**
     * Finds manufacturer by name, if not found creates new one and returns ID
     * @param string $name
     * @return int
     */
    protected function getManufacturerIdByName($name)
    {
        $id_manufacturer = null;

        if (empty($name)) {
            return $id_manufacturer;
        }

        if (empty($this->manufacturers)) {
            $this->manufacturers = Manufacturer::getManufacturers();
        }

        foreach ($this->manufacturers as $manufacturer) {
            if (Tools::strtolower($manufacturer['name']) == Tools::strtolower($name)) {
                $id_manufacturer = $manufacturer['id_manufacturer'];
                break;
            }
        }

        if (!$id_manufacturer) {
            $model = new Manufacturer();
            $model->name = $name;
            $model->active = 1;
            if ($model->add()) {
                $id_manufacturer = $model->id;
                $this->manufacturers[] = array('id_manufacturer' => $id_manufacturer, 'name' => $name);
            }
        }

        return $id_manufacturer;
    }

    protected function createProductSpecificPrice($id_product, $discount_amount, $is_percentage, $discount_tax_included = 1, $discount_from = '0000-00-00 00:00:00', $discount_to = '0000-00-00 00:00:00', $discount_base_price = "", $discount_starting_unit = 1, $discount_customer_group = "", $discount_country = "", $discount_currency = "", $id_product_attribute = 0)
    {
        if (preg_match('/([0-9]+\.{0,1}[0-9]*)/', $discount_amount, $match)) {
            $discount_amount = $match[0];
        }
        $discount_amount = (float) $discount_amount;
        if ($is_percentage && $discount_amount > 0 && $discount_amount < 1) {
            $discount_amount = $discount_amount * 100;
        }
        if (!$discount_base_price && (!$discount_amount || empty($discount_amount) || $discount_amount === 0 || $discount_amount === 0.00)) {
            return;
        }

        $discount_starting_unit = (int) $discount_starting_unit;
        if ($discount_starting_unit < 1) {
            $discount_starting_unit = 1;
        }

        if ($discount_customer_group && !Validate::isInt($discount_customer_group)) {
            $group = Group::searchByName($discount_customer_group);
            if ($group && $group['id_group']) {
                $discount_customer_group = $group['id_group'];
            }
        }
        if ($discount_country && !Validate::isInt($discount_country)) {
            $country_id = Country::getIdByName(null, $discount_country);
            if ($country_id) {
                $discount_country = $country_id;
            } else {
                $discount_country = Country::getByIso($discount_country);
            }
        }
        if ($discount_currency && !Validate::isInt($discount_currency)) {
            $discount_currency = Currency::getIdByIsoCode($discount_currency);
        }

        $specificPrice = new SpecificPrice();
        $specificPrice->id_product = (int) $id_product;
        $specificPrice->id_product_attribute = (int) $id_product_attribute;
        $specificPrice->id_shop = 0;
        $specificPrice->id_currency = (int) $discount_currency;
        $specificPrice->id_country = (int) $discount_country;
        $specificPrice->id_group = (int) $discount_customer_group;
        $specificPrice->id_customer = 0;
        $specificPrice->from_quantity = $discount_starting_unit;
        $specificPrice->price = ((float) $discount_base_price) ? (float) $discount_base_price : '-1';
        $specificPrice->from = $discount_from;
        $specificPrice->to = $discount_to;
        $specificPrice->reduction = (float) ($is_percentage ? round((($discount_amount) / 100), 8) : round($discount_amount, 8));
        $specificPrice->reduction_tax = $discount_tax_included;
        $specificPrice->reduction_type = $is_percentage ? 'percentage' : 'amount';
        $specificPrice->add();
    }

    protected function createProductFeatures($product, $value, $multiple_value_separator, $id_lang)
    {
        if (!$product->id || !$value) {
            return;
        }
        $features = explode($multiple_value_separator, $value);
        foreach ($features as $feature) {
            if (empty($feature)) {
                continue;
            }
            $feature_parts = explode(':', $feature);
            $feature_name = isset($feature_parts[0]) ? trim($feature_parts[0]) : '';
            $feature_value = isset($feature_parts[1]) ? trim($feature_parts[1]) : '';
            $position = isset($feature_parts[2]) ? (int) $feature_parts[2] - 1 : false;
            $is_custom = isset($feature_parts[3]) ? (bool) $feature_parts[3] : false;

            if (empty($this->features_dictionary)) {
                $features_dictionary = $this->getSetting('text_features_dictionary');
                if ($features_dictionary) {
                    $features_dictionary = preg_split("/\\r\\n|\\r|\\n/", $features_dictionary);
                    if ($features_dictionary && is_array($features_dictionary)) {
                        foreach ($features_dictionary as $dict) {
                            $dict = explode('=>', $dict);
                            if (isset($dict[0]) && isset($dict[1]) && $dict[0] && $dict[1]) {
                                $dict[0] = trim($dict[0]);
                                $this->features_dictionary[$dict[0]] = trim($dict[1]);
                            }
                        }
                    }
                }
            }
            if (isset($this->features_dictionary[$feature_name])) {
                $feature_name = $this->features_dictionary[$feature_name];
            }

            if (empty($this->feature_values_dictionary)) {
                $feature_values_dictionary = $this->getSetting('text_feature_values_dictionary');
                if ($feature_values_dictionary) {
                    $feature_values_dictionary = preg_split("/\\r\\n|\\r|\\n/", $feature_values_dictionary);
                    if ($feature_values_dictionary && is_array($feature_values_dictionary)) {
                        foreach ($feature_values_dictionary as $dict) {
                            $dict = explode('=>', $dict);
                            if (isset($dict[0]) && isset($dict[1]) && $dict[0] && $dict[1]) {
                                $dict[0] = trim($dict[0]);
                                $this->feature_values_dictionary[$dict[0]] = trim($dict[1]);
                            }
                        }
                    }
                }
            }
            if (isset($this->feature_values_dictionary[$feature_value])) {
                $feature_value = $this->feature_values_dictionary[$feature_value];
            }

            if (!empty($feature_name) && !empty($feature_value)) {
                $feature_name = htmlspecialchars($feature_name);
                $feature_value = htmlspecialchars($feature_value);
                $id_feature = (int) Feature::addFeatureImport($feature_name, $position);
                $id_feature_value = (int) FeatureValue::addFeatureValueImport($id_feature, $feature_value, $product->id, $id_lang, $is_custom);
                Product::addFeatureProductImport($product->id, $id_feature, $id_feature_value);
            }
        }
        Feature::cleanPositions();
    }

    protected function createProductAccessories($product, $value, $multiple_value_separator)
    {
        if (!$product->id || !$value) {
            return;
        }

        // Delete old accessories
        $product->deleteAccessories();

        $accessory_product_ids = array();

        // Create new accessories
        $accessories = explode($multiple_value_separator, $value);
        $accessories = array_map('trim', $accessories);
        $accessories = array_unique($accessories);
        foreach ($accessories as $id_accessory_product) {
            if (empty($id_accessory_product)) {
                continue;
            }
            if ($this->model->find_products_by == 'reference' || !Validate::isInt($id_accessory_product)) {
                // Find product id by reference
                $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product` WHERE `reference` = '" . pSQL($id_accessory_product) . "'";
                $row = Db::getInstance()->getRow($sql);
                if ($row && isset($row['id_product']) && $row['id_product']) {
                    $id_accessory_product = $row['id_product'];
                }
            }
            $accessory_product_ids[] = $id_accessory_product;
        }
        $product->changeAccessories($accessory_product_ids);
    }

    protected function createProductTags($id_product, $value, $multiple_value_separator, $id_lang, $debug_mode = 0)
    {
        if ($id_product) {
            // Validate tag list
            if (Validate::isTagsList($value)) {
                // Delete old tags. Similar function to Tag::deleteTagsForProduct but need to add id_lang
                $tagsRemoved = Db::getInstance()->executeS("SELECT id_tag FROM " . _DB_PREFIX_ . "product_tag WHERE id_product = " . (int) $id_product . " AND id_lang = " . (int) $id_lang);
                Db::getInstance()->delete("product_tag", "id_product = " . (int) $id_product . " AND id_lang = " . (int) $id_lang);
                Db::getInstance()->delete("tag", "NOT EXISTS (SELECT 1 FROM " . _DB_PREFIX_ . "product_tag WHERE " . _DB_PREFIX_ . "product_tag.id_tag = " . _DB_PREFIX_ . "tag.id_tag)");
                $tagList = array();
                foreach ($tagsRemoved as $tagRemoved) {
                    $tagList[] = $tagRemoved['id_tag'];
                }
                if ($tagList != array()) {
                    Tag::updateTagCount($tagList);
                }

                // Add tags to the product
                Tag::addTags($id_lang, (int) $id_product, $value, $multiple_value_separator);
            } elseif ($debug_mode) {
                throw new Exception(sprintf($this->l('The tags list (%s) is invalid.'), $value));
            }
        }
    }

    protected function createProductAttachments($product, $attachments_str, $multiple_value_separator)
    {
        $attachments = array_map('trim', explode($multiple_value_separator, $attachments_str));
        if (!$attachments || !is_array($attachments) || count($attachments) < 1) {
            return;
        }

        foreach ($attachments as $attachment_file) {
            $tmp_file = null;
            $filename = basename($attachment_file);
            if (ElegantalEasyImportTools::isValidUrl($attachment_file)) {
                $tmp_file = ElegantalEasyImportTools::getTempDir() . DIRECTORY_SEPARATOR . rand(1000, 1000000) . '-' . $filename;
                if (ElegantalEasyImportTools::downloadFileFromUrl($attachment_file, $tmp_file)) {
                    $attachment_file = $tmp_file;
                } else {
                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Unable to download attachment') . ' ' . $attachment_file . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                    @unlink($tmp_file);
                    continue;
                }
            } elseif (Tools::substr($attachment_file, 0, 1) != '/') {
                $attachment_file = _PS_ROOT_DIR_ . '/' . $attachment_file;
            }
            if (is_file($attachment_file) && ($filesize = filesize($attachment_file))) {
                if ($filesize > (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024)) {
                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Attachment file size is too large') . ' (' . ElegantalEasyImportTools::displaySize($filesize) . '). ' . $this->l('Max allowed size is') . ' ' . ElegantalEasyImportTools::displaySize(Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024) . '. ' . $filename . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                    @unlink($tmp_file);
                    continue;
                }

                $uniqid = null;
                do {
                    $uniqid = sha1(microtime());
                } while (file_exists(_PS_DOWNLOAD_DIR_ . $uniqid));
                if (!copy($attachment_file, _PS_DOWNLOAD_DIR_ . $uniqid)) {
                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Unable to copy attachment') . ' ' . $attachment_file . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                    @unlink($tmp_file);
                    continue;
                }

                $attachment = new Attachment();
                $languages = Language::getLanguages();
                foreach ($languages as $language) {
                    $attachment->name[$language['id_lang']] = Tools::substr($filename, 0, 32);
                }
                $attachment->file = $uniqid;
                $attachment->mime = ElegantalEasyImportTools::getMimeType($attachment_file);
                $attachment->file_name = $filename;
                $attachment->add();
                $attachment->attachProduct($product->id);
                $product->cache_has_attachments = 1;
                @unlink($tmp_file);
            } else {
                $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $this->l('Attachment file is missing.') . ' ' . $attachment_file . ' ' . $this->l('for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                @unlink($tmp_file);
                continue;
            }
        }

        // Update model to save error log
        $this->model->update();
    }

    protected function createProductImages($product, $csv_images, $multiple_value_separator, $captions)
    {
        $id_shop = $this->context->shop->id;
        $id_lang = $this->context->language->id;
        $context_shop = Shop::getContext();
        $base_url_images = $this->model->base_url_images;

        if ($multiple_value_separator == '/') {
            $multiple_value_separator = ',';
        }

        // Get images from csv column into array
        $csv_images = array_map('trim', explode($multiple_value_separator, $csv_images));
        $captions = $captions ? array_map('trim', explode($multiple_value_separator, $captions)) : null;

        $image_ids = array();

        if ($csv_images && is_array($csv_images) && count($csv_images) > 0) {
            // Manage images globally
            if (Shop::getContext() != Shop::CONTEXT_ALL) {
                Shop::setContext(Shop::CONTEXT_ALL);
            }

            $images_hashes = array();
            $hasCover = false;

            // Prepare image hash array
            $product_images = Image::getImages($id_lang, $product->id);
            foreach ($product_images as $product_image) {
                $imageObj = new Image($product_image['id_image']);
                if (Validate::isLoadedObject($imageObj)) {
                    $hash = md5_file(_PS_PROD_IMG_DIR_ . $imageObj->getExistingImgPath() . '.' . $imageObj->image_format);
                    $images_hashes[$hash] = $imageObj->id;
                }
            }
            $hasCover = $this->getCoverImage($product->id);

            foreach ($csv_images as $key => $file) {
                $url = $file;

                if ($base_url_images) {
                    if (strpos($base_url_images, '%s') !== false) {
                        $url = str_replace('%s', $file, $base_url_images);
                    } elseif (!ElegantalEasyImportTools::isValidUrl($url)) {
                        $file = trim($file, '/');
                        if (Tools::substr($base_url_images, -1) != '/') {
                            $base_url_images .= '/';
                        }
                        $url = $base_url_images . $file;
                    }
                }

                $image = new Image();
                $image->id_product = $product->id;
                $image->position = Image::getHighestPosition($product->id) + 1;
                if (empty($hasCover)) {
                    $image->cover = true;
                }

                if ($captions && isset($captions[$key]) && $captions[$key]) {
                    $image->legend = $captions[$key];
                }

                $image_add = $image->add();
                if (!$image_add) {
                    Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "image_shop WHERE id_image NOT IN (SELECT id_image FROM " . _DB_PREFIX_ . "image)");
                    $image_add = $image->add();
                }
                if ($image_add) {
                    if ($this->copyImg($product->id, $image, $url)) {
                        // Delete image if it is duplicate
                        $hash = md5_file(_PS_PROD_IMG_DIR_ . $image->getExistingImgPath() . '.' . $image->image_format);
                        if (isset($images_hashes[$hash])) {
                            // Get id of existing image
                            $image_ids[] = $images_hashes[$hash];
                            // Delete new image because it is duplicate
                            if ($image->delete() && $image->cover) {
                                $hasCover = false;
                            }
                        } else {
                            $images_hashes[$hash] = $image->id;
                            if ($image->cover) {
                                $hasCover = true;
                            }
                            $image_ids[] = $image->id;
                        }
                    } else {
                        if ($image->delete() && $image->cover) {
                            $hasCover = false;
                        }
                        $error = $url . ' ' . $this->l('image is missing for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                        $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $error;
                    }
                } else {
                    $error = $this->l('Failed to create image object for the product') . ' ' . (!empty($product->id) ? 'ID: ' . $product->id : '') . (!empty($product->reference) ? '  Reference: ' . $product->reference : '');
                    $this->model->error_log .= (empty($this->model->error_log) ? '' : PHP_EOL) . date('d-m-Y H:i:s') . ' ' . $error;
                }
            }

            // Set context shop back to its original value
            Shop::setContext($context_shop, $id_shop);

            // Update model to save error log
            $this->model->update();
        }

        return $image_ids;
    }

    /**
     * Copies image file for product image
     * @param int $id_product
     * @param int $image Image object
     * @param string $url
     * @return boolean
     */
    protected function copyImg($id_product, $image, $url)
    {
        // $url = urldecode(trim($url));
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $tmp_file = ElegantalEasyImportTools::getTempDir() . DIRECTORY_SEPARATOR . rand(1000, 1000000) . '.' . $extension;
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));
        $path = $image->getPathForCreation();

        try {
            if (ElegantalEasyImportTools::downloadFileFromUrl($url, $tmp_file)) {
                // Check if real image
                $mime_type = ElegantalEasyImportTools::getMimeType($tmp_file);
                if (!$mime_type || strpos($mime_type, 'image') === false) {
                    return false;
                }

                // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
                if (!ImageManager::checkImageMemoryLimit($tmp_file)) {
                    @unlink($tmp_file);
                    return false;
                }

                ImageManager::resize($tmp_file, $path . '.' . $image->image_format, null, null, $image->image_format);

                // Regenerate. It is enabled because one customer reported that images are not generated when this is disabled.
                $regenerate = true;
                if ($regenerate) {
                    $images_types = ImageType::getImagesTypes('products', true);
                    foreach ($images_types as $image_type) {
                        ImageManager::resize($tmp_file, $path . '-' . Tools::stripslashes($image_type['name']) . '.' . $image->image_format, $image_type['width'], $image_type['height'], $image->image_format);
                        if (in_array($image_type['id_image_type'], $watermark_types)) {
                            Hook::exec('actionWatermark', array('id_image' => $image->id, 'id_product' => $id_product));
                        }
                    }
                }
            } else {
                @unlink($tmp_file);
                return false;
            }
        } catch (Exception $e) {
            @unlink($tmp_file);
            return false;
        }

        @unlink($tmp_file);

        if (is_file(_PS_TMP_IMG_DIR_ . 'product_' . (int) $id_product . '.jpg')) {
            @unlink(_PS_TMP_IMG_DIR_ . 'product_' . (int) $id_product . '.jpg');
        }
        if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '.jpg')) {
            @unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '.jpg');
        }
        if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '_' . (int) $this->context->shop->id . '.jpg')) {
            @unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_product . '_' . (int) $this->context->shop->id . '.jpg');
        }

        return true;
    }

    protected function getCoverImage($id_product)
    {
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "image_shop` 
			WHERE `id_product` = " . (int) $id_product . " AND id_shop = " . (int) $this->context->shop->id . " AND `cover`= 1";
        return Db::getInstance()->getRow($sql);
    }

    protected function createProductSuppliers($product, $value, $supplier_references, $supplier_prices, $multiple_value_separator)
    {
        $id_default_supplier = false;
        $suppliers = explode($multiple_value_separator, $value);
        $suppliers = array_unique($suppliers);
        $suppliers = array_map('trim', $suppliers);
        if ($supplier_references) {
            $supplier_references = array_unique(explode($multiple_value_separator, $supplier_references));
            $supplier_references = array_map('trim', $supplier_references);
        }
        if ($supplier_prices) {
            $supplier_prices = array_unique(explode($multiple_value_separator, $supplier_prices));
            $supplier_prices = array_map('trim', $supplier_prices);
        }

        $product_suppliers = ProductSupplier::getSupplierCollection($product->id);

        foreach ($suppliers as $key => $supplier_name) {
            $id_supplier = $this->getSupplierIdByName($supplier_name);
            if (!$id_supplier) {
                continue;
            }

            // Get first supplier as default supplier. Will be used if product has no default supplier.
            if (!$id_default_supplier) {
                $id_default_supplier = $id_supplier;
            }

            // Check if supplier is already associated
            $already_accociated = false;
            foreach ($product_suppliers as $product_supplier) {
                if ($product_supplier->id_supplier == $id_supplier) {
                    $already_accociated = true;
                    if (isset($supplier_references[$key]) && $supplier_references[$key]) {
                        $product_supplier->product_supplier_reference = pSQL($supplier_references[$key]);
                    }
                    if (isset($supplier_prices[$key]) && $supplier_prices[$key]) {
                        $product_supplier->product_supplier_price_te = (float) $supplier_prices[$key];
                    }
                    $product_supplier->update();
                    break;
                }
            }
            if (!$already_accociated) {
                $productSupplier = new ProductSupplier();
                $productSupplier->id_product = $product->id;
                $productSupplier->id_product_attribute = 0;
                $productSupplier->id_supplier = $id_supplier;
                if (isset($supplier_references[$key]) && $supplier_references[$key]) {
                    $productSupplier->product_supplier_reference = pSQL($supplier_references[$key]);
                }
                if (isset($supplier_prices[$key]) && $supplier_prices[$key]) {
                    $productSupplier->product_supplier_price_te = (float) $supplier_prices[$key];
                }
                if ($this->context->currency->id) {
                    $productSupplier->id_currency = (int) $this->context->currency->id;
                } else {
                    $productSupplier->id_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                }
                $productSupplier->save();

                $attributes = $product->getAttributesResume($this->context->language->id);
                if ($attributes && is_array($attributes)) {
                    foreach ($attributes as $attribute) {
                        if ((int) $attribute['id_product_attribute'] > 0) {
                            $productSupplier = new ProductSupplier();
                            $productSupplier->id_product = $product->id;
                            $productSupplier->id_product_attribute = (int) $attribute['id_product_attribute'];
                            $productSupplier->id_supplier = $id_supplier;
                            $productSupplier->save();
                        }
                    }
                }
            }
        }
        if (!$product->id_supplier && $id_default_supplier) {
            $product->id_supplier = $id_default_supplier;
            $product->update();
        }
    }

    protected function createProductCarriers($product, $value, $multiple_value_separator)
    {
        $carriers = explode($multiple_value_separator, $value);
        $carriers = array_unique($carriers);
        $carriers = array_map('trim', $carriers);
        if (!$carriers || !is_array($carriers)) {
            return;
        }
        $carriers_ids = array();
        foreach ($carriers as $carrier) {
            if (empty($carrier)) {
                continue;
            }
            if (empty($this->carriers)) {
                $this->carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
            }
            foreach ($this->carriers as $c) {
                if ((Validate::isInt($carrier) && $carrier == $c['id_reference']) || Tools::strtolower($c['name']) == Tools::strtolower($carrier)) {
                    $carriers_ids[] = $c['id_reference'];
                    break;
                }
            }
        }
        $product->setCarriers($carriers_ids);
    }

    protected function createProductCustomizableFields($product, $uploadable_files, $uploadable_files_labels, $text_fields, $text_fields_labels, $multiple_value_separator)
    {
        $current_customization = $product->getCustomizationFieldIds();
        $files_count = 0;
        $text_count = 0;
        if (is_array($current_customization)) {
            foreach ($current_customization as $field) {
                if ($field['type'] == 1) {
                    $text_count++;
                } else {
                    $files_count++;
                }
            }
        }
        // Create only new fields
        $files_count = (int) $product->uploadable_files - $files_count;
        $text_count = (int) $product->text_fields - $text_count;
        if ($files_count > 0 || $text_count > 0) {
            $languages = Language::getLanguages();
            $shop_ids = Shop::getContextListShopID();
            if ($files_count > 0) {
                $uploadable_files_labels = explode($multiple_value_separator, $uploadable_files_labels);
                $uploadable_files_labels = array_map('trim', $uploadable_files_labels);
                for ($i = 0; $i < $files_count; $i++) {
                    $sql = "INSERT INTO `" . _DB_PREFIX_ . "customization_field` (`id_product`, `type`, `required`)
                        VALUES (" . (int) $product->id . ", " . (int) Product::CUSTOMIZE_FILE . ", 0)";
                    if (Db::getInstance()->execute($sql) && ($id_customization_field = (int) Db::getInstance()->Insert_ID())) {
                        $sql = "INSERT INTO `" . _DB_PREFIX_ . "customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`) 
                                VALUES ";
                        $values = "";
                        $label = isset($uploadable_files_labels[$i]) ? $uploadable_files_labels[$i] : "";
                        foreach ($languages as $language) {
                            foreach ($shop_ids as $id_shop) {
                                $values .= $values ? ", " : "";
                                $values .= "(" . (int) $id_customization_field . ", " . (int) $language['id_lang'] . ", " . (int) $id_shop . ", '" . pSQL($label) . "')";
                            }
                        }
                        $sql .= $values;
                        Db::getInstance()->execute($sql);
                    }
                }
            }
            if ($text_count > 0) {
                $text_fields_labels = explode($multiple_value_separator, $text_fields_labels);
                $text_fields_labels = array_map('trim', $text_fields_labels);
                for ($i = 0; $i < $text_count; $i++) {
                    $sql = "INSERT INTO `" . _DB_PREFIX_ . "customization_field` (`id_product`, `type`, `required`)
                        VALUES (" . (int) $product->id . ", " . (int) Product::CUSTOMIZE_TEXTFIELD . ", 0)";
                    if (Db::getInstance()->execute($sql) && ($id_customization_field = (int) Db::getInstance()->Insert_ID())) {
                        $sql = "INSERT INTO `" . _DB_PREFIX_ . "customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`) 
                                VALUES ";
                        $values = "";
                        $label = isset($text_fields_labels[$i]) ? $text_fields_labels[$i] : "";
                        foreach ($languages as $language) {
                            foreach ($shop_ids as $id_shop) {
                                $values .= $values ? ", " : "";
                                $values .= "(" . (int) $id_customization_field . ", " . (int) $language['id_lang'] . ", " . (int) $id_shop . ", '" . pSQL($label) . "')";
                            }
                        }
                        $sql .= $values;
                        Db::getInstance()->execute($sql);
                    }
                }
            }
            Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', '1');
        }
    }

    /**
     * Create records for fsproductvideo module
     * @param Product $product
     * @param string $urls
     */
    protected function createFsProductVideoUrls($product, $urls, $multiple_value_separator)
    {
        if (!Module::isInstalled('fsproductvideo')) {
            return;
        }

        $urls = explode($multiple_value_separator, $urls);
        $languages = Language::getLanguages(false);
        if (Shop::getContext() == Shop::CONTEXT_ALL) {
            $shops = array(array('id_shop' => $this->context->shop->id));
        } else {
            $shops = Shop::getShops();
        }

        // Delete old lang records
        $sql = "DELETE l FROM `" . _DB_PREFIX_ . "fsproductvideo_lang` l 
            INNER JOIN `" . _DB_PREFIX_ . "fsproductvideo` f ON f.`id_fsproductvideo` = l.`id_fsproductvideo` 
            WHERE f.`id_product` = " . (int) $product->id;
        Db::getInstance()->execute($sql);

        // Delete old records
        $sql = "DELETE FROM `" . _DB_PREFIX_ . "fsproductvideo` WHERE `id_product` = " . (int) $product->id;
        Db::getInstance()->execute($sql);

        // Create new record
        $ids = array();
        foreach ($urls as $pos => $url) {
            $url = (Tools::substr($url, 0, 7) != "http://" && Tools::substr($url, 0, 8) != "https://") ? "http://" . $url : $url;
            if (ElegantalEasyImportTools::isValidUrl($url)) {
                // Get thumbnail image as fsproductvideo module's controller
                $thumbnail = $this->fsSaveThumbnailImage($url, $product->id);
                foreach ($shops as $shop) {
                    $sql = "INSERT INTO `" . _DB_PREFIX_ . "fsproductvideo` (`id_shop`, `id_product`, `active`, `position`, `thumbnail`, `date_add`, `date_upd`) 
                        VALUES (" . (int) $shop['id_shop'] . ", " . (int) $product->id . ", 1, " . (int) ($pos + 1) . ", '" . pSQL($thumbnail) . "', '" . pSQL(date('Y-m-d H:i:s')) . "', '" . pSQL(date('Y-m-d H:i:s')) . "')";
                    if (Db::getInstance()->execute($sql)) {
                        $ids[DB::getInstance()->Insert_ID()] = $url;
                    }
                }
            }
        }
        if (!empty($ids) && is_array($ids)) {
            foreach ($ids as $id => $url) {
                foreach ($languages as $lang) {
                    $sql = "INSERT INTO `" . _DB_PREFIX_ . "fsproductvideo_lang` (`id_fsproductvideo`, `id_lang`, `url`, `title`) 
                        VALUES(" . (int) $id . ", " . (int) $lang['id_lang'] . ", '" . pSQL($url) . "', '" . pSQL($product->name) . "')";
                    Db::getInstance()->execute($sql);
                }
            }
        }
    }

    /**
     * Saves video thumbnail in fsproductvideo module directory
     * @param string $fspv_video_url
     * @param int $fspv_id_product
     * @return string
     */
    protected function fsSaveThumbnailImage($fspv_video_url, $fspv_id_product)
    {
        $fsproductvideo = Module::getInstanceByName('fsproductvideo');

        $imageName = '';
        if ($fsproductvideo && $fspv_video_url && $fspv_id_product) {
            $image_url = '';
            $youtube_matches = array();
            $exp = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|';
            $exp .= '(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
            preg_match($exp, trim($fspv_video_url), $youtube_matches);
            if (isset($youtube_matches[1]) && $youtube_matches[1]) {
                $videoType = 'youtube';
                $videoId = $youtube_matches[1];
                $image_url = 'http://img.youtube.com/vi/' . $videoId . '/sddefault.jpg';
                $image_curl_handle = curl_init($image_url);
                curl_setopt($image_curl_handle, CURLOPT_RETURNTRANSFER, true);
                curl_exec($image_curl_handle);
                $image_curl_response_http_code = curl_getinfo($image_curl_handle, CURLINFO_HTTP_CODE);
                if ($image_curl_response_http_code == 404) {
                    $image_url = 'http://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg';
                }
            }
            $vimeo_matches = array();
            $exp = '/https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|';
            $exp .= 'groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/';
            preg_match($exp, trim($fspv_video_url), $vimeo_matches);
            if (isset($vimeo_matches[3]) && $vimeo_matches[3]) {
                $videoType = 'vimeo';
                $videoId = $vimeo_matches[3];
                $vimeo = Tools::jsonDecode(Tools::file_get_contents('http://vimeo.com/api/v2/video/' . $videoId . '.json'), true);
                $image_url = $vimeo[0]['thumbnail_large'];
            }
            if ($videoType) {
                $file_attachment = array();
                $file_attachment['content'] = Tools::file_get_contents($image_url);
                $ext = Tools::strtolower(strrchr(basename($image_url), '.'));
                $file_attachment['name'] = $fspv_id_product . '_' . $videoId . $ext;
                $same_file_counter = 1;
                while (file_exists(dirname($fsproductvideo->getModuleFile()) . '/thumbnail/' . $file_attachment['name'])) {
                    $file_attachment['name'] = $fspv_id_product . '_' . $videoId . '-';
                    $file_attachment['name'] .= $same_file_counter . $ext;
                    $same_file_counter++;
                }
                $class = 'FsProductVideoImage';
                $image = new $class($file_attachment);
                $image->setResizeOptions(640, 480, 'crop');
                $image->saveImage(dirname($fsproductvideo->getModuleFile()) . '/thumbnail/' . $file_attachment['name']);
                $imageName = $file_attachment['name'];
            }
        }
        return $imageName;
    }

    /**
     * Saves related product ids for the module additionalproductsorder
     * @param int $product
     * @param string $value
     * @param string $multiple_value_separator
     */
    protected function createAdditionalproductsorderRelation($product, $value, $multiple_value_separator)
    {
        if (!$product->id || !$value) {
            return;
        }
        if (!Module::isInstalled('additionalproductsorder')) {
            return;
        }
        // Delete old records
        $sql = "DELETE FROM `" . _DB_PREFIX_ . "lineven_apo` WHERE `product_cart_id` = " . (int) $product->id;
        Db::getInstance()->execute($sql);

        $id_shop = $this->context->shop->id;
        $id_shop_group = $this->context->shop->id_shop_group;

        $ids = explode($multiple_value_separator, $value);
        $ids = array_map('trim', $ids);
        $ids = array_unique($ids);
        foreach ($ids as $id) {
            if (empty($id)) {
                continue;
            }
            if ($this->model->find_products_by == 'reference' || !Validate::isInt($id)) {
                // Find product id by reference
                $sql = "SELECT * FROM `" . _DB_PREFIX_ . "product` WHERE `reference` = '" . pSQL($id) . "'";
                $row = Db::getInstance()->getRow($sql);
                if ($row && isset($row['id_product']) && $row['id_product']) {
                    $id = $row['id_product'];
                }
            }
            $sql = "INSERT INTO `" . _DB_PREFIX_ . "lineven_apo` (`id_shop_group`, `id_shop`, `name`, `short_description`, `comments`, `category_id`, `product_cart_id`, `product_id`, `minimum_amount`, `maximum_amount`, `is_active_groups`, `order_display`)  
                VALUES (" . (int) $id_shop_group . ", " . (int) $id_shop . ", 'a:1:{i:1;s:0:\"\";}', 'a:1:{i:1;s:0:\"\";}', '', NULL, " . (int) $product->id . ", " . (int) $id . ", 0, 0, 0, 1)";
            Db::getInstance()->execute($sql);
        }
    }

    protected function createJmarketplaceSellerRelation($id_product, $value)
    {
        if (!Module::isInstalled('jmarketplace') || !$id_product || !$value || !Validate::isInt($value)) {
            return;
        }
        $sql = "INSERT INTO `" . _DB_PREFIX_ . "seller_product` (`id_seller_product`, `id_product`)  
            VALUES (" . (int) $value . ", " . (int) $id_product . ")";
        Db::getInstance()->execute($sql);
    }

    protected function createProductaffiliateRelation($product_id, $lang_id, $productaffiliate_external_shop_url, $productaffiliate_button_text)
    {
        if (!Module::isInstalled('productaffiliate') || !$product_id || !$lang_id) {
            return;
        }
        if (!empty($productaffiliate_button_text) && !empty($productaffiliate_external_shop_url)) {
            $sql = "INSERT INTO `" . _DB_PREFIX_ . "affiliated_product` (`id_product`,`id_language`,`text`,`href`) 
                VALUES (" . (int) $product_id . "," . (int) $lang_id . ",'" . pSQL($productaffiliate_button_text) . "','" . pSQL($productaffiliate_external_shop_url) . "') 
                ON DUPLICATE KEY UPDATE `text`='" . pSQL($productaffiliate_button_text) . "', `href`='" . pSQL($productaffiliate_external_shop_url) . "'";
            $result = Db::getInstance()->execute($sql);
            if ($result) {
                Configuration::updateValue('AFFP_ID_LANGUAGE', $lang_id);
            }
        } else {
            $sql = "DELETE FROM `" . _DB_PREFIX_ . "affiliated_product` WHERE `id_product`=" . (int) $product_id . " AND `id_language`=" . (int) $lang_id;
            $result = Db::getInstance()->execute($sql);
        }
    }

    protected function createAdvancedcustomfieldsValue($product_id, $acf_technical_name, $value)
    {
        if (!Module::isInstalled('advancedcustomfields') || !$product_id || !$acf_technical_name) {
            return;
        }
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "advanced_custom_fields` WHERE `location` = 'product' AND `technical_name` = '" . pSQL($acf_technical_name) . "'";
        $acf = Db::getInstance()->getRow($sql);
        if (!$acf || !isset($acf['id_custom_field'])) {
            return;
        }
        // Delete old records
        $sql = "DELETE `" . _DB_PREFIX_ . "advanced_custom_fields_content`, `" . _DB_PREFIX_ . "advanced_custom_fields_content_lang` 
            FROM `" . _DB_PREFIX_ . "advanced_custom_fields_content` 
            INNER JOIN `" . _DB_PREFIX_ . "advanced_custom_fields_content_lang` ON `" . _DB_PREFIX_ . "advanced_custom_fields_content`.`id_custom_field_content` = `" . _DB_PREFIX_ . "advanced_custom_fields_content_lang`.`id_custom_field_content` 
            WHERE `" . _DB_PREFIX_ . "advanced_custom_fields_content`.`id_custom_field` = " . (int) $acf['id_custom_field'] . " AND `" . _DB_PREFIX_ . "advanced_custom_fields_content`.`resource_id` = " . (int) $product_id;
        Db::getInstance()->execute($sql);
        // Create new records
        $sql = "INSERT INTO `" . _DB_PREFIX_ . "advanced_custom_fields_content` (`id_store`, `id_custom_field`, `resource_id`, `value`)
            VALUES (" . (int) $this->context->shop->id . ", " . (int) $acf['id_custom_field'] . ", " . (int) $product_id . ", '" . pSQL($value) . "')";
        if (Db::getInstance()->execute($sql) && ($id_custom_field_content = (int) Db::getInstance()->Insert_ID())) {
            $languages = Language::getLanguages();
            foreach ($languages as $language) {
                $lang_value = $acf['translatable'] ? $value : "";
                if (!$this->model->replicate_all_languages && $language['id_lang'] != $this->model->lang_id) {
                    $lang_value = "";
                }
                $sql = "INSERT INTO `" . _DB_PREFIX_ . "advanced_custom_fields_content_lang` (`id_lang`, `lang_value`, `id_custom_field_content`) 
                    VALUES (" . (int) $language['id_lang'] . ", '" . pSQL($lang_value) . "', " . (int) $id_custom_field_content . ")";
                Db::getInstance()->execute($sql);
            }
        }
    }

    /**
     * Action called after finishing import
     */
    protected function actionAfterImport()
    {
        $csvRowsCount = ElegantalEasyImportCsv::model()->countAll(array(
            'condition' => array(
                'id_elegantaleasyimport' => $this->model->id,
            )
        ));
        // If no more csv rows found, it means CRON finished import
        if (empty($csvRowsCount)) {
            $this->notifyAfterImport();
        }
    }

    /**
     * Send email notification after CRON has finished importing products
     */
    protected function notifyAfterImport()
    {
        $email = $this->model->email_to_send_notification;
        if (!$email || (!Validate::isEmail($email) && !Validate::isAbsoluteUrl($email))) {
            return;
        }
        try {
            if (Validate::isEmail($email)) {
                $subject = $this->l('CRON finished importing products');
                $template_vars = array(
                    'rule_name' => $this->model->name,
                    'error_log' => $this->model->error_log,
                );
                $template_path = dirname(__FILE__) . '/mails/';
                Mail::Send($this->context->language->id, 'cron', $subject, $template_vars, $email, null, null, $this->displayName, null, null, $template_path);
            } elseif (Validate::isAbsoluteUrl($email)) {
                Tools::file_get_contents($email);
            }
        } catch (Exception $e) {
            // Do nothing
        }
    }

    protected function importCronInfo()
    {
        $cron_cpanel_doc = null;
        $documentation_urls = $this->getDocumentationUrls();
        foreach ($documentation_urls as $doc => $url) {
            if ($doc == 'Setup Cron Job In Cpanel') {
                $cron_cpanel_doc = $url;
                break;
            }
        }
        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'cron_url' => $this->getControllerUrl('import', array('id' => $this->model->id)),
                'cron_cpanel_doc' => $cron_cpanel_doc,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/import_cron.tpl');
    }

    protected function exportList()
    {
        // Pagination data
        $total = ElegantalEasyImportExport::model()->countAll();
        $limit = 20;
        $pages = ceil($total / $limit);
        $currentPage = (int) Tools::getValue('page', 1);
        $currentPage = ($currentPage > $pages) ? $pages : $currentPage;
        $halfVisibleLinks = 5;
        $offset = ($total > $limit) ? ($currentPage - 1) * $limit : 0;

        // Sorting records
        $sortableColumns = array(
            'name',
            'entity',
            'file_path',
            'last_export_date',
            'active',
        );

        $orderBy = in_array(Tools::getValue('orderBy'), $sortableColumns) ? Tools::getValue('orderBy') : 'id_elegantaleasyimport_export';
        $orderType = Tools::getValue('orderType') == 'asc' ? 'asc' : 'desc';

        $models = ElegantalEasyImportExport::model()->findAll(array(
            'order' => $orderBy . ' ' . $orderType,
            'limit' => $limit,
            'offset' => $offset,
        ));

        foreach ($models as &$model) {
            if (!empty($model['last_export_date']) && $model['last_export_date'] != '0000-00-00 00:00:00' && $model['last_export_date'] != '0000-00-00') {
                $model['download_link'] = $this->getControllerUrl('export', array('action' => 'download', 'id' => $model['id_elegantaleasyimport_export']));
            } else {
                $model['last_export_date'] = null;
                $model['download_link'] = null;
            }
        }

        $this->context->smarty->assign(
            array(
                'models' => $models,
                'adminUrl' => $this->getAdminUrl(),
                'pages' => $pages,
                'currentPage' => $currentPage,
                'halfVisibleLinks' => $halfVisibleLinks,
                'orderBy' => $orderBy,
                'orderType' => $orderType,
                'security_token_key' => $this->getSetting('security_token_key'),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/export_list.tpl');
    }

    protected function exportRenderSteps($step, $model)
    {
        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'model' => $model->getAttributes(),
                'step' => $step,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/export_steps.tpl');
    }

    protected function exportEdit()
    {
        $html = "";

        $model = null;
        if (Tools::getValue('id_elegantaleasyimport_export')) {
            $model = new ElegantalEasyImportExport(Tools::getValue('id_elegantaleasyimport_export'));
            if (!Validate::isLoadedObject($model)) {
                $this->setRedirectAlert($this->l('Record not found.'), 'error');
                $this->redirectAdmin(array('event' => 'exportList'));
            }
        }
        if (!$model) {
            $model = new ElegantalEasyImportExport();
        }

        if ($this->isPostRequest()) {
            $old_file_path = $model->file_path;

            // Validate submitted data
            $errors = $model->validateAndAssignModelAttributes();

            if ($model->file_path && Tools::substr($model->file_path, 0, 1) != '/') {
                $errors[] = sprintf($this->l('You should enter absolute path for %sFile Path%s which should start with /'), '<b>', '</b>');
            } elseif ($model->file_path && (!is_file($model->file_path) || !filesize($model->file_path) || filesize($model->file_path) < 5) && !file_put_contents($model->file_path, " ")) {
                $errors[] = $this->l('File Path you specified is not writable.') . ' ' . $model->file_path . ' ' . $this->l('Please make sure you enter file path that the module has permissions to write.');
            }

            if ($model->price_range) {
                $model->price_range = str_replace(" ", "", $model->price_range);
                if (preg_match("/^([0-9]+(\.[0-9]{1,})?)-([0-9]+(\.[0-9]{1,})?)$/", $model->price_range, $match)) {
                    if ($match[1] > $match[3]) {
                        $model->price_range = "";
                    }
                } else {
                    $model->price_range = "";
                }
            }
            if ($model->quantity_range) {
                $model->quantity_range = str_replace(" ", "", $model->quantity_range);
                if (preg_match("/^(\d+)-(\d+)$/", $model->quantity_range, $match)) {
                    if ($match[1] > $match[2]) {
                        $model->quantity_range = "";
                    }
                } else {
                    $model->quantity_range = "";
                }
            }

            if (!Tools::getValue('category_ids')) {
                if (Tools::getValue('categoryBox')) {
                    $model->category_ids = ElegantalEasyImportTools::serialize(Tools::getValue('categoryBox'));
                } else {
                    $model->category_ids = null;
                }
            }

            if (empty($errors)) {
                $result = empty($model->id) ? $model->add() : $model->update();
                if ($result) {
                    if ($old_file_path != $model->file_path) {
                        @unlink($old_file_path);
                    }
                    if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                        $this->setRedirectAlert($this->l('Rule saved successfully.'), 'success');
                        $this->redirectAdmin(array(
                            'event' => 'exportEdit',
                            'id_elegantaleasyimport_export' => $model->id
                        ));
                    } else {
                        $this->redirectAdmin(array(
                            'event' => 'exportColumns',
                            'id_elegantaleasyimport_export' => $model->id,
                        ));
                    }
                } else {
                    $html .= $this->displayError($this->l('Rule could not be saved.') . ' ' . Db::getInstance()->getMsgError());
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = $model->getAttributes();
        $fields_value['shop_ids[]'] = ElegantalEasyImportTools::unserialize($fields_value['shop_ids']);
        $fields_value['category_ids'] = ElegantalEasyImportTools::unserialize($fields_value['category_ids']);
        $fields_value['supplier_ids[]'] = ElegantalEasyImportTools::unserialize($fields_value['supplier_ids']);
        $fields_value['manufacturer_ids[]'] = ElegantalEasyImportTools::unserialize($fields_value['manufacturer_ids']);

        // Default Values
        if (!$fields_value['id_elegantaleasyimport_export'] && !$this->isPostRequest()) {
            $fields_value['currency_id'] = Currency::getDefaultCurrency()->id;
            $fields_value['shop_ids[]'] = 'all';
            $fields_value['file_path'] = realpath(dirname(__FILE__) . '/tmp') . '/export_' . date('d-m-Y') . '_' . date('His') . '.csv';
            $fields_value['supplier_ids[]'] = 'all';
            $fields_value['manufacturer_ids[]'] = 'all';
            $fields_value['active'] = 1;
        }

        // Currency input
        $currencies = $this->getCurrenciesForSelect();
        if (count($currencies) > 1) {
            $currency_input = array(
                'type' => 'select',
                'label' => $this->l('Currency'),
                'name' => 'currency_id',
                'options' => array(
                    'query' => $this->getCurrenciesForSelect(),
                    'id' => 'key',
                    'name' => 'value'
                ),
                'desc' => $this->l('Select currency to use for the product price.'),
            );
        } else {
            $currency_input = array(
                'type' => 'hidden',
                'name' => 'currency_id'
            );
        }

        // Shops input
        $shops = Shop::getShops();
        if (Shop::isFeatureActive() && is_array($shops) && count($shops) > 1) {
            $shops_for_select = array(array('key' => 'all', 'value' => $this->l('All shops')));
            foreach ($shops as $shop) {
                $shops_for_select[] = array('key' => $shop['id_shop'], 'value' => $shop['name']);
            }
            $shops_input = array(
                'type' => 'select',
                'label' => $this->l('Shops'),
                'name' => 'shop_ids[]',
                'multiple' => true,
                'options' => array(
                    'query' => $shops_for_select,
                    'id' => 'key',
                    'name' => 'value'
                ),
                'desc' => $this->l('Select shop(s) from which products should be exported.'),
            );
        } else {
            $shops_input = array(
                'type' => 'hidden',
                'name' => 'shop_ids[]'
            );
        }

        // Categories input
        // Category input is different in 1.5
        $rootCategory = Category::getRootCategory();
        if (_PS_VERSION_ < '1.6') {
            $categories_input = array(
                'type' => 'categories',
                'label' => $this->l('Categories'),
                'name' => 'category_ids',
                'values' => array(
                    'trads' => array(
                        'Root' => array('id_category' => $rootCategory->id_category, 'name' => $rootCategory->name),
                        'selected' => $this->l('Selected'),
                        'Collapse All' => $this->l('Collapse All'),
                        'Expand All' => $this->l('Expand All'),
                        'Check All' => $this->l('Check All'),
                        'Uncheck All' => $this->l('Uncheck All'),
                    ),
                    'selected_cat' => $fields_value['category_ids'],
                    'input_name' => 'category_ids[]',
                    'use_checkbox' => true,
                    'use_radio' => false,
                    'use_search' => false,
                    'top_category' => Category::getTopCategory(),
                    'use_context' => true,
                )
            );
        } else {
            $categories_input = array(
                'type' => 'categories',
                'label' => $this->l('Categories'),
                'name' => 'category_ids',
                'tree' => array(
                    'use_search' => true,
                    'id' => 'elegantal_category_ids',
                    'root_category' => $rootCategory->id,
                    'use_checkbox' => true,
                    'selected_categories' => $fields_value['category_ids'],
                ),
                'desc' => $this->l('Select categories from which you want to export products. You can leave it empty to export products from all categories.'),
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Step') . ' 1: ' . $this->l('Export Configuration'),
                    'icon' => 'icon-edit'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'name' => 'name',
                        'desc' => $this->l('Name is for your reference only.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Export Entity'),
                        'name' => 'entity',
                        'options' => array(
                            'query' => array(
                                array('key' => 'product', 'value' => $this->l('Products')),
                                array('key' => 'combination', 'value' => $this->l('Combinations')),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Choose what you would like to export.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Full path to export file'),
                        'name' => 'file_path',
                        'desc' => $this->l('Enter absolute file path where exported file should be saved.') . ' ' . $this->l('For example') . ': ' . realpath(dirname(__FILE__) . '/tmp') . '/export_123.csv',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('File format'),
                        'name' => 'file_format',
                        'options' => array(
                            'query' => array(
                                array('key' => 'csv', 'value' => 'CSV'),
                                array('key' => 'xml', 'value' => 'XML'),
                                array('key' => 'json', 'value' => 'JSON'),
                                array('key' => 'xls', 'value' => 'XLS'),
                                array('key' => 'xlsx', 'value' => 'XLSX'),
                                array('key' => 'txt', 'value' => 'TXT'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Choose file format to use for this export rule.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Multiple value separator'),
                        'name' => 'multiple_value_separator',
                        'options' => array(
                            'query' => array(
                                array('key' => '|', 'value' => '|'),
                                array('key' => ';', 'value' => ';'),
                                array('key' => ',', 'value' => ','),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select a character used as delimeter for list type values. For example, images list separator: image1.jpg|image2.jpg|image3.jpg'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Multiple subcategory separator'),
                        'name' => 'multiple_subcategory_separator',
                        'options' => array(
                            'query' => array(
                                array('key' => '=>', 'value' => '=>'),
                                array('key' => '->', 'value' => '->'),
                                array('key' => '>', 'value' => '>'),
                                array('key' => '/', 'value' => '/'),
                                array('key' => '|', 'value' => '|'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select separator that is used to separate subcategories.') . ' ' . $this->l('For example, if your categories are written like the following:') . ' Home/Fashion/Men, Home/Fashion/Men/T-Shirt, Home/Fashion/Men/T-Shirt/Polo. ' . $this->l('According to this example, you should select / slash.') . ' ' . $this->l('NOTE that this is DIFFERENT than Multiple Value Separator.') . ' ' . $this->l('In this example, Multiple Value Separator is a comma.'),
                    ),
                    $currency_input,
                    $shops_input,
                    $categories_input,
                    array(
                        'type' => 'select',
                        'label' => $this->l('Suppliers'),
                        'name' => 'supplier_ids[]',
                        'multiple' => true,
                        'options' => array(
                            'query' => $this->getSuppliersForSelect(),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Products of selected suppliers will be exported.') . ' ' . $this->l('You can select multiple items with SHIFT + LEFT CLICK.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Manufacturers'),
                        'name' => 'manufacturer_ids[]',
                        'multiple' => true,
                        'options' => array(
                            'query' => $this->getManufacturersForSelect(),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Products of selected manufacturers will be exported.') . ' ' . $this->l('You can select multiple items with SHIFT + LEFT CLICK.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Exclude products by ID'),
                        'name' => 'exclude_product_ids',
                        'desc' => $this->l('Enter product IDs separated by comma. For example: 8,9,10,25. These products will be excluded from export.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Product status'),
                        'name' => 'product_status',
                        'options' => array(
                            'query' => array(
                                array('key' => '2', 'value' => 'Both active and inactive products'),
                                array('key' => '1', 'value' => 'Only active products'),
                                array('key' => '0', 'value' => 'Only inactive products'),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select weather you want to export only active products or only disabled products or both.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Price Modifier'),
                        'name' => 'price_modifier',
                        'desc' => $this->l('You can use arithmetic formula which will be used to modify product price while exporting.') . ' ' . $this->l('Examples') . ': *2, /3, +1.11, -0.5',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Price range'),
                        'name' => 'price_range',
                        'desc' => $this->l('Only products that have price in specified range will be exported.') . ' ' . $this->l('You need to enter it in this format:') . ' 100 - 500',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Quantity range'),
                        'name' => 'quantity_range',
                        'desc' => $this->l('Only products that have quantity in specified range will be exported.') . ' ' . $this->l('You need to enter it in this format:') . ' 100 - 500',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Order by'),
                        'name' => 'order_by',
                        'options' => array(
                            'query' => array(
                                array('key' => 'p.id_product', 'value' => $this->l('Product ID')),
                                array('key' => 'pl.name', 'value' => $this->l('Name')),
                                array('key' => 'psh.date_add', 'value' => $this->l('Date added')),
                                array('key' => 'psh.date_upd', 'value' => $this->l('Date updated')),
                                array('key' => 'psh.price', 'value' => $this->l('Price')),
                                array('key' => 'RAND()', 'value' => $this->l('Random')),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Products will be sorted by specified attribute.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Order direction'),
                        'name' => 'order_direction',
                        'options' => array(
                            'query' => array(
                                array('key' => 'ASC', 'value' => $this->l('From smallest to largest (ASC)')),
                                array('key' => 'DESC', 'value' => $this->l('From largest to smallest (DESC)')),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Sort products in ascending (ASC) or descending (DESC) order.'),
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'active',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save & Next'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'exportList')),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'submitExportEdit';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'exportEdit', 'id_elegantaleasyimport_export' => $model->id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $this->exportRenderSteps(1, $model) . $html . $helper->generateForm(array($fields_form));
    }

    protected function exportColumns()
    {
        $model = null;
        if (Tools::getValue('id_elegantaleasyimport_export')) {
            $model = new ElegantalEasyImportExport(Tools::getValue('id_elegantaleasyimport_export'));
        }
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'exportList'));
        }

        $columns_with_title = $model->getColumns();
        $columns_keys = array_keys($columns_with_title);
        $default_columns = array_fill_keys($columns_keys, 1);
        $model_columns = ElegantalEasyImportTools::unserialize($model->columns);

        if ($this->isPostRequest()) {
            $columns = array();
            $column_override_values = array();
            foreach ($columns_keys as $key) {
                if (Tools::isSubmit($key)) {
                    $columns[$key] = (int) Tools::getValue($key);
                } else {
                    $columns[$key] = '1';
                }
                if (Tools::isSubmit('default_' . $key)) {
                    $column_override_values[$key] = Tools::getValue('default_' . $key);
                } else {
                    $column_override_values[$key] = '';
                }
            }

            // Save columns
            $model->columns = ElegantalEasyImportTools::serialize($columns);
            $model->column_override_values = ElegantalEasyImportTools::serialize($column_override_values);
            $model->update();

            if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                $this->setRedirectAlert($this->l('Rule saved successfully.'), 'success');
                $this->redirectAdmin(array(
                    'event' => 'exportColumns',
                    'id_elegantaleasyimport_export' => $model->id
                ));
            } if (Tools::isSubmit('submitAndExport') && !Tools::isSubmit('submitAndNext')) {
                $this->redirectAdmin(array(
                    'event' => 'export',
                    'id_elegantaleasyimport_export' => $model->id
                ));
            } else {
                $this->redirectAdmin(array(
                    'event' => 'exportCronInfo',
                    'id_elegantaleasyimport_export' => $model->id
                ));
            }
        }

        $fields_value = array_merge($default_columns, $model_columns);
        $column_override_values = ElegantalEasyImportTools::unserialize($model->column_override_values);

        $inputs = array();
        foreach ($columns_with_title as $key => $title) {
            $inputs[] = array(
                'type' => 'elegantal_columns_select',
                'label' => $title,
                'name' => $key,
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => $key . '_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => $key . '_off',
                        'value' => 0,
                        'label' => $this->l('No')
                    )
                ),
                'column_override_value' => isset($column_override_values[$key]) ? $column_override_values[$key] : '',
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Step') . ' 2: ' . $this->l('Select what to export'),
                    'icon' => 'icon-columns'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save & Export'),
                    'name' => 'submitAndExport',
                    'icon' => 'process-icon-upload'
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & CRON'),
                        'name' => 'submitAndNext',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-terminal'
                    ),
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-edit'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'exportList')),
                        'title' => $this->l('Home'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'exportEdit', 'id_elegantaleasyimport_export' => $model->id)),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'submitExportColumns';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'exportColumns', 'id_elegantaleasyimport_export' => $model->id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $this->exportRenderSteps(2, $model) . $helper->generateForm(array($fields_form));
    }

    protected function export()
    {
        $model = null;
        if (Tools::getValue('id_elegantaleasyimport_export')) {
            $model = new ElegantalEasyImportExport(Tools::getValue('id_elegantaleasyimport_export'));
        }
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'exportList'));
        }

        if (Tools::getValue('ajax')) {
            $result = $model->export();
            die(Tools::jsonEncode($result));
        }

        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'moduleUrl' => $this->getModuleUrl(),
                'module' => $model->getAttributes(),
                'download_link' => $this->getControllerUrl('export', array('action' => 'download', 'id' => $model->id)),
            )
        );

        return $this->exportRenderSteps(3, $model) . $this->display(__FILE__, 'views/templates/admin/export.tpl');
    }

    protected function exportChangeStatus()
    {
        $model = null;
        if (Tools::getValue('id_elegantaleasyimport_export')) {
            $model = new ElegantalEasyImportExport(Tools::getValue('id_elegantaleasyimport_export'));
        }
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'exportList'));
        }
        $model->active = $model->active == 1 ? 0 : 1;
        if ($model->update()) {
            $this->setRedirectAlert($this->l('Status changed successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Status could not be changed.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'exportList'));
    }

    protected function exportDuplicate()
    {
        $model = null;
        if (Tools::getValue('id_elegantaleasyimport_export')) {
            $model = new ElegantalEasyImportExport(Tools::getValue('id_elegantaleasyimport_export'));
        }
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'exportList'));
        }

        $model->id = null;
        $model->id_elegantaleasyimport_export = null;
        $model->name .= ' (Copy)';

        $count = 1;
        $dir = pathinfo($model->file_path, PATHINFO_DIRNAME);
        $filename = pathinfo($model->file_path, PATHINFO_FILENAME);
        $ext = Tools::strtolower(pathinfo($model->file_path, PATHINFO_EXTENSION));
        do {
            $count++;
            $model->file_path = $dir . DIRECTORY_SEPARATOR . $filename . '_' . $count . '.' . $ext;
        } while (file_exists($model->file_path));

        $model->last_export_date = null;
        $model->error_log = "";
        $model->active = 1;
        if ($model->add()) {
            $this->setRedirectAlert($this->l('Rule duplicated successfully.'), 'success');
            if (!file_put_contents($model->file_path, " ")) {
                $this->setRedirectAlert($this->l('File Path you specified is not writable.') . ' ' . $model->file_path . ' ' . $this->l('Please make sure you enter file path that the module has permissions to write.'), 'error');
            }
            $this->redirectAdmin(array(
                'event' => 'exportEdit',
                'id_elegantaleasyimport_export' => $model->id,
            ));
        } else {
            $this->setRedirectAlert($this->l('Rule could not be duplicated.') . ' ' . Db::getInstance()->getMsgError(), 'error');
        }

        $this->redirectAdmin(array('event' => 'exportList'));
    }

    protected function exportDelete()
    {
        $model = null;
        if (Tools::getValue('id_elegantaleasyimport_export')) {
            $model = new ElegantalEasyImportExport(Tools::getValue('id_elegantaleasyimport_export'));
        }
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'exportList'));
        }

        if ($model->delete()) {
            @unlink($model->file_path);
            $this->setRedirectAlert($this->l('Rule deleted successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Rule could not be deleted.') . ' ' . Db::getInstance()->getMsgError(), 'error');
        }
        $this->redirectAdmin(array('event' => 'exportList'));
    }

    protected function exportCronInfo()
    {
        $model = null;
        if (Tools::getValue('id_elegantaleasyimport_export')) {
            $model = new ElegantalEasyImportExport(Tools::getValue('id_elegantaleasyimport_export'));
        }
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'exportList'));
        }

        $cron_cpanel_doc = null;
        $documentation_urls = $this->getDocumentationUrls();
        foreach ($documentation_urls as $doc => $url) {
            if ($doc == 'Setup Cron Job In Cpanel') {
                $cron_cpanel_doc = $url;
                break;
            }
        }
        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'cron_url' => $this->getControllerUrl('export', array('id' => $model->id)),
                'cron_cpanel_doc' => $cron_cpanel_doc,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/export_cron.tpl');
    }

    /**
     * Action to trigger CRON manually
     */
    protected function triggerCron()
    {
        $url = "";
        $id = Tools::getValue('id');
        $type = Tools::getValue('type');
        if ($type == 'import') {
            $url = $this->getControllerUrl('import', array('id' => $id));
        } elseif ($type == 'export') {
            $url = $this->getControllerUrl('export', array('id' => $id));
        } else {
            $this->setRedirectAlert("Invalid Type.", 'error');
            $this->redirectAdmin();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = ($response === false) ? curl_error($ch) : "";

        curl_close($ch);

        if ($http_code == 200) {
            $this->setRedirectAlert($this->l('CRON executed successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('CRON execution failed.') . ' ' . $this->l('Error') . ': ' . $http_code . ' ' . $error, 'error');
        }

        if ($type == 'export') {
            $this->redirectAdmin(array('event' => 'exportList'));
        }

        $this->redirectAdmin();
    }
}
