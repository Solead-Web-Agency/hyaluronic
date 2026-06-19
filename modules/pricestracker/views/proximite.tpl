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
</style>
<br /><br />


<script>
$(document).ready(function() {
        document.title = '{l s='Proximities' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>

<script>
function operation(url,bouton,i,total=null)
{
	if(!total)
	{
		$(bouton).html("IN PROGRESS - WAIT PLEASE")
		total = 1
		htmlTotal = $.ajax({
			url: url + "&pack=0",
			async: false, // Rend l'appel AJAX synchrone
			success: function(data) {
				htmlTotal = data;
			}
		}).responseText;
		total = parseInt( htmlTotal , 10);
		$(bouton).html("0%")
	}
	
	$.ajax({
	  url: url+"&pack="+i,
	}).done(function(data) {
	  if(data && data.trim()=='STOP')
	  {
		  alert("END OF ASSOCIATIONS")
		  $(bouton).html("SUCCESS")
	  }
	  else
	  {
		  $(bouton).html((i*100 / total).toFixed(2)+"%")
		  operation(url,bouton,i+1,total)
	  }
	}).fail(function() {
		$(bouton).html("Failed - "+(i*100 / total).toFixed(2)+"%")
		operation(url,bouton,i+1,total)
	});
}
</script>


<!-- PricesTracker -->
<form action="" method="post">
{if $proximites}
  <fieldset>
	<legend>{l s='Proximity rules' mod='pricestracker'}</legend>

    
    
        <table class="table"cellpadding="0" cellspacing="0">
                        <tbody><tr>
                            <th>{l s='ID Category'}</th>
                            <th>{l s='ID Favoris'}</th>
                            <th>{l s='Competitor' mod='pricestracker'}</th>
                            <th>{l s='Maximum number of associations for client\'s products' mod='pricestracker'}</th>
                            <th>{l s='Maximum number of associations for competitor\'s products' mod='pricestracker'}</th>
                            <th>{l s='Minimum proximity' mod='pricestracker'}</th>
                            <th>{l s='Do not associate after the Xth suggestion' mod='pricestracker'}</th>
                            <th>{l s='Just  products not associated' mod='pricestracker'}</th>
                            <th>{l s='Using ChatGPT' mod='pricestracker'}</th>
                            <th>{l s='[ChatGPT] Go to the Xth' mod='pricestracker'}</th>
                            <th>{l s='[ChatGPT] Model' mod='pricestracker'}</th>
                            
                            <th>{l s='Actions' mod='pricestracker'}</th>
                            </tr>
    {foreach name=proximite key=k item=proximite from=$proximites}
    
    <tr>
        <td>{if !$proximite.id_category}{l s='All' mod='pricestracker'}{else}{$proximite.id_category}{/if}</td>
        <td>{if !$proximite.id_favoris}{l s='All' mod='pricestracker'}{else}{$proximite.id_favoris}{/if}</td>
        <td>{if !$proximite.id_concurrents}{l s='All' mod='pricestracker'}{else}{if isset($concurrents[$proximite.id_concurrents])}{$concurrents[$proximite.id_concurrents]}{else}{$proximite.id_concurrents}{/if}{/if}</td>
        <td>{$proximite.max_produits}</td>
        <td>{$proximite.max_asso_pe}</td>
        <td>{$proximite.min_proximite}</td>
        <td>{$proximite.max_suggestion}</td>
        <td>{$proximite.just_nvo}</td>
        <td>{$proximite.utiliser_chatgpt}</td>
        <td>{$proximite.aller_jusquau_chatgpt}</td>
        <td>{$proximite.modele_chatgpt}</td>
        
        <td>
          <a href="{$urlDebut}modules/pricestracker/pages/executeProx.php?clef={Configuration::get('pricestracker_CLEFACCES')}&id_category={$proximite.id_category}&id_favoris={$proximite.id_favoris}&id_concurrents={$proximite.id_concurrents}&max_produits={$proximite.max_produits}&max_asso_pe={$proximite.max_asso_pe}&max_suggestion={$proximite.max_suggestion}&min_proximite={$proximite.min_proximite}&just_nvo={$proximite.just_nvo}&utiliser_chatgpt={$proximite.utiliser_chatgpt}&aller_jusquau_chatgpt={$proximite.aller_jusquau_chatgpt}&modele_chatgpt={$proximite.modele_chatgpt|escape:'url'}" target="_blank" class="button">	{l s='Execute the rule' mod='pricestracker'}</a><a onclick="operation('{$urlDebut}modules/pricestracker/pages/executeProx.php?clef={Configuration::get('pricestracker_CLEFACCES')}&id_category={$proximite.id_category}&id_favoris={$proximite.id_favoris}&id_concurrents={$proximite.id_concurrents}&max_produits={$proximite.max_produits}&max_asso_pe={$proximite.max_asso_pe}&max_suggestion={$proximite.max_suggestion}&min_proximite={$proximite.min_proximite}&just_nvo={$proximite.just_nvo}&utiliser_chatgpt={$proximite.utiliser_chatgpt}&aller_jusquau_chatgpt={$proximite.aller_jusquau_chatgpt}&modele_chatgpt={$proximite.modele_chatgpt|escape:'url'}',this,1)" class="button">{l s='with AJAX' mod='pricestracker'}</a> -   <a href="{$lien}&proximite&supprProx&id={$proximite.id_proximite_regles}">{l s='Delete' mod='pricestracker'}</a><br />
<br />

          <a href="{$urlDebut}modules/pricestracker/pages/executeProx.php?clef={Configuration::get('pricestracker_CLEFACCES')}&id_category={$proximite.id_category}&id_favoris={$proximite.id_favoris}&id_concurrents={$proximite.id_concurrents}&max_produits={$proximite.max_produits}&max_asso_pe={$proximite.max_asso_pe}&max_suggestion={$proximite.max_suggestion}&min_proximite={$proximite.min_proximite}&just_nvo={$proximite.just_nvo}&utiliser_chatgpt={$proximite.utiliser_chatgpt}&aller_jusquau_chatgpt={$proximite.aller_jusquau_chatgpt}&modele_chatgpt={$proximite.modele_chatgpt|escape:'url'}&supprAncien=1" target="_blank" class="button" onclick="return confirm('{l s='Are you sure?' mod='pricestracker'|addslashes}')">	{l s='Execute the rule and delete old associations' mod='pricestracker'}</a><a onclick="operation('{$urlDebut}modules/pricestracker/pages/executeProx.php?clef={Configuration::get('pricestracker_CLEFACCES')}&id_category={$proximite.id_category}&id_favoris={$proximite.id_favoris}&id_concurrents={$proximite.id_concurrents}&max_produits={$proximite.max_produits}&max_asso_pe={$proximite.max_asso_pe}&max_suggestion={$proximite.max_suggestion}&min_proximite={$proximite.min_proximite}&just_nvo={$proximite.just_nvo}&utiliser_chatgpt={$proximite.utiliser_chatgpt}&aller_jusquau_chatgpt={$proximite.aller_jusquau_chatgpt}&modele_chatgpt={$proximite.modele_chatgpt|escape:'url'}&supprAncien=1',this,1)" class="button" onclick="return confirm('{l s='Are you sure?' mod='pricestracker'|addslashes}')">{l s='with AJAX' mod='pricestracker'}</a>

    </td>
    </tr>
    
    {/foreach}
    
    </tbody></table>
    
 
<!--    <a href="{$urlDebut}modules/pricestracker/pages/executeProx.php?clef={Configuration::get('pricestracker_CLEFACCES')}&executeTous" target="_blank" class="button">	{l s='Execute all rules' mod='pricestracker'}</a>
-->
  </fieldset>
 {/if}
  
  <fieldset>
	<legend>{l s='Add a proximity rule' mod='pricestracker'}</legend>

		  <label>ID Category :</label>
		  <div class="margin-form">
			<input type="text" name="id_category" id="id_category" value="" size="45" />
		  </div>


		  <label>ID Favoris :</label>
		  <div class="margin-form">
			<input type="text" name="id_favoris" id="id_favoris" value="" size="45" />
		  </div>


		  <label>{l s='Competitor ID' mod='pricestracker'} {l s='or list between virgule (,)' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="id_concurrents" id="id_concurrents" value="" size="45" />
		  </div>

<br />

		  <label>{l s='Maximum number of associations for client\'s products' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="max_produits" id="max_produits" value="1" size="45" />
		  </div>
          
<br />

		  <label>{l s='Maximum number of associations for competitor\'s products' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="max_asso_pe" id="max_asso_pe" value="" size="45" />
		  </div>
<br />


		  <label>{l s='Minimum proximity' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="min_proximite" id="min_proximite" value="" size="45" />
		  </div>



		  <label>{l s='Do not associate after the Xth suggestion' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="max_suggestion" id="max_suggestion" value="" size="45" />
		  </div>

		  <label style="width: auto;">{l s='Just  products not associated' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input name="just_nvo" type="checkbox" value="1" checked />
		  </div>
          <br style="clear:both" />


		  <label>{l s='Use ChatGPT to match products (after pre-sorting by integrated AI)' mod='pricestracker'} :</label>
		  <div class="margin-form">
           <label style="width: auto;">
			<input name="utiliser_chatgpt" type="checkbox" value="1" />
           {l s='Yes' mod='pricestracker'} </label>
            {l s='This option requires you to set up a Cloud account with idIA Tech and to enter this Cloud account in Analyse > Manage competitors > Cloud Settings. Ticking this box will consume approximately $0.05 per thousand matching evaluations under gpt-3.5-turbo-0125 and 10 times more under gpt-4o and $0.02 under gpt-4o-mini.' mod='pricestracker'}
    </div>
          <br style="clear:both">

		  <label>{l s='[Option for ChatGPT] If not recognised, continue the ChatGPT matching until the Xth closest competing product is found' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="aller_jusquau_chatgpt" id="aller_jusquau_chatgpt" value="5" size="45" />
		  </div>
          <br style="clear:both">

		  <label>{l s='[Option for ChatGPT] OpenIA model to use' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="modele_chatgpt" id="modele_chatgpt" value="gpt-4o-mini" size="45" /><br />
{l s='Current values' mod='pricestracker'} : gpt-3.5-turbo-0125, gpt-4o, gpt-3.5-turbo, gpt-4-turbo. <a href="https://beta.openai.com/docs/models/overview" target="_blank">{l s='Chose a model' mod='pricestracker'}</a>.
		  </div>
          

      <input type="submit" name="submit" id="submit" value="{l s='Submit' mod='pricestracker'}" class="button" style="margin-left:250px" />


  </fieldset>
<br />
<br />
      <a class="button" href="{$lien}&proximite&displayProx" style="margin-left:250px">{if $displayProx}{l s='Do not display proximity in Product Matching' mod='pricestracker'}{else}{l s='Display proximity in Product Matching' mod='pricestracker'}{/if}</a><br />
  <br />
  {l s='Note: products with the same references have a proximity score of over 10000.' mod='pricestracker'} <br />
<br />
<br />
      <a class="button" href="{$lien}&proximite&supprAsso" style="margin-left:250px" onclick="return confirm('{l s='Are you sure you\'re going to remove all the associations for all the competitors and all the products?' mod='pricestracker'|addslashes}')">{l s='Delete all associations' mod='pricestracker'}</a><br />
<br />

      <a class="button" href="{$lien}&proximite&supprAssoAutomatiques" style="margin-left:250px" onclick="return confirm('{l s='Are you sure you\'re going to remove all the associations that have been put in place by the rules on this panel for all the competitors and all the products?' mod='pricestracker'|addslashes}')">{l s='Delete all automatic associations' mod='pricestracker'}</a>

</form>
<!-- PricesTracker -->