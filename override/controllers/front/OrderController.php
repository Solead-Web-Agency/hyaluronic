<?php
class OrderController extends OrderControllerCore
{
    /*
    * module: advancedvatmanager
    * date: 2026-06-19 12:02:32
    * version: 1.5.5
    */
    public function postProcess()
    {
        parent::postProcess();
        if (Module::isEnabled('advancedvatmanager')) {
            if (Module::isEnabled('advancedantispamsystem') && Configuration::get('ADVANCEDANTISPAMSYSTEM_ENABLED') == 1) {
                if (Tools::isSubmit('submitCreate')) {
                    $advancedantispamsystem = Module::getInstanceByName('advancedantispamsystem');
                    if ($advancedantispamsystem->displayReCAPTCHA('registration')) {
                        if (!$advancedantispamsystem->recaptchaServerVerification('submitAuthentication')) {
                            $advancedantispamsystem->insertLog($advancedantispamsystem->controller_error_msg['RECAPTCHA_VERIFICATION_CHECKOUT']);
                            unset($_POST['submitCreate']);
                        }
                    }  
                }
            }
            if (Tools::isSubmit('submitCreate') &&
                Module::isInstalled('recaptcha') &&
                Module::isEnabled('recaptcha') &&
                Configuration::get('CAPTCHA_ENABLE_ACCOUNT')
            ) {
                require_once _PS_ROOT_DIR_ . '/modules/recaptcha/recaptcha.php';
                $recaptcha = new Recaptcha();
                $recaptcha->validateCaptcha();
                if (!empty($this->errors)) {
                    unset($_POST['password']);
                }
            }
            if (Tools::isSubmit('submitCreate')
                && Module::isInstalled('eicaptcha')
                && Module::isEnabled('eicaptcha')
                && false === Module::getInstanceByName('eicaptcha')->hookActionContactFormSubmitCaptcha(array())
                && !empty($this->errors)
            ) {
                unset($_POST['submitCreate']);
            }
            if (Module::isEnabled('minpurchase')) {
                include_once(_PS_MODULE_DIR_.'minpurchase/minpurchase.php');
                $mod = new MinpurchaseConfiguration();
                $errors = array();
                if ($cart = Context::getContext()->cart) {
                    $errors = $mod->checkProductsAvailability($cart->getProducts());
                }
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $this->errors[] = $error;
                    }
                }
    
                if (!empty($this->errors)) {
                    $id_lang = Context::getContext()->language->id;
                    $params = array('action' => 'show');
                    Tools::redirect($this->context->link->getPageLink('cart', true, (int)$id_lang, $params));
                }
            }
            if (Module::isEnabled('hideprice')) {
                include_once(_PS_MODULE_DIR_.'hideprice/hideprice.php');
                $mod = new HidepriceConfiguration();
                $errors = $mod->checkProductsAvailability($this->context->cart->getProducts());
    
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $this->errors[] = $error;
                    }
                }
    
                if (!empty($this->errors)) {
                    $params = array('action' => 'show');
                    $this->canonicalRedirection($this->context->link->getPageLink('cart', true, (int)$this->context->language->id, $params));
                }
            }
            if (Module::isEnabled('onepagecheckoutps') && version_compare(Module::getInstanceByName('onepagecheckoutps')->version, '4.1.5', '>=')) {
                if (isset($this->is_active_module)?$this->is_active_module:$this->isModuleActived) {
                    $this->bootstrap();
                    $this->opc->postProcessControllerOPC($this);
                }
            }
            else {
                $advancedvatmanager = Module::getInstanceByName('advancedvatmanager');
                $checkNotAllowCheckoutByBrexit = $advancedvatmanager->checkNotAllowCheckoutByBrexit();
                if($checkNotAllowCheckoutByBrexit !== false) {
                    if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                        if (version_compare(_PS_VERSION_, '1.7.8.0', '<')) {
                            $this->errors[] = $checkNotAllowCheckoutByBrexit;
                        }
                        $this->checkoutProcess->setSteps(array());    
                    }
                    else {
                        if (!Tools::getValue('ajax') || !Tools::getIsset('add') || !Tools::getIsset('update')) {
                            $this->errors[] = $checkNotAllowCheckoutByBrexit;
                            $this->step = 0;
                            if (Tools::getIsset('step')) {
                                Tools::redirect('index.php?controller=order');    
                            }     
                        } 
                    }
                    
                }
            }
        }
    }
    /*
    * module: blockproductsbycountry
    * date: 2026-06-19 12:20:38
    * version: 1.0.2
    */
    public function init()
    {
        parent::init();
        if (Module::isEnabled('blockproductsbycountry')) {
            if (Module::isEnabled('blockproductsbycountry')) {
                $bpbc = Module::getInstanceByName('blockproductsbycountry');
                $context = Context::getContext();
                $id_country = $context->country->id;
                if ($id_country) {
                    foreach ($context->cart->getProducts() as $product) {
                        if ($bpbc->isProductBlocked((int)$product['id_product'], $id_country)) {
                            $this->step = 0;
                            $this->errors[] = sprintf($bpbc->l('An item in your cart (%1s) is not available in %2s.'), Product::getProductName((int)$product['id_product']), $context->country->name[$context->language->id]);
                        }
                    }
                }
            }
        }
    }
}
