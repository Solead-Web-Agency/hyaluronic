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
require_once 'RouteSentry.php';

class RouteMerchant
{
    /**
     * @var mixed|RouteApi
     */
    private $handler;

    /**
     * RouteMerchant constructor.
     *
     * @param string $token
     * @param string $env
     * @param null $handler
     */
    public function __construct($token = '', $env = 'stage', $handler = null)
    {
        if (isset($handler)) {
            $this->handler = $handler;
        } else {
            $this->handler = new RouteApi($token, $env);
        }
    }

    /**
     * Returns a single Merchant
     *
     * @return mixed|json|false
     */
    public function getMerchant()
    {
        $merchantId = RouteSetup::getMerchantId();
        $merchantResponse = false;
        try {
            $merchantResponse = $this->handler->get('/merchants/' . $merchantId, null, true);

            if (isset($merchantResponse['body']) && !empty($merchantResponse['body'])) {
                return $merchantResponse['body'];
            }
        } catch (Exception $e) {
            RouteSentry::track('error', $e->getMessage(), debug_backtrace(), [
                'merchantId' => $merchantId,
                'merchantResponse' => $merchantResponse,
            ]);
        }

        return false;
    }
}
