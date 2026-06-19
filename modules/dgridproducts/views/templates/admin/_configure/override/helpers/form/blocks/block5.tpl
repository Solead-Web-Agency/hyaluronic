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
* @author    SeoSA <885588@bk.ru>
* @copyright 2012-2022 SeoSA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<div class="form-wrapper">
    <div class="form-group">
        {foreach $field as $input}
            {block name="label"}
                {if $input.name == 'date_upd'}
                    {if isset($input.label)}
                        <label class="control-label col-lg-3" data-toggle="tooltip" data-html="true" title="">
                            {$input.label}
                        </label>
                    {/if}
                {/if}
            {/block}
        {/foreach}
        {foreach $field as $input}
            {block name="input"}
                {if $input.type == 'radio' && $input.name == 'date_upd'}
                    {foreach $input.values as $value}
                        <div class="radio {if isset($input.class)}{$input.class}{/if}">
                            {strip}
                                <label>
                                    <input type="radio"	name="{$input.name}" id="{$value.id}" value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                    {$value.label}
                                </label>
                            {/strip}
                        </div>
                        {if isset($value.p) && $value.p}<p class="help-block">{$value.p}</p>{/if}
                    {/foreach}
                {elseif $input.type == 'switch' && $input.name == 'date_upd'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes' mod='dgridproducts'}
                                        {else}
                                            {l s='No' mod='dgridproducts'}
                                        {/if}
                                    </label>
                                {/strip}
                            {/foreach}
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                {/if}
            {/block}
        {/foreach}
    </div>
    <div class="form-group">
        {foreach $field as $input}
            {block name="label"}
                {if $input.name == 'message'}
                    {if isset($input.label)}
                        <label class="control-label col-lg-3" data-toggle="tooltip" data-html="true" title="">
                            {$input.label}
                        </label>
                    {/if}
                {/if}
            {/block}
        {/foreach}
        {foreach $field as $input}
            {block name="input"}
                {if $input.type == 'radio' && $input.name == 'message'}
                    {foreach $input.values as $value}
                        <div class="radio {if isset($input.class)}{$input.class}{/if}">
                            {strip}
                                <label>
                                    <input type="radio"	name="{$input.name}" id="{$value.id}" value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                    {$value.label}
                                </label>
                            {/strip}
                        </div>
                        {if isset($value.p) && $value.p}<p class="help-block">{$value.p}</p>{/if}
                    {/foreach}
                {elseif $input.type == 'switch' && $input.name == 'message'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes' mod='dgridproducts'}
                                        {else}
                                            {l s='No' mod='dgridproducts'}
                                        {/if}
                                    </label>
                                {/strip}
                            {/foreach}
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                {/if}
            {/block}
        {/foreach}
    </div>
    <div class="form-group">
        {foreach $field as $input}
            {block name="label"}
                {if $input.name == 'employee'}
                    {if isset($input.label)}
                        <label class="control-label col-lg-3" data-toggle="tooltip" data-html="true" title="">
                            {$input.label}
                        </label>
                    {/if}
                {/if}
            {/block}
        {/foreach}
        {foreach $field as $input}
            {block name="input"}
                {if $input.type == 'radio' && $input.name == 'employee'}
                    {foreach $input.values as $value}
                        <div class="radio {if isset($input.class)}{$input.class}{/if}">
                            {strip}
                                <label>
                                    <input type="radio"	name="{$input.name}" id="{$value.id}" value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                    {$value.label}
                                </label>
                            {/strip}
                        </div>
                        {if isset($value.p) && $value.p}<p class="help-block">{$value.p}</p>{/if}
                    {/foreach}
                {elseif $input.type == 'switch' && $input.name == 'employee'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes' mod='dgridproducts'}
                                        {else}
                                            {l s='No' mod='dgridproducts'}
                                        {/if}
                                    </label>
                                {/strip}
                            {/foreach}
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                {/if}
            {/block}
        {/foreach}
    </div>
</div>
