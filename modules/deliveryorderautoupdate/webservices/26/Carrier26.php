<?php
/**
* 2007-2021 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2021 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

$DOCUMENT_ROOT = explode('modules', dirname(__FILE__));
require_once($DOCUMENT_ROOT[0].'config/config.inc.php');
require_once($DOCUMENT_ROOT[0].'init.php');
require_once('../../classes/trackingmodel.php');

$context = Context::getContext();
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$reference = Tools::getValue('order_ref');
$id_order = Tools::getValue('shipment_ref');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://tracking.dpd.de/rest/plc/en_NL/'.$tracking_number.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);
curl_close($curl);
$response = Tools::jsonDecode($response, true);

$id_status = 0;
$result_carrier = array();
$file_webcarrier = dirname(__FILE__).'/steps26.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$events = array();
if ($response) {
    $rs = $response['parcellifecycleResponse']['parcelLifeCycleData']['statusInfo'];
    $events = array_map(function ($e) use ($webxml_crr) {
        if (isset($e['date'])) {
            $datenode = (string)$e['date'];
            $date = DateTime::createFromFormat('d.m.Y, H:i', $datenode);
            if ($date != false) {
                $date = $date->format('Y-m-d H:i:s');
            } else {
                $date = date("Y-m-d H:i:s");
            }
        } else {
            $date = date("Y-m-d H:i:s");
        }
        return array(
            'event_code' => (string)$e['status'],
            'event_description' => count($e['description']['content'])?$e['description']['content'][0]:$e['label'],
            'event_date' => $date,
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e['status'])
        );
    }, $rs);
    $rs = array_filter($rs, function ($i) {
        return $i['statusHasBeenReached'];
    });
    $rs = $rs[count($rs)-1];
    $success = true;
    if (isset($rs['date'])) {
        $datenode = (string)$rs['date'];
        $date = DateTime::createFromFormat('d.m.Y, H:i', $datenode);
        $date = $date->format('Y-m-d H:i:s');
    } else {
        $date = date("Y-m-d H:i:s");
    }
    $status = (string)$rs['status'];
    $result_status = count($rs['description']['content'])?$rs['description']['content'][0]:$rs['label'];
} else {
    $success = false;
    $status = '404';
    $date = date("Y-m-d H:i:s");
    $result_status = 'Not found';
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
