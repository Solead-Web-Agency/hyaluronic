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

$id_order = (int)Tools::getValue('shipment_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
if ($conf_token != $token) {
    die("wrong token");
}

$result_carrier = array();
$context = Context::getContext();
$id_shop = Tools::getValue('id_shop');

$data = '<soapenv:Envelope
xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:cxf="http://cxf.ws.app.tnt.fr/">
   <soapenv:Header/>
   <soapenv:Body>
      <cxf:trackingByConsignment>
         <!--Optional:-->
         <parcelNumber>'.$tracking_number.'</parcelNumber>
      </cxf:trackingByConsignment>
   </soapenv:Body>
</soapenv:Envelope>';

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "http://www.tnt.fr/service/tracking",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "UTF-8",
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/xml; charset: UTF-8"
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$response = utf8_decode($response);
$response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
$xml = simplexml_load_string($response);
$Body = $xml->Body;
if (isset($Body->Fault)) {
    $status_error = true;
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = '';
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = 'false';
    $result_carrier['result']['carrier_shipping_status_code'] = 0;

    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = '';


    $result_carrier['result']['module_shipping_status_code'] = 0;
    print_r(json_encode($result_carrier));
    exit;
}
$status_error = false;
$traceResult = $Body->trackingByConsignmentResponse->Parcel;
$file_webcarrier = dirname(__FILE__).'/steps6.xml';
$trace = $traceResult->events;
//$trace = end($trace);
$status = (string)$traceResult->statusCode;
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 0;
foreach ($webxml_crr->step as $key => $step) {
    $statuscode = $step->status;
    if (is_array($statuscode)) {
        if (in_array($status, $statuscode)) {
            $id_status = $step->id_status;
            break;
        }
    } else {
        if (is_object($statuscode)) {
            $statuscode = (array)$statuscode;
            $statuscode = implode('', $statuscode);
        }
        if (strcmp($status, $statuscode) === 0) {
            $id_status = $step->id_status;
            break;
        }
    }
}
$date = $trace->requestDate;
$date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
$result_status = (string)$traceResult->longStatus;
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] =  null;
$result_carrier['result']['carrier_server_status_text'] = null;
$result_carrier['result']['carrier_shipping_status_code'] = $id_status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;

$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = array(
  array(
    'event_code' => $status,
    'event_description' => $result_status,
    'event_date' => $date,
    'id_status' => $id_status
  )
);
print_r(json_encode($result_carrier));
