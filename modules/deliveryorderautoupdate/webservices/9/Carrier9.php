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
$id_carrier = 9;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials9.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER9_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER9_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$AccessLicenseNumber = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER9_id3" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];
$AccessLicenseNumber = $AccessLicenseNumber['value'];

$data= '{
"UPSSecurity": {
"UsernameToken": {
"Username": "'.$username.'",
"Password": "'.$password.'"
},
"ServiceAccessToken": {
"AccessLicenseNumber": "'.$AccessLicenseNumber.'"
}
},
"TrackRequest": {
"Request": {
"RequestOption": "1",
"TransactionReference": {
"CustomerContext": "Your Test Case Summary Description"
}
},
"InquiryNumber": "'.$tracking_number.'"
}
}';

$curl = curl_init();

curl_setopt_array($curl, array(
//  CURLOPT_URL => "https://wwwcie.ups.com/rest/Track",
  CURLOPT_URL => "https://onlinetools.ups.com/rest/Track",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/json",
    "Content-Type: text/plain"
  ),
));

$response = Tools::jsonDecode(curl_exec($curl), true);

curl_close($curl);
$file_webcarrier = dirname(__FILE__).'/steps9.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
if (isset($response['Fault'])) {
    $result = 100;
    $error_code = $response['Fault']['detail']['Errors']['ErrorDetail']['PrimaryErrorCode']['Code'];
    $error_desc = $response['Fault']['detail']['Errors']['ErrorDetail']['PrimaryErrorCode']['Description'];
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
    $status_error = 'NumÃ©ro incorrect';
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = $error_code;
    $result_carrier['result']['carrier_server_status_text'] = $error_desc;
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_shipping_status_code'] = null;
    $result_carrier['result']['module_shipping_status_code'] = $result;
    print_r(json_encode($result_carrier));
    exit;
} else {
    ///echo $response;
    $id_status = 100;
    $array = $response;
    $events = $array['TrackResponse']['Shipment']['Package']['Activity'];
    $events = array_map(function ($e) use ($webxml_crr) {
        return array(
            'event_code' => (string)$e['Status']['Code'],
            'event_description' => (string)$e['Status']['Description'],
            'event_date' => DateTime::createFromFormat('Ymd His', $e['Date'].' '.$e['Time'])->format('Y-m-d H:i:s'),
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e['Status']['Code'])
        );
    }, array_reverse($events));
    $status = $array['TrackResponse']['Shipment']['Package']['Activity'][0]['Status']['Code'];
    $carrier_xml = array();
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
    $date = $array['TrackResponse']['Shipment']['Package']['Activity'][0]['Date'].' '
    .$array['TrackResponse']['Shipment']['Package']['Activity'][0]['Time'];
    $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));

    $result_status = $array['TrackResponse']['Shipment']['Package']['Activity'][0]['Status']['Description'];
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_success'] = true;
    $result_carrier['result']['carrier_server_status_code'] =  null;
    $result_carrier['result']['carrier_server_status_text'] = null;
    $result_carrier['result']['carrier_shipping_status_code'] = $status;
    $result_carrier['result']['carrier_shipping_status_text'] = $result_status;
    $result_carrier['result']['carrier_shipping_status_date'] = $date;

    $result_carrier['result']['module_shipping_status_code'] = $id_status;
    $result_carrier['result']['all_events'] = $events;
    print_r(json_encode($result_carrier));
}
