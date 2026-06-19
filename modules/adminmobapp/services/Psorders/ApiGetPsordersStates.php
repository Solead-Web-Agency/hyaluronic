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
class ApiGetPsordersStates extends Core
{
    public function getData()
    {
        $id_language = (int) Tools::getValue('id_lang');
        
        if (!$id_language || !Validate::isUnsignedId($id_language)) {
            $id_language = $this->context->language->id;
        }

        $states = OrderState::getOrderStates($id_language);

        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'Order states retrieved successfully',
            'data' => $states
        );

        return $this->fetchJSONResponse();
    }
}
