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

<div id="form-pbp-bundle-edit" class="form-wrapper pbp-form-wrapper">

	{*<input type="hidden" name="id_product" value="{$id_product|intval}">*}
	<div style="padding: 30px;">
		<h2>{l s='Add / Edit Bundle' mod='productbundlespro'}</h2>
		<div class="alert alert-danger mp-errors" style="display: none"></div>

		<div class="form-group row">
			<div class="col-lg-12">
				{l s='Bundle Enabled?' mod='productbundlespro'}
				<input data-toggle="switch" class="" id="enabled" name="enabled" data-inverse="true" type="checkbox" value="1" {if $bundle->enabled}checked{/if} />
			</div>
		</div>

		<div class="form-group row">
			<div class="col-lg-12">
				{l s='Allow product selection?' mod='productbundlespro'}
				<input data-toggle="switch" class="" id="allow_selection" name="allow_selection" data-inverse="true" type="checkbox" value="1" {if $bundle->allow_selection}checked{/if} />
                <div class="alert alert-info" role="alert" style="margin-top: 8px;">
                    <p class="alert-text">
                        {l s='[1]Disable[/1] to force all products in a bundle to be added to the cart for the discounts to be applied.[2][1]Enable[/1] to allow customer to choose the products in a bundle to add' tags=['<strong>', '<br />'] mod='productbundlespro'}
                    </p>
                </div>
			</div>
		</div>

        <div class="form-group row">
            <div class="col-xs-12 col-md-6">
                {l s='Discount Scope' mod='productbundlespro'}
                <select class="form-control" name="pbp-discount-scope">
                    <option value="product" {if $bundle->discount_enabled eq 0}selected{/if}>{l s='Assign discounts to individual products in the bundle' mod='productbundlespro'}</option>
                    <option value="bundle" {if $bundle->discount_enabled eq 1}selected{/if}>{l s='Apply a global discount for this bundle' mod='productbundlespro'}</option>
                </select>
            </div>

            <div class="col-xs-12 col-md-6">
                <div id="bundle-discount-wrapper" class="{if $bundle->discount_enabled eq 0}pbp-disabled{/if}">
                    {l s='Discount Amount' mod='productbundlespro'}
                    <div class="input-group money-type">
                        <input type="text" id="pbp-discount-amount" name="" class="form-control"
                               value="{$bundle->discount_amount|escape:'htmlall':'UTF-8'}">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="form-group row">
			<div class="col-sm-12">
				<label>{l s='Bundle Name' mod='productbundlespro'}</label>

				{foreach from=$languages item=language}
					<div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" style="{if $language.id_lang eq $id_lang_default}display: block;{else}display:none;{/if}">
						<div class="col-lg-7">
							<input name="pbp_name_{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                   id="title_{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                   class="form-control pbp-name"
								    value="{if !empty($name_localised_array[$language.id_lang])}{$name_localised_array[$language.id_lang]|escape:'html':'UTF-8'}{/if}" />
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
			<div class="col-lg-12">
				{l s='Choose a tab' mod='productbundlespro'}<br>
				<select id="id_tab" name="id_tab" class="form-control">
					{foreach from=$tabs_collection item=tab}
						{if isset($bundle) && $bundle->id_tab eq $tab->id_tab}
							<option value="{$tab->id_tab|escape:'htmlall':'UTF-8'}" selected>{$tab->title|escape:'htmlall':'UTF-8'}</option>
						{else}
							<option value="{$tab->id_tab|escape:'htmlall':'UTF-8'}">{$tab->title|escape:'htmlall':'UTF-8'}</option>
						{/if}
					{/foreach}
				</select>
			</div>
		</div>



		<h3>{l s='Products in bundle' mod='productbundlespro'}</h3>

        <div id="parent-product-discount" style="border-bottom: 1px solid #ccc; margin-bottom: 10px; {if $bundle->discount_enabled eq 1}display: none;{/if}">
            <strong>{l s='Parent product discount' mod='productbundlespro'}</strong>
            <div class="form-group row">
                <div class="col-lg-6">
                    <input id="parent_product_discount_amount" class="form-control" placeholder="{l s='Discount amount' mod='productbundlespro'}" type="number" value="{$bundle->parent_product_discount_amount|escape:'htmlall':'UTF-8'}" />
                </div>
                <div class="col-lg-6">
                    <select id="parent_product_discount_type" class="form-control">
                        <option value="percentage" {if $bundle->parent_product_discount_type eq 'percentage'}selected{/if}>{l s='Percentage' mod='productbundlespro'}</option>
                        <option value="amount" {if $bundle->parent_product_discount_type eq 'amount'}selected{/if}>{l s='Amount' mod='productbundlespro'}</option>
                    </select>
                </div>
            </div>
        </div>

		<button type="button" id="pbp-btn-bundle-addproduct" class="btn btn-primary-outline" style="margin-bottom: 10px;">{l s='Add Product' mod='productbundlespro'}</button>

		<div id="pbp-bundle-products" style="height: 220px; overflow-y: auto; overflow-x: hidden"></div>

		<div class="panel-footer">
			<a id="pbp-popup-close" href="#close" class="btn btn-primary"><i class="process-icon-cancel"></i> Cancel</a>
			<button type="submit" id="pbp-popup-bundle-save" class="btn btn-primary pull-right">
				<i class="process-icon-save"></i> {l s='Save' mod='productbundlespro'}
			</button>
		</div>

	</div>
</div>



{* Start : Panel2 Add Product *}
<div id="panel2" class="panel subpanel pbp-form-wrapper" style="padding: 20px;">

	<h2>{l s='Add / Edit Product in bundle' mod='productbundlespro'}</h2>

	<input type="hidden" name="id_pbp_product" id="id_pbp_product" value="" />
    <input type="hidden" name="id_product" id="id_product" value=""/>

	<div class="form-group row">
		<div class="col-lg-12">
			{l s='Display when out of stock?' mod='productbundlespro'}
			<input data-toggle="switch" class="" id="allow_oos" name="allow_oos" data-inverse="true" type="checkbox" value="1" />
		</div>
	</div>


	<div style="padding: 20px;">
		<div class="form-group row">
			<div class="col-lg-12">
				<label class="control-label">
					{l s='Product Name' mod='productbundlespro'}
				</label>
                {$product_search nofilter}
			</div>
		</div>

		<div class="form-group row form-group-discount" style="padding: 15px; margin-bottom: 0px;">
			<label class="control-label col-lg-2">
				<span class="label-tooltip" title="">{l s='Apply Discount of' mod='productbundlespro'}</span>
			</label>

			<div class="col-lg-10">
				<input id="discount_amount" type="text" name="discount_amount" value="" class="form-control">

				<select name="discount_type" id="discount_type" class="form-control">
					<option value="percentage">%</option>
					<option value="money">{l s='Decimal/Money' mod='productbundlespro'}</option>
				</select>

				{*
				<select name="discount_tax" id="discount_tax" class="form-control">
					<option value="tax_inc">{l s='Tax inc.' mod='productbundlespro'}</option>
					<option value="tax_ex">{l s='Tax ex.' mod='productbundlespro'}</option>
				</select>
				*}
			</div>
		</div>

		<div class="form-group row form-group-qty" style="padding: 15px">
			<label class="control-label col-lg-2">
				<span class="label-tooltip" title="">{l s='Quantity' mod='productbundlespro'}</span>
			</label>
			<div class="col-lg-10">
				<input id="qty" type="number" name="qty" value="1" min="1" class="form-control">
			</div>
		</div>

	</div>

	<div class="panel-footer">
		<a href="#close" id="pbp-btn-bundle-addproduct-cancel" class="btn btn-primary-outline">{l s='Cancel' mod='productbundlespro'}</a>
		<button type="submit" id="pbp-btn-bundle-addproduct-done" class="btn btn-primary-outline pull-right">
			{l s='Done' mod='productbundlespro'}
		</button>
	</div>

</div>
{* End: Panel2 Add Product *}

<script>
	$(document).ready(function () {
		prestaShopUiKit.init();
	});
</script>