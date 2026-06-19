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
#container_concurrents label {
	float:none;
}
#categories-tree label
{
	color:inherit !important;
	float:inherit !important;
	width:auto !important;
}
.bootstrap .tree-panel-heading-controls
{
	margin:0 !important;
}
.encart
{
position: relative;
padding: 20px;
margin-bottom: 20px;
margin-right:20px;
border-radius: 5px;
box-shadow: 0px 2px 0px rgba(0, 0, 0, 0.1), 0px 0px 0px 3px #FFF inset;
border: 1px solid #E6E6E6;
background-color: #FFF;
}
</style>
<br /><br />

<script>
$(document).ready(function() {
        document.title = '{l s='Rapid pricing' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>



<!-- PricesTracker -->
<form action="" method="post" id="formPrinc">

<script>
function clicRech()
{
	if($('#rech').val()=='{l s='Keyword, EAN, ISBN, Reference...' mod='pricestracker'}') $('#rech').val('');
}
function blurRech()
{
	if($('#rech').val()=='') $('#rech').val('{l s='Keyword, EAN, ISBN, Reference...' mod='pricestracker'}');
}

$( document ).ready(function() {
	$('.correction').click(function() {
		$('#rech').val($(this).attr('data-ean'));
		$('#formPrinc').submit()
	});
});
</script>


<div id="filtres" style="clear:both">
  <input type="text" name="rech" id="rech" onclick="clicRech()" onblur="blurRech()" value="{if $smarty.post.rech}{$smarty.post.rech}{else}{l s='Keyword, EAN, ISBN, Reference...' mod='pricestracker'}{/if}" style="width:300px" /><!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;


    <input type="checkbox" name="plusCher" id="plusCher" />
    Display the most expensive-->
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <input type="submit" name="button" id="button" value="{l s='Search' mod='pricestracker'}" class="button" />
</div>




</form>

{if $trouver}
<fieldset style="width:98%">
	<legend>{l s='Product' mod='pricestracker'}</legend>

<table width="100%" border="0">
  <tr>
    <td>
<p>
{if $image}<img src="{$image}" align="left" />{/if}
<h2>{$titre}</h2>
EAN: {$ean}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{if $reference_priceminister && $ean!=$reference_priceminister}Ref: {$reference_priceminister}{/if}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{if $isbn}ISBN: {$isbn}{/if}<br />
{if $prix_conseille}{l s='Recommanded price' mod='pricestracker'}: {Product::convertAndFormatPrice($prix_conseille)}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{/if}
SalesRank: {$rang_amazon}
</p></td>
    <td align="right">
    {if $corrections}
    {l s='Others choices' mod='pricestracker'}:
    {$corrections}
    {/if}
    </td>
  </tr>
</table>

</fieldset>

<fieldset style="width:98%">
	<legend>{l s='Prices' mod='pricestracker'}</legend>


<table class="table" cellpadding="0" cellspacing="0" align="center" width="100%">
<tbody>
    <tr>
        <th>{l s='Marketplace' mod='pricestracker'}</th>
       <th>{l s='Lower New price' mod='pricestracker'}</th>
       <th>{l s='Lower Used price' mod='pricestracker'}</th>
       <th>{l s='Option' mod='pricestracker'}</th>
    </tr>
    
    <tr>
        <td valign="top">
          <a href="{$lien_amazon}" target="_blank">
		  <img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/amazon.png" />
          </a>
        </td>
         <td valign="top">
         {if $prix_amazon}{Product::convertAndFormatPrice($prix_amazon)}{/if}
		</td>
         <td valign="top">
         {if $prix_occas_amazon}{Product::convertAndFormatPrice($prix_occas_amazon)}{/if}
		</td>
         <td>
         <a href="https://www.idia-tech.com/contact.php" target="_blank" class="button">
         {l s='Sell yours' mod='pricestracker'}
         </a>
		</td>
    </tr>
    
    <tr>
        <td valign="top">
          <a href="{$lien_priceminister}" target="_blank">
		  <img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/priceminister.png" />
          </a>
        </td>
        <td valign="top">{if $prix_priceminister} {Product::convertAndFormatPrice($prix_priceminister)} <br />
 {if $frais_priceminister}
      <span style="font-size:9px">  + {Product::convertAndFormatPrice($frais_priceminister)} = 
   {Product::convertAndFormatPrice($prix_priceminister+$frais_priceminister)}   
</span>
{/if}{/if}</td>
        <td valign="top">{if $prix_occas_priceminister} {Product::convertAndFormatPrice($prix_occas_priceminister)}
 {if $frais_occas_priceminister} <br />
      <span style="font-size:9px">  + {Product::convertAndFormatPrice($frais_occas_priceminister)} = 
   {Product::convertAndFormatPrice($prix_occas_priceminister+$frais_occas_priceminister)}   
</span>
{/if}{/if} </td>
        <td><a href="https://www.idia-tech.com/contact.php" target="_blank" class="button"> {l s='Sell yours' mod='pricestracker'} </a></td>
      </tr>
    
</tbody>
</table>


</fieldset>

{/if}

<!-- PricesTracker -->