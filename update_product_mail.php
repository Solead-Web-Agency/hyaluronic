<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require(dirname(__FILE__).'/config/config.inc.php');
require(dirname(__FILE__).'/init.php');

$xml_url = 'https://heuristic-pasteur.141-95-205-136.plesk.page/xml/BorneInfoPrix.xml';
$xml_data = file_get_contents($xml_url);
$xml = simplexml_load_string($xml_data);
$counter = 0;

$output = "";

try {
    foreach ($xml->Produits->article as $article) {
        $ean_xml = (string)$article->ean;
        $libelle_xml = (string)$article->libelle;
        $product_id_xml = (int)$article->product_id;
        $prix_ttc_xml = (float)$article->PUTTCNet;
        $stock_xml = (int)$article->Stock;
        $taux_tva_xml = (float)$article->TauxTVA;
        $pamp_xml = (float)$article->PAMP;

        if ($ean_xml != '') {
            $sql = 'SELECT p.`id_product`, p.`reference`, pl.`name`, p.`price`, p.`ean13`
                    FROM `'._DB_PREFIX_.'product` p
                    INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product`)
                    WHERE p.`ean13` = \''.pSQL($ean_xml).'\'';
            $result = Db::getInstance()->getRow($sql);

            if ($result && $result['ean13'] == $ean_xml) {
                $id_product = $result['id_product'];
                $reference = $result['reference'];
                $product_name_presta = $result['name'];
                $ean_presta = $result['ean13'];

                $product = new Product($id_product);
                $prix_ht_xml = round($prix_ttc_xml / (1 + ($taux_tva_xml / 100)), 6);
				$prix_ht_xml *= 1.12; // Augmente le prix de 10%
                $product->price = $prix_ht_xml;
                $product->wholesale_price = $pamp_xml;

                if ($taux_tva_xml == 20) {
                    $product->id_tax_rules_group = 54;
                }

                if($taux_tva_xml == 5.5){
                    $product->id_tax_rules_group = 56;
                }

                $product->update();

                // Mettre à jour le stock et définir le comportement en cas de rupture de stock pour la table stock_available
                StockAvailable::setQuantity($id_product, 0, $stock_xml);
                $id_stock_available = StockAvailable::getStockAvailableIdByProductId($id_product);
                $stock_available = new StockAvailable($id_stock_available);
                $stock_available->out_of_stock = 0; // Refuser les commandes
                $stock_available->save();

                // Calcul du prix TTC dans PrestaShop
                $address = null;
                $id_product_attribute = null;
                $use_tax = true;
                $decimals = 6;
                $only_reduc = false;
                $usereduc = true;
                $quantity = 1;
                $prix_ttc_presta = Product::getPriceStatic($id_product, $use_tax, $id_product_attribute, $decimals, $only_reduc, $usereduc, $quantity, false, null, null, $address);

                $counter++;

                $output .= "Occurrence $counter:\nID du produit = $id_product\nRéférence = $reference\n";
                $output .= "Nom du produit Presta = $product_name_presta\nNom du produit XML = $libelle_xml\n";
                $output .= "Product ID XML = $product_id_xml\nEAN en cours = $ean_presta\n";
                $output .= "Après modification :\nPrix HT Presta = $prix_ht_xml\nPrix TTC Presta = $prix_ttc_presta\nPrix d'achat HT Presta = $pamp_xml\nStock Presta = $stock_xml\n";
                $output .= "TVA Presta = $taux_tva_xml\nGroupe de règles fiscales Presta = " . $product->id_tax_rules_group . "\n";
                $output .= "Prix TTC XML = " . number_format($prix_ttc_xml, 2) . "\nStock XML = $stock_xml\n\n";
            }
        }
    }
} catch (Exception $e) {
    $output .= "Une erreur est survenue : " . $e->getMessage() . "\n";
}

$output .= 'Mise à jour terminée.';

Mail::Send(
    (int)Configuration::get('PS_LANG_DEFAULT'), // lang id
    'contact', // email template file to be used
    'Mise à jour des produits', // email subject
    array('{message}' => nl2br($output)), // email content
    'mamzallag@hotmail.fr', // receiver email address
    null, // receiver name
    null, // from email address
    'Update Script' // from name
);
?>
