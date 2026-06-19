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

class RouteCron
{
    const INVALID_STATUSES = ['CANCELED', 'PAYMENT ERROR', 'REFUNDED'];

    /**
     * Process orders and send to Route API
     *
     * @return int
     */
    public static function processOrders()
    {
        if (!RouteSetup::isInstalled()) {
            return 0;
        }

        $count = 0;
        $routeFeeClass = new RouteappFee();
        $route_fees = $routeFeeClass->getNotProcessedOrders();
        if ($route_fees) {
            $routeApi = new RouteOrder(
                RouteSetup::getSecretToken(),
                RouteSetup::getEnvironment()
            );
            foreach ($route_fees as $routeapp_fee) {
                try {
                    $order = new Order((int) $routeapp_fee->id_order);
                } catch (PrestaShopDatabaseException | PrestaShopException $e) {
                    RouteSentry::track(
                        'error',
                        'Error trying to load order: ' . $e->getMessage(),
                        debug_backtrace(),
                        ['orderId' => $routeapp_fee->id_order]
                    );
                }
                if (Validate::isLoadedObject($order)) {
                    $status = Tools::strtoupper($order->current_state);
                    $routeParsedOrder = RouteHelper::buildRouteOrder((int) $routeapp_fee->id_order);

                    if (!in_array($status, self::INVALID_STATUSES)) {
                        $routeApi->upsertOrder($routeParsedOrder['source_order_id'], $routeParsedOrder);
                    }

                    try {
                        $routeapp_fee->processed = 1;
                        $routeapp_fee->update();
                        ++$count;
                    } catch (PrestaShopDatabaseException | PrestaShopException $e) {
                        RouteSentry::track(
                            'error',
                            'Error trying to mark fee as processed: ' . $e->getMessage(),
                            debug_backtrace(),
                            ['orderId' => $routeParsedOrder['source_order_id']]
                        );
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Process shipments and send to Route API
     *
     * @return int
     */
    public static function processShipments()
    {
        if (!RouteSetup::isInstalled()) {
            return 0;
        }

        $count = 0;
        $routeShipmentClass = new RouteappShipment();
        $route_shipments = $routeShipmentClass->getNotProcessedShipments();

        $routeApiOrder = new RouteOrder(
            RouteSetup::getSecretToken(),
            RouteSetup::getEnvironment()
        );

        $routeApiShipment = new RouteShipment(
            RouteSetup::getSecretToken(),
            RouteSetup::getEnvironment()
        );

        if ($route_shipments) {
            foreach ($route_shipments as $route_shipment) {
                try {
                    $order = new Order((int) $route_shipment->id_order);
                } catch (PrestaShopDatabaseException | PrestaShopException $e) {
                    RouteSentry::track(
                        'error',
                        'Error trying to load order: ' . $e->getMessage(),
                        debug_backtrace(),
                        ['orderId' => $route_shipment->id_order]
                    );
                }
                if (Validate::isLoadedObject($order)) {
                    $routeappFee = RouteappFee::getByOrderId($route_shipment->id_order);
                    if (isset($routeappFee->id)) {
                        if ($routeappFee->processed == 0) {
                            //send order to Route
                            $status = Tools::strtoupper($order->current_state);
                            $routeParsedOrder = RouteHelper::buildRouteOrder((int) $route_shipment->id_order);

                            if (!in_array($status, self::INVALID_STATUSES)) {
                                $routeApiOrder->upsertOrder($routeParsedOrder['source_order_id'], $routeParsedOrder);
                                $routeappFee->processed = 1;
                                $routeappFee->update();
                            }
                        }

                        if ($routeappFee->processed == 1) {
                            //send shipment
                            $carrier = new Carrier($order->id_carrier);
                            $shipment = RouteHelper::buildRouteShipment($order, $carrier);
                            $shippingNumber = $order->shipping_number;

                            //update shipment on custom table with order data
                            $route_shipment->tracking_number = $shippingNumber;
                            $route_shipment->id_order_carrier = $order->id_carrier;
                            $route_shipment->update();

                            $orderResponse = $routeApiOrder->getOrder($order->reference);
                            if (isset($orderResponse['id'])) {
                                // cancel all existing shipments for this order, except the new one
                                // we will only be upserting latest shipment
                                // since PS only allows one shipment per order
                                $routeApiShipment->cancelShipment($orderResponse['id'], $routeParsedOrder['source_order_id'], $shippingNumber);
                            }
                            $response = false;
                            if (isset($shippingNumber) && $shippingNumber != '') {
                                $response = $routeApiShipment->upsertShipment($shipment['tracking_number'], $shipment, $orderResponse['id']);
                            }
                            $accepted_response_statuses = [200, 201];
                            if (isset($response['status_code']) &&
                                in_array($response['status_code'], $accepted_response_statuses)) {
                                $route_shipment->processed = 1;
                                $route_shipment->update();
                                ++$count;
                            }
                        }
                    }
                }
            }
        }

        return $count;
    }
}
