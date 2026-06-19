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
$tracking_number = Tools::strtoupper($tracking_number);
$conf_token = Configuration::get('DELIVERY_TOKEN');
if ($conf_token != $token) {
    die("wrong token");
}
$id_shop = Tools::getValue('id_shop');
$client_secret = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER134_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$client_id = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER134_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.ctt.pt/cttorg/clients/tracktracers/api/v2/events/search',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
	"objects": [
        {"objectNumber": "'.$tracking_number.'"}
	]
}',
  CURLOPT_HTTPHEADER => array(
    'x-ibm-Client-Secret: '.$client_secret.'',
    'x-ibm-Client-ID: '.$client_id.'',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$file_webcarrier = dirname(__FILE__).'/steps134.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 100;
$events = array();
if (!$response) {
    $success = false;
    $status = 106;
    $desc = '';
    $date = Date('Y-m-d H:i:s');
    $id_status = 106;
} else {
    $response = Tools::jsonDecode($response, true);
    $success = true;
    $events = array_map(function ($e) use ($webxml_crr) {
        return array(
            'event_code' => $e['eventTypeCode'],
            'event_description' => $e['eventDesig'],
            'event_date' => date('Y-m-d H:i:s', strtotime($e['eventDate'].' '.$e['eventHour'])),
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e['eventTypeCode']),
        );
    }, $response);
    $event = end($events);
    $status = $event['event_code'];
    $desc = $event['event_description'];
    $date = $event['event_date'];
    $id_status = $event['id_status'];
}
$result_carrier = array();
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
