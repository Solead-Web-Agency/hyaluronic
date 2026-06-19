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

{if isset($value) && $value}<ul class="ets_abancart_discount">
	<li>{if $discount_option == 'no'}{l s='No discount' mod='ets_abandonedcart'}{elseif $discount_option == 'fixed'}{l s='Fixed discount code' mod='ets_abandonedcart'}{else}{l s='Generate discount code automatically' mod='ets_abandonedcart'}{/if}</li>
	{if $discount_option == 'fixed'}
		<li>{l s='Discount code' mod='ets_abandonedcart'} : {$discount_code|escape:'html':'UTF-8'}</li>
	{elseif $discount_option == 'auto'}
		<li>{l s='Free shipping' mod='ets_abandonedcart'} : {if $free_shipping}{l s='Yes' mod='ets_abandonedcart'}{else}{l s='No' mod='ets_abandonedcart'}{/if}</li>
		{if $apply_discount == 'percent'}
			<li>{l s='Percentage' mod='ets_abandonedcart'} : {$reduction_percent|cat:' %'|escape:'html':'UTF-8'}</li>
		{elseif $apply_discount == 'amount'}
			<li>{l s='Amount' mod='ets_abandonedcart'} : {$currency->sign|cat: $reduction_amount nofilter} {if $reduction_tax}{l s='(tax incl.)' mod='ets_abandonedcart'}{else}{l s='(tax excl.)' mod='ets_abandonedcart'}{/if}</li>
		{/if}
	{/if}
	{if $campaign_type!='email' && $campaign_type!='browser' && $discount_option != 'no' && $enable_count_down_clock}
		<li>{l s='Countdown clock' mod='ets_abandonedcart'} : {if $enable_count_down_clock}{l s='Yes' mod='ets_abandonedcart'}{else}{l s='No' mod='ets_abandonedcart'}{/if}</li>
	{/if}
</ul>{/if}