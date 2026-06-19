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


$id_product=(int)Tools::getValue('id');

if(Tools::isSubmit('favoris'))
{
	$favoris=(int)Tools::getValue('favoris');
	$id_favoris=(int)Tools::getValue('id_favoris_product');
	
	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_favoris_product`
	WHERE id_product='.$id_product.' AND id_favoris_product='.$id_favoris);

 
	if($favoris)
	{
		DbCoreCompaSPpricestracker::insert(
			'pricestracker_favoris_product',
			array(
				'id_product'=>$id_product,
				'id_favoris'=>$favoris,
				'id_favoris_product'=>$id_favoris?$id_favoris:NULL
			)
		);
	}
}

$favori=Db::getInstance()->executeS('
SELECT id_favoris,id_favoris_product
FROM `'._DB_PREFIX_.'pricestracker_favoris_product`
WHERE id_product='.$id_product);
$favori[]=array('id_favoris'=>'','id_favoris_product'=>'');

//where shop
$id_shop=0;
if($cookie->shopContext && preg_match("#s-([0-9]+)#isU",$cookie->shopContext,$subShop))
{
	$id_shop=$subShop[1];
}

//print_r($favoris_liste);die;

foreach($favori as $ff)
{
	echo'
 <select id="fav'.$id_product.'p'.$ff['id_favoris_product'].'">
    <option value="0">'.$this->l('None').'</option>';

	$favoris_liste = Db::getInstance()->executeS('
	SELECT F.id_favoris AS id_favoris,F.nom AS nom
	FROM `'._DB_PREFIX_.$this->name.'_favoris` F
	WHERE F.id_shop=0 OR F.id_shop='.(int)$id_shop.'
	GROUP BY F.id_favoris');
	
	foreach($favoris_liste as $f_liste) echo '<option value="'.$f_liste['id_favoris'].'" '.($f_liste['id_favoris']==$ff['id_favoris']?'selected="selected"':'').'>'.$f_liste['nom'].'</option> ';
  
echo'
 </select>
     
	 
     <input id="bFav" class="button" type="button" value="OK" onclick="$(\'#dFav'.$id_product.'\').prepend(\'...<br>\'); favoris=$(\'#fav'.$id_product.'p'.$ff['id_favoris_product'].'\').val(); $.ajax(\''.$lien.'&favorisAjax&id='.$id_product.'&id_favoris_product='.$ff['id_favoris_product'].'&favoris=\'+escape( favoris )).done(function( data ) { $(\'#dFav'.$id_product.'\').html(data); '.
																																																																													($this->version2=='Silver'?"alert('".addslashes($this->l('Saved ! But this version does not have this option. To use it you can go higher.'))."');":"")
																																																																																													  .' });" />

<br>
     ';
	 
}