<!--
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
-->

<style type="text/css">
#categories-tree {
	overflow: auto;
	max-height: 400px;
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
.tree-folder { clear:both }
.tree-item-name { clear:both }
@media (min-width: 1000px){
	 .col-lg-1,  .col-lg-2,  .col-lg-3,  .col-lg-4,  .col-lg-5,  .col-lg-6,  .col-lg-7,  .col-lg-8,  .col-lg-9,  .col-lg-10,  .col-lg-11,  .col-lg-12 {
		float: left;
		position: relative;
		min-height: 1px;
		padding-left: 5px;
		padding-right: 5px;
	}
	.col-lg-6 {
		width: 50%;
	}
	.col-lg-5 {
		width: 41.6667%;
	}
	.col-lg-12 {
		width: 100%;
	}
}
.btn-group, .btn-group-vertical {
    position: relative;
    display: inline-block;
    vertical-align: middle;
}
.btn.btn-default {
    box-shadow: 0px -2px 0px #E6E6E6 inset;
}
.btn-group > .btn,  .btn-group-vertical > .btn {
    position: relative;
    float: left;
}
.btn-default {
    color: #555;
    background-color: #FFF;
    border-color: #CCC;
}
.btn {
    display: inline-block;
    margin-bottom: 0px;
    font-weight: normal;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    background-image: none;
    border: 1px solid transparent;
    white-space: nowrap;
    padding: 4px 8px;
    font-size: 12px;
    line-height: 1.42857;
    border-radius: 3px;
    -moz-user-select: none;
}
.btn-default.active, .btn-default.dropdown-toggle {
    color: #555;
    background-color: #E6E6E6;
    border-color: #ADADAD;
}
</style>

