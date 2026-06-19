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
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$id_order = Tools::getValue('shipment_ref');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 38;
$id_shop = (int)Tools::getValue('id_shop');
$apikey = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER38_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$apipassword = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER38_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$accountnumber = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER38_id3" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$apikey = $apikey['value'];
$apipassword = $apipassword['value'];
$accountnumber = $accountnumber['value'];

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://digitalapi.auspost.com.au/shipping/v1/track?tracking_ids='.$tracking_number.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Account-Number: 0000123456",
    "Accept: application/json",
    "Content-Type: application/json",
    "Authorization: ". base64_encode($apikey.':'.$apipassword)
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
