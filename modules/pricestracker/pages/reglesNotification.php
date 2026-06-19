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


$id_associations=(int)Tools::getValue('id');

$isAlerteMail= Db::getInstance()->getValue('
SELECT id_regles_association
FROM `'._DB_PREFIX_.'pricestracker_regles_association`
WHERE id_associations='.$id_associations.' AND id_regles=1');

if($isAlerteMail)
{
	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pricestracker_regles_association`
	WHERE id_associations='.$id_associations.' AND id_regles=1');
	echo '0';
}
else
{
	$prix= Db::getInstance()->getValue('
	SELECT PE.prix
	FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE,`'._DB_PREFIX_.'pricestracker_associations` A
	WHERE A.id_associations='.$id_associations.' AND PE.id_produits_etrangers=A.id_produits_etrangers');

	DbCoreCompaSPpricestracker::insert(
		'pricestracker_regles_association',
		array(
			'id_associations'=>$id_associations,
			'id_regles'=>1,
			'arguments'=>str_replace("'","\'",$prix),
		)
	);
	echo '1';
}