{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}

 <div style="font-size: 12px; font-family: Consolas;">
    {$idorders_|escape:'htmlall':'UTF-8'} {l s='orders have been successfully tracked' mod='deliveryorderautoupdate'}<br />
    {if $response}
    <table style="font-size: 12px; margin-top: 10px;">
        <tr>
            <th>Order</th>
            <th>Order Status</th>
            <th>Carrier</th>
            <th>Parcel Number</th>
            <th>Carrier Result</th>
            <th>Shipping status</th>
        </tr>
        {foreach $orders as $o}
        <tr>
            <th style="padding: 3px 10px;text-align:left">{$o.id_order|escape:'htmlall':'UTF-8'}</th>
            <th style="padding: 3px 10px;text-align:left">{$o.current_state|escape:'htmlall':'UTF-8'}</th>
            <th style="padding: 3px 10px;text-align:left">{$o.carrier|escape:'htmlall':'UTF-8'}</th>
            <th style="padding: 3px 10px;text-align:left">{$o.track_number|escape:'htmlall':'UTF-8'}</th>
            <th style="padding: 3px 10px;text-align:left">{$o.last_result|escape:'htmlall':'UTF-8'}</th>
            <th style="padding: 3px 10px;text-align:left">{$o.event_code|escape:'htmlall':'UTF-8'}</th>
        </tr>
        {/foreach}
    </table>
    {/if}
</div>