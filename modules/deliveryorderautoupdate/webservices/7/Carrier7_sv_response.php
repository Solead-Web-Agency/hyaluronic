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

$id_shop = (int)Tools::getValue('id_shop');
$key = Db::getInstance()->getValue(
    'SELECT value
    FROM '._DB_PREFIX_.'configuration
    WHERE name like "HL_CARRIER7_id1" '.($id_shop ? 'AND id_shop='.(int)$id_shop : '')
);
$status_error = null;
$glsstatus = null;

$result_carrier = array();
$result = 'https://api.laposte.fr/suivi/v2/idships/'.$tracking_number;

$ch = curl_init($result);
curl_setopt_array($ch, array(
    CURLOPT_HTTPHEADER  => array('X-Okapi-Key: '.$key.'',
    'Accept: application/json'),
    CURLOPT_RETURNTRANSFER  =>true,
    CURLOPT_VERBOSE     => 1
));
$out = curl_exec($ch);
curl_close($ch);
header("Content-Type: text/plain");
print_r(Tools::jsonDecode($out));
