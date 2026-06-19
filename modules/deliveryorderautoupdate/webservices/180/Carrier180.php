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
$id_carrier = 180;
$id_shop = (int)Tools::getValue('id_shop');
$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://status.dpd.ee/external/tracking?lang=en&pknr='.$tracking_number.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/json"
  ),
));

$response = curl_exec($curl);
$response = Tools::jsonDecode($response);
$trace = end($response);
$id_status = 100;
curl_close($curl);
$file_webcarrier = dirname(__FILE__).'/steps180.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
if ($trace->error) {
    $status_error = true;
    $status = (string)$trace->error->code;
    $desc = (string)$trace->error->message;
    $date = Date('Y-m-d H:i:s');
} else {
    ///echo $response;
    $status_error = false;
    $details = $trace->details;
    $detail = end($details);
    $status = $detail->status;
    $date = $detail->dateTime;
    $desc = $status;
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
$result_carrier['result']['carrier_server_success'] = $status_error;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
print_r(json_encode($result_carrier));
