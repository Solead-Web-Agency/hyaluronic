{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}
{extends file='page.tpl'}
{block name='page_content'}
{if (!isset($hide_step)) || !$hide_step}
<h3 style="text-align: center;margin: 10px 0">{l s='Delivery tracking of your order %s' mod='deliveryorderautoupdate' sprintf=[$reference]} </h3>
{foreach $orderArr as $i => $order}
<fieldset class="orderdetail_form">
    <div class="tracking-box">
        <div class="left-content">
            <div class="accordion">
                <button class="show">
                    <div class="title header-info">
                        <div class="shipment">
                            {l s='Shipment of' mod='deliveryorderautoupdate'} <span>{$order.created_date|escape:'htmlall':'UTF-8'}</span>
                        </div>
                        <div class="expand">+</div>
                    </div>
                </button>
            </div>
            <div class="more-content" {if $i == $orderArr|count - 1}style="max-height:unset"{/if}>
                <div class="row">
                    <div class="row-info">
                        {l s='Destination' mod='deliveryorderautoupdate'}
                    </div>
                    <div class="destination row-info">
                        {$order.address1|escape:'htmlall':'UTF-8'}
                    </div>
                </div>
                <div class="row">
                    <div class="row-info">
                    {l s='Carrier' mod='deliveryorderautoupdate'}
                    </div>
                    <div class="carrier row-info">
                        <img {if $order.id_carrier}src="{$image|escape:'html':'UTF-8'}{$order.id_carrier|escape:'html':'UTF-8'}.jpg"{/if}>
                        <span class="carrier-name">{$order.name|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="row-info">
                        {l s='Parcel number' mod='deliveryorderautoupdate'}
                    </div>
                    <div class="tracking-number row-info">
                        {if $order.url}
                        <a target="_blank" href="{$order.url|escape:'html':'UTF-8'}" style="text-decoration: underline;">{$order.tracking_number|escape:'htmlall':'UTF-8'}</a>
                        {else}
                        {$order.tracking_number|escape:'htmlall':'UTF-8'}
                        {/if}
                    </div>
                </div>
                {if $order.steps && $order.steps.current_status !== null}
                <div class="status header-row" style="background:{$statuses[$order.steps.current_status]->color|escape:'htmlall':'UTF-8'}">
                    {l s='Your parcel is ' mod='deliveryorderautoupdate'}{$order.steps.status_text|lower|escape:'htmlall':'UTF-8'}
                </div>
                {else}
                <div class="status header-row " style="background:#a59fb5">
                    {l s='No shipping status yet' mod='deliveryorderautoupdate'}
                </div>
                {/if}
                <div class="event-detail">
                    {include file='module:deliveryorderautoupdate/views/templates/hook/steplist.tpl' orderArr=$order steps=$order.steps}
                    <div class="status header-row" style="background-color: #eeedf1; color: #444">
                        {l s='All events' mod='deliveryorderautoupdate'}
                    </div>
                    {include file='module:deliveryorderautoupdate/views/templates/hook/event-list.tpl' events=$order.history_left showStatus=true}
                </div>

            </div>
        </div>
    </div>
</fieldset>
{/foreach}
{/if}
{/block}