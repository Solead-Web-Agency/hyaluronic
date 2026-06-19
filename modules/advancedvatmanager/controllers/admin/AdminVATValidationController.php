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

class AdminVATValidationController extends ModuleAdminController
{
    public function __construct()
    {
        $this->controller_name = 'AdminVATValidationController';
        parent::__construct();
    }
    
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
    }
    
    public function setMedia($isNewTheme = true)
    {
        parent::setMedia(); 
    }
    
    public function postProcess() {
        parent::postProcess(); 
    }

    public function ajaxProcessCheckVATnumberAdminAddresses()
    {
        if (Tools::getValue('token')) {
            $vat_number = new ValidationEngine(Tools::getValue('vat_number'));
            $result = $vat_number->VATValidationProcess(Tools::getValue('country'), Tools::getValue('id_customer'), Tools::getValue('address'), Tools::getValue('company'));               
            $message = $vat_number->getMessage();
            $company = true;
            if (ValidationEngine::$skip_validation_process) {
                 die(json_encode(array('success'=> true)));
            }
            if (Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') == 1 && ValidationEngine::getVATValidation() && ValidationEngine::$company_valid == 0) {
                $company = false;
                $result = false; 
            }
            else if (!ValidationEngine::getVATValidation() && ValidationEngine::$skip_validation_process === false) {
                $result = false;       
            }
            else if (!ValidationEngine::getVATValidation() && ValidationEngine::$skip_validation_process === true) {
                $result = true; 
            }
            
            die(json_encode(array('message'=> $message, 'success'=> $result, 'company'=> $company)));
        }
        else {
            die('Token is not valid!');
        }
    }
    
}
