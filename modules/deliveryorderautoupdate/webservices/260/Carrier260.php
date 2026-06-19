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
//print_r ($result);
$id_status = 0;
$result = json_decode($result, true);
if ($result['result']) {
    $file_webcarrier = dirname(__FILE__).'/steps260.xml';
    $webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
    $events = array_map(function ($e) use ($webxml_crr) {
        return array(
            'event_code' => (string)$e['businessLinkCode'],
            'event_description' => (string)$e['trackingContent'],
            'event_date' => $e['occurDatetime'],
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e['businessLinkCode'])
        );
    }, array_reverse($result['data']['trackingList']));
    $id_status = TrackingModel::getIdStatusByCarrierCode(
        $webxml_crr,
        $result['data']['trackingList'][0]['businessLinkCode']
    );
} else {
    /*
    $result = 0;
    $eventDate = null;
    // do nothing
    $result_status = 'no found case';
    $document = Tools::file_get_contents(dirname(__FILE__)."/labelcode_260.txt");
    $lines = explode("\n", $document);
    foreach ($lines as $newline) {
        if (strpos($newline, $result['data']['trackingList'][0]['businessLinkCode']) !== false) {
            $label = explode("_", $newline);
            $label_status = $label[1];
            $status_text = $label[2];
        }
    }
    $status_date = Date('Y-m-d H:i:s');
    */
}
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($result['result'] == 1 ? 'true' : 'false');
$result_carrier['result']['carrier_server_status_code'] = $result['result'] == 1 ?
null : $result['data']['trackingList'][0]['businessLinkCode'];
$result_carrier['result']['carrier_server_status_text'] = $result['result'] == 1 ?
null : $result['data']['trackingList'][0]['trackingContent'];
$result_carrier['result']['carrier_shipping_status_code'] = $result['result'] == 1 ?
$result['data']['trackingList'][0]['businessLinkCode'] : null;
$result_carrier['result']['carrier_shipping_status_text'] = $result['result'] == 1 ?
$result['data']['trackingList'][0]['trackingContent'] : null;
$result_carrier['result']['carrier_shipping_status_date'] = $result['data']['trackingList'][0]['occurDatetime'];

$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;

echo json_encode($result_carrier);
