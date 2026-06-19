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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

require_once _PS_MODULE_DIR_ . 'cedwish/classes/api.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/product.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/profile.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/queue.php';

class AdminCedWishProductController extends ModuleAdminController
{
    protected $profiles = array();

    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'product';
        $this->list_id = 'product';
        $this->list_no_link = true;
        $this->lang = true;

        $this->multishop_context = Shop::CONTEXT_ALL;
        parent::__construct();

        if (empty($this->profiles)) {
            $profiles = CedWishProfile::getProfiles();
            if (!empty($profiles)) {
                foreach ($profiles as $profile) {
                    $this->profiles[$profile['id_cedwish_profile']] = $profile['name'];
                }
            }
        }

        $this->fields_list = array(
            'image' => array(
                'title' => $this->l('Image'),
                'align' => 'center',
                'image' => 'p',
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'id_product' => array(
                'title' => $this->l('ID'),
                'class' => 'fixed_width_sx',
                'filter_key' => 'a!id_product'
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'filter_key' => 'a!reference'
            ),
            'name' => array(
                'title' => $this->l('Product Title'),
                'filter_key' => 'b!name',
                'class' => 'cedwish_product_name'
            ),
            'name_category' => array(
                'title' => $this->l('Category'),
                'filter_key' => 'cl!name',
            ),
            'id_cedwish_profile' => array(
                'title' => $this->l('Wish Profile'),
                'filter_key' => 'cpp!id_cedwish_profile',
                'type' => 'select',
                'list' => $this->profiles,
                'callback' => 'renderProfileList'
            ),
            'price' => array(
                'title' => $this->l('Base Price'),
                'type' => 'price',
                'class' => 'fixed_width_sx',
                'align' => 'text-center',
                'filter_key' => 'a!price'
            ),
            'sav_quantity' => array(
                'title' => $this->l('QTY'),
                'type' => 'int',
                'class' => 'fixed_width_sx',
                'align' => 'text-center',
                'filter_key' => 'sav!quantity',
                'orderby' => true,
                'badge_danger' => true,
            ),
            'enabled' => array(
                'title' => $this->l('Sync Enabled'),
                'align' => 'center',
                'class' => 'fixed_width_sx',
                'active' => 'enabled',
                'type' => 'bool',
                'orderby' => false,
            ),
            'marketplace_id' => array(
                'title' => $this->l('Wish Product ID'),
                'class' => 'fixed_width_sx',
            ),
            'status' => array(
                'title' => $this->l('Wish Status'),
                'class' => 'fixed_width_sx'
            ),
            'error' => array(
                'title' => $this->l('Status'),
                'filter_key' => 'status',
                'class' => 'fixed_width_sx',
                'callback' => 'renderProductErrors'
            ),
        );
        $this->bulk_actions = array(
            'upload' => array(
                'text' => $this->l('Upload'),
                'icon' => 'icon-upload',
            ),
            'update' => array(
                'text' => $this->l('Update'),
                'icon' => 'icon-upload',
            ),
            'sync' => array(
                'text' => $this->l('Sync Status'),
                'icon' => 'icon-refresh',
            ),
            'enable' => array(
                'text' => $this->l('Enable'),
                'icon' => 'icon-power-off text-success',
            ),
            'disable' => array(
                'text' => $this->l('Disable'),
                'icon' => 'icon-power-off text-danger',
            ),
            'price' => array(
                'text' => $this->l('Sync Price'),
                'icon' => 'icon-refresh',
            ),
            'stock' => array(
                'text' => $this->l('Sync Stock'),
                'icon' => 'icon-refresh',
            ),
            'brand_tagging' => array(
                'text' => $this->l('Brand Tagging'),
                'icon' => 'icon-plus',
            ),
            'delete' => array(
                'text' => $this->l('Delete'),
                'icon' => 'icon-trash',
                'confirmation' => $this->l('Are you Sure you want to delete this Item'),
            ),
        );

        $this->_conf = array(
            1 => ('Product Data Updated successfully.'),
            2 => ('Product Uploaded/Updated successfully.'),
            3 => ('Stock Uploaded/Updated successfully.'),
            4 => ('Price Uploaded/Updated successfully.')
        );
    }

