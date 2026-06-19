<?php
/**
* 2007-2021 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2021 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

$DOCUMENT_ROOT = explode('modules', dirname(__FILE__));
require_once($DOCUMENT_ROOT[0].'config/config.inc.php');
require_once($DOCUMENT_ROOT[0].'init.php');

$context = Context::getContext();
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$id_order = Tools::getValue('shipment_ref');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 183;
$id_shop = (int)Tools::getValue('id_shop');
$result_carrier = array();

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://weblabel.dpd.hu/dpd_wow/parcel_status.php?character-encoding:=UTF-8&content-type=application'.
  '/x-www-form-urlencoded&secret=FcJyN7vU7WKPtUh7m1bx&parcel_number='.$tracking_number,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/json"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = Tools::jsonDecode($response);
if (isset($response->errmsg)) {
    $status_error = true;
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = '';
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = 'false';
    $result_carrier['result']['carrier_shipping_status_code'] = 0;

    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = '';


    $result_carrier['result']['module_shipping_status_code'] = 0;
    print_r(json_encode($result_carrier));
    exit;
}
