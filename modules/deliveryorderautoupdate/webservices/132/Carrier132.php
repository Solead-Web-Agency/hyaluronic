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
$id_shop = Tools::getValue('id_shop');
$user = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER132_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER132_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$pass = Tools::strtoupper(md5($password));
$agency = Tools::substr($tracking_number, 0, 4);
$tracking_number = str_replace('/', '', $tracking_number);
$tracking_number = Tools::substr($tracking_number, 4, 8);

$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://pda.nacex.com/nacex_ws/ws?method=getInfoEnvio&data=tipo=E%7Cdel='
  .$agency.'%7Cnum='.$tracking_number.'&user='.$user.'&pass='.$pass.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);
$response = explode('|', $response);
$file_webcarrier = dirname(__FILE__).'/steps132.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

$id_status = '0';
$events = array();
if ($response[0] != 'ERROR') {
    $patt = '/^\d{1,2}\/\d{1,2}\/\d{1,4}\s\d{1,2}:\d{1,2}:\d{1,2}\~\d*~\w*~/';
    $trace = array_filter($response, function ($r) use ($patt) {
        return preg_match($patt, $r);
    });
    $events = array_map(function ($e) use ($webxml_crr) {
        $rs = explode('~', $e);
        $datenode = $rs[0];
        $date = DateTime::createFromFormat('d/m/Y H:i:s', $datenode);
        $date = $date->format('Y-m-d H:i:s');
        $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $rs[1]);
        return array(
            'event_code' => $rs[1],
            'event_description' => $rs[2],
            'event_date' => $date,
            'id_status' => $id_status,
        );
    }, array_values($trace));
    $rs = $response[count($response)-1];
    $rs = explode('~', $rs);
    $success = true;
    $datenode = $rs[0];
    $status = $rs[1];
    $date = DateTime::createFromFormat('d/m/Y H:i:s', $datenode);
    $date = $date->format('Y-m-d H:i:s');
    $result_status = utf8_encode($rs[2]);
} else {
    $success = false;
    $status = $response[2];
    $date = date("Y-m-d H:i:s");
    $result_status = utf8_encode($response[1]);
}

$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
