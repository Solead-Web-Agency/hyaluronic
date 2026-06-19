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

class ZendeskPluginModuleFrontController extends ZendeskFrontController
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
        $sql = new DbQuery();
        $sql->select('c.id_customer');
        $sql->from('customer', 'c');
        $sql->where('c.`email` = \'' . pSQL($email) . '\'');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if (empty($result) === true) {
            $this->showError('Customer does not exist');
        }

        $customer = new Customer((int) $result['id_customer']);

        if (!Validate::isLoadedObject($customer)) {
            $this->showError('Customer does not exist');
        }

        $ordersResults = $this->getCustomerOrders((int) $customer->id);

        $orders = [];
        $phone = '';
        foreach ($ordersResults as $oneOrder) {
            $orders[] = $this->getOrderDetail(new Order((int) $oneOrder['id_order']), $phone);
        }

        $json = [
            'guest' => false,
            'id' => (int) $customer->id,
            'name' => $customer->firstname . ' ' . $customer->lastname,
            'email' => $customer->email,
            'phone' => $phone,
            'active' => (bool) $customer->active,
            'admin_url' => Context::getContext()->link->getAdminLink('AdminCustomers', false),
            'created' => $customer->date_add,
            'orders' => $orders,
            'hookActionZendeskDisplayTop' => Hook::exec('actionZendeskDisplayTop', ['customer' => $customer, 'orders' => $orders]),
            'hookActionZendeskDisplayMiddle' => Hook::exec('actionZendeskDisplayMiddle', ['customer' => $customer, 'orders' => $orders]),
            'hookActionZendeskDisplayBottom' => Hook::exec('actionZendeskDisplayBottom', ['customer' => $customer, 'orders' => $orders]),
            'success' => true,
        ];

        $json['success'] = true;

        exit(json_encode($json));
    }

    /**
     * Get last 10 orders id from customer
     *
     * @param int $customerId
     *
     * @return mixed $orders
     */
    private function getCustomerOrders($customerId)
    {
        $sql = '
            SELECT id_order 
            FROM `' . _DB_PREFIX_ . 'orders` o
            WHERE o.`id_customer` = ' . (int) $customerId .
            ' ORDER BY `id_order` DESC 
            LIMIT 0,10';

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$res) {
            return [];
        }

        return $res;
    }
}
