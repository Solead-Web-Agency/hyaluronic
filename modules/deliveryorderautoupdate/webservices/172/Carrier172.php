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
$id_order = Tools::getValue('shipment_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}

$id_carrier = 175;
$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'api-gw.dhlparcel.nl/track-trace?key='.$tracking_number.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Cookie: __cfruid=0185369450cbe5fb08dd157c737c760005fb18c5-1614325308'
  ),
));

$rs = curl_exec($curl);

curl_close($curl);
$response = Tools::jsonDecode($rs, true);
$file_webcarrier = dirname(__FILE__).'/steps172.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

$id_status = '100';
$success = false;
$status = 'E206';
$date = date("Y-m-d H:i:s");
$result_status = '';
$events = array();
if ($response) {
    if (count($response)) {
        $response = reset($response);
        if (isset($response['events']) && count($response['events'])) {
            $events = array_map(function ($e) use ($webxml_crr) {
                return array(
                    'event_code' => $e['status'],
                    'event_description' => $e['status'],
                    'event_date' => $e['timestamp'],
                    'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e['status'])
                );
            }, $response['events']);
            $event = end($response['events']);
            $success = true;
            $status = (string)$event['status'];
            $date = $event['timestamp'];
            $result_status = (string)$event['status'];
        }
    }
} else {
    $result_status = $rs;
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
