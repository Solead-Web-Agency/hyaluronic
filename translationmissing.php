<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$db = Db::getInstance();

$startId = 827;
$endId = 5680;

$sourceLangId = 1;
$targetLangIds = [2, 3, 4, 5, 8];

for ($productId = $startId; $productId <= $endId; $productId++) {
    // Récupérer les données de la langue source
    $sourceDataQuery = 'SELECT * FROM `ps_product_lang` WHERE `id_product` = ' . $productId . ' AND `id_lang` = ' . $sourceLangId;
    $sourceData = $db->getRow($sourceDataQuery);

    if ($sourceData) {
        echo "Les données de la langue source pour le produit {$productId} ont été récupérées avec succès.\n";

        foreach ($targetLangIds as $targetLangId) {
            // Mise à jour des données existantes
            $updateQuery = 'UPDATE `ps_product_lang` SET `description` = "'. pSQL($sourceData['description']) .'", `description_short` = "'. pSQL($sourceData['description_short']) .'", `link_rewrite` = "'. pSQL($sourceData['link_rewrite']) .'", `meta_description` = "'. pSQL($sourceData['meta_description']) .'", `meta_keywords` = "'. pSQL($sourceData['meta_keywords']) .'", `meta_title` = "'. pSQL($sourceData['meta_title']) .'", `name` = "'. pSQL($sourceData['name']) .'", `available_now` = "'. pSQL($sourceData['available_now']) .'", `available_later` = "'. pSQL($sourceData['available_later']) .'", `delivery_in_stock` = "'. pSQL($sourceData['delivery_in_stock']) .'", `delivery_out_stock` = "'. pSQL($sourceData['delivery_out_stock']) .'" WHERE `id_product` = ' . $productId . ' AND `id_lang` = ' . $targetLangId;
            $updateSuccess = $db->execute($updateQuery);

            if($updateSuccess) {
                echo "Les données du produit {$productId} pour la langue cible {$targetLangId} ont été mises à jour avec succès.\n";
            } else {
                echo "Échec de la mise à jour des données du produit {$productId} pour la langue cible {$targetLangId}.\n";
            }
        }
    } else {
        echo "Aucune donnée de langue source trouvée pour le produit {$productId}.\n";
    }
}

echo "Script terminé avec succès.";
