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
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<!-- * PROPERTY
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
<script src="<?php
if(_PS_VERSION_ >= '1.5' )
{
	$jq=Media::getJqueryPath();
	echo $jq[0];
}
else
{
	echo '../js/jquery/jquery.min.js';
}
?>"></script>

</head>

<body>
<a name="top"></a>
<?php

$display_proximite=Configuration::get('pricestracker_MONTRER_PROX');
$deja_afficher=array();
function bloc($produit,$id_produits_etrangers_deja_associes,$lien,$id_product,$isProximite=false)
{
	global $deja_afficher;
	if($deja_afficher && in_array($produit['id_produits_etrangers'],$deja_afficher)) return;
	else $deja_afficher[]=$produit['id_produits_etrangers'];
	$display_proximite=Configuration::get('pricestracker_MONTRER_PROX');
	$associer=false;
	if(in_array($produit['id_produits_etrangers'],$id_produits_etrangers_deja_associes)) $associer=true;
	
	echo'<a id="" href="#" class="l'.$produit['id_produits_etrangers'].' '.($associer?'actif':'passif').' '.($isProximite?'proximite':'').'" onclick="isActif=false;  if($(this).hasClass(\'actif\')) isActif=true; $.ajax(\''.$lien.'&associationAjax&id_product='.$id_product.'&id_produits_etrangers='.$produit['id_produits_etrangers'].'&associer=\'+(isActif?0:1)).done(function( data ) { $(\'.l'.$produit['id_produits_etrangers'].'\').removeClass(\'passif\'); $(\'.l'.$produit['id_produits_etrangers'].'\').removeClass(\'actif\'); if(data==1) $(\'.l'.$produit['id_produits_etrangers'].'\').addClass(\'actif\'); else $(\'.l'.$produit['id_produits_etrangers'].'\').addClass(\'passif\'); }); return false;"'.(($produit['image'] && $produit['image']!='null')?' onmouseover="afficherImg(\''.$produit['image'].'\')" onmouseout="cacherImg()"':'').'>'.(($produit['image'] && $produit['image']!='null')?'<img src="'.$produit['image'].'" height="20" align="absmiddle">':'').$produit['nom'].' - '.Tools::displayPrice($produit['prix']).(($display_proximite && $isProximite)?' ('.$produit['proximite'].')':'').'</a><a href="'.$produit['lien'].'" target="_blank" class="passif"><img src="'.Tools::getAdminUrl().'/modules/pricestracker/pages/images/external.png" border="0" /></a> &nbsp;
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

$where='';
if(Tools::getValue('rech'))
{
	$recherche=stripAccents(pSQL(str_replace('--PLUS--','+',base64_decode(utf8_encode(Tools::getValue('rech'))))));
	//echo $recherche.'********';
	$mots=preg_split("#\s+#is", $recherche );
	foreach($mots as $mot) $where.=' AND (nom LIKE \'%'.pSQL($mot).'%\' OR reference LIKE \'%'.pSQL($mot).'%\' OR lien LIKE \''.pSQL($recherche).'\' OR lien LIKE \''.pSQL(urldecode($recherche)).'\')';
}
//echo $where; die;

$total=Db::getInstance()->getValue('
SELECT COUNT(id_produits_etrangers)
FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers`
WHERE id_concurrents='.$id_concurrents.$where);

$nbPages=max(1,ceil($total/15));


//pages
echo'<div style="overflow:auto;max-height:40px">Pages: ';
echo pagesProposer($page,$nbPages,$lien.'&association&id_concurrents='.$id_concurrents.'&id_product='.$id_product.'&rech='.urlencode(stripslashes(Tools::getValue('rech'))).'&page=');
echo' <a href="'.$lien.'&association&id_concurrents='.$id_concurrents.'&id_product='.$id_product.'&rech='.urlencode(stripslashes(Tools::getValue('rech'))).'&page=asso">Associations</a> ';
echo'</div>';


//catalogue etranger
$order='';
if(Tools::isSubmit('prix') && $version2!='Silver') $order=' ORDER BY ABS( prix - '.((float)Tools::getValue('prix')).' ) ASC';
elseif(Configuration::get('pricestracker_DERNIERS') && $version2!='Silver') $order=' ORDER BY id_produits_etrangers DESC';

if(Tools::getValue('page')=='asso') $where.=" AND EXISTS ( SELECT id_associations FROM `"._DB_PREFIX_."pricestracker_associations` ASS WHERE ASS.id_product=$id_product AND PE.id_produits_etrangers=ASS.id_produits_etrangers ) ";

$produits = Db::getInstance()->executeS('
SELECT id_produits_etrangers,nom,prix,lien,image
FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE
WHERE id_concurrents='.$id_concurrents.$where.($order?$order:' ORDER BY nom ASC').
(Tools::getValue('page')!='asso' ? (' LIMIT '.(($page-1)*15).',15') :'')
);


//proximité
if($page==1 && Tools::getValue('page')!='asso' && !Tools::isSubmit('prix'))
{
	//associations
	$produitsAsso = Db::getInstance()->executeS('
	SELECT id_produits_etrangers,nom,prix,lien,image
	FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE
	WHERE id_concurrents='.$id_concurrents.$where." AND EXISTS ( SELECT id_associations FROM `"._DB_PREFIX_."pricestracker_associations` ASS WHERE ASS.id_product=$id_product AND PE.id_produits_etrangers=ASS.id_produits_etrangers ) ".($order?$order:' ORDER BY nom ASC'));
	
	
	foreach($produitsAsso as $p)
	{
		bloc($p,$id_produits_etrangers_deja_associes,$lien,$id_product);
	}

	//proximités
	$whereProx='';
	if(Tools::getValue('rech'))
	{
		$recherche=stripAccents(pSQL(str_replace('--PLUS--','+',base64_decode(utf8_encode(Tools::getValue('rech'))))));
		$mots=preg_split("#\s+#is", $recherche );
		foreach($mots as $mot) $whereProx.=' AND (PE.nom LIKE \'%'.pSQL($mot).'%\' OR PE.reference LIKE \'%'.pSQL($mot).'%\' OR PE.lien LIKE \''.pSQL($recherche).'\' OR lien LIKE \''.pSQL(urldecode($recherche)).'\')';
	}

	$produitsProx = Db::getInstance()->executeS('
	SELECT PE.id_produits_etrangers AS id_produits_etrangers,PE.nom AS nom,PE.prix AS prix,PE.lien AS lien,PE.image AS image,P.proximite AS proximite
	FROM `'._DB_PREFIX_.'pricestracker_produits_etrangers` PE,`'._DB_PREFIX_.'pricestracker_proximites` P
	WHERE PE.id_concurrents='.$id_concurrents.' AND P.id_product='.$id_product.' AND P.id_produits_etrangers=PE.id_produits_etrangers '.$whereProx.'
	GROUP BY PE.id_produits_etrangers
	ORDER BY P.proximite DESC');
	foreach($produitsProx as $p)
	{
		bloc($p,$id_produits_etrangers_deja_associes,$lien,$id_product,true);
	}
}



//catalogue etranger
foreach($produits as $p)
{
	bloc($p,$id_produits_etrangers_deja_associes,$lien,$id_product);
}

?>
<div id="agrandissement" style="position: absolute;top: 0;height: 100%;right: 0; display:none"><img src="" id="agrandissementImg" style="height:100%" /></div>

</body>
</html>

<?php
	}
}
