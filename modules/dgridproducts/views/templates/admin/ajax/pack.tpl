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
*  @author    SeoSA <885588@bk.ru>
*  @copyright 2012-2022 SeoSA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="row">
	<label class="control-label col-xs-12 col-lg-3 product_description">
        {l s='List of products of this pack' mod='dgridproducts'}
	</label>
	<div class="col-xs-12 col-lg-9">
		<p class="alert alert-warning pack-empty-warning" {if $pack_row.pack_items|@count != 0}style="display:none"{/if}>{l s='This pack is empty. You must add at least one product item.'  mod='dgridproducts'}</p>
		<ul id="divPackItems" class="list-unstyled">
            {foreach $pack_row.pack_items as $pack_item}
				<li class="product-pack-item media-product-pack" data-product-name="{$curPackItemName}" data-product-qty="{$pack_item.pack_quantity}" data-product-id="{$pack_item.id}" data-product-id-attribute="{$pack_item.id_product_attribute}">
					<img class="media-product-pack-img" src="{$pack_item.image}"/>
					<span class="media-product-pack-title">{$pack_item.name}</span>
					<span class="media-product-pack-ref">REF: {$pack_item.reference}</span>
					<span class="media-product-pack-quantity"><span class="text-muted">x</span>{$pack_item.pack_quantity}</span>
					<button type="button" class="btn btn-default delPackItem media-product-pack-action" data-delete="{$pack_item.id}" data-delete-attr="{$pack_item.id_product_attribute}"><i class="icon-trash"></i></button>
				</li>
            {/foreach}
		</ul>
	</div>
</div>
<br>
<div class="row">
	<label class="control-label col-xs-12 col-lg-3" for="curPackItemName" title="{l s='Start by typing the first letters of the product name, then select the product from the drop-down list.'  mod='dgridproducts'}">
        {l s='Add product in your pack'  mod='dgridproducts'}
	</label>
	<div class="col-xs-12 col-lg-9">

		<input type="text" id="curPackItemName" name="curPackItemName" class="form-control fixed-width-xxl float-left mr-1" />

		<div class="input-group fixed-width-md float-left mr-1">
			<span class="input-group-addon">&times;</span>
			<input type="number" name="curPackItemQty" id="curPackItemQty" class="form-control" min="1" value="1"/>
		</div>

		<button type="button" id="add_pack_item" class="btn btn-default float-left">
			<i class="icon-plus-sign-alt"></i> {l s='Add this product'  mod='dgridproducts'}
		</button>

	</div>
	<input type="hidden" name="inputPackItems" id="inputPackItems" value="{$pack_row.input_pack_items}" placeholder="inputs"/>
	<input type="hidden" name="namePackItems" id="namePackItems" value="{$pack_row.input_namepack_items}" placeholder="name"/>
</div>
<script>
    var error_heading_msg = '{l s='Error'  mod='dgridproducts' js=1}';
    var msg_select_one = "{l s='Please select at least one product.'  mod='dgridproducts' js=1}";
    var error_continue_msg = '{l s='Continue'  mod='dgridproducts' js=1}';
    Pack_row.onReady();
</script>