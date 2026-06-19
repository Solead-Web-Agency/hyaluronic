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
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER214_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER214_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];
$id_carrier = 214;
$result_carrier = array();

//1st step : Request access token
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://apid.gp.posteitaliane.it/dev/kindergarden/user/sessions',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{"clientId": "'.$username.'",
 "secretId": "'.$password.'"}',
  CURLOPT_HTTPHEADER => array(
    'POSTE_clientID: '.$username.'',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$access_token = Tools::jsonDecode($response, true);
if (isset($access_token['error'])) {
    $success = false;
    $status = $access_token['error'];
    $status_event = $access_token['error_description'];
    $date = date('Y-m-d H:i:s');
} else {
    $access_token = $access_token['access_token'];
    //2nd step : Request tracking status
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apid.gp.posteitaliane.it/dev/kindergarden/postalandlogistics/parcel/tracking',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
     "arg0": {
     "shipmentsData": [
     {
     "waybillNumber": "'.$tracking_number.'",
    "lastTracingState": "N"
     }
     ],
     "statusDescription": "E",
    "customerType": "DQ"
     }
    } ',
      CURLOPT_HTTPHEADER => array(
        'POSTE_clientID: '.$username.'',
        'Content-Type: application/json',
       'Authorization: Bearer ' .$access_token

      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $array = Tools::jsonDecode($response, true);
    $file_webcarrier = dirname(__FILE__).'/steps214.xml';
    $webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
    $result_ = 0;
    $rs = $array['return']['messages'][0]['messages'];
    if (isset($rs[0]) && count($rs[0])) {
        $success = false;
        $status = $rs[0]['code'];
        $status_event = $rs[0]['message'];
        $date = date('Y-m-d H:i:s');
    } else {
        $success = true;
        $shipment = end($array['parcelShipments']);
        $date = $shipment['modifiedAt'];
        $status_event = $shipment['status']['description']['en'];
        $status = $shipment['status']['code'];
    }
}
foreach ($webxml_crr->step as $step) {
    $statuscode = $step->status;
    if (is_array($statuscode)) {
        foreach ($statuscode as $code) {
            if (is_object($code)) {
                continue;
            }
            if (strpos($status, $code) !== false) {
                $result_ = $step->id_status;
                break;
            }
        }
    } else {
        if (is_object($statuscode)) {
            continue;
        }
        if (strpos($status, $statuscode) !== false) {
            $result_ = $step->id_status;
            break;
        }
    }
}
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $status_event;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $status_event;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $result_;

echo json_encode($result_carrier);
