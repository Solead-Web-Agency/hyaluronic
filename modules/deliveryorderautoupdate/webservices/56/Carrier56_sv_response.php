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
$id_order = (int)Tools::getValue('shipment_ref');
$id_carrier = 56;
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_shop = Tools::getValue('id_shop');
$credentials = dirname(__FILE__).'/credentials56.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($credentials, 'SimpleXMLElement', LIBXML_NOCDATA)));

$access_key = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER56_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);

$access_key = $access_key['value'];

$data= '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
 <env:Header/>
 <env:Body>
 <ns3:getPublicServiceShipmentDetails
xmlns:ns2="http://www.schenker.com/SGI/v4_0"
xmlns:ns3="http://www.schenker.com/CustomerServices/eBusiness/ShipmentService/v2">
 <AccessKey>'.$access_key.'</AccessKey>
 <in>
 <ns2:ApplicationArea>
 	 <ns2:requestId>NGES-TRACKING-144255386</ns2:requestId>
 <ns2:CreationDateTime>2020-04-20T11:42:33.867+01:00</ns2:CreationDateTime>
 </ns2:ApplicationArea>
 <ns2:referenceType>ff</ns2:referenceType>
 <ns2:referenceNumber>'.$tracking_number.'</ns2:referenceNumber>
 </in>
 </ns3:getPublicServiceShipmentDetails>
 </env:Body>
</env:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://eschenker.dbschenker.com/webservice/trackingWebServiceV2",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/xml",
    "Cookie: INGRESSCOOKIE=1587483340.164.1737.82550"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header('Content-Type: text/xml');
echo $response;
