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
$id_order = Tools::getValue('shipment_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 174;
$credentials_embed =_PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/'.
$id_carrier.'/credentials'.$id_carrier.'.xml';
$webxml_crr = json_decode(
    json_encode(@simplexml_load_file($credentials_embed, 'SimpleXMLElement', LIBXML_NOCDATA))
);
$values = array();
foreach ($webxml_crr->credential as $cre) {
    $values[] = Configuration::get($cre->credname);
}
$appname = isset($values[0])?$values[0]:'';
$password = isset($values[1])?$values[1]:'';
$xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<data appname="'.$appname.'" language-code="en" password="'.$password.
'" piece-code="'.$tracking_number.'" request="d-get-piece"/>';
$query = http_build_query(array(
  'xml' => $xml,
));
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://cig.dhl.de/services/sandbox/rest/sendungsverfolgung?{$query}",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Basic ZGFoaGFvdWk6YzklXmgqQzY="
  ),
));

$response = curl_exec($curl);
$result = new SimpleXMLElement($response);
curl_close($curl);
d($result);
