<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) { exit; }
if (!class_exists('CommonCart')) {
    abstract class CommonCart extends Cart
    {
        public $taxCalculationMethod = PS_TAX_INC;
        public static $debug_mode = false;

        /**
         * @return float|int
         */
        public function marketplaceGetCarrierTaxRate()
        {
            $carrier_tax_rate = 0;
            $pass = true;

            if (!$this->id_carrier) {
                $pass = false;
            }

            $address_type = Configuration::get('PS_TAX_ADDRESS_TYPE');

            if (empty($address_type)) {
                $address_type = 'id_address_delivery';
            }

            $address = new Address($this->{$address_type});

            if (!Validate::isLoadedObject($address)) {
                $pass = false;
            }

            if ($this->id_customer) {
                $customer = new Customer((int)($this->id_customer));
                $this->taxCalculationMethod = !Group::getPriceDisplayMethod((int)($customer->id_default_group));
            } else {
                $this->taxCalculationMethod = !Group::getDefaultPriceDisplayMethod();
            }

            if ($pass && $this->taxCalculationMethod) {
                // Carrier Taxes
                //
                if (method_exists('Carrier', 'getTaxesRate')) {
                    $carrier = new Carrier($this->id_carrier);

                    if (Validate::isLoadedObject($carrier)) {
                        $carrier_tax_rate = (float)$carrier->getTaxesRate($address);
                    }
                } elseif (method_exists('Tax', 'getCarrierTaxRate')) {
                    $carrier_tax_rate = (float)Tax::getCarrierTaxRate($this->id_carrier, (int)$address->id);
                }
            }

            if (static::$debug_mode) {
                CommonTools::p("marketplaceGetCarrierTaxRate");
                CommonTools::p(sprintf('taxCalculationMethod: %s', $this->taxCalculationMethod));
                CommonTools::p(sprintf('id_carrier: %d', $this->id_carrier));
                CommonTools::p(sprintf('address_type: %s', $address_type));
                CommonTools::p(sprintf('id_address: %d', $address->id));
                CommonTools::p(sprintf('carrier_tax_rate: %s', $carrier_tax_rate));
            }

            return ($carrier_tax_rate);
        }


        /**
         * @return int
         */
        public function marketplaceCalculationMethod($force = false)
        {
            if ($force) {
                return (PS_TAX_INC);
            }

            if ($this->id_customer) {
                $customer = new Customer((int)($this->id_customer));
                $this->taxCalculationMethod = !Group::getPriceDisplayMethod((int)($customer->id_default_group));
            } else {
                $this->taxCalculationMethod = !Group::getDefaultPriceDisplayMethod();
            }

            if (static::$debug_mode) {
                CommonTools::p('marketplaceCalculationMethod:');
                CommonTools::p(sprintf('id_customer: %d', $this->id_customer));
                CommonTools::p(sprintf('taxCalculationMethod: %s', $this->taxCalculationMethod));
            }
            return((int)$this->taxCalculationMethod);
        }

        /**
         * @param $product
         *
         * @return float|int
         */
        protected function marketplaceGetTaxRate($product)
        {
            $product_tax_rate = 0;
            if ($product['tax_rate']) {
                if ($this->id_customer) {
                    $customer = new Customer((int)($this->id_customer));
                    $this->taxCalculationMethod = !Group::getPriceDisplayMethod((int)($customer->id_default_group));
                } else {
                    $this->taxCalculationMethod = !Group::getDefaultPriceDisplayMethod();
                }

                if ($this->taxCalculationMethod) {
                    if (method_exists('Tax', 'getProductTaxRate')) {
                        $product_tax_rate = (float)Tax::getProductTaxRate(
                            (int)$product['id_product'],
                            (int)$product['id_address_delivery']
                        );
                    } else {
                        $product_tax_rate = (float)Tax::getApplicableTax(
                            (int)$product['id_tax'],
                            $product['tax_rate'],
                            (int)$product['id_address_delivery']
                        );
                    }
                }
            }
            if (static::$debug_mode) {
                CommonTools::p('marketplaceGetTaxRate:');
                CommonTools::p(sprintf('taxCalculationMethod: %s', $this->taxCalculationMethod));
                CommonTools::p(sprintf('product/id_product: %d', $product['id_product']));
                CommonTools::p(sprintf('product/id_tax: %d', $product['id_tax']));
                CommonTools::p(sprintf('product/id_address_delivery: %d', $product['id_address_delivery']));
                CommonTools::p(sprintf('product_tax_rate: %s', $product_tax_rate));
            }

            return ($product_tax_rate);
        }
    }
}
