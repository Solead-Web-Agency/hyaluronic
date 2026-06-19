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
$id_shop = Tools::getValue('id_shop');
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER187_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER187_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];
$data = '<soapenv:Envelope
xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:even="http://events.dpdinfoservices.dpd.com.pl/">
   <soapenv:Header/>
   <soapenv:Body>
      <even:getEventsForWaybillV1>
         <!--Optional:-->
         <waybill>'.$tracking_number.'</waybill>
         <!--Optional:-->
         <eventsSelectType>ALL</eventsSelectType>
         <!--Optional:-->
         <language>EN</language>
         <!--Optional:-->
         <authDataV1>
            <!--Optional:-->
            <channel>?</channel>
            <!--Optional:-->
            <login>'.$username.'</login>
            <!--Optional:-->
            <password>'.$password.'</password>
         </authDataV1>
      </even:getEventsForWaybillV1>
   </soapenv:Body>
</soapenv:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://dpdinfoservices.dpd.com.pl/DPDInfoServicesObjEventsService/DPDInfoServicesObjEvents',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: text/xml;charset=UTF-8'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$response = str_ireplace(['s:', 'ns2:'], '', $response);
$response = simplexml_load_string($response);
$array = json_decode(json_encode((array)$response), true);
$array = $array['Body']['getEventsForWaybillV1Response']['return'];

$file_webcarrier = dirname(__FILE__).'/steps187.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$events = array();
if (!isset($array['eventsList'])) {
    $success = false;
    $status = $array['confirmId'];
    $desc = '';
    $date = date('Y-m-d H:i:s');
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
} else {
    $events = $array['eventsList'];
    $success = true;
    $events = array_map(function ($e) use ($webxml_crr) {
        return array(
            'event_code' => $e["businessCode"],
            'event_description' => $e["description"],
            'event_date' => date('Y-m-d H:i:s', strtotime($e['eventTime'])),
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e["businessCode"])
        );
    }, array_reverse($events));
    $event = $events[count($events)-1];
    $status = $event['event_code'];
    $desc = $event['event_description'];
    $date = $event['event_date'];
    $id_status = $event['id_status'];
}
$result_ = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;

echo json_encode($result_carrier);
