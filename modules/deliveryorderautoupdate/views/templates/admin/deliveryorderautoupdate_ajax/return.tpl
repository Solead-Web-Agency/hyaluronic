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
<tr class="tr_cols" id_return="{$order.id_return|escape:'htmlall':'UTF-8'}">
    <td class="text-center pointer fixed-width-xs center">
        <input class="mailtracksend" value="{$order.id_return|escape:'htmlall':'UTF-8'}" name="id_return[]" type="checkbox" checked>
    </td>
    <td class="pointer id_return track_cols">
        <span class="title_box"> {$order.id_return|escape:'htmlall':'UTF-8'}  </span>
    </td>
    <td class="pointer  service_id track_cols">
        <span class="title_box"><a href="{$order['url']|escape:'html':'UTF-8'}" target="_blank"> {$order.id_order|escape:'htmlall':'UTF-8'}  </a> </span>
    </td>
    <td class="pointer  issue track_cols">
        <span class="title_box">
            {if $order.state_name}
            {$order.state_name|escape:'htmlall':'UTF-8'}
            {else}
            -
            {/if}
        </a> </span>
    </td>
    <td class="">
        <span class="title_box customer">
            {if $order.customer}
            {$order.customer|escape:'htmlall':'UTF-8'}
            {else}
            -
            {/if}
        </span>
    </td>
    <td style="position: relative;">
        <div class="editable edit_connector" id_connector="{if $order.id_connector}{$order.id_connector|escape:'htmlall':'UTF-8'}{else}0{/if}">
            <i class="icon-pencil openmenu"></i>
            <span class="title_box connector_name">
                {if $order.connector_name}
                {$order.id_connector|escape:'htmlall':'UTF-8'}_{$order.connector_name|escape:'htmlall':'UTF-8'}
                {else}
                <span class="red">{l s='No connector' mod='deliveryorderautoupdate'}</span>
                {/if}
            </span>
            <span class="icon-check-circle success_2"></span>
        </div>
    </td>
    <td class="">
        <div class="editable">
            <span class="title_box shipping_number">
            {if $order.track_number}
            {$order.track_number|escape:'htmlall':'UTF-8'}
            {else}
            -
            {/if}
            </span>
        </div>
    </td>
    <td class="left shipping_status">
        <span class="title_box result_carrier last_result_carrier" data-style="background-color: {$statuses[$order.code]->color|escape:'htmlall':'UTF-8'}">
        {if $order.shipping_status != ''}
            <a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$order.shipping_status]->color|escape:'htmlall':'UTF-8'}">
                <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$order.shipping_status]->id_status|escape:'htmlall':'UTF-8'}.png" />
            </a>
            <div class="right_shipping">
                {$order.event_code|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$order.step_date|escape:'htmlall':'UTF-8'}</span>
            </div>
        {else}
        -
        {/if}

        </span>
        <span class="loading_carrier loading_carrier_{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" style="display:none;">
            <i class="process-icon-refresh icon-spin" style="color: #40c9ed"></i>
            {l s='Connecting' mod='deliveryorderautoupdate'}
        </span>
    </td>
    <td class=" center">
        {include file="../../hook/order-button-groups.tpl" page='return'}
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