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

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class PBPFrontProductController extends Module
{

    protected $sibling;

    public function __construct(&$sibling)
    {
        parent::__construct();

        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }
    }

    public function setMedia()
    {
        if (Context::getContext()->controller->php_self == 'product' || Context::getContext()->controller->php_self == 'category' || Context::getContext()->controller->php_self == 'search' || Context::getContext()->controller->php_self == 'index') {
            $this->sibling->context->controller->addJquery();
            $this->sibling->context->controller->addJS($this->sibling->_path . 'views/js/front/PBPFrontProductController.js');
            $this->sibling->context->controller->addJS($this->sibling->_path . 'views/js/lib/Tools.js');
            $this->sibling->context->controller->addCSS($this->sibling->_path . 'views/css/front/product.css');

            if (Configuration::get('pbp_slider') == 1) {
                $this->sibling->context->controller->addJS($this->sibling->_path . 'views/js/lib/splide/dist/js/splide.min.js');
                $this->sibling->context->controller->addCSS($this->sibling->_path . 'views/js/lib/splide/dist/css/splide.min.css');
            }
        }
    }

    /**
     * Format a float to currency format with symbol
     * @param $price
     * @return string
     */
    private function _formatPrice($price)
    {
        $priceFormatter = new PriceFormatter();
        return $priceFormatter->format($price);
    }

    /**
     * Format a float to currency format with symbol
     * @param $price
     * @return string
     */
    private function _convertAndformatPrice($price)
    {
        $priceFormatter = new PriceFormatter();
        return $priceFormatter->convertAndFormat($price);
    }

    private function convertAmount($price)
    {
        $priceFormatter = new PriceFormatter();
        return $priceFormatter->convertAmount($price);
    }


    /**
     * Apply a discount to a price
     * @param $price
     * @param $amount
     * @param $type
     * @return mixed
     */
    private function _applyDiscount($price, $amount, $type)
    {
        if ($type == 'percentage') {
            $price = $price - (($price / 100) * $amount);
        }
        if ($type == 'money') {
            $price = $price - $amount;
        }
        return $price;
    }


    /**
     * Add script initialisation vars for the PPBS widgt which will be loaded via ajax
     * @param $params
     * @return bool
     */
    public function hookDisplayFooter($params)
    {
        if (Context::getContext()->controller->php_self != 'product') {
            return false;
        }

        $pbp_product_option_model = new PBPProductOptionModel();
        $pbp_product_option_model->load((int)Tools::getValue('id_product'));

        $pbp_general = [];
        $pbp_general['pbp_location'] = Configuration::get('pbp_location');
        $pbp_general['pbp_slider'] = Configuration::get('pbp_slider');
        $pbp_general['disabled_addtocart'] = $pbp_product_option_model->disabled_addtocart;

        $this->sibling->smarty->assign(array(
            'id_shop' => (int)Context::getContext()->shop->id,
            'action' => Tools::getValue('action'),
            'pbp_general' => $pbp_general
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/front/product_footer.tpl');
    }

    /**
     * Displ;ay the module on the product page
     * @param $module_file
     * @return string
     */
    public function renderWidget()
    {
        $parent_product_info = PBPProductHelper::getProductInfo(Tools::getValue('id_product'), Tools::getValue('group'));
        $pbp_general = [];
        $pbp_general['pbp_location'] = Configuration::get('pbp_location');
        $pbp_general['pbp_parent_attr_display'] = Configuration::get('pbp_parent_attr_display');
        $pbp_general['pbp_display_bundle_total'] = Configuration::get('pbp_display_bundle_total');
        $pbp_general['pbp_show_bundle_quantity'] = Configuration::get('pbp_show_bundle_quantity');;
        $pbp_general['pbp_slider'] = Configuration::get('pbp_slider');;

        $id_lang = Context::getContext()->language->id;
        $id_shop = (int)Tools::getValue('id_shop');

        $tabs_collection = array();

        $bundle = new PBPBundleModel();
        $bundles_collection = $bundle->getByProduct(Tools::getValue('id_product'), true, $id_shop);

        //determine if prices should include tax or not
        $include_tax = true;
        if (!empty(Context::getContext()->customer->id_default_group)) {
            $id_group = Context::getContext()->customer->id_default_group;
            if (Group::getPriceDisplayMethod($id_group) == 0) {
                $include_tax = true;
            } else {
                $include_tax = false;
            }
        }

        foreach ($bundles_collection as $bundle) {
            $tab = new PBPTabLangModel();
            $tab->load($bundle->id_tab, $id_lang);

            if (!empty($tab->id)) {
                if (empty($tabs_collection[$tab->id])) {
                    $tab->bundles[] = $bundle;
                    $tabs_collection[$tab->id] = $tab;
                } else {
                    $tabs_collection[$tab->id]->bundles[] = $bundle;
                }
            }
        }

		$parent_product = new Product(Tools::getValue('id_product'), $include_tax, $this->context->language->id, $this->context->shop->id);

        if (!PBPProductHelper::isProductAvailable($parent_product)) {
            return false;
        }

        if (!empty($parent_product->link_rewrite[Context::getContext()->language->id])) {
			$parent_product->link_rewrite = $parent_product->link_rewrite[Context::getContext()->language->id];
		}
		
        $parent_cover_image = $parent_product->getCover($parent_product->id);
        $parent_product->cover_image = $parent_cover_image['id_image'];
        $parent_product->url_image = Context::getContext()->link->getImageLink($parent_product->link_rewrite, $parent_product->cover_image, ImageType::getFormattedName('home'));

        if (!empty(Tools::getValue('group'))) {
            $parent_product_price = Product::getPriceStatic(Tools::getValue('id_product'), $include_tax, $parent_product_info['id_product_attribute']);
        } else {
            $parent_product_price = Product::getPriceStatic(Tools::getValue('id_product'), $include_tax, Product::getDefaultAttribute(Tools::getValue('id_product')));
        }

        /* Assign Images and attributes to each of the child products in each bundle */
        $link = new Link();
        if (!empty($tabs_collection)) {
            foreach ($tabs_collection as $tab) {
                foreach ($tab->bundles as $bundle) {
                    $bundle->available = true;
                    $bundle_price_discounted = $parent_product_price;
                    $bundle_price_original = $parent_product_price;

                    if ($bundle->discount_enabled || $bundle->parent_product_discount_amount > 0) {
                        if ($bundle->discount_enabled) {
                            $discount_amount = $bundle->discount_amount;
                            $discount_type = $bundle->discount_type;
                        } elseif ($bundle->parent_product_discount_amount > 0) {
                            $discount_amount = $bundle->parent_product_discount_amount;
                            $discount_type = $bundle->parent_product_discount_type;
                        }

                        if ($discount_type == 'percentage') {
                            $parent_discounted_price = $parent_product_price * (1 - ($discount_amount / 100));
                        } else {
                            $parent_discounted_price = $parent_product_price - $discount_amount;
                        }
                        $bundle_price_discounted = $parent_discounted_price;
                        $parent_saving = $parent_product_price - $parent_discounted_price;
                        $parent_product->original_price_formatted = $this->_formatPrice($parent_product_price);
                        $parent_product->discounted_price_formatted = $this->_formatPrice($parent_discounted_price);
                        $parent_product->discount_saving_formatted = $this->_formatPrice($parent_saving);
                        $parent_product->discount_saving = $parent_saving;

                        $bundle->parent_product_discount = new stdClass();
                        $bundle->parent_product_discount->original_price_formatted = $this->_formatPrice($parent_product_price);
                        $bundle->parent_product_discount->discounted_price_formatted = $this->_formatPrice($parent_discounted_price);
                        $bundle->parent_product_discount->discount_saving = $parent_saving;
                        $bundle->parent_product_discount->discount_saving_formatted = $parent_product->discount_saving_formatted;
                        $bundle->parent_product_discount->discount_saving = $parent_saving;
                        $bundle->parent_product_discount->discount_type = $bundle->parent_product_discount_type;
                        $bundle->parent_product_discount->discount_amount = $bundle->parent_product_discount_amount;
                    }

                    $i = 1;
                    if (!empty($bundle->products) && is_array($bundle->products)) {
                        foreach ($bundle->products as &$bundle_product) {
                            $product = new Product($bundle_product->id_product, true, $this->context->language->id, $this->context->shop->id);

                            if (!PBPProductHelper::isProductAvailable($product)) {
                                if ($bundle_product->allow_oos == 0) {
                                    $bundle->available = false;
                                }
                            }

                            $product->id_product_attribute = Product::getDefaultAttribute($product->id);

                            /* get Image */
                            $cover_image = $product->getCover($bundle_product->id_product);

                            $bundle_product->id_unique = $i;
                            $bundle_product->id_image = $cover_image['id_image'];
                            $bundle_product->link_rewrite = $product->link_rewrite;
                            $bundle_product->name = $product->name;
                            $bundle_product->url = $link->getProductLink($product);
                            $bundle_product->url_image = Context::getContext()->link->getImageLink($bundle_product->link_rewrite, $bundle_product->id_image, ImageType::getFormattedName('home'));

                            $attributes_groups = $product->getAttributesGroups($this->context->language->id);
                            $groups = array();

                            foreach ($attributes_groups as $attribute_group) {
                                if ($attribute_group['quantity'] > 0 || $bundle_product->allow_oos == 1 || !PBPConfigHelper::isStockManagementEnabled()) {
                                    $ipa = $attribute_group['id_product_attribute'];
                                    $groups[$ipa]['id_product_attribute'] = $ipa;
                                    if (!isset($groups[$ipa]['label'])) {
                                        $groups[$ipa]['label'] = $attribute_group['public_group_name'] . ':' . $attribute_group['attribute_name'] . ', ';
                                    } else {
                                        $groups[$ipa]['label'] .= $attribute_group['public_group_name'] . ':' . $attribute_group['attribute_name'] . ', ';
                                    }

                                    if ($attribute_group['default_on']) {
                                        $groups[$ipa]['default'] = '1';
                                    } else {
                                        $groups[$ipa]['default'] = '0';
                                    }
                                    $groups[$ipa]['label'] = rtrim($groups[$ipa]['label'], ', ');
                                }
                            }

                            $bundle_product->attribute_groups = $groups;
                            $bundle_product->product->id_product = $bundle_product->id_product;
                            $bundle_product->product->price = Product::getPriceStatic($bundle_product->product->id_product, $include_tax);

                            if ($bundle->discount_enabled) {
                                $bundle_product->product->discount_price = $bundle_product->product->price * (1 - ($bundle->discount_amount / 100));
                            } else {
                                if ($bundle_product->discount_type == 'money') {
                                    $bundle_product->discount_amount = $this->convertAmount($bundle_product->discount_amount);

                                    if ($include_tax) {
                                        $bundle_product->product->discount_price = $bundle_product->product->price - ($bundle_product->discount_amount * (1 + ($product->tax_rate / 100)));
                                    } else {
                                        $bundle_product->product->discount_price = $bundle_product->product->price - $bundle_product->discount_amount;
                                    }
                                }

                                if ($bundle_product->discount_type == 'percentage') {
                                    $bundle_product->product->discount_price = $bundle_product->product->price * (1 - ($bundle_product->discount_amount / 100));
                                }
                            }

                            $bundle_product->product->price *= $bundle_product->qty;
                            $bundle_product->product->discount_price *= $bundle_product->qty;

                            $bundle_product->product->discount_saving = $bundle_product->product->price - $bundle_product->product->discount_price;
                            $bundle_price_discounted += $bundle_product->product->discount_price;
                            $bundle_price_original += $bundle_product->product->price;

                            $bundle_product->product->original_price = $bundle_product->product->price;
                            $bundle_product->product->discount_price_formatted = $this->_formatPrice($bundle_product->product->discount_price);
                            $bundle_product->product->discount_saving_formatted = $this->_formatPrice($bundle_product->product->discount_saving);
                            $bundle_product->product->original_price_formatted = $this->_formatPrice($bundle_product->product->price);
                            $i++;
                        }
                    }

                    $bundle->bundle_price_saving = $this->_formatPrice($bundle_price_original - $bundle_price_discounted);
                    $bundle->bundle_price_discounted = $this->_formatPrice($bundle_price_discounted);
                    $bundle->bundle_price_original_formatted = $this->_formatPrice($bundle_price_original);
                    $bundle->bundle_price_original = $bundle_price_original;
                    $bundle->bundle_price_saving_unformatted = $bundle_price_original - $bundle_price_discounted;

                    // calculate discount for bundle if necessary
                    if ($bundle->discount_enabled) {
                        if ($bundle->discount_type == 'percentage') {
                            $bundle_price_discounted = $bundle->bundle_price_original * (1 - ($bundle->discount_amount / 100));
                        } else {
                            $bundle_price_discounted = $bundle->bundle_price_original - $bundle->discount_amount;
                        }
                        $bundle->bundle_price_discounted = $this->_formatPrice($bundle_price_discounted);
                        $bundle->bundle_price_original_formatted = $this->_formatPrice($bundle_price_original);
                        $bundle->bundle_price_original = $bundle_price_original;
                        $bundle->bundle_price_saving = $this->_formatPrice($bundle_price_original - $bundle_price_discounted);
                        $bundle->bundle_price_saving_unformatted = $bundle_price_original - $bundle_price_discounted;
                    }
                }
            }
        }

        /* clean up tabs, remove bundles with no products */
        $tabs_collection_final = array();
        foreach ($tabs_collection as $tab) {
            $bundles = array();
            foreach ($tab->bundles as $bundle) {
                if (count($bundle->products) > 0 && $bundle->available) {
                    $bundles[] = $bundle;
                }
            }
            $layout = PBPConfigHelper::getTabBundleLayout($tab->id_tab);
            if ($layout == PBPConfigHelper::$LAYOUT_HALF_WIDTH) {
                $tab->layout = 'pbp-widget-half-width';
            } else {
                $tab->layout = 'pbp-widget-full-width';
            }
            $tabs_collection_final[$tab->id_pbp_tab] = $tab;
            $tabs_collection_final[$tab->id_pbp_tab]->bundles = $bundles;
        }

        if (empty($tabs_collection_final)) {
            return false;
        }

        /* Add attributes to parent product if we need to */
        $attributes_groups = array();
        if ($pbp_general['pbp_parent_attr_display']) {
            $attributes_groups = $parent_product->getAttributesGroups($this->context->language->id);
            $groups = array();

            foreach ($attributes_groups as $attribute_group) {
                if ($attribute_group['quantity'] > 0 || !PBPConfigHelper::isStockManagementEnabled()) {
                    $ipa = $attribute_group['id_product_attribute'];
                    $groups[$ipa]['id_product_attribute'] = $ipa;
                    $groups[$ipa]['default'] = $attribute_group['default_on'];
                    if (!isset($groups[$ipa]['label'])) {
                        $groups[$ipa]['label'] = $attribute_group['group_name'] . ':' . $attribute_group['attribute_name'] . ', ';
                    } else {
                        $groups[$ipa]['label'] .= $attribute_group['group_name'] . ':' . $attribute_group['attribute_name'] . ', ';
                    }
                }
            }
            $parent_product->attribute_groups = $groups;
        }

        $layout = Configuration::get('pbp_layout');

        if (empty($layout)) {
            $layout = 'pbp-widget-full-width';
        }

        // determine if we should display as slider or not
        reset($tabs_collection_final);
		$first_tab = current($tabs_collection_final);
		
        if (empty($first_tab)) {
            return false;
        }

        if (count($first_tab->bundles) == 1) {
            $pbp_general['pbp_slider'] = 0;
        }

        $this->sibling->module->smarty->assign(array(
            'layout' => $layout,
            'location' => Configuration::get('pbp_location'),
            'id_product' => Tools::getValue('id_product'),
            'tabs_collection' => $tabs_collection_final,
            'parent_product' => $parent_product,
            'id_lang' => Context::getContext()->language->id,
            'pbp_general' => $pbp_general
        ));
        return $this->sibling->module->display($this->sibling->module->module_file, 'views/templates/front/widget.tpl');
    }

    /**
     * Add a normal product to the cart
     * @param $id_product
     * @param $id_product_attribute
     * @param $quantity
     * @return PBPCartProductModel
     */
    protected function addNormalProductToCart($id_product, $id_product_attribute, $quantity)
    {
        $cart = Context::getContext()->cart;
        $pbp_cart_product_model = new PBPCartProductModel();

        if (empty($id_product_attribute)) {
            $id_product_attribute = Product::getDefaultAttribute($id_product);
        }
        $pbp_cart_product_model->load(Context::getContext()->cart->id, $id_product, $id_product_attribute, Context::getContext()->shop->id);

        if (empty($pbp_cart_product_model->id_cart)) {
            $pbp_cart_product_model->id_product = (int)$id_product;
            $pbp_cart_product_model->id_product_attribute = (int)$id_product_attribute;
            $pbp_cart_product_model->id_cart = (int)$cart->id;
            $pbp_cart_product_model->id_address_delivery = (int)$cart->id_address_delivery;
            $pbp_cart_product_model->id_shop = (int)Context::getContext()->shop->id;
            $pbp_cart_product_model->quantity = (int)$quantity;
            $pbp_cart_product_model->date_add = date('Y-m-d H:i:s');
            $pbp_cart_product_model->add();
        } else {
            // increase quantity
            $pbp_cart_product_model->quantity = $pbp_cart_product_model->quantity + $quantity;
            $pbp_cart_product_model->updateQty();
        }
        return $pbp_cart_product_model;
    }

    /**
     * Add a single product as part of a bundle to the cart
     * @param $product_cart_data
     * @param null $parent_product_cart_data
     * @return bool
     */
    protected function addBundleProductToCart($product_cart_data, $parent_product_cart_data = null)
    {
        $cart = Context::getContext()->cart;
        $pbp_cart_product_model = new PBPCartProductModel();

        if (empty($product_cart_data['ipa'])) {
            $product_cart_data['ipa'] = Product::getDefaultAttribute($product_cart_data['id_product']);
        }

        $pbp_cart_product_model->load(Context::getContext()->cart->id, $product_cart_data['id_product'], $product_cart_data['ipa'], Context::getContext()->shop->id);

        $pbp_product_model = new PBPProductModel();
        $pbp_product_model->load($product_cart_data['id_product'], Tools::getValue('id_bundle'), Context::getContext()->shop->id);

        if (empty($pbp_cart_product_model->id_cart)) {
            $pbp_cart_product_model->id_product = $product_cart_data['id_product'];
            $pbp_cart_product_model->id_product_attribute = $product_cart_data['ipa'];
            $pbp_cart_product_model->id_cart = $cart->id;
            $pbp_cart_product_model->id_address_delivery = $cart->id_address_delivery;
            $pbp_cart_product_model->id_shop = Context::getContext()->shop->id;
            $pbp_cart_product_model->quantity = $pbp_product_model->qty;
            $pbp_cart_product_model->date_add = date('Y-m-d H:i:s');

            if (isset($parent_product_cart_data)) {
                $pbp_cart_product_model->id_pbp_bundle = $pbp_product_model->id_bundle;
                $pbp_cart_product_model->id_parent_pbp_product = $parent_product_cart_data['id_product'];
                $pbp_cart_product_model->id_parent_pbp_product_ipa = $parent_product_cart_data['ipa'];
            }

            if ((int)$pbp_product_model->qty == 0) {
                $pbp_cart_product_model->quantity = 1;
            }
            $pbp_cart_product_model->add();
        } else {
            // increase quantity
            $pbp_cart_product_model->quantity = $pbp_cart_product_model->quantity + $pbp_product_model->qty;
            $pbp_cart_product_model->updateQty();
        }
    }

    /**
     * Get Product Information such as prices, tax etc based on id_product and id_product_attribute
     */
    public function getProductInfo()
    {
        return (Tools::jsonEncode(PBPProductHelper::getProductInfo(Tools::getValue('id_product'), Tools::getValue('group'))));
    }

    /**
     * Gets the total bundle price and bundle savings information to update front end (ajax request when attributes change)
     */
    public function getBundlePrices()
    {
        $json_return = [];
        $json_return['bundle_products'] = [];
        $id_product_parent = (int)Tools::getValue('pbp_cart_parent_product')['id_product'];
        $ipa_parent = (int)Tools::getValue('pbp_cart_parent_product')['ipa'];
        $pbp_bundle = new PBPBundleModel();
        $pbp_bundle->loadSingle((int)Tools::getValue('id_bundle'), $id_product_parent);

        // get price of parent product
        $product = new Product(Tools::getValue('pbp_cart_parent_product')['id_product'], true);
        $price = Product::getPriceStatic($id_product_parent, false, $ipa_parent);
        $parent_product_original_price = $price;
        $parent_product_discount_price = $price;

        $bundle_price = $price;
        $bundle_price_original = $price;

        if ($pbp_bundle->discount_enabled) {
            $bundle_price = $bundle_price * (1 - ($pbp_bundle->discount_amount / 100));
            $parent_product_discount_price = $price * (1 - ($pbp_bundle->discount_amount / 100));
        }

        // parent product price
        $parent_product_original_price = $parent_product_original_price + ($parent_product_original_price * ($product->tax_rate / 100));
        $parent_product_discount_price = $parent_product_discount_price + ($parent_product_discount_price * ($product->tax_rate / 100));
        $json_return['parent_product'] = array(
            'price_original' => $parent_product_original_price,
            'price_discount' => $parent_product_discount_price,
            'price_original_formatted' => Tools::displayPrice($parent_product_original_price, Currency::getCurrencyInstance((Context::getContext()->cart->id_currency), false)),
            'price_discount_formatted' => Tools::displayPrice($parent_product_discount_price, Currency::getCurrencyInstance((Context::getContext()->cart->id_currency), false)),
        );

        // get prices of products in the bundle along with discount information
        foreach (Tools::getValue('pbp_cart_products') as $bundle_product_post) {
            // apply discount
            $pbp_bundle_product = new PBPProductModel();
            $pbp_bundle_product->load($bundle_product_post['id_product'], (int)Tools::getValue('id_bundle'), Context::getContext()->shop->id);
            $ipa_bundle_product = 0;

            if (!empty($bundle_product_post['ipa'])) {
                $ipa_bundle_product = $bundle_product_post['ipa'];
            }
            $bundle_product_price = Product::getPriceStatic($bundle_product_post['id_product'], false, $ipa_bundle_product);
            $bundle_product_price_original = $bundle_product_price;
            $bundle_price_original += $bundle_product_price_original *= $pbp_bundle_product->qty;

            if ($pbp_bundle->discount_enabled) {
                $bundle_product_price = $bundle_product_price * (1 - ($pbp_bundle->discount_amount / 100));
            } else {
                if ($pbp_bundle_product->discount_type == 'money') {
                    $bundle_product_price = $bundle_product_price - $pbp_bundle_product->discount_amount;
                }

                if ($pbp_bundle_product->discount_type == 'percentage') {
                    $bundle_product_price = $bundle_product_price * (1 - ($pbp_bundle_product->discount_amount / 100));
                }
            }
            $bundle_product_price *= $pbp_bundle_product->qty;
            $bundle_price += $bundle_product_price;

            $offer_price = $bundle_product_price + ($bundle_product_price * ($product->tax_rate / 100));#
            $saving = $bundle_product_price_original - $bundle_product_price;
            $saving = $saving + ($saving * ($product->tax_rate / 100));
            $saving_percent = round((($bundle_product_price_original - $bundle_product_price) / ($bundle_product_price_original)) * 100, 0);

            $original_price = $bundle_product_price_original + ($bundle_product_price_original * ($product->tax_rate / 100));

            $json_return['bundle_products'][] = array(
                'id_unique' => $bundle_product_post['id_unique'],
                'id_product' => $bundle_product_post['id_product'],
                'original_price' => $original_price,
                'original_price_formatted' => Tools::displayPrice($original_price, Currency::getCurrencyInstance((Context::getContext()->cart->id_currency), false)),
                'offer_price' => $offer_price,
                'offer_price_formatted' => Tools::displayPrice($offer_price, Currency::getCurrencyInstance((Context::getContext()->cart->id_currency), false)),
                'saving' => $saving,
                'saving_percent' => $saving_percent,
                'saving_formatted' => Tools::displayPrice($saving, Currency::getCurrencyInstance((Context::getContext()->cart->id_currency), false))
            );
        }

        // ad the taxes
        $bundle_price = $bundle_price + ($bundle_price * ($product->tax_rate / 100));
        $bundle_price_original = $bundle_price_original + ($bundle_price_original * ($product->tax_rate / 100));

        $json_return['bundle_total'] = $bundle_price;
        $json_return['bundle_original_total'] = $bundle_price_original;
        $json_return['bundle_original_total_formatted'] = Tools::displayPrice($bundle_price_original, Currency::getCurrencyInstance((Context::getContext()->cart->id_currency), false));
        $json_return['bundle_total_formatted'] = Tools::displayPrice($bundle_price, Currency::getCurrencyInstance((Context::getContext()->cart->id_currency), false));
        $json_return['bundle_total_saving'] = $bundle_price_original - $bundle_price;
        $json_return['bundle_total_saving_formatted'] = Tools::displayPrice($bundle_price_original - $bundle_price, Currency::getCurrencyInstance((Context::getContext()->cart->id_currency), false));
        return $json_return;
    }

    /**
     * Process add bundle to cart
     */
    public function processAddToCart()
    {
        $id_cart = Context::getContext()->cart->id;
        $id_bundle = Tools::getValue('id_bundle');
        $id_shop = Context::getContext()->shop->id;
		$parent_product = Tools::getValue('pbp_cart_parent_product');
		$quantity = (int)Tools::getValue('quantity');

        if ($quantity == 0) {
            $quantity = 1;
        }

        if (empty($parent_product['ipa'])) {
            $parent_product['ipa'] = 0;
        }		

        if (empty(Tools::getValue('pbp_cart_products')) || empty($parent_product['id_product'])) {
            return false;
        }

        // Initialize a cart if we need to
        if (empty($id_cart)) {
            Context::getContext()->cart->add();
            if ($this->context->cart->id) {
                $this->context->cookie->id_cart = (int)$this->context->cart->id;
                $id_cart = $this->context->cookie->id_cart;
            } else {
                return false;
            }
        }

        for ($i=0; $i<$quantity; $i++) {
            // Now add the parent product to the basket too
            $cart_product_added = $this->addNormalProductToCart($parent_product['id_product'], $parent_product['ipa'], 1);

            // Add the extra parent product
            $pbp_cart_productextra = new PBPCartProductExtraModel();
            $pbp_cart_productextra->id_product_parent = 0;
            $pbp_cart_productextra->id_product_attribute_parent = 0;
            $pbp_cart_productextra->id_customization = 0;
            $pbp_cart_productextra->id_product = $parent_product['id_product'];
            $pbp_cart_productextra->id_product_attribute = $parent_product['ipa'];
            $pbp_cart_productextra->date_add_parent = $cart_product_added->date_add;
            $pbp_cart_productextra->id_cart = (int)$id_cart;
            $pbp_cart_productextra->quantity = 1;
            $pbp_cart_productextra->id_pbp_bundle = (int)Tools::getValue('id_bundle');
            $pbp_cart_productextra->add();
            $id_pbp_cart_productextra_parent = $pbp_cart_productextra->id;

            // Add the children
            foreach (Tools::getValue('pbp_cart_products') as $add_product) {
                $this->addBundleProductToCart($add_product, $parent_product);

                if (empty($add_product['ipa'])) {
                    $add_product['ipa'] = 0;
                }

                $pbp_product_model = new PBPProductModel();
                $pbp_product_model = new PBPProductModel($add_product['id_pbp_product']);

                $pbp_cart_productextra = new PBPCartProductExtraModel();
                $pbp_cart_productextra->id_pbp_cart_productextra_parent = (int)$id_pbp_cart_productextra_parent;
                $pbp_cart_productextra->id_product_parent = (int)$parent_product['id_product'];
                $pbp_cart_productextra->id_product_attribute_parent = (int)$parent_product['ipa'];
                $pbp_cart_productextra->id_customization = 0;
                $pbp_cart_productextra->date_add_parent = $cart_product_added->date_add;
                $pbp_cart_productextra->id_cart = (int)$id_cart;
                $pbp_cart_productextra->id_product = (int)$add_product['id_product'];
                $pbp_cart_productextra->id_product_attribute = (int)$add_product['ipa'];
                $pbp_cart_productextra->quantity = $pbp_product_model->qty;
                $pbp_cart_productextra->discount_amount = (float)$pbp_product_model->discount_amount;
                $pbp_cart_productextra->discount_type = pSQL($pbp_product_model->discount_type);
                $pbp_cart_productextra->discount_tax = pSQL($pbp_product_model->discount_tax);
                $pbp_cart_productextra->id_pbp_bundle = (int)$id_bundle;
                $pbp_cart_productextra->add();
            }
        }

        $front_cart_controller = new PBPFrontCartController($this);
        $front_cart_controller->calculateCartDiscount();

        $json = array();
        $json['redirect_url'] = $this->context->link->getPageLink('cart', true, null, array('action' => 'show'));
        return $json;
    }

    public function route()
    {
        switch (Tools::getValue('action')) {
            case 'renderwidget':
                return $this->renderWidget();

            case 'getproductinfo':
                die($this->getProductInfo());

            case 'getbundleprices':
                die(Tools::jsonEncode($this->getBundlePrices()));

            case 'processaddtocart':
                die(Tools::jsonEncode($this->processAddToCart()));
        }
    }
}