    public function renderDetails()
    {
        if (Tools::isSubmit('id_product')
            && Tools::getIsset('detailsproduct')
        ) {
            // override attributes
            $this->identifier = 'id_product_attribute';
            $this->list_id = 'product_attribute';
            $this->lang = false;

            $this->addRowAction('wishedit');
            $this->addRowAction('upload');
            $this->addRowAction('wish_stock');
            $this->addRowAction('wish_price');

            // no link on list rows
            $this->list_no_link = true;

            // inits toolbar
            $this->toolbar_btn = array();

            // Get product id
            $product_id = (int)Tools::getValue('id_product');

            $id_shop = Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP
                ? (int)$this->context->shop->id : 'p.id_shop_default';

            // Load product attributes with sql override
            $this->table = 'product_attribute';
            $this->list_id = 'product_attribute';
            $this->_select = 'a.id_product_attribute as id, a.id_product, a.reference, 
            IF (pai.`id_image`,pai.`id_image`,image_shop.`id_image`) AS `id_image`,
            ccp.error as error,
            ccp.variation_id as marketplace_id,
            ccp.status,
            a.id_product_attribute,
            cpp.id_cedwish_profile,
            IF (ccp.enabled IS NULL, 1, ccp.enabled) as enabled,
            cl.`name` AS `name_category`,
            p.`price` AS `price`,
            sav.`quantity` AS `sav_quantity`, 
            IF(sav.`quantity`<=0, 1, 0) AS `badge_danger`';
            $this->_join = 'INNER JOIN `' . _DB_PREFIX_ . 'product` p ON (p.id_product = a.id_product)';
            $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'stock` s ON (s.id_product = a.id_product 
            AND s.id_product_attribute = a.id_product_attribute )
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (p.`id_category_default` = cl.`id_category` 
            AND cl.`id_lang` = ' . $this->context->language->id . ' AND cl.id_shop = ' . $id_shop . ')
			LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . $id_shop . ')
			LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop ON (image_shop.`id_product` = a.`id_product`)
			JOIN `' . _DB_PREFIX_ . 'cedwish_profile_product` cpp ON (cpp.`id_product` = a.`id_product`)
			LEFT JOIN `' . _DB_PREFIX_ . 'cedwish_product` ccp ON (ccp.`id_product` = a.`id_product` 
			AND ccp.`id_product_attribute` = a.`id_product_attribute`)
			LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` pai 
			ON (a.`id_product_attribute` = pai.`id_product_attribute`)
			LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sav ON (sav.`id_product` = p.`id_product` AND 
			sav.`id_product_attribute` =  a.`id_product_attribute`'
            . StockAvailable::addSqlShopRestriction(null, null, 'sav') . ') ';
            $this->_where = 'AND (image_shop.`cover` = 1) AND (image_shop.id_shop = ' . $id_shop . ') 
            AND (a.id_product = ' . $product_id .')';
            $this->_group = 'GROUP BY a.id_product_attribute';

            $this->fields_list['name'] =
                array(
                    'title' => 'Product Name',
                    'orderby' => false,
                    'filter' => false,
                    'search' => false
                );

            if (Tools::getIsset('id_product_attribute') && Tools::getIsset('enabledproduct_attribute')) {
                self::$currentIndex = self::$currentIndex . '&id_product=' . (int)$product_id . '&detailsproduct';
            } elseif (Tools::getIsset('id_product_attribute')) {
                self::$currentIndex = self::$currentIndex . '&id_product=' . (int)$product_id;
            } else {
                self::$currentIndex = self::$currentIndex . '&id_product=' . (int)$product_id . '&detailsproduct';
            }
            $this->processFilter();
            $list_actions = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/product_list.tpl'
            );

            $this->bulk_actions = array();
            return $list_actions . parent::renderList();
        } else {
            return $this->renderList();
        }
    }

    /**
     * AdminController::renderList() override
     * @see AdminController::renderList()
     */
    public function renderList()
    {
        // sets actions
        $this->addRowAction('details');
        $this->addRowAction('wishedit');
        $this->addRowAction('upload');
        $this->addRowAction('wish_stock');
        $this->addRowAction('wish_price');
        // no link on list rows
        $this->list_no_link = true;
        // inits toolbar
        $this->toolbar_btn = array();
        // overrides query
        $id_shop = Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP
            ? (int)$this->context->shop->id : 'a.id_shop_default';

        // if strict group enabled
        /**
         *  ANY_VALUE(cfp.enabled) as enabled,
            ANY_VALUE(cfp.error) as error,
            ANY_VALUE(cfp.marketplace_id) as marketplace_id,
            ANY_VALUE(cpp.id_cedwish_profile) as id_cedwish_profile,
            ANY_VALUE(cfp.status) as status,
         */

        $this->_select = '
            a.reference as reference,
            IF (cfp.enabled IS NULL, 1, cfp.enabled) as enabled,
            cfp.error,
            cfp.marketplace_id,
            cpp.id_cedwish_profile,
            cfp.status,  
            pa.id_product_attribute,
            i.`id_image` AS `id_image`,
            cl.`name` AS `name_category`,
            a.`price` AS `price_final`,
            sav.`quantity` AS `sav_quantity`, 
            IF(sav.`quantity`<=0, 1, 0) AS `badge_danger`,
            a.id_product as id, COUNT(pa.id_product_attribute) as variations';
        $this->_join = '
        LEFT JOIN `' . _DB_PREFIX_ . 'cedwish_product` cfp ON (cfp.`id_product` = a.`id_product` 
		AND cfp.`id_product_attribute` = 0)
		JOIN `' . _DB_PREFIX_ . 'cedwish_profile_product` cpp ON (cpp.`id_product` = a.`id_product`) 
        LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.id_product = a.id_product)'
        . Shop::addSqlAssociation('product_attribute', 'pa', false) . ' 
        LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (a.`id_category_default` = cl.`id_category` 
        AND b.`id_lang` = cl.`id_lang` AND cl.id_shop = ' . $id_shop . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . $id_shop . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop ON (image_shop.`id_product` = a.`id_product` 
		AND image_shop.`cover` = 1 AND image_shop.id_shop = ' . $id_shop . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_image` = image_shop.`id_image`)
		LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sav ON (sav.`id_product` = a.`id_product` 
		AND sav.`id_product_attribute` = 0
		' . StockAvailable::addSqlShopRestriction(null, null, 'sav') . ') ';
        $this->_group .= 'GROUP BY a.id_product';

