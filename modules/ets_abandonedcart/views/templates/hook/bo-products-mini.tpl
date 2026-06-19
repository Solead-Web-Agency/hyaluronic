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
{if isset($products) && $products}
	<div class="ets_abancart_product_list_table">
		<table class="ets_abancart_products-mini" style="width: 100%;border: 1px solid #ddd;">
			<tbody>
			{foreach from=$products item='product'}
				<tr class="ets_abancart_products_mini_item" style="border-bottom: 1px solid #ddd;">
					<td class="ets_abancart_product_item" style="padding:5px;font-weight: normal;min-width: 40px;;max-width: 80px;text-align: left;width: 15%;">
						<a href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|truncate:80:'...':true|escape:'html':'UTF-8'}" style="text-decoration: none;">
							<img class="ets_abancart_product_image" style="width:100%;display:block;min-width:60px;" src="{$product.image|escape:'quotes':'UTF-8'}" alt="{$product.name|truncate:20:'...':true|escape:'html':'UTF-8'}"/>
						</a>
					</td>
					<td style="text-align:left;padding:5px;">
						<div class="product-info">
                            <div class="product-line-info">
                                <a href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|truncate:80:'...':true|escape:'html':'UTF-8'}" style="text-decoration: none;line-height:1.6;display:block;margin-bottom:5px;">
    								<span class="product_name" style="line-height:1.6;display:block;">{$product.name|truncate:80:'...':true|escape:'html':'UTF-8'}</span>
                                </a>
                            </div>
							{if isset($product.attributes) && $product.attributes}
								{assign var='ik2' value=0}
								<div class="product_combination" style="font-size:11px;">
                                    {foreach from=$product.attributes item='attribute'}
                                        {assign var='ik2' value=$ik2+1}
                                        {$attribute.group_name|truncate:80:'...':true|escape:'html':'UTF-8'}-{$attribute.attribute_name|truncate:80:'...':true|escape:'html':'UTF-8'}
                                        {if $ik2 < count($product.attributes)}, {/if}
                                    {/foreach}
                                </div>
							{/if}
						</div>
                        <div class="product-line-info product-price has-discount" style="display:block;padding-right:10px;">
                            <span class="p_price" style="display:inline-block;margin-right: 12px;">
        						<span class="price">{$product.price|escape:'html':'UTF-8'}</span>
        						{if !empty($product.old_price)}
                                    <span class="regular-price" style="text-decoration: line-through;color: #999;">
                                        {$product.old_price|escape:'html':'UTF-8'}
                                    </span>
                                {/if}
        					</span>
                            <span class="product-qty-item" style="margin-right: 12px;color: #999;display:inline-block;">
                                x <span class="cart-quantity" data-title="Qty">{$product.cart_quantity|intval}</span>
                            </span>
                            <span class="product-total-item" style="color: #2fb5d2;font-weight: 600;float:right;">
                                {$product.product_total|escape:'html':'UTF-8'}
                            </span>
                        </div>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}