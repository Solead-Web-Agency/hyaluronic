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

<div class="row">
	<div id="pbp-product-tabs" class="col-xs-12 {$pbp_general.pbp_location|escape:'htmlall':'UTF-8'} compact">
		{if $tabs_collection|@count gt 1}
			<div class="tab-links">
				{$i = 1}
				{foreach from=$tabs_collection item=tab}
					<a data-tab_id="{$tab->id|escape:'htmlall':'UTF-8'}" class="{if $i==1}active{/if}">{$tab->title|escape:'htmlall':'UTF-8'}</a>
					{$i = $i+1}
				{/foreach}
			</div>
		{/if}

		{$counter = 1}
		{foreach from=$tabs_collection item=tab}
			<div class="tab-content-wrapper">
				<div class="tab-content tab-content-{$tab->id|escape:'htmlall':'UTF-8'}" style="{if $counter gt 1}display:none;{/if}">
					{foreach from=$tab->bundles item=bundle}
						<div class="bundle col-xs-12 col-sm-12 col-md-12" data-id_bundle="{$bundle->id_bundle|escape:'htmlall':'UTF-8'}">

							<div class="content">
								<span class="bundle-title">
									<div>
										{l s='Buy it with ' mod='productbundlespro'} <span class="product-title">{$bundle->products[0]->product->name|escape:'htmlall':'UTF-8'}</span>
									</div>
									{*{$parent_product->name[$id_lang]}*}
									{if !empty($parent_product->attribute_groups) > 0}
										<select class="parent_product_ipa" style="margin-top: 10px; margin-bottom: 5px; font-weight: normal;">
											{foreach from=$parent_product->attribute_groups item=attribute_group name=attribute_group}
												<option value="{$attribute_group.id_product_attribute|escape:'htmlall':'UTF-8'}"
														{if !empty($attribute_group.default)}selected="selected"{/if}>
													{$attribute_group.label|escape:'htmlall':'UTF-8'}
												</option>
											{/foreach}
										</select>
									{/if}
								</span>

								<div class="parent">
										<img itemprop="image" src="{$parent_product->url_image|escape:'html':'UTF-8'}" />
								</div>

								<div class="bundle-products">
									{if !empty($bundle->products)}
										{foreach from=$bundle->products item=bundle_product}
											<div class="bundle-product" data-id_product="{$bundle_product->id_product|escape:'htmlall':'UTF-8'}">
												<div class="image">
													<a href="{$bundle_product->url|escape:'htmlall':'UTF-8'}">
														<img src="{$bundle_product->url_image|escape:'html':'UTF-8'}" class="bundle_product_thumb"/>
													</a>
												</div>

												<div class="info">
													{if $bundle_product->discount_type eq 'percentage'}
														<span class="pbp-discount">
															{l s='SAVE:' mod='productbundlespro'}
															<span class="pbp-discount-amount">{$bundle_product->product->discount_saving|escape:'html':'UTF-8'} ({$bundle_product->discount_amount|string_format:"%.0f"}%)</span>
														</span>
														<span class="pbp-discount">
															{l s='Offer Price:' mod='productbundlespro'}
															<span class="pbp-offer-price">{$bundle_product->product->discount_price|escape:'html':'UTF-8'}</span>
														</span>
													{else}
														<span class="pbp-discount">
															{l s='SAVE:' mod='productbundlespro'}
															<span class="pbp-discount-amount">{$bundle_product->product->discount_saving|escape:'html':'UTF-8'}</span>
														</span>
														<span class="pbp-discount">
															{l s='Offer Price:' mod='productbundlespro'}
															<span class="pbp-offer-price">{$bundle_product->product->discount_price|escape:'html':'UTF-8'}</span>
														</span>
													{/if}
													{if count($bundle_product->attribute_groups) > 0}
														<select class="bundle_product_ipa">
															{foreach from=$bundle_product->attribute_groups item=attribute_group name=attribute_group}
																<option value="{$attribute_group.id_product_attribute|escape:'htmlall':'UTF-8'}">{$attribute_group.label|escape:'htmlall':'UTF-8'}</option>
															{/foreach}
														</select>
													{/if}
												</div>
											</div>
										{/foreach}
									{/if}
								</div>

								<div class="box-info-product">
									{if $pbp_general.pbp_display_bundle_total eq 1}
										<div class="bundle-total">
											{$bundle->bundle_price_discounted|escape:'html':'UTF-8'}
										</div>
									{/if}
									<button type="submit" name="Submit" class="btn_add_bundle_cart btn btn-primary">
										{l s='Add to cart' mod='productbundlespro'}
									</button>
								</div>
							</div>
						</div>
					{/foreach}
				</div>
			</div>
			{$counter = $counter+1}
		{/foreach}
	</div>
</div>

<script>
	id_product = {$id_product|escape:'html':'UTF-8'};
</script>