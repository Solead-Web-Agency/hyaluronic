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

$id_carrier = 215;
$result_carrier = array();
$data = '{"tipoRichiedente":"WEB",
            "codiceSpedizione":"'.$tracking_number.'",
            "periodoRicerca":1
        }';
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.poste.it/online/dovequando/DQ-REST/ricercasemplice",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/json"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header("Content-Type: text/plain");
$response = Tools::jsonDecode($response, true);

$file_webcarrier = dirname(__FILE__).'/steps215.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 0;
$events = array();
if (!isset($response['listaMovimenti']) || !count($response['listaMovimenti'])) {
    $success = false;
    if (isset($response['esitoRicerca'])) {
        $status =  $response['esitoRicerca'];
        $desc = $status =  $response['stato'];
    } else {
        $status =  $response['tipo'];
        $desc = $response['descrizione'];
    }
    $date = Date('Y-m-d H:i:s');
} else {
    $success = true;
    $events = array_map(function ($e) use ($webxml_crr) {
        $date = $e['dataOra']/1000;
        $date = date("Y-m-d H:i:s", $date);
        return array(
            'event_code' => $e['statoLavorazione'],
            'event_description' => $e['luogo'],
            'event_date' => $date,
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, trim($e['statoLavorazione'])),
        );
    }, $response['listaMovimenti']);
    $shipment = $response['listaMovimenti'][count($response['listaMovimenti'])-1];
    $status = $shipment['statoLavorazione'];
    $desc = $shipment['luogo'];
    $date = $shipment['dataOra']/1000;
    $date = date("Y-m-d H:i:s", $date);
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
