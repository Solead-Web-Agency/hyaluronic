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

$id_carrier = 212;
$result_carrier = array();

try {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://tracking.nexive.it/?b='.$tracking_number.'&lang=EN',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);

    // Check the return value of curl_exec(), too
    if ($response === false) {
        throw new Exception(curl_error($curl), curl_errno($curl));
    }

    // Check HTTP return code, too; might be something else than 200
    $httpReturnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    /* Process $content here */
} catch (Exception $e) {
    trigger_error(
        sprintf(
            'Curl failed with error #%d: %s',
            $e->getCode(),
            $e->getMessage()
        ),
        E_USER_ERROR
    );
} finally {
    // Close curl handle unless it failed to initialize
    if (is_resource($curl)) {
        curl_close($curl);
    }
}
