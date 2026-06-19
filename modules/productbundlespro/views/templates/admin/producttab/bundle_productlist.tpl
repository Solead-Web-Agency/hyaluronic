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

<table id="productsTable" class="table simpletable">
	<thead>
	<tr class="nodrag nodrop">
		<th><span class="title_box">{l s='ID' mod='productbundlespro'}</span></th>
		<th><span class="title_box">{l s='Name' mod='productbundlespro'}</span></th>
		<th><span class="title_box">{l s='Discount Type' mod='productbundlespro'}</span></th>
		<th><span class="title_box">{l s='Discount' mod='productbundlespro'}</span></th>
		<th><span class="title_box">{l s='Tax' mod='productbundlespro'}</span></th>
		<th><span class="title_box">{l s='Qty' mod='productbundlespro'}</span></th>
		<th><span class="title_box">{l s='Action' mod='productbundlespro'}</span></th>
		<th><span class="title_box">{l s='Position' mod='productbundlespro'}</span></th>
	</tr>
	</thead>

	<tbody>

	{if $products|@count gt 0}
		{foreach from=$products item=product}
			<tr data-id_pbp_product="{$product->id_pbp_product|escape:'htmlall':'UTF-8'}"
				data-id_product="{$product->id_product|escape:'htmlall':'UTF-8'}"
				data-name="{$product->product->name|escape:'htmlall':'UTF-8'}"
				data-discount_type="{$product->discount_type|escape:'htmlall':'UTF-8'}"
				data-discount_amount="{$product->discount_amount|escape:'htmlall':'UTF-8'}"
				data-discount_tax="{$product->discount_tax|escape:'htmlall':'UTF-8'}"
				data-qty="{$product->qty|escape:'htmlall':'UTF-8'}"
				data-allow_oos="{$product->allow_oos|escape:'htmlall':'UTF-8'}"
				>
				<td class="id_product">{$product->id_product|escape:'htmlall':'UTF-8'}</td>
				<td class="name">{$product->product->name|escape:'htmlall':'UTF-8'}</td>
				<td class="discount_type">{$product->discount_type|escape:'htmlall':'UTF-8'}</td>
				<td class="discount_amount">{$product->discount_amount|escape:'htmlall':'UTF-8'}</td>
				<td class="discount_tax">{$product->discount_tax|escape:'htmlall':'UTF-8'}</td>
				<td class="qty">{$product->qty|escape:'htmlall':'UTF-8'}</td>
				<td>
					<a href="#edit"
                       data-id_pbp_product="{$product->id_pbp_product|escape:'htmlall':'UTF-8'}"
                       data-id_product="{$product->id_product|escape:'htmlall':'UTF-8'}" class="pbp-bundle-product-edit"><i class="material-icons">edit</i></a>
					<a href="#delete"
                       data-id_pbp_product="{$product->id_pbp_product|escape:'htmlall':'UTF-8'}"
                       data-id_product="{$product->id_product|escape:'htmlall':'UTF-8'}" class="pbp-bundle-product-delete"><i class="material-icons">delete forever</i></a>
				</td>
				<td>
					<i class="material-icons" style="cursor: pointer">swap_vert</i>
				</td>
			</tr>
		{/foreach}
	{else}
		<tr>
			<td colspan="8">
				<div style="height:100px; padding-top:10px;" class="text-center">
					<i class="icon-warning-sign"></i> {l s='No Products to show' mod='productbundlespro'}
				</div>
			</td>
		</tr>
	{/if}
	<tr class="cloneable hidden" style="cursor:pointer">
		<td class="id_product"></td>
		<td class="name"></td>
		<td class="discount_type"></td>
		<td class="discount_amount"></td>
		<td class="discount_tax"></td>
		<td class="qty"></td>
		<td>
			<a href="#edit" data-id_product="{if !empty($product->id_product)}{$product->id_product|escape:'htmlall':'UTF-8'}{/if}" class="pbp-bundle-product-edit"><i class="material-icons">edit</i></a>
			<a href="#delete" data-id_product="{if !empty($product->id_product)}{$product->id_product|escape:'htmlall':'UTF-8'}{/if}" class="pbp-bundle-product-delete"><i class="material-icons">delete forever</i></a>
		</td>
		<td>
			<i class="material-icons" style="cursor: pointer">swap_vert</i>
		</td>
	</tr>
	</tbody>
</table>