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
$id_carrier = 181;
$id_shop = (int)Tools::getValue('id_shop');
$result_carrier = array();

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://status.dpd.lv/external/tracking?lang=en&pknr='.$tracking_number.'',
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

curl_close($curl);
if ($trace->error) {
    $status_error = 'NumÃ©ro incorrect';
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = 'E206';
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');


    $result_carrier['result']['carrier_shipping_status_text'] = null;

//print_R(($result_carrier));exit;
    $result_carrier['module_shipping_status_code'] = 0;
    print_r(json_encode($result_carrier));
    exit;
} else {
    ///echo $response;
    $file_webcarrier = dirname(__FILE__).'/steps181.xml';
    $details = $trace->details;
    $detail = end($details);
    $status = $detail->status;

    $carrier_xml = array();
    $webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

    foreach ($webxml_crr->step as $step) {
        $statuscode = $step->status;

        $txt_str = 0;
        if (is_array($statuscode)) {
            if (in_array($status, $statuscode)) {
                // delivered
                $id_status = $step->id_status;
                break;
            }
        } else {
            if ($status == $statuscode) {
                // delivered
                $id_status = $step->id_status;
                break;
            }
        }
    }
    $date = $detail->dateTime;
    $result_status = $status;
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
    $result_carrier['result']['carrier_server_status_code'] =  null;
    $result_carrier['result']['carrier_server_status_text'] = null;
    $result_carrier['result']['carrier_shipping_status_code'] = $status;
    $result_carrier['result']['carrier_shipping_status_text'] = $result_status;
    $result_carrier['result']['carrier_shipping_status_date'] = $date;

    $result_carrier['result']['module_shipping_status_code'] = $id_status;
    print_r(json_encode($result_carrier));
}
