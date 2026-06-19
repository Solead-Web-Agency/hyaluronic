<?php
/**
/modules/pricestracker/pages/majMarketplace.php


 * PROPERTY
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the idIA Tech License
 * You can't :
 * 1) Modify the source code
 * 2) Sell or distribute this module without our agreement
 * 3) Use this module with more than one domain (1 licence = 1 domain)
 * 4) Divert the source code
 *
 * @author    	idIA Tech
 * @copyright   Copyright (c) 2013 idIA Tech (https://www.idia-tech.com)
 * @license     https://www.storeinterfacer.com/conditions-generales.php idIA Tech (Proprietary)
 * @category    main
 */



//Initialisation Prestashop page module
include('../../../config/config.inc.php');
require_once('../../../init.php');


set_time_limit(0);
ini_set('display_errors', 'on');
ini_set('post_max_size', '32M');
error_reporting(E_ERROR | E_WARNING | E_PARSE);


/*
$Pricestracker=new Pricestracker();
if( $Pricestracker->version2 !='Diamond' ) die('version DIAMOND');
$Pricestracker=false;
*/

require 'libAmazon/AmazonECS.class.php';

defined('AWS_API_KEY') or define('AWS_API_KEY', Configuration::get('AMAZON_AWS_KEY_ID')?Configuration::get('AMAZON_AWS_KEY_ID'):'AKIAJOAOVQ3K3JXRKDJQ' );
defined('AWS_API_SECRET_KEY') or define('AWS_API_SECRET_KEY',Configuration::get('AMAZON_SECRET_KEY')?Configuration::get('AMAZON_SECRET_KEY'):'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' );
defined('AWS_ASSOCIATE_TAG') or define('AWS_ASSOCIATE_TAG', 'pricestracker'.date("YmdHis"));


$langue=(int)Configuration::get('PS_LANG_DEFAULT');



function amazon($asin,$id_pe,$nomConcurrent)
{
	global $langue;
	
	$delai_amazon=Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."configuration WHERE name='pricestracker_AMAZONDELAI'");
	while($delai_amazon && $delai_amazon>time())
	{
		sleep(1);
		$delai_amazon=Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."configuration WHERE name='pricestracker_AMAZONDELAI'");
	}
	Configuration::updateValue('pricestracker_AMAZONDELAI',time()+1);
	
	$produit=array();
	
	try
	{
		$lg_amz=str_replace('Amazon ','',$nomConcurrent);
		$lg_amz=strtolower($lg_amz);
		if(!$lg_amz) $lg_amz=Language::getIsoById($langue);
		
		$amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, $lg_amz,AWS_ASSOCIATE_TAG);
		// searching again
		$response = $amazonEcs->responseGroup('Small,Images,ItemAttributes,SalesRank,OfferSummary')->category('All')->search( $asin );
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

	return array($produit);
}
function buildURLArray ($filterarray) {
  global $urlfilter;
  global $i;
  // Iterate through each filter in the array
  foreach($filterarray as $itemfilter) {
	// Iterate through each key in the filter
	foreach ($itemfilter as $key =>$value) {
	  if(is_array($value)) {
		foreach($value as $j => $content) { // Index the key for each value
		  $urlfilter .= "&itemFilter($i).$key($j)=$content";
		}
	  }
	  else {
		if($value != "") {
		  $urlfilter .= "&itemFilter($i).$key=$value";
		}
	  }
	}
	$i++;
  }
  return "$urlfilter";
} // End of buildURLArray function
function ebay($id_pe,$nomConcurrent)
{
	global $langue;
	
	// API request variables
	$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
	$version = '1.0.0';  // API version supported by your application
	$appid = Configuration::get('pricestracker_Ebay_AppId')?Configuration::get('pricestracker_Ebay_AppId'):'energypa-fluxebay-PRD-52f5e2e0d-d4feaa3d';  // Replace with your own AppID
	$globalid = Configuration::get('pricestracker_Ebay_GlobalID')?Configuration::get('pricestracker_Ebay_GlobalID'):('EBAY-'.strtoupper(Language::getIsoById($langue)));  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
	$query = $id_pe;  // You may want to supply your own query
	$safequery = urlencode($query);  // Make the query URL-friendly
	$i = '0';  // Initialize the item filter index to 0
	
	// Create a PHP array of the item filters you want to use in your request
	$filterarray =
	  array(
		array(
		'name' => 'ListingType',
		'value' => array('AuctionWithBIN','FixedPrice','StoreInventory'),
		'paramName' => '',
		'paramValue' => ''),
	  );
	
	// Generates an indexed URL snippet from the array of item filters
	
	// Build the indexed item filter URL snippet
	buildURLArray($filterarray);
	
	// Construct the findItemsByKeywords HTTP GET call 
	$apicall = "$endpoint?";
	$apicall .= "OPERATION-NAME=findItemsAdvanced";
	$apicall .= "&SERVICE-VERSION=$version";
	$apicall .= "&SECURITY-APPNAME=$appid";
	$apicall .= "&GLOBAL-ID=$globalid";
	$apicall .= "&keywords=".$id_pe;
	$apicall .= "&outputSelector=SellerInfo";
	$apicall .= "&paginationInput.entriesPerPage=7";
	$apicall .= "$urlfilter";
	
	// Load the call and capture the document returned by eBay API
	$resp = simplexml_load_file($apicall);
	
	// Check to see if the request was successful, else print an error
	if ($resp->ack == "Success") {
	  $results = '';
	  
	  $item=$resp->searchResult->item;
		
		$image   = (string)$item->galleryURL;
		$lien_produit  = (string)$item->viewItemURL;
		$titre = $item->title;
		$prix = $item->sellingStatus->convertedCurrentPrice;
		$prix_immediat = $item->listingInfo->convertedBuyItNowPrice;
		$frais=$item->shippingInfo->shippingServiceCost;
		$vendeur=$item->sellerInfo->sellerUserName;
		$id=(string)$item->itemId;
			
		if($lien_produit && $titre)
		{		
			$produits=array(
				'nom'=>$titre.' <span style="font-size:9px">Seller:'.$vendeur.'</span>',
				'image'=>$image,
				'prix'=>$prix.'<span style="font-size:9px">+'.($frais).($prix_immediat?'. Buy Now:'.$prix_immediat:'').'</span>',
				'lien'=>$lien_produit,
				'id_produits_etrangers'=>$id,
			);
		}
	}
		  
	return array($produits);
}

