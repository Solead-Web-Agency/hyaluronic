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
$id_carrier = 174;
$status_error = null;
$glsstatus = null;

$credentials_embed =_PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'.
$id_carrier.'/credentials'.$id_carrier.'.xml';
$webxml_crr = json_decode(
    json_encode(@simplexml_load_file($credentials_embed, 'SimpleXMLElement', LIBXML_NOCDATA))
);
$values = array();
foreach ($webxml_crr->credential as $cre) {
    $values[] = Configuration::get($cre->credname);
}
$appname = isset($values[0])?$values[0]:'';
$password = isset($values[1])?$values[1]:'';
$xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<data appname="'.$appname.'" language-code="en" password="'.$password.
'" piece-code="'.$tracking_number.'" request="d-get-piece"/>';
$query = http_build_query(array(
  'xml' => $xml,
));
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://cig.dhl.de/services/sandbox/rest/sendungsverfolgung?{$query}",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Basic ZGFoaGFvdWk6YzklXmgqQzY="
  ),
));
$response = curl_exec($curl);
$file_webcarrier = dirname(__FILE__).'/steps174.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$result = new SimpleXMLElement($response);
$attributes = $result->data->attributes();
$result_carrier = array();

if ($result->attributes()->{'code'} != 0) {
    $success = false;
    $status = (string)$result->attributes()->{'code'};
    $status_event = (string)$result->attributes()->{'error'};
    $date = date('Y-m-d H:i:s');
} else {
    $success = true;
    $date = (string)$attributes->{'status-timestamp'};
    $status_event = (string)$attributes->{'short-status'};
    $status = (string)$attributes->{'ice'}.'_'.$attributes->{'standard-event-code'};
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
