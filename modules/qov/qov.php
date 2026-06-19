<?php

/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-9999 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */
class qov extends Module
{
    public function __construct()
    {
        $this->name = 'qov';
        $this->tab = 'front_office_features';
        $this->author = 'MyPresta.eu';
        $this->version = '2.0.0';
        $this->module_key = 'd16dfcb44d033d05e3bab40156ee80a1';
        $this->secure_key = Tools::hash($this->name);
        parent::__construct();
        $this->bootstrap = true;
        $this->displayName = $this->l('Quick Order View');
        $this->description = $this->l('Display list of ordered products quickly with Quick Order View module. Module increase usability of back office to quickly preview orders in "orders" section.');
        $this->mypresta_link = 'https://mypresta.eu/modules/administration-tools/quick-order-preview.html';
        $this->checkforupdates(0,0);
    }

    public function inconsistency($return = 0)
    {
        return;
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 16 //
        // ---------- //
        // ---------- //
        $this->mkey = "nlc";
        if (@file_exists('../modules/' . $this->name . '/key.php')) {
            @require_once('../modules/' . $this->name . '/key.php');
        } else {
            if (@file_exists(dirname(__FILE__) . $this->name . '/key.php')) {
                @require_once(dirname(__FILE__) . $this->name . '/key.php');
            } else {
                if (@file_exists('modules/' . $this->name . '/key.php')) {
                    @require_once('modules/' . $this->name . '/key.php');
                }
            }
        }
        if ($form == 1) {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_module_block_settings">
                         ' . ($this->psversion() == 5 ? '<legend style="">' . $this->l('MyPresta updates') . '</legend>' : '') . '
                        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                            <label>' . $this->l('Check updates') . '</label>
                            <div class="margin-form">' . (Tools::isSubmit('submit_settings_updates_now') ? ($this->inconsistency(0) ? '' : '') . $this->checkforupdates(1) : '') . '
                                <button style="margin: 0px; top: -3px; position: relative;" type="submit" name="submit_settings_updates_now" class="button btn btn-default" />
                                <i class="process-icon-update"></i>
                                ' . $this->l('Check now') . '
                                </button>
                            </div>
                            <label>' . $this->l('Updates notifications') . '</label>
                            <div class="margin-form">
                                <select name="mypresta_updates">
                                    <option value="-">' . $this->l('-- select --') . '</option>
                                    <option value="1" ' . ((int)(Configuration::get('mypresta_updates') == 1) ? 'selected="selected"' : '') . '>' . $this->l('Enable') . '</option>
                                    <option value="0" ' . ((int)(Configuration::get('mypresta_updates') == 0) ? 'selected="selected"' : '') . '>' . $this->l('Disable') . '</option>
                                </select>
                                <p class="clear">' . $this->l('Turn this option on if you want to check MyPresta.eu for module updates automatically. This option will display notification about new versions of this addon.') . '</p>
                            </div>
                            <label>' . $this->l('Module page') . '</label>
                            <div class="margin-form">
                                <a style="font-size:14px;" href="' . $this->mypresta_link . '" target="_blank">' . $this->displayName . '</a>
                                <p class="clear">' . $this->l('This is direct link to official addon page, where you can read about changes in the module (changelog)') . '</p>
                            </div>
                            <div class="panel-footer">
                                <button type="submit" name="submit_settings_updates"class="button btn btn-default pull-right" />
                                <i class="process-icon-save"></i>
                                ' . $this->l('Save') . '
                                </button>
                            </div>
                        </form>
                    </fieldset>
                    <style>
                    #fieldset_myprestaupdates {
                        display:block;clear:both;
                        float:inherit!important;
                    }
                    </style>
                </div>
            </div>
            </div>';
        } else {
            if (defined('_PS_ADMIN_DIR_')) {
                if (Tools::isSubmit('submit_settings_updates')) {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') != false) {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = qovUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (qovUpdate::version($this->version) < qovUpdate::version(Configuration::get('updatev_' . $this->name)) && Tools::getValue('ajax','false') == 'false') {
                        $this->context->controller->warnings[] = '<strong>' . $this->displayName . '</strong>: ' . $this->l('New version available, check http://MyPresta.eu for more informations') . ' <a href="' . $this->mypresta_link . '">' . $this->l('More details in changelog') . '</a>';
                        $this->warning                         = $this->context->controller->warnings[0];
                    }
                } else {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = qovUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                }
                if ($display_msg == 1) {
                    if (qovUpdate::version($this->version) < qovUpdate::version(qovUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version))) {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    } else {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }

    public function install()
    {
        if (!parent::install() OR !$this->registerHook('backofficefooter') OR !$this->InDelMenu('install', 'AdminPreviewOrder', $this->l('PreviewOrder'), 'AdminParentModules', false))
        {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() OR !$this->inDelMenu('uninstall', 'AdminPreviewOrder'))
        {
            return false;
        }
        return true;
    }

    private function InDelMenu($what, $controller, $name = null, $parent = null, $active = true)
    {
        if ($what == 'install')
        {
            $tab = new Tab();
            $tab->class_name = $controller;
            $tab->id_parent = (is_int($parent) ? $parent : Tab::getIdFromClassName($parent));
            $tab->module = $this->name;
            $tab->active = $active;
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang)
            {
                $tab->name[$lang['id_lang']] = $name;
            }
            $tab->save();
            return true;
        }
        elseif ($what == 'uninstall')
        {
            $tab = new Tab(Tab::getIdFromClassName($controller));
            $tab->delete();
            return true;
        }
    }

