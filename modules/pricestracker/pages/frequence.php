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


$id_produits_etrangers=(int)Tools::getValue('id');											//GOLD - DIAMOND

if(Tools::isSubmit('freq'))																	//GOLD - DIAMOND
{
	$frequence=(float)str_replace(',','.',Tools::getValue('freq'));							//GOLD - DIAMOND
	
	DbCoreCompaSPpricestracker::update(																	//GOLD - DIAMOND
		'pricestracker_produits_etrangers',														//GOLD - DIAMOND
		array(																				//GOLD - DIAMOND
			'frequenceMaj'=>$frequence														//GOLD - DIAMOND
		), 'id_produits_etrangers = '.$id_produits_etrangers								//GOLD - DIAMOND
	);
																							//GOLD - DIAMOND
}																							//GOLD - DIAMOND


																							//GOLD - DIAMOND
$frequenceMaj=Db::getInstance()->getValue('
SELECT frequenceMaj
FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
WHERE id_produits_etrangers='.$id_produits_etrangers);										//GOLD - DIAMOND


echo $frequenceMaj;																			//GOLD - DIAMOND