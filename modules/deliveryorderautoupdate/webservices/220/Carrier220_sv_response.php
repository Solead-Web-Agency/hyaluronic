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

$id_order = (int)Tools::getValue('shipment_ref');

$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$lang = Db::getInstance()->getValue(
    'SELECT CASE WHEN l.iso_code IN("fr","es","nl","pt","pl","de","it","ru") THEN l.iso_code ELSE "en" END
    FROM '._DB_PREFIX_.'order_carrier oc
	LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
	LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
    WHERE oc.id_order_carrier='.(int)$id_order.''
);
$id_carrier = 220;
$result_carrier = array();
$context = Context::getContext();
$curl = curl_init();
$data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:brt="http://brt_trackingbybrtshipmentid.wsbeans.iseries/">
   <soapenv:Header/>
   <soapenv:Body>
      <brt:brt_trackingbybrtshipmentid>
         <arg0>
            <LINGUA_ISO639_ALPHA2>'.$lang.'</LINGUA_ISO639_ALPHA2>
            <SPEDIZIONE_ANNO>0</SPEDIZIONE_ANNO>
            <SPEDIZIONE_BRT_ID>'.$tracking_number.'</SPEDIZIONE_BRT_ID>
         </arg0>
      </brt:brt_trackingbybrtshipmentid>
   </soapenv:Body>
</soapenv:Envelope>';

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://wsr.brt.it:10041/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: text/xml;charset=UTF-8"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header("Content-Type: text/xml");
echo $response;
