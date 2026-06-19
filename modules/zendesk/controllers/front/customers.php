<?php
/**
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
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
use ZendeskAddon\Controller\ZendeskFrontController;

class ZendeskCustomersModuleFrontController extends ZendeskFrontController
{
    public function initContent()
    {
        parent::initContent();

        // Authorization
        $this->checkAuthorization();

        // Email
        $email = Tools::getValue('email');

        if (!Validate::isEmail($email)) {
            $this->showError('Email is not valid');
        }

        // Customers
        $customer = new Customer();
        $customer = $customer->getByEmail($email, null, false);

        if (!Validate::isLoadedObject($customer)) {
            $this->showError('Customer does not exist');
        }

        $orders = Order::getCustomerOrders((int) $customer->id);
        $orders = array_slice($orders, 0, 20); // Only take the 20 first orders

        foreach ($orders as $k => $order) {
            // Date Add
            $orders[$k]['date_add'] = Tools::displayDate($order['date_add'], null, true);

            // Shop
            $shop = new Shop((int) $order['id_shop']);
            $orders[$k]['shop_name'] = $shop->name;

            // State
            $order_state = new OrderState((int) $order['id_order_state'], Context::getContext()->language->id);
            $orders[$k]['state_name'] = $order_state->name;
            $orders[$k]['state_color'] = $order_state->color;

            // Total
            $orders[$k]['total_paid'] = Tools::convertPriceFull($order['total_paid']);

            // Addresses
            $orders[$k]['address_delivery'] = new Address((int) $order['id_address_delivery']);
            $orders[$k]['address_invoice'] = new Address((int) $order['id_address_invoice']);

            // Carrier
            $carrier = new Carrier((int) $order['id_carrier']);
            $orders[$k]['carrier'] = $carrier->name;

            // Products
            $order_obj = new Order((int) $order['id_order']);
            $orders[$k]['products'] = $order_obj->getProductsDetail();

            /*
            foreach ($orders[$k]['products'] as $p => $product) {
                $product_obj = new Product((int)$product['product_id'], false, Context::getContext()->language->id);
                $attribute_obj = new Attribute((int)$product['product_attribute_id'], Context::getContext()->language->id);
                $attribute_group_obj = new AttributeGroup((int)$attribute_obj->id_attribute_group, Context::getContext()->language->id);
                $orders[$k]['products'][$p]['product_name'] = $product_obj->name;
                $orders[$k]['products'][$p]['attribute_name'] = $attribute_obj->name;
                $orders[$k]['products'][$p]['attribute_group_name'] = $attribute_group_obj->name;
            }
            */
        }

        $json = [
            'guest' => false,
            'id' => (int) $customer->id,
            'name' => $customer->firstname . ' ' . $customer->lastname,
            'email' => $customer->email,
            'active' => (bool) $customer->active,
            'admin_url' => Context::getContext()->link->getAdminLink('AdminCustomers', false),
            'created' => $customer->date_add,
            'orders' => $orders,
            'success' => true,
        ];

        $json['success'] = true;

        exit(json_encode($json));
    }
}
