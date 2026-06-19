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
$severDescription = null;
$severCode = null;
$StatusDescription = null;
if (!empty($result->LastError)) {
    //echo 'Order jklgjdkflgjk - Error : '.$result->LastError.'<br/>';
    $severDescription = $result->LastError;
    $severCode = preg_replace("/[^0-9]/", "", $severDescription);
    $id_status = 100;
} else {
    if (!is_array($result)) {
        $traces=$result->Traces->clsTrace;
        $statuslist = $traces[0]->StatusNumber;
    } else {
        foreach ($result as $shipment) {
            $variables2=array(  'customer_center'=>'3',
                                'customer'=>'1064',
                                'password'=>'Pr2%5sHg',
                                'shipmentnumber'=>$shipment->ShipmentNumber
                            );
            $response2=$client->getShipmentTrace($variables2);
            $traces=$response2->getShipmentTraceResult->Traces->clsTrace;

            $statuslist = $traces->StatusNumber;
        }
    }
    $StatusDescription = $result->Traces->clsTrace[0]->StatusDescription;
}
$file_webcarrier = dirname(__FILE__).'/steps12.xml';
$webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
foreach ($webxml_crr->step as $step) {
    $statuscode = $step->carrierstatus;
    foreach ($statuscode as $gl) {
        if ($statuslist == $gl) {
            // delivered
            $id_status = $step->id_status;
            break;
        }
    }
}
$result_carrier['result']['shipment_ref'] = $parcel_number;
$result_carrier['result']['carrier_server_success'] = in_array(
    $result->Traces->clsTrace[0]->StatusNumber,
    array('40', '400')
) ? 'true' : 'false';
$result_carrier['result']['carrier_server_status_code'] = $severCode;
$result_carrier['result']['carrier_server_status_text'] = $severDescription;
$result_carrier['result']['carrier_shipping_status_code'] = $statuslist;
$result_carrier['result']['carrier_shipping_status_text'] = $StatusDescription;
$result_carrier['result']['carrier_shipping_status_date'] = date(
    "Y-m-d H:i:s",
    strtotime(
        str_replace(
            '.',
            '-',
            $result->Traces->clsTrace[0]->ScanDate.' '.$result->Traces->clsTrace[0]->ScanTime
        )
    )
);

$result_carrier['result']['module_shipping_status_code'] = $id_status;

echo json_encode($result_carrier);
