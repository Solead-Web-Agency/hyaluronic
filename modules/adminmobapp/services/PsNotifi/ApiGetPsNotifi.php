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
class ApiGetPsNotifi extends Core
{
    public function getData()
    {
        try {
            $customer_count = Configuration::get('ADMINMOBAPP_CUSTOMER_NOTIFY');
            $order_count = Configuration::get('ADMINMOBAPP_ORDER_NOTIFY');

            if (!Validate::isInt($customer_count)) {
                $customer_count = 0;
            }
            if (!Validate::isInt($order_count)) {
                $order_count = 0;
            }
            $this->response['response'] = array(
                'status' => 'success',
                'message' => 'success',
                'data' => [
                    'customer_count' => $customer_count,
                    'order_count' => $order_count
                ]
            );
        } catch (Exception $e) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            );
        }
        return $this->fetchJSONResponse();
    }
}
