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
 * @copyright 2016-2017 Musaffar Patel
 * @license   LICENSE.txt
 */

class PBPAdminProductTabBundlesExistingController extends PBPControllerCore
{
    public function renderForm()
    {
        $tabs_model = new PBPTabLangModel();
        $tabs_collection = $tabs_model->loadAll(Context::getContext()->language->id);

        $product_search_widget = new PBPProductSearchWidgetController('pbpproducts2', $this->sibling);
        $selected_products = array();

        Context::getContext()->smarty->assign(array(
            'module_ajax_url_pbp' => $this->module_bo_ajax_url,
            'module_tab_url' => $this->module_tab_url,
            'module_url' => $this->module_url,
            'tabs_collection' => $tabs_collection,
            'product_search' => $product_search_widget->render($selected_products)
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/producttab/bundles_existing.tpl');
    }

    public function renderProductBundles()
    {
        $pbp_bundle_model = new PBPBundleModel();
        $bundles = $pbp_bundle_model->getByProduct(Tools::getValue('id_product'), false, (int)Tools::getValue('id_shop'));

        Context::getContext()->smarty->assign(array(
            'module_tab_url' => $this->module_tab_url,
            'module_url' => $this->module_url,
            'bundles' => $bundles
        ));

        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/producttab/bundles_existing_list.tpl');
    }

    /**
     * Duplicate an existing bundle
     */
    public function processAddBundle()
    {
        $id_bundle = (int)Tools::getValue('id_bundle');
        $pbp_bundle_model = new PBPBundleModel();
        $pbp_bundle_model->load($id_bundle);

        if (empty($pbp_bundle_model->id_bundle)) {
            return false;
        }

        $pbp_bundle_model_new = new PBPBundleModel();
        $pbp_bundle_model_new->id_bundle = (int)$pbp_bundle_model_new::getNewBundleID();
        $pbp_bundle_model_new->id_tab = (int)Tools::getValue('id_tab');
        $pbp_bundle_model_new->id_product = (int)Tools::getValue('id_product');
        $pbp_bundle_model_new->id_shop = (int)Tools::getValue('id_shop');
        $pbp_bundle_model_new->enabled = (int)$pbp_bundle_model->enabled;
        $pbp_bundle_model_new->allow_selection = (int)$pbp_bundle_model->allow_selection;
        $pbp_bundle_model_new->position = 0;
        $pbp_bundle_model_new->add();
        $names = PBPBundleHelper::getNames($id_bundle);

        foreach($names as $id_lang=>$name) {
            $pbp_bundle_lang_model = new PBPBundleLangModel();
            $pbp_bundle_lang_model->id_bundle = (int)$pbp_bundle_model_new->id_bundle;
            $pbp_bundle_lang_model->id_lang = (int)$id_lang;
            $pbp_bundle_lang_model->name = pSQL($name);
            $pbp_bundle_lang_model->add();
        }

        // duplicate the bundle products
        $pbp_product_model = new PBPProductModel();
        $bundle_products = $pbp_product_model->getByBundle($id_bundle);

        foreach($bundle_products as $bundle_product) {
            $pbp_product_model_new = new PBPProductModel();
            $pbp_product_model_new->id_bundle =(int)$pbp_bundle_model_new->id_bundle;
            $pbp_product_model_new->id_product = (int)$bundle_product['id_product'];
            $pbp_product_model_new->discount_type = pSQL($bundle_product['discount_type']);
            $pbp_product_model_new->discount_amount = (float)$bundle_product['discount_amount'];
            $pbp_product_model_new->discount_tax = pSQL($bundle_product['discount_tax']);
            $pbp_product_model_new->qty = (int)$bundle_product['qty'];
            $pbp_product_model_new->position = (int)$bundle_product['position'];
            $pbp_product_model_new->add();
        }
    }

    public function route()
    {
        switch (Tools::getValue('action')) {
            case 'renderform':
                return $this->renderForm();
            case 'renderproductbundles':
                return $this->renderProductBundles();
            case 'processaddbundle':
                return $this->processAddBundle();
        }
    }
}
