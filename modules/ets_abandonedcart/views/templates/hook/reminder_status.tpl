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
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

{if $reminder_status == 'running' || $reminder_status == 'stopped'}
    <div class="ets_ab_reminder_status status_{$reminder_status|escape:'html':'UTF-8'}">
        <div class="ets_ab_reminder_status_running{if $reminder_status == 'stopped'} hidden{/if}">
            <span class="reminder_status_running">{l s='Running' mod='ets_abandonedcart'}</span>
            <button title="{l s='Pause' mod='ets_abandonedcart'}" class="btn btn-default ets-ac-pause-reminder js-ets-ac-pause-reminder" data-reminder="{$reminder_id|escape:'html':'UTF-8'}" data-click="{l s='Pause' mod='ets_abandonedcart'}">
                <i class="ets_svg_icon ets_svg_fill_gray ets_svg_fill_hover_white">
                    <svg class="w_16 h_16" width="16" height="16" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M896 128q209 0 385.5 103t279.5 279.5 103 385.5-103 385.5-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103zm0 1312q148 0 273-73t198-198 73-273-73-273-198-198-273-73-273 73-198 198-73 273 73 273 198 198 273 73zm96-224q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h192q14 0 23 9t9 23v576q0 14-9 23t-23 9h-192zm-384 0q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h192q14 0 23 9t9 23v576q0 14-9 23t-23 9h-192z"/></svg>
                </i>
            </button>
        </div>
        <div class="ets_ab_reminder_status_stopped{if $reminder_status == 'running'} hidden{/if}">
            <span class="reminder_status_stopped">{l s='Stopped' mod='ets_abandonedcart'}</span>
            <button title="{l s='Continue' mod='ets_abandonedcart'}" class="btn btn-default ets-ac-pause-reminder js-ets-ac-continue-reminder" data-reminder="{$reminder_id|escape:'html':'UTF-8'}" data-click="{l s='Pause' mod='ets_abandonedcart'}">
                <i class="ets_svg_icon ets_svg_fill_gray ets_svg_fill_hover_white">
                    <svg class="w_16 h_16" width="16" height="16" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M896 128q209 0 385.5 103t279.5 279.5 103 385.5-103 385.5-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103zm384 823q32-18 32-55t-32-55l-544-320q-31-19-64-1-32 19-32 56v640q0 37 32 56 16 8 32 8 17 0 32-9z"/></svg>
                </i>
            </button>
        </div>
    </div>
{elseif $reminder_status == 'pending'}
    <span class="reminder_status_pending">{l s='Pending' mod='ets_abandonedcart'}</span>
{elseif $reminder_status == 'finished'}
    <span class="reminder_status_finished">{l s='Finished' mod='ets_abandonedcart'}</span>
{/if}
