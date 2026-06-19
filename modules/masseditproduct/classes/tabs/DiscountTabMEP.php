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

class DiscountTabMEP extends BaseTabMEP
{
    public $id_currency;
    public $id_country;
    public $id_group;
    public $from_quantity;
    public $reduction;
    public $delete_old_discount;
    public $reduction_type;
    public $from;
    public $price;
    public $leave_base_price;
    public $to;
    public $id_shop;
    public $currency;
    public $action_for_sp;
    public $action_discount;

    public function __construct()
    {
        parent::__construct();
        $this->id_currency = Tools::getValue('sp_id_currency');
        $this->id_country = Tools::getValue('sp_id_country');
        $this->id_group = Tools::getValue('sp_id_group');
        $this->from_quantity = Tools::getValue('sp_from_quantity');
        $this->reduction_type = !Tools::getValue('sp_reduction_type') ? 'amount' : Tools::getValue('sp_reduction_type');
        $this->reduction = (float)($this->reduction_type == 'amount'
            ? Tools::getValue('sp_reduction') : Tools::getValue('sp_reduction') / 100);
        $this->delete_old_discount = (int)Tools::getValue('delete_old_discount');
        $this->delete_old_discount_all = (int)Tools::getValue('delete_old_discount_all');
        $this->from = Tools::getValue('sp_from');
        $this->to = Tools::getValue('sp_to');
        $this->price = (float)Tools::getValue('price');
        $this->leave_base_price = (int)Tools::getValue('leave_base_price');
        $this->action_for_sp = Tools::getValue('action_for_sp');
        $this->action_discount = Tools::getValue('action_discount');

//        if ($this->leave_base_price) {
//            $this->price = -1;
//        }

        if (!$this->from) {
            $this->from = '0000-00-00 00:00:00';
        }
        if (!$this->to) {
            $this->to = '0000-00-00 00:00:00';
        }

        $this->id_shop = $this->context->cookie->__get('shopContext');
        $this->id_shop = !$this->id_shop ? 0 : $this->context->shop->id;

        $this->currency = Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'));
    }

    public function applyChangeBoth($products, $combinations)
    {
    }

    public function deleteOldDiscount($id_product)
    {
        if ($this->delete_old_discount && $this->checkAccessField('delete_specific_price')) {
            SpecificPrice::deleteByProductId((int)$id_product);
        }
    }

