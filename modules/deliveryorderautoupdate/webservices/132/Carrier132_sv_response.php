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
$id_shop = Tools::getValue('id_shop');
$user = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER132_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER132_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$pass = Tools::strtoupper(md5($password));
$agency = Tools::substr($tracking_number, 0, 4);
$tracking_number = str_replace('/', '', $tracking_number);
$tracking_number = Tools::substr($tracking_number, 4, 8);
//$url = 'http://pda.nacex.com/nacex_ws/ws?method=getInfoEnvio&data=tipo=E%7Cdel='.$agency.'%7Cnum='.$tracking_number.'&user='.$user.'&pass='.$pass.'';
//print_r($tracking_number);exit;
$result_carrier = array();
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://pda.nacex.com/nacex_ws/ws?method=getInfoEnvio&data=tipo=E%7Cdel='
  .$agency.'%7Cnum='.$tracking_number.'&user='.$user.'&pass='.$pass.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
