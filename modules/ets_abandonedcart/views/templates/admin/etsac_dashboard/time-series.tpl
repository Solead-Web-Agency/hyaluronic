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
{if isset($time_series) && is_array($time_series) && $time_series|count > 0}
	{assign var="dashboard" value=isset($dashboard) && $dashboard}
	<{if $dashboard}ul{else}select{/if}{if $dashboard}  id="ets_abancart_time_series"{/if} class="ets_abancart_time_series{if $dashboard} dashboard{/if}">
		{foreach from=$time_series key='id' item='option'}
			<{if $dashboard}li{else}option{/if} {if $dashboard}data-{/if}value="{$id|escape:'html':'UTF-8'}" {if isset($time_series_selected) && $time_series_selected|trim == $id|trim}{if $dashboard}class="ets_abancart_time_series_li active"{else}selected="selected"{/if}{else}class="ets_abancart_time_series_li"{/if}>{$option.label|escape:'html':'UTF-8'}</{if $dashboard}li{else}option{/if}>
		{/foreach}
	</{if $dashboard}ul{else}select{/if}>
	<div class="ets_abancart_form_group input-group">
		<div class="ets_abancart_group">
			<input placeholder="{l s='From' mod='ets_abandonedcart'}" name="from_time" list="autocompleteOff" autocomplete="off" class="datepicker" value="{if isset($time_series_range[0]) && $time_series_range[0]|trim !== ''}{$time_series_range[0]|escape:'html':'UTF-8'}{/if}" type="text" />
		</div>
		<div class="ets_abancart_group">
			<input placeholder="{l s='To' mod='ets_abandonedcart'}" name="to_time" list="autocompleteOff" autocomplete="off" class="datepicker" value="{if isset($time_series_range[1]) && $time_series_range[1]|trim !== ''}{$time_series_range[1]|escape:'html':'UTF-8'}{/if}" type="text" />
		</div>
		<button class="ets_abancart_btn_apply btn btn-primary{if $dashboard} dashboard{/if}" name="ets_abancart_btn_apply">
			{l s='Apply' mod='ets_abandonedcart'}
		</button>
	</div>
{/if}