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
{if $thumb_link}
    <div class="ets_abancart_thumb">
        <img src="{$thumb_link|escape:'html':'UTF-8'}" alt="{$thumb_title|escape:'html':'UTF-8'}"{if isset($thumb_width) && $thumb_width} width="{$thumb_width|intval}"{/if}{if isset($thumb_height) && $thumb_height} height="{$thumb_height|intval}"{/if} />
        <div class="ets_abancart_lookup_content">
            <span class="thumb_arrow"></span>
            <img src="{$thumb_link|escape:'html':'UTF-8'}" />
        </div>
    </div>
{else}
    <span class="ets_abancart_thumbnail"></span>
{/if}