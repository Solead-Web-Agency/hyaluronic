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

$id_order = (int)Tools::getValue('shipment_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}

$id_carrier = 201;
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
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/xml"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
$response = utf8_decode($response);
$xml = simplexml_load_string($response);
$Body = $xml->Body;
$file_webcarrier = dirname(__FILE__).'/steps201.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
if (isset($Body->Fault)) {
    $result = 100;
    $error_code = trim((string)$Body->Fault->faultstring);
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
    $result_carrier['result']['carrier_server_success'] = 'false';
    $result_carrier['result']['carrier_shipping_status_code'] = 0;
    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = '';
    $result_carrier['result']['module_shipping_status_code'] = $result;
    print_r(json_encode($result_carrier));
    exit;
}
$traceResult = $Body->trackingByConsignmentResponse->Parcel;
$trace = $traceResult->events;
//$trace = end($trace);
$status = $traceResult->statusCode;
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
        if (strcmp($status, (string)$statuscode) === 0) {
            $id_status = $step->id_status;
            break;
        }
    }
}
$date = $trace->requestDate;
$date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
$result_status = (string)$traceResult->shortStatus;
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = true;
$result_carrier['result']['carrier_server_status_code'] =  null;
$result_carrier['result']['carrier_server_status_text'] = null;
$result_carrier['result']['carrier_shipping_status_code'] = $id_status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;

$result_carrier['result']['module_shipping_status_code'] = $id_status;
print_r(json_encode($result_carrier));
