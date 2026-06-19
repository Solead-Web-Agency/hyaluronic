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
$id_order = (int)Tools::getValue('shipment_ref');
$id_carrier = 56;
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_shop = Tools::getValue('id_shop');
$result_carrier = array();
$access_key = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER56_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);

$access_key = $access_key['value'];

$data= '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
<env:Header/>
<env:Body>
<ns3:getPublicServiceShipmentDetails
xmlns:ns2="http://www.schenker.com/SGI/v4_0"
xmlns:ns3="http://www.schenker.com/CustomerServices/eBusiness/ShipmentService/v2">
<AccessKey>'.$access_key.'</AccessKey>
<in>
<ns2:ApplicationArea>
<ns2:requestId>NGES-TRACKING-144255386</ns2:requestId>
<ns2:CreationDateTime>2020-04-20T11:42:33.867+01:00</ns2:CreationDateTime>
</ns2:ApplicationArea>
<ns2:referenceType>ff</ns2:referenceType>
<ns2:referenceNumber>'.$tracking_number.'</ns2:referenceNumber>
</in>
</ns3:getPublicServiceShipmentDetails>
</env:Body>
</env:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://eschenker.dbschenker.com/webservice/trackingWebServiceV2",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>$data,
    CURLOPT_HTTPHEADER => array(
        "Content-Type: application/xml",
        "Cookie: INGRESSCOOKIE=1587483340.164.1737.82550"
    ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = str_ireplace(['ns3:', 'ns2:', 'SOAP:'], '', $response);
$xml = simplexml_load_string($response);
//print_r($xml);exit;
$file_webcarrier = dirname(__FILE__).'/steps56.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));

$id_status = 100;
if (isset($xml->Body->Fault)) {
    $status_error = true;
    $status = (string)$xml->Body->Fault->faultstring;
    $status = str_replace($tracking_number, '', $status);
    $date = Date('Y-m-d H:i:s');
    $result_status = (string)$xml->Body->Fault->faultstring;
} else {
    $status_error = false;
    $traceResult = $xml->Body->getPublicShipmentDetailsResponse->out->Shipment->ShipmentBasicInfo;
    $trace = $traceResult->xpath('//StatusEvent');
    $trace = end($trace);
    $status = $trace->Status;
    $date = $trace->Date.' '.$trace->Time;
    $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
    $result_status = (string)$trace->StatusDescription;
}
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
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] =  null;
$result_carrier['result']['carrier_server_status_text'] = null;
$result_carrier['result']['carrier_shipping_status_code'] = $id_status;
$result_carrier['result']['carrier_shipping_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;

$result_carrier['result']['module_shipping_status_code'] = $id_status;
print_r(json_encode($result_carrier));
