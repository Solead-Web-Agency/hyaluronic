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

// Récupération du contexte PrestaShop
include('../../../config/config.inc.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

$postedApiKey = Tools::getValue('apiKey');
$languages = Language::getLanguages(true, Context::getContext()->shop->id);
$apiKeyOk = false;

// Check API key
foreach ($languages as $language) {
    if ($apiKeyTest = Configuration::get('steavisgarantis_apiKey_'.$language["id_lang"])) {
        if ($apiKeyTest == $postedApiKey) {
            $apiKeyOk = true;
        }
    }
}

if (!$apiKeyOk) {
    echo "Wrong api key";
    exit;
}

$id_lang = (int) Context::getContext()->language->id;

$sql = "
    SELECT
        p.id_product AS id,
        pl.name AS name,
        p.id_category_default AS category_id,
        cl.name AS category_name,
        p.ean13,
        p.reference AS sku,
        p.upc,
        pl.link_rewrite,
        i.id_image
    FROM "._DB_PREFIX_."product p
    INNER JOIN "._DB_PREFIX_."product_lang pl 
        ON (p.id_product = pl.id_product AND pl.id_lang = " . $id_lang . ")
    LEFT JOIN "._DB_PREFIX_."category_lang cl 
        ON (p.id_category_default = cl.id_category AND cl.id_lang = " . $id_lang . ")
    LEFT JOIN "._DB_PREFIX_."image i 
        ON (p.id_product = i.id_product AND i.cover = 1)
    WHERE p.active = 1
    GROUP BY p.id_product
    ORDER BY p.id_product ASC
";

$products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

$link = new Link();
foreach($products as $k => $product) {
    // Get product public URL
    $products[$k]['url'] = $link->getProductLink((int)$product['id'], $product['link_rewrite'] ?? null, null, null, (int)$id_lang);

    // Get product image URL
    $productImageUrl = $link->getImageLink($product['link_rewrite'], (int)$product['id_image'], 'home_default');
    $products[$k]['image_url'] = $productImageUrl ? "https://" . $productImageUrl : null;

    unset($products[$k]['link_rewrite'], $products[$k]['id_image']);
}

header('Content-Type: application/json');
echo json_encode($products);
exit;