    public function addSpecificPrice($products)
    {
        $return_products = array();
        foreach ($products as $id_product) {
            $this->deleteOldDiscount($id_product);

            if ($this->validateSpecificPrice(
                (int)$id_product,
                $this->id_shop,
                $this->id_currency,
                $this->id_country,
                $this->id_group,
                0,
                $this->price,
                $this->from_quantity,
                $this->reduction,
                $this->reduction_type,
                $this->from,
                $this->to,
                0
            )) {
                $specific_price = new SpecificPrice();
                $specific_price->id_product = (int)$id_product;
                $specific_price->id_product_attribute = (int)0;
                $specific_price->id_shop = (int)$this->id_shop;
                $specific_price->id_currency = (int)$this->id_currency;
                $specific_price->id_country = (int)$this->id_country;
                $specific_price->id_group = (int)$this->id_group;
                $specific_price->id_customer = 0;
                $specific_price->price = (float)$this->price;
                $specific_price->from_quantity = (int)$this->from_quantity;
                $specific_price->reduction = (float)$this->reduction;
                $specific_price->reduction_type = $this->reduction_type;
                $specific_price->from = $this->from;
                $specific_price->to = $this->to;

                if (!$specific_price->add()) {
                    LoggerMEP::getInstance()->error(
                        sprintf(
                            $this->l('Product №%s: an error occurred while updating the specific price.'),
                            $id_product
                        )
                    );
                }
            }

            $return_products[$id_product] = array(
                'price' => Tools::displayPrice(
                    Product::getPriceStatic($id_product, false),
                    $this->currency
                ),
                'price_final' => Tools::displayPrice(
                    Product::getPriceStatic($id_product, true),
                    $this->currency
                ),
            );
        }

        return $return_products;
    }
    public function updateSpecificPrice($products)
    {
        $where = $this->getWhereFilter($products);

        $ids_sp = Db::getInstance()->executeS('SELECT `id_specific_price` FROM `'._DB_PREFIX_.'specific_price` '.
            $where);

        $price = function ($specific_price, $price) {
            $discount_price_reduction_type = Tools::getValue('discount_price_reduction_type');

            if (Tools::getValue('discount_price') === '0') {
                if ($discount_price_reduction_type == 'amount') {
                    return $specific_price->price + $price;
                } elseif ($discount_price_reduction_type == 'percentage') {
                    return $specific_price->price + ($specific_price->price * $price / 100);
                }
            } elseif (Tools::getValue('discount_price') === '1') {
                if ($discount_price_reduction_type == 'amount') {
                    $new_price = $specific_price->price - $price;
                    return ($new_price > 0) ? $new_price : $specific_price->price;
                } elseif ($discount_price_reduction_type == 'percentage') {
                    return $specific_price->price - ($specific_price->price * $price / 100);
                }
            } elseif (Tools::getValue('discount_price') === '2') {
                return $price;
            }
            return $specific_price->price;
        };

        $reduction = function ($specific_price, $reduction, $reduction_type) {
            if (Tools::getValue('discount_discount') === '0') {
                if ($reduction_type == $specific_price->reduction_type) {
                    return $specific_price->reduction + $reduction;
                }
            } elseif (Tools::getValue('discount_discount') === '1') {
                if ($reduction_type == $specific_price->reduction_type) {
                    return $specific_price->reduction - $reduction;
                }
            } elseif (Tools::getValue('discount_discount') === '2') {
                $specific_price->reduction_type = $reduction_type;
                return $reduction;
            }
            return $specific_price->reduction;
        };

        $return_products = array();
        foreach ($ids_sp as $value) {
            $sp = new SpecificPrice($value['id_specific_price']);
            if (!Validate::isLoadedObject($sp)) {
                continue;
            }

            $sp->id_currency = (int)$this->id_currency;
            $sp->id_country = (int)$this->id_country;
            $sp->id_group = (int)$this->id_group;
            $sp->from = $this->from;
            $sp->to = $this->to;
            $sp->price = $price($sp, $this->price);
            $sp->reduction = $reduction($sp, $this->reduction, $this->reduction_type);
            $sp->from_quantity = (int)$this->from_quantity;

            if (!$sp->update()) {
                LoggerMEP::getInstance()->error(
                    sprintf(
                        $this->l(
                            'Product №%s / Specific price №%s: an error 
                            occurred while updating the specific price.'
                        ),
                        $sp->id_product,
                        $sp->id
                    )
                );
            } else {
                $return_products[$sp->id_product] = true;
            }
        }

        return $return_products;
    }

    private function getWhereFilter($products)
    {
        $where = 'WHERE 1';
        if (count(Shop::getCompleteListOfShopsID()) > 1) {
            $where .= ' AND `id_shop` = '.(int)$this->id_shop;
        }

        if (!$this->checkOptionForCombination()) {
            $in = implode(array_map('intval', $products), ', ');
            $where .= ' AND `id_product` IN ('.pSQL($in).')';
        }

        if ($this->checkOptionForCombination()) {
            $ids_combination = array();
            foreach ($products as $id_product => $combinations) {
                foreach ($combinations as $id_combination) {
                    $ids_combination[] = $id_combination;
                }
            }

            if (count($ids_combination) == 1) {
                $where .= ' AND `id_product_attribute` = '.(int)$ids_combination[0];
            } else {
                $in = implode($ids_combination, ', ');
                $where .= ' AND `id_product_attribute` IN ('.pSQL($in).')';
            }
        }

        if (Tools::getValue('search_id_currency')) {
            $where .= ' AND `id_currency` = '.(int)Tools::getValue('search_id_currency');
        }

        if (Tools::getValue('search_id_country')) {
            $where .= ' AND `id_country` = '.(int)Tools::getValue('search_id_country');
        }

        if (Tools::getValue('search_id_group')) {
            $where .= ' AND `id_group` = '.(int)Tools::getValue('search_id_group');
        }

        if (Tools::getValue('search_from')) {
            $where .= ' AND `from` >= "'.MassEditTools::roundFromDate(Tools::getValue('search_from')).'"';
        }

        if (Tools::getValue('search_to')) {
            $where .= ' AND `to` <= "'.MassEditTools::roundToDate(Tools::getValue('search_to')).'"';
        }
        if (Tools::getValue('search_from_quantity')) {
            $where .= ' AND `from_quantity` = "'.(int)Tools::getValue('search_from_quantity').'"';
        }
        return $where;
    }

