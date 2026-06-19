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
class ApiGetSettingsConnection extends Core
{
    public function getData()
    {
        $connection_key = Tools::getValue('connection_key');
        $shop_key = Configuration::get('ADMINMOBAPP_CONNECTION_KEY');
        if ($connection_key === $shop_key) {
            $response = array(
                'status' => 'success',
                'message' => 'Authorization successful. Allowed to connect with site.',
                'data' => array(
                    'access' => 'allowed'
                )
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Authorization failed. Invalid connection key.',
                'data' => array(
                    'access' => 'denied'
                )
            );
        }
        $this->response['response'] = $response;
        return $this->fetchJSONResponse();
    }
}
