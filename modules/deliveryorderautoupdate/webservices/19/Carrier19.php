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
$status_error = false;
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
$id_carrier = 19;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');

$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER19_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER19_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$AccessLicenseNumber = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER19_id3" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];
$AccessLicenseNumber = $AccessLicenseNumber['value'];

$data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:v1="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0"
xmlns:v3="http://www.ups.com/XMLSchema/XOLTWS/Track/v2.0"
xmlns:v11="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0">
	   <soapenv:Header>
	   <v1:UPSSecurity>
	   <v1:UsernameToken>
	   <v1:Username>'.$username.'</v1:Username>
	   <v1:Password>'.$password.'</v1:Password>
	   </v1:UsernameToken>
	   <v1:ServiceAccessToken>
	   <v1:AccessLicenseNumber>'.$AccessLicenseNumber.'</v1:AccessLicenseNumber>
	   </v1:ServiceAccessToken>
	   </v1:UPSSecurity>
	   </soapenv:Header>
	   <soapenv:Body>
	   <v3:TrackRequest>
	   <v11:Request>
	   <v11:RequestOption>1</v11:RequestOption>
	   <v11:TransactionReference>
	   <v11:CustomerContext>Your Test Case Summary Description</v11:CustomerContext>
	   </v11:TransactionReference>
	   </v11:Request>
	   <v3:InquiryNumber>'.$tracking_number.'</v3:InquiryNumber>
	   </v3:TrackRequest>
	   </soapenv:Body>
	   </soapenv:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://onlinetools.ups.com/webservices/Track",
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
$response = str_ireplace(['soapenv:', 'trk:', 'common:', 'err:'], '', $response);
$xml = simplexml_load_string($response);
$body = $xml->Body;

$file_webcarrier = dirname(__FILE__).'/steps19.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 0;
if (isset($body->Fault)) {
    $result = 100;
    $error_code = (string)$body->Fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Code;
    $error_desc = (string)$body->Fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Description;
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
    $status_error = true;
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = $error_code;
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = 'false';
    $result_carrier['result']['carrier_shipping_status_code'] = 0;

    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = $error_desc;


    $result_carrier['result']['module_shipping_status_code'] = $result;
    print_r(json_encode($result_carrier));
    exit;
}
$traceResult = $xml->Body->TrackResponse->Shipment->Package;
$trace = $traceResult->xpath('//Activity');
$events = array_map(function ($e) use ($webxml_crr) {
    return array(
        'event_code' => (string)$e->Status->Code,
        'event_description' => (string)$e->Status->Description,
        'event_date' => date("y-m-d H:i:s", strtotime(str_replace("/", "-", $e->Date.' '.$e->Time))),
        'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e->Status->Code)
    );
}, array_reverse($trace));
$trace = current($trace);
$status = (string)$trace->Status->Code;
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$date = $trace->Date.' '.$trace->Time;
$date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
$result_status = (string)$trace->Status->Description;
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
