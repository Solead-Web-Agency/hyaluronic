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
$id_carrier = 125;
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
$tracking_number = explode(',', $tracking_number);
$tracking_number = end($tracking_number);
if ($conf_token != $token) {
    die("wrong token");
}
$result_carrier = array();
$context = Context::getContext();
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
header('Content-type: text/xml');
try {
    $order_return = $client->__doRequest($xml, $wsdl_url, $action_url, 1);
    preg_match(
        '/<ConsultaLocalizacionEnviosFasesResult>(.*)<\/ConsultaLocalizacionEnviosFasesResult>/',
        $order_return,
        $content
    );
    $content = htmlspecialchars_decode($content[1]);
    //Get response from here
    print_r($content);
} catch (SoapFault $exception) {
    var_dump(get_class($exception));
    var_dump($exception);
}
