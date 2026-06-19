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
<tr class="tr_cols " data-event_code="{if $order.event_code}{$order.event_code|escape:'htmlall':'UTF-8'}{else}0{/if}" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}" id_carrier="{$order.id_carrier|escape:'htmlall':'UTF-8'}" carrier_reference="{$order.carrier_reference|escape:'htmlall':'UTF-8'}" data-track="{$order.tracking_number|escape:'htmlall':'UTF-8'}" order_reference={$order.reference|escape:'htmlall':'UTF-8'}>

    <td class="pointer  service_id track_cols">
        <span class="title_box"> {$order.id_order|escape:'htmlall':'UTF-8'}  </span>
    </td>
    <td class="">
        <span class="title_box reference"><a href="
        <span class="title_box reference"><a href="{$order['url']|escape:'html':'UTF-8'}" target="_blank"> {$order.reference|escape:'htmlall':'UTF-8'}  </a></span>
    </td>
    <td class="">
        <span class="title_box date_add">{$order.date_add|escape:'htmlall':'UTF-8'}</span>
    </td>
    <td class="">
        <span class="title_box customer"> {$order.firstname|escape:'htmlall':'UTF-8'} {$order.lastname|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class="" style="position: relative;">
        <span class="title_box carrier_name"> {$order.id_carrier|escape:'htmlall':'UTF-8'}_{$order.carrier_name|escape:'htmlall':'UTF-8'}</span>
    </td>
    <td>
        <span class="title_box">
            {if $order.preparation >= 60}
                {assign var=hour value=floor($order.preparation/60)}
                {assign var=minute value=$order.preparation%60}
                {if $hour > 24}
                    {assign var=day value=floor($hour/24)}
                    {assign var=hour value=$hour%24}
                {else}
                    {assign var=day value=0}
                {/if}
                {if $day}{$day|escape:'htmlall':'UTF-8'}d{/if} {$hour|escape:'htmlall':'UTF-8'}h {$minute|escape:'htmlall':'UTF-8'}m
            {elseif $order.preparation > 0}
                {$order.preparation|escape:'htmlall':'UTF-8'} {l s='m' mod='deliveryorderautoupdate'}
            {else}
                -
            {/if}
        </span>
    </td>
    <td>
        <span class="title_box">
            {if $order.transit_time >= 60}
                {assign var=hour value=floor($order.transit_time/60)}
                {assign var=minute value=$order.transit_time%60}
                {if $hour > 24}
                    {assign var=day value=floor($hour/24)}
                    {assign var=hour value=$hour%24}
                {else}
                    {assign var=day value=0}
                {/if}
                {if $day}{$day|escape:'htmlall':'UTF-8'}d{/if} {$hour|escape:'htmlall':'UTF-8'}h {$minute|escape:'htmlall':'UTF-8'}m
            {elseif $order.transit_time > 0}
                {$order.transit_time|escape:'htmlall':'UTF-8'} {l s='m' mod='deliveryorderautoupdate'}
            {else}
                -
            {/if}
        </span>
    </td>
    <td>
        <span class="title_box">
            {if $order.delay >= 60}
                {assign var=hour value=floor($order.delay/60)}
                {assign var=minute value=$order.delay%60}
                {if $hour > 24}
                    {assign var=day value=floor($hour/24)}
                    {assign var=hour value=$hour%24}
                {else}
                    {assign var=day value=0}
                {/if}
                {if $day}{$day|escape:'htmlall':'UTF-8'}d{/if} {$hour|escape:'htmlall':'UTF-8'}h {$minute|escape:'htmlall':'UTF-8'}m
            {elseif $order.delay > 0}
                {$order.delay|escape:'htmlall':'UTF-8'} {l s='m' mod='deliveryorderautoupdate'}
            {else}
                -
            {/if}
        </span>
    </td>
    <td>
        {if $order.code}
        <a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$order.code]->color|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
            <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$order.code]->id_status|escape:'htmlall':'UTF-8'}.png" />
        </a>
        {/if}
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