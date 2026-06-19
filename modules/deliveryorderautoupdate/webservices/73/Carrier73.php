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

$result_carrier = array();

$apikey = Configuration::get('HL_CARRIER73_id1');
$result_carrier = array();

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api2.postnord.com/rest/shipment/v5/trackandtrace/findByIdentifier.xml?apikey='.$apikey.'&id='.$tracking_number.'&locale=en',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);

$response = json_decode(json_encode(simplexml_load_string($response)), true);
$id_status = 0;
$file_webcarrier = dirname(__FILE__).'/steps73.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
if (isset($response['shipments']['Shipment'])) {
    $events = $response['shipments']['Shipment']['items']['Item']['events']['TrackingEvent'];
    $events = array_map(function ($e) use ($webxml_crr) {
        return array(
            'event_code' => $e["eventCode"],
            'event_description' => $e["eventDescription"],
            'event_date' => date('Y-m-d H:i:s', strtotime($e["eventTime"])),
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e["eventCode"])
        );
    }, $events);
    $event = $events[count($events)-1];
    $server = 1;
    $label_status = $event['event_description'];
    $status = $event['event_code'];
    $date = $event['event_date'];
} else {
    $server = 0;
    $label_status = 'Not found';
    $status = 404;
    $date = date('Y-m-d H:i:s');
}

$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);

$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($server == 0 ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['carrier_shipping_status_code'] = $id_status;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;

//print_R($result_carrier);exit;
echo json_encode($result_carrier);
