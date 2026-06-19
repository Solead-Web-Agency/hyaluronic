{*
* 2007-2022 ETS-Soft
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
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{extends file="helpers/form/form.tpl"}
{block name="legend"}
    {if isset($menuTab) && $menuTab}
        <div class="panel-heading">
            <div class="ets_abancart_menus">
                {if isset($field.image) && isset($field.title)}<img src="{$field.image nofilter}"
                                                                    alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                {if isset($field.icon)}<i class="{$field.icon|escape:'html':'UTF-8'}"></i>{/if}
                {$field.title|escape:'html':'UTF-8'}
            </div>
            <div class="ets_abancart_buttons ets_abancart_cronjob_tab_right">
                <ul class="ets_abancart_cronjob_tabs">
                    {foreach from=$menuTab key='id_tab' item ='tab'}
                        <li class="ets_abancart_cronjob_tab_item" data-tab="{$id_tab|escape:'html':'UTF-8'}">
                            {if isset($tab.icon) && $tab.icon}<i
                                class="{$tab.icon|escape:'html':'UTF-8'}"></i>{/if} {$tab.name|escape:'html':'UTF-8'}
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
        <div class="clearfix"></div>
    {else}{$smarty.block.parent}{/if}
    {if isset($fields_value['ETS_ABANCART_CONTENT'])}
        <div class="ets-ac-content-design-tab">
            <div class="tab-menu-item active" data-tab="content">{l s='Content' mod='ets_abandonedcart'}</div>
            <div class="tab-menu-item" data-tab="design">{l s='Design' mod='ets_abandonedcart'}</div>
        </div>
    {/if}
{/block}
{block name="input"}
    {if $input.type == 'abancart_group'}
        {assign var=groups value=$input.values}
        {if $groups}
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="fixed-width-xs"><span class="title_box"><input type="checkbox"
                                                                                      class="all_abancart_group"
                                                                                      name="{$input.name|escape:'html':'UTF-8'}[]"
                                                                                      id="all_abancart_group"
                                                                                      onclick="checkDelBoxes(this.form, '{$input.name|escape:'html':'UTF-8'}[]', this.checked)"
                                                                                      value="ALL"/></span></th>
                            <th><label for="all_abancart_group"
                                       class="title_box">{l s='All' mod='ets_abandonedcart'}</label></th>
                        </tr>
                        </thead>
                        <tbody>
                        {if isset($groups.query) && $groups.query}{foreach $groups.query as $group}
                            {if !empty($groups.id)}{assign var='id_group' value=$group[$groups.id]}{else}{assign var='id_group' value=$group.id_group}{/if}
                            {if isset($id_group) && $id_group}
                                <tr>
                                <td><input type="checkbox" name="{$input.name|escape:'html':'UTF-8'}[]"
                                           class="groupBox abancart_group"
                                           id="{$input.name|escape:'html':'UTF-8'}_{$id_group|escape:'quotes':'UTF-8'}"
                                           value="{$id_group|escape:'quotes':'UTF-8'}"
                                           {if !empty($fields_value[$input.name]) && is_array($fields_value[$input.name]) && in_array($id_group, $fields_value[$input.name]) || $fields_value[$input.name]=='all'}checked="checked"{/if}/>
                                </td>
                                <td>
                                    <label for="{$input.name|escape:'html':'UTF-8'}_{$id_group|escape:'quotes':'UTF-8'}">{$group.name|escape:'html':'UTF-8'}</label>
                                </td>
                                </tr>{/if}
                        {/foreach}{/if}
                        </tbody>
                    </table>
                </div>
            </div>
        {else}
            <p>{l s='No group created' mod='ets_abandonedcart'}</p>
        {/if}
    {elseif $input.name == 'ETS_ABANCART_REDUCTION_AMOUNT'}
        <div class="row">
            {if $input.name == 'ETS_ABANCART_REDUCTION_AMOUNT'}
                <div class="col-lg-4">
                <input type="text" id="{$input.name|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}"
                       value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|floatval}{/if}"
                       onchange="this.value = this.value.replace(/,/g, '.');">
                </div>{/if}
            <div class="col-lg-4">
                {if !empty($input.currencies)}<select name="ETS_ABANCART_ID_CURRENCY">
                    {foreach from=$input.currencies item='currency'}
                        <option value="{$currency.id_currency|intval}"{if isset($fields_value['ETS_ABANCART_ID_CURRENCY']) && $fields_value['ETS_ABANCART_ID_CURRENCY'] == $currency.id_currency} selected="selected"{/if}>{$currency.iso_code|escape:'html':'UTF-8'}</option>
                    {/foreach}
                    </select>{/if}
            </div>
            <div class="col-lg-4">
                {if !empty($input.tax)}<select name="ETS_ABANCART_REDUCTION_TAX">
                    {foreach from=$input.tax item='option'}
                        <option value="{$option.id_option|intval}"{if isset($fields_value['ETS_ABANCART_REDUCTION_TAX']) && $fields_value['ETS_ABANCART_REDUCTION_TAX'] == $option.id_option} selected="selected"{/if}>{$option.name|escape:'html':'UTF-8'}</option>
                    {/foreach}
                    </select>{/if}
            </div>
        </div>
    {elseif $input.type == 'radios'}
        {if isset($input.options.query) && $input.options.query}
            <ul style="padding: 0; margin-top: 5px;">
                {foreach $input.options.query as $option}
                    <li class="ets_abancart_{$input.name|escape:'html':'UTF-8'}{if isset($option.class) && $option.class} {$option.class|escape:'html':'UTF-8'}{/if}"
                        style="list-style: none; padding-bottom: 5px">
                        <input {if $option.id_option == $fields_value[$input.name]} checked="checked" {elseif !$fields_value[$input.name] && $input.default == $option.id_option}checked="checked"{/if}
                                style="margin: 2px 7px 0 5px; float: left;"
                                type="radio"
                                id="{$input.name|cat:'_'|cat:$option.id_option|escape:'html':'UTF-8'}"
                                value="{$option.id_option|escape:'html':'UTF-8'}"
                                name="{$input.name|escape:'html':'UTF-8'}"/>
                        {if $input.name == 'enabled'}<span class="enabled_bg"></span>{/if}
                        {if $option.id_option == 'off'}
                            <i class="icon-remove color_danger"></i>
                        {/if}
                        <label for="{$input.name|cat:'_'|cat:$option.id_option|escape:'html':'UTF-8'}">{$option.name|escape:'html':'UTF-8'}
                            {if isset($option.cart_rule_link) && $option.cart_rule_link} <a target="_blank"
                                                                                            href="{$option.cart_rule_link|escape:'quotes':'UTF-8'}">{l s='Configure discounts' mod='ets_abandonedcart'}</a>{/if}
                        </label>
						{if isset($option.prestashop_mail_link) && $option.prestashop_mail_link} <a
                        target="_blank"
                        href="{$option.prestashop_mail_link|escape:'quotes':'UTF-8'}">{l s='Configure mail' mod='ets_abandonedcart'}</a>{/if}
                    </li>
                {/foreach}
            </ul>
        {/if}
    {elseif $input.name == 'ETS_ABANCART_CONTENT'}
        {$smarty.block.parent}
        {if $smarty.get.controller == 'AdminEtsACReminderLeave' }
            <div class="ets_ac_reset_popup_box col-lg-12">
                <button type="button" class="btn btn-default ets-ac-btn-reset-content-popup js-ets-ac-btn-reset-content-popup"
                        data-confirm="{l s='If you reset to default, all data changed will not be saved. Do you want to reset to default?' mod='ets_abandonedcart'}">
                        <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                            <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1664 896q0 156-61 298t-164 245-245 164-298 61q-172 0-327-72.5t-264-204.5q-7-10-6.5-22.5t8.5-20.5l137-138q10-9 25-9 16 2 23 12 73 95 179 147t225 52q104 0 198.5-40.5t163.5-109.5 109.5-163.5 40.5-198.5-40.5-198.5-109.5-163.5-163.5-109.5-198.5-40.5q-98 0-188 35.5t-160 101.5l137 138q31 30 14 69-17 40-59 40h-448q-26 0-45-19t-19-45v-448q0-42 40-59 39-17 69 14l130 129q107-101 244.5-156.5t284.5-55.5q156 0 298 61t245 164 164 245 61 298z"/></svg>
                        </i>
                    {l s='Reset to default' mod='ets_abandonedcart'}
                </button>
            </div>
        {/if}
        {assign var='typeObj' value='leave'}
        {if isset($hasProductInCart) && $hasProductInCart !== 1}
            <input type="hidden" name="etsAcHasProductInCart" id="etsAcHasProductInCart" value="1">
        {/if}
        <p class="help-block">
            {if isset($short_codes) && $short_codes}
                {l s='Available tags' mod='ets_abandonedcart'} :
                {foreach from=$short_codes key='id_short_code' item='short_code'}
                    {if empty($short_code.object) || in_array($typeObj, explode(',', $short_code.object))}
                        <span class="ets_abancart_short_code group_{$short_code.group|escape:'html':'UTF-8'} {$id_short_code|escape:'html':'UTF-8'}">
                            <button type="button" class="btn btn-outline-primary sensitive ets_abancart_btn_short_code"
                                    data-short-code="[{$id_short_code|escape:'html':'UTF-8'}{if $id_short_code == 'lead_form'} id=1{elseif $id_short_code == 'product_grid'} id=&quot;&quot{elseif $id_short_code == 'custom_button'} href=&quot;#&quot; text=&quot;{l s='Click here'  mod='ets_abandonedcart'}&quot;{/if}]"><i
                                        class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
                                    <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1344 960v-128q0-26-19-45t-45-19h-256v-256q0-26-19-45t-45-19h-128q-26 0-45 19t-19 45v256h-256q-26 0-45 19t-19 45v128q0 26 19 45t45 19h256v256q0 26 19 45t45 19h128q26 0 45-19t19-45v-256h256q26 0 45-19t19-45zm320-64q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/></svg>
                                </i> [{$short_code.name nofilter}]</button>
                        </span>
                    {/if}
                {/foreach}
            {/if}
	    </p>
	{elseif $input.name == 'ETS_ABANCART_CRONJOB_LOG'}
		<textarea readonly id="{$input.name|escape:'html':'UTF-8'}" name="_{$input.name|escape:'html':'UTF-8'}">{if isset($cronjobLog) && $cronjobLog}{$cronjobLog nofilter}{/if}</textarea>
		<button class="ets_abancart_clear_log btn btn-default" name="ets_abancart_clear_log" type="button">
			<i class="icon-trash"></i> {l s='Clear log' mod='ets_abandonedcart'}
		</button>
    {elseif $input.name == 'ETS_ABANCART_REDUCTION_PRODUCT'}
        <div class="input_group_form">
            {if isset($fields_value.specific_product_item) && $fields_value.specific_product_item}
                {$fields_value.specific_product_item nofilter}
            {else}
                <ul class="ets-ac-products-list-selected" id="ets-ac-products-list-{$input.name|escape:'html':'UTF-8'}"></ul>
            {/if}
            <div class="input-group">
                <input class="form-control specific_product ets_ac_specific_product_filter"
                       value=""
                       data-name="{$input.name|escape:'html':'UTF-8'}" />
                <input type="hidden" name="{$input.name|escape:'html':'UTF-8'}" value="{$fields_value[$input.name]|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="ets_svg_fill_gray lh_16">
						<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1216 832q0-185-131.5-316.5t-316.5-131.5-316.5 131.5-131.5 316.5 131.5 316.5 316.5 131.5 316.5-131.5 131.5-316.5zm512 832q0 52-38 90t-90 38q-54 0-90-38l-343-342q-179 124-399 124-143 0-273.5-55.5t-225-150-150-225-55.5-273.5 55.5-273.5 150-225 225-150 273.5-55.5 273.5 55.5 225 150 150 225 55.5 273.5q0 220-124 399l343 343q37 37 37 90z"/></svg>
					</i></span>
            </div>
        </div>
    {elseif $input.name == 'ETS_ABANCART_SELECTED_PRODUCT'}
        <div class="input-group">
            <input class="form-control selected_product ets_ac_selected_product_filter" data-name="{$input.name|escape:'html':'UTF-8'}" />
            <span class="input-group-addon"><i class="ets_svg_fill_gray lh_16">
						<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1216 832q0-185-131.5-316.5t-316.5-131.5-316.5 131.5-131.5 316.5 131.5 316.5 316.5 131.5 316.5-131.5 131.5-316.5zm512 832q0 52-38 90t-90 38q-54 0-90-38l-343-342q-179 124-399 124-143 0-273.5-55.5t-225-150-150-225-55.5-273.5 55.5-273.5 150-225 225-150 273.5-55.5 273.5 55.5 225 150 150 225 55.5 273.5q0 220-124 399l343 343q37 37 37 90z"/></svg>
					</i></span>
        </div>
        {if isset($fields_value.selected_product_list) && $fields_value.selected_product_list}
            {$fields_value.selected_product_list nofilter}
        {else}
            <ul class="ets-ac-products-list-selected" id="ets-ac-products-list-{$input.name|escape:'html':'UTF-8'}"></ul>
        {/if}
    {elseif $input.name == 'ETS_ABANCART_PRODUCT_GIFT'}
        <div class="input_group_form">
            {if isset($fields_value.gift_product_item) && $fields_value.gift_product_item}
                {$fields_value.gift_product_item nofilter}
            {else}
                <ul class="ets-ac-products-list-selected ets_abancart_result_productlist" id="ets-ac-products-list-{$input.name|escape:'html':'UTF-8'}"></ul>
            {/if}
            <div class="input-group">
                <input class="form-control selected_product ets_ac_gift_product_filter"
                       value=""
                       data-name="{$input.name|escape:'html':'UTF-8'}" />
                <input type="hidden" name="ETS_ABANCART_GIFT_PRODUCT" value="{if isset($fields_value.ETS_ABANCART_GIFT_PRODUCT)}{$fields_value.ETS_ABANCART_GIFT_PRODUCT|escape:'html':'UTF-8'}{/if}" />
                <input type="hidden" name="ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE" value="{if isset($fields_value.ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE)}{$fields_value.ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE|escape:'html':'UTF-8'}{/if}" />
                <span class="input-group-addon"><i class="ets_svg_fill_gray lh_16">
						<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1216 832q0-185-131.5-316.5t-316.5-131.5-316.5 131.5-131.5 316.5 131.5 316.5 316.5 131.5 316.5-131.5 131.5-316.5zm512 832q0 52-38 90t-90 38q-54 0-90-38l-343-342q-179 124-399 124-143 0-273.5-55.5t-225-150-150-225-55.5-273.5 55.5-273.5 150-225 225-150 273.5-55.5 273.5 55.5 225 150 150 225 55.5 273.5q0 220-124 399l343 343q37 37 37 90z"/></svg>
					</i></span>
            </div>
        </div>
    {elseif $input.type == 'range'}
        <input type="hidden" name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{elseif isset($input.default)}{$input.default|escape:'html':'UTF-8'}{/if}">
        <div class="range-wrap ets-ac-range-input ets_range_input">
            <div class="range-wrap ets-ac-range-input">
                <div class="ets_range_input_slide">
                    <span class="range-bubble-bar"></span>
                </div>
                <div class="range-bubble"></div>
                <input type="range" class="range for-target-name" data-name-target="{$input.name|escape:'html':'UTF-8'}" name="range_{$input.name|escape:'html':'UTF-8'}"
                       data-selector-change="{if isset($input.selector_change)}{$input.selector_change|escape:'html':'UTF-8'}{/if}"
                       data-attr-change="{if isset($input.attr_change)}{$input.attr_change|escape:'html':'UTF-8'}{/if}"
                       data-unit="{if isset($input.unit)}{$input.unit|escape:'html':'UTF-8'}{/if}"
                       min="{$input.min|escape:'html':'UTF-8'}" max="{$input.max|escape:'html':'UTF-8'}"
                       step="{if isset($input.step) && $input.step}{$input.step|floatval}{else}1{/if}"
                       value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{elseif isset($input.default)}{$input.default|escape:'html':'UTF-8'}{/if}" />
                <div class="range_title">
                    <span class="min-number">{$input.min|escape:'html':'UTF-8'} {if isset($input.unit)}{$input.unit|escape:'html':'UTF-8'}{/if}</span>
                    <span class="max-number">{$input.max|escape:'html':'UTF-8'} {if isset($input.unit)}{$input.unit|escape:'html':'UTF-8'}{/if}</span>
                </div>
            </div>
        </div>
    {elseif $input.type == 'color'}
        <div class="form-group">
            <div class="col-lg-5">
                <div class="row">
                    <div class="input-group">
                        <input type="color"
                               data-selector-change="{if isset($input.selector_change)}{$input.selector_change|escape:'html':'UTF-8'}{/if}"
                               data-attr-change="{if isset($input.attr_change)}{$input.attr_change|escape:'html':'UTF-8'}{/if}"
                               data-hex="true"
                                {if isset($input.class)} class="{$input.class|escape:'html':'UTF-8'}"
                                {else} class="color mColorPickerInput"{/if}
                               name="{$input.name|escape:'html':'UTF-8'}"
                               value="{$fields_value[$input.name]|escape:'html':'UTF-8'}" />
                    </div>
                </div>
            </div>
        </div>
    {else}
        {if $input.name=='ETS_ABANCART_SECURE_TOKEN'}
            <div class="input-group">
                <input type="text" name="ets_abancart_secure_token" id="ets_abancart_secure_token" value="{if !empty($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}" placeholder="">
                <span class="input-group-addon"><i class="ets_icon_svg">
					<svg width="12" height="12" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M666 481q-60 92-137 273-22-45-37-72.5t-40.5-63.5-51-56.5-63-35-81.5-14.5h-224q-14 0-23-9t-9-23v-192q0-14 9-23t23-9h224q250 0 410 225zm1126 799q0 14-9 23l-320 320q-9 9-23 9-13 0-22.5-9.5t-9.5-22.5v-192q-32 0-85 .5t-81 1-73-1-71-5-64-10.5-63-18.5-58-28.5-59-40-55-53.5-56-69.5q59-93 136-273 22 45 37 72.5t40.5 63.5 51 56.5 63 35 81.5 14.5h256v-192q0-14 9-23t23-9q12 0 24 10l319 319q9 9 9 23zm0-896q0 14-9 23l-320 320q-9 9-23 9-13 0-22.5-9.5t-9.5-22.5v-192h-256q-48 0-87 15t-69 45-51 61.5-45 77.5q-32 62-78 171-29 66-49.5 111t-54 105-64 100-74 83-90 68.5-106.5 42-128 16.5h-224q-14 0-23-9t-9-23v-192q0-14 9-23t23-9h224q48 0 87-15t69-45 51-61.5 45-77.5q32-62 78-171 29-66 49.5-111t54-105 64-100 74-83 90-68.5 106.5-42 128-16.5h256v-192q0-14 9-23t23-9q12 0 24 10l319 319q9 9 9 23z"/></svg>
				</i> {l s='Generate' mod='ets_abandonedcart'}</span>
            </div>
            <input type="hidden" name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}" value="{if !empty($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}">
        {else}
            {$smarty.block.parent}
            {if trim($input.name) === 'ETS_ABANCART_MAIL_API_KEY'}
                <p class="help-block">
                    <a target="_blank" rel="noreferrer noopener" class="ets-ab-config-mail sendgrid" href="https://sendgrid.com/docs/ui/account-and-settings/api-keys/">{l s='How to get key?' mod='ets_abandonedcart'}</a>
                    <a target="_blank" rel="noreferrer noopener" class="ets-ab-config-mail sendinblue" href="https://help.sendinblue.com/hc/en-us/articles/209467485-What-s-an-API-key-and-how-can-I-get-mine">{l s='How to get key?' mod='ets_abandonedcart'}</a>
                </p>
            {/if}
            {if trim($input.name) === 'ETS_ABANCART_MAIL_SECRET_KEY'}
                <p class="help-block">
                    <a target="_blank" rel="noreferrer noopener" class="ets-ab-config-mail mailjet" href="https://app.mailjet.com/account/api_keys">{l s='How to get key?' mod='ets_abandonedcart'}</a>
                </p>
            {/if}
            {if preg_match('/^ETS_ABANCART_MAIL_SMTP_PORT/', trim($input.name))}
                <p class="help-block">
                    <span class="ets-ab-config-mail gmail"><a target="_blank"  href="https://support.google.com/mail/answer/7126229?hl=en" rel="noreferrer noopener">{l s='How to configure Gmail?' mod='ets_abandonedcart'}</a>. {l s='Note that you may need to enable less secure apps to access Gmail in order to send reminder emails via Gmail SMTP:' mod='ets_abandonedcart'} <a href="https://support.google.com/a/answer/6260879?hl=en" target="_blank" rel="noreferrer noopener">{l s='See more here!' mod='ets_abandonedcart'}</a></span>
                    <a target="_blank" class="ets-ab-config-mail hotmail" href="https://support.microsoft.com/en-us/office/pop-imap-and-smtp-settings-for-outlook-com-d088b986-291d-42b8-9564-9c414e2aa040" rel="noreferrer noopener">{l s='How to configure Hotmail?' mod='ets_abandonedcart'}</a>
                    <a target="_blank" class="ets-ab-config-mail yahoomail" href="https://help.yahoo.com/kb/set-imap-sln4075.html" rel="noreferrer noopener">{l s='How to configure Yahoo mail?' mod='ets_abandonedcart'}</a>
                </p>
            {/if}
        {/if}
    {/if}
{/block}

{block name="input_row"}
    {if (!isset($tabs) || !$tabs) && isset($menuTab) && $menuTab}{assign var="tabs" value=$menuTab}{/if}
    {if $input.name == 'ETS_ABANCART_CRONJOB_MAIL_LOG'}{*ETS_ABANCART_CRONJOB_EMAILS*}
        <div class="form-group ets_abancart_cronjob"{if isset($input.tab) && $input.tab} data-tab-id="{$input.tab|escape:'html':'UTF-8'}"{/if}>
            <p>{l s='Configure cronjob feature to send email for reminder campaign that you added. For example, send reminder email after customer adding products to shopping cart, after customer registering an account, after customer subscribes to newsletter, etc.' mod='ets_abandonedcart'}</p>
            <p>{l s='Moreover, you can save failed email to mail queue to run in next time. This will help you resend the errored email within allowed time.' mod='ets_abandonedcart'}</p>
            <h4><span class="required">*</span> {l s='Some important notes:' mod='ets_abandonedcart'}</h4>
            <ul>
                <li>{l s='The recommended frequency is ' mod='ets_abandonedcart'}<b>{l s='once per minute' mod='ets_abandonedcart'}</b></li>
                <li>{l s='How to set up a cronjob is different depending on your server. If you are using a Cpanel hosting, watch this video for reference: ' mod='ets_abandonedcart'}
                    <a target="_blank" href="https://www.youtube.com/watch?v=bmBjg1nD5yA" rel="noreferrer noopener">https://www.youtube.com/watch?v=bmBjg1nD5yA</a><br/>
                    {l s='If your cpanel software is Plesk, see this:' mod='ets_abandonedcart'} <a href="https://docs.plesk.com/en-US/obsidian/customer-guide/scheduling-tasks.65207/" target="_blank" rel="noreferrer noopener">https://docs.plesk.com/en-US/obsidian/customer-guide/scheduling-tasks.65207/</a><br/>
                    {l s='If your server is Ubuntu, see this:' mod='ets_abandonedcart'} <a href="https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-ubuntu-1804" target="_blank" rel="noreferrer noopener">https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-ubuntu-1804</a><br/>
                    {l s='If your server is Centos, see this:' mod='ets_abandonedcart'} <a href="https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-centos-8" target="_blank" rel="noreferrer noopener">https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-centos-8</a><br/>
                    {l s='You can also contact your hosting provider to ask them for support on setting up the cronjob' mod='ets_abandonedcart'}
                </li>
                <li>{l s='Web push notification only works on Chrome and Firefox (and some other modern web browsers) when HTTPS is enabled' mod='ets_abandonedcart'}</li>
                <li>{l s='Configure SMTP for your website (instead of using default PHP mail() function) to send email better. If you can afford, buy professional marketing email hosting to send a large number of emails' mod='ets_abandonedcart'}</li>
            </ul>
        </div>
    {/if}
    {if $input.name != 'ETS_ABANCART_ID_CURRENCY' && $input.name != 'ETS_ABANCART_REDUCTION_TAX' && $input.name != 'ETS_ABANCART_HOURS' && $input.name != 'ETS_ABANCART_MINUTES' && $input.name != 'ETS_ABANCART_SECONDS'}
        {if $input.name == 'ETS_ABANCART_MAIL_SERVICE'}
            <p class="ets_abancart_title_block alert alert-info">{l s='Select and configure a mail service to send reminder emails.' mod='ets_abandonedcart'}</p>
        {/if}
        {if $input.name == 'ETS_ABANCART_BROWSER_TAB_ENABLED'}
            <p class="ets_abancart_title_block alert alert-info">{l s='Highlight the number of products in shopping cart on customer\'s browser tab' mod='ets_abandonedcart'}</p>
        {/if}
        {$smarty.block.parent}
    {/if}
    {if $input.name=='ETS_ABANCART_HOURS'}
        <div class="form-group{if $input.form_group_class} {$input.form_group_class|escape:'html':'UTF-8'}{/if}">
            <label class="control-label col-lg-3">{l s='Display a reminder message to suggest customers to save their shopping cart if they have not checkout after' mod='ets_abandonedcart'}</label>
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-2">
                        <div class="input-group">
                            <input type="text" class="" name="ETS_ABANCART_HOURS"
                                   value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}"/>
                            <span class="input-group-addon">{l s='Hour(s)' mod='ets_abandonedcart'}</span>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group">
                            <input type="text" class="" name="ETS_ABANCART_MINUTES"
                                   value="{if isset($fields_value['ETS_ABANCART_MINUTES'])}{$fields_value['ETS_ABANCART_MINUTES']|escape:'html':'UTF-8'}{/if}"/>
                            <span class="input-group-addon">{l s='Minute(s)' mod='ets_abandonedcart'}</span>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group">
                            <input type="text" class="" name="ETS_ABANCART_SECONDS"
                                   value="{if isset($fields_value['ETS_ABANCART_SECONDS'])}{$fields_value['ETS_ABANCART_SECONDS']|escape:'html':'UTF-8'}{/if}"/>
                            <span class="input-group-addon">{l s='Second(s)' mod='ets_abandonedcart'}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
    {if $input.name=='ETS_ABANCART_SECURE_TOKEN'}
        <div class="form-group ets_abancart_cronjob ets_abancart_help"{if isset($input.tab) && $input.tab} data-tab-id="{$input.tab|escape:'html':'UTF-8'}"{/if}>
            <label class="control-label col-lg-3"><span
                        class="required">*</span> {l s='Setup a cronjob as below on your server to send email reminders automatically' mod='ets_abandonedcart'}
            </label>

            <em><span id="ets_abd_cronjob_path">{$path|escape:'quotes':'UTF-8'}</span></em>
            <label class="control-label col-lg-3"><span
                        class="required">*</span> {l s='Execute the cronjob manually by clicking on the button below' mod='ets_abandonedcart'}
            </label><br>
            <a id="ets_abd_cronjob_link" class="btn btn-default" href="{$url|escape:'quotes':'UTF-8'}"
               target="_blank">{l s='Execute cronjob manually' mod='ets_abandonedcart'}</a>
        </div>
    {/if}
    {if $input.type=='default_content'}
        {if $smarty.get.controller !== 'AdminEtsACReminderLeave' }
        <div class="form-group">
            <button type="button" class="btn btn-default ets-ac-btn-reset-content-popup js-ets-ac-btn-reset-content-popup"
                    data-confirm="{l s='If you reset to default, all data changed will not be saved. Do you want to reset to default?' mod='ets_abandonedcart'}">
                {l s='Reset to default' mod='ets_abandonedcart'}
            </button>
        </div>
        {/if}
        <textarea class="ets_ac_default_content_has_discount" style="display: none">{$input.has_discount nofilter}</textarea>
        <textarea class="ets_ac_default_content_no_discount" style="display: none">{$input.no_discount nofilter}</textarea>
        <textarea class="ets_ac_default_content_no_product_in_cart" style="display: none">{$input.no_product_in_cart nofilter}</textarea>
        {if isset($input.title_has_discount)}
            <input type="hidden" class="ets_ac_default_title_has_discount" value="{$input.title_has_discount nofilter}" />
        {/if}
        {if isset($input.title_no_discount)}
            <input type="hidden" class="ets_ac_default_title_no_discount" value="{$input.title_no_discount nofilter}" />
        {/if}
        {if isset($input.title_no_product_in_cart)}
            <input type="hidden" class="ets_ac_default_title_no_product_in_cart" value="{$input.title_no_product_in_cart nofilter}" />
        {/if}
    {/if}
{/block}

{block name="after"}
    {if isset($smarty.get.controller) && $smarty.get.controller && (trim($smarty.get.controller) === 'AdminEtsACMailConfigs' || trim($smarty.get.controller) === 'AdminEtsACMailServices')}
        {hook h='displayBoFormTestMail'}
    {/if}
{/block}

{block name="autoload_tinyMCE"}
    tinySetup({
        editor_selector : 'autoload_rte',
        force_br_newlines : true,
        force_p_newlines : false,
        forced_root_block : '',
        setup : function(ed) {
            ed.on('keyup change blur', function(ed) {
                tinyMCE.triggerSave();
                ets_ab_fn.previewLanguage();
                if($('.ets_abancart_overload.active').length > 0) {
                    ets_ab_fn.prevNext();
                }
            });
            ed.on('change', function(ed) {
                if(!ets_abancart_textarea_changed && ets_abancart_tab_message_active){
                    ets_abancart_textarea_changed = true;
                }
            });
        },
        resize : 'both',
        height : 350
    });
{/block}