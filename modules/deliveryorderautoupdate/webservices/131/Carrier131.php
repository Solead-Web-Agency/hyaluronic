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
$postcode = Tools::substr($postcode, 0, 4);
$data = '<?xml version="1.0" encoding="utf-8"?><soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:ser="http://servicio.envialiamobileservice.dinaprise.com"><soapenv:Header/>
<soapenv:Body><ser:buscarEstadoEnvioWeb><strValor>'.$tracking_number.'</strValor><strTipoBusqueda>T</strTipoBusqueda>
<strCodPostal>'.$postcode.'</strCodPostal></ser:buscarEstadoEnvioWeb></soapenv:Body></soapenv:Envelope>';
//print_r($data);exit;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://app.envialia.com//axis2/services/'.
  'EnvialiaMobileService2.00.02.EnvialiaMobileService2.00.02HttpSoap12Endpoint/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    'Host: app.envialia.com',
    'Content-Type: text/plain'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$result_carrier = array();
$response = str_ireplace(['soapenv:', 'ns:', 'ax23:', 'xsi:'], '', $response);
$xml = simplexml_load_string($response);
$id_status = 100;
$file_webcarrier = dirname(__FILE__).'/steps131.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$rs = $xml->Body->buscarEstadoEnvioWebResponse->return;
$events = array();
if (isset($rs->attributes()->nil)) {
    $success = false;
    $status = '404';
    $date = date("Y-m-d H:i:s");
    $result_status = 'Not found';
} else {
    $success = true;
    $datenode = (string)$rs->strFechaEnt;
    $status = (string)$rs->strCodEstado;
    $date = DateTime::createFromFormat('d/m/Y', $datenode);
    $date = $date->format('Y-m-d');
    $result_status = (string)$rs->strDesEstado;
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
    $events[] = array(
        'event_code' => $status,
        'event_description' => $result_status,
        'event_date' => $date,
        'id_status' => $id_status,
    );
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