    public function applyChangeForProducts($products)
    {
        $return_products = array();
        if ($this->checkAccessField('specific_price') && $this->action_for_sp == 0) {
            $return_products = $this->addSpecificPrice($products);
        } elseif ($this->checkAccessField('specific_price') && $this->action_for_sp == 1) {
            $return_products = $this->deleteSpecificPrice($products);
        } elseif ($this->checkAccessField('specific_price') && $this->action_for_sp == 2) {
            $return_products = $this->updateSpecificPrice($products);
        } elseif ($this->checkAccessField('delete_specific_price_all') && $this->delete_old_discount_all) {
            foreach ($products as $id_product) {
                if (SpecificPrice::deleteByProductId((int)$id_product)) {
                    $return_products[$id_product] = true;
                }
            }
        }

        return array(
            'products' => $return_products
        );
    }

    public function addSpecificPriceCombination($products)
    {
        $return_combinations = array();
        foreach ($products as $id_product => $combinations) {
            foreach ($combinations as $id_pa) {
                if ($this->validateSpecificPrice(
                    (int)$id_product,
                    $this->id_shop,
                    $this->id_currency,
                    $this->id_country,
                    $this->id_group,
                    $id_pa,
                    $this->price,
                    $this->from_quantity,
                    $this->reduction,
                    $this->reduction_type,
                    $this->from,
                    $this->to,
                    0
                )) {
                    $specific_price = new SpecificPrice();
                    $specific_price->id_product = (int)$id_product;
                    $specific_price->id_product_attribute = (int)$id_pa;
                    $specific_price->id_shop = (int)$this->id_shop;
                    $specific_price->id_currency = (int)$this->id_currency;
                    $specific_price->id_country = (int)$this->id_country;
                    $specific_price->id_group = (int)$this->id_group;
                    $specific_price->id_customer = 0;
                    $specific_price->price = (float)$this->price;
                    $specific_price->from_quantity = (int)$this->from_quantity;
                    $specific_price->reduction = (float)$this->reduction;
                    $specific_price->reduction_type = $this->reduction_type;
                    $specific_price->from = $this->from;
                    $specific_price->to = $this->to;
                    if (!$specific_price->add()) {
                        $logger = LoggerMEP::getInstance();
                        $logger->error(
                            sprintf(
                                $this->module->l(
                                    'Product №%s: an error occurred while updating the specific price.'
                                ),
                                $id_product
                            )
                        );
                    }
                }

                $return_combinations[$id_pa] = array(
                    'price' => Tools::displayPrice(
                        Product::getPriceStatic($id_product, false, $id_pa),
                        $this->currency
                    ),
                    'price_final' => Tools::displayPrice(
                        Product::getPriceStatic($id_product, true, $id_pa),
                        $this->currency
                    ),
                );
            }
        }
        return $return_combinations;
    }

