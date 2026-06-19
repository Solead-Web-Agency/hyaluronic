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

class OrderController extends OrderControllerCore
{
    public function postProcess()
    {
        parent::postProcess();
        if (Module::isEnabled('advancedvatmanager')) {
            // Compatibility with Advanced Antispam System module
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
            // Compatibility with recaptcha module
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
            // Compatibility with Ei captcha free module
            if (Tools::isSubmit('submitCreate')
                && Module::isInstalled('eicaptcha')
                && Module::isEnabled('eicaptcha')
                && false === Module::getInstanceByName('eicaptcha')->hookActionContactFormSubmitCaptcha(array())
                && !empty($this->errors)
            ) {
                unset($_POST['submitCreate']);
            }
            // Compatibility with module Minimum and maximum purchase product quantity by Idnovate
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
            // Compatibility with module Hide the product prices and disallow purchases by categories, groups, and more by Idnovate
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
            // Compatibility with module One Page Checkout by Presteamshop
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
}