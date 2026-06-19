<?php
/** * PROPERTY
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

class PricestrackerExecuteProxModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{

		set_time_limit(0);
		
		
		
		
		@error_reporting(E_ERROR | E_WARNING | E_PARSE);
		@ini_set('display_errors', 'on');
		@ini_set('memory_limit', '-1');
		
		
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
		
		
		// Generates an indexed URL snippet from the array of item filters
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
		
		function ebay($nomProduct,$id_product,$id_concurrents,$nomConcurrent)
		{
			global $langue;
			
			$produits=array();
			// API request variables
			$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
			$version = '1.0.0';  // API version supported by your application
			$appid = Configuration::get('pricestracker_Ebay_AppId')?Configuration::get('pricestracker_Ebay_AppId'):'energypa-fluxebay-PRD-52f5e2e0d-d4feaa3d';  // Replace with your own AppID
			$globalid = Configuration::get('pricestracker_Ebay_GlobalID')?Configuration::get('pricestracker_Ebay_GlobalID'):('EBAY-'.strtoupper(Language::getIsoById($langue)));  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
			$query =$nomProduct;  // You may want to supply your own query
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
			
			
			// Build the indexed item filter URL snippet
			buildURLArray($filterarray);
			
			// Construct the findItemsByKeywords HTTP GET call 
			$apicall = "$endpoint?";
			$apicall .= "OPERATION-NAME=findItemsByKeywords";
			$apicall .= "&SERVICE-VERSION=$version";
			$apicall .= "&SECURITY-APPNAME=$appid";
			$apicall .= "&GLOBAL-ID=$globalid";
			$apicall .= "&keywords=$safequery";
			$apicall .= "&outputSelector=SellerInfo";
			$apicall .= "&paginationInput.entriesPerPage=7";
			$apicall .= "$urlfilter";
			
			// Load the call and capture the document returned by eBay API
			$resp = simplexml_load_file($apicall);
			
			// Check to see if the request was successful, else print an error
			if ($resp->ack == "Success") {
			  $results = '';
			  // If the response was loaded, parse it and build links  
			  foreach($resp->searchResult->item as $item) {
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
					$produits[]=array(
						'nom'=>$titre.' <span style="font-size:9px">Seller:'.$vendeur.'</span>',
						'image'=>$image,
						'prix'=>$prix.'<span style="font-size:9px">+'.($frais).($prix_immediat?'. Buy Now:'.$prix_immediat:'').'</span>',
						'lien'=>$lien_produit,
						'id_produits_etrangers'=>$id,
					);
				}
				
				//print_r($item);
				 //echo "$lien ; $frais ; $image ; $prix ; $prix_immediat ; $titre ; $id ; $vendeur<br>";
			  }
			  if(!is_array($resp->searchResult->item))
			  {
				$item=$resp->searchResult->item;
				if($item)
				{
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
						$produits[]=array(
							'nom'=>$titre.' <span style="font-size:9px">Vendeur:'.$vendeur.'</span>',
							'image'=>$image,
							'prix'=>$prix.'<span style="font-size:9px">+'.($frais).($prix_immediat?'. Immédiat:'.$prix_immediat:'').'</span>',
							'lien'=>$lien_produit,
							'id_produits_etrangers'=>$id,
							'marketplace'=>$id,
						);
					}
				}
				//print_r($item);
				 //echo "$lien ; $frais ; $image ; $prix ; $prix_immediat ; $titre ; $id ; $vendeur<br>";
			  }
			}
			
			
			
			//insertion
			$iProx=1;
			if(!$produits) return;
			foreach($produits as $produit)
			{
				Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,marketplace)
				VALUES ('".addslashes($produit['nom'])."','".addslashes($produit['prix'])."','".addslashes($produit['lien'])."','".addslashes($produit['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$id_concurrents."','".time()."','".pSQL($produit['id_produits_etrangers'])."','".pSQL($produit['marketplace'])."')");
		
				$id_produits_etrangers=$produit['id_produits_etrangers'];
		
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_proximites`
				WHERE id_product='.$id_product.' AND id_produits_etrangers='.$id_produits_etrangers.' AND id_concurrents='.$id_concurrents);
				
				Db::getInstance()->insert(
					'pricestracker_proximites',
					array(
						'id_product'=>$id_product,
						'id_concurrents'=>$id_concurrents,
						'proximite'=>101-$iProx,
						'id_produits_etrangers'=>$id_produits_etrangers,
					)
				);
				$iProx++;
			}
		
		}
		
		function cdiscount($ean13,$nomProduct,$id_product,$id_concurrents,$nomConcurrent)
		{
			$url = "https://api.cdiscount.com/OpenApi/json/Search";    
			$content = json_encode(array(
				  "ApiKey"=> Configuration::get('cdiscount_ApiKey')?Configuration::get('cdiscount_ApiKey'):"a10981ae-5361-4902-a625-a2ed78cfb042",
				  "SearchRequest"=> array(
					"Keyword"=> trim( $ean13?$ean13:$nomProduct ),
					"SortBy"=> "relevance",
					"Pagination"=> array(
					  "ItemsPerPage"=> 10,
					  "PageNumber"=> 1
					),
					"Filters"=> array(
					  "Price"=> array(),
					  "IncludeMarketPlace"=> true
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
		
			foreach($produits_req as $item)
			{
						
				$lien_produit=$item['BestOffer']['ProductURL'];
				$image=$item['MainImageUrl'];
				$ean=$item['Ean'];
				$titre=$item['Name'];
				$prix=$item['BestOffer']['SalePrice'];
				$id=$item['Id'];
				
				if($lien_produit && $titre)
				{		
					$produits[]=array(
						'nom'=>$titre,
						'image'=>$image,
						'reference'=>$ean,
						'prix'=>$prix,
						'lien'=>$lien_produit,
						'marketplace'=>$id,
						'id_produits_etrangers'=>crc32($id),
					);
				}
			}
			
			
			
			//insertion
			$iProx=1;
			if(!$produits) return;
			foreach($produits as $produit)
			{
				Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,marketplace)
				VALUES ('".addslashes($produit['nom'])."','".addslashes($produit['prix'])."','".addslashes($produit['lien'])."','".addslashes($produit['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$id_concurrents."','".time()."','".pSQL($produit['id_produits_etrangers'])."','".pSQL($produit['marketplace'])."')");
		
				$id_produits_etrangers=$produit['id_produits_etrangers'];
		
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_proximites`
				WHERE id_product='.$id_product.' AND id_produits_etrangers='.$id_produits_etrangers.' AND id_concurrents='.$id_concurrents);
				
				Db::getInstance()->insert(
					'pricestracker_proximites',
					array(
						'id_product'=>$id_product,
						'id_concurrents'=>$id_concurrents,
						'proximite'=>101-$iProx,
						'id_produits_etrangers'=>$id_produits_etrangers,
					)
				);
				$iProx++;
			}
		}
		
		function priceminister($ean13,$nomProduct,$id_product,$id_concurrents,$nomConcurrent)
		{
			$resp = simplexml_load_file('https://ws.priceminister.com/listing_ssl_ws?action=listing&login='.(Configuration::get('pricestracker_PM_login')?Configuration::get('pricestracker_PM_login'):'Ceubex').'&pwd='.(Configuration::get('pricestracker_PM_token')?Configuration::get('pricestracker_PM_token'):'a15a388fef514f6b8c7590e5a71b3c71').'&version=2015-07-05&scope=PRICING&nbproductsperpage=10'.
			( $ean13  ? '&refs='.urlencode($ean13)  :  '&kw='.urlencode(trim($nomProduct)) ));
			
			if($resp->response->status=='ok')
			{
				foreach($resp->response->products->product as $item) {
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
						$produits[]=array(
							'nom'=>$titre.($reference?' <span style="font-size:9px">Ref:'.$reference.'</span>':''),
							'image'=>$image,
							'prix'=>$prix.'<span style="font-size:9px">+'.($frais).'</span>'.($prix_occas? '. Occasion:'.$prix_occas.'<span style="font-size:9px">+'.($frais_occas).'</span>':''),
							'lien'=>$lien_produit,
							'reference'=>$reference,
							'id_produits_etrangers'=>$id,
						);
					}
			
					//print_r($item);
					//echo "$lien ; $image ; $titre ; $prix ; $frais ; $prix_occas ; $frais_occas ; $id ; $reference<br>";
				}
				if(!$produits && !is_array($resp->response->products->product))
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
						$produits[]=array(
							'nom'=>$titre.($reference?' <span style="font-size:9px">Ref:'.$reference.'</span>':''),
							'image'=>$image,
							'prix'=>$prix.'<span style="font-size:9px">+'.($frais).'</span>'.($prix_occas? '. Occasion:'.$prix_occas.'<span style="font-size:9px">+'.($frais_occas).'</span>':''),
							'lien'=>$lien_produit,
							'reference'=>$reference,
							'id_produits_etrangers'=>$id,
						);
					}
			
					//print_r($item);
					//echo "$lien ; $image ; $titre ; $prix ; $frais ; $prix_occas ; $frais_occas ; $id ; $reference<br>";
				}
				
				
			}
			
			
			//insertion
			$iProx=1;
			if(!$produits) return;
			foreach($produits as $produit)
			{
				Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,marketplace)
				VALUES ('".addslashes($produit['nom'])."','".addslashes($produit['prix'])."','".addslashes($produit['lien'])."','".addslashes($produit['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$id_concurrents."','".time()."','".pSQL($produit['id_produits_etrangers'])."','".pSQL($produit['marketplace'])."')");
		
				$id_produits_etrangers=$produit['id_produits_etrangers'];
		
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_proximites`
				WHERE id_product='.$id_product.' AND id_produits_etrangers='.$id_produits_etrangers.' AND id_concurrents='.$id_concurrents);
				
				Db::getInstance()->insert(
					'pricestracker_proximites',
					array(
						'id_product'=>$id_product,
						'id_concurrents'=>$id_concurrents,
						'proximite'=>101-$iProx,
						'id_produits_etrangers'=>$id_produits_etrangers,
					)
				);
				$iProx++;
			}
		}
		
		function amazon($ean13,$nomProduct,$id_product,$id_concurrents,$nomConcurrent)
		{
			global $langue;
			$delai_amazon=Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."configuration WHERE name='pricestracker_AMAZONDELAI'");
			//echo $delai_amazon.' && '.$delai_amazon.'>'.time();die;
			while($delai_amazon && $delai_amazon>time())
			{
				sleep(1);
				$delai_amazon=Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."configuration WHERE name='pricestracker_AMAZONDELAI'");
			}
			Configuration::updateValue('pricestracker_AMAZONDELAI',time()+1);
			
			$produits=array();
			
			try
			{
				$lg_amz=str_replace('Amazon ','',$nomConcurrent);
				$lg_amz=strtolower($lg_amz);
				if(!$lg_amz) $lg_amz=Language::getIsoById($langue);
				
				$amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, $lg_amz,AWS_ASSOCIATE_TAG);
				// searching again
				$response = $amazonEcs->responseGroup('Small,Images,ItemAttributes,SalesRank,OfferSummary')->category('All')->search( trim($ean13?$ean13:$nomProduct) );
				//print_r($response->Items->Item);
				
				if(is_array($response->Items->Item))
				{
					foreach($response->Items->Item as $item)
					{
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
							$produits[]=array(
								'nom'=>$titre.' <span style="font-size:9px">SalesRank:'.$rang.'. EAN:'.$ean.'</span>',
								'image'=>$image,
								'reference'=>$ean,
								'marketplace'=>$id,
								'prix'=>$prix,
								'lien'=>$lien_produit,
								'id_produits_etrangers'=>crc32($lg_amz.$id),
							);
						}
						
						//echo "$lien ; $rang ; $image ; $ean ; $prix ; $titre ; $id<br>";
					}
				}
				elseif($response->Items->Item) //si un seul
				{
					if(!is_array($response->Items->Item))
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
							$produits[]=array(
								'nom'=>$titre.' <span style="font-size:9px">SalesRank:'.$rang.'. EAN:'.$ean.'</span>',
								'image'=>$image,
								'marketplace'=>$id,
								'reference'=>$ean,
								'prix'=>$prix,
								'lien'=>$lien_produit,
								'id_produits_etrangers'=>crc32($lg_amz.$id),
							);
						}
					}
				}
				
			}
			catch(Exception $e)
			{
			  echo $e->getMessage();
			}	
		
			//insertion
			$iProx=1;
			if(!$produits) return;
			foreach($produits as $produit)
			{
				Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,reference,marketplace)
				VALUES ('".addslashes($produit['nom'])."','".addslashes($produit['prix'])."','".addslashes($produit['lien'])."','".addslashes($produit['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$id_concurrents."','".time()."','".pSQL($produit['id_produits_etrangers'])."','".pSQL($produit['reference'])."','".pSQL($produit['marketplace'])."')");
		
				$id_produits_etrangers=$produit['id_produits_etrangers'];
		
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_proximites`
				WHERE id_product='.$id_product.' AND id_produits_etrangers='.$id_produits_etrangers.' AND id_concurrents='.$id_concurrents);
				
				Db::getInstance()->insert(
					'pricestracker_proximites',
					array(
						'id_product'=>$id_product,
						'id_concurrents'=>$id_concurrents,
						'proximite'=>101-$iProx,
						'id_produits_etrangers'=>$id_produits_etrangers,
					)
				);
				$iProx++;
			}
		
		}
		
		
		$concurrents = Db::getInstance()->executeS('
		SELECT nom,id_concurrents
		FROM `'._DB_PREFIX_.'pricestracker_concurrents`
		WHERE 1=1');
		$tabConcurrents=array();
		foreach($concurrents as $c)
		{
			$tabConcurrents[$c['id_concurrents']]=$c['nom'];
		}
		
		
		$concurrents_autorises=Tools::getValue('id_concurrents')? explode(',',Tools::getValue('id_concurrents')):false;
		
		$produits = Db::getInstance()->executeS('
		SELECT P.id_product
		FROM `'._DB_PREFIX_.'product` P '.(Tools::getValue('id_favoris')?' JOIN `'._DB_PREFIX_.'pricestracker_favoris_product` F ON F.id_product=P.id_product':'').' LEFT JOIN `'._DB_PREFIX_.'category_product` CP ON P.id_product=CP.id_product
		WHERE 1=1 '.(Tools::getValue('id_category')?" AND CP.id_category=".((int)Tools::getValue('id_category')):'').' 
			'.(Tools::getValue('id_favoris')?' AND F.id_favoris='.((int)Tools::getValue('id_favoris')):'').'
		GROUP BY P.id_product');
		
		
		
		$ids_product=array();
		foreach($produits as $produit)
		{
			$id_product=$produit['id_product'];
			$ids_product[]=$id_product;
			
		
			if(Tools::getValue('supprAncien'))
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_associations`
				WHERE id_product='.$id_product);
			}	
			
		}
		
		
		$concurrents_nb_asso=array();	
		foreach($tabConcurrents as $id_concurrents=>$nom_concurrent)
		{
			if($concurrents_autorises && !in_array($id_concurrents,$concurrents_autorises)) continue;
			
			//insertion initiale
			$produits_market = Db::getInstance()->executeS('
			SELECT P.id_product AS id_product,P.ean13 AS ean13,L.name AS nom
			FROM `'._DB_PREFIX_.'product_lang` L JOIN `'._DB_PREFIX_.'product` P ON L.id_product=P.id_product '.(Tools::getValue('id_favoris')?' JOIN `'._DB_PREFIX_.'pricestracker_favoris_product` F ON F.id_product=P.id_product':'').' LEFT JOIN `'._DB_PREFIX_.'category_product` CP ON P.id_product=CP.id_product
			WHERE L.id_lang='.$langue.' '.(Tools::getValue('id_category')?" AND CP.id_category=".((int)Tools::getValue('id_category')):'').' 
			'.(Tools::getValue('id_favoris')?' AND F.id_favoris='.((int)Tools::getValue('id_favoris')):'').'
			GROUP BY P.id_product');
				
			if(strpos($nom_concurrent,'Amazon')!==FALSE)
			{
				foreach($produits_market as $pm)
				{
					amazon($pm['ean13'],$pm['nom'],$pm['id_product'],$id_concurrents,$nom_concurrent);
				}
			}
			elseif(strpos($nom_concurrent,'Ebay')!==FALSE)
			{
				foreach($produits_market as $pm)
				{
					ebay($pm['nom'],$pm['id_product'],$id_concurrents,$nom_concurrent);
				}
			}
			elseif(strpos($nom_concurrent,'PriceMinister')!==FALSE)
			{
				foreach($produits_market as $pm)
				{
					priceminister($pm['ean13'],$pm['nom'],$pm['id_product'],$id_concurrents,$nom_concurrent);
				}
			}
		/*	elseif(strpos($nom_concurrent,'CDiscount')!==FALSE)
			{
				foreach($produits_market as $pm)
				{
					cdiscount($pm['ean13'],$pm['nom'],$pm['id_product'],$id_concurrents,$nom_concurrent);
				}
			}*/
			
			$produitsProx_sql = Db::getInstance()->executeS('
			SELECT PE.id_produits_etrangers AS id_produits_etrangers,P.proximite AS proximite,P.id_product AS id_product
			FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE,`'._DB_PREFIX_.'pricestracker_proximites` P
			WHERE PE.id_concurrents='.((int)$id_concurrents).' AND P.id_product IN ('.implode(',',$ids_product).') AND P.id_produits_etrangers=PE.id_produits_etrangers 
				AND P.proximite>='.((float)Tools::getValue('min_proximite')).'
				'.(Tools::getValue('just_nvo')?' AND NOT EXISTS (SELECT A.id_product FROM `'._DB_PREFIX_.'pricestracker_associations` A,`'._DB_PREFIX_.'pricestracker_produits_etrangers` PE2 WHERE A.id_product=P.id_product AND PE2.id_produits_etrangers=A.id_produits_etrangers AND PE2.id_concurrents='.(int)$id_concurrents.')':'').'
			GROUP BY PE.id_produits_etrangers,P.id_product
			ORDER BY '.(Tools::getValue('max_asso_pe')?'':'P.id_product,').'P.proximite DESC');
			
			//print_r($produitsProx_sql);die;
			
			
			$id_product_courant=-1;
			foreach($produitsProx_sql as $pProx)
			{
				$id_product=$pProx['id_product'];
				$produitsProx=$pProx['id_produits_etrangers'];
				
				if($id_product!=$id_product_courant)
				{
					$id_product_courant=$id_product;
					$concurrents_nb_asso=array();
				}
				
				
				if(Tools::getValue('max_asso_pe') )
				{
					$nb_asso_pe=Db::getInstance()->getValue('SELECT COUNT(id_associations)	FROM `'._DB_PREFIX_.'pricestracker_associations`	WHERE id_produits_etrangers='.$produitsProx);
					//echo '1::'.$nb_asso_pe.'>='.Tools::getValue('max_asso_pe');
					if($nb_asso_pe>=Tools::getValue('max_asso_pe')) continue;
					
					if(Tools::getValue('max_produits'))
					{
						$nb_asso_produit=Db::getInstance()->getValue('SELECT COUNT(id_associations)	FROM `'._DB_PREFIX_.'pricestracker_associations`	WHERE id_product='.$id_product);
						//echo '2::'.Tools::getValue('max_produits').'<='.$nb_asso_produit;
						if(Tools::getValue('max_produits')<=$nb_asso_produit) continue;
					}
				}
				elseif(Tools::getValue('max_produits') && isset($concurrents_nb_asso[ $id_concurrents ]) && Tools::getValue('max_produits')<=$concurrents_nb_asso[ $id_concurrents ])
				{
					//echo'3::'.Tools::getValue('max_produits').' && '.isset($concurrents_nb_asso[ $id_concurrents ]).' && '.Tools::getValue('max_produits').'<='.$concurrents_nb_asso[ $id_concurrents ];
					continue;
				}
		
				if(Tools::getValue('max_suggestion') )
				{
					$sugg_plus_proches=Db::getInstance()->getValue('SELECT COUNT(id_proximites)	FROM `'._DB_PREFIX_.'pricestracker_proximites` WHERE id_product='.$id_product.' AND id_concurrents='.$id_concurrents.' AND proximite>'.$pProx['proximite']);
					if($sugg_plus_proches>=Tools::getValue('max_suggestion')) continue;
				}
		
				
				$exist=Db::getInstance()->getValue('SELECT COUNT(id_associations)	FROM `'._DB_PREFIX_.'pricestracker_associations`	WHERE id_product='.$id_product.' AND id_produits_etrangers='.$produitsProx);
				//echo 'exist:id_product='.$id_product.' AND id_produits_etrangers='.$produitsProx.'--->'.$exist;
				if(!$exist)
				{
					//echo 'insersion';
					Db::getInstance()->insert(
						'pricestracker_associations',
						array(
							'id_product'=>$id_product,
							'id_produits_etrangers'=>$produitsProx,
							'automatique'=>1,
						)
					);
				}
				@$concurrents_nb_asso[ $id_concurrents ]++;
			}
		}
		
		
		echo 'OK';
	}
}