function priceminister($id_pe,$nomConcurrent)
{
	global $langue;

	$resp = simplexml_load_file('https://ws.priceminister.com/listing_ssl_ws?action=listing&login='.(Configuration::get('pricestracker_PM_login')?Configuration::get('pricestracker_PM_login'):'Ceubex').'&pwd='.(Configuration::get('pricestracker_PM_token')?Configuration::get('pricestracker_PM_token'):'a15a388fef514f6b8c7590e5a71b3c71').'&version=2015-07-05&scope=PRICING&nbproductsperpage=10'.
	'&productids='.$id_pe );
	
	if($resp->response->status=='ok')
	{
		$item=$resp->response->products->product;
	
		$image   = $item->image->url;
		$lien_produit  = $item->url;
		$titre = $item->headline;
		$prix = $item->adverts->newadverts->advert->price->amount;
		$frais=$item->adverts->newadverts->advert->shippingcost->amount;
		$prix_occas = $item->adverts->usedadverts->advert->price->amount;
		$frais_occas=$item->adverts->usedadverts->advert->shippingcost->amount;
		$reference=$item->references->barcode;
		$id=$item->productid;
		
		
		if($lien_produit && $titre)
		{		
			$produits=array(
				'nom'=>$titre.($reference?' <span style="font-size:9px">Ref:'.$reference.'</span>':''),
				'image'=>$image,
				'prix'=>$prix.'<span style="font-size:9px">+'.($frais).'</span>'.($prix_occas? '. Occasion:'.$prix_occas.'<span style="font-size:9px">+'.($frais_occas).'</span>':''),
				'lien'=>$lien_produit,
				'reference'=>$reference,
				'id_produits_etrangers'=>$id,
			);
		}

	}
	
	return array($produits);
}

