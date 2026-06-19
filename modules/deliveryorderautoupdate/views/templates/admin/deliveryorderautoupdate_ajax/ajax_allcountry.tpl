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
<h4>{l s='There are %1$d API connectors' sprintf=[$carrier_xml|count] mod='deliveryorderautoupdate'}</h4>
<div class="">

	<div class="table-responsive clearfix" >
		<table class="table deliveryorderautoupdate carrier_form ">
			<thead>
				<tr class="nodrag nodrop">
					<th class=" center" width="25%">
						<span class="title_box">{l s='Connector' mod='deliveryorderautoupdate'} </span>
					</th>
					<th class=" center" width="30%">
						<span class="title_box">{l s='API' mod='deliveryorderautoupdate'} </span>
					</th>
					<th class=" center" width="25%">
						<span class="title_box">{l s='Country' mod='deliveryorderautoupdate'} </span>
					</th>
					<th class=" center" width="20%">
					</th>
				</tr>
				<tr class="nodrag nodrop filter ">
					<th class="center">
						<input type="text" value="{$id_search|escape:'htmlall':'UTF-8'}" name="filter_id" class="filter_id">
					</th>
					<th class="center">
						<input type="text" value="{$carrier_search|escape:'htmlall':'UTF-8'}" name="filter_carrier" class="filter_carrier">
					</th>
					<th class="center">
						<select name="filter_country" class="filter_country">
							<option value="">-</option>
							{foreach $country_xml as $cnn}
							<option value="{$cnn.id|escape:'htmlall':'UTF-8'}" {if $country_search == $cnn.id}selected{/if}>{$cnn.name|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
					</th>
					<th class="center">
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach $carrier_xml as $data}
					<tr>
						<th class=" center">
							<span class="title_box id_carrier"> {$data.id|escape:'htmlall':'UTF-8'}  </span>
						</th>
						<th class=" left carrier_fieldname">
							{if $data.logo}
							<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/logos/{$data.id|escape:'htmlall':'UTF-8'}.jpg" title="{l s='logo' mod='deliveryorderautoupdate'}" alt="{l s='logo' mod='deliveryorderautoupdate'}" />
							{/if}
							<div>
							<span class="title_box name_carrier"> {$data.name|escape:'htmlall':'UTF-8'}  </span>
							{if $data.detail}<br/><span class="small_title"> {$data.detail|escape:'htmlall':'UTF-8'}  </span>{/if}
							</div>
						</th>
						<th class=" center">
							<span class="title_box country_carrier" data-country="{$data.iso_code|escape:'htmlall':'UTF-8'}"> {$data.country|escape:'htmlall':'UTF-8'}  </span>
						</th>
						<th class=" center">
							<div class="infor_country">
								<span class="success_country" style="color:green;display:none;">{l s='Connector Successfully added' mod='deliveryorderautoupdate'}</span>
								<span class="false_country" style="color:red;display:none;">{l s='Can not add' mod='deliveryorderautoupdate'}</span>
							</div>
							<span class="title_box">
								<span class="loading_carrier loading_carrier_{$data.id|escape:'htmlall':'UTF-8'}" style="display:none;">
									<img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/loading_carrier.gif" title="{l s='Loading' mod='deliveryorderautoupdate'}" alt="{l s='Loading' mod='deliveryorderautoupdate'}">
								</span>
								<div id="not-installed" {if $data.add_cr !== 1}style="display:none"{/if}>
									<a class="btn btn-default add_country" data-id="{$data.id|escape:'htmlall':'UTF-8'}" data-name="{$data.name|escape:'htmlall':'UTF-8'}">
										<i class="icon icon-plus-square"></i>
										{l s='Enable' mod='deliveryorderautoupdate'}
									</a>
								</div>
								<div class="btn-group-action" id="installed" {if $data.add_cr != 2}style="display:none"{/if}>
									<div class="btn-group">
										<a class="btn btn-default edit_carrier edit_confcarrier" id_carrier="{$data.id|escape:'htmlall':'UTF-8'}"  name="edit_carrier" >
											<i class="icon-user"></i>
											{l s='Credentials' mod='deliveryorderautoupdate'}
										</a>

										<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
											<span class="caret"> </span>
										</button>
										<ul class="dropdown-menu">
											<li>
												<a class="del_carrier" href="#" id_carrier="{$data.id|escape:'htmlall':'UTF-8'}"  name="del_carrier" data-credential="{$data.hasCredential|escape:'htmlall':'UTF-8'}" >
													<i class="icon-minus-sign-alt"></i>
													{l s='Disable' mod='deliveryorderautoupdate'}
												</a>
											</li>
										</ul>
									</div>
								</div>
							</span>
						</th>
					</tr>
				{/foreach}

			</tbody>
		</table>
		<div class="box_carrier" style="display:none">
			<div class="box_form">
				<div class="col-md-12"  style="position: relative; z-index: 9; top: 1% !important;"><div class="close_popup pull-right" style="cursor: pointer;"><img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/close.png" width="22px" alt="close" title="close" class="img-responsive" /></div></div>
				<div class="add_html panel">

				</div>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function() {
	var url_ajax = '{$url_ajax|escape:'html':'UTF-8'|htmlspecialchars_decode}';
	var token = $("input[name='secure_key']").val();
	var timer;
	$(".filter_id, .filter_carrier").keyup(function(){
		clearTimeout(timer);
		var id = $("input[name='filter_id']").val().toLowerCase();
		var carrier = $("input[name='filter_carrier']").val().toLowerCase();
		var country = $(".filter_country").val().toLowerCase();
		timer = setTimeout(function() {
			search(id, carrier, country);
		}, 100);
	});
	$(".filter_country").change(function(){
		var id = $("input[name='filter_id']").val().toLowerCase();
		var carrier = $("input[name='filter_carrier']").val().toLowerCase();
		var country = $(this).val().toLowerCase();
		search(id, carrier, country);
	});
	function search(id, carrier, country)
	{
		$('.carrier_form tbody').find('tr').each(function(i, e) {
			eid = $(e).find('.id_carrier').html().toLowerCase();
			ecarrier = $(e).find('.name_carrier').html().toLowerCase();
			ecountry = $(e).find('.country_carrier').attr('data-country').toLowerCase();
			if (eid.indexOf(id)>-1 && ecarrier.indexOf(carrier)>-1 && ecountry.indexOf(country)>-1) {
				$(e).show();
			} else {
				$(e).hide();
			}
		})
	}
	$(".add_country").click(function(){
		var country_btn = $(this);
		let tr = country_btn.closest('tr');
		var id = $(this).data('id');
		$(".loading_carrier_"+id).show();
		tr.find('#not-installed').hide();
		$.ajax({
			type: 'POST',
			headers: { "cache-control": "no-cache" },
			url: url_ajax,
			data: {
				controller : 'AdmindeliveryorderautoupdateAjax',
				action : 'AllCountry',
				ajax : true,
				id : id,
				name : $(this).data('name'),
				l : 1,
				secure_key: token
			},
			success: function(data)
			{
				$(".loading_carrier_"+id).hide();
				if (data == 'false') {
					tr.find('#not-installed').show();
				} else {
					tr.find('#installed').show();
				}
			}
		});
	});
});
</script>