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

$id_carrier = 75;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER13_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER13_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];

$data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:trac="http://gls-group.eu/Tracking/">
   <soapenv:Header/>
   <soapenv:Body>
      <trac:TuDetailsRequest>
         <trac:RefValue>'.$tracking_number.'</trac:RefValue>
         <trac:Credentials>
            <trac:UserName>'.$username.'</trac:UserName>
            <trac:Password>'.$password.'</trac:Password>
         </trac:Credentials>
         <!--Zero or more repetitions:-->
         <trac:Parameters>
            <trac:ParamCode>LangCode</trac:ParamCode>
            <trac:ParamValue>EN</trac:ParamValue>
         </trac:Parameters>
      </trac:TuDetailsRequest>
   </soapenv:Body>
</soapenv:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/Tracking",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: text/xml;charset=UTF-8",
    "Cookie: BIGipServerP_Uniportal_Soapapi=!ydwUO2IhYe4U/W/pCFzgT86OtAZzjRcj1".
    "tssFJbk3aO/w1p2Y0pzImXNz/KHUwrIJ9Y9fWpqtj7YNwQ="
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header("Content-Type: text/xml");
echo $response;
