<?php
ignore_user_abort();
set_time_limit(0);

//Initialisation Prestashop page module
include_once('../../../config/config.inc.php');
require_once('../../../init.php');

error_reporting(E_ERROR | E_WARNING | E_PARSE);


$ttc=true;
if(Tools::isSubmit('exclTax')) $ttc=false;

$date=time();


if(Tools::getValue("pack")) $produits=Product::getProducts((int)Configuration::get('PS_LANG_DEFAULT'),(((int)Tools::getValue("pack")-1)*200),200,'id_product','ASC');
else $produits=Product::getProducts((int)Configuration::get('PS_LANG_DEFAULT'), 0, 500000,'id_product','ASC');

if(!$produits && Tools::getValue("pack"))
{
	echo'STOP';
	die;
}

foreach($produits as $produit)
{
	$id_product=(int)$produit['id_product'];
	$prixMonProduit=Product::getPriceStatic($id_product,$ttc);
	
	Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'pricestracker_historique` (id_product,date,prix) VALUE (\''.$id_product.'\',\''.$date.'\',\''.$prixMonProduit.'\')');
	

	Db::getInstance()->update(
		'product',
		array(
			'prix_precalcule'=>$prixMonProduit,
		), 'id_product = '.$id_product	
	);
	$produitsEtrangers = Db::getInstance()->executeS('
	SELECT A.id_produits_etrangers AS id_produits_etrangers,prix
	FROM `'._DB_PREFIX_.'pricestracker_associations` A,'._DB_PREFIX_.'pricestracker_produits_etrangers PE
	WHERE A.id_product='.$id_product.' AND A.id_produits_etrangers=PE.id_produits_etrangers
	GROUP BY id_produits_etrangers');

	foreach($produitsEtrangers as $pe)
	{
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'pricestracker_historique` (id_product,date,id_produits_etrangers,prix) VALUE (\''.$id_product.'\',\''.$date.'\',\''.$pe['id_produits_etrangers'].'\',\''.$pe['prix'].'\')');
	}
}

echo'DONE ('.count($produits).' products)';