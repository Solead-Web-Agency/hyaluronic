{* NOTICE OF LICENSE
* @copyright  2007-2023 Helloshop
* @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{include file='./carriers.tpl' selectedCarrier=''}
{include file='./delivery_status.tpl' selectedStatus='not_delivered'}
<div id="delivery_time" class="panel left col-md-3">
    <div>
        <div class="block-title">
            {l s='Delivery time' mod='deliveryorderautoupdate'}
        </div>
        <table style="width: 100%;">
            <!-- <tr class="preparation">
                <td>{l s='Preparation time' mod='deliveryorderautoupdate'}</td>
                <td class="time" style="text-align: right;">{$overview.time.preparation_time|escape:'html':'UTF-8'}</td>
            </tr> -->
            <tr class="transit">
                <td>{l s='Average time' mod='deliveryorderautoupdate'}</td>
                <td class="time" style="text-align: right;">{$overview.time.transit_time|escape:'html':'UTF-8'}</td>
            </tr>
            <!-- <tr class="total">
                <td>{l s='Total time' mod='deliveryorderautoupdate'}</td>
                <td class="time" style="text-align: right;">{$overview.time.total_time|escape:'html':'UTF-8'}</td>
            </tr> -->
        </table>
        {include file='./slider.tpl'}
    </div>
</div>