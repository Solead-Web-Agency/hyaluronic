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
$lang = Db::getInstance()->getValue(
    'SELECT CASE WHEN l.iso_code IN("fr","es","pl","de","it") THEN l.iso_code ELSE "en" END
    FROM '._DB_PREFIX_.'order_carrier oc
	LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
	LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
    WHERE oc.id_order_carrier='.(int)$id_order.''
);
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:cxf="http://cxf.tracking.soap.chronopost.fr/">
   <soapenv:Header/>
   <soapenv:Body>
      <cxf:trackSkybill>
         <!--Optional:-->
         <language>'.$lang.'</language>
         <!--Optional:-->
         <skybillNumber>'.$tracking_number.'</skybillNumber>
      </cxf:trackSkybill>
   </soapenv:Body>
</soapenv:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.chronopost.fr/tracking-cxf/TrackingServiceWS",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/xml"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header("Content-Type: text/xml");
echo $response;
