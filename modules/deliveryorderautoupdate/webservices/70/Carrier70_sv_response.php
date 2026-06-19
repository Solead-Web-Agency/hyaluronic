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

$id_order = (int)Tools::getValue('shipment_ref');
$id_carrier = 124;
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
if ($conf_token != $token) {
    die("wrong token");
}
$result_carrier = array();
$context = Context::getContext();
$id_shop = Tools::getValue('id_shop');
$file_webcarrier = dirname(__FILE__).'/credentials70.xml';
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


$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:req="http://www.post.ch/npp/trackandtracews/v02/shipmentssearch/req">
<soapenv:Header/>
<soapenv:Body>
<req:ShipmentsSearch>
<language>de</language>
<ShipmentNumbers>
<ShipmentNumber>996012261600003936</ShipmentNumber>
</ShipmentNumbers>
<Identity>?</Identity>
<Version>2.4</Version>
</req:ShipmentsSearch>
</soapenv:Body>
</soapenv:Envelope>';

$wsdl_url = 'https://webservices.post.ch:17005/IN_MYPBxTT/services/TrackAndTraceDFU.ws?WSDL';
$action_url = 'https://webservices.post.ch:443/IN_MYPBxTT/services/TrackAndTraceDFUv25.ws';

$client = new SoapClient(null, array(
    'location' => $wsdl_url,
    'uri'      => '',
    'trace'    => 1,
    'Username'    => "TU_4853_0001",
    'Password' => "Zu5YnjUT"
));

try {
    $order_return = $client->__doRequest($xml, $wsdl_url, $action_url, 1);
//Get response from here
    print_r($order_return);
} catch (SoapFault $exception) {
    var_dump(get_class($exception));
    var_dump($exception);
}
