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

<br /><br />

<script>
$(document).ready(function() {
        document.title = '{l s='List of rules' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='Pricing rules' mod='pricestracker'}</legend>
	<p><a href="{$lien}&ajRegle" class="button">	{l s='Add a rule' mod='pricestracker'}</a></p>
    
    
{if $regles}
<table class="table"cellpadding="0" cellspacing="0">
                        <tbody><tr>
                            <th>{l s='ID' mod='pricestracker'}</th>
                            <th>{l s='Name' mod='pricestracker'}</th>
                            
                            <th>{l s='Actions' mod='pricestracker'}</th>
                            </tr>
    {foreach name=regles key=k item=regle from=$regles}
    
    <tr>
        <td>{$regle.id_regles}</td>
        <td>{$regle.nom}</td>
        
        <td><a href="{$lien}&ajRegle&id={$regle.id_regles}">{l s='Edit' mod='pricestracker'}</a> - <a href="{$lien}&supprRegle&id={$regle.id_regles}">{l s='Delete' mod='pricestracker'}</a></td>
    </tr>
    
    {/foreach}
    
    </tbody></table>
    <br />

    <a href="{$urlDebut}modules/pricestracker/pages/executeRules.php?clef={if _PS_VERSION_ >= '1.5'}{Configuration::getGlobalValue('pricestracker_CLEFACCES')}{else}{Configuration::get('pricestracker_CLEFACCES')}{/if}" target="_blank" class="button">	{l s='Execute all rules' mod='pricestracker'}</a><br />
<br />

<script>
function ajaxExecuteRegleProduit(index)
{
	if (index < ids.length) {
		idProduit = ids[index]
		  pourc = index*100/total
		  pourc=pourc.toFixed(3)
		  $('#statut_ajax').html("In progress - "+pourc+"% - Product #"+idProduit)
		  
		  $.ajax({
			type: "GET",
			url: urlExecuteRules+"&id_product="+idProduit,
		  }).done(function () {
			  setTimeout(function () {
				ajaxExecuteRegleProduit(index + 1);
			  }, 500);
		  });
	}
	else $('#statut_ajax').html("<strong>100% - End of the execution of pricing rules in Ajax</strong>")
}

function ajaxRegles()
{
	$('#statut_ajax').html("Initialisation...")
	urlExecuteRules = "{$urlDebut}modules/pricestracker/pages/executeRules.php?clef={if _PS_VERSION_ >= '1.5'}{Configuration::getGlobalValue('pricestracker_CLEFACCES')}{else}{Configuration::get('pricestracker_CLEFACCES')}{/if}"
	
	$.ajax({
	  url: urlExecuteRules+"&ajax_get_liste=1"
	}).done(function(data) {
		
	  ids=data.split(',')
	  total=ids.length
	  ajaxExecuteRegleProduit(0)
	  
	});
}
</script>
    <a href="javascript:void(0)" onclick="ajaxRegles()" class="button">	{l s='Execute all rules in Ajax (slower but no max time error)' mod='pricestracker'}</a>
    <span id="statut_ajax"></span>

 {/if}
  </fieldset>
</form><br />
<br />

    <a href="{$lien}&supprToutesReglesAsso" target="_blank" class="button" onclick="return confirm('{l s='Are you sure?' mod='pricestracker'}')">	{l s='Remove all associated rules' mod='pricestracker'}</a><br />
<br />
    <button class="button" onclick="idProduit = prompt('{l s='Enter the ID of the product to be checked' mod='pricestracker'}'); window.location.href = '{$urlDebut}modules/pricestracker/pages/executeRules.php?clef={if _PS_VERSION_ >= '1.5'}{Configuration::getGlobalValue('pricestracker_CLEFACCES')}{else}{Configuration::get('pricestracker_CLEFACCES')}{/if}&id_product='+idProduit; return false">	{l s='Control the rules on a single product' mod='pricestracker'}</button>

<!-- PricesTracker -->