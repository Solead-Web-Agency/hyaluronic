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
$id_shop = Tools::getValue('id_shop');
$key = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER160_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api-eu.dhl.com/track/shipments?trackingNumber='.$tracking_number.
  '&requesterCountryCode=DE&originCountryCode=DE&language=en',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    'DHL-API-Key: '.$key.''
  ),
));

$response = curl_exec($curl);
$response = Tools::jsonDecode($response, true);


curl_close($curl);

$file_webcarrier = dirname(__FILE__).'/steps160.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 100;
if (isset($response['status']) || !count($response['shipments'])) {
    $success = false;
    $status =  $response['status'];
    $desc = $response['detail'];
    $date = Date('Y-m-d H:i:s');
    $events = array();
} else {
    $success = true;
    $shipment = reset($response['shipments']);
    $events = array_map(function ($e) use ($webxml_crr) {
        return array(
            'event_code' => $e['status'],
            'event_description' => $e['description'],
            'event_date' => date("y-m-d H:i:s", strtotime(str_replace("/", "-", $e['timestamp']))),
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e['status'], false)
        );
    }, array_reverse($shipment['events']));
    $status = $shipment['status']['statusCode'];
    $desc = isset($shipment['status']['description'])?$shipment['status']['description']:$shipment['status']['status'];
    $date = $shipment['status']['timestamp'];
    $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status, false);
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
sleep(1);
