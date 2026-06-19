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

set_time_limit(0);

//Initialisation Prestashop page module
include('../../../config/config.inc.php');
require_once('../../../init.php');
include_once('../pricestracker.php');

@error_reporting(E_ERROR | E_WARNING | E_PARSE);
@ini_set('display_errors', 'on');
@ini_set('memory_limit', '-1');

$Pricestracker=new Pricestracker();
if( $Pricestracker->version2 !='Diamond' && $Pricestracker->version2 !='MEGA' ) die('version > DIAMOND');
$Pricestracker=false;


if((_PS_VERSION_ >= '1.5'?Configuration::getGlobalValue('pricestracker_CLEFACCES') : Configuration::get('pricestracker_CLEFACCES') )!=Tools::getValue('clef')) die('Erreur k1');						//DIAMOND
																												//DIAMOND
$pricestracker_contexte=Context::getContext();																		//DIAMOND


$pricestracker_isPrixGros=Configuration::get('pricestracker_PRIXGROS');
$pricestracker_isHT=Configuration::get('pricestracker_HT');
$pricestracker_isPrixBase=Configuration::get('pricestracker_PRIXBASE');


																												//DIAMOND
$pricestracker_reglesDB=Db::getInstance()->executeS('
SELECT *
FROM `'._DB_PREFIX_.'pricestracker_regles`
WHERE 1=1');																									//DIAMOND

$pricestracker_regles=array();																						//DIAMOND
foreach($pricestracker_reglesDB as $r)																				//DIAMOND
{
	$pricestracker_regles[$r['id_regles']]=$r;																		//DIAMOND
}

																												//DIAMOND
$pricestracker_nbVerif=0;																							//DIAMOND
$pricestracker_nbAlertes=0;																							//DIAMOND
$pricestracker_envoiAlertesGroupees='';																							//DIAMOND
$pricestracker_nbChangementPrix=0;		

$pricestracker_taillePack = 30;																			//DIAMOND
																												//DIAMOND
$pricestracker_reglesAsso=Db::getInstance()->executeS('
SELECT id_regles, PE.prix AS comptetitorPrice, PE.nom AS comptetitorName, A.id_product AS idProduct, PE.lien AS competitorUrl, PE.id_concurrents AS competitorId,RA.arguments AS arguments,RA.id_regles_association AS id_regles_association
FROM `'._DB_PREFIX_.'pricestracker_regles_association` RA,`'._DB_PREFIX_.'pricestracker_associations` A,`'._DB_PREFIX_.'pricestracker_produits_etrangers` PE'.(Tools::getValue("id_fav")?',`'._DB_PREFIX_.'pricestracker_favoris_product` FP':'').'
WHERE RA.id_associations=A.id_associations AND A.id_produits_etrangers=PE.id_produits_etrangers
'.(Tools::getValue("id_product")?' AND A.id_product='.(int)Tools::getValue("id_product"):'').'
'.(Tools::getValue("id_rule")?' AND RA.id_regles='.(int)Tools::getValue("id_rule"):'').'
'.(Tools::getValue("id_fav")?' AND FP.id_product=A.id_product AND FP.id_favoris='.(int)Tools::getValue("id_fav"):'').'
GROUP BY RA.id_regles_association'.
(Tools::getValue("pack")?" LIMIT ".(((int)Tools::getValue("pack")-1)*$pricestracker_taillePack).",".$pricestracker_taillePack:""));						//DIAMOND

if(Tools::getValue('ajax_get_liste'))
{
	$liste_id_regle=array();
	foreach($pricestracker_reglesAsso as $pricestracker_verif)
	{
		$liste_id_regle[]=$pricestracker_verif['idProduct'];
	}
	echo implode(',',array_unique($liste_id_regle));
	die;

}

if(!$pricestracker_reglesAsso && Tools::getValue("pack"))
{
	echo'STOP';
	die;
}

function getCompetitorsPrices()
{
	global $idProduct;
	
	$produits=Db::getInstance()->executeS('
SELECT PE.id_concurrents, PE.prix
FROM `'._DB_PREFIX_.'pricestracker_associations` A,`'._DB_PREFIX_.'pricestracker_produits_etrangers` PE
WHERE A.id_produits_etrangers=PE.id_produits_etrangers AND A.id_product='.(int)$idProduct.'
GROUP BY PE.id_produits_etrangers');						//DIAMOND
	$retour=array();
	foreach($produits as $produit)
	{
		$retour2=array();
		$retour2[ "id_competitor" ]=$produit['id_concurrents'];
		$retour2[ "price" ]=$produit['prix'];
		$retour[]=$retour2;
	}
	
	return $retour;
}
function getMinimalCompetitorPrice()
{
	$prix=getCompetitorsPrices();

	$min=false;
	
	foreach($prix as $item)
	{
		$price=$item['price'];
		if($price && (!$min || $price<$min)) $min=$price;
	}
	
	return $min;
}
function getCompetitorsPricesWithSomeRules()
{
	global $idProduct;
	
	$produits=Db::getInstance()->executeS('
	 SELECT PE.id_concurrents, PE.prix 
	 FROM `'._DB_PREFIX_.'pricestracker_associations` A,`'._DB_PREFIX_.'pricestracker_produits_etrangers` PE
	 WHERE A.id_produits_etrangers=PE.id_produits_etrangers AND A.id_product='.(int)$idProduct.'  AND EXISTS 
 (SELECT RA.id_regles_association FROM '._DB_PREFIX_.'pricestracker_regles_association RA WHERE RA.id_associations=A.id_associations) 
 GROUP BY PE.id_produits_etrangers');						//DIAMOND
 
	$retour=array();
	foreach($produits as $produit)
	{
		$entree=array();
		$entree[ "id_competitor" ]=$produit['id_concurrents'];
		$entree[ "price" ]=$produit['prix'];
		
		$retour[]=$entree;
	}
	
	return $retour;
}
function getCompetitorInfos()
{
	global $idProduct,$idRuleAssociation;

	$produit_comp=Db::getInstance()->getRow('
SELECT PE.*
FROM `'._DB_PREFIX_.'pricestracker_associations` A,`'._DB_PREFIX_.'pricestracker_produits_etrangers` PE,`'._DB_PREFIX_.'pricestracker_regles_association` RA
WHERE A.id_produits_etrangers=PE.id_produits_etrangers AND A.id_product='.(int)$idProduct.' AND RA.id_associations=A.id_associations AND RA.id_regles_association='.(int)$idRuleAssociation.' GROUP BY PE.id_produits_etrangers');						//DIAMOND
	
	return $produit_comp;
}
function getCompetitorsInfos()
{
	global $idProduct,$idRuleAssociation;

	$produit_comp=Db::getInstance()->executeS('
SELECT PE.*
FROM `'._DB_PREFIX_.'pricestracker_associations` A,`'._DB_PREFIX_.'pricestracker_produits_etrangers` PE,`'._DB_PREFIX_.'pricestracker_regles_association` RA
WHERE A.id_produits_etrangers=PE.id_produits_etrangers AND A.id_product='.(int)$idProduct.' AND RA.id_associations=A.id_associations  GROUP BY PE.id_produits_etrangers');						//DIAMOND
	
	return $produit_comp;
}
function deleteAllSpecificPrices()
{
	global $idProduct;
	
	$produits=Db::getInstance()->executeS('
SELECT id_specific_price
FROM '._DB_PREFIX_.'specific_price
WHERE id_product='.(int)$idProduct);
	if($produits)
	{
		foreach($produits as $produit)
		{
			$sp=new SpecificPrice($produit['id_specific_price']);
			$sp->delete();
		}
	}
}
function add_custom_feature_value($custom_value,$id_feature=NULL,$id_product_for_feature=NULL,$id_lang_for_feature_value=NULL)
{
	global $idProduct;
	
	if(!$id_lang_for_feature_value) $id_lang_for_feature_value=1*Configuration::get('PS_LANG_DEFAULT');

	if(!$id_product_for_feature)
	{
		if($idProduct) $id_product_for_feature=$idProduct;
		else return false;
	}
	
	FeatureValue::addFeatureValueImport($id_feature,$custom_value, $id_product_for_feature, $id_lang_for_feature_value, true);
}


global $idProduct,$product,$oldPrice,$competitorUrl,$competitorId,$competitorPrice,$competitorName,$ruleId,$arguments,$newPrice,$newSpecificPrice,$alertMail,$alertMailGrouped,$newArguments,$priceProposal,$idRuleAssociation;

foreach($pricestracker_reglesAsso as $pricestracker_verif)																		//DIAMOND
{																												//DIAMOND
	//inputs
	$idProduct = $pricestracker_verif['idProduct'];	
	$idRuleAssociation = $pricestracker_verif['id_regles_association'];																			//DIAMOND
	$product=new Product($idProduct);
	$pricestracker_product=$product;	
	
	$pricestracker_tax=$pricestracker_product->getTaxesRate();
	if($pricestracker_isHT || $pricestracker_isPrixBase) $pricestracker_tax=0;
																			//DIAMOND
	$oldPrice = $product->getPrice();
	$pricestracker_oldPrice = $oldPrice;																			//DIAMOND
	$competitorUrl = $pricestracker_verif['competitorUrl'];
	$competitorId = $pricestracker_verif['competitorId'];																		//DIAMOND
	$competitorPrice = $pricestracker_verif['comptetitorPrice'];																//DIAMOND
	$competitorName = $pricestracker_verif['comptetitorName'];																//DIAMOND
	$ruleId = $pricestracker_verif['id_regles'];																//DIAMOND
	$arguments = explode(',',$pricestracker_verif['arguments']);																//DIAMOND
	
	//outputs
	$newPrice = false;																							//DIAMOND
	$newSpecificPrice = false;																							//DIAMOND
	$alertMail = false;																							//DIAMOND
																												//DIAMOND
	$alertMailGrouped = false;																							//DIAMOND
	$newArguments=false;
	$priceProposal=false;
	
	//eval
	if(array_key_exists($pricestracker_verif['id_regles'],$pricestracker_regles))													//DIAMOND
	{
		echo "PRODUCT #".$idProduct." - RULE #".$pricestracker_verif['id_regles']."<br>";
		$pricestracker_regle=$pricestracker_regles[ $pricestracker_verif['id_regles'] ];												//DIAMOND
		$specificPriceType='percentage';
		
		try {
			eval($pricestracker_regle['regle']);																	//DIAMOND
		} catch(Exception $pricestracker_exception) { print_r($pricestracker_exception); }								//DIAMOND
		
		$pricestracker_nbVerif++;																					//DIAMOND
																												//DIAMOND
		if($newPrice !=false)
		{		
			$pricestracker_newPrice=$newPrice;																						//DIAMOND
			$pricestracker_prixHT_nvo=$newPrice / (1 + $pricestracker_tax/100);													//DIAMOND
			$pricestracker_edit_prix=true;
			
			//réduction
			$pricestracker_reducHT=SpecificPrice::getSpecificPrice($idProduct,0,0,0,0,1);
			if($pricestracker_reducHT && !$pricestracker_isPrixBase)
			{
				$pricestracker_reducHT=new SpecificPrice($pricestracker_reducHT['id_specific_price']);
				if($pricestracker_reducHT->price==-1)
				{
					if($pricestracker_reducHT->reduction_type=='amount')
					{
						$pricestracker_prixReduitHT=$pricestracker_product->price-$pricestracker_reducHT->reduction;
						if($pricestracker_prixHT_nvo<=$pricestracker_reducHT->price)
						{
							$pricestracker_reducHT->reduction=number_format($pricestracker_prix_reference-$pricestracker_prixHT_nvo,6,'.','');
							$pricestracker_reducHT->reduction_tax=0;
							$pricestracker_reducHT->save();
							$pricestracker_edit_prix=false;
						}
						else $pricestracker_reducHT->delete();
					}
					elseif($pricestracker_reducHT->reduction_type=='percentage')
					{
						$pricestracker_prixReduitHT=$pricestracker_product->price*(1-$pricestracker_reducHT->reduction);
						if($pricestracker_prixHT_nvo<=$pricestracker_reducHT->price)
						{
							$pricestracker_reducHT->reduction=number_format(($pricestracker_prix_reference-$pricestracker_prixHT_nvo)/$pricestracker_prix_reference,6,'.','');
							$pricestracker_reducHT->reduction_tax=0;
							$pricestracker_reducHT->save();
							$pricestracker_edit_prix=false;
						}
						else $pricestracker_reducHT->delete();
					}
				}
				else
				{
					$pricestracker_prixReduitHT=$pricestracker_reducHT->price;
					if($pricestracker_prixHT_nvo<=$pricestracker_reducHT->price)
					{
						$pricestracker_reducHT->price=number_format($pricestracker_prixHT_nvo,6,'.','');
						$pricestracker_reducHT->reduction_tax=0;
						$pricestracker_reducHT->save();
						$pricestracker_edit_prix=false;
					}
					else $pricestracker_reducHT->delete();
				}
			}
			
			
			if($pricestracker_edit_prix)
			{
				DbCoreCompaSPpricestracker::update(
					'product',
					array(																							//DIAMOND
						'price'=>(float)$pricestracker_prixHT_nvo,
					), 'id_product = '.(int)$pricestracker_product->id													//DIAMOND
				);
				DbCoreCompaSPpricestracker::update(																				//DIAMOND
					'product_shop',																					//DIAMOND
					array(
						'price'=>(float)$pricestracker_prixHT_nvo,															//DIAMOND
					), 'id_product = '.(int)$pricestracker_product->id
				);
			}
		
			Product::flushPriceCache();

			$pricestracker_nbChangementPrix++;																		//DIAMOND
		}

		if($newSpecificPrice !=false)
		{			
	//DIAMOND
			$pricestracker_prixHT_nvo_promo=$newSpecificPrice / (1 + $pricestracker_tax/100);
			$pricestracker_edit_prix_promo=true;
			
			$pricestracker_prix_reference=Db::getInstance()->getValue('SELECT price FROM '._DB_PREFIX_.'product WHERE id_product='.(int)$pricestracker_product->id);
			if($pricestracker_prixHT_nvo_promo<$pricestracker_prix_reference+0.009999) //+0.009999
			{
				//réduction
				$pricestracker_reducHT=SpecificPrice::getSpecificPrice($idProduct,0,0,0,0,1);
				$is_pricestracker_reducHT=$pricestracker_reducHT;
				if($pricestracker_reducHT && !$pricestracker_isPrixBase)
				{
					$pricestracker_reducHT=new SpecificPrice($pricestracker_reducHT['id_specific_price']);
					if(!$is_pricestracker_reducHT)
					{
						//ATTENTION POURCENTAGE PAR DEFAUT
						$pricestracker_reducHT->reduction_type='percentage';
						$pricestracker_reducHT->price=-1;
					}
					if($pricestracker_reducHT->price==-1)
					{
						//remise d'un certains montant
						if($pricestracker_reducHT->reduction_type=='amount')
						{
							$pricestracker_prixReduitHT=$pricestracker_product->price-$pricestracker_reducHT->reduction;

							$pricestracker_reducHT->reduction=number_format($pricestracker_prix_reference-$pricestracker_prixHT_nvo_promo,6,'.','');
							$pricestracker_reducHT->reduction_tax=0;
							if($pricestracker_reducHT->reduction>0)
							{
								deleteAllSpecificPrices();
								$pricestracker_reducHT->id=NULL;
								$pricestracker_reducHT->save();
							}

							else $pricestracker_reducHT->delete();
							$pricestracker_edit_prix_promo=false;
						}
						//pourcentage
						elseif($pricestracker_reducHT->reduction_type=='percentage')
						{
							$pricestracker_prixReduitHT=$pricestracker_product->price*(1-$pricestracker_reducHT->reduction);
							
							$pricestracker_reducHT->reduction=number_format(($pricestracker_prix_reference-$pricestracker_prixHT_nvo_promo)/$pricestracker_prix_reference,6,'.','');
							$pricestracker_reducHT->reduction_tax=0;
							if($pricestracker_reducHT->reduction>0)
							{
								deleteAllSpecificPrices();
								$pricestracker_reducHT->id=NULL;
								$pricestracker_reducHT->save();
							}
							else $pricestracker_reducHT->delete();
							$pricestracker_edit_prix_promo=false;
						}
					}
					//montant de remplacement
					else
					{
						$pricestracker_prixReduitHT=$pricestracker_reducHT->price;
						$pricestracker_reducHT->price=number_format($pricestracker_prixHT_nvo_promo,6,'.','');
						$pricestracker_reducHT->reduction_tax=0;
						deleteAllSpecificPrices();
						$pricestracker_reducHT->id=NULL;
						$pricestracker_reducHT->save();
						$pricestracker_edit_prix_promo=false;
					}
				}
				if($pricestracker_edit_prix_promo)
				{
					$pricestracker_reducHT = new SpecificPrice();
					$pricestracker_reducHT->id_product = (int)$idProduct;
					$pricestracker_reducHT->id_customer = 0;
					$pricestracker_reducHT->id_shop = 0;
					$pricestracker_reducHT->id_country = 0;
					$pricestracker_reducHT->id_currency = 0;
					$pricestracker_reducHT->id_group = 0;
					$pricestracker_reducHT->from_quantity = 1;
					$pricestracker_reducHT->from = '0000-00-00 00:00:00';
					$pricestracker_reducHT->to = '0000-00-00 00:00:00';
					
					if($specificPriceType=='amount')
					{
						$pricestracker_reducHT->price=-1;
						$pricestracker_reducHT->reduction = number_format($pricestracker_prix_reference-$pricestracker_prixHT_nvo_promo,6,'.','');
						$pricestracker_reducHT->reduction_type = 'amount';
						$pricestracker_reducHT->reduction_tax=0;
						deleteAllSpecificPrices();
						$pricestracker_reducHT->id=NULL;
						$pricestracker_reducHT->save();
					}
					elseif($specificPriceType=='percentage')
					{
						$pricestracker_reducHT->price=-1;
						$pricestracker_reducHT->reduction = number_format(($pricestracker_prix_reference-$pricestracker_prixHT_nvo_promo)/$pricestracker_prix_reference,6,'.','');
						$pricestracker_reducHT->reduction_type = 'percentage';
						$pricestracker_reducHT->reduction_tax=0;
						deleteAllSpecificPrices();
						$pricestracker_reducHT->id=NULL;
						//print_r($pricestracker_reducHT); echo '('.$pricestracker_prix_reference.'-'.$pricestracker_prixHT_nvo_promo.')/'.$pricestracker_prix_reference;
						if(abs($pricestracker_reducHT->reduction)>=0.0001 && $pricestracker_reducHT->reduction>=0) $pricestracker_reducHT->save();
					}
					else
					{
						$pricestracker_reducHT->price=number_format($pricestracker_prixHT_nvo_promo,6,'.','');
						$pricestracker_reducHT->reduction = 0;
						$pricestracker_reducHT->reduction_tax=0;
						$pricestracker_reducHT->reduction_type = 'amount';
						deleteAllSpecificPrices();
						$pricestracker_reducHT->id=NULL;
						$pricestracker_reducHT->save();
						
					}

				}

			}
			else
			{
				DbCoreCompaSPpricestracker::update(
					'product',
					array(																							//DIAMOND
						'price'=>(float)$pricestracker_prixHT_nvo_promo,
					), 'id_product = '.(int)$idProduct													//DIAMOND
				);
				DbCoreCompaSPpricestracker::update(																				//DIAMOND
					'product_shop',																					//DIAMOND
					array(
						'price'=>(float)$pricestracker_prixHT_nvo_promo,															//DIAMOND
					), 'id_product = '.(int)$idProduct
				);
			}
			
			Product::flushPriceCache();

			$pricestracker_nbChangementPrix++;																		//DIAMOND
		}
		if($alertMail !=false)
		{
			if(is_string($email_alertMail)) $emails=explode(',', $email_alertMail);
			else $emails=array(Configuration::get('PS_SHOP_EMAIL'));
			foreach($emails as $email)
			{
				$message='';
				if($message_alertMail) $message=$message_alertMail; 												//DIAMOND
				Mail::Send($pricestracker_contexte->language->id, 'alerte', Mail::l('Alert PricesTracker : Pricing rule '.$pricestracker_regle['nom'], $pricestracker_contexte->language->id), 																			//DIAMOND
	array(	'{regle}' => $pricestracker_regle['nom'].($pricestracker_verif['arguments']?(' ('.$pricestracker_verif['arguments'].')'):''),
			'{idProduct}' => $idProduct,																			//DIAMOND
			'{nomProduit}' => $pricestracker_product->name[$pricestracker_contexte->language->id],
			'{oldPrice}' =>$pricestracker_oldPrice,																		//DIAMOND
			'{competitorUrl}' => $competitorUrl,
			'{competitorId}' => $competitorId,
			'{competitorPrice}' => $competitorPrice,																//DIAMOND
			'{newPrice}' => $pricestracker_newPrice,
			'{date}' => date("Y/m/d H:i:s"),
			'{var_newPrice}' => $pricestracker_newPrice,																		//DIAMOND
			'{message}' => $message,																		//DIAMOND
			)
				, pSQL($email), null, null, null, null, null, dirname(__FILE__).'/../mails/');						//DIAMOND
	
				$pricestracker_nbAlertes++;		
			}//DIAMOND
		}
		if($alertMailGrouped != false)
		{
			$pricestracker_envoiAlertesGroupees.="  <tr>
    <td>".($pricestracker_regle['nom'].($pricestracker_verif['arguments']?(' ('.$pricestracker_verif['arguments'].')'):''))."</td>
    <td>".$pricestracker_product->name[$pricestracker_contexte->language->id]." ($idProduct)</td>
    <td>$competitorUrl</td>
    <td>$competitorId</td>
    <td>$competitorPrice</td>
    <td>$pricestracker_oldPrice</td>
    <td>$pricestracker_newPrice</td>
    <td>$pricestracker_newPrice</td>
  </tr>
";
			$pricestracker_nbAlertes++;																				//DIAMOND
		}

		if($newArguments !=false)
		{
			DbCoreCompaSPpricestracker::update(																				//DIAMOND
				'pricestracker_regles_association',																					//DIAMOND
				array(
					'arguments'=>pSQL($newArguments),															//DIAMOND
				), 'id_regles_association = '.(int)$pricestracker_verif['id_regles_association']
			);
		}
		if($priceProposal !=false)
		{
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_proposition`
			WHERE id_product='.(int)$idProduct);
			
			DbCoreCompaSPpricestracker::insert(																				//DIAMOND
				'pricestracker_proposition',																					//DIAMOND
				array(
					'prix'=>(float)$priceProposal,
					'id_product'=>(int)$idProduct
					)
			);
		}
	}																											//DIAMOND
}
	
	
if($pricestracker_envoiAlertesGroupees)																											//DIAMOND
{
	if(is_string($email_alertMail)) $emails=explode(',', $email_alertMail);
	else $emails=array(Configuration::get('PS_SHOP_EMAIL'));
	foreach($emails as $email)
	{														//DIAMOND
		Mail::Send($pricestracker_contexte->language->id, 'alerte_groupe', Mail::l('Alerts PricesTracker', $pricestracker_contexte->language->id), 																			//DIAMOND
	array(	'{regles}' => $pricestracker_envoiAlertesGroupees,
	'{date}' => date("Y/m/d H:i:s"),
	)
		, pSQL($email), null, null, null, null, null, dirname(__FILE__).'/../mails/');						//DIAMOND
	}
}																												//DIAMOND

																												//DIAMOND
echo "<br /><br />
Number of checking : $pricestracker_nbVerif <br />
Number of price changes : $pricestracker_nbChangementPrix <br />
Number of alerts sent : $pricestracker_nbAlertes";
																												//DIAMOND