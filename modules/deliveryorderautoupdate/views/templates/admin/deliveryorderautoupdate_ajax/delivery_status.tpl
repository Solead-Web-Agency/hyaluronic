{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}
 
<div id="delivery_status" class="col-md-6 right panel">
    <div class="carrier">
		<div class="block-title">
			<input type="radio" name="status" value="" {if empty($selectedStatus)}checked{/if}>
			{l s='All statuses' mod='deliveryorderautoupdate'}
		</div>
		<table>
			{foreach $deliveryStatus as $i => $detail}
			{if $overview.total}
			{math assign="ratio" equation="(x / y) * 100" x=$detail.shipments y=$overview.total format="%.2f"}
			{else}
			{assign var='ratio' value=0}
			{/if}
			<tr>
				<td style="width: 220px;{if $detail.id_status != 1 && $detail.id_status != 'not_delivered'}padding-left: 20px;{/if}">
					<input type="radio" name="status" value="{$detail.id_status|escape:'html':'UTF-8'}" {if $selectedStatus==$detail.id_status}checked{/if}>
					<span>{$detail.label|escape:'html':'UTF-8'}</span>
				</td>
				<td style="width: 100px;text-align:right;padding-right:15px;font-weight:600">{$detail.shipments|escape:'html':'UTF-8'}</td>
				<td style="width:250px">
                <div class="group-wrapper">
                    <div class="group-progress" style="width:{$ratio|escape:'html':'UTF-8'}%;background-color: {$detail.color|escape:'html':'UTF-8'};">
                    <span class="ratio">{$ratio|escape:'html':'UTF-8'}%</span>
					</div>
				</div>
				</td>
			</tr>
       {/foreach}
		</table>
	</div>
</div>