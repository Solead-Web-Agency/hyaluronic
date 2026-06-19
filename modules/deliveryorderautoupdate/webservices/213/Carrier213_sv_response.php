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

$id_carrier = 213;
$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://www.gls-italy.com/?option=com_gls&view=track_e_trace&mode=search&numero_spedizione='.
  $tracking_number.'&tipo_codice=nazionale',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Cookie: joomsef_lang=en; STunn=A+BuYvABOgqObMR00YSiDg$$; ".
    "0c73b3f6168197b4a0547b472c2dfd1f=5k3s6tltc7mnhqmj9urhfbbhpf"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
