<?php
/**
* 2007-2021 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2021 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

$DOCUMENT_ROOT = explode('modules', dirname(__FILE__));
require_once($DOCUMENT_ROOT[0].'config/config.inc.php');
require_once($DOCUMENT_ROOT[0].'init.php');

$context = Context::getContext();
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$reference = Tools::getValue('order_ref');
$id_order = Tools::getValue('shipment_ref');
$id_shop = Tools::getValue('id_shop');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);

//print_r($tracking_number);exit;
if ($conf_token != $token) {
    die("wrong token");
}
$credentials = Db::getInstance()->getValue(
    'SELECT REPLACE(c2.url,"@","'.$tracking_number.'")
    FROM '._DB_PREFIX_.'order_carrier oc
  LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference=c2.id_reference AND c2.deleted=0
  WHERE oc.id_order_carrier = '.$id_order.''
);

$credentials = explode('_', $credentials);
$credentials = end($credentials);
//print_r($credentials);
$shippingcustomercenter = substr($credentials,0,3);
//print_r($shippingcustomercenter);
$shippingcustomer = substr($credentials,3,8);
//print_r($shippingcustomer);
$tracking_number = explode('_', $tracking_number);
$tracking_number = current($tracking_number);
//print_r($tracking_number);exit;
$data = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:car="http://www.cargonet.software/">
   <soap:Header/>
   <soap:Body>
      <car:getShipmentTraceByReferenceGlobalWithCenterAsArray>
         <car:customer_center>3</car:customer_center>
         <car:customer>1064</car:customer>
         <!--Optional:-->
         <car:password>Pr2%5sHg</car:password>
         <!--Optional:-->
         <car:reference>'.$tracking_number.'</car:reference>
         <!--Optional:-->
         <car:shipping_date></car:shipping_date>
         <car:shipping_customer_center>'.$shippingcustomercenter.'</car:shipping_customer_center>
         <car:shipping_customer>'.$shippingcustomer.'</car:shipping_customer>
      </car:getShipmentTraceByReferenceGlobalWithCenterAsArray>
   </soap:Body>
</soap:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    'Host: webtrace.dpd.fr',
    'Content-Type: application/soap+xml',
    'Accept-Encoding: gzip,deflate',
    'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)',
    'Cookie: __cfduid=def5d54a460a440781c23b59b07fda7371607457320; pers=!Q0wB/bV2PZfYll6rEm4m3rxJRTAJ'.
    'BMh81p3nQaI6ifVr84HKfk8L6MM7UTYoQSatsP//lq0OyJRaWUUAGr38ecGSvl2Dg2LX0c0TmjI='
  ),
));

$response = curl_exec($curl);

header("Content-type: text/xml");
curl_close($curl);
print_r($response);
