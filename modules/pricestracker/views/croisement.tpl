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
        document.title = '{l s='Product matching' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />

{if $modeTest}    
<a href="https://www.storeinterfacer.com/pricestracker.php" target="_blank" class="button">{l s='Buy the module' mod='pricestracker'}</a>
<br />

{/if}




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
            <a href="https://www.youtube.com/watch?{if $is_francais}v=Y9r7iC1HZlw&index=4&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}v=Tc0d37hzjSU&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank" class="open-yt-lightbox">
        <img alt="Video tuto" src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/video-tutorial.png" style="width:80px" />
    </a>
</div>


<div class="video-container-wrapper lightbox-backdrop">
  <div class="sizewrapper box">
    <div class="close shadowed">X</div>
    <div id="videowrapper" class="shadowed">
      <iframe width="560" height="315" src="https://www.youtube.com/embed/{if $is_francais}Y9r7iC1HZlw?index=4&enablejsapi=1&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}Tc0d37hzjSU?si=V4m7rsRwFqiQgDPc{/if}&ab_channel=idIATech" frameborder="0" id="yt-iframe" allowfullscreen></iframe>
    </div>
    <div class="go-to-yt shadowed"><a href="https://www.youtube.com/watch?{if $is_francais}v=Y9r7iC1HZlw&index=4&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}v=Tc0d37hzjSU&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank">{l s='Video not loading? Watch on Youtube' mod='pricestracker'} ></a></div>
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
	window.location=document.location.href.replace(/&(prix_min|prix_max|diff_max|diff_min|type_diff|date_ajout)=[^=&]*/gi,'')+"&prix_min="+removeUnknownCar($('#prix_min').val())+"&prix_max="+removeUnknownCar($('#prix_max').val())+"&date_ajout="+removeUnknownCar($('#date_ajout').val())
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
 {l s='Filter by brand' mod='pricestracker'} : 
 
 <select style="max-width: 100px;" onchange="window.location=document.location.href.replace(/&id_manufacturer=[\-0-9,]+/gi,'')+'&id_manufacturer='+removeUnknownCar($(this).val())">
   <option value="">{l s='All' mod='pricestracker'}</option>
   {foreach key=id_manufacturer_m item=marque from=$marques}
       <option value="{$id_manufacturer_m}" {if $id_manufacturer_m eq $id_manufacturer}selected="selected"{/if}>{$marque}</option>
   {/foreach}
 
 </select>


 
 
 
 
 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 {l s='Only display competitor products added after the ' mod='pricestracker'} : 
 
<input name="date_ajout" id="date_ajout" type="text" style="width:50px" value="{$smarty.get.date_ajout}" /> 
(yyyy/mm/dd) <a href="#" onclick="$('#date_ajout').val('{$dernier_crawl}')">&lt; {l s='Last crawling' mod='pricestracker'}</a>








 
 
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

{/if}

<form action="" method="post" id="formPrinc">

{assign var="txtUpgrade" value={l s='This version does not have this option. To use it you can go higher.' mod='pricestracker'}}


{if isset($category_tree)}
<div id="container_category_tree" style="border: 1px solid #E6E6E6; display:{if !$id_category}none{/if}">
    {$category_tree}
</div>
<div class="clear"></div>
{/if}

<script>
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
{foreach key=kconc item=concurrent from=$concurrents2}

  <label style="float:none">
    <input type="checkbox" name="concurrent_{$kconc}" value="1" {if in_array($kconc,$id_concurrents)}checked="checked"{/if} />
    {$concurrent}</label><br />

{/foreach} <br />
</div>
<br />

<div class="toolbar-placeholder" style="clear:both">
	<div class="toolbarBox toolbarHead">
		
			<ul class="cc_button">
              <li style="vertical-align:middle; padding-top:20px; padding-right:30px; overflow: auto; max-height: 50px; clear: both;">
            {l s='Page' mod='pricestracker'} :
             					
{$htmlPage}
                             </li>
                             
                             
                    <li style="vertical-align:middle; padding-top:15px; padding-right:30px">
 {l s='Search' mod='pricestracker'} : 
 
 <input name="rech" id="rech" type="text" size="20" style="width:100px" />
<input class="button" type="button" value="{l s='Search' mod='pricestracker'}" onclick="window.location=document.location.href.replace(/#.*$/gi,'').replace(/&page=[^=&]*/gi,'').replace(/&rech=[^=&]*/gi,'')+'&rech='+escape(removeUnknownCar($('#rech').val()))" />
</li>         


<li style="vertical-align:middle; padding-top:15px; padding-right:30px">
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
   
   {if $version2 eq 'MEGA'}
                <li>
                <a id="desc-product-preview" class="toolbar_btn" title="Proximities" href="{$lien}&proximite">
                <span style="background-image:url({Tools::getAdminUrl()}/modules/pricestracker/pages/images/proximite.png)"></span>
                <div>
            		{l s='Proximities' mod='pricestracker'}
                </div>
            
            </a>
            </li>
  {/if}
                                       
							</ul>

		
    <div class="pageTitle">
			<h3>
				<span id="current_obj" style="font-weight: normal;">
																		
							<span class="breadcrumb item-0 ">{l s='Product matching' mod='pricestracker'}
															</span>
															</span>
				
	  </h3>
		</div>
	</div>
</div>


</form>

