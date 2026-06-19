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

class RouteappShipment extends ObjectModel
{
    public $id;
    public $id_order;
    public $id_order_carrier;
    public $tracking_number;
    public $processed;
    const LIMIT = 100;

    public static $definition = [
        'table' => 'routeapp_shipment',
        'primary' => 'id_routeapp_shipment',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_order_carrier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'tracking_number' => ['type' => self::TYPE_STRING],
            'processed' => ['type' => self::TYPE_BOOL],
        ],
    ];

    private function convert($result)
    {
        $shipment = new RouteappShipment();
        $shipment->id = $result['id_routeapp_shipment'];
        $shipment->id_order = (int) $result['id_order'];
        $shipment->id_order_carrier = (int) $result['id_order_carrier'];
        $shipment->tracking_number = $result['tracking_number'];
        $shipment->processed = $result['processed'];

        return $shipment;
    }

    public static function getByOrderId($id_order)
    {
        $sql = new DbQuery();
        $sql->select('r.*');
        $sql->from('routeapp_shipment', 'r');
        $sql->where('r.`id_order` = \'' . pSQL($id_order) . '\'');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!$result) {
            return false;
        }
        $routeapp_shipment = new RouteappShipment();
        $routeapp_shipment->id = $result['id_routeapp_shipment'];
        $routeapp_shipment->id_order = (int) $result['id_order'];
        $routeapp_shipment->id_order_carrier = (int) $result['id_order_carrier'];
        $routeapp_shipment->tracking_number = $result['tracking_number'];
        $routeapp_shipment->processed = (bool) $result['processed'];

        return $routeapp_shipment;
    }

    public function getNotProcessedShipments($limit = false)
    {
        if (!$limit) {
            $limit = self::LIMIT;
        }
        $sql = new DbQuery();
        $sql->select('r.*');
        $sql->from('routeapp_shipment', 'r');
        $sql->where('r.`processed` = 0');
        $sql->limit($limit);

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        if (!$results) {
            return [];
        }
        $routeapp_shipments = array_map(['RouteappShipment', 'convert'], $results);

        return $routeapp_shipments;
    }
}
