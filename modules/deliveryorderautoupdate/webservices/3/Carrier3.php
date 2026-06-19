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
//$tracking_number = Tools::substr($tracking_number, -12, 12);
if ($conf_token != $token) {
    die("wrong token");
}
$status_error = null;
$status = null;
$result_carrier = array();
$lang = Db::getInstance()->getValue(
    'SELECT CASE WHEN l.iso_code IN("fr","es","nl","pt","pl","de") THEN l.iso_code ELSE "en" END
    FROM '._DB_PREFIX_.'order_carrier oc
	LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
	LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
    WHERE oc.id_order_carrier='.(int)$id_order.''
);
$url = 'https://gls-group.eu/app/service/open/rest/'.$lang.'/'.$lang.'/rstt001?match='.$tracking_number;
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
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
$response = Tools::jsonDecode($response, true);
$file_webcarrier = dirname(__FILE__).'/steps3.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 0;
if (isset($response['lastError'])) {
    $error_code = (string)$response['lastError'];
    $error_desc = (string)$response['exceptionText'];
    foreach ($webxml_crr->step as $key => $step) {
        $statuscode = $step->status;
        if (is_array($statuscode)) {
            if (in_array($error_code, $statuscode)) {
                $id_status = $step->id_status;
                break;
            }
        } else {
            if (is_object($statuscode)) {
                $statuscode = (array)$statuscode;
                $statuscode = implode('', $statuscode);
            }
            if (strcmp($error_code, (string)$statuscode) === 0) {
                $id_status = $step->id_status;
                break;
            }
        }
    }
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = $error_code;
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = false;
    $result_carrier['result']['carrier_shipping_status_code'] = null;
    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = $error_desc;
    $result_carrier['result']['module_shipping_status_code'] = $id_status;
    print_r(json_encode($result_carrier));
    exit;
}
$carrier_xml = array();
$evtNos = $response['tuStatus'][0]['progressBar']['evtNos'];
$history = $response['tuStatus'][0]['history'];
$events = array_map(function ($e) use ($webxml_crr) {
    return array(
        'event_code' => (string)$e,
        'event_description' => '',
        'event_date' => '',
        'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e)
    );
}, $evtNos);
foreach ($history as $key => $h) {
    if (isset($events[$key])) {
        $events[$key]['event_date'] = $h['date'].' '.$h['time'];
        $events[$key]['event_description'] = $h['evtDscr'];
    }
}
$events = array_reverse($events);
if (count($evtNos)) {
    $statusInfo = (string)$evtNos[0];
} else {
    $statusInfo = "E206";
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $statusInfo);

$description = $response['tuStatus'][0]['history'][0]['evtDscr'];
$date = $response['tuStatus'][0]['history'][0]['date']
.' '.$response['tuStatus'][0]['history'][0]['time'];
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = $status_error ? $id_status : null;
$result_carrier['result']['carrier_server_status_text'] = $status_error ? $status_error : null;
$result_carrier['result']['carrier_shipping_status_code'] = $status_error ? null : $statusInfo;
$result_carrier['result']['carrier_shipping_status_text'] = $description;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
