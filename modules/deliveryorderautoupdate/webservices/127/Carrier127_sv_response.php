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
$id_carrier = 127;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials127.xml';
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



$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.correosexpress.com/wpsc/apiRestSeguimientoEnvios/rest/seguimientoEnvios",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "<?xml version='1.0' encoding='UTF-8'?>
  <SeguimientoEnviosRequest
  xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
  xsi:noNamespaceSchemaLocation='SeguimientoEnviosRequest.xsd'>
  <Solicitante>xx</Solicitante> <Dato>".$tracking_number."</Dato>
  </SeguimientoEnviosRequest>",
  CURLOPT_HTTPHEADER => array(
    "authorization: Basic ".base64_encode($username.':'.$password),
    "cache-control: no-cache",
    "content-type: application/xml"
  ),
));

$result = curl_exec($curl);
header('content-type: text/plain');
echo $result;
//print_r($result);
//var_dump($_POST);
//echo htmlentities($data);
