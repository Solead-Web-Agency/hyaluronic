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


<!-- PricesTracker -->

<style type="text/css">
#categories-tree {
	overflow: auto;
	max-height: 400px;
}
.tree-folder { clear:both }
.tree-item-name { clear:both }
.toolbarBox .pageTitle h3 {
    font-size: 2em;
    font-weight: bold;
    line-height: 48px;
    margin: 0px;
    padding: 0px;
}
.toolbarBox .pageTitle {
    margin-left: 10px;
    line-height: 48px;
}
#container {
    text-align: left;
}
.toolbarBox ul.cc_button {
    float: right;
    margin: 0px;
    padding: 0px;
}
ul, ol {
    list-style: outside none none;
}
.toolbarBox {
    background-color: #F8F8F8;
    border: 1px solid #CCC;
    margin-bottom: 10px;
    padding: 10px 0px;
    border-radius: 3px;
	position: relative;
}
.toolbar-placeholder {
    position: relative;
}
.toolbarBox ul.cc_button li {
    color: #666;
    float: left;
    height: 48px;
    list-style: outside none none;
    padding: 1px 1px 3px 4px;
    text-align: center;
}
.toolbarBox .toolbar_btn span {
    display: block;
    float: none;
    height: 32px;
    margin: 0px auto;
    width: 32px;
}
.toolbarBox a.toolbar_btn {
    border-width: 1px;
    font-size: 11px;
    cursor: pointer;
    display: block;
    float: left;
    padding: 1px 5px;
    white-space: nowrap;
    text-shadow: 0px 1px 0px #FFF;
}
fieldset {
position: relative !important;
padding: 20px !important;
margin-bottom: 20px !important;
border: 1px solid #E6E6E6 !important;
background-color: #FFF !important;
border-radius: 5px !important;
box-shadow: 0px 2px 0px rgba(0, 0, 0, 0.1), 0px 0px 0px 3px #FFF inset !important;	
}
legend {
font-family: "Ubuntu Condensed",Helvetica,Arial,sans-serif;
font-weight: 400;
font-size: 14px;
text-overflow: ellipsis;
white-space: nowrap;
color: #555;	
border: 1px solid #E6E6E6 !important;
background-color: #FFF !important;
border-radius: 5px !important;
}

</style>


<script>
$(document).ready(function() {
        document.title = '{l s='Prices of competitors' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>



<br /><br />

{if $modeTest}    
<a href="https://www.storeinterfacer.com/pricestracker.php" target="_blank" class="button">{l s='Buy the module' mod='pricestracker'}</a><br />

{/if}



<br />


{if $version2 eq 'MEGA'}




<div id="filtres" style="clear:both">

<!-- popup-->


<style type="text/css">
.shadowed {
  -moz-box-shadow:0px 0px 5px #444444;
  -webkit-box-shadow:0px 0px 5px #444444;
  box-shadow:0px 0px 5px #444444;
}

.video-container-wrapper {
  width: 100%;
  height: 100%;
  background: rgba(37,37,37,.7);
  text-align: center;
  position: fixed;
  top:0; left: 0;
  z-index:50;
  display: none;
  opacity: 0;
}
.video-container-wrapper:before {
  content: ' ';
  display: inline-block;
  height: 100%;
  vertical-align: middle;
}

.sizewrapper {
  width: 50%;
  /* Would normally be 1920px, limited because codepen's screen is small */
  max-width: 920px;
  vertical-align: middle;
  position: relative;
  background: rgba(255,255,255,0);
  z-index:51;
  display:none;
}

#videowrapper {
  position: relative;
  padding-top: 25px;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  height: 0;
}
#videowrapper iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.close {
  position: absolute;
  right: -42px; top: 0;
  color: #fff;
  cursor:pointer;
  font-size: 24px;
  line-height: 32px;
  text-align: center;
  background-color: orange;
  width: 32px; height: 32px;
}

.go-to-yt {
  padding: 16px;
  margin: 8px 0 0;
  float: right;
  text-align: right;
  background-color: #fff;
  display: inline-block;
}
.go-to-yt a {
  text-decoration: none;
}
</style>

