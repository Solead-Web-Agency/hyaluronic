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
<script type="text/javascript">
	/*---init defines---*/
		{if !empty($trans)}{foreach from=$trans key='id' item='tran'}var {$id|escape:'html':'UTF-8'} = "{$tran|escape:'html':'UTF-8'}";{/foreach}{/if}
	/*---end init defines---*/
</script>
<div class="ets_abancart_leave_website_overload ets_abancart_popup ets_abancart_overload">
	<div class="ets_table">
		<div class="ets_tablecell">
			<div class="ets_abancart_container">
				<div class="ets_abancart_close leave" title="{l s='Close' mod='ets_abandonedcart'}"></div>
				<div class="ets_abancart_wrapper">{$html nofilter}</div>
			</div>
		</div>
	</div>
</div>