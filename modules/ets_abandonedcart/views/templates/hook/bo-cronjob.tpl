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
{if $ETS_ABANCART_OVERTIME > 0 || $ETS_ABANCART_LAST_CRONJOB}
	<div class="ets_abancart_cronjobs">
		<h3 class="ets_abancart_title">{l s='Cronjob notification' mod='ets_abandonedcart'}</h3>
		{if $ETS_ABANCART_OVERTIME > 0}
			<div class="alert alert-danger">{l s='It has been 12 hours since the last time Cronjob was executed. Automated emails may not be sent!' mod='ets_abandonedcart'} <a href="{if isset($automationLink)}{$automationLink nofilter}{else}#{/if}">{l s='Configure cronjob' mod='ets_abandonedcart'}</a></div>
		{elseif $ETS_ABANCART_LAST_CRONJOB}
			<div class="alert alert-info">
				<span>{l s='The last time Cronjob was executed: %s ago' sprintf=[$ETS_ABANCART_LAST_CRONJOB] mod='ets_abandonedcart'}. <a href="{if isset($automationLink)}{$automationLink nofilter}{else}#{/if}">{l s='Configure cronjob' mod='ets_abandonedcart'}</a></span>
			</div>
		{/if}
	</div>
{/if}