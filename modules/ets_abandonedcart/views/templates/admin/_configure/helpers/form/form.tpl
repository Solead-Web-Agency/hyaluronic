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
	{* Defines *}
	{assign var="isTypeCampaign" value=isset($type) && $type}
    {assign var="isCampaignObj" value=isset($entity) && in_array($entity, array('reminder', 'cart'))}

	{* Menus *}
    {if $isCampaignObj && isset($menus)}
	    <div class="panel-heading">

			{* Nav *}
			<div class="ets_abancart_menus pull-left ">
			    <ul class="ets_abancart_nav_tabs">
                    {foreach from=$menus key='id_menu' item ='menu'}
					    <li class="ets_abancart_tab_item" data-tab="{$id_menu|escape:'html':'UTF-8'}">{if $menu.icon}<i class="{$menu.icon|escape:'html':'UTF-8'}"></i>{/if}&nbsp;
                            {if $id_menu == 'message' && $type!='email' && isset($menu.reminder_type) && $menu.reminder_type|count > 0}
                                {$menu.reminder_type.$type|escape:'quotes':'UTF-8'}
                            {else}{$menu.label|escape:'html':'UTF-8'}{/if}
					    </li>
                    {/foreach}
			    </ul>
		    </div>

			{* Button back *}
		    <div class="ets_abancart_buttons pull-right">
			    <button class="btn btn-default" name="{$entity|lower|escape:'html':'UTF-8'}BackToCampaign" type="button"><i class="icon-long-arrow-left"></i>&nbsp;{if $entity != 'cart'}{l s='Back to campaign' mod='ets_abandonedcart'}{else}{l s='Back to Abandoned carts' mod='ets_abandonedcart'}{/if}</button>
			    <button class="btn btn-default" name="save{$entity|ucfirst|escape:'html':'UTF-8'}" type="button"><i class="icon-save"></i> {if $entity != 'cart'}{l s='Save' mod='ets_abandonedcart'}{else}{l s='Send email' mod='ets_abandonedcart'}{/if}</button>
		    </div>

	    </div>
	    <div class="clearfix"></div>
    {else}{$smarty.block.parent}{/if}
{/block}
{block name="input"}
	{assign var="isTypeCampaign" value=isset($type) && $type}
	{assign var="isCampaignObj" value=isset($entity) && in_array($entity, array('reminder', 'cart'))}
    {if $input.type == 'abancart_group'}
	    {assign var=groups value=$input.values}
	    {if $groups}
			<div class="row">
				<div class="col-lg-6">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="fixed-width-xs"><span class="title_box"><input type="checkbox" class="all_abancart_group" name="{$input.name|escape:'html':'UTF-8'}[]" id="all_abancart_group" onclick="checkDelBoxes(this.form, '{$input.name|escape:'html':'UTF-8'}[]', this.checked)" value="ALL"/></span></th>
								<th><label for="all_abancart_group" class="title_box">{l s='All' mod='ets_abandonedcart'}</label></th>
							</tr>
						</thead>
						<tbody>
	                    {if isset($groups.query) && $groups.query}{foreach $groups.query as $group}
	                        {if !empty($groups.id)}{assign var='id_group' value=$group[$groups.id]}{else}{assign var='id_group' value=$group.id_group}{/if}
	                        {if isset($id_group) && $id_group}<tr>
								<td><input type="checkbox" name="{$input.name|escape:'html':'UTF-8'}[]" class="groupBox abancart_group" id="{$input.name|escape:'html':'UTF-8'}_{$id_group|escape:'quotes':'UTF-8'}" value="{$id_group|escape:'quotes':'UTF-8'}" {if !empty($fields_value[$input.name]) && is_array($fields_value[$input.name]) && in_array($id_group, $fields_value[$input.name]) || $fields_value[$input.name]=='all'}checked="checked"{/if}/></td>
								<td><label for="{$input.name|escape:'html':'UTF-8'}_{$id_group|escape:'quotes':'UTF-8'}">{$group.name|escape:'html':'UTF-8'}</label></td>
							</tr>{/if}
	                    {/foreach}{/if}
						</tbody>
					</table>
				</div>
			</div>
	    {else}
            <p>{l s='No group created' mod='ets_abandonedcart'}</p>
        {/if}
	{elseif $input.name == 'available_from'}
		<div class="row">
			<div class="col-lg-3">
				<div class="input-group">
					<span class="input-group-addon">{l s='From' mod='ets_abandonedcart'}</span>
					<input type="text" class="datepicker input-medium" name="available_from" list="autocompleteOff" autocomplete="off" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}" />
					<span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="input-group">
					<span class="input-group-addon">{l s='To' mod='ets_abandonedcart'}</span>
					<input type="text" class="datepicker input-medium" name="available_to" list="autocompleteOff" autocomplete="off" value="{if isset($fields_value['available_to'])}{$fields_value['available_to']|escape:'html':'UTF-8'}{/if}" />
					<span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
				</div>
			</div>
		</div>
	{elseif $input.name == 'min_total_cart'}
		<div class="row">
			<div class="col-lg-3">
				<div class="input-group">
					<span class="input-group-addon">{l s='From' mod='ets_abandonedcart'}</span>
					<input type="text" class="" name="min_total_cart" value="{if isset($fields_value[$input.name]) && $fields_value[$input.name] != ''}{$fields_value[$input.name]|round:2|escape:'html':'UTF-8'}{/if}" />
					<span class="input-group-addon">{$input.suffix|escape:'html':'UTF-8'}</span>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="input-group">
					<span class="input-group-addon">{l s='To' mod='ets_abandonedcart'}</span>
					<input type="text" class="" name="max_total_cart" value="{if isset($fields_value['max_total_cart']) && $fields_value['max_total_cart'] != ''}{$fields_value['max_total_cart']|round:2|escape:'html':'UTF-8'}{/if}" />
					<span class="input-group-addon">{$input.suffix|escape:'html':'UTF-8'}</span>
				</div>
			</div>
		</div>
    {elseif $input.name == 'reduction_amount'}
	    <div class="row">
		    {if $input.name == 'reduction_amount'}<div class="col-lg-4">
			    <input type="text" id="{$input.name|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|floatval}{/if}" onchange="this.value = this.value.replace(/,/g, '.');">
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
    {elseif $input.name == 'ETS_ABANCART_REDUCTION_AMOUNT'}
	    <div class="row">
            {if $input.name == 'ETS_ABANCART_REDUCTION_AMOUNT'}<div class="col-lg-4">
			    <input type="text" id="{$input.name|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|floatval}{/if}" onchange="this.value = this.value.replace(/,/g, '.');">
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
				    <li class="ets_abancart_{$input.name|escape:'html':'UTF-8'}{if isset($option.class) && $option.class} {$option.class|escape:'html':'UTF-8'}{/if}" style="list-style: none; padding-bottom: 5px">
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
					    <label for="{$input.name|cat:'_'|cat:$option.id_option|escape:'html':'UTF-8'}">{$option.name|escape:'html':'UTF-8'}{if isset($option.cart_rule_link) && $option.cart_rule_link} <a target="_blank" href="{$option.cart_rule_link|escape:'quotes':'UTF-8'}">{l s='Configure discounts' mod='ets_abandonedcart'}</a>{/if}</label>
				    </li>
                {/foreach}
		    </ul>
        {/if}
    {elseif !empty($entity) && ($input.name == 'content' || $input.name == 'email_content' || $input.name == 'ETS_ABANCART_CONTENT')}
        {$smarty.block.parent}

		{* get Type Object *}
		{if isset($input.desc_type) && $input.desc_type}
			{assign var='typeObj' value=$input.desc_type}
	    {elseif $input.name == 'ETS_ABANCART_CONTENT'}
			{assign var='typeObj' value='leave'}
	    {else}
			{assign var='typeObj' value='cart' }
		{/if}
		{if isset($hasProductInCart) && $hasProductInCart !== 1}
			<input type="hidden" name="etsAcHasProductInCart" id="etsAcHasProductInCart" value="1">
		{/if}
		{* Shortcode *}
	    <p class="help-block">
            {if isset($short_codes) && $short_codes}
                {l s='Available tags' mod='ets_abandonedcart'} :
                {foreach from=$short_codes key='id_short_code' item='short_code'}
                    {if empty($short_code.object) || in_array($typeObj, explode(',', $short_code.object))}
                        <span class="ets_abancart_short_code group_{$short_code.group|escape:'html':'UTF-8'} {$id_short_code|escape:'html':'UTF-8'}">
                            <button type="button" class="btn btn-outline-primary sensitive ets_abancart_btn_short_code" data-short-code="[{$id_short_code|escape:'html':'UTF-8'}{if $id_short_code == 'lead_form'} id=1{elseif $id_short_code == 'product_grid'} id=&quot;&quot{elseif $id_short_code == 'custom_button'} href=&quot;#&quot; text=&quot;{l s='Click here'  mod='ets_abandonedcart'}&quot;{/if}]"><i class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
									<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1344 960v-128q0-26-19-45t-45-19h-256v-256q0-26-19-45t-45-19h-128q-26 0-45 19t-19 45v256h-256q-26 0-45 19t-19 45v128q0 26 19 45t45 19h256v256q0 26 19 45t45 19h128q26 0 45-19t19-45v-256h256q26 0 45-19t19-45zm320-64q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/></svg>
								</i> [{$short_code.name|escape:'html':'UTF-8'}]</button>
                        </span>
                    {/if}
                {/foreach}
            {/if}
	    </p>
    {else}{$smarty.block.parent}{/if}
{/block}

