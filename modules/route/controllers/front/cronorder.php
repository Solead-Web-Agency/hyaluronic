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

include _PS_MODULE_DIR_ . 'route' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'RouteCron.php';

class RouteCronorderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $routeCron = new RouteCron();
        $json = [
            'cronjob_orders_execution' => $routeCron->processOrders(),
        ];

        die(Tools::jsonEncode($json));
    }

    public function setMedia()
    {
        parent::setMedia();
    }
}
