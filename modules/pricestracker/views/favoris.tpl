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
        document.title = '{l s='List of favorites' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />

<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='Favorites' mod='pricestracker'}</legend>
	<p><a href="{$lien}&ajFavoris" class="button">	{l s='Add a favorites' mod='pricestracker'}</a></p>
    
    
{if $favoris}
        <table class="table"cellpadding="0" cellspacing="0">
                        <tbody><tr>
                            <th>{l s='ID' mod='pricestracker'}</th>
                            <th>{l s='Name' mod='pricestracker'}</th>
                            
                            <th>{l s='Actions' mod='pricestracker'}</th>
                            </tr>
    {foreach name=favoris key=k item=favori from=$favoris}
    
    <tr>
        <td>{$favori.id_favoris}</td>
        <td>{$favori.nom}</td>
        
        <td><a href="{$lien}&ajFavoris&id={$favori.id_favoris}">{l s='Edit' mod='pricestracker'}</a> - <a href="{$lien}&supprFavoris&id={$favori.id_favoris}">{l s='Delete' mod='pricestracker'}</a></td>
    </tr>
    
    {/foreach}
    
    </tbody></table>
 {/if}
  </fieldset>
</form>
<!-- PricesTracker -->