<?php
/**
 * A Route Prestashop Extension that adds secure shipping
 * protection to your orders
 *
 * Php version 7.0^
 *
 * @author    Route Development Team <dev@routeapp.io>
 * @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
 * @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
 */

class RouteTax
{
    const ROUTE_TAX_CLASSES = 'PS_ROUTE_TAX_CLASSES';
    const ROUTE_TAX_ENABLED = 'PS_ROUTE_TAX_ENABLED';
    const TAX_ENABLED = 'PS_TAX';

    public static function getIdTaxRulesGroup()
    {
        $sql = new DbQuery();
        $sql->select('id_tax_rules_group, name');
        $sql->from('tax_rules_group', 'ps');
        $sql->where('deleted=0');

        $taxes = [
            [
                'value' => '',
                'name' => '--',
                'id_tax_rules_group' => '',
            ],
        ];
        $taxes = array_merge(Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql), $taxes);
        foreach ($taxes as $key => $tax) {
            $taxes[$key]['value'] = $tax['id_tax_rules_group'];
            $taxes[$key]['name'] = $tax['name'];
        }

        return $taxes;
    }

    public static function getTaxClasses()
    {
        return Configuration::get(self::ROUTE_TAX_CLASSES);
    }

    public static function getRouteTaxEnabled()
    {
        return Configuration::get(self::ROUTE_TAX_ENABLED) == true;
    }

    public static function setTaxClasses($classes = [])
    {
        Configuration::updateValue(self::ROUTE_TAX_CLASSES, $classes);
    }

    public static function setRouteTaxEnabled($bool)
    {
        Configuration::updateValue(self::ROUTE_TAX_ENABLED, (bool) $bool);
    }

    public static function getTaxEnabled()
    {
        return Configuration::get(self::TAX_ENABLED) == true;
    }

    public static function isTaxable()
    {
        return !empty(self::getTaxClasses())
            && self::getRouteTaxEnabled()
            && self::getTaxEnabled();
    }

    public static function getTotalRate($addressId, $taxId)
    {
        $totalTaxRate = 0;
        $address = new Address($addressId);
        $taxManager = TaxManagerFactory::getManager($address, $taxId);
        $taxCalculator = $taxManager->getTaxCalculator();
        $totalTaxRate += $taxCalculator->getTotalRate();

        return $totalTaxRate;
    }

    public static function calculateTax($routeFee, $addressId)
    {
        if (!self::isTaxable()) {
            return 0;
        }

        return ($routeFee * RouteTax::getTotalRate($addressId, self::getTaxClasses())) / 100;
    }

    public static function getRouteTax($addressId, $subtotal, $currency)
    {
        if (RouteTax::isTaxable()) {
            $api = new RouteWidget(
                RouteSetup::getSecretToken(),
                RouteSetup::getEnvironment()
            );
            $routeFee = $api->getQuote($subtotal, $currency);

            return RouteTax::calculateTax($routeFee, $addressId);
        }

        return 0;
    }
}
