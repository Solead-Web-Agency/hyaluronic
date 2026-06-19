<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$proProductIds = [1, 8, 9, 11, 13, 14, 20, 21, 22, 23, 33, 34, 37, 38, 47, 48, 49, 52, 53, 55, 62, 64, 65, 101, 103, 104, 111, 113, 115, 116, 119, 120, 121, 122, 123, 124, 125, 126, 127, 132, 147, 148, 149, 150, 168, 170, 171, 172, 173, 174, 179, 195, 197, 202, 203, 354, 358, 359, 365, 367, 368, 369, 370, 371, 433, 447, 452, 453, 454, 463, 464, 465, 466, 467, 468, 470, 471, 473, 474, 487, 515, 518, 519, 522, 524, 525, 526, 527, 528, 533, 552, 592, 593, 594, 595, 596, 597, 660, 661, 662, 663, 667, 668, 686, 696, 699, 701, 706, 707, 708, 709, 710, 712, 714, 715, 716, 717, 718, 719, 724, 745, 746, 747, 752, 753, 754, 755, 756, 757, 758, 759, 760, 762, 763, 764, 765, 766, 767, 768, 770, 771, 772, 773, 774, 775, 776, 798, 799, 800, 801, 802, 803, 804, 810, 811, 814, 815, 822, 823, 825, 826, 6281, 6282, 6283, 6286, 6287, 6288, 6289, 6291, 6292, 6293, 6294, 6316, 6325, 6326, 6327, 6328, 6329, 6330, 6331, 6332, 6335, 6336, 6337];
$feature_id = 5; // ID de la caractéristique à ajouter
$feature_value_id = 35; // ID de la valeur de la caractéristique

foreach ($proProductIds as $product_id) {
    $product = new Product($product_id);

    if (!Validate::isLoadedObject($product)) {
        echo "Produit ID: $product_id n'existe pas\n";
        continue;
    }

    // Vérifie si la caractéristique est déjà présente
    $existingFeatures = $product->getFeatures();
    $found = false;
    foreach ($existingFeatures as $existingFeature) {
        if ($existingFeature['id_feature'] == $feature_id) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        // Ajoute la caractéristique au produit
        $product->addFeaturesToDB($feature_id, $feature_value_id);
        echo "Caractéristique ajoutée au produit ID: $product_id\n";
    } else {
        echo "La caractéristique existe déjà pour le produit ID: $product_id\n";
    }
}
?>
