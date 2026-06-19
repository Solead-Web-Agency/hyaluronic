<label class="control-label col-lg-3">
    {l s='Condition' mod='exportproducts'}
</label>
<div class="block_selection_type condition">
    <label for="selection_type_condition_1" class="label_selection_type {if isset($settings) && $settings && in_array("1", $settings)} active {/if}" >{l s='New'  mod='exportproducts'}</label>
    <input type="checkbox" name="selection_type_condition[]" value="1" id="selection_type_condition_1" class="type_condition_checkbox" {if isset($settings) && $settings && in_array("1", $settings)} checked="checked" {/if}>
    <label for="selection_type_condition_2" class="label_selection_type {if isset($settings) && $settings && in_array("2", $settings)} active {/if}">{l s='Used'  mod='exportproducts'}</label>
    <input type="checkbox" name="selection_type_condition[]" value="2" id="selection_type_condition_2" class="type_condition_checkbox" {if isset($settings) && $settings && in_array("2", $settings)} checked="checked" {/if}>
    <label for="selection_type_condition_3" class="label_selection_type {if isset($settings) && $settings && in_array("3", $settings)} active {/if}">{l s='Refurbished'  mod='exportproducts'}</label>
    <input type="checkbox" name="selection_type_condition[]" value="3" id="selection_type_condition_3" class="type_condition_checkbox" {if isset($settings) && $settings && in_array("3", $settings)} checked="checked" {/if}>
</div>