<script>
$(document).ready(function() {
        document.title = '{l s='Dashboard' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />

<div id="filtres" style="clear:both">
<input id="is_id_category" type="checkbox" value="1" {if $id_category}checked{/if} /> {l s='Filter by category' mod='pricestracker'}
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input id="is_id_concurrents" type="checkbox" value="1" {if $id_concurrents}checked{/if} /> {l s='Filter by competitor' mod='pricestracker'}

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 {l s='Filter by favorites' mod='pricestracker'} : 
 
 <select style="max-width: 100px;" onchange="window.location=document.location.href.replace(/&fav=[\-0-9,]+/gi,'')+'&fav='+$(this).val()">
   <option value="">{l s='All' mod='pricestracker'}</option>
   {foreach key=kf item=favori from=$favoris}
       <option value="{$favori.id_favoris}" {if $favori.id_favoris eq $fav}selected="selected"{/if}>{$favori.nom}</option>
   {/foreach}
 
 </select>


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
	window.location=document.location.href.replace(/&(prix_min|prix_max|diff_max|diff_min|type_diff)=[^=&]*/gi,'')+"&prix_min="+$('#prix_min').val()+"&prix_max="+$('#prix_max').val()+"&diff_max="+$('#diff_max').val()+"&diff_min="+$('#diff_min').val()+"&type_diff="+$('#type_diff').val()
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
 
 <select style="max-width: 100px;" onchange="window.location=document.location.href.replace(/&id_manufacturer=[\-0-9,]+/gi,'')+'&id_manufacturer='+$(this).val()">
   <option value="">{l s='All' mod='pricestracker'}</option>
   {foreach key=id_manufacturer_m item=marque from=$marques}
       <option value="{$id_manufacturer_m}" {if $id_manufacturer_m eq $id_manufacturer}selected="selected"{/if}>{$marque}</option>
   {/foreach}
 
 </select>

 
 
 

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input name="" type="submit" value="OK" />
</form>
</div>


<div id="container_category_tree" style="border: 1px solid #E6E6E6; display:{if !$id_category}none{/if}">
    {$category_tree}
</div>

<script>
function actionCategory()
{
	$('#categories-tree input').click(function() {
		id_category=$(this).val();
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

  <label>
    <input type="checkbox" name="concurrent_{$kconc}" value="1" {if in_array($kconc,$id_concurrents)}checked="checked"{/if} />
    {$concurrent}</label><br />

{/foreach} <br />
</div>
<br />

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://rawgithub.com/highslide-software/export-csv/master/export-csv.js"></script>

<!-- PricesTracker -->
<div class="bootstrap">
<form action="" method="post">

<div class="col-lg-5 encart" id="divPlusOuMoinsCher">
<div align="right">
{l s='Click = Tooltip On/Off. Shift+Click = See products' mod='pricestracker'}
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$smarty.server.REQUEST_URI|regex_replace:"/&moinsDonnes=[0-9]/":""}&moinsDonnes={$moinsDonnes*1}"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/moinsDonnes.png" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="#" onclick="polaire=!polaire; chartPlusMoinsDessiner(polaire); return false;"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/graph-split.png" /></a></div>
<div id="plusOuMoinsCher"></div>
    
    
<div style="display:none">
	<table id="plusOuMoinsCherData" border="0" cellspacing="0" cellpadding="0">
		<tr nowrap bgcolor="#CCCCFF">
			<th colspan="14" class="hdr"></th>
		</tr>
        
        
   {if !$moinsDonnes}
   
   
   
        <tr nowrap bgcolor="#CCCCFF">
			<th class="freq">Difference</th>
			<th class="freq" color="red">{l s='You are' mod='pricestracker'} > 20% {l s='most expensive' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 15 - 20% {l s='most expensive' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 10 - 15% {l s='most expensive' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 5 - 10% {l s='most expensive' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 2 - 5% {l s='most expensive' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 0 - 2% {l s='most expensive' mod='pricestracker'}</th>
			<th class="freq">{l s='Same price' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 0 - 2% {l s='cheaper' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 2 - 5% {l s='cheaper' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 5 - 10% {l s='cheaper' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 10 - 15% {l s='cheaper' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} 15 - 20% {l s='cheaper' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} > 20% {l s='cheaper' mod='pricestracker'}</th>
		</tr>
      
        {if $plusOuMoinsCher|count>2}
		<tr nowrap>
			<td class="dir"><strong style="font-size:18px">{l s='All' mod='pricestracker'}</strong></td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][20])}{$plusOuMoinsCher['tous'][20]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][15])}{$plusOuMoinsCher['tous'][15]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][10])}{$plusOuMoinsCher['tous'][10]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][5])}{$plusOuMoinsCher['tous'][5]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][2])}{$plusOuMoinsCher['tous'][2]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][1])}{$plusOuMoinsCher['tous'][1]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][0])}{$plusOuMoinsCher['tous'][0]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][-1])}{$plusOuMoinsCher['tous'][-1]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][-2])}{$plusOuMoinsCher['tous'][-2]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][-5])}{$plusOuMoinsCher['tous'][-5]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][-10])}{$plusOuMoinsCher['tous'][-10]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][-15])}{$plusOuMoinsCher['tous'][-15]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][-20])}{$plusOuMoinsCher['tous'][-20]*100/max(1,$nb_produits_total)}{/if}</td>
		</tr>
		{/if}
        
        {foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
		<tr nowrap>
			<td class="dir">{$nomConcurrent}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][20])}{$plusOuMoinsCher[$idConcurrent][20]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][15])}{$plusOuMoinsCher[$idConcurrent][15]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][10])}{$plusOuMoinsCher[$idConcurrent][10]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][5])}{$plusOuMoinsCher[$idConcurrent][5]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][2])}{$plusOuMoinsCher[$idConcurrent][2]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][1])}{$plusOuMoinsCher[$idConcurrent][1]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][0])}{$plusOuMoinsCher[$idConcurrent][0]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][-1])}{$plusOuMoinsCher[$idConcurrent][-1]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][-2])}{$plusOuMoinsCher[$idConcurrent][-2]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][-5])}{$plusOuMoinsCher[$idConcurrent][-5]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][-10])}{$plusOuMoinsCher[$idConcurrent][-10]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][-15])}{$plusOuMoinsCher[$idConcurrent][-15]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][-20])}{$plusOuMoinsCher[$idConcurrent][-20]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
		</tr>
        {/foreach}	
        
        
   {else}
   
   
           <tr nowrap bgcolor="#CCCCFF">
			<th class="freq">Difference</th>
			<th class="freq" color="red">{l s='You are' mod='pricestracker'} {l s='most expensive' mod='pricestracker'}</th>
			<th class="freq">{l s='Same price' mod='pricestracker'}</th>
			<th class="freq">{l s='You are' mod='pricestracker'} {l s='cheaper' mod='pricestracker'}</th>
		</tr>
      
        {if $plusOuMoinsCher|count>2}
		<tr nowrap>
			<td class="dir"><strong style="font-size:18px">{l s='All' mod='pricestracker'}</strong></td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][100])}{$plusOuMoinsCher['tous'][100]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][0])}{$plusOuMoinsCher['tous'][3]*100/max(1,$nb_produits_total)}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher['tous'][-100])}{$plusOuMoinsCher['tous'][-100]*100/max(1,$nb_produits_total)}{/if}</td>
		</tr>
		{/if}
        
        {foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
		<tr nowrap>
			<td class="dir">{$nomConcurrent}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][100])}{$plusOuMoinsCher[$idConcurrent][100]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][3])}{$plusOuMoinsCher[$idConcurrent][3]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
			<td class="data">{if isset($plusOuMoinsCher[$idConcurrent][-100])}{$plusOuMoinsCher[$idConcurrent][-100]*100/max(1, $nb_produits[$idConcurrent])}{/if}</td>
		</tr>
        {/foreach}	
   
   
   {/if}     	
	</table>
</div>
<script>
var nbProduits={
	0:{$nb_produits_total},
	{assign var="iNb" value=1}
	{foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
		{$iNb}:{$nb_produits[$idConcurrent]*1},
		{assign var="iNb" value=$iNb+1}
	{/foreach}
}
var nomsConcurrents={
	0:'{l s='All' mod='pricestracker'}',
	{assign var="iNb" value=1}
	{foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
		{$iNb}:'{$nomConcurrent|addslashes}',
		{assign var="iNb" value=$iNb+1}
	{/foreach}
}
var idsConcurrents={
	{l s='All' mod='pricestracker'}:'',
	{foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
		'{$nomConcurrent|addslashes}':{$idConcurrent},
	{/foreach}
}
var moinCher={
	0:{$plusOuMoinsCher['tous'][-100]*1},
	{assign var="iNb" value=1}
	{foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
		{$iNb}:{$plusOuMoinsCher[$idConcurrent][-100]*1},
		{assign var="iNb" value=$iNb+1}
	{/foreach}
}
var plusCher={
	0:{$plusOuMoinsCher['tous'][100]*1},
	{assign var="iNb" value=1}
	{foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
		{$iNb}:{$plusOuMoinsCher[$idConcurrent][100]*1},
		{assign var="iNb" value=$iNb+1}
	{/foreach}
}
var memePrix={
	0:{$plusOuMoinsCher['tous'][3]*1},
	{assign var="iNb" value=1}
	{foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
		{$iNb}:{$plusOuMoinsCher[$idConcurrent][3]*1},
		{assign var="iNb" value=$iNb+1}
	{/foreach}
}

{literal}
var polaire=true;
$(function () {
	chartPlusMoinsDessiner(polaire)
	$('#divPlusOuMoinsCher .highcharts-tooltip').css('display','none');
	
});

function forceRedraw(div){
}

var maxAxe=-1;
function chartPlusMoinsDessiner(polaire)
{
	maxAxe=-1;
    chartPlusMoins=$('#plusOuMoinsCher').highcharts({
    	data: {
	    	table: 'plusOuMoinsCherData',
	    	startRow: 1
	    },
	    
	    chart: {
	        polar: polaire,
	        type: 'column',
	    },
	    colors: [
	{/literal}
	{if !$moinsDonnes}
   '#D90000', '#FF2B2B', '#FF5151', '#FF8080', '#FFB3B3', '#FFD9D9', 
   '#FCFFB9', 
   '#D2FFD3', '#A4FFA6', '#5EFF62', '#00F206', '#00CE05', '#00B304'
   {else}
   '#D90000',
   '#FFA600', 
   '#00CE05'
   {/if}
   {literal}
   ],
	    title: {
	        text: null
	    },
		exporting: {
			enabled: true
		},
		credits: {
			enabled: false
		},
	    
	    legend: {
	    	reversed: true,
	    	align: 'right',
	    	verticalAlign: 'top',
	    	y: 100,
	    	layout: 'vertical',
			itemStyle: {
                fontSize: '10px'
            }
	    },
	    
	    xAxis: {
	    	tickmarkPlacement: 'on'
	    },
	        
	    yAxis: {
	        min: 0,
	        endOnTick: false,
	        showLastLabel: true,
			gridZIndex:4,
			gridLineColor:'#CCC',
			tickPixelInterval:30,
	        title: {
	        	text: 'Nb of products'
	        },
			plotLines: [{
                color: '#777777',
                width: 1,
                value: 50,
				zIndex: 5
            }],
	        labels: {
	        	formatter: function () {
					if(maxAxe==-1) maxAxe=this.axis.max
	        		return ""+((this.axis.max-maxAxe+100) * this.value / $(this.axis.tickPositions).last()[0]).toFixed(1) + 
					'<u>%</u>';
	        	}
	        }
	    },
	    
	    tooltip: {
			useHTML:true,
			followPointer:false,
			formatter: function() {
				setTimeout(function(){
					$('.highcharts-tooltip path').attr('d','M 3.5 0.5 L 298.5 0.5 C 301.5 0.5 301.5 0.5 301.5 3.5 L 301.5 93.5 C 301.5 96.5 301.5 96.5 298.5 96.5 L 88.5 96.5 82.5 102.5 76.5 96.5 3.5 96.5 C 0.5 96.5 0.5 96.5 0.5 93.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5')
					}, 200);
				
				return "<strong>"+this.series.name+"</strong><br><em><strong>"+
				nomsConcurrents[this.x]+"</strong></em><br>"+
				(nbProduits[this.x]*this.y/100).toFixed(0)
				+" {/literal}{l s='products' mod='pricestracker'}{literal}<br>"+
				(this.y).toFixed(1)+"%<br>"+
				"<span style=\"font-size:10px\">{/literal}{l s='Summary' mod='pricestracker'}{literal}: "+
				(moinCher[this.x]*100/Math.max(1, nbProduits[this.x])).toFixed(1)+
				"% {/literal}{l s='cheaper' mod='pricestracker'}{literal}. "+
				(plusCher[this.x]*100/Math.max(1, nbProduits[this.x])).toFixed(1)+
				"% {/literal}{l s='more expensive' mod='pricestracker'}{literal}. "+
				(memePrix[this.x]*100/Math.max(1, nbProduits[this.x])).toFixed(1)+
				"% {/literal}{l s='same price' mod='pricestracker'}{literal}"+
				"</span>";
			}
	    },
	        
	    plotOptions: {
	        series: {
	        	stacking: 'normal',
	        	shadow: false,
	        	groupPadding: 0,
	        	pointPlacement: polaire?'on':null,
				events: {
					click:function(event) {
						mondeNonInfobulle=false
						if(event.shiftKey || event.ctrlKey) mondeNonInfobulle=true
						if(mondeNonInfobulle)
						{
							labelInterval=this.name
							idConcurrent=idsConcurrents[event.point.name]
							urlRedirect=document.location.href.replace("&tableaubord","&comparaison")
							if(idConcurrent) urlRedirect=urlRedirect.replace(/&id_concurrents=[\-0-9,]+/gi,'')+"&id_concurrents="+idConcurrent;
							else urlRedirect=urlRedirect.replace(/&id_concurrents=[\-0-9,]+/gi,'')
							
							if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} &gt; 20% {l s='most expensive' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+20;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 15 - 20% {l s='most expensive' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+15+"&diff_max="+20;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 10 - 15% {l s='most expensive' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+10+"&diff_max="+15;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 5 - 10% {l s='most expensive' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+5+"&diff_max="+10;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 2 - 5% {l s='most expensive' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+2+"&diff_max="+5;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 0 - 2% {l s='most expensive' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+0+"&diff_max="+2;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 0 - 2% {l s='cheaper' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+0+"&diff_max="+-2;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 2 - 5% {l s='cheaper' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+-5+"&diff_max="+-2;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 5 - 10% {l s='cheaper' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+-10+"&diff_max="+-5;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 10 - 15% {l s='cheaper' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+-15+"&diff_max="+-10;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} 15 - 20% {l s='cheaper' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+-20+"&diff_max="+-15;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} &gt; 20% {l s='cheaper' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_max="+-20;
							else if(labelInterval=='{/literal}{l s='Same price' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+0+"&diff_max="+0;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} {l s='most expensive' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_min=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_min="+0;
							else if(labelInterval=='{/literal}{l s='You are' mod='pricestracker'} {l s='cheaper' mod='pricestracker'}{literal}') urlRedirect=urlRedirect.replace(/&type_diff=[^=&]*/gi,'').replace(/&diff_max=[^=&]*/gi,'')+"&type_diff=pourc"+"&diff_max="+0;
							
							//alert(urlRedirect)
							window.location=urlRedirect
						}
						else
						{
							if($('#divPlusOuMoinsCher .highcharts-tooltip').css('display')=='none') $('.highcharts-tooltip').css('display','')
							else $('#divPlusOuMoinsCher .highcharts-tooltip').css('display','none')
							forceRedraw($('#divPlusOuMoinsCher .highcharts-tooltip')[0])
						}
					}
				}
	        }
	    }
	});
}
{/literal}
</script>

</div>


<div class="col-lg-6 encart">
{l s='This table shows your indicators (between brackets) and equivalents from competitors obtained by replacing your prices in formulas by those of competitors (ex: equivalent basket = Sum [ competitor price x number of your sales ] / number of your orders)' mod='pricestracker'}<br />
<br />

<table class="table" cellpadding="0" cellspacing="0" align="center" width="100%">
<tbody>
    <tr>
        <th>{l s='Competitor' mod='pricestracker'}</th>
        <th>{l s='Average price' mod='pricestracker'}</th>
        <th>{l s='Average basket' mod='pricestracker'}</th>
        <th>{l s='Turnover' mod='pricestracker'}</th>
        <th>{l s='Margin' mod='pricestracker'}</th>
        <th>{l s='Associated products' mod='pricestracker'}</th>
    </tr>
{foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
    <tr>
        {assign var="prixMoyen" value=$somme_prix[$idConcurrent]/max(1, $nb_produits[$idConcurrent])}
        {assign var="prixMoyenMoi" value=$somme_prix_moi[$idConcurrent]/max(1, $nb_produits[$idConcurrent])}
    	<td><span style="color:{if $prixMoyenMoi>$prixMoyen}#900{elseif $prixMoyenMoi<$prixMoyen}#039603{/if}">{$nomConcurrent}</span></td>
    	<td>
{if $nb_produits[$idConcurrent]>0}
    <strong>{Product::convertAndFormatPrice($prixMoyen)}</strong>
    <em>({Product::convertAndFormatPrice($prixMoyenMoi)|replace:' ':''})</em>
{/if}
</td>
    	<td>
{if $nb_commandes[$idConcurrent]>0}
    {assign var="panierMoyen" value=$ca[$idConcurrent]/max(1, $nb_commandes[$idConcurrent])}
    {assign var="panierMoyenMoi" value=$ca_moi[$idConcurrent]/max(1, $nb_commandes[$idConcurrent])}
    <strong>{Product::convertAndFormatPrice($panierMoyen)}</strong>
    <em>({Product::convertAndFormatPrice($panierMoyenMoi)|replace:' ':''})</em>
{/if}
</td>
    	<td>
{if $ca[$idConcurrent]}
    <strong>{Product::convertAndFormatPrice($ca[$idConcurrent])}</strong>
    <em>({Product::convertAndFormatPrice($ca_moi[$idConcurrent])|replace:' ':''})</em>
{/if}
</td>
    	<td>
{if $ca[$idConcurrent]>0}
    {assign var="marge" value=$num_marge[$idConcurrent]/max(1, $ca[$idConcurrent])}
    {assign var="margeMoi" value=$num_marge_moi[$idConcurrent]/$ca_moi[$idConcurrent]}
    <strong>{($marge*100)|string_format:"%.2f"}%</strong>
    <em>({($margeMoi*100)|string_format:"%.2f"}%)</em>
{/if}
</td>
    	<td>{$nb_produits[$idConcurrent]}</td>
    </tr>
{/foreach}
</tbody>
</table>
</div>

<br class="clear" />
<br class="clear" />

<div class="col-lg-12 encart" align="center">
<div class="btn-group" data-toggle="buttons">
			<label class="btn btn-default{if !$historique_get || $historique_get eq 'prix'} active{/if}" onclick="window.location=document.location.href.replace(/&historique=[a-z]+/gi,'')+'&historique=prix'">
				<i class="icon-circle" style="color:#9E5BA1"></i> {l s='Average price' mod='pricestracker'}
			</label>
			<label class="btn btn-default{if $historique_get eq 'panier'} active{/if}" onclick="window.location=document.location.href.replace(/&historique=[a-z]+/gi,'')+'&historique=panier'">
				<i class="icon-circle" style="color:#00A89C"></i> {l s='Average basket' mod='pricestracker'}
			</label>
			<label class="btn btn-default{if $historique_get eq 'ca'} active{/if}" onclick="window.location=document.location.href.replace(/&historique=[a-z]+/gi,'')+'&historique=ca'">
				<i class="icon-circle" style="color:#3AC4ED"></i> {l s='Turnover' mod='pricestracker'}
			</label>
			<label class="btn btn-default{if $historique_get eq 'marge'} active{/if}" onclick="window.location=document.location.href.replace(/&historique=[a-z]+/gi,'')+'&historique=marge'">
				<i class="icon-circle" style="color:#F99031"></i> {l s='Margin' mod='pricestracker'}
			</label>
		</div>
<div id="historique" style="height:500px"></div>
<div id="historique_rien" style="display:none"><br />
{l s='No data' mod='pricestracker'}</div>
<script>
{literal}
$(function () {
    var chart;
    $(document).ready(function() {
        $('#historique').highcharts({
            chart: {
                zoomType: 'x',
                type: 'areaspline'
            },
            title: {
                text: 'Evolution'
            },
            exporting: {
                enabled: true
            },
            xAxis: {
                type: 'datetime',
				startOnTick:false
            },
            tooltip: {
				useHTML:true,
                formatter: function() {
                        return ''+
                        Highcharts.dateFormat('%e. %b %Y', this.x) +' : '+ 
						(this.y.toFixed(2).replace('.',','));
                }
            },
            credits: {
                enabled: false
            },
            plotOptions: {
                areaspline: {
                    fillOpacity: -1,
					marker:{ radius:3 }
                }
            },
            series: [
			
{/literal}


{assign var="prix_min" value=-1}

	{ 
	fillOpacity: 0.3,
	name: '{l s='Your site' mod='pricestracker'}'
	,data: [
	
{foreach item=h from=$historique[0]}
	{
		x:{$h.date*1000},
		y:{$h.indicateur}
	},

	{if $prix_min==-1 || $h.indicateur<$prix_min}
		{assign var="prix_min" value=$h.indicateur}
	{/if}
{/foreach} 


{foreach key=idConcurrent item=nomConcurrent from=$concurrents_filtrer}
				] },
		{ 
		name: '{$nomConcurrent|addslashes}'
		,data: [
		
	{foreach item=h from=$historique[$idConcurrent]}
		{
			x:{$h.date*1000},
			y:{$h.indicateur},
		},
	
		{if $prix_min==-1 || $h.indicateur<$prix_min}
			{assign var="prix_min" value=$h.indicateur}
		{/if}
	{/foreach} 
{/foreach} 

 
{literal}
				]   }
			 ],
			yAxis: { // left y axis
                title: {
                    text: null
                },
				endOnTick :false,
				min:{/literal}{$prix_min-0.1}{literal},
				startOnTick: false,
                labels: {
                    formatter: function() {
                        return this.value.toFixed(2) 
                    }
                },
                showFirstLabel: false
            }
        });
    });
{/literal}
	
	if( {$prix_min} == 0)
	{
		$('#historique').css("display","none");
		$('#historique_rien').css("display","block");
	}
});
</script>


</div>
 
</form>
</div>
<!-- PricesTracker -->