    public function applyChangeForCombinations($products)
    {
        $return_products = array();
        if ($this->checkAccessField('specific_price') && $this->action_for_sp == 0) {
            $return_products = $this->addSpecificPriceCombination($products);
        } elseif ($this->checkAccessField('specific_price') && $this->action_for_sp == 1) {
            $return_products = $this->deleteSpecificPrice($products);
        } elseif ($this->checkAccessField('specific_price') && $this->action_for_sp == 2) {
            $return_products = $this->updateSpecificPrice($products);
        } elseif ($this->checkAccessField('delete_specific_price_all') && $this->delete_old_discount_all) {
            foreach ($products as $id_product) {
                if (SpecificPrice::deleteByProductId((int)$id_product)) {
                    $return_products[$id_product] = true;
                }
            }
        }

        return array(
            'products' => $return_products
        );
    }

    protected function deleteSpecificPrice($products)
    {

        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(
            '
			DELETE FROM `'._DB_PREFIX_.'specific_price` '.$this->getWhereFilter($products)
        )) {
            Configuration::updateGlobalValue(
                'PS_SPECIFIC_PRICE_FEATURE_ACTIVE',
                ObjectModel::isCurrentlyUsed('specific_price')
            );
        }

        return ($this->checkOptionForCombination() ? array_keys($products) : $products);
    }

    public function checkBeforeChange()
    {
        if ($this->reduction_type == 'percentage'
            && ((float)$this->reduction <= 0
            || (float)$this->reduction > 100)) {
            LoggerMEP::getInstance()->error($this->l('Product №%s: submitted reduction value (0-100) is out-of-range'));
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function checkOptionForCombination()
    {
        $change_for = (int)Tools::getValue('change_for');
        if ($change_for == self::CHANGE_FOR_COMBINATION) {
            return true;
        }
        return false;
    }

    public function validateSpecificPrice(
        $id_product,
        $id_shop,
        $id_currency,
        $id_country,
        $id_group,
        $id_customer,
        $price,
        $from_quantity,
        $reduction,
        $reduction_type,
        $from,
        $to,
        $id_combination = 0
    ) {
        if (!Validate::isUnsignedId($id_shop)
            || !Validate::isUnsignedId($id_currency)
            || !Validate::isUnsignedId($id_country) || !Validate::isUnsignedId($id_group) || !Validate::isUnsignedId(
                $id_customer
            )) {
            LoggerMEP::getInstance()->error(sprintf($this->l('Product №%s: wrong IDs'), $id_product));
        } elseif ((!isset($price)
                && !isset($reduction))
            || (isset($price)
                && !Validate::isNegativePrice($price))
            || (isset($reduction) && !Validate::isPrice($reduction))) {
            LoggerMEP::getInstance()->error(
                sprintf($this->l('Product №%s: invalid price/discount amount'), $id_product)
            );
        } elseif (!Validate::isUnsignedInt($from_quantity)) {
            LoggerMEP::getInstance()->error(sprintf($this->l('Product №%s: invalid quantity'), $id_product));
        } elseif ($reduction && !Validate::isReductionType($reduction_type)) {
            LoggerMEP::getInstance()->error(
                sprintf(
                    $this->l('Product №%s: please select a discount type (amount or percentage).'),
                    $id_product
                )
            );
        } elseif ($from && $to && (!Validate::isDateFormat($from) || !Validate::isDateFormat($to))) {
            LoggerMEP::getInstance()->error(
                sprintf($this->l('Product №%s: the from/to date is invalid.'), $id_product)
            );
        } elseif (SpecificPrice::exists(
            (int)$id_product,
            $id_combination,
            $id_shop,
            $id_group,
            $id_country,
            $id_currency,
            0,
            $from_quantity,
            $from,
            $to,
            false
        )) {
            LoggerMEP::getInstance()->error(
                sprintf($this->l('Product №%s: speicifc price already exists.'), $id_product)
            );

            return false;
        } else {
            return true;
        }

        return false;
    }

    public function getTitle()
    {
        return $this->l('Discount');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['currencies'] = Currency::getCurrencies(false, true);
        $variables['countries'] = Country::getCountries($this->context->language->id, true);
        $variables['groups'] = Group::getGroups($this->context->language->id);

        return $variables;
    }
}
