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

class PBPAdminProductTabBundlesController extends PBPControllerCore
{

    public function __construct($sibling, $params = array())
    {
        parent::__construct($sibling, $params);
        $this->sibling = $sibling;
        $this->base_url = Tools::getShopProtocol() . Tools::getShopDomain() . __PS_BASE_URI__;
    }

    public function setMedia()
    {
    }

    public function renderList()
    {
        $product_bundles = new PBPBundleModel();
        $pbp_bundles = $product_bundles->getByProduct(Tools::getValue('id_product'), false, (int)Tools::getValue('id_shop'));

        Context::getContext()->smarty->assign(array(
            'product_bundles' => $pbp_bundles
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/producttab/bundles.tpl');
    }

    /**
     * Render the Add/ Edit Bundle form
     */
    public function renderBundleForm()
    {
        $tabs_model = new PBPTabLangModel();
        $bundle = new PBPBundleModel();
        $languages = Language::getLanguages();
        $id_bundle = (int)Tools::getValue('id_bundle');

        if ($id_bundle > 0) {
            foreach ($languages as $language) {
                $pbp_bundle_model = new PBPBundleLangModel();
                $pbp_bundle_model->load($id_bundle, $language['id_lang']);
                $name_localised_array[$language['id_lang']] = $pbp_bundle_model->name;
            }
        } else {
            foreach ($languages as $language) {
                $name_localised_array[$language['id_lang']] = '';
            }
        }

        $tabs_collection = $tabs_model->loadAll($this->id_lang_default);
        if ($id_bundle > 0) {
            $bundle->loadSingle($id_bundle, Tools::getValue('id_product'));
        } else {
            $bundle = new PBPBundleModel();
        }

        $product_search_widget = new PBPProductSearchWidgetController('pbpproducts1', $this->sibling);

        Context::getContext()->smarty->assign(array(
            'tabs_collection' => $tabs_collection,
            'bundle' => $bundle,
            'languages' => $languages,
            'name_localised_array' => $name_localised_array,
            'id_lang_default' => $this->id_lang_default,
            'product_search' => $product_search_widget->render()
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/producttab/bundle_edit.tpl');
    }

    /**
     * Render list of products in a bundle
     * @return mixed
     */
    public function renderProductList()
    {
        $pbp_product = new PBPProductModel();
        $products = $pbp_product->loadByBundle(Tools::getValue('id_bundle'), Context::getContext()->language->id);

        Context::getContext()->smarty->assign(array(
            'products' => $products
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/producttab/bundle_productlist.tpl');
    }

    /**
     * Get Bundle Product Data for ajax call
     * @return json string
     */
    public function getBundleProductData()
    {
        $pbp_product_model = new PBPProductModel();
        $pbp_product_model->load(Tools::getValue('id_product'), Tools::getValue('id_bundle'), Context::getContext()->shop->id);

        $product = new Product(Tools::getValue('id_product'), false, Context::getContext()->language->id);

        $pbp_product_model->product_name = $product->name;
        return json_encode($pbp_product_model);
    }

    /**
     * @return string
     */
    public function processForm()
    {
        $id_bundle = (int)Tools::getValue('id_bundle');
        $languages = Language::getLanguages();
        $ajax_response = new PBPAjaxResponse();

        if (Tools::getValue('pbp_name_' . $this->id_lang_default) == '') {
            $ajax_response->addMessage('Missing bundle name', '#pbp_name_' . $this->id_lang_default, true);
        }

        if ($ajax_response->hasErrors()) {
            $ajax_response->ajaxDie();
        }

        // new bundle
        if ($id_bundle == 0) {
            $bundle = new PBPBundleModel();
            $bundle->id_bundle = $bundle->getNewBundleID();
            $bundle->id_product = Tools::getValue('id_product');
            $bundle->id_tab = Tools::getValue('id_tab');
            $bundle->id_shop = Tools::getValue('id_shop');
            $bundle->enabled = 1;
            $bundle->allow_selection = (int)Tools::getValue('allow_selection');;
            $bundle->discount_enabled = (int)Tools::getValue('discount_enabled');
            $bundle->discount_amount = (float)Tools::getValue('discount_amount');
            $bundle->discount_type = 'percentage';
            $bundle->parent_product_discount_amount = (float)Tools::getValue('parent_product_discount_amount');
            $bundle->parent_product_discount_type = pSQL(Tools::getValue('parent_product_discount_type'));
            $bundle->add();

            foreach ($languages as $language) {
                $bundle_lang_model = new PBPBundleLangModel();
                $bundle_lang_model->id_bundle = $bundle->id_bundle;
                $bundle_lang_model->id_lang = $language['id_lang'];

                if (Tools::getValue('pbp_name_' . (int)$language['id_lang']) != '') {
                    $bundle_lang_model->name = Tools::getValue('pbp_name_' . (int)$language['id_lang']);
                } else {
                    $bundle_lang_model->name = Tools::getValue('pbp_name_' . (int)$this->id_lang_default);
                }
                $bundle_lang_model->add();
            }
        } else {
            $bundle = new PBPBundleModel();
            $bundle->loadSingle(Tools::getValue('id_bundle'), Tools::getValue('id_product'));
            $bundle->id_tab = Tools::getValue('id_tab');
            $bundle->enabled = (int)Tools::getValue('enabled');
            $bundle->id_shop = Tools::getValue('id_shop');
            $bundle->allow_selection = (int)Tools::getValue('allow_selection');
            $bundle->discount_enabled = (int)Tools::getValue('discount_enabled');
            $bundle->discount_amount = (float)Tools::getValue('discount_amount');
            $bundle->discount_type = 'percentage';
            $bundle->parent_product_discount_amount = (float)Tools::getValue('parent_product_discount_amount');
            $bundle->parent_product_discount_type = pSQL(Tools::getValue('parent_product_discount_type'));

            foreach ($languages as $language) {
                $bundle_lang_model = new PBPBundleLangModel();
                $bundle_lang_model->id_bundle = $id_bundle;
                $bundle_lang_model->id_lang = $language['id_lang'];

                if (Tools::getValue('pbp_name_' . (int)$language['id_lang']) != '') {
                    $bundle_lang_model->name = Tools::getValue('pbp_name_' . (int)$language['id_lang']);
                } else {
                    $bundle_lang_model->name = Tools::getValue('pbp_name_' . (int)$this->id_lang_default);
                }
                $bundle_lang_model->deleteBundle($id_bundle, $language['id_lang']);
                $bundle_lang_model->save();
            }
            $bundle->update();
        }

        // Add the bundle products
        PBPProductModel::deleteBundleProducts($bundle->id_bundle);

        $post_bundle_products = Tools::getValue('bundle_products');

        if (is_array($post_bundle_products)) {
            foreach ($post_bundle_products as $bundle_product) {
                $pbp_product = new PBPProductModel();
                $pbp_product->id_bundle = $bundle->id_bundle;
                $pbp_product->id_product = $bundle_product['id_product'];
                $pbp_product->discount_type = $bundle_product['discount_type'];
                $pbp_product->discount_amount = $bundle_product['discount_amount'];
                $pbp_product->qty = $bundle_product['qty'];
                $pbp_product->allow_oos = $bundle_product['allow_oos'];
                //$pbp_product->discount_tax = $bundle_product['discount_tax'];
                $pbp_product->discount_tax = 'tax_ex';
                $pbp_product->add();
            }
        }

        $return = array();
        $return['id_bundle'] = $bundle->id_bundle;
        return Tools::jsonEncode($return);
    }

    /**
     * Update display order of bundles in a product
     * @throws PrestaShopException
     */
    public function processBundlePositions()
    {
        $position = 0;
        foreach (Tools::getValue('bundle_positions') as $post_bundle_id) {
            $pbp_bundle = new PBPBundleModel();
            $pbp_bundle->loadSingle($post_bundle_id, Tools::getValue('id_product'));
            $pbp_bundle->position = $position;
            $position++;
            $pbp_bundle->update();
        }
    }

    /**
     * Process postions of products in a bundle
     * @throws PrestaShopException
     */
    public function processBundleProductPositions()
    {
        $position = 0;
        foreach (Tools::getValue('bundle_product_positions') as $post_product_bundle_id) {
            $pbp_product = new PBPProductModel($post_product_bundle_id);
            $pbp_product->position = $position;
            $position++;
            $pbp_product->update();
        }
    }


    /**
     * Delete a bundle from a product
     */
    public function processDeleteBundle()
    {
        $id_bundle = (int)Tools::getValue('id_bundle');
        $id_product = (int)Tools::getValue('id_product');
        PBPProductModel::deleteBundleProducts($id_bundle);
        PBPBundleHelper::deleteByBundle($id_bundle);
        $bundle = new PBPBundleModel();
        $bundle->loadSingle($id_bundle, $id_product);
        $bundle->delete();
    }

    /**
     * Delete a product bundle from a product process
     */
    public function processDeleteProduct()
    {
        $id_pbp_product = (int)Tools::getValue('id_pbp_product');
        $pbp_product = new PBPProductModel($id_pbp_product);
        $pbp_product->delete();
    }

    public function route()
    {
        switch (Tools::getValue('action')) {
            case 'renderbundleform':
                die($this->renderBundleForm());

            case 'renderproductlist':
                die($this->renderProductList());

            case 'getproductbundledata':
                die($this->getBundleProductData());

            case 'processform':
                die($this->processForm());

            case 'processbundlepositions':
                die($this->processBundlePositions());

            case 'processbundleproductpositions':
                die($this->processBundleProductPositions());

            case 'processdeletebundle':
                die($this->processDeleteBundle());

            case 'processdeleteproduct':
                die($this->processDeleteProduct());

            default:
                return $this->renderList();
        }
    }
}
