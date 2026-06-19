<?php
/**
 * 2010-2023 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2023 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class FeedShippingPrice
{
    protected $langId = 0;
    protected $multistoreId = 0;
    protected $settings = [];
    protected $configuration = [];
    protected $countryData = [];

    public function setData($langId, $settings, $configuration, $multistoreId)
    {
        $this->langId = $langId;
        $this->settings = $settings;
        $this->configuration = $configuration;
        $this->multistoreId = $multistoreId;
    }

    public function loadCountries($carrierIdDefault)
    {
        $countries = $this->settings['shipping_countries'];

        if (empty($countries) || empty($this->settings['shipping_countries_status'])) {
            return false;
        }

        foreach ($countries as $countryId) {
            $defaultCountry = new Country($countryId, $this->langId);
            $idZone = $defaultCountry->id_zone;

            if ($carrierIdDefault < 1) {
                $carrierIdDefault = $this->getCarrierId($this->langId, $idZone);
            }

            $carrier = new Carrier($carrierIdDefault);

            if (empty($carrier->active)) {
                $carrierIdDefault = $this->getCarrierId($this->langId, $idZone);
                $carrier = new Carrier($carrierIdDefault);
            }

            $address = new Address();
            $address->id_country = $countryId;
            $address->id_state = 0;
            $address->postcode = 0;

            $carrierTax = 0;

            if (_PS_VERSION_ >= '1.5') {
                $carrierTax = $carrier->getTaxCalculator($address)->getTotalRate();
            } elseif (class_exists('TaxManagerFactory', false)) {
                $tax_manager = TaxManagerFactory::getManager($address, $carrier->id_tax_rules_group);
                $carrierTax = $tax_manager->getTaxCalculator()->getTotalRate();
            }

            $this->countryData[] = [
                'defaultCountry' => $defaultCountry,
                'carrierTax' => $carrierTax,
                'carrier' => $carrier,
                'address' => $address,
            ];
        }

        return true;
    }

    public function getPrice($product_class, $salePrice)
    {
        $prices = [];

        foreach ($this->countryData as $c) {
            $prices[$c['defaultCountry']->iso_code] = 0;

            if (empty($this->settings['shipping_price_mode'])) {
                $prices[$c['defaultCountry']->iso_code] = $this->getProductShippingCost($c['defaultCountry']->id_zone, $product_class, $this->configuration, $c['carrier'], $c['carrierTax'], $salePrice);
                continue;
            }

            $prices[$c['defaultCountry']->iso_code] = $this->getCarriersBestPrice($this->langId, $c['defaultCountry']->id_zone, $product_class, $this->configuration, $c['address'], $salePrice, $this->multistoreId);
        }

        return $prices;
    }

    public function getProductShippingCost($idZone, $Product, $configuration, $carrier, $carrierTax, $salePrice)
    {
        if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT) {
            $shipping_cost = $carrier->getDeliveryPriceByWeight($Product->weight, $idZone);
        } elseif ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE) {
            $shipping_cost = $carrier->getDeliveryPriceByPrice($salePrice, $idZone);
        } elseif ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_FREE) {
            return '0.00';
        }

        $taxRation = 1 + ($carrierTax / 100);

        $shipping_cost *= $taxRation;
        $shipping_cost += $carrier->shipping_handling ? $configuration['PS_SHIPPING_HANDLING'] : 0;
        $shipping_cost += $Product->additional_shipping_cost * $taxRation;

        return $shipping_cost;
    }

    public function getCarriersBestPrice($id_lang, $id_zone, $product, $configuration, $address, $salePrice, $multistoreId)
    {
        $error = [];
        $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        $multistoreId = !empty($multistoreId) ? $multistoreId : 1;

        $query = new DbQuery();
        $query->select('id_carrier');
        $query->from('product_carrier', 'pc');
        $query->innerJoin(
            'carrier',
            'c',
            'c.id_reference = pc.id_carrier_reference AND c.deleted = 0 AND c.active = 1'
        );
        $query->where('pc.id_product = '.(int)$product->id);
        $query->where('pc.id_shop = '.(int)$multistoreId);

        $carriers_for_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        $carriersForProductColumn = array();

        if (!empty($carriers_for_product)) {
            foreach ($carriers_for_product as $f) {
                $carriersForProductColumn[] = $f['id_carrier'];
            }
        }

        $result = Carrier::getCarriers($id_lang, true, false, (int)$id_zone, array(Configuration::get('PS_UNIDENTIFIED_GROUP')), Carrier::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
        $results_array = array();

        foreach ($result as $k => $row) {
            if (!empty($carriersForProductColumn)) {
                if (!in_array($row['id_carrier'], $carriersForProductColumn)) {
                    continue;
                }
            }

            $carrier = new Carrier((int)$row['id_carrier']);
            $shipping_method = $carrier->getShippingMethod();
            if ($shipping_method != Carrier::SHIPPING_METHOD_FREE) {
                // Get only carriers that are compliant with shipping method
                if (($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)) {
                    $error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
                    unset($result[$k]);
                    continue;
                }
                if (($shipping_method == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice($id_zone) === false)) {
                    $error[$carrier->id] = Carrier::SHIPPING_PRICE_EXCEPTION;
                    unset($result[$k]);
                    continue;
                }

                // If out-of-range behavior carrier is set on "Desactivate carrier"
                if ($row['range_behavior']) {
                    // Get only carriers that have a range compatible with cart
                    if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT
                        && (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $product->weight, $id_zone))) {
                        $error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
                        unset($result[$k]);
                        continue;
                    }

                    if ($shipping_method == Carrier::SHIPPING_METHOD_PRICE
                        && (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $product->price, $id_zone, $id_currency))) {
                        $error[$carrier->id] = Carrier::SHIPPING_PRICE_EXCEPTION;
                        unset($result[$k]);
                        continue;
                    }
                }
            }

            $carrierTax = 0;

            if (_PS_VERSION_ >= '1.5') {
                $carrierTax = $carrier->getTaxCalculator($address)->getTotalRate();
            } elseif (class_exists('TaxManagerFactory', false)) {
                $tax_manager = TaxManagerFactory::getManager($address, $carrier->id_tax_rules_group);
                $carrierTax = $tax_manager->getTaxCalculator()->getTotalRate();
            }

            $row['price'] = (($shipping_method == Carrier::SHIPPING_METHOD_FREE) ? 0 : $this->getProductShippingCost($id_zone, $product, $configuration, $carrier, $carrierTax, $salePrice));

            // If price is false, then the carrier is unavailable (carrier module)
            if ($row['price'] === false || empty($row['price']) || $row['price'] < 0.0001) {
                unset($result[$k]);
                continue;
            }

            $results_array[] = $row;
        }

        // if we have to sort carriers by price
        $prices = array();

        if (Configuration::get('PS_CARRIER_DEFAULT_SORT') == Carrier::SORT_BY_PRICE) {
            foreach ($results_array as $r) {
                $prices[] = $r['price'];
            }
            if (Configuration::get('PS_CARRIER_DEFAULT_ORDER') == Carrier::SORT_BY_ASC) {
                array_multisort($prices, SORT_ASC, SORT_NUMERIC, $results_array);
            } else {
                array_multisort($prices, SORT_DESC, SORT_NUMERIC, $results_array);
            }
        }

        return !empty($results_array[0]) ? $results_array[0]['price'] : '0.00';
    }

    public function getCarrierId($id_lang, $idZone)
    {
        $carriers = Carrier::getCarriers($id_lang, true, false, $idZone, array(Configuration::get('PS_UNIDENTIFIED_GROUP')), Carrier::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);

        if (!empty($carriers[0])) {
            return $carriers[0]['id_carrier'];
        }

        return 0;
    }
}
