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

class Cart extends CartCore
{
    public function checkProductsAccess()
    {
        $return = parent::checkProductsAccess();
        $context = Context::getContext();
        if (!$return) {
            if (empty($context->controller->php_self) || $context->controller->php_self == 'order' || $context->controller->php_self == 'order-opc') {
                return $return;
            }
            if (Module::isEnabled('blockproductsbycountry')) {
                $bpbc = Module::getInstanceByName('blockproductsbycountry');
                $id_country = $context->country->id;
                if ($id_country) {
                    foreach ($this->getProducts() as $product) {
                        if ($bpbc->isProductBlocked((int)$product['id_product'], $id_country)) {
                            return $product['id_product'];
                        }
                    }
                }
            }
        }
        return $return;
    }
}
