{*
* 2007-2022 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

<div class="shopping-cart-list ets_aban_listsavecart">
	<h6>{l s='Here are the shopping carts you have saved' mod='ets_abandonedcart'}</h6>
	{if !empty($msg_success)}<ul class="ets_abancart_messages alert alert-success">
		{foreach from=$msg_success item='msg'}
			<li>{$msg|escape:'html':'UTF-8'}</li>
		{/foreach}
	</ul>{/if}
    {if !empty($carts)}
		<table class="table table-striped table-bordered table-labeled">
			<thead class="thead-default">
			<tr>
				<th class="text-center">{l s='Cart ID' mod='ets_abandonedcart'}</th>
				<th class="text-center">{l s='Cart name' mod='ets_abandonedcart'}</th>
				<th class="text-center">{l s='Product(s)' mod='ets_abandonedcart'}</th>
				<th class="text-center">{l s='Total cost' mod='ets_abandonedcart'}</th>
				<th class="text-center ets_aban_action">{l s='Action' mod='ets_abandonedcart'}</th>
			</tr>
			</thead>
			<tbody>
            {foreach from=$carts item=cart}
				<tr>
					<th class="text-center" scope="row">{$cart.id_cart|intval}</th>
					<td class="text-center">{$cart.cart_name|escape:'html':'UTF-8'}</td>
					<td class="text-xs-left">
						{if !empty($cart.products)}<ul class="ets_abancart_products">{foreach from=$cart.products item=product}
							<li><a href="{$product.link nofilter}" title="{$product.name|escape:'html':'UTF-8'}"><img src="{$product.image nofilter}" alt="{$product.name|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" /></a></li>
						{/foreach}</ul>{/if}
					</td>
					<td class="text-center"><span class="badge-info">{$cart.total|escape:'html':'UTF-8'}</span></td>
					<td class="text-center ets_aban_action" cart-actions">
						<a class="ets_abancart_view_shopping_cart" href="{$cart.view_url nofilter}"><i class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
								<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1664 960q-152-236-381-353 61 104 61 225 0 185-131.5 316.5t-316.5 131.5-316.5-131.5-131.5-316.5q0-121 61-225-229 117-381 353 133 205 333.5 326.5t434.5 121.5 434.5-121.5 333.5-326.5zm-720-384q0-20-14-34t-34-14q-125 0-214.5 89.5t-89.5 214.5q0 20 14 34t34 14 34-14 14-34q0-86 61-147t147-61q20 0 34-14t14-34zm848 384q0 34-20 69-140 230-376.5 368.5t-499.5 138.5-499.5-139-376.5-368q-20-35-20-69t20-69q140-229 376.5-368t499.5-139 499.5 139 376.5 368q20 35 20 69z"/></svg>
							</i> {l s='View' mod='ets_abandonedcart'}</a>
						<a href="{$cart.load_cart_url nofilter}" class="ets_abancart_checkout_cart" id="submit_load_cart" name="submitLoadCart">
							<i class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
								<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M704 1536q0 52-38 90t-90 38-90-38-38-90 38-90 90-38 90 38 38 90zm896 0q0 52-38 90t-90 38-90-38-38-90 38-90 90-38 90 38 38 90zm128-1088v512q0 24-16.5 42.5t-40.5 21.5l-1044 122q13 60 13 70 0 16-24 64h920q26 0 45 19t19 45-19 45-45 19h-1024q-26 0-45-19t-19-45q0-11 8-31.5t16-36 21.5-40 15.5-29.5l-177-823h-204q-26 0-45-19t-19-45 19-45 45-19h256q16 0 28.5 6.5t19.5 15.5 13 24.5 8 26 5.5 29.5 4.5 26h1201q26 0 45 19t19 45z"/></svg>
							</i> {l s='Checkout' mod='ets_abandonedcart'}</a>
						<a href="{$cart.delete_url nofilter}" class="ets_abancart_delete_cart btn-default" data-confirm="{l s='Do you want to delete this item?' mod='ets_abandonedcart'}">
							<i class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
								<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1490 1322q0 40-28 68l-136 136q-28 28-68 28t-68-28l-294-294-294 294q-28 28-68 28t-68-28l-136-136q-28-28-28-68t28-68l294-294-294-294q-28-28-28-68t28-68l136-136q28-28 68-28t68 28l294 294 294-294q28-28 68-28t68 28l136 136q28 28 28 68t-28 68l-294 294 294 294q28 28 28 68z"/></svg>
							</i> {l s='Delete' mod='ets_abandonedcart'}</a>
					</td>
				</tr>
            {/foreach}
			</tbody>
		</table>
    {else}<p class="ets_abancart_no_cart">{l s='No content' mod='ets_abandonedcart'}</p>{/if}
</div>