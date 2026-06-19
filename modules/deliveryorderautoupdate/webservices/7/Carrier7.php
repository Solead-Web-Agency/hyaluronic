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
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$id_order = Tools::getValue('shipment_ref');
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
    WHERE name like "HL_CARRIER7_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$status_error = false;
$glsstatus = null;

//sleep(3);
$result_carrier = array();
$result = 'https://api.laposte.fr/suivi/v2/idships/'.$tracking_number;

$ch = curl_init($result);
curl_setopt_array($ch, array(
    CURLOPT_HTTPHEADER  => array('X-Okapi-Key: '.$key.'',
        'Accept: application/json'),
    CURLOPT_RETURNTRANSFER  =>true,
    CURLOPT_VERBOSE     => 1
));
$out = curl_exec($ch);
curl_close($ch);

$url = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $out), true);

$file_webcarrier = dirname(__FILE__).'/steps7.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
if (isset($url['shipment']) && isset($url['shipment']['event'])) {
    $events = $url['shipment']['event'];
    if (is_array($events) && count($events)) {
        $event = $events[0];
        $events = array_map(function ($e) use ($webxml_crr) {
            if (trim($e['code']) == '') {
                $id_status = 1;
            } else {
                $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, trim($e['code']));
            }
            return array(
                'event_code' => trim($e['code']),
                'event_description' => $e['label'],
                'event_date' => date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $e['date']))),
                'id_status' => $id_status
            );
        }, array_reverse($events));
    } else {
        $event = false;
    }
} else {
    $event = false;
}
$date = Date('Y-m-d H:i:s');
if ($event === false) {
    $result = 0;
    if (isset($url['returnCode'])) {
        $error_code = $url['returnCode'];
        $msg = $url['returnMessage'];
    } else {
        $error_code = $url['code'];
        $msg = $url['message'];
    }
    $result = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $error_code);
    $status_error = true;
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = $error_code;
    $result_carrier['result']['carrier_shipping_status_date'] = $date;
    $result_carrier['result']['carrier_server_success'] = 'false';
    $result_carrier['result']['carrier_shipping_status_code'] = 0;

    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = $msg;


    $result_carrier['result']['module_shipping_status_code'] = $result;
    print_r(json_encode($result_carrier));
    exit;
}


$carrier_xml = array();
$result_ = null;
$date = date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $event['date'])));
$carrier_shipping_status_text = $event['label'];
if ($event['code'] == '') {
    $result = 1;
} else {
    $result = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $event['code']);
}

$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = true;
$result_carrier['result']['carrier_server_status_code'] = null;
$result_carrier['result']['carrier_server_status_text'] = null;
$result_carrier['result']['carrier_shipping_status_code'] = $event['code'];
$result_carrier['result']['carrier_shipping_status_text'] = $carrier_shipping_status_text;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $result;
$result_carrier['result']['all_events'] = $events;
echo json_encode($result_carrier);
