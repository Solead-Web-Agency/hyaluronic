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

<script src="{Tools::getAdminUrl()}modules/pricestracker/js/codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="{Tools::getAdminUrl()}modules/pricestracker/js/codemirror/lib/codemirror.css">
<script src="{Tools::getAdminUrl()}modules/pricestracker/js/codemirror/mode/clike/clike.js"></script>

<script src="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/dialog.js"></script>
<script src="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/jump-to-line.js"></script>
<script src="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/search.js"></script>
<script src="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/searchcursor.js"></script>

<link href="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/dialog.css" rel="stylesheet">


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
.CodeMirror {
font-size: 12px !important;
}

</style>


<script>
$(document).ready(function() {
        document.title = '{$regle.nom|escape:'htmlall'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
		
		CodeMirror.fromTextArea(document.getElementById("regle"), {
			lineNumbers: true,
      		lineWrapping: true,
			matchBrackets: true,
			mode: 'clike'
		  });

    });
</script>


<br /><br />

<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='Add a pricing rule' mod='pricestracker'}</legend>

    <a href="{$lien}&regles">&lt;&lt; {l s='Back to rules' mod='pricestracker'}</a><br />
				<br class="clear"/>
	<br class="clear"/>

   

		  <label>{l s='Name' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="nom" value="{$regle.nom|htmlspecialchars}" size="45" id="nom" />
		  </div>
		

				<br class="clear"/>
                

		  <label>{l s='Rule to execute' mod='pricestracker'} :</label>
    <div class="margin-form">
            <textarea name="regle" cols="70" rows="5" id="regle">{$regle.regle|htmlspecialchars}</textarea><br />
      {l s='Input variables : $oldPrice = your current product price with taxes, $competitorPrice = the price of your competitor, $idProduct = product ID of your product, $competitorUrl = URL of the product page of the competitor, $competitorId = ID of the competitor, $arguments = array of arguments that you specify in the field (between commas), $product = Prestashop object Product (with class Product), function getCompetitorsPrices() = return an array with all competitors price (format:[ [id_competitor=>1,price=>15.5], [id_competitor=>2,price=>17.9] ]), function getCompetitorsPricesWithSomeRules() = same than getCompetitorsPrices but just for competitors with rules associated to the product, function getMinimalCompetitorPrice() = return minimal price of all competitors, function getCompetitorInfos() = return all infos imported during the crawl about the competitor product, function getCompetitorInfos() = return all infos imported during the crawl about all competitor\'s products associated with the product' mod='pricestracker'}<br />
			{l s='Output variables : $newPrice = your new product price with taxes (false by default to make no change), $alertMail = send a mail to alert the administrator, true or false (false by default), $alertMailGrouped = send all alerts in one grouped mail, $newArguments = to modify arguments field, $priceProposal = proposal of price, you must validate it to be effective, $newSpecificPrice = if you want to create promotion, $specificPriceType = in the last case, precise the type of promotion (amount, percentage, replace)' mod='pricestracker'}<br />
            {l s='Allow to define the new price of your product or to send an alert with a PHP code. For example :' mod='pricestracker'}<br />
			if( $oldPrice &gt; $competitorPrice + 0.5 &amp;&amp; $competitorPrice &gt; 34.50 )<br />
$newPrice = $competitorPrice - 0.8;

<br />
if( $competitorPrice
    &lt; 35.50 ) $alertMail=true; 

</div>
		


		




    <input type="submit" name="submit" id="submit" value="{l s='Submit' mod='pricestracker'}" class="button" style="margin-left:250px" />
   
   
   
  </fieldset>
 
 

</form>

<form action="https://www.storeinterfacer.com/pricestracker_regle.php" method="post">

    <fieldset>
	<legend>{l s='Ask to idIA Tech to write the rule' mod='pricestracker'}</legend>
 		  <label>{l s='What do you want the rule to do?' mod='pricestracker'} :</label>
		  <div class="margin-form">
   	<textarea name="message" cols="70" rows="5" id="message"></textarea>
    <input name="site" type="hidden" value="{$site}" />
    <input name="clef" type="hidden" value="{$clef}" />
    		</div>
    
    
        <br />

        <input type="submit" name="submit" id="submit" value="{l s='Send' mod='pricestracker'}" class="button" style="margin-left:250px" />
    </fieldset>

<br>
<br>
 
{if $version2 eq 'MEGA'}

       {assign var="messageId" value="{l s='ID(s) of the favorite, separated by comas if sevral (if all do not put anything)' mod='pricestracker'}"}
       {assign var="messageId2" value="{l s='ID(s) of the competitor, separated by comas if sevral (if all do not put anything)' mod='pricestracker'}"}
       {assign var="messageId3" value="{l s='Argument (optionnal)' mod='pricestracker'}"}
       {assign var="messageId4" value="{l s='If the rule has already been defined (with or without the same argument), should it be associated again? (OK = Yes, Cancel = No)' mod='pricestracker'}"}
 
<input type="button" name="add" id="add" value="{l s='Apply this rule to some products' mod='pricestracker'}" class="button" style="margin-left:250px" onclick="id=prompt('{$messageId|addslashes}');  { idConcurrent=prompt('{$messageId2|addslashes}'); argum=prompt('{$messageId3|addslashes}'); associationsMultiples=confirm('{$messageId4|addslashes}'); $.ajax('{$lien}&regleFavoris&id_regle={$id}&id_fav='+id+'&id_concurrent='+idConcurrent+'&arguments='+escape(argum)+'&associationsMultiples='+escape(associationsMultiples)).done(function(data) { alert('OK'); }); return true; }" /><br>
<br>
<input type="button" name="add" id="add" value="{l s='Remove this rule from a favorite' mod='pricestracker'}" class="button" style="margin-left:250px" onclick="id=prompt('{$messageId|addslashes}');  { idConcurrent=prompt('{$messageId2|addslashes}');  $.ajax('{$lien}&supprRegleFav&associationsMultiples=1&id_regle={$id}&id_fav='+id+'&id_concurrent='+idConcurrent).done(function(data) { alert('OK'); }); return true; }" />

{/if}
<!-- PricesTracker -->