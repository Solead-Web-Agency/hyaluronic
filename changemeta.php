<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$db = Db::getInstance();

$startId = 827;
$endId = 5680;

for ($productId = $startId; $productId <= $endId; $productId++) {
    // 1. Copier la meta_description de la langue 1 vers elle-même
    // Bien que cela semble redondant, je le fais selon votre demande
    $metaDescLang1Query = 'SELECT meta_description FROM `ps_product_lang` WHERE `id_product` = ' . $productId . ' AND `id_lang` = 1';
    $metaDescLang1 = $db->getValue($metaDescLang1Query);

    if ($metaDescLang1) {
        $updateMetaDescLang1Query = 'UPDATE `ps_product_lang` SET `meta_description` = "'. pSQL($metaDescLang1) .'" WHERE `id_product` = ' . $productId . ' AND `id_lang` = 1';
        $updateSuccessMetaDescLang1 = $db->execute($updateMetaDescLang1Query);

        if ($updateSuccessMetaDescLang1) {
            echo "La meta_description du produit {$productId} pour la langue 1 a été mise à jour avec succès.\n";
        } else {
            echo "Échec de la mise à jour de la meta_description du produit {$productId} pour la langue 1.\n";
        }
    } else {
        echo "Aucune meta_description trouvée pour le produit {$productId} en langue 1.\n";
    }

    // 2. Copier la description longue de la langue 2 vers la langue 1
    $descLang2Query = 'SELECT description FROM `ps_product_lang` WHERE `id_product` = ' . $productId . ' AND `id_lang` = 2';
    $descLang2 = $db->getValue($descLang2Query);

    if ($descLang2) {
        $updateDescLang1Query = 'UPDATE `ps_product_lang` SET `description` = "'. pSQL($descLang2) .'" WHERE `id_product` = ' . $productId . ' AND `id_lang` = 1';
        $updateSuccessDescLang1 = $db->execute($updateDescLang1Query);

        if ($updateSuccessDescLang1) {
            echo "La description du produit {$productId} pour la langue 1 a été mise à jour avec succès en utilisant la langue 2.\n";
        } else {
            echo "Échec de la mise à jour de la description du produit {$productId} pour la langue 1 à partir de la langue 2.\n";
        }
    } else {
        echo "Aucune description trouvée pour le produit {$productId} en langue 2.\n";
    }
}

echo "Script terminé avec succès.";
?>
