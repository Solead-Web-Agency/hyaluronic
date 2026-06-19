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

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'delivery_order'}
	<style>
	{literal}
	.bootstrap .form-horizontal .form-wrapper {
		padding: 0;
	}
	#main_conf .bootstrap label.control-label {
		font-size:12px;
	}
	.carrier_form .center {
		vertical-align: middle!important;
	}
	.header_title {
		font-weight:600 !important;
	}
	.checkstt_form {
		padding-bottom: 6px;
	}
	.checkstt_form input {
		position:absolute;
	}
	.checkstt_form span {
		position:inherit;
		left: 21px;
		top: 2px;
	}
	.ui-front {
		z-index: 1000!important;
	}
	.ui-dialog-titlebar-close {
    	background-position: -96px -128px;
    	display: none;
	}
	thead th {
		text-align: center;
	}
	{/literal}
	</style>
	<style type="text/css">
		.modal-dialog-centered::before {
			display: block;
			height: calc(100vh - 1rem);
			content: "";
		}
		.modal-dialog-centered {
			display: flex;
			align-items: center;
		}
		.doc {
			padding: 15px;
			text-align: center;
			margin: 10px;
		}
		.doc-group {
			display: flex;
			flex-direction: column;
			width: 40%;
			justify-content: space-between;
			margin: 30px auto;
		}
		.status {
			width: 30px;
			height: 30px;
			display: inline-block;
			border-radius: 8px;
			margin-right: 5px;
		}
		.status img {
			padding: 6px;
			width: 100%;
		}
		input[type=radio] {
			margin-right: 5px;
		}

	</style>
	<div id="dialog-confirm" title="{l s='Uninstall connector' mod='deliveryorderautoupdate'}" style="display: none">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin-right:5px;"></span>{l s='Do you want to delete credentials ?' mod='deliveryorderautoupdate'}</p>
	</div style="margin: 0 5px">
		<div class="alert alert-warning warning_ajax" style="display:none; width: 100%; margin-bottom: 40px;" >
			<span class="protocolcurrent" style="display:none;">{l s='Please connect with https to ensure proper functionning of module' mod='deliveryorderautoupdate'} <br/></span>
			<span class="wwwwcurrent" style="display:none;">{l s='please connect with www to ensure proper functionning of module' mod='deliveryorderautoupdate'}</span>
		</div>
		<div>
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active lb_print_inetform"><a href="#main_conf" aria-controls="main_conf" role="tab" data-toggle="tab">{l s='General' mod='deliveryorderautoupdate'}</a></li>
				<li role="presentation" class="all_couriers"><a href="#all_couriers" aria-controls="all_couriers" role="tab" data-toggle="tab">{l s='Connectors' mod='deliveryorderautoupdate'}</a></li>
				<li role="presentation" class="emailform"><a href="#email_conf" aria-controls="email_conf" role="tab" data-toggle="tab">{l s='Email' mod='deliveryorderautoupdate'}</a></li>

				<li class="pull-right"><a href="#" class="guide">{l s='Guide' mod='deliveryorderautoupdate'}</a></li>

			</ul>
			<!-- load icon when run ajax -->
			<div class="popup_parent">
				<div class="popup_items">

				</div>
			</div>
			<!-- Tab panes -->
			<input type="hidden" value="{$fields_value.url_ajax|escape:'htmlall':'UTF-8'}" name="url_ajax" />
			<input type="hidden" value="{$fields_value.url_root|escape:'htmlall':'UTF-8'}" name="url_root" />
			<input type="hidden" value="{$fields_value.token|escape:'htmlall':'UTF-8'}" name="secure_key" />
			<div class="tab-content" style="margin-top:20px;">

				<div role="tabpanel" class="tab-pane active" id="main_conf">
					<div class="note_conf" style="color:green; text-align:center; font-size:14px; display:none">{l s='Your request has been succeded.' mod='deliveryorderautoupdate'}</div>
					<div class="alert alert-info">
						{l s='To use Tracking center, go to' mod='deliveryorderautoupdate'}
						<a href="{$fields_value.admin_dashboard|escape:'htmlall':'UTF-8'}">{l s='Menu > Orders > Tracking Center' mod='deliveryorderautoupdate'}</a>
					</div>
					<div class="config_module">
						<div class="form-group" data-tab-id="export">
							<label class="control-label col-lg-3 header_title"> {l s='Orders to track' mod='deliveryorderautoupdate'} </label>
							<div class="col-lg-9 ">
								<hr/>
							</div>
						</div>
						<div class="form-group" data-tab-id="export">
							<label class="control-label col-lg-3"> {l s='From' mod='deliveryorderautoupdate'} </label>
							<div class="col-lg-9 ">
								<div class="row">
									<div class="input-group col-lg-4">
										<input
											type="text"
											data-hex="true"
											class="datepicker"
											name="date_requestorder"
											value="{$fields_value.date_requestorder|escape:'html':'UTF-8'}" />
										<span class="input-group-addon">
										<i class="icon-calendar-empty"></i>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="alert alert-info">
						{l s='Only shipments from orders having status marked as shipped will be tracked by module, to mark status as shipped, go to Menu/Orders/Statuses, then edit a status and mark it as shipped' mod='deliveryorderautoupdate'}</a>
						</div>
						<div class="form-group" data-tab-id="export">
							<div class="row">
								<label class="control-label col-lg-3"> {l s='Statuses to exclude' mod='deliveryorderautoupdate'} </label>
								<div class="col-lg-4">
								{capture name=status_exclude assign=status_exclude},{$fields_value.status_exclude|escape:'htmlall':'UTF-8'},{/capture}
									<select class="chosen" name="status_exclude[]" multiple>
										{foreach $fields_value.order_export as $order}
										{capture name=id_order_check assign=id_order_check},{$order.id|escape:'htmlall':'UTF-8'},{/capture}
										<option value="{$order.id|escape:'htmlall':'UTF-8'}" {if $status_exclude|strpos:$id_order_check !== false}selected{/if}>{$order.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="form-group" data-tab-id="export">
							<div class="row">
								<label class="control-label col-lg-3"> {l s='Carriers to exclude' mod='deliveryorderautoupdate'} </label>
								<div class="col-lg-4">
								{capture name=carrier_exclude assign=carrier_exclude},{$fields_value.carrier_exclude|escape:'htmlall':'UTF-8'},{/capture}
									<select class="chosen" name="carrier_exclude[]" multiple>
										{foreach $fields_value.carriers as $carrier}
										{capture name=id_carrier_check assign=id_carrier_check},{$carrier.id_reference|escape:'htmlall':'UTF-8'},{/capture}
										<option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}" {if $carrier_exclude|strpos:$id_carrier_check !== false}selected{/if}>{$carrier.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="form-group" data-tab-id="export">
							<div class="row">
								<label class="control-label col-lg-3 header_title"> {l s='Update status and send email' mod='deliveryorderautoupdate'} </label>
								<div class="col-lg-9 col-sm-12 col-xs-12">
								<hr />
								</div>
							</div>
						</div>
						<div class="form-group" data-tab-id="export">
							<div class="row">
								<div class="col-lg-3"></div>
								<div class="col-lg-6">
									<table class="table">
										<thead>
											<tr>
												<th scope="col" class="col-lg-5">{l s='Shipping status' mod='deliveryorderautoupdate'}</th>
												<th scope="col" class="col-lg-3">{l s='Update order status to' mod='deliveryorderautoupdate'}</th>
												<th scope="col" class="col-lg-1">{l s='Notify customer' mod='deliveryorderautoupdate'}</th>
												<th scope="col" class="col-lg-1">{l s='Notify admin (cron task only)' mod='deliveryorderautoupdate'}</th>
											</tr>
										</thead>
										<tbody>
											{foreach $statuses as $i => $status}
											<tr>
												<td>
													<div class="status" style="background-color: {$status->color|escape:'htmlall':'UTF-8'};">
														<img src="{$status_img|escape:'htmlall':'UTF-8'}{$status->id_status|escape:'htmlall':'UTF-8'}.png">
													</div>
													<span>{$status->id_status|escape:'htmlall':'UTF-8'} - {$status->name|escape:'htmlall':'UTF-8'}</span>
												</td>
												<td>
													<select name="DELIVERY_ORDER_STATUS_TO[{$status->id_status|escape:'htmlall':'UTF-8'}]">
														<option value="-1">{l s='No update' mod='deliveryorderautoupdate'}</option>
														{foreach $orderStates as $state}
														<option value="{$state.id_order_state|escape:'htmlall':'UTF-8'}" {if isset($fields_value.DELIVERY_ORDER_STATUS_TO[$status->id_status]) && $fields_value.DELIVERY_ORDER_STATUS_TO[$status->id_status] == $state.id_order_state}selected{/if}>{$state.name|escape:'htmlall':'UTF-8'}</option>
														{/foreach}
													</select>
												</td>
												<td>
												{if $i > 0 && $i < 100}
													<input type="checkbox" {if in_array($status->id_status, $fields_value.event_code_mail)} checked {/if} value="{$status->id_status|escape:'htmlall':'UTF-8'}" name="event_code_mail[]" />
												{/if}
												</td>
												<td>
													<input type="checkbox" {if in_array($status->id_status, $fields_value.DELIVERY_EVENT_CODE_MAIL_ADMIN)} checked {/if} value="{$status->id_status|escape:'htmlall':'UTF-8'}" name="DELIVERY_EVENT_CODE_MAIL_ADMIN[]" />
												</td>
											</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								{l s='Admin emails' mod='deliveryorderautoupdate'}
							</label>
							<div class="col-lg-6">
								<input type="text" class="tagify tags" name="DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS" value="{if $fields_value.DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS}{$fields_value.DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS|escape:'htmlall':'UTF-8'}{/if}">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								{l s='Report error to module editor' mod='deliveryorderautoupdate'}
							</label>
							<div class="col-lg-9">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" value="1" id="HL_TRACKING_REPORT_ERROR_on" {if $fields_value.HL_TRACKING_REPORT_ERROR == 1}checked="checked"{/if} name="HL_TRACKING_REPORT_ERROR">
									<label for="HL_TRACKING_REPORT_ERROR_on">Yes</label>
									<input type="radio" value="0" id="HL_TRACKING_REPORT_ERROR_off" {if $fields_value.HL_TRACKING_REPORT_ERROR != 1}checked="checked"{/if} name="HL_TRACKING_REPORT_ERROR">
									<label for="HL_TRACKING_REPORT_ERROR_off">No</label>
									<a class="slide-button btn"></a>
								</span>
								<p class="help-block">
									{l s='This will send: id_connector, shipping_number, carrier_shipping_status, carrier_shipping_description, module_version, prestashop_version, tracking_method, shop_url' mod='deliveryorderautoupdate'}
								</p>
							</div>
						</div>
						<div class="form-group" data-tab-id="export">
							<div class="row">
								<label class="control-label col-lg-3 header_title"> {l s='Display widgets' mod='deliveryorderautoupdate'} </label>
								<div class="col-lg-9 col-sm-12 col-xs-12">
									<hr />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								{l s='Shipping status as bubbles in order list' mod='deliveryorderautoupdate'}
							</label>
							<span class="switch prestashop-switch fixed-width-lg col-lg-9">
								<input type="radio" value="1" id="order_show_status_on" {if $fields_value.order_show_status == 1}checked="checked"{/if} name="order_show_status">
								<label for="order_show_status_on">Yes</label>
								<input type="radio" value="0" id="order_show_status_off" {if $fields_value.order_show_status != 1}checked="checked"{/if} name="order_show_status">
								<label for="order_show_status_off">No</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								{l s='Tracking block in order page' mod='deliveryorderautoupdate'}
							</label>
							<span class="switch prestashop-switch fixed-width-lg col-lg-9">
								<input type="radio" value="1" id="HL_ORDER_TRACKING_BLOCK_on" {if $fields_value.HL_ORDER_TRACKING_BLOCK == 1}checked="checked"{/if} name="HL_ORDER_TRACKING_BLOCK">
								<label for="HL_ORDER_TRACKING_BLOCK_on">Yes</label>
								<input type="radio" value="0" id="HL_ORDER_TRACKING_BLOCK_off" {if $fields_value.HL_ORDER_TRACKING_BLOCK != 1}checked="checked"{/if} name="HL_ORDER_TRACKING_BLOCK">
								<label for="HL_ORDER_TRACKING_BLOCK_off">No</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								{l s='Shipping steps in customer area' mod='deliveryorderautoupdate'}
							</label>
							<span class="switch prestashop-switch fixed-width-lg col-lg-9">
								<input type="radio" value="1" id="HL_CUSTOMER_SHIPPING_STEP_on" {if $fields_value.HL_CUSTOMER_SHIPPING_STEP == 1}checked="checked"{/if} name="HL_CUSTOMER_SHIPPING_STEP">
								<label for="HL_CUSTOMER_SHIPPING_STEP_on">Yes</label>
								<input type="radio" value="0" id="HL_CUSTOMER_SHIPPING_STEP_off" {if $fields_value.HL_CUSTOMER_SHIPPING_STEP != 1}checked="checked"{/if} name="HL_CUSTOMER_SHIPPING_STEP">
								<label for="HL_CUSTOMER_SHIPPING_STEP_off">No</label>
								<a class="slide-button btn"></a>
							</span>
						</div>

					</div>

					<div class="form-group" data-tab-id="export">
						<div class="row">
							<label class="control-label col-lg-3 header_title"> {l s='Track automatically' mod='deliveryorderautoupdate'} </label>
							<div class="col-lg-9 col-sm-12 col-xs-12">
							<hr />
							</div>
						</div>
					</div>

						<div class="form-group" data-tab-id="export">
							<label class="alert alert-info draft col-lg-12 col-xs-12 col-sm-12">
								{l s='Put this URL in your CRON tab to run tracking process automatically' mod='deliveryorderautoupdate'}
							</label>
							<div class="col-lg-3">
								<select name="carrier_connect" class="carrier_connect" style="float: right;width: auto;">
									<option value="0">{l s='All Connectors' mod='deliveryorderautoupdate'}</option>
									{foreach $fields_value.connectors as $cnn}
									<option value="{$cnn.id|escape:'htmlall':'UTF-8'}">{$cnn.name|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-9 col-xs-12 col-sm-12">
								<div class="row">
									<div class="input-group col-lg-10 col-xs-10 col-sm-10" style="display:flex;align-item:center">
										<input
											id="cron_url"
											type="text"
											name="cron_url"
											readonly
											value="{$fields_value.cron_url|escape:'htmlall':'UTF-8'}" />
										<a class="btn btn-default " id="cron_task_button" data-copytarget="#cron_url"><i class="icon-copy"></i><span style="padding-left: 4px;">{l s='copy' mod='deliveryorderautoupdate'}</span></a>

									</div>
								</div>
							</div>
						</div>
						<div class="form-group" data-tab-id="export">
							<div class="col-lg-3">
								<label class="control-label pull-right">{l s='Response type' mod='deliveryorderautoupdate'}</label>
							</div>
							<div class="col-lg-9 col-xs-12 col-sm-12">
								<div class="col-lg-6 col-sm-12 col-xs-6">
									<input type="radio" name="HL_TRACKING_CRON_RESPONSE" value="0" {if !$fields_value.response_type}checked{/if}> <label class="control-label" style="margin-right: 15px;">{l s='Summary' mod='deliveryorderautoupdate'}</label>
									<input type="radio" name="HL_TRACKING_CRON_RESPONSE" value="1" {if $fields_value.response_type}checked{/if}> <label class="control-label">{l s='Detailed' mod='deliveryorderautoupdate'}</label>
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<button class="btn btn-default pull-right" name="submitItem" id="module_form_submit_btn" value="1" type="submit">
								<i class="process-icon-save"></i> {l s='Save' mod='deliveryorderautoupdate'}
							</button>
						</div>
				</div>
						<!-- end panel -->

				<div role="tabpanel" class="tab-pane " id="all_couriers">
					<div class="list"></div>
					<div class="alert alert-info">
						{l s='If you don\'t found connector to your carrier API, then ask us to add it in the module. For this, please ask to your technical contact the API Guide and' mod='deliveryorderautoupdate'}
						<a target="_blank" href="https://addons.prestashop.com/en/contact-us?id_product=22347">{l s='send us via your customer area' mod='deliveryorderautoupdate'}</a>{l s=', we will implement it within 3 working days' mod='deliveryorderautoupdate'}
					</div>
				</div>
				<div role="tabpanel" class="tab-pane " id="email_conf">
					<div class="row well">
						<div class="col-lg-9 col-sm-12 col-xs-12">
							<div class="col-lg-12 col-sm-12 col-xs-12">
								<div class="col-lg-3 col-sm-5 col-xs-5">
									<label class="control-label col-lg-6"> {l s='Language' mod='deliveryorderautoupdate'} </label>
									<div class="col-lg-6 col-sm-6 col-xs-6">
										<select name="language_test">
										{foreach $fields_value.language as $id}
										<option value="{$id.id_lang|escape:'htmlall':'UTF-8'}">{$id.iso_code|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
										</select>
									</div>
								</div>
								<div class="col-lg-9 col-sm-7 col-xs-7">
									<select name="orders_test" style="float: left; width: 19%;margin-left: 9px;"/>
										<option value="">{l s='Id Order' mod='deliveryorderautoupdate'}</option>
										{foreach $fields_value['order_ids'] as $id}
										<option value="{$id.id_order|escape:'htmlall':'UTF-8'}">{$id.id_order|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
									<input type="text" value="" name="email_test" placeholder="Enter email" style="float: left; width: 30%;margin-left: 20px;"/>
									<a class="btn btn-default test_event_email" style="float: left;">{l s='Test' mod='deliveryorderautoupdate'}</a>
									<div class=" alert-successed " style="display:none;color:green; text-align:center; font-size:13px; margin:9px;float:left;" >{l s='Sent!' mod='deliveryorderautoupdate'}</div>
									<div class=" alert-error " style="display:none;color:red; text-align:center; font-size:13px; margin:9px;float:left;" >{l s='Email is empty!' mod='deliveryorderautoupdate'}</div>
									<div class=" alert-error_order " style="display:none;color:red; text-align:center; font-size:13px; margin:9px;float:left;" >{l s='pls select an order' mod='deliveryorderautoupdate'}</div>
								</div>
							</div>
						</div>
					</div>
					<div class="template_content" style="width: 50%; left: 20%; position: relative;"></div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-help" role="dialog">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content" style="width: 100%">
					<button style="padding: 10px 20px;position: absolute;right: 0;font-size: 40px;" type="button" class="close" data-dismiss="modal">&times;</button>
					<h2 style="display: flex;justify-content: center">{l s='View guide in :' mod='deliveryorderautoupdate'}</h2>
					<div class="doc-group">
						<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_en.pdf">EN</a>
						<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_fr.pdf">FR</a>
						<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_de.pdf">DE</a>
						<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_es.pdf">ES</a>
						<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_it.pdf">IT</a>
					</div>
				</div>

			</div>
		</div>
		<div id="growls" class="default" style="display: none"><div class="growl growl-notice growl-medium">
			<div class="growl-close">×</div>
			<div class="growl-title"></div>
			<div class="growl-message">{l s='Saved!' mod='deliveryorderautoupdate'}</div>
		</div></div>
		<script type="text/javascript">
			$(document).ready(function () {
				$('.tags').tagify({
					delimiters: [13, 188, 44],
					outputDelimiter: ',',
					cssClass: 'tagify-container',
					addTagPrompt: 'add emails'
				});
				$('#module_form').on('submit', function() {
					$('.tags').tagify('serialize');
				});
				$('.tagify-container').on('change', 'input', function() {
					if ($(this).val() != '') {
						$(this).parent().prev().tagify('add', $(this).val());
					}
				})
				$('.chosen').chosen();
				$('.guide').click(function(e) {
					e.preventDefault();
					$('#modal-help').modal();
				});
				$('.template_content').on('click', '.edit_subject', function(e) {
					e.preventDefault();
					$(this).removeClass('edit_subject');
					$(this).addClass('save_subject');
					$(this).html($(this).attr('label-save'));
					$(this).closest('.form-group').find('input[name=object_email]').prop('disabled', false);
				})
				$('.template_content').on('click', '.save_subject', function(e) {
					btn = $(this);
					e.preventDefault();
					$.ajax({
						type: 'POST',
						url: url_ajax,
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'saveSubject',
							ajax : true,
							lang : $("select[name='language_test']").val(),
							subject : $("input[name=object_email]").val(),
							secure_key: token,
						},
						success: function(data)
						{
							data = JSON.parse(data);
							if (data.success) {
								btn.removeClass('save_subject');
								btn.addClass('edit_subject');
								btn.html(btn.attr('label-edit'));
								btn.closest('.form-group').find('input[name=object_email]').prop('disabled', true);
							}
						}

					});
				})
				$('.template_content').on('change', '[name=HL_TRACKING_EMAIL_SUBJECT]', function(e) {
					type = $(this).val();
					$.ajax({
						type: 'POST',
						url: url_ajax,
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'changeSubjectType',
							ajax : true,
							subjectType: type,
							lang : $("select[name='language_test']").val(),
							id_order : $("select[name=orders_test]").val(),
							secure_key: token,
						},
						success: function(data)
						{
							data = JSON.parse(data);
							if (data.success) {
								$('#growls').show(400, function() {
									setTimeout(function() {
										$('#growls').hide(400);
									}, 1000);
								});
								$("input[name=object_email]").val(data.subject);
								if (type == 'fixed') {
									$('.save_subject, .edit_subject').show();
								} else {
									$('.save_subject, .edit_subject').hide();
								}
							}
						}

					});
				})
				$('.show_bubble').click(function() {
					val = $('#update_status_on').is(':checked');
					if (val)
						$('.order_status').show();
					else
						$('.order_status').hide();
				})
				var url_ajax = $("input[name='url_ajax']").val();
				var url_root = $("input[name='url_root']").val();
				var timer;
				var token = $("input[name='secure_key']").val();
				var cron_url = $("input[name='cron_url']").val();

				var url_protocolcurrent = window.location.protocol;
				var url_wwwcurrent = '//'+window.location.host;
				var url_ajax_work = '{$fields_value.url_root|escape:'htmlall':'UTF-8'}';
				if (url_ajax_work.indexOf(url_protocolcurrent) == -1) {
					$(".warning_ajax").show();
					$(".warning_ajax .protocolcurrent").show();
				} else {
					$(".warning_ajax .protocolcurrent").hide();
				}
				if (url_ajax_work.indexOf(url_wwwcurrent) == -1) {
					$(".warning_ajax").show();
					$(".warning_ajax .wwwwcurrent").show();
				} else {
					$(".warning_ajax .wwwwcurrent").hide();
				}
				$(".carrier_connect").change(function() {
					var cnn = $(this).val();
					if (cnn != 0) {
						$("input[name='cron_url']").val(cron_url+'&carrier='+cnn);
					} else {
						$("input[name='cron_url']").val(cron_url);
					}
				});
				$(".emailform").click(function() {
					loadEmailTpl();
				});
				$("select[name='language_test'], select[name='orders_test']").change(function() {
					loadEmailTpl();
				});
				function loadEmailTpl()
				{
					$.ajax({
						type: 'POST',
						headers: { "cache-control": "no-cache" },
						url: url_ajax,
						async: true,
						cache: false,
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'TrackConfig',
							ajax : true,
							l : 2,
							lang : $("select[name='language_test']").val(),
							id_order : $("select[name='orders_test']").val(),
							secure_key: token,
						},
						success: function(data)
						{
							$(".template_content").html(data);
						}

					});
				}
				// $(".deliveryorderautoupdate .action-carrier").click(function() {
				// 	var dou_Track_status = $("select[name='dou_Track_status']").val();
				// 	var active = $(this).parent().parent().attr("status");
				// 	if ((dou_Track_status == 2) || (dou_Track_status == 1)) {
				// 		$(this).closest('tr').hide();
				// 	} else {
				// 		$(this).hide();
				// 		if (active == 1) {
				// 			$(this).parent().parent().attr('status', 0);
				// 			$(this).next().show();
				// 		} else {
				// 			$(this).parent().parent().attr('status', 1);
				// 			$(this).prev().show();
				// 		}
				// 	}

				// 	$.ajax({
				// 		type: 'POST',
				// 		headers: { "cache-control": "no-cache" },
				// 		url: url_root+'modules/deliveryorderautoupdate/ajax_order.php' + '?rand=' + new Date().getTime(),
				// 		async: true,
				// 		cache: false,
				// 		data: 'l=6&url_root='+url_root+'&status='+$(this).attr("status")+'&id_order='+$(this).attr("id_order")+'&token='+token,

				// 	});

				// });

				// $(".lb_print_inetform").click(function() {
				// 	$(".popup_parent").show();
				// 	$.ajax({
				// 		type: 'POST',
				// 		headers: { "cache-control": "no-cache" },
				// 		url: url_ajax,
				// 		data: {
				// 			controller : 'AdmindeliveryorderautoupdateAjax',
				// 			action : 'AllCountry',
				// 			ajax : true,
				// 			url_ajax : url_ajax,
				// 			l : 4,
				// 		},
				// 		success: function(data)
				// 		{
				// 			$("#main_conf").html(data);
				// 			$(".popup_parent").hide();
				// 		}
				// 	});
				// });
				// $('.my_couriers').click(function() {
				// 	$(".popup_parent").show();
				// 	$.ajax({
				// 		type: 'POST',
				// 		headers: { "cache-control": "no-cache" },
				// 		url: url_ajax,
				// 		data: {
				// 			controller : 'AdmindeliveryorderautoupdateAjax',
				// 			action : 'AllCountry',
				// 			ajax : true,
				// 			url_ajax : url_ajax,
				// 			l : 2,
				// 		},
				// 		success: function(data)
				// 		{
				// 			$(".dou_Track_filter tbody").html(data);
				// 			$(".popup_parent").hide();
				// 		}
				// 	});
				// });
				$('#all_couriers').on('click', ".edit_confcarrier", function() {
					$(".popup_parent").show();
					$.ajax({
						type: 'POST',
						headers: { "cache-control": "no-cache" },
						url: url_ajax,
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'EditCarrier',
							ajax : true,
							id_carrier : $(this).attr("id_carrier"),
							secure_key: token,
						},
						success: function(data)
						{
							// console.log(data, $(".ps_back-office #main"));
							$("#main").append(data);
							$(".popup_parent").hide();
						},
						error : function(xhr, textStatus, errorThrown) {
							$(".box_carrier").css("display", "none");
						}
					});
				});
				$('#all_couriers').on('click', ".update_carrier", function() {
					$(".popup_parent").show();
					$.ajax({
						type: 'POST',
						headers: { "cache-control": "no-cache" },
						url: url_ajax,
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'Carriers',
							ajax : true,
							l : 1,
							onlyshow : 0,
							id : $(this).attr("id_carrier"),
							secure_key: token,
						},
						success: function(data)
						{
							$("#main").append(data);
							$(".popup_parent").hide();
						},
						error : function(xhr, textStatus, errorThrown) {
							$(".box_carrier").css("display", "none");
						}
					});
				});
				// $("select[name='dou_Track_status'], select[name='dou_Track_method']").change(function(){
				// 	filterTrackCarrier();
				// });
				// $("input[name='dou_Track_id_order'], input[name='dou_Track_carrier']").keyup(function(){

				// 	clearTimeout(timer);
				// 	timer = setTimeout(function() {
				// 		filterTrackCarrier();

				// 	}, 1000);
				// });
				// function filterTrackCarrier()
				// {
				// 	// $(".popup_parent").show();
				// 	var id = $("input[name=dou_Track_id_order]").val();
				// 	var carrier = $("input[name=dou_Track_carrier]").val().toLowerCase();
				// 	var status = $("select[name=dou_Track_status]").val();
				// 	var method = $("select[name=dou_Track_method]").val();
				// 	$.ajax({
				// 		type: 'POST',
				// 		headers: { "cache-control": "no-cache" },
				// 		url: url_ajax,
				// 		data: {
				// 			controller : 'AdmindeliveryorderautoupdateAjax',
				// 			action : 'AllCountry',
				// 			ajax : true,
				// 			id : id,
				// 			carrier : carrier,
				// 			status : status,
				// 			method : method,
				// 			url_ajax : url_ajax,
				// 			secure_key: token,
				// 			l : 3,
				// 		},
				// 		success: function(data)
				// 		{
				// 			$(".dou_Track_filter tbody").html(data);
				// 			// $(".popup_parent").hide();
				// 		}
				// 	});
				// }
				$(".all_couriers").click(function(){
					var all_couriers = $("#all_couriers").length;
					// $(".popup_parent").show();
					if (all_couriers == 1) {
						$.ajax({
							type: 'POST',
							headers: { "cache-control": "no-cache" },
							url: url_ajax,
							data: {
								controller : 'AdmindeliveryorderautoupdateAjax',
								action : 'AllCountry',
								l : 0,
								url_ajax : url_ajax,
								country : '',
								ajax : true,
								secure_key: token,
							},
							success: function(data)
							{
								$("#all_couriers").find('.list').html(data);
								// $(".popup_parent").hide();
							}
						});
					}
				});
			});
		</script>
		<!-- carrier form -->

	{/if}
		<script src="../modules/deliveryorderautoupdate/views/js/copy.js"></script>
	{$smarty.block.parent}
{/block}