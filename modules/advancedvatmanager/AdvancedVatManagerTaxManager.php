<?php
/**
 * 2017-2022 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2022 www.liewebs.com - Liewebs
 *  @license see file:license_es.html and license_en.html
 * 	@module Advanced VAT Manager
 */
 
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/ValidationEngine.php');
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersVAT.php');

class AdvancedVatManagerTaxManager implements TaxManagerInterface
{
	public static function isAvailableForThisAddress(Address $address)
	{   
        static $cached_address = null;

        if ($address->id_customer !== null) {
            $cached_address = $address;
            ValidationEngine::$id_address_used = $cached_address->id;  
            // Checks if TAX EXEMPT
            if (Context::getContext()->cart) {
                if (Configuration::get('ADVANCEDVATMANAGER_ADDRESS_TYPE') == 'both') {
                    $country_delivery = Address::getCountryAndState(Context::getContext()->cart->id_address_delivery);
                    $country_invoice = Address::getCountryAndState(Context::getContext()->cart->id_address_invoice);
                    $noTax = ValidationEngine::checkNoTax($cached_address->id_customer, Context::getContext()->cart->id_address_delivery, $country_delivery['id_country']??null) || ValidationEngine::checkNoTax($cached_address->id_customer, Context::getContext()->cart->id_address_invoice, $country_invoice['id_country']??null);  
                }
                else {
                    $noTax = ValidationEngine::checkNoTax($cached_address->id_customer, Context::getContext()->cart->{Configuration::get('ADVANCEDVATMANAGER_ADDRESS_TYPE')}, $cached_address->id_country);    
                }    
            }
            else {
                $noTax = ValidationEngine::checkNoTax($cached_address->id_customer, $cached_address->id, $cached_address->id_country);
            } 
            // Change customer default group when address is selected by customer (delivery address)).
            if (Configuration::get('ADVANCEDVATMANAGER_CHANGE_DEFAULT_GROUP_WITHADDRESS') == 1) {
                ValidationEngine::changeCustomerGroupsByAddress($cached_address->id, $cached_address->id_customer);
            }
            // Save customer vat data in cookie to get it in some places where static variables are not executed.
            Context::getContext()->cookie->__set('customer_vat_data', json_encode(array('brexit_customer' => ValidationEngine::$brexit_customer, 'notax_customer' => ValidationEngine::$notax_customer, 'id_address_used' => ValidationEngine::$id_address_used)));
            return $noTax; 
        }
        else {
            return false;
        }  
	}

    public function getTaxCalculator()
    {
    	$tax = new Tax();
    	$tax->rate = 0;// Set 0% to tax rate if TAX EXEMPT
    	return new TaxCalculator(array($tax));
    }
}