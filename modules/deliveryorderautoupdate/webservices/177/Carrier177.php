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
$id_carrier = 177;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials177.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[0]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[1]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];
$uri = 'https://api.bpost.be/services/trackedmail/item/'.$tracking_number.'/trackingInfo';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '. base64_encode($username.':'.$password)));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
$result_ent = $result;
$file_webcarrier = dirname(__FILE__).'/steps177.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = 100;
if (strpos($result_ent, 'Error report')) {
    $content = array();
    $status_error = 'Numéro incorrect';
    preg_match('/HTTP Status ([0-9]*)/', $result_ent, $content);
    $status = isset($content[1])?$content[1]:100;
    $date = Date('Y-m-d H:i:s');
    preg_match('/<u>(JBWEB.*)<\/u>/', $result_ent, $content);
    $desc = isset($content[1])?$content[1]:'';
} else {
    $xml = new SimpleXMLElement($result);
    $status = (string)$xml->stateInfo[count($xml->stateInfo)-1]->stateCode;
    $date = (string)$xml->stateInfo[count($xml->stateInfo)-1]->time;
    $desc = $status;
}
foreach ($webxml_crr->step as $key => $step) {
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
        if (strcmp($status, $statuscode) === 0) {
            $id_status = $step->id_status;
            break;
        }
    }
}
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = 'true';
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = date(
    "Y-m-d H:i:s",
    strtotime(str_replace("/", "-", $date))
);
$result_carrier['result']['module_shipping_status_code'] = $id_status;

echo json_encode($result_carrier);
