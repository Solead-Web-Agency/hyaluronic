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
class ApiSetFirebaseDevice extends Core
{
    public function getData()
    {
        $token = Tools::getValue('token');
        $device = Tools::getValue('device');
        $os = Tools::getValue('os');

        if (empty($token)) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid data:'
            );
            return $this->fetchJSONResponse();
        }

        if (empty($device) || !is_string($device)) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid data:'
            );
            return $this->fetchJSONResponse();
        }

        if (empty($os) || !is_string($os)) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid data:'
            );
            return $this->fetchJSONResponse();
        }

        try {
            Configuration::updateValue('ADMINMOBAPP_FCM_TOKEN', pSQL($token));
            Configuration::updateValue('ADMINMOBAPP_FCM_DEVICE', pSQL($device));
            Configuration::updateValue('ADMINMOBAPP_FCM_OS', pSQL($os));

            $this->response['response'] = array(
                'status' => 'success',
                'message' => 'success',
                'data' => [
                    'token' => $token,
                    'device' => $device,
                    'os' => $os
                ]
            );
            return $this->fetchJSONResponse();

        } catch (Exception $e) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred while updating data:'
            );
            return $this->fetchJSONResponse();

        }
    }

}
