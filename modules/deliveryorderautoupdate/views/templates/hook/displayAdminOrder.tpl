{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
.left-content {
	display: flex;
	flex-direction: column;
}
.left-content .col-lg-3{
	background:#F8F8F8;
}
.right-content {
	display: flex;
	flex-direction: column;
}
.right-content .tab-content {
	margin-top:20px;
	overflow: visible;
	min-height: 150px;
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
.tracking-box {
}
.carrier {
	position: relative;
}
.tracking-box .title {
	color:#fff;
}
.left-content .title span{
	display:inline-block;
	background:#008000;
	padding:5px;
	border-radius:5px;
}
.step-list {
	max-width: 800px;
	margin: auto;
}
.last_result_carrier {
	position: absolute;
    right: 109px;
    top: 1px;
}
.popup_order {
	background: rgba(255, 255, 255, 0.5) none repeat scroll 0 0;
	display: block;
	height: 100%;
	left: 0;
	display:none;
	position: absolute;
	top: 0;
	width: 100%;
	z-index: 2500;
}
.wrapper img {
	display: inline-block;
	padding: 9px;
	width: 100%;
	vertical-align: unset;
}
.deliveryorderautoupdate .nav.nav-tabs {
    background: #E4E4E4;
}
.panel.orderdetail_form {
	min-height: 250px;
}
.deliveryorderautoupdate .nav-tabs > li {
    float: left;
    margin-bottom: -1px;
}
.deliveryorderautoupdate .dropdown-menu {
	left: unset;
}
.deliveryorderautoupdate .dropdown-menu a{
	cursor: pointer;
}
.deliveryorderautoupdate .nav > li > a {
    position: relative;
    display: block;
    padding: 10px 15px;
}
.deliveryorderautoupdate .nav-tabs li a {
    font-size: 1em;
    font-family: "Open Sans",helvetica,arial,sans-serif;
    text-transform: uppercase;
    font-weight: 600;
}
.deliveryorderautoupdate .nav-tabs > li > a {
    margin-right: 2px;
    line-height: 1.42857;
    border: 1px solid transparent;
    border-radius: 3px 3px 0 0;
}
.deliveryorderautoupdate [class^="process-icon-"] {
	width: 20px;
	height: 20px;
	font-size: 20px;
}
.deliveryorderautoupdate .nav-tabs > li > a.active {
	color: #555;
	background-color: #fff;
	border: 1px solid #ddd;
	border-bottom-color: rgb(221, 221, 221);
	border-bottom-color: transparent;
	cursor: default;
}
.list-action-enable img {
    padding: 6px;
    width: 100%;
}
.htr_shipping {
	display: flex;
}
.htr_shipping a{
	flex: 0 0 30px;
	width: 30px;
	height: 30px;
}
.htr_shipping .right_shipping{
	margin-left: 5px;
}
.shipment {
	display: flex;
	flex-direction: column;
	background-color: #e8eef3;
	padding: 5px;
}
.shipment label {
	display: block;
}
.shipment .dateinput {
	padding: 6px 5px;
	border: 1px solid #c7d6db;
	min-height: 30px;
}
.shipment .uimenu {
	width: 100%;
	right: 0;
	left: 0;
}
.shipment .uimenu .input-container{
	flex:  1;
}
.shipment-select {
}
.shipment .btn-group-action {
	margin-top: 10px;
}
.left-block label {
	margin: 8px 5px 3px 5px;
}
.left-block {
	display: flex;
	flex-direction: column;
}
.deliveryorderautoupdate .openmenu {
	cursor: pointer;
}
.updateCarrier, .updateConnector {
	height: 100%!important;
}
.hide {
	display: none;
}
.status-list .status {
	text-align: left;
	padding: 5px;
	cursor: pointer;
}
.right_shipping {
    display: inline-block;
    margin-left: 5px;
}
table.event-list .date {
    max-width: 200px;
    width:  200px;
}
table.event-list td,table.event-list th{
    padding:  5px;
}
.fit-cell {
	width: 1%;
	white-space: nowrap;
}
.loading_carrier .icon-spin {
	margin: 0;
}
#modal .modal-dialog {
    width: inherit;
    max-width: 60%;
    margin: 30px auto;
}
.panel.deliveryorderautoupdate .nav.nav-tabs li.active a {
	z-index: 1!important;
}
.issue label, .status label {
    display: inline-block;
    max-width: 100%;
    font-weight: bold;
    padding: 5px;
    margin: 0;
}
@media only screen and (min-width: 768px) {
	#modal .modal-body {
		min-height: 400px;
	}
}
{/literal}
</style>
<fieldset class="panel deliveryorderautoupdate col-lg-12 card"  style="position: relative;">
	<div class="hide" id="hidden-objects">
		<div class="create-issue">
			<h3 style="text-align: center">{l s='Create an issue for this shipment' mod='deliveryorderautoupdate'}</h3>
			<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
				<select class="custom-select updateIssue">
					{foreach $issues as $s}
					<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
				<h4 style="text-align: left">{l s='Detail' mod='deliveryorderautoupdate'}</h4>
				<textarea name="detail"></textarea>
			</div>
		</div>
		<div class="update-issue-status">
			<h3 style="text-align: center">{l s='Update status of issue' mod='deliveryorderautoupdate'} #<span class="id_issue"></span></h3>
			<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
				<select class="custom-select updateIssueStatus" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
					{foreach $issue_status as $s}
					<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
				<h4 style="text-align: left">{l s='Detail' mod='deliveryorderautoupdate'}</h4>
				<textarea name="detail"></textarea>
			</div>
		</div>
		<div id="editIssue">
			<ul class="uimenu ui-menu ui-widget ui-widget-content ui-corner-all" style="display: flex;">
				<div class="input-container">
					<select class="custom-select updateIssue">
						<option value="0">{l s='No Issue' mod='deliveryorderautoupdate'}</option>
						{foreach $issues as $s}
						<option value="{$s.id|escape:'htmlall':'UTF-8'}">{$s.id|escape:'htmlall':'UTF-8'}_{$s.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<button class="btn btn-default closeMenu"><i class="icon-remove"></i></button>
			</ul>
		</div>
		<div id="editCarrier">
			<ul class="uimenu ui-menu ui-widget ui-widget-content ui-corner-all" style="display: flex;">
				<div class="input-container">
					<select class="updateCarrier custom-select" name="updateCarrier">
						<option value="0">-</option>
						{foreach $carriers as $s}
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
					<select class="updateConnector custom-select" name="updateConnector">
						<option value="0">-</option>
						{foreach $connectors as $s}
						<option value="{$s.id_connector|escape:'htmlall':'UTF-8'}">{$s.id_connector|escape:'htmlall':'UTF-8'}_{$s.connector|escape:'htmlall'}</option>
						{/foreach}
					</select>
				</div>
				<button class="btn btn-default closeMenu"><i class="icon-remove"></i></button>
			</ul>
		</div>
		<div id="add-shipment">
			<h3 style="text-align: center">{l s='Add shipment to order' mod='deliveryorderautoupdate'} <span class="id_order"></span></h3>
			<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
				<select name="id_carrier" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
					<option value="0">{l s='Select carrier' mod='deliveryorderautoupdate'}</option>
					{foreach $carriers as $s}
					<option value="{$s.id_carrier|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall'}</option>
					{/foreach}
				</select>
				<input style="font-weight: bold; text-align: center; height: 40px" type="text" name="shipping_number" placeholder="{l s='Enter shipping number' mod='deliveryorderautoupdate'}">
			</div>
		</div>
		<div id="add-return">
			<h3 style="text-align: center">{l s='Add a return shipment' mod='deliveryorderautoupdate'} <span class="id_order"></span></h3>
			<div class="bootstrap" style="max-width: 250px; text-align: center; margin:auto">
				<select name="id_order_return" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
					<option value="0">{l s='Select a return request' mod='deliveryorderautoupdate'}</option>
					{foreach $requests as $s}
					<option value="{$s.id_order_return|escape:'htmlall':'UTF-8'}">{$s.name|escape:'htmlall'}</option>
					{/foreach}
				</select>
				<select name="id_connector" style="margin-bottom: 10px; text-align: center; font-weight: bold; height: 40px">
					<option value="0">{l s='Select connector' mod='deliveryorderautoupdate'}</option>
					{foreach $connectors as $s}
					<option value="{$s.id_connector|escape:'htmlall':'UTF-8'}">{$s.connector|escape:'htmlall'}</option>
					{/foreach}
				</select>
				<input style="font-weight: bold; text-align: center; height: 40px" type="text" name="shipping_number" placeholder="{l s='Enter shipping number' mod='deliveryorderautoupdate'}">
			</div>
		</div>
		<div id="force-list">
			<h3 style="text-align: center">{l s='Force shipping status' mod='deliveryorderautoupdate'}</h3>
			<div class="status-list bootstrap">
				{foreach $statuses as $s}
				{if $s->id_status > 11}{continue}{/if}
				<div class="status" data-id_status="{$s->id_status|escape:'htmlall':'UTF-8'}">
					<a target="_blank" class="list-action-enable action-hisenabled" style="background: {$s->color|escape:'htmlall':'UTF-8'}">
						<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$s->id_status|escape:'htmlall':'UTF-8'}.png" />
					</a>
					<div class="right_shipping">
						{$s->EN|escape:'htmlall':'UTF-8'}
					</div>
				</div>
				{/foreach}
			</div>
		</div>{l s='Create an issue for this shipment' mod='deliveryorderautoupdate'}
	</div>
	<div class="panel-heading card-header" style="position: relative;">
		<div class="card-header-title" style="display: inline-block;">
			<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/logo.png" width="20" height="20">
		{l s='Tracking Center' mod='deliveryorderautoupdate'}</div>
		<div class="tracking-center" style="float:right;text-transform: none;">
			<a href="{$trackingpage_url|escape:'htmlall':'UTF-8'}">{l s='Go to tracking page' mod='deliveryorderautoupdate'}</a>
		</div>
	</div>
	<div class="row">
		<div class="tracking-box col-lg-12">
		{include file="../hook/tracking-box.tpl"}
		</div>
	</div>
	<div class="popup_order"></div>
	<input type="hidden" value="{$ajaxdel_url|escape:'html':'UTF-8'|htmlspecialchars_decode}" name="admin_url" />
	<input type="hidden" value="{$id_order|escape:'html':'UTF-8'}" name="id_order" />
	<input type="hidden" value="{$secure_key|escape:'html':'UTF-8'}" name="secure_key" />

	<div class="modal fade" id="modal" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" style="display:inline-block;">Modal Header</h4>
					<button type="button" class="close" data-dismiss="modal" style="float:right">&times;</button>
				</div>
				<div class="modal-body">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</fieldset>
