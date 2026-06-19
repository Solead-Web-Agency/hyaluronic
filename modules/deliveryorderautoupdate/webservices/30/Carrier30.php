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
require_once('../../classes/trackingmodel.php');

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
$id_carrier = 30;
$id_shop = (int)Tools::getValue('id_shop');

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://soa-gw.canadapost.ca/vis/track/pin/'.$tracking_number.'/summary',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Basic MzQ2ZmRkOGYwZjkzYjhiODoyMzI4ODViODZlYWI4MGJmNDFjZWNk",
    "Cookie: OWSPRD002TRACK-SERVICE=track-service_02803_s002ptom001"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$result_carrier = array();
$response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
$xml = simplexml_load_string($response);
$file_webcarrier = dirname(__FILE__).'/steps30.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$events = array();
if (isset($xml->message)) {
    $rs = $xml->message;
    $date = date('Y-m-d H:i:s');
    $status = (string)$rs->description;
    $result_status = '';
    $success = false;
} else {
    $rs = $xml->{'pin-summary'};
    $date = (string)$rs->{'event-date-time'};
    $status = (string)$rs->{'event-description'};
    $result_status = '';
    $date = DateTime::createFromFormat('Ymd:His', $date);
    $date = $date->format('Y-m-d H:i:s');
    $success = true;
    $events = array(array(
        'event_code' => $status,
        'event_description' => $status,
        'event_date' => $date,
        'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status)
    ));
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status, false);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
