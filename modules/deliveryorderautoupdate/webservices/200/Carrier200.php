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
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$id_order = Tools::getValue('shipment_ref');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 200;
$id_shop = Tools::getValue('id_shop');
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER200_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER200_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];
$result_carrier = array();

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://express.tnt.com/expressconnect/track.do?xml_in=%3C?xml%20version=%221.0%22%20encoding='.
  '%22UTF-8%22%20standalone=%22no%22?%3E%0A%3CTrackRequest%3E%0A%3CSearchCriteria%3E%0A%3CConsignmentNumber%3E'.
  $tracking_number.'%3C/ConsignmentNumber%3E%0A%3C/SearchCriteria%3E%0A%3CLevelOfDetail%3E%0A%3CComplete%20originAddre'.
  'ss=%22true%22%20destinationAddress=%22true%22%20package=%22true%22%20shipment=%22true%22/%3E%0A%3C/LevelOfDetail%3E'.
  '%0A%3C/TrackRequest%3E',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => array(
    "Content-Type: text/xml",
    "Authorization: Basic ". base64_encode($username.':'.$password),
    "Cookie: BIGipServerexpress.tnt.com_pool_7=2676609546.20992.0000"
  ),
));

$response = curl_exec($curl);

libxml_use_internal_errors(true);
curl_close($curl);
$response = str_ireplace(['ns1:', 'access="full"'], '', $response);
$xml = simplexml_load_string($response);
$file_webcarrier = dirname(__FILE__).'/steps200.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 100;
if (isset($xml->HEAD)) {
    $success = false;
    $status = (string)$xml->HEAD->TITLE;
    $desc = $status;
    $date = Date('Y-m-d H:i:s');
} elseif (isset($xml->Error)) {
    $success = false;
    $status = (string)$xml->Error->Code;
    $desc = (string)$xml->Error->Message;
    $date = Date('Y-m-d H:i:s');
} elseif (!isset($xml->Consignment->StatusData)) {
    $success = false;
    $status = (string)$xml->Consignment->SummaryCode;
    $desc = 'Invalid number';
    $date = Date('Y-m-d H:i:s');
} else {
    $success = true;
    $traceResult = $xml->TrackResponse;
    $trace = $traceResult->xpath('//StatusData');
    $trace = current($trace);
    $status = trim((string)$trace->StatusCode);
    $date = $trace->LocalEventDate.' '.$trace->LocalEventTime;
    $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
    $desc = (string)$trace->StatusDescription;
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
$result_carrier['result']['carrier_server_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
print_r(json_encode($result_carrier));
