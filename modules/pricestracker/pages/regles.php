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


$id_associations=(int)Tools::getValue('id');

if(Tools::isSubmit('regle'))
{
	$regle=(int)Tools::getValue('regle');
	$id_regle=(int)Tools::getValue('id_regle_produit');
	
	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_regles_association`
	WHERE id_associations='.$id_associations.' AND id_regles_association='.$id_regle);

	if($regle)
	{
		DbCoreCompaSPpricestracker::insert(
			'pricestracker_regles_association',
			array(
				'id_associations'=>$id_associations,
				'id_regles'=>$regle,
				'arguments'=>str_replace("'","\'",Tools::getValue('arguments')),
				'id_regles_association'=>$id_regle?$id_regle:NULL
			)
		);
	}
}

$regles_sql=Db::getInstance()->executeS('
SELECT id_regles,arguments,id_regles_association
FROM `'._DB_PREFIX_.'pricestracker_regles_association`
WHERE id_associations='.$id_associations);
$regles_sql[]=array('id_regles'=>'','arguments'=>'','id_regles_association'=>'');


//where shop
$id_shop=0;
if($cookie->shopContext && preg_match("#s-([0-9]+)#isU",$cookie->shopContext,$subShop))
{
	$id_shop=$subShop[1];
}

//print_r($favoris_liste);die;

foreach($regles_sql as $rr)
{
	echo' <select id="regle'.$id_associations.'p'.$rr['id_regles_association'].'">
    <option value="0" selected="selected">'.$this->l('None').'</option> ';
	
	$regles_liste = Db::getInstance()->executeS('
	SELECT R.id_regles AS id_regles,R.nom AS nom,RA.id_regles_association AS id_regles_association
	FROM `'._DB_PREFIX_.$this->name.'_regles` R LEFT JOIN `'._DB_PREFIX_.$this->name.'_regles_association` RA ON (RA.id_regles=R.id_regles AND RA.id_associations='.$id_associations.' AND RA.id_regles_association='.(int)$rr['id_regles_association'].')
	WHERE 1=1
	GROUP BY R.id_regles');
	
	//print_r($regles_liste);die;

	foreach($regles_liste as $r_liste) echo '<option value="'.$r_liste['id_regles'].'" '.($r_liste['id_regles_association']?'selected="selected"':'').'>'.$r_liste['nom'].'</option> ';


	echo'
 </select>
     
     <input id="arguments'.$id_associations.'p'.$rr['id_regles_association'].'" type="text" value="'.$rr['arguments'].'" title="'.$this->l('Arguments (between ,)').'" size="10" />
	 
     <input id="bRegle" class="button" type="button" value="OK" onclick="$(\'#dRegle'.$id_associations.'\').prepend(\'...<br>\'); regle=$(\'#regle'.$id_associations.'p'.$rr['id_regles_association'].'\').val(); arguments=$(\'#arguments'.$id_associations.'p'.$rr['id_regles_association'].'\').val(); $.ajax(\''.$lien.'&regleAjax&id='.$id_associations.'&id_regle_produit='.$rr['id_regles_association'].'&regle=\'+escape( regle )+\'&arguments=\'+escape( arguments )).done(function( data ) { $(\'#dRegle'.$id_associations.'\').html(data); '.
																																																																													(($this->version2!='Diamond' && $this->version2!='MEGA')?"alert('".addslashes($this->l('Saved ! But this version does not have this option. To use it you can go higher.'))."');":"")
																																																																																													  .' });" />

<br>
     ';
}