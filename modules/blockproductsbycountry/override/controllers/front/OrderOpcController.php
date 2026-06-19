<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*/

class OrderOpcController extends OrderOpcControllerCore
{
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
    
    protected function _getPaymentMethods()
    {
        if (Module::isEnabled('blockproductsbycountry')) {
            if (Module::isEnabled('blockproductsbycountry')) {
                $bpbc = Module::getInstanceByName('blockproductsbycountry');
                $context = Context::getContext();
                $id_country = $context->country->id;
                if ($id_country) {
                    foreach ($context->cart->getProducts() as $product) {
                        if ($bpbc->isProductBlocked((int)$product['id_product'], $id_country)) {
                            return '<p class="warning">'.sprintf($bpbc->l('An item in your cart (%1s) is not available in %2s.'), Product::getProductName((int)$product['id_product']), $context->country->name[$context->language->id]).'</p>';
                        }
                    }
                }
            }
        }
        return parent::_getPaymentMethods();
    }
}
