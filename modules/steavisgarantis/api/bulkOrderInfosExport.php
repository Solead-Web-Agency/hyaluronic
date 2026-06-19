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

// Fichier permettant l'export des X dernières commandes.
// Debug mode : affiche des echos des variables clés
$debug = isset($_GET['debug']);

// Importation des avis produit
//global $smarty;
require_once('../../../config/config.inc.php');
include_once('../steavisgarantis.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

// Fonctionnement
// STEAVISGARANTIS appelle le Client (bulkOrderInfosExport.php) en transmettant un token et le nombre de commande à exporter
// Le client (bulkOrderInfosExport.php) appelle STEAVISGARANTIS pour vérifier la validité du token
// Si le token est bon on va chercher la liste des commandes à exporter
// On envoie la liste des commandes à exporter à la fonction postData
// La fonction postData envoie les données en post en SSL avec la clé d'API

// Si le module est désactivé alors on ne fait aucun traitement
if (!Module::isEnabled('steavisgarantis')) {
    exit;
}

// Expose util headers
header( 'X-GRC-Module: prestashop' );
header( 'X-GRC-Version: ' . STEAVISGARANTIS::getVersion() );
header( 'X-GRC-Widgets: ' . (Configuration::get('steavisgarantis_newWidgets') ? '1' : '0') );

// On récupère les dates des dernières commandes à extraire
$fromDate = pSQL(Tools::getValue("fromDate"));
$toDate = pSQL(Tools::getValue("toDate"));
$lang = pSQL(Tools::getValue("lang"));
$source = pSQL(Tools::getValue("source"));

if (!$fromDate) {
    echo "Missing fromDate";
    exit;
}
if (!$toDate) {
    echo "Missing toDate";
    exit;
}
if (!$lang) {
    echo "Missing lang";
    exit;
}

//On récupère le token et on vérifie que l'on ai bien le droit d'envoyer les données
$token = Tools::getValue("token");
if (!$token) {
    echo "Missing token";
    exit;
}

$checkAnswer = STEAVISGARANTIS::tokenCheck($token, $lang);

if (strpos($checkAnswer, "ValidSagData") === false) {
    // DEBUG: Si le token est invalide
    if ($debug) {
        var_dump($checkAnswer);
    }
    exit;
}
else {
    // Debug
    if ($debug) {
        echo "Récupération des commandes pour la période du $fromDate au $toDate <br/>";
    }

    // Récupération des id states auxquels il ne faut pas envoyer le mail
    // On initialise la variable qui permettra de générer la string sql
    $includeStatusString = "";

    // On met la liste des status à inclure en array
    $includeStatus = explode(",", Configuration::get('steavisgarantis_includeStatus'));

    if ($debug) {
        echo "Liste des status IDs à inclure : ";
    }

    // Si on a des statuts à inclure
    // Pour chaque champ
    foreach ($includeStatus as $value) {
        //On vérifie que le champ n'est pas vide et est un nombre
        if (!empty($value) and is_numeric($value)) {
            $value = (int)($value);
            $includeStatusString .= " OR oh.id_order_state = " . $value;
            if ($debug) {
                echo $value . ", ";
            }
        }
    }

    // Shop Condition Multiboutique (permet de récupérer seulement les commandes de la boutique appelée)
    if (version_compare(_PS_VERSION_, '1.5', '<')) {
        $shopCond = "";
    } else {
        $shopId = (int)Context::getContext()->shop->id;
        // On gère le multiboutique
        $shopCond = "o.id_shop = " . $shopId . " AND";
        // Et le multilingue
        if ($lang) {
            // La on récupère les ids lang associés à la clé d'API de la langue entrée en paramètre
            $langIds = STEAVISGARANTIS::getLangsId($lang);
            if (count($langIds)) {
                $shopCond .= " ( 0 ";
                foreach ($langIds as $langId) {
                    $langId = (int)$langId;
                    $shopCond .= " OR o.id_lang = " . $langId . " ";
                }
                $shopCond .= " ) AND";
            }
        }
    }

    // Mode RGPD est activé sur le module steavisgarantis ?
    $rgpd = Configuration::get("steavisgarantis_rgpd");

    $ordersCount = ($source && $source == "count");

    if(!$ordersCount) {
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
                ". ($rgpd ? ', sagcu.value AS rgpd' : '');
    } else {
        $sql = "SELECT COUNT(DISTINCT o.id_order) AS orders_count";
    }

    // Récupération des infos de la commande (+ client et produits)
    // 1 ligne = 1 produit
    $sql .= " FROM 
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
            AND oh.date_add BETWEEN '$fromDate' AND '$toDate' 
            AND (0 $includeStatusString) 
            ORDER BY oh.date_add DESC";
    
    // DEBUG: Requête SQL
    if ($debug) {
        echo "<br/><br/>" . $sql . "<br/><br/>";
    }

    if ($results0 = Db::getInstance()->ExecuteS($sql)) 
    {
        if($ordersCount && isset($results0[0]["orders_count"])) {
            echo json_encode(array("count" => $results0[0]["orders_count"]));
            exit;
        }

        $toSendTable = array();
        foreach ($results0 as $row) 
        {
            $id_order = (int)($row['id_order']);
            $order_date = pSQL($row['date_add']);
            $reference = pSQL($row['reference']);
            $id_customer = (int)($row['id_customer']);
			$id_lang = (int)($row['id_lang']);

            // Consentement : Si on trouve le client à 0 dans la table steavisgarantis_customer
            if(isset($row['rgpd']) && $row['rgpd'] == '0') {
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

                $toSendTable[$id_order] = $mailToSend;
            }

            // On récupère le contexte
            $context = Context::getContext();

            // On récupère les informations du produit
            $productTmp = new Product($row["product_id"], false, $id_lang);

            // On récupère l'image du produit
            $productImgUrl = '';
            $productImg = $productTmp->getCover($productTmp->id);
            
            if ($productImg && isset($productImg['id_image'])) {
                $productImgUrl = $context->link->getImageLink(
                    isset($productTmp->link_rewrite) ? $productTmp->link_rewrite : $productTmp->name, 
                    (int)$productImg['id_image'], 
                    'home_default'
                );
            }

			// On récupère l'URL du produit en prenant en compte la variante si besoin
            $url = $context->link->getProductLink(
                $productTmp, 
                null, 
                null, 
                null, 
                $id_lang, 
                $row["id_shop"], 
                $row["product_attribute_id"]
            );

            $toSendTable[$id_order]["products"][] = array(
                "id_shop" => $row["id_shop"],
                "url" => $url,
                "id" => $row["product_id"],
                "name" => $row["product_name"],
                "ean13" => $row["product_ean13"],
                "sku" => $row["product_reference"],
                "upc" => $row["product_upc"]
            );
        }
        
        // DEBUG: Nombre de commandes détectées + détail des commandes
        if ($debug) {
            echo "Commande(s) détectée(s) : " . count($toSendTable) . "<br/>";
            echo "Dump de 'toSendTable': ";
            echo '<pre>' . print_r($toSendTable, true) . '</pre>';
        }

        // On envoie les données en post avec cryptage SSL
        $posted = STEAVISGARANTIS::postData($toSendTable, "bulkOrderInfos.php", $token, $lang);

        // DEBUG: Retour du postData
        if ($debug) {
            var_dump($posted);
        }
    } else {
        // DEBUG: Aucune commande
        if ($debug) {
            echo "Aucune commande détectée pour la période choisie.";
        }
    }
}