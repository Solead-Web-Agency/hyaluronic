{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Musaffar Patel <musaffar.patel@gmail.com>
*  @copyright  2015-2016 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<strong>{l s='Select a bundle below' mod='productbundlespro'}</strong>

{foreach from=$bundles item=bundle}
	<div class="pbp-bundle" data-id="{$bundle->id_bundle|escape:'htmlall':'UTF-8'}">
		<span class="id-bundle">
			ID: {$bundle->id_bundle|escape:'htmlall':'UTF-8'}
		</span>
		<div class="pbp-products">
			{foreach from=$bundle->products item=bundle_product}
				<span class="pbp-product">
					<b>{$bundle_product->product->name|escape:'htmlall':'UTF-8'}</b>
					{l s='Discount' mod='productbundlespro'} :
					{if $bundle_product->discount_type eq 'money'}
						{displayPrice price=$bundle_product->discount_amount}
					{/if}
					{if $bundle_product->discount_type eq 'percentage'}
						{$bundle_product->discount_amount|escape:'htmlall':'UTF-8'}%
					{/if}
				</span>
			{/foreach}
		</div>
	</div>
{/foreach}
