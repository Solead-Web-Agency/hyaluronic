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
$status_error = null;
$glsstatus = null;
$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://gls-group.eu/app/service/open/rest/PT/en/rstt001?match='.$tracking_number.'&caller=witt002',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Cookie: BIGIP=!OrwhvceAywVWyr3bNcy34UUsgnBJenqkssmRqaUzbrHRMnQa/pL9fBHGpf1mpQh2qmYpwOSwhdX3CZ4='
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$id_carrier = 123;
$response = Tools::jsonDecode($response, true);
$file_webcarrier = dirname(__FILE__).'/steps123.xml';
$webxml_crr = json_decode(
    json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
);
if (isset($response['lastError'])) {
    $status_error = true;
    $error_code = (string)$response['lastError'];
    $error_desc = (string)$response['exceptionText'];
    $result = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $error_code);

    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = $error_code;
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = false;
    $result_carrier['result']['carrier_shipping_status_code'] = null;
    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = $error_desc;
    $result_carrier['result']['module_shipping_status_code'] = $result;
    print_r(json_encode($result_carrier));
    exit;
}
$status_error = true;
$evtNos = $response['tuStatus'][0]['progressBar']['evtNos'];
$history = $response['tuStatus'][0]['history'];
$events = array_map(function ($e) use ($webxml_crr) {
    return array(
        'event_code' => (string)$e,
        'event_description' => '',
        'event_date' => '',
        'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e)
    );
}, $evtNos);
foreach ($history as $key => $h) {
    if (isset($events[$key])) {
        $events[$key]['event_date'] = $h['date'].' '.$h['time'];
        $events[$key]['event_description'] = $h['evtDscr'];
    }
}
$events = array_reverse($events);
if (count($events)) {
    $event = $events[count($events)-1];
    $date = $event['event_date'];
    $desc = $event['event_description'];
    $status = $event['event_code'];
    $id_status = $event['id_status'];
} else {
    $date = date('Y-m-d H:i:s');
    $desc = '';
    $status = '';
    $id_status = 0;
}
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $status_error;
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
