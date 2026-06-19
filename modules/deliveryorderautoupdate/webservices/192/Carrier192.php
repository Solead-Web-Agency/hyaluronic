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

if ($conf_token != $token) {
    die("wrong token");
}
$data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:sled="http://sledzenie.pocztapolska.pl">
<soapenv:Header>
<wsse:Security
soapenv:mustUnderstand="1"
xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
<wsse:UsernameToken wsu:Id="UsernameToken-2"
xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
<wsse:Username>sledzeniepp</wsse:Username>
<wsse:Password
Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">
PPSA</wsse:Password>
<wsse:Nonce
EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
X41PkdzntfgpowZsKegMFg==</wsse:Nonce>
<wsu:Created>2011-12-08T07:59:28.656Z</wsu:Created>
</wsse:UsernameToken>
</wsse:Security>
</soapenv:Header>
<soapenv:Body>
<sled:sprawdzPrzesylke>
<sled:numer>'.$tracking_number.'</sled:numer>
</sled:sprawdzPrzesylke>
</soapenv:Body>
</soapenv:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://tt.poczta-polska.pl/Sledzenie/services/Sledzenie.SledzenieHttpSoap11Endpoint/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $data ,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: text/plain'
  ),
));

$response = curl_exec($curl);
curl_close($curl);

$response = str_ireplace(['soapenv:', 'ns:', 'ax21:', 'xsi:'], '', $response);
$xml = simplexml_load_string($response);
$json = Tools::jsonDecode(Tools::jsonEncode($xml), true);

$file_webcarrier = dirname(__FILE__).'/steps192.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$body = $json['Body']['sprawdzPrzesylkeResponse']['return']['danePrzesylki'];
$success = false;
$events = array();
$id_status = 0;
if (isset($body['zdarzenia'])) {
    $track = $body['zdarzenia']['zdarzenie'];
    $events = array_map(function ($e) use ($webxml_crr) {
        $date = DateTime::createFromFormat('Y-m-d H:i', $e['czas']);
        return array(
            'event_code' => $e['kod'],
            'event_description' => $e['nazwa'],
            'event_date' => $date->format("y-m-d H:i:s"),
            'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e['kod'])
        );
    }, $track);
    if (count($events)) {
        $success = true;
        $event = end($events);
        $status = $event['event_code'];
        $desc = $event['event_description'];
        $date = $event['event_date'];
        $id_status = $event['id_status'];
    }
} else {
    $status = $json['Body']['sprawdzPrzesylkeResponse']['return']['status'];
    $desc = '';
    $date = date('Y-m-d H:i:s');
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
}
$result_carrier = array();
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  null;
$result_carrier['result']['carrier_server_status_text'] = null;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;
print_r(json_encode($result_carrier));
