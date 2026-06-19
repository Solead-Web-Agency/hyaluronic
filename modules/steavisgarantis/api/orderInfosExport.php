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

// Debug mode : affiche des echos des variables clés
$debug = isset($_GET['debug']);

// Importation des avis produit
require_once('../../../config/config.inc.php');
include_once('../steavisgarantis.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

// Si le module est désactivé alors on ne fait aucun traitement
if (!Module::isEnabled('steavisgarantis')) {
    exit;
}

// Expose util headers
header( 'X-GRC-Module: prestashop' );
header( 'X-GRC-Version: ' . STEAVISGARANTIS::getVersion() );
header( 'X-GRC-Widgets: ' . (Configuration::get('steavisgarantis_newWidgets') ? '1' : '0') );

// On récupère la clé d'API en post et on vérifie que c'est la même que celle en base
$postedApiKey = Tools::getValue('apiKey');
$languages = Language::getLanguages(true, Context::getContext()->shop->id);

// Pour chaque langue active, on recupère la potentielle clé d'api
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
    echo "Wrong api key";
    exit;
} 
else {
    // Ignore old mail sending method
    $source = Tools::getValue('source');
    if($source == "mail" && !Configuration::get('steavisgarantis_useOldOrdersMethod')) {
        echo "IGNORE";
        exit;
    }
    
    $lang = STEAVISGARANTIS::getLangFromApiKey($apiKey);
    $afterDays = (Configuration::get('steavisgarantis_afterDays') ? Configuration::get('steavisgarantis_afterDays') : 10);

    // Permet d'éviter de sortir les commandes auxquelles on est censé avoir déjà envoyé un mail
    $dateFrom = pSQL(date("Y-m-d H:i:s", time()-($afterDays*86400 + 86400)));
    $dateTo = pSQL(date("Y-m-d H:i:s", time()-($afterDays*86400)));

    // Récupération des id states auxquels il ne faut pas envoyer le mail
    // On initialise la variable qui permettra de générer la string sql
    $includeStatusString = "";

    // On met la liste des status à inclure en array
    $includeStatus = explode(",", Configuration::get('steavisgarantis_includeStatus'));

    // Si on a des statuts à inclure
    // Pour chaque champ
    foreach ($includeStatus as $value) {
        //On vérifie que le champ n'est pas vide et est un nombre
        if (!empty($value) and is_numeric($value)) {
            $value = (int)($value);
            $includeStatusString .=  " OR oh.id_order_state = " . $value;
            if ($debug) {
                echo $value;
            }
        }
    }

    // Shop Condition Multiboutique (permet de récupérer seulement les commandes de la boutique appelée)
    if (version_compare(_PS_VERSION_, '1.5', '<')) {
        $shopCond = "";
    } else {
        $shopCond = "o.id_shop = " . Context::getContext()->shop->id . " AND";
        //Et le multilingue
        if ($lang) {
            $langIds = STEAVISGARANTIS::getLangsId($lang);  //La on récupère les ids lang associés à la clé d'API de la langue entrée en paramètre
            if (count($langIds)) {
                $shopCond .= " ( 0 ";
                foreach ($langIds as $langId) {
                    $shopCond .= " OR o.id_lang = " . $langId . " ";
                }
                $shopCond .= " ) AND";
            }
        }
    }


    // Mode RGPD est activé sur le module steavisgarantis ?
    $rgpd = Configuration::get("steavisgarantis_rgpd");

    // Récupération des infos de la commande (+ client et produits)
    // 1 ligne = 1 produit
    $sql = "SELECT 
                o.*, 
                od.id_shop, 
                od.product_id,
				od.product_attribute_id,
                od.product_name, 
                od.product_ean13, 
                od.product_upc,
                od.product_reference,
                cu.firstname,
                cu.lastname,
                cu.email,
                ad.phone,
                ad.phone_mobile
                ". ($rgpd ? ', sagcu.value AS rgpd' : '') ."
            FROM 
                "._DB_PREFIX_."orders o, 
                "._DB_PREFIX_."order_detail od, 
                "._DB_PREFIX_."order_history oh, 
                "._DB_PREFIX_."customer cu, 
                "._DB_PREFIX_."address ad 
                " . ($rgpd ? ', '._DB_PREFIX_.'steavisgarantis_customer sagcu' : '');

    $sql .= "WHERE $shopCond oh.id_order = o.id_order 
            AND od.id_order = o.id_order 
            AND cu.id_customer = o.id_customer 
            AND ad.id_address = o.id_address_delivery 
            ". ($rgpd ? 'AND sagcu.id_customer = o.id_customer AND id_steavisgarantis_customfield = 1 ' : '') ."
            AND oh.date_add BETWEEN '$dateFrom' AND '$dateTo' 
            AND (0 $includeStatusString) 
            ORDER BY o.id_order ASC";

    if ($results0 = Db::getInstance()->ExecuteS($sql)) {

        if ($debug) {
            echo "On a des commandes...";
        }

        $toSendTable = array();
        foreach ($results0 as $row) {
            $id_order = (int)($row['id_order']);
            $order_date = pSQL($row['date_add']);
            $reference = pSQL($row['reference']);
            $id_customer = (int)($row['id_customer']);
			$id_lang = (int)($row['id_lang']);

            // Consentement : Si on trouve le client à 0 dans la table steavisgarantis_customer
            if($row['rgpd'] == '0') {
                // Si le client n'a pas coché la case, alors on passe au suivant
                continue;
            }

            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $email = $row['email'];
            $phone = (!empty($row['phone_mobile']) ? $row['phone_mobile'] : $row['phone']);

            // Si la commande n'a pas encoré été ajoutée, on l'insère dans le tableau
            if(!array_key_exists($id_order, $toSendTable))
            {
                $id_site = STEAVISGARANTIS::getShopId($lang);
                $products = array();

                $mailToSend = array(
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "id_order" => $id_order,
                    "id_site" => $id_site,
                    "products" => $products,
                    "email" => $email,
                    "phone" => Configuration::get('steavisgarantis_sendPhone') ? $phone : null,
                    "reference" => $reference,
                    "order_date" => $order_date
                );

                $toSendTable[] = $mailToSend;
            }

            // On récupère les informations du produit
            $link = new Link();
            $productTmp = new Product($row["product_id"], false, $id_lang);
            $url = $link->getProductLink($productTmp, null, null, null, $id_lang, $row["id_shop"], $row["product_attribute_id"]);

            //On retrouve le bon index pour ajouter les produits à la commande concernée
            $toSendTable[count($toSendTable) - 1]["products"][] = array(
                "id_shop" => $row["id_shop"],
                "url" => $url,
                "id" => $row["product_id"],
                "name" => $row["product_name"],
                "ean13" => $row["product_ean13"],
                "sku" => $row["product_reference"],
                "upc" => $row["product_upc"]
            );
        }

        // Affichage des informations au format JSON
        echo json_encode($toSendTable);
    } else {
        if ($debug) {
            echo "Aucune commande à afficher.";
        }
    }
}
