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
$id_carrier = 185;
$id_shop = Tools::getValue('id_shop');
$user = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER185_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER185_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
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
  CURLOPT_URL => "https://apps.geopostuk.com/trackingcore/ie/parcels",
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
$xml = simplexml_load_string($response);
$array = json_decode(json_encode((array)$xml), true);
$file_webcarrier = dirname(__FILE__).'/steps185.xml';
$webxml_crr = json_decode(
    json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
);
$rs = $array['trackingdetails'];
$id_status = 100;
$label_status = '';
$success = false;
if (!isset($rs['trackingdetail'])) {
    $success = false;
    $status = $array['error'];
    $date = date("Y-m-d H:i:s");
} else {
    $rs = $rs['trackingdetail'];
    if (isset($rs['error'])) {
        $success = false;
        $status = $rs['error'];
        $date = date("Y-m-d H:i:s");
        $label_status = 'wrong number';
    } else {
        $result = $rs['trackingevents']['trackingevent'];
        if (count($result)) {
            $event = reset($result);
            $status = $event['code'];
            $date = $event['date'];
            $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
            $label_status = $event['type'];
            $success = true;
        }
    }
}

if ($status) {
    foreach ($webxml_crr->step as $step) {
        $statuscode = $step->status;
        if (is_array($statuscode)) {
            if (in_array($status, $statuscode)) {
                $id_status = $step->id_status;
                break;
            }
        } else {
            if (is_object($statuscode)) {
                $statuscode = (array)$statuscode;
                $statuscode = implode('', $statuscode);
            }
            if (strcmp($status, (string)$statuscode) === 0) {
                $id_status = $step->id_status;
                break;
            }
        }
    }
}

$result_carrier = array();
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $label_status;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['carrier_shipping_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['carrier_shipping_status_code'] = $status;

echo json_encode($result_carrier);
