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

var_dump($response);
curl_close($curl);
