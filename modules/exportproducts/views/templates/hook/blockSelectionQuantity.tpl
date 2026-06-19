<label class="control-label col-lg-3">
    {l s='Products with quantity' mod='exportproducts'}
</label>
<div class="block_selection_type quantity">
    <label for="selection_type_quantity_1" class="label_selection_type {if isset($quantitySettings['selection_type_quantity']) && $quantitySettings['selection_type_quantity'] && $quantitySettings['selection_type_quantity'] == 1} active{/if}"><</label>
    <input type="radio" name="selection_type_quantity" value="1" id="selection_type_quantity_1" {if isset($quantitySettings['selection_type_quantity']) && $quantitySettings['selection_type_quantity'] && $quantitySettings['selection_type_quantity'] == 1} checked="checked"{/if}>
    <label for="selection_type_quantity_2" class="label_selection_type {if isset($quantitySettings['selection_type_quantity']) && $quantitySettings['selection_type_quantity'] && $quantitySettings['selection_type_quantity'] == 2} active{/if}">></label>
    <input type="radio" name="selection_type_quantity" value="2" id="selection_type_quantity_2" {if isset($quantitySettings['selection_type_quantity']) && $quantitySettings['selection_type_quantity'] && $quantitySettings['selection_type_quantity'] == 2} checked="checked"{/if}>
    <label for="selection_type_quantity_3" class="label_selection_type {if isset($quantitySettings['selection_type_quantity']) && $quantitySettings['selection_type_quantity'] && $quantitySettings['selection_type_quantity'] == 3} active{/if}">=</label>
    <input type="radio" name="selection_type_quantity" value="3" id="selection_type_quantity_3" {if isset($quantitySettings['selection_type_quantity']) && $quantitySettings['selection_type_quantity'] && $quantitySettings['selection_type_quantity'] == 3} checked="checked"{/if}>
</div>
<input type="text" class="selection_quantity style_width_100" name="quantity_value" {if isset($quantitySettings['quantity_value'])}value="{$quantitySettings['quantity_value']|escape:'htmlall':'UTF-8'}" {/if}>