<div>{l s='Blue products = suggestions of proximity - Green products = associated with your product (you will find it in the competitor\'s prices) - White products = all the competitor\'s products (click on them to associate them)' mod='pricestracker'}</div><br>

{if $modeTest}
<div>{l s='Note: With the test version you can manage only 10 products' mod='pricestracker'}</div><br>

{/if}

<fieldset style="width:98%">
	<legend>{l s='Product matching' mod='pricestracker'}</legend>


<table class="table" cellpadding="0" cellspacing="0" align="center" width="100%">
<tbody>
    <tr>
        <th>{l s='Your product' mod='pricestracker'}</th>
        <th>{l s='Competitor' mod='pricestracker'}</th>
        <th>{l s='Matching with competitor\'s product' mod='pricestracker'}</th>
    </tr>
    
    {if !$produits}
    <tr>
        <td colspan="3">{l s='No data' mod='pricestracker'}</td>
      </tr>
    
    {/if}

    
	{foreach key=k item=produit from=$produits}
    <tr>
        <td style="min-width:200px" width="200" rowspan="{if $concurrents|@count < 1}1{else}{$concurrents|@count}{/if}" valign="top">

          <a href="{Context::getContext()->link->getProductLink($produit->id, $produit->link_rewrite[$langue], $produit->id_category_default, $produit->ean13)}" target="_blank" style="font-size:16px">
          
         {if $produit->getCoverWs() && method_exists($link,'getImageLink')}
          <img src="{$link->getImageLink($produit->link_rewrite[$langue],$produit->getCoverWs(), $typeImage)}" align="left" />
        {/if}
        {$produit->name[$langue]} <img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/external.png" border="0" align="middle" style="vertical-align:middle" /></a><br />
		Ref : {$produit->reference} <br />
        
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
    
 {if $monPrix}{l s='Price' mod='pricestracker'} : {$produit->convertAndFormatPrice($monPrix)}{/if}
</td>
          
        {if $concurrents|@count < 1}
       	<td valign="middle">{l s='No competitor' mod='pricestracker'}</td>
        {else}
        
        	{assign var="nConcurrent" value="1"}
        	{foreach key=id_concurrents item=concurrent from=$concurrents}
            	<td valign="middle"><strong>{$concurrent}</strong>
{if $version2 eq 'MEGA'}
<br /><br />
 <!--<a href="{$lien}&extension" class="google-matching button" idConcurrent="{$id_concurrents}" idProduct="{$produit->id}" nom="{$produit->name[$langue]|htmlspecialchars}" url="{$concurrents_url[$id_concurrents]}">{l s='Google Matching' mod='pricestracker'}</a>-->
{/if}               
</td>
            	<td valign="top" width="100%">
                
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="1" align="right">
    <input id="se{$id_concurrents}p{$produit->id}" type="text" size="20" style="width:100px" value="{if ({$concurrent|strstr:'Amazon'} || {$concurrent} eq 'CDiscount' || {$concurrent} eq 'PriceMinister' || {$concurrent} eq 'Ebay') && !$produit->ean13}{$produit->name[$langue]|trim|htmlspecialchars}{elseif $preremplissageRecherche == 'NOM'}{$produit->name[$langue]|trim|htmlspecialchars}{elseif $preremplissageRecherche == 'REFERENCE'}{$produit->reference|trim|htmlspecialchars}{/if}" /><br />
<input class="button" type="button" value="{l s='Search' mod='pricestracker'}" onclick="$('#if{$id_concurrents}p{$produit->id}').attr('src','{$lien}&association&id_concurrents={$id_concurrents}&id_product={$produit->id}&rech='+escape(btoa(removeUnknownCar($('#se{$id_concurrents}p{$produit->id}').val()).replace(/\+/g,'--PLUS--')))+'')" /><br />
{if !{$concurrent|strstr:'Amazon'} && {$concurrent} neq 'PriceMinister' && {$concurrent} neq 'Ebay'}
<input class="button" type="button" value="{l s='Price proximity' mod='pricestracker'}" onclick="$('#if{$id_concurrents}p{$produit->id}').attr('src','{$lien}&association&id_concurrents={$id_concurrents}&id_product={$produit->id}&prix={$produit->getPrice()}&rech='+escape(removeUnknownCar($('#se{$id_concurrents}p{$produit->id}').val()))+''); {if $version2 eq 'Silver'}alert('{$txtUpgrade|addslashes}');{/if}" />
{/if}</td>
    <td>
    <iframe id="if{$id_concurrents}p{$produit->id}" src="{$lien}&association&id_concurrents={$id_concurrents}&id_product={$produit->id}&date_ajout={$smarty.get.date_ajout}{if $preremplissageRecherche == 'NOM'}&rech={$produit->name[$langue]|trim|base64_encode|urlencode}{elseif $preremplissageRecherche == 'REFERENCE'}&rech={$produit->reference|trim|base64_encode|urlencode}{/if}" frameborder="0" style="border:1px #CCC solid" height="100" scrolling="auto" width="100%"></iframe>
</td>
  </tr>
</table>
                
                </td>
                
                {if $nConcurrent neq $concurrents|@count}     </tr><tr>    {/if}
                {assign var="nConcurrent" value=$nConcurrent+1}
                
            {/foreach}
        
        {/if}
        
          
 
    </tr>
    
    {/foreach}
</tbody>
</table>


</fieldset>

<!-- PricesTracker -->