{*
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Helloshop <contact@prestashop.com>
*  @copyright  2007-2023 Helloshop
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Helloshop
*}

{if $orders|count}
{foreach $orders as $order}
{assign var=event_code value="_"|explode:$order.event_code}
<tr class="tr_cols " data-event_text="{$event_code[1]|escape:'htmlall':'UTF-8'}" data-event_code="{if $event_code[0]}{$event_code[0]|escape:'htmlall':'UTF-8'}{else}0{/if}" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}" id_order_carrier="{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" id_carrier="{$order.id_carrier|escape:'htmlall':'UTF-8'}" carrier_reference="{$order.carrier_reference|escape:'htmlall':'UTF-8'}" data-track="{$order.track_number|escape:'htmlall':'UTF-8'}" order_reference={$order.reference|escape:'htmlall':'UTF-8'}>

    <td class="text-center pointer fixed-width-xs center">
        <input class="mailtracksend" value="{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" name="mailtracksend[]" type="checkbox">
    </td>
    <td class="pointer center service_id track_cols">
        <span class="title_box"> <a class="order_url" href="{$order.url|escape:'html':'UTF-8'}" target="_blank"> {$order.id_order|escape:'htmlall':'UTF-8'}</a></span>
    </td>
    <td class="center">
        <span class="title_box customer"> {$order.firstname|escape:'htmlall':'UTF-8'} {$order.lastname|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class="center" style="position: relative;">
        <div class="editable edit_carrier" id_carrier="{$order.id_carrier|escape:'htmlall':'UTF-8'}">
            <i class="icon-pencil openmenu"></i>
            <span class="title_box carrier_name"> {$order.id_carrier|escape:'htmlall':'UTF-8'}_{$order.carrier_name|escape:'htmlall':'UTF-8'}</span>
            <span class="icon-check-circle success"></span>
        </div>
    </td>
    <td class="center carrier" style="position: relative;">
        <div class="editable edit_connector {if !$order.id}no-connector{/if}" id_connector="{if $order.id}{$order.id|escape:'htmlall':'UTF-8'}{else}0{/if}">
            <i class="icon-pencil openmenu"></i>
            <span class="title_box connector_name">
                {if $order.carrier}
                {$order.id|escape:'htmlall':'UTF-8'}_{$order.carrier|escape:'htmlall':'UTF-8'}
                {else}
                <span class="red">{l s='No connector' mod='deliveryorderautoupdate'}</span>
                {/if}
            </span>
            <span class="icon-check-circle success_2"></span>
        </div>
    </td>
    <td class="pointer center">
        <div class="editable">
            <span class="title_box parcel_number">
                {if $order.track_number}
                {$order.track_number|escape:'htmlall':'UTF-8'}
                {else}
                -
                {/if}
            </span>
        </div>
    </td>
    <td class=" left track_cols">
        <div style="position:relative;">
            <span class="title_box result_carrier last_result_carrier last_result_carrier_{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" data-style="background-color: {$statuses[$order.code]->color|escape:'htmlall':'UTF-8'}">
                {if $event_code[1] != ''}
                <a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$order.code]->color|escape:'htmlall':'UTF-8'}" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
                    <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$order.code]->id_status|escape:'htmlall':'UTF-8'}.png" />
                </a>
                <div class="right_shipping">
                    {$event_code[1]|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$order.step_date|escape:'htmlall':'UTF-8'} {if $order.email_sent}<i class="icon-envelope"></i>{/if}</span><span class="step_even">{$order.transit_time|escape:'htmlall':'UTF-8'}</span>
                </div>
                {/if}
            </span>
            <span class="loading_carrier loading_carrier_{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" style="display:none;">
                <i class="process-icon-refresh icon-spin" style="color: #40c9ed"></i>
                {l s='Connecting' mod='deliveryorderautoupdate'}
            </span>
            <span class="waiting_carrier" style="display:none;">
                <a target="_blank" class="list-action-enable action-time">
                    <i class="icon-time"></i>
                </a>
            </span>
            {if $order.id_issue>0}<i class="icon-warning-sign icon-issue"></i>{/if}
            <i class="icon-list openmenu track_history"></i>
            <a class="json_server hidden" href="{$order.json_server|escape:'htmlall':'UTF-8'}"></a>
        </div>
    </td>



    <td class=" center">
        {include file="../../hook/order-button-groups.tpl"}
    </td>
</tr>
{/foreach}
{else}
<tr>
    <td colspan="9">
        <div class="empty">
            <div>{l s='Empty' mod='deliveryorderautoupdate'}</div>
        </div>
    </td>
</tr>
{/if}