{if strpos(Tools::getShopDomain(), 'pbs-video.com') === false}
<div style="text-align: right;float: right; width: 114px;">
            <a href="https://www.youtube.com/watch?{if $is_francais}v=2p-BT_2_NSw&index=5&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}v=BkYY5Kbiu5s&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank" class="open-yt-lightbox">
        <img alt="Video tuto" src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/video-tutorial.png" style="width:80px" />
    </a>
</div>


<div class="video-container-wrapper lightbox-backdrop">
  <div class="sizewrapper box">
    <div class="close shadowed">X</div>
    <div id="videowrapper" class="shadowed">
      <iframe width="560" height="315" src="https://www.youtube.com/embed/{if $is_francais}2p-BT_2_NSw?index=5&enablejsapi=1&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}BkYY5Kbiu5s?si=-9A_Pu4LZD3ugrIr{/if}&ab_channel=idIATech" frameborder="0" id="yt-iframe" allowfullscreen></iframe>
    </div>
    <div class="go-to-yt shadowed"><a href="https://www.youtube.com/watch?{if $is_francais}v=2p-BT_2_NSw&index=5&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}v=BkYY5Kbiu5s&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank">{l s='Video not loading? Watch on Youtube' mod='pricestracker'} ></a></div>
  </div>

</div>
{/if}

<!--Fin popup-->



{if isset($category_tree)}
<input id="is_id_category" type="checkbox" value="1" {if $id_category}checked{/if} /> {l s='Filter by category' mod='pricestracker'}
{/if}
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input id="is_id_concurrents" type="checkbox" value="1" {if $id_concurrents}checked{/if} /> {l s='Filter by competitor' mod='pricestracker'}

<script>
$( document ).ready(function() {
	$('#is_id_category').click(function() {
		if($(this).is(':checked')) $('#container_category_tree').show();
		else window.location=document.location.href.replace(/&id_category=[0-9]+/gi,'')
	});
	$('#is_id_concurrents').click(function() {
		if($(this).is(':checked')) $('#container_concurrents').show();
		else window.location=document.location.href.replace(/&id_concurrents=[\-0-9,]+/gi,'')
	});
});

function submitFiltresPrix()
{
	window.location=document.location.href.replace(/&(prix_min|prix_max|diff_max|diff_min|type_diff)=[^=&]*/gi,'')+"&prix_min="+removeUnknownCar($('#prix_min').val())+"&prix_max="+removeUnknownCar($('#prix_max').val())+"&diff_max="+removeUnknownCar($('#diff_max').val())+"&diff_min="+removeUnknownCar($('#diff_min').val())+"&type_diff="+removeUnknownCar($('#type_diff').val())
}
</script>

