<label class="control-label col-lg-3">
    {l s='Visibility' mod='exportproducts'}
</label>
<div class="block_selection_type visibility">
    <label for="selection_type_visibility_1" class="label_selection_type {if isset($settings) && $settings && in_array("1", $settings)} active {/if}">{l s='Everywhere'  mod='exportproducts'}</label>
    <input type="checkbox" name="selection_type_visibility[]" value="1" id="selection_type_visibility_1" class="type_visibility_checkbox" {if isset($settings) && $settings && in_array("1", $settings)} checked="checked" {/if}>
    <label for="selection_type_visibility_2" class="label_selection_type {if isset($settings) && $settings && in_array("2", $settings)} active {/if}">{l s='Catalog only'  mod='exportproducts'}</label>
    <input type="checkbox" name="selection_type_visibility[]" value="2" id="selection_type_visibility_2" class="type_visibility_checkbox" {if isset($settings) && $settings && in_array("2", $settings)} checked="checked" {/if}>
    <label for="selection_type_visibility_3" class="label_selection_type {if isset($settings) && $settings && in_array("3", $settings)} active {/if}">{l s='Search only'  mod='exportproducts'}</label>
    <input type="checkbox" name="selection_type_visibility[]" value="3" id="selection_type_visibility_3" class="type_visibility_checkbox" {if isset($settings) && $settings && in_array("3", $settings)} checked="checked" {/if}>
    <label for="selection_type_visibility_4" class="label_selection_type {if isset($settings) && $settings && in_array("4", $settings)} active {/if}">{l s='Nowhere'  mod='exportproducts'}</label>
    <input type="checkbox" name="selection_type_visibility[]" value="4" id="selection_type_visibility_4" class="type_visibility_checkbox" {if isset($settings) && $settings && in_array("4", $settings)} checked="checked" {/if}>
</div>