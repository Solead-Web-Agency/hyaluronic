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
$tracking = Tools::getValue('parcel_number');
$tracking_number = Tools::substr($tracking, -8, 8);
$conf_token = Configuration::get('DELIVERY_TOKEN');
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 68;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials68.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$enseigne = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER68_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$privatekey = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER68_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$enseigne = $enseigne['value'];
$privatekey = $privatekey['value'];
//$security = $enseigne.$tracking_number.Tools::strtoupper($lang['iso_code']).$privatekey;
$security = $enseigne.$tracking_number.'FR'.$privatekey;
$securitymd5 = Tools::strtoupper(md5($security));
//print_r($securitymd5);exit;

$data= '<soap:Envelope
xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
xmlns:web="http://www.mondialrelay.fr/webservice/">
   <soap:Header/>
   <soap:Body>
      <web:WSI2_TracingColisDetaille>
         <!--Optional:-->
         <web:Enseigne>'.$enseigne.'</web:Enseigne>
         <!--Optional:-->
         <web:Expedition>'.$tracking_number.'</web:Expedition>
         <!--Optional:-->
         <web:Langue>FR</web:Langue>
         <!--Optional:-->
         <web:Security>'.$securitymd5.'</web:Security>
      </web:WSI2_TracingColisDetaille>
   </soap:Body>
</soap:Envelope>';
$uri = 'http://api.mondialrelay.com/Web_Services.asmx';


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $uri,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: text/xml",
    "postman-token: e6bc2e27-8abd-6869-b1e3-1a41bff24787"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    header("Content-Type: text/xml");
    echo $response;
}
