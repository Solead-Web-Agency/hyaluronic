{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}
<!-- /deliveryorderautoupdate/views/templates/admin/deliveryorderautoupdate_ajax/admin_order_track.tpl -->
<div class="track">
  <div class="delivery-icon">
    <div class="dl-icon">
        <span style="background:{$statuses[$order.id_status]->color|escape:'htmlall':'UTF-8'}">
            <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$order.id_status]->id_status|escape:'htmlall':'UTF-8'}.png" />
        </span>
    </div>
    <div class="dl-detail" style="background:{$statuses[$order.id_status]->color|escape:'htmlall':'UTF-8'}">
        <div style="font-weight:bold; text-align: left; white-space: nowrap;"> {$order.result|escape:'htmlall':'UTF-8'} </div>
        <div style=" white-space: nowrap;">
        <span class="date">{l s=$order.step_date mod='deliveryorderautoupdate'}</span><span style="margin-left: 5px">{$order.step_time|escape:'htmlall':'UTF-8'}</span>
        </div>
    </div>
  </div>
</div>