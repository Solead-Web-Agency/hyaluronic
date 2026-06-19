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
$id_shop = Tools::getValue('id_shop');
$id_carrier = 67;
$status_error = null;
$glsstatus = null;
$result_carrier = array();
$result = 'https://gls-group.eu/app/service/open/rest/FR/fr/rstt001?match='.$tracking_number;
$url_page = strip_tags(Tools::file_get_contents($result, true));
$url = Tools::stripslashes($url_page);
$url = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $url), true);

if (Tools::strlen($url_page) == 0) {
    $status_error = 'Numéro incorrect';
    $result_carrier['result']['shipment_ref'] = $id_order;
    $result_carrier['result']['carrier_server_status_code'] = 'E206';
    $result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
    $result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
    $result_carrier['result']['carrier_shipping_status_code'] = $status_error ? $status_error : $glsstatus;

    $result_carrier['result']['carrier_shipping_status_text'] = null;
    $result_carrier['result']['carrier_server_status_text'] = $status_error ? $status_error : $glsstatus;

    //$result_carrier['shipping_status_text'] = $result_status;
//print_R(($result_carrier));exit;
    $result_carrier['module_shipping_status_code'] = 0;
    print_r(json_encode($result_carrier));
    exit;
}
$file_webcarrier = dirname(__FILE__).'/steps67.xml';


$carrier_xml = array();
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

foreach ($webxml_crr->step as $step) {
    $id_status = $step->id_status;
    $glsstatus = $step->glsstatus;
    $delivery = implode(",", $step->glsstatus);
    $delivery = $delivery ? $delivery : $glsstatus;
    $txt_str = 0;
    $text_link = explode(",", $delivery);
    foreach ($text_link as $txt) {
        $txt = '"'.$txt.'"';
        if (strpos($url_page, $txt) ||
            (strpos($url_page, mb_convert_encoding($txt, "UTF-8", "Windows-1252"))) ||
            (mb_strpos(mb_convert_encoding($url_page, 'utf-8', 'ISO-8859-15'), $txt))
        ) {
            $txt_str = 1;
        }
    }
    if ($txt_str) {
        break;
    }
}

$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = $status_error ? $id_status : null;
$result_carrier['result']['carrier_server_status_text'] = $status_error ? $status_error : null;
$result_carrier['result']['carrier_shipping_status_code'] = $status_error ? null : $id_status;
$result_carrier['result']['carrier_shipping_status_text'] = $status_error ? null : $glsstatus;
$result_carrier['result']['carrier_shipping_status_date'] = $url['tuStatus'][0]['history'][0]['date']
.' '.$url['tuStatus'][0]['history'][0]['time'];

//$result_carrier['shipping_status_text'] = $result_status;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
print_r(json_encode($result_carrier));
