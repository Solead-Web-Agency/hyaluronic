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
 *}
<style>
{literal}
.action-time {
	background:#777;
}
.action-truck8 {
    background: #73c9e8 none repeat scroll 0 0;
}
.action-warning {
    background: #060068 none repeat scroll 0 0;
}
.left-content .col-lg-3{
	background:#F8F8F8;
}
.left-content .title{
	background:#99CCFF;
	text-transform:uppercase;
	font-size:12px;
	font-weight:bold;
	padding:5px;
	display:flex;

	-webkit-box-align: space-between;
	-webkit-align-items: space-between;
	-ms-flex-align: space-between;
	justify-content: space-between;

	-webkit-box-align: center;
	-webkit-align-items: center;
	-ms-flex-align: center;
	align-items: center;
}
.tracking-box .step-list {
	height: 250px;
    overflow: auto;
}
.tracking-box .title {
	color:#fff;
}
.left-content .step-list .item-rows {
	display:flex;
	-webkit-box-align: center;
	-webkit-align-items: center;
	-ms-flex-align: center;
	align-items: center;
	padding:14px 0;
}
.left-content .step-list .item-rows .icon {
	position:relative;
}
.left-content .step-list .item-rows .icon span {
	width:45px;
	height:45px;
	line-height:46px;
	text-align:center;
	border-radius:50%;
	border:1px solid #ebebeb;
	position:relative;
	background:{/literal}{$bg_color.default|escape:'htmlall':'UTF-8'}{literal};
	z-index:2;
	display:block;
	color:#fff;
}
.left-content .step-list .item-rows .icon:before {
	content:"";
	position:absolute;
	top:-15px;
	left:50%;
	height:calc(100% + 30px);
	width:2px;
	display:block;
	background:#ebebeb;
	z-index:1;
}
.left-content .step-list .item-rows .text {
	padding-left:15px;
}
.left-content .step-list .item-rows .text span {
	display:block;
}

.left-content .title span{
	display:inline-block;
	background:#008000;
	padding:5px;
	border-radius:5px;
}
.tracking-box .step-list .action-truck,.tracking-box .step-list .action-truck8, .dl_dashboard .action-truck, .dl_dashboard .action-truck8{
	transform: scale(-1, 1);
}
.tracking-box .action-truck {
	background:{/literal}{$bg_color.take_deli|escape:'htmlall':'UTF-8'}{literal} !important;
}
.tracking-box .action-truck8 {
	background:{/literal}{$bg_color.out_delivery|escape:'htmlall':'UTF-8'}{literal} !important;
}
.tracking-box .action-warning {
	background:{/literal}{$bg_color.warning|escape:'htmlall':'UTF-8'}{literal} !important;
}

.tracking-box .action-truck7{
	background:{/literal}{$bg_color.returned_shipper|escape:'htmlall':'UTF-8'}{literal} !important;
}
.tracking-box .action-basket{
	background:{/literal}{$bg_color.delivered_shop|escape:'htmlall':'UTF-8'}{literal} !important;
}
.tracking-box .htr_shipping .action-enabled{
	background:{/literal}{$bg_color.enable|escape:'htmlall':'UTF-8'}{literal} !important;
}

.tracking-box .action-barcode {
    background: {/literal}{$bg_color.vail_number|escape:'htmlall':'UTF-8'}{literal} !important;
}
.step_text {
	font-weight: bold;
}
{/literal}
</style>

<div class="popup_parent">
	<div class="popup_items">

	</div>
</div>
<ul class="nav nav-tabs" role="tablist" style="left:0;">
	<li role="presentation" class="steps_conf {if $tab_active == 1}active{/if}"><a href="#steps_conf" aria-controls="steps_conf" role="tab" data-toggle="tab">{l s='Steps' mod='deliveryorderautoupdate'}</a></li>
	<li role="presentation" class=" {if $tab_active == 2}active{/if} trackhistory_conf"><a href="#trackhistory_conf" aria-controls="trackhistory_conf" role="tab" data-toggle="tab">{l s='Tracking History' mod='deliveryorderautoupdate'}</a></li>