<form onsubmit="submitFiltresPrix(); return false" style="display:inline">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 {l s='Filter by price' mod='pricestracker'} : 
{l s='Between' mod='pricestracker'}
<input name="prix_min" id="prix_min" type="text" style="width:50px" value="{$smarty.get.prix_min}" />
{l s='and' mod='pricestracker'}
<input name="prix_max" id="prix_max" type="text" style="width:50px" value="{$smarty.get.prix_max}" /> {$devise}



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 {l s='Filter by difference of price' mod='pricestracker'} : 
{l s='Between' mod='pricestracker'}
<input name="diff_min" id="diff_min" type="text" style="width:50px" value="{$smarty.get.diff_min}" />
{l s='and' mod='pricestracker'}
<input name="diff_max" id="diff_max" type="text" style="width:50px" value="{$smarty.get.diff_max}" />
 <select name="type_diff" id="type_diff">
   <option value="pourc" {if $smarty.get.type_diff eq 'pourc'}selected="selected"{/if}>%</option>
   <option value="devise" {if $smarty.get.type_diff eq 'devise'}selected="selected"{/if}>{$devise}</option>
 </select>
 ({l s='negative = your price < competitor' mod='pricestracker'})



 
 
 
 
 
 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 {l s='Filter by brand' mod='pricestracker'} : 
 
 <select onchange="window.location=document.location.href.replace(/&id_manufacturer=[\-0-9,]+/gi,'')+'&id_manufacturer='+removeUnknownCar($(this).val())" style="max-width: 100px;">
   <option value="">{l s='All' mod='pricestracker'}</option>
   {foreach key=id_manufacturer_m item=marque from=$marques}
       <option value="{$id_manufacturer_m}" {if $id_manufacturer_m eq $id_manufacturer}selected="selected"{/if}>{$marque}</option>
   {/foreach}
 
 </select>




 
 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 {l s='Order by' mod='pricestracker'} : 
     <input type="radio" {if $orderBy eq 'name' OR !$orderBy}checked="checked"{/if} name="orderBy" value="name" id="orderBy_0" onclick="window.location=document.location.href.replace(/&orderBy=[a-z]+/gi,'')+'&orderBy='+removeUnknownCar($(this).val())" />
     {l s='Name' mod='pricestracker'}
   
     <input type="radio" {if $orderBy eq 'creation'}checked="checked"{/if} name="orderBy" value="creation" id="orderBy_1" onclick="window.location=document.location.href.replace(/&orderBy=[a-z]+/gi,'')+'&orderBy='+removeUnknownCar($(this).val())" />
     {l s='Date of creation' mod='pricestracker'}
     <input type="radio" {if $orderBy eq 'reference'}checked="checked"{/if} name="orderBy" value="reference" id="orderBy_1" onclick="window.location=document.location.href.replace(/&orderBy=[a-z]+/gi,'')+'&orderBy='+removeUnknownCar($(this).val())" />
     {l s='Reference' mod='pricestracker'}
     
     
     



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input name="" type="submit" value="OK" />
</form>
</div>


{if isset($category_tree)}
<div id="container_category_tree" style="border: 1px solid #E6E6E6; display:{if !$id_category}none{/if}">
    {$category_tree}
</div>
<div class="clear"></div>
{/if}



<script>
function removeUnknownCar(str) {
  var strArr = str.split('');
  var newStr = '';

  for (var i = 0; i < strArr.length; i++) {
    var char = strArr[i];
    var charNo = char.charCodeAt(0);

    if (charNo === 163) {
      newStr += char;
      continue;
    }

    if (charNo > 31 && charNo <= 255) {
      newStr += char;
    }
  }

  return newStr;
}

function actionCategory()
{
	$('#categories-tree input').click(function() {
		id_category=removeUnknownCar($(this).val());
		window.location=document.location.href.replace(/&id_category=[0-9]+/gi,'') + "&id_category="+id_category
	});	
	$('#categories-tree i.icon-folder-close').click(function() {
		actionCategory()
		setTimeout(function(){ actionCategory(); }, 500);
		setTimeout(function(){ actionCategory(); }, 2000);
	});
	$('#categories-tree label.tree-toggler').click(function() {
		actionCategory()
		setTimeout(function(){ actionCategory(); }, 500);
		setTimeout(function(){ actionCategory(); }, 2000);
	});
	$('#collapse-all-categories-tree').click(function() {
		actionCategory()
		setTimeout(function(){ actionCategory(); }, 500);
		setTimeout(function(){ actionCategory(); }, 2000);
	});
	$('#expand-all-categories-tree').click(function() {
		actionCategory()
		setTimeout(function(){ actionCategory(); }, 500);
		setTimeout(function(){ actionCategory(); }, 2000);
	});
}

$( document ).ready(function() {
	//catégories
	actionCategory()
	setTimeout(function(){ actionCategory(); }, 500);
	
	//concurrents
	$('#container_concurrents input').click(function() {
		var chaineConc='-1';
		$('#container_concurrents input').each(function() {
			if($(this).is(':checked'))
			{
				id_concurrent=$(this).attr('name').replace('concurrent_','');
				chaineConc+=","+id_concurrent
			}
			
		});
		if(chaineConc=='-1') window.location=document.location.href.replace(/&id_concurrents=[\-0-9,]+/gi,'')
		else  window.location=document.location.href.replace(/&id_concurrents=[\-0-9,]+/gi,'') + "&id_concurrents="+chaineConc
	});

});
</script>


