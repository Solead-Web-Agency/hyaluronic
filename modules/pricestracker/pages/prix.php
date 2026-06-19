<?php
/**
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


$id_product=(int)Tools::getValue('id');
$pricestracker_product=new Product($id_product);

		
$pricestracker_tax=$pricestracker_product->getTaxesRate();

$newPrice=(float)str_replace(',','.',Tools::getValue('prix'));
	
	
$isPrixGros=Configuration::get('pricestracker_PRIXGROS');
$isHT=Configuration::get('pricestracker_HT');
$isPrixBase=Configuration::get('pricestracker_PRIXBASE');
			
if($isHT || $isPrixBase) $pricestracker_tax=0;
																										//DIAMOND
if($newPrice)
{																										//DIAMOND
	$pricestracker_prixHT_nvo=$newPrice / (1 + $pricestracker_tax/100);													//DIAMOND
	$pricestracker_edit_prix=true;
	
	//réduction
	$pricestracker_reducHT=SpecificPrice::getSpecificPrice($id_product,0,0,0,0,1);
	if($pricestracker_reducHT && !$isPrixBase)
	{
		$pricestracker_reducHT=new SpecificPrice($pricestracker_reducHT['id_specific_price']);
		if($pricestracker_reducHT->price==-1)
		{
			if($pricestracker_reducHT->reduction_type=='amount')
			{
				$pricestracker_prixReduitHT=$pricestracker_product->price-$pricestracker_reducHT->reduction;
				if($pricestracker_prixHT_nvo<$pricestracker_reducHT->price)
				{
					$pricestracker_reducHT->reduction=number_format($pricestracker_reducHT->price-$pricestracker_prixHT_nvo,6,'.','');
					$pricestracker_reducHT->save();
					$pricestracker_edit_prix=false;
				}
				else $pricestracker_reducHT->delete();
			}
			elseif($pricestracker_reducHT->reduction_type=='percentage')
			{
				$pricestracker_prixReduitHT=$pricestracker_product->price*(1-$pricestracker_reducHT->reduction);
				if($pricestracker_prixHT_nvo<$pricestracker_reducHT->price)
				{
					$pricestracker_reducHT->reduction=number_format(($pricestracker_reducHT->price-$pricestracker_prixHT_nvo)/$pricestracker_reducHT->price,6,'.','');
					$pricestracker_reducHT->save();
					$pricestracker_edit_prix=false;
				}
				else $pricestracker_reducHT->delete();
			}
		}
		else
		{
			$pricestracker_prixReduitHT=$pricestracker_reducHT->price;
			if($pricestracker_prixHT_nvo<$pricestracker_reducHT->price)
			{
				$pricestracker_reducHT->price=number_format($pricestracker_prixHT_nvo,6,'.','');
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
	
}

$nvo_produit=new Product($id_product);


if($isPrixBase) $monPrix=$nvo_produit->price;
else
{
	if($isPrixGros)
	{
		if($isHT) $monPrix=$nvo_produit->getPriceMin(false);
		else $monPrix=$nvo_produit->getPriceMin();
	}
	else
	{
		if($isHT) $monPrix=$nvo_produit->getPrice(false);
		else $monPrix=$nvo_produit->getPrice();
	}
}
Product::flushPriceCache();


echo $nvo_produit->convertAndFormatPrice($monPrix);


