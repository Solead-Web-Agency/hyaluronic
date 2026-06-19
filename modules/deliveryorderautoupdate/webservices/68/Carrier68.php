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
$tracking = Tools::getValue('parcel_number');
$tracking_number = Tools::substr($tracking, -8, 8);
$conf_token = Configuration::get('DELIVERY_TOKEN');
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 10;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials10.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$enseigne = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER10_id1" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$privatekey = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER10_id2" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$enseigne = $enseigne['value'];
$privatekey = $privatekey['value'];
$security = $enseigne.$tracking_number.'FR'.$privatekey;
$securitymd5 = Tools::strtoupper(md5($security));
$server = 1;

$data= '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
xmlns:web="http://www.mondialrelay.fr/webservice/">
   <soap:Header/>
   <soap:Body>
      <web:WSI2_TracingColisDetaille>
         <!--Optional:-->
         <web:Enseigne>'.$enseigne.'</web:Enseigne>
         <!--Optional:-->
         <web:Expedition>'.$tracking_number.'</web:Expedition>
         <!--Optional:-->
         <web:Langue>FR</web:Langue>
         <!--Optional:-->
         <web:Security>'.$securitymd5.'</web:Security>
      </web:WSI2_TracingColisDetaille>
   </soap:Body>
</soap:Envelope>';
$uri = 'http://api.mondialrelay.com/Web_Services.asmx';

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => $uri,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: text/xml",
        "postman-token: e6bc2e27-8abd-6869-b1e3-1a41bff24787"
    ),
));

$result = curl_exec($curl);
$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
$xml = simplexml_load_string($response);
$array = json_decode(json_encode((array)$xml), true);
$rsl = $array['soapBody']['WSI2_TracingColisDetailleResponse']['WSI2_TracingColisDetailleResult']['STAT'];
$label_status = '';
$file_webcarrier = dirname(__FILE__).'/steps10.xml';
$webxml_crr = json_decode(
    json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
);
if (strpos(',1,24,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,', ','.$rsl.',') !== false) {
    $status = $array['soapBody']['WSI2_TracingColisDetailleResponse']['WSI2_TracingColisDetailleResult']['STAT'];
    $date = date("Y-m-d H:i:s");
    $label_status = 'wrong number';
    $file_label = dirname(__FILE__).'/labelcode_Mondial.txt';
    $labels = explode(PHP_EOL, Tools::file_get_contents($file_label));
    foreach ($labels as $label) {
        if (strpos($label, "{$status}_") !== false) {
            $label_status = str_replace("{$status}_", '', $label);
            break;
        }
    }
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
    $events = array(
        array(
            'event_code' => $status,
            'event_description' => $label_status,
            'event_date' => $date,
            'id_status' => $id_status,
        )
    );
} else {
    $result = $array['soapBody']['WSI2_TracingColisDetailleResponse'];
    $result = $result['WSI2_TracingColisDetailleResult']['Tracing']['ret_WSI2_sub_TracingColisDetaille'];
    $events = array();
    if (is_array($result)) {
        foreach ($result as $r) {
            if ($r['Libelle']) {
                $status = $r['Libelle'];
                $date = $r['Date'].' '.$r['Heure'];
                $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
                $date = date("d-m-y H:i:s", strtotime(str_replace("/", "-", $date)));
                $events[] = array(
                    'event_code' => $status,
                    'event_description' => $status,
                    'event_date' => $date,
                    'id_status' => $id_status,
                );
            }
        }
    } else {
        if ($result['Libelle']) {
            $status = $result['Libelle'];
            $date = $result['Date'].' '.$result['Heure'];
            $date = date("d-m-y H:i:s", strtotime(str_replace("/", "-", $date)));
            $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
            $events[] = array(
                'event_code' => $status,
                'event_description' => $status,
                'event_date' => $date,
                'id_status' => $id_status,
            );
        }
    }
}

$server = (strpos(',1,24,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,', ','.$status.',') !== false ? 0 : 1);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($server == 0 ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = ($server == 0 ? $label_status : null);
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['carrier_shipping_status_text'] = ($server ? $label_status : null);
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['carrier_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;

//print_R($result_carrier);exit;
echo json_encode($result_carrier);
