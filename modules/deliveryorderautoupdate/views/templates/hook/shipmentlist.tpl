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
<button class="button btn btn-primary pull-right add-shipment">{l s='Add a shipment' mod='deliveryorderautoupdate'}</button>
 {if count($shipments)}
 <table style="width: 100%" class="table dl_dashboard table_form table-shipment">
    <thead>
        <tr>
            <th>{l s='#' mod='deliveryorderautoupdate'}</th>
            <th width="175px">{l s='Date' mod='deliveryorderautoupdate'}</th>
            <th>{l s='Service' mod='deliveryorderautoupdate'}</th>
            <th>{l s='Connector' mod='deliveryorderautoupdate'}</th>
            <th>{l s='Tracking number' mod='deliveryorderautoupdate'}</th>
            <th>{l s='Status' mod='deliveryorderautoupdate'}</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {include file='../hook/shipment-row.tpl'}
    </tbody>
</table>
{/if}