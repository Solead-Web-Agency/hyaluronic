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

<div class="{$location|escape:'htmlall':'UTF-8'} pbp-widget">

    <div class="title">
        {l s='Buy it as a bundle and save...' mod='productbundlespro'}
    </div>

    <div id="pbp-product-tabs" class="{$pbp_general.pbp_location|escape:'htmlall':'UTF-8'}">
        {if $tabs_collection|@count gt 1}
            <div class="tab-links">
                {$i = 1}
                {foreach from=$tabs_collection item=tab}
                    <a data-tab_id="{$tab->id|escape:'htmlall':'UTF-8'}"
                       class="{if $i==1}active{/if}">{$tab->title|escape:'htmlall':'UTF-8'}</a>
                    {$i = $i+1}
                {/foreach}
            </div>
        {/if}

        {$counter = 1}
        {foreach from=$tabs_collection item=tab}
            <div class="{$tab->layout|escape:'htmlall':'UTF-8'} tab-content-wrapper {if $tabs_collection|@count gt 1}has_tabs{/if}">
                <div id="tab-content-{$tab->id|escape:'htmlall':'UTF-8'}" class="tab-content tab-content-{$tab->id|escape:'htmlall':'UTF-8'} {if $pbp_general.pbp_slider eq 1}splide{/if}" style="{if $counter gt 1}display:none;{/if}">
                    <div class="{if $pbp_general.pbp_slider eq 1}splide__track{/if}">
                        <div class="{if $pbp_general.pbp_slider eq 1}splide__list{/if}">
                            {foreach from=$tab->bundles item=bundle}
                                <div class="bundle {if $pbp_general.pbp_slider eq 1}splide__slide{/if}" data-id_bundle="{$bundle->id_bundle|escape:'htmlall':'UTF-8'}">
                                    <div class="bundle-inner">
                                        <div class="products">
                                            <div class="product parent-product">
                                                <div class="image">
                                                    {if ($bundle->discount_enabled eq 1 && $bundle->parent_product_discount->discount_saving gt 0) || ($bundle->parent_product_discount_amount > 0)}
                                                        <div class="saving-label-child">
                                                            {l s='SAVE:' mod='productbundlespro'}
                                                            <span class="pbp-discount-amount parent-product-discount-saving">
                                                                {$bundle->parent_product_discount->discount_saving_formatted|escape:'html':'UTF-8'}
                                                                {if $bundle->parent_product_discount->discount_type eq 'percentage'}
                                                                    ({$bundle->parent_product_discount->discount_amount|string_format:"%.0f"}%)
                                                                {else}
                                                                    ({$bundle->parent_product_discount->discount_amount})
                                                                {/if}
                                                            </span>
                                                        </div>
                                                    {/if}
                                                    <img itemprop="image" src="{$parent_product->url_image|escape:'html':'UTF-8'}"/>
                                                </div>
                                                <div class="name">
                                                    {$parent_product->name|escape:'htmlall':'UTF-8'}
                                                </div>
                                                <div class="options">
                                                    {if !empty($parent_product->attribute_groups) > 0}
                                                        <select class="parent_product_ipa">
                                                            {foreach from=$parent_product->attribute_groups item=attribute_group name=attribute_group}
                                                                <option value="{$attribute_group.id_product_attribute|escape:'htmlall':'UTF-8'}"
                                                                        {if $attribute_group.default eq 1}selected="selected"{/if}>
                                                                    {$attribute_group.label|escape:'htmlall':'UTF-8'}
                                                                </option>
                                                            {/foreach}
                                                        </select>
                                                    {/if}
                                                </div>
                                                {if $bundle->discount_enabled eq 1 || $bundle->parent_product_discount_amount > 0}
                                                    <div class="prices">
                                                        {if $parent_product->discount_saving gt 0}
                                                            <span class="parent-product-price-original pbp-original-price">
                                                                {$bundle->parent_product_discount->original_price_formatted|escape:'html':'UTF-8'}
                                                            </span>
                                                        {/if}
                                                        <span class="parent-product-price-discounted" style="font-weight: bold;">
                                                            {$bundle->parent_product_discount->discounted_price_formatted|escape:'html':'UTF-8'}
                                                        </span>
                                                    </div>
                                                {/if}
                                            </div>

                                            {if !empty($bundle->products)}
                                                {foreach from=$bundle->products item=bundle_product}
                                                    <div class="product child-product"
                                                         data-id_pbp_product="{$bundle_product->id_pbp_product|escape:'htmlall':'UTF-8'}"
                                                         data-id_unique="{$bundle_product->id_unique|escape:'htmlall':'UTF-8'}"
                                                         data-id_product="{$bundle_product->id_product|escape:'htmlall':'UTF-8'}"
                                                         data-discount_price="{$bundle_product->product->discount_price|escape:'html':'UTF-8'}"
                                                         data-original-price="{$bundle_product->product->original_price|escape:'html':'UTF-8'}"
                                                    >
                                                        <div class="image">
                                                            {if $bundle_product->product->discount_saving gt 0}
                                                                {if $bundle->discount_enabled eq 1}
                                                                    <div class="saving-label-child">
                                                                        {l s='SAVE:' mod='productbundlespro'}
                                                                        <span class="pbp-discount-amount">{$bundle_product->product->discount_saving_formatted|escape:'html':'UTF-8'}</span>
                                                                    </div>
                                                                {else}
                                                                    {if $bundle_product->discount_type eq 'percentage'}
                                                                        <div class="saving-label-child">
                                                                            {l s='SAVE:' mod='productbundlespro'}
                                                                            <span class="pbp-discount-amount">{$bundle_product->product->discount_saving_formatted|escape:'html':'UTF-8'} ({$bundle_product->discount_amount|string_format:"%.0f"}%)</span>
                                                                        </div>
                                                                    {else}
                                                                        <div class="saving-label-child">
                                                                            {l s='SAVE:' mod='productbundlespro'}
                                                                            <span class="pbp-discount-amount">{$bundle_product->product->discount_saving_formatted|escape:'html':'UTF-8'}</span>
                                                                        </div>
                                                                    {/if}
                                                                {/if}
                                                            {/if}
                                                            <a href="{$bundle_product->url|escape:'htmlall':'UTF-8'}">
                                                                <img itemprop="image" src="{$bundle_product->url_image|escape:'html':'UTF-8'}"/>
                                                            </a>
                                                        </div>
                                                        <div class="name">
                                                            {if $bundle->allow_selection eq 1}
                                                                <label class="container-checkbox">
                                                                    <input value="1" name="bundle_products" type="checkbox"
                                                                           checked="checked">
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                            {/if}
                                                            {if $bundle_product->qty gt 1}
                                                                {$bundle_product->qty|escape:'htmlall':'UTF-8'} x
                                                            {/if}
                                                            {$bundle_product->name|escape:'htmlall':'UTF-8'}
                                                        </div>
                                                        <div class="options">
                                                            {if count($bundle_product->attribute_groups) > 0}
                                                                <select class="bundle_product_ipa">
                                                                    {foreach from=$bundle_product->attribute_groups item=attribute_group name=attribute_group}
                                                                        <option value="{$attribute_group.id_product_attribute|escape:'htmlall':'UTF-8'}"
                                                                                {if $attribute_group.default eq 1}selected="selected"{/if}>
                                                                            {$attribute_group.label|escape:'htmlall':'UTF-8'}
                                                                        </option>
                                                                    {/foreach}
                                                                </select>
                                                            {/if}
                                                        </div>
                                                        <div class="prices">
                                                            {if $bundle_product->discount_type eq 'percentage'}
                                                                <span class="pbp-discount">
                                                                    {if $bundle_product->product->discount_saving gt 0}
                                                                        <span class="pbp-original-price">{$bundle_product->product->original_price_formatted|escape:'html':'UTF-8'}</span>
                                                                    {/if}
                                                                    <span class="pbp-offer-price" style="font-weight: bold;">{$bundle_product->product->discount_price_formatted|escape:'html':'UTF-8'}</span>
                                                                </span>
                                                            {else}
                                                                <span class="pbp-discount">
                                                                    {if $bundle_product->product->discount_saving gt 0}
                                                                        <span class="pbp-original-price">{$bundle_product->product->original_price_formatted|escape:'html':'UTF-8'}</span>
                                                                    {/if}
                                                                    <span class="pbp-offer-price" style="font-weight: bold;">{$bundle_product->product->discount_price_formatted|escape:'html':'UTF-8'}</span>
                                                                </span>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                {/foreach}
                                            {/if}
                                        </div>

                                        <div class="bundle-action">
                                            {if $bundle->bundle_price_saving_unformatted gt 0}
                                                {l s='buy as a bundle and save' mod='productbundlespro'}
                                                {if $bundle->discount_enabled}
                                                    {if $bundle->discount_type eq 'percentage'}
                                                        <strong>{$bundle->discount_amount}%</strong>
                                                    {/if}
                                                {/if}
                                            {else}
                                                {l s='buy as a bundle' mod='productbundlespro'}
                                            {/if}
                                            <div class="bundle-prices">
                                                {if $bundle->bundle_price_saving_unformatted gt 0}
                                                    <span class="pbp_bundle_original_total" style="text-decoration: line-through">
                                                        {$bundle->bundle_price_original_formatted|escape:'html':'UTF-8'}
                                                    </span>
                                                {/if}
                                                <span class="pbp_bundle_total">
                                                    {$bundle->bundle_price_discounted|escape:'html':'UTF-8'}
                                                </span>
                                            </div>

                                            {if $bundle->bundle_price_saving_unformatted gt 0}
                                                <div class="saving-label">
                                                    {l s='SAVE' mod='productbundlespro'}
                                                    <span class="pbp_bundle_saving_total">{$bundle->bundle_price_saving|escape:'html':'UTF-8'}</span>
                                                </div>
                                            {/if}

                                            {if $pbp_general.pbp_show_bundle_quantity eq 1}
                                                <div class="pbp_add_quantity_wrapper">
                                                    <div class="pbp-quantity-wrapper">
                                                        <input type="number" value="1" name="pbp_quantity" placeholder="" style="width: 60px">
                                                        <script>
                                                            $("input[name='pbp_quantity']").TouchSpin({
                                                                verticalbuttons: true,
                                                                verticalupclass: 'material-icons touchspin-up',
                                                                verticaldownclass: 'material-icons touchspin-down',
                                                            });
                                                        </script>
                                                    </div>
                                                    <button type="submit" name="Submit"
                                                            class="btn_add_bundle_cart btn btn-primary add-to-cart"
                                                            style="padding: 12px;">
                                                        <span>
                                                            {l s='Add to cart' mod='productbundlespro'}
                                                        </span>
                                                    </button>
                                                </div>
                                            {else}
                                                <button type="submit" name="Submit"
                                                        class="btn_add_bundle_cart btn btn-primary add-to-cart"
                                                        style="padding: 12px;">
                                                <span>
                                                    {l s='Add to cart' mod='productbundlespro'}
                                                </span>
                                                </button>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>

                    {if $tab->bundles|@count gt 1}
                        <span class="btn-show-more-bundles">
                            {l s='Show more Bundles' mod='productbundlespro'}
                        </span>
                    {/if}
                </div>
            </div>
            {$counter = $counter+1}
        {/foreach}
    </div>
</div>


<script>
    id_product = {$id_product|escape:'html':'UTF-8'};
    {if $pbp_general.pbp_slider eq 1}
        /*new Splide('.splide', {
        }).mount();*/
    {/if}
</script>
