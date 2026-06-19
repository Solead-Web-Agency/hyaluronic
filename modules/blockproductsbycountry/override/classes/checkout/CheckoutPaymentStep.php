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

class CheckoutPaymentStep extends CheckoutPaymentStepCore
{
    public function render(array $extraParams = array())
    {
        if (Module::isEnabled('blockproductsbycountry')) {
            $bpbc = Module::getInstanceByName('blockproductsbycountry');
            $context = Context::getContext();
            $id_country = $context->country->id;
            if ($id_country) {
                foreach ($context->cart->getProducts() as $product) {
                    if ($bpbc->isProductBlocked((int)$product['id_product'], $id_country)) {
                        $extraParams['bpbc_error_message'] = sprintf($bpbc->l('An item in your cart (%1s) is not available in %2s.'), Product::getProductName((int)$product['id_product']), $context->country->name[$context->language->id]);
                        return $this->renderTemplate('module:blockproductsbycountry/views/templates/hook/payment_step.tpl', $extraParams);
                    }
                }
            }
            return parent::render($extraParams);
        }
    }
}
