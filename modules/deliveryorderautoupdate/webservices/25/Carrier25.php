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

$context = Context::getContext();
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$reference = Tools::getValue('order_ref');
$id_order = Tools::getValue('shipment_ref');
$id_shop = Tools::getValue('id_shop');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);

if ($conf_token != $token) {
    die("wrong token");
}
$credentials = Db::getInstance()->getValue(
    'SELECT REPLACE(c2.url,"@","'.$tracking_number.'")
    FROM '._DB_PREFIX_.'order_carrier oc
  LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference=c2.id_reference AND c2.deleted=0
  WHERE oc.id_order_carrier = '.$id_order.''
);

$credentials = explode('_', $credentials);
$credentials = end($credentials);
$shippingcustomercenter = substr($credentials,0,3);
$shippingcustomer = substr($credentials,3,8);
$tracking_number = explode('_', $tracking_number);
$tracking_number = current($tracking_number);
$result_carrier = array();
$data = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:car="http://www.cargonet.software/">
   <soap:Header/>
   <soap:Body>
      <car:getShipmentTraceByReferenceGlobalWithCenterAsArray>
         <car:customer_center>3</car:customer_center>
         <car:customer>1064</car:customer>
         <!--Optional:-->
         <car:password>Pr2%5sHg</car:password>
         <!--Optional:-->
         <car:reference>'.$tracking_number.'</car:reference>
         <!--Optional:-->
         <car:shipping_date></car:shipping_date>
         <car:shipping_customer_center>'.$shippingcustomercenter.'</car:shipping_customer_center>
         <car:shipping_customer>'.$shippingcustomer.'</car:shipping_customer>
      </car:getShipmentTraceByReferenceGlobalWithCenterAsArray>
   </soap:Body>
</soap:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    'Host: webtrace.dpd.fr',
    'Content-Type: application/soap+xml',
    'Accept-Encoding: gzip,deflate',
    'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)',
    'Cookie: __cfduid=def5d54a460a440781c23b59b07fda7371607457320; pers=!Q0wB/bV2PZfYll6rEm4m3rxJRTAJ'.
    'BMh81p3nQaI6ifVr84HKfk8L6MM7UTYoQSatsP//lq0OyJRaWUUAGr38ecGSvl2Dg2LX0c0TmjI='
  ),
));

$response = curl_exec($curl);

//header("Content-type: text/xml");
curl_close($curl);
//print_r($response);


$response = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
$xml = simplexml_load_string($response);

$status_error = false;
$file_webcarrier = dirname(__FILE__).'/steps25.xml';
$events = array();
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 0;
if (isset($xml->Body->Fault)) {
    $status_error = false;
    $traceResult = $xml->Body->Fault;
    $status = (string)$traceResult->Code->Value;
    $result_status = (string)$traceResult->Reason->Text;
    $date = date('Y-m-d H:i:s');
} else {
    $traceResult = $xml->Body->getShipmentTraceByReferenceGlobalWithCenterAsArrayResponse;
    $traceResult = $traceResult->getShipmentTraceByReferenceGlobalWithCenterAsArrayResult;
    $Traces = $traceResult->clsShipmentTrace->Traces;
    $traces = $Traces->xpath('*');
    $traceResult = $traceResult->clsShipmentTrace;
    $error = (array)$traceResult->LastError;
    if (count($error)) {
        $status_error = false;
        $status = (string)$traceResult->LastError;
        $date = date('Y-m-d H:i:s');
        $result_status = '';
    } else {
        $trace = isset($traceResult->Traces->clsTrace)?$traceResult->Traces->clsTrace:false;
        $status_error = true;
        if ($trace) {
            $events = array_map(function ($e) use ($webxml_crr) {
                $date = $e->ScanDate.' '.$e->ScanTime;
                $date = date("Y-m-d H:i:s", strtotime($date));
                return array(
                    'event_code' => (string)$e->StatusNumber,
                    'event_description' => (string)$e->StatusDescription,
                    'event_date' => $date,
                    'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e->StatusNumber)
                );
            }, array_reverse($traces));
            $status = (string)$trace->StatusNumber;
            $date = $trace->ScanDate.' '.$trace->ScanTime;
            $date = date("Y-m-d H:i:s", strtotime($date));
            $result_status = (string)$trace->StatusDescription;
        } else {
            $status = '';
            $date = date('Y-m-d H:i:s');
            $result_status = '';
        }
    }
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
