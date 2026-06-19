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

class RevolutpaymentValidationModuleFrontController extends ModuleFrontController
{


    public function postProcess()
    {
        /**
         * Get current cart object from session
         */
        $cart = $this->context->cart;
        $authorized = false;

        /**
         * Verify if this module is enabled and if the cart has
         * a valid customer, delivery address and invoice address
         */
        if (!$this->module->active
            || $cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        /**
         * Verify if this payment module is authorized
         */
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'revolutpayment') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            $this->displayError($this->l('This payment method is not available.'));
            return;
        }

        $public_id = Tools::getValue('public_id');
        $revolut_order = $this->module->getRevolutOrderByIdCart($cart->id);

        if (empty($public_id) || empty($revolut_order['id_revolut_order']) || $revolut_order['public_id'] != $public_id) {
            $this->displayError($this->l('Something went wrong while taking the payment'));
            return;
        }

        /** @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);

        /**
         * Check if this is a vlaid customer account
         */
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $id_new_order = (int)$this->module->createPrestaShopOrder($customer);

        if (!$id_new_order) {
            PrestaShopLogger::addLog('Error: Can not create an order', 3);
            $this->displayError('Something went wrong while taking the payment');
            return;
        }

        $revolut_order = $this->module->getRevolutOrder($id_new_order);

        if (empty($revolut_order['id_revolut_order'])) {
            $this->displayError('Something went wrong while taking the payment');
            return;
        }

        $this->module->processRevolutOrderResult($id_new_order, $revolut_order);

        Tools::redirect($this->module->getOrderConfirmationLink($cart->id, $customer->secure_key, $id_new_order));
    }

    protected function displayError($error_message)
    {
        $this->context->smarty->assign('error', $error_message);

        if ($this->module->isPs17) {
            $this->setTemplate('module:' . $this->module->name .
                '/views/templates/front/version17/payment_error.tpl');
        } else {
            $this->setTemplate('version16/payment_error.tpl');
        }
    }
}
