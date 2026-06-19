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
$result_carrier = array();
$id_order = Tools::getValue('shipment_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$lang = Db::getInstance()->getValue(
    'SELECT CASE WHEN l.iso_code IN("es") THEN "1" ELSE "2" END
    FROM '._DB_PREFIX_.'order_carrier oc
	LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
	LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
    WHERE oc.id_order_carrier='.(int)$id_order.''
);

$xml = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
 <soap:Body>
 <ConsultaLocalizacionEnviosFases xmlns="ServiciosWebLocalizacionMI/">
 <XMLin><![CDATA[<?xml version="1.0" encoding="utf-8" ?>
            <ConsultaXMLin Idioma="'.$lang.'"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <Consulta><Codigo>'.$tracking_number.'</Codigo></Consulta></ConsultaXMLin>]]></XMLin>
 </ConsultaLocalizacionEnviosFases>
 </soap:Body>
</soap:Envelope>
';
$wsdl_url = 'https://online.correos.es/servicioswebLocalizacionMI/localizacionMI.asmx?wsdl';
$action_url = 'https://online.correos.es/servicioswebLocalizacionMI/localizacionMI.asmx';

$client = new SoapClient(null, array(
    'location' => $wsdl_url,
    'uri'      => '',
    'trace'    => 1,
));
$file_webcarrier = dirname(__FILE__).'/steps125.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$result_ = 0;
try {
    $content = array();
    $order_return = $client->__doRequest($xml, $wsdl_url, $action_url, 1);
    $order_return = str_ireplace(['soap:', 'xmlns:'], '', $order_return);
    $xml = @simplexml_load_string($order_return);
    if (isset($xml->Body)) {
        $content = $xml->Body->ConsultaLocalizacionEnviosFasesResponse->ConsultaLocalizacionEnviosFasesResult;
    } else {
        throw new Exception("Error Processing Request");
    }
    $xml = simplexml_load_string($content);
    $arr = json_decode(json_encode($xml), true);
    $events = array();
    if (!is_array($arr)) {
        $success = false;
        $status = 0;
        $status_event = '';
        $date = date('Y-m-d H:i:s');
    } else {
        $success = true;
        $array = $arr['Respuestas']['DatosIdiomas']['DatosEnvios']['Datos'];
        $events = array_map(function ($e) use ($webxml_crr) {
            $date = DateTime::createFromFormat('d/m/Y H:i', $e['Fecha'].' 00:00');
            return array(
                'event_code' => (string)$e["Estado"],
                'event_description' => (string)$e["Estado"],
                'event_date' => $date->format('Y-m-d H:i:s'),
                'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, (string)$e["Estado"])
            );
        }, $array);
        if (count($events)) {
            $event = $events[count($events)-1];
            $date = $event['event_date'];
            $status_event = $event['event_description'];
            $status = $event['event_code'];
        } else {
            $status = 0;
            $status_event = '';
            $date = date('Y-m-d H:i:s');
        }
    }
} catch (SoapFault $exception) {
    $success = false;
    $status = 0;
    $status_event = '';
    $date = date('Y-m-d H:i:s');
}
$result_ = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);

$result_carrier = array();
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] = $status;
$result_carrier['result']['carrier_server_status_text'] = $status_event;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $status_event;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $result_;
$result_carrier['result']['all_events'] = $events;

echo json_encode($result_carrier);
