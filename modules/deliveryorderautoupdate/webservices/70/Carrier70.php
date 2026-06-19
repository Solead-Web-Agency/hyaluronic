<?php
/**
* 2007-2021 PrestaShop
*
* Tracking Center
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2021 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: https://helloshop.com
*/

$DOCUMENT_ROOT = explode('modules', dirname(__FILE__));
require_once($DOCUMENT_ROOT[0].'config/config.inc.php');
require_once($DOCUMENT_ROOT[0].'init.php');
$context = Context::getContext();
$result_carrier = array();
$id_order = Tools::getValue('shipment_ref');
$token = Tools::getValue('token');
$tracking_number = Tools::getValue('parcel_number');
$conf_token = Configuration::get('DELIVERY_TOKEN');
if ($conf_token != $token) {
    die("wrong token");
}
$id_carrier = 70;
$id_shop = Tools::getValue('id_shop');


$file_credentials = dirname(__FILE__).'/credentials10.xml';
$webxml_credentials = json_decode(
    json_encode(simplexml_load_file($file_credentials, 'SimpleXMLElement', LIBXML_NOCDATA))
);

$user = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_credentials->credential[0]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);
$password = Db::getInstance()->getRow(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "'.$webxml_credentials->credential[1]->credname.'" '.($id_shop ? 'AND id_shop='.$id_shop : '')
);

$user = $user['value'];
$password = $password['value'];
$client = new SoapClient("http://api.mondialrelay.com/Web_Services.asmx?WSDL", true);
$client->soap_defencoding = 'utf-8';

$label_status = null;
$lang = Db::getInstance()->getRow("SELECT iso_code FROM "._DB_PREFIX_."lang
                    WHERE id_lang=".(int)$context->language->id);

//security = UPPERCASE MD5( user + password + Language + Private Key )
$code = $user.$tracking_number.Tools::strtoupper($lang['iso_code']).$password;
$params = array(
    'Enseigne' => $user,
    'Expedition' => $tracking_number,
    'Langue' => Tools::strtoupper($lang['iso_code']),
    'Security' => Tools::strtoupper(md5($code))
);

$result = $client->call(
    'WSI2_TracingColisDetaille',
    $params,
    'http://api.mondialrelay.com/',
    'http://api.mondialrelay.com/WSI2_TracingColisDetaille'
);

if ($client->fault) {
    return false;
} else {
    $err = $client->getError();
    if (!$err) {
        $status = $result['WSI2_TracingColisDetailleResult']['STAT'];

        if ((strpos(',80,81,82,83,', ','.$status.',') !== false) || ($status == '')) {
            $file_webcarrier = dirname(__FILE__).'/steps10.xml';
            $webxml_crr = json_decode(
                json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
            );
            foreach ($webxml_crr->step as $step) {
                $id_status = $step->id_status;
                $statuscode = $step->statuscode;
                $statustext = $step->statustext;
                $txt_str = 0;
                if ($status == $step->statuscode) {
                    // delivered
                    $result_status = $step->statustext;
                    break;
                }
            }
            $carrier_server_success = 'true';
        } else {
            // do nothing
            $id_status = 0;

            $carrier_server_success = 'false';
            $result_status = '';
            $document = Tools::file_get_contents(dirname(__FILE__)."/labelcode_Mondial.txt");
            $lines = explode("\n", $document);
            foreach ($lines as $newline) {
                if (strpos($newline, $status) !== false) {
                    $label = explode("_", $newline);
                    $label_status = $label[1];
                    break;
                }
            }
        }
    }
}
$server = (strpos(',1,24,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,', ','.$status.',') !== false ? 0 : 1);
$result_carrier['result']['shipment_ref'] = $id_order;
$result_carrier['result']['carrier_server_success'] = ($server == 0 ? 'false' : 'true');
$result_carrier['result']['carrier_server_status_code'] = ($server == 0 ? $status : null);
$result_carrier['result']['carrier_server_status_text'] = ($server == 0 ? $label_status : null);
$result_carrier['result']['module_shipping_status_code'] = ($server ? $id_status : null);
$result_carrier['result']['carrier_shipping_status_text'] = ($server ? $result_status : null);
$result_carrier['result']['carrier_shipping_status_date'] = Date('Y-m-d H:i:s');
$result_carrier['result']['carrier_shipping_status_code'] = null;

//print_R($result_carrier);exit;
echo json_encode($result_carrier);
