{* NOTICE OF LICENSE
*
* tdis source file is subject to a commercial license from Helloshop
* Use, copy, modification or distribution of tdis source file witdout written
* license agreement from tde Helloshop is strictly forbidden.
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
* @autdor     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{foreach $shipments as $shipment}
<tr class="tr_cols" id_order_carrier="{$shipment.id_order_carrier|escape:'htmlall':'UTF-8'}">
    <td>{$shipment.id_order_carrier|escape:'htmlall':'UTF-8'}</td>
    <td>
        <div class="editable">
            <span class="shipment-date" js-v16="{$v16|escape:'htmlall':'UTF-8'}">
            {if $shipment.date_add}
                {$shipment.date_add|escape:'htmlall':'UTF-8'}
            {else}
                -
            {/if}
            </span>
        </div>
    </td>
    <td style="position: relative;">
        <div class="editable edit_carrier" id_reference="{$shipment.id_reference|escape:'htmlall':'UTF-8'}" id_carrier="{$shipment.id_carrier|escape:'htmlall':'UTF-8'}">
            <i class="icon-pencil openmenu"></i>
            <span class="carrier_name">
            {if $shipment.carrier}
                {$shipment.carrier|escape:'htmlall':'UTF-8'}
            {else}
                -
            {/if}
            </span>
        </div>
    </td>
    <td style="position: relative;">
        <div class="editable edit_connector {if !$shipment.carrier}not-allowed{/if} {if !$shipment.id}no-connector{/if}" id_connector="{if $shipment.id}{$shipment.id|escape:'htmlall':'UTF-8'}{else}0{/if}">
            <i class="icon-pencil openmenu "></i>
            <span class="title_box connector_name">
                {if $shipment.connector}
                {$shipment.connector|escape:'htmlall':'UTF-8'}
                {else}
                <span class="red">{l s='No connector' mod='deliveryorderautoupdate'}</span>
                {/if}
            </span>
        </div>
    </td>
    <td>
        <div class="editable">
            <span class="parcel_number">
            {if $shipment.track_number}
                {$shipment.track_number|escape:'htmlall':'UTF-8'}
            {else}
                -
            {/if}
            </span>
        </div>
    </td>
    <td class="left fit-cell">
        <div class="htr_shipping">
            {if $shipment.code != ''}
            <a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$shipment.code]->color|escape:'htmlall':'UTF-8'}">
                <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$shipment.code]->id_status|escape:'htmlall':'UTF-8'}.png" />
            </a>
            <div class="right_shipping">
                {$shipment.event_code|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$shipment.step_date|escape:'htmlall':'UTF-8'}</span>
            </div>
            {else}
            -
            {/if}
        </div>
        <span class="loading_carrier" style="display:none;">
            <i class="process-icon-refresh icon-spin" style="color: #40c9ed"></i>
            <span>{l s='Connecting' mod='deliveryorderautoupdate'}</span>
        </span>
    </td>
    <td>
        <span class="open_issue">
        {if $shipment.issue > 0}
            <a class="check_issue" href="#">{l s='issue' mod='deliveryorderautoupdate'}</a>
        {/if}
        </span>
    </td>
    <td class=" center">
        {include file="../hook/order-button-groups.tpl" order=$shipment page='adminOrder'}
    </td>
</tr>
{/foreach}