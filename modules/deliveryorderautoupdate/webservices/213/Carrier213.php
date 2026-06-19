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

$id_carrier = 213;
$result_carrier = array();
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://www.gls-italy.com/?option=com_gls&view=track_e_trace&mode=search&numero_spedizione='.
  $tracking_number.'&tipo_codice=nazionale',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Cookie: joomsef_lang=en; STunn=A+BuYvABOgqObMR00YSiDg$$; ".
    "0c73b3f6168197b4a0547b472c2dfd1f=5k3s6tltc7mnhqmj9urhfbbhpf"
),
));

$response = curl_exec($curl);

curl_close($curl);

$file_webcarrier = dirname(__FILE__)."/steps{$id_carrier}.xml";
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$id_status = '0';
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
libxml_use_internal_errors(true);
$doc->loadHTML($response);
$finder = new DomXPath($doc);
$events = array();
$table = $finder->query("//table[@id='esitoSpedizioneRS']");
if ($table->length) {
    $row = $finder->query("tr", $table->item(0));
    for ($i=1; $i < $row->length; $i++) {
        $data = $finder->query("td", $row->item($i));
        if ($data->length) {
            $status = (string)$data->item(2)->nodeValue;
            $datenote = (string)$data->item(0)->nodeValue;
            $date = DateTime::createFromFormat('d/m/Y H:i', $datenote);
            if (!$date) {
                $date = DateTime::createFromFormat('d/m/Y H:i', $datenote.'00:00');
            }
            if ($date) {
                $date = $date->format('Y-m-d H:i:s');
            } else {
                $date = $datenote;
            }
            $result_status = (string)$data->item(3)->nodeValue;
            $events[] = array(
                'event_code' => $status,
                'event_description' => $status,
                'event_date' => $date,
                'id_status' => TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status)
            );
        }
    }
    $data = $finder->query("td", $row->item(1));
    if ($data->length) {
        $success = true;
        $status = (string)$data->item(2)->nodeValue;
        $date = (string)$data->item(0)->nodeValue;
        $date = DateTime::createFromFormat('d/m/Y H:i', $date);
        $date = $date->format('Y-m-d H:i:s');
        $result_status = (string)$data->item(3)->nodeValue;
    }
} else {
    $success = false;
    $status = '105';
    $date = date("Y-m-d H:i:s");
    $err = $finder->query("//div[@class='errorTxt']");
    $result_status = $err->length?$err->item(0)->nodeValue:'';
}

$id_status = TrackingModel::getIdStatusByCarrierCode($webxml_crr, $status);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = $success;
$result_carrier['result']['carrier_server_status_code'] =  $status;
$result_carrier['result']['carrier_server_status_text'] = $result_status;
$result_carrier['result']['carrier_shipping_status_code'] = $status;
$result_carrier['result']['carrier_shipping_status_text'] = $status;
$result_carrier['result']['carrier_shipping_status_date'] = $date;
$result_carrier['result']['module_shipping_status_code'] = $id_status;
$result_carrier['result']['all_events'] = array_reverse($events);
print_r(json_encode($result_carrier));
