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
$lang = Db::getInstance()->getValue(
    'SELECT CASE WHEN l.iso_code IN("fr","es","pl","de","it") THEN l.iso_code ELSE "en" END
    FROM '._DB_PREFIX_.'order_carrier oc
	LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
	LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
    WHERE oc.id_order_carrier='.(int)$id_order.''
);
$status_error = null;
$status = null;
$result_carrier = array();

$data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:cxf="http://cxf.tracking.soap.chronopost.fr/">
   <soapenv:Header/>
   <soapenv:Body>
      <cxf:trackSkybill>
         <!--Optional:-->
         <language>'.$lang.'</language>
         <!--Optional:-->
         <skybillNumber>'.$tracking_number.'</skybillNumber>
      </cxf:trackSkybill>
   </soapenv:Body>
</soapenv:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.chronopost.fr/tracking-cxf/TrackingServiceWS",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/xml"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
$xml = simplexml_load_string($response);
$traceResult = $xml->Body->trackSkybillResponse->return->listEvents;

$file_webcarrier = dirname(__FILE__).'/steps2.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
if (!isset($traceResult->events)) {
    $result = 100;
    $error_code = (string)$xml->Body->trackSkybillResponse->return->errorCode;
    foreach ($webxml_crr->step as $key => $step) {
        $statuscode = $step->statuscode;
        if (is_array($statuscode)) {
            if (in_array($error_code, $statuscode)) {
                $result = $step->id_status;
                break;
            }
        } else {
            if (is_object($statuscode)) {
                $statuscode = (array)$statuscode;
                $statuscode = implode('', $statuscode);
            }
            if (strcmp($error_code, (string)$statuscode) === 0) {
                $result = $step->id_status;
                break;
            }
        }
    }
    $status_error = true;
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = $error_code;
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = 'false';
    $result_carrier['result']['carrier_shipping_status_code'] = 0;
    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = '';
    $result_carrier['result']['module_shipping_status_code'] = $result;
    print_r(json_encode($result_carrier));
    exit;
}
$trace = $traceResult->xpath('//events');
$events = array_map(function ($e) use ($webxml_crr) {
    return array(
        'event_code' => trim($e->code),
        'event_description' => (string)$e->eventLabel,
        'event_date' => date("y-m-d H:i:s", strtotime(str_replace("/", "-", $e->eventDate))),
        'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, trim($e->code))
    );
}, $trace);
$trace = end($trace);
$status = trim((string)$trace->code);
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
if ($id_status < 0) {
    $id_status = 0;
}
$date = $trace->eventDate;
$date = date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)));
$result_status = (string)$trace->eventLabel;
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] =  null;
$result_carrier['result']['carrier_server_status_text'] = null;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
