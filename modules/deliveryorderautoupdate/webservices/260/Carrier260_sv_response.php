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
$id_carrier = 260;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');

$data= '{ "deliveryOrderNo":"'.$tracking_number.'" }';
$appkey = 'e6428e3a-6563-469f-ae28-0db5c0059e05';
$appsecret = 'b9c11814-ee1c-4279-bd1b-b58f6a9c87e9';
$date= date("Y-d-j H:i:s");
$dateurl= urlencode($date);
$sign_row= 'app_key'.$appkey.'formatjsonmethodtr.order.tracking.gettimestamp'.$date.'v1.0'.$data.$appsecret;
$sign= Tools::strtoupper(md5($sign_row));
$uri = 'http://open.4px.com/router/api/service?method=tr.order.tracking.get&v=1.0&app_key='
.$appkey.'&timestamp='.$dateurl.'&format=json&access_token=&sign='.$sign;
//$uri = 'https://httpbin.org/post?method=tr.order.tracking.get&v=1.0&app_key='.$appkey.'&timestamp='.$dateurl.'&format=json&access_token=&sign='.$sign;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    array(
        'Accpet: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Content-Type: application/json'
    )
);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$result = curl_exec($ch);
print_r($result);
