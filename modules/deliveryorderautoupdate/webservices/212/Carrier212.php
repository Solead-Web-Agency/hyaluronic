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

$id_carrier = 212;
$result_carrier = array();

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://tracking.nexive.it/?b='.$tracking_number.'&lang=en',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
));

$response = curl_exec($curl);
curl_close($curl);
$month_en = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$month_it = array('gen','feb','mar','apr','mag','giu','lug','ago','set','ott','nov','dic');
$file_webcarrier = dirname(__FILE__).'/steps212.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 100;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
libxml_use_internal_errors(true);
$doc->loadHTML($response);
$finder = new DomXPath($doc);
$nodes = $finder->query("//div[contains(@class, 'mainState') and contains(@class, 'active')]");
if ($nodes->length) {
    $stateDetailNote = $finder->query("div[contains(@class, 'stateDetail')]", $nodes->item(0));
    $dataNode = $finder->query("div", $stateDetailNote->item(0));
    if ($dataNode->length) {
        $success = true;
        $status = (string)$dataNode->item(0)->nodeValue;
        $date = (string)$dataNode->item(4)->nodeValue;
        $date = str_replace($month_it, $month_en, $date);
        $date = date_create_from_format('j M Y, H:i', $date);
        $date = $date->format('Y-m-d H:i:s');
        $desc = $finder->query("div[contains(@class, 'nx_text')]", $dataNode->item(6));
        $result_status = (string)$desc->item(0)->nodeValue;
    }
} else {
    $nodes = $finder->query("//div[@class='tile']");
    $block = $nodes->item(0);
    $statusNode = $finder->query("span[contains(@class, 'nx_title')]", $block);
    $descNode = $finder->query("div[contains(@class, 'tile-body')]", $block);
    $descNode = $finder->query("div[contains(@class, 'tile-row')]", $descNode->item(0));
    $descNode = $finder->query("div[contains(@class, 'nx_text')]", $descNode->item(0));
    $success = false;
    $status = (string)$statusNode->item(0)->nodeValue;
    $date = date("Y-m-d H:i:s");
    $result_status = (string)$descNode->item(0)->nodeValue;
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
print_r(json_encode($result_carrier));
