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

    {if !empty($trans)}{foreach from=$trans key='id' item='tran'}var {$id|escape:'html':'UTF-8'}="{$tran|escape:'html':'UTF-8'}";{/foreach}{/if}
	{if !empty($ETS_ABANCART_LINK_AJAX)}var ETS_ABANCART_LINK_AJAX='{$ETS_ABANCART_LINK_AJAX|escape:'quotes':'UTF-8'}';{/if}
	{if !empty($campaigns)}var ets_abancart_campaigns={$campaigns nofilter};{/if}
    {if isset($ETS_ABANCART_LIFE_TIME)}var ETS_ABANCART_LIFE_TIME={$ETS_ABANCART_LIFE_TIME|intval};{/if}
    {if !empty($ETS_ABANCART_LINK_SHOPPING_CART)}var ETS_ABANCART_LINK_SHOPPING_CART='{$ETS_ABANCART_LINK_SHOPPING_CART|escape:'quotes':'UTF-8'}';{/if}

    /*---end init defines---*/
	/*---init favicon---*/

    {if isset($ETS_ABANCART_BROWSER_TAB_ENABLED)}var ETS_ABANCART_BROWSER_TAB_ENABLED = {$ETS_ABANCART_BROWSER_TAB_ENABLED|intval};{/if}
    {if isset($ETS_ABANCART_TEXT_COLOR)}var ETS_ABANCART_TEXT_COLOR = "{$ETS_ABANCART_TEXT_COLOR|escape:'html':'UTF-8'}";{/if}
    {if isset($ETS_ABANCART_BACKGROUND_COLOR)}var ETS_ABANCART_BACKGROUND_COLOR = "{$ETS_ABANCART_BACKGROUND_COLOR|escape:'html':'UTF-8'}";{/if}
    {if isset($ETS_ABANCART_PRODUCT_TOTAL)}var ETS_ABANCART_PRODUCT_TOTAL = {$ETS_ABANCART_PRODUCT_TOTAL|intval};{/if}

	/*---end init favicon---*/

</script>