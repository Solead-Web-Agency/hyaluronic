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

$postedApiKey = Tools::getValue('key');
$languages = Language::getLanguages(true, Context::getContext()->shop->id);

//Pour chaque langue active, on recupère la potentielle clé d'api
$apiKeyOk = false;
$apiKey = false;

foreach ($languages as $language) {
    //Si on a une clé d'api
    if ($apiKeyTest = Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"])) {
        if ($apiKeyTest == $postedApiKey) {
            $apiKeyOk = true;
            $apiKey = $postedApiKey;
        }
    }
}

if (!$apiKeyOk) {
    exit;
} else {
    //On détermine la langue correspondant à cette clé
    $lang = STEAVISGARANTIS::getLangFromApiKey($apiKey);
    //Et les ids lang de cette langue
    $langsId = STEAVISGARANTIS::getLangsId($lang);

    if ($langsId[0]) {
        $product = new Product(Tools::getValue('id'), false, $langsId[0]);
        echo($product->name);
    }
}
