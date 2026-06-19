<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require(dirname(__FILE__).'/config/config.inc.php');
require(dirname(__FILE__).'/init.php');

$xml_url = 'https://heuristic-pasteur.141-95-205-136.plesk.page/xml/BorneInfoPrix.xml';
$xml_data = file_get_contents($xml_url);
$xml = simplexml_load_string($xml_data);

$eanList = array();
foreach ($xml->Produits->article as $article) {
    $eanList[] = (string)$article->ean;
}

try {
    for ($id_product = 827; $id_product <= 6280; $id_product++) {
        $sql = 'SELECT p.`id_product`, p.`reference`, p.`ean13`
                FROM `'._DB_PREFIX_.'product` p
                WHERE p.`id_product` = '.$id_product;
        $result = Db::getInstance()->getRow($sql);

        if ($result && (!in_array($result['reference'], $eanList) && !in_array($result['ean13'], $eanList))) {
            $product = new Product($id_product);
            $product->active = 0;
            $product->update();
            
            echo "Produit avec ID $id_product désactivé car EAN/Reference non trouvé dans le fichier XML.<br>";
        }
    }
} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage() . "<br>";
}

echo 'Opération terminée.';
?>