<div style="clear: both"></div>

<script type="text/html" id="historyContentWithStep">
	<div class="steps" style="max-width: 800px;margin: auto">

	</div>
	<div class="historyContent">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#event-modal" aria-controls="event" role="tab" data-toggle="tab">{l s='Events' mod='deliveryorderautoupdate'}</a></li>
			<li role="presentation"><a href="#history-modal" aria-controls="history" role="tab" data-toggle="tab">{l s='Requests' mod='deliveryorderautoupdate'}</a></li>
			<li role="presentation"><a href="#email_history-modal" aria-controls="email_history" role="tab" data-toggle="tab">{l s='Emails' mod='deliveryorderautoupdate'}</a></li>
		</ul>
		<div class="tab-content" style="margin-top:20px;">
			<div role="tabpanel" class="tab-pane" id="event-modal">
				<div role="tabpanel" id="event_conf">
					<div class="table-responsive clearfix events" style="width:100%;display:inline-block; overflow:auto;">
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="history-modal">
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
			<div role="tabpanel" class="tab-pane" id="email_history-modal">
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
<script type="text/javascript">
	$(document).ready(function () {
		var force_action = 'order';
		var force_id_return = 0;
		var force_id_order = 0;
		var url_ajax = $("input[name='admin_url']").val();
		var secure_key = $('input[name=secure_key]').val();
		var timer;
		$('.nav-tabs a[href="#shipment"]').tab('show');
		$(".table-shipment").on('click', ".update_ordercarrier", function() {
			row = $(this).closest('tr');
			id_order_carrier = row.attr('id_order_carrier');
			id_connector = row.find('.edit_connector').attr('id_connector');
			shipping_number = row.find('.parcel_number').text().trim();
			if (!id_connector) {
				swal('{l s='No connector linked to carrier' mod='deliveryorderautoupdate'}');
				return;
			}
			if (!shipping_number) {
				swal('{l s='Shipping number is empty' mod='deliveryorderautoupdate'}');
				return;
			}
			updateStatus(id_order_carrier, id_connector);
		});
		$(document).on('click', '.tracking_url[data-view=false]', function(e) {
			e.preventDefault();
			swal('tracking url not set');
		})
		$(document).on('click', '.json_server', function(e) {
			{literal}
			e.preventDefault();
			let href = $(this).attr('href');
			$('#modal .modal-header .modal-title').html("");
			$('#modal .modal-body').html(`<iframe type="application/xml" style="position:absolute;width:100%;height:100%;right:0" src=${href}></iframe>`);;
			$('#modal').modal();
			// {/literal}
		})
		function updateUI(data) {
			if (!data.tracking_url)
				$('.tracking_url').attr('data-view', false);
			else
				$('.tracking_url').attr('data-view', true);
			if (data.tracking_url != undefined) {
				$('.tracking_url').attr('href', data.tracking_url);
			}
			if (data.tracking_number != undefined)
				$('.parcel_number').html(data.tracking_number);
			if (data.carrier_name != undefined)
				$('.carrier_name').html(data.carrier_name);
			if (data.id_carrier != undefined)
				$('.edit_carrier').attr('id_carrier', data.id_carrier);
			if (data.id_reference != undefined)
				$('.edit_carrier').attr('id_reference', data.id_reference);
			if (data.connector_name != undefined)
				$('.connector_name').html(data.connector_name);
			if (data.id_connector != undefined)
				$('.edit_connector').attr('id_connector', data.id_connector);
			if (data.date_add != undefined)
				$('.shipment-date').html(data.date_add);
		}
		function updateStatus(id_order_carrier, id_carrier)
		{
			secure_key = $('input[name=secure_key]').val();
			if (!id_order_carrier) {
				swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
				return;
			}
			{literal}row = $(`.table-shipment tr[id_order_carrier=${id_order_carrier}]`);//{/literal}
			row.find('.htr_shipping').css('display', 'none');
			row.find('.loading_carrier').css('display', 'flex');
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				dataType: 'json',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateCarriers',
					ajax : true,
					id_carrier : id_carrier,
					orders : id_order_carrier,
					carrier_stt : 1,
					secure_key: secure_key
				},
				success: function(data)
				{
					row.find(".htr_shipping").html(data.last_result_carrier);
					row.find(".htr_shipping").css("display", "flex");
					row.find(".loading_carrier").css("display", "none");
				},
			});
		}
		$("select[name='douhistory_Filter_mail']").change(function() {
			filterHistoryCarrier();
		});
		$("input[name='douhistory_Filter_date'], input[name='douhistory_Filter_carrier_response'], input[name='douhistory_Filter_shipping'], input[name='douhistory_Filter_method']").keyup(function() {
			clearTimeout(timer);
			timer = setTimeout(function() {
				filterHistoryCarrier();
			}, 1000);
		});
		function filterHistoryCarrier()
		{
			var date = $("input[name='douhistory_Filter_date']").val();
			var carrier_response = $("input[name='douhistory_Filter_carrier_response']").val().toLowerCase();
			var shipping = $("input[name='douhistory_Filter_shipping']").val().toLowerCase();
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
					if ((hide_date)|| (hide_carrier_response) || (hide_shipping) || (hide_method) || (hide_email)) {
						$(this).hide();
					} else {
						$(this).show();
					}
				}
			});
		}

		$('.changeShipment').change(function() {
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				dataType: 'json',
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'changeShipment',
					ajax : true,
					id_order_carrier : $(this).val(),
					secure_key: secure_key
				},
				success: function(data)
				{
					if (data.success) {
						$('.tracking-box').html(data.html);
						$('.nav-tabs a[href="#event"]').tab('show');
						shipment = data.shipment;
						shipment.carrier_name = shipment.name;
						shipment.connector_name = shipment.connector;
						updateUI(shipment);
					} else {
						alert(data.err)
					}
				},
			});
		})
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
		function addEditUI(e) {
			e.find('.shipment-date').track({
				dateinput: true,
				onSelect: function(val, e) {
					val = val.trim();
					row = $(e).closest('tr');
					id_order_carrier = row.attr('id_order_carrier');
					if (!id_order_carrier) {
						swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
						return;
					}
					$.ajax({
						type: 'POST',
						url: $("input[name='admin_url']").val(),
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'updateShipmentDate',
							ajax : true,
							id_order_carrier : id_order_carrier,
							shipment_date: val,
							secure_key: secure_key,
						},
						success: function(data)
						{
							data = JSON.parse(data);
							if (data.success) {
								showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
								row.find('.shipment-date').html(val);
							}
							else
								alert(data.msg);
						}
					});
				}
			});
			e.find('.parcel_number').track({
				onSelect: function(val, e) {
					val = val.replaceAll(' ', '');
					row = $(e).closest('tr');
					id_order_carrier = row.attr('id_order_carrier');
					if (!id_order_carrier) {
						swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
						return;
					}
					$.ajax({
						type: 'POST',
						url: $("input[name='admin_url']").val(),
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'updateTrackingNumber',
							ajax : true,
							id_order_carrier : id_order_carrier,
							tracking_number: val,
							secure_key: secure_key,
						},
						success: function(data)
						{
							data = JSON.parse(data);
							if (data.success) {
								showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
								row.find('.parcel_number').html(val);
								row.find('.json_server').attr('href', data.json_server);
								if (val) {
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
			});
			e.find('.return .shipping_number').track({
				onSelect: saveTrackingNumberReturn
			});
		}
		addEditUI($('.deliveryorderautoupdate'));
		$(document).on('change', '.updateCarrier', function() {
			row = $(this).closest('tr');
			id_order = $('[name="id_order"]').val();
			id_order_carrier = row.attr('id_order_carrier');
			id_carrier = $(this).val();
    		$('#editCarrier').append(panel);
			if (!id_order_carrier) {
				swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
				return;
			}
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
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
					if (data.success) {
						showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
						row.find('.carrier_name').html(data.carrier_name);
						row.find('.edit_carrier').attr('id_carrier', data.id_carrier);
						row.find('.edit_carrier').attr('id_reference', data.data.id_reference);
						if (!data.id_carrier) {
							row.find('.edit_connector').addClass('not-allowed');
						} else {
							row.find('.edit_connector').removeClass('not-allowed');
						}
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
					else
						alert(data.msg);
				}
			});
		})
		$('#shipment').on('change', '.updateConnector', function() {
			row = $(this).closest('tr');
			id_reference = row.find('.edit_carrier').attr('id_reference');
			id_carrier = row.find('.edit_carrier').attr('id_carrier');
			id_order_carrier = row.attr('id_order_carrier');
			id_connector = $(this).val();
    		$('#editConnector').append(editConnector);
			if (!id_order_carrier) {
				swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
				return;
			}
			$.ajax({
				type: 'POST',
				url: $("input[name='admin_url']").val(),
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'updateConnector',
					ajax : true,
					id_order_carrier : id_order_carrier,
					id_carrier : id_reference,
					id_connector: id_connector,
					secure_key: secure_key,
				},
				success: function(data)
				{
					data = JSON.parse(data);
					if (data.success) {
						showSuccessMessage('{l s='Updated' mod='deliveryorderautoupdate'}');
						rows = $('#shipment').find('tr');
						rows.each(function(){
							row = $(this);
							let carrier = $(this).find('.edit_carrier').attr('id_carrier');
							if (carrier == id_carrier) {
								row.find('.connector_name').html(data.connector_name);
								row.find('.edit_connector').attr('id_connector', data.id_connector);
								row.find('.json_server').attr('href', data.json_server);
								if (data.id_connector) {
									row.find('.edit_connector').removeClass('no-connector');
									toggleTrackButton(row, false);
								} else {
									row.find('.edit_connector').addClass('no-connector');
									toggleTrackButton(row, true);
								}
							}

						});
					}
					else
						alert(data.msg);
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
		$('.add-shipment').on('click', function(e) {
			e.preventDefault();
			id_order = $('[name="id_order"]').val();
			var html = document.getElementById("add-shipment");
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
								row = $(data.html);
								$('#shipment .table-shipment tbody').prepend(row);
								addEditUI(row);
							}
							else
								alert(data.msg);
						}
					});
				} else {
					$('#hidden-objects').append(html);
				}
			});
			console.log('blo');
		});
		$('.deliveryorderautoupdate').on('click', '.add-return', function(e) {
			e.preventDefault();
			id_order = $('[name="id_order"]').val();
			var html = document.getElementById("add-return");
			swal({
				content: html,
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Save' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					id_order = $('[name="id_order"]').val();
					shipping_number = $('#add-return').find('[name=shipping_number]').val();
					id_connector = $('#add-return').find('[name=id_connector]').val();
					id_order_return = $('#add-return').find('[name=id_order_return]').val();
					$.ajax({
						type: 'POST',
						url: url_ajax,
						dataType: 'json',
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'addReturn',
							ajax : true,
							id_order: id_order,
							id_order_return: id_order_return,
							shipping_number: shipping_number,
							id_connector: id_connector,
							secure_key: secure_key,
						},
						success: data => {
							if (data.success) {
								showSuccessMessage('{l s='Saved' mod='deliveryorderautoupdate'}');
								$('.deliveryorderautoupdate .returns').html(data.html);
							}
							else
								alert(data.msg);
						}
					});
				} else {
					$('#hidden-objects').append(html);
				}
			});
		});
		$('.delete-shipment').on('click', function(e) {
			e.preventDefault();
			swal({
				title: 'Delete shipment',
				buttons: ["{l s='Close' mod='deliveryorderautoupdate'}", "{l s='Confirm' mod='deliveryorderautoupdate'}"],
			}).then(save => {
				if (save) {
					id_order_carrier = $('.changeShipment').val();
					if (!id_order_carrier) {
						swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
						return;
					}
					$.ajax({
						type: 'POST',
						url: url_ajax,
						dataType: 'json',
						data: {
							controller : 'AdmindeliveryorderautoupdateAjax',
							action : 'deleteShipment',
							ajax : true,
							id_order_carrier: id_order_carrier,
							secure_key: secure_key,
						},
						success: data => {
							if (data.success) {
								{literal}$('.changeShipment').find(`[value="${id_order_carrier}"]`).remove();//{/literal}
								$('.changeShipment').trigger('change');
							}
							else
								alert(data.msg);
						}
					});
				}
			})
		});
		$('.force').on('click', function(e) {
			e.preventDefault();
			force_action = 'order';
			force_id_order = $(this).closest('tr').attr('id_order_carrier');
			var html = document.getElementById("force-list");
			swal({
				content: html,
				button: '{l s='Close' mod='deliveryorderautoupdate'}'
			}).then(() => $('#hidden-objects').append(html));
		});
		$(document).on('click', '.status-list .status', function(e) {
			switch(force_action) {
				case 'order':
				forceOrder(this);
				break;
				case 'return':
				forceReturn(this);
				break;
			}
		});
		function forceOrder(target) {
			id_status = $(target).data('id_status');
			swal.close();
			if (!force_id_order) {
				swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
				return;
			}
			{literal}row = $(`.table-shipment tr[id_order_carrier=${force_id_order}]`);//{/literal}
			row.find('.htr_shipping').css('display', 'none');
			row.find('.loading_carrier').css('display', 'flex');
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
					id_order_carrier : force_id_order,
					secure_key: secure_key,
				},
				success: function(data)
				{
					if (data.success) {
						row.find(".htr_shipping").html(data.last_status_result);
						row.find(".htr_shipping").css("display", "flex");
						row.find(".loading_carrier").css("display", "none");
					}
					else
						alert(data.msg);
				}
			});
		}
		function forceReturn(target) {
			id_status = $(target).data('id_status');
			swal.close();
			if (!force_id_return) {
				swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
				return;
			}
			{literal}
			row = $(`.return tr[id_return=${force_id_return}]`);
			row.find('.htr_shipping').css('display', 'none');
			row.find('.loading_carrier').css('display', 'flex');
			// {/literal}
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
						row.find(".htr_shipping").html(data.last_status_result);
						row.find(".htr_shipping").css("display", "flex");
						row.find(".loading_carrier").css("display", "none");
					}
					else
						alert(data.msg);
				}
			});
		}
		$(document).on('click', '.split-shipment', function(e) {
			e.preventDefault();
			row = $(this).closest('tr');
			id_order_carrier = row.attr('id_order_carrier');
			shipping_number = row.find('.parcel_number').text().trim();
			count = shipping_number.split(',').length;
			if (!id_order_carrier) {
				swal("{l s='No shipment found' mod='deliveryorderautoupdate'}");
				return;
			}
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
								row.remove();
								rows = $(data.html);
								$('#shipment .table-shipment tbody').prepend(rows);
								addEditUI(rows);
								// data.shipments.forEach(shipment => {
								// 	{literal}$('.changeShipment').prepend(`<option value="${shipment.id}">${shipment.id}</option>`).val(shipment.id);//{/literal}
								// })
								// $('.changeShipment').trigger('change');
							}
							else
								alert(data.msg);
						}
					});
				} else {
				}
			});
		});
		editIssue = $('#editIssue').children();
		$(document).on('click', '.edit_issue', function(e) {
			if ($(this).find('.uimenu').length)
				return;
			$(this).closest('div').append(editIssue);
			editIssue.find('select').val($(this).attr('id_issue')).trigger('open');
		})
		$('.deliveryorderautoupdate').on('change', '.updateIssue', function() {
			let row = $(this).closest('.issue');
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
		$(document).on('click', '.edit_issue_status', function(e) {
			e.preventDefault();
			row = $(this).closest('.status');
			var html = $("#hidden-objects .update-issue-status").get(0).cloneNode(true);
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
								$('.issue-list').prepend(data.html);
							} else {
								alert(data.msg);
							}
						}
					});

				}
			})
		})
		$(document).on('click', '.create_issue', function(e) {
			e.preventDefault();
			row = $(this).closest('tr');
			id_order_carrier = row.attr('id_order_carrier');
			var html = $("#hidden-objects .create-issue").get(0).cloneNode(true);
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
								row.find('.open_issue').html('<a class="check_issue" href="#">issue</a>');
							} else {
								alert(data.msg);
							}
						}
					});

				}
			})
		})
		$(document).on('click', ".track_return", function(e) {
			e.preventDefault();
			let orders = [];
			let tr = $(this).closest('tr');
			let id_return = tr.attr('id_return');
			orders.push(id_return);
			trackReturn(orders);
		});
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
			row.find('.htr_shipping').css('display', 'none');
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
					row.find(".htr_shipping").html(data.last_result_carrier);
					row.find(".htr_shipping").css("display", "flex");
					row.find(".loading_carrier").css("display", "none");
					if (step) {
						trackReturn(orders);
					}
				},
				error: function () {
					row.find(".htr_shipping").css("display", "unset");
					row.find(".loading_carrier").css("display", "none");
				}
			});
		}
		$(document).on('click', '.force_return', function(e) {
			e.preventDefault();
			force_action = 'return';
			force_id_return = $(this).closest('tr').attr('id_return');
			var html = document.getElementById("force-list");
			swal({
				content: html,
				button: '{l s='Close' mod='deliveryorderautoupdate'}'
			}).then(() => $('#hidden-objects').append(html));
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
		$(document).on('click', '.track_history', function(e) {
			e.preventDefault();
			var id_order_carrier = $(this).closest('tr').attr('id_order_carrier');
			$('#modal .modal-header .modal-title').html("{l s='Tracking History of shipment' mod='deliveryorderautoupdate'} "+id_order_carrier);
			$('#modal').find('.modal-dialog').attr('class', 'modal-dialog');
			$('#modal').modal();
			$('#modal .modal-body').html('');
			setHistoryContent(id_order_carrier);
			activeTab('event');
		});
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
			// setScrollLoad();
		}
		{literal}
		setStep = function(id_order_carrier) {
			var url_ajax = $("input[name='admin_url']").val();
			row = $('.del_message').find(`tr[id_order_carrier=${id_order_carrier}]`);
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
        $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        	var target = $(e.target).attr("href");
        	id_order_carrier = $(this).closest('.historyContent').data('id_order_carrier');
        	if (!$(this).data('active')) {
        		$(this).data('active', true);
	        	switch (target) {
	        		case '#history-modal':
						showHistory(id_order_carrier);
	        			break;
	        		case '#email_history-modal':
	        			showEmailHistory(id_order_carrier);
		        		break;
	        		defaut:
	        		break;
	        	}
        	}
        });
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
							$('#history-modal #track_list').append(data);
						} else {
							$('#history-modal #track_list').html(data);
						}
						$('#history-modal #track_list').attr('page', page).attr('data-id_order_carrier', id_order_carrier).attr('total', response.total);
						if (callback)
							callback();
					}
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
						$('#email_history-modal #email_list').append(data);
					} else {
						$('#email_history-modal #email_list').html(data);
					}
					$('#email_history-modal #email_list').attr('page', page).attr('data-id_order', id_order_carrier).attr('total', response.total);
					if (callback)
						callback();
				},
			});
		}
		var panel = $('#editCarrier').children();
		var editConnector = $('#editConnector').children();
		var editIssue = $('#editIssue').children();
		var editIssueStatus = $('#editIssueStatus').children();
		$(document).on('click', '.edit_carrier', function() {
			$(this).closest('td').append(panel);
			panel.find('select').val($(this).attr('id_carrier'));
		})
		$(document).on('click', '.edit_connector', function() {
			if ($(this).hasClass('not-allowed')) return;
			$(this).closest('td').append(editConnector);
			editConnector.find('select').val($(this).attr('id_connector'));
		})
		$(document).on('click', '.check_issue', function(e) {
			e.preventDefault();
			var id_order_carrier = $(this).closest('tr').attr('id_order_carrier');
			$('#modal .modal-header .modal-title').html("{l s='Issue of delivery' mod='deliveryorderautoupdate'} "+id_order_carrier);
			$('#modal').find('.modal-dialog').attr('class', 'modal-dialog');
			$('#modal').modal();
			$('#modal .modal-body').html('');
			$.ajax({
				type: 'POST',
				url: url_ajax,
				headers: { "cache-control": "no-cache" },
				dataType: 'json',
				async: true,
				data: {
					controller : 'AdmindeliveryorderautoupdateAjax',
					action : 'checkIssue',
					ajax : true,
					id_order_carrier : id_order_carrier,
					secure_key: secure_key,
				},
				success: function(data)
				{
					$('#modal .modal-body').html(data.html);
				}
			});
		})
	});
	function toggleTrackButton(row, disabled) {
		row.find('.update_ordercarrier').prop('disabled', disabled);
		if (disabled) {
			row.find('.btn-group').attr('title', '{l s='disabled' mod='deliveryorderautoupdate'}')
			row.find('.dropdown-menu li').hide();
			row.find('.dropdown-menu .no-disable').show();
		} else {
			row.find('.btn-group').attr('title', '{l s='Track' mod='deliveryorderautoupdate'}');
			row.find('.dropdown-menu li').show();
		}
	}
</script>
