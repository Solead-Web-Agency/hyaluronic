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

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
/* Check to security tocken */
$tokens = Configuration::getMultiShopValues('DELIVERY_TOKEN');
$tokens[0] = Configuration::getGlobalValue('DELIVERY_TOKEN');
$id_shop = array_search(Tools::getValue('token'), $tokens);

if ($id_shop === false ||
    !Module::isInstalled('deliveryorderautoupdate')
) {
    die('Bad token');
}
Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
$lastCheck = Configuration::getGlobalValue('HL_TRACKING_LAST_STATUSCODE_CHECK');
$version = Configuration::getGlobalValue('HL_TRACKING_STATUSCODE_VERSION');
$today = date('Y-m-d');
$rs = array(
    'success' => true,
    'count' => 0
);
$url = 'https://helloshop.com/status_code.xml';
if (!checkExists($url)) {
    $rs['success'] = false;
    $rs['err'] = 'update not available';
    die(json_Encode($rs));
}
if ($lastCheck != $today) {
    Configuration::updateGlobalValue('HL_TRACKING_LAST_STATUSCODE_CHECK', $today, 0, 0);
    $xml = simplexml_load_file($url);
    $newversion = (string)$xml->database->attributes()->name;
    if ($version != $newversion) {
        $json = json_decode(json_encode($xml), true);
        $table = $json['database']['table'];
        Db::getInstance()->execute('TRUNCATE TABLE '._DB_PREFIX_.'hl_tracking_status_matching');
        foreach ($table as $row) {
            $sql = 'INSERT INTO '._DB_PREFIX_.'hl_tracking_status_matching VALUES ('.$row['column'][0].', '.$row['column'][1].', "'.$row['column'][2].'")';
            Db::getInstance()->execute($sql);
            $rs['count'] += 1;
        }
        Configuration::updateGlobalValue('HL_TRACKING_STATUSCODE_VERSION', $newversion, 0, 0);
    }
}
die(json_Encode($rs));

function checkExists($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($code == 200) {
        $status = true;
    } else {
        $status = false;
    }
    curl_close($ch);
    return $status;
}
