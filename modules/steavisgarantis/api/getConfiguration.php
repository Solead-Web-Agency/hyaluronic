<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*/

require_once('../../../config/config.inc.php');
include_once('../steavisgarantis.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

// Si le module est désactivé alors on ne fait aucun traitement
if (!Module::isEnabled('steavisgarantis')) {
    exit;
}

$postedApiKey = Tools::getValue('key');
$languages = Language::getLanguages(true, Context::getContext()->shop->id);

//Pour chaque langue active, on recupère la potentielle clé d'api
$apiKeyOk = false;
$apiKeyList = array();
$currentIdLang = null;

foreach ($languages as $language) {
    //Si on a une clé d'api
    if ($apiKeyTest = Configuration::get('steavisgarantis_apiKey_' . $language["id_lang"])) {
        $apiKeyList[$language["iso_code"]] = $apiKeyTest;

        if ($apiKeyTest == $postedApiKey) {
            $apiKeyOk = true;
            $apiKey = $postedApiKey;
            $currentIdLang = $language["id_lang"];
        }
    }
}

if (!$apiKeyOk) {
    exit;
} else {
    // Order status
    $stateList = array();
    $sqlQuery = "SELECT * FROM "._DB_PREFIX_."order_state_lang WHERE id_lang = $currentIdLang";
    $orderStates = Db::getInstance()->ExecuteS($sqlQuery);
    $includeStatus = explode(',', Configuration::get('steavisgarantis_includeStatus'));

    // Format datas
    foreach ($orderStates as $orderState) {
        if(in_array($orderState['id_order_state'], $includeStatus)) {
            $stateList[$orderState['id_order_state']] = $orderState['name'];
        }
    }

    $shopsList = array();
    $shops = Shop::getShops();

    foreach($shops as $shop) {
        $shopsList[$shop['id_shop']] = $shop['name'] . " (Actif: ". $shop['active'] .")";
    }

    $langsList = array();
    $langs = Language::getLanguages(true);

    foreach($langs as $lang) {
        if($lang['active']) {
            $langsList[$lang['id_lang']] = $lang['iso_code'] . " (Shops: " . implode(', ', array_keys($lang['shops'])) . ")";
        }
    }

    $module = Module::getInstanceByName('steavisgarantis');

    $config = array(
        "versions" => array(
            "php" => phpversion(),
            "prestashop" => _PS_VERSION_,
            "module" => $module->version
        ),
        "hooks" => array(
            "actionObjectOrderHistoryAddAfter" => (bool)$module->isRegisteredInHook('actionObjectOrderHistoryAddAfter'),
            "actionOrderHistoryAddAfter" => (bool)$module->isRegisteredInHook('actionOrderHistoryAddAfter')
        ),
        "apiKeys" => $apiKeyList,
        "includeStatus" => $stateList,
        "shopsList" => $shopsList,
        "langsList" => $langsList,
        "publicKey" => Configuration::get('steavisgarantis_publicKey'),
        "newWidgets" => (int)Configuration::get('steavisgarantis_newWidgets'),
        "useOldOrdersMethod" => (int)Configuration::get('steavisgarantis_useOldOrdersMethod'),
        "sendPhone" => (int)Configuration::get('steavisgarantis_sendPhone'),
        "afterDays" => Configuration::get('steavisgarantis_afterDays'),
        "normalBehaviour" => Configuration::get('steavisgarantis_normalBehaviour'),
        "maxReviewPerPage" => Configuration::get('steavisgarantis_maxReviewPerPage'),
        "summaryDesign" => Configuration::get('steavisgarantis_summaryDesign'),
        "showStructured" => Configuration::get('steavisgarantis_showStructured'),
        "structuredFormat" => Configuration::get('steavisgarantis_structuredFormat'),
        "widgetPosition" => Configuration::get('steavisgarantis_widgetPosition'),
        "widgetJavascript" => Configuration::get('steavisgarantis_widgetJavascript'),
        "catStars" => Configuration::get('steavisgarantis_catStars'),
        "customCSS" => Configuration::get('steavisgarantis_customCSS'),
        "footerLink" => Configuration::get('steavisgarantis_footerLink'),
        "rgpd" => Configuration::get('steavisgarantis_rgpd'),
        "starColor" => Configuration::get('steavisgarantis_starColor')
    );

    header('Content-Type: application/json');
    echo json_encode($config);
}