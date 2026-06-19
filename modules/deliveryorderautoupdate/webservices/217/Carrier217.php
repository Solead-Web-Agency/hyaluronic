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
$order_reference = Tools::getValue('order_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$CodiceCliente = Configuration::get('HL_CARRIER217_id1');
$Controllo = $order_reference.'FERCAM'.$CodiceCliente ;
$Controllo = md5($Controllo);
$Controllo = Tools::substr($Controllo, 0, 10);
//echo $Controllo ; exit ;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://tracktrace.fercamapps.com/DirektRef/'.$order_reference.'/'.$CodiceCliente.'/'.$Controllo.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
CURLOPT_HTTPHEADER => array(
    'Accept-Language: it',
    'Cookie: ARRAffinity=ca3c1e581f05eb74edd90e6b97659d62bcf6e282c3dfc021b1ca35fd9bac1dd5; ARRAffinitySameSite=ca3c1e581f05eb74edd90e6b97659d62bcf6e282c3dfc021b1ca35fd9bac1dd5; ASP.NET_SessionId=ocvti3htfecc23no2yj2uj0p'
  ),
));

$response = curl_exec($curl);
curl_close($curl);

$file_webcarrier = dirname(__FILE__)."/steps217.xml";
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
libxml_use_internal_errors(true);
$doc->loadHTML($response);
$finder = new DomXPath($doc);
$events = array();
$li = $finder->query("//ul[contains(@class, 'timeline')]/li");
if ($li->length) {
    $success = true;
    $timeLabelNode = $finder->query('span[@class="bg-orange"]', $li->item(0));
    if ($timeLabelNode->length) {
        $timeLabel = trim((string)$timeLabelNode->item(0)->nodeValue);
    } else {
        $timeLabel = '';
    }
    for ($i=0; $i < $li->length; $i++) {
        $timelineNode = $finder->query('div[@class="timeline-item"]/div[@class="timeline-body"]', $li->item($i));
        $locationNode = $finder->query('div[@class="timeline-item"]/h3[@class="timeline-header"]/a', $li->item($i));
        $statusNode = $finder->query('div[@class="movement"]/span[@class="descMov"]', $timelineNode->item(0));
        if (!$statusNode->length) {
            continue;
        }
        $dateNode = $finder->query('div[@class="timeline-item"]/span[@class="time"]', $li->item($i));
        $status = (string)$statusNode->item(0)->nodeValue;
        $des = $status;
        if (!$status) {
            $status = $timeLabel;
        }
        if ($locationNode->length) {
            $location = (string)$locationNode->item(0)->nodeValue;
            if ($location) {
                $des .= ' - '.$location;
            } else {
                continue;
            }
        } else {
            continue;
        }
        $dateStr = trim((string)$dateNode->item(0)->nodeValue);
        $date = str_replace('/', '-', $dateStr);
        if ($date === false) {
            $date = str_replace('/', '-', $dateStr);
        }
        $date = date('Y-m-d H:i:s', strtotime($date));
        $events[] = array(
            'event_code' => $status,
            'event_description' => $des,
            'event_date' => $date,
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status, false),
        );
    }
    $events = array_reverse($events);
    $event = end($events);
    $status = $event['event_code'];
    $date = $event['event_date'];
    $result_status = $event['event_description'];
} else {
    $success = false;
    $status = 'Tracking was not found';
    $date = date("Y-m-d H:i:s");
    $result_status = '';
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status, false);
$result_carrier = array();
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