    public function getContent()
    {
        return $this->_postProcess() . $this->displayForm() . $this->checkforupdates(0, 1);
    }

    public function psversion()
    {
        $version = _PS_VERSION_;
        $exp = explode(".", $version);
        return $exp[1];
    }

    protected function getProducts($order)
    {
        $image_size = Configuration::get('QOV_PRODUCTS_IMG');
        if ($image_size == false || $image_size == "") {
            $image_size = 45;
        }

        $products = $order->getProducts();

        foreach ($products as &$product)
        {
            if ($product['image'] != null)
            {
                $name = 'product_mini_' . $image_size . '_' . (int)$product['product_id'] . (isset($product['product_attribute_id']) ? '_' . (int)$product['product_attribute_id'] : '') . '.jpg';
                // generate image cache, only for back office
                $product['image_tag'] = ImageManager::thumbnail(_PS_IMG_DIR_ . 'p/' . $product['image']->getExistingImgPath() . '.jpg', $name, $image_size, 'jpg');
                if (file_exists(_PS_TMP_IMG_DIR_ . $name))
                {
                    $product['image_size'] = getimagesize(_PS_TMP_IMG_DIR_ . $name);
                }
                else
                {
                    $product['image_size'] = false;
                }
            }
        }
        ksort($products);
        return $products;
    }


