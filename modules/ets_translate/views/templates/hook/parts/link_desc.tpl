{*
* 2007-2020 ETS-Soft
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
*  @copyright  2007-2020 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{if isset($checkApi) && $checkApi}
    <button class="btn btn-default btn-sm ets-trans-btn-check-api js-ets-trans-check-api">{l s='Check API' mod='ets_translate'}</button>
{/if}
<a href="{if isset($linkDesc)}{$linkDesc|escape:'quotes':'UTF-8'}{/if}" {if isset($linkTarget)}target="{$linkTarget|escape:'html':'UTF-8'}"{/if} rel="noreferrer">{if isset($linkText)}{$linkText|escape:'html':'UTF-8'}{/if}</a>
{l s='Make sure you have enabled "Cloud Translation API" for the project associated with this key' mod='ets_translate'}