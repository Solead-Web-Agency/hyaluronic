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
$id_carrier = 184;
$id_shop = Tools::getValue('id_shop');
$user = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER184_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER184_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$user = $user['value'];
$password = $password['value'];

$data='<?xml version="1.0" encoding="UTF-8"?>
<trackingrequest>
 <user>'.$user.'</user>
 <password>'.$password.'</password>
 <trackingnumbers>
 <trackingnumber>'.$tracking_number.'</trackingnumber>
 </trackingnumbers>
</trackingrequest>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://apps.geopostuk.com/trackingcore/dpd/parcels",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: text/xml",
    "Cookie: ak_bmsc=C327E2870BA52C2533CFFDE04E49196C02175C05025400000E02295F93AD9276~pl6SfvDKascXznajHRhsdXyT957pKPI".
    "fDpWVsJAVeF2m+xobmFv/MO6fcsu9C+YvDwlYuQ6fXV00iB+Squwzhlxl5at4c6omuhFX6B/sLEQqMn2Il9hAC+mIniFtv7as9H6HS6Ik4W9Qw59".
    "Ze26+3JMiFwsPstvfiuDqBNEC7f0DN/rtrdQjqfMcWKJe1A4/Flbu4UIFPqi/CIB53ZFrRY7DRKlXXpLd1AiL1Kic8kKsw=; X-Mapping-mhdpb".
    "jif=250FAD21CC968A87638098272F020F60; bm_sv=2D09517B6B485A7268D494D054884431~Gb6jQRcOJgGEOWjLqG9nykW+5ZDwf2yFfzT".
    "E2L2mMpP+IO6nmvcAjS/Nf8K3QvVuulLvFQUghxvph9k2b80aopHYbZJIqw/FgDO2o55GvUGbMS912Lp8N+pm9UruUGbe2T97OPJkzV7bVacfYW9".
    "U14uW/NXsjqBOGukC2mxxPiw="
  ),
));

$response = curl_exec($curl);

curl_close($curl);
header("Content-Type: text/xml");
echo $response;
