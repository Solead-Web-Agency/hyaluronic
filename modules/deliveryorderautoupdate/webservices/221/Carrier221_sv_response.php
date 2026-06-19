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

$response = curl_exec($curl);
curl_close($curl);
$response = str_ireplace(['soap:', 'ns2:'], '', $response);
$response = simplexml_load_string($response);
$array = json_decode(json_encode($response), true);
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
header("Content-Type: text/xml");
echo $response;
