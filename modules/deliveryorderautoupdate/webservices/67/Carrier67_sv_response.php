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
$id_shop = Tools::getValue('id_shop');
$id_carrier = 67;
$status_error = null;
$glsstatus = null;
$result_carrier = array();
$result = 'https://gls-group.eu/app/service/open/rest/FR/fr/rstt001?match='.$tracking_number;
$url_page = strip_tags(Tools::file_get_contents($result, true));
$url = Tools::stripslashes($url_page);
$url = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $url), true);

print_r($url);
