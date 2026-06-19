<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Core.php');
class ApiSetPsordersStates extends Core
{
    public function getData()
    {
        $id_order = (int) Tools::getValue('id_order');
        $id_order_state = (int) Tools::getValue('id_order_state');

        if (!$id_order || !Validate::isUnsignedId($id_order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Invalid or missing order ID'
            );
            return $this->fetchJSONResponse();
        }

        if (!$id_order_state || !Validate::isUnsignedId($id_order_state)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Invalid or missing order state ID'
            );
            return $this->fetchJSONResponse();
        }

        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Order not found'
            );
            return $this->fetchJSONResponse();
        }

        $orderState = new OrderState($id_order_state);
        if (!Validate::isLoadedObject($orderState)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Order state not found'
            );
            return $this->fetchJSONResponse();
        }

        $orderHistory = new OrderHistory();
        $orderHistory->id_order = $order->id;
        $orderHistory->changeIdOrderState($id_order_state, $order->id);
        $orderHistory->add();

        // Prepare the response
        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'Order state updated successfully'
        );

        return $this->fetchJSONResponse();
    }

}
