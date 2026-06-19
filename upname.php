<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$db = Db::getInstance();

$startId = 1;
$endId = 826;

$sourceLangId = 1;
$targetLangIds = [2, 3, 4, 5, 8]; // Vous pouvez ajouter d'autres id_lang si nécessaire

for ($productId = $startId; $productId <= $endId; $productId++) {
    // Récupérer le nom du produit en français
    $nameFrenchQuery = 'SELECT `name` FROM `ps_product_lang` WHERE `id_product` = ' . $productId . ' AND `id_lang` = ' . $sourceLangId;
    $nameFrench = $db->getValue($nameFrenchQuery);

    if ($nameFrench) {
        echo "Nom du produit {$productId} en français récupéré avec succès: {$nameFrench}.\n";

        foreach ($targetLangIds as $targetLangId) {
            // Mise à jour du nom du produit pour chaque langue cible
            $updateNameQuery = 'UPDATE `ps_product_lang` SET `name` = "'. pSQL($nameFrench) .'" WHERE `id_product` = ' . $productId . ' AND `id_lang` = ' . $targetLangId;
            $updateSuccessName = $db->execute($updateNameQuery);

            if($updateSuccessName) {
                echo "Le nom du produit {$productId} pour la langue cible {$targetLangId} a été mis à jour avec succès.\n";
            } else {
                echo "Échec de la mise à jour du nom du produit {$productId} pour la langue cible {$targetLangId}.\n";
            }
        }
    } else {
        echo "Aucun nom de produit trouvé pour le produit {$productId} en français.\n";
    }
}

echo "Script terminé avec succès.";
?>