function cdiscount($id_CDisco,$id_pe,$nomConcurrent)
{
	global $langue;

	$url = "https://api.cdiscount.com/OpenApi/json/Search";    
	$content = json_encode(array(
		  "ApiKey"=> Configuration::get('cdiscount_ApiKey')?Configuration::get('cdiscount_ApiKey'):"a10981ae-5361-4902-a625-a2ed78cfb042",
		  "ProductRequest"=> array(
			"ProductIdList"=> trim( $id_CDisco ),
			"Scope"=> array(
			  "Offers"=> true,
			  "AssociatedProducts"=> false,
			  "Images"=> true,
			  "Ean"=> true
			)
		  )
	));
	
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	
	$json_response = curl_exec($curl);
	
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	if ( $status != 201 && $status != 200 ) {
		echo("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}
	
	
	curl_close($curl);
	
	$response = json_decode($json_response, true);
	
	$produits_req=$response['Products'];

	$item=$produits_req[0];
				
	$lien_produit=$item['BestOffer']['ProductURL'];
	$image=$item['MainImageUrl'];
	$ean=$item['Ean'];
	$titre=$item['Name'];
	$prix=$item['BestOffer']['SalePrice'];
	$id=$item['Id'];
	
	if($lien_produit && $titre)
	{		
		$produits=array(
			'nom'=>$titre,
			'image'=>$image,
			'reference'=>$ean,
			'prix'=>$prix,
			'lien'=>$lien_produit,
			'marketplace'=>$id,
			'id_produits_etrangers'=>crc32($id),
		);
	}
	
	return array($produits);
}


$concurrents = Db::getInstance()->executeS('
SELECT nom,id_concurrents
FROM `'._DB_PREFIX_.'pricestracker_concurrents`
WHERE 1=1');
foreach($concurrents as $concurrent)
{
	$produits = Db::getInstance()->executeS('
	SELECT PE.id_produits_etrangers,PE.marketplace
	FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE,`'._DB_PREFIX_.'pricestracker_associations` A
	WHERE id_concurrents='.$concurrent['id_concurrents'].' AND A.id_produits_etrangers=PE.id_produits_etrangers
	GROUP BY PE.id_produits_etrangers' );		

	
	foreach($produits as $produit)
	{
		//amazon
		if(strpos($concurrent['nom'],'Amazon')!==FALSE)
		{
			$inserer=amazon($produit['marketplace'],$produit['id_produits_etrangers'],$concurrent['nom']);
		
			if($inserer) echo $produit['marketplace'].','.$produit['id_produits_etrangers'].','.$concurrent['nom'].'OK<br>';
			else echo $produit['marketplace'].','.$produit['id_produits_etrangers'].','.$concurrent['nom'].'FAIL<br>';
			
	
			//insertion
			foreach($inserer as $ins)
			{
				Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,reference,marketplace)
				VALUES ('".addslashes($ins['nom'])."','".addslashes($ins['prix'])."','".addslashes($ins['lien'])."','".addslashes($ins['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$concurrent['id_concurrents']."','".time()."','".pSQL($ins['id_produits_etrangers'])."','".pSQL($ins['reference'])."','".pSQL($ins['marketplace'])."')");
	
				echo '-> '.$ins['nom'].','.$ins['prix'].','.$ins['lien'].','.$ins['reference'].','.$ins['marketplace'].'OK<br>';
			}
		}
		//ebay
		elseif(strpos($concurrent['nom'],'Ebay')!==FALSE)
		{
			$inserer=ebay($produit['id_produits_etrangers'],$concurrent['nom']);
		
			if($inserer) echo $produit['id_produits_etrangers'].','.$concurrent['nom'].'OK<br>';
			else echo $produit['id_produits_etrangers'].','.$concurrent['nom'].'FAIL<br>';
			
		
			//insertion
			foreach($inserer as $ins)
			{
				Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,reference,marketplace)
				VALUES ('".addslashes($ins['nom'])."','".addslashes($ins['prix'])."','".addslashes($ins['lien'])."','".addslashes($ins['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$concurrent['id_concurrents']."','".time()."','".pSQL($ins['id_produits_etrangers'])."','".pSQL($ins['reference'])."','".pSQL($ins['marketplace'])."')");
				echo '-> '.$ins['nom'].','.$ins['prix'].','.$ins['lien'].','.$ins['reference'].','.$ins['marketplace'].'<br>';
	
			}
		}
		//priceminister
		if(strpos($concurrent['nom'],'PriceMinister')!==FALSE)
		{
			$inserer=priceminister($produit['id_produits_etrangers'],$concurrent['nom']);
		
			if($inserer) echo $produit['id_produits_etrangers'].','.$concurrent['nom'].'OK<br>';
			else echo $produit['id_produits_etrangers'].','.$concurrent['nom'].'FAIL<br>';
			
			//insertion
			foreach($inserer as $ins)
			{
				Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,reference,marketplace)
				VALUES ('".addslashes($ins['nom'])."','".addslashes($ins['prix'])."','".addslashes($ins['lien'])."','".addslashes($ins['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$concurrent['id_concurrents']."','".time()."','".pSQL($ins['id_produits_etrangers'])."','".pSQL($ins['reference'])."','".pSQL($ins['marketplace'])."')");
				echo '-> '.$ins['nom'].','.$ins['prix'].','.$ins['lien'].','.$ins['reference'].','.$ins['marketplace'].'<br>';
	
			}
		}
		//CDiscount
		/*if(strpos($concurrent['nom'],'CDiscount')!==FALSE)
		{
			$inserer=cdiscount($produit['marketplace'],$produit['id_produits_etrangers'],$concurrent['nom']);
		
			if($inserer) echo $produit['marketplace'].','.$produit['id_produits_etrangers'].','.$concurrent['nom'].'OK<br>';
			else echo $produit['marketplace'].','.$produit['id_produits_etrangers'].','.$concurrent['nom'].'FAIL<br>';
			
			//insertion
			foreach($inserer as $ins)
			{
				Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,reference,marketplace)
				VALUES ('".addslashes($ins['nom'])."','".addslashes($ins['prix'])."','".addslashes($ins['lien'])."','".addslashes($ins['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$concurrent['id_concurrents']."','".time()."','".pSQL($ins['id_produits_etrangers'])."','".pSQL($ins['reference'])."','".pSQL($ins['marketplace'])."')");
				echo '-> '.$ins['nom'].','.$ins['prix'].','.$ins['lien'].','.$ins['reference'].','.$ins['marketplace'].'<br>';
	
			}
		}*/
	}
}



echo 'OK';