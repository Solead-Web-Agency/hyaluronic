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
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER74_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER74_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];
$id_carrier = 74;
$result_carrier = array();

//1st step : Request access token
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://oauth2.posti.com/oauth/token?grant_type=client_credentials',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    "Authorization: Basic ". base64_encode($username.':'.$password),
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$access_token = Tools::jsonDecode($response, true);
$access_token = $access_token['access_token'];
//echo $access_token;exit;

//2nd step : Request tracking status
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.posti.fi/tracking/7/shipments/trackingnumber/'.$tracking_number.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    //'Authorization: Bearer eyJhbGciOiJSUzI1NiJ9.eyJtYWlsIjoiMjMzMjM4MC02QGxvY2FsaG9zdC5sb2NhbGhvc3QiLCJzY29wZSI6WyJleHRlcm5hbCJdLCJvcmdhbml6YXRpb24iOiIyMzMyMzgwLTYiLCJyb2xlcyI6WyJST0xFX2FwX2Vjb21tZXJjZV9hcGlfc2hpcG1lbnRfdHJhY2tpbmdfcHVibGljX3JlYWQiXSwibmFtZSI6IktvbmV0aWxpIEdhbWVvbiBmaSBWZXJra29rYXVwcGEiLCJyZW1vdGVfYWRkcmVzcyI6IjEwLjI1MC45My45MyIsImV4cCI6MTYwNjE2OTkwMywianRpIjoiY2QwNTZiZTUtZDM5Zi00NmViLThmOWYtNTQ5ZGVlYzgwNDE4IiwiY2xpZW50X2lkIjoibWFfMjMzMjM4MC02In0.CMrz_gVltTchT6YfWUJhx1ZyQigZVNPUqFLSeeG0Z1Z46fEuE-Lw5dyqkf_Ko6yogY6BSGzTr3K_3fsOVD-aXIflnp-ABCiS8XKARNsDrtEWx5laByjxy_Xou1Mq9mAmXWA2L2ElFBPfi0IYSUl4AtrEHcjwOnkPn7w90Ar1nDhgczuFkxHqMaPeaiE1zVyxDUuRsOLqzXawjLvCPRG7MIfDBI99ZQacto-cpCP82gFFkgpqeXvoEnBSjIUu5IhQkLYyyHpXx1rgc3XRR_Ow3SUp6Aa3-8i0_SeTzEmmFszlZl0tnkh3VT7zgBevQ1vHwA8fs-VDIsxOSZLFYtyVcA'
    'Authorization: Bearer' .$access_token.''
  ),
));

$response = curl_exec($curl);

curl_close($curl);

header("Content-Type: html");
p(Tools::jsonDecode($response));
