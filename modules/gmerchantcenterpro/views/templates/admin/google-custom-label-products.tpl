{*
*
* Google merchant center Pro
*
* @author BusinessTech.fr
* @copyright Business Tech
*
*           ____    _______
*          |  _ \  |__   __|
*          | |_) |    | |
*          |  _ <     | |
*          | |_) |    | |
*          |____/     |_|
*
*}
{if !empty($aErrors)}
{include file="`$sErrorInclude`"}
{* USE CASE - edition review mode *}
{else}
	<div  id="gmcp" class="bootstrap">
		<h3 class="text-center"><i class="fa fa-tags"></i>&nbsp; {l s='Products labeled' mod='gmerchantcenterpro'}</h3>

		<div class="clr_hr"></div>
		<div class="clr_10"></div>

		{if !empty($aProduct)}
			<div class="alert alert-info">
				<p>{l s='The list below gives the products to which the label has been assigned.' mod='gmerchantcenterpro'}</p>
			</div>
			<table class="table table-responsive col-xs-12">
				<thead>
					<tr class="bt_tr_header text-center">
						<th class="text-center">{l s='Id' mod='gmerchantcenterpro'}</th>
						<th class="text-center">{l s='Product name' mod='gmerchantcenterpro'}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$aProduct key=iKey item=product}
						<tr>
							<td class="text-center">{$product.id|escape:'htmlall':'UTF-8'}</td>
							<td class="text-center">{$product.name|escape:'htmlall':'UTF-8'}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		{else}
			<div class="alert alert-warning">
				<p>{l s='No product matching' mod='gmerchantcenterpro'}</p>
			</div>
		{/if}
	</div>

{/if}