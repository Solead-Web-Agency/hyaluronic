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
{if isset($campaigns_type) && is_array($campaigns_type) && $campaigns_type|intval}
	{foreach from=$campaigns_type key='id' item='reminder'}
	    {if $id|trim !== 'browser_tab'}
			<div class="ets_abancart_stats_item {$id|escape:'html':'UTF-8'}">
				<span class="ets_abancart_label">{$reminder.label|escape:'html':'UTF-8'}&nbsp;
					{if $id|trim=='email'|| $id|trim=='customer'}
						<a class="ets_abancart_tab_link" href="{$tracking_link|cat:'&submitFilterets_abancart_tracking=1&campaign_type='|cat:$id|cat:'#ets_abancart_tracking' nofilter}">{l s='View tracking list' mod='ets_abandonedcart'}
							<i class="lh_16 ets_svg"><svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M979 960q0 13-10 23l-466 466q-10 10-23 10t-23-10l-50-50q-10-10-10-23t10-23l393-393-393-393q-10-10-10-23t10-23l50-50q10-10 23-10t23 10l466 466q10 10 10 23zm384 0q0 13-10 23l-466 466q-10 10-23 10t-23-10l-50-50q-10-10-10-23t10-23l393-393-393-393q-10-10-10-23t10-23l50-50q10-10 23-10t23 10l466 466q10 10 10 23z"/></svg></i>
						</a>
					{/if}
				</span>
				<span class="ets_abancart_totals">
					<span class="ets_abancart_total">{if isset($abandoned_carts[$id]) && $abandoned_carts[$id]|count > 0}{$abandoned_carts[$id]['total_execute_times']|intval}{/if}</span>
					{if $abandoned_carts[$id]['total_execute_times']|intval > 0 && ($id|trim == 'email' || $id|trim == 'customer')}
						{assign var='pipe' value=''}
	                    ({if isset($abandoned_carts[$id]['total_success']) && $abandoned_carts[$id]['total_success']|intval > 0}
							<span class="type-{$id|escape:'html':'UTF-8'} success">{l s='Success' mod='ets_abandonedcart'}: <span class="ets_abancart_total_success">{$abandoned_carts[$id]['total_success']|intval}</span></span>
                            {assign var='pipe' value='|'}
	                    {/if}
	                    {if isset($abandoned_carts[$id]['total_failed']) && $abandoned_carts[$id]['total_failed']|intval > 0}
							<span class="type-{$id|escape:'html':'UTF-8'} failed"> {$pipe|escape:'quotes':'UTF-8'} {l s='Failed' mod='ets_abandonedcart'}: <span class="ets_abancart_total_failed">{$abandoned_carts[$id]['total_failed']|intval}</span></span>
                            {assign var='pipe' value='|'}
		                {/if}
						{if isset($abandoned_carts[$id]['total_read']) && $abandoned_carts[$id]['total_read']|intval > 0}
							<span class="type-{$id|escape:'html':'UTF-8'} read"> {$pipe|escape:'quotes':'UTF-8'} {l s='Read' mod='ets_abandonedcart'}: <span class="ets_abancart_total_read">{$abandoned_carts[$id]['total_read']|intval}</span></span>
                            {assign var='pipe' value='|'}
						{/if}
						{if isset($abandoned_carts[$id]['total_queue']) && $abandoned_carts[$id]['total_queue']|intval > 0}
							<span class="type-{$id|escape:'html':'UTF-8'} queue"> {$pipe|escape:'quotes':'UTF-8'} {l s='Queue' mod='ets_abandonedcart'}: <span class="ets_abancart_total_queue">{$abandoned_carts[$id]['total_queue']|intval}</span></span>
                        {/if})
	                {/if}
				</span>
			</div>
	    {/if}
	{/foreach}
{/if}