<div id="container_concurrents" style="border: 1px solid #E6E6E6; display:{if !$id_concurrents}none{/if}">
{foreach key=kconc item=concurrent from=$concurrents}

  <label style="float:none">
    <input type="checkbox" name="concurrent_{$kconc}" value="1" {if in_array($kconc,$id_concurrents)}checked="checked"{/if} />
    {$concurrent}</label><br />

{/foreach} <br />
</div>
<br />
{/if}


<form action="" method="post" id="formPrinc" style="clear:both">


{assign var="txtUpgrade" value={l s='Saved ! But this version does not have this option. To use it you can go higher.' mod='pricestracker'}}

<div class="toolbar-placeholder bootstrap">
	<div class="toolbarBox toolbarHead">
		
			<ul class="cc_button">
              <li style="vertical-align:middle; padding-top:20px; padding-right:30px; overflow: auto; max-height: 50px; clear: both;">
            {l s='Page' mod='pricestracker'} :
             					
{$htmlPage}

              </li>
                             
                             
                    <li style="vertical-align:middle; {if $ps_version >= '1.6'}margin-top:-10px;{/if} padding-right:30px">
 {l s='Search' mod='pricestracker'} : 
 
 <input name="rech" id="rech" type="text" size="20" value="{$rech}" style="width:100px" onkeyup="var code = (event.keyCode ? event.keyCode : e.which); if(code == 13) { $('#okFiltre').click() }" />
<input id="okFiltre" class="button" type="button" value="{l s='Search' mod='pricestracker'}" onclick="window.location=document.location.href.replace(/#.*$/gi,'').replace(/&page=[^=&]*/gi,'').replace(/&rech=[^=&]*/gi,'')+'&rech='+escape(removeUnknownCar($('#rech').val()))" />
</li>     


<li style="vertical-align:middle;  padding-right:30px">
 {l s='Favorites' mod='pricestracker'} : 
 
 <select onchange="window.location=document.location.href.replace(/&fav=[^=&]*/gi,'')+'&fav='+removeUnknownCar($(this).val())">
   <option value="">{l s='All' mod='pricestracker'}</option>
   <option value="-1" {if $fav eq -1}selected="selected"{/if}>* {l s='Associated products' mod='pricestracker'}</option>
   <option value="-2" {if $fav eq -2}selected="selected"{/if}>* {l s='Not associated products' mod='pricestracker'}</option>
   <option value="-3" {if $fav eq -3}selected="selected"{/if}>* {l s='Products without any favorites' mod='pricestracker'}</option>
   <option value="-4" {if $fav eq -4}selected="selected"{/if}>* {l s='Products without price rules' mod='pricestracker'}</option>
   
   {foreach key=kf item=favori from=$favoris}
       <option value="{$favori.id_favoris}" {if $favori.id_favoris eq $fav}selected="selected"{/if}>{$favori.nom}</option>
   {/foreach}
 
 </select>
 
</li>            


