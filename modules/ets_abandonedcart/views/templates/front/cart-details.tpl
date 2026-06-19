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

{block name='order_products_table'}
	<div class="box hidden-sm-down">
		<h4>
			<i class="svg_fill_gray lh_18">
			<svg class="w_18 h_18" width="18" height="18" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1664 960q-152-236-381-353 61 104 61 225 0 185-131.5 316.5t-316.5 131.5-316.5-131.5-131.5-316.5q0-121 61-225-229 117-381 353 133 205 333.5 326.5t434.5 121.5 434.5-121.5 333.5-326.5zm-720-384q0-20-14-34t-34-14q-125 0-214.5 89.5t-89.5 214.5q0 20 14 34t34 14 34-14 14-34q0-86 61-147t147-61q20 0 34-14t14-34zm848 384q0 34-20 69-140 230-376.5 368.5t-499.5 138.5-499.5-139-376.5-368q-20-35-20-69t20-69q140-229 376.5-368t499.5-139 499.5 139 376.5 368q20 35 20 69z"/></svg>
			</i> {$cart.shopping_cart->cart_name|escape:'html':'UTF-8'} ({l s='Cart ID: %s' sprintf=[$cart.shopping_cart->id_cart] mod='ets_abandonedcart'})</h4>
		<table id="order-products" class="table table-bordered">
			<thead class="thead-default">
			<tr>
				<th>{l s='Product' mod='ets_abandonedcart'}</th>
				<th>{l s='Quantity' mod='ets_abandonedcart'}</th>
				<th>{l s='Unit price' mod='ets_abandonedcart'}</th>
				<th>{l s='Total price' mod='ets_abandonedcart'}</th>
			</tr>
			</thead>
            {foreach from=$cart.products item=product}
				<tr>
					<td>
						<a href="{$product.link nofilter}" title="{$product.name|escape:'html':'UTF-8'}">
							<img src="{$product.image nofilter}" alt="{$product.name|escape:'html':'UTF-8'}" />
							<strong>{$product.name|escape:'html':'UTF-8'}</strong><br/>
                            {if !empty($product.attributes)}{$product.attributes|escape:'html':'UTF-8'}<br/>{/if}
						</a>
					</td>
					<td class="text-xs-center">{$product.cart_quantity|intval}</td>
					<td class="text-xs-right">{$product.price|escape:'html':'UTF-8'}</td>
					<td class="text-xs-right">{$product.total|escape:'html':'UTF-8'}</td>
				</tr>
            {/foreach}
			<tfoot>
			{if !empty($cart.sub_total)}<tr class="text-xs-right line-sub-total">
				<td colspan="3">{l s='Subtotal' mod='ets_abandonedcart'}&nbsp;{if $cart.use_tax}{l s='(tax incl.)'  mod='ets_abandonedcart'}{else}{l s='(tax excl.)'  mod='ets_abandonedcart'}{/if}</td>
				<td>{$cart.sub_total|escape:'html':'UTF-8'}</td>
			</tr>{/if}
            <tr class="text-xs-right line-total-shipping">
				<td colspan="3">{l s='Total shipping' mod='ets_abandonedcart'}</td>
				<td>{if $cart.total_shipping}{$cart.total_shipping|escape:'html':'UTF-8'}{else}{l s='Free' mod='ets_abandonedcart'}{/if}</td>
			</tr>
            {if $cart.total_tax}<tr class="text-xs-right line-total-tax">
				<td colspan="3">{l s='Total tax' mod='ets_abandonedcart'}</td>
				<td>{$cart.total_tax|escape:'html':'UTF-8'}</td>
			</tr>{/if}
            {if !empty($cart.total)}<tr class="text-xs-right line-total">
				<td colspan="3">{l s='Total' mod='ets_abandonedcart'}&nbsp;{if $cart.use_tax}{l s='(tax incl.)'  mod='ets_abandonedcart'}{else}{l s='(tax excl.)'  mod='ets_abandonedcart'}{/if}</td>
				<td>{$cart.total|escape:'html':'UTF-8'}</td>
			</tr>{/if}
			</tfoot>
		</table>
	</div>
	<div class="ets_abancart_actions">
		<a href="javascript:void(0)" class="ets_abancart_cancel btn btn-primary">
			<i class="svg_fill_white svg_fill_hover_white lh_18">
				<svg class="w_18 h_18" width="18" height="18" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1490 1322q0 40-28 68l-136 136q-28 28-68 28t-68-28l-294-294-294 294q-28 28-68 28t-68-28l-136-136q-28-28-28-68t28-68l294-294-294-294q-28-28-28-68t28-68l136-136q28-28 68-28t68 28l294 294 294-294q28-28 68-28t68 28l136 136q28 28 28 68t-28 68l-294 294 294 294q28 28 28 68z"/></svg>
			</i> {l s='Cancel' mod='ets_abandonedcart'}
		</a>
		<a href="{$cart.delete_url nofilter}" class="ets_abancart_delete btn btn-primary" data-confirm="{l s='Do you want to delete this item?' mod='ets_abandonedcart'}">
			<i class="svg_fill_white svg_fill_hover_white lh_18">
				<svg class="w_18 h_18" width="18" height="18" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M704 736v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm128 724v-948h-896v948q0 22 7 40.5t14.5 27 10.5 8.5h832q3 0 10.5-8.5t14.5-27 7-40.5zm-672-1076h448l-48-117q-7-9-17-11h-317q-10 2-17 11zm928 32v64q0 14-9 23t-23 9h-96v948q0 83-47 143.5t-113 60.5h-832q-66 0-113-58.5t-47-141.5v-952h-96q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h309l70-167q15-37 54-63t79-26h320q40 0 79 26t54 63l70 167h309q14 0 23 9t9 23z"/></svg>
			</i> {l s='Delete cart' mod='ets_abandonedcart'}
		</a>
		<a href="{$cart.load_cart_url nofilter}" class="ets_abancart_load_this_cart btn btn-primary" id="submit_load_cart">
			<i class="svg_fill_white svg_fill_hover_white lh_18">
				<svg class="w_18 h_18" width="18" height="18" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M704 1536q0 52-38 90t-90 38-90-38-38-90 38-90 90-38 90 38 38 90zm896 0q0 52-38 90t-90 38-90-38-38-90 38-90 90-38 90 38 38 90zm128-1088v512q0 24-16.5 42.5t-40.5 21.5l-1044 122q13 60 13 70 0 16-24 64h920q26 0 45 19t19 45-19 45-45 19h-1024q-26 0-45-19t-19-45q0-11 8-31.5t16-36 21.5-40 15.5-29.5l-177-823h-204q-26 0-45-19t-19-45 19-45 45-19h256q16 0 28.5 6.5t19.5 15.5 13 24.5 8 26 5.5 29.5 4.5 26h1201q26 0 45 19t19 45z"/></svg>
			</i> {l s='Checkout now' mod='ets_abandonedcart'}
		</a>
	</div>
{/block}

