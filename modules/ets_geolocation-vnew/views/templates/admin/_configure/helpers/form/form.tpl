{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}

{extends file="helpers/form/form.tpl"}
{block name="input"}
    {if $input.type == 'switch'}
    	<span class="switch prestashop-switch fixed-width-lg">
    		{foreach $input.values as $value}
    		<input type="radio" name="{$input.name|escape:'html':'UTF-8'}"{if $value.value == 1} id="{$input.name|escape:'html':'UTF-8'}_on"{else} id="{$input.name|escape:'html':'UTF-8'}_off"{/if} value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
    		{strip}
    		<label {if $value.value == 1} for="{$input.name|escape:'html':'UTF-8'}_on"{else} for="{$input.name|escape:'html':'UTF-8'}_off"{/if}>
    			{$value.label|escape:'html':'UTF-8'}
    		</label>
    		{/strip}
    		{/foreach}
    		<a class="slide-button btn"></a>
    	</span>
    {elseif $input.type == 'text_search_prd'}
      <input class="ets_geo_search_ids" id="{$input.name|escape:'html':'UTF-8'}_SEARCH" data-target="{$input.name|escape:'html':'UTF-8'}" type="text"
             autocomplete="off" class="form-control"
             placeholder="{l s='Search by name, reference and ID' mod='ets_geolocation'}" value="">
      <input class="ets_geo_ids" type="hidden" name="{$input.name|escape:'html':'UTF-8'}"
             value="{$input.values|escape:'html':'UTF-8'}"/>
      <p class="help-block">{$input.desc|escape:'html':'UTF-8'}</p>
      <ul class="egl_products_added">
          {Module::getInstanceByName('ets_geolocation')->hookDisplayGeoRuleHiddenProductList(['rule' => $geo_rule]) nofilter}
      </ul>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name="description"}
    {if $input.type === 'text_search_prd'}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name="field"}
    {if $input.type == 'geo_countries'}
        <div class="well margin-form wrap_country">
            {if $input.options.query}
            <table class="table" style="border-spacing : 0; border-collapse : collapse;">
                    <thead>
                        <tr>{if isset($fields_value['all_countries'])}{assign var="all_countries" value=$fields_value['all_countries']|intval}{else}{assign var="all_countries" value=0}{/if}
                            <th><input type="checkbox" name="all_countries" value="1"{if $all_countries} checked="checked"{/if} onclick="checkDelBoxes(this.form, 'countries[]', this.checked)" /></th>
                            <th>{l s='All' mod='ets_geolocation'}</th>
                        </tr>
                    </thead>
                    <tbody>
                    {assign var="id_option" value=$input.options.id}
                    {foreach $input.options.query as $option}
                        <tr>
                            <td><input type="checkbox" name="countries[]" id="item{$option.$id_option|escape:'html':'UTF-8'}" value="{$option.$id_option|escape:'html':'UTF-8'}" {if isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && in_array($option.$id_option, $fields_value[$input.name]) || $all_countries}checked="checked"{/if}/></td>
                            <td><label for="item{$option.$id_option|escape:'html':'UTF-8'}">{$option.name|escape:'html':'UTF-8'}</label></td>
                        </tr>
                    {/foreach}
                    </tbody>

            </table>
            {else}
                <div class="alert alert-warning">{l s='No country found' mod='ets_geolocation'}</div>
            {/if}
        </div>
    {/if}
    {$smarty.block.parent}
{/block}