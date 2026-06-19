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

 <table style="width: 100%" class="event-list">
    <tbody>
        {if isset($show_header) && $show_header}
        <tr style="border-bottom: 1px solid #7fa9ec;">
            <th></th>
            <th class="date">{l s='Date' mod='deliveryorderautoupdate'}</th>
        </tr>
        {/if}
        {if count($events)}
        {foreach $events as $event}
        <tr>
            <td>
                {if isset($showStatus) && $showStatus}
                <span class="status" style="background:{$statuses[$event.id_status]->color|escape:'htmlall':'UTF-8'}">
                    <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$event.id_status]->id_status|escape:'htmlall':'UTF-8'}.png"  />
                </span>
                {/if}
            </td>
            <td>
                <div class="event">
                    <div class="date">
                        <div>{str_replace(' 00:00:00', '', date($date_format_full, strtotime($event.date)))|escape:'htmlall':'UTF-8'}</div>
                    </div>
                    <div>
                        {$event.event_description|escape:'htmlall':'UTF-8'}
                    </div>
                </div>
            </td>
        </tr>
        {/foreach}
        {else}
        <tr><td colspan="2">{l s='No event' mod='deliveryorderautoupdate'}</td></tr>
        {/if}
    </tbody>
</table>