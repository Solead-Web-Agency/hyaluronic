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
$key = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER130_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://www.viadirectanet.pt/WebApiV2/api/ServiceVD/?Key='.$key.'&CodSRV='.$tracking_number.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$rs = curl_exec($curl);
$response = Tools::jsonDecode($rs, true);
$file_webcarrier = dirname(__FILE__).'/steps130.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

$id_status = '0';
if (is_array($response)) {
    $success = true;
    $status = $response['Cod'];
    $datenode = $response['DataEstado'];
    $date = DateTime::createFromFormat('d-m-Y', $datenode);
    $date = $date->format('Y-m-d');
    $result_status = $response['Descricao'];
} else {
    $success = false;
    $status = $rs;
    $date = date("Y-m-d H:i:s");
    $result_status = '';
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
