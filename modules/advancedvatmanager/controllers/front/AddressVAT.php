<?php
/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */

 
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/ValidationEngine.php');

class AdvancedVatManagerAddressVATModuleFrontController extends ModuleFrontController
{

    public function __construct()
    {
        parent::__construct();
    }
    
    public function initContent()
    {
        parent::initContent();
        $skipvalidation = ValidationEngine::skipVATFieldBycountry(Tools::getValue('id_country')); 
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {           
            if (Tools::getValue('action') == 'updateAddressForm16') {
                //Check vat_number field
                $addressFormat = AddressFormat::getOrderedAddressFields(Tools::getValue('id_country'));
                $vat_field_exists = in_array('vat_number', $addressFormat);
                $vat_required = in_array('vat_number', AddressFormat::getFieldsRequired());
                $company_required = in_array('company', AddressFormat::getFieldsRequired());
                //Check to skip validation for VAT field
                die(json_encode(array('skipValidation'=> $skipvalidation, 'vat_field_exists' => $vat_field_exists, 'vat_required' => $vat_required, 'company_required' => $company_required)));             
            }
        } 
        else {
            if (Tools::getValue('action') == 'checkErrorInCheckoutAddress') {
                $advancedvatmanager = Module::getInstanceByName('advancedvatmanager');
                $id_address_invoice = Tools::getValue('id_address_invoice');
                $id_address_delivery = Tools::getValue('id_address_delivery');
                $address_invoice_errors = $advancedvatmanager->getCustomerAddressWithError($id_address_invoice);
                $address_delivery_errors = $advancedvatmanager->getCustomerAddressWithError($id_address_delivery);
                $this->ajaxRender(Tools::jsonEncode([
                        'address_invoice_errors' => $address_invoice_errors,
                        'address_delivery_errors' => $address_delivery_errors
                ]));  
            }
            if ($skipvalidation === false) {       
                if (Tools::getValue('action') == 'updateAddressForm') {              
                    $addressForm = $this->makeAddressForm();
                    
                    if (Tools::getIsset('id_address') && ($id_address = (int) Tools::getValue('id_address'))) {
                        $addressForm->loadAddressById($id_address);
                    }
                    
                    // Fill form fields after submitting form.
                    $values = Tools::getValue('data_form');
                    $additionalTemplateVariables = array();
                    if (!empty($values)) {
                        foreach ($values as $value) {
                            $addressForm->fillWith([$value['name'] => $value['value']]);
                            $additionalTemplateVariables[$value['name'] == 'saveAddress'?'type':$value['name']] = $value['value'];                          
                        }
                        $additionalTemplateVariables['form_has_continue_button'] = Tools::getValue('form_has_continue_button');
                        $additionalTemplateVariables['use_same_address'] = Tools::getValue('use_same_address');
                    }
                    ob_end_clean();
                    header('Content-Type: application/json');
                    $this->ajaxRender(Tools::jsonEncode([
                        'address_form' => $this->render(
                            Tools::getValue('php_self') == 'order'?'checkout/_partials/address-form':'customer/_partials/address-form',
                            Tools::getValue('php_self') == 'order'?array_merge($addressForm->getTemplateVariables(), $additionalTemplateVariables):$addressForm->getTemplateVariables()
                        ),
                    ]));
                }
            }
        }
    }
}