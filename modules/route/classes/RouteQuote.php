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

require_once 'RouteApi.php';

class RouteQuote
{
    public function __construct($token = '', $env = '', $handler = null)
    {
        if (isset($handler)) {
            $this->handler = $handler;
        } else {
            $this->handler = new RouteApi($token, $env);
        }
    }

    /**
     * Shortcut for Get Quote request
     *
     * @param float $subtotal
     * @param string $currency
     *
     * @return mixed|json
     */
    public function getQuote($subtotal = 0, $currency = 'USD')
    {
        $params = [
            'subtotal' => $subtotal,
            'currency' => $currency,
        ];

        return $this->handler->get('/quote', $params, true);
    }
}
