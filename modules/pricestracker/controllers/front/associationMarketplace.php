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

class PricestrackerExecuteRulesModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{

		?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<!--
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
-->
<style>
.actif {
	-moz-box-shadow:inset 0px 1px 0px 0px #a4e271 !important;
	-webkit-box-shadow:inset 0px 1px 0px 0px #a4e271 !important;
	box-shadow:inset 0px 1px 0px 0px #a4e271 !important;
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #89c403), color-stop(1, #77a809)) !important;
	background:-moz-linear-gradient(top, #89c403 5%, #77a809100%) !important;
	background:-webkit-linear-gradient(top, #89c403 5%, #77a809 100%) !important;
	background:-o-linear-gradient(top, #89c403 5%, #77a809 100%) !important;
	background:-ms-linear-gradient(top, #89c403 5%, #77a809 100%) !important;
	background:linear-gradient(to bottom, #89c403 5%, #77a809 100%) !important;
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#89c403', endColorstr='#77a809',GradientType=0) !important;
	background-color:#89c403 !important;
	-moz-border-radius:6px !important;
	-webkit-border-radius:6px !important;
	border-radius:6px !important;
	border:1px solid #74b807 !important;
	display:inline-block !important;
	color:#ffffff !important;
	font-family:arial !important;
	font-size:11px !important;
	font-weight:bold !important;
	padding:3px 3px !important;
	text-decoration:none !important;
	text-shadow:0px 1px 0px #528009 !important;
}
.actif:hover {
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #77a809), color-stop(1, #89c403)) !important;
	background:-moz-linear-gradient(top, #77a809 5%, #89c403 100%) !important;
	background:-webkit-linear-gradient(top, #77a809 5%, #89c403 100%) !important;
	background:-o-linear-gradient(top, #77a809 5%, #89c403 100%) !important;
	background:-ms-linear-gradient(top, #77a809 5%, #89c403 100%) !important;
	background:linear-gradient(to bottom, #77a809 5%, #89c403 100%) !important;
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77a809', endColorstr='#89c403',GradientType=0) !important;
	background-color:#77a809 !important;
}
.actif:active {
	position:relative !important;
	top:1px !important;
}
.passif {
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf));
	background:-moz-linear-gradient(top, #ededed 5%, #dfdfdf100%);
	background:-webkit-linear-gradient(top, #ededed 5%, #dfdfdf 100%);
	background:-o-linear-gradient(top, #ededed 5%, #dfdfdf 100%);
	background:-ms-linear-gradient(top, #ededed 5%, #dfdfdf 100%);
	background:linear-gradient(to bottom, #ededed 5%, #dfdfdf 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf',GradientType=0);
	background-color:#ededed;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:6px;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#777777;
	font-family:arial;
	font-size:11px;
	font-weight:bold;
	padding:3px 3px;
	text-decoration:none;
	text-shadow:0px 1px 0px #ffffff;
}
.passif:hover {
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #dfdfdf), color-stop(1, #ededed));
	background:-moz-linear-gradient(top, #dfdfdf 5%, #ededed 100%);
	background:-webkit-linear-gradient(top, #dfdfdf 5%, #ededed 100%);
	background:-o-linear-gradient(top, #dfdfdf 5%, #ededed 100%);
	background:-ms-linear-gradient(top, #dfdfdf 5%, #ededed 100%);
	background:linear-gradient(to bottom, #dfdfdf 5%, #ededed 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#dfdfdf', endColorstr='#ededed',GradientType=0);
	background-color:#dfdfdf;
}
.passif:active {
	position:relative;
	top:1px;
}
.proximite {
	-moz-box-shadow:inset 0px 1px 0px 0px #dcecfb;
	-webkit-box-shadow:inset 0px 1px 0px 0px #dcecfb;
	box-shadow:inset 0px 1px 0px 0px #dcecfb;
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #bddbfa), color-stop(1, #80b5ea));
	background:-moz-linear-gradient(top, #bddbfa 5%, #80b5ea 100%);
	background:-webkit-linear-gradient(top, #bddbfa 5%, #80b5ea 100%);
	background:-o-linear-gradient(top, #bddbfa 5%, #80b5ea 100%);
	background:-ms-linear-gradient(top, #bddbfa 5%, #80b5ea 100%);
	background:linear-gradient(to bottom, #bddbfa 5%, #80b5ea 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#bddbfa', endColorstr='#80b5ea',GradientType=0);
	background-color:#bddbfa;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:6px;
	border:1px solid #84bbf3;
	display:inline-block;
	cursor:pointer;
	color:rgba(47, 47, 47, 1);
	font-family:arial;
	font-size:11px;
	font-weight:bold;
	padding:3px 3px;
	text-decoration:none;
	text-shadow:none;
}
.proximite:hover {
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #80b5ea), color-stop(1, #bddbfa));
	background:-moz-linear-gradient(top, #80b5ea 5%, #bddbfa 100%);
	background:-webkit-linear-gradient(top, #80b5ea 5%, #bddbfa 100%);
	background:-o-linear-gradient(top, #80b5ea 5%, #bddbfa 100%);
	background:-ms-linear-gradient(top, #80b5ea 5%, #bddbfa 100%);
	background:linear-gradient(to bottom, #80b5ea 5%, #bddbfa 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#80b5ea', endColorstr='#bddbfa',GradientType=0);
	background-color:#80b5ea;
}
.proximite:active {
	position:relative;
	top:1px;
}


body
{
	font-size:10px;
	font-family:Arial, Helvetica, sans-serif;
}

</style>

<script src="<?php $jq=Media::getJqueryPath(); echo $jq[0]; ?>"></script>

</head>

<body>
<script>
function cacherImg()
{
	$('#agrandissement').hide();
}
function afficherImg(img)
{
	$('#agrandissementImg').attr('src',img);
	$('#agrandissement').css('top',window.pageYOffset);
	$('#agrandissement').show();
}
</script>

<a name="top"></a>
<?php

function bloc($produit,$id_produits_etrangers_deja_associes,$lien,$id_product,$isProximite=false,$monnaie=1)
{
	$associer=false;
	if(in_array($produit['id_produits_etrangers'],$id_produits_etrangers_deja_associes)) $associer=true;
	
	echo'<a id="" href="#" class="l'.$produit['id_produits_etrangers'].' '.($associer?'actif':'passif').' '.($isProximite?'proximite':'').'" onclick="isActif=false;  if($(this).hasClass(\'actif\')) isActif=true; $.ajax(\''.$lien.'&associationAjax&id_product='.$id_product.'&id_produits_etrangers='.$produit['id_produits_etrangers'].'&associer=\'+(isActif?0:1)).done(function( data ) { $(\'.l'.$produit['id_produits_etrangers'].'\').removeClass(\'passif\'); $(\'.l'.$produit['id_produits_etrangers'].'\').removeClass(\'actif\'); if(data==1) $(\'.l'.$produit['id_produits_etrangers'].'\').addClass(\'actif\'); else $(\'.l'.$produit['id_produits_etrangers'].'\').addClass(\'passif\'); }); return false;"'.($produit['image']?' onmouseover="afficherImg(\''.$produit['image'].'\')" onmouseout="cacherImg()"':'').'>'.($produit['image']?'<img src="'.$produit['image'].'" height="20" align="absmiddle">':'').$produit['nom'].' - '.Tools::displayPrice($produit['prix'],$monnaie).'</a><a href="'.$produit['lien'].'" target="_blank" class="passif"><img src="'.Tools::getAdminUrl().'/modules/pricestracker/pages/images/external.png" border="0" /></a> &nbsp;
	';
}

function stripAccents($string){
	$accents = array('À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö',
	'Ù','Ú','Û','Ü','Ý','à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ð','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ');
	$sans = array('A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','O','O','O','O','O',
	'U','U','U','U','Y','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','o','u','u','u','u','y','y');
	return str_replace($accents, $sans, $string);
}

function pagesProposer($courante,$max,$lien,$style='')
{
	$page=1;
	$html='';
	//debut
	for(;$page<5 && $page<=$max;$page++) $html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
	//autour de courante
	if($courante>$page-2)
	{
		$ancien=$page;
		$page=max($page,$courante-1);
		if($ancien!=$page) $html.='... ';
		for($iPage=0;$iPage<4 && $page<=$max;$page++)
		{
			$html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
			$iPage++;
		}
	}
	//milieu
	$milieu=round($max/2);
	if($milieu>$page-2)
	{
		$ancien=$page;
		$page=max($page,$milieu-1);
		if($ancien!=$page) $html.='... ';
		for($iPage=0;$iPage<3 && $page<=$max;$page++)
		{
			$html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
			$iPage++;
		}
	}
	//autour de courante
	if($courante>$page-2)
	{
		$ancien=$page;
		$page=max($page,$courante-1);
		if($ancien!=$page) $html.='... ';
		for($iPage=0;$iPage<4 && $page<=$max;$page++)
		{
			$html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
			$iPage++;
		}
	}
	//fin
	if($max>$page)
	{
		$ancien=$page;
		$page=max($page,$max-3);
		if($ancien!=$page) $html.='... ';
		for(;$page<=$max;$page++) $html.='<a style="'.$style.'" href="'.$lien.$page.'">'.($page==$courante?'<strong>':'').$page.($page==$courante?'</strong>':'').'</a> ';
	}
	
	return $html;
}

$page=(int)Tools::getValue('page');
if(!$page) $page=1;


$id_product=(int)Tools::getValue('id_product');
$id_concurrents=(int)Tools::getValue('id_concurrents');
$product=new Product($id_product);



//places de marchés



$produits_etrangers_deja_associes = Db::getInstance()->executeS('
SELECT id_produits_etrangers
FROM `'._DB_PREFIX_.'pricestracker_associations`
WHERE id_product='.$id_product
);
$id_produits_etrangers_deja_associes=array();
foreach($produits_etrangers_deja_associes as $produits_etranger)
{
	$id_produits_etrangers_deja_associes[]=$produits_etranger['id_produits_etrangers'];
}

$rech=pSQL(str_replace('--PLUS--','+',utf8_encode(Tools::getValue('rech'))));


$nbPages=1;


//pages
echo'<div style="overflow:auto;max-height:40px">Pages: ';
echo pagesProposer($page,$nbPages,$lien.'&association&id_concurrents='.$id_concurrents.'&id_product='.$id_product.'&rech='.urlencode(trim(stripslashes(Tools::getValue('rech')))).'&page=');
echo' <a href="'.$lien.'&association&id_concurrents='.$id_concurrents.'&id_product='.$id_product.'&rech='.urlencode(trim(stripslashes(Tools::getValue('rech')))).'&page=asso">Associations</a> ';
echo'</div>';


//catalogue etranger
$order='';
$where='';
if(Tools::isSubmit('prix') && $version2!='Silver') $order=' ORDER BY ABS( prix - '.((float)Tools::getValue('prix')).' ) ASC';

if(Tools::getValue('page')=='asso')
{
	$where.=" AND EXISTS ( SELECT id_associations FROM `"._DB_PREFIX_."pricestracker_associations` ASS WHERE ASS.id_product=$id_product AND PE.id_produits_etrangers=ASS.id_produits_etrangers ) ";

	$produits = Db::getInstance()->executeS('
	SELECT id_produits_etrangers,nom,prix,lien,image
	FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE
	WHERE id_concurrents='.$id_concurrents.$where.'
	'.($order?$order:' ORDER BY nom ASC').' LIMIT '.(($page-1)*15).',15');
}
else
{
	//langue
	$langue=(int)Configuration::get('PS_LANG_DEFAULT');
	
	$nomProduct=$product->name[$langue];
	$ean13=$product->ean13;
	$reference=$product->reference;
	
	$produits=array();
	
	if(strpos($nomConcurrent,'Amazon')!==FALSE)
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
			$response = $amazonEcs->responseGroup('Small,Images,ItemAttributes,SalesRank,OfferSummary')->category('All')->search( trim($rech ? $rech : ($ean13?$ean13:$nomProduct)) );
			//print_r($response->Items->Item);
			
			//si aucun produit par ean13
			if(!$response->Items && (!$rech && $ean13))
			{
				$response = $amazonEcs->responseGroup('Small,Images,ItemAttributes,SalesRank,OfferSummary')->category('All')->search( trim($nomProduct) );
			}
	
			
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
						'prix'=>$prix,
						'marketplace'=>$id,
						'lien'=>$lien_produit,
						'id_produits_etrangers'=>crc32($lg_amz.$id),
					);
				}
				
				//echo "$lien ; $rang ; $image ; $ean ; $prix ; $titre ; $id<br>";
			}
			//si un seul
			if($response->Items->Item && !is_array($response->Items->Item))
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
						'reference'=>$ean,
						'prix'=>$prix,
						'marketplace'=>$id,
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
	elseif($nomConcurrent=='Ebay')
	{
		// API request variables
		$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
		$version = '1.0.0';  // API version supported by your application
		$appid = Configuration::get('pricestracker_Ebay_AppId')?Configuration::get('pricestracker_Ebay_AppId'):'energypa-fluxebay-PRD-52f5e2e0d-d4feaa3d';  // Replace with your own AppID
		$globalid = Configuration::get('pricestracker_Ebay_GlobalID')?Configuration::get('pricestracker_Ebay_GlobalID'):('EBAY-'.strtoupper(Language::getIsoById($langue)));  // Global ID of the eBay site you want to search (e.g., EBAY-DE) 'EBAY-'.strtoupper(Language::getIsoById($langue))
		$query = $rech ? $rech : $nomProduct;  // You may want to supply your own query
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
			$image   = (string)$item->galleryURL[0];
			$lien_produit  = (string)$item->viewItemURL[0];
			$titre = $item->title;
			$prix = $item->sellingStatus->convertedCurrentPrice;
			$prix_immediat = $item->listingInfo->convertedBuyItNowPrice;
			$frais=$item->shippingInfo->shippingServiceCost;
			$vendeur=$item->sellerInfo->sellerUserName;
			$id=(string)$item->itemId[0];
				
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
		  if(!$produits && !is_array($resp->searchResult->item))
		  {
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
				$produits[]=array(
					'nom'=>$titre.' <span style="font-size:9px">Vendeur:'.$vendeur.'</span>',
					'image'=>$image,
					'prix'=>$prix.($frais?'<span style="font-size:9px">+'.($frais):'').'. '.($prix_immediat?'Immédiat:'.$prix_immediat:'').'</span>',
					'lien'=>$lien_produit,
					'id_produits_etrangers'=>$id,
				);
			}
			
			//print_r($item);
		     //echo "$lien ; $frais ; $image ; $prix ; $prix_immediat ; $titre ; $id ; $vendeur<br>";
		  }
		}
	}
	elseif($nomConcurrent=='PriceMinister')
	{
		$resp = simplexml_load_file('https://ws.priceminister.com/listing_ssl_ws?action=listing&login='.(Configuration::get('pricestracker_PM_login')?Configuration::get('pricestracker_PM_login'):'Ceubex').'&pwd='.(Configuration::get('pricestracker_PM_token')?Configuration::get('pricestracker_PM_token'):'a15a388fef514f6b8c7590e5a71b3c71').'&version=2015-07-05&scope=PRICING&nbproductsperpage=10'.
		( (($ean13 || $reference) && !$rech) ? '&refs='.urlencode($ean13.','.$reference)  :  '&kw='.urlencode(trim($rech ? $rech : $nomProduct)) ));
		
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
						'prix'=>$prix.($frais?'<span style="font-size:9px">+'.($frais).'</span>':'').'. '.($prix_occas? 'Occasion:'.$prix_occas.($frais_occas?'<span style="font-size:9px">+'.($frais_occas).'</span>':''):''),
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
						'prix'=>$prix.($frais?('<span style="font-size:9px">+'.($frais).'</span>'):'').($prix_occas? '. Occasion:'.$prix_occas.($frais_occas?('<span style="font-size:9px">+'.($frais_occas).'</span>'):''):''),
						'lien'=>$lien_produit,
						'reference'=>$reference,
						'id_produits_etrangers'=>$id,
					);
				}
		
				//print_r($item);
				//echo "$lien ; $image ; $titre ; $prix ; $frais ; $prix_occas ; $frais_occas ; $id ; $reference<br>";
			}
		}
	}
	elseif($nomConcurrent=='CDiscount')
	{
		$url = "https://api.cdiscount.com/OpenApi/json/Search";    
		$content = json_encode(array(
			  "ApiKey"=> Configuration::get('cdiscount_ApiKey')?Configuration::get('cdiscount_ApiKey'):"a10981ae-5361-4902-a625-a2ed78cfb042",
			  "SearchRequest"=> array(
				"Keyword"=> trim( $rech ? $rech : ($ean13?$ean13:$nomProduct) ),
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
		
		//echo $url; print_r($json_response);
		
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		if ( $status != 201 && $status != 200 ) {
			echo("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
		}
		
		if(strpos($json_response,'summary="Reputation Error"')!==FALSE) echo $json_response;
		
		curl_close($curl);
		
		$response = json_decode($json_response, true);
		
		
		$produits_req=$response['Products'];
		
		
		//si aucun produit par ean13
		if(!$produits_req && (!$rech && $ean13))
		{
			$url = "https://api.cdiscount.com/OpenApi/json/Search";    
			$content = json_encode(array(
				  "ApiKey"=> Configuration::get('cdiscount_ApiKey')?Configuration::get('cdiscount_ApiKey'):"a10981ae-5361-4902-a625-a2ed78cfb042",
				  "SearchRequest"=> array(
					"Keyword"=> trim( $nomProduct ),
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
			
			//echo $url; print_r($json_response);
			
			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			if ( $status != 201 && $status != 200 ) {
				echo("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
			}
			
			if(strpos($json_response,'summary="Reputation Error"')!==FALSE) echo $json_response;
			
			curl_close($curl);
			
			$response = json_decode($json_response, true);
			
			
			$produits_req=$response['Products'];
		}
	
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

	}
	
	
	//insertion
	foreach($produits as $produit)
	{
		Db::getInstance()->Execute("REPLACE INTO `"._DB_PREFIX_."pricestracker_produits_etrangers` (nom,prix,lien,image,frequenceMaj,id_concurrents,date,id_produits_etrangers,reference,marketplace)
		VALUES ('".addslashes($produit['nom'])."','".addslashes($produit['prix'])."','".addslashes($produit['lien'])."','".addslashes($produit['image'])."','".Configuration::get('pricestracker_JOURSMAJ')."','".$id_concurrents."','".time()."','".pSQL($produit['id_produits_etrangers'])."','".pSQL($produit['reference'])."','".pSQL($produit['marketplace'])."')");
	}
}


//catalogue etranger
foreach($produits as $p)
{
	bloc($p,$id_produits_etrangers_deja_associes,$lien,$id_product,true,$monnaie);
}

?>
<div id="agrandissement" style="position: absolute;top: 0;height: 100%;right: 0; display:none"><img src="" id="agrandissementImg" style="height:100%" /></div>
</body>
</html>

<?php
	}
}
