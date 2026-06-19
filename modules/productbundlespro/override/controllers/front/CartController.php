<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

class CartController extends CartControllerCore
{
    /**
     * if no other PPBS customizations exist, then only Prestashop customization exists which needs to be deleted
     */
    protected function updateCart()
    {
        include_once(_PS_MODULE_DIR_ . '/productbundlespro/lib/bootstrap.php');
        $id_shop = $this->context->shop->id;

        if (PBPConfigHelper::getBundleDiscountCombinable($id_shop)) {
            parent::updateCart();
            return;
        }

        if (CartRule::isFeatureActive()) {
            if (Tools::getIsset('addDiscount') && PBPVoucherHelper::cartHasBundleVoucher($this->context->cart->id)) {
                $this->errors[] = $this->trans(
                    'The voucher is not combinable with bundle discounts',
                    array(),
                    'Shop.Notifications.Error'
                );
            }
        }
        parent::updateCart();
    }
}
