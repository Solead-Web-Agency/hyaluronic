{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}
 
 <div id="carriers" class="panel left col-md-3">
    <div class="carrier">
        <div class="block-title">
            <input type="radio" name="carrier" value="" {if empty($selectedCarrier)}checked{/if}>
            {l s='All carriers' mod='deliveryorderautoupdate'}
        </div>
        <table style="width: 100%;">
            {foreach $carrierStat as $carrier}
            <tr>
                <td>
                    {if !is_null($carrier.id_reference)}
                    <input type="radio" name="carrier" value="{$carrier.id_reference|escape:'html':'UTF-8'}" {if $selectedCarrier==$carrier.id_reference}checked{/if}>
                    {/if}
                </td>
                <td>{$carrier.name|escape:'html':'UTF-8'}</td>
                <td style="text-align: right;">{$carrier.shipments|escape:'html':'UTF-8'}</td>
            </tr>
            {/foreach}
        </table>
        <div></div>
    </div>
</div>