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
class ApiDetPsNotifi extends Core
{
    public function getData()
    {
        try {
            $action = Tools::getValue('action');
            
            if ($action !== 'order' && $action !== 'customer') {
                throw new Exception('Invalid action value');
            }
            if ($action === 'order') {
                Configuration::updateValue('ADMINMOBAPP_ORDER_NOTIFY', 0);
            } elseif ($action === 'customer') {
                Configuration::updateValue('ADMINMOBAPP_CUSTOMER_NOTIFY', 0);
            }
            $this->response['response'] = array(
                'status' => 'success',
                'message' => 'Action executed successfully'
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
