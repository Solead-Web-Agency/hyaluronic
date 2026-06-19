<?php
/**
* 2007-2021 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2021 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

$DOCUMENT_ROOT = explode('modules', dirname(__FILE__));
require_once($DOCUMENT_ROOT[0].'config/config.inc.php');
require_once($DOCUMENT_ROOT[0].'init.php');

$context = Context::getContext();
$parcel_number = Tools::getValue('parcel_number');
$reference = Tools::getValue('order_ref');
$id_shop = Tools::getValue('id_shop');

$file_webcarrier = dirname(__FILE__).'/credentials12.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$agency = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[0]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$account = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[1]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);

$result_carrier = array();
$variables=array(
            'customer_center'=>'3',
            'customer'=>'1064',
            'password'=>'Pr2%5sHg',
            'reference' => $reference,
            'shipping_date'=>'',
            'shipmentnumber'=> $parcel_number,
            'shipping_customer_center'=>$agency['value'],
            'shipping_customer'=>$account['value'],
            'searchmode'=>'SearchMode_Equals',
            'language'=>'F'
        );

$serviceurl='http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx?WSDL';
try {
    $client=new SoapClient(
        $serviceurl,
        array(
            'connection_timeout'=>5,
            'cache_wsdl'=>WSDL_CACHE_NONE,
            'exceptions'=>true
        )
    );
} catch (Exception$e) {
    echo '<div class="warnmsg">Error : '.$e->getMessage().'</div>';
    exit;
}
// Call WS for traces by Ref
$response=$client->getShipmentTraceByReferenceGlobalWithCenterAsArray($variables);
$result=$response->getShipmentTraceByReferenceGlobalWithCenterAsArrayResult->clsShipmentTrace;

print_r($result);
