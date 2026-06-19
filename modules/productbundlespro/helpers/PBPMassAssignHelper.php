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

class PBPMassAssignHelper
{
    /**
     * Duplicate module settings from one product to another
     * @param $id_product_old
     * @param $id_product_new
     * @param $id_shop
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function duplicateProduct($id_product_old, $id_product_new, $id_shop)
    {
        self::deleteProductBundles($id_product_new, $id_shop);

        // Get all bundles associated with the source product
        $pbp_bundle_model = new PBPBundleModel();
        $bundles = $pbp_bundle_model->getByProduct($id_product_old, false, $id_shop);

        foreach ($bundles as $bundle) {
            self::duplicateBundle($bundle->id_bundle, $id_product_new);
        }
    }

    /**
     * Delete all bundles associated with a product
     * @param $id_product
     * @param $id_bundle
     * @param $id_shop
     */
    public static function deleteProductBundles($id_product, $id_shop)
    {
        $pbp_bundle_model = new PBPBundleModel();
        $bundles = $pbp_bundle_model->getByProduct($id_product, false, $id_shop);

        foreach ($bundles as $bundle) {
            self::deleteProductBundle($id_product, $bundle->id_bundle, $id_shop);
        }
    }

    /**
     * Delete a single bundle associated with a specific product
     * @param $id_product
     * @param $id_bundle
     * @param $id_shop
     */
    public static function deleteProductBundle($id_product, $id_bundle, $id_shop)
    {
        PBPBundleHelper::deleteByProductBundle($id_product, $id_bundle);
        PBPProductModel::deleteBundleProducts($id_bundle);
        PBPBundleLangModel::deleteBundle($id_bundle);
    }

    /**
     * Duplicate an existing bundle
     * @param $id_bundle
     * @param $id_product_new
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function duplicateBundle($id_bundle, $id_product_new)
    {
        $pbp_bundle_model = new PBPBundleModel();
        $pbp_bundle_model->load($id_bundle);

        if (empty($pbp_bundle_model->id_bundle)) {
            return false;
        }

        $pbp_bundle_model_new = new PBPBundleModel();
        $pbp_bundle_model_new->id_bundle = (int)$pbp_bundle_model_new::getNewBundleID();
        $pbp_bundle_model_new->id_tab = (int)$pbp_bundle_model->id_tab;
        $pbp_bundle_model_new->id_product = (int)$id_product_new;
        $pbp_bundle_model_new->id_shop = (int)$pbp_bundle_model->id_shop;
        $pbp_bundle_model_new->enabled = (int)$pbp_bundle_model->enabled;
        $pbp_bundle_model_new->allow_selection = (int)$pbp_bundle_model->allow_selection;
        $pbp_bundle_model_new->position = 0;
        $pbp_bundle_model_new->discount_type = pSQL($pbp_bundle_model->discount_type);
        $pbp_bundle_model_new->discount_amount = (float)$pbp_bundle_model->discount_amount;
        $pbp_bundle_model_new->discount_enabled = (int)$pbp_bundle_model->discount_enabled;
        $pbp_bundle_model_new->add();

        $names = PBPBundleHelper::getNames($id_bundle);

        foreach ($names as $id_lang => $name) {
            $pbp_bundle_lang_model = new PBPBundleLangModel();
            $pbp_bundle_lang_model->id_bundle = (int)$pbp_bundle_model_new->id_bundle;
            $pbp_bundle_lang_model->id_lang = (int)$id_lang;
            $pbp_bundle_lang_model->name = pSQL($name);
            $pbp_bundle_lang_model->add();
        }

        // duplicate the bundle products
        $pbp_product_model = new PBPProductModel();
        $bundle_products = $pbp_product_model->getByBundle($id_bundle);

        foreach ($bundle_products as $bundle_product) {
            $pbp_product_model_new = new PBPProductModel();
            $pbp_product_model_new->id_bundle = (int)$pbp_bundle_model_new->id_bundle;
            $pbp_product_model_new->id_product = (int)$bundle_product['id_product'];
            $pbp_product_model_new->discount_type = pSQL($bundle_product['discount_type']);
            $pbp_product_model_new->discount_amount = (float)$bundle_product['discount_amount'];
            $pbp_product_model_new->discount_tax = pSQL($bundle_product['discount_tax']);
            $pbp_product_model_new->qty = (int)$bundle_product['qty'];
            $pbp_product_model_new->position = (int)$bundle_product['position'];
            $pbp_product_model_new->add();
        }
    }
}
