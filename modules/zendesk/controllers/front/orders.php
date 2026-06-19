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

class ZendeskOrdersModuleFrontController extends ZendeskFrontController
{
    public function initContent()
    {
        parent::initContent();

        // Authorization
        $this->checkAuthorization();

        // Order ref.
        $order_reference = Tools::getValue('reference', 0);

        if (!Validate::isReference($order_reference)) {
            $this->showError('Order reference is not valid');
        }

        // Order
        $orders = Order::getByReference(pSQL($order_reference));

        if (!$order = $orders->getFirst()) {
            $this->showError('Order does not exist');
        }

        $json = $this->getOrderDetail($order);

        $json['success'] = true;

        exit(json_encode($json));
    }
}
