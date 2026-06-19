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
{foreach $returns as $return}
<tr id_return="{$return.id_return|escape:'htmlall':'UTF-8'}">
    <td>
        {if $return.id_return}
        {$return.id_return|escape:'htmlall':'UTF-8'}
        {else}
        -
        {/if}
    </td>
    <td>
        {if $return.state_name}
        {$return.state_name|escape:'htmlall':'UTF-8'}
        {else}
        -
        {/if}
    </td>
    <td style="position: relative;">
        <div class="editable edit_connector" id_connector="{if $shipment.id}{$shipment.id|escape:'htmlall':'UTF-8'}{else}0{/if}">
            <i class="icon-pencil openmenu "></i>
            <span class="title_box connector_name">
                {if $return.connector_name}
                {$return.id_connector|escape:'htmlall':'UTF-8'}_{$return.connector_name|escape:'htmlall':'UTF-8'}
                {else}
                <span class="red">{l s='No connector' mod='deliveryorderautoupdate'}</span>
            </span>
            {/if}
        </div>
    </td>
    <td>
        <div class="editable">
            <span class="shipping_number">
                {if $return.track_number}
                {$return.track_number|escape:'htmlall':'UTF-8'}
                {else}
                -
                {/if}
            </span>
        </div>
    </td>
    <td class="left fit-cell">
        <div class="htr_shipping">
            {if $return.shipping_status != ''}
            <a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$return.shipping_status]->color|escape:'htmlall':'UTF-8'}">
                <img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$return.shipping_status]->id_status|escape:'htmlall':'UTF-8'}.png" />
            </a>
            <div class="right_shipping">
                {$return.event_code|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$return.step_date|escape:'htmlall':'UTF-8'}</span>
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
    <td class=" center">
        {include file="../hook/order-button-groups.tpl" page='return' order=$return}
    </td>
</tr>
{/foreach}