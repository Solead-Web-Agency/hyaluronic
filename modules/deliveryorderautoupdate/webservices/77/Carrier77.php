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
$lang = Db::getInstance()->getValue(
    'SELECT CASE WHEN l.iso_code IN("fr","es","nl","pt","pl","de","it") THEN l.iso_code ELSE "en" END
    FROM '._DB_PREFIX_.'order_carrier oc
	LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
	LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
    WHERE oc.id_order_carrier='.(int)$id_order.''
);
$username = Configuration::get('HL_CARRIER77_id1');
$password = Configuration::get('HL_CARRIER77_id2');
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://extservices.matkahuolto.fi/mpaketti/public/tracking?ids='.$tracking_number,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic '. base64_encode($username.':'.$password),
	  'Accept-Language: '.$lang
  ),
));

$response = curl_exec($curl);
curl_close($curl);


$response = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
$xml = simplexml_load_string($response);
$status_error = false;
$file_webcarrier = dirname(__FILE__).'/steps77.xml';
$events = array();
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$labels = json_decode(file_get_contents(dirname(__FILE__).'/labelcode77.txt'), true);
$id_status = 0;
if (!isset($xml->Event)) {
    $status_error = false;
    $status = '';
    $date = date('Y-m-d H:i:s');
    $result_status = '';
} else {
    $traces = $xml->xpath('*');
    // if (count($error)) {
    //     $status_error = false;
    //     $status = (string)$traceResult->LastError;
    //     $date = date('Y-m-d H:i:s');
    //     $result_status = '';
    // } else {
        // $trace = isset($traceResult->Traces->clsTrace)?$traceResult->Traces->clsTrace:false;
        $status_error = true;
        if (count($traces)) {
            $events = array_map(function ($e) use ($webxml_crr, $labels) {
                $date = $e->EventTime;
                $date = date("Y-m-d H:i:s", strtotime($date));
                $code = (string)$e->EventCode;
                return array(
                    'event_code' => $code,
                    'event_description' => isset($labels[$code])?$labels[$code]:0,
                    'event_date' => $date,
                    'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $code)
                );
            }, $traces);
            $event = $events[count($events)-1];
            $status = $event['event_code'];
            $result_status = $event['event_description'];
            $date = $event['event_date'];
        } else {
            $status = '';
            $date = date('Y-m-d H:i:s');
            $result_status = '';
        }
    // }
}

$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $status_error;
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
