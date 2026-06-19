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

<div class="row">
	<div id="pbp-tabs-list" class="col-sm-6">
		<h4>{l s='Bundle Tabs' mod='productbundlespro'}</h4>
		<table class="table">
			<thead>
			<tr>
				<th>{l s='Name' mod='productbundlespro'}</th>
				<th>{l s='Action' mod='productbundlespro'}</th>
			</tr>
			</thead>
			<tbody>
				{foreach from=$tabs item=tab}
					<tr data-id="{$unit.id_ppat_unit|escape:'html':'UTF-8'}">
						<td>{$tab->title|escape:'htmlall':'UTF-8'}</td>
						<td>
							<i class="pbp-tab-edit material-icons" data-id_tab="{$tab->id_tab|escape:'htmlall':'UTF-8'}" style="cursor: pointer;">edit</i>
							<i class="pbp-tab-delete material-icons"
							   data-id_tab="{$tab->id_tab|escape:'htmlall':'UTF-8'}" style="cursor: pointer;">delete</i>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
<button type="button" id="pbp-btn-tab-add" class="btn btn-primary">{l s='Add Tab' mod='productbundlespro'}</button>