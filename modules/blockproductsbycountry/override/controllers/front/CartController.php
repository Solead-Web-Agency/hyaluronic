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

class CartController extends CartControllerCore
{
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
