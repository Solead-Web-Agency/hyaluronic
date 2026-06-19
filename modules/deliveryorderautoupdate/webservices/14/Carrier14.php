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
$postcode = Db::getInstance()->getValue(
    'SELECT a.postcode FROM '._DB_PREFIX_.'orders o
    LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery=a.id_address
	LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
    WHERE oc.id_order_carrier = '.$id_order
);
$id_shop = Tools::getValue('id_shop');
$id_carrier = 14;
$result_carrier = array();
$status_error = null;
$glsstatus = null;

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.colisprive.com/moncolis/pages/detailColis.aspx?numColis={$tracking_number}{$postcode}",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/xml"
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$file_webcarrier = dirname(__FILE__)."/steps{$id_carrier}.xml";
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = '100';
$events = array();
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
libxml_use_internal_errors(true);
$doc->loadHTML($response);
$finder = new DomXPath($doc);
$nodes = $finder->query("//table[contains(@class, 'tableHistoriqueColis')]");
if ($nodes->length) {
    $row = $finder->query("tr[contains(@class, 'bandeauText')]", $nodes->item(0));
    if ($row->length) {
        $success = true;
        for ($i=0; $i < $row->length; $i++) {
            $dataNode = $finder->query("td[contains(@class, 'tdText')]", $row->item($i));
            $status = (string)$dataNode->item(1)->nodeValue;
            $date = (string)$dataNode->item(0)->nodeValue;
            $date = DateTime::createFromFormat('d/m/Y H:i:s', $date.' 00:00:00');
            $date = $date->format('Y-m-d H:i:s');
            $events[] = array(
                'event_code' => $status,
                'event_description' => $status,
                'event_date' => $date,
                'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status, false),
            );
        }
        $events = array_reverse($events);
        $event = end($events);
        $status = $event['event_code'];
        $date = $event['event_date'];
        $result_status = $event['event_description'];
    }
} else {
    $success = false;
    $status = '105';
    $date = date("Y-m-d H:i:s");
    $result_status = '';
}

$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status, false);
// foreach ($webxml_crr->step as $key => $step) {
//     $statuscode = $step->statuscode;
//     if (is_array($statuscode)) {
//         if (in_array($status, $statuscode)) {
//             $id_status = $step->id_status;
//             break;
//         }
//     } else {
//         if (is_object($statuscode)) {
//             $statuscode = (array)$statuscode;
//             $statuscode = implode('', $statuscode);
//         }
//         if (strcmp($status, $statuscode) === 0) {
//             $id_status = $step->id_status;
//             break;
//         }
//     }
// }
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
