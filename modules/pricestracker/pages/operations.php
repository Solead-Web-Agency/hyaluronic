<?php
//    /modules/pricestracker/pages/operations.php?prolongerEssai&clef=
//    /modules/pricestracker/pages/operations.php?doublonsProduitsEtrangers
//    /modules/pricestracker/pages/operations.php?effacerConcurrent&id=1
//    /modules/pricestracker/pages/operations.php?reglesPrixMega
//    /modules/pricestracker/pages/operations.php?associerParReferenceExacte
//    /modules/pricestracker/pages/operations.php?clearHistory&max_age=
//    /modules/pricestracker/pages/operations.php?effacerConcurrentMotif&id=1&motif=https%3A%2F%2Fwww%5C.rm365%5C.fr%2F

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set("memory_limit","1600M");

header('Content-Type: text/html; charset=UTF-8');

//Initialisation Prestashop page module
include('../../../config/config.inc.php');
require_once('../../../init.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set("memory_limit","1600M");
error_reporting(E_ALL & ~E_NOTICE);


function supprimer_dossier($directory, $empty = false) {
    if(substr($directory,-1) == "/") {
        $directory = substr($directory,0,-1);
    }
 
    if(!file_exists($directory) || !is_dir($directory)) {
        return false;
    } elseif(!is_readable($directory)) {
        return false;
    } else {
        $directoryHandle = opendir($directory);
 
        while ($contents = readdir($directoryHandle)) {
            if($contents != '.' && $contents != '..') {
                $path = $directory . "/" . $contents;
 
                if(is_dir($path)) {
                    supprimer_dossier($path);
                } else {
                    unlink($path);
                }
            }
        }
 
        closedir($directoryHandle);
 
        if($empty == false) {
            if(!rmdir($directory)) {
                return false;
            }
        }
 
        return true;
    }
} 

function recursiveChmod($path, $filePerm=0644, $dirPerm=0755) {
		// Check if the path exists
		if (!file_exists($path)) {
			return(false);
		}
		
		// See whether this is a file
		if (is_file($path)) {
			// Chmod the file with our given filepermissions
			chmod($path, $filePerm);
		
		// If this is a directory...
		} elseif (is_dir($path)) {
			// Then get an array of the contents
			$foldersAndFiles = scandir($path);
			
			// Remove "." and ".." from the list
			$entries = array_slice($foldersAndFiles, 2);
			
			// Parse every result...
			foreach ($entries as $entry) {
				// And call this function again recursively, with the same permissions
				$this->recursiveChmod($path."/".$entry, $filePerm, $dirPerm);
			}
			
			// When we are done with the contents of the directory, we chmod the directory itself
			chmod($path, $dirPerm);
		}
		
		// Everything seemed to work out well, return true
		return(true);
	}	


if(Tools::isSubmit('effacerConcurrent') && (int)Tools::getValue('id'))
{
	Db::getInstance()->Execute('
	DELETE FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
	WHERE id_concurrents='.(int)Tools::getValue('id'));
}
if(Tools::isSubmit('effacerConcurrentMotif') && (int)Tools::getValue('id'))
{
	Db::getInstance()->Execute('
	DELETE FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
	WHERE id_concurrents='.(int)Tools::getValue('id').' AND lien REGEXP \''.pSQL(Tools::getValue('motif')).'\'');
}

if(Tools::isSubmit('doublonsProduitsEtrangers'))
{
	$products=Db::getInstance()->executeS('
		SELECT DISTINCT id_produits_etrangers,id_concurrents,nom
		FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
		ORDER BY id_concurrents,nom ASC');
	$nom_courant=NULL;
	$id_courant=NULL;
	foreach($products as $product)
	{
		if(strcmp($nom_courant,$product['nom'])!==0)
		{
			$nom_courant=$product['nom'];
			$id_courant=$product['id_produits_etrangers'];
		}
		else
		{
			Db::getInstance()->Execute('
			DELETE FROM '._DB_PREFIX_.'pricestracker_produits_etrangers
			WHERE id_produits_etrangers='.(int)$product['id_produits_etrangers']);
			Db::getInstance()->Execute('
			DELETE FROM '._DB_PREFIX_.'pricestracker_associations
			WHERE id_produits_etrangers='.(int)$product['id_produits_etrangers']);
			Db::getInstance()->Execute('
			DELETE FROM '._DB_PREFIX_.'pricestracker_historique
			WHERE id_produits_etrangers='.(int)$product['id_produits_etrangers']);
			Db::getInstance()->Execute('
			DELETE FROM '._DB_PREFIX_.'pricestracker_proximites
			WHERE id_produits_etrangers='.(int)$product['id_produits_etrangers']);
		}
	}
}
if(Tools::isSubmit('doublonsAssociations'))
{
	$products=Db::getInstance()->executeS('
		SELECT id_associations,id_produits_etrangers,id_product
		FROM `'._DB_PREFIX_.'pricestracker_associations`
		ORDER BY id_produits_etrangers,id_product ASC');
	$nom_courant=NULL;
	$id_courant=NULL;
	foreach($products as $product)
	{
		if(strcmp($nom_courant,$product['id_produits_etrangers'].','.$product['id_product'])!==0)
		{
			$nom_courant=$product['id_produits_etrangers'].','.$product['id_product'];
			$id_courant=$product['id_associations'];
		}
		else
		{
			Db::getInstance()->Execute('
			DELETE FROM '._DB_PREFIX_.'pricestracker_associations
			WHERE id_associations='.(int)$product['id_associations']);
		}
	}
}
if(Tools::getValue('chmod777'))
{
	$this->recursiveChmod(dirname(__FILE__),0777,0777);
	die;
}
if(Tools::getValue('chmod777Com'))
{
	$this->recursiveChmod(dirname(__FILE__).'/pages/communication.php',0777,0777);
	die;
}
if(Tools::getValue('chmod755'))
{
	$this->recursiveChmod(dirname(__FILE__),0644,0755);
	die;
}
if(Tools::getValue('supprProxi'))
{
	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_proximites`
	WHERE 1');
}
if(Tools::isSubmit('effacerCache'))
{
	supprimer_dossier('../../../cache/smarty');
}
if(Tools::isSubmit('getPostMax'))
{
	echo ini_get('post_max_size');
}
if(Tools::isSubmit('prolongerEssai'))
{
	Configuration::updateGlobalValue('pricestracker_DATEINSTALL', time());
}
if(Tools::isSubmit('terminerEssai'))
{
	Configuration::updateGlobalValue('pricestracker_DATEINSTALL', 0);
}
if(Tools::isSubmit('bloquer'))
{
	Configuration::updateGlobalValue('pricestracker_DATEINSTALL', -1);
}
if(Tools::isSubmit('reglesPrixMega'))
{
	$sql[] = 'INSERT INTO `'._DB_PREFIX_.'pricestracker_regles` ( `nom`, `regle`) VALUES ( \''.pSQL(('Mail alert : price change (reference price)')).'\', \'if($arguments[0]!=$competitorPrice )\r\n{\r\n  $alertMailGrouped=true;\r\n  $newArguments=$competitorPrice;\r\n}\');';
$sql[] = 'INSERT INTO `'._DB_PREFIX_.'pricestracker_regles` ( `nom`, `regle`) VALUES ( \''.pSQL(('Mail alert : price between(limit max[default:my price],limit min[optionnal])')).'\', \'$limite_max= @$arguments[0] ? @$arguments[0] : $oldPrice;\r\nif($competitorPrice<=$limite_max) $alertMailGrouped=true;\r\n\r\nif(@$arguments[1] && $competitorPrice<@$arguments[1]) $alertMailGrouped=false;\');';
$sql[] = 'INSERT INTO `'._DB_PREFIX_.'pricestracker_regles` ( `nom`, `regle`) VALUES 
( \''.pSQL(('Adjust on competitor lower(lower of X than the competitor[default:0],my price limit[optionnal])')).'\', \'$limite=$oldPrice-@$arguments[0];\r\nif($competitorPrice<$limite && $competitorPrice>=@$arguments[1])\r\n{\r\n  $newPrice = $competitorPrice-@$arguments[0];\r\n  if($newPrice<@$arguments[1]) $newPrice=$arguments[1];\r\n}\');';
$sql[] = 'INSERT INTO `'._DB_PREFIX_.'pricestracker_regles` ( `nom`, `regle`) VALUES 
( \''.pSQL(('Adjust on competitor upper(my price limit[optionnal])')).'\', \'if($competitorPrice>$oldPrice)\r\n{\r\n  $newPrice = $competitorPrice;\r\n  if(@$arguments[0] && $newPrice > @$arguments[0]) $newPrice = false;\r\n}\');';
$sql[] = 'INSERT INTO `'._DB_PREFIX_.'pricestracker_regles` ( `nom`, `regle`) VALUES 
( \''.pSQL(('Adjust always on competitor(lower of X than the competitor[default:0])')).'\', \'$newPrice = $competitorPrice-@$arguments[0];\');';
$sql[] = 'INSERT INTO `'._DB_PREFIX_.'pricestracker_regles` ( `nom`, `regle`) VALUES 
( \''.pSQL(('Adjust always on minimum competitors\' price (lower of X than the competitor[default:0])')).'\', \'$prices=getCompetitorsPrices();\r\nforeach($prices as $price)\r\n{\r\n   if(!$newSpecificPrice || $newPrice>$price[\\\'price\\\']-@$arguments[0]) $newPrice=$price[\\\'price\\\']-@$arguments[0];\r\n}\');';


	foreach ($sql as $s) {
		echo $s;
		Db::getInstance()->Execute($s);
	}
}

// /modules/pricestracker/pages/operations.php?associerParReferenceExacte
if(Tools::isSubmit('associerParReferenceExacte'))
{

	$concurrents = Db::getInstance()->executeS('
	SELECT nom,id_concurrents
	FROM `'._DB_PREFIX_.'pricestracker_concurrents`
	WHERE 1=1');
	$tabConcurrents=array();
	foreach($concurrents as $c)
	{
		$tabConcurrents[]=$c['id_concurrents'];
	}
	
	
	$produits = Db::getInstance()->executeS('
	SELECT id_product, reference, ean13
	FROM `'._DB_PREFIX_.'product`
	WHERE 1=1');
	$correspRef2idProduct = array();
	foreach($produits as $produit)
	{
		if($produit['reference']) $correspRef2idProduct[ $produit['reference'] ] = (int)$produit['id_product'];
		if($produit['ean13']) $correspRef2idProduct[ $produit['ean13'] ] = (int)$produit['id_product'];
	}
	
	
	if('effacerAssociations')
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_associations`
		WHERE 1=1');
	}
	
	$produits_concurrents = Db::getInstance()->executeS('
	SELECT id_produits_etrangers, reference
	FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
	WHERE 1=1');
	foreach($produits_concurrents as $produit_concurrent)
	{
		$id_product_correspondant = $correspRef2idProduct[ $produit_concurrent['reference'] ];
		if($id_product_correspondant)
		{
			Db::getInstance()->insert(
				'pricestracker_associations',
				array(
					'id_product'=>$id_product_correspondant,
					'id_produits_etrangers'=>(int)$produit_concurrent['id_produits_etrangers'],
					'automatique'=>1,
				)
			);
		}
	}

}

// /modules/pricestracker/pages/operations.php?proximiteAsso&limiteProximite=1
if(Tools::isSubmit('proximiteAsso'))
{

	$concurrents = Db::getInstance()->executeS('
	SELECT nom,id_concurrents
	FROM `'._DB_PREFIX_.'pricestracker_concurrents`
	WHERE 1=1');
	$tabConcurrents=array();
	foreach($concurrents as $c)
	{
		$tabConcurrents[]=$c['id_concurrents'];
	}
	
	
	$produits = Db::getInstance()->executeS('
	SELECT id_product
	FROM `'._DB_PREFIX_.'product_lang`
	GROUP BY id_product
	ORDER BY name ASC');
	
	
	if('effacerAssociations')
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_associations`
		WHERE 1=1');
	}
	
	foreach($produits as $produit)
	{
		$id_product=$produit['id_product'];
		foreach($tabConcurrents as $id_concurrents)
		{
			$produitsProx_sql = Db::getInstance()->executeS('
			SELECT PE.id_produits_etrangers AS id_produits_etrangers,P.proximite AS proximite
			FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE,`'._DB_PREFIX_.'pricestracker_proximites` P
			WHERE PE.id_concurrents='.$id_concurrents.' AND P.id_product='.$id_product.' AND P.id_produits_etrangers=PE.id_produits_etrangers 
			GROUP BY PE.id_produits_etrangers
			ORDER BY P.proximite');
			
			
			foreach($produitsProx_sql as $pProx)
			{
				$produitsProx=$pProx['id_produits_etrangers'];
				$proximiteProd=$pProx['proximite'];
		
				/*Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_associations`
				WHERE id_product='.$id_product.' AND id_produits_etrangers='.$produitsProx);*/
				
				if(Tools::getValue('limiteProximite')<=$proximiteProd)
				{
					Db::getInstance()->insert(
						'pricestracker_associations',
						array(
							'id_product'=>$id_product,
							'id_produits_etrangers'=>$produitsProx,
						)
					);
				}
			}
		}
	}
}
elseif(Tools::isSubmit('effacerUPC'))
{
	Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET upc=\'\' WHERE 1=1');
}
elseif(Tools::isSubmit('effacerAssociations'))
{
	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_associations`
	WHERE 1=1');
}
elseif(Tools::isSubmit('voirUrl'))
{
	echo __PS_BASE_URI__ .'///';
	$sites=Db::getInstance()->Execute('SELECT * `'._DB_PREFIX_.'shop_url` WHERE 1=1');
	print_r($sites);
}
elseif(Tools::isSubmit('associationMapUrl'))
{
	$id_product_to_url=array(
	2254733=>'http://djmania.es/p/ibiza-sound-combo-210-sistema-portable',
2254733=>'http://www.madridhifi.com/p/ibiza-sound-combo210/',
2254912=>'https://www.thomann.de/es/rcf_l_pad_6.htm',
12530195=>'https://www.radiocolon.com/MALETA-WALKASSE-WM18M-LTS-aWMCPROUNIMED.html',
12530255=>'https://www.madridhifi.com/p/walkasse-wmc-almixcd/',

	);
	
	foreach($id_product_to_url as $id_product=>$url)
	{
		$id_produits_etrangers = Db::getInstance()->getValue("
		SELECT id_produits_etrangers
		FROM `"._DB_PREFIX_."pricestracker_produits_etrangers`
		WHERE lien='".pSQL($url)."'");
		
		Db::getInstance()->Execute("INSERT IGNORE INTO `"._DB_PREFIX_."pricestracker_associations` (id_produits_etrangers,id_product,automatique)
		VALUES ($id_produits_etrangers,$id_product,1)");
	}
	
}
elseif(Tools::isSubmit('correctionPrix'))
{
	$produits_etrangers = Db::getInstance()->ExecuteS("
		SELECT id_produits_etrangers,prix
		FROM `"._DB_PREFIX_."pricestracker_produits_etrangers`
		WHERE 1=1");
		
		
	foreach($produits_etrangers as $produits_etranger)
	{
		Db::getInstance()->Execute("UPDATE `"._DB_PREFIX_."pricestracker_produits_etrangers` SET prix='".($produits_etranger['prix']/3)."' WHERE id_produits_etrangers=".$produits_etranger['id_produits_etrangers']);
	}
}
elseif(Tools::isSubmit('effacerPrixA0'))
{
		Db::getInstance()->Execute("DELETE FROM `"._DB_PREFIX_."pricestracker_produits_etrangers`  WHERE prix<=0");

}
elseif(Tools::isSubmit('clearHistory'))
{
	if(Tools::getValue('timestamp'))
	{
		Db::getInstance()->Execute('
			DELETE FROM '._DB_PREFIX_.'pricestracker_historique
			WHERE date < '.(int)Tools::getValue('timestamp'));
	}
	if(Tools::getValue('max_age'))
	{
		Db::getInstance()->Execute('
			DELETE FROM '._DB_PREFIX_.'pricestracker_historique
			WHERE date < '.(time() - (int)Tools::getValue('max_age')));
	}
	elseif(Tools::isSubmit('all'))
	{
		Db::getInstance()->Execute('
			DELETE FROM '._DB_PREFIX_.'pricestracker_historique
			WHERE 1=1');
	}
}


	
echo "OK - END";