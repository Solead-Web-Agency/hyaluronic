<?php
/**
* Price increment/Reduction by groups, categories and more
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*/

if (!class_exists('ProductController', false)) {
class ProductController extends ProductControllerCore
{
    protected function assignAttributesGroups()
    {
        if (!Module::isEnabled('groupinc')) {
            return parent::assignAttributesGroups();
        }
        $colors = array();
        $groups = array();
        $attributes_groups = $this->product->getAttributesGroups($this->context->language->id);
        if (is_array($attributes_groups) && $attributes_groups) {
            $combination_images = $this->product->getCombinationImages($this->context->language->id);
            $combination_prices_set = array();
            foreach ($attributes_groups as $k => $row) {
                if (isset($row['is_color_group']) && $row['is_color_group'] && (isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) {
                    $colors[$row['id_attribute']]['value'] = $row['attribute_color'];
                    $colors[$row['id_attribute']]['name'] = $row['attribute_name'];
                    if (!isset($colors[$row['id_attribute']]['attributes_quantity'])) {
                        $colors[$row['id_attribute']]['attributes_quantity'] = 0;
                    }
                    $colors[$row['id_attribute']]['attributes_quantity'] += (int)$row['quantity'];
                }
                if (!isset($groups[$row['id_attribute_group']])) {
                    $groups[$row['id_attribute_group']] = array(
                        'group_name' => $row['group_name'],
                        'name' => $row['public_group_name'],
                        'group_type' => $row['group_type'],
                        'default' => -1,
                    );
                }
                $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
                if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                    $groups[$row['id_attribute_group']]['default'] = (int)$row['id_attribute'];
                }
                if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']])) {
                    $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
                }
                $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)$row['quantity'];
                $combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
                $combinations[$row['id_product_attribute']]['attributes'][] = (int)$row['id_attribute'];
                $combinations[$row['id_product_attribute']]['price'] = (float)Tools::convertPriceFull($row['price'], null, Context::getContext()->currency);
                if (!isset($combination_prices_set[(int)$row['id_product_attribute']])) {
                    Product::getPriceStatic((int)$this->product->id, false, $row['id_product_attribute'], 6, null, false, true, 1, false, null, null, null, $combination_specific_price);
                    $combination_prices_set[(int)$row['id_product_attribute']] = true;
                    $combinations[$row['id_product_attribute']]['specific_price'] = $combination_specific_price;
                }
                if (Module::isEnabled('groupinc')) {
                    $id_customer = $this->context->cart->id_customer;
                    $priceDisplay = Product::getTaxCalculationMethod((int)$id_customer);
                    if ($priceDisplay == 0) {
                        $product_price = Product::getPriceStatic((int)$this->product->id, true, $row['id_product_attribute'], 6, null, false, true, 1);
                        $old_price = Product::getPriceStatic((int)$this->product->id, true, $row['id_product_attribute'], 6, null, false, false, 1);
                    } else {
                        $product_price = Product::getPriceStatic((int)$this->product->id, false, $row['id_product_attribute'], 6, null, false, true, 1);
                        $old_price = Product::getPriceStatic((int)$this->product->id, false, $row['id_product_attribute'], 6, null, false, false, 1);
                    }
                    $combinations[$row['id_product_attribute']]['price_modified'] = (float)$product_price;
                    if ($old_price > $product_price) {
                        $combinations[$row['id_product_attribute']]['old_price'] = (float)$old_price;
                    }
                    include_once(_PS_MODULE_DIR_.'groupinc/classes/GroupincConfiguration.php');
                    $groupinc = new GroupincConfiguration();
                    $id_shop = $this->context->cart->id_shop;
                    $id_currency = $this->context->cart->id_currency;
                    $id_address_delivery = $this->context->cart->id_address_delivery;
                    $address = new Address($id_address_delivery);
                    $id_country = $address->id_country;
                    if ($id_country == 0) {
                        $id_country = $this->context->country->id;
                    }
                    $id_state = $address->id_state;
                    $id_product = $this->product->id;
                    $configs_onsale_show_discounts = $groupinc->getGIConfigurations($id_shop, $id_product, $id_customer, $id_country, $id_state, $id_currency, $this->context->language->id, true, true, $row['id_product_attribute'], (int)$row['quantity'], false, true);
                    if (!empty($configs_onsale_show_discounts)) {
                        $combinations[$row['id_product_attribute']]['on_sale'] = 1;
                    }
                    $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $this->context));
                    $ptc = $tax_manager->getTaxCalculator();
                    $product = new Product($id_product);
                    $product_price_without_tax = $product->price;
                    $product_price_with_tax = $ptc->addTaxes($product_price_without_tax);
                    $configs_qd = $groupinc->getGIConfigurations($id_shop, $id_product, $id_customer, $id_country, $id_state, $id_currency, $this->context->language->id, false, true, $row['id_product_attribute'], (int)$row['quantity'], true, false);
                    if (!empty($configs_qd)) {
                        $qds = $groupinc->getQuantityDiscounts($configs_qd, $id_product, false, $product_price_with_tax, $product_price_without_tax, $ptc);
                        if (!empty($qds)) {
                            $count = 0;
                            foreach ($qds as $c) {
                                if ((int)$c['id_product_attribute'] == (int)$row['id_product_attribute'] || (int)$c['id_product_attribute'] == 0) {
                                    $combinations[$row['id_product_attribute']]['quantities'][$count]['qty'] = $c['from_quantity'];
                                    $combinations[$row['id_product_attribute']]['quantities'][$count]['price_modified_quantity'] = $old_price * (1 - $c['reduction']);
                                    $combinations[$row['id_product_attribute']]['quantities'][$count]['reduction'] = $c['reduction'] * 100;
                                    $combinations[$row['id_product_attribute']]['quantities'][$count]['reduction_type'] = $c['reduction_type'];
                                    $count++;
                                }
                            }
                        }
                    }
                }
                $combinations[$row['id_product_attribute']]['ecotax'] = (float)$row['ecotax'];
                $combinations[$row['id_product_attribute']]['weight'] = (float)$row['weight'];
                $combinations[$row['id_product_attribute']]['quantity'] = (int)$row['quantity'];
                $combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
                $combinations[$row['id_product_attribute']]['unit_impact'] = Tools::convertPriceFull($row['unit_price_impact'], null, Context::getContext()->currency);
                $combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
                if ($row['available_date'] != '0000-00-00' && Validate::isDate($row['available_date'])) {
                    $combinations[$row['id_product_attribute']]['available_date'] = $row['available_date'];
                    $combinations[$row['id_product_attribute']]['date_formatted'] = Tools::displayDate($row['available_date']);
                } else {
                    $combinations[$row['id_product_attribute']]['available_date'] = $combinations[$row['id_product_attribute']]['date_formatted'] = '';
                }
                if (!isset($combination_images[$row['id_product_attribute']][0]['id_image'])) {
                    $combinations[$row['id_product_attribute']]['id_image'] = -1;
                } else {
                    $combinations[$row['id_product_attribute']]['id_image'] = $id_image = (int)$combination_images[$row['id_product_attribute']][0]['id_image'];
                    if ($row['default_on']) {
                        if (isset($this->context->smarty->tpl_vars['cover']->value)) {
                            $current_cover = $this->context->smarty->tpl_vars['cover']->value;
                        }
                        if (is_array($combination_images[$row['id_product_attribute']])) {
                            foreach ($combination_images[$row['id_product_attribute']] as $tmp) {
                                if ($tmp['id_image'] == $current_cover['id_image']) {
                                    $combinations[$row['id_product_attribute']]['id_image'] = $id_image = (int)$tmp['id_image'];
                                    break;
                                }
                            }
                        }
                        if ($id_image > 0) {
                            if (isset($this->context->smarty->tpl_vars['images']->value)) {
                                $product_images = $this->context->smarty->tpl_vars['images']->value;
                            }
                            if (isset($product_images) && is_array($product_images) && isset($product_images[$id_image])) {
                                $product_images[$id_image]['cover'] = 1;
                                $this->context->smarty->assign('mainImage', $product_images[$id_image]);
                                if (count($product_images)) {
                                    $this->context->smarty->assign('images', $product_images);
                                }
                            }
                            if (isset($this->context->smarty->tpl_vars['cover']->value)) {
                                $cover = $this->context->smarty->tpl_vars['cover']->value;
                            }
                            if (isset($cover) && is_array($cover) && isset($product_images) && is_array($product_images)) {
                                $product_images[$cover['id_image']]['cover'] = 0;
                                if (isset($product_images[$id_image])) {
                                    $cover = $product_images[$id_image];
                                }
                                $cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id.'-'.$id_image) : (int)$id_image);
                                $cover['id_image_only'] = (int)$id_image;
                                $this->context->smarty->assign('cover', $cover);
                            }
                        }
                    }
                }
            }
            if (!Product::isAvailableWhenOutOfStock($this->product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
                foreach ($groups as &$group) {
                    foreach ($group['attributes_quantity'] as $key => &$quantity) {
                        if ($quantity <= 0) {
                            unset($group['attributes'][$key]);
                        }
                    }
                }
                foreach ($colors as $key => $color) {
                    if ($color['attributes_quantity'] <= 0) {
                        unset($colors[$key]);
                    }
                }
            }
            foreach ($combinations as $id_product_attribute => $comb) {
                $attribute_list = '';
                foreach ($comb['attributes'] as $id_attribute) {
                    $attribute_list .= '\''.(int)$id_attribute.'\',';
                }
                $attribute_list = rtrim($attribute_list, ',');
                $combinations[$id_product_attribute]['list'] = $attribute_list;
            }
            $this->context->smarty->assign(array(
                'groups' => $groups,
                'colors' => (count($colors)) ? $colors : false,
                'combinations' => $combinations,
                'combinationImages' => $combination_images
            ));
        }
    }

    public function initContent()
    {
        if (Module::isEnabled('groupinc')) {
            include_once(_PS_MODULE_DIR_.'groupinc/classes/GroupincConfiguration.php');
            $this->product = GroupincConfiguration::changeProductProperties($this->product);
        }
        return parent::initContent();
    }
}
}