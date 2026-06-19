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

//Exemples d'appel du fichier productReviewsImport.php:
//
//Ajouter l'avis dont l'id est 57 sans l'updater dans le cas ou il existe déjà
//productReviewsImport.php?idSAG=57
//
//Ajouter l'avis dont l'id est 57 et l'updater dans le cas ou il existe déjà
//productReviewsImport.php?idSAG=57&update=1
//
//Ajouter tous les avis correspondant à un produit sans update des avis déjà enregistrés
//productReviewsImport.php?productID=67
//
//Ajouter tous les avis correspondant à un produit et updater ceux qui sont déjà enregistrés
//productReviewsImport.php?productID=67&update=1

// Récupération du contexte PrestaShop
include('../../../config/config.inc.php');
include_once('../steavisgarantis.php'); //Appel du fichier contenant la fonction d'import des avis produits

if (!defined('_PS_VERSION_')) {
    exit;
}

//
//Définition des variables
//

// Ignore review import if new widgets are enabled
if(Configuration::get('steavisgarantis_newWidgets')) {
    echo 'IGNORE';
    exit;
}

//Récupération des variables GET à transmettre à l'api AG et nettoyage (DO NOT USE ISSET WITH TOOLS GET VALUE)
$lang = (Tools::getIsset('lang')) ? htmlentities(trim(Tools::getValue('lang'))) : false;                    //Récupération de la langue
$url_ag = STEAVISGARANTIS::getDomainUrlFromLang($lang);

//Détermination de la clé d'api à communiquer
$apiKey = STEAVISGARANTIS::getApiKeyFromLang($lang);

//Récupération
$productID  = (Tools::getIsset('productID')) ? htmlentities(trim(Tools::getValue('productID'))) : false;    //Critère de recherche id produit concerné par l'avis
$idSAG      = (Tools::getIsset('idSAG'))     ? htmlentities(trim(Tools::getValue('idSAG')))     : false;    //Critère de recherche id de l'avis idSAG
$minDate    = (Tools::getIsset('minDate'))   ? htmlentities(trim(Tools::getValue('minDate')))   : false;    //Critère de recherche date minimum de l'avis (format timestamp)
$maxDate    = (Tools::getIsset('maxDate'))   ? htmlentities(trim(Tools::getValue('maxDate')))   : false;    //Critère de recherche date maximum de l'avis (format timestamp)
$maxResults = (Tools::getIsset('maxR'))      ? htmlentities(trim(Tools::getValue('maxR')))      : false;    //Nombre de résultats max à renvoyer (Attention, ne dépassera pas $maxResNeverExceed)
$token      = (Tools::getIsset('token'))     ? htmlentities(trim(Tools::getValue('token')))     : false;    //Jeton de vérification
$from       = (Tools::getIsset('from'))      ? htmlentities(trim(Tools::getValue('from')))      : false;    //Permet de reprendre l'importation de la totalité des avis à partir du dernier avis importé (dernier avis inclu)
$update     = (Tools::getIsset('update'))    ? htmlentities(trim(Tools::getValue('update')))    : false;    //Récupération de la variable booléenne update: 1 on force l'update d'un avis déjà présent, 0 on ne fait rien

$stepLimit  = (Tools::getIsset('stepLimit')) ? htmlentities(trim(Tools::getValue('stepLimit'))) : false;
$stepOffset = (Tools::getIsset('stepOffset')) ? htmlentities(trim(Tools::getValue('stepOffset'))) : false;

//Appel de la fonction d'import
STEAVISGARANTIS::importProductReviews($url_ag, $apiKey, $productID, $idSAG, $from, $minDate, $maxDate, $maxResults, $token, $update, $stepLimit, $stepOffset);
