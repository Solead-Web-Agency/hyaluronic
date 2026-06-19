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
</style>

<script>
$(document).ready(function() {
        document.title = '{l s='List of competitors' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />

{if $modeTest}
<div>{l s='Note: With the test version you can add only 1 competitor' mod='pricestracker'}</div><br>

{/if}


<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='Competitors' mod='pricestracker'}</legend>
	<p><a href="{$lien}&analyse" class="button">&lt;&lt; {l s='Back to analysis' mod='pricestracker'}</a><br /><br />
    <a href="{$lien}&ajConcurrent" class="button">	{l s='Add a competitors' mod='pricestracker'}</a></p>
    
    
{if $concurrents}
        <table class="table"cellpadding="0" cellspacing="0">
                        <tbody><tr>
                            <th>{l s='ID' mod='pricestracker'}</th>
                            <th>{l s='Name' mod='pricestracker'}</th>
                            <th>{l s='URL' mod='pricestracker'}</th>
                            <th>{l s='Actions' mod='pricestracker'}</th>
                            </tr>
    {foreach name=concurrents key=k item=concurrent from=$concurrents}
    
    <tr>
        <td>{$concurrent.id_concurrents}</td>
        <td>{$concurrent.nom}</td>
        <td>{$concurrent.url}</td>
        <td><a href="{$lien}&ajConcurrent&id={$concurrent.id_concurrents}">{l s='Edit' mod='pricestracker'}</a> - <a href="{$lien}&supprConcurrent&id={$concurrent.id_concurrents}" onclick="return confirm('{l s='Are you sure to delete ?' mod='pricestracker'}')">{l s='Delete' mod='pricestracker'}</a> - <a href="{$lien}&cloud&id={$concurrent.id_concurrents}">{l s='Cloud settings' mod='pricestracker'}</a> - 
    {if $version2 neq 'Silver'}
        {if $concurrent.actif}
            <a href="{$lien}&concurrents&actif=0&id={$concurrent.id_concurrents}"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/enabled.gif" title="{l s='Competitor enabled for analysis' mod='pricestracker'}" /></a>
        {else}
            <a href="{$lien}&concurrents&actif=1&id={$concurrent.id_concurrents}"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/disabled.gif" title="{l s='Competitor disabled for analysis' mod='pricestracker'}" /></a>
        {/if}
     {/if}
        </td>
    </tr>
    
    {/foreach}
    
    </tbody></table>
 {/if}
 
{if $version2 eq 'MEGA'}
 <br />
 <br />
 <a href="{$lien}&concurrents&ajMarketplace=amazon" class="button">	{l s='Add Amazon' mod='pricestracker'}</a> <br /> <br />
 <a href="{$lien}&concurrents&ajMarketplace=ebay" class="button">	{l s='Add Ebay' mod='pricestracker'}</a> <br /> <br />
 <a href="{$lien}&concurrents&ajMarketplace=priceminister" class="button">	{l s='Add PriceMinister' mod='pricestracker'}</a> <br /> <br />
 <a href="{$lien}&concurrents&ajMarketplace=cdiscount" class="button">	{l s='Add CDiscount' mod='pricestracker'}</a>
{/if} 
</fieldset>
</form>
<!-- PricesTracker -->