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
$tracking_number = Tools::strtoupper($tracking_number);
$conf_token = Configuration::get('DELIVERY_TOKEN');
if ($conf_token != $token) {
    die("wrong token");
}
$id_shop = Tools::getValue('id_shop');
$client_secret = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER134_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$client_id = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER134_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.ctt.pt/cttorg/clients/tracktracers/api/v2/events/search',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
	"objects": [
        {"objectNumber": "'.$tracking_number.'"}
	]
}',
  CURLOPT_HTTPHEADER => array(
    'x-ibm-Client-Secret: '.$client_secret.'',
    'x-ibm-Client-ID: '.$client_id.'',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header("Content-Type: text/plain");
print_r(Tools::jsonDecode($response));
