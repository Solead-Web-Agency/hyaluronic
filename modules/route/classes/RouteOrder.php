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

class RouteOrder
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
     * Return a list of orders
     *
     * @param int $limit Amount of orders to return, minimum 1 and maximum 100
     *
     * @return mixed|json
     */
    public function getOrders($limit = 10)
    {
        return $this->handler->get('/orders', ['limit' => $limit])['body'];
    }

    /**
     * Return a single order by ID
     *
     * @param string $id The source order id
     *
     * @return mixed|json
     */
    public function getOrder($id)
    {
        $response = $this->handler->get('/orders/' . $id);

        return $response['body'];
    }

    /**
     * Create an order
     *
     * @param mixed $params The order params
     *
     * @return self object
     */
    public function createOrder($params)
    {
        return $this->handler->post('/orders', $params);
    }

    /**
     * Update an order
     *
     * @param string $id The source order id
     * @param mixed $params The order params
     *
     * @return self object
     */
    public function updateOrder($id, $params)
    {
        return $this->handler->post('/orders/' . $id, $params);
    }

    /**
     * Cancel an order
     *
     * @param string $id The source order id
     *
     * @return self object
     */
    public function cancelOrder($id)
    {
        return $this->handler->post('/orders/' . $id . '/cancel');
    }

    /**
     * Upsert an order. If the order exists, will update it, if not, will create.
     *
     * @param string $id The source order id
     * @param mixed $params The order params
     *
     * @return self object
     */
    public function upsertOrder($id, $params)
    {
        return $this->getOrder($id) ? $this->updateOrder($id, $params) : $this->createOrder($params);
    }
}