</ul>
<div class="tab-content tracking-box">

	<div role="tabpanel" class="tab-pane  {if $tab_active == 1}active{/if}" id="steps_conf">
		<div class="left-content">
			<div class="title">
				{assign var=event_status value="_"|explode:$history_left[0]['result']}
				{l s='Order ' mod='deliveryorderautoupdate'} {$id_order|escape:'htmlall':'UTF-8'}
				{if $history_left}
				<span class="{if $event_status[0] == 2}action-warning{elseif $event_status[0] == 3}action-truck{elseif $event_status[0] == 4}action-barcode{elseif $event_status[0] == 7}action-truck7{elseif $event_status[0] == 8}action-truck8{/if}" style="{if $event_status[0] == 1}background:{$bg_color.enable|escape:'htmlall':'UTF-8'}{elseif $event_status[0] == 6}background:{$bg_color.delivered_shop|escape:'htmlall':'UTF-8'}{/if}">{$event_status[1]|escape:'htmlall':'UTF-8'}</span>
				{/if}
			</div>
			<div class="step-list">
			{if $history_left}
			{foreach $history_left as $hs}
				{assign var=event_code_left value="_"|explode:$hs.result}
				<div class="flex-box item-rows">
					<div class="icon">
					{if $event_code_left[0] == 1}
						<span style="line-height:42px; background:{$bg_color.enable|escape:'htmlall':'UTF-8'}">
						<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/home.png" />
						</span>
					{elseif $event_code_left[0] == 2}
						<span class="icon-warning action-warning"></span>
					{elseif ($event_code_left[0] == 3)}
						<span class="icon-truck action-truck"></span>
					{elseif $event_code_left[0] == 4}
						<span class="icon-barcode action-barcode"></span>
					{elseif $event_code_left[0] == 6}
						<span style="line-height:42px; background:{$bg_color.delivered_shop|escape:'htmlall':'UTF-8'}">
						<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/shop.png"  />
						</span>
					{elseif ($event_code_left[0] == 7)}
						<span class="icon-truck action-truck7"></span>
					{elseif $event_code_left[0] == 8}
						<span class="icon-truck action-truck8"></span>
					{else}
						<span></span>
					{/if}
					</div>
					<div class="text">
						<span class="step_text"> {$event_code_left[1]|escape:'htmlall':'UTF-8'} </span>
						<span>{$hs.step_date|escape:'htmlall':'UTF-8'}</span>
					</div>
				</div>
			{/foreach}
			{else}
				<span style="color:#c8def7; text-align:center; font-size:22px">{l s='No step has been validated for this order' mod='deliveryorderautoupdate'}</span>
			{/if}
			</div>
		</div>
	</div>
	<div role="tabpanel" class="tab-pane {if $tab_active == 2}active{/if}" id="trackhistory_conf">
		<div class="table-responsive clearfix" style="width:100%;display:inline-block; height:300px; overflow:auto;">
			<table class="table dl_dashboard table_form">
				<thead>
					<tr class="nodrag nodrop">

						<th class=" center">
							<span class="title_box"> {l s='Date' mod='deliveryorderautoupdate'}  </span>
						</th>
						<th class=" center">
							<span class="title_box">{l s='Carrier response' mod='deliveryorderautoupdate'} </span>
						</th>
						<th class=" center">
							<span class="title_box">{l s='Shipping status' mod='deliveryorderautoupdate'} </span>
						</th>
						<th class=" center">
							<span class="title_box"> {l s='Method' mod='deliveryorderautoupdate'}  </span>
						</th>
						<th class=" center">
							<span class="title_box"> {l s='Mail' mod='deliveryorderautoupdate'}  </span>
						</th>
					</tr>
					<tr class="nodrag nodrop filter row_hover">

						<th class="center">
							<input class="filter" name="douhistory_Filter_date" value="" type="text">
						</th>
						<th class="center">
							<input class="filter" name="douhistory_Filter_carrier_response" value="" type="text">
						</th>
						<th class="center">
							<select name="douhistory_Filter_shipping">
								<option value="">-</option>
								{foreach $shipping as $s}
								<option value="{$s.name|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</th>
						<th class="center">
							<input class="filter" name="douhistory_Filter_method" value="" type="text">
						</th>
						<th class="center">
							<select name="douhistory_Filter_mail" style="width: 35px;">
								<option value="">-</option>
								<option value="1">{l s='Yes' mod='deliveryorderautoupdate'}</option>
								<option value="2">{l s='No' mod='deliveryorderautoupdate'}</option>
							</select>
						</th>

					</tr>
				</thead>
				<tbody class='dashboard_history'>
					{foreach $history as $hs}
						{assign var=event_code value="_"|explode:$hs.result}
						<tr class="tr_cols">
							<td class=" center">
								<span class="title_box htr_date_add" data-id="{$hs.date_add|escape:'htmlall':'UTF-8'}"> {$hs.date_add|escape:'htmlall':'UTF-8'} </span>
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
							<td class=" left htr_shipping" data-id="{$event_code[1]|escape:'htmlall':'UTF-8'}">
								{if $hs.event_code == 1}
									<a target="_blank" class="list-action-enable action-enabled" href="#" title="Active" status="1" id_order="{$hs.id|escape:'htmlall':'UTF-8'}">
										<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/home.png"/>
									</a>
									<div class="right_shipping infor_newbox home_1_box" >
										{$event_code[1]|escape:'htmlall':'UTF-8'}
									</div>
								{else if $hs.event_code == 2}
									<!-- Not delivery -->
									<a target="_blank" class="list-action-enable action-warning" href="#" title="Delivered problem" status="0" id_order="{$hs.id|escape:'htmlall':'UTF-8'}">
										<i class="icon-truck"></i>
									</a>
									<div class="right_shipping infor_newbox">
										{$event_code[1]|escape:'htmlall':'UTF-8'}
									</div>
								{else if ($hs.event_code == 3) || ($hs.event_code == 7) || ($hs.event_code == 8)}
									<!-- Take delivery -->
									<a target="_blank" class="list-action-enable  {if ($hs.event_code == 7)} action-truck7 {elseif ($hs.event_code == 8)} action-truck8 {else} action-truck {/if}" href="#" title="Take delivery" status="0" id_order="{$hs.id|escape:'htmlall':'UTF-8'}">
										<i class="icon-truck"></i>
									</a>
									<div class="right_shipping infor_newbox">
										{$event_code[1]|escape:'htmlall':'UTF-8'}
									</div>
								{else if $hs.event_code == 4}
									<!-- Take delivery -->
									<a target="_blank" class="list-action-enable action-barcode" href="#" title="Take delivery" status="0" id_order="{$hs.id|escape:'htmlall':'UTF-8'}">
										<i class="icon-barcode"></i>
									</a>

									<div class="right_shipping infor_newbox">
										{$event_code[1]|escape:'htmlall':'UTF-8'}
									</div>
								{else if ($hs.event_code == 0) || ($hs.event_code == 5)}


								{else if $hs.event_code == 6}
									<!-- Take delivery -->
									<a target="_blank" class="list-action-enable action-basket" href="#" title="Take delivery" status="0" id_order="{$hs.id|escape:'htmlall':'UTF-8'}">
										<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/shop.png" style="width: 13px;"/><!-- <i class="icon-shopping-basket"></i>-->
									</a>

									<div class="right_shipping infor_newbox">
										{$event_code[1]|escape:'htmlall':'UTF-8'}
									</div>
								{else}
									{$hs.event_code|escape:'htmlall':'UTF-8'}
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
				</tbody>
			</table>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		var timer;
		var step = 1;

		$(".trackhistory_conf").click(function() {
			$("input[name='tab_active']").val(2);
		});
		$(".steps_conf").click(function() {
			$("input[name='tab_active']").val(1);
		});
		$("select[name='douhistory_Filter_mail'], select[name='douhistory_Filter_shipping']").change(function() {
			filterHistory_Carrier();
		});
		$("input[name='douhistory_Filter_id_track'], input[name='douhistory_Filter_order'], input[name='douhistory_Filter_date'], input[name='douhistory_Filter_carrier'], input[name='douhistory_Filter_carrier_response'], input[name='douhistory_Filter_method']").keyup(function() {
			clearTimeout(timer);
			timer = setTimeout(function() {
				filterHistory_Carrier();
			}, 1000);
		});
		function filterHistory_Carrier()
		{
			var date = $("input[name='douhistory_Filter_date']").val();
			var carrier_response = $("input[name='douhistory_Filter_carrier_response']").val().toLowerCase();
			var shipping = $("select[name='douhistory_Filter_shipping']").val().toLowerCase();
			var method = $("input[name='douhistory_Filter_method']").val().toLowerCase();
			var email = $("select[name='douhistory_Filter_mail']").val();
			$(".dl_dashboard tbody tr").each(function() {
				var date_ = $(this).find('.htr_date_add').data('id');
				var carrier_response_ = $(this).find('.carrier_response').data('id').toLowerCase();
				var shipping_ = $(this).find('.htr_shipping').data('id').toLowerCase();
				var method_ = $(this).find('.htr_method').data('id').toLowerCase();
				var email_ = $(this).find('.htr_email').data('id');

				var hide_date = 0;
				var hide_carrier_response = 0;
				var hide_shipping = 0;
				var hide_method = 0;
				var hide_email = 0;
				if (!date && !carrier_response && !shipping && !method && !email) {
					$(this).show();
				} else {

					if (email) {
						if (email == email_) {
							hide_email = 0;
						} else {
							hide_email = 1;
						}
					} else {
						hide_email = 0;
					}

					if (date) {
						if (date_.indexOf(date) == 0) {
							hide_date = 0;
						} else {
							hide_date = 1;
						}
					} else {
						hide_date = 0;
					}
					if (carrier_response) {
						if (carrier_response_.indexOf(carrier_response) == 0) {
							hide_carrier_response = 0;
						} else {
							hide_carrier_response = 1;
						}
					} else {
						hide_carrier_response = 0;
					}
					if (shipping) {
						if (shipping_.indexOf(shipping) == 0) {
							hide_shipping = 0;
						} else {
							hide_shipping = 1;
						}
					} else {
						hide_shipping = 0;
					}
					if (method) {
						if (method_.indexOf(method) == 0) {
							hide_method = 0;
						} else {
							hide_method = 1;
						}
					} else {
						hide_method = 0;
					}
					if ((hide_date) || (hide_carrier_response) || (hide_shipping) || (hide_method) || (hide_email)) {
						$(this).hide();
					} else {
						$(this).show();
					}
				}
			});
		}
	});
</script>