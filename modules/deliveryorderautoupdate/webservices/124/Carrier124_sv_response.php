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
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 124;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials124.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[0]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[1]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];

$data= '<soapenv:Envelope
xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:req="http://www.post.ch/npp/trackandtracews/v02/shipmentssearch/req">
  <soapenv:Header/>
  <soapenv:Body>
    <req:ShipmentsSearch>
      <language>en</language>
      <ShipmentNumbers>
        <ShipmentNumber>'.$tracking_number.'</ShipmentNumber>
      </ShipmentNumbers>
      <Identity>?</Identity>
      <Version>2.4</Version>
    </req:ShipmentsSearch>
  </soapenv:Body>
</soapenv:Envelope>';
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://webservices.post.ch:443/IN_MYPBxTT/services/TrackAndTraceDFUv25.ws",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Accept-Encoding: gzip,deflate",
    "Content-Type: text/xml",
 //   "Content-Length: 541",
    "Host: webservices.post.ch:443",
    "Connection: Keep-Alive",
    "User-Agent: Apache-HttpClient/4.1.1 (java 1.5)",
    "Authorization: Basic ". base64_encode($username.':'.$password),
  ),
));

$result = curl_exec($curl);

curl_close($curl);
header('Content-Type: text/xml');
echo $result;