<li>
						<a id="desc-meta-save" class="toolbar_btn" title="Favorites" href="{$lien}&favoris" >
							<span style="background-image:url({Tools::getAdminUrl()}/modules/pricestracker/pages/images/fav.png)" ></span>
							<div >{l s='Favorites' mod='pricestracker'}</div>
						</a>
											</li>      
                                            
               <li onmouseover="$('#exportSub').show()" onmouseout="$('#exportSub').hide()">                             
                        <a id="desc-product-export" class="toolbar_btn" title="Export" href="{$lien}&exporter">

                        <span class="process-icon-export "></span>
                        <div>
                    
                            {l s='Export' mod='pricestracker'}
                    
                        </div>
                    
                    </a>
                        <div id="exportSub" style="z-index:1000; margin-top:40px; position: absolute; background:#CCC; margin-left: -50px; display:none">
                            <a href="{$urlPage}&exporter">{l s='1 line = 1 competitor product' mod='pricestracker'}</a><br />
                            <a href="{$urlPage}&exporterHoriz">{l s='1 line = 1 of your products' mod='pricestracker'}</a>
                        </div>
                </li>
                
                <li>
                <a id="desc-product-preview" class="toolbar_btn" title="Pricing rules" href="{$lien}&regles">
                <span style="background-image:url({Tools::getAdminUrl()}/modules/pricestracker/pages/images/regle.png)"></span>
                <div>
            		{l s='Pricing rules' mod='pricestracker'}
                </div>
            
            </a>
            </li>
                                          
	  </ul>

		
    <div class="pageTitle">
			<h3>
				<span id="current_obj" style="font-weight: normal;">
																		
							<span class="breadcrumb item-0 ">{l s='Product comparison' mod='pricestracker'}
				</span>
			  </span>
				
	  </h3>
	  </div>
	</div>
</div>




<fieldset style="width:98%">
	<legend>{l s='Product matching' mod='pricestracker'}</legend>


<table class="table" cellpadding="0" cellspacing="0" align="center" width="100%">
<tbody>
    <tr>
        <th>{l s='Your product' mod='pricestracker'}</th>
        <th align="right" style="text-align:right">{l s='Your price' mod='pricestracker'}</th>
        <th>{l s='Comptetitor\'s price' mod='pricestracker'}</th>
        <th>{l s='Comptetitor\'s product' mod='pricestracker'}</th>
        <th>{l s='Date of update' mod='pricestracker'}</th>
        <th>{l s='Competitor' mod='pricestracker'}</th>
    </tr>
    
    {if !$produits}
    <tr>
        <td colspan="5">{l s='No data. Maybe you must' mod='pricestracker'} <a href="{$lien}&croisement">{l s='associate more products' mod='pricestracker'}.</a></td>
      </tr>
    
    {/if}
    
	{foreach key=k item=produit from=$produits}
        {assign var="produitsConcurrents" value=$produitsEtrangers[$produit->id]}
    <tr>
        <td rowspan="{if $produitsConcurrents|@count < 1}1{else}{$produitsConcurrents|@count}{/if}" valign="top">
          <a href="{Context::getContext()->link->getProductLink($produit->id, $produit->link_rewrite[$langue], null, $produit->ean13)}" target="_blank" style="font-size:16px">
        {if $produit->getCoverWs() && method_exists($link,'getImageLink')}
          <img src="{$link->getImageLink($produit->link_rewrite[$langue],$produit->getCoverWs(), $typeImage)}" align="left" />
        {/if}
        {$produit->name[$langue]} <img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/external.png" border="0" align="middle" style="vertical-align:middle" /></a> <br />
	 <a href="{$lienAdmin}&id_product={$produit->id}" target="_blank"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/edit.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='Edit' mod='pricestracker'}" title="{l s='Edit' mod='pricestracker'}" /></a> <a href="#" onclick="$.ajax('{$lien}&favorisAjax&id={$produit->id}').done(function( data ) { $('#dFav{$produit->id}').html(data); $('#dFav{$produit->id}').toggle();  }); return false;"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/favoris.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='Favorites' mod='pricestracker'}" title="{l s='Favorites' mod='pricestracker'}" /></a>  
      <a href="{$lien}&historique&id={$produit->id}"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/graph.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='History' mod='pricestracker'}" title="{l s='History' mod='pricestracker'}" /></a>
     Ref : {$produit->reference}
     
     <div id="dFav{$produit->id}" style="display:none">
     ...
     </div>
     
