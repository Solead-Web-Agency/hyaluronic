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

{if $enabled|intval == 0}
    <span class="ets_ab_reminder_status draft reminder_status_draff">{l s='Draft' mod='ets_abandonedcart'}</span>
    <a href="{$href nofilter}&enabled={if $email_timing_option == 4}3{else}1{/if}" class="ets_ab_btn_change_status" title="{l s='Play' mod='ets_abandonedcart'}">
        <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M896 128q209 0 385.5 103t279.5 279.5 103 385.5-103 385.5-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103zm384 823q32-18 32-55t-32-55l-544-320q-31-19-64-1-32 19-32 56v640q0 37 32 56 16 8 32 8 17 0 32-9z"/></svg>
    </a>
{elseif $enabled|intval == 1}
    {if $campaignStatus|intval == 1}
        <span class="ets_ab_reminder_status disabled">{l s='Disabled' mod='ets_abandonedcart'}</span>
    {elseif $campaignStatus|intval == 2}
        <span class="ets_ab_reminder_status waiting">{l s='Waiting' mod='ets_abandonedcart'}</span>
    {elseif $campaignStatus|intval == 3}
        <span class="ets_ab_reminder_status expired reminder_status_draff">{l s='Expired' mod='ets_abandonedcart'}</span>
    {else}
        <span class="ets_ab_reminder_status running reminder_status_pending">{if $email_timing_option == 3}{l s='Pending' mod='ets_abandonedcart'}{else}{l s='Running' mod='ets_abandonedcart'}{/if}</span>
        <a href="{$href nofilter}&enabled=2" class="ets_ab_btn_change_status" title="{l s='Stop' mod='ets_abandonedcart'}">
            <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1216 1184v-576q0-14-9-23t-23-9h-576q-14 0-23 9t-9 23v576q0 14 9 23t23 9h576q14 0 23-9t9-23zm448-288q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/></svg>
        </a>
    {/if}
{elseif $enabled|intval == 2}
    <span class="ets_ab_reminder_status stop reminder_status_stopped">{l s='Stop' mod='ets_abandonedcart'}</span>
    <a href="{$href nofilter}&enabled=1" class="ets_ab_btn_change_status" title="{l s='Play' mod='ets_abandonedcart'}">
        <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M896 128q209 0 385.5 103t279.5 279.5 103 385.5-103 385.5-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103zm384 823q32-18 32-55t-32-55l-544-320q-31-19-64-1-32 19-32 56v640q0 37 32 56 16 8 32 8 17 0 32-9z"/></svg>
    </a>
{elseif $enabled|intval == 3}
    <span class="ets_ab_reminder_status finished reminder_status_finished">{l s='Finished' mod='ets_abandonedcart'}</span>
{/if}