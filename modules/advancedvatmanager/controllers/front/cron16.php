<?php
/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */
 
/* Includes files */
require_once(_PS_MODULE_DIR_ . 'advancedvatmanager/classes/ValidationEngine.php');
require_once(_PS_MODULE_DIR_ . 'advancedvatmanager/classes/CustomersVAT.php');

class AdvancedVatManagerCron16ModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $ssl = true;
    public $ajax;

    public function display()
    {
        $this->ajax = 1;
        
        if (!in_array(php_sapi_name(), array('apache', 'apache2handler', 'cgi', 'cgi-fcgi', 'cli', 'cli-server', 'embed', 'fpm-fcgi', 'litespeed', 'phpdbg'))) {
            return die('Forbidden call.');
        }
        
        /* Check to security tocken */
        if (Tools::substr(Tools::hash('advancedvatmanager'), 0, 12) != Tools::getValue('token')) {
            return die('Bad token');
        }
        if (!Module::isEnabled('advancedvatmanager')) {
            return die('Module disabled.');
        }
        /* Check shop id */
        if (Tools::getValue('id_shop') == Context::getContext()->shop->id) {
            ValidationEngine::$checkVatCron = true;
            if (Configuration::get('ADVANCEDVATMANAGER_CRON_SCANFROMLAST')) {
                $addresses = CustomersVAT::getCustomerAddresses(CustomersVAT::getLastCustomerAddressChecked());  
            }
            else {
                $addresses = CustomersVAT::getCustomerAddresses(); 
                // Truncate table
                CustomersVAT::truncateTable(); 
            }
            
            if ($addresses) {
                foreach ($addresses as $address) {
                    $id_customer = $address['id_customer'];
                    $id_country = $address['id_country'];
                    $id_address = $address['id_address'];
                    $company = $address['company'];
                    $vat = $address['vat_number'];
                    $module = Module::getInstanceByName('advancedvatmanager');
                    $ve = new ValidationEngine($vat);
                    $ve->VATValidationProcess($id_country, $id_customer, $id_address, $company);
                    $valid = $ve->getVATValidation();

                    if (!ValidationEngine::$skip_validation_process) {
                        // Insert element into table ps_advancedvatmanager_customers
                        $cv = new CustomersVAT();
                        if ($cv->addCustomersVAT(ValidationEngine::getVat(), $id_customer, $id_address, ValidationEngine::getVATValidation(),ValidationEngine::$company_valid, Configuration::get('ADVANCEDVATMANAGER_CRON_AUTOINSERT_COMPANY')?ValidationEngine::getRegisteredCompanyName():null, ValidationEngine::getStatus(), ValidationEngine::getSystemFail())) {
                             echo sprintf('The address with ID#%s and VAT number %s has been saved into the Customer VAT list',$id_address, ValidationEngine::getVat()).'<br />';
                        }
                        else {
                            echo sprintf('The address with ID#%s and VAT number %s could not be saved into the Customer VAT list',$id_address, ValidationEngine::getVat()).'<br />';
                        }
                        if (Configuration::get('ADVANCEDVATMANAGER_CRON_ASSIGN_GROUP')) {
                            ValidationEngine::manageCustomerGroups($id_country, $id_customer, $id_address, $valid);
                        } 
                        
                        if (!$ve->getVATValidation()) {
                            if (Configuration::get('ADVANCEDVATMANAGER_CRON_DELETE_EMPTY') && !ValidationEngine::getVat()) {
                                CustomersVAT::deleteByIDAddress($id_address);
                                if (Db::getInstance()->update('address', array('deleted' => '1'), 'id_address ='.(int)$id_address)) {
                                    echo sprintf('The address with ID#%s has been deleted',$id_address).'<br />';
                                }
                                else {
                                    echo sprintf('The address with ID#%s could not be deleted',$id_address).'<br />';    
                                }  
                            } 
                            if (Configuration::get('ADVANCEDVATMANAGER_CRON_DELETE_INVALID')) {
                                CustomersVAT::deleteByIDAddress($id_address);
                                if (Db::getInstance()->update('address', array('deleted' => '1'), 'id_address ='.(int)$id_address)) {
                                    echo sprintf('The address with ID#%s has been deleted',$id_address).'<br />';
                                }
                                else {
                                    echo sprintf('The address with ID#%s could not be deleted',$id_address).'<br />';    
                                }  
                            }
                            if (Configuration::get('ADVANCEDVATMANAGER_CRON_DELETE_DUPLICATED') && ValidationEngine::$duplicated) {
                                CustomersVAT::deleteByIDAddress($id_address);
                                if (Db::getInstance()->update('address', array('deleted' => '1'), 'id_address ='.(int)$id_address)) {
                                    echo sprintf('The address with ID#%s has been deleted',$id_address).'<br />';
                                }
                                else {
                                    echo sprintf('The address with ID#%s could not be deleted',$id_address).'<br />';    
                                }    
                            }
                            if (Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY') && !ValidationEngine::getVat()) {
                                if ($module->sendEmail(2, CustomersVAT::getCustomerAddress($id_customer, $id_address))) {
                                    echo sprintf('Email to customer ID#%s has been sent successfully.',$id_customer).'<br />';    
                                }
                                else {
                                    echo sprintf('Email to customer ID#%s could not be sent due an error.',$id_address).'<br />';
                                }    
                            } 
                            if (Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_DUPLICATED') && ValidationEngine::$duplicated) {
                                if ($module->sendEmail(5, CustomersVAT::getCustomerAddress($id_customer, $id_address))) {
                                    echo sprintf('Email to customer ID#%s has been sent successfully.',$id_customer).'<br />';        
                                }
                                else {
                                    echo sprintf('Email to customer ID#%s could not be sent due an error.',$id_address).'<br />';    
                                }    
                            }
                            if (Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID')) {
                                if ($module->sendEmail(3, CustomersVAT::getCustomerAddress($id_customer, $id_address))) {
                                    echo sprintf('Email to customer ID#%s has been sent successfully.',$id_customer).'<br />';         
                                }
                                else {
                                    echo sprintf('Email to customer ID#%s could not be sent due an error.',$id_address).'<br />';        
                                }        
                            }
                        }
                        if (Configuration::get('ADVANCEDVATMANAGER_CRON_AUTOINSERT_COMPANY') == 0) {
                            // If option "company validation" is enabled
                            if (Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_INVALID_COMPANY') && ValidationEngine::$company_valid == 0) {
                                if ($module->sendEmail(6, CustomersVAT::getCustomerAddress($id_customer, $id_address))) {
                                    echo sprintf('Email to customer ID#%s has been sent successfully.',$id_customer).'<br />';         
                                }
                                else {
                                    echo sprintf('Email to customer ID#%s could not be sent due an error.',$id_address).'<br />';        
                                }        
                            }
                            if (Configuration::get('ADVANCEDVATMANAGER_CRON_SENDEMAIL_EMPTY_COMPANY') && empty($company)) {
                                if ($module->sendEmail(7, CustomersVAT::getCustomerAddress($id_customer, $id_address))) {
                                    echo sprintf('Email to customer ID#%s has been sent successfully.',$id_customer).'<br />';         
                                }
                                else {
                                    echo sprintf('Email to customer ID#%s could not be sent due an error.',$id_address).'<br />';        
                                }        
                            }
                        } 
                    }
                }
            }
            else {
                return die('No address to check.'); 
            }
        }
        else {
            return die('Bad Shop ID');
        }
    }
}
