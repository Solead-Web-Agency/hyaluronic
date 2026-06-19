<?php
/**
 * A Route Prestashop Extension that adds secure shipping
 * protection to your orders
 *
 * Php version 7.0^
 *
 * @author    Route Development Team <dev@routeapp.io>
 * @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
 * @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
 */

class AdminRouteappController extends ModuleAdminController
{
    public function ajaxProcessGetOrders()
    {
        $order_ids = Tools::getValue('order_ids');
        $route_fees = RouteappFee::getByOrderIds($order_ids);
        $res = [];
        foreach ($route_fees as $routeapp_fee) {
            $res[$routeapp_fee->id_order] = Tools::displayPrice($routeapp_fee->fee_amount);
        }
        die(Tools::jsonEncode($res));
    }
}
