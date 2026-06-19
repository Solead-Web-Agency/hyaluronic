{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}
<h3 style="text-align: center;margin: 10px 0;font-family:Open sans, arial, sans-serif;font-size: 1.5em;margin: 10px 0px;text-transform: none;height: auto">{sprintf($lang.tracking_order, $reference)|escape:'htmlall':'UTF-8'} </h3>
{foreach $orderArr as $i => $order}
<fieldset style="padding:0;border: none">
	<div style="width: 100%;margin: auto;">
        {if $i == $orderArr|count -1}
            {if $order.history_left}
            {assign var=event_status value="_"|explode:$order.history_left[0]['result']}
            <div  style="border: none;text-align: center;min-height: 45px;line-height: 45px;color: white;border-bottom: 1px solid #ffffff;padding:5px;font-size: 16px;font-weight: bold;background:{$statuses[$event_status[0]]->color|escape:'htmlall':'UTF-8'}">
                {$lang.current_status|escape:'html':'UTF-8'}{$event_status[1]|lower|escape:'htmlall':'UTF-8'}
            </div>
            {else}
            <div style="border: none;text-align: center;min-height: 45px;line-height: 45px;color: white;border-bottom: 1px solid #ffffff;padding:5px;font-size: 16px;font-weight: bold;background:#a59fb5">
                {$lang.no_status|escape:'html':'UTF-8'}
            </div>
            {/if}
            <table style="width: 100%;border-collapse: collapse;">
                <tr height="50" style="background-color:#eeedf1;border-bottom: 2px solid #ffffff;">
                    <td style="background-color:#eeedf1;min-height: 35px;vertical-align: middle;width: 50%;text-indent: 15px;min-height: 35px;font-weight: 400;font-size: 14px;;box-sizing: border-box;-moz-box-sizing: border-box;">{$lang.destination|escape:'html':'UTF-8'}</td>
                    <td style="background-color:#eeedf1;min-height: 35px;vertical-align: middle;width: 50%;text-indent: 15px;min-height: 35px;font-weight: 400;font-size: 14px;;box-sizing: border-box;-moz-box-sizing: border-box;">{$order.address1|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr height="50" style="background-color:#eeedf1;border-bottom: 2px solid #ffffff;">
                    <td style="background-color:#eeedf1;min-height: 35px;vertical-align: middle;width: 50%;text-indent: 15px;min-height: 35px;font-weight: 400;font-size: 14px;;box-sizing: border-box;-moz-box-sizing: border-box;">{$lang.carrier|escape:'html':'UTF-8'}</td>
                    <td style="vertical-align: top;background-color:#eeedf1;min-height: 35px;vertical-align: middle;width: 50%;min-height: 35px;font-weight: 400;font-size: 14px;;box-sizing: border-box;-moz-box-sizing: border-box;">
                        <table>
                            <tr>
                                <td style="background-color:#eeedf1;"><img src="{$image|escape:'html':'UTF-8'}{$order.id_carrier|escape:'html':'UTF-8'}.jpg" width="35" height="auto" style="; margin-left: 15px" ></td>
                                <td style="background-color:#eeedf1;">{$order.name|escape:'htmlall':'UTF-8'}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr height="50" style="background-color:#eeedf1;border-bottom: 2px solid #ffffff;">
                    <td style="background-color:#eeedf1;min-height: 35px;vertical-align: middle;width: 50%;text-indent: 15px;min-height: 35px;font-weight: 400;font-size: 14px;;box-sizing: border-box;-moz-box-sizing: border-box;">{$lang.parcel_number|escape:'html':'UTF-8'}</td>
                    <td style="background-color:#eeedf1;min-height: 35px;vertical-align: middle;width: 50%;text-indent: 15px;min-height: 35px;font-weight: 400;font-size: 14px;;box-sizing: border-box;-moz-box-sizing: border-box;">{$order.tracking_number|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr height="56">
                    <td colspan="2" style="border: none;background-color: #0d0366;text-align: center;color: white;border-bottom: 1px solid #ffffff;padding:5px;font-size: 16px;font-weight: bold;">
                        <a href="{literal}{track_link}{/literal}" style="color:#fff;color: #fff;display: block;height: 100%;width: 100%;text-decoration: none;padding: 8px;">
                        {$lang.view_more|escape:'html':'UTF-8'}</a>
                    </td>
                </tr>
            </table>
        {else}
            <table style="width: 100%;border-collapse: collapse;margin-bottom: 10px">
                <tr height="56">
                    <td colspan="2" style="border: none;background-color: #0d0366;text-align: center;color: white;border-bottom: 1px solid #ffffff;padding:5px;font-size: 16px;font-weight: bold;">
                        <a href="{literal}{track_link}{/literal}" style="color:#fff;color: #fff;display: block;height: 100%;width: 100%;text-decoration: none;padding: 8px;">{$lang.shipment_of|escape:'html':'UTF-8'} <span>{$order.created_date|escape:'htmlall':'UTF-8'}</span></a>
                    </td>
                </tr>
            </table>
        {/if}
	</div>
</fieldset>
{/foreach}