{if $versionPS>='1.5'}     
     {assign var="tabDecl" value=$produit->getAttributesResume($langue)}
     {if $tabDecl && $tabDecl|count>1}
     <a href="#" onclick="$('#declinaisons{$produit->id}').toggle(); return false;"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/rubiks-cube.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='Combinaitions' mod='pricestracker'}" title="{l s='Combinaitions' mod='pricestracker'}" /></a>

     	
     <div style="display:none" id="declinaisons{$produit->id}">
     	<table class="table" cellspacing="0" cellpadding="0">
         {foreach key=kD item=declinaison from=$tabDecl}
            <tr><td>{$declinaison.id_product_attribute}: {$declinaison.attribute_designation}</td>  <td>{$produit->convertAndFormatPrice($produit->getPrice(!$ht,$declinaison.id_product_attribute))}</td>  </tr>
         {/foreach}
        </table>
   </div>
     {/if}
{/if}

     <a href="#" onclick="window.open('{$lien}&croisement&id_product={$produit->id}', 'Matching', config='height=600, width=1500, toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=no, directories=no, status=no'); return false;"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/correspondance.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='Rapid matching' mod='pricestracker'}" title="{l s='Rapid matching' mod='pricestracker'}" /></a>
     
{if $produit->ean13}&nbsp;&nbsp;&nbsp;&nbsp; EAN : {$produit->ean13}{/if}
{if $produit->id_manufacturer}<br />{l s='Brand' mod='pricestracker'} : {$marques[$produit->id_manufacturer]}{/if}
     </td>
        
    <td rowspan="{if $produitsConcurrents|@count < 1}1{else}{$produitsConcurrents|@count}{/if}" align="right" valign="middle" style="font-weight: bold; font-size: 16px;">
    <span style="font-weight: bold; font-size: 16px; cursor:pointer" {if $version2 eq 'MEGA'}id="prix_{$produit->id}"  ondblclick="var nvoPrix=prompt('{l s='Your new price' mod='pricestracker'}',''); if(nvoPrix!=null) { $.ajax('{$lien}&prixAjax&id={$produit->id}&prix='+escape(nvoPrix)).done(function( data0 ) {  $.ajax('{$lien}&prixAjax&id={$produit->id}').done(function( data ) { if(data) { $('#prix_{$produit->id}').html('<em>'+data+'</em>'); } });  }); }  return false;"{/if}>
    {if $prixDeGros}
    	{if $ht}
        	{assign var="monPrix" value={$produit->getPriceMin(false)}}
            
        {else}
            {assign var="monPrix" value={$produit->getPriceMin()}}
        {/if}
    {else}
    	{if $ht}
            {assign var="monPrix" value={$produit->getPrice(false)}}
        {else}
            {assign var="monPrix" value={$produit->getPrice()}}
        {/if}
    {/if}
    {$produit->convertAndFormatPrice($monPrix)}
    </span>
{if $version2 eq 'MEGA'}    
    {if $produit->prix_propose}
     <a id="sugg_{$produit->id}" class="button" style="display: inherit;" href="#" onclick="$.ajax('{$lien}&prixAjax&id={$produit->id}&prix={$produit->prix_propose}').done(function( data0 ) {  $.ajax('{$lien}&prixAjax&id={$produit->id}').done(function( data ) { if(data) { $('#prix_{$produit->id}').html('<em>'+data+'</em>'); $(this).css('display','none') } }); });  return false;">{l s='Change for' mod='pricestracker'} {$produit->convertAndFormatPrice($produit->prix_propose)}</a>
   {/if}
{/if}
    </td>



        {assign var="id_concurrents" value=-1}

          
        {foreach key=k2 item=produitConcurrent from=$produitsConcurrents}
        
        {if $produitConcurrent.seuil && is_numeric($produitConcurrent.seuil)}
        	{assign var="seuil" value=$produitConcurrent.seuil}
        {else}
        	{assign var="seuil" value=$monPrix}
        {/if}
        <td valign="middle" style="color:{if $produitConcurrent.prix<$seuil}#900{elseif $produitConcurrent.prix>$seuil}#039603{/if}">
        {$produit->convertAndFormatPrice($produitConcurrent.prix)}
        {if $produitConcurrent.seuil}<em>({$produitConcurrent.seuil})</em>{/if}
        </td>
        

             <td valign="middle"><a href="{$produitConcurrent.lien}" target="_blank">{$produitConcurrent.nom } <img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/external.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='See the product page' mod='pricestracker'}" title="{l s='See the product page' mod='pricestracker'}" /></a> 
             <a href="#" onclick="$.ajax('{$lien}&notificationAjax&id={$produitConcurrent.id_associations}').done(function( data ) { if(data==1) $('#notif_{$produitConcurrent.id_associations}').attr('src','{Tools::getAdminUrl()}/modules/pricestracker/pages/images/notif-on.png'); else $('#notif_{$produitConcurrent.id_associations}').attr('src','{Tools::getAdminUrl()}/modules/pricestracker/pages/images/notif-off.png');  }); return false;"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/notif-{if $produitConcurrent.isAlerteMail}on{else}off{/if}.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='Mail alert' mod='pricestracker'}" title="{l s='Mail alert' mod='pricestracker'}" id="notif_{$produitConcurrent.id_associations}" /></a> 
             <a href="#" onclick="$.ajax('{$lien}&regleAjax&id={$produitConcurrent.id_associations}').done(function( data ) { $('#dRegle{$produitConcurrent.id_associations}').html(data); $('#dRegle{$produitConcurrent.id_associations}').toggle();  }); return false;"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/regle_min.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='Set a pricing rule' mod='pricestracker'}" title="{l s='Set a pricing rule' mod='pricestracker'}" /></a> 
    {if Configuration::get('pricestracker_SEUILS')}         <a href="{$lien}&seuil&id={$produitConcurrent.id_associations}" target="_blank"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/thermometer.png" border="0" align="middle" style="vertical-align:middle" alt="{l s='Programmed threshold' mod='pricestracker'}" title="{l s='Programmed threshold' mod='pricestracker'}" /></a>        {/if}
             
             
     <div id="dRegle{$produitConcurrent.id_associations}" style="display:none"> 
 ...
     </div>             
             </td>
             <td valign="middle">{$produitConcurrent.date|date_format:"%d/%m/%Y"} <a href="#" onclick="$('#ff{$produitConcurrent.id_produits_etrangers}p{$produit->id}').toggle(); $('#dd{$produitConcurrent.id_produits_etrangers}p{$produit->id}').val('...'); $.ajax('{$lien}&frequenceAjax&id={$produitConcurrent.id_produits_etrangers}').done(function( data ) { $('#dd{$produitConcurrent.id_produits_etrangers}p{$produit->id}').val(data) }); return false;"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/time.png" border="0" align="top" style="vertical-align:top" /></a>
             

