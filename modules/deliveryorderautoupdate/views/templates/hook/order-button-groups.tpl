{* NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Helloshop
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Helloshop is strictly forbidden.
 * In order to obtain a license, please contact us: modules@helloshop.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par Helloshop
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Helloshop est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter la Helloshop a l'adresse:
 *                  modules@helloshop.com
 * ...........................................................................
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}
{if isset($page) && $page == 'return'}
    <div class="btn-group-action  pull-right">
        <div class="btn-group pull-right">
            <button class="btn btn-default track_return">
				<i class="icon-arrow-circle-right"></i>
                {l s='Track' mod='deliveryorderautoupdate'}
            </button>

            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
                <span class="caret"> </span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="view_carrier json_server" target="_blank" href="{$order.json_server|escape:'htmlall':'UTF-8'}">
                        <i class="icon-code"></i>
                        {l s='Track in debug mode' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li>
                    <a class="force_return">
                        <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/force.png" style="width:14px;">
                        {l s='Force shipping status' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li>
                    <a class="delete_return">
                        <i class="icon-trash"></i>
                        {l s='Delete return shipment' mod='deliveryorderautoupdate'}
                    </a>
                </li>
            </ul>
        </div>
    </div>
{elseif isset($page) && $page == 'adminOrder'}
    <div class="btn-group-action  pull-right">
        <div class="btn-group pull-right" data-toggle="tooltip" {if !$order.carrier || !$order.track_number}title="{l s='Shipping number is empty' mod='deliveryorderautoupdate'}" {/if}>
            <button {if !$order.carrier || !$order.connector || !$order.track_number || $order.disable}disabled{/if} class=" btn btn-default update_ordercarrier update_ordercarrier_{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" id_order_carrier="{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" id_carrier="{$order.id|escape:'htmlall':'UTF-8'}" name="update_ordercarrier">
                {if $order.disable}
                <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/close.png" title="{l s='Play' mod='deliveryorderautoupdate'}" alt="{l s='Play' mod='deliveryorderautoupdate'}" style="height:14px;">
                {l s='Off' mod='deliveryorderautoupdate'}
                {else}
				<i class="icon-arrow-circle-right"></i>
                {l s='Track' mod='deliveryorderautoupdate'}
                {/if}
            </button>

            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
                <span class="caret"> </span>
            </button>
            <ul class="dropdown-menu">
                <li {if !$order.carrier || !$order.connector || !$order.track_number}style="display:none"{/if}>
                    <a class="view_carrier track_history" href="#">
                        <i class="icon-list"></i>
                        {l s='Details' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li {if !$order.carrier || !$order.connector || !$order.track_number}style="display:none"{/if}>
                    <a class="view_carrier" target="_blank" href="{$front_url|escape:'htmlall':'UTF-8'}?order_reference={$order.reference|escape:'htmlall':'UTF-8'}" >
                        <i class="icon-shopping-cart"></i>
                        {l s='View on front office' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li {if !$order.carrier || !$order.connector || !$order.track_number}style="display:none"{/if}>
                    <a class="view_carrier tracking_url" target="_blank" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" data-view="{if $order.tracking_url}true{else}false{/if}">
                        <i class="icon-truck"></i>
                        {l s='View on carrier site' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li {if !$order.carrier || !$order.connector || !$order.track_number}style="display:none"{/if}>
                    <a class="view_carrier json_server" target="_blank" href="{$order.json_server|escape:'htmlall':'UTF-8'}">
                        <i class="icon-code"></i>
                        {l s='Track in debug mode' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li class="no-disable">
                    <a class="force">
                        <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/force.png" style="width:14px;">
                        {l s='Force shipping status' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li {if !$order.carrier || !$order.connector || !$order.track_number}style="display:none"{/if}>
                    <a class="split-shipment">
                        <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/split.png" style="width:14px;">
                        {l s='Split into several shipments' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li {if !$order.carrier || !$order.connector || !$order.track_number}style="display:none"{/if}>
                    <a class="create_issue">
                        <i class="icon-warning-sign"></i>
                        {l s='Add to issues' mod='deliveryorderautoupdate'}
                    </a>
                </li>
            </ul>
        </div>
    </div>
{else}
    {* {if $order.method != 2} *}
    <div class="btn-group-action  pull-right">
        <div class="btn-group pull-right" data-toggle="tooltip" {if !$order.carrier || !$order.track_number}title="{l s='Shipping number is empty' mod='deliveryorderautoupdate'}" {/if}>
            <button {if !$order.carrier || !$order.track_number || $order.disable}disabled{/if} class=" btn btn-default update_ordercarrier update_ordercarrier_{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" id_order_carrier="{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" id_carrier="{$order.id|escape:'htmlall':'UTF-8'}" name="update_ordercarrier">
                {if $order.disable}
                <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/close.png" title="{l s='Play' mod='deliveryorderautoupdate'}" alt="{l s='Play' mod='deliveryorderautoupdate'}" style="height:14px;">
                {l s='Off' mod='deliveryorderautoupdate'}
                {else}
				<i class="icon-arrow-circle-right"></i>
                {l s='Track' mod='deliveryorderautoupdate'}
                {/if}
            </button>
             <!-- {if !$order.carrier || !$order.track_number}disabled{/if} -->
            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
                <span class="caret"> </span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="view_carrier" target="_blank" href="{$front_url|escape:'htmlall':'UTF-8'}?order_reference={$order.reference|escape:'htmlall':'UTF-8'}" >
                        <i class="icon-shopping-cart"></i>
                        {l s='View on front office' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li>
                    <a class="view_carrier tracking_url" target="_blank" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" data-view="{if $order.tracking_url}true{else}false{/if}">
                        <i class="icon-truck"></i>
                        {l s='View on carrier site' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li>
                    <a class="force">
                        <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/force.png" style="width:14px;">
                        {l s='Force shipping status' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li>
                    {if $order.disable}
                    <a class="disable" data-disable="0">
                        <i class="icon-play"></i>
                        <span>{l s='Enable tracking' mod='deliveryorderautoupdate'}</span>
                    </a>
                    {else}
                    <a class="disable" data-disable="1">
                        <i class="icon-pause"></i>
                        <span>{l s='Disable tracking' mod='deliveryorderautoupdate'}</span>
                    </a>
                    {/if}
                </li>
                <li>
                    <a class="add-shipment">
                        <i class="icon-plus"></i>
                        {l s='Add shipment to order' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li>
                    <a class="split-shipment">
                        <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/split.png" style="width:14px;">
                        {l s='Split into several shipments' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li>
                    <a class="create_issue">
                        <i class="icon-warning-sign"></i>
                        {l s='Add to issues' mod='deliveryorderautoupdate'}
                    </a>
                </li>
                <li>
                    <a class="send-email">
                        <i class="icon-envelope"></i>
                        {l s='Send email' mod='deliveryorderautoupdate'}
                    </a>
                </li>
            </ul>
        </div>
    </div>
    {* {/if} *}
{/if}