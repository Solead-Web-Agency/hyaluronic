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
$id_carrier = 35;
$id_shop = (int)Tools::getValue('id_shop');
$data = '%3CTrackRequest%20USERID=%22758HELLO7881%22%3E%0A%3CTrackID%20ID=%22'.$tracking_number.
'%22%3E%3C/TrackID%3E%3C/TrackRequest%3E';
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://production.shippingapis.com/ShippingAPI.dll?API=TrackV2&XML='.$data.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
));

$response = curl_exec($curl);

curl_close($curl);
$result_carrier = array();
$response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
$xml = simplexml_load_string($response);
$file_webcarrier = dirname(__FILE__).'/steps35.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

$rs = $xml->TrackInfo;
$TrackSummary = (string)$rs->TrackSummary;
$id_status = 100;
$trace = (array)$rs->TrackDetail;
$events = array_map(function ($e) use ($webxml_crr) {
    $match = array();
    $datetime = array();
    if (preg_match('/, (\w* \d{1,2}, \d{4}),/', $e, $match)) {
        $datetime[] = $match[1];
    } else {
        $datetime[] = Date('Y-m-d');
    }
    if (preg_match('/, (\d{1,2}:\d{1,2} (am|pm)),/', $e, $match)) {
        $datetime[] = $match[1];
    } else {
        $datetime[] = Date('H:i:s');
    }
    $event = explode(',', $e);
    $status = $event[0];
    $date = implode(' ', $datetime);
    return array(
        'event_code' => (string)$status,
        'event_description' => (string)$status,
        'event_date' => date('Y-d-d H:i:s', strtotime($date)),
        'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$status)
    );
}, array_reverse($trace));
$match = array();
$datetime = array();
if (preg_match('/on (\w* \d{1,2}, \d{4})/', $TrackSummary, $match)) {
    $datetime[] = $match[1];
} else {
    $datetime[] = Date('Y-m-d');
}
if (preg_match('/at (\d{1,2}:\d{1,2} (am|pm))/', $TrackSummary, $match)) {
    $datetime[] = $match[1];
} else {
    $datetime[] = Date('H:i:s');
}
$date = implode(' ', $datetime);
$date = date('Y-d-d H:i:s', strtotime($date));
$status = $TrackSummary;
if (!isset($rs->TrackDetail)) {
    $success = false;
    $result_status = '';
} else {
    $success = true;
    $detail = (array)$rs->TrackDetail;
    $result_status = count($detail)?$detail[0]:'';
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
