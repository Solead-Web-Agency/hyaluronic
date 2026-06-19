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

class RouteappFee extends ObjectModel
{
    public $id;
    public $id_order;
    public $fee_amount;
    public $processed;
    const LIMIT = 100;

    public static $definition = [
        'table' => 'routeapp_fee',
        'primary' => 'id_routeapp_fee',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'fee_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'tax_amount' => ['type' => self::TYPE_FLOAT],
            'tax_class' => ['type' => self::TYPE_STRING],
            'processed' => ['type' => self::TYPE_BOOL],
        ],
    ];

    public static function getByOrderId($id_order)
    {
        $sql = new DbQuery();
        $sql->select('r.*');
        $sql->from('routeapp_fee', 'r');
        $sql->where('r.`id_order` = \'' . pSQL($id_order) . '\'');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!$result) {
            return false;
        }
        $routeapp_fee = new RouteappFee();
        $routeapp_fee->id = $result['id_routeapp_fee'];
        $routeapp_fee->id_order = (int) $result['id_order'];
        $routeapp_fee->fee_amount = (float) $result['fee_amount'];
        $routeapp_fee->tax_class = $result['tax_class'];
        $routeapp_fee->tax_amount = (float) $result['tax_amount'];
        $routeapp_fee->processed = (bool) $result['processed'];

        return $routeapp_fee;
    }

    private function convert($result)
    {
        $fee = new RouteappFee();
        $fee->id = $result['id_routeapp_fee'];
        $fee->id_order = (int) $result['id_order'];
        $fee->fee_amount = (float) $result['fee_amount'];
        $fee->tax_class = $result['tax_class'];
        $fee->tax_amount = (float) $result['tax_amount'];

        return $fee;
    }

    public static function getByOrderIds($id_orders)
    {
        $list = implode(',', array_map('intval', $id_orders));
        $sql = new DbQuery();
        $sql->select('r.*');
        $sql->from('routeapp_fee', 'r');
        $sql->where('r.`id_order` IN (' . $list . ')');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        if (!$results) {
            return [];
        }
        $routeapp_fees = array_map(['RouteappFee', 'convert'], $results);

        return $routeapp_fees;
    }

    public function getNotProcessedOrders($limit = false)
    {
        if (!$limit) {
            $limit = self::LIMIT;
        }
        $sql = new DbQuery();
        $sql->select('r.*');
        $sql->from('routeapp_fee', 'r');
        $sql->where('r.`processed` = 0');
        $sql->limit($limit);

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        if (!$results) {
            return [];
        }
        $routeapp_fees = array_map(['RouteappFee', 'convert'], $results);

        return $routeapp_fees;
    }
}
