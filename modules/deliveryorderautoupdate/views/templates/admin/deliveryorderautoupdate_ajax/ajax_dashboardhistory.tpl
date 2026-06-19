{*
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Helloshop <contact@prestashop.com>
*  @copyright  2007-2023 Helloshop
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Helloshop
*}
{foreach $history as $hs}
<tr class="tr_cols">
	<td class=" center">
		<span class="title_box htr_track_id" data-id="{$hs.id|escape:'htmlall':'UTF-8'}"> {$hs.id|escape:'htmlall':'UTF-8'} </span>
	</td>
	<td class=" center">
		<span class="title_box htr_id_order" data-id="{$hs.id_order|escape:'htmlall':'UTF-8'}"> {$hs.id_order|escape:'htmlall':'UTF-8'} </span>
	</td>
	<td class=" center">
		<span class="title_box htr_date_add" data-id="{$hs.date_add|escape:'htmlall':'UTF-8'}"> {$hs.date_add|escape:'htmlall':'UTF-8'} </span>
	</td>
	<td class=" center">
		<span class="title_box htr_carrier_name" data-id="{$hs.carrier_name|escape:'htmlall':'UTF-8'}"> {$hs.carrier_name|escape:'htmlall':'UTF-8'} </span>
	</td>
	<td class=" left carrier_response" data-id="{$hs.carrier_response|escape:'htmlall':'UTF-8'}">
		{if $hs.success_response == 0}
		<a target="_blank" class="list-action-enable action-disabled" href="#" title="Disable" status="0" >
			<i class="icon-remove"></i>
		</a>
		{elseif $hs.success_response == 1}
		<a target="_blank" class="list-action-enable action-enabled" href="#" title="Active">
			<i class="icon-check"></i>
		</a>
		{/if}
		{$hs.carrier_response|escape:'htmlall':'UTF-8'}
	</td>
	<td class=" left htr_shipping">

		{if isset($hs.event_code)}
		<a target="_blank" class="list-action-enable action-hisenabled" style="background: {$statuses[$hs.event_code]->color|escape:'htmlall':'UTF-8'}" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
			<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$hs.event_code]->id_status|escape:'htmlall':'UTF-8'}.png" />
		</a>
		<div class="right_shipping">
			{$hs.shipping_status|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$hs.step_date|escape:'htmlall':'UTF-8'}</span>
		</div>
		{/if}

	</td>
	<td class=" center">
        <span class="title_box htr_method" data-id="{$hs.carrier|escape:'htmlall':'UTF-8'}">
            {if isset($methods[$hs.carrier])}
            {$methods[$hs.carrier]|escape:'htmlall':'UTF-8'}
            {else}
            {$hs.carrier|escape:'htmlall':'UTF-8'}
            {/if}
        </span>
	</td>
	<td class="htr_email center" data-id="{if $hs.email_sent == 1}1{else}2{/if}">
		{if $hs.email_sent == 1}
		<i class="icon-envelope"></i>
		{/if}
	</td>
</tr>
{/foreach}