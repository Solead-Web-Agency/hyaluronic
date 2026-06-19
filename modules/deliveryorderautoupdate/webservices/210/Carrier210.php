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
$id_carrier = 210;
$status_error = null;
$carrierstatus = null;
$result_carrier = array();
$result = 'https://www.tnt.it/tracking/getXMLTrack?WT=1&ConsigNos='.$tracking_number;
$url_page = simplexml_load_string(
    Tools::file_get_contents($result, true),
    'SimpleXMLElement',
    LIBXML_NOCDATA | LIBXML_NOBLANKS
);

$url = Tools::stripslashes($url_page);
$file_webcarrier = dirname(__FILE__).'/steps210.xml';
$id_status = 100;
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$events = array();
// if (!$url_page) {
//     $success = false;
//     $content = array();
//     $status_error = 'Numéro incorrect';
//     preg_match('/HTTP Status ([0-9]*)/', $result, $content);
//     $status = isset($content[1])?$content[1]:100;
//     $date = Date('Y-m-d H:i:s');
//     preg_match('/<u>(JBWEB.*)<\/u>/', $result, $content);
//     $desc = isset($content[1])?$content[1]:'';
// } else
if ($url->RuntimeError) {
    $status = (string)$url->RuntimeError->Code;
    $desc = (string)$url->RuntimeError->Message;
    $date = Date('Y-m-d H:i:s');
    $success = false;
} else {
    $success = true;
    $trace = $url->Consignment->StatusDetails;
    foreach ($trace as $key => $value) {
        $date = str_replace(
            '/',
            '-',
            (string)$value->StatusDate
        );
        $date = date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $date)));
        $events[] = array(
            'event_code' => (string)$value->StatusCode,
            'event_description' => (string)$value->StatusDescription,
            'event_date' => $date,
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$value->StatusCode)
        );
    }
    $status = (string)$url->Consignment->StatusDetails->StatusCode;
    $date = str_replace(
        '/',
        '-',
        (string)$url->Consignment->StatusDetails->StatusDate
    );
    $date = date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $date)));
    $desc = (string)$url->Consignment->StatusDetails->StatusDescription;
}

$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = array_reverse($events);
print_r(json_encode($result_carrier));
