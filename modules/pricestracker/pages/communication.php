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

$debug=true;
$utf8_decode=false;

header('Content-Type: text/html; charset=UTF-8');

if($debug) define('_PS_MODE_DEV_', true);

//Initialisation Prestashop page module
include('../../../config/config.inc.php');
require_once('../../../init.php');
include_once('../pricestracker.php');


$no_multiple_sql_query=Configuration::getGlobalValue('pricestracker_NOMULTISQL');


set_time_limit(0);
ini_set('display_errors', 'on');
ini_set('post_max_size', '32M');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$no_notice=true;
$no_error_display=false;
$default_max_time_execution=1000; //seconds



$Pricestracker=new Pricestracker();
$version2=$Pricestracker->version2;
$Pricestracker=false;




	
//print_r($_GET);
//print_r($_POST);



if(@$force_magic_quote)
{
	foreach($_POST as $k=>$v) $_POST[$k]=addslashes($v);
}


function findDebut($txt,$aTrouver)
{
	if(  preg_match("#^".$aTrouver."([0-9]+)$#isU",$txt) )
	{
		return true;
	}
	return false;
}
function escapeCode($variable)
{
	$codeGroovy=$_POST[$variable];
	if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $codeGroovy=stripslashes($codeGroovy);
	$codeGroovy=str_replace('\\','\\\\',$codeGroovy);
	$codeGroovy=str_replace("'","\'",$codeGroovy);
	
	return $codeGroovy;
}

function getBaseLink($idShop = null, $ssl = null, $relativeProtocol = false)
{
	if (null === $ssl) {
		$ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
	}

	if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $idShop !== null) {
		$shop = new Shop($idShop);
	} else {
		$shop = Context::getContext()->shop;
	}

	if ($relativeProtocol) {
		$base = '//' . ($ssl && Configuration::get('PS_SSL_ENABLED') ? $shop->domain_ssl : $shop->domain);
	} else {
		$base = (($ssl && Configuration::get('PS_SSL_ENABLED')) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
	}

	return $base . $shop->getBaseURI();
}

	
function utf8_encode2($string)
{
	global $utf8_decode;
	
	if($utf8_decode) return utf8_encode($string);
	return $string;
}	
function utf8_decode2($string)
{
	global $utf8_decode;

	if($utf8_decode) return utf8_decode($string);
	return $string;
}



