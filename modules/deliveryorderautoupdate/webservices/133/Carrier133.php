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
$tracking_number = Tools::strtoupper($tracking_number);
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_shop = Tools::getValue('id_shop');
$userid = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER133_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://cttexpressows.ctt.pt/CTTEWSPoolHTTPS/EventosWS.svc',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'<soapenv:Envelope
  xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/"
  xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
   <soapenv:Header/>
   <soapenv:Body>
      <tem:GetEventosObjectos_V3>
         <!--Optional:-->
         <tem:ID>'.$userid.'</tem:ID>
         <!--Optional:-->
         <tem:NObjectos>
            <!--Zero or more repetitions:-->
            <arr:string>'.$tracking_number.'</arr:string>
         </tem:NObjectos>
      </tem:GetEventosObjectos_V3>
   </soapenv:Body>
</soapenv:Envelope>',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: text/xml;charset=UTF-8',
    'SOAPAction: "http://tempuri.org/IEventosWS/GetEventosObjectos_V3"'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = str_ireplace(['a:', 's:', 'i:'], '', $response);
$response = json_decode(json_encode(simplexml_load_string($response)), true);
$id_status = '0';
$result_carrier = array();
$file_webcarrier = dirname(__FILE__).'/steps133.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$events = array();
if (isset($response['Body']['Fault'])) {
    $response = $response['Body']['Fault'];
    $server = 0;
    $label_status = $response['faultstring'];
    $status = $response['faultcode'];
    $date = date('Y-m-d H:i:s');
} elseif (empty($response['Body']['GetEventosObjectos_V3Response']['GetEventosObjectos_V3Result']['_Objectos'])) {
    $server = 0;
    $label_status = '';
    $status = 5611;
    $date = date('Y-m-d H:i:s');
} else {
    $response = $response['Body']['GetEventosObjectos_V3Response']['GetEventosObjectos_V3Result']['_Objectos'];
    $response = $response['DadosObjectos_V3BE']['_Eventos']['DadosEventos_V3BE'];
    $events = array_map(function ($e) use ($webxml_crr) {
        $label_status = empty($e['_DescricaoEvento'])?'':$e['_DescricaoEvento'];
        $status = $e['_CodigoEvento'];
        $myDateTime = DateTime::createFromFormat(
            'd-m-Y H:i:s',
            $e['_DataEvento']
        );
        $date = $myDateTime->format('Y-m-d H:i:s');
        return array(
            'event_code' => $status,
            'event_description' => $label_status,
            'event_date' => $date,
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status)
        );
    }, $response);
    $track = $response[count($response)-1];
    $server = 1;
    $label_status = $track['_DescricaoEvento'];
    $status = $track['_CodigoEvento'];
    $myDateTime = DateTime::createFromFormat(
        'd-m-Y H:i:s',
        $track['_DataEvento']
    );
    $date = $myDateTime->format('Y-m-d H:i:s');
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);

$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($server == 0 ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['carrier_shipping_status_code'] = $id_status;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;

//print_R($result_carrier);exit;
echo json_encode($result_carrier);
