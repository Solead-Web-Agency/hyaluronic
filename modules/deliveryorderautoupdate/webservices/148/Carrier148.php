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
$id_shop = Tools::getValue('id_shop');
$client_id = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER148_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$client_secret = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER148_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER148_id3" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER148_id4" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$result_carrier = array();

//1st step : Request access token
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.ancorasemargens.pt/oauth/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
"grant_type" : "password",
"client_id" : "'.$client_id.'",
"client_secret" : "'.$client_secret.'",
"username" : "'.$username.'",
"password" : "'.$password.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$access_token = Tools::jsonDecode($response, true);
$access_token = isset($access_token['access_token'])?$access_token['access_token']:'';
//echo $access_token;exit;

//2nd step : Request tracking status
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'api.ancorasemargens.pt/v1/shipments/'.$tracking_number,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' .$access_token.''
  ),
));

$response = curl_exec($curl);

header("Content-Type: html");
$response = Tools::jsonDecode($response, true);

curl_close($curl);
$file_webcarrier = dirname(__FILE__).'/steps148.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

$id_status = '0';
if (isset($response['status'])) {
    $success = true;
    $status = (string)$response['status']['id'];
    $result_status = (string)$response['status']['name'];
    $date = date('Y-m-d H:i:s');
} else {
    $success = false;
    $status = (string)$response['error'];
    $result_status = (string)$response['message'];
    $date = date("Y-m-d H:i:s");
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
        'id_status' => $id_status
    )
);
print_r(json_encode($result_carrier));
