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
$id_carrier = 122;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$subscrid = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER122_id1" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER122_id2" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER122_id3" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$subscrid = $subscrid['value'];
$username = $username['value'];
$password = $password['value'];

$data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:est="http://www.estafeta.com/">
<soapenv:Header/>
<soapenv:Body>
<est:ExecuteQuery>
<!--Optional:-->
<est:suscriberId>'.$subscrid.'</est:suscriberId>
<!--Optional:-->
<est:login>'.$username.'</est:login>
<!--Optional:-->
<est:password>'.$password.'</est:password>
<!--Optional:-->
<est:searchType>
<!--Optional:-->
<est:waybillList>
<!--Optional:-->
<est:waybillType>G</est:waybillType>
<!--Optional:-->
<est:waybills>
<!--Zero or more repetitions:-->
<est:string>'.$tracking_number.'</est:string>
</est:waybills>
</est:waybillList>
<!--Optional:-->
<est:type>L</est:type>
</est:searchType>
<!--Optional:-->
<est:searchConfiguration>
<est:historyConfiguration>
<est:includeHistory>1</est:includeHistory>
<!--Optional:-->
<est:historyType>LAST_EVENT</est:historyType>
</est:historyConfiguration>
<!--Optional:-->
<est:filterType>
<est:filterInformation>0</est:filterInformation>
<!--Optional:-->
<est:filterType></est:filterType>
</est:filterType>
</est:searchConfiguration>
</est:ExecuteQuery>
</soapenv:Body>
</soapenv:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://tracking.estafeta.com/Service.asmx",
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
        "Content-Type: text/xml"
    ),
));

$result = curl_exec($curl);

curl_close($curl);
$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
$xml = simplexml_load_string($response);
$array = json_decode(json_encode((array)$xml), true);
$error = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult']['errorCode'];
$events = array();
$file_webcarrier = dirname(__FILE__).'/steps122.xml';
$webxml_crr = json_decode(
    json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
);
if (isset($error) && $error) {
    $status = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult']['errorCode'];
    $date = Date('Y-m-d H:i:s');
    $desc = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult']['errorCodeDescriptionENG'];
    if (is_array($desc)) {
        $desc = implode(';', $desc);
    } else {
        $desc = (string)$desc;
    }
    $server = 0;
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
} else {
    $server = 1;
 /*   $status = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult']
    ['trackingData']['TrackingData']['statusENG'];*/
    $queryresult = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult'];
    $history = $queryresult['trackingData']['TrackingData']['history'];
    if (isset($history['History'])) {
        $trace = array_values($history);
        $events = array_map(function ($e) use ($webxml_crr) {
            $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $e['eventId']);
            $event = array(
                'event_code' => $e['eventId'],
                'event_description' => $e['eventDescriptionENG'],
                'event_date' => date(
                    "Y-m-d H:i:s",
                    strtotime(str_replace("/", "-", $e['eventDateTime']))
                ),
                'id_status' => $id_status
            );
            return $event;
        }, array_reverse($trace));
        if (count($events)) {
            $event = $events[count($events)-1];
            $date = $event['event_date'];
            $desc = $event['event_description'];
            $status = $event['event_code'];
            $id_status = $event['id_status'];
        }
    } else {
        $date = $queryresult['trackingData']['TrackingData']['deliveryData']['deliveryDateTime'];
        $date = date(
            "Y-m-d H:i:s",
            strtotime(str_replace("/", "-", $date))
        );
        $desc = $queryresult['trackingData']['TrackingData']['statusENG'];
        $status = $queryresult['trackingData']['TrackingData']['statusENG'];
        $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
        $events[] = array(
            'event_code' => $status,
            'event_description' => $desc,
            'event_date' => $date,
            'id_status' => $id_status
        );
    }
}

$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($server == 0 ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = null;
$result_carrier['result']['carrier_server_status_text'] = null;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['carrier_shipping_status_text'] = $desc;
$result_carrier['result']['carrier_shipping_status_date'] = date(
    "Y-m-d H:i:s",
    strtotime(str_replace("/", "-", $date))
);
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['all_events'] = $events;
echo json_encode($result_carrier);
