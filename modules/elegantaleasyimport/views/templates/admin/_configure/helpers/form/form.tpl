{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2020, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}

{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.type == 'el_switch'}
        <div class="radio clearfix" style="margin-bottom: 20px">
            {foreach $input.values as $value}
                <label style="margin-right: 15px">
                    <input type="radio" name="{$input.name|escape:'html':'UTF-8'}"{if $value.value == 1} id="{$input.name|escape:'html':'UTF-8'}_on"{else} id="{$input.name|escape:'html':'UTF-8'}_off"{/if} value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if} style="margin-top: 1px"/>
                    {if $value.value == 1} Yes {else} No {/if}
                </label>
            {/foreach}
        </div>
    {elseif $input.type == 'elegantalpassword'}
        {assign var='value_text' value=$fields_value[$input.name]}
        <input type="password"
               id="{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}{/if}"
               name="{$input.name|escape:'html':'UTF-8'}"
               class="{if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if}"
               value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'html':'UTF-8'}{else}{$value_text|escape:'html':'UTF-8'}{/if}"
               {if isset($input.size)} size="{$input.size|escape:'html':'UTF-8'}"{/if}
               {if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}
               {if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}
               {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
               {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
               {if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
               {if isset($input.required) && $input.required} required="required" {/if}
               {if isset($input.placeholder) && $input.placeholder} placeholder="{$input.placeholder|escape:'html':'UTF-8'}"{/if} />
    {elseif $input.type == 'elegantal_mapping_select'}
        <div class="row">
            <div class="col-xs-6 col-md-4">
                <select name="{$input.name|escape:'html':'UTF-8'}" class="fixed-width-xl" id="{$input.name|escape:'html':'UTF-8'}">
                    {foreach $input.options.query as $option}
                        <option value="{$option[$input.options.id]}" {if $fields_value[$input.name] == $option[$input.options.id]}selected="selected"{/if}>{$option[$input.options.name]}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-xs-6 col-md-5">
                {if $input.name=='id_reference'}
                    <div style="text-align: right">
                        <span style="color:#00aff0">&#9660;</span> 
                        {l s='You can enter custom value that will be applied to all products.' mod='elegantaleasyimport'} 
                        {l s='It will be used if value is missing in import file.' mod='elegantaleasyimport'}
                    </div>
                {else}
                    <input type="text" name="default_{$input.name|escape:'html':'UTF-8'}" value="{$input.map_default_value|escape:'html':'UTF-8'}" placeholder="{if $input.name == 'action_when_out_of_stock'}WHEN OUT OF STOCK{else}{$input.label|escape:'html':'UTF-8'}{/if}{if in_array($input.name, array('delete_existing_discount','discount_tax_included','available_for_order','show_price', 'enabled','on_sale','delete_existing_images','delete_existing_features','customizable','advanced_stock_management','depends_on_stock','delete_product'))}   1 = Yes   0 = No{elseif in_array($input.name, array('categories', 'supplier', 'carriers'))}  Name or ID separated by {$input.multiple_value_separator|escape:'html':'UTF-8'}{elseif in_array($input.name, array('tax_rules_group', 'manufacturer'))}   Name or ID{elseif in_array($input.name, array('action_when_out_of_stock'))}  0 = Deny  1 = Allow  2 = Default{elseif $input.name=='condition'}    new / used / refurbished{elseif in_array($input.name, array('accessories'))}  Reference or ID separated by {$input.multiple_value_separator|escape:'html':'UTF-8'}{elseif in_array($input.name, array('features'))}  Name:Value:Position:Custom{$input.multiple_value_separator|escape:'html':'UTF-8'}...{/if}" title="{l s='You can enter custom value that will be applied to all products.' mod='elegantaleasyimport'} {l s='This value will be used only when you select "Ignore this column" for the mapping or the value is missing in your import file.' mod='elegantaleasyimport'}">
                {/if}
            </div>
        </div>
    {elseif $input.type == 'elegantal_columns_select'}
        <div class="row">
            <div class="col-xs-6 col-md-4">
                <span class="switch prestashop-switch fixed-width-lg">
                    {foreach $input.values as $value}
                        <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if (isset($input.disabled) && $input.disabled) or (isset($value.disabled) && $value.disabled)} disabled="disabled"{/if}/>
                        {strip}
                            <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                {if $value.value == 1}
                                    {l s='Yes' d='Admin.Global'}
                                {else}
                                    {l s='No' d='Admin.Global'}
                                {/if}
                            </label>
                        {/strip}
                    {/foreach}
                    <a class="slide-button btn"></a>
                </span>
            </div>
            <div class="col-xs-6 col-md-5">
                {if $input.name=='product_id'}
                    <div>
                        <span style="color:#00aff0">&#9660;</span> 
                        {l s='You can enter custom value that will override actual data for the corresponding product property.' mod='elegantaleasyimport'}
                    </div>
                {else}
                    <input type="text" name="default_{$input.name|escape:'html':'UTF-8'}" value="{$input.column_override_value|escape:'html':'UTF-8'}" placeholder="{$input.label|escape:'html':'UTF-8'}" title="{l s='Override' mod='elegantaleasyimport'} {$input.label|escape:'html':'UTF-8'}">
                {/if}
            </div>
        </div>
    {elseif $input.type == 'elegantal_categories'}
        {$input.categories_tree}
    {elseif $input.type == 'elegantal_categories_map'}
        {foreach $input.selected_categories_map as $category_map}
            <div class="row elegantal_categories_map">
                <div class="col-xs-6">
                    <select name="categories_map_file[]" title="{l s='Choose File Category' mod='elegantaleasyimport'}">
                        <option value="" style="color:#aaa">{l s='Choose File Category' mod='elegantaleasyimport'}</option>
                        {foreach $input.file_categories as $category_id => $category_name}
                            <option value="{$category_id|escape:'html':'UTF-8'}" title="{$category_id|escape:'html':'UTF-8'}" {if $category_id == $category_map.csv_category}selected="selected"{/if}>
                                {$category_name|escape:'html':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-xs-6">
                    <select name="categories_map_shop[]" title="{l s='Choose Shop Category' mod='elegantaleasyimport'}">
                        <option value="" style="color:#aaa">{l s='Choose Shop Category' mod='elegantaleasyimport'}</option>
                        {foreach $input.shop_categories as $category_id => $category_name}
                            <option value="{$category_id|escape:'html':'UTF-8'}" title="{$category_id|escape:'html':'UTF-8'}" {if $category_id == $category_map.shop_category_id}selected="selected"{/if}>
                                {$category_name|escape:'html':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/foreach}
        <button type="button" class="btn btn-info add_new_category_map">
            <i class="icon-edit"></i>
            {l s='Add new category mapping' mod='elegantaleasyimport'}
        </button>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}