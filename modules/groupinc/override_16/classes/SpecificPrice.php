<?php
/**
* Price increment/reduction by groups, categories and more
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

if (!class_exists('SpecificPrice', false)) {
class SpecificPrice extends SpecificPriceCore
{
    public static function getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_product_attribute = null, $all_combinations = false, $id_customer = 0)
    {
        if (!Module::isEnabled('groupinc')) {
            return parent::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_product_attribute, $all_combinations, $id_customer);
        }
        include_once(_PS_MODULE_DIR_.'groupinc/classes/GroupincConfiguration.php');
        $groupinc = new GroupincConfiguration();
        $configs_qd = array();
        $context = Context::getContext();
        if (isset($context->controller) && !in_array($context->controller->controller_type, array('admin'))) {
            $id_address_delivery = $context->cart->id_address_delivery;
            $address = new Address($id_address_delivery);
            $id_state = $address->id_state;
            $configs_qd = $groupinc->getGIConfigurations($id_shop, $id_product, $id_customer, $id_country, $id_state, $id_currency, $context->language->id, false, true, $id_product_attribute, 0, true);
        } else {
            return parent::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_product_attribute, $all_combinations, $id_customer);
        }
        if (empty($configs_qd) || !$configs_qd) {
            return parent::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_product_attribute, $all_combinations, $id_customer);
        }

        $configs_override = array();
        if (empty($configs_qd) || !$configs_qd) {
            $configs_override = $groupinc->getGIConfigurations($id_shop, $id_product, $id_customer, $id_country, $id_state, $id_currency, $context->language->id, false, true, $id_product_attribute, 0, false);
            if (empty($configs_override)) {
                return parent::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_product_attribute, $all_combinations, $id_customer);
            }
        }

        foreach ($configs_override as $c) {
            if ($c['override_discounts']) {
                return array();
            }
        }
        $now = date('Y-m-d H:i:00');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT *,
                    '.SpecificPrice::_getScoreQuery($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_customer).'
                FROM `'._DB_PREFIX_.'specific_price` USE INDEX (id_product_2)
                WHERE
                    `id_product` IN(0, '.(int)$id_product.') AND
                    '.(!$all_combinations ? '`id_product_attribute` IN(0, '.(int)$id_product_attribute.') AND ' : '').'
                    `id_shop` IN(0, '.(int)$id_shop.') AND
                    `id_currency` IN(0, '.(int)$id_currency.') AND
                    `id_country` IN(0, '.(int)$id_country.') AND
                    `id_group` IN(0, '.(int)$id_group.') AND
                    `id_customer` IN(0, '.(int)$id_customer.')
                    AND
                    (
                        (`from` = \'0000-00-00 00:00:00\' OR \''.$now.'\' >= `from`)
                        AND
                        (`to` = \'0000-00-00 00:00:00\' OR \''.$now.'\' <= `to`)
                    )
                    ORDER BY `from_quantity` ASC, `id_specific_price_rule` ASC, `score` DESC, `to` DESC, `from` DESC
        ', false, false);

        $targeted_prices = array();
        $last_quantity = array();
        while ($specific_price = Db::getInstance()->nextRow($result)) {
            if (!isset($last_quantity[(int)$specific_price['id_product_attribute']])) {
                $last_quantity[(int)$specific_price['id_product_attribute']] = $specific_price['from_quantity'];
            } elseif ($last_quantity[(int)$specific_price['id_product_attribute']] == $specific_price['from_quantity']) {
                continue;
            }
            $last_quantity[(int)$specific_price['id_product_attribute']] = $specific_price['from_quantity'];
            if ($specific_price['from_quantity'] > 1) {
                $targeted_prices[] = $specific_price;
            }
        }
        $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context));
        $ptc = $tax_manager->getTaxCalculator();
        $product = new Product($id_product);
        $product_price_without_tax = $product->price;
        $product_price_with_tax = $ptc->addTaxes($product_price_without_tax);
        if (empty($targeted_prices)) {
            $targeted_prices = $groupinc->getQuantityDiscounts($configs_qd, $id_product, false, $product_price_with_tax, $product_price_without_tax, $ptc, $id_product_attribute);
        } else {
            $targeted_prices = $groupinc->getQuantityDiscounts($configs_qd, $id_product, $targeted_prices, $product_price_with_tax, $product_price_without_tax, $ptc, $id_product_attribute);
        }
        return $targeted_prices;
    }

    protected static function formatIntInQuery($first_value, $second_value) {
        $first_value = (int)$first_value;
        $second_value = (int)$second_value;
        if ($first_value != $second_value) {
            return 'IN ('.$first_value.', '.$second_value.')';
        } else {
            return ' = '.$first_value;
        }
    }
}
}
