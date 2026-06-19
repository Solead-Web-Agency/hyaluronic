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

$id_carrier = 72;
$result_carrier = array();

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://tracking.bring.com/api/tracking.xml?q='.$tracking_number.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Cookie: BIGipServerpool_posten_konsernportal_sporing_prod_8010=2259513995.18975.0000"
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$response = json_decode(json_encode(simplexml_load_string($response)), true);
$id_status = '100';
$file_webcarrier = dirname(__FILE__).'/steps72.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

if (isset($response['Consignment'])) {
    $events = $response['Consignment']['PackageSet']['Package']['EventSet']['Event'];
    $event = $events[0];
    $server = 1;
    $label_status = $event['Description'];
    $status = $event['Status'];
    $date = $event['OccuredAtIsoDateTime'];
    $events = array_map(function ($e) use ($webxml_crr) {
        return array(
            'event_code' => $e["Status"],
            'event_description' => $e["Description"],
            'event_date' => $e["OccuredAtIsoDateTime"],
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e["Status"])
        );
    }, array_reverse($events));
} else {
    $event = $response['Status'];
    $server = 0;
    $label_status = $event['Error'];
    $status = $event['Code'];
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
