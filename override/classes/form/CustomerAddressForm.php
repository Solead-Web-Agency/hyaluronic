<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
 
class CustomerAddressForm extends CustomerAddressFormCore
{
    /*
    * module: advancedvatmanager
    * date: 2026-06-19 12:02:32
    * version: 1.5.5
    */
    public function getTemplateVariables()
    {
        $context = Context::getContext();
        if (!$this->formFields) {
            $this->formFields = $this->formatter->getFormat();
        }
        $this->setValue('token', $this->getPersister()->getToken());
        
        $formFields = array_map(
            function (FormField $item) {
                return $item->toArray();
            },
            $this->formFields
        );
        if (Module::isEnabled('advancedvatmanager')) {
            $default_vatfield = false;
            $default_companyfield = false;
            $advancedvatmanager = Module::getInstanceByName('advancedvatmanager');
            if (in_array('vat_number', AddressFormat::getOrderedAddressFields($formFields['id_country']['value']))) {
                $default_vatfield = $formFields['vat_number']['required']; 
                $default_companyfield = $formFields['company']['required']; 
            }
            if (!ValidationEngine::skipVATFieldBycountry($formFields['id_country']['value'])) {
                $label = Configuration::get('ADVANCEDVATMANAGER_FIELD_LABEL', $context->language->id);
                $legend = Configuration::get('ADVANCEDVATMANAGER_FIELD_LEGEND', $context->language->id);
                if ($label) {
                    $formFields['vat_number']['label'] = $label;    
                }
                if ($legend) {
                    $formFields['vat_number']['availableValues']['comment'] = $legend;    
                }
                if (Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required') {
                    $formFields['vat_number']['required'] = true;   
                }
                else {
                    $formFields['vat_number']['required'] = false;               
                }
                
                $formFields['company']['availableValues']['comment'] = (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION')?$advancedvatmanager->controller_msg['COMPANY_VALIDATION_COMMENT']:'').' '.(Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY')?$advancedvatmanager->controller_msg['COMPANY_DISPLAY_COMMENT']:'');
                
                if (Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 1) {
                    if (empty($formFields['company']['value'])){
                        require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersVAT.php');
                        CustomersVAT::updateVATAddress(Tools::getValue('id_address'), '');
                        unset($formFields['vat_number']);
                    }
                    else {
                        if (Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required') {
                            $formFields['vat_number']['required'] = true;
                        }
                        else {
                            $formFields['vat_number']['required'] = false;    
                        }   
                    }
                }
                if (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1) {
                    if (!empty($formFields['vat_number']['value'])) {
                        $formFields['company']['required'] = true;    
                    }
                    else {
                        $formFields['company']['required'] = false;
                    }
                } 
            }
            else {
                if (in_array('vat_number', AddressFormat::getOrderedAddressFields($formFields['id_country']['value']))) {
                    $formFields['vat_number']['required'] = $default_vatfield;
                    $formFields['company']['required'] = $default_companyfield;
                }    
            }
        }
        
        if (empty($formFields['firstname']['value'])) {
            $formFields['firstname']['value'] = $context->customer->firstname;
        }
        if (empty($formFields['lastname']['value'])) {
            $formFields['lastname']['value'] = $context->customer->lastname;
        }
        return [
            'id_address' => ($this->getAddress()) ? $this->getAddress()->id : 0,
            'action' => $this->action,
            'errors' => $this->getErrors(),
            'formFields' => $formFields,
        ];
    }
}
