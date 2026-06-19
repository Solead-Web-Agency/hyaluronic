<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

class PBPAdminConfigMassAssignController extends PBPControllerCore
{
    protected $sibling;

    private $route = 'pbpadminconfigmassassigncontroller';

    public function __construct(&$sibling = null)
    {
        parent::__construct($sibling);
        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }
    }

    /**
     * Get the form
     * @return string
     */
    public function getForm()
    {
        $inputs = array();
        $id_shop = Context::getContext()->shop->id;
        $fields_form = array();

        $product_search_widget = new PBPProductSearchWidgetController('pbpproducts1', $this->sibling);
        $selected_products = array();

        Context::getContext()->smarty->assign(array());
        $product_mass_assign_container = $this->sibling->display(_PS_MODULE_DIR_ . $this->module_folder, 'views/templates/admin/config/mass_assign_products_container.tpl');

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->sibling->l('Mass Assign', $this->route),
                'icon' => 'icon-question'
            ),
            'input' => array(
                array(
                    'name' => '',
                    'type' => 'html',
                    'label' => $this->sibling->l('Copy settings from', $this->route),
                    'desc' => $this->sibling->l('Name of product to copy module settings from', $this->route),
                    'class' => 'fixed-width-xl',
                    'required' => true,
                    'html_content' => $product_search_widget->render($selected_products),
                    'size' => 255
                ),
                array(
                    'type' => 'categories',
                    'label' => $this->sibling->l('Product Category', $this->route),
                    'name' => 'category',
                    'tree' => array(
                        'id' => 'category',
                        'use_checkbox' => true,
                        'selected_categories' => array()
                    )
                ),
                array(
                    'type' => 'html',
                    'label' => $this->sibling->l('Products', $this->route),
                    'name' => '',
                    'html_content' => $product_mass_assign_container
                ),
            ),
        );

        $helper = new HelperForm();
        $this->setupHelperConfigForm($helper, $this->route, 'process');
        return $helper->generateForm($fields_form);
    }

    /**
     * @return mixed
     */
    public function render()
    {
        Context::getContext()->smarty->assign(array(
            'form' => $this->getForm()
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/mass_assign.tpl');
    }

    /**
     * Render products in a category
     */
    public function renderProducts()
    {
        $id_lang = Context::getContext()->language->id;
        $id_product = Tools::getValue('id_product');
        $id_category = (int)Tools::getValue('id_category');
        $category = new Category($id_category);
        $products = $category->getProducts($id_lang, 0, 9999, 'name');

        $products_filtered = array();

        if (!empty($id_product)) {
            foreach ($products as $product) {
                if ($product['id_product'] != $id_product) {
                    $products_filtered[] = $product;
                }
            }
        } else {
            $products_filtered = $products;
        }

        Context::getContext()->smarty->assign(array(
            'products' => $products_filtered
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/mass_assign_products.tpl');
    }

    /**
     * copy module settings from one product to another array of products
     */
    public function processMassAssignProducts()
    {
        $id_shop = Context::getContext()->shop->id;
        $products_new = Tools::getValue('id_product_new');

        if (empty($products_new)) {
            return false;
        }

        foreach ($products_new as $id_product_new) {
            PBPMassAssignHelper::duplicateProduct(Tools::getValue('id_product'), $id_product_new, $id_shop);
        }
    }

    /**
     * Process mass assignment of categories
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function processMassAssignCategories()
    {
        $id_shop = Context::getContext()->shop->id;
        $id_lang = Context::getContext()->language->id;
        $id_categories = Tools::getValue('id_categories');

        if (empty($id_categories)) {
            return false;
        }

        foreach ($id_categories as $id_category) {
            $category = new Category($id_category);
            $products = $category->getProducts($id_lang, 0, 9999, 'name');

            foreach ($products as $product) {
                PBPMassAssignHelper::duplicateProduct(Tools::getValue('id_product'), $product['id_product'], $id_shop);
            }
        }
    }

    /**
     * Delete bundle from the specified product
     */
    public function processMassAssignDeleteProducts()
    {
        $id_products = Tools::getValue('id_products');
        $id_shop = Context::getContext()->shop->id;

        if (empty($id_products)) {
            return false;
        }

        foreach ($id_products as $id_product) {
            PBPMassAssignHelper::deleteProductBundles($id_product, $id_shop);
        }
    }

    /**
     * Delete bundles from all products in category
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function processMassAssignDeleteCategories()
    {
        $id_shop = Context::getContext()->shop->id;
        $id_lang = Context::getContext()->language->id;
        $id_categories = Tools::getValue('id_categories');

        if (empty($id_categories)) {
            return false;
        }

        foreach ($id_categories as $id_category) {
            $category = new Category($id_category);
            $products = $category->getProducts($id_lang, 0, 9999, 'name');

            foreach ($products as $product) {
                PBPMassAssignHelper::deleteProductBundles($product['id_product'], $id_shop);
            }
        }
    }


    public function route()
    {
        switch (Tools::getValue('action')) {
            case 'process':
                die($this->process());

            case 'renderproducts':
                die($this->renderProducts());

            case 'massassign':
                die($this->processMassAssign());

            case 'processmassassignproducts':
                die($this->processMassAssignProducts());

            case 'processmassassigncategories':
                die($this->processMassAssignCategories());

            case 'processmassassigndeleteproducts':
                die($this->processMassAssignDeleteProducts());

            case 'processmassassigndeletecategories':
                die($this->processMassAssignDeleteCategories());

            default:
                return $this->render();
        }
    }
}