        $list_actions = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/product_list.tpl'
        );
        return $list_actions . parent::renderList();
    }

    /**
     * AdminController::getList() override
     * @param int $id_lang
     * @param string|null $order_by
     * @param string|null $order_way
     * @param int $start
     * @param int|null $limit
     * @param int|bool $id_lang_shop
     *
     * @throws PrestaShopException
     * @see AdminController::getList()
     *
     */
    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        // Check each row to see if there are combinations and get the correct action in consequence
        $nb_items = count($this->_list);

        for ($i = 0; $i < $nb_items; $i++) {
            $item = &$this->_list[$i];

            $item['reference'] = (!empty($item['reference']) && isset($item['reference'])) ? $item['reference'] : '--';
            // if it's an ajax request we have to consider manipulating a product variation
            if (Tools::isSubmit('id_product')) {
                $item['name'] = Product::getProductName(
                    $item['id_product'],
                    empty($item['id_product_attribute']) ? null : $item['id_product_attribute']
                );
                $item['id_product'] = empty($item['id_product_attribute']) ? null : $item['id_product_attribute'];
                $this->addRowActionSkipList('details', array($item['id']));
            } elseif (array_key_exists('variations', $item) && (int)$item['variations'] > 0) {
                // we have to deactivate stock actions on current row
                $this->addRowActionSkipList('wishedit', $item['id']);
            } else {
                $this->addRowActionSkipList('details', array($item['id']));
            }
        }
    }

    /**
     * AdminController::postProcess() override
     * @see AdminController::postProcess()
     */
    public function postProcess()
    {
        if ((Tools::isSubmit('wishedit')) &&
            Tools::isSubmit('is_post')
        ) {
            // get product ID
            $id_product = (int)Tools::getValue('id_product', 0);
            if ($id_product <= 0) {
                $this->errors[] = Tools::displayError('The selected product is not valid.');
            }

            // get product_attribute ID
            $id_product_attribute = (int)Tools::getValue('id_product_attribute', 0);

            $token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
            if ($id_product_attribute) {
                $redirect = self::$currentIndex . '&id_product=' . $id_product . '&detailsproduct&token=' . $token;
            } else {
                $redirect = self::$currentIndex . '&token=' . $token;
            }
        }

        if (Tools::isSubmit('submitFilter') && Tools::getIsset('detailsproduct')) {
            $id_product = (int)Tools::getValue('id_product', 0);
            $token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
            $redirect = self::$currentIndex . '&id_product=' . $id_product . '&detailsproduct&token=' . $token;
            Tools::redirectAdmin($redirect);
        }

        if (Tools::isSubmit('wishedit') && Tools::isSubmit('is_post')) {
            if (empty($this->errors)) {
                if (!empty(Tools::getAllValues())) {
                    $product_data = Tools::getAllValues();
                    $skip_fields = array(
                        'controller',
                        'token',
                        'controllerUri',
                        'is_post',
                        'wishedit',
                    );
                    foreach ($skip_fields as $skip_field) {
                        if (isset($product_data[$skip_field])) {
                            unset($product_data[$skip_field]);
                        }
                    }
                    $id_cedwish_product = Db::getInstance()->getValue(
                        'SELECT id_cedwish_product FROM ' . _DB_PREFIX_ . 'cedwish_product 
                        WHERE id_product = "' . (int)$product_data['id_product'] . '" AND 
                        id_product_attribute = "' . (int)$product_data['id_product_attribute'] . '"'
                    );
                    if ($id_cedwish_product) {
                        Db::getInstance()->update(
                            'cedwish_product',
                            array(
                                'product_level_data' => pSQL(json_encode($product_data))
                            ),
                            'id_product = "' . (int)$product_data['id_product'] . '" 
                            AND id_product_attribute = "' . (int)$product_data['id_product_attribute'] . '"'
                        );
                    } else {
                        Db::getInstance()->insert(
                            'cedwish_product',
                            array(
                                'product_level_data' => pSQL(json_encode($product_data)),
                                'id_product_attribute' => (int)$product_data['id_product_attribute'],
                                'id_product' => (int)$product_data['id_product'],
                            )
                        );
                    }

                    if (Tools::getIsset('detailsproduct')) {
                        $redirect = self::$currentIndex . '&id_product=' . (int)$id_product;
                        $redirect .= '&wishedit&token=' . $token;
                        $redirect .= '&detailsproduct';
                    }
                    Tools::redirectAdmin($redirect . '&conf=1');
                } else {
                    $this->errors[] = Tools::displayError('An error occurred. No stock was added.');
                }
            }
        }
        if (Tools::getIsset('upload')
            && Tools::getIsset('upload_product_id')
            && Tools::getValue('upload_product_id')
        ) {
            $facebook_product = new CedWishProduct();
            $product_id = Tools::getValue('upload_product_id');
            if (Tools::getIsset('detailsproduct')) {
                $id_product = Tools::getValue('id_product');
                $resposne = $facebook_product->upload(array($id_product), array($product_id));
            } else {
                $resposne = $facebook_product->upload(array($product_id));
            }

            if (isset($resposne['success']) && $resposne['success']) {
                $this->confirmations[] = $resposne['msg'];
            } elseif (isset($resposne['msg'])) {
                $this->errors[] = $resposne['msg'];
            } else {
                $this->errors[] = 'Something Went Wrong.';
            }
            if (Tools::getIsset('detailsproduct') && empty($this->errors)) {
                $redirect = self::$currentIndex . '&id_product=' . (int)Tools::getValue('id_product');
                if ($product_id) {
                    $redirect .= '&id_product_attribute=' . (int)$product_id;
                }
                $token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
                $redirect .= '&token=' . $token;
                $redirect .= '&detailsproduct';
                Tools::redirectAdmin($redirect . '&conf=2');
            }
        }
        if (Tools::getIsset('stock')
            && Tools::getIsset('stock_product_id')
            && Tools::getValue('stock_product_id')
        ) {
            $facebook_product = new CedWishProduct();
            $product_id = Tools::getValue('stock_product_id');
            $resposne = $facebook_product->upload(array($product_id));
            if (isset($resposne['success']) && $resposne['success']) {
                $this->confirmations[] = $resposne['msg'];
            } elseif (isset($resposne['msg'])) {
                $this->errors[] = $resposne['msg'];
            } else {
                $this->errors[] = 'Something Went Wrong.';
            }
        }
        if (Tools::getIsset('price')
            && Tools::getIsset('price_product_id')
            && Tools::getValue('price_product_id')
        ) {
            $facebook_product = new CedWishProduct();
            $product_id = Tools::getValue('price_product_id');
            $resposne = $facebook_product->upload(array($product_id));
            if (isset($resposne['success']) && $resposne['success']) {
                $this->confirmations[] = $resposne['msg'];
            } elseif (isset($resposne['msg'])) {
                $this->errors[] = $resposne['msg'];
            } else {
                $this->errors[] = 'Something Went Wrong.';
            }
        }

        return parent::postProcess();
    }
    /**
     * AdminController::init() override
     * @see AdminController::init()
     */
    public function init()
    {
        parent::init();
        $product_name = false;
        $id_product = (int)Tools::getValue('id_product');
        if (!empty($id_product)) {
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            $product_name = Product::getProductName($id_product, $id_product_attribute);
        }

        if (Tools::isSubmit('upload')) {
            $this->display = 'details';
            $this->toolbar_title = ('Add a product');
        }

        if (Tools::isSubmit('wishedit')) {
            $this->display = 'wishedit';
            $this->toolbar_title = ('Edit');
        }

        if (Tools::isSubmit('wish_stock')) {
            $this->display = 'details';
            $this->toolbar_title = ('Sync Stock');
        }

        if (Tools::isSubmit('wish_price')) {
            $this->display = 'details';
            $this->toolbar_title = ('Sync Price');
        }
        if (is_string($product_name) && is_string($this->toolbar_title)) {
            $this->toolbar_title .= empty($this->toolbar_title) ? $product_name : ' - ' . $product_name;
        } elseif (isset($this->toolbar_title['0']) && $product_name) {
            $this->toolbar_title['0'] .= empty($this->toolbar_title['0']) ? $product_name : ' - ' . $product_name;
        }
    }

    /**
     * AdminController::initContent() override
     * @see AdminController::initContent()
     */
    public function initContent()
    {
        // Manage update fb data form
        if ($this->display == 'wishedit') {
            if (Tools::isSubmit('id_product') || Tools::isSubmit('id_product_attribute')) {
                $id_product = Tools::getValue('id_product');
                $id_product_attribute = Tools::getValue('id_product_attribute');
                $product_is_valid = false;
                $reference = false;
                $name = false;
                $lang_id = $this->context->language->id;
                $product = false;
                if ($id_product_attribute > 0) {
                    // try to load product attribute
                    $combination = new Combination($id_product_attribute);
                    if (Validate::isLoadedObject($combination)) {
                        $product_is_valid = true;
                        $id_product = $combination->id_product;
                        $reference = $combination->reference;
                        // get the full name for this combination
                        $query = new DbQuery();
                        $query->select(
                            'IFNULL(CONCAT(pl.`name`, \' : \', GROUP_CONCAT(agl.`name`, \' - \',
                             al.`name` SEPARATOR \', \')),pl.`name`) as name'
                        );
                        $query->from('product_attribute', 'a');
                        $query->join(
                            'INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.`id_product` = a.`id_product` 
                            AND pl.`id_lang` = ' . (int)$lang_id . ')
							LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac 
							ON (pac.`id_product_attribute` = a.`id_product_attribute`)
							LEFT JOIN ' . _DB_PREFIX_ . 'attribute atr ON (atr.`id_attribute` = pac.`id_attribute`)
							LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON (al.`id_attribute` = atr.`id_attribute` 
							AND al.`id_lang` = ' . (int)$lang_id . ')
							LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl 
							ON (agl.`id_attribute_group` = atr.`id_attribute_group` 
							AND agl.`id_lang` = ' . (int)$lang_id . ')'
                        );
                        $query->where('a.`id_product_attribute` = ' . $id_product_attribute);
                        $name = Db::getInstance()->getValue($query);
                        $product = new Product($id_product, false, $lang_id);
                    }
                } else {
                    // try to load a simple product
                    $product = new Product((int)$id_product, false, $lang_id);

                    if (is_int($product->id)) {
                        $product_is_valid = true;
                        $reference = $product->reference;
                        $name = $product->name;
                    }
                }

                if ($product_is_valid === true) {
                    // init form
                    $this->renderForm();
                    $helper = new HelperForm();
                    $this->initPageHeaderToolbar();
                    $this->setHelperDisplay($helper);
                    $helper->submit_action = $this->display;
                    $helper->id = null; // no display standard hidden field in the form
                    $helper->languages = $this->_languages;
                    $helper->default_form_language = $this->default_form_language;
                    $helper->allow_employee_form_lang = $this->allow_employee_form_lang;
                    $helper->show_cancel_button = true;
                    $helper->back_url = $this->context->link->getAdminLink('AdminCedWishProduct');

                    $productClass = new CedWishProduct();
                    $form_values = $productClass->getProductData((int)$product->id, $product, (array)$product);
                    if (isset($form_values['success']) && !$form_values['success']) {
                        $helper->fields_value['description'] = '';
                        $helper->fields_value['condition'] = '';
                        $helper->fields_value['video'] = '';
                        $helper->fields_value['gtin'] = '';
                        $helper->fields_value['tags'] = '';
                        $helper->fields_value['unit'] = '';
                        $helper->fields_value['status'] = '';
                        if (isset($form_values['msg']) && $form_values['msg']) {
                            $this->errors[] = $form_values['msg'];
                        }
                    } else {
                        $helper->fields_value = $form_values;
                    }

                    $helper->fields_value['is_post'] = 1;
                    $helper->fields_value['id_product'] = $id_product;
                    $helper->fields_value['id_product_attribute'] = $id_product_attribute;
                    $helper->fields_value['reference'] = $reference;
                    $helper->fields_value['name'] = $name;
                    $helper->fields_value['percent_increment'] = '';
                    $helper->fields_value['fix_increment'] = '';

                    $product_level_data = $productClass->getProductLevelData((int)$product->id, $id_product_attribute);
                    if (!empty($product_level_data)) {
                        foreach ($product_level_data as $field => $value) {
                            if ((isset($helper->fields_value[$field]) && !$helper->fields_value[$field])
                                || !isset($helper->fields_value[$field])
                            ) {
                                $helper->fields_value[$field] = $value;
                            }
                        }
                    }

                    $this->content .= $helper->generateForm($this->fields_form);

                    $this->context->smarty->assign(array(
                        'content' => $this->content,
                        'show_page_header_toolbar' => $this->show_page_header_toolbar,
                        'page_header_toolbar_title' => $this->page_header_toolbar_title,
                        'page_header_toolbar_btn' => $this->page_header_toolbar_btn
                    ));
                } else {
                    $this->errors[] = Tools::displayError('The specified product is not valid.');
                }
            }
        } else {
            parent::initContent();
        }
    }

    /**
     * AdminController::renderForm() override
     * @see AdminController::renderForm()
     */
    public function renderForm()
    {
        $id_product = (int)Tools::getValue('id_product');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');
        switch ($this->display) {
            case 'wishedit':
                $this->prepareAddWishDataForm($id_product, $id_product_attribute);
                break;
        }
        $this->initToolbar();
    }

    public function prepareAddWishDataForm($id_product, $id_product_attribute)
    {
        $profile = CedWishProfile::getProfileByProductId($id_product);
        $attributes = json_decode($profile['attribute_mapping'], true);
        $id_cedwish_product = Db::getInstance()->getValue(
            "SELECT id_cedwish_product FROM `" . _DB_PREFIX_ . "cedwish_product`
            WHERE id_product = '" . (int)$id_product . "' 
            AND id_product_attribute = '" . (int)$id_product_attribute . "'"
        );
        if ($id_cedwish_product) {
            $this->object = new CedWishProduct((int)$id_cedwish_product);
        } else {
            $this->object = new CedWishProduct();
            $this->object->id_product_attribute = $id_product_attribute;
            $this->object->id_product = $id_product;
        }

        $form_fields = array(
            'legend' => array(
                'title' => ('Wish Product Data'),
                'icon' => 'icon-edit'
            ),
            'submit' => array(
                'title' => ('Save')
            ),
            'submit' => array(
                'title' => ('Save')
            )
        );

        $form_input = array(
            array(
                'type' => 'hidden',
                'name' => 'is_post',
            ),
            array(
                'type' => 'hidden',
                'name' => 'id_product',
                'value' => $id_product
            ),
            array(
                'type' => 'hidden',
                'name' => 'id_product_attribute',
                'value' => $id_product_attribute
            ),
            array(
                'type' => 'text',
                'label' => ('Product reference'),
                'name' => 'reference',
            ),
            array(
                'type' => 'text',
                'label' => ('Percent Amount to Increase'),
                'name' => 'percent_increment',
            ),
            array(
                'type' => 'text',
                'label' => ('Amount to Increase'),
                'name' => 'fix_increment',
            ),
        );
        $skip_attributes_list = array(
            'quantity',
        );

        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $mapping) {
                if ($mapping) {
                }
                if (in_array($attribute, $skip_attributes_list)) {
                    continue;
                }
                if (!empty($attribute)) {
                    if ($attribute == 'description') {
                        $form_input[] = array(
                            'type' => 'textarea',
                            'label' => Tools::ucfirst($attribute),
                            'name' => $attribute,
                        );
                    } else {
                        $form_input[] = array(
                            'type' => 'text',
                            'label' => Tools::ucfirst($attribute),
                            'name' => $attribute,
                        );
                    }
                }
            }
        }
        $form_fields['input'] = $form_input;
        $this->fields_form[]['form'] = $form_fields;
    }

    /**
     * assign default action in toolbar_btn smarty var, if they are not set.
     * uses override to specifically add, modify or remove items
     *
     */
    public function initToolbar()
    {
        parent::initToolbar();
    }

    public function initPageHeaderToolbar()
    {
        if ($this->display == 'details' || $this->display == 'wishedit') {
            $id_product_attribute = Tools::getValue('id_product_attribute', 0);
            $id_product = Tools::getValue('id_product', 0);
            if ($id_product_attribute) {
                $this->page_header_toolbar_btn['back_to_list'] = array(
                    'href' => Context::getContext()->link->getAdminLink(
                        'AdminCedWishProduct'
                    ) . '&id_product=' . (int)$id_product . '&detailsproduct',
                    'desc' => 'Back to Product',
                    'icon' => 'process-icon-back'
                );
            } else {
                $this->page_header_toolbar_btn['back_to_list'] = array(
                    'href' => Context::getContext()->link->getAdminLink('AdminCedWishProduct'),
                    'desc' => 'Back to List',
                    'icon' => 'process-icon-back'
                );
            }
        } elseif (empty($this->display)) {
            $product_queue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                "SELECT COUNT(*) FROM `"._DB_PREFIX_."cedwish_queue` WHERE (queue_type='product') 
                OR (queue_type='upload')"
            );
            $this->page_header_toolbar_btn['upload_all'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishBulk') .
                    '&cron_bulk_upload=1',
                'desc' => sprintf($this->module->l('Upload All ( %s )'), $product_queue),
                'icon' => 'process-icon-upload'
            );

            $product_queue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                "SELECT COUNT(*) FROM `"._DB_PREFIX_."cedwish_queue` WHERE queue_type='update'"
            );
            $this->page_header_toolbar_btn['update_all'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishBulk') .
                    '&cron_bulk_update=1',
                'desc' => sprintf($this->module->l('Update All ( %s )'), $product_queue),
                'icon' => 'process-icon-upload'
            );

            $stock_queue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                "SELECT COUNT(*) FROM `"._DB_PREFIX_."cedwish_queue` WHERE queue_type='stock'"
            );
            $this->page_header_toolbar_btn['sync_inventory'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishBulk') .
                    '&cron_bulk_sync_inventory=1',
                'desc' => sprintf($this->module->l('Sync Stock( %s )'), $stock_queue),
                'icon' => 'process-icon-refresh'
            );
        }

        parent::initPageHeaderToolbar();
    }

    public function renderProfileList($profile_id, $row_data)
    {
        if (isset($this->profiles[$profile_id]) && !empty($row_data)) {
            return $this->profiles[$profile_id];
        }
        return $profile_id;
    }

    public function renderProductErrors($errors, $row_data)
    {
        $has_error = false;
        if ($errors) {
            $errors = @json_decode($errors, true);
            if (!empty($errors)) {
                $has_error = true;
            }
        }
        $uploaded = false;
        $marketplace_id = '';
        if (isset($row_data['marketplace_id']) && $row_data['marketplace_id']) {
            $uploaded = true;
            $marketplace_id = $row_data['marketplace_id'];
        }
        $variation_marketplace_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            "SELECT `marketplace_id` FROM `"._DB_PREFIX_."cedwish_product` WHERE 
             `id_product` = '".(int)$row_data['id_product']."' 
            AND `id_product_attribute` = '".(int)$row_data['id_product_attribute']."'"
        );
        $this->context->smarty->assign(
            array(
                'has_error' => $has_error,
                'uploaded' => $uploaded,
                'marketplace_id' => $variation_marketplace_id ? $variation_marketplace_id : $marketplace_id,
                'product_grid_token' => Tools::getAdminTokenLite('AdminCedWishProduct'),
                'id_product' => $row_data['id_product'],
                'id_product_attribute' => isset($row_data['id_product_attribute'])
                    ? $row_data['id_product_attribute'] : 0
            )
        );
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/errors.tpl'
        );
    }

    /**
     * Display upload action link
     * @param string $token the token to add to the link
     * @param int $id the identifier to add to the link
     * @return string
     */
    public function displayUploadLink($token = null, $id = 0)
    {
        if (!array_key_exists('Upload', self::$cache_lang)) {
            self::$cache_lang['Upload'] = ('Upload');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&upload_product_id=' . $id .
                '&upload&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Upload'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/list_action_upload.tpl'
        );
    }

    /**
     * Display upload action link
     * @param string $token the token to add to the link
     * @param int $id the identifier to add to the link
     * @return string
     */
    public function displayWishEditLink($token = null, $id = 0)
    {
        if (!array_key_exists('Wish Edit', self::$cache_lang)) {
            self::$cache_lang['Wish Edit'] = ('Wish Edit');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&' . $this->identifier . '=' . $id .
                '&wishedit&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Wish Edit'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/list_action_edit.tpl'
        );
    }

    /**
     * Display removestock action link
     * @param string $token the token to add to the link
     * @param int $id the identifier to add to the link
     * @return string
     */
    public function displayWishStockLink($token = null, $id = 0)
    {
        if (!array_key_exists('Stock', self::$cache_lang)) {
            self::$cache_lang['Stock'] = ('Sync stock');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&stock_product_id=' . $id .
                '&stock&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Stock'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/list_action_stock.tpl'
        );
    }

    /**
     * Display FBPriceLink action link
     * @param string $token the token to add to the link
     * @param int $id the identifier to add to the link
     * @return string
     */
    public function displayWishPriceLink($token = null, $id = 0)
    {
        if (!array_key_exists('Price', self::$cache_lang)) {
            self::$cache_lang['Price'] = ('Sync Price');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&price_product_id=' . $id .
                '&price&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Price'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/list_action_price.tpl'
        );
    }

    public function initProcess()
    {
        if (Tools::getIsset('detailsproduct')) {
            $this->list_id = 'product_attribute';
            if (Tools::getIsset('submitReset' . $this->list_id)) {
                $this->processResetFilters();
            }
        } else {
            $this->list_id = 'product';
        }
        parent::initProcess();
        if ((Tools::getIsset('enabled' . $this->list_id) || Tools::getIsset('enabled'))
            && Tools::getValue($this->identifier)
        ) {
            $this->action = 'enabled';
        }
    }

    public function processBulkBrandTagging()
    {
        try {
            $product_id_array = array();
            if (is_array($this->boxes) && !empty($this->boxes)) {
                $product_id_array = $this->boxes;
            }
            if (!empty($product_id_array)) {
                CedWishQueue::addQueue(
                    'brand_tagging',
                    $product_id_array,
                    6
                );
                $link = new LinkCore();
                $controller_link = $link->getAdminLink('AdminCedWishBulk') .
                    '&brand_tagging=redirected&redirected=1';
                Tools::redirectAdmin($controller_link);
            } else {
                $this->warnings[] = "Please select product(s)";
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    public function processEnabled()
    {
        if ($object = $this->loadObject()) {
            if ($object->toggleFeedEnabled()) {
                PrestaShopLogger::addLog(
                    sprintf($this->l(
                        '%s Mode switched to %s',
                        'AdminTab',
                        false,
                        false
                    ), $this->className, $object->enabled ? 'enable' : 'disable'),
                    1,
                    null,
                    $this->className,
                    (int)$object->id,
                    true,
                    (int)$this->context->employee->id
                );

                $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
                $page = (int)Tools::getValue('page');
                $page = $page > 1 ? '&submitFilter' . $this->table . '=' . (int)$page : '';
                if (Tools::getIsset('id_product_attribute')
                    && Tools::getIsset('id_product')
                    && Tools::getValue('id_product_attribute')
                    && Tools::getValue('id_product')
                    && Tools::getIsset('enabledproduct_attribute')
                ) {
                    $this->redirect_after
                        .= '&id_product=' . (int)Tools::getValue('id_product') . '&detailsproduct';
                }
                $this->redirect_after .= $page;
            } else {
                $this->errors[] = Tools::displayError('An error occurred while updating the status.');
            }
        } else {
            $this->errors[] = Tools::displayError(
                'An error occurred while updating the status for an object.'
            ) . Tools::displayError('(cannot load object)');
        }
    }

    /**
     * Load class object using identifier in $_GET (if possible)
     * otherwise return an empty object, or die.
     *
     * @param bool $opt Return an empty object if load fail
     *
     * @return ObjectModel|false
     */
    protected function loadObject($opt = false)
    {
        $product_id = (int)Tools::getValue($this->identifier, 0);
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');
        if ($product_id && Validate::isUnsignedId($product_id)) {
            if (!$this->object) {
                $id_cedwish_product = Db::getInstance()->getValue(
                    "SELECT `id_cedwish_product` FROM `" . _DB_PREFIX_ . "cedwish_product`
                    WHERE id_product = '" . (int)$product_id . "' 
                    AND id_product_attribute = '" . (int)$id_product_attribute . "'"
                );

                if (!$id_cedwish_product) {
                    $this->object = new CedWishProduct();
                    $this->object->id_product_attribute = $id_product_attribute;
                    $this->object->id_product = $product_id;
                } else {
                    $this->object = new CedWishProduct((int)$id_cedwish_product);
                }
            }
            return $this->object;
        } elseif (!$opt) {
            if (!$this->object) {
                $this->object = new CedWishProduct();
            }
            return $this->object;
        } else {
            $this->errors[] = $this->l(
                'The object cannot be loaded (the identifier is missing or invalid)'
            );
            return false;
        }
    }

    public function processBulkUpload()
    {
        if ($this->boxes && !empty($this->boxes)) {
            $productHelper = new CedWishProduct();
            $chunk_size = (int)Configuration::get(
                'CED_WISH_CRON_CHUNK_SIZE',
                null,
                null,
                null,
                10
            );
            if (!$chunk_size) {
                $chunk_size  = 10;
            }
            if (count($this->boxes) > $chunk_size) {
                try {
                    $products = array_chunk($this->boxes, $chunk_size);

                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'product',
                            $product,
                            1
                        );
                    }
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedWishBulk') .
                        '&cron_bulk_upload=redirected&redirected=1';
                    Tools::redirectAdmin($controller_link);
                } catch (PrestaShopDatabaseException $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (count($this->boxes)) {
                $response = $productHelper->upload($this->boxes);
                if (isset($response['success']) && $response['success']) {
                    $this->confirmations[] = $response['msg'];
                } elseif (isset($response['msg'])) {
                    $this->errors[] = $response['msg'];
                } else {
                    $this->errors[] = 'Something went wrong, please check log.';
                }
            } else {
                $this->errors[] = 'No product selected';
            }
        } else {
            $this->errors[] = 'Please Select Product(s)';
        }
    }

    public function processBulkUpdate()
    {
        if ($this->boxes && !empty($this->boxes)) {
            $productHelper = new CedWishProduct();
            $chunk_size = (int)Configuration::get(
                'CED_WISH_CRON_CHUNK_SIZE',
                null,
                null,
                null,
                10
            );
            if (!$chunk_size) {
                $chunk_size  = 10;
            }
            if (count($this->boxes) > $chunk_size) {
                try {
                    $products = array_chunk($this->boxes, $chunk_size);

                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'update',
                            $product,
                            1
                        );
                    }
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedWishBulk') .
                        '&cron_bulk_upload=redirected&redirected=1';
                    Tools::redirectAdmin($controller_link);
                } catch (PrestaShopDatabaseException $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (count($this->boxes)) {
                $response = $productHelper->massUpdate($this->boxes);
                if (isset($response['success']) && $response['success']) {
                    $this->confirmations[] = $response['success'];
                } elseif (isset($response['errors'])) {
                    $this->errors[] = implode(", ", $response['errors']);
                } else {
                    $this->errors[] = 'Something went wrong, please check log.';
                }
            } else {
                $this->errors[] = 'No product selected';
            }
        } else {
            $this->errors[] = 'Please Select Product(s)';
        }
    }

    public function processBulkDisable()
    {
        if ($this->boxes && !empty($this->boxes)) {
            $productHelper = new CedWishProduct();
            $chunk_size = (int)Configuration::get('CED_WISH_CRON_CHUNK_SIZE');
            if (count($this->boxes) > $chunk_size) {
                try {
                    $products = array_chunk($this->boxes, $chunk_size);
                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'stock',
                            $product,
                            2
                        );
                    }
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedWishBulk') .
                        '&cron_bulk_sync_inventory=redirected&redirected=1';
                    Tools::redirectAdmin($controller_link);
                } catch (PrestaShopDatabaseException $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (count($this->boxes)) {
                $response = $productHelper->updateProductStatus($this->boxes, "DISABLED");
                if (isset($response['success']) && $response['success']) {
                    $this->confirmations[] = implode(", ", $response['success']);
                }

                if (isset($response['error']) && $response['error']) {
                    $this->errors[] = implode(", ", $response['error']);
                }
            } else {
                $this->errors[] = 'No product selected';
            }
        } else {
            $this->errors[] = $this->l('Please Select Product(s)');
        }
    }

    public function processBulkEnable()
    {
        if ($this->boxes && !empty($this->boxes)) {
            $productHelper = new CedWishProduct();
            $chunk_size = (int)Configuration::get('CED_WISH_CRON_CHUNK_SIZE');
            if (count($this->boxes) > $chunk_size) {
                try {
                    $products = array_chunk($this->boxes, $chunk_size);
                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'stock',
                            $product,
                            2
                        );
                    }
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedWishBulk') .
                        '&cron_bulk_sync_inventory=redirected&redirected=1';
                    Tools::redirectAdmin($controller_link);
                } catch (PrestaShopDatabaseException $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (count($this->boxes)) {
                $response = $productHelper->updateProductStatus($this->boxes, "ENABLED");
                if (isset($response['success']) && $response['success']) {
                    $this->confirmations[] = implode(", ", $response['success']);
                }

                if (isset($response['error']) && $response['error']) {
                    $this->errors[] = implode(", ", $response['error']);
                }
            } else {
                $this->errors[] = 'No product selected';
            }
        } else {
            $this->errors[] = $this->l('Please Select Product(s)');
        }
    }

    public function processBulkStock()
    {
        if ($this->boxes && !empty($this->boxes)) {
            $productHelper = new CedWishProduct();
            $chunk_size = (int)Configuration::get('CED_WISH_CRON_CHUNK_SIZE');
            if (count($this->boxes) > $chunk_size) {
                try {
                    $products = array_chunk($this->boxes, $chunk_size);
                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'stock',
                            $product,
                            2
                        );
                    }
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedWishBulk') .
                        '&cron_bulk_sync_inventory=redirected&redirected=1';
                    Tools::redirectAdmin($controller_link);
                } catch (PrestaShopDatabaseException $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (count($this->boxes)) {
                $response = $productHelper->updateProductStockAndPrice($this->boxes, 'stock');
                if (isset($response['success']) && $response['success']) {
                    $this->confirmations[] = $response['success'];
                } elseif (isset($response['error'])) {
                    $this->errors[] = implode(",", $response['error']);
                } else {
                    $this->errors[] = 'Something went wrong, please check log.';
                }
            } else {
                $this->errors[] = 'No product selected';
            }
        } else {
            $this->errors[] = $this->l('Please Select Product(s)');
        }
    }

    public function processBulkPrice()
    {
        if ($this->boxes && !empty($this->boxes)) {
            $productHelper = new CedWishProduct();
            $chunk_size = (int)Configuration::get('CED_WISH_CRON_CHUNK_SIZE');
            if (count($this->boxes) > $chunk_size) {
                try {
                    $products = array_chunk($this->boxes, $chunk_size);
                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'price',
                            $product,
                            3
                        );
                    }
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedWishBulk') .
                        '&cron_bulk_sync_inventory=redirected&redirected=1';
                    Tools::redirectAdmin($controller_link);
                } catch (PrestaShopDatabaseException $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (count($this->boxes)) {
                $response = $productHelper->updateProductStockAndPrice($this->boxes, 'price');
                if (isset($response['success']) && $response['success']) {
                    $this->confirmations[] = $response['success'];
                } elseif (isset($response['error'])) {
                    $this->errors[] = implode(",", $response['error']);
                } else {
                    $this->errors[] = 'Something went wrong, please check log.';
                }
            } else {
                $this->errors[] = 'No product selected';
            }
        } else {
            $this->errors[] = $this->l('Please Select Product(s)');
        }
    }

    public function ajaxProcessGetProductError()
    {
        $id_product = Tools::getValue('product_id', false);
        $id_product_attribute = Tools::getValue('id_product_attribute', false);
        $response = array('success' => false, 'message' => 'No Errors Found.');
        if ($id_product) {
            $errors = Db::getInstance()->getValue(
                "SELECT `error` FROM `" . _DB_PREFIX_ . "cedwish_product` 
                WHERE `id_product` = '" . (int)$id_product . "' AND `id_product_attribute` = '0'"
            );

            if (empty($errors)) {
                $errors = Db::getInstance()->getValue(
                    "SELECT `error` FROM `" . _DB_PREFIX_ . "cedwish_product` 
                    WHERE `id_product` = '" . (int)$id_product . "' 
                    AND `id_product_attribute` = '".(int)$id_product_attribute."'"
                );
            }


            if ($errors) {
                $errors = Tools::getDescriptionClean($errors);
                $errors = json_decode($errors, true);
                if (!empty($errors)) {
                    $this->context->smarty->assign(array(
                        'error_rows' => $errors,
                    ));
                    $response = array(
                        'success' => true,
                        'message' => $this->context->smarty->fetch(
                            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/error_view.tpl'
                        )
                    );
                } else {
                    $response = array('success' => true, 'message' => $errors);
                }
            } else {
                $response = array('success' => false, 'message' => 'No Update is status, check again.');
            }
        }
        die(json_encode($response));
    }

    public function ajaxProcessGetProductData()
    {
        $marketplace_id = Tools::getValue('marketplace_id', false);
        $response = array('success' => false, 'message' => 'No Item Found.');
        if ($marketplace_id) {
            $product = new CedWishProduct();
            $response = $product->getWishInfoByProductId($marketplace_id);
            $regions = CedWishHelper::getShippingRegions();
            $region_lists = array();
            foreach ($regions as $country_iso => $region) {
                foreach ($region as $reg) {
                    $region_lists[$country_iso][$reg['0']] = $reg;
                }
            }

            if (isset($response['code']) && ($response['code']==0)) {
                if (!empty($response['data'])) {
                    $this->context->smarty->assign(array(
                        'data' => $response['data'],
                        'countries' => CedWishHelper::getShippableCountries(),
                        'regions' => $region_lists,
                    ));
                    $response = array(
                        'success' => true,
                        'message' => $this->context->smarty->fetch(
                            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/list/product_view.tpl'
                        )
                    );
                } else {
                    $response = array('success' => true, 'message' => $response['message']);
                }
            } elseif (isset($response['message']) && $response['message']) {
                $response = array('success' => false, 'message' => $response['message']);
            } else {
                $response = array('success' => false, 'message' => 'No Update is status, check again.');
            }
        }
        die(json_encode($response));
    }

    public function processBulkDelete()
    {
        if (!empty($this->boxes)) {
            $product = new CedWishProduct();
            $result = $product->deleteProduct($this->boxes);
            $this->confirmations[] = implode(', ', $result);
        } else {
            $this->errors[] = $this->l('Please Select Item to delete');
        }
    }

    public function processBulkSync()
    {
        if (!empty($this->boxes)) {
            $product = new CedWishProduct();
            $result = $product->syncProduct($this->boxes);
            if (isset($result['success']) && !empty($result['success'])) {
                $this->confirmations[] = implode(", ", $result['success'])
                    .$this->l('Product sync successfully');
            }
            if (isset($result['error']) && !empty($result['error'])) {
                $this->errors[] = implode(", ", $result['error']);
            }
        } else {
            $this->errors[] = $this->l('Please Select Item to sync');
        }
    }

    public function renderKpis()
    {
        $kpis = array();
        $time = time();
        $helper = new HelperKpi();
        $helper->id = 'box-total-product';
        $helper->icon = 'icon-list';
        $helper->color = 'color4';
        $helper->title = $this->l('Product(s)');
        $helper->subtitle = $this->l('Total Item(s)');
        if (ConfigurationKPI::get('WISH_TOTAL_ITEMS') !== false) {
            $helper->value = ConfigurationKPI::get('WISH_TOTAL_ITEMS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminCedWishProduct')
            . '&ajax=1&action=getKpi&kpi=wish_product_total';
        $helper->refresh = (bool) (ConfigurationKPI::get('WISH_TOTAL_ITEMS_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-total-uploaded';
        $helper->icon = 'icon-upload';
        $helper->color = 'color1';
        $helper->title = $this->l('Uploaded');
        $helper->subtitle = $this->l('Item(s) Uploaded On Wish');
        if (ConfigurationKPI::get('WISH_TOTAL_UPLOADED_ITEMS') !== false) {
            $helper->value = ConfigurationKPI::get('WISH_TOTAL_UPLOADED_ITEMS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminCedWishProduct')
            . '&ajax=1&action=getKpi&kpi=wish_uploaded_product_total';
        $helper->refresh = (bool) (ConfigurationKPI::get('WISH_TOTAL_UPLOADED_ITEMS_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-total-error';
        $helper->icon = 'icon-remove';
        $helper->color = 'color2';
        $helper->title = $this->l('Error');
        $helper->subtitle = $this->l('Item(s) Having Error');
        if (ConfigurationKPI::get('WISH_TOTAL_ERROR_ITEMS') !== false) {
            $helper->value = ConfigurationKPI::get('WISH_TOTAL_ERROR_ITEMS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminCedWishProduct')
            . '&ajax=1&action=getKpi&kpi=wish_error_product_total';
        $helper->refresh = (bool) (ConfigurationKPI::get('WISH_TOTAL_ERROR_ITEMS_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-net-profit-visitor';
        $helper->icon = 'icon-toggle-off';
        $helper->color = 'color4';
        $helper->title = $this->l('Disabled Item(s)');
        $helper->subtitle = $this->l('Item(s) Are Disabled to Sync');
        if (ConfigurationKPI::get('WISH_TOTAL_DISABLED_ITEMS') !== false) {
            $helper->value = ConfigurationKPI::get('WISH_TOTAL_DISABLED_ITEMS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminCedWishProduct')
            . '&ajax=1&action=getKpi&kpi=wish_disable_product_total';
        $helper->refresh = (bool) (ConfigurationKPI::get('WISH_TOTAL_DISABLED_ITEMS_EXPIRE') < $time);
        $kpis[] = $helper->generate();
        $helper = new HelperKpiRow();
        $helper->kpis = $kpis;
        return $helper->generate();
    }

    public function displayAjaxGetKpi()
    {
        $tooltip = null;
        switch (Tools::getValue('kpi')) {
            case 'wish_product_total':
                $value = Db::getInstance()->getValue(
                    "SELECT count(DISTINCT (`id_product`)) FROM `" . _DB_PREFIX_ . "cedwish_profile_product`"
                );
                ConfigurationKPI::updateValue('WISH_TOTAL_ITEMS', $value);
                ConfigurationKPI::updateValue(
                    'WISH_TOTAL_ITEMS_EXPIRE',
                    strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')))
                );

                break;

            case 'wish_uploaded_product_total':
                $value = Db::getInstance()->getValue(
                    "SELECT count(DISTINCT cp.id_product) FROM `" . _DB_PREFIX_
                    . "cedwish_product` cp JOIN `" . _DB_PREFIX_ . "cedwish_profile_product` cpp 
                    ON (cp.id_product = cpp.id_product) 
                    WHERE cp.marketplace_id IS NOT NULL AND cp.id_product_attribute='0'"
                );
                ConfigurationKPI::updateValue('WISH_TOTAL_UPLOADED_ITEMS', $value);
                ConfigurationKPI::updateValue('WISH_TOTAL_UPLOADED_ITEMS_EXPIRE', strtotime('+1 hour'));

                break;

            case 'wish_error_product_total':
                $value =  Db::getInstance()->getValue(
                    "SELECT count(DISTINCT id_product) FROM `" . _DB_PREFIX_
                    . "cedwish_product` WHERE (`error` != '[]') AND (`error` IS NOT NULL)"
                );
                ConfigurationKPI::updateValue('WISH_TOTAL_ERROR_ITEMS', $value);
                ConfigurationKPI::updateValue('WISH_TOTAL_ERROR_ITEMS_EXPIRE', strtotime('+1 hour'));

                break;

            case 'wish_disable_product_total':
                $value = Db::getInstance()->getValue(
                    "SELECT count(DISTINCT id_product) FROM `" . _DB_PREFIX_
                    . "cedwish_product` WHERE `enabled` = 0 OR `enabled` IS NULL"
                );
                ConfigurationKPI::updateValue('WISH_TOTAL_DISABLED_ITEMS', $value);
                ConfigurationKPI::updateValue('WISH_TOTAL_DISABLED_ITEMS_EXPIRE', strtotime('+1 hour'));

                break;

            default:
                $value = false;
        }
        if ($value !== false) {
            $array = ['value' => $value, 'tooltip' => $tooltip];
            die(json_encode($array));
        }
        die(json_encode(['has_errors' => true]));
    }
}
