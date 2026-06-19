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
$id_carrier = 124;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials124.xml';
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

$data = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"
xmlns:req=\"http://www.post.ch/npp/trackandtracews/v02/shipmentssearch/req\">\r\n
<soapenv:Header/>\r\n   <soapenv:Body>\r\n
<req:ShipmentsSearch>\r\n
<language>en</language>\r\n         <ShipmentNumbers>\r\n
<ShipmentNumber>".$tracking_number."</ShipmentNumber>\r\n         </ShipmentNumbers>\r\n
<Identity>?</Identity>\r\n         <!--Optional:-->\r\n
<Version>2.5</Version>\r\n      </req:ShipmentsSearch>\r\n
</soapenv:Body>\r\n</soapenv:Envelope>";
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://webservices.post.ch:443/IN_MYPBxTT/services/TrackAndTraceDFUv25.ws",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Accept-Encoding: gzip,deflate",
    "Content-Type: text/xml",
//    "Content-Length: 541",
    "Host: webservices.post.ch:443",
    "Connection: Keep-Alive",
    "User-Agent: Apache-HttpClient/4.1.1 (java 1.5)",
    "Authorization: Basic ". base64_encode($username.':'.$password),
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
$file_webcarrier = dirname(__FILE__).'/steps124.xml';
$webxml_crr = json_decode(
    json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
);
$xml = simplexml_load_string($response);
$array = json_decode(json_encode((array)$xml), true);
if (isset($array['soapBody']['ns9ShipmentsSearchDFURes'])) {
    $events = $array['soapBody']['ns9ShipmentsSearchDFURes']['Envelope'];
    if (isset($events[1])) {
        $events = end($events);
    }
    $trace = $events['Data']['Provider']['Sending']['Item']['Event'];
    $events = array_map(function ($e) use ($webxml_crr) {
        $date = isset($e['Timestamp'])?$e['Timestamp']:Date('Y-m-d H:i:s');
        $date = date(
            "Y-m-d H:i:s",
            strtotime(str_replace("/", "-", $date))
        );
        $status = isset($e['EventNumber'])?$e['EventNumber']:0;
        return array(
            'event_code' => $status,
            'event_description' => isset($e['Description'])?$e['Description']:'',
            'event_date' => $date,
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status)
        );
    }, $trace);
    $event = array_pop($trace);
    $status = isset($event['EventNumber'])?$event['EventNumber']:0;
    $date = isset($event['Timestamp'])?$event['Timestamp']:Date('Y-m-d H:i:s');
    $desc = isset($event['Description'])?$event['Description']:'';
    $server = 1;
} else {
    $server = 0;
    $date = Date('Y-m-d H:i:s');
    if (isset($array['soapBody']['soapFault'])) {
        $status = $array['soapBody']['soapFault']['detail']['ns16TrackAndTraceLog']['Entry']['Code'];
        $desc = $array['soapBody']['soapFault']['detail']['ns16TrackAndTraceLog']['Entry']['Desc'];
    } else {
        $status = $array['envBody']['envFault']['faultstring'];
        $desc = '';
    }
}
$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($server == 0 ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = ($server == 0 ? $status : null);
$result_carrier['result']['carrier_server_status_text'] = ($server == 0 ? $desc : null);
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = ($server ? $desc : null);
$result_carrier['result']['carrier_shipping_status_date'] = date(
    "Y-m-d H:i:s",
    strtotime(str_replace("/", "-", $date))
);
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;

//print_R($result_carrier);exit;
echo json_encode($result_carrier);
