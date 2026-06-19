<?php
class CartController extends CartControllerCore
{
    /*
    * module: advancedvatmanager
    * date: 2026-06-19 12:02:32
    * version: 1.5.5
    */
    public function displayAjaxUpdate()
    {
        if (Module::isEnabled('advancedvatmanager')) {
            $advancedvatmanager = Module::getInstanceByName('advancedvatmanager');
            $checkNotAllowCheckoutByBrexit = $advancedvatmanager->checkNotAllowCheckoutByBrexit();
            if ($checkNotAllowCheckoutByBrexit !== false) {
                if (isset($this->updateOperationError)) {
                    $this->updateOperationError[] = $checkNotAllowCheckoutByBrexit;
                }
                else {
                    $this->errors[] = $checkNotAllowCheckoutByBrexit;     
                }   
            }
        }      
        parent::displayAjaxUpdate();
    }
    /*
    * module: advancedvatmanager
    * date: 2026-06-19 12:02:32
    * version: 1.5.5
    */
    public function postProcess()
    {
        if (Module::isEnabled('advancedvatmanager')) {
            $advancedvatmanager = Module::getInstanceByName('advancedvatmanager');
            $checkNotAllowCheckoutByBrexit = $advancedvatmanager->checkNotAllowCheckoutByBrexit();
            if (!Tools::getValue('ajax') && $checkNotAllowCheckoutByBrexit !== false) {
                if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                    $this->errors[] =  $checkNotAllowCheckoutByBrexit;
                }
                else {
                    if (!Tools::getIsset('add') || !Tools::getIsset('update')) {
                        $this->errors[] =  $checkNotAllowCheckoutByBrexit;    
                    }    
                }
            }
        }
        parent::postProcess();
    }
    /*
    * module: blockproductsbycountry
    * date: 2026-06-19 12:20:38
    * version: 1.0.2
    */
    protected function processChangeProductInCart()
    {
        if (Module::isEnabled('blockproductsbycountry')) {
            if ($this->id_product) {
                $context = Context::getContext();
                if (!empty($context->country)) {
                    $country = $context->country;
                    if (!empty($country->id)) {
                        $id_country = $country->id;
                        $bpbc_module = Module::getInstanceByName('blockproductsbycountry');
                        if ($bpbc_module->isProductBlocked($this->id_product, $id_country)) {
                            $this->errors[] = Tools::displayError($bpbc_module->getBlockedText($this->id_product), !Tools::getValue('ajax'));
                            return;
                        }
                    }
                }
            }
        }
        return parent::processChangeProductInCart();
    }
}
