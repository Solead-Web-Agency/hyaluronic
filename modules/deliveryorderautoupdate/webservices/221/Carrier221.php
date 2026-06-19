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
$result_carrier = array();
$client_id = Configuration::get('HL_CARRIER221_id1');
$status_error = null;
$glsstatus = null;

$curl = curl_init();
$data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:get="http://getidspedizionebyidcollo.wsbeans.iseries/">
   <soapenv:Header/>
   <soapenv:Body>
      <get:getidspedizionebyidcollo>
         <arg0>
            <CLIENTE_ID>'.$client_id.'</CLIENTE_ID>
            <COLLO_ID>'.$tracking_number.'</COLLO_ID>
         </arg0>
      </get:getidspedizionebyidcollo>
   </soapenv:Body>
</soapenv:Envelope>';

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://wsr.brt.it:10041/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: text/xml;charset=UTF-8"
  ),
));
$response1 = curl_exec($curl);
curl_close($curl);
$response1 = str_ireplace(['soap:', 'ns2:'], '', $response1);
$response1 = simplexml_load_string($response1);
$array = json_decode(json_encode($response1), true);
$id_spedizione = $array['Body']['getidspedizionebyidcolloResponse']['return']['SPEDIZIONE_ID'] ;

$curl = curl_init();
$data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:brt="http://brt_trackingbybrtshipmentid.wsbeans.iseries/">
   <soapenv:Header/>
   <soapenv:Body>
      <brt:brt_trackingbybrtshipmentid>
         <arg0>
            <LINGUA_ISO639_ALPHA2>EN</LINGUA_ISO639_ALPHA2>
            <SPEDIZIONE_ANNO>0</SPEDIZIONE_ANNO>
            <SPEDIZIONE_BRT_ID>'.$id_spedizione.'</SPEDIZIONE_BRT_ID>
         </arg0>
      </brt:brt_trackingbybrtshipmentid>
   </soapenv:Body>
</soapenv:Envelope>';

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://wsr.brt.it:10041/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: text/xml;charset=UTF-8"
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$response = str_ireplace(['ns1:', 'SOAP:', 'ns2:'], '', $response);
$response = simplexml_load_string($response);
$array = json_decode(json_encode((array)$response), true);
$array = $array['Body'];

$file_webcarrier = dirname(__FILE__).'/steps221.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$result_ = 0;
$events = array();
if (isset($array['Fault'])) {
    $success = false;
    $status = $array['Fault']['faultcode'];
    $status_event = $array['Fault']['faultstring'];
    $date = date('Y-m-d H:i:s');
} else {
    $success = true;
    $array = $array['brt_trackingbybrtshipmentidResponse'];
    $events = array_map(function ($e) use ($webxml_crr) {
        $date = DateTime::createFromFormat('d.m.Y H.i', $e['EVENTO']["DATA"].' '.$e['EVENTO']['ORA']);
        return array(
            'event_code' => (string)$e["EVENTO"]["ID"],
            'event_description' => (string)$e["EVENTO"]["DESCRIZIONE"],
            'event_date' => $date->format('Y-m-d H:i:s'),
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e["EVENTO"]["ID"])
        );
    }, array_reverse($array["return"] ["LISTA_EVENTI"]));
    $array1 = $array["return"]["LISTA_EVENTI"][0]["EVENTO"];
    $date = DateTime::createFromFormat('d.m.Y H.i', $array1["DATA"].' '.$array1['ORA']);
    $date = $date->format('Y-m-d H:i:s');
    $status_event = $array1["DESCRIZIONE"];
    $status = $array1["ID"];
}
$result_ = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $status_event;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $status_event;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $result_;
$result_carrier['result']['all_events'] = $events;

echo json_encode($result_carrier);
