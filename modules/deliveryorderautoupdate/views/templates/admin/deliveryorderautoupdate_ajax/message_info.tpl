{*
* 2007-2021 PrestaShop
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
*  @copyright  2007-2021 Helloshop
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Helloshop
*}
<form id="delivery_carrier_form" class="delivery_carrier_form defaultForm form-horizontal " action="#" method="post" enctype="multipart/form-data" novalidate="">
	<style>
		{literal}

		.center {
			padding: 0 30px;
		}

		.message_infor .infor_box {
			font-size:12px;
			display: inline-block;
		}

		.deliveryorderautoupdate_form thead .title_box{font-size:16px;}
		.close_popup{
			color: #888;
			display: inline-block;
			font-size: 15px;
			position: absolute;
			right: 1%;
			top: 2%;
			cursor:pointer;
		}
		.message_infor .refresh_orders {
			height: 32px;
			line-height: 2em !important;
			margin: 0 5px;
			position: absolute;
			right: 56px;
			width: 32px;
		}
		.deliveryorderautoupdate_form .tr_cols .title_box{
			color:#777;
			font-size:15px;
		}
		.message_infor .img-responsive{
			left: 40%;
			position: absolute;
			top: 22%;
		}
		.deliveryorderautoupdate_form.table_form thead tr th {
			line-height:1em;
		}

		.orderform_review .table td.center, .orderform_review .table th.center {
			line-height: 0.9em;
		}
		.orderform_review .deliveryorderautoupdate_form .title_box {
			font-size: 12px;

		}
		.message_infor .action-truck{
			background:{/literal}{$bg_color.take_deli|escape:'htmlall':'UTF-8'}{literal};
			transform: scale(-1, 1);
		}
		.message_infor .action-truck7{
			background:{/literal}{$bg_color.returned_shipper|escape:'htmlall':'UTF-8'}{literal};
		}
		.message_infor .action-truck8{
			background:{/literal}{$bg_color.out_delivery|escape:'htmlall':'UTF-8'}{literal};
			transform: scale(-1, 1);
		}
		.message_infor .action-basket{
			background:{/literal}{$bg_color.delivered_shop|escape:'htmlall':'UTF-8'}{literal};
		}
		.message_infor .action-warning{
			background:{/literal}{$bg_color.warning|escape:'htmlall':'UTF-8'}{literal};
		}
		.message_infor .action-barcode{
			background:{/literal}{$bg_color.vail_number|escape:'htmlall':'UTF-8'}{literal};
		}
		.message_infor .last_result_carrier .action-enabled{
			background:{/literal}{$bg_color.enable|escape:'htmlall':'UTF-8'}{literal};
		}
		.action-time {
			background:#777;
		}
		.service_id a img {
			margin-top: 7px;
		}
		{/literal}
	</style>

		<div class='message_infor_box bootstrap ' style='display:block'>
			<div class="message_infor">
				<div class="panel">
					<div class="panel-heading" style="padding: 0px; line-height:1em;">
						<div style="color:#777; font-size: 13px; font-weight: 600; display: inline-block;">
							<img src="../modules/deliveryorderautoupdate/logo.png" width="22px" alt="close" title="close" class="" />
							{l s='Tracker And Update' mod='deliveryorderautoupdate'}
						</div>
						<div class="panel-heading-act pull-right">
							<a href="#" style="margin:0 5px;" class="close_popup btn btn-default pull-right"> <i class="icon-close" /></a>

							<a href="#" name="refresh_orders" style="margin:0 5px;" class="btn btn-default refresh_orders"><i class="icon-refresh" /></a>
						</div>
					</div>

					<div class="panel_body">

						<img class="img-responsive img_warning" style="position:absolute; top:41%;display:none" src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/loading.gif" alt="" />

						<div class="table-responsive clearfix orderform_review" style="width:100%;display:inline-block; height:400px; overflow:auto;">
							<table class="table deliveryorderautoupdate_form table_form">
								<thead>
									<tr class="nodrag nodrop">
										<th class=" center">
											<a href="#" style="position: relative;top: -6px;" id="check_mailsend"><span><i class="icon-check-sign"></i></span></a>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Order' mod='deliveryorderautoupdate'} </span>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Order status' mod='deliveryorderautoupdate'}  </span>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Connector' mod='deliveryorderautoupdate'}  </span>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Parcel Number' mod='deliveryorderautoupdate'}  </span>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Carrier Result' mod='deliveryorderautoupdate'}  </span>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Shipping Status' mod='deliveryorderautoupdate'}  </span>
										</th>
										<th class="center">

										</th>
									</tr>
									<tr class="nodrag nodrop filter row_hover">
										<th class="text-center">
										--
										</th>

										<th class="center">
											<input class="filter" name="dou_Filter_id_order" value="" type="text">
										</th>
										<th class="center">
											<select class="dou_Filter_current_status" name="dou_Filter_current_status">
												<option value="">-</option>
												{foreach $status as $s}
												<option value="{$s.name|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall':'UTF-8'}</option>
												{/foreach}
											</select>
										</th>
										<th class="center">
											<select class="dou_Filter_carrier" name="dou_Filter_carrier">
												<option value="">-</option>
												{foreach $carrier as $s}
												<option value="{$s.name|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall':'UTF-8'}</option>
												{/foreach}
											</select>
										</th>
										<th class="center">
											<input class="filter" name="dou_Filter_parcel_number" value="" type="text">
										</th>
										<th class="center">
										</th>
										<th class="center">
											<select name="doutrack_Filter_result">
												<option value="0">-</option>
												<option value="1">{l s='Delivered' mod='deliveryorderautoupdate'}</option>
												<option value="2" selected>{l s='Not Delivered' mod='deliveryorderautoupdate'}</option>
											</select>
										</th>
										<th class="center">

										</th>

									</tr>
								</thead>
								<tbody class='del_message'>
								{if $error_message}
									<tr class="tr_cols">
										<th class=" center">
											<span class="title_box"> {$error_message|escape:'htmlall':'UTF-8'} </span>
										</th>
									</tr>
								{else}
									{if $orders}
									{assign var=carrier_name value=''}
									{foreach $orders as $order}
										{assign var=event_code value="_"|explode:$order.event_code}
										<tr class="tr_cols"  data-event_code="{$event_code[0]|escape:'htmlall':'UTF-8'}" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}"  {if $event_code[0] == 1} style="display:none;" {/if} >

											<td class="text-center">
												<input class="mailsend" value="{$order.id_order|escape:'htmlall':'UTF-8'}" {if $order.method != 2} checked {else} disabled {/if} name="mailBox[]" type="checkbox">
											</td>
											<td class=" center service_id">
												<a target="_blank" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}">
													<span class="title_box order_idfields"> {$order.id_order|escape:'htmlall':'UTF-8'}  </span>
												</a>
											</td>
											<td class=" center">
												<span class="label color_field " style="background-color:{$order.current_state_color|escape:'htmlall':'UTF-8'};color:white">
													{$order.current_state|escape:'htmlall':'UTF-8'}
												</span>
											</td>
											<td class=" center">
												<a target="_blank" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}">
													<span class="title_box carrier_orderfield"> {$order.carrier|escape:'htmlall':'UTF-8'}  </span>
												</a>
											</td>
											<td class=" center">
												<span class="title_box "> {$order.track_number|escape:'htmlall':'UTF-8'}  </span>
											</td>

											<td class=" center" style="padding-left: 3%;">
												<span class="title_box status_carrier status_carrier_{$order.id_order|escape:'htmlall':'UTF-8'}">
													{if $order.success_response == 0}
														<a target="_blank" class="list-action-enable action-disabled" href="#" title="Disable" status="0" >
															<i class="icon-remove"></i>
														</a>
													{else}
														<a target="_blank" class="list-action-enable action-enabled" href="#" title="Active">
															<i class="icon-check"></i>
														</a>
													{/if}
													{$order.last_result|escape:'htmlall':'UTF-8'}
												</span>
												<span class="loading_carrier loading_carrier_{$order.id_order|escape:'htmlall':'UTF-8'}" style="display:none;">
													<img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/loading_carrier.gif" title="{l s='Loading' mod='deliveryorderautoupdate'}" alt="{l s='Loading' mod='deliveryorderautoupdate'}">
												</span>
											</td>
											<td class=" left">
												<span class="title_box result_carrier last_result_carrier last_result_carrier_{$order.id_order|escape:'htmlall':'UTF-8'}">

													{if $order.event_code == 1}
														<a target="_blank" class="list-action-enable action-enabled" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
															<i class="icon-check"></i>
														</a>
														{$order.event_code|escape:'htmlall':'UTF-8'}
													<!-- <span class="title_box">{l s='Delivered' mod='deliveryorderautoupdate'}</span> -->
													{elseif $order.event_code == 2}
														<a target="_blank" class="list-action-enable action-warning" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
															<i class="icon-truck"></i>
														</a>
															{$order.event_code|escape:'htmlall':'UTF-8'}
															<!-- <span class="title_box">{l s='Delivery Problem' mod='deliveryorderautoupdate'} </span> -->
													{elseif ($order.event_code == 3) || ($order.event_code == 7) || ($order.event_code == 8)}
														<a target="_blank" class="list-action-enable {if ($order.event_code == 7)} action-truck7 {elseif ($order.event_code == 8)} action-truck8 {else} action-truck {/if}" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
															<i class="icon-truck"></i>
														</a>
														<!--
														<span class="title_box">
															{if $order.event_code == 3}{l s='In Transit' mod='deliveryorderautoupdate'} {elseif $order.event_code == 7} {l s='Returned to shipper' mod='deliveryorderautoupdate'} {elseif $order.event_code == 8} {l s='Out for delivery' mod='deliveryorderautoupdate'} {/if}
														</span>-->
														{$order.event_code|escape:'htmlall':'UTF-8'}
														<!-- <span class="title_box">{l s='In Transit' mod='deliveryorderautoupdate'} </span> -->
													{elseif $order.event_code == 4}
														<a target="_blank" class="list-action-enable action-barcode" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
															<i class="icon-barcode"></i>
														</a>
														{$order.event_code|escape:'htmlall':'UTF-8'}
														<!-- <span class="title_box">{l s='Shipment created' mod='deliveryorderautoupdate'}</span> -->
													{elseif $order.event_code == 0}
													-
													<!--
														<a target="_blank" class="list-action-enable action-disabled" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Disable" status="0" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
															<i class="icon-remove"></i>
														</a>
														{$order.event_code|escape:'htmlall':'UTF-8'}
													-->
													{elseif $order.event_code == 6}
														<a target="_blank" class="list-action-enable action-basket" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
															<img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/shop.png" style="width: 12px;" /><!-- <i class="icon-shopping-basket"></i>-->
														</a>
														{$order.event_code|escape:'htmlall':'UTF-8'}
													{else}
														{$order.event_code|escape:'htmlall':'UTF-8'}
													{/if}

												</span>
												<span class="loading_carrier loading_carrier_{$order.id_order|escape:'htmlall':'UTF-8'}" style="display:none;">
													<img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/loading_carrier.gif" title="{l s='Loading' mod='deliveryorderautoupdate'}" alt="{l s='Loading' mod='deliveryorderautoupdate'}">
												</span>
											</td>


											<td class=" center">
											{if $order.method != 2}
												<div class="btn-group-action">
													<div class="btn-group pull-right">
														<a class="btn btn-default update_ordercarrier update_ordercarrier_{$order.id_order|escape:'htmlall':'UTF-8'}" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}" name="update_ordercarrier">
															<img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/check.png" title="{l s='Play' mod='deliveryorderautoupdate'}" alt="{l s='Play' mod='deliveryorderautoupdate'}" style="height:14px;">
															{l s='Track' mod='deliveryorderautoupdate'}
														</a>

														<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
															<span class="caret"> </span>
														</button>
														<ul class="dropdown-menu">
															<li>
																<a class="view_carrier" target="_blank" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" >
																	<i class="icon-eye"></i>
																	{l s='View public tracking' mod='deliveryorderautoupdate'}
																</a>
															</li>
														</ul>
													</div>
												</div>
											{/if}
											</td>
										</tr>
									{/foreach}

									{else}
										<tr class="tr_cols">

											<td class="center" colspan="3">
												<a target="_blank" href="#">
												<span class="title_box"> {l s='There is no orders to check.' mod='deliveryorderautoupdate'}  </span>
												</a>
											</td>
										</tr>
									{/if}
								</tbody>
								{/if}
							</table>
						</div>

					</div>

					<table class="table deliveryorderautoupdate_form">
						<tr class="tr_cols">

							<td class=" left">
								<a class="btn btn-default start_progress pull-right center" href="#" title="{l s='Start' mod='deliveryorderautoupdate'}" style="padding: 10px 25px; font-size: 14px; font-weight: 600; ">
									<img src="{$url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/check.png" />

									{l s='Start' mod='deliveryorderautoupdate'}
								</a>
								{if $l == 0}
									<a  class="btn btn-default" href="{$config_url|escape:'html':'UTF-8'}" style="font-size: 14px;color: #777;"> <i class="icon-cogs" style="padding: 6px;" ></i> {l s='Go to configuration' mod='deliveryorderautoupdate'}</a>
								{/if}
							</td>
						</tr>
					</table>

				</div>
			</div>
	</div>
	<script type="text/javascript">
	$(document).ready(function () {

		$(".close_popup").click(function(){
			$(".delivery_carrier_form").remove();
		});

		$("#check_mailsend").toggle(function() {
			$("#check_mailsend span").html('<i class="icon-check-empty"></i>');
			checkDelBoxes($('#delivery_carrier_form').get(0), 'mailBox[]', false, 1);
		}, function(){
			$("#check_mailsend span").html('<i class="icon-check-sign"></i>');
			checkDelBoxes($('#delivery_carrier_form').get(0), 'mailBox[]', true, 1);
		});
		var timer;
		$("input[name=dou_Filter_id_order]").keyup(function(){
			var id_order = $(this).val();
			clearTimeout(timer);
			timer = setTimeout(function() {
				filterCarrier();

			}, 1000);
		});
		$("select[name='doutrack_Filter_result']").change(function() {
			var shipping_status = $("select[name='doutrack_Filter_result']").val();
			$(".del_message tr").each(function() {
				var event_code = $(this).data('event_code');
				if (shipping_status == 1) {
					if (event_code != 1) {
						$(this).hide();
						$(this).children().children().removeAttr('checked');
					} else {
						$(this).show();
						$(this).children().children().attr('checked', true);
					}
				} else if (shipping_status == 2) {
					if (event_code == 1) {
						$(this).hide();
						$(this).children().children().removeAttr('checked');
					} else {
						$(this).show();
						$(this).children().children().attr('checked', true);
					}
				} else {
					$(this).show();
					$(this).children().children().attr('checked', true);
				}
			});
		});
		$(".dou_Filter_carrier, .dou_Filter_current_status").change(function() {
			filterCarrier();
		});

		function filterCarrier()
		{
			var id = $("input[name=dou_Filter_id_order]").val();
			var carrier = $(".dou_Filter_carrier").val();
			var status = $(".dou_Filter_current_status").val();
			$(".deliveryorderautoupdate_form .del_message tr").each(function() {
				var id_field = parseInt($(this).children().find('.order_idfields').html());
				var carrier_field = $(this).children().find('.carrier_orderfield').html().trim();
				var status_field = $(this).children().find('.color_field ').html().trim();
				var hide_id = 0;
				var hide_carrier = 0;
				var hide_status = 0;
				if (!id && !carrier && !status) {
					$(this).show();
				} else {
					if (id) {
						if (id == id_field) {
							hide_ = 0;
						} else {
							hide_ = 1;
						}
					} else {
						hide_ = 0;
					}
					if (carrier) {
						if (carrier.indexOf(carrier_field) == 0) {
							hide_carrier = 0;
						} else {
							hide_carrier = 1;
						}
					} else {
						hide_carrier = 0;
					}
					if (status) {
						if (status_field.indexOf(status) == 0) {
							hide_status = 0;
						} else {
							hide_status = 1;
						}
					} else {
						hide_status = 0;
					}
					if ((hide_) || (hide_carrier) || (hide_status)) {
						$(this).hide();
					} else {
						$(this).show();
					}
				}

			});
		}
		$(".update_ordercarrier").click(function() {
			var orders = new Array();
			var id_order = $(this).attr("id_order");
			orders.push(id_order);
			$(".status_temporized_"+id_order).css("display", "none");
			$(".last_result_carrier_"+id_order).css("display", "none");
			$(".status_carrier_"+id_order).css("display", "none");
			$(".loading_carrier_"+id_order).css("display", "unset");
			updateStatus(orders);
		});
		$(".start_progress").click(function() {
			$(".result_carrier").css("display", "none");
			$(".status_carrier").css("display", "none");
			var orders = getmaillist($('.mailsend:checked'));
			updateStatus(orders);
		});
		function getmaillist(option)
		{
			var orders = new Array();
			var id_order;
			option.each(function() {
				id_order = $(this).val();
				orders.push(id_order);
				$(".loading_carrier_"+id_order).css("display", "unset");
			});
			return orders;
		}
		function updateStatus(orders)
		{
			var step = 0;
			if (orders.length > 1) {
				order = orders[0];
				orders.shift();
				step = 1;
			} else {
				order = orders[0];
			}

			$.ajax({
				type: 'POST',
				url: '{$admin_url|escape:'htmlall':'UTF-8'}',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateCarriers',
					ajax : true,
					id_carrier : '{$id|escape:'htmlall':'UTF-8'}',
					orders : order,
					secure_key: secure_key,
				},
				success: function(return_data)
				{
					var data = JSON.parse(return_data);
					$(".last_result_carrier_"+order).html(data.last_result_carrier);
					$(".status_carrier_"+order).html(data.status_carrier);
					$(".last_result_carrier_"+order).css("display", "unset");
					$(".status_temporized_"+order).css("display", "unset");
					$(".status_carrier_"+order).css("display", "unset");
					$(".loading_carrier_"+order).css("display", "none");
					if (step) {
						updateStatus(orders);
					}
				},
				error: function () {
					$(".last_result_carrier_"+order).css("display", "unset");
					$(".status_temporized_"+order).css("display", "unset");
					$(".status_carrier_"+order).css("display", "unset");
					$(".loading_carrier_"+order).css("display", "none");
				}
			});
		}


	});
	</script>
</form>