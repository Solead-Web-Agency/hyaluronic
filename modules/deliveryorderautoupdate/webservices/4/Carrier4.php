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
$status_error = null;
$status = null;
$result_carrier = array();
$postcode = Db::getInstance()->getValue(
    'SELECT a.postcode FROM '._DB_PREFIX_.'orders o
    LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery=a.id_address
	LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
    WHERE oc.id_order_carrier = '.$id_order
);
$postcode = str_replace(' ', '', $postcode);
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://www.gls-info.nl/Tracking?parcelNo='.$tracking_number.'&zipcode='.$postcode.'&lang=EN',
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
$file_webcarrier = dirname(__FILE__).'/steps4.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

$id_status = 0;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
libxml_use_internal_errors(true);
$doc->loadHTML($response);
$finder = new DomXPath($doc);
$table = $finder->query("//table[@id='lastscan']/tr/td");
$trace = $finder->query("//table[@id='scandata_table']/tr");
$events = array();
if ($table->length) {
    $success = true;
    for ($i=1; $i < $trace->length; $i++) {
        $td = $finder->query("td", $trace->item($i));
        $datenode = (string)$td->item(0)->nodeValue;
        $date = str_replace('<br />', '', $datenode);
        $date = DateTime::createFromFormat('d-m-Y H:i', $date);
        $date = $date->format('Y-m-d H:i:s');
        $events[] = array(
            'event_code' => (string)$td->item(6)->nodeValue,
            'event_description' => trim((string)$td->item(7)->nodeValue),
            'event_date' => $date,
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$td->item(6)->nodeValue)
        );
    }
    $status = (string)$table->item(6)->nodeValue;
    $datenode = (string)$table->item(0)->nodeValue;
    $date = str_replace('<br />', '', $datenode);
    $date = DateTime::createFromFormat('d-m-Y H:i', $date);
    $date = $date->format('Y-m-d H:i:s');
    $result_status = trim((string)$table->item(7)->nodeValue);
} else {
    $success = false;
    $status = 'E206';
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
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
