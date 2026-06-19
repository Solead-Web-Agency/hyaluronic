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
class ApiGetPsordersDelivery extends Core
{
    public function getData()
    {
        $id_order = (int) Tools::getValue('id_order');
        $id_lang = (int) Tools::getValue('id_lang');
        $language = new Language($id_lang);
        $this->context->language = $language;
        if (!$id_order || !Validate::isUnsignedId($id_order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Invalid or missing order ID'
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

        $orderDeliver = $order->getDeliverySlipsCollection();
        
        if (empty($orderDeliver)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'No Delivery found for this order'
            );
            return $this->fetchJSONResponse();
        }

        $base_url = Tools::getShopDomainSsl(true).__PS_BASE_URI__;
        $token = Configuration::get('ADMINMOBAPP_SHOP_FCM');
        $return_url = $base_url.'datasync?action=delivery&id_order='.$id_order.'&id_lang='.$id_lang.'&token='.$token;
        
        $this->response['response'] = array(
            'status' => 'success',
            'data' => $return_url
        );
        return $this->fetchJSONResponse();
    }
}
