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
require_once _PS_MODULE_DIR_ . 'cedwish/classes/profile.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/helper.php';

class AdminCedWishProfileController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cedwish_profile';
        $this->identifier = 'id_cedwish_profile';
        $this->className = 'CedWishProfile';
        $this->_orderBy = 'id_cedwish_profile';
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;
        parent::__construct();

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Are you sure, you want to remove mapping.')
            ),
        );

        $this->fields_list = array(
            'id_cedwish_profile' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'align' => 'text-center',
            ),
            'attribute_mapping' => array(
                'title' => $this->l('Item(s)'),
                'align' => 'text-center',
                'callback' => 'renderProfileItemCount'
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'align' => 'text-center',
                'active' => 'status',
                'class' => 'fixed-width-xs',
            )
        );

        $this->_conf = array(
            1 => $this->l('Profile Created Successfully.'),
            2 => $this->l('Profile Updated Successfully.'),
        );
    }

    public function renderProfileItemCount($attribute_mapping, $row)
    {
        if ($attribute_mapping && $row['id_cedwish_profile']) {
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                "SELECT COUNT(DISTINCT (id_product)) FROM `" . _DB_PREFIX_ . "cedwish_profile_product` 
                WHERE id_cedwish_profile = '" . (int)$row['id_cedwish_profile'] . "'"
            );
        }
        return 0;
    }

    public function initBreadcrumbs($tab_id = null, $tabs = null)
    {
        if (Tools::getValue('id_cedwish_profile')) {
            $this->display = 'edit';
        }
        return parent::initBreadcrumbs($tab_id, $tabs);
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['add_profile'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishProfile') . '&addcedwish_profile',
                'desc' => 'New Profile',
                'icon' => 'process-icon-plus'
            );
        }
        if ($this->display == 'edit') {
            $this->page_header_toolbar_btn['back_profile'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedWishProfile'),
                'desc' => 'Back To List',
                'icon' => 'process-icon-back'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function renderForm()
    {
        $current_object = $this->loadObject(true);
        $selected_categories = array();
        if ($current_object && $current_object->categories) {
            $selected_categories = json_decode($current_object->categories, true);
        }
        $root_category = Category::getRootCategory();
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $tree_categories_helper = new HelperTreeCategories('categories-treeview');
            $tree_categories_helper->setRootCategory(
                (Shop::getContext() == Shop::CONTEXT_SHOP ?
                    Category::getRootCategory()->id_category : 0)
            )
                ->setInputName('categories')
                ->setUseCheckBox(true);
        } else {
            if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $root_category = Category::getRootCategory();
                $root_category = array(
                    'id_category' => $root_category->id_category,
                    'name' => $root_category->name
                );
            } else {
                $root_category = array('id_category' => '0', 'name' => $this->l('Root'));
            }
            $tree_categories_helper = new Helper();
        }
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $tree_categories_helper->setUseSearch(true);
            $tree_categories_helper->setSelectedCategories($selected_categories);
            $storeCategories = $tree_categories_helper->render();
        } else {
            $storeCategories = $tree_categories_helper->renderCategoryTree(
                $root_category,
                $selected_categories,
                'categories'
            );
        }
        $selected_manufacturers = array();
        if ($current_object && $current_object->manufacturers) {
            $selected_manufacturers = json_decode($current_object->manufacturers, true);
        }

        $selected_suppliers = array();
        if ($current_object && $current_object->suppliers) {
            $selected_suppliers = json_decode($current_object->suppliers, true);
        }

        $product_setting = array();
        if ($current_object && $current_object->product_setting) {
            $product_setting = json_decode($current_object->product_setting, true);
        }

        $defaultAttributes = $current_object->getDefaultAttributes();
        $storeFeatures = Feature::getFeatures(CedWishHelper::getLanguageId());
        $storeAttributes = AttributeGroup::getAttributesGroups(CedWishHelper::getLanguageId());
        $storeDefaultAttributes = $current_object->getSystemAttributes();
        $id_cedwish_profile = Tools::getValue('id_cedwish_profile');

        $profile = false;
        if ($id_cedwish_profile) {
            $profile = new CedWishProfile((int)$id_cedwish_profile);
        }
        $profile_data = array(
            'attribute_mapping' => array(),
            'product_setting' => array(),
            'default_mapping' => array(),
        );

        if ($profile && $profile->id_cedwish_profile) {
            if ($profile->attribute_mapping) {
                $profile_data['attribute_mapping'] = json_decode($profile->attribute_mapping, true);
            }

            if ($profile->product_setting) {
                $profile_data['product_setting'] = json_decode($profile->product_setting, true);
            }

            if ($profile->default_mapping) {
                $profile_data['default_mapping'] = json_decode($profile->default_mapping, true);
            }

            if ($profile->default_shipping_prices) {
                $profile_data['default_shipping_prices'] = json_decode($profile->default_shipping_prices, true);
            }

            if ($profile->warehouse_to_shippings) {
                $profile_data['warehouse_to_shippings'] = json_decode($profile->warehouse_to_shippings, true);
            }
        }

        $warehouses = CedWishProfile::getWarehouses();
        if (empty($warehouses)) {
            $link = new LinkCore();
            $controller_link = $link->getAdminLink('AdminModules');
            Tools::redirectAdmin($controller_link . '&configure=cedwish&check_config=1');
        }
        $this->context->smarty->assign(
            array(
                'manufacturer_list' => Manufacturer::getManufacturers(),
                'shippableCountries' => CedWishHelper::getShippableCountries(),
                'shippableRegions' => CedWishHelper::getShippingRegions(),
                'supplier_list' => Supplier::getSuppliers(),
                'storeCategories' => $storeCategories,
                'profile' => $current_object,
                'warehouses' => $warehouses,
                'wish_currency' => Configuration::get('CED_WISH_WISH_CURRENCY'),
                'default_origin_country' => Configuration::get('CED_WISH_ORIGIN_COUNTRY'),
                'default_shipping_amount' => Configuration::get('CED_WISH_DEFAULT_SHIPPING_AMOUNT'),
                'product_setting' => $product_setting,
                'attributes' => CedWishProfile::getAttributes(),
                'selected_manufacturers' => $selected_manufacturers,
                'selected_suppliers' => $selected_suppliers,
                'id_profile' => $id_cedwish_profile,
                'profile_data' => $profile_data,
                'defaultAttributes' => $defaultAttributes,
                'storeFeatures' => $storeFeatures,
                'storeDefaultAttributes' => $storeDefaultAttributes,
                'storeAttributes' => $storeAttributes
            )
        );

        $profileTemplate = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/profile/profile.tpl'
        );

        return $profileTemplate . parent::renderForm();
    }

    public function renderList()
    {
        return parent::renderList();
    }

    public function initContent()
    {
        return parent::initContent();
    }

    public function postProcess()
    {

        return parent::postProcess();
    }

    public function processAdd()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $post_data = Tools::getAllValues();
            $fields = CedWishProfile::getDefinition('CedWishProfile');
            if (isset($fields['fields']) && !empty($fields['fields'])) {
                foreach ($fields['fields'] as $field => $specification) {
                    if (!isset($post_data[$field]) && $specification) {
                        $post_data[$field] = '';
                    }
                }
            }

            $required = array(
                'name'
            );

            foreach ($required as $field) {
                if (isset($post_data[$field]) && empty($post_data[$field])) {
                    $this->invalid[] = $field;
                }
            }
            if (empty($this->invalid)) {
                try {
                    $profile = new CedWishProfile();
                    foreach ($post_data as $field => $value) {
                        if (is_array($value)) {
                            $value = json_encode($value);
                        }
                        $profile->{$field} = $value;
                    }
                    if ($profile->add()) {
                        $save_status = $profile->assignProfile(
                            ($profile->id_cedwish_profile > 0) ? $profile->id_cedwish_profile : $profile->id,
                            $post_data['categories'],
                            $post_data['manufacturers'],
                            $post_data['price_from'],
                            $post_data['price_to'],
                            $post_data['suppliers']
                        );
                        if ($save_status) {
                            $link = new Link();
                            $redirect = $link->getAdminLink('AdminCedWishProfile');
                            Tools::redirectAdmin($redirect . '&conf=1');
                        } else {
                            $this->errors[] = 'Failed to assign items to profile.';
                        }
                    }
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (!empty($this->invalid)) {
                $this->errors[] = implode(', ', $this->invalid) . ' Fields are invalid.';
            }
        }
        return parent::processAdd();
    }

    public function processUpdate()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $post_data = Tools::getAllValues();
            $fields = CedWishProfile::getDefinition('CedWishProfile');
            if (isset($fields['fields']) && !empty($fields['fields'])) {
                foreach ($fields['fields'] as $field => $specification) {
                    if (!isset($post_data[$field]) && $specification) {
                        $post_data[$field] = '';
                    }
                }
            }

            $required = array(
                'name'
            );

            foreach ($required as $field) {
                if (isset($post_data[$field]) && empty($post_data[$field])) {
                    $this->invalid[] = $field;
                }
            }
            if (empty($this->invalid)) {
                try {
                    $profile = new CedWishProfile((int)$post_data['id_cedwish_profile']);
                    foreach ($post_data as $field => $value) {
                        if (is_array($value)) {
                            $value = json_encode($value);
                        }
                        $profile->{$field} = $value;
                    }
                    $updated = $profile->update();
                    if ($updated) {
                        $profile->assignProfile(
                            $profile->id_cedwish_profile,
                            $post_data['categories'],
                            $post_data['manufacturers'],
                            $post_data['price_from'],
                            $post_data['price_to'],
                            $post_data['suppliers']
                        );
                        $this->confirmations[] = 'Profile Updated Successfully.';
                        $link = new Link();
                        $redirect = $link->getAdminLink('AdminCedWishProfile');
                        Tools::redirectAdmin($redirect . '&conf=2');
                    }
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (!empty($this->invalid)) {
                $this->errors[] = implode(', ', $this->invalid) . ' Fields are invalid.';
            }
        }
        return parent::processUpdate();
    }

    protected function afterDelete($object, $old_id)
    {
        if ($object && $old_id) {
            Db::getInstance()->delete(
                'cedwish_profile_product',
                'id_cedwish_profile = "' . (int)$old_id . '"'
            );
        }
    }
}
