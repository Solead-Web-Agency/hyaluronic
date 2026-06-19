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
$id_carrier = 200;
$id_shop = Tools::getValue('id_shop');
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER200_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER200_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://express.tnt.com/expressconnect/track.do?xml_in=%3C?xml%20version=%221.0%22%20encoding='.
  '%22UTF-8%22%20standalone=%22no%22?%3E%0A%3CTrackRequest%3E%0A%3CSearchCriteria%3E%0A%3CConsignmentNumber%3E'.
  $tracking_number.'%3C/ConsignmentNumber%3E%0A%3C/SearchCriteria%3E%0A%3CLevelOfDetail%3E%0A%3CComplete%20originAddre'.
  'ss=%22true%22%20destinationAddress=%22true%22%20package=%22true%22%20shipment=%22true%22/%3E%0A%3C/LevelOfDetail%3E'.
  '%0A%3C/TrackRequest%3E',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => array(
    "Content-Type: text/xml",
    "Authorization: Basic ". base64_encode($username.':'.$password),
    "Cookie: BIGipServerexpress.tnt.com_pool_7=2676609546.20992.0000"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header("Content-Type: text/xml");
echo $response;
