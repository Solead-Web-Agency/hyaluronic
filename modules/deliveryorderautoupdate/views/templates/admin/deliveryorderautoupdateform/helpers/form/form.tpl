{* NOTICE OF LICENSE
* @copyright  2007-2023 Helloshop
* @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{extends file="helpers/form/form.tpl"}
{block name="after"}
{addJsDef translate=$fields_value.translate}
{addJsDef mailStatus=$fields_value.DELIVERY_EVENT_CODE_MAIL_ADMIN}
<div class="modal fade" id="modal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Modal Header</h4>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="order-modal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">{l s='Order' mod='deliveryorderautoupdate'}</h4>
			</div>
			<div class="modal-body" style="padding:0">

			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-help" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content" style="width: 100%">
			<button style="padding: 10px 20px;position: absolute;right: 0;font-size: 40px;" type="button" class="close" data-dismiss="modal">&times;</button>
			<h2 style="display: flex;justify-content: center">{l s='View guide in :' mod='deliveryorderautoupdate'}</h2>
			<div class="doc-group">
				<div class="list">
					<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_en.pdf">EN</a>
					<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_fr.pdf">FR</a>
					<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_de.pdf">DE</a>
					<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_es.pdf">ES</a>
					<a class="btn btn-default doc" target="_blank" href="{$fields_value.guide|escape:'html':'UTF-8'}readme_it.pdf">IT</a>
				</div>
			</div>
			<div class="contact">
				<p>{l s='If you find have a bug or you don\'t find connector for your carrier, contact us'  mod='deliveryorderautoupdate'}</p>
				<a href="https://addons.prestashop.com/contact-form.php?id_product=22347" target=blank class="btn btn-default">{l s='Contact us' mod='deliveryorderautoupdate'}</a>
			</div>
		</div>

	</div>
</div>
<div class="hide" id="temp">
	<div id="disable-track">
		<img src="{$fields_value.url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/close.png" title="{l s='Disable' mod='deliveryorderautoupdate'}" alt="{l s='Disable' mod='deliveryorderautoupdate'}" style="height:14px;">
		<span>{l s='Off' mod='deliveryorderautoupdate'}</span>
	</div>
	<div id="enable-track">
		<i class="icon-arrow-circle-right"></i>
		<span>{l s='Track' mod='deliveryorderautoupdate'}</span>
	</div>
	<div id="bulk-edit-carrier">
		<h3 style="text-align: center">{l s='Edit carrier' mod='deliveryorderautoupdate'}</h3>
		<select name="id_carrier" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
			<option value="0">{l s='Select carrier' mod='deliveryorderautoupdate'}</option>
			{foreach $fields_value.carrier_2 as $s}
			<option value="{$s.id_carrier|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall'}</option>
			{/foreach}
		</select>
	</div>
	<div id="force-list">
		<h3 style="text-align: center">{l s='Force shipping status' mod='deliveryorderautoupdate'}</h3>
		<div class="status-list bootstrap">
			<div class="form-group">
				<label class="col-md-4">{l s='Select a status' mod='deliveryorderautoupdate'}</label>
				<div class="col-md-8">
					<select name="status" class="fixed-width-xl">
						<option value="">--</option>
						{foreach $fields_value.statuses as $s}
						{if $s->id_status > 11}{continue}{/if}
						<option value="{$s->id_status|escape:'htmlall':'UTF-8'}">
							{if isset($s->{$fields_value.id_lang})}
							{$s->{$fields_value.id_lang}|escape:'htmlall':'UTF-8'}
							{else}
							{$s->EN|escape:'htmlall':'UTF-8'}
							{/if}
						</option>
						{/foreach}
					</select>
				</div>
				<div style="clear:both"></div>
			</div>
			<div class="form-group">
				<label class="col-md-4">{l s='Date' mod='deliveryorderautoupdate'}</label>
				<div class="col-md-8">
					<input class=" fixed-width-xl" type="text" name="date" value="{date('d/m/Y')}">
				</div>
				<div style="clear:both"></div>
			</div>
			<div class="form-group">
				<label class="col-md-4">{l s='Send mail' mod='deliveryorderautoupdate'}</label>
				<div class="col-md-8">
					<span class="switch prestashop-switch fixed-width-lg col-lg-9">
						<input type="radio" value="1" id="send_mail_on" checked="checked" name="send_mail">
						<label for="send_mail_on">Yes</label>
						<input type="radio" value="0" id="send_mail_off" name="send_mail">
						<label for="send_mail_off">No</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
				<div style="clear:both"></div>
			</div>
		</div>
	</div>
	<div id="add-shipment">
		<h3 style="text-align: center">{l s='Add shipment to order' mod='deliveryorderautoupdate'} <span class="id_order"></span></h3>
		<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
			<input type="hidden" name="id_order">
			<select name="id_carrier" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
				<option value="0">{l s='Select carrier' mod='deliveryorderautoupdate'}</option>
				{foreach $fields_value.carrier_2 as $s}
				<option value="{$s.id_carrier|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall'}</option>
				{/foreach}
			</select>
			<input style="font-weight: bold; text-align: center; height: 40px" type="text" name="shipping_number" placeholder="{l s='Enter shipping number' mod='deliveryorderautoupdate'}">
		</div>
	</div>
	<div id="import-return">
		<h3 style="text-align: center">{l s='Import return shipments from other modules' mod='deliveryorderautoupdate'} <span class="id_order"></span></h3>
		<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
			<select name="module" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
				<option value="0">{l s='Select a shipment module' mod='deliveryorderautoupdate'}</option>
				<option value="colissimo">{l s='Colissimo' mod='deliveryorderautoupdate'}</option>
			</select>
			<div class="noti" style="font-size: 14px;margin: 5px 0px"></div>
			<select name="id_connector" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
				<option value="0">{l s='Select connector' mod='deliveryorderautoupdate'}</option>
				{foreach $fields_value.carrier as $s}
				<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall'}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="change-connector">
		<h3 style="text-align: center">{l s='Change connector' mod='deliveryorderautoupdate'} <span class="id_order"></span></h3>
		<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
			<select name="id_connector" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
				<option value="0">{l s='Select connector' mod='deliveryorderautoupdate'}</option>
				{foreach $fields_value.carrier as $s}
				<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall'}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="update-issue-status">
		<h3 style="text-align: center">{l s='Update status of issue' mod='deliveryorderautoupdate'} #<span class="id_issue"></span></h3>
		<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
			<select class="updateIssueStatus" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
				{foreach $fields_value.issue_status as $s}
				<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
			<h4 style="text-align: left">{l s='Detail' mod='deliveryorderautoupdate'}</h4>
			<textarea name="detail"></textarea>
		</div>
	</div>
	<div class="update-xml">
		<h4>{l s='Current version' mod='deliveryorderautoupdate'} {if $fields_value.updatecheck.update}{$fields_value.updatecheck.old|escape:'htmlall':'UTF-8'}{/if}</h3>
			<h4>{l s='Updating to version' mod='deliveryorderautoupdate'} {if $fields_value.updatecheck.update}{$fields_value.updatecheck.new|escape:'htmlall':'UTF-8'}{/if}</h3>
				<h4 class="result"></h4>
			</div>
			<div class="create-issue">
				<h3 style="text-align: center">{l s='Create an issue for this shipment' mod='deliveryorderautoupdate'}</h3>
				<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
					<select class="updateIssue">
						{foreach $fields_value.issue as $s}
						<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<h4 style="text-align: left">{l s='Detail' mod='deliveryorderautoupdate'}</h4>
					<textarea name="detail"></textarea>
				</div>
			</div>
		</div>
		<div id="editCarrier">
			<ul class="uimenu ui-menu ui-widget ui-widget-content ui-corner-all" style="display: flex;">
				<div class="input-container">
					<select class="updateCarrier" name="updateCarrier">
						<option value="">-</option>
						{foreach $fields_value.carrier_2 as $s}
						<option value="{$s.id_carrier|escape:'htmlall':'UTF-8'}">{$s.id_carrier|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall'}</option>
						{/foreach}
					</select>
				</div>
				<button class="btn btn-default closeMenu"><i class="icon-remove"></i></button>
			</ul>
		</div>
		<div id="editConnector">
			<ul class="uimenu ui-menu ui-widget ui-widget-content ui-corner-all" style="display: flex;">
				<div class="input-container">
					<select class="updateConnector connector">
						<option value="0">{l s='No connector linked' mod='deliveryorderautoupdate'}</option>
						{foreach $fields_value.carrier as $s}
						<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<button class="btn btn-default closeMenu"><i class="icon-remove"></i></button>
			</ul>
		</div>
		<div id="editIssue">
			<ul class="uimenu ui-menu ui-widget ui-widget-content ui-corner-all" style="display: flex;">
				<div class="input-container">
					<select class="updateIssue">
						<option value="0">{l s='No Issue' mod='deliveryorderautoupdate'}</option>
						{foreach $fields_value.issue as $s}
						<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<button class="btn btn-default closeMenu"><i class="icon-remove"></i></button>
			</ul>
		</div>
		{/block}
		{block name="field"}
		{if $input.type == 'loading'}
		<div class="date-range">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="preparation avg"><strong>{l s='Preparation' mod='deliveryorderautoupdate'}</strong><span class="time"></span></div>
				</div>
				<div class="col-md-4">
					<div class="transit avg"><strong>{l s='Transit time' mod='deliveryorderautoupdate'}</strong><span class="time"></span></div>
				</div>
				<div class="col-md-4">
					<div class="total avg"><strong>{l s='Total' mod='deliveryorderautoupdate'}</strong><span class="time"></span></div>
				</div>
				<input type="hidden" name="total[preparation]">
				<input type="hidden" name="total[transit]">
				<input type="hidden" name="total[total]">
				<input type="hidden" name="count[preparation]">
				<input type="hidden" name="count[transit]">
				<input type="hidden" name="count[total]">
			</div>
			<div class="col-md-6">
				<div style="padding: 0;float:right;">
					<input type="text" name="date_range" style="max-width: 155px;float:right">
				</div>
				<div class="col-md-6" style="float:right;">
					<div class="col-md-3 date btn filter" filter="month">{l s='Month' mod='deliveryorderautoupdate'}</div>
					<div class="col-md-3 date btn filter" filter="year">{l s='Year' mod='deliveryorderautoupdate'}</div>
					<div class="col-md-3 date btn filter" filter="prevMonth">{l s='Month-1' mod='deliveryorderautoupdate'}</div>
					<div class="col-md-3 date btn filter" filter="prevYear">{l s='Year-1' mod='deliveryorderautoupdate'}</div>
				</div>
			</div>
			<div style="clear:both"></div>
		</div>
		<table class="table deliveryorderautoupdate_form table_form">
			<thead>
				<tr class="nodrag nodrop">
					<th class="" style="width: 48px">
						<span class="title_box"> {l s='Order' mod='deliveryorderautoupdate'} </span>
					</th>
					<th class="">
						<span class="title_box"> {l s='Reference' mod='deliveryorderautoupdate'} </span>
					</th>
					<th class="">
						<span class="title_box"> {l s='Date order' mod='deliveryorderautoupdate'} </span>
					</th>
					<th class="">
						<span class="title_box"> {l s='Customer' mod='deliveryorderautoupdate'} </span>
					</th>
					<th class="" style="width: 200px">
						<span class="title_box"> {l s='Carrier' mod='deliveryorderautoupdate'} </span>
					</th>
					<th class=" ">
						<span class="title_box"> {l s='Preparation' mod='deliveryorderautoupdate'}
							<span class="di-hint label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{l s='Time from order creation to shipped status' mod='deliveryorderautoupdate'}" data-placement="top">i</span>
						</span>
					</th>
					<th class=" ">
						<span class="title_box"> {l s='Transit time' mod='deliveryorderautoupdate'}
							<span class="di-hint label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{l s='Time from shipped status to status 1/3 or now' mod='deliveryorderautoupdate'}" data-placement="top">i</span>
						</span>
					</th>
					<th class=" ">
						<span class="title_box"> {l s='Total' mod='deliveryorderautoupdate'}
							<span class="di-hint label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{l s='Time from order creation to status 1/3 or now' mod='deliveryorderautoupdate'}" data-placement="top">i</span>
						</span>
					</th>
					<th class=" ">
						<span class="title_box"> {l s='Status' mod='deliveryorderautoupdate'}  </span>
					</th>
				</tr>
				<tr class="nodrag nodrop filter row_hover">
					<th class="">
						<input class="filter" name="dou_Filter_id_order" value="" type="text">
					</th>
					<th class="">
						<input class="filter" name="dou_Filter_reference" value="" type="text">
					</th>
					<th class="">
					</th>
					<th class="">
						<input class="filter" name="dou_Filter_customer" value="" type="text">
					</th>
					<th class="">
						<select class="dou_Filter_carrier_name" name="dou_Filter_carrier_name">
							<option value="">-</option>
							{foreach $fields_value.carrier_2 as $s}
							<option value="{$s.id_carrier|escape:'htmlall':'UTF-8'}">{$s.id_carrier|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall'}</option>
							{/foreach}
						</select>
					</th>
					<th class="">
					</th>
					<th class="">
					</th>
					<th class="">
					</th>
					<th class="">
					</th>
				</tr>
			</thead>
			<tbody class='delay'>
			</tbody>
		</table>
		<div id="loadingDelay" class="loading" style="text-align: center;">
		</div>
		div
		{/if}

		{if $input.type == 'loading_return'}
		<div role="tabpanel" class="tab-pane active" id="returns">
			<div class="row">
				<div class="panel">
					<div class="block-title">
						{l s='Returns' mod='deliveryorderautoupdate'}
						<span class="total"></span>
						<div class="return-bulk btn-group pull-right" style="white-space: nowrap;">
							<button class="btn btn-primary update_ordercarrier_all start_progress_return" name="update_ordercarrier" >
								<i class="icon-arrow-circle-left"></i>
								{l s='Track' mod='deliveryorderautoupdate'}
							</button>
							<button class="btn btn-primary dropdown-toggle" style="float:none;height: 31px;" data-toggle="dropdown" type="button">
								<span class="caret" style="border-top-color: #fff !important;"></span>
							</button>
							<ul class="dropdown-menu">
								<li>
									<a class="import-return" href="#">
										<i class="icon-upload"></i>
										{l s='Import return shipments' mod='deliveryorderautoupdate'}
									</a>
								</li>
								<li>
									<a class="change-connector-return">
										<i class="icon-random"></i>
										{l s='Change connector' mod='deliveryorderautoupdate'}
									</a>
								</li>
								<li>
									<a class="bulk-force-return">
										<i class="icon-hand-up"></i>
										{l s='force shipping status' mod='deliveryorderautoupdate'}
									</a>
								</li>
								<li>
									<a class="bulk_delete_return" href="#">
										<i class="icon-trash"></i>
										{l s='Delete shipment' mod='deliveryorderautoupdate'}
									</a>
								</li>
							</ul>
						</div>
						<span class="pull-right" style="padding:5px"><span class="row_selected"></span> {l s='return selected' mod='deliveryorderautoupdate'}</span>
					</div>
					<div class="table-responsive table-responsive-row clearfix orderform_review" style="width:100%;display:inline-block;  overflow:inherit;">
						<table id="return" class="table deliveryorderautoupdate_form table_form">
							<thead>
								<tr class="nodrag nodrop">
									<th class=" center">
										<a href="#" style="position: relative;top: -6px;" id="check_return" data-active="1"><span><i class="icon-check-sign"></i></span></a>
									</th>
									<th class="center" style="width: 48px">
										<span class="title_box"> {l s='#' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center" style="width: 48px">
										<span class="title_box"> {l s='Order' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center">
										<span class="title_box"> {l s='Customer request' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center">
										<span class="title_box"> {l s='Customer' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center">
										<span class="title_box"> {l s='Connector' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class=" center" style="width:170px">
										<span class="title_box"> {l s='Parcel Number' mod='deliveryorderautoupdate'}  </span>
									</th>
									<th class=" center" style="width:250px">
										<span class="title_box"> {l s='Shipping Status' mod='deliveryorderautoupdate'}  </span>
									</th>
									<th class=" center">
										<span class="title_box"> </span>
									</th>
								</tr>
								<tr class="nodrag nodrop filter row_hover">
									<th class="text-center">
										--
									</th>
									<th class="center">
										<input class="filter" name="filter_id_return" value="" type="text">
									</th>
									<th class="center">
										<input class="filter" name="filter_id_order" value="" type="text">
									</th>
									<th class="center">
										<select class="filter_id_order_return" name="filter_id_order_return">
											<option value="">-</option>
											{foreach $fields_value.state_return as $s}
											<option value="{$s.id_order_return_state|escape:'htmlall':'UTF-8'}">{$s.state_name|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
											<option value="null">{l s='without return request' mod='deliveryorderautoupdate'}</option>
										</select>
									</th>
									<th class="center">
										<input class="filter" name="filter_customer" value="" type="text">
									</th>
									<th class="center">
										<select class="filter_connector" name="filter_connector">
											<option value="">-</option>
											{foreach $fields_value.connector_return as $s}
											<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
										</select>
									</th>
									<th class="center">
										<input class="filter" name="filter_tracking_number" value="" type="text">
									</th>
									<th class="center">
										<select class="filter_status" name="filter_status">
											<option value="show_all">-</option>
											<option value="delivery">{l s='Delivered' mod='deliveryorderautoupdate'}</option>
											<option value="no_delivery" selected>{l s='Not Delivered' mod='deliveryorderautoupdate'}</option>
											<optgroup label="--------------">
												{foreach $fields_value.shipping_status as $i => $s}
												<option value="{$i|escape:'htmlall':'UTF-8'}" style="padding-left:20px;">{$s|escape:'htmlall':'UTF-8'}</option>
												{/foreach}
												<option value="no_tracking">{l s='No tracking' mod='deliveryorderautoupdate'}</option>
											</optgroup>
										</select>
									</th>
									<th class="center">

									</th>
								</tr>
							</thead>
							<tbody class='return'>
								{if $fields_value.error_message}
								<tr class="tr_cols">
									<td class=" center">
										<span class="title_box"> {$fields_value.error_message|escape:'htmlall':'UTF-8'} </span>
									</td>
								</tr>
								{else}
								{if $fields_value.orders}


								{else}
								<tr class="tr_cols">

									<td class="left" colspan="9">
										<div class="alert alert-warning" >
											{l s='No order to track. Check these settings in configuration page :' mod='deliveryorderautoupdate'}<br />
											{l s='1. Order statuses to track' mod='deliveryorderautoupdate'}<br />
											{l s='2. Order date from which these must be tracked' mod='deliveryorderautoupdate'}<br />
										</div>
									</td>
								</tr>
								{/if}
								{/if}
							</tbody>
						</table>
					</div>
					<div id="loadingReturn" class="loading" style="text-align: center;">
					</div>
				</div>
			</div>
		</div>
		{/if}
		{if $input.type == 'loading_issue'}
		<div role="tabpanel" class="tab-pane active" id="issues">
			<div class="row">
				<div class="panel">
					<div class="block-title">
						{l s='Issues' mod='deliveryorderautoupdate'}
						<span class="total"></span>
					</div>
					<div class="table-responsive table-responsive-row clearfix orderform_review" style="width:100%;display:inline-block;  overflow:inherit;">
						<table id="issue" class="table deliveryorderautoupdate_form table_form">
							<thead>
								<tr class="nodrag nodrop">
									<th class=" center" style="width: 48px">
										<a href="#" style="position: relative;top: -6px;" id="check_return" data-active="1"><span><i class="icon-check-sign"></i></span></a>
									</th>
									<th class="center" style="width: 48px">
										<span class="title_box"> {l s='#' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center" style="width: 48px">
										<span class="title_box"> {l s='Order' mod='deliveryorderautoupdate'}  </span>
									</th>
									<th class="center" style="width: 140px">
										<span class="title_box"> {l s='Client' mod='deliveryorderautoupdate'}  </span>
									</th>
									<th class="center" style="width: 140px">
										<span class="title_box"> {l s='Service' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center" style="width: 240px">
										<span class="title_box"> {l s='Last shipping status' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center" style="width: 240px">
										<span class="title_box"> {l s='Issue' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center" style="width: 240px">
										<span class="title_box"> {l s='Status' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class="center">
									</th>
								</tr>
								<tr class="nodrag nodrop filter row_hover">
									<th class="text-center">
										--
									</th>
									<th class="center">
										<input class="filter" name="filter_id_issue" value="" type="text">
									</th>
									<th class="center">
										<input class="filter" name="filter_id_order" value="" type="text">
									</th>
									<th class="center">
										<input class="filter" name="filter_customer" value="" type="text">
									</th>
									<th class="center">
										<select class="filter_service" name="filter_service">
											<option value="">-</option>
											{foreach $fields_value.carrier_2 as $s}
											<option value="{$s.id_carrier|escape:'htmlall':'UTF-8'}">{$s.id_carrier|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall'}</option>
											{/foreach}
										</select>
									</th>
									<th class="center">
									</th>
									<th class="center">
										<select class="filter_issue_type" name="filter_issue_type">
											<option value="">-</option>
											{foreach $fields_value.issue as $s}
											<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
										</select>
									</th>
									<th class="center">
										<select class="filter_status" name="filter_status">
											<option value="show_all">-</option>
											{foreach $fields_value.issue_status as $s}
											<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
										</select>
									</th>
									<th class="center">
									</th>
								</tr>
							</thead>
							<tbody class='issue'>
							</tbody>
						</table>
					</div>
					<div id="loadingIssue" class="loading" style="text-align: center;">
					</div>
				</div>
			</div>
		</div>
		{/if}
		{if $input.type == 'shipments_info'}
		{if !$fields_value.shop_active}
		<div class="alert alert-warning">
			<p>{l s='Tracking doesn\'t work when shop is in maintenance' mod='deliveryorderautoupdate'}</p>
			<p>{l s='Add server IP' mod='deliveryorderautoupdate'} <span id="maintenance_ip"></span> {l s='to maintenance IP to make tracking work' mod='deliveryorderautoupdate'} </p>
		</div>
		{/if}
		<div class="alert alert-warning warning_ajax" style="display:none; width: 100%; margin-bottom: 40px;" >
			<span class="protocolcurrent" style="display:none;">{l s='Please connect with https to ensure proper functionning of module' mod='deliveryorderautoupdate'} <br/></span>
			<span class="wwwwcurrent" style="display:none;">{l s='please connect with www to ensure proper functionning of module' mod='deliveryorderautoupdate'}</span>
		</div>
		<input type="hidden" value="{$fields_value.ajax_url|escape:'html':'UTF-8'|htmlspecialchars_decode}" name="admin_url" />
		<input type="hidden" value="{$fields_value.secure_key|escape:'htmlall':'UTF-8'}" name="token" />
		<input type="hidden" value="{$fields_value.DL_HISTORY_TAB_ACTIVE|escape:'htmlall':'UTF-8'}" name="DL_HISTORY_TAB_ACTIVE" />
		<input type="hidden" value="1" name="tab_active" />
		<img class="img-responsive img_warning" style="position:absolute; top:41%;display:none" src="{$fields_value.url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/loading.gif" alt="" />
		<div class="panel top-filter">
			<div class="col-md-12">
				<div class="col-md-7 pull-right btn-group">
					<span class="btn btn-default range" data-range="day">{l s='Day' mod='deliveryorderautoupdate'}</span>
					<span class="btn btn-default range" data-range="7day">{l s='Last 7 days' mod='deliveryorderautoupdate'}</span>
					<span class="btn btn-default range" data-range="30day">{l s='Last 30 days' mod='deliveryorderautoupdate'}</span>
					<span class="btn btn-default range active" data-range=" {$fields_value.DELIVERY_ORDER_DATE|escape:'htmlall':'UTF-8'}">{l s='From' mod='deliveryorderautoupdate'} {$fields_value.DELIVERY_ORDER_DATE|escape:'htmlall':'UTF-8'}</span>
					<span>
						<input type="text" name="date_range" style="max-width: 155px;display: inline-block;">
					</span>
				</div>
				<div class="col-md-5 order-search">
					<div class="order_filter">
						<div class="control-wrap tagify-container">
							<input type="text" class="form-contdol product" name="product" id="order-search" placeholder="{l s='Order Id or reference, customer name, tracking number' mod='deliveryorderautoupdate'}">
							<span class="tag"><span class="text"></span><a href="#" class="remove">x</a></span>
						</div>
					</div>
				</div>
			</div>
			<div style="clear:both"></div>
		</div>
		<div class="overview">
			{include file="../../../deliveryorderautoupdate_ajax/overview.tpl"
			overview=$fields_value.overview
			deliveryStatus=$fields_value.deliveryStatus
			carrierStat=$fields_value.carrierStat
			slider=$fields_value.slider
		}
	</div>
	<div role="tabpanel" class="tab-pane active" id="track_conf">
		<div class="row">
			<div class="panel">
				<div class="block-title">
					{l s='Shipments' mod='deliveryorderautoupdate'}
					<span class="total total-shipment">{$fields_value.overview.total|escape:'htmlall':'UTF-8'}</span>
					<div class="btn-group pull-right order-bulk" style="white-space: nowrap;">
						<button class="btn btn-primary update_ordercarrier_all start_progress" name="update_ordercarrier" >
							<i class="icon-arrow-circle-right"></i>
							{l s='Track' mod='deliveryorderautoupdate'}
						</button>
						<button class="btn btn-primary dropdown-toggle" style="float:none;height: 31px;" data-toggle="dropdown" type="button">
							<span class="caret" style="border-top-color: #fff!important"></span>
						</button>
						<ul class="dropdown-menu">
							<li>
								<a class="bulkEditCarrier" href="#">
									<i class="icon-truck"></i>
									{l s='Edit carrier' mod='deliveryorderautoupdate'}
								</a>
							</li>
							<li>
								<a class="bulkForce" href="#">
									<img src="https://a2pro.com/modules/deliveryorderautoupdate/views/img/force.png" style="width:14px;">
									{l s='Force status' mod='deliveryorderautoupdate'}
								</a>
							</li>
							<li>
								<a class="sendBulk" href="#">
									<i class="icon-envelope"></i>
									{l s='Send Emails' mod='deliveryorderautoupdate'}
								</a>
							</li>
						</ul>
					</div>
					<span class="pull-right" style="padding:5px"><span class="row_selected"></span></span>
				</div>
				<div class="table-responsive table-responsive-row clearfix orderform_review" style="width:100%;display:inline-block; min-height:400px; overflow:inherit;margin-top:10px">
					<table class="table deliveryorderautoupdate_form table_form">
						<thead>
							<tr class="nodrag nodrop">
								<th class=" center">
									<a href="#" style="position: relative;top: -6px;" id="check_mailsend" data-active="0"><span><i class="icon-check-empty"></i></span></a>
								</th>
								<th class="sort center" style="width: 48px">
									<span class="title_box" orderby="order"> {l s='Order' mod='deliveryorderautoupdate'}
										<a href="#" class="icon" orderway="desc" style="display: inline;">
											<i class="icon-caret-down"></i>
										</a>
										<a href="#" class="icon" orderway="asc">
											<i class="icon-caret-up"></i>
										</a>
									</span>
								</th>
								<th class="sort center">
									<span class="title_box" orderby="customer"> {l s='Customer' mod='deliveryorderautoupdate'}
										<a href="#" class="icon" orderway="desc">
											<i class="icon-caret-down"></i>
										</a>
										<a href="#" class="icon" orderway="asc">
											<i class="icon-caret-up"></i>
										</a>
									</span>
								</th>
								<th class="sort center" style="width: 200px">
									<span class="title_box" orderby="carrier"> {l s='Carrier' mod='deliveryorderautoupdate'}
										<a href="#" class="icon" orderway="desc">
											<i class="icon-caret-down"></i>
										</a>
										<a href="#" class="icon" orderway="asc">
											<i class="icon-caret-up"></i>
										</a>
									</span>
								</th>
								<th class="sort center" style="width: 200px">
									<span class="title_box" orderby="connector"> {l s='Connector' mod='deliveryorderautoupdate'}
										<a href="#" class="icon" orderway="desc">
											<i class="icon-caret-down"></i>
										</a>
										<a href="#" class="icon" orderway="asc">
											<i class="icon-caret-up"></i>
										</a>
									</span>
								</th>
								<th class="sort center">
									<span class="title_box" orderby="tracking_number"> {l s='Tracking Number' mod='deliveryorderautoupdate'}
										<a href="#" class="icon" orderway="desc">
											<i class="icon-caret-down"></i>
										</a>
										<a href="#" class="icon" orderway="asc">
											<i class="icon-caret-up"></i>
										</a>
									</span>
								</th>
								<th class="sort center" style="width: 290px">
									<span class="title_box" orderby="status"> {l s='Shipping Status' mod='deliveryorderautoupdate'}
										<a href="#" class="icon" orderway="desc">
											<i class="icon-caret-down"></i>
										</a>
										<a href="#" class="icon" orderway="asc">
											<i class="icon-caret-up"></i>
										</a>
									</span>
								</th>
								<th class="center" style="width: 100px">
									<span class="title_box"> </span>
								</th>
							</tr>
							<!-- <tr class="nodrag nodrop filter row_hover">
								<th class="text-center">
									--
								</th>
								<th class="center">
									<input class="filter" name="dou_Filter_id_order" value="" type="text">
								</th>
								<th class="center">
									<input class="filter" name="dou_Filter_reference" value="" type="text">
								</th>
								<th class="center">
									<input class="filter" name="dou_Filter_customer" value="" type="text">
								</th>
								<th class="center">
									<select class="dou_Filter_carrier_name" name="dou_Filter_carrier_name">
										<option value="">-</option>
										{foreach $fields_value.carrier_2 as $s}
										<option value="{$s.id_carrier|escape:'htmlall':'UTF-8'}">{$s.id_carrier|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall'}</option>
										{/foreach}
									</select>
								</th>
								<th class="center">
									<select class="dou_Filter_carrier" name="dou_Filter_carrier">
										<option value="">-</option>
										{foreach $fields_value.carrier as $s}
										<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</th>
								<th class="center">
									<input class="filter" name="dou_Filter_parcel_number" value="" type="text">
								</th>
								<th class="center" style="width: 200px;">
								</th>
								<th class="center">
								</th>
							</tr> -->
						</thead>
						<tbody class='del_message'>
							{if $fields_value.error_message}
							<tr class="tr_cols">
								<td class=" center">
									<span class="title_box"> {$fields_value.error_message|escape:'htmlall':'UTF-8'} </span>
								</td>
							</tr>
							{else}
							{if $fields_value.orders}
							{assign var=carrier_name value=''}
							{foreach $fields_value.orders as $order}
							{assign var=event_code value="_"|explode:$order.event_code}
							<tr class="tr_cols " data-event_text="{$event_code[1]|escape:'htmlall':'UTF-8'}" data-event_code="{if $event_code[0]}{$event_code[0]|escape:'htmlall':'UTF-8'}{else}0{/if}"
							id_order="{$order.id_order|escape:'htmlall':'UTF-8'}"
							id_order_carrier="{$order.id_order_carrier|escape:'htmlall':'UTF-8'}"
							id_carrier="{$order.id_carrier|escape:'htmlall':'UTF-8'}" carrier_reference="{$order.carrier_reference|escape:'htmlall':'UTF-8'}" data-track="{$order.track_number|escape:'htmlall':'UTF-8'}" {if ($event_code[0] == 1)} style="display:none;" {/if} order_reference={$order.reference|escape:'htmlall':'UTF-8'}>

							<td class="text-center pointer fixed-width-xs center">
								<input class="mailtracksend" value="{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" name="mailtracksend[]" type="checkbox">
							</td>
							<td class="pointer center service_id track_cols">
								<span class="title_box"> <a class="order_url" href="{$order['url']|escape:'html':'UTF-8'}" target="_blank"> {$order.id_order|escape:'htmlall':'UTF-8'}</a></span>
							</td>
							<td class="center">
								<span class="title_box customer"> {$order.firstname|escape:'htmlall':'UTF-8'} {$order.lastname|escape:'htmlall':'UTF-8'} </span>
							</td>
							<td class="center" style="position: relative;">
								<div class="editable edit_carrier" id_carrier="{$order.id_carrier|escape:'htmlall':'UTF-8'}">
									<i class="icon-pencil openmenu"></i>
									<span class="title_box carrier_name"> {$order.id_carrier|escape:'htmlall':'UTF-8'}_{$order.carrier_name|escape:'htmlall':'UTF-8'}</span>
									<span class="icon-check-circle success"></span>
								</div>
							</td>
							<td class=" center carrier" style="position: relative;">
								<div class="editable edit_connector {if !$order.id}no-connector{/if}" id_connector="{if $order.id}{$order.id|escape:'htmlall':'UTF-8'}{else}0{/if}">
									<i class="icon-pencil openmenu"></i>
									<span class="title_box connector_name">
										{if $order.carrier}
										{$order.id|escape:'htmlall':'UTF-8'}_{$order.carrier|escape:'htmlall':'UTF-8'}
										{else}
										<span class="red">{l s='No connector' mod='deliveryorderautoupdate'}</span>
										{/if}
									</span>
									<span class="icon-check-circle success_2"></span>
								</div>
							</td>
							<td class="pointer center">
								<div class="editable">
									<span class="title_box parcel_number">
										{if $order.track_number}
										{$order.track_number|escape:'htmlall':'UTF-8'}
										{else}
										-
										{/if}
									</span>
								</div>
							</td>
							<td class=" left track_cols">
								<div style="position:relative;">
									<span class="title_box result_carrier last_result_carrier last_result_carrier_{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" data-style="background-color: {$fields_value.statuses[$order.code]->color|escape:'htmlall':'UTF-8'}">
										{if $event_code[1] != ''}
										<a target="_blank" class="list-action-enable action-hisenabled" style="background: {$fields_value.statuses[$order.code]->color|escape:'htmlall':'UTF-8'}" href="{$order.tracking_url|escape:'htmlall':'UTF-8'}" title="Active" status="1" id_order="{$order.id_order|escape:'htmlall':'UTF-8'}">
											<img src="{$fields_value.url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$fields_value.statuses[$order.code]->id_status|escape:'htmlall':'UTF-8'}.png" />
										</a>
										<div class="right_shipping">
											{$event_code[1]|escape:'htmlall':'UTF-8'} <br /> <span class="step_even"> {$order.step_date|escape:'htmlall':'UTF-8'} {if $order.email_sent}<i class="icon-envelope"></i>{/if}</span> <span class="step_even">{$order.transit_time|escape:'htmlall':'UTF-8'}</span>
										</div>
										{else}
										-
										{/if}
									</span>
									<span class="loading_carrier loading_carrier_{$order.id_order_carrier|escape:'htmlall':'UTF-8'}" style="display:none;">
										<i class="process-icon-refresh icon-spin" style="color: #40c9ed"></i>
										{l s='Connecting' mod='deliveryorderautoupdate'}
									</span>
									<span class="waiting_carrier" style="display:none;">
										<a target="_blank" class="list-action-enable action-time">
											<i class="icon-time"></i>
										</a>
									</span>
									{if $order.id_issue>0}<i class="icon-warning-sign icon-issue"></i>{/if}
									<i class="icon-list openmenu track_history"></i>
									<a class="json_server hidden" href="{$order.json_server|escape:'htmlall':'UTF-8'}"></a>
								</div>
							</td>
							<td class=" center">
								{include file="../../../../hook/order-button-groups.tpl" url=$fields_value.url front_url=$fields_value.front_url}
							</td>
						</tr>
						{/foreach}

						{else}
						<tr class="tr_cols">

							<td class="left" colspan="9">
								<div class="alert alert-warning" >
									{l s='No order to track. Check these settings in configuration page :' mod='deliveryorderautoupdate'}<br />
									{l s='1. Order statuses to track' mod='deliveryorderautoupdate'}<br />
									{l s='2. Order date from which these must be tracked' mod='deliveryorderautoupdate'}<br />
								</div>
							</td>
						</tr>
						{/if}
						{/if}
					</tbody>
				</table>
				<div id="loading" style="text-align: center;width: 100%;" data-loading="{$fields_value.pagenb|escape:'htmlall':'UTF-8'}" data-initLoad="{$fields_value.pagenb|escape:'htmlall':'UTF-8'}">
				</div>
			</div>
		</div>
		<div class="row" style="border-top: 1px solid #DEDEDE;">
		</div>
	</div>
</div>
<div id="loading-table" class="hidden">
	<table class="loading-table">
		<tr><td><div class="loading-row"></div></td></tr>
		<tr><td><div class="loading-row"></div></td></tr>
		<tr><td><div class="loading-row"></div></td></tr>
		<tr><td><div class="loading-row"></div></td></tr>
		<tr><td><div class="loading-row"></div></td></tr>
		<tr><td><div class="loading-row"></div></td></tr>
		<tr><td><div class="loading-row"></div></td></tr>
		<tr><td><div class="loading-row"></div></td></tr>
	</table>
</div>
<div id="pick_ui" class="hidden">
	<div class="header">
		<div class="title">
			<img src="{$fields_value.url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/main_icon.png">
			<span>{$title}</span>
		</div>
		<div class="menu"><a href="#" id="pick_ui_cogs" class="dropdown" data-toggle="dropdown"><div class="vselipsis"></div></a>
		<div class="dropdown-menu" aria-labelledby="pick_ui_cogs">
			<ul>
				<li class="picker_settings_drop_list">
					<a href="{$fields_value.config_url|escape:'htmlall':'UTF-8'}"><i class="icon-gear"></i> {l s='Configuration' mod='deliveryorderautoupdate'}</a>
				</li>
				<li class="picker_settings_drop_list">
					<a class="view_history" href="#"><i class="icon-list"></i> {l s='History' mod='deliveryorderautoupdate'}</a>
				</li>
				<li class="picker_settings_drop_list">
					<a class="toolbar_btn btn-help" href=""><i class="icon-book"></i> {l s='Help' mod='deliveryorderautoupdate'}</a>
				</li>
				<li class="picker_settings_drop_list">
					<a target="_blank" href="https://addons.prestashop.com/fr/214_helloshop"><i class="icon-plus-square"></i> {l s='Our modules' mod='deliveryorderautoupdate'}</a>
				</li>
			</ul>
		</div>
	</div>
</div>
<div class="pick_ui">
	<ul class="list-inline" class="nav nav-tabs" role="tablist">
		<li class="active nav-item">
			<a class="nav-link" id="pick_ui_progress" data-toggle="tab" href="#fieldset_progress" role="tab" data-loading="0">{l s='SHIPMENTS' mod='deliveryorderautoupdate'}</a>
		</li>
				<!-- <li class="nav-item">
					<a class="nav-link" id="pick_ui_delivered" data-toggle="tab" href="#" role="tab" data-loading="0">{l s='DELAY' mod='deliveryorderautoupdate'}</a>
				</li> -->
				<li class="nav-item">
					<a class="nav-link" id="pick_ui_return" data-toggle="tab" href="#" role="tab" data-loading="0">{l s='RETURNS' mod='deliveryorderautoupdate'}</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="pick_ui_issue" data-toggle="tab" href="#" role="tab" data-loading="0">{l s='ISSUES' mod='deliveryorderautoupdate'}</a>
				</li>

			</ul>
		</div>
	</div>
	<script type="text/javascript">
		var date_from = '{$fields_value.date_from|escape:'html':'UTF-8'}';
		var date_format = '{$fields_value.date_format|escape:'html':'UTF-8'}';
		var trackPanel, returnPanel;
		var orderWayArr = ['desc', 'asc'];
		var trackOrderBy = 'order';
		var trackOrderWay = 0;
		var force_id_order = null;
		var force_action = 'order';
		function reindex() {
			let count = {};
			$('.del_message tr').each((i, e) => {
				id_order = $(e).attr('id_order');
				if (!count[id_order])
					count[id_order] = 0;
				count[id_order]++;
			})
			let position = {};
			$('.del_message tr').each((i, e) => {
				id_order = $(e).attr('id_order');
				if (count[id_order] > 1) {
					if (!position[id_order])
						position[id_order] = 0;
					position[id_order]++;
				{literal}$(e).find('.service_id span .order_url').html(`${id_order} - ${position[id_order]}`);//{/literal}
			}
		})
		}
		function toggleTrackButton(row, disabled) {
			if (parseInt(row.find('.disable').attr('data-disable')))
				row.find('.update_ordercarrier').prop('disabled', disabled);
			row.find('.dropdown-toggle').prop('disabled', disabled);
			if (disabled) {
				row.find('.btn-group').attr('title', '{l s='disabled' mod='deliveryorderautoupdate'}')
			} else {
				row.find('.btn-group').attr('title', '{l s='Track' mod='deliveryorderautoupdate'}');
			}
		}
		function saveTrackingNumber(val, e) {
			val = val.replaceAll(' ', '');
			row = $(e).closest('tr');
			id_order = $(e).closest('tr').attr('id_order');
			id_order_carrier = $(e).closest('tr').attr('id_order_carrier');
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateTrackingNumber',
					ajax : true,
					id_order : id_order,
					id_order_carrier : id_order_carrier,
					tracking_number: val,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					if (data.success) {
						showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
						row.attr('data-track', val);
						e.html(val);
						row = $(e).closest('tr');
						if (val) {
							row.find('.json_server').attr('href',data.json_server);
							row.find('.tracking_url').attr('href',data.tracking_url);
							toggleTrackButton(row, false);
						} else {
							toggleTrackButton(row, true);
						}
					}
					else
						alert(data.msg);
				}
			});
		}
		function saveTrackingNumberReturn(val, e) {
			val = val.replaceAll(' ', '');
			row = $(e).closest('tr');
			id_return = row.attr('id_return');
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateTrackingNumberReturn',
					ajax : true,
					id_return : id_return,
					tracking_number: val,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					if (data.success) {
						showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
						e.html(val);
						if (val) {
							row.find('.json_server').attr('href',data.json_server);
						}
					}
					else
						alert(data.msg);
				}
			});
		}
		function renderInput(element) {
			element.find('.parcel_number').track({
				onSelect: saveTrackingNumber
			});
		}
		function showHistory(id_order_carrier, page = 0, callback = undefined) {
			loadRow = $($('#load-row').html()).clone();
			$('#track_list').append(loadRow);
			if (id_order_carrier) {
				{literal}
				var parcel_number = $(`tr[id_order_carrier=${id_order_carrier}]`).data('track');
			//{/literal}
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				dataType: 'json',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'trackHistory',
					ajax : true,
					parcel_number : parcel_number,
					page: page,
					id_order_carrier : id_order_carrier,
					secure_key: secure_key,
				},
				success: function(response)
				{
					data = response.html;
					loadRow.remove();
					if (page) {
						$('#track_list').append(data);
					} else {
						$('#track_list').html(data);
					}
					$('#track_list').attr('page', page).attr('data-id_order_carrier', id_order_carrier).attr('total', response.total);
					if (callback)
						callback();
				}
			});
		} else {
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				dataType: 'json',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'Dashboard',
					ajax : true,
					l : 2,
					step : page,
					id_order_carrier: id_order_carrier,
					secure_key: secure_key,
				},
				success: function(response)
				{
					data = response.html;
					loadRow.remove();
					if (page) {
						$('#track_list').append(data);
					} else {
						$('#track_list').html(data);
					}
					$('#track_list').attr('page', page).attr('data-id_order_carrier', id_order_carrier).attr('total', response.total);
					if (callback)
						callback();
				},
			});
		}
	}
	function showEmailHistory(id_order_carrier, page = 0, callback = undefined) {
		loadRow = $($('#load-row').html()).clone();
		$('#email_list').append(loadRow);
		$.ajax({
			type: 'POST',
			url: $("input[name='admin_url']").val(),
			dataType: 'json',
			data: {
				controller : 'AdmindeliveryorderautoupdateAjax',
				action : 'EmailHistory',
				ajax : true,
				id_order_carrier: id_order_carrier,
				secure_key: secure_key,
				page: page,
			},
			success: function(response)
			{
				data = response.html;
				loadRow.remove();
				if (page) {
					$('#email_list').append(data);
				} else {
					$('#email_list').html(data);
				}
				$('#email_list').attr('page', page).attr('data-id_order', id_order_carrier).attr('total', response.total);
				if (callback)
					callback();
			},
		});
	}
	function getTrackFilter(changeOverview) {
		let id_order_carrier = searchInput.getValue();
		let status = trackPanel.find("[name='status']:checked").val();
		let carrier = trackPanel.find("[name='carrier']:checked").val();
		let picker = trackPanel.find('input[name="date_range"]').data('daterangepicker');
		let range = {
			start: picker.startDate.format('YYYY-MM-DD'),
			end: picker.endDate.format('YYYY-MM-DD')
		};

		let filter = {
			id_order_carrier: id_order_carrier,
			status: status,
			carrier: carrier,
			date: {
				key: 'range',
				range: range
			},
			sort: {
				orderBy: trackOrderBy,
				orderWay: orderWayArr[trackOrderWay]
			}
		}
		if ($('#fromSlider').val() && $('#toSlider').val()) {
			let transit_range = {
				start: $('#fromSlider').val(),
				end: $('#toSlider').val(),
			};
			filter.transit_range = transit_range;
		}
		switch(changeOverview) {
			case 1:
				filter.transit_range = undefined;
				break;
		}
		return filter;
	}
	function updateXML()
	{
		var html = $("#temp .update-xml").get(0).cloneNode(true);
		al = swal({
			content: html,
		})
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: url_ajax,
			data: {
				controller : 'AdmindeliveryorderautoupdateAjax',
				action : 'updateXML',
				ajax : true,
				secure_key: secure_key,
			},
			success: function(data)
			{
				if (data.success) {
					$('.update-xml .result').html(data.count+' rows updated');
				}
			}
		});
	}
	function checkUpdate()
	{
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: url_ajax,
			data: {
				controller : 'AdmindeliveryorderautoupdateAjax',
				action : 'checkUpdate',
				ajax : true,
				secure_key: secure_key,
			},
			success: function(data)
			{
			}
		});
	}
	function updateDateRangeFilter(e, key) {
		let startDate, endDate;
		switch (key) {
			case '30day':
			endDate = moment();
			startDate = moment().subtract(30,'days');
			break;
			case '7day':
			endDate = moment();
			startDate = moment().subtract(7,'days');
			break;
			case 'day':
			endDate = moment();
			startDate = moment();
			break;
			default:
			endDate = moment();
			startDate = moment(key);
		}
		let dateObj = e.data('daterangepicker');
		if (dateObj) {
			dateObj.setStartDate(startDate);
			dateObj.setEndDate(endDate);
		}
	}
	function updateBulkStatus() {
		let selected = trackPanel.find('.mailtracksend:checked').length;
		trackPanel.find('.row_selected').html(selected+' '+(selected>1?translate.shipments_selected:translate.shipment_selected));
		if (selected) {
			$('.order-bulk .btn').prop('disabled', false);
		} else {
			$('.order-bulk .btn').prop('disabled', true);
		}
	}
	function updateBulkStatusReturn() {
		let selected = returnPanel.find('.mailtracksend:checked').length;
		returnPanel.find('.row_selected').html(selected);
		if (selected) {
			$('.return-bulk .btn').prop('disabled', false);
		} else {
			$('.return-bulk .btn').prop('disabled', true);
		}
	}
	/* 0: not change
	1: update entire overview
	2: update status only
	3: update carrier only
	*/
	function filterCarrier(changeOverview = 0)
	{
		filter = getTrackFilter(changeOverview);
		$loading = $('#loading');
		$loading.html(loadingTable);
		overviewloading = $loading.clone();
		switch (changeOverview) {
			case 1:
			filter['carrier'] = '';
			filter['status'] = 'not_delivered';
			$('#delivery_status').html(overviewloading);
			$('#carriers').html(overviewloading.clone());
			$('#delivery_time').html(overviewloading.clone());
			break;
			case 2:
			$('#delivery_status').html(overviewloading);
			break;
			case 3:
			$('#carriers').html(overviewloading);
			break;
		}
		$('.del_message').empty();
		let scrollLoad = $loading.data('scrollLoad');
		$.ajax({
			type: 'POST',
			url: url_ajax,
			data: {
				controller : 'AdmindeliveryorderautoupdateAjax',
				action : 'filter',
				ajax : true,
				secure_key: secure_key,
				changeOverview: changeOverview,
				filter: filter
			},
			success: function(data) {
				data = JSON.parse(data);
				scrollLoad.page = data.p;
				if (data.html) {
					data.html = $(data.html);
					$('.del_message').append(data.html);
					renderInput(data.html);
					reindex();
				}
				switch (changeOverview) {
					case 1:
					$('.overview').html(data.overview);
					activeSlider();
					break;
					case 2:
					$('#delivery_status').replaceWith(data.delivery_status);
					break;
					case 3:
					$('#carriers').replaceWith(data.carriers);
					break;
				}
				if (data.p >= data.pages_nb) {
					scrollLoad.stop = true;
				} else {
					scrollLoad.stop = false;
				}
				scrollLoad.loading = false;
				trackPanel.find('.total-shipment').html(data.total);
				$loading.empty();
				updateBulkStatus();
			}
		})
	}
	$(document).ready(function () {
		loadingTable = $('#loading-table .loading-table').clone();
		trackPanel = $('#fieldset_progress');
		returnPanel = $('#returns');
		window.url_ajax = $("input[name='admin_url']").val();
		window.secure_key = $('input[name=token]').val();
		{if $fields_value.updatecheck.update}
		updateXML();
		// {/if}
		updateBulkStatus();

		$(document).on('click', '.tracking_url[data-view=false]', function(e) {
			e.preventDefault();
			swal('tracking url not set');
		})
		var pick_ui = $('#pick_ui').children();
        // let tab_delivery = $('[id^=fieldset_delivered]');
        let tab_return = $('[id^=fieldset_return]');
        let tab_issue = $('[id^=fieldset_issue]');
        // pick_ui.find('#pick_ui_delivered').attr('href', '#' + tab_delivery.attr('id'));
        pick_ui.find('#pick_ui_return').attr('href', '#' + tab_return.attr('id'));
        pick_ui.find('#pick_ui_issue').attr('href', '#' + tab_issue.attr('id'));
        var panel = $('#editCarrier').children();
        var editConnector = $('#editConnector').children();
        var editIssue = $('#editIssue').children();
        var editIssueStatus = $('#editIssueStatus').children();
        $('.admindeliveryorderautoupdatedashboard .page-head').html(pick_ui).show();
        {if $fields_value.v17}
    	$('#content').addClass('ui17');// {/if}


    	setTabEvent();
		// $('.deliveryorderautoupdate_form thead tr.filter th').each(function(i, e) {
		// 	$(e).css('width', $(e).outerWidth()+'px');
		// })
		$(document).on('click', '.edit_carrier', function() {
			$(this).closest('td').append(panel);
			panel.find('select').val($(this).attr('id_carrier'));
		})

		$(document).on('click', '.edit_issue', function() {
			$(this).closest('td').append(editIssue);
			editIssue.find('select').val($(this).attr('id_issue'));
		})
		$(document).on('click', '.edit_issue_status', function(e) {
			e.preventDefault();
			row = $(this).closest('tr');
			var html = $("#temp .update-issue-status").get(0).cloneNode(true);
			id_issue = row.attr('id_issue');
			id_issue_status = row.find('.edit_issue_status').attr('id_issue_status');
			$(html).find('.id_issue').html(id_issue);
			$(html).find('.updateIssueStatus').val(id_issue_status);
			al = swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					id_status = $(html).find('.updateIssueStatus').val();
					detail = $(html).find('[name=detail]').val();
					$('#editIssueStatus').append(editIssueStatus);
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: url_ajax,
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'updateIssueStatus',
							ajax : true,
							id_issue : id_issue,
							id_status: id_status,
							detail: detail,
							secure_key: secure_key,
						},
						success: function(data)
						{
							if (data.success) {
								showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
								row.find('.status_name').html(data.status_name);
								row.find('.edit_issue_status').attr('id_issue_status', data.id_issue_status);
								row.find('.issue_status_date').html(data.issue_status_date);
							} else {
								alert(data.msg);
							}
						}
					});

				}
			})
		})
		$(document).on('click', '.edit_connector', function() {
			$(this).closest('td').append(editConnector);
			editConnector.find('select').val($(this).attr('id_connector'));
		})
		$('#fieldset_progress').addClass('active');
		$('#maintenance_ip').ready(function() {
			$.ajax({
				type: 'POST',
				url: url_ajax,
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'getServerIP',
					ajax : true,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					if (data.success) {
						$('#maintenance_ip').html(data.ip);
					}
				}
			});
		})
		{literal}
		$('#modal').on('click', '.icon-envelope', function(e) {
			id_order = $(e.target).attr('data-id_order');
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'resend',
					ajax : true,
					id_order : id_order,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					if (data.success) {
						rs = $(e.target).parent().find('.result');
						rs.css('display', 'inline-block');
						rs.addClass('sent');
						rs.removeClass('error');
						rs.html('success');
						setTimeout(function(){rs.hide()}, 3000);
					}
					else {
						rs = $(e.target).parent().find('.result');
						rs.css('display', 'inline-block');
						rs.addClass('error');
						rs.removeClass('sent');
						rs.html('failed');
						setTimeout(function(){rs.hide()}, 3000);
					}
				}
			});
		})
		$('#modal').on('click', '#clearData', function(e) {
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'clearData',
					ajax : true,
					type : $('#delete').find('[name=range]').val(),
					step: $('#delete').find('[name=step]').prop('checked')?1:0,
					email: $('#delete').find('[name=email]').prop('checked')?1:0,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					if (data.success) {
						$('#delete .alert').show();
					}
				}
			});
		})
		//{/literal}
		$('a.btn-help').off('click');
		$('a.btn-help').on('click', function(e) {
			e.preventDefault();
			$('#modal-help').modal();
		});
		renderInput($('.del_message'));
		reindex();
		$(document).on('change', '.updateCarrier', function() {
			let row = $(this).closest('tr');
			id_order_carrier = row.attr('id_order_carrier');
			id_order = row.attr('id_order');
			let status = $(this).closest('td').find('.success');
			id_carrier = $(this).val();
			$('#editCarrier').append(panel);
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateCarrier',
					ajax : true,
					id_order : id_order,
					id_order_carrier : id_order_carrier,
					id_carrier: id_carrier,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					status.fadeIn('fast', () => {
						status.delay(1000).fadeOut(500);
					});
					row.attr('id_carrier',data.id_carrier);
					row.attr('carrier_reference',data.data.id_reference);
					if (data.success) {
						showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
						let e = row.find('.connector_name');
						if(data.data.name) {
							e.html(data.data.name);
							row.find('.edit_connector').attr('id_connector', data.data.id);
						} else {
							e.html('-');
							row.find('.edit_connector').attr('id_connector', 0);
						}
						if (!data.data.tracking_url)
							row.find('.tracking_url').attr('data-view', false);
						else
							row.find('.tracking_url').attr('data-view', true);
						row.find('.tracking_url').attr('href', data.data.tracking_url);

						row.find('.json_server').attr('href', data.json_server);
						// {literal}
						row.find('.carrier_name').html(`${data.id_carrier}_${data.carrier_name}`);
						// {/literal}
						row.find('.edit_carrier').attr('id_carrier', data.id_carrier);
						if (data.data.id) {
							if (data.data.tracking_number) {
								toggleTrackButton(row, false);
							} else {
								toggleTrackButton(row, true);
							}
						} else {
							toggleTrackButton(row, true);
						}
					}
				}
			});
		})
		$('#fieldset_progress').on('change', '.updateConnector', function() {
			let row = $(this).closest('tr');
			id_order_carrier = row.attr('id_order_carrier');
			let status = $(this).closest('td').find('.success_2');
			let tbody = $(this).closest('tbody');
			let rows = $(this).closest('tbody').find('tr');
			id_carrier = row.attr('carrier_reference');
			id_connector = $(this).val();
			$('#editConnector').append(editConnector);
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateConnector',
					ajax : true,
					id_order_carrier : id_order_carrier,
					id_carrier : id_carrier,
					id_connector: id_connector,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
					rows.each(function(){
						let id_order_carrier = $(this).attr('id_order_carrier');
						let tracking_number = $(this).attr('data-track');
						let reference = $(this).attr('order_reference');
						let carrier = $(this).attr('carrier_reference');
						if (carrier == id_carrier) {
							$(this).find('.connector_name').html(data.connector_name);
							$(this).find('.edit_connector').attr('id_connector', id_connector);
							if (id_connector >0){
								$(this).find('.connector').removeClass('no_connector_row');
								$(this).find('.edit_connector').removeClass('no-connector');
								if ($(this).data('track')) {
									toggleTrackButton($(this), false);
									$(this).find('.dropdown-toggle').prop('disabled', false);
								}
							} else {
								$(this).find('.connector_name').html(`<span class="red">{l s='No connector' mod='deliveryorderautoupdate'}</span>`);
								$(this).find('.connector').addClass('no_connector_row');
								$(this).find('.edit_connector').addClass('no-connector');
								toggleTrackButton($(this), true);
								$(this).find('.dropdown-toggle').prop('disabled', true);
							}
							$(this).find('.update_ordercarrier').attr('id_carrier',id_connector);
							// {literal}
							// $(this).find('.json_server').attr('href',data.json_server.replace('{$id_order_carrier}', id_order_carrier).replace('{$tracking_number}', tracking_number).replace('{$reference}', reference));
							$(this).find('.json_carrier').attr('href',data.json_carrier.replace('{$id_order_carrier}', id_order_carrier).replace('{$tracking_number}', tracking_number).replace('{$reference}', reference));
							// {/literal}
						}

					});


					status.fadeIn('fast', () => {
						status.delay(1000).fadeOut(500);
					});
				}
			});
		})
		$('#return').on('change', '.updateConnector', function() {
			let row = $(this).closest('tr');
			id_return = row.attr('id_return');
			let status = $(this).closest('td').find('.success_2');
			id_connector = $(this).val();
			$('#editConnector').append(editConnector);
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateConnectorReturn',
					ajax : true,
					id_return : id_return,
					id_connector: id_connector,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
					{literal}
					row.find('.connector_name').html(`${data.id_connector}_${data.connector_name}`);
					// {/literal}
					row.find('.edit_connector').attr('id_connector', data.id_connector);
					row.find('.json_server').attr('href',data.json_server);
					status.fadeIn('fast', () => {
						status.delay(1000).fadeOut(500);
					});
				}
			});
		})
		$('#issue').on('change', '.updateIssue', function() {
			let row = $(this).closest('tr');
			id_issue = row.attr('id_issue');
			issue_type = $(this).val();
			$('#editIssue').append(editIssue);
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: url_ajax,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateIssue',
					ajax : true,
					id_issue : id_issue,
					issue_type: issue_type,
					secure_key: secure_key,
				},
				success: function(data)
				{
					if (data.success) {
						showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
						row.find('.issue_name').html(data.issue_name);
						row.find('.edit_issue').attr('id_issue', data.issue_type);
					} else {
						alert(data.msg);
					}
				}
			});
		})
		$('#issue').on('click', '.load_issue_history', function() {
			let row = $(this).closest('tr');
			id_issue = row.attr('id_issue');
			$('#modal .modal-header .modal-title').html("{l s='Issue #' mod='deliveryorderautoupdate'}"+id_issue);
			$('#modal').find('.modal-dialog').attr('class', 'modal-dialog');
			$('#modal').modal();
			$('#modal .modal-body').html('');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: url_ajax,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'loadIssueHistory',
					ajax : true,
					id_issue : id_issue,
					secure_key: secure_key,
				},
				success: function(data)
				{
					$('#modal .modal-body').html(data.html);
				}
			});
		})
		$(document).on('click', '.create_issue', function(e) {
			e.preventDefault();
			let row = $(this).closest('tr');
			id_order_carrier = row.attr('id_order_carrier');
			var html = $("#temp .create-issue").get(0).cloneNode(true);
			al = swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					issue_type = $(html).find('.updateIssue').val();
					detail = $(html).find('[name=detail]').val();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: url_ajax,
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'createIssue',
							ajax : true,
							id_order_carrier : id_order_carrier,
							issue_type: issue_type,
							detail: detail,
							secure_key: secure_key,
						},
						success: function(data)
						{
							if (data.success) {
								showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
								filterIssue();
							} else {
								alert(data.msg);
							}
						}
					});

				}
			})
		})
		// $('.view_step').click(function(e) {
		// 	e.preventDefault();
		// 	var url_ajax = $("input[name='admin_url']").val();
		// 	var parcel_number = $(this).closest('tr').data('track');
		// 	var id_order = $(this).closest('tr').attr('id_order');
		// 	$.ajax({
		// 		type: 'POST',
		// 		url: url_ajax,
		// 		headers: { "cache-control": "no-cache" },
		// 		async: true,
		// 		data: {
		// 			controller : 'AdmindeliveryorderautoupdateAjax',
		// 			action : 'viewStep',
		// 			ajax : true,
		// 			parcel_number : parcel_number,
		// 			id_order : id_order,
		// 			secure_key: secure_key,
		// 		},
		// 		success: function(data)
		// 		{
		// 			$('#modal .modal-header .modal-title').html("{l s='Steps' mod='deliveryorderautoupdate'}");
		// 			$('#modal .modal-body').html(data);
		// 			$('#modal').find('.modal-dialog').attr('class', 'modal-dialog view-step');
		// 			$('#modal').modal();
		// 		}
		// 	});
		// });
		$(document).on('click', '.track_history', function(e) {
			e.preventDefault();
			var id_order_carrier = $(this).closest('tr').attr('id_order_carrier');
			DL_HISTORY_TAB_ACTIVE = $('input[name=DL_HISTORY_TAB_ACTIVE]').val();
			$('#modal .modal-header .modal-title').html("{l s='Tracking History of shipment' mod='deliveryorderautoupdate'} "+id_order_carrier);
			$('#modal').find('.modal-dialog').attr('class', 'modal-dialog');
			$('#modal').modal();
			$('#modal .modal-body').html('');
			if (!DL_HISTORY_TAB_ACTIVE) {
				$('#modal .modal-body').html($('#selectTab').html());
				$('#modal .modal-body').find('.selectTab').attr('data-id_order', id_order);
				$('#modal .modal-body').find('.selectTab').attr('data-id_order_carrier', id_order_carrier);
			} else {
				setHistoryContent(id_order_carrier);
				activeTab(DL_HISTORY_TAB_ACTIVE);
			}
			{literal}
			let href = $(this).closest('tr').find('.json_server').attr('href');
			$('#carrier-response').html(`<iframe type="application/xml" style="width:100%;min-height:200px;" data-src=${href}&devmode=1></iframe>`);;
			$('#connector-response').html(`<iframe type="application/xml" style="width:100%;min-height:200px;" data-src=${href}&devmode=0></iframe>`);;
			// {/literal}
		});
		$(document).on('click', '.track-devmode', function(e) {
			e.preventDefault();
			$('#carrier-response iframe').attr('src', $('#carrier-response iframe').data('src'));
			$('#connector-response iframe').attr('src', $('#connector-response iframe').data('src'));
		});
		$(document).on('click', '.add-shipment', function(e) {
			e.preventDefault();
			id_order = $(this).closest('tr').attr('id_order');
			var html = document.getElementById("add-shipment");
			$(html).find('[name=id_order]').val(id_order);
			swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					shipping_number = $('#add-shipment').find('[name=shipping_number]').val();
					id_carrier = $('#add-shipment').find('[name=id_carrier]').val();
					$.ajax({
						type: 'POST',
						url: url_ajax,
						dataType: 'json',
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'addShipment',
							ajax : true,
							id_order: id_order,
							shipping_number: shipping_number,
							id_carrier: id_carrier,
							secure_key: secure_key,
						},
						success: data => {
							if (data.success) {
								$('[name=doutrack_Filter_result]').trigger('change');
							}
							else
								alert(data.msg);
						}
					});
				} else {
					$('#temp').append(html);
				}
			}).then(() => $('#temp').append(html));
		});
		$(document).on('click', '.send-email', function(e) {
			e.preventDefault();
			id_order = $(this).closest('tr').attr('id_order_carrier');
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				dataType: 'json',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'sendMail',
					ajax : true,
					id_order : id_order,
					secure_key: secure_key,
				},
				success: function(res)
				{
					if (res.success) {
						swal({
							text: "Sent",
							icon: "success",
						});
					} else {
						swal({
							text: "Failed",
							icon: "error",
						});
					}
				},
			});
		});
		$(document).on('click', '.split-shipment', function(e) {
			e.preventDefault();
			id_order_carrier = $(this).closest('tr').attr('id_order_carrier');
			shipping_number = $(this).closest('tr').data('track').toString();
			count = shipping_number.split(',').length;
			if (count <= 1) {
				swal("{l s='Only orders having several shipping numbers can be split' mod='deliveryorderautoupdate'}");
				return;
			}
			str = "{l s='split shipmnet into %s shipments' mod='deliveryorderautoupdate'}";
			str = str.replace('%s', count);
			swal(str).then(save => {
				if (save) {
					$.ajax({
						type: 'POST',
						url: url_ajax,
						dataType: 'json',
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'splitShipment',
							ajax : true,
							id_order_carrier: id_order_carrier,
							secure_key: secure_key,
						},
						success: data => {
							if (data.success) {
								$('[name=doutrack_Filter_result]').trigger('change');
							}
							else
								alert(data.msg);
						}
					});
				} else {
				}
			});
		});
		$(document).on('click', '.force', function(e) {
			e.preventDefault();
			force_action = 'order';
			force_id_order_carrier = $(this).closest('tr').attr('id_order_carrier');
			var html = document.getElementById("force-list");
			swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					forceOrder();
				}
			}).then(() => $('#temp').append(html));
		});
		$(document).on('click', '.force_return', function(e) {
			e.preventDefault();
			force_action = 'return';
			force_id_return = $(this).closest('tr').attr('id_return');
			var html = document.getElementById("force-list");
			swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					forceReturn();
				}
			}).then(() => $('#temp').append(html));
		});
		$(document).on('click', '.disable', function(e) {
			e.preventDefault();
			row = $(this).closest('tr');
			id_order_carrier = row.attr('id_order_carrier');
			disabled = parseInt($(this).attr('data-disable'));
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				dataType: 'json',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'disable',
					ajax : true,
					id_order_carrier: id_order_carrier,
					disabled: disabled,
					secure_key: secure_key,
				},
				success: data => {
					tracking_number = row.attr('data-track');
					id_connector = parseInt(row.find('.edit_connector').attr('id_connector'));
					if (data.success) {
						if (!tracking_number || !id_connector)
							return;
						if (disabled) {
							row.find('.update_ordercarrier').prop('disabled', true).html($('#disable-track').children().clone());
							$(this).attr('data-disable', 0);
							$(this).find('i').removeClass('icon-pause').addClass('icon-play');
							$(this).find('span').html('{l s='Enable tracking' mod='deliveryorderautoupdate'}');
						} else {
							$(this).attr('data-disable', 1);
							row.find('.update_ordercarrier').prop('disabled', false).html($('#enable-track').children().clone());
							$(this).find('i').removeClass('icon-play').addClass('icon-pause');
							$(this).find('span').html('{l s='Disable tracking' mod='deliveryorderautoupdate'}');
						}
					}
					else
						alert(data.msg);
				}
			});
		});
		function forceOrder() {
			$(".last_result_carrier_"+force_id_order_carrier).css("display", "none");
			$(".loading_carrier_"+force_id_order_carrier).css("display", "unset");
			id_status = $('#force-list').find('[name=status]').val();
			send_mail = $('#force-list').find('[name=send_mail]:checked').val();
			date = $('#force-list').find('[name=date]').val();
			swal.close();
			return new Promise((resolve, reject) => {
				$.ajax({
					type: 'POST',
					url: url_ajax,
					headers: { "cache-control": "no-cache" },
					dataType: 'json',
					data: {
						controller : 'AdmindeliveryorderautoupdateAjax',
						action : 'force',
						ajax : true,
						id_status: id_status,
						send_mail: send_mail,
						date: date,
						id_order_carrier : force_id_order_carrier,
						secure_key: secure_key,
					},
					success: function(data)
					{
						if (data.success) {
							$(".last_result_carrier_"+force_id_order_carrier).html(data.last_status_result);
							$(".last_result_carrier_"+force_id_order_carrier).css("display", "inline-block");
							$(".loading_carrier_"+force_id_order_carrier).css("display", "none");
							resolve(data);
						}
						else
							alert(data.msg);
					}
				});

			})
		}
		async function bulkForceOrder() {
			var orders = getmaillist($('input[name="mailtracksend[]"]:checked'));
			for (var i = 0; i < orders.length; i++) {
				force_id_order_carrier = orders[i];
			console.log(orders, force_id_order_carrier);
				await forceOrder();
			}
		}

		async function bulkForceReturn() {
			var returns = getmaillist($('input[name="id_return[]"]:checked'));
			for (var i = 0; i < returns.length; i++) {
				force_id_return = returns[i];
				await forceReturn();
			}
		}
		function forceReturn() {
			{literal}
			row = $(`.return tr[id_return=${force_id_return}]`);
			// {/literal}
			row.find(".last_result_carrier").css("display", "none");
			row.find(".loading_carrier").css("display", "unset");
			id_status = $('#force-list').find('[name=status]').val();
			swal.close();
			return new Promise((resolve, reject) => {
				$.ajax({
					type: 'POST',
					url: url_ajax,
					headers: { "cache-control": "no-cache" },
					dataType: 'json',
					data: {
						controller : 'AdmindeliveryorderautoupdateAjax',
						action : 'forceReturn',
						ajax : true,
						id_status: id_status,
						id_return : force_id_return,
						secure_key: secure_key,
					},
					success: function(data)
					{
						if (data.success) {
							row.find(".last_result_carrier").html(data.last_status_result);
							row.find(".last_result_carrier").css("display", "inline-block");
							row.find(".loading_carrier").css("display", "none");
							resolve(data);
						}
						else
							alert(data.msg);
					}
				});
			})
		}

		$("#check_mailsend").click(function(e) {
			e.preventDefault();
			if ($(this).data('active')) {
				$(this).data('active', false);
				$("#check_mailsend span").html('<i class="icon-check-empty"></i>');
				checkDelBoxes($('#configuration_form').get(0), 'mailtracksend[]', false, 1);
			} else {
				$(this).data('active', true);
				$("#check_mailsend span").html('<i class="icon-check-sign"></i>');
				checkDelBoxes($('#configuration_form').get(0), 'mailtracksend[]', true, 1);
			}
			updateBulkStatus();
		})
		trackPanel.on('change', '.mailtracksend', function() {
			updateBulkStatus();
		})

		trackPanel.on('click', '.order_url', e => {
			e.preventDefault();
			e.stopPropagation();
			let href = $(e.currentTarget).attr('href');
            {literal}$('#order-modal .modal-body').html(`<iframe src="${href}" style="width:100%;min-height: 400px;"></iframe>`);//{/literal};
            $('#order-modal').modal()
        })
		returnPanel.on('change', '.mailtracksend', function() {
			updateBulkStatusReturn();
		})
		$("#check_return").click(function(e) {
			e.preventDefault();
			if ($(this).data('active')) {
				$(this).data('active', false);
				$("#check_return span").html('<i class="icon-check-empty"></i>');
				checkDelBoxes($('#configuration_form').get(0), 'id_return[]', false, 1);
			} else {
				$(this).data('active', true);
				$("#check_return span").html('<i class="icon-check-sign"></i>');
				checkDelBoxes($('#configuration_form').get(0), 'id_return[]', true, 1);
			}
			updateBulkStatusReturn();
		})
		$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
			var target = $(e.target).attr("href");
			id_order_carrier = $(this).closest('.historyContent').data('id_order_carrier');
			if (!$(this).data('active')) {
				$(this).data('active', true);
				switch (target) {
					case '#history':
					showHistory(id_order_carrier);
					break;
					case '#email_history':
					showEmailHistory(id_order_carrier);
					break;
					defaut:
					break;
				}
			}
		});
		// $("#check_mailsend").toggle(function() {
		// 	$("#check_mailsend span").html('<i class="icon-check-empty"></i>');
		// 	checkDelBoxes($('#configuration_form').get(0), 'mailtracksend[]', false, 1);
		// }, function(){
		// 	$("#check_mailsend span").html('<i class="icon-check-sign"></i>');
		// 	checkDelBoxes($('#configuration_form').get(0), 'mailtracksend[]', true, 1);
		// });
		var url_protocolcurrent = window.location.protocol;
		var url_wwwcurrent = '//'+window.location.host;
		var url_ajax_work = '{$fields_value.url|escape:'html':'UTF-8'|htmlspecialchars_decode}';
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


		setHistoryContent = function(id_order_carrier = undefined) {
			if (id_order_carrier) {
				$('#modal .modal-body').html($('#historyContentWithStep').html());
				$('#modal').find('.historyContent').attr('data-id_order_carrier', id_order_carrier);
				setStep(id_order_carrier);
				setEvent(id_order_carrier);
			} else {
				$('#modal .modal-dialog').addClass('global-history');
				$('#modal .modal-body').html($('#historyContent').html());
			}
			setScrollLoad();
			// showHistory(id_order_carrier);
			// showEmailHistory(id_order_carrier);
		}
		setStep = function(id_order_carrier) {
			var url_ajax = $("input[name='admin_url']").val();
			{literal}row = $('.del_message').find(`tr[id_order_carrier=${id_order_carrier}]`);//{/literal}
			var parcel_number = row.data('track');
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'viewStep',
					ajax : true,
					parcel_number : parcel_number,
					id_order_carrier : id_order_carrier,
					secure_key: secure_key,
				},
				success: function(data)
				{
					$('#modal').find('.steps').html(data);
				}
			});
		}
		setEvent = function(id_order_carrier) {
			var url_ajax = $("input[name='admin_url']").val();
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'viewEvent',
					ajax : true,
					id_order_carrier : id_order_carrier,
					secure_key: secure_key,
				},
				success: function(data)
				{
					$('#modal').find('.events').html(data);
				}
			});
		}
		{literal}
		activeTab = function(tab) {
			$('#modal').find(`a[aria-controls=${tab}]`).click();
		}
		$('#modal').on('click', '.selectTab a', function(e) {
			e.preventDefault();
			id_order = $(this).closest('.selectTab').attr('data-id_order');
			tab = $(this).attr('data-tab');
			setHistoryContent(id_order);
			activeTab(tab);
			$('input[name=DL_HISTORY_TAB_ACTIVE]').val(tab);
		})
		$('#modal').on('hidden.bs.modal', function () {
			$(this).find('.modal-dialog').removeClass('global-history');
		})
		//{/literal}
		$(document).on('click', ".view_history", function(e) {
			e.preventDefault();
			DL_HISTORY_TAB_ACTIVE = $('input[name=DL_HISTORY_TAB_ACTIVE]').val();
			$('#modal .modal-header .modal-title').html("{l s='History' mod='deliveryorderautoupdate'}");
			$('#modal').find('.modal-dialog').attr('class', 'modal-dialog');
			$('#modal').modal();
			$('#modal .modal-body').html('');
			if (!DL_HISTORY_TAB_ACTIVE) {
				$('#modal .modal-body').html($('#selectTab').html());
			} else {
				setHistoryContent();
				activeTab('history');
			}
		});
		$(document).on('click', ".update_ordercarrier", function(e) {
			e.preventDefault();
			var orders = new Array();
			var id_order_carrier = $(this).attr("id_order_carrier");
			orders.push(id_order_carrier);
			$(".status_temporized_"+id_order_carrier).css("display", "none");
			$(".last_result_carrier_"+id_order_carrier).css("display", "none");
			$(".status_carrier_"+id_order_carrier).css("display", "none");
			$(".loading_carrier_"+id_order_carrier).css("display", "unset");
			updateStatus(orders);
		});
		$(document).on('click', ".track_return", function(e) {
			e.preventDefault();
			let orders = [];
			let tr = $(this).closest('tr');
			let id_return = tr.attr('id_return');
			orders.push(id_return);
			trackReturn(orders);
		});
		$(".start_progress").click(function(e) {
			{literal}
			e.preventDefault();
			var orders = getmaillist($('input[name="mailtracksend[]"]:checked'));
			orders = orders.filter(o => {
				row = $('.del_message').find(`tr[id_order_carrier=${o}]`);
				return !row.find('.update_ordercarrier').prop('disabled');
			});
			orders.map(id_order_carrier => {
				row = $(`.del_message tr[id_order_carrier=${id_order_carrier}]`);
				row.find('.result_carrier').css("display", "none");
				row.find('.waiting_carrier').css('display', "unset");
				// $(".loading_carrier_"+id_order).css("display", "unset");
			})
			//{/literal}
			updateStatus(orders);
		});
		$(".start_progress_return").click(function(e) {
			{literal}
			e.preventDefault();
			var orders = getmaillist($('input[name="id_return[]"]:checked'));
			orders = orders.filter(o => {
				row = $('.return').find(`tr[id_return=${o}]`);
				return !row.find('.update_ordercarrier').prop('disabled');
			});
			orders.map(id_return => {
				row = $(`.return tr[id_return=${id_return}]`);
				row.find('.result_carrier').css("display", "none");
				row.find('.waiting_carrier').css('display', "unset");
			})
			//{/literal}
			trackReturn(orders);
		});
		$(".bulk_delete_return").click(function() {
			var returns = getmaillist($('input[name="id_return[]"]:checked'));
			$.ajax({
				type: 'POST',
				url: url_ajax,
				dataType: 'json',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'deleteReturn',
					ajax : true,
					ids: returns,
					secure_key: secure_key,
				},
				success: data => {
					if (data.success) {
						showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
						filterReturn();
					}
					else
						alert(data.msg);
				}
			});
		});
		$(document).on('click', '.delete_return', function(e) {
			let tr = $(this).closest('tr');
			id_return = tr.attr('id_return');
			$.ajax({
				type: 'POST',
				url: url_ajax,
				dataType: 'json',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'deleteReturn',
					ajax : true,
					ids: [id_return],
					secure_key: secure_key,
				},
				success: data => {
					if (data.success) {
						showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
						tr.remove();
					}
					else
						alert(data.msg);
				}
			});
		});
		$('#import-return').on('change', '[name=module]', function(e) {
			module_name = $(this).val();
			$('#import-return .noti').empty();
			if (module_name != '0') {
				$.ajax({
					type: 'POST',
					url: url_ajax,
					dataType: 'json',
					data: {
						controller : 'AdmindeliveryorderautoupdateAjax',
						action : 'checkModule',
						ajax : true,
						module_name: module_name,
						secure_key: secure_key,
					},
					success: data => {
						if (data.success) {
							$('#import-return .noti').css('color', 'green');
							$('#import-return').closest('.swal-modal').find('.swal-button--confirm').prop('disabled', false);
						}
						else {
							$('#import-return .noti').css('color', 'red');
							$('#import-return').closest('.swal-modal').find('.swal-button--confirm').prop('disabled', true);
						}
						$('#import-return .noti').html(data.msg);
					}
				});
			} else {
				$('#import-return').closest('.swal-modal').find('.swal-button--confirm').prop('disabled', true);
			}
		})
		$(".import-return").click(function(e) {
			e.preventDefault();
			var html = document.getElementById("import-return");
			al = swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					id_connector = $('#import-return').find('[name=id_connector]').val();
					module_name = $('#import-return').find('[name=module]').val();
					$.ajax({
						type: 'POST',
						url: url_ajax,
						dataType: 'json',
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'importReturn',
							ajax : true,
							id_connector: id_connector,
							module_name: module_name,
							secure_key: secure_key,
						},
						success: data => {
							if (data.success) {
								showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
								filterReturn();
							}
							else
								alert(data.msg);
						}
					});
				}
			}).then(() => $('#temp').append(html));
			module_name = $('#import-return').find('[name=module]').val();
			if (module_name == '0') {
				$('#import-return').closest('.swal-modal').find('.swal-button--confirm').prop('disabled', true);
			} else {
				$('#import-return').closest('.swal-modal').find('.swal-button--confirm').prop('disabled', false);
			}
		});
		$(".change-connector-return").click(function(e) {
			e.preventDefault();
			var html = $("#temp .change-connector").get(0).cloneNode(true);
			al = swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					let panel = $('.swal-content .change-connector');
					id_connector = panel.find('[name=id_connector]').val();
					returns = getmaillist($('input[name="id_return[]"]:checked'));
					$.ajax({
						type: 'POST',
						url: url_ajax,
						dataType: 'json',
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'updateBulkConnectorReturn',
							ajax : true,
							id_connector: id_connector,
							ids_return: returns,
							secure_key: secure_key,
						},
						success: data => {
							if (data.success) {
								showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
								filterReturn();
							}
							else
								alert(data.msg);
						}
					});
				}
			})
		});
		$(".bulk-force-return").click(function(e) {
			e.preventDefault();
			force_action = 'bulk-return';
			var html = document.getElementById("force-list");
			swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					bulkForceReturn();
				}
			}).then(() => $('#temp').append(html));
		});
		$(".sendBulk").click(function(e) {
			e.preventDefault();
			var orders = getmaillist($('input[name="mailtracksend[]"]:checked'));
			orders = orders.filter(o => {
				id = $('.update_ordercarrier_'+o).attr('id_carrier');
				return id;
			});
			if (!orders.length) {
				swal({
					text: '{l s='No shipment selected' mod='deliveryorderautoupdate'}',
					icon: "error",
				});
				return;
			}
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'sendBulk',
					ajax : true,
					id_orders : orders,
					secure_key: secure_key,
				},
				success: function(return_data)
				{
					swal({
						text: '{l s='Sent' mod='deliveryorderautoupdate'}',
						icon: "success",
					});
				},
			});

		});
		$(".bulkEditCarrier").click(function(e) {
			e.preventDefault();
			var html = document.getElementById("bulk-edit-carrier");
			swal({
				content: html,
				buttons: ['{l s='Close' mod='deliveryorderautoupdate'}', '{l s='Save' mod='deliveryorderautoupdate'}']
			}).then(save => {
				if (save) {
					var orders = getmaillist($('input[name="mailtracksend[]"]:checked'));
					id_carrier = $(html).find('[name=id_carrier]').val();
					$('#temp').append(html);
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: url_ajax,
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'bulkEditCarrier',
							ajax : true,
							id_carrier : id_carrier,
							id_orders : orders,
							secure_key: secure_key,
						},
						success: function(data)
						{
							if (data.success) {
								showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
								filterCarrier();
							} else {
								alert(data.error);
							}
						}
					});

				}
			});
		});
		$(".bulkForce").click(function(e) {
			e.preventDefault();
			force_action = 'bulkForceOrder';
			var html = document.getElementById("force-list");
			swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					bulkForceOrder();
				}
			}).then(() => $('#temp').append(html));
		});
		function getmaillist(option)
		{
			var orders = new Array();
			var id_order;
			option.each(function() {
				id_order = $(this).val();
				orders.push(id_order);
			});
			return orders;
		}
		function trackReturn(orders) {
			var step = 0;
			if (orders.length > 1) {
				order = orders[0];
				orders.shift();
				step = 1;
			} else {
				order = orders[0];
			}
			{literal}
			var id_return = order;
			row = $(`.return tr[id_return=${id_return}]`);
			row.find('.last_result_carrier').css('display', 'none');
			row.find('.loading_carrier').css('display', 'unset');
			//{/literal}
			var id_carrier = row.attr('id_carrier_reference');
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'trackReturn',
					ajax : true,
					id_carrier : id_carrier,
					id_return : id_return,
					secure_key: secure_key,
				},
				success: function(return_data)
				{
					var data = JSON.parse(return_data);
					row.find(".last_result_carrier").html(data.last_result_carrier);
					row.find(".last_result_carrier").css("display", "flex");
					row.find(".loading_carrier").css("display", "none");
					if (step) {
						trackReturn(orders);
					}
				},
				error: function () {
					row.find(".last_result_carrier").css("display", "unset");
					row.find(".loading_carrier").css("display", "none");
				}
			});
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
			{literal}
			var id_order_carrier = order;
			row = $(`.del_message tr[id_order_carrier=${id_order_carrier}]`);
			row.find('.waiting_carrier').css('display', "none");
			//{/literal}
			$(".loading_carrier_"+id_order_carrier).css("display", "unset");
			var id_carrier = $('.update_ordercarrier_'+order).attr('id_carrier');
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateCarriers',
					ajax : true,
					id_carrier : id_carrier,
					orders : id_order_carrier,
					secure_key: secure_key,
				},
				success: function(return_data)
				{
					var data = JSON.parse(return_data);
					$(".last_result_carrier_"+id_order_carrier).html(data.last_result_carrier);
					$(".status_carrier_"+id_order_carrier).html(data.status_carrier);
					$(".last_result_carrier_"+id_order_carrier).css("display", "flex");
					$(".status_temporized_"+id_order_carrier).css("display", "unset");
					$(".status_carrier_"+id_order_carrier).css("display", "unset");
					$(".loading_carrier_"+id_order_carrier).css("display", "none");
					if (step) {
						updateStatus(orders);
					}
				},
				error: function () {
					$(".last_result_carrier_"+id_order_carrier).css("display", "unset");
					$(".status_temporized_"+id_order_carrier).css("display", "unset");
					$(".status_carrier_"+id_order_carrier).css("display", "unset");
					$(".loading_carrier_"+id_order_carrier).css("display", "none");
				}
			});
		}
		var timer;
		trackPanel.on('change', "[name=status]", function() {
			filterCarrier(3);
		});
		trackPanel.on('change', "[name=carrier]", function() {
			filterCarrier(2);
		});
		trackPanel.on('change', ".sliders_control input", function() {
			filterCarrier();
		});
		var timer_return;
		returnPanel.find("input[name=filter_id_return], input[name=filter_id_order], input[name=filter_customer], input[name=filter_tracking_number]").keyup(function(){
			clearTimeout(timer_return);
			timer_return = setTimeout(function() {
				filterReturn();
			}, 500);
		});

		returnPanel.find(".filter_connector, .filter_status, .filter_id_order_return").change(function() {
			filterReturn();
		});
		var timer_issue;
		let issuePanel = $('#issue');
		issuePanel.find("input[name=filter_id_issue], input[name=filter_id_order], input[name=filter_customer]").keyup(function(){
			clearTimeout(timer_issue);
			timer_issue = setTimeout(function() {
				filterIssue();
			}, 500);
		});

		issuePanel.find(".filter_issue_type, .filter_status, .filter_service").change(function() {
			filterIssue();
		});
		trackPanel.find('.range').click(function() {
			trackPanel.find('.range').removeClass('active');
			$(this).addClass('active');
			let key = $(this).data('range');
			let e = trackPanel.find('input[name="date_range"]');
			updateDateRangeFilter(e, key);
			filterCarrier(1);
		});
		$('#force-list').find("[name=date]").datepicker();
		$('#force-list [name=status]').change(function() {
			id_status = $(this).val();
			if (mailStatus.indexOf(id_status) > -1) {
				$('#send_mail_on').prop('checked', true);
			} else {
				$('#send_mail_off').prop('checked', true);
			}
		})
		trackPanel.find('input[name="date_range"]').daterangepicker({
			startDate: moment(date_from).startOf('month'),
			endDate: moment(),
			"locale": {
				"format": date_format
			}
		});
		searchInput = new SearchInput(trackPanel.find('.order_filter'), () => {
			filterCarrier(1);
		});
		trackPanel.find('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
			filterCarrier(1);
		});
		{literal}
		trackPanel.find('.sort').click(e => {
			e.preventDefault();
			let target = $(e.currentTarget);
			let orderBy = $(target).find('.title_box').attr('orderby');
			if (trackOrderBy == orderBy) {
				trackOrderWay = 1 - trackOrderWay;
			}
			if (trackOrderBy) {
				trackPanel.find(`[orderby=${trackOrderBy}]`).find('.icon').hide();
			}
			target.find(`.icon[orderWay=${orderWayArr[trackOrderWay]}]`).show();
			trackOrderBy = orderBy;
			filterCarrier();
		})
		// {/literal}
		function filterReturn()
		{
			var id_return = returnPanel.find("input[name=filter_id_return]").val();
			var id_order = returnPanel.find("input[name=filter_id_order]").val();
			var id_order_return = returnPanel.find("select[name='filter_id_order_return']").val();
			var customer = returnPanel.find("input[name=filter_customer]").val().trim();
			var tracking_number = returnPanel.find("input[name='filter_tracking_number']").val().trim();
			var connector = returnPanel.find("select[name='filter_connector']").val();
			var status = returnPanel.find("select[name=filter_status]").val();

			$loading = $('#loadingReturn');
			$('.return').empty();
			$loading.html(loadingTable);
			let scrollLoad = $loading.data('scrollLoad');
			$.ajax({
				type: 'POST',
				url: url_ajax,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'filterReturn',
					ajax : true,
					secure_key: secure_key,
					filter: {
						id_return: id_return,
						id_order: id_order,
						id_order_return: id_order_return,
						customer: customer,
						tracking_number: tracking_number,
						connector: connector,
						status: status,
					}
				},
				success: function(data) {
					data = JSON.parse(data);
					scrollLoad.page = data.p;
					if (data.html) {
						data.html = $(data.html);
						$('.return').append(data.html);
						$('.return').find('.shipping_number').track({
							onSelect: saveTrackingNumberReturn
						});
						reindex();
					}
					if (data.p >= data.pages_nb) {
						scrollLoad.stop = true;
					} else {
						scrollLoad.stop = false;
					}
					scrollLoad.loading = false;
					returnPanel.find('.total').html(data.total);
					updateBulkStatusReturn();
					$loading.empty();
				}
			})
		}
		function filterIssue()
		{
			var id_issue = issuePanel.find("input[name=filter_id_issue]").val();
			var id_order = issuePanel.find("input[name=filter_id_order]").val();
			var customer = issuePanel.find("input[name=filter_customer]").val().trim();
			var service = issuePanel.find("select[name='filter_service']").val();
			var issue_type = issuePanel.find("select[name='filter_issue_type']").val();
			var status = issuePanel.find("select[name=filter_status]").val();

			$loading = $('#loadingIssue');
			$loading.html(loadingTable);
			$('.issue').empty();
			let scrollLoad = $loading.data('scrollLoad');
			$.ajax({
				type: 'POST',
				url: url_ajax,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'filterIssue',
					ajax : true,
					secure_key: secure_key,
					filter: {
						id_issue: id_issue,
						id_order: id_order,
						service: service,
						customer: customer,
						issue_type: issue_type,
						status: status,
					}
				},
				success: function(data) {
					data = JSON.parse(data);
					scrollLoad.page = data.p;
					if (data.html) {
						data.html = $(data.html);
						issuePanel.find('.issue').append(data.html);
                        // reindex();
                    }
                    if (data.p >= data.pages_nb) {
                    	scrollLoad.stop = true;
                    } else {
                    	scrollLoad.stop = false;
                    }
                    scrollLoad.loading = false;
                    $loading.empty();
                }
            })
		}
	});