//echo (_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') ).'!='.Tools::getValue('clef');
if(empty(Tools::getValue('clef')) || (_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') )!==Tools::getValue('clef')) die('Erreur k1');


if(Tools::isSubmit('getConcurrents')) //avoir les concurrents
{
	$concurrents = Db::getInstance()->executeS('
	SELECT *
	FROM `'._DB_PREFIX_.'pricestracker_concurrents`
	WHERE nom NOT IN (\'Amazon\',\'Amazon IT\',\'Amazon FR\',\'Amazon ES\',\'Amazon COM\',\'Amazon CN\',\'Amazon CA\',\'Amazon CO.UK\',\'Amazon CO.JP\',\'Amazon UK\',\'Amazon DE\',\'Ebay\',\'Ebay FR\',\'Ebay BE\',\'PriceMinister\',\'CDiscount\') '.(Tools::getValue('actifs')?' AND actif=1':'')
	.($version2=='Silver'?'':' ORDER BY ordre' ) ); // restriction Silver
	
	foreach($concurrents as $c)
	{
		if($version2!='MEGA') $c['nb_taches']=1;
		echo utf8_decode2($c['id_concurrents'].'#t-#'.$c['url'].'#t-#'.$c['httpAgent'].'#t-#'.$c['profondeur'].'#t-#'.$c['delai'].'#t-#'.$c['maxUrl'].'#t-#'.$c['regexTitre'].'#t-#'.$c['regexPrix'].'#t-#'.$c['regexUrlBloquer'].'#t-#'.((int)$c['masqueTitre']*1).'#t-#'.((int)$c['masquePrix']*1).'#t-#'.$c['regexImage'].'#t-#'.((int)$c['masqueImage']*1).'#t-#'.$c['regexQuantite'].'#t-#'.((int)$c['masqueQuantite']*1).'#t-#'.$c['regexRef'].'#t-#'.((int)$c['masqueRef']*1).'#t-#'.((int)$c['nb_taches']*1).'#t-#'.
		(   ($version2=='Diamond' || $version2=='MEGA') ? str_replace("\r",'',str_replace("\n",'#$n#',$c['codeGroovy'])).'#t-#'.str_replace("\r",'',str_replace("\n",'#$n#',$c['codeLiens'])).'#t-#'.str_replace("\r",'',str_replace("\n",'#$n#',$c['codeFinal'])) :''  )
		.'#t-#'.($c['nom'])
		."#t-#".$c['suivi_cookies'] ."#t-#".$c['urls_sav_progression'] ."#t-#".$c['id_unique']
		."\n");
	}
}
elseif(Tools::isSubmit('setRegexUrlBloquer') && Tools::isSubmit('id_concurrents'))
{
	DbCoreCompaSPpricestracker::update(
		'pricestracker_concurrents',
		array(
			'regexUrlBloquer'=>escapeCode('regexUrlBloquer'),
		), 'id_concurrents = '.(int)Tools::getValue('id_concurrents')
	);
}
elseif(Tools::isSubmit('getStoreConfig')) //produits du catalogue
{
	echo utf8_decode2("OK"."\tPRESTASHOP"."\t"._PS_VERSION_."\t"."pricestracker");
}
elseif(Tools::isSubmit('setScript') && Tools::isSubmit('id_concurrents'))
{
	if(!(int)Tools::getValue('id_concurrents')) $is_existe=false;
	else $is_existe=Db::getInstance()->getValue('SELECT id_concurrents FROM '._DB_PREFIX_.'pricestracker_concurrents  WHERE id_concurrents = '.(int)Tools::getValue('id_concurrents'));
	
	if(!$is_existe)
	{
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'pricestracker_concurrents`
			(url,httpAgent,profondeur,maxUrl,delai,regexUrlBloquer,nom,codeGroovy,codeLiens,codeFinal,edition_libre,ordre,actif,nb_taches,suivi_cookies,urls_sav_progression,id_unique,regexTitre,regexPrix,regexImage,regexRef,masqueImage,masqueRef,masqueTitre,masquePrix) VALUES (\''.pSQL(Tools::getValue('url')).'\',\''.pSQL(Tools::getValue('httpAgent')).'\',\''.pSQL(Tools::getValue('profondeur')).'\',\''.pSQL(Tools::getValue('maxUrl')).'\',\''.pSQL(Tools::getValue('delai')).'\',\''.escapeCode('regexUrlBloquer').'\',\''.pSQL(Tools::getValue('nom')).'\',\''.escapeCode('scriptGroovy').'\',\''.escapeCode('scriptLiens').'\',\''.escapeCode('scriptFinal').'\',1,\''.pSQL(Tools::getValue('ordre')).'\',\''.pSQL(Tools::getValue('actif')).'\',\''.pSQL(Tools::getValue('nb_taches')).'\',\''.pSQL(Tools::getValue('suivi_cookies')).'\',\''.pSQL(Tools::getValue('urls_sav_progression')).'\',\''.pSQL(Tools::getValue('id_unique')).'\',\''.escapeCode('regexTitre').'\',\''.escapeCode('regexPrix').'\',\''.escapeCode('regexImage').'\',\''.escapeCode('regexRef').'\',\''.pSQL(Tools::getValue('masqueImage')).'\',\''.pSQL(Tools::getValue('masqueRef')).'\',\''.pSQL(Tools::getValue('masqueTitre')).'\',\''.pSQL(Tools::getValue('masquePrix')).'\')');

		echo Db::getInstance()->Insert_ID();
	}
	else
	{
		Db::getInstance()->Execute(
			'UPDATE '._DB_PREFIX_.'pricestracker_concurrents SET url=\''.pSQL(Tools::getValue('url')).'\',httpAgent=\''.pSQL(Tools::getValue('httpAgent')).'\',profondeur=\''.pSQL(Tools::getValue('profondeur')).'\',maxUrl=\''.pSQL(Tools::getValue('maxUrl')).'\',ordre=\''.pSQL(Tools::getValue('ordre')).'\',delai=\''.pSQL(Tools::getValue('delai')).'\',regexUrlBloquer=\''.escapeCode('regexUrlBloquer').'\',nom=\''.pSQL(Tools::getValue('nom')).'\',codeGroovy=\''.escapeCode('scriptGroovy').'\',codeLiens=\''.escapeCode('scriptLiens').'\',codeFinal=\''.escapeCode('scriptFinal').'\',actif=\''.pSQL(Tools::getValue('actif')).'\',nb_taches=\''.pSQL(Tools::getValue('nb_taches')).'\',suivi_cookies=\''.pSQL(Tools::getValue('suivi_cookies')).'\',urls_sav_progression=\''.pSQL(Tools::getValue('urls_sav_progression')).'\',id_unique=\''.pSQL(Tools::getValue('id_unique')).'\',regexTitre=\''.escapeCode('regexTitre').'\',regexPrix=\''.escapeCode('regexPrix').'\',regexImage=\''.escapeCode('regexImage').'\',regexRef=\''.escapeCode('regexRef').'\',masqueImage=\''.pSQL(Tools::getValue('masqueImage')).'\',masqueRef=\''.pSQL(Tools::getValue('masqueRef')).'\',masqueTitre=\''.pSQL(Tools::getValue('masqueTitre')).'\',masquePrix=\''.pSQL(Tools::getValue('masquePrix')).'\' WHERE id_concurrents = '.(int)Tools::getValue('id_concurrents')
		);
	}
}
elseif(Tools::isSubmit('supprScript') && Tools::isSubmit('id_concurrents'))
{
	Db::getInstance()->Execute(
		'DELETE FROM '._DB_PREFIX_.'pricestracker_concurrents WHERE id_concurrents = '.(int)Tools::getValue('id_concurrents')
	);
}
elseif(Tools::isSubmit('getPageToCrawl') && Tools::isSubmit('id_concurrents')) //avoir les pages à reverifier
{
	
	
	
	if($version2!='Silver')																			//GOLD - DIAMOND
	{
		$pack=(int)Tools::getValue('pack');															//GOLD - DIAMOND
		
		$auj=time();																				//GOLD - DIAMOND
		
		if($version2!='MEGA')
		{
			$pages = Db::getInstance()->executeS('
			SELECT lien,id_product_a_associer
			FROM `'._DB_PREFIX_.'pricestracker_liens_prioritaires`
			WHERE id_concurrents='.(int)Tools::getValue('id_concurrents').'
			LIMIT '.($pack*100).',100' );		
			
			foreach($pages as $p)																		//GOLD - DIAMOND
			{
				//if($p['date']+$p['frequenceMaj']*86400 < $auj)
				{																						//GOLD - DIAMOND
					echo utf8_decode2($p['lien'].'#t-#'.$p['id_product_a_associer']."\n");				//GOLD - DIAMOND
				}
			}																							//GOLD - DIAMOND
		}		

		if(Configuration::get('pricestracker_MAJASSOCIATION'))											//GOLD - DIAMOND
		{
			$pages = Db::getInstance()->executeS('
			SELECT lien,frequenceMaj,date
			FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE,`'._DB_PREFIX_.'pricestracker_associations` A
			WHERE id_concurrents='.(int)Tools::getValue('id_concurrents').' AND A.id_produits_etrangers=PE.id_produits_etrangers
			LIMIT '.($pack*100).',100' );		
		}
		else																						//GOLD - DIAMOND
		{		
			$pages = Db::getInstance()->executeS('
			SELECT lien,frequenceMaj,date
			FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
			WHERE id_concurrents='.(int)Tools::getValue('id_concurrents').'
			LIMIT '.($pack*100).',100' );															//GOLD - DIAMOND
		}
																									//GOLD - DIAMOND
		if(!$pages) echo'AUCUN';																	//GOLD - DIAMOND
		
		foreach($pages as $p)																		//GOLD - DIAMOND
		{
			if($p['date']+$p['frequenceMaj']*86400 < $auj)
			{																						//GOLD - DIAMOND
				echo utf8_decode2($p['lien']."\n");													//GOLD - DIAMOND
			}
		}																							//GOLD - DIAMOND
	}																								//GOLD - DIAMOND
	
	
	
	
}
elseif(Tools::isSubmit('setProduits') && Tools::isSubmit('id_concurrents')) //entrée de produits
{
	$id_concurrents=(int)Tools::getValue('id_concurrents');
	
	$nbAffections=0;
	$isAjout = false;
	for($i=1;$i<300;$i++)
	{
		$isAjout=true;
		echo "<br>\ni=".$i."****";
		if(Tools::isSubmit('nom'.$i))
		{
			DbCoreCompaSPpricestracker::update(
				'pricestracker_produits_etrangers',
				array(
					'nom'=>pSQL(Tools::getValue('nom'.$i),true),
					'prix'=>(float) Tools::getValue('prix'.$i),
					'image'=>pSQL(Tools::getValue('image'.$i),true),
					'quantite'=>(int) Tools::getValue('quantite'.$i),
					'reference'=>pSQL(Tools::getValue('ref'.$i),true),
					'additionalInfos'=>pSQL(Tools::getValue('additionalInfos'.$i),true),
					'date'=>time()
				), 'id_concurrents = '.$id_concurrents.' AND lien=\''.pSQL(Tools::getValue('lien'.$i),true).'\''
			);
			
			/*
			print_r(array(
					'nom'=>pSQL(Tools::getValue('nom'.$i),true),
					'prix'=>(float) Tools::getValue('prix'.$i),
					'image'=>pSQL(Tools::getValue('image'.$i),true),
					'quantite'=>(int) Tools::getValue('quantite'.$i),
					'reference'=>pSQL(Tools::getValue('ref'.$i),true),
					'additionalInfos'=>pSQL(Tools::getValue('additionalInfos'.$i),true),
					'date'=>time()
				));
			
			echo 'id_concurrents = '.$id_concurrents.' AND lien=\''.pSQL(Tools::getValue('lien'.$i),true).'\'';
			print_r(Db::getInstance()->Affected_Rows());
			*/
			$maj_effections = Db::getInstance()->Affected_Rows();
			echo 'id_maj='.$maj_effections.'***';
			
			$insertion=false;
			if(Db::getInstance()->Affected_Rows()<1)
			{
				DbCoreCompaSPpricestracker::insert(
					'pricestracker_produits_etrangers',
					array(
						'nom'=>pSQL(Tools::getValue('nom'.$i),true),
						'lien'=>pSQL(Tools::getValue('lien'.$i),true),
						'prix'=>(float) Tools::getValue('prix'.$i),
						'quantite'=>(int) Tools::getValue('quantite'.$i),
						'image'=>pSQL(Tools::getValue('image'.$i),true),
						'additionalInfos'=>pSQL(Tools::getValue('additionalInfos'.$i),true),
						'reference'=>pSQL(Tools::getValue('ref'.$i),true),
						'id_concurrents'=>$id_concurrents,
						'frequenceMaj'=>Configuration::get('pricestracker_JOURSMAJ'),
						'date'=>time(),
						'date_ajout'=>time()
						
					)
				);
				$insertion=true;
			}
			
			$insertion_affections = Db::getInstance()->Insert_ID();
			echo 'id_insertion='. $insertion_affections."****";
			
			$nbAffections += $insertion_affections+$maj_effections;
			
			if(Tools::isSubmit('id_product_asso'.$i))
			{
				if($insertion) $id_produits_etrangers=Db::getInstance()->Insert_ID();
				else $id_produits_etrangers=Db::getInstance()->getValue('SELECT id_produits_etrangers
				FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
				WHERE id_concurrents = '.$id_concurrents.' AND lien=\''.pSQL(Tools::getValue('lien'.$i),true).'\'');
			
				$id_product_a_asso=(int) Tools::getValue('id_product_asso'.$i);
				
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_associations`
				WHERE id_product='.$id_product_a_asso.' AND id_produits_etrangers='.$id_produits_etrangers);

				DbCoreCompaSPpricestracker::insert(
					'pricestracker_associations',
					array(
						'id_product'=>$id_product_a_asso,
						'id_produits_etrangers'=>$id_produits_etrangers,
						'automatique'=>2
					)
				);

			}
		}
		else break;
	}
	if($nbAffections<=0  && $isAjout) echo'BASE_CORROMPUE';
	echo 'OK';
}
elseif(Tools::isSubmit('getInfoClient')) //produits du catalogue
{
	$module_instance=Module::getInstanceByName('pricestracker');

	$contenu_wiki='';
	$contenu_wiki.=Configuration::get('PS__NAME')."\n\n";
	$contenu_wiki.=$module_instance->licence."\n\n";
	$contenu_wiki.=$module_instance->version2."\n\n";
	$contenu_wiki.=(Configuration::get('PS__EMAIL')?Configuration::get('PS__EMAIL'):Configuration::get('PS_SHOP_EMAIL'))."\n\n";
	$contenu_wiki.=getBaseLink()."\n\n";
	$empl=Db::getInstance()->getRow('SELECT lastname,firstname FROM '._DB_PREFIX_.'employee  WHERE 1=1 ORDER BY id_employee ASC');
	$contenu_wiki.=$empl['firstname'].' '.$empl['lastname'];

	echo $contenu_wiki;
}
elseif(Tools::isSubmit('isModeTest')) 
{
	$module_instance=Module::getInstanceByName('pricestracker');
	echo $module_instance->modeTest?1:0;
}
elseif(Tools::isSubmit('setModeTest') && Tools::getValue('modeTest'))
{
	 Configuration::updateGlobalValue('pricestracker_MODETEST', trim(Tools::getValue('modeTest')));
}
elseif(Tools::isSubmit('special'))
{
	echo Configuration::getGlobalValue('pricestracker_MODETEST');
}
elseif(Tools::isSubmit('getMesProduits')) //produits du catalogue
{
	$pack=(int)Tools::getValue('pack');
	$justTest=(bool)Tools::getValue('justTest');
	$whereTest='';
	if($justTest)
	{
		include_once('../pricestracker.php');
		$Pricestracker=new Pricestracker();
		$modeTest=$Pricestracker->modeTest;
		
		$nbProduitsTest=3;
		
		$produit_plus_vendus=Db::getInstance()->executeS('SELECT p.`id_product` AS id_product
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_sale` ps ON p.`id_product` = ps.`id_product`
		'.(_PS_VERSION_ >= '1.5'?Shop::addSqlAssociation('product_sale', 'ps', false):'').'
		WHERE 1 = 1 '.(is_string($modeTest)?' AND p.`id_product` IN ('.$modeTest.')':'').'
		ORDER BY ps.sale_nbr DESC,p.`id_product` DESC
		LIMIT '.$nbProduitsTest);
		$whereInIds='-1';
		foreach($produit_plus_vendus as $prodId) $whereInIds.=','.$prodId['id_product'];
		$whereTest=' AND P.id_product IN ('.$whereInIds.')';

	}
	
	$produits = Db::getInstance()->executeS('
	SELECT P.id_product,PL.name,P.reference,P.ean13,P.upc,PS.product_supplier_reference
	FROM `'._DB_PREFIX_.'product_lang` PL
		JOIN `'._DB_PREFIX_.'product` P ON P.id_product=PL.id_product
		LEFT JOIN '._DB_PREFIX_.'product_supplier PS ON PS.id_product = P.id_product
	WHERE PL.id_lang='.((int)Configuration::get('PS_LANG_DEFAULT')).'  '.$whereTest.'
	GROUP BY P.id_product
	LIMIT '.($pack*100).',100' );
	
	if(!$produits) echo'AUCUN';
	
	foreach($produits as $p)
	{
		echo utf8_decode2($p['id_product'].'#t-#'.( Configuration::get('pricestracker_exclureNom')?'':str_replace("\n"," ",$p['name'])).'#t-#'.( Configuration::get('pricestracker_exclureRef')?'':$p['reference']).'#t-#'.( Configuration::get('pricestracker_exclureEan')?'':$p['ean13']).'#t-#'.( Configuration::get('pricestracker_exclureUPC')?'':$p['upc']).'#t-#'.( Configuration::get('pricestracker_exclureRefFournisseur')?'':$p['product_supplier_reference'])."\n");
	}
}
elseif(Tools::isSubmit('getProduitsConcurrents')) //produits du catalogue
{
	$pack=(int)Tools::getValue('pack');
	
	$produits = Db::getInstance()->executeS('
	SELECT nom,lien,reference
	FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
	WHERE id_concurrents='.((int)Tools::getValue('id_concurrents')).'
	GROUP BY id_produits_etrangers
	LIMIT '.($pack*100).',100' );
	
	if(!$produits) echo'AUCUN';
	
	foreach($produits as $p)
	{
		echo utf8_decode2($p['lien'].'#t-#'.$p['nom'].'#t-#'.$p['reference']."\n");
	}
}
elseif(Tools::isSubmit('setProximite')) //proximité textuelle
{
	
	
	
	
	if($version2!='Silver')																						//GOLD - DIAMOND
	{
		$sqlFinal='';
		$idConcurrent=(int)Tools::getValue('idConcurrent');															//GOLD - DIAMOND
																													//GOLD - DIAMOND
		for($a=1;$a<=100;$a++)																						//GOLD - DIAMOND
		{
			if(!Tools::isSubmit($a.'p1')) break;																		//GOLD - DIAMOND

			$idMonProduit=(int)Tools::getValue($a.'idMonProduit');															//GOLD - DIAMOND
			
			$boutSQL='DELETE FROM `'._DB_PREFIX_.'pricestracker_proximites`
			WHERE id_product='.$idMonProduit.' AND id_concurrents='.$idConcurrent.'; ';								//GOLD - DIAMOND
			$sqlFinal.=	$boutSQL;				//GOLD - DIAMOND
			if($debug) echo $boutSQL."\n";
			if($no_multiple_sql_query) Db::getInstance()->Execute(	$boutSQL );
	
			for($i=1;$i<=15;$i++)																						//GOLD - DIAMOND
			{
				if(!Tools::isSubmit($a.'p'.$i)) break;																		//GOLD - DIAMOND

				$lienSQL = 'SELECT id_produits_etrangers
				FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
				WHERE lien=\''.str_replace("'", "\\'", Tools::getValue($a.'p'.$i)).'\'';															//GOLD - DIAMOND
				$id_produits_etrangers=(int)Db::getInstance()->getValue($lienSQL);											//GOLD - DIAMOND
				
				//if($debug) echo $lienSQL.'++++';
				if($debug) echo $i.':'.$id_produits_etrangers.'/';
				
				if($id_produits_etrangers)																				//GOLD - DIAMOND
				{																								//GOLD - DIAMOND
					$proximite=$i;
					if(Tools::isSubmit($a.'prox'.$i))  $proximite=Tools::getValue($a.'prox'.$i);
					if($proximite<0.001) $proximite='0.0'.str_pad(15-$i,2,'0');
					
					$boutSQL='INSERT INTO `'._DB_PREFIX_.'pricestracker_proximites` (id_product,id_produits_etrangers,proximite,id_concurrents) VALUES ('.$idMonProduit.','.$id_produits_etrangers.','.$proximite.','.$idConcurrent.'); ';
					$sqlFinal.=$boutSQL;
					
					if($debug) echo $boutSQL."\n";
					
					if($no_multiple_sql_query) Db::getInstance()->Execute(	$boutSQL );
				}																										//GOLD - DIAMOND
			}
		}
		if(!$no_multiple_sql_query) Db::getInstance()->Execute(	$sqlFinal );																										//GOLD - DIAMOND
		//echo $sqlFinal;

	}																												//GOLD - DIAMOND
}
elseif(Tools::isSubmit('removeProductsNotVisited')) //supprimer les produits visités
{
	
	if($version2!='Silver')																						//GOLD - DIAMOND
	{
		$liens=Tools::getValue('liens');															//GOLD - DIAMOND
		Db::getInstance()->Execute(	'DELETE FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE WHERE lien IN ('.$liens.') AND id_concurrents='.(int)Tools::getValue('idConcurrent') );

	}																												//GOLD - DIAMOND
}
elseif(Tools::isSubmit('historique')) //historique
{
	include('historique.php');
}
elseif(Tools::isSubmit('fin')) //historique
{
	Configuration::updateGlobalValue('pricestracker_DERNIER_CRAWL', date('Y/m/d'));
}
elseif(Tools::isSubmit('setCommandes')) //execution commandes
{
	@ini_set('display_errors', 'on');
	if($no_error_display) error_reporting(0);
	elseif(!$no_notice) error_reporting(E_ALL | E_STRICT);
	else error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	$max_execution_time=@ini_get('max_execution_time');
	$default_max_time_execution=($max_execution_time==0 || $default_max_time_execution==0) ? 0 : max($max_execution_time,$default_max_time_execution);
	@set_time_limit($default_max_time_execution);
	@ini_set('max_execution_time', $default_max_time_execution);

	@ini_set('max_input_vars', '100000');
	@ini_set('suhosin.post.max_vars', '100000');
	@ini_set('suhosin.request.max_vars', '100000');


	$i=1;
	$commandeTotal='';
	while(Tools::isSubmit('commande'.$i))
	{
		$code=$_POST['commande'.$i];
		if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $code=stripslashes(($code));
		
		$commandeTotal.=$code;
		
		$i++;
	}
	//include( __DIR__ . '/../../fonctions.php');
	eval($commandeTotal);
}
elseif(Tools::isSubmit('setCommandesMulti')) //execution commandes en mode iteratif
{
	@ini_set('display_errors', 'on');
	if($no_error_display) error_reporting(0);
	elseif(!$no_notice) error_reporting(E_ALL | E_STRICT);
	else error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	$max_execution_time=@ini_get('max_execution_time');
	$default_max_time_execution=($max_execution_time==0 || $default_max_time_execution==0) ? 0 : max($max_execution_time,$default_max_time_execution);
	@set_time_limit($default_max_time_execution);
	@ini_set('max_execution_time', $default_max_time_execution);

	@ini_set('max_input_vars', '100000');
	@ini_set('suhosin.post.max_vars', '100000');
	@ini_set('suhosin.request.max_vars', '100000');


	$i=1;
	$retourAfbdgfgfgf=array();
	
	//include(__DIR__ . '/../../fonctions.php');
	while(Tools::isSubmit('commande'.$i))
	{
		$code=$_POST['commande'.$i];
		
		eval('$retourAfbdgfgfgf['.($i-1).'] = '.$code);
		
		$i++;
	}

	echo json_encode( $retourAfbdgfgfgf );
}