<div id="ff{$produitConcurrent.id_produits_etrangers}p{$produit->id}" style="display:none">
{l s='Frequency of update' mod='pricestracker'} :<br />
<input id="dd{$produitConcurrent.id_produits_etrangers}p{$produit->id}" type="text" size="3" />{l s='days' mod='pricestracker'} <input class="button" type="button" value="OK" onclick="freq=removeUnknownCar($('#dd{$produitConcurrent.id_produits_etrangers}p{$produit->id}').val()); $('#dd{$produitConcurrent.id_produits_etrangers}p{$produit->id}').val('...'); $.ajax('{$lien}&frequenceAjax&id={$produitConcurrent.id_produits_etrangers}&freq='+escape( freq )).done(function( data ) { $('#dd{$produitConcurrent.id_produits_etrangers}p{$produit->id}').val(data); {if $version2 eq 'Silver'}alert('{$txtUpgrade|addslashes}');{/if} });" />
</div>
             
             </td>
     
     		{if $id_concurrents neq $produitConcurrent.id_concurrents}
                {assign var="id_concurrents" value=$produitConcurrent.id_concurrents}
                {assign var="indice" value={$produit->id}|cat:"c"|cat:{$id_concurrents}}
   		<td rowspan="{$nbProdConcurrents[$indice]}"><strong>{$concurrents[$id_concurrents]}</strong></td>
            {/if}
      </tr><tr>
            
     
                
        {/foreach}
        
        
          
 
    </tr>
    
    {/foreach}
</tbody>
</table>

</fieldset>
</form>
<!-- PricesTracker -->