{*
* 2007-2013 PrestaShop
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

<div class="note_conf" style="color:green; text-align:center; font-size:14px; display:none">{l s='Your request has been succeded.' mod='deliveryorderautoupdate'}</div>
<div class="config_module">
	<div class="form-group" data-tab-id="export">
		<label class="control-label col-lg-3 header_title"> {l s='Track orders' mod='deliveryorderautoupdate'} </label>
		<div class="col-lg-9 ">
			<hr/>
		</div>
	</div>
	<div class="form-group" data-tab-id="export">
		<label class="control-label col-lg-3"> {l s='Placed after' mod='deliveryorderautoupdate'} </label>
		<div class="col-lg-9 ">
			<div class="row">
				<div class="input-group col-lg-4">
					<input
						type="text"
						data-hex="true"
						class="datepicker"
						name="date_requestorder"
						value="{$date_requestorder|escape:'html':'UTF-8'}" />
					<span class="input-group-addon">
					<i class="icon-calendar-empty"></i>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group" data-tab-id="export">
		<div class="row">
			<label class="control-label col-lg-3"> {l s='Having status' mod='deliveryorderautoupdate'} </label>
			<div class="col-lg-9 col-sm-12 col-xs-12">
			{capture name=export_from assign=export_from},{$export_from|escape:'htmlall':'UTF-8'},{/capture}
					{foreach $order_export as $order}
			{capture name=id_order_check assign=id_order_check},{$order.id|escape:'htmlall':'UTF-8'},{/capture}
						<div class="col-lg-3 col-sm-12 col-xs-6 checkstt_form">
							<input type="checkbox" name="status_from[]" value="{$order.id|escape:'htmlall':'UTF-8'}" {if $export_from|strpos:$id_order_check !== false} checked {/if}/>
							<span>{$order.name|escape:'htmlall':'UTF-8'}</span>
						</div>
					{/foreach}
			</div>
		</div>
	</div>
	<div class="form-group" data-tab-id="export">
		<div class="row">
			<label class="control-label col-lg-3 header_title"> {l s='Send email to customer' mod='deliveryorderautoupdate'} </label>
			<div class="col-lg-9 col-sm-12 col-xs-12">
			<hr />
			</div>
		</div>
	</div>
	<div class="form-group" data-tab-id="export">
		<div class="row">
			<label class="control-label col-lg-3"> {l s='If tracking result is' mod='deliveryorderautoupdate'} </label>
			<div class="col-lg-9 col-sm-12 col-xs-12">
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-6 checkstt_form">
					<input type="checkbox" {if $event_code_mail|strpos:"1" !== false } checked {/if} value="1" name="event_code_mail[]" />
					<span>{l s='Delivered' mod='deliveryorderautoupdate'} </span>

				</div>
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-6 checkstt_form">
					<input type="checkbox" {if $event_code_mail|strpos:"4" !== false } checked {/if} value="4" name="event_code_mail[]" />
					<span>{l s='Shipment created' mod='deliveryorderautoupdate'}</span>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-6 checkstt_form">
					<input type="checkbox" {if $event_code_mail|strpos:"3" !== false } checked {/if} value="3" name="event_code_mail[]" />
					<span>{l s='In transit' mod='deliveryorderautoupdate'}</span>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-6 checkstt_form">
					<input type="checkbox" {if $event_code_mail|strpos:"2" !== false } checked {/if} value="2" name="event_code_mail[]" />
					<span> {l s='Could not be delivered' mod='deliveryorderautoupdate'} </span>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-6 checkstt_form">
					<input type="checkbox" {if $event_code_mail|strpos:"6" !== false } checked {/if} value="6" name="event_code_mail[]" />
					<span>{l s='Delivered to a pick-up point' mod='deliveryorderautoupdate'}</span>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-6 checkstt_form">
					<input type="checkbox" {if $event_code_mail|strpos:"7" !== false } checked {/if} value="7" name="event_code_mail[]" />
					<span>{l s='Returning to shipper' mod='deliveryorderautoupdate'}</span>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-6 checkstt_form">
					<input type="checkbox" {if $event_code_mail|strpos:"8" !== false } checked {/if} value="8" name="event_code_mail[]" />
					<span>{l s='Out for delivery' mod='deliveryorderautoupdate'}</span>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-6 checkstt_form">
					<input type="checkbox" {if $event_code_mail|strpos:"5" !== false } checked {/if} value="5" name="event_code_mail[]" />
					<span>{l s='Delivered to shipper' mod='deliveryorderautoupdate'}</span>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group" data-tab-id="export">
		<div class="row">
			<label class="control-label col-lg-3"> {l s='Update order status to' mod='deliveryorderautoupdate'} </label>
			<div class="col-lg-3 col-sm-12 col-xs-12">
				<select name="order_status_mailupdate">
				{foreach $order_export as $order}
					<option value="{$order.id|escape:'htmlall':'UTF-8'}" {if $order_status_mailupdate == $order.id}selected{/if}>{$order.id|escape:'htmlall':'UTF-8'}_{$order.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
				</select>
			</div>
		</div>
	</div>
</div>

<div class="form-group" data-tab-id="export">
	<div class="row">
		<label class="control-label col-lg-3 header_title"> {l s='Automated tracking' mod='deliveryorderautoupdate'} </label>
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
			<select name="carrier_connect" class="carrier_connect col-lg-4" style="float: right;">
				<option value="0">{l s='All Connectors' mod='deliveryorderautoupdate'}</option>
				{foreach $connectors as $cnn}
				<option value="{$cnn.id|escape:'htmlall':'UTF-8'}">{$cnn.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</div>
		<div class="col-lg-9 col-xs-12 col-sm-12">
			<div class="row">
				<div class="input-group col-lg-10 col-xs-10 col-sm-10">
					<input
						id="cron_url"
						type="text"
						name="cron_url"
						readonly
						value="{$cron_url|escape:'htmlall':'UTF-8'}" />
					<a class="btn btn-default " id="cron_task_button" data-copytarget="#cron_url"><i class="icon-copy"></i><span style="padding-left: 4px;">{l s='copy' mod='deliveryorderautoupdate'}</span></a>

				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<button class="btn btn-default pull-right" name="submitItem" id="module_form_submit_btn" value="1" type="submit">
			<i class="process-icon-save"></i> {l s='Save' mod='deliveryorderautoupdate'}
		</button>
	</div>
