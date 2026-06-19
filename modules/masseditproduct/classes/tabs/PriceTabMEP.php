<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    SeoSA <885588@bk.ru>
 * @copyright 2012-2019 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class PriceTabMEP extends BaseTabMEP
{
    const TYPE_PRICE_BASE = 0;
    const TYPE_PRICE_FINAL = 1;
    const TYPE_PRICE_WHOLESALE = 2;

    const CHANGE_FOR_PRODUCT = 0;
    const CHANGE_FOR_COMBINATION = 1;

    public $currency;
    public $type_price;
    public $action_price;
    public $price_value;
    public $not_change_final_price;
    public $country;
    public $address;
    public $id_shop;
    public $unity;
    public $unity_price;
    public $type_price_r;
    public $action_direction;
    public $action_rounding_value;

    public function __construct()
    {
        parent::__construct();
        $this->currency = Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $this->currency['decimals'] = 1;
        $this->type_price = (int)Tools::getValue('type_price');
        $this->action_price = (int)Tools::getValue('action_price');
        $this->price_value = (float)Tools::getValue('price_value');
        $this->not_change_final_price = Tools::getValue('not_change_final_price');
        $this->country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
        $this->address = new Address();
        $this->address->id_country = $this->country->id;
        $this->id_shop = Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP
            ? (int)$this->context->shop->id : 'p.id_shop_default';
        $this->unity = Tools::getValue('unity');
        $this->unity_price = Tools::getValue('unity_price');
        $this->type_price_r = Tools::getValue('type_price_r');
        $this->action_direction = Tools::getValue('action_direction');
        $this->action_rounding_value = Tools::getValue('action_rounding_value');
    }

    public function checkBeforeChange()
    {
        if ($this->checkAccessField('price') && !(float)$this->price_value) {
            LoggerMEP::getInstance()->error($this->l('Write value'));
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function getQueryProducts($ids)
    {
        $query_products = Db::getInstance()->executeS(
            'SELECT
				p.`id_product`,
				pss.`price`,
				p.`wholesale_price`,
				p.`unity`
			FROM ' . _DB_PREFIX_ . 'product p
			JOIN `' . _DB_PREFIX_ . 'product_shop` pss ON (p.`id_product` = pss.`id_product` AND pss.id_shop = ' . pSQL(
                $this->id_shop
            ) . ')
			WHERE p.`id_product` IN (' . pSQL(
                implode(',', array_map('intval', $ids))
            ) . ')'
        );
        return (is_array($query_products) ? $query_products : array());
    }

    public function applyChangeForProducts($products)
    {
        $return_products = array();
        $query_products = $this->getQueryProducts($products);

        if ($this->checkAccessField('price')) {
            foreach ($query_products as $product) {
                if ($this->type_price === self::TYPE_PRICE_WHOLESALE) {
                    $wholesale_price = $product['wholesale_price'];
                    $new_price = MassEditTools::actionPrice(
                        $wholesale_price,
                        $this->action_price,
                        $this->price_value
                    );

                    $return_products[$product['id_product']] = array(
                        'wholesale_price' => Tools::displayPrice($new_price, $this->currency),
                    );
                    MassEditTools::updateWholePriceProduct(
                        $product['id_product'],
                        $new_price
                    );
                } else {
                    $price = 0;

                    if ((int)Configuration::get('PS_TAX')) {
                        $tax_manager = TaxManagerFactory::getManager(
                            $this->address,
                            Product::getIdTaxRulesGroupByIdProduct((int)$product['id_product'], $this->context)
                        );
                        $product_tax_calculator = $tax_manager->getTaxCalculator();
                        $product['price_final'] = $product_tax_calculator->addTaxes($product['price']);
                        $product['rate'] = $tax_manager->getTaxCalculator()->getTotalRate();
                    } else {
                        $product['price_final'] = $product['price'];
                        $product['rate'] = 0;
                    }

                    if ($this->type_price === self::TYPE_PRICE_BASE) {
                        $price = $product['price'];
                    } else {
                        if ($this->type_price === self::TYPE_PRICE_FINAL) {
                            $price = $product['price_final'];
                        }
                    }
                    $price = MassEditTools::actionPrice(
                        $price,
                        $this->action_price,
                        $this->price_value
                    );

                    $final_price = 0;
                    if ($this->type_price === self::TYPE_PRICE_FINAL) {
                        $final_price = $price;
                        if (Configuration::get('PS_TAX')) {
                            $price = $price / (100 + (int)$product['rate']) * 100;
                        }
                    } else {
                        if ($this->type_price === self::TYPE_PRICE_BASE) {
                            if (Configuration::get('PS_TAX')) {
                                $final_price = $price + ($price / 100 * (int)$product['rate']);
                            } else {
                                $final_price = $price;
                            }
                        }
                    }
                    MassEditTools::updatePriceProduct($product['id_product'], $price);

                    $return_products[$product['id_product']] = array(
                        'price' => Tools::displayPrice($price, $this->currency),
                        'price_final' => Tools::displayPrice($final_price, $this->currency),
                    );
                }
            }
        } elseif ($this->checkAccessField('tax_rule_group') && is_array($query_products)) {
            foreach ($query_products as $query_product) {
                $price = $query_product['price'];
                if ($this->not_change_final_price) {
                    $product_obj = new Product((int)$query_product['id_product']);
                    $new_tax_rule_arr = TaxRule::getTaxRulesByGroupId(
                        Configuration::get('PS_LANG_DEFAULT'),
                        (int)Tools::getValue('id_tax_rules_group')
                    );
                    $price = (100 + $product_obj->getTaxesRate())
                        / (100 + $new_tax_rule_arr[0]['rate']) * $product_obj->price;
                    MassEditTools::updateObjectField(
                        'Product',
                        'price',
                        (int)$query_product['id_product'],
                        Tools::ps_round($price, 6)
                    );
                }

                MassEditTools::updateObjectField(
                    'Product',
                    'id_tax_rules_group',
                    (int)$query_product['id_product'],
                    (int)Tools::getValue('id_tax_rules_group')
                );

                $product = new Product($query_product['id_product']);
                $final_price = $product->getPrice(true, null, 6);

                $return_products[(int)$query_product['id_product']] = array(
                    'price' => Tools::displayPrice($price, $this->currency),
                    'price_final' => Tools::displayPrice($final_price, $this->currency),
                );
            }
        } elseif ($this->checkAccessField('unity')) {
            $final_price = 0;
            foreach ($query_products as $query_product) {
                $product = new Product($query_product['id_product']);
                if (version_compare(_PS_VERSION_, '1.7', '>')) {
                    $final_price = $product->price / Tools::getValue('unity_price');
                } else {
                    $final_price = Tools::ps_round($product->price / Tools::getValue('unity_price'), 6);
                }
                    MassEditTools::updateObjectField(
                        'Product',
                        'unit_price_ratio',
                        (int)$query_product['id_product'],
                        (float)$final_price
                    );



                if (Tools::getValue('unity') != "") {
                    MassEditTools::updateObjectField(
                        'Product',
                        'unity',
                        (int)$query_product['id_product'],
                        Tools::getValue('unity')
                    );
                }
            }
        } elseif ($this->checkAccessField('price_round')) {
            foreach ($query_products as $product_r) {
                $product = new Product($product_r['id_product']);
                if ((int)Configuration::get('PS_TAX')) {
                    $tax_manager = TaxManagerFactory::getManager(
                        $this->address,
                        Product::getIdTaxRulesGroupByIdProduct((int)$product_r['id_product'], $this->context)
                    );
                    $product_tax_calculator = $tax_manager->getTaxCalculator();
                    $product_r['price_final'] = $product_tax_calculator->addTaxes($product_r['price']);
                    $product_r['rate'] = $tax_manager->getTaxCalculator()->getTotalRate();
                } else {
                    $product_r['price_final'] = $product_r['price'];
                    $product_r['rate'] = 0;
                }

                if ((int)$this->type_price_r === self::TYPE_PRICE_BASE) {
                    $type_price = $product_r['price'];
                } elseif ((int)$this->type_price_r === self::TYPE_PRICE_FINAL) {
                    $type_price = $product_r['price_final'];
                } else {
                    $type_price = $product_r['wholesale_price'];
                }

                $price_round = MassEditTools::actionPriceRound(
                    $type_price,
                    (int)$this->action_direction,
                    $this->action_rounding_value
                );
                if ((int)$this->type_price_r === self::TYPE_PRICE_BASE) {
                    MassEditTools::updatePriceProduct($product_r['id_product'], $price_round);
                } elseif ((int)$this->type_price_r === self::TYPE_PRICE_FINAL) {
                    if ((int)Configuration::get('PS_TAX')) {
                        $price_round = $price_round / (1 + $product_r['rate'] / 100);
                    }
                    MassEditTools::updatePriceProduct($product_r['id_product'], $price_round);
                } else {
                    MassEditTools::updateWholePriceProduct($product_r['id_product'], $price_round);
                }
                $final_price = $product->getPrice(true, null, 6);
                $return_products[$product_r['id_product']] = array(
                    'price' => Tools::displayPrice($price_round, $this->currency),
                    'price_final' => Tools::displayPrice($final_price, $this->currency),
                );
            }
        }
        return array(
            'products' => $return_products
        );
    }

    public function applyChangeForCombinations($products)
    {
        $return_products = array();
        $return_combinations = array();

        $query_products = $this->getQueryProducts(
            array_keys($products)
        );

        if ($this->checkAccessField('price')) {
            foreach ($query_products as $product) {
                if ($this->type_price === self::TYPE_PRICE_WHOLESALE) {
                    $wholesale_price = $product['wholesale_price'];
                    $new_price = MassEditTools::actionPrice(
                        $wholesale_price,
                        $this->action_price,
                        $this->price_value
                    );

                    $return_products[$product['id_product']] = array(
                        'wholesale_price' => Tools::displayPrice($new_price, $this->currency),
                    );
                    if (array_key_exists(
                        $product['id_product'],
                        $products
                    )) {
                        $combinations = MassEditTools::getCombinationsByIds(
                            $products[$product['id_product']],
                            $this->id_shop
                        );
                        $update_combinations = array();
                        foreach ($combinations as $combination) {
                            $combination_wholesale = $combination['wholesale_price'];
                            $new_combination_wholesale = MassEditTools::actionPrice(
                                $combination_wholesale,
                                $this->action_price,
                                $this->price_value
                            );
                            $return_combinations[$combination['id_product_attribute']] = array(
                                'wholesale_price' => Tools::displayPrice($new_combination_wholesale, $this->currency),
                            );
                            $update_combinations[$combination['id_product_attribute']] = $new_combination_wholesale;
                        }
                    }
                    if (isset($update_combinations) && count(
                        $update_combinations
                    )) {
                        foreach ($update_combinations as $id_pa => $pa_price) {
                            MassEditTools::updateWholePriceCombination($id_pa, $pa_price);
                        }
                    }
                } else {
                    $price = 0;

                    if ((int)Configuration::get('PS_TAX')) {
                        $tax_manager = TaxManagerFactory::getManager(
                            $this->address,
                            Product::getIdTaxRulesGroupByIdProduct(
                                (int)$product['id_product'],
                                $this->context
                            )
                        );
                        $product_tax_calculator = $tax_manager->getTaxCalculator();
                        $product['price_final'] = $product_tax_calculator->addTaxes($product['price']);
                        $product['rate'] = $tax_manager->getTaxCalculator()->getTotalRate();
                    } else {
                        $product['price_final'] = $product['price'];
                        $product['rate'] = 0;
                    }

                    $update_combinations = array();
                    if ($this->type_price === self::TYPE_PRICE_BASE) {
                        $price = $product['price'];
                    } else {
                        if ($this->type_price === self::TYPE_PRICE_FINAL) {
                            $price = $product['price_final'];
                        }
                    }

                    if (array_key_exists(
                        $product['id_product'],
                        $products
                    )) {
                        $combinations = MassEditTools::getCombinationsByIds(
                            $products[$product['id_product']],
                            $this->id_shop
                        );

                        foreach ($combinations as $combination) {
                            $price_pa = $combination['price'];
                            $price_pa_final = $price_pa;
                            $product_price = $combination['product_price'];
                            $product_price_final = $combination['product_price_final'];


                            if ($this->type_price === self::TYPE_PRICE_BASE) {
                                $new_price_pa = MassEditTools::actionPrice(
                                    $price_pa,
                                    $this->action_price,
                                    $this->price_value
                                );

                                if (isset($tax_manager)) {
                                    $price_pa_final = $product_tax_calculator->addTaxes(
                                        MassEditTools::actionPrice(
                                            $new_price_pa,
                                            $this->action_price,
                                            $this->price_value
                                        )
                                    );
                                }
                            } else {
                                if ($this->type_price === self::TYPE_PRICE_FINAL) {
                                    $new_price_pa = MassEditTools::actionPrice(
                                        $price_pa,
                                        $this->action_price,
                                        $this->price_value
                                    );
                                    $price_pa_final = $new_price_pa;
                                    $new_price_pa = ($new_price_pa / (100 + (int)$product['rate']) * 100);
                                }
                            }

                            $return_combinations[$combination['id_product_attribute']] = array(
                                'price' => Tools::displayPrice($new_price_pa, $this->currency),
                                'total_price' => Tools::displayPrice($product_price + $new_price_pa, $this->currency),
                                'price_final' => Tools::displayPrice($price_pa_final, $this->currency),
                                'total_price_final' => Tools::displayPrice(
                                    $product_price_final + $price_pa_final,
                                    $this->currency
                                ),
                            );
                            $update_combinations[$combination['id_product_attribute']] = $new_price_pa;
                        }
                    }

                    $final_price = 0;
                    if ($this->type_price === self::TYPE_PRICE_FINAL) {
                        $final_price = $price;
                        if (Configuration::get('PS_TAX')) {
                            $price = $price / (100 + (int)$product['rate']) * 100;
                        }
                    } else {
                        if ($this->type_price === self::TYPE_PRICE_BASE) {
                            if (Configuration::get('PS_TAX')) {
                                $final_price = $price + ($price / 100 * (int)$product['rate']);
                            } else {
                                $final_price = $price;
                            }
                        }
                    }


                    if (count($combinations)) {
                        foreach ($combinations as $id) {
//                           увеличить на значение
                            if ($this->action_price == 2) {
                                if ($this->type_price == 1) {
                                    $rest = (($id['total_price_final'] + $this->price_value)
                                            - $id['product_price_final']) / (1 + (int)$product['rate'] / 100);
                                    $pa_price = $rest;
                                    $id_pa = $id['id_product_attribute'];
                                } else {
                                    $rest = ($id['price'] + $this->price_value);
                                    $pa_price = $rest;
                                    $id_pa = $id['id_product_attribute'];
                                }
                            }
//                           Уменьшить на значение
                            if ($this->action_price == 4) {
                                if ($this->type_price == 1) {
                                    $rest = (($id['total_price_final'] - $this->price_value)
                                            - $id['product_price_final']) / (1 + (int)$product['rate'] / 100);
                                    $pa_price = $rest;
                                    $id_pa = $id['id_product_attribute'];
                                } else {
                                    $rest = ($id['price'] - $this->price_value);
                                    $pa_price = $rest;
                                    $id_pa = $id['id_product_attribute'];
                                }
                            }
//                            Увеличить на процент
                            if ($this->action_price == 1) {
                                if ($this->type_price == 1) {
                                    $rest = ($id['total_price_final'] + ($id['total_price_final'] / 100
                                                * $this->price_value) - $id['product_price_final'])
                                                / (1 + (int)$product['rate'] / 100);
                                    $pa_price = $rest;
                                    $id_pa = $id['id_product_attribute'];
                                } else {
                                    $rest = $id['price'] + ($id['price']/100)*$this->price_value;
                                    $pa_price = $rest;
                                    $id_pa = $id['id_product_attribute'];
                                }
                            }
//                           Уменьшить на процент
                            if ($this->action_price == 3) {
                                if ($this->type_price == 1) {
                                    $rest = ($id['total_price_final'] - ($id['total_price_final']/100
                                                * $this->price_value)
                                                - $id['product_price_final']) / (1 + (int)$product['rate'] / 100);
                                    $pa_price = $rest;
                                    $id_pa = $id['id_product_attribute'];
                                } else {
                                    $rest = $id['price'] - ($id['price']/100)*$this->price_value;
                                    $pa_price = $rest;
                                    $id_pa = $id['id_product_attribute'];
                                }
                            }
// перезаписать значение
                            if ($this->action_price == 5) {
                                if ($this->type_price == 1) {
                                    $pa_price =  $this->price_value / (1 + (int)$product['rate'] / 100);
                                    $id_pa = $id['id_product_attribute'];
                                } else {
                                    $pa_price = $this->price_value;
                                    $id_pa = $id['id_product_attribute'];
                                }
                            }

                            MassEditTools::updatePriceCombination($id_pa, $pa_price);
                        }
                    }

                    $return_products[$product['id_product']] = array(
                        'price' => Tools::displayPrice($price, $this->currency),
                        'price_final' => Tools::displayPrice($final_price, $this->currency),
                    );
                }
            }
        } elseif ($this->checkAccessField('tax_rule_group') && is_array($query_products)) {
            foreach ($query_products as $query_product) {
                $price = $query_product['price'];
                if ($this->not_change_final_price) {
                    $product_obj = new Product((int)$query_product['id_product']);
                    $new_tax_rule_arr = TaxRule::getTaxRulesByGroupId(
                        Configuration::get('PS_LANG_DEFAULT'),
                        (int)Tools::getValue('id_tax_rules_group')
                    );
                    $price = (100 + $product_obj->getTaxesRate())
                        / (100 + $new_tax_rule_arr[0]['rate']) * $product_obj->price;
                    MassEditTools::updateObjectField(
                        'Product',
                        'price',
                        (int)$query_product['id_product'],
                        Tools::ps_round($price, 6)
                    );
                }

                MassEditTools::updateObjectField(
                    'Product',
                    'id_tax_rules_group',
                    (int)$query_product['id_product'],
                    (int)Tools::getValue('id_tax_rules_group')
                );

                $product = new Product($query_product['id_product']);
                $final_price = $product->getPrice(true, null, 6);

                $return_products[(int)$query_product['id_product']] = array(
                    'price' => Tools::displayPrice($price, $this->currency),
                    'price_final' => Tools::displayPrice($final_price, $this->currency),
                );
            }
        }

        return array(
            'products' => $return_products,
            'combinations' => $return_combinations,
        );
    }

    public function applyChangeBoth($products, $combinations)
    {
    }

    public function checkOptionForCombination()
    {
        $change_for = (int)Tools::getValue('change_for');
        return $change_for == self::CHANGE_FOR_COMBINATION;
    }

    public function getTitle()
    {
        return $this->l('Price');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $tax_rules_groups = TaxRulesGroup::getTaxRulesGroups(true);
        $variables['tax_rules_groups'] = $tax_rules_groups;
        $variables['tax_exclude_taxe_option'] = Tax::excludeTaxeOption();
        $variables['currency'] = $this->context->currency;

        return $variables;
    }
}
