<label class="control-label col-lg-3">
    {l s='Products with pre-tax retail price' mod='exportproducts'}
</label>
<div class="block_selection_type price">
    <label for="selection_type_price_1" class="label_selection_type {if isset($priceSettings['selection_type_price']) && $priceSettings['selection_type_price'] && $priceSettings['selection_type_price'] == 1} active{/if}"><</label>
    <input type="radio" name="selection_type_price" value="1" id="selection_type_price_1" {if isset($priceSettings['selection_type_price']) && $priceSettings['selection_type_price'] && $priceSettings['selection_type_price'] == 1} checked="checked"{/if}>
    <label for="selection_type_price_2" class="label_selection_type {if isset($priceSettings['selection_type_price']) && $priceSettings['selection_type_price'] && $priceSettings['selection_type_price'] == 2} active{/if}">></label>
    <input type="radio" name="selection_type_price" value="2" id="selection_type_price_2"  {if isset($priceSettings['selection_type_price']) && $priceSettings['selection_type_price'] && $priceSettings['selection_type_price'] == 2} checked="checked"{/if}>
    <label for="selection_type_price_3" class="label_selection_type {if isset($priceSettings['selection_type_price']) && $priceSettings['selection_type_price'] && $priceSettings['selection_type_price'] == 3} active{/if}">=</label>
    <input type="radio" name="selection_type_price" value="3" id="selection_type_price_3"  {if isset($priceSettings['selection_type_price']) && $priceSettings['selection_type_price'] && $priceSettings['selection_type_price'] == 3} checked="checked"{/if}>
</div>
<input type="text" class="selection_price style_width_100" name="price_value" {if isset($priceSettings['price_value'])}value="{$priceSettings['price_value']|escape:'htmlall':'UTF-8'}" {/if}>

