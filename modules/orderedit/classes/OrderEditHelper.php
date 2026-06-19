<?php
/**
 * OrderEdit
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @license   See joined file licence.txt
 * @support   silbersaiten <support@silbersaiten.de>
 * @category  Module
 * @version   2.0.8
 * @link      https://www.silbersaiten.de
 */

class OrderEditHelper
{
    public static function getLastOrderStateId($id_order)
    {
        return (int)Db::getInstance()->getValue('
        SELECT `id_order_state`
        FROM `' . _DB_PREFIX_ . 'order_history`
        WHERE `id_order` = ' . (int)$id_order . '
        ORDER BY `date_add` DESC, `id_order_history` DESC');
    }

    public static function saveOriginalOrderBeforeChangeIt($order)
    {
        $inserted = array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'id_address_delivery' => $order->id_address_delivery,
            'id_address_invoice' => $order->id_address_invoice,
            'current_state' => $order->current_state,
            'module' => $order->module,
            'total_paid' => $order->total_paid,
            'total_discounts' => $order->total_discounts,
            'total_shipping' => $order->total_shipping,
            'total_wrapping' => $order->total_wrapping,
            'total_products_wt' => $order->total_products_wt,
            'invoice_number' => $order->invoice_number,
            'delivery_number' => $order->delivery_number,
            'invoice_date' => $order->invoice_date,
            'delivery_date' => $order->delivery_date,
            'valid' => $order->valid,
            'date_add_origin' => $order->date_add,
        );
        return Db::getInstance()->insert('orderedit_history', $inserted);
    }

    public static function getOrderBeforeLastChange($id_order)
    {
        return Db::getInstance()->getRow('
        SELECT *
        FROM `' . _DB_PREFIX_ . 'orderedit_history`
        WHERE `id_order` = ' . (int)$id_order . '
        ORDER BY `id_original` DESC');
    }
}
