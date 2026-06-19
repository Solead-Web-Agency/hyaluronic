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
$order_reference = Tools::getValue('order_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$CodiceCliente = Configuration::get('HL_CARRIER217_id1');
$Controllo = $order_reference.'FERCAM'.$CodiceCliente ;
$Controllo = md5($Controllo);
$Controllo = Tools::substr($Controllo, 0, 10);
//echo $Controllo ; exit ;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://tracktrace.fercamapps.com/DirektRef/'.$order_reference.'/'.$CodiceCliente.'/'.$Controllo.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
CURLOPT_HTTPHEADER => array(
    'Accept-Language: it',
    'Cookie: ARRAffinity=ca3c1e581f05eb74edd90e6b97659d62bcf6e282c3dfc021b1ca35fd9bac1dd5; ARRAffinitySameSite=ca3c1e581f05eb74edd90e6b97659d62bcf6e282c3dfc021b1ca35fd9bac1dd5; ASP.NET_SessionId=ocvti3htfecc23no2yj2uj0p'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
