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

class PBPFrontCartController extends PBPControllerCore
{

    protected $sibling;

    public function __construct(&$sibling = null)
    {
        parent::__construct($sibling);
        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }
    }

    public function setMedia()
    {
        if (Context::getContext()->controller->php_self == 'order') {
            $this->sibling->context->controller->addJS($this->sibling->_path . 'views/js/front/cart.js');
        }
    }


    /**
     * @param $price_original
     * @param $discount_type
     * @param $discount_amount
     * @param $allowed_discount_qty
     * @return mixed
     */
    public function getDiscountedPrice($price_original, $discount_type, $discount_amount, $qty, $allowed_discount_qty)
    {
        switch ($discount_type) {
            case 'percentage':
                $non_discounted_price = $price_original * ($qty - $allowed_discount_qty);
                $discounted_price = ($price_original * $allowed_discount_qty) - (($discount_amount / 100) * ($price_original * $allowed_discount_qty));
                return ($non_discounted_price + $discounted_price) / $qty;
            case 'money':
                $non_discounted_price = $price_original * ($qty - $allowed_discount_qty);
                $discounted_price = ($price_original * $allowed_discount_qty) - ($discount_amount * $allowed_discount_qty);
                return ($discounted_price + $non_discounted_price) / $qty;
        }
    }

    /**
     * Calculate the discount to be applied to the cart
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function calculateCartDiscount()
    {
        if (empty(Context::getContext()->cart->id)) {
            return false;
        }

        $id_cart = Context::getContext()->cart->id;
        $cart = new Cart($id_cart);
        $id_customer = Context::getContext()->customer->id;
        $id_currency = Context::getContext()->currency->id;
        $id_lang = Context::getContext()->language->id;
        $currency_active = new Currency($id_currency);
        $currency_default = Currency::getDefaultCurrency();
        $cart_products = $cart->getProducts();
        $parent_products = PBPCartProductExtraHelper::getAllParents($id_cart);

        //prestashop does not pick up the new country when address changes during checkout, lets force it to make sure correct ax rates are applied
        if ((int)Context::getContext()->cart->id_address_delivery > 0) {
            $address = new Address(Context::getContext()->cart->id_address_delivery);
            foreach ($cart_products as &$cart_product) {
                $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$cart_product['id_product'], Context::getContext()));
                $cart_product['rate'] = $tax_manager->getTaxCalculator()->getTotalRate();
                $cart_product['tax_name'] = $tax_manager->getTaxCalculator()->getTaxesName();
            }
            unset($cart_product);
        }

        $cart_products_hash = array();
        foreach ($cart_products as $cart_product) {
            $cart_products_hash[$cart_product['id_product'] . '-' . $cart_product['id_product_attribute']] = array(
                'price_tax_inc' => $cart_product['price_with_reduction'],
                'price_tax_excl' => $cart_product['price_with_reduction_without_tax'],
                'tax_rate' => $cart_product['rate']
            );
        }

        $discounts_bundles = array();
        foreach ($parent_products as $parent_product) {
            $bundle_model = new PBPBundleModel();
            $bundle = $bundle_model->getByID($parent_product['id_pbp_bundle'], true);

            $must_contain_all = true;
            $bundle_products = PBPCartProductExtraHelper::getChildren($parent_product['id_pbp_cart_productextra'], $id_cart);

            if (!empty($bundle[0])) {
                $bundle = $bundle[0];
            }

            // if the bundle must contain all child products for the discount to be applied then check
            if (!$bundle->allow_selection) {
                $found_all = true;
                foreach ($bundle->products as $bundle_product) {
                    $found = false;
                    foreach ($bundle_products as $b_product) {
                        if ($bundle_product->id_product == $b_product['id_product'] && $b_product['quantity'] >= $bundle_product->qty) {
                            $found = true;
                        }
                    }
                    if (!$found) {
                        $found_all = false;
                        break;
                    }
                }
                if (!$found_all) {
                    continue;
                }
            }
            unset($bundle_product);

            $id_pbp_bundle = $parent_product['id_pbp_bundle'];

            if (!$bundle->discount_enabled) {
                if ($bundle->parent_product_discount_amount > 0) {
                    $discounts_bundles[$id_pbp_bundle][] = array(
                        'quantity' => $parent_product['quantity'],
                        'price_original_tax_exc' => $cart_products_hash[$parent_product['id_product'] . '-' . $parent_product['id_product_attribute']]['price_tax_excl'],
                        'price_original_tax_inc' => $cart_products_hash[$parent_product['id_product'] . '-' . $parent_product['id_product_attribute']]['price_tax_inc'],
                        'discount_amount' => $bundle->parent_product_discount_amount,
                        'discount_type' => $bundle->parent_product_discount_type,
                        'discount_tax' => 0,
                        'tax_rate' => $cart_products_hash[$parent_product['id_product'] . '-' . $parent_product['id_product_attribute']]['tax_rate'],
                    );
                }

                foreach ($bundle_products as $bundle_product) {
                    $discounts_bundles[$id_pbp_bundle][] = array(
                        'quantity' => $bundle_product['quantity'],
                        'price_original_tax_exc' => $cart_products_hash[$bundle_product['id_product'] . '-' . $bundle_product['id_product_attribute']]['price_tax_excl'],
                        'price_original_tax_inc' => $cart_products_hash[$bundle_product['id_product'] . '-' . $bundle_product['id_product_attribute']]['price_tax_inc'],
                        'discount_amount' => $bundle_product['discount_amount'],
                        'discount_type' => $bundle_product['discount_type'],
                        'discount_tax' => $bundle_product['discount_tax'],
                        'tax_rate' => $cart_products_hash[$bundle_product['id_product'] . '-' . $bundle_product['id_product_attribute']]['tax_rate'],
                    );
                }
            }

            if ($bundle->discount_enabled) {
                $discounts_bundles[$id_pbp_bundle][] = array(
                    'quantity' => $parent_product['quantity'],
                    'price_original_tax_exc' => $cart_products_hash[$parent_product['id_product'] . '-' . $parent_product['id_product_attribute']]['price_tax_excl'],
                    'price_original_tax_inc' => $cart_products_hash[$parent_product['id_product'] . '-' . $parent_product['id_product_attribute']]['price_tax_inc'],
                    'discount_amount' => $bundle->discount_amount,
                    'discount_type' => $bundle->discount_type,
                    'discount_tax' => 0,
                    'tax_rate' => $cart_products_hash[$parent_product['id_product'] . '-' . $parent_product['id_product_attribute']]['tax_rate'],
                );

                foreach ($bundle_products as $bundle_product) {
                    $discounts_bundles[$id_pbp_bundle][] = array(
                        'quantity' => $bundle_product['quantity'],
                        'price_original_tax_exc' => $cart_products_hash[$bundle_product['id_product'] . '-' . $bundle_product['id_product_attribute']]['price_tax_excl'],
                        'price_original_tax_inc' => $cart_products_hash[$bundle_product['id_product'] . '-' . $bundle_product['id_product_attribute']]['price_tax_inc'],
                        'discount_amount' => $bundle->discount_amount,
                        'discount_type' => $bundle->discount_type,
                        'discount_tax' => 0,
                        'tax_rate' => $cart_products_hash[$bundle_product['id_product'] . '-' . $bundle_product['id_product_attribute']]['tax_rate'],
                    );
                }
            }
        }

        // now we have all the information we need to calculate the discount for the cart
        $discounts_final = array();

        foreach ($discounts_bundles as $id_pbp_bundle => $discounts_bundle) {
            foreach ($discounts_bundle as $discount) {
                if (empty($discounts_final[$id_pbp_bundle])) {
                    $discounts_final[$id_pbp_bundle] = array(
                        'names' => PBPBundleHelper::getNames($id_pbp_bundle),
                        'total_discount_tax_exc' => 0,
                        'total_discount_tax_inc' => 0
                    );
                }
                if ($discount['discount_type'] == 'money') {
                    if ($discount['discount_tax'] == 'tax_ex') {
                        $discounts_final[$id_pbp_bundle]['total_discount_tax_exc'] += $discount['discount_amount'] * $discount['quantity'];
						$discounts_final[$id_pbp_bundle]['total_discount_tax_inc'] += ($discount['discount_amount'] * (1 + ($discount['tax_rate'] / 100))) * $discount['quantity'];
						$discounts_final[$id_pbp_bundle]['total_discount_tax_inc'] = PBPProductHelper::formatPrice($discounts_final[$id_pbp_bundle]['total_discount_tax_inc']);
                    } else {
                        $discounts_final[$id_pbp_bundle]['total_discount_tax_exc'] += $discount['discount_amount'] * $discount['quantity'];
                    }
                }

                if ($discount['discount_type'] == 'percentage' || $discount['discount_type'] == '') {
                    $discounts_final[$id_pbp_bundle]['total_discount_tax_exc'] += ($discount['price_original_tax_exc'] * ($discount['discount_amount'] / 100)) * $discount['quantity'];
                    $discounts_final[$id_pbp_bundle]['total_discount_tax_inc'] += ($discount['price_original_tax_inc'] * ($discount['discount_amount'] / 100)) * $discount['quantity'];
                }

                //$discounts_final[$id_pbp_bundle]['total_discount_tax_exc'] = PBPUtilityHelper::convertPriceFull($discounts_final[$id_pbp_bundle]['total_discount_tax_exc'], $currency_default, $currency_default);
                //$discounts_final[$id_pbp_bundle]['total_discount_tax_inc'] = PBPUtilityHelper::convertPriceFull($discounts_final[$id_pbp_bundle]['total_discount_tax_inc'], $currency_default, $currency_default);
            }
        }

        unset($discount);

        //Apply the vouchers to the cart
        PBPVoucherHelper::clearVoucher($id_cart);
        foreach($discounts_final as $discount) {
            PBPVoucherHelper::addVoucher($id_cart, $id_customer, 365, $discount['total_discount_tax_inc'], $currency_default->id, $discount['names']);
        }
    }

    /**
     * Hook called after product has been deleted
     * @param $params
     */
    public function hookActionObjectProductInCartDeleteAfter($params)
    {
        PBPCartProductExtraHelper::deleteProduct($params['id_product'], $params['id_product_attribute'], $params['id_cart']);
    }

    /**
     * Called when cart is changed by the customer
     * @param $params
     */
    public function hookActionCartSave($params)
    {
        if (empty(Context::getContext()->cart)) {
            return false;
        }

        $qty = (int)Tools::getValue('qty');
        $op = Tools::getValue('op');
        $id_product = Tools::getValue('id_product');
        $id_product_attribute = Tools::getValue('id_product_attribute');
        $id_cart = Context::getContext()->cart->id;

        //determine if this is a parent product in cart extra
        $parents = PBPCartProductExtraHelper::getAllParentsByProduct($id_product, $id_product_attribute, $id_cart);
        // get all children of this product that have identical ID and IPA and remove extra quantities from the module data table
        $children = PBPCartProductExtraHelper::getAllChildrenByProduct($id_product, $id_product_attribute, $id_cart);
        $cart_product = PBPCartProductHelper::getCartProduct($id_product, $id_product_attribute, $id_cart);

        if (Tools::getValue('delete') == '1' || Tools::getValue('delete') == 'true') {
            PBPCartProductExtraHelper::deleteBundlesFromParentsArray($parents, $id_cart, 0);
            PBPCartProductExtraHelper::deleteFromProductsArray($children, $id_cart, 0);
        }

        if ($op == 'down') {
            if ($cart_product['quantity'] == 0 || empty($cart_product['quantity'])) {
                PBPCartProductExtraHelper::deleteBundlesFromParentsArray($parents, $id_cart, $qty);
                PBPCartProductExtraHelper::deleteFromProductsArray($children, $id_cart, $qty);
            } else {
                $quantity_decreased_by = (int)Tools::getValue('qty');
                if ($quantity_decreased_by == 0) {
                    $quantity_decreased_by = 1;
                }

                // quantity of parents product is being decreased
                if (!empty($parents)) {
                    $count = 0;
                    foreach ($parents as $parent) {
                        $p_children = PBPCartProductExtraHelper::getChildren($parent['id_pbp_cart_productextra'], $id_cart);

                        foreach ($p_children as $p_child) {
                            PBPCartProductExtraHelper::deleteById($p_child['id_pbp_cart_productextra'], $id_cart);
                        }
                        PBPCartProductExtraHelper::deleteById($parent['id_pbp_cart_productextra'], $id_cart);

                        $count++;
                        if ($count >= $quantity_decreased_by) {
                            break;
                        }
                    }
                }

                // quantity of child product is being decreased
                $count = 0;
                if (!empty($children)) {
                    foreach ($children as $child) {
                        PBPCartProductExtraHelper::deleteById($child['id_pbp_cart_productextra'], $id_cart);
                        PBPCartProductExtraHelper::deleteById($child['id_pbp_cart_productextra_parent'], $id_cart);
                        $count++;
                        if ($count >= $quantity_decreased_by) {
                            break;
                        }
                    }
                }
            }
        }
    }
}
