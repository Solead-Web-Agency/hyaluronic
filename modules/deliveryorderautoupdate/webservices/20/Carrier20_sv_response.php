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
$id_carrier = 20;
$result_carrier = array();
$id_shop = Tools::getValue('id_shop');
// $file_webcarrier = dirname(__FILE__).'/credentials19.xml';
// $webxml_crr = json_decode(json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA)));
$username = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER20_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER20_id2" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$AccessLicenseNumber = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER20_id3" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$username = $username['value'];
$password = $password['value'];
$AccessLicenseNumber = $AccessLicenseNumber['value'];

$data= '<?xml version="1.0"?>
<AccessRequest xml:lang="en-US">
	 <AccessLicenseNumber>'.$AccessLicenseNumber.'</AccessLicenseNumber>
	 <UserId>'.$username.'</UserId>
	 <Password>'.$password.'</Password>
	 </AccessRequest>
	 <?xml version="1.0"?>
 <TrackRequest xml:lang="en-US">
	 	 <Request>
	 	 	 <TransactionReference>
	 	 	 		 <CustomerContext>Your Test Case Summary Description</CustomerContext>
	 	 	 	 </TransactionReference>
	 	 	 	 <RequestAction>Track</RequestAction>
	 	 	 	 <RequestOption>activity</RequestOption>
  	 	 </Request>
	<TrackingNumber>'.$tracking_number.'</TrackingNumber>
</TrackRequest>';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://onlinetools.ups.com/ups.app/xml/Track",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/xml"
  ),
));

$response = curl_exec($curl);
header("Content-Type: text/xml");
curl_close($curl);
echo $response;
