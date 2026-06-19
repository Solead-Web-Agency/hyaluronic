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
require_once('../../classes/trackingmodel.php');

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
$id_carrier = 10;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials127.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$events = array();
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

$data= "<SeguimientoEnviosRequest
xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
xsi:noNamespaceSchemaLocation='SeguimientoEnviosRequest.xsd'>
<Solicitante>xx</Solicitante> <Dato>".$tracking_number."</Dato> </SeguimientoEnviosRequest>";
$uri = 'https://www.correosexpress.com/wpsc/apiRestSeguimientoEnvios/rest/seguimientoEnvios';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "authorization: Basic ".base64_encode($username.':'.$password),// YWdvcmExOnFseFpB",
    "cache-control: no-cache",
    "content-type: application/xml"
  ));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


$result = curl_exec($ch);

$aa = strpos($result, "close")+9;
$string = Tools::substr($result, $aa);
libxml_use_internal_errors(true);
$aa = mb_convert_encoding($string, 'utf-8', 'ISO-8859-15');
$url_page = simplexml_load_string($aa, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
$id_status = 0;
$file_webcarrier = dirname(__FILE__).'/steps127.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
if (!$url_page) {
    $server = 0;
    preg_match('/Error ([0-9]*)/', $aa, $content);
    $status = isset($content[1])?$content[1]:100;
    preg_match('/<h2>(.*)<\/h2>/', $aa, $content);
    $label_status = isset($content[1])?$content[1]:'';
    $date = Date('Y-m-d H:i:s');
} elseif ((string)$url_page->Error) {
    $server = 0;
    $status = (string)$url_page->Error;
    $label_status = (string)$url_page->MensajeError;
    $date = Date('Y-m-d H:i:s');
} else {
    $server = 1;
    $trace = $url_page->xpath('//EstadoEnvios');
    $events = array_map(function ($e) use ($webxml_crr) {
        $myDateTime = DateTime::createFromFormat(
            'dmY His',
            (string)$e->FechaEstado.' '.(string)$e->HoraEstado
        );
        $date = $myDateTime->format('Y-m-d H:i:s');
        $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e->CodEstado);
        return array(
            'event_code' => (string)$e->CodEstado,
            'event_description' => (string)$e->DescEstado,
            'event_date' => $date,
            'id_status' => $id_status
        );
    }, $trace);
    $label_status = (string)$url_page->DescEstado;
    $status = (string)$url_page->CodEstado;
    $myDateTime = DateTime::createFromFormat(
        'dmY His',
        (string)$url_page->FechaEstado.' '.(string)$url_page->HoraEstado
    );
    $date = $myDateTime->format('Y-m-d H:i:s');
}

$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);

$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($server == 0 ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;

//print_R($result_carrier);exit;
echo json_encode($result_carrier);
