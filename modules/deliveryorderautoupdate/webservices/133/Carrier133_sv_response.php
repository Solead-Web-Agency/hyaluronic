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
header("Content-Type: text/xml");
echo $response;