    public function getOrderDetails($idorder)
    {
        $contents = '';
        $order = new Order((int)$idorder);
        $customer = new Customer($order->id_customer);

        // ADDRESSESS
        $addressInvoice = new Address($order->id_address_invoice, $this->context->language->id);

        if ($order->id_address_invoice == $order->id_address_delivery)
        {
            $addressDelivery = $addressInvoice;
        }
        else
        {
            $addressDelivery = new Address($order->id_address_delivery, $this->context->language->id);
        }

        //PRODUCTS
        $products = $this->getProducts($order);

        $total_weight = 0;
        foreach ($products as $product => $product_detail)
        {
            $total_weight = $total_weight + ($product_detail['product_weight'] * $product_detail['product_quantity']);
        }

        //PRODUCTS CATEGORIES
        $category = array();
        $product_categories = array();
        $product_main_category = array();
        if (Configuration::get('QOV_PRODUCTS_CAT') == 1 || Configuration::get('QOV_PRODUCTS_CATS') == 1)
        {
            foreach ($products AS $product => $product_value)
            {
                $pr = new Product($product_value['product_id'], true, $this->context->language->id);
                if (!isset($category[$pr->id_category_default]))
                {
                    $category[$pr->id_category_default] = new Category($pr->id_category_default, $this->context->language->id);
                }
                if (!isset($product_main_category[$product_value['product_id']]))
                {
                    $product_main_category[$product_value['product_id']] = $category[$pr->id_category_default]->name;
                }

                if (!isset($product_categories[$product_value['product_id']]))
                {
                    $product_categories[$product_value['product_id']] = array();
                    foreach (Product::getProductCategoriesFull($product_value['product_id'], $this->context->language->id) AS $prc => $prv)
                    {
                        $product_categories[$product_value['product_id']][] = $prv['name'];
                    }
                }
            }

            foreach ($product_categories AS $k => $v)
            {
                $product_categories[$k] = implode(', ', $product_categories[$k]);
            }
        }


        //PRODUCTS FEATURES
        $feature = array();
        $feature_value = array();
        $product_features = array();
        if (Configuration::get('QOV_PRODUCTS_FEAT') == 1)
        {
            $features_to_show = explode(',', Configuration::get('QOV_FARRAY'));
            foreach ($products AS $product => $product_value)
            {
                foreach (Product::getFeaturesStatic($product_value['product_id']) AS $k => $v)
                {
                    if (!isset($feature[$v['id_feature']]))
                    {
                        $feature[$v['id_feature']] = new Feature($v['id_feature'], $this->context->language->id);
                    }
                    if (!isset($feature_value[$v['id_feature_value']]))
                    {
                        $feature_value[$v['id_feature_value']] = new FeatureValue($v['id_feature_value'], $this->context->language->id);
                    }
                    if (!isset($product_features[$product_value['product_id']]))
                    {
                        $product_features[$product_value['product_id']] = '';
                    }

                    foreach ($features_to_show AS $f)
                    {
                        if ($f == $v['id_feature'])
                        {
                            $product_features[$product_value['product_id']] = $product_features[$product_value['product_id']] . '<strong>' . $feature[$v['id_feature']]->name . '</strong>: ' . $feature_value[$v['id_feature_value']]->value . ', ';
                        }
                    }
                }
            }
        }

        //SHIPPING
        $id_order_carrier = Db::getInstance()->getValue('SELECT `id_order_carrier` FROM `' . _DB_PREFIX_ . 'order_carrier` WHERE `id_order` = ' . (int)$idorder . '');
        $order_carrier = new OrderCarrier($id_order_carrier);
        $carrier = new Carrier($order_carrier->id_carrier);
        if ($order->shipping_number != '')
        {
            $tracking = str_replace('@', $order->shipping_number, $carrier->url);
        }
        else
        {
            $tracking = false;
        }

        //ORDER STATES HISTORY
        $history = $order->getHistory($this->context->language->id);
        foreach ($history as &$order_state)
        {
            $order_state['text-color'] = Tools::getBrightness($order_state['color']) < 128 ? 'white' : 'black';
        }

        //ASSIGN VARIABLES TO SMARTY
        if (version_compare(Tools::substr(_PS_VERSION_, 0, 3), '1.7.6', '>=')) {
            $ps_version_176 = false;
        } else {
            $ps_version_176 = true;
        }

        $this->context->smarty->assign(array(
            'customerStats' => $customer->getStats(),
            'addresses' => array(
                'delivery' => $addressDelivery,
                'invoice' => $addressInvoice
            ),
            'paramsAddressesDelivery' => array(
                'delivery' => $addressDelivery
            ),
            'paramsAddressesInvoce' => array(
                'delivery' => $addressInvoice
            ),
            'order' => $order,
            'currency' => new Currency($order->id_currency),
            'history' => $history,
            'tracking' => $tracking,
            'link' => $this->context->link,
            'carrier' => $carrier,
            'order_carrier' => $order_carrier,
            'products' => $products,
            'product_features' => $product_features,
            'product_categories' => $product_categories,
            'product_main_category' => $product_main_category,
            'messages' => array_reverse(CustomerThread::getCustomerMessages($order->id_customer, null, $order->id)),
            'total_weight' => $total_weight,
            'customer' => $customer,
            'ps_version_176' => $ps_version_176,
            'states' => OrderState::getOrderStates((int)$this->context->language->id)
        ));


        $contents .= $this->context->smarty->fetch('../modules/qov/views/templates/admin/address.tpl');
        $contents .= $this->context->smarty->fetch('../modules/qov/views/templates/admin/products.tpl');
        return $contents;
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit'))
        {
            Configuration::updateValue('QOV_CUSTOMNOTE', Tools::getValue('QOV_CUSTOMNOTE'));
            Configuration::updateValue('QOV_ROLLIN', Tools::getValue('QOV_ROLLIN'));
            Configuration::updateValue('QOV_CUSTOMER', Tools::getValue('QOV_CUSTOMER'));
            Configuration::updateValue('QOV_CUSTOMERM', Tools::getValue('QOV_CUSTOMERM'));
            Configuration::updateValue('QOV_SHIPPING', Tools::getValue('QOV_SHIPPING'));
            Configuration::updateValue('QOV_SHIPPING_INVOICE', Tools::getValue('QOV_SHIPPING_INVOICE'));
            Configuration::updateValue('QOV_SHIPPING_ADDRESS', Tools::getValue('QOV_SHIPPING_ADDRESS'));
            Configuration::updateValue('QOV_SHIPPING_ID', Tools::getValue('QOV_SHIPPING_ID'));
            Configuration::updateValue('QOV_SHIPPING_REF', Tools::getValue('QOV_SHIPPING_REF'));
            Configuration::updateValue('QOV_SHIPPING_EDIT', Tools::getValue('QOV_SHIPPING_EDIT'));
            Configuration::updateValue('QOV_HISTORY', Tools::getValue('QOV_HISTORY'));
            Configuration::updateValue('QOV_PRODUCTS', Tools::getValue('QOV_PRODUCTS'));
            Configuration::updateValue('QOV_PRODUCTS_IMG', Tools::getValue('QOV_PRODUCTS_IMG'));
            Configuration::updateValue('QOV_PRODUCTS_FEAT', Tools::getValue('QOV_PRODUCTS_FEAT'));
            Configuration::updateValue('QOV_PRODUCTS_CAT', Tools::getValue('QOV_PRODUCTS_CAT'));
            Configuration::updateValue('QOV_PRODUCTS_CATS', Tools::getValue('QOV_PRODUCTS_CATS'));
            Configuration::updateValue('QOV_CURRENT_STOCK', Tools::getValue('QOV_CURRENT_STOCK'));
            Configuration::updateValue('QOV_HISTORY_UPDATE', Tools::getValue('QOV_HISTORY_UPDATE'));
            Configuration::updateValue('QOV_EXPAND_ALL', Tools::getValue('QOV_EXPAND_ALL'));
            Configuration::updateValue('QOV_PARCEL_WEIGHT', Tools::getValue('QOV_PARCEL_WEIGHT'));
            Configuration::updateValue('QOV_PRODUCTS_UPC', Tools::getValue('QOV_PRODUCTS_UPC'));
            Configuration::updateValue('QOV_TRACKING_EMAIL', Tools::getValue('QOV_TRACKING_EMAIL'));
            Configuration::updateValue('QOV_FARRAY', implode(',', Tools::getValue('QOV_FARRAY')));

        }
        return $this->displayConfirmation($this->l('Settings updated'));
    }

