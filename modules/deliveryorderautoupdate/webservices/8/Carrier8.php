<?php
/**
* 2007-2021 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2021 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

$DOCUMENT_ROOT = explode('modules', dirname(__FILE__));
require_once($DOCUMENT_ROOT[0].'config/config.inc.php');
require_once($DOCUMENT_ROOT[0].'init.php');
require_once('../../classes/trackingmodel.php');

$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$id_order = Tools::getValue('shipment_ref');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}

if (Tools::substr($tracking_number, 0, 3) != "250") {
    $tracking_number = '250' . $tracking_number;
}
$context = Context::getContext();
$reference = Tools::getValue('order_ref');
$id_shop = Tools::getValue('id_shop');
$result_carrier = array();

$data = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:car="http://www.cargonet.software/">
   <soap:Header/>
   <soap:Body>
      <car:getShipmentTrace>
         <car:customer_center>3</car:customer_center>
         <car:customer>1064</car:customer>
         <!--Optional:-->
         <car:password>Pr2%5sHg</car:password>
         <!--Optional:-->
         <car:shipmentnumber>'.$tracking_number.'</car:shipmentnumber>
      </car:getShipmentTrace>
   </soap:Body>
</soap:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/soap+xml",
    "Cookie: pers=rd3o00000000000000000000ffff0a65143bo80"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
// header("Content-type: text/xml");
// print_r($response);exit;
$response = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
$xml = simplexml_load_string($response);
$traceResult = $xml->Body->getShipmentTraceResponse->getShipmentTraceResult;
$status_error = false;
$file_webcarrier = dirname(__FILE__).'/steps8.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$error = (array)$traceResult->LastError;
$id_status = 100;
if (count($error)) {
    $status_error = true;
    $result = 100;
    $error_code = (string)$traceResult->LastError;
    foreach ($webxml_crr->step as $key => $step) {
        $statuscode = $step->status;
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
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = $error_code;
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
    $result_carrier['result']['carrier_shipping_status_code'] = 0;

    $result_carrier['result']['carrier_shipping_status_text'] = $error_code;
    $result_carrier['result']['carrier_server_status_text'] = '';

    $result_carrier['result']['module_shipping_status_code'] = $result;
    print_r(json_encode($result_carrier));
    exit;
} else {
    $traces = (array)$traceResult->Traces;
    $traces = $traces['clsTrace'];
    $events = array_map(function ($e) use ($webxml_crr) {
        return array(
            'event_code' => (string)$e->StatusNumber,
            'event_description' => (string)$e->StatusDescription,
            'event_date' => date("Y-m-d H:i:s", strtotime($e->ScanDate.' '.$e->ScanTime)),
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e->StatusNumber)
        );
    }, array_reverse($traces));
    $trace = $traceResult->Traces->clsTrace;
    $status = (string)$trace->StatusNumber;
    $carrier_xml = array();
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
    $date = $trace->ScanDate.' '.$trace->ScanTime;
    $date = date("Y-m-d H:i:s", strtotime($date));
    $result_status = (string)$trace->StatusDescription;
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
}
