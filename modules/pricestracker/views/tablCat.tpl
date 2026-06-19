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

<script>
$(document).ready(function() {
        document.title = '{l s='Dashboard' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />

<!-- PricesTracker -->
<form action="" method="post" id="formPrinc">



<div id="filtres" style="clear:both">
<input id="is_id_concurrents" type="checkbox" value="1" {if $id_concurrents}checked{/if} /> {l s='Filter by competitor' mod='pricestracker'}

<script>
$( document ).ready(function() {
	$('#is_id_concurrents').click(function() {
		if($(this).is(':checked')) $('#container_concurrents').show();
		else window.location=document.location.href.replace(/&id_concurrents=[\-0-9,]+/gi,'')
	});
});
</script>
</div>




<script>
$( document ).ready(function() {
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




</form>

<fieldset style="width:98%">
	<legend>{l s='Categories' mod='pricestracker'}</legend>


<table class="table" cellpadding="0" cellspacing="0" align="center" width="100%">
<tbody>
    <tr>
        <th>{l s='Category' mod='pricestracker'}</th>
 	{foreach key=idConcurrent item=conc from=$concurrents_filtrer}
       <th>{$conc}</th>
    {/foreach}
    </tr>
    
	{foreach key=k item=categorie from=$liste_cat}
    <tr>
        <td valign="top">
          
          <a href="{$lien}&tablCat&id_category={$categorie->id}&id_concurrents={$get_id_concurrents}">
		  {$categorie->name[$langue]}
          </a>
</td>

 	{foreach key=idConcurrent item=conc from=$concurrents_filtrer}
       <td>
       {if $cat_p[$categorie->id][$idConcurrent]}
	       {assign var="taux" value={($cat_pe[$categorie->id][$idConcurrent] - $cat_p[$categorie->id][$idConcurrent])/$cat_p[$categorie->id][$idConcurrent]}}
            <span style="color:{if $taux<0}#900{elseif $taux>0}#039603{/if}">
           {if $taux>0}+{/if}{number_format($taux*100,1,',','')}%
           </span>
       {/if}
       </td>
    {/foreach}
          
 
    </tr>
    
    {/foreach}
</tbody>
</table>


</fieldset>

<!-- PricesTracker -->