</script>
<script type="text/html" id="selectTab">
	<div class="doc-group selectTab">
		<a class="btn btn-default doc" data-tab="history">{l s='Tracking' mod='deliveryorderautoupdate'}</a>
		<a class="btn btn-default doc" data-tab="email_history">{l s='Emails' mod='deliveryorderautoupdate'}</a>
	</div>
</script>
<script type="text/html" id="load-row">
	<tr>
		<td colspan="100%" style="text-align: center">
			<img src="{$fields_value.url|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/loading.gif">
		</td>
	</tr>
</script>

<script type="text/html" id="historyContentWithStep">
	<div class="steps" style="max-width: 800px;margin: auto">

	</div>
	<div class="historyContent">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#event" aria-controls="event" role="tab" data-toggle="tab">{l s='Events' mod='deliveryorderautoupdate'}</a></li>
			<li role="presentation"><a href="#history" aria-controls="history" role="tab" data-toggle="tab">{l s='Requests' mod='deliveryorderautoupdate'}</a></li>
			<li role="presentation"><a href="#email_history" aria-controls="email_history" role="tab" data-toggle="tab">{l s='Emails' mod='deliveryorderautoupdate'}</a></li>
			<li role="presentation"><a href="#dev-mode" aria-controls="dev-mode" role="tab" data-toggle="tab">{l s='Dev mode' mod='deliveryorderautoupdate'}</a></li>
		</ul>
		<div class="tab-content" style="margin-top:20px;">
			<div role="tabpanel" class="tab-pane" id="dev-mode">
				<div style="text-align: right;">
					<button class="track-devmode btn btn-primary">{l s='Track' mod='deliveryorderautoupdate'}</button>
				</div>
				<div class="block-title">{l s='Carrier response' mod='deliveryorderautoupdate'}</div>
				<div id="carrier-response"></div>
				<div class="block-title">{l s='Connector response' mod='deliveryorderautoupdate'}</div>
				<div id="connector-response"></div>
			</div>
			<div role="tabpanel" class="tab-pane" id="event">
				<div role="tabpanel" id="event_conf">
					<div class="table-responsive clearfix events" style="width:100%;display:inline-block; overflow:auto;">
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="history">
				<div role="tabpanel" id="trackhistory_conf">
					<div class="table-responsive table-responsive-row clearfix" style="width:100%;display:inline-block; overflow:auto;">
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
											{*** {foreach $shipping as $s}
											<option value="{$s|escape:'htmlall':'UTF-8'}">{$s|escape:'htmlall':'UTF-8'}</option>
											{/foreach} ***}
										</select>
									</th>
									<th class="center">
										<input class="filter" name="douhistory_Filter_method" value="" type="text">
									</th>
									<th class="center">
										<select name="douhistory_Filter_mail">
											<option value="">-</option>
											<option value="1">{l s='Yes' mod='deliveryorderautoupdate'}</option>
											<option value="2">{l s='No' mod='deliveryorderautoupdate'}</option>
										</select>
									</th>

								</tr>
							</thead>
							<tbody id="track_list" class='dashboard_history'>

							</tbody>
						</table>
					</div>
				</div>

			</div>
			<div role="tabpanel" class="tab-pane" id="email_history">
				<div class="table-responsive table-responsive-row clearfix" style="width: 100%; display: inline-block; overflow-y: auto; overflow-x: hidden;">
					<table class="table dl_dashboard table_form">
						<thead>
							<tr class="nodrag nodrop">
								<th class=" center">
									<span class="title_box"> {l s='Id Email' mod='deliveryorderautoupdate'} </span>
								</th>
								<th class=" center">
									<span class="title_box"> {l s='Order' mod='deliveryorderautoupdate'}  </span>
								</th>
								<th class=" center">
									<span class="title_box"> {l s='Customer' mod='deliveryorderautoupdate'}  </span>
								</th>
								<th class=" center">
									<span class="title_box"> {l s='Id tracking' mod='deliveryorderautoupdate'}  </span>
								</th>
								<th class=" center">
									<span class="title_box"> {l s='Date' mod='deliveryorderautoupdate'}  </span>
								</th>
								<th class=" center">
									<span class="title_box">{l s='Shipping status' mod='deliveryorderautoupdate'} </span>
								</th>
								<th class=" center">
									<span class="title_box"> {l s='Status' mod='deliveryorderautoupdate'}  </span>
								</th>
							</tr>
							<tr class="nodrag nodrop filter row_hover">
								<th class="center">
									<input class="filter" name="douhistory_Filter_id_track" value="" type="text">
								</th>
								<th class="center">
									<input class="filter" name="douhistory_Filter_order" value="" type="text">
								</th>
								<th class="center">
									<input class="filter" name="douhistory_Filter_carrier" value="" type="text">
								</th>
								<th class="center">
									<input class="filter" name="douhistory_Filter_date" value="" type="text">
								</th>
								<th class="center">
									<input class="filter" name="douhistory_Filter_carrier_response" value="" type="text">
								</th>
								<th class="center">
									<input class="filter" name="douhistory_Filter_shipping" value="" type="text">
								</th>
								<th class="center">
									<select name="douhistory_Filter_mail">
										<option value="">-</option>
										<option value="1">{l s='Yes' mod='deliveryorderautoupdate'}</option>
										<option value="2">{l s='No' mod='deliveryorderautoupdate'}</option>
									</select>
								</th>
							</tr>
						</thead>
						<tbody id="email_list" class='dashboard_history'>
						</tbody>
					</table>
				</div>
				<div role="tabpanel" class="tab-pane" id="delete">
				</div>
			</div>
		</div>
	</script>
	<script type="text/html" id="historyContent">
		<div class="historyContent">
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation"><a href="#history" aria-controls="history" role="tab" data-toggle="tab">{l s='Requests' mod='deliveryorderautoupdate'}</a></li>
				<li role="presentation"><a href="#email_history" aria-controls="email_history" role="tab" data-toggle="tab">{l s='Emails' mod='deliveryorderautoupdate'}</a></li>
				<li role="presentation"><a href="#delete" aria-controls="delete" role="tab" data-toggle="tab">{l s='Delete' mod='deliveryorderautoupdate'}</a></li>
			</ul>
			<div class="tab-content" style="margin-top:20px;">
				<div role="tabpanel" class="tab-pane" id="history">
					<div role="tabpanel" id="trackhistory_conf">
						<div class="table-responsive table-responsive-row clearfix" style="width:100%;display:inline-block; overflow:auto;">
							<table class="table dl_dashboard table_form">
								<thead>
									<tr class="nodrag nodrop">
										<th class=" center">
											<span class="title_box"> {l s='Id Tracking' mod='deliveryorderautoupdate'} </span>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Order' mod='deliveryorderautoupdate'}  </span>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Date' mod='deliveryorderautoupdate'}  </span>
										</th>
										<th class=" center">
											<span class="title_box"> {l s='Connector' mod='deliveryorderautoupdate'}  </span>
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
											<input class="filter" name="douhistory_Filter_id_track" value="" type="text">
										</th>
										<th class="center">
											<input class="filter" name="douhistory_Filter_order" value="" type="text">
										</th>
										<th class="center">
											<input class="filter" name="douhistory_Filter_date" value="" type="text">
										</th>
										<th class="center">
											<input class="filter" name="douhistory_Filter_carrier" value="" type="text">
										</th>
										<th class="center">
											<input class="filter" name="douhistory_Filter_carrier_response" value="" type="text">
										</th>
										<th class="center">
											<input class="filter" name="douhistory_Filter_shipping" value="" type="text">
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
								<tbody id="track_list" class='dashboard_history'>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="email_history">
					<div class="table-responsive table-responsive-row clearfix" style="width: 100%; display: inline-block; overflow-y: auto; overflow-x: hidden;">
						<table class="table dl_dashboard table_form">
							<thead>
								<tr class="nodrag nodrop">
									<th class=" center">
										<span class="title_box"> {l s='Id Email' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class=" center">
										<span class="title_box"> {l s='Order' mod='deliveryorderautoupdate'}  </span>
									</th>
									<th class=" center">
										<span class="title_box"> {l s='Customer' mod='deliveryorderautoupdate'}  </span>
									</th>
									<th class=" center">
										<span class="title_box"> {l s='Id tracking' mod='deliveryorderautoupdate'}  </span>
									</th>
									<th class=" center">
										<span class="title_box"> {l s='Date' mod='deliveryorderautoupdate'}  </span>
									</th>
									<th class=" center">
										<span class="title_box">{l s='Shipping status' mod='deliveryorderautoupdate'} </span>
									</th>
									<th class=" center">
										<span class="title_box"> {l s='Status' mod='deliveryorderautoupdate'}  </span>
									</th>
								</tr>
								<tr class="nodrag nodrop filter row_hover">
									<th class="center">
										<input class="filter" name="douhistory_Filter_id_track" value="" type="text">
									</th>
									<th class="center">
										<input class="filter" name="douhistory_Filter_order" value="" type="text">
									</th>
									<th class="center">
										<input class="filter" name="douhistory_Filter_carrier" value="" type="text">
									</th>
									<th class="center">
										<input class="filter" name="douhistory_Filter_date" value="" type="text">
									</th>
									<th class="center">
										<input class="filter" name="douhistory_Filter_carrier_response" value="" type="text">
									</th>
									<th class="center">
										<input class="filter" name="douhistory_Filter_shipping" value="" type="text">
									</th>
									<th class="center">
										<select name="douhistory_Filter_mail">
											<option value="">-</option>
											<option value="1">{l s='Yes' mod='deliveryorderautoupdate'}</option>
											<option value="2">{l s='No' mod='deliveryorderautoupdate'}</option>
										</select>
									</th>
								</tr>
							</thead>
							<tbody id="email_list" class='dashboard_history'>
							</tbody>
						</table>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="delete">
					<div class="alert alert-success" style="display: none">
						<button type="button" class="close" data-dismiss="alert">×</button>
						{l s='Saved' mod='deliveryorderautoupdate'}
					</div>
					<div class="container">
						<div class="form-group">
							<label>{l s='Time range' mod='deliveryorderautoupdate'}</label>
							<select name="range" class="fixed-width-xl">
								<option value="all">{l s='All time' mod='deliveryorderautoupdate'}</option>
								<option value="week">{l s='More than 1 week' mod='deliveryorderautoupdate'}</option>
								<option value="month">{l s='More than 1 month' mod='deliveryorderautoupdate'}</option>
							</select>
						</div>
						<div class="form-group">
							<input type="checkbox" name="step" value="1">
							<label>{l s='Requests history' mod='deliveryorderautoupdate'}</label>
						</div>
						<div class="form-group">
							<input type="checkbox" name="email" value="1">
							<label>{l s='Email sending history' mod='deliveryorderautoupdate'}</label>
						</div>
						<div class="form-group">
							<button class="btn btn-default" id="clearData">{l s='Clear data' mod='deliveryorderautoupdate'}</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</script>
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
		width: 40%;
		margin: 30px auto;
	}
	.doc-group .list {
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}
	.contact {
		text-align: center;
		margin: 0 auto 30px;
	}
	.contact p {
		font-weight: bold;
	}
</style>

{/if}
{$smarty.block.parent}
{/block}