    public function displayForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Expand all'),
                        'name' => 'QOV_EXPAND_ALL',
                        'desc' => $this->l('With this option you will expand details of all orders once you will expand one of them') . '</br>' .
                            (Configuration::get('QOV_EXPAND_ALL') == 0 ? '<div class="alert alert-info">' . $this->l('Preview of order will be activated when you will click on it. You can open preview of next order with keyboard "right arrow" button. You can open preview of previous order with keyboard "left arrow" button.') . '</div>':'') .
                            (Configuration::get('QOV_EXPAND_ALL') == 1 ? '<div class="alert alert-info">' . $this->l('Preview of all orders will be activated when you will try to open preview of any order') . '</div>':''),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Multiple preview'),
                        'name' => 'QOV_ROLLIN',
                        'desc' => $this->l('Activate option if you want to preview many orders.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Customer'),
                        'name' => 'QOV_CUSTOMER',
                        'desc' => $this->l('Display Customer details'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Customer note'),
                        'name' => 'QOV_CUSTOMNOTE',
                        'desc' => $this->l('Display Customer note'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Customer Messages'),
                        'name' => 'QOV_CUSTOMERM',
                        'desc' => $this->l('Display Customer messages'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Shipping'),
                        'name' => 'QOV_SHIPPING',
                        'desc' => $this->l('Display shipping info'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Shipping').' ('. $this->l('delivery address').')',
                        'name' => 'QOV_SHIPPING_ADDRESS',
                        'desc' => $this->l('Display shipping info').' ('. $this->l('delivery address').')',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Shipping').' ('. $this->l('invoice address').')',
                        'name' => 'QOV_SHIPPING_INVOICE',
                        'desc' => $this->l('Display shipping info').' ('. $this->l('invoice address').')',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Include order ID to shipping details'),
                        'name' => 'QOV_SHIPPING_ID',
                        'desc' => $this->l('Option when active will display ID below the shipping details info'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Include order reference to shipping details'),
                        'name' => 'QOV_SHIPPING_REF',
                        'desc' => $this->l('Option when active will display order reference below the shipping details info'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Tracking number change'),
                        'name' => 'QOV_SHIPPING_EDIT',
                        'desc' => $this->l('Display feature to change tracking number'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Send email with tracking'),
                        'name' => 'QOV_TRACKING_EMAIL',
                        'desc' => $this->l('If enabled - module will send email with tracing informations to customer'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Parcel weight'),
                        'name' => 'QOV_PARCEL_WEIGHT',
                        'desc' => $this->l('Display weight of the parcel'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Products'),
                        'name' => 'QOV_PRODUCTS',
                        'desc' => $this->l('Display list of products'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Product\'s Features'),
                        'name' => 'QOV_PRODUCTS_FEAT',
                        'desc' => $this->l('Display products\'s features on list of purchased products'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Products miniature size'),
                        'name' => 'QOV_PRODUCTS_IMG',
                        'desc' => $this->l('Set the size of product\'s miniature on list of purchased items. Default value: 45'),
                        'suffix' => 'px',
                        'class' => 'col-lg-1 col-md-3 col-sm-12 col-xs-12'
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Features to show'),
                        'desc' => $this->l('Choose features to show'),
                        'name' => 'QOV_FARRAY',
                        'multiple' => true,
                        'options' => array(
                            'query' => Feature::getFeatures($this->context->language->id),
                            'id' => 'id_feature',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Product\'s category'),
                        'name' => 'QOV_PRODUCTS_CAT',
                        'desc' => $this->l('Display product\'s main category'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Product\'s categories'),
                        'name' => 'QOV_PRODUCTS_CATS',
                        'desc' => $this->l('Show all associations with categories'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Current stock'),
                        'name' => 'QOV_CURRENT_STOCK',
                        'desc' => $this->l('Display current stock of product'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Product UPC'),
                        'name' => 'QOV_PRODUCTS_UPC',
                        'desc' => $this->l('Display product UPC code on list of products'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status history'),
                        'name' => 'QOV_HISTORY',
                        'desc' => $this->l('Display history of order states'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status history update'),
                        'name' => 'QOV_HISTORY_UPDATE',
                        'desc' => $this->l('Display form to change order status from preview window'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));

    }

    public function getConfigFieldsValues()
    {
        return array(
            'QOV_ROLLIN' => Tools::getValue('QOV_ROLLIN', Configuration::get('QOV_ROLLIN')),
            'QOV_HISTORY' => Tools::getValue('QOV_HISTORY', Configuration::get('QOV_HISTORY')),
            'QOV_HISTORY_UPDATE' => Tools::getValue('QOV_HISTORY_UPDATE', Configuration::get('QOV_HISTORY_UPDATE')),
            'QOV_PRODUCTS' => Tools::getValue('QOV_PRODUCTS', Configuration::get('QOV_PRODUCTS')),
            'QOV_PRODUCTS_FEAT' => Tools::getValue('QOV_PRODUCTS_FEAT', Configuration::get('QOV_PRODUCTS_FEAT')),
            'QOV_PRODUCTS_CAT' => Tools::getValue('QOV_PRODUCTS_CAT', Configuration::get('QOV_PRODUCTS_CAT')),
            'QOV_PRODUCTS_CATS' => Tools::getValue('QOV_PRODUCTS_CATS', Configuration::get('QOV_PRODUCTS_CATS')),
            'QOV_CURRENT_STOCK' => Tools::getValue('QOV_CURRENT_STOCK', Configuration::get('QOV_CURRENT_STOCK')),
            'QOV_PRODUCTS_UPC' => Tools::getValue('QOV_PRODUCTS', Configuration::get('QOV_PRODUCTS_UPC')),
            'QOV_SHIPPING' => Tools::getValue('QOV_SHIPPING', Configuration::get('QOV_SHIPPING')),
            'QOV_SHIPPING_INVOICE' => Tools::getValue('QOV_SHIPPING_INVOICE', Configuration::get('QOV_SHIPPING_INVOICE')),
            'QOV_SHIPPING_ADDRESS' => Tools::getValue('QOV_SHIPPING_ADDRESS', Configuration::get('QOV_SHIPPING_ADDRESS')),
            'QOV_SHIPPING_ID' => Tools::getValue('QOV_SHIPPING_ID', Configuration::get('QOV_SHIPPING_ID')),
            'QOV_SHIPPING_REF' => Tools::getValue('QOV_SHIPPING_REF', Configuration::get('QOV_SHIPPING_REF')),
            'QOV_SHIPPING_EDIT' => Tools::getValue('QOV_SHIPPING_EDIT', Configuration::get('QOV_SHIPPING_EDIT')),
            'QOV_CUSTOMER' => Tools::getValue('QOV_CUSTOMER', Configuration::get('QOV_CUSTOMER')),
            'QOV_CUSTOMERM' => Tools::getValue('QOV_CUSTOMERM', Configuration::get('QOV_CUSTOMERM')),
            'QOV_EXPAND_ALL' => Tools::getValue('QOV_EXPAND_ALL', Configuration::get('QOV_EXPAND_ALL')),
            'QOV_PARCEL_WEIGHT' => Tools::getValue('QOV_PARCEL_WEIGHT', Configuration::get('QOV_PARCEL_WEIGHT')),
            'QOV_TRACKING_EMAIL' => Tools::getValue('QOV_TRACKING_EMAIL', Configuration::get('QOV_TRACKING_EMAIL')),
            'QOV_FARRAY[]' => $this->returnFeatures(explode(',', Configuration::get('QOV_FARRAY'))),
            'QOV_CUSTOMNOTE' => Tools::getValue('QOV_CUSTOMNOTE', Configuration::get('QOV_CUSTOMNOTE')),
            'QOV_PRODUCTS_IMG' => Tools::getValue('QOV_PRODUCTS_IMG', Configuration::get('QOV_PRODUCTS_IMG')),
        );
    }

    public function returnFeatures($selected)
    {
        $array = array();
        foreach (Feature::getFeatures($this->context->language->id) AS $key => $value)
        {
            foreach ($selected as $selected_option => $selected_option_value)
            {
                if ($value['id_feature'] == $selected_option_value)
                {
                    array_push($array, $value['id_feature']);
                }
            }
        }
        return $array;
    }

    public function hookbackofficefooter($params)
    {
        if ($this->psversion() == 7 && !Tools::getIsset(Tools::getValue('id_order')) && Tools::getValue('controller', 'false') == "AdminOrders")
        {
            echo '<script>var module_secure_key = "' . $this->secure_key . '"; var qov_employee_id = "' . Context::getContext()->cookie->id_employee . '";</script>';
            if (Configuration::get('QOV_EXPAND_ALL') == 1)
            {
                echo '<script src="' . $this->_path . 'views/js/16orders-expand-all.js"></script>';
                echo '<script> var previewOrderUrl = "'.$this->context->link->getAdminLink('AdminPreviewOrder').'&ajax=1";</script>';
            }
            elseif (Configuration::get('QOV_ROLLIN') == 1)
            {
                echo '<script src="' . $this->_path . 'views/js/16orders-multiple.js"></script>';
                echo '<script> var previewOrderUrl = "'.$this->context->link->getAdminLink('AdminPreviewOrder').'&ajax=1";</script>';
            }
            else
            {
                echo '<script src="' . $this->_path . 'views/js/16orders.js"></script>';
                echo '<script> var previewOrderUrl = "'.$this->context->link->getAdminLink('AdminPreviewOrder').'&ajax=1";</script>';
            }
        }
    }
}

class qovUpdate extends qov
{
    public static function version($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 3) {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 2) {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 1) {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 0) {
            $version = (int)$version . "0000";
        }

        return (int)$version;
    }

    public static function encrypt($string)
    {
        return base64_encode($string);
    }

    public static function verify($module, $key, $version)
    {
        if (ini_get("allow_url_fopen")) {
            if (function_exists("file_get_contents")) {
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module=' . $module . "&version=" . self::encrypt($version) . "&lic=$key&u=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__));
            }
        }
        Configuration::updateValue("update_" . $module, date("U"));
        Configuration::updateValue("updatev_" . $module, $actual_version);

        return $actual_version;
    }
}
?>