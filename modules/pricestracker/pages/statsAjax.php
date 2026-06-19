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


 function csv($dataArray,$delimiter=',',$enclosure= '"')
  {
  // Write a line to a file
  // $dataArray = the data to write out
  // $delimeter = the field separator
 
  // Build the string
  $string = "";
 
  // No leading delimiter
  $writeDelimiter = FALSE;
  foreach($dataArray as $dataElement)
   {
    // Replaces a double quote with two double quotes
    $dataElement=str_replace("\"", "\"\"", ($dataElement));
   
    // Adds a delimiter before each field (except the first)
    if($writeDelimiter) $string .= $delimiter;
   
    // Encloses each field with $enclosure and adds it to the string
    $string .= $enclosure . $dataElement . $enclosure;
   
    // Delimiters are used every time except the first.
    $writeDelimiter = TRUE;
   } // end foreach($dataArray as $dataElement)
 
  // Append new line
  $string .= "\n";
 
 	return $string;
  }
  
  $allCat=Category::getCategories();
  
  function categorieInfo($id , $allCat)
  {
	  foreach($allCat as $id_parent=>$sous_cat)
	  {
		   foreach($sous_cat as $id_cat=>$categorie)
		   {
			   $categorie=$categorie['infos'];
			   if($id==$id_cat)
			   {
				   return $categorie;
			   }
		   }
	  }
	  return NULL;
  }
  
  function getParentCat($id , $allCat)
  {
	  foreach($allCat as $id_parent=>$sous_cat)
	  {
		   foreach($sous_cat as $id_cat=>$categorie)
		   {
			   if($id_cat==$id) return $id_parent;
		   }
	  }
	  return NULL;
  }
  
  function categoriesBases($ids , $allCat)
  {
	  $ids=array_unique($ids);
	  foreach($ids as $id)
	  {
		  if($id)
		  {
			  $parent=$id;
			  do {
				  $parent=getParentCat($parent , $allCat);
				  foreach($ids as $k=>$id2)
				  {
					  if($id2==$parent) $ids[$k]=NULL;
				  }
			  } while($parent);
		  }
	  }
	  
	  return array_unique($ids);
  }
  
  function categoriesNiveau($ids , $allCat)
  {
	  $bases=categoriesBases($ids , $allCat);
	  $retour=array();
	  
	  foreach($bases as $id)
	  {
		  if($id)
		  {
			  $cat1='';
			  $cat2='';
			  $cat3='';
			  $cat4='';
			  
			  $infos=categorieInfo($id , $allCat);
			  if($infos['level_depth']==1) $cat1=$infos['name'];
			  elseif($infos['level_depth']==2) $cat2=$infos['name'];
			  elseif($infos['level_depth']==3) $cat3=$infos['name'];
			  elseif($infos['level_depth']==4) $cat4=$infos['name'];
			  
			  $parent=$id;
			  do {
				  $parent=getParentCat($parent , $allCat);
				  $infos=categorieInfo($parent , $allCat);
				  if($infos['level_depth']==1) $cat1=$infos['name'];
				  elseif($infos['level_depth']==2) $cat2=$infos['name'];
				  elseif($infos['level_depth']==3) $cat3=$infos['name'];
				  elseif($infos['level_depth']==4) $cat4=$infos['name'];
			  } while($parent);
			  
			  $retour[]=array($cat1,$cat2,$cat3,$cat4);
		  }
	  }
	  
	  return $retour;
  }
  

echo csv(array('Price','Price difference (You - Competitor)','Website/Competitor','Category 1','Category 2','Category 3','Category 4','Manufacturer','Supplier'));

//concurrents
$concurrents = Db::getInstance()->executeS('
SELECT nom,id_concurrents
FROM `'._DB_PREFIX_.$this->name.'_concurrents`
WHERE 1=1');
$tabConcurrents=array();
foreach($concurrents as $c)
{
	$tabConcurrents[$c['id_concurrents']] = $c['nom'];
}

$produits_sql=Db::getInstance()->executeS('
SELECT E.prix AS prixConcurrent,A.id_product AS id_product,id_concurrents
FROM `'._DB_PREFIX_.$this->name.'_produits_etrangers` E,`'._DB_PREFIX_.$this->name.'_associations` A
WHERE A.id_produits_etrangers=E.id_produits_etrangers
GROUP BY A.id_associations');

$produits_deja_affiches=array();
foreach($produits_sql as $pe)
{
	$prixConcurrent=$pe['prixConcurrent'];
	$id_product=$pe['id_product'];
	$concurrent=$tabConcurrents[ $pe['id_concurrents'] ];
	
	$product=new Product($id_product);
	$monPrix=$product->getPrice();
	$difference=$monPrix-$prixConcurrent;
	$marque=Manufacturer::getNameById($product->id_manufacturer);
	$fournisseur=Supplier::getNameById($product->id_supplier);
	
	$categories=categoriesNiveau( $product->getCategories() , $allCat );
	foreach($categories as $cat)
	{
		$csvMonPrix=array($monPrix,0,'Your site');
		$csvPrixConcurrent=array($prixConcurrent,$monPrix-$prixConcurrent,$concurrent);
		
		$csvMonPrix=array_merge($csvMonPrix,$cat);
		$csvPrixConcurrent=array_merge($csvPrixConcurrent,$cat);
		
		$csvMonPrix[]=$marque;
		$csvPrixConcurrent[]=$marque;
		$csvMonPrix[]=$fournisseur;
		$csvPrixConcurrent[]=$fournisseur;
		
		echo csv($csvPrixConcurrent);
		if(!in_array($id_product,$produits_deja_affiches))
		{
			echo csv($csvMonPrix);
			$produits_deja_affiches[]=$id_product;
		}
	}
}