{block name="input_row"}
	{assign var="isTypeCampaign" value=isset($type) && $type}
	{assign var="isCampaignObj" value=isset($entity) && in_array($entity, array('reminder', 'cart'))}
	{if $input.name == 'ETS_ABANCART_CRONJOB_EMAILS'}
		<div class="alert alert-info">
			<h4>{l s='* Some important notes:' mod='ets_abandonedcart'}</h4>
			<ul>
				<li>{l s='Cronjob frequency should be at least twice per day, the recommended frequency is ' mod='ets_abandonedcart'}<b>{l s='once per hour' mod='ets_abandonedcart'}</b></li>
				<li>{l s='How to set up a cronjob is different depending on your server. If you are using a Cpanel hosting, watch this video for reference: ' mod='ets_abandonedcart'} <a target="_blank" href="https://www.youtube.com/watch?v=bmBjg1nD5yA" rel="noreferrer noopener">https://www.youtube.com/watch?v=bmBjg1nD5yA</a><br />
                    {l s='You can also contact your hosting provider to ask them for support on setting up the cronjob' mod='ets_abandonedcart'}</li>
				<li>{l s='Web push notification only works on Chrome and Firefox (and some other modern web browsers) when HTTPS is enabled' mod='ets_abandonedcart'}</li>
				<li>{l s='Load pre-made email and popup templates then edit with your own texts to save time. Do not delete initial templates, save them for future uses ' mod='ets_abandonedcart'}</li>
				<li>{l s='Configure SMTP for your website (instead of using default PHP mail() function) to send email better. If you can afford, buy professional marketing email hosting to send a large number of emails' mod='ets_abandonedcart'}</li>
			</ul>
		</div>
	{/if}

    {if $input.name != 'available_to' && $input.name != 'last_order_to' && $input.name != 'max_total_cart' && $input.name != 'id_currency' && $input.name != 'reduction_tax'
        && $input.name != 'ETS_ABANCART_ID_CURRENCY' && $input.name != 'ETS_ABANCART_REDUCTION_TAX' && $input.name != 'ETS_ABANCART_HOURS' && $input.name != 'ETS_ABANCART_MINUTES' && $input.name != 'ETS_ABANCART_SECONDS'}
	    {$smarty.block.parent}
    {/if}

	{if $input.name=='ETS_ABANCART_HOURS'}
		<div class="form-group{if $input.form_group_class} {$input.form_group_class|escape:'html':'UTF-8'}{/if}">
			<label class="control-label col-lg-3 required">{l s='Display a reminder message to suggest customers to save their shopping cart if they have not checkout after' mod='ets_abandonedcart'}</label>
			<div class="col-lg-9">
				<div class="row">
					<div class="col-lg-2">
						<div class="input-group">
							<input type="text" class="" name="ETS_ABANCART_HOURS" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}" />
							<span class="input-group-addon">{l s='Hour(s)' mod='ets_abandonedcart'}</span>
						</div>
					</div>
					<div class="col-lg-2">
						<div class="input-group">
							<input type="text" class="" name="ETS_ABANCART_MINUTES" value="{if isset($fields_value['ETS_ABANCART_MINUTES'])}{$fields_value['ETS_ABANCART_MINUTES']|escape:'html':'UTF-8'}{/if}" />
							<span class="input-group-addon">{l s='Minute(s)' mod='ets_abandonedcart'}</span>
						</div>
					</div>
					<div class="col-lg-2">
						<div class="input-group">
							<input type="text" class="" name="ETS_ABANCART_SECONDS" value="{if isset($fields_value['ETS_ABANCART_SECONDS'])}{$fields_value['ETS_ABANCART_SECONDS']|escape:'html':'UTF-8'}{/if}" />
							<span class="input-group-addon">{l s='Second(s)' mod='ets_abandonedcart'}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	{/if}

    {if $isCampaignObj && in_array($input.name, array('enabled','id_cart'))}
    <div class="form-group abancart form_message{if $isTypeCampaign} campaign_type_{$type|escape:'html':'UTF-8'}{/if}">
		<div class="ets_abancart_form_group_left col-lg-6 col-md-6">
		<h4 class="ets_abancart_title">
			{if $isTypeCampaign}
				{if $type=='email'}{l s='Email templates' mod='ets_abandonedcart'}
				{elseif $type=='popup'}{l s='Popup content' mod='ets_abandonedcart'}
				{elseif $type=='bar'}{l s='Highlight bar template' mod='ets_abandonedcart'}
				{elseif $type=='browser'}{l s='Web push notification template' mod='ets_abandonedcart'}
				{elseif $type=='customer'}{l s='Email' mod='ets_abandonedcart'}
				{else}{l s='Email content' mod='ets_abandonedcart'}{/if}
			{/if}
		</h4>
    {/if}

	{if $isCampaignObj && $input.name=='content'}
		</div>
		<div class="ets_abancart_form_group_right col-lg-6 col-md-6">
			<h4 class="ets_abancart_title">
				{if $isTypeCampaign}
					{if $type=='email'}{l s='Email preview' mod='ets_abandonedcart'}
					{elseif $type=='popup'}{l s='Popup preview' mod='ets_abandonedcart'}
					{elseif $type=='bar'}{l s='Highlight bar preview' mod='ets_abandonedcart'}
					{elseif $type=='browser'}{l s='Web push notification preview' mod='ets_abandonedcart'}
					{elseif $type=='customer'}{l s='Email preview' mod='ets_abandonedcart'}
					{else}{l s='Email preview' mod='ets_abandonedcart'}{/if}
				{/if}
			</h4>
            <div class="ets_abancart_responsive_mode">
                <ul>
                    <li><a data-respon="desktop_mode" href="#" class="desktop_mode active">
							<i class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
								<svg width="16" height="14" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1856 992v-832q0-13-9.5-22.5t-22.5-9.5h-1600q-13 0-22.5 9.5t-9.5 22.5v832q0 13 9.5 22.5t22.5 9.5h1600q13 0 22.5-9.5t9.5-22.5zm128-832v1088q0 66-47 113t-113 47h-544q0 37 16 77.5t32 71 16 43.5q0 26-19 45t-45 19h-512q-26 0-45-19t-19-45q0-14 16-44t32-70 16-78h-544q-66 0-113-47t-47-113v-1088q0-66 47-113t113-47h1600q66 0 113 47t47 113z"/></svg>
							</i> {l s='Desktop' mod='ets_abandonedcart'}</a>
					</li>
                    <li><a data-respon="tablet_mode" href="#">
							<i class="tablet_mode ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
								<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M960 1408q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm384-160v-960q0-13-9.5-22.5t-22.5-9.5h-832q-13 0-22.5 9.5t-9.5 22.5v960q0 13 9.5 22.5t22.5 9.5h832q13 0 22.5-9.5t9.5-22.5zm128-960v1088q0 66-47 113t-113 47h-832q-66 0-113-47t-47-113v-1088q0-66 47-113t113-47h832q66 0 113 47t47 113z"/></svg>
							</i> {l s='Tablet' mod='ets_abandonedcart'}</a>
					</li>
                    <li><a data-respon="mobile_mode" href="#">
							<i class="mobile_mode ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
								<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M976 1408q0-33-23.5-56.5t-56.5-23.5-56.5 23.5-23.5 56.5 23.5 56.5 56.5 23.5 56.5-23.5 23.5-56.5zm208-160v-704q0-13-9.5-22.5t-22.5-9.5h-512q-13 0-22.5 9.5t-9.5 22.5v704q0 13 9.5 22.5t22.5 9.5h512q13 0 22.5-9.5t9.5-22.5zm-192-848q0-16-16-16h-160q-16 0-16 16t16 16h160q16 0 16-16zm288-16v1024q0 52-38 90t-90 38h-512q-52 0-90-38t-38-90v-1024q0-52 38-90t90-38h512q52 0 90 38t38 90z"/></svg>
							</i> {l s='Mobile' mod='ets_abandonedcart'}</a>
					</li>
                </ul>
            </div>
			{assign var="isPopupOrBrowser" value=$isTypeCampaign && ($type=='popup' || $type=='browser')}
				<div class="ets_abancart_preview_info {$type|escape:'html':'UTF-8'}">
					{if $isPopupOrBrowser}
						{if $type=='popup'}<div class="ets_abancart_preview_title"></div>{/if}
					{/if}
					{if $type=='browser'}<div class="ets_abancart_preview_title"></div>{/if}
					<div class="ets_abancart_preview"></div>
				</div>

			<div class="alert alert-info">{l s='Customers will see a popup with the same content like this template. Please keep in mind that all the values such as logo, discount information, etc. are just demo data for reference.' mod='ets_abandonedcart'}</div>
		</div>
	</div>
	{if $isTypeCampaign && in_array($type, array('email', 'cart', 'customer'))}
		<div class="form-group abancart form_select_template ">
			<div class="ets_abancart_title alert alert-info">{l s='Select an email template you prefer' mod='ets_abandonedcart'}</div>
			<ul class="ets_abancart_template_ul">
				{if !empty($email_templates)}{foreach from=$email_templates item='template'}
					<li class="ets_abancart_template_li item{$template.id_ets_abancart_email_template|intval}{if !empty($fields_value['id_ets_abancart_email_template']) && $fields_value['id_ets_abancart_email_template'] == $template.id_ets_abancart_email_template} active{/if}" data-id="{$template.id_ets_abancart_email_template|intval}">
						<div class="ets_abancart_template_li_img" {if $template.thumbnail} style="background-image:url('{$template.thumbnail_url|escape:'quotes':'UTF-8'}');"{/if}>
							{if $template.thumbnail}
							<div class="ets_abancart_lookup">
								<i class="ets_svg_fill_gray lh_16">
									<svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1088 800v64q0 13-9.5 22.5t-22.5 9.5h-224v224q0 13-9.5 22.5t-22.5 9.5h-64q-13 0-22.5-9.5t-9.5-22.5v-224h-224q-13 0-22.5-9.5t-9.5-22.5v-64q0-13 9.5-22.5t22.5-9.5h224v-224q0-13 9.5-22.5t22.5-9.5h64q13 0 22.5 9.5t9.5 22.5v224h224q13 0 22.5 9.5t9.5 22.5zm128 32q0-185-131.5-316.5t-316.5-131.5-316.5 131.5-131.5 316.5 131.5 316.5 316.5 131.5 316.5-131.5 131.5-316.5zm512 832q0 53-37.5 90.5t-90.5 37.5q-54 0-90-38l-343-342q-179 124-399 124-143 0-273.5-55.5t-225-150-150-225-55.5-273.5 55.5-273.5 150-225 225-150 273.5-55.5 273.5 55.5 225 150 150 225 55.5 273.5q0 220-124 399l343 343q37 37 37 90z"/></svg>
								</i>
								<div class="ets_abancart_lookup_content"><img src="{$template.thumbnail_url|escape:'quotes':'UTF-8'}" /></div>
							</div>
							{/if}
						</div>
						<span>{$template.name|escape:'html':'UTF-8'}</span>
					</li>
				{/foreach}{/if}
				<li class="ets_abancart_template_li item0{if empty($fields_value['id_ets_abancart_email_template'])} active{/if}" data-id="0">
					<div class="ets_abancart_template_li_img"></div>
					<span>{l s='Blank template' mod='ets_abandonedcart'}</span>
				</li>
			</ul>
		</div>{/if}
	{/if}

	{if $input.name=='ETS_ABANCART_SECURE_TOKEN'}
		<div class="ets_abancart_help">
			<p><span class="required">*</span> {l s='Setup a cronjob as below on your server to send email reminders automatically' mod='ets_abandonedcart'}</p>
			<em><span id="ets_abd_cronjob_path">{$path|escape:'quotes':'UTF-8'}</span></em>
			<p><span class="required">*</span> {l s='Manually send emails to customers by running the following URL on your web browser' mod='ets_abandonedcart'}</p>
			<a id="ets_abd_cronjob_link" href="{$url|escape:'quotes':'UTF-8'}" target="_blank">{$url|escape:'quotes':'UTF-8'}</a>
		</div>
	{/if}
{/block}

{block name="footer"}
	{assign var="isTypeCampaign" value=isset($type) && $type}
	{assign var="isCampaignObj" value=isset($entity) && in_array($entity, array('reminder', 'cart'))}
	{if isset($isCampaignObj) && $isCampaignObj}
		<div class="panel-footer">
			<button class="btn btn-default pull-left" name="back{$entity|ucfirst|escape:'html':'UTF-8'}" type="button" disabled><i class="icon-long-arrow-left"></i>&nbsp;{l s='Back' mod='ets_abandonedcart'}</button>
			<button class="btn btn-default pull-right" name="continue{$entity|ucfirst|escape:'html':'UTF-8'}" type="button">{l s='Continue' mod='ets_abandonedcart'}&nbsp;<i class="icon-long-arrow-right"></i></button>
		</div>
	{else}{$smarty.block.parent}{/if}

	{* POST entity *}
	{if isset($entity)}<input type="hidden" name="entity" value="{$entity|escape:'html':'UTF-8'}">{/if}
{/block}

{block name="autoload_tinyMCE"}
	tinySetup({
	    editor_selector: 'autoload_rte',
		force_br_newlines : true,
		force_p_newlines : false,
		forced_root_block : '',
	    setup: function (ed) {
	        ed.on('keyup change blur', function (ed) {
	            //tinyMCE.triggerSave();
	            ets_ab_fn.previewLanguage();
	            if ($('.ets_abancart_overload.active').length > 0)
	                ets_ab_fn.prevNext();
	            else
	                ed.save();
	        });
	        ed.on('change', function (ed) {
	            if (!ets_abancart_textarea_changed && ets_abancart_tab_message_active) ets_abancart_textarea_changed = true;
	        });
	    },
	    resize: false,
	    height: 350
	});
{/block}