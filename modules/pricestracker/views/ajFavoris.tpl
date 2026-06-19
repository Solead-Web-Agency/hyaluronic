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
        document.title = '{$favoris.nom|escape:'htmlall'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />

<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='Add a favorite' mod='pricestracker'}</legend>

    <a href="{$lien}&favoris">&lt;&lt; {l s='Back to favorites' mod='pricestracker'}</a><br />
<br />
<br />

   

		  <label>{l s='Name' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="nom" value="{$favoris.nom|htmlspecialchars}" size="45" id="nom" />
		  </div>
		

		


		




    <p>
    <input type="submit" name="submit" id="submit" value="{l s='Submit' mod='pricestracker'}" class="button" style="margin-left:250px" />
   
   <br />
<br />
    {assign var="messageId" value="{l s='ID of the category' mod='pricestracker'}"}
      {assign var="messageId3" value="{l s='ID of the manufacturer' mod='pricestracker'}"}
      {assign var="messageId5" value="{l s='Products IDs separated by , (ex: 25,35,41)' mod='pricestracker'}"}
      {assign var="messageId2" value="{l s='Words contained in the name of products. Ex : Tshirt & (madonna|lopez)' mod='pricestracker'}"}
      
      {if $version2 eq 'MEGA'}
      
    <input type="button" name="add" id="add" value="{l s='Add products of a whole category' mod='pricestracker'}" class="button" style="margin-left:250px" onclick="id=prompt('{$messageId|addslashes}'); if(!id) return false; else { $.ajax('{$lien}&ajFavCat&id_fav={$id}&id_category='+id).done(function() { alert('OK') }); return true; }" />
<br />
<br />
      <input type="button" name="add" id="add" value="{l s='Add products of a brand' mod='pricestracker'}" class="button" style="margin-left:250px" onclick="id=prompt('{$messageId3|addslashes}'); if(!id) return false; else { $.ajax('{$lien}&ajFavManuf&id_fav={$id}&id_manufacturer='+id).done(function() { alert('OK') }); return true; }" />
<br />
<br />
      <input type="button" name="add" id="add" value="{l s='Add all products' mod='pricestracker'}" class="button" style="margin-left:250px" onclick=" { $.ajax('{$lien}&ajFavAll&id_fav={$id}&id_category='+id).done(function() { alert('OK') }); return true; }" />
    </p>
    <p>
      <input type="button" name="add2" id="add2" value="{l s='Add products which the name contains certains words' mod='pricestracker'}" class="button" style="margin-left:250px" onclick="id=prompt('{$messageId2|addslashes}'); if(!id) return false; else { $.ajax('{$lien}&ajFavNom&id_fav={$id}&nom='+escape(id)).done(function() { alert('OK') }); return true; }" />
      <span style="margin-left:250px">{l s='Ex: "Tshirt & (madonna|lopez)" adds products which have in their names "Tshirt" AND ("madonna" OR "lopez")' mod='pricestracker'}
      
      <br />
      <br />
      <input type="button" name="add3" id="add3" value="{l s='Add a list of product IDs' mod='pricestracker'}" class="button" style="margin-left:250px" onclick="id=prompt('{$messageId5|addslashes}'); if(!id) return false; else { $.ajax('{$lien}&ajFavIds&id_fav={$id}&ids='+id).done(function() { alert('OK') }); return true; }" /><br />
<br />

      
      <br />
      <br />
      <input type="button" name="add3" id="add3" value="{l s='Unassociate a list of product IDs' mod='pricestracker'}" class="button" style="margin-left:250px" onclick="id=prompt('{$messageId5|addslashes}'); if(!id) return false; else { $.ajax('{$lien}&supprFavIds&id_fav={$id}&ids='+id).done(function() { alert('OK') }); return true; }" />
      
      <br />
      <br />
      <input type="button" name="add3" id="add3" value="{l s='Unassociate all products from this favorite' mod='pricestracker'}" class="button" style="margin-left:250px" onclick="sure=confirm('Sure?'); if(!sure) return false; else { $.ajax('{$lien}&supprTousFav&id_fav='+{$id}).done(function() { alert('OK') }); return true; }" />
{/if} </p>
   
  </fieldset>
</form>
<!-- PricesTracker -->