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
<tr class="tr_cols" id_issue="{$order.id_issue|escape:'htmlall':'UTF-8'}">
    <td class="text-center pointer fixed-width-xs center">
        <input class="mailtracksend" value="{$order.id_issue|escape:'htmlall':'UTF-8'}" name="id_issue[]" type="checkbox" checked>
    </td>
    <td class="pointer id_issue track_cols">
        <span class="title_box"> {$order.id_issue|escape:'htmlall':'UTF-8'}  </span>
    </td>
    <td class="pointer  service_id track_cols">
        <span class="title_box"><a href="{$order['url']|escape:'html':'UTF-8'}" target="_blank"> {$order.id_order|escape:'htmlall':'UTF-8'}  </a> </span>
    </td>
    <td class="">
        <span class="title_box customer"> {$order.customer|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td class="">
        <span class="title_box customer"> {$order.service|escape:'htmlall':'UTF-8'} </span>
    </td>
    <td>
        <div style="position:relative;">
            <span class="title_box result_carrier last_result_carrier" data-style="background-color: {$statuses[$order.code]->color|escape:'htmlall':'UTF-8'}">
                {if $order.code != ''}
                <a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$order.code]->color|escape:'htmlall':'UTF-8'}" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
                    <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$order.code]->id_status|escape:'htmlall':'UTF-8'}.png" />
                </a>
                <div class="right_shipping">
                    {$order.event_code|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$order.step_date|escape:'htmlall':'UTF-8'}</span>
                </div>
                {/if}
            </span>
        </div>
    </td>
    <td style="position: relative;">
        <div class="editable edit_issue" id_issue="{if $order.issue_type}{$order.issue_type|escape:'htmlall':'UTF-8'}{else}0{/if}">
            <i class="icon-pencil openmenu"></i>
            <span class="title_box issue_name">{$order.issue|escape:'htmlall':'UTF-8'}  </a> </span>
        </div>
    </td>
    <td>
        <div class="issue-status" style="display: flex;align-items: center;">
            <div class="editable edit_issue_status" id_issue_status="{if $order.id_status}{$order.id_status|escape:'htmlall':'UTF-8'}{else}0{/if}" style="position: relative;">
                <span class="title_box">
                    <div class="status_name">{$order.status|escape:'htmlall':'UTF-8'}</div>
                    <div class="issue_status_date">{$order.issue_date|escape:'htmlall':'UTF-8'} </div>
                </span>
                <i class="icon-pencil openmenu" style="margin-left: 15px;position: unset;"></i>
            </div>
            <i class="icon-list openmenu load_issue_history" style="position: unset;margin-left: 5px;"></i>
        </div>
    </td>
    <td>
        <span class="title_box">
            {if $order.status == 6}
                {l s='Closed' mod='deliveryorderautoupdate'}
            {else}
                {l s='Open' mod='deliveryorderautoupdate'}
            {/if}
        </span>
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