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

$result_carrier = array();
$result = null;
$account = Configuration::get('HL_CARRIER1_id1');
$passwords = Configuration::get('HL_CARRIER1_id2');

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www.coliposte.fr/tracking-chargeur-cxf/TrackingServiceWS/track?accountNumber='.
    $account.'&password='.$passwords.'&skybillNumber='.$tracking_number.'',
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

$response = str_ireplace(['soapenv:', 'ns1:', 'soap:', 'xsi:'], '', $response);
$xml = simplexml_load_string($response);
$id_status = 0;
$file_webcarrier = dirname(__FILE__).'/steps1.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$rs = $xml->Body->trackResponse->return;

if ((string)$rs->errorCode !== '0') {
    $success = false;
    $status = (string)$rs->errorCode;
    $date = date("Y-m-d H:i:s");
    $result_status = (string)$rs->errorMessage;
} else {
    $success = true;
    $status = (string)$rs->eventCode;
    $date = (string)$rs->eventDate;
    $date = date('Y-m-d H:i:s', strtotime($date));
    $result_status = (string)$rs->eventLibelle;
}

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
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = array(
    array(
        'event_code' => $status,
        'event_description' => $result_status,
        'event_date' => $date,
        'id_status' => $id_status,
    )
);
print_r(json_encode($result_carrier));
