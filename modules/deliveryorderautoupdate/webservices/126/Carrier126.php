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
$lang = Db::getInstance()->getValue(
    'SELECT CASE WHEN l.iso_code IN("es","pt") THEN l.iso_code ELSE "en" END
    FROM '._DB_PREFIX_.'order_carrier oc
	LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
	LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
    WHERE oc.id_order_carrier='.(int)$id_order.''
);
$postcode = Db::getInstance()->getValue(
    'SELECT a.postcode FROM '._DB_PREFIX_.'orders o
    LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery=a.id_address
	LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
    WHERE oc.id_order_carrier = '.$id_order
);
$id_carrier = 126;
$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://m.gls-spain.es/e/'.$tracking_number.'/'.$postcode.'/'.$lang.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Cookie: culture=en; ASP.NET_SessionId=xkbxx3jpcm5b3chdb2e4am3e"
  ),
));

$response = curl_exec($curl);
curl_close($curl);


$file_webcarrier = dirname(__FILE__)."/steps{$id_carrier}.xml";
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = '100';
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
libxml_use_internal_errors(true);
$doc->loadHTML($response);
$finder = new DomXPath($doc);
$table = $finder->query("//div[@id='div-info-envio']");
$events = array();
if ($table->length) {
    $statusnode = $finder->query("//span[@id='estado']", $table->item(0));
    $datenode = $finder->query("//table[contains(@class, 'table-striped')]/tr/td[contains(@class, 'col-sm-2')]");
    $eventsnode = $finder->query("//table[contains(@class, 'table-striped')]/tr");
    if ($statusnode->length && $eventsnode->length) {
        $success = true;
        // $status = (string)$statusnode->item(0)->nodeValue;
        // $date = (string)$datenode->item($datenode->length-1)->nodeValue;
        $match = array();
        // $format = '';
        // if (preg_match('/(\d{1,2}\/\d{1,2}) (\d{1,2}:\d{1,2})/', $date, $match)) {
        //     $date = DateTime::createFromFormat('d/m H:i', "{$match[1]} {$match[2]}");
        //     $date = $date->format('Y-m-d H:i:s');
        //     $result_status = '';
        // } else {
        //     $date = date();
        // }
            // $eventnode = $finder->query("td", $eventsnode->item(1));
            // d($eventnode);
            // d($doc->saveHTML($eventsnode->item(1)));
        for ($i=0; $i < $eventsnode->length; $i++) {
            $eventnode = $finder->query("td", $eventsnode->item($i));
            $date = (string)$eventnode->item(0)->nodeValue;
            $desc = (string)$eventnode->item(1)->nodeValue;
            if (preg_match('/(\d{1,2}\/\d{1,2}) (\d{1,2}:\d{1,2})/', $date, $match)) {
                $date = DateTime::createFromFormat('d/m H:i', "{$match[1]} {$match[2]}");
                $date = $date->format('Y-m-d H:i:s');
                $result_status = '';
            } else {
                $date = date();
            }
            $events[] = array(
                'event_code' => $desc,
                'event_description' => $desc,
                'event_date' => $date,
                'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, trim($desc))
            );
        }
        $event = end($events);
        $status = $event['event_code'];
        $result_status = $event['event_description'];
        $date = $event['event_date'];
    }
} else {
    $success = false;
    $status = '105';
    $date = date("Y-m-d H:i:s");
    $err = $finder->query("//h2[contains(@class, 'title')]");
    $result_status = $err->length?$err->item(0)->nodeValue:'';
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
