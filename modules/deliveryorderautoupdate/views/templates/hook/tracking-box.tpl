
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

<div class="right-content">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#shipment" aria-controls="shipment" role="tab" data-toggle="tab">{l s='Shipment' mod='deliveryorderautoupdate'}</a></li>
        <li role="presentation"><a href="#return" aria-controls="return" role="tab" data-toggle="tab">{l s='Return' mod='deliveryorderautoupdate'}</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="shipment">
            <div class="shipments">
                {include file='../hook/shipmentlist.tpl'}
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="return">
            <div class="returns">
                {include file='../hook/returnlist.tpl'}
            </div>
        </div>
    </div>
</div>