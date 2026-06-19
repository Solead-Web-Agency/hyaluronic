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
{$header nofilter}
<div class="ets_abancart_wrapper">
	{block name='menu'}
		{if isset($menus) && $menus}
			<div class="aband_group_header_fixed">
				{assign var='_breadcrumb' value=''}
				<ul class="ets_abancart_menus aband_group_header">
					{foreach from=$menus key='id' item='menu'}
						<li class="ets_abancart_menu_li{if $controller_name|trim == $slugTab|cat:$menu.class|trim || $menu.class|trim === 'Campaign' && preg_match('#Reminder#', $controller_name) || $menu.class|trim === 'MailConfigs' && preg_match('#(Mail|Queue|Indexed|Unsubscribed)#', $controller_name) || $menu.class|trim === 'Tracking' && preg_match('#(EmailTracking|DisplayTracking|Discounts|DisplayLog)#', $controller_name)} active{/if}">
							{assign var='_breadcrumb_first' value=$id}
							{include file="./menu.tpl"}
							{if isset($menu.sub_menus) && $menu.sub_menus}
								<ul class="ets_abancart_sub_menus">
									{foreach from=$menu.sub_menus key='id' item='sub_menu'}
										<li class="ets_abancart_sub_menu_li{if $controller_name|trim === $slugTab|cat:$sub_menu.class|trim}{assign var='_breadcrumb' value=$_breadcrumb_first|cat:','|cat:$id} active{/if}">
											{include file="./menu.tpl" menu=$sub_menu}
										</li>
									{/foreach}
								</ul>
							{elseif $controller_name !== 'AdminEtsACDashboard' && $controller_name|trim == $slugTab|cat:$menu.class|trim}
								{assign var='_breadcrumb' value=$id}
								{assign var='onLv2' value=1}
							{/if}
						</li>
					{/foreach}
				</ul>
			</div>
			<div class="aban_menu_height"></div>
		{/if}
	{/block}
	{block name='breadcrumb'}
		{if isset($isModuleDisabled) && $isModuleDisabled}
			<div class="alert alert-warning">
				{l s='Please enable module to use the features of Abandoned Cart Reminder + Auto Email module' mod='ets_abandonedcart'}
			</div>
		{/if}

		{if $_breadcrumb || $controller_name == 'AdminEtsACCampaign'}
			<div class="ets_abancart_breadcrumb">
				<a href="{$link->getAdminLink($slugTab|cat:'Dashboard', true)|escape:'html':'UTF-8'}" title="{l s='Home' mod='ets_abandonedcart'}"><span class="breadcrumb"><i class="icon-home"></i></span></a>
				{assign var="dot" value=" > "}{$dot|escape:'html':'UTF-8'}
				{assign var='_breadcrumb' value=explode(',', $_breadcrumb)}{assign var="ik" value="0"}
				{if $controller_name !== 'AdminEtsACCampaign'}
					{foreach from=$_breadcrumb item='id'}
						{assign var="ik" value=$ik+1}
						{if isset($menus[$id]) && $menus[$id]}
							{assign var='menu' value=$menus[$id]}
							{if isset($onLv2) && $onLv2}
								{if isset($leadFormTitle) && $leadFormTitle}
									<a href="{$link->getAdminLink($slugTab|cat: $menu.class, true)|escape:'quotes':'UTF-8'}"><span class="breadcrumb">{$menu.label|escape:'html':'UTF-8'}</span></a>
								{else}
									<span class="breadcrumb">{$menu.label|escape:'html':'UTF-8'}</span>
								{/if}
							{else}
								<a href="{$link->getAdminLink($slugTab|cat: $menu.class, true)|escape:'quotes':'UTF-8'}"><span class="breadcrumb">{$menu.label|escape:'html':'UTF-8'}</span></a>
							{/if}
						{else}
							{foreach from=$menus key='id_menu' item='menu'}{if !empty($menu.sub_menus)}{foreach from=$menu.sub_menus key='id_menu2' item='menu2'}
								{if $id_menu2 == $id}
									{if isset($campaignName) && $campaignName}<a href="{$link->getAdminLink($slugTab|cat: $menu2.class, true)|escape:'quotes':'UTF-8'}">{/if}
									<span class="breadcrumb">{$menu2.label|escape:'html':'UTF-8'}</span>
									{if isset($campaignName) && $campaignName}</a>{/if}
								{/if}
							{/foreach}{/if}{/foreach}
						{/if}
						{if $ik < $_breadcrumb|count}{$dot|escape:'html':'UTF-8'}{/if}
					{/foreach}
				{else}
					{l s='Reminder campaigns' mod='ets_abandonedcart'}
				{/if}
				{if isset($campaignName) && $campaignName}
                    {$dot|escape:'html':'UTF-8'}
					<span class="breadcrumb">{$campaignName|escape:'html':'UTF-8'}</span>
				{/if}
				{if isset($leadFormTitle) && $leadFormTitle}
                    {$dot|escape:'html':'UTF-8'}
					<span class="breadcrumb">{$leadFormTitle|escape:'html':'UTF-8'}</span>
				{/if}
			</div>
		{/if}
	{/block}
	{block name="main_form"}
		<div class="ets_abancart_forms">
			{if $controller_name==$slugTab|cat:'EmailTemplate'}
				{assign var="is_email_template" value=($display=='add'||$display=='edit')}
			{elseif $controller_name==$slugTab|cat:'ReminderLeave'}
				{assign var="is_email_template" value=1}
			{else}
				{assign var="is_email_template" value=0}
			{/if}
			{if $is_email_template}<div class="ets_abancart_forms_info"><div class="ets_abancart_form_fields">{/if}
				{$content nofilter}
			{if $is_email_template}</div>
				<div class="ets_abancart_form_preview">
					<h3 class="title"><i class="icon-eye"></i> {l s='Preview template' mod='ets_abandonedcart'}</h3>
					<div class="ets_abancart_responsive_mode">
						<ul>
							<li><a data-respon="desktop_mode" href="#" class="desktop_mode active">
									<i class="ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
										<svg width="16" height="14" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1856 992v-832q0-13-9.5-22.5t-22.5-9.5h-1600q-13 0-22.5 9.5t-9.5 22.5v832q0 13 9.5 22.5t22.5 9.5h1600q13 0 22.5-9.5t9.5-22.5zm128-832v1088q0 66-47 113t-113 47h-544q0 37 16 77.5t32 71 16 43.5q0 26-19 45t-45 19h-512q-26 0-45-19t-19-45q0-14 16-44t32-70 16-78h-544q-66 0-113-47t-47-113v-1088q0-66 47-113t113-47h1600q66 0 113 47t47 113z"/></svg>
									</i> {l s='Desktop' mod='ets_abandonedcart'}</a></li>
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
					<div class="ets_abancart_preview_info">
						<div class="ets_abancart_preview"></div>
					</div>
					<div class="col-xs-12 col-sm-12">
						<div class="alert alert-info">
							{l s='Customers will receive a reminder email with the same content like this email template. Please keep in mind that all the values such as logo, product list, discount information, etc. are just demo data for reference.' mod='ets_abandonedcart'}
						</div>
					</div>
					{if $smarty.get.controller !== 'AdminEtsACReminderLeave'}
					<div class="col-xs-12 col-sm-12 ets-ac-box-preview-footer">
						<button type="button" class="btn btn-default pull-right ets_ac_btn_send_test_email " name="sendTestMail">
                            <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1664 1504v-768q-32 36-69 66-268 206-426 338-51 43-83 67t-86.5 48.5-102.5 24.5h-2q-48 0-102.5-24.5t-86.5-48.5-83-67q-158-132-426-338-37-30-69-66v768q0 13 9.5 22.5t22.5 9.5h1472q13 0 22.5-9.5t9.5-22.5zm0-1051v-24.5l-.5-13-3-12.5-5.5-9-9-7.5-14-2.5h-1472q-13 0-22.5 9.5t-9.5 22.5q0 168 147 284 193 152 401 317 6 5 35 29.5t46 37.5 44.5 31.5 50.5 27.5 43 9h2q20 0 43-9t50.5-27.5 44.5-31.5 46-37.5 35-29.5q208-165 401-317 54-43 100.5-115.5t46.5-131.5zm128-37v1088q0 66-47 113t-113 47h-1472q-66 0-113-47t-47-113v-1088q0-66 47-113t113-47h1472q66 0 113 47t47 113z"/></svg>
                            </i> {l s='Send test email' mod='ets_abandonedcart'}
                        </button>
					</div>
					{/if}
				</div>
			</div>
			{/if}
		</div>
        {if $controller_name|trim === $slugTab|cat:'Configs' || $controller_name|trim === $slugTab|cat:'Dashboard'}
	        {hook h='displayCronjobInfo'}
        {/if}
		{if preg_match('#Reminder(Email|Popup|Bar|Browser|Customer)|Cart|Tracking|ConvertedCarts|Campaign$#', $controller_name)}
			{block name="after"}
				<div class="ets_abancart_overload">
					<div class="ets_abancart_table">
						<div class="ets_abancart_table_cell">
							<div class="ets_abancart_popup_content">
								<span class="ets_abancart_close_form" title="{l s='Close' mod='ets_abandonedcart'}"></span>
								<div class="ets_abancart_form"></div>
							</div>
						</div>
					</div>
				</div>
			{/block}
		{/if}
	{/block}
</div>