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
$id_carrier = 16;
$id_status = 0;
$status_error = null;
$status = null;
$result_carrier = array();
$result = 'http://www.dhl.co.uk/shipmentTracking?AWB='.$tracking_number;
$url_page = strip_tags(Tools::file_get_contents($result, true));
$url = Tools::stripslashes($url_page);
$url = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $url), true);

if (Tools::strlen($url_page) == 0) {
    $status_error = 'Numéro incorrect';
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = 'E206';
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
    $result_carrier['result']['carrier_shipping_status_code'] = $status_error ? $status_error : $status;

    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = $status_error ? $status_error : $status;

    //$result_carrier['shipping_status_text'] = $result_status;
//print_R(($result_carrier));exit;
    $result_carrier['result']['module_shipping_status_code'] = 0;
    print_r(json_encode($result_carrier));
    exit;
}
$file_webcarrier = dirname(__FILE__).'/steps16.xml';


$carrier_xml = array();

$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$date_ = $url['results'][0]['checkpoints'][0]['date'];
$stt_return = $url['results'][0]['delivery']['code'];
foreach ($webxml_crr->step as $step) {
    $statuscode = $step->status;

    if (is_array($statuscode)) {
        if (in_array($stt_return, $statuscode)) {
            // delivered
                $id_status = $step->id_status;
            break;
        }
    } else {
        if ($stt_return == $statuscode) {
            // delivered
                $id_status = $step->id_status;
            break;
        }
    }
}

$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = $status_error ? $id_status : null;
$result_carrier['result']['carrier_server_status_text'] = $status_error ? $status_error : null;
$result_carrier['result']['carrier_shipping_status_code'] = $status_error ? null : $id_status;
$result_carrier['result']['carrier_shipping_status_text'] = $status_error ? null : $stt_return;
$result_carrier['result']['carrier_shipping_status_date'] = date("Y-m-d", strtotime($date_));

//$result_carrier['shipping_status_text'] = $result_status;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
print_r(json_encode($result_carrier));
