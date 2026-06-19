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
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$id_order = Tools::getValue('shipment_ref');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 128;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$Franquicia = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER128_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$Cliente = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER128_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$Password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER128_id3" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$Franquicia = $Franquicia['value'];
$Cliente = $Cliente['value'];
$Password = $Password['value'];

$data='<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
 xmlns:seg="http://www.mrw.es/webservices/seguimiento">
   <soap:Header/>
   <soap:Body>
      <seg:SeguimientoNumeroEnvioMRWNacional>
         <!--Optional:-->
         <seg:Franquicia>'.$Franquicia.'</seg:Franquicia>
         <!--Optional:-->
         <seg:Cliente>'.$Cliente.'</seg:Cliente>
         <!--Optional:-->
         <seg:Password>'.$Password.'</seg:Password>
         <!--Optional:-->
         <seg:NumeroMRW>'.$tracking_number.'</seg:NumeroMRW>
         <!--Optional:-->
         <seg:Referencia>PEDIDO-'.$id_order.'</seg:Referencia>
         <seg:Agrupado>0</seg:Agrupado>
      </seg:SeguimientoNumeroEnvioMRWNacional>
   </soap:Body>
</soap:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://seguimiento.mrw.es/swc/wssgmntnvs.asmx?op=SeguimientoNumeroEnvioMRWNacional",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "Content-type: application/soap+xml; charset=utf-8",
    "Accept: text/xml",
    "Host: seguimiento.mrw.es"
  ),
));

$response = curl_exec($curl);
curl_close($curl);


$response = str_ireplace(['soap:', 'xmlns:'], '', $response);
$response = json_decode(json_encode(simplexml_load_string($response)), true);
$id_status = '0';
$file_webcarrier = dirname(__FILE__).'/steps128.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$response = $response['Body']['SeguimientoNumeroEnvioMRWNacionalResponse']['SeguimientoNumeroEnvioMRWNacionalResult'];
$events = array();
if ($response['Estado'] != 'true') {
    $server = 0;
    $label_status = $response['Mensaje'];
    if (strpos($label_status, 'no existe información disponible para el número indicado') !== false) {
        $status = '105';
    } elseif (strpos($label_status, 'Error de acceso al seguimiento de envíos') !== false) {
        $status = '102';
    } else {
        $status = '100';
    }
    $date = date('Y-m-d H:i:s');
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
} else {
    $server = 1;
    $track = $response['Envio'];
    $label_status = $track['EstadoDescripcion'];
    $status = $track['Estado'];
    $myDateTime = DateTime::createFromFormat(
        'dmY Hi',
        $track['FechaEntrega'].' '.$track['HoraEntrega']
    );
    $date = $myDateTime->format('Y-m-d H:i:s');
    $id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
    $events = array(
        'event_code' => $status,
        'event_description' => $label_status,
        'event_date' => $date,
        'id_status' => $id_status
    );
}


$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($server == 0 ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = $id_status;
$result_carrier['result']['carrier_server_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_text'] = $label_status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = $events;

//print_R($result_carrier);exit;
echo json_encode($result_carrier);
