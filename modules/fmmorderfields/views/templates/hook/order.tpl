{*
*
*================ FME Order Fields Module =================
*===============================================
* 2013 FME Modules - http://www.fmemodules.com
*
*
*  @author Kashif Raza
*  @copyright  2014 FME Modules ---- http://www.fmemodules.com
*  @
*}


<!-- FME Order Fields Module -->
{if $FILTER_CONFIG == 1}
<input type="hidden" value="{$CUR_CURENCY}" id="curCurencySign" />
{if $PS_VER == 1}
<script type="text/javascript">
jQuery(document).ready(function() {
								jQuery('table.table.order tbody').append('<tr class="alt_row row_hover"><td class="center"></td><td  class="pointer center"> <b style="color:#F03D25"> Total: </b></td><td class="pointer center"> &nbsp; </td><td class="pointer center"> &nbsp; </td><td class="pointer center"> &nbsp; </td><td class="pointer"> &nbsp; </td><td class="pointer"> <strong style="color:#F03D25" id="totalFilteredProducts"></strong> </td><td class="pointer text-right"><strong style="color:#F03D25" id="totalFilteredAmount"></strong></td><td class="pointer">&nbsp; </td><td class="pointer"><span class="color_field"> &nbsp; </span></td><td class="pointer right"> &nbsp;</td><td class="pointer center"><span style="width:20px; margin-right:5px;">&nbsp; </span> <span style="width:20px;">&nbsp; </span></td><td style="white-space: nowrap;" class="center">&nbsp;</td><td class="center"></td></tr>');
								var eachAmountsum = 0; var eachAmountsumProd = 0; 
								jQuery('table.table.order tbody td .thisProductTotal').each(
																						function() {
																											var eachAmount = jQuery(this).text().trim().replace(/[^\d\.]/g, '');
																											var convRate = jQuery(this).parent().next('td').find('span.convRate').text().trim();
																											convRate = 1 / convRate;
																											eachAmount = eachAmount*convRate; //alert(eachAmount);
																											eachAmountsum += parseFloat(eachAmount);
																											
																							}
																						);
								eachAmountsum = ceilf(eachAmountsum * 100) / 100;
								var curSign = jQuery('#curCurencySign').val();
								jQuery('#totalFilteredAmount').text(curSign+eachAmountsum);
								
								jQuery('table.table.order tbody td .eachProductAmount').each(
																						function() {
																							var eachAmountProd = jQuery(this).text().trim();
																							eachAmountsumProd += parseFloat(eachAmountProd);
																						}
																						);
								jQuery('#totalFilteredProducts').text(eachAmountsumProd);
								//alert(eachAmountsumProd);
});
</script>
{else}
<script type="text/javascript">
jQuery(document).ready(function() {
								jQuery('table.table.order tbody').append('<tr class="alt_row row_hover"><td class="center"></td><td  class="pointer center"> <b style="color:#F03D25"> Total: </b></td><td class="pointer center"> &nbsp; </td><td class="pointer center"> &nbsp; </td><td class="pointer"> &nbsp; </td><td class="pointer"> <strong style="color:#F03D25" id="totalFilteredProducts"></strong> </td><td class="pointer right"><strong style="color:#F03D25" id="totalFilteredAmount"></strong></td><td class="pointer">&nbsp; </td><td class="pointer"><span class="color_field"> &nbsp; </span></td><td class="pointer right"> &nbsp;</td><td class="pointer center"><span style="width:20px; margin-right:5px;">&nbsp; </span> <span style="width:20px;">&nbsp; </span></td><td style="white-space: nowrap;" class="center">&nbsp;</td><td class="center"></td></tr>');
								var eachAmountsum = 0; var eachAmountsumProd = 0; 
								jQuery('table.table.order tbody td.pointer.right b').each(
																						function() {
																											var eachAmount = jQuery(this).text().trim().replace(/[^\d\.]/g, '');
																											var convRate = jQuery(this).parent().next('td').find('span.convRate').text().trim();
																											convRate = 1 / convRate;
																											eachAmount = eachAmount*convRate; //alert(eachAmount);
																											eachAmountsum += parseFloat(eachAmount);
																											
																							}
																						);
								eachAmountsum = ceilf(eachAmountsum * 100) / 100;
								var curSign = jQuery('#curCurencySign').val();
								jQuery('#totalFilteredAmount').text(curSign+eachAmountsum);
								
								jQuery('table.table.order tbody td .eachProductAmount').each(
																						function() {
																							var eachAmountProd = jQuery(this).text().trim();
																							eachAmountsumProd += parseFloat(eachAmountProd);
																						}
																						);
								jQuery('#totalFilteredProducts').text(eachAmountsumProd);
								//alert(eachAmountsumProd);
});
</script>
{/if}

{/if}

