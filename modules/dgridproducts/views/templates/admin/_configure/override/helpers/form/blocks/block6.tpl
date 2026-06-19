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
                {if $input.name == 'combinations'}
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
                {if $input.type == 'radio' && $input.name == 'combinations'}
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
                {elseif $input.type == 'switch' && $input.name == 'combinations'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'features'}
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
                {if $input.type == 'radio' && $input.name == 'features'}
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
                {elseif $input.type == 'switch' && $input.name == 'features'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'image'}
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
                {if $input.type == 'radio' && $input.name == 'image'}
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
                {elseif $input.type == 'switch' && $input.name == 'image'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'name_category'}
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
                {if $input.type == 'radio' && $input.name == 'name_category'}
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
                {elseif $input.type == 'switch' && $input.name == 'name_category'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'meta_tags'}
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
                {if $input.type == 'radio' && $input.name == 'meta_tags'}
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
                {elseif $input.type == 'switch' && $input.name == 'meta_tags'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'additional_setting_product'}
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
                {if $input.type == 'radio' && $input.name == 'additional_setting_product'}
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
                {elseif $input.type == 'switch' && $input.name == 'additional_setting_product'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'date_add'}
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
                {if $input.type == 'radio' && $input.name == 'date_add'}
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
                {elseif $input.type == 'switch' && $input.name == 'date_add'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'brand'}
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
                {if $input.type == 'radio' && $input.name == 'brand'}
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
                {elseif $input.type == 'switch' && $input.name == 'brand'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                    <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'visibility'}
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
                {if $input.type == 'radio' && $input.name == 'visibility'}
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
                {elseif $input.type == 'switch' && $input.name == 'visibility'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'product_supplier_reference'}
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
                {if $input.type == 'radio' && $input.name == 'product_supplier_reference'}
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
                {elseif $input.type == 'switch' && $input.name == 'product_supplier_reference'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
                {if $input.name == 'location'}
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
                {if $input.type == 'radio' && $input.name == 'location'}
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
                {elseif $input.type == 'switch' && $input.name == 'location'}
                    <div class="col-lg-9">
                        <span class="switch ps-switch prestashop-switch fixed-width-lg float-left">
                            {foreach $input.values as $value}
                                <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                {strip}
                                <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
                                        {if $value.value == 1}
                                            {l s='Yes'  mod='dgridproducts'}
                                        {else}
                                            {l s='No'  mod='dgridproducts'}
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
