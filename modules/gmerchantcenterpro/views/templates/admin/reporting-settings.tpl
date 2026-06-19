{*
*
* Google merchant center Pro
*
* @author BusinessTech.fr
* @copyright Business Tech
*
*           ____    _______
*          |  _ \  |__   __|
*          | |_) |    | |
*          |  _ <     | |
*          | |_) |    | |
*          |____/     |_|
*
*}
<div class="bootstrap">
	<form class="form-horizontal col-xs-12" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_reporting-form" name="bt_reporting-form" {if $smarty.const._GMCP_USE_JS == true}onsubmit="javascript: oGmcPro.form('bt_feed-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_reporting-settings', 'bt_reporting-settings', false, false, null, 'Reporting', 'loadingReportingDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.reporting.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.reporting.type|escape:'htmlall':'UTF-8'}" />

		

		<h3 class="subtitle"><i class="icon icon-play"></i>&nbsp;{l s='Reporting (products data feed only)' mod='gmerchantcenterpro'}</h3>
		
		{if !empty($bUpdate)}
			{include file="`$sConfirmInclude`"}
		{elseif !empty($aErrors)}
			{include file="`$sErrorInclude`"}
		{/if}

		<div class="alert alert-info" id="info_export">
		<p><strong class="highlight_element">
		{l s='Please read the following FAQ to learn how the tool works :' mod='gmerchantcenterpro'}</strong>
		<a class="badge badge-info pulse pulse2" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/160" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='How does the diagnostic tool work ?' mod='gmerchantcenterpro'}</a></p>
		<p>{l s='This tool allows you' mod='gmerchantcenterpro'}&nbsp;<strong>{l s='to display again the last' mod='gmerchantcenterpro'}</strong>&nbsp;{l s='product feed quality diagnostic that has been generated. Select the combination "language_country_currency" that matches with the product feed you want to display again the last reporting.' mod='gmerchantcenterpro'}</p>
		</div>
		<div class="alert alert-warning">
			<p>{l s='WARNING : if you change your product feed configuration, remember to manually generate again your feed (in "My feeds" tab -> "Products data feed" -> "Your XML files") and to save again this current page, in order to be sure of having the very last reporting.' mod='gmerchantcenterpro'}</p>
		</div>
		<div class="clr_20"></div>

		<div class="form-group" id="optionplus">
			<label class="control-label col-lg-4">
				<span class="label-tooltip" title="{l s='Select "YES" to fill out the reporting file automatically every time the matching feed URL is called (manually, or by an automated task). Select "NO" to fill it out only for manually generating (in "My feeds" tab -> "Products data feed" -> "Your XML files"). However, If you\'ve an important bulk of products (many thousands), you should leave this option on "NO" in order to improve speed and performance of data feeds generating.' mod='gmerchantcenterpro'}">
				
				<b>{l s='Activate reporting file automatic writing :' mod='gmerchantcenterpro'}</b></span>
			</label>
			<div class="col-xs-12 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_reporting" id="bt_reporting_on" value="1" {if !empty($bReporting)}checked="checked"{/if} onclick="javascript: oGmcPro.changeSelect('display_reporting', 'display_reporting', null, null, true, true);"/>
						<label for="bt_reporting_on" class="radioCheck">
							{l s='YES' mod='gmerchantcenterpro'}
						</label>
						<input type="radio" name="bt_reporting" id="bt_reporting_off" value="0" {if empty($bReporting)}checked="checked"{/if} onclick="javascript: oGmcPro.changeSelect('display_reporting', 'display_reporting', null, null, true, false);" />
						<label for="bt_reporting_off" class="radioCheck">
							{l s='NO' mod='gmerchantcenterpro'}
						</label>
						<a class="slide-button btn"></a>
					</span>
				&nbsp;&nbsp;
				<span class="icon-question-sign label-tooltip" title="{l s='Select "YES" to fill out the reporting file automatically every time the matching feed URL is called (manually, or by an automated task). Select "NO" to fill it out only for manually generating (in "My feeds" tab -> "Products data feed" -> "Your XML files"). However, If you\'ve an important bulk of products (many thousands), you should leave this option on "NO" in order to improve speed and performance of data feeds generating.' mod='gmerchantcenterpro'}"></span>
			</div>
		</div>

		<div class="form-group" id="display_reporting" style="display: {if !empty($bReporting)}block{else}none{/if};">
			{if !empty($aLangCurrencies)}
			<div class="clr_10"></div>
			<table class="table">
				<thead>
					<th class="bt_tr_header center">{l s='Language' mod='gmerchantcenterpro'}</th>
					<th class="bt_tr_header center">{l s='Country' mod='gmerchantcenterpro'}</th>
					<th class="bt_tr_header center">{l s='Currency' mod='gmerchantcenterpro'}</th>
					<th class="bt_tr_header center">{l s='Action' mod='gmerchantcenterpro'}</th>
				</thead>
				<tbody>
					{foreach from=$aLangCurrencies item=sISO key=currency}
						<tr>
							<td class="center">{$sISO.lang_iso|escape:'htmlall':'UTF-8'}</td>
							<td class="center">{$sISO.country|escape:'htmlall':'UTF-8'}</td>
							<td class="center">{$sISO.currency|escape:'htmlall':'UTF-8'}</td>
							<td class="center"><a id="handleReportingBox" class="btn btn-sm btn-info fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.reportingBox.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.reportingBox.type|escape:'htmlall':'UTF-8'}&lang={$sISO.full}"><i class="fa fa-file"></i> </a></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			{else}
			<label class="control-label col-lg-3">
			</label>
			
			<div class="col-xs-6">
				<div class="alert alert-warning">
				<p>{l s='There is currently no report available.' mod='gmerchantcenterpro'}
				<p>{l s='Please generate again manually your feed (in "My feeds" tab -> "Products data feed" -> Your XML files"), save again this current page and if there is always nothing, make sure that the "reporting" folder inside the "gmerchantcenterpro" module folder has correct writing permissions.' mod='gmerchantcenterpro'}</div>
			</div>
			{/if}
		</div>

		<div class="center">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
					<div id="{$sModuleName|escape:'htmlall':'UTF-8'}ReportingError"></div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
					<button  class="btn btn-info pull-right" onclick="oGmcPro.form('bt_reporting-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_reporting-settings', 'bt_reporting-settings', false, false, null, 'Reporting', 'loadingReportingDiv');return false;"><i class="process-icon-save"></i>{l s='Save' mod='gmerchantcenterpro'}</button>
				</div>
			</div>
		</div>
	</form>
</div>

{literal}
<script type="text/javascript">
	$(document).ready(function() {
		// manage change value for reporting
		$("#bt_reporting").change(function() {
			if ($(this).val() == "1") {
				$("#display_reporting").show();
			}
			else {
				$("#display_reporting").hide();
			}
		});

		// fancy box
		$("a#handleReportingBox").fancybox({
			'hideOnContentClick' : false,
			'scrolling' : 'yes'
		});

		// handle reporting files
		// $("#select_reporting").bind('change', function (event) {
		// 	$("#select_reporting option:selected").each(function () {
		// 		if ($(this).val() != "") {
		// 			$('a#handleReportingBox').attr('href', $('#handleReportingBox').attr('href') + '&lang=' + $(this).val());
		//
		// 			$('a#handleReportingBox').click();
		// 		}
		// 	});
		// });

		//bootstrap components init
		{/literal}{if !empty($bAjaxMode)}{literal}
		$('.label-tooltip, .help-tooltip').tooltip();
		{/literal}{/if}{literal}
	});
</script>
{/literal}