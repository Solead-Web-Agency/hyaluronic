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
$id_shop = Tools::getValue('id_shop');
$id_carrier = 250;
$status_error = null;
$glsstatus = null;
$result_carrier = array();
$soapClient = new SoapClient(dirname(__FILE__).'/shipments-tracking-api-wsdl.wsdl');

$file_webcarrier = dirname(__FILE__).'/credentials29.xml';
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
$version = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[2]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$accountnumber = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[3]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$accountpin = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[4]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$accountentity = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[5]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$accountcountrycode = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_crr->credential[6]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);

$params = array(
    'ClientInfo' => array(
        'AccountCountryCode'    => $accountcountrycode,
        'AccountEntity'         => $accountentity,
        'AccountNumber'         => $accountnumber,
        'AccountPin'            => $accountpin,
        'UserName'              => $username,
        'Password'              => $password,
        'Version'               => $version
    ),

    'Transaction' => array(
                'Reference1'    => $tracking_number
                            ),
    'Shipments' => array(
                    'XXXXXXXXXX'
                )
);
try {
    $auth_call = $soapClient->TrackShipments($params);
    $result_carrier['result']['carrier_shipping_status_code'] = $auth_call->Notifications->Notification->Code;
    $result_carrier['result']['carrier_shipping_status_text'] = $auth_call->Notifications->Notification->Message;
} catch (SoapFault $fault) {
//.print_R($fault->faultstring);exit;
    $result_carrier['result']['status'] = null;
    $result_carrier['result']['carrier_server_status_code'] = $fault->faultstring;
    //die('Error : ' . $fault->faultstring);
}


$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($status_error ? 'false' : 'true');
$result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');

//$result_carrier['shipping_status_text'] = $result_status;
// $result_carrier['result']['module_shipping_status_code'] = $id_status;
print_r(json_encode($result_carrier));
