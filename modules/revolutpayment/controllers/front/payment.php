<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RevolutpaymentPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;

        parent::initContent();

        $cart = $this->context->cart;

        // create revolut order
        $public_id = $this->module->createRevolutOrder();
        if ($public_id == '') {
            return; // set error
        }


        // address data
        $customer_name = $this->context->customer->firstname . ' ' . $this->context->customer->lastname;
        $customer_email = $this->context->customer->email;
        $address = new Address($this->context->cart->id_address_delivery);
        $state = new State($address->id_state);
        $country = new Country($address->id_country);

        // payment data
        $merchant_type = 'prod';
        if ($this->module->revolutApi->sandboxEnable) {
            $merchant_type = 'sandbox';
        }

        $this->context->smarty->assign([
            'revolutpayment_path' => $this->module->getPathUri(),
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'controller_link' => Context::getContext()->link->getModuleLink($this->module->name, 'validation', array()),
            'public_id' => $public_id,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'address' => $address,
            'state' => $state,
            'country' => $country,
            'revolut_payment_title' => $this->module->checkoutDisplayName,
            'merchant_type' => $merchant_type,
            'payment_description' => $this->module->paymentDescription,
            'locale' => $this->context->language->iso_code,
        ]);

        if ($this->module->isPs17) {
            return $this->setTemplate('module:' . $this->module->name . '/views/templates/front/version17/payment_page.tpl');
        }

        return $this->setTemplate('version16/payment_page.tpl');
    }
}
