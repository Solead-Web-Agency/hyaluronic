{*
* 2007-2021 ETS-Soft
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
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="card mb-0 ets-seo-advanced">
    <div class="card-header">
        <h3 class="card-title">{l s='Advanced settings' mod='ets_seo'}</h3>
    </div>
    <div class="box-seo-inner" id="box-seo-advanced">
        {foreach $seo_advanced as $k=>$field}
            <div class="form-group">
                <label>{$field.label|escape:'html':'UTF-8'}</label>
                <div class="input-group locale-input-group d-flex">
                    {foreach from=$ets_seo_languages item='lang'}
                        <div class="input-lang multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                            {if $lang.id_lang != $current_lang.id}
                                hide
                            {/if}">
                            {if $field.type == 'input_text'}
                                <input type="text"
                                       name="ets_seo_advanced[{$k|escape:'html':'UTF-8'}][{$lang.id_lang|escape:'html':'UTF-8'}]"
                                       class="form-control"
                                       id="{$field.id|cat:'-'|cat:$lang.id_lang|escape:'html':'UTF-8'}"
                                       value="{if isset($field.value[$lang.id_lang])}{$field.value[$lang.id_lang]|escape:'html':'UTF-8'}{/if}">
                            {elseif $field.type == 'select'}
                                <select class="form-control"
                                        data-value="{if isset($field.config_value)}{$field.config_value|escape:'html':'UTF-8'}{/if}"
                                        name="ets_seo_advanced[{$k|escape:'html':'UTF-8'}][{$lang.id_lang|escape:'html':'UTF-8'}]"
                                        id="{$field.id|cat:'-'|cat:$lang.id_lang|escape:'html':'UTF-8'}">
                                    {foreach $field.options as $option}
                                        <option value="{$option.value|escape:'html':'UTF-8'}"
                                                {if isset($field.selected[$lang.id_lang]) && $field.selected[$lang.id_lang] == $option.value}
                                            selected="selected"
                                                {elseif isset($option.default_option)}
                                            selected="selected"
                                                {/if}>
                                            {$option.label|escape:'html':'UTF-8'} {if isset($option.suffix_label)}{$option.suffix_label|escape:'html':'UTF-8'}{/if}
                                        </option>
                                    {/foreach}
                                </select>
                            {elseif $field.type == 'radio'}
                                {foreach $field.options as $r=>$option}
                                    <div class="custom-control custom-radio">
                                        <input type="radio"
                                                {if isset($option.id)} id="{$option.id|cat:'-'|cat:$lang.id_lang|escape:'html':'UTF-8'}" {/if}
                                                {if isset($field.checked[$lang.id_lang]) && $option.value == $field.checked[$lang.id_lang]} checked="checked"
                                                {elseif !isset($field.checked[$lang.id_lang]) && $r==0}checked="checked" {/if}
                                               name="ets_seo_advanced[{$k|escape:'html':'UTF-8'}][{$lang.id_lang|escape:'html':'UTF-8'}]"
                                               class="custom-control-input"
                                               value="{$option.value|escape:'html':'UTF-8'}">
                                        <label class="custom-control-label"
                                               {if isset($option.id)}for="{$option.id|cat:'-'|cat:$lang.id_lang|escape:'html':'UTF-8'}"{/if}>
                                            {$option.label|escape:'html':'UTF-8'}
                                        </label>
                                    </div>
                                {/foreach}
                            {elseif $field.type == 'textarea'}
                                <textarea class="form-control"
                                          name="ets_seo_advanced[{$k|escape:'html':'UTF-8'}][{$lang.id_lang|escape:'html':'UTF-8'}]"
                                          id="{$field.id|cat:'-'|cat:$lang.id_lang|escape:'html':'UTF-8'}">
                                    {if isset($field.value[$lang.id_lang])}{$field.value[$lang.id_lang]|escape:'html':'UTF-8'}{/if}
                                </textarea>
                            {elseif $field.type == 'select2'}
                                {assign 'ets_seo_select2_value' []}
                                {if isset($field.selected[$lang.id_lang])}
                                    {assign 'ets_seo_select2_value' explode(',', $field.selected[$lang.id_lang])}
                                {/if}
                                <select class="form-control js-ets-seo-select2 ets_seo_advanced_select2"
                                        multiple="multiple">
                                    {foreach $field.options as $option}
                                        <option value="{$option.value|escape:'html':'UTF-8'}"
                                                {if in_array($option.value, $ets_seo_select2_value) }
                                            selected="selected"
                                                {elseif $option.value == ''}
                                            selected="selected"
                                                {/if}>{$option.label|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                                <input type="hidden"
                                       name="ets_seo_advanced[{$k|escape:'html':'UTF-8'}][{$lang.id_lang|escape:'html':'UTF-8'}]"
                                       class="ets-seo-select2-value"
                                       value="{if isset($field.selected[$lang.id_lang])}{$field.selected[$lang.id_lang|escape:'html':'UTF-8']}{/if}">
                            {/if}
                            {if isset($field.desc) }
                                <p class="help-block">{$field.desc|escape:'html':'UTF-8'}</p>
                            {elseif $k == 'allow_search'}
                                {if isset($show_readability) && $show_readability}
                                    <p class="help-block">{l s='Change default behavior in' mod='ets_seo'} <a
                                                href="{if isset($field.link_default)}{$field.link_default|escape:'quotes'}{else}#{/if}">{l s='Meta templates' mod='ets_seo'}</a>
                                    </p>
                                {/if}
                            {/if}
                        </div>
                        {if !$is_new_theme && count($ets_seo_languages) > 1}
                            {include file="./_btn_multilang.tpl"}
                        {/if}
                    {/foreach}
                    {if $is_new_theme && count($ets_seo_languages) > 1}
                        {include file="./_btn_multilang.tpl"}
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
</div>