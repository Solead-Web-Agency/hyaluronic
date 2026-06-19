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
$id_order = Tools::getValue('shipment_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$date_shipment = Db::getInstance()->getValue(
    'SELECT oc.date_add FROM '._DB_PREFIX_.'order_carrier oc
    WHERE oc.id_order_carrier LIKE '.$id_order
);
$date_from = date("d-m-Y", strtotime($date_shipment. '- 3 days'));
$date_to = date("d-m-Y", strtotime($date_from. '+ 15 days'));
$id_carrier = 152;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials152.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$user = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[0]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[1]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$user = $user['value'];
$password = $password['value'];

$data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:con="http://consultaExpediciones.servicios.webseur">
   <soapenv:Header/>
   <soapenv:Body>
      <con:consultaExpedicionesStr>
         <con:in0>S</con:in0>
         <con:in1></con:in1>
         <con:in2></con:in2>
         <con:in3></con:in3>
         <con:in4></con:in4>
         <con:in5>'.$date_from.'</con:in5>
         <con:in6>'.$date_to.'</con:in6>
         <con:in7></con:in7>
         <con:in8></con:in8>
         <con:in9></con:in9>
         <con:in10>'.$tracking_number.'</con:in10>
         <con:in11>0</con:in11>
         <con:in12>'.$user.'</con:in12>
         <con:in13>'.$password.'</con:in13>
         <con:in14>N</con:in14>
      </con:consultaExpedicionesStr>
   </soapenv:Body>
</soapenv:Envelope>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://ws.seur.com/webseur/services/WSConsultaExpediciones",
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
    "Cookie: visid_incap_1370163=6zmE/QpAS6OoEP
	//ST4Y3krXnl4AAAAAQUIPAAAAAADzg1QF4WVW7rFA9mpkJFVW;
	incap_ses_467_1370163=WURySgVI6yQ2WAsCSCB7Bib0oV4AAAAA7yPDXlDogJhWRGkPrixepA==;
	incap_ses_390_1370163=2ps+DFGgmj6t+4Z1/49pBdTzpl4AAAAAQ1WMSXYB4dIb3qcGI2QJGg=="
  ),
));

$response = curl_exec($curl);

header("Content-type: text/xml");
curl_close($curl);
$response = html_entity_decode($response);
print_r($response);
