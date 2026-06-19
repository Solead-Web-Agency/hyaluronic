{*
* 2007-2019 Musaffar
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
*  @copyright  2007-2019 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<div id="pbp-bundles-list">

	<h2>{l s='Bundles' mod='productbundlespro'}</h2>

	<table id="pbp-bundles-list-table" class="ui-sortable table">
		<thead>
		<tr class="nodrag nodrop">
			<th><span class="title_box">{l s='ID' mod='productbundlespro'}</span></th>
			<th><span class="title_box">{l s='Tab' mod='productbundlespro'}</span></th>
			<th><span class="title_box">{l s='Products' mod='productbundlespro'}</span></th>
			<th><span class="title_box">{l s='Action' mod='productbundlespro'}</span></th>
			<th><span class="title_box">{l s='Position' mod='productbundlespro'}</span></th>
		</tr>
		</thead>
		<tbody>
			{foreach from=$product_bundles item=bundle}
				<tr class="pbp-bundle" data-id="{$bundle->id_bundle|intval}">
					<td>{$bundle->id_bundle|escape:'htmlall':'UTF-8'}</td>
					<td>{$bundle->tab->title|escape:'htmlall':'UTF-8'}</td>
					<td>{$bundle->products|@count|escape:'htmlall':'UTF-8'}</td>
					<td>
						<a href="#edit" data-id="{$bundle->id_bundle|intval}" class="pbp-bundle-edit"><i class="material-icons">edit</i></a>
						<a href="#delete" data-id="{$bundle->id_bundle|intval}" class="pbp-bundle-delete"><i class="material-icons">delete forever</i></a>
					</td>
					<td>
						<i class="material-icons" style="cursor: pointer">swap_vert</i>
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>

<button type="button" id="pbp-btn-bundles-add" class="btn btn-primary">{l s='Create Bundle' mod='productbundlespro'}</button>
<button type="button" id="pbp-btn-bundles-existing" class="btn btn-primary">{l s='Copy bundle from a product' mod='productbundlespro'}</button>