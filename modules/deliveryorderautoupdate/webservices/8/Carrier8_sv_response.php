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
if ($conf_token != $token) {
    die("wrong token");
}
if (Tools::substr($tracking_number, 0, 3) != "250") {
    $tracking_number = '250' . $tracking_number;
}

$data = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:car="http://www.cargonet.software/">
   <soap:Header/>
   <soap:Body>
      <car:getShipmentTrace>
         <car:customer_center>3</car:customer_center>
         <car:customer>1064</car:customer>
         <!--Optional:-->
         <car:password>Pr2%5sHg</car:password>
         <!--Optional:-->
         <car:shipmentnumber>'.$tracking_number.'</car:shipmentnumber>
      </car:getShipmentTrace>
   </soap:Body>
</soap:Envelope>';
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/soap+xml",
    "Cookie: pers=rd3o00000000000000000000ffff0a65143bo80"
  ),
));

$response = curl_exec($curl);

header("Content-type: text/xml");
curl_close($curl);
print_r($response);
