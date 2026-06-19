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
$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://dpd.pt/track-and-trace?reference='.$tracking_number,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Cookie: SERVERID=sf1'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$file_webcarrier = dirname(__FILE__).'/steps186.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

$id_status = '100';
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
libxml_use_internal_errors(true);
$doc->loadHTML($response);
$finder = new DomXPath($doc);
$table = $finder->query("//table[@class='table']/tbody/tr");
$events = array();
if ($table->length) {
    for ($i=0; $i < $table->length; $i++) {
        $td = $finder->query('td', $table->item($i));
        $status = (string)$td->item(0)->nodeValue;
        $datenode = (string)$td->item(1)->nodeValue;
        $date = DateTime::createFromFormat('Y/m/d H:i', $datenode);
        $date = $date->format('Y-m-d H:i:s');
        $result_status = (string)$td->item(2)->nodeValue;
        $events[] = array(
            'event_code' => $status,
            'event_description' => $result_status,
            'event_date' => $date,
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status)
        );
    }
    $success = true;
    $event = $events[0];
    $status = $event['event_code'];
    $date = $event['event_date'];
    $result_status = $event['event_description'];
} else {
    $success = false;
    $status = 'PARCEL NOT FOUND';
    $date = date("Y-m-d H:i:s");
    // $err = $finder->query("//h2[contains(@class, 'title')]");
    $result_status = '';
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
$result_carrier['result']['all_events'] = array_reverse($events);
print_r(json_encode($result_carrier));
