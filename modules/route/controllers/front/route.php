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

class RouteRouteModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $pricesToFormat = Tools::getValue('pricesToFormat');

        if ($pricesToFormat) {
            $res = [];
            foreach ($pricesToFormat as $priceToFormat) {
                array_push($res, Tools::displayPrice($priceToFormat));
            }
            die(Tools::jsonEncode($res));
        } else {
            $is_insured = Tools::getValue('is_route_insured', null);
            if (is_null($is_insured)) {
                die(Tools::jsonEncode([]));
            }
            $insurance_price = Tools::getValue('insurance_price');
            $last_price = 0.00;
            if (Context::getContext()->cookie->route_insurace_price) {
                $last_price = Context::getContext()->cookie->route_insurace_price;
            }

            $json = [
                'last_price' => $last_price,
                'formatted_price' => Tools::displayPrice($insurance_price),
            ];

            Context::getContext()->cookie->route_insurace_selected = $is_insured;
            Context::getContext()->cookie->route_insurace_price = $insurance_price;
            die(Tools::jsonEncode($json));
        }
    }

    public function setMedia()
    {
        parent::setMedia();
    }
}
