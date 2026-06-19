<?php
//Initialisation Prestashop page module
include('../../../config/config.inc.php');
require_once('../../../init.php');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
</head>

<body><?php
$id_product=(int)Tools::getValue('id_product');
$id_concurrent=(int)Tools::getValue('id_concurrent');
$url=Tools::getValue('url');

$nomConcurrent = Db::getInstance()->getValue('
SELECT nom
FROM `'._DB_PREFIX_.'pricestracker_concurrents`
WHERE id_concurrents='.$id_concurrent);


//marketplace
$marketplace=Tools::getValue('marketplace');
$identifieur=Tools::getValue('identifieur');

if(strpos($url,'amazon.')!==false)
{
	$marketplace='amazon';
	preg_match("#/([A-Z0-9]{10})(/|$)#sU",$url,$sub);
	$identifieur=$sub[1];
}


if(!$marketplace)
{
	$id_produits_etrangers=Db::getInstance()->getValue('SELECT id_produits_etrangers FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
	WHERE id_concurrents='.$id_concurrent.' AND lien=\''.pSQL($url).'\'');
}
else
{
	$langue=(int)Configuration::get('PS_LANG_DEFAULT');
	//amazon
	if($marketplace=='amazon')
	{
		$delai_amazon=Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."configuration WHERE name='pricestracker_AMAZONDELAI'");
		while($delai_amazon && $delai_amazon>time())
		{
			sleep(1);
			$delai_amazon=Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."configuration WHERE name='pricestracker_AMAZONDELAI'");
		}
		Configuration::updateValue('pricestracker_AMAZONDELAI',time()+1);
		
		require 'libAmazon/AmazonECS.class.php';
		
		defined('AWS_API_KEY') or define('AWS_API_KEY', Configuration::get('AMAZON_AWS_KEY_ID')?Configuration::get('AMAZON_AWS_KEY_ID'):'AKIAJOAOVQ3K3JXRKDJQ' );
		defined('AWS_API_SECRET_KEY') or define('AWS_API_SECRET_KEY',Configuration::get('AMAZON_SECRET_KEY')?Configuration::get('AMAZON_SECRET_KEY'):'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' );
		defined('AWS_ASSOCIATE_TAG') or define('AWS_ASSOCIATE_TAG', 'pricestracker'.date("YmdHis"));
		
		try
		{
			$lg_amz=str_replace('Amazon ','',$nomConcurrent);
			$lg_amz=strtolower($lg_amz);
			if(!$lg_amz) $lg_amz=Language::getIsoById($langue);
			
			$amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, $lg_amz,AWS_ASSOCIATE_TAG);
			// searching again
			$response = $amazonEcs->responseGroup('Small,Images,ItemAttributes,SalesRank,OfferSummary')->category('All')->search( $identifieur );
			//print_r($response->Items->Item);
			
			if($response->Items->Item)
			{
				$item=$response->Items->Item;
				
				$lien_produit=$item->DetailPageURL;
				$rang=$item->SalesRank;
				$image=$item->SmallImage->URL;
				$ean=$item->ItemAttributes->EAN;
				$titre=$item->ItemAttributes->Title;
				$prix=$item->OfferSummary->LowestNewPrice->Amount/100;
				$id=$item->ASIN;
				
				if($lg_amz=='co.uk') $prix=Tools::convertPriceFull($prix, new Currency(3), new Currency(1));
				
				if($lien_produit && $titre)
				{		
					$produit=array(
						'nom'=>$titre.' <span style="font-size:9px">SalesRank:'.$rang.'. EAN:'.$ean.'</span>',
						'image'=>$image,
						'reference'=>$ean,
						'marketplace'=>$id,
						'prix'=>$prix,
						'lien'=>$lien_produit,
						'id_produits_etrangers'=>crc32($lg_amz.$id),
					);
				}
			}
		}
		catch(Exception $e)
		{
		  echo $e->getMessage();
		}
	}

	//insertion
	if($produit)
	{
		Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,reference,marketplace)
		VALUES ('".addslashes($produit['nom'])."','".addslashes($produit['prix'])."','".addslashes($produit['lien'])."','".addslashes($produit['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$id_concurrent."','".time()."','".pSQL($produit['id_produits_etrangers'])."','".pSQL($produit['reference'])."','".pSQL($produit['marketplace'])."')");
		
		$id_produits_etrangers=(int)$produit['id_produits_etrangers'];
	}
}

if($id_produits_etrangers)
{
	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_associations`
	WHERE id_product='.$id_product.' AND id_produits_etrangers='.$id_produits_etrangers);
	
	Db::getInstance()->insert(
		'pricestracker_associations',
		array(
			'id_product'=>$id_product,
			'id_produits_etrangers'=>$id_produits_etrangers,
		)
	);
	echo'1';
}
else
{
	Db::getInstance()->insert(
		'pricestracker_liens_prioritaires',
		array(
			'id_product_a_associer'=>$id_product,
			'lien'=>pSQL($url,true),
			'id_concurrents'=>$id_concurrent,
		)
	);

	echo'0';
}

?></body>
</html>