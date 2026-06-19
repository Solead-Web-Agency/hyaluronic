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
{block name="defaultForm"}
	{if isset($table) && trim($table) === 'ets_abancart_campaign' && isset($id_campaign) && $id_campaign|intval > 0}
		<p class="ets_abancart_title_block ets_abancart_reminder_empty alert alert-warning{if isset($nb_reminders) && $nb_reminders|intval <= 0} active{/if}">
	        {l s='Campaign is not running because no reminders have been added.' mod='ets_abandonedcart'}&nbsp;<a href="{if isset($href) && $href}{$href|escape:'quotes':'UTF-8'}{else}#{/if}" class="ets_abancart_add_new_reminder">{l s='Add reminder' mod='ets_abandonedcart'}</a>
		</p>
	{/if}
	{$smarty.block.parent}
{/block}
{block name="legend"}
    {assign var='isCampaignObj' value=preg_match('#Reminder(Email|Popup|Bar|Browser|Customer)|Cart$#', $controller_name)}
	{assign var='reminderType' value=preg_replace('#^'|cat:$slugTab|cat:'(?:Reminder)?(\w+)$#', '$1', $controller_name)|lower}
    {if $isCampaignObj && isset($menus) && $menus|count > 0}
		<div class="panel-heading">
			<div class="ets_abancart_menus pull-left">
				<ul class="ets_abancart_nav_tabs">
                    {foreach from=$menus key='id_menu' item ='menu'}
                        {if $reminderType!='cart'|| $id_menu!='frequency'}
							<li class="ets_abancart_tab_item" data-tab="{$id_menu|escape:'html':'UTF-8'}">
                                {if $menu.icon}<i class="{$menu.icon|escape:'html':'UTF-8'}"></i>{/if}&nbsp;
                                {if $id_menu == 'message' && $reminderType|lower != 'email' && isset($menu.reminder_type) && $menu.reminder_type|count > 0 && isset($menu.reminder_type.$reminderType)}
                                    {$menu.reminder_type.$reminderType|escape:'quotes':'UTF-8'}
                                {else}
                                    {$menu.label|escape:'html':'UTF-8'}
                                {/if}
							</li>
                        {/if}
                    {/foreach}
				</ul>
			</div>
			<div class="ets_ac_step_hidden hide"></div>
			<div class="ets_abancart_buttons pull-right">
				<button class="btn btn-default" name="{$reminderType|escape:'html':'UTF-8'}BackToCampaign" type="button"><i class="icon-long-arrow-left"></i>&nbsp;{if $reminderType!='cart'}{l s='Back to campaign' mod='ets_abandonedcart'}{else}{l s='Back to Abandoned cart' mod='ets_abandonedcart'}{/if}</button>
				<button class="btn btn-default{if $reminderType|lower=='cart'} hidden{/if}" name="save{$reminderType|ucfirst|escape:'html':'UTF-8'}" type="button">
					{if $reminderType!='cart'}
                        <i class="icon-save"></i> {l s='Save' mod='ets_abandonedcart'}
                    {else}
                        <i class="icon-envelope-o"></i> {l s='Send email' mod='ets_abandonedcart'}
                    {/if}
				</button>
			</div>
		</div>
		<div class="clearfix"></div>
    {else}
        {if isset($warning_add_new) && $warning_add_new}
			<p class="alert alert-warning">{l s='Please use one of the premade email template to ensure that the style for notification email will not be broken.' mod='ets_abandonedcart'}
				<a href="{$warning_add_new nofilter}">{l s='View email template' mod='ets_abandonedcart'}</a></p>
        {/if}
        {$smarty.block.parent}
    {/if}
{/block}
{block name="input"}
	{assign var='isCampaignObj' value=preg_match('#Reminder(Email|Popup|Bar|Browser|Customer)|Cart$#', $controller_name)}
	{assign var='reminderType' value=preg_replace('#^'|cat:$slugTab|cat:'(?:Reminder)?(\w+)$#', '$1', $controller_name)|lower}
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
    {elseif $input.name|trim === 'purchased_product' || $input.name|trim === 'not_purchased_product'}
	    <div class="input-group">
		    <input id="search_{$input.name|escape:'html':'UTF-8'}" class="ets_abancart_result_productlist" type="text" name="search_{$input.name|escape:'html':'UTF-8'}"{if isset($input.class) && $input.class} class="{$input.class|escape:'html':'UTF-8'}"{/if} placeholder="{l s='Search product by id or name' mod='ets_abandonedcart'}" autocomplete="off">
		    <input id="{$input.name|escape:'html':'UTF-8'}" type="hidden" name="{$input.name|escape:'html':'UTF-8'}" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}">
		    <span class="input-group-addon"><i class="icon-search"></i></span>
	    </div>
        {hook h='displayBoPurchasedProduct' ids=$fields_value[$input.name]|escape:'html':'UTF-8' name=$input.name|escape:'html':'UTF-8'}
    {elseif $input.name|trim == 'available_from' || $input.name|trim == 'last_order_from'}
		<div class="row">
			<div class="col-lg-3">
				<div class="input-group">
					<span class="input-group-addon">{l s='From' mod='ets_abandonedcart'}</span>
					<input type="text" class="datepicker input-medium" name="{$input.name|escape:'html':'UTF-8'}" list="autocompleteOff" autocomplete="off" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}"/>
					<span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
				</div>
			</div>
			{if isset($input.to) && $input.to}
				<div class="col-lg-3">
					<div class="input-group">
						<span class="input-group-addon">{l s='To' mod='ets_abandonedcart'}</span>
						<input type="text" class="datepicker input-medium" name="{$input.to|escape:'html':'UTF-8'}" list="autocompleteOff" autocomplete="off"value="{if isset($fields_value[$input.to])}{$fields_value[$input.to]|escape:'html':'UTF-8'}{/if}"/>
						<span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
					</div>
				</div>
			{/if}
		</div>
    {elseif $input.name|trim == 'min_total_cart' || $input.name|trim == 'min_total_order'}
		<div class="row">
			<div class="col-lg-3">
				<div class="input-group">
					<span class="input-group-addon">{l s='From' mod='ets_abandonedcart'}</span>
					<input type="text" class="" name="{$input.name|escape:'html':'UTF-8'}"
					       value="{if isset($fields_value[$input.name]) && $fields_value[$input.name] != ''}{$fields_value[$input.name]|round:2|escape:'html':'UTF-8'}{/if}"/>
					<span class="input-group-addon">{$input.suffix|escape:'html':'UTF-8'}</span>
				</div>
			</div>
            {if isset($input.to) && $input.to}
				<div class="col-lg-3">
					<div class="input-group">
						<span class="input-group-addon">{l s='To' mod='ets_abandonedcart'}</span>
						<input type="text" class="" name="{$input.to|escape:'html':'UTF-8'}"
						       value="{if isset($fields_value[$input.to]) && $fields_value[$input.to]|trim != ''}{$fields_value[$input.to]|round:2|escape:'html':'UTF-8'}{/if}"/>
						<span class="input-group-addon">{$input.suffix|escape:'html':'UTF-8'}</span>
					</div>
				</div>
            {/if}
		</div>
    {elseif $input.name == 'reduction_amount'}
		<div class="row">
            {if $input.name == 'reduction_amount'}
				<div class="col-lg-4">
				<input type="text" id="{$input.name|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}"
				       value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|floatval}{/if}"
				       onchange="this.value = this.value.replace(/,/g, '.');">
				</div>{/if}
			<div class="col-lg-4">
                {if !empty($input.currencies)}<select name="id_currency">
                    {foreach from=$input.currencies item='currency'}
						<option value="{$currency.id_currency|intval}"{if isset($fields_value['id_currency']) && $fields_value['id_currency']|intval == $currency.id_currency|intval} selected="selected"{/if}>{$currency.iso_code|escape:'html':'UTF-8'}</option>
                    {/foreach}
					</select>{/if}
			</div>
			<div class="col-lg-4">
                {if !empty($input.tax)}<select name="reduction_tax">
                    {foreach from=$input.tax item='option'}
						<option value="{$option.id_option|intval}"{if isset($fields_value['reduction_tax']) && $fields_value['reduction_tax']|intval == $option.id_option|intval} selected="selected"{/if}>{$option.name|escape:'html':'UTF-8'}</option>
                    {/foreach}
					</select>{/if}
			</div>
		</div>
    {elseif $input.type == 'radios'}
        {if isset($input.options.query) && $input.options.query}
			<ul style="padding: 0; margin-top: 5px;">
                {foreach $input.options.query as $option}
					<li class="ets_abancart_{$input.name|escape:'html':'UTF-8'}{if isset($option.class) &&  $option.class} {$option.class|escape:'html':'UTF-8'}{/if}"
					    style="list-style: none; padding-bottom: 5px">

						<input {if $option.id_option == $fields_value[$input.name]} checked="checked" {elseif !$fields_value[$input.name] && isset($input.default) && $input.default == $option.id_option}checked="checked"{/if}
								style="margin: 2px 7px 0 5px; float: left;"
								type="radio"
								id="{$input.name|cat:'_'|cat:$option.id_option|escape:'html':'UTF-8'}"
								value="{$option.id_option|escape:'html':'UTF-8'}"
								name="{$input.name|escape:'html':'UTF-8'}"/>
                                {if $input.name == 'enabled'}<span class="enabled_bg"></span>{/if}
                        {if $option.id_option === 'off'}
							<i class="icon-remove color_danger"></i>
                        {/if}
						<label for="{$input.name|cat:'_'|cat:$option.id_option|escape:'html':'UTF-8'}">{$option.name|escape:'html':'UTF-8'}{if isset($option.cart_rule_link) && $option.cart_rule_link}
								<a target="_blank"
								   href="{$option.cart_rule_link|escape:'quotes':'UTF-8'}">{l s='Configure discounts' mod='ets_abandonedcart'}</a>{/if}
						</label>
						{if isset($option.p) && $option.p}<p class="help-block">{$option.p|escape:'html':'UTF-8'}</p>{/if}
					</li>
                {/foreach}
			</ul>
        {/if}
    {elseif $reminderType && ($input.name == 'content' || $input.name == 'email_content')}
        {$smarty.block.parent}
        {if isset($input.desc_type) && $input.desc_type}
            {assign var='typeObj' value=$input.desc_type}
        {else}
            {assign var='typeObj' value='cart'}
        {/if}
		{if $smarty.get.controller == 'AdminEtsACReminderPopup' || $smarty.get.controller == 'AdminEtsACReminderBar'|| $smarty.get.controller == 'AdminEtsACReminderBrowser'}
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
		{if isset($hasProductInCart) && $hasProductInCart !== 1}
			<input type="hidden" name="etsAcHasProductInCart" id="etsAcHasProductInCart" value="1">
		{/if}
		<p class="help-block">
            {if isset($short_codes) && $short_codes}
                {l s='Available tags' mod='ets_abandonedcart'} :
                {foreach from=$short_codes key='id_short_code' item='short_code'}
                    {if empty($short_code.object) || in_array($typeObj, explode(',', $short_code.object))}
						<span class="ets_abancart_short_code group_{$short_code.group|escape:'html':'UTF-8'} {$id_short_code|escape:'html':'UTF-8'}">
							<button type="button"
									class="btn btn-outline-primary sensitive ets_abancart_btn_short_code"
									data-short-code="[{$id_short_code|escape:'html':'UTF-8'}{if $id_short_code == 'lead_form'} id=1{elseif $id_short_code == 'product_grid'} id=&quot;&quot{elseif $id_short_code == 'custom_button'} href=&quot;#&quot; text=&quot;{l s='Click here'  mod='ets_abandonedcart'}&quot;{/if}]"><i
										class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
									<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1344 960v-128q0-26-19-45t-45-19h-256v-256q0-26-19-45t-45-19h-128q-26 0-45 19t-19 45v256h-256q-26 0-45 19t-19 45v128q0 26 19 45t45 19h256v256q0 26 19 45t45 19h128q26 0 45-19t19-45v-256h256q26 0 45-19t19-45zm320-64q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/></svg>
								</i> [{$short_code.name nofilter}]</button>
						</span>
                    {/if}
                {/foreach}
            {/if}
		</p>
	{elseif isset($input.specific_product) && $input.specific_product}
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
	{elseif isset($input.search_product) && $input.search_product}
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
	{elseif $input.name == 'product_gift'}
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
    			<input type="hidden" name="gift_product" value="{if isset($fields_value.gift_product)}{$fields_value.gift_product|escape:'html':'UTF-8'}{/if}" />
    			<input type="hidden" name="gift_product_attribute" value="{if isset($fields_value.gift_product_attribute)}{$fields_value.gift_product_attribute|escape:'html':'UTF-8'}{/if}" />
    			<span class="input-group-addon"><i class="ets_svg_fill_gray lh_16">
						<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1216 832q0-185-131.5-316.5t-316.5-131.5-316.5 131.5-131.5 316.5 131.5 316.5 316.5 131.5 316.5-131.5 131.5-316.5zm512 832q0 52-38 90t-90 38q-54 0-90-38l-343-342q-179 124-399 124-143 0-273.5-55.5t-225-150-150-225-55.5-273.5 55.5-273.5 150-225 225-150 273.5-55.5 273.5 55.5 225 150 150 225 55.5 273.5q0 220-124 399l343 343q37 37 37 90z"/></svg>
					</i></span>
    		</div>
		</div>
	{elseif $input.name == 'label_email_timing_option'}
		{if isset($input.list_title) && $input.list_title}
			{foreach $input.list_title as $kt=>$tt}
				<p class="label_email_timing_option {$kt|escape:'html':'UTF-8'}">{$tt|escape:'html':'UTF-8'}</p>
			{/foreach}
		{/if}
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
	{elseif $input.name == 'minute' || $input.name == 'hour' || $input.name == 'second' || $input.name == 'day' || $input.name == 'redisplay'}
		{$smarty.block.parent}
		<div class="ets-ac-range-time-tool ets_range_input">
			<div class="range-wrap ets-ac-range-input">
                <div class="ets_range_input_slide">
                    <span class="range-bubble-bar"></span>
                </div>
				<div class="range-bubble"></div>
				<input type="range" class="range for-target-name" data-name-target="{$input.name|escape:'html':'UTF-8'}" name="range_{$input.name|escape:'html':'UTF-8'}"
					   data-unit="" min="0" max="{if $input.name == 'hour'}24{elseif $input.name == 'day'}31{else}60{/if}" step="{if $input.name == 'second'}1{else}0.01{/if}"
					   value="{if isset($fields_value[$input.name]) && $fields_value[$input.name] }{$fields_value[$input.name]|escape:'html':'UTF-8'}{else}0{/if}" />
				<div class="range_title">
					<span class="min-number">0</span>
					<span class="max-number">{if $input.name == 'hour'}24{elseif $input.name == 'day'}31{else}60{/if}</span>
				</div>
			</div>
			{if !empty($input.desc)}<p class="help-block">{$input.desc nofilter}</p>{/if}
			<p class="text-muted">{l s='Click' mod='ets_abandonedcart'} <a href="#" class="ets-ac-hide-range-time-tool">{l s='here' mod='ets_abandonedcart'}</a> {l s='to custom time' mod='ets_abandonedcart'}</p>
		</div>
    {else}
		{$smarty.block.parent}
	{/if}
{/block}
{block name="input_row"}
	{assign var='isCampaignObj' value=preg_match('#Reminder(Email|Popup|Bar|Browser|Customer)|Cart$#', $controller_name)}
	{assign var='reminderType' value=preg_replace('#^'|cat:$slugTab|cat:'(?:Reminder)?(\w+)$#', '$1', $controller_name)|lower}
    {if $isCampaignObj && $reminderType}
        {assign var="reminder_name" value=$reminderType|ucfirst}
        {if $reminderType=='bar'}
            {assign var="reminder_name" value="{l s='Highlight bar' mod='ets_abandonedcart'}"}
        {elseif $reminderType=='browser'}
            {assign var="reminder_name" value="{l s='Web push notification' mod='ets_abandonedcart'}"}
        {elseif $reminderType=='customer'}
            {assign var="reminder_name" value="{l s='Customer' mod='ets_abandonedcart'}"}
        {elseif $reminderType=='cart'}
            {assign var="reminder_name" value="{l s='Manually abandoned cart emails' mod='ets_abandonedcart'}"}
        {/if}
    {/if}
	{if !$isCampaignObj && $input.name=='email_content'}
		<div class="ets-ac-content-design-tab">
			<div class="tab-menu-item active" data-tab="content">{l s='Content' mod='ets_abandonedcart'}</div>
			<div class="tab-menu-item" data-tab="design">{l s='Settings' mod='ets_abandonedcart'}</div>
		</div>
	{/if}
	{if $reminderType == 'customer' && $input.name == 'email_timing_option'}
		<div class="form-group abancart form_frequency">
			<div class="control-label col-lg-3">&nbsp;</div>
			<div class="col-lg-6">
				<p class="alert alert-info">{l s='When to send email reminder to customer since they created their account?' mod='ets_abandonedcart'}</p>
			</div>
		</div>
	{/if}
    {if $input.name != 'available_to' && $input.name != 'last_order_to' && $input.name != 'max_total_order' && $input.name != 'max_total_cart' && $input.name != 'id_currency' && $input.name != 'reduction_tax' && $input.name != 'content'}
		{if $reminderType != 'customer' && $input.name == 'discount_option'}
			<div class="form-group abancart form_discount">
				<div class="control-label col-lg-3">&nbsp;</div>
				<div class="col-lg-6">
				    <p class="alert alert-info">{l s='You may want to give customer discount in order to encourage them to make their order?' mod='ets_abandonedcart'}</p>
				</div>
			</div>
        {/if}
        {$smarty.block.parent}
		{if $input.name|trim=='enabled' && $controller_name|trim=='AdminEtsACCart'}
			<div class="form-group abancart form_confirm_information ets_abancart_display_confirm">
				<div class="content-confirm"></div>
			</div>
		{/if}
    {/if}
    {if $isCampaignObj && $input.name|trim == 'hidden_reminder_id'}
		{if in_array($reminderType, array('email', 'cart', 'customer'))}
			<div class="form-group abancart form_select_template isSelectedTemp">
				<div class="ets_abancart_title alert alert-info">{l s='Select an email template you prefer' mod='ets_abandonedcart'}</div>
				<ul class="ets_abancart_template_ul">
					{if !empty($email_templates)}
						{foreach from=$email_templates item='template'}
							<li class="ets_abancart_template_li item{$template.id_ets_abancart_email_template|intval}{if !empty($fields_value['id_ets_abancart_email_template']) && $fields_value['id_ets_abancart_email_template'] == $template.id_ets_abancart_email_template} active{/if}"
								data-id="{$template.id_ets_abancart_email_template|intval}"
								data-type-of="{$template.type_of_campaign|escape:'quotes':'UTF-8'}">
								<div class="ets_abancart_template_li_img" {if $template.thumbnail} style="background-image:url('{$template.thumbnail_url|escape:'quotes':'UTF-8'}');"{/if}>
									{if $template.thumbnail}
										<div class="ets_abancart_lookup">
											<i class="ets_svg_fill_gray lh_16">
												<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1088 800v64q0 13-9.5 22.5t-22.5 9.5h-224v224q0 13-9.5 22.5t-22.5 9.5h-64q-13 0-22.5-9.5t-9.5-22.5v-224h-224q-13 0-22.5-9.5t-9.5-22.5v-64q0-13 9.5-22.5t22.5-9.5h224v-224q0-13 9.5-22.5t22.5-9.5h64q13 0 22.5 9.5t9.5 22.5v224h224q13 0 22.5 9.5t9.5 22.5zm128 32q0-185-131.5-316.5t-316.5-131.5-316.5 131.5-131.5 316.5 131.5 316.5 316.5 131.5 316.5-131.5 131.5-316.5zm512 832q0 53-37.5 90.5t-90.5 37.5q-54 0-90-38l-343-342q-179 124-399 124-143 0-273.5-55.5t-225-150-150-225-55.5-273.5 55.5-273.5 150-225 225-150 273.5-55.5 273.5 55.5 225 150 150 225 55.5 273.5q0 220-124 399l343 343q37 37 37 90z"/></svg>
											</i>
											<div class="ets_abancart_lookup_content"><img src="{$template.thumbnail_url|escape:'quotes':'UTF-8'}"/></div>
										</div>
									{/if}
								</div>
								<span>{$template.name|escape:'html':'UTF-8'}</span>
							</li>
						{/foreach}
					{/if}
					<li class="ets_abancart_template_li item0{if empty($fields_value['id_ets_abancart_email_template'])} active{/if}" data-id="0" data-type-of="both">
						<div class="ets_abancart_template_li_img"></div>
						<span>{l s='Blank template' mod='ets_abandonedcart'}</span>
					</li>
				</ul>
			</div>
		{/if}
		<div class="form-group abancart form_message campaign_type_{$reminderType|escape:'html':'UTF-8'}">
			<div class="ets_abancart_form_group_left col-lg-6 col-md-6">
				<h4 class="ets_abancart_title">
					{if isset($reminderType) && ($reminderType=='cart' || $reminderType=='customer' || $reminderType=='email')}
						{l s='Email content' mod='ets_abandonedcart'}
					{else}
						{l s='%s template' sprintf = [$reminder_name] mod='ets_abandonedcart'}
					{/if}
				</h4>
				{if $reminderType == 'popup' || $reminderType == 'bar' || !$isCampaignObj}
					<div class="ets-ac-content-design-tab">
						<div class="tab-menu-item active" data-tab="content">{l s='Content' mod='ets_abandonedcart'}</div>
						<div class="tab-menu-item" data-tab="design">{l s='Design' mod='ets_abandonedcart'}</div>
					</div>
				{/if}
    {/if}
	{if $isCampaignObj && $input.name == 'content'}
		{$smarty.block.parent}
			</div>
			<div class="ets_abancart_form_group_right col-lg-6 col-md-6">
				<h4 class="ets_abancart_title">
					{if isset($reminderType) && ($reminderType=='cart' || $reminderType=='customer' || $reminderType=='email')}
						{l s='Email preview' mod='ets_abandonedcart'}
					{else}
						{l s='%s preview' sprintf = [$reminder_name] mod='ets_abandonedcart'}
					{/if}
				</h4>
				<div class="ets_abancart_responsive_mode">
					<ul>
						<li><a data-respon="desktop_mode" href="#" class="desktop_mode active">
								<i class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
									<svg width="16" height="14" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1856 992v-832q0-13-9.5-22.5t-22.5-9.5h-1600q-13 0-22.5 9.5t-9.5 22.5v832q0 13 9.5 22.5t22.5 9.5h1600q13 0 22.5-9.5t9.5-22.5zm128-832v1088q0 66-47 113t-113 47h-544q0 37 16 77.5t32 71 16 43.5q0 26-19 45t-45 19h-512q-26 0-45-19t-19-45q0-14 16-44t32-70 16-78h-544q-66 0-113-47t-47-113v-1088q0-66 47-113t113-47h1600q66 0 113 47t47 113z"/></svg>
								</i>  {l s='Desktop' mod='ets_abandonedcart'}</a></li>
						<li><a data-respon="tablet_mode" href="#">
								<i class="tablet_mode ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
									<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M960 1408q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm384-160v-960q0-13-9.5-22.5t-22.5-9.5h-832q-13 0-22.5 9.5t-9.5 22.5v960q0 13 9.5 22.5t22.5 9.5h832q13 0 22.5-9.5t9.5-22.5zm128-960v1088q0 66-47 113t-113 47h-832q-66 0-113-47t-47-113v-1088q0-66 47-113t113-47h832q66 0 113 47t47 113z"/></svg>
								</i> {l s='Tablet' mod='ets_abandonedcart'}</a></li>
						<li><a data-respon="mobile_mode" href="#">
								<i class="mobile_mode ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
									<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M976 1408q0-33-23.5-56.5t-56.5-23.5-56.5 23.5-23.5 56.5 23.5 56.5 56.5 23.5 56.5-23.5 23.5-56.5zm208-160v-704q0-13-9.5-22.5t-22.5-9.5h-512q-13 0-22.5 9.5t-9.5 22.5v704q0 13 9.5 22.5t22.5 9.5h512q13 0 22.5-9.5t9.5-22.5zm-192-848q0-16-16-16h-160q-16 0-16 16t16 16h160q16 0 16-16zm288-16v1024q0 52-38 90t-90 38h-512q-52 0-90-38t-38-90v-1024q0-52 38-90t90-38h512q52 0 90 38t38 90z"/></svg>
								</i> {l s='Mobile' mod='ets_abandonedcart'}</a></li>
					</ul>
				</div>
				{assign var="isPopupOrBrowser" value=($reminderType=='popup' || $reminderType=='browser')}
				<div class="ets_abancart_preview_info {$reminderType|escape:'html':'UTF-8'}" id="ets_abancart_campaign_type" data-type="{$reminderType|escape:'html':'UTF-8'}">
					<div class="ets_abancart_preview_content_view">
						{if $isPopupOrBrowser}
							{if $reminderType=='popup'}
								<div class="ets_abancart_preview_title"></div>
							{/if}
						{/if}
					{if $reminderType=='browser'}
						{if isset($fields_value['icon_notify']) && $fields_value['icon_notify']}
							<img class="ets_abancart_image" src="{$image_url|cat: $fields_value['icon_notify']|escape:'html':'UTF-8'}"/>
						{/if}
						<div class="ets_abancart_content">
							<div class="ets_abancart_preview_title"></div>
					{/if}
							<div class="ets_abancart_preview"></div>
					{if $reminderType == 'browser'}
						</div>
					{/if}
					</div>
				</div>
				<div class="alert alert-info">
					{if in_array($reminderType, array('email', 'customer', 'cart'))}
						{l s='Customers will receive an email with the same content like this template. Please keep in mind that all the values such as logo, discount information, etc. are just demo data for reference.' mod='ets_abandonedcart'}
					{elseif in_array($reminderType, array('popup', 'bar', 'browser'))}
						{l s='Customers will see a popup with the same content like this template. Please keep in mind that all the values such as logo, discount information, etc. are just demo data for reference.' mod='ets_abandonedcart'}
					{/if}
				</div>
				{if $reminderType === 'email' || $reminderType === 'customer' || $reminderType === 'cart'}
					<button class="btn btn-default" type="button" name="sendTestMail"><i class="icon-envelope"></i>&nbsp;{l s='Send test mail' mod='ets_abandonedcart'}</button>
				{/if}
			</div>
		</div>
	{/if}
	{if $input.name == 'content2'}
		{if isset($emailTimingOption) && $emailTimingOption}
			<div class="ets_abancart_email_timing_option">
				<input type="hidden" name="email_timing_option" id="email_timing_option" value="{$emailTimingOption|escape:'html':'UTF-8'}" />
			</div>
		{/if}
		<div class="form-group abancart form_lead_form">
			{if isset($lead_forms)}
			{foreach $lead_forms as $f}
				{if isset($f.fields) && $f.fields}
					<div class="ets-ac-lead-form-field-item hide" data-id="{$f.id_ets_abancart_form|escape:'html':'UTF-8'}" data-enable="{$f.enable|escape:'html':'UTF-8'}">
						{if isset($is17Ac) && $is17Ac}
							{include './../../../../hook/lead_form_short_code.tpl' lead_form=$f field_types=$field_types reminderType=$reminderType isAdmin=1 maxSizeUpload=$maxSizeUpload}
						{else}
							{include './../../../../hook/lead_form_short_code.tpl' lead_form=$f field_types=$field_types reminderType=$reminderType isAdmin=1 maxSizeUpload=$maxSizeUpload}
						{/if}
					</div>
				{/if}
			{/foreach}
			{/if}
		</div>
	{/if}
	{if $input.type == 'confirm_info'}
		{$input.html nofilter}
	{/if}
	{if $input.type=='default_content'}
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
{block name="description"}
	{if $input.name == 'minute' || $input.name == 'hour' || $input.name == 'second' || $input.name == 'day' || $input.name == 'redisplay'}
		<p class="help-block">{if isset($input.desc) && $input.desc}{$input.desc nofilter}{/if} <a href="#" class="ets-ac-show-range-time-tool">{l s='Display time range' mod='ets_abandonedcart'}</a> </p>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
{block name="footer"}
	{assign var='isCampaignObj' value=preg_match('#Reminder(Email|Popup|Bar|Browser|Customer)|Cart$#', $controller_name)}
	{assign var='reminderType' value=preg_replace('#^'|cat:$slugTab|cat:'(?:Reminder)?(\w+)$#', '$1', $controller_name)|lower}
    {if $isCampaignObj}
		<div class="panel-footer">
			<button class="btn btn-default pull-left ets_ac_btn_step_back" name="back{$reminderType|ucfirst|escape:'html':'UTF-8'}" type="button" disabled><i class="icon-long-arrow-left"></i>&nbsp;{l s='Back' mod='ets_abandonedcart'}</button>
			<button class="btn btn-primary pull-right ets_ac_btn_step_continue" name="continue{$reminderType|ucfirst|escape:'html':'UTF-8'}" type="button">{l s='Continue' mod='ets_abandonedcart'}&nbsp;<i class="icon-long-arrow-right"></i></button>
			<button class="btn btn-primary pull-right hide ets_ac_btn_step_save finish" name="finishStepAndRun"
					{if $controller_name|trim !== 'AdminEtsACCart'}
						data-no-send-mail="{l s='Save draft' mod='ets_abandonedcart'}"
						data-send-mail="{if isset($emailTimingOption) && $emailTimingOption == 3}{l s='Save and waiting' mod='ets_abandonedcart'}{else}{l s='Save and run now' mod='ets_abandonedcart'}{/if}"
					{/if}
			        type="button">
				{if $controller_name|trim == 'AdminEtsACCart'}{l s='Send' mod='ets_abandonedcart'}{elseif isset($enabled) && !$enabled}{l s='Save draft' mod='ets_abandonedcart'}{else}{if isset($emailTimingOption) && $emailTimingOption|intval == 3}{l s='Save and waiting' mod='ets_abandonedcart'}{else}{l s='Save and run now' mod='ets_abandonedcart'}{/if}{/if}
			</button>
		</div>
    {else}
		{$smarty.block.parent}
	{/if}
	{if $controller_name|trim == 'AdminEtsACCart' && isset($id_lang_default) && $id_lang_default|intval > 0}
		<input type="hidden" id="ETS_ABANCART_LANG_DEFAULT" name="ETS_ABANCART_LANG_DEFAULT" value="{$id_lang_default|intval}"/>
		<input type="hidden" id="PS_LANG_DEFAULT" name="PS_LANG_DEFAULT" value="{$PS_LANG_DEFAULT|intval}"/>
	{/if}
{/block}
{block name="after"}
    {if $reminderType === 'email' || $reminderType === 'customer' || $reminderType === 'cart' || $smarty.get.controller == 'AdminEtsACEmailTemplate'}
        {hook h='displayBoFormTestMail'}
    {/if}
{/block}
{block name="autoload_tinyMCE"}
	if (typeof tinyMCE !== "undefined") {
	    var tinyMCEs = document.getElementsByClassName("autoload_rte");
	    for (var i = 0; i < tinyMCEs.length; i++) {
	        tinyMCEs[i].id = tinyMCEs[i].name + Math.floor(Math.random() * (100000 - 1000 + 1)) + 1000;
	    }
	}
	tinySetup({
	    editor_selector: 'autoload_rte',
		verify_html: false,
		force_br_newlines : true,
		force_p_newlines : false,
		forced_root_block : '',
	    setup: function (ed) {
	        ed.on('keyup change blur', function (ed) {
	            tinyMCE.triggerSave();
	            ets_ab_fn.previewLanguage();
	            if ($('.ets_abancart_overload.active').length > 0)
	                ets_ab_fn.prevNext();
	        });
	        ed.on('change', function (ed) {
	            if (!ets_abancart_textarea_changed && ets_abancart_tab_message_active) ets_abancart_textarea_changed = true;
	        });
	    },
	    resize: false,
	    min_height: 350,
	});
{/block}