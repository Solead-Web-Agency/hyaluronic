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
*  @copyright 2016-2019 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<div id="{$id|escape:'htmlall':'UTF-8'}">
	<div class="input-group">
		<input type="text" name="product_search" value="" class="form-control">
		<span class="input-group-addon">
			<i class="icon-search"></i>
		</span>
	</div>

	<div class="mp-multiselect">
	</div>

	{* Product search results *}
	<div id="product-search-results" style="display: none;">
		<input id="id_product_{$id|escape:'htmlall':'UTF-8'}" name="id_product" type="hidden">

		<table id="search-results-table" class="table">
			<thead>
			<tr class="nodrag nodrop">
				<th style="width:10%"><span class="title_box">{l s='ID' mod='productpricebysize'}</span></th>
				<th style="width:30%"><span class="title_box">{l s='Ref' mod='productpricebysize'}</span></th>
				<th><span class="title_box">{l s='Name' mod='productpricebysize'}</span></th>
			</tr>
			</thead>
			<tbody>
			<tr class="cloneable hidden" style="cursor:pointer">
				<td class="id_product" data-bind="id_product"></td>
				<td class="reference" data-bind="reference"></td>
				<td class="name" data-bind="name"></td>
			</tr>
			</tbody>
		</table>
	</div>
	{* / Product Search Results *}
</div>

<script>
	/*let selected_products = '';

	if ('{$selected_products|@json_encode}' != '') {
		selected_products = {$selected_products|@json_encode};
	}*/
	search_widget = new PBPProductSearchWidget('{$id|escape:'htmlall':'UTF-8'}', {$selected_products|@json_encode});
</script>