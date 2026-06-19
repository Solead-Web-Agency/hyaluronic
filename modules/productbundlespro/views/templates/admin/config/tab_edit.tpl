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

<div id="pbp-tab-edit">

	<div id="form-pbp-tab-edit" class="form-wrapper pbp-form-wrapper" style="padding-left: 15px;">
		<h4>{l s='Add / Edit Tab' mod='productbundlespro'}</h4>

		<input name="id_tab" value="{$id_tab|escape:'htmlall':'UTF-8'}" type="hidden" />

		<div class="alert alert-danger" style="display: none"></div>

		<div class="form-group row">
			<div class="col-sm-12">
				<label>{l s='Title' mod='productbundlespro'}</label>

				{foreach from=$languages item=language}
					<div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" style="{if $language.id_lang eq $id_lang_default}display: block;{else}display:none;{/if}">
						<div class="col-lg-7">
							<input name="title_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="title_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control"
								   value="{if !empty($title_localised_array[$language.id_lang])}{$title_localised_array[$language.id_lang]|escape:'html':'UTF-8'}{/if}" />
						</div>

						<div class="col-lg-2">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
								{$language.iso_code|escape:'htmlall':'UTF-8'}
							</button>
							<ul class="dropdown-menu">
								{foreach from=$languages item=language_dropdown}
									<li>
										<a href="javascript:hideOtherLanguage({$language_dropdown.id_lang|escape:'htmlall':'UTF-8'});">{$language_dropdown.name|escape:'htmlall':'UTF-8'}</a>
									</li>
								{/foreach}
							</ul>
						</div>
					</div>
				{/foreach}
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-12">
				<label>{l s='Bundle Layout' mod='productbundlespro'}</label>
			</div>
			<div class="col-sm-12">
				<select name="layout" class="form-control">
                    <option value="1" {if $tab_bundle_layout eq 1}selected{/if}>{l s='Full Width Bundles (Default)' mod="productbundlespro"}</option>
                    <option value="2" {if $tab_bundle_layout eq 2}selected{/if}>{l s='Half width bundles Grid' mod="productbundlespro"}</option>
                </select>
			</div>
		</div>

		<button type="button" id="btn-pbp-edit-save" class="btn btn-primary">{l s='Save' mod='productbundlespro'}</button>
		<button type="button" id="btn-pbp-edit-cancel" class="btn btn-primary-outline">{l s='Cancel' mod='productbundlespro'}</button>
	</div>

</div>