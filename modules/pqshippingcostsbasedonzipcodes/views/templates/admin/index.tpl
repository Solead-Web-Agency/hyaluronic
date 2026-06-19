{*
* ProQuality (c) All rights reserved.
*
* DISCLAIMER
*
* Do not edit, modify or copy this file.
* If you wish to customize it, contact us at addons4prestashop@gmail.com.
*
* @author    Andrei Cimpean (ProQuality) <addons4prestashop@gmail.com>
* @copyright 2015-2016 ProQuality
* @license   Do not edit, modify or copy this file
*}
{$media|escape:'quotes':'UTF-8'|replace:'\\':''}
<table border="0" style="width: 100%;" id="scboz_box" class="scboz radius5-top" bgcolor="#F7F7F7">
	<tr>
		<td>
			<div align="left" style="margin-top: 0px;">
				<table border="0">
					<tr>
						<td><img border="0" src="{$module_path|escape:'quotes':'UTF-8'}views/img/pqshippingcostsbasedonzipcodes.png"></td>
						<td><b>{$module_version|escape:'quotes':'UTF-8'}</b></td>
					</tr>
				</table>
			</div>
			<div id="scboz_tabs">
				<ul>
					<li><a id="tabs_configuration_a" href="#scboz_tabs_1" style="line-height: 30px;"><span style="cursor: hand; cursor: pointer;">Configuration</span></a><span id="conditions_ajax_load_span"></span></li>
				</ul>
				<div id="scboz_tabs_1">
					<table border="0" width="100%">
						<tr>
							<td><span class="scboz label-tooltip">{l s='Import conditions from .CSV file (separator must be ","):' mod='pqshippingcostsbasedonzipcodes'} <b>{l s='NEW!!! SUPPORT FOR ZIPCODES THAT CONTAIN LETTERS' mod='pqshippingcostsbasedonzipcodes'}</b></span></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td><input type="file" class="" id="csv_upload" />
						<b><a href="{$module_path|escape:'quotes':'UTF-8'}uploads/sample.csv">{l s='Click here to download a sample .CSV file as an example of the accepted files for batch import.' mod='pqshippingcostsbasedonzipcodes'}</a></b></td>
					</tr>
					<tr>
						<td><hr class="scboz style2"></td>
					</tr>
				</table>
				<table border="0" width="100%">
					<tr>
						<td height="30"><span class="scboz label-tooltip"><b>{l s='LIST OF CONDITIONS:' mod='pqshippingcostsbasedonzipcodes'}</b></span></td>
					</tr>
					<tr>
						<td>
							<!--
							<table id="conditions-grid" class="scboz conditions-grid">
									<tr>
												<th class="scboz id_condition" data-field="id_condition">{l s='ID' mod='pqshippingcostsbasedonzipcodes'}</th>
												<th class="scboz country" data-field="country">{l s='Country' mod='pqshippingcostsbasedonzipcodes'}</th>
												<th class="scboz zone" data-field="zone">{l s='Zone' mod='pqshippingcostsbasedonzipcodes'}</th>
												<th class="scboz zipcodes" data-field="zipcodes">{l s='Zipcodes' mod='pqshippingcostsbasedonzipcodes'}</th>
												<th class="scboz upd_date" data-field="upd_date">{l s='Date' mod='pqshippingcostsbasedonzipcodes'}</th>
												<th class="scboz actions" data-template="actions">{l s='Actions' mod='pqshippingcostsbasedonzipcodes'}</th>
									</tr>
								</table>
							-->
							<table id="conditions_grid" class="scboz display compact radius5" cellspacing="0" width="100%" style="border: 1px solid #dddddd;">
								<thead>
									<tr>
										<th>{l s='Country' mod='pqshippingcostsbasedonzipcodes'}</th>
										<th>{l s='Zone' mod='pqshippingcostsbasedonzipcodes'}</th>
										<th>{l s='Filter' mod='pqshippingcostsbasedonzipcodes'}</th>
										<th>{l s='Zipcode min' mod='pqshippingcostsbasedonzipcodes'}</th>
										<th>{l s='Zipcode max' mod='pqshippingcostsbasedonzipcodes'}</th>
										<th>{l s='Date' mod='pqshippingcostsbasedonzipcodes'}</th>
									</tr>
								</thead>
								<tfoot>
								<tr>
									<th>{l s='Country' mod='pqshippingcostsbasedonzipcodes'}</th>
									<th>{l s='Zone' mod='pqshippingcostsbasedonzipcodes'}</th>
									<th>{l s='Filter' mod='pqshippingcostsbasedonzipcodes'}</th>
									<th>{l s='Zipcode min' mod='pqshippingcostsbasedonzipcodes'}</th>
									<th>{l s='Zipcode max' mod='pqshippingcostsbasedonzipcodes'}</th>
									<th>{l s='Date' mod='pqshippingcostsbasedonzipcodes'}</th>
								</tr>
								</tfoot>
							</table>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
				</table>
			</div>
		</div>
	</td>
</tr>
</table>