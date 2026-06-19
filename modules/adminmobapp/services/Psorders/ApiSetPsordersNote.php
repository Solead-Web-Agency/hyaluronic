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
class ApiSetPsordersNote extends Core
{
    public function getData()
    {
        $id_order = (int) Tools::getValue('id_order');
        $note = Tools::getValue('note');

        if (!$id_order || !Validate::isUnsignedId($id_order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Invalid or missing order ID'
            );
            return $this->fetchJSONResponse();
        }

        if (!$note || !Validate::isMessage($note)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Invalid or missing note'
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

        $order->note = $note;

        if ($order->save()) {
            $this->response['response'] = array(
                'status' => 'success',
                'message' => 'Order note updated successfully'
            );
        } else {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Failed to update order note'
            );
        }

        return $this->fetchJSONResponse();
    }
}
