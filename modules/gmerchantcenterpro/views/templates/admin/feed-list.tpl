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
<div class="bootstrap" id="gmcp">
	<form class="form-horizontal col-xs-12" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_feedlist-{$sDisplay|escape:'htmlall':'UTF-8'}-form" name="bt_feedlist-{$sDisplay|escape:'htmlall':'UTF-8'}-form" {if $smarty.const._GMCP_USE_JS == true}onsubmit="javascript: oGmcPro.form('bt_feedlist-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_feed-list-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_feed-list-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, null, 'FeedList{$sDisplay|escape:'htmlall':'UTF-8'}', 'loadingFeedListDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.feedListUpdate.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.feedListUpdate.type|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sDisplay" id="sFeedListDisplay" value="{if !empty($sDisplay)}{$sDisplay|escape:'htmlall':'UTF-8'}{else}data{/if}" />

		{* BEGIN - classic product data feed *}
		{if !empty($sDisplay) && $sDisplay == 'data'}
			<h3 class="subtitle"><i class="fa fa-book"></i>&nbsp;{l s='Products data feed' mod='gmerchantcenterpro'}</h3>
			<div class="clr_10"></div>
			{if !empty($bUpdate)}
				{include file="`$sConfirmInclude`"}
			{elseif !empty($aErrors)}
				{include file="`$sErrorInclude`"}
			{/if}

		{if !empty($sGmcLink)}
		{if !empty($iTotalProductToExport)}
		{literal}
			<script type="text/javascript">
				var aDataFeedGenOptions = {
					'sURI' : '{/literal}{$sURI}{literal}',
					'sParams' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.dataFeed.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.dataFeed.type|escape:'htmlall':'UTF-8'}{literal}',
					'iShopId' : {/literal}{$iShopId|intval}{literal},
					'sFilename' : '',
					'iLangId' : 0,
					'sLangIso' : '',
					'sCountryIso' : '',
					'sCurrencyIso' : '',
					'iStep' : 0,
					'iTotal' : {/literal}{$iTotalProductToExport|intval}{literal},
					'iProcess' : 0,
					'sDisplayedCounter' : '#regen_counter',
					'sDisplayedBlock' : '#syncCounterDiv',
					'sDisplaySuccess' : '#regen_xml',
					'sDisplayTotal' : '#total_product_processed',
					'sLoaderBar' : 'myBar',
					'sErrorContainer' : 'AjaxFeed',
					'bReporting' : 1,
					'sFeedType' : 'product',
					'sDisplayReporting' : '#handleGenerateReportingBox',
					'sResultText' : '{/literal}{l s='product(s) exported' mod='gmerchantcenterpro'}{literal}',
					'bExcludedProduct' : '{/literal}{$bExcludedProduct}{literal}'
				};
			</script>
		{/literal}

			{* USE CASE - AVAILABLE FEED FILE LIST *}
		{if !empty($aFeedFileListProduct)}
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2"></div>
					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4">
						<div class="box xml-product">
							{if $iTotalProduct > 5000}
								<div class="ribbon"><span>{l s='Recommended' mod='gmerchantcenterpro'}</span></div>
							{/if}
							<div class="box-icon  icon-active-cog">
								<span class="fa fa-cog fa-3x"></span>
							</div>
							<div class="info col-xs-12">
								<h4 class="text-center">{l s='PHYSICAL FILE + CRON TASK' mod='gmerchantcenterpro'}</h4>
								<p class="center box-export col-xs-12">{l s='This export method is recommended for large products catalogs (usually > 5000 products)' mod='gmerchantcenterpro'}</p>
								<div class="center col-xs-12">
									<a id="btn-xml-product" class="btn btn btn-lg-custom  btn-lg btn-success">{l s='Use this solution' mod='gmerchantcenterpro'}</a>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4">
						<div class="box xml-fly">
							{if $iTotalProduct <= 5000}
								<div class="ribbon"><span>{l s='Recommended' mod='gmerchantcenterpro'}</span></div>
							{/if}
							<div class="box-icon icon-active-file">
								<span class="icon icon-file icon-3x"></span>
							</div>
							<div class="info col-xs-12">
								<h4 class="text-center">{l s='ON THE FLY OUTPUT' mod='gmerchantcenterpro'}</h4>
								<p class="center box-export col-xs-12">{l s='This export method is recommended for small products catalogs (usually < 5000 products)' mod='gmerchantcenterpro'}</p>
								<div class="center col-xs-12">
									<a id="btn-fly-product" class="btn btn-lg-custom btn-lg btn-success">{l s='Use this solution' mod='gmerchantcenterpro'}</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="clr_50"></div>

			<div class="bt-fb-cron-product" style="display: none;">
				<ul class="nav nav-tabs" id="myTab">
				<li class="active">
					<a data-toggle="tab" href="#xml"><i class="fa fa-file-code-o"></i>&nbsp;{l s='Your XML files' mod='gmerchantcenterpro'}</a>
				</li>
				<li class="nav-item">
					<a data-toggle="tab" href="#cron"><i class="fa fa-server"></i>&nbsp;{l s='Your CRON URL\'s' mod='gmerchantcenterpro'}</a>
				</li>
				</ul>
				<div class="tab-content" id="myTabContent">
					{*Start first tab*}
					<div class="tab-pane active" id="xml">
						<div class="clr_50"></div>
						<div id="syncCounterDiv" style="display: none;" class="alert alert-success">
							<button type="button" class="close" onclick="$('#syncCounterDiv').hide();">×</button>
							<h3>{l s='Export in progress' mod='gmerchantcenterpro'}</h3>
							<div class="row">
								<b>{l s='Number of generated products:' mod='gmerchantcenterpro'}</b>&nbsp;
								<input  size="5" name="bt_regen-counter" id="regen_counter" value="0" />&nbsp;
								{l s='on' mod='gmerchantcenterpro'}&nbsp;{$iTotalProduct|intval} ({l s='total of products on the shop' mod='gmerchantcenterpro'})
								<div class="clr_10"></div>
								<div class="progress">
									<div class="progress-bar bg-success progress-bar-striped active" id="myBar"></div>
								</div>

							</div>
							<div class="row">
								<div id="{$sModuleName|escape:'htmlall':'UTF-8'}AjaxFeedError"></div>
							</div>
							<div class="clr_20"></div>
						</div>

						<div class="alert alert-info">
							<p><strong class="highlight_element">{l s='Here are the XML files that will receive your feed data every time the CRON task will be executed.' mod='gmerchantcenterpro'}</strong></p><br />
							<ul>
								<li>{l s='If you want to use a general CRON task to update several XML files at the same time, check first the relevant files below. Then,' mod='gmerchantcenterpro'}<b>&nbsp;{l s='SAVE YOUR SELECTION' mod='gmerchantcenterpro'}</b>&nbsp;{l s='and set up your CRON task by using the general CRON URL that appears in "Your CRON URL\'s" tab.' mod='gmerchantcenterpro'}</li>
								<li>{l s='If you want to set up a different CRON task for each feed in order to update them one by one, do not check any file and use the independent CRON URL\'s that are in "Your CRON URL\'s" tab.' mod='gmerchantcenterpro'}</li>
							</ul>
						</div>

						<div class="btn-actions pull-right">
							<div class="btn btn-default btn-mini" id="categoryCheck" onclick="return oGmcPro.selectAll('input.bt_export_feed', 'check');"><span class="icon-plus-square"></span>&nbsp;{l s='Check All' mod='gmerchantcenterpro'}</div> - <div class="btn btn-default btn-mini" id="categoryUnCheck" onclick="return oGmcPro.selectAll('input.bt_export_feed', 'uncheck');"><span class="icon-minus-square"></span>&nbsp;{l s='Uncheck All' mod='gmerchantcenterpro'}</div>
							<div class="clr_10"></div>
						</div>

						<table border="0" cellpadding="2" cellspacing="2" class="table table-responsive">
							<tr class="bt_tr_header text-center">
								<th class="center col-xs-1">{l s='Regenerate during CRON' mod='gmerchantcenterpro'}</th>
								<th class="center">{l s='Country' mod='gmerchantcenterpro'}</th>
								<th class="center">{l s='Language' mod='gmerchantcenterpro'}</th>
								<th class="center">{l s='Currency' mod='gmerchantcenterpro'}</th>
								<th class="center">{l s='Last update' mod='gmerchantcenterpro'}</th>
								<th class="center">{l s='Action' mod='gmerchantcenterpro'}</th>
							</tr>
							{foreach from=$aFeedFileListProduct name=feed key=iKey item=aFeed}
								<tr id="regen_xml_{$aFeed.lang|lower|escape:'htmlall':'UTF-8'}_{$aFeed.country|lower|escape:'htmlall':'UTF-8'}">
									<td class="center"><input type="checkbox" class="bt_export_feed" name="bt_cron-export[]" value="{$aFeed.lang|lower|escape:'htmlall':'UTF-8'}_{$aFeed.country|escape:'htmlall':'UTF-8'}_{$aFeed.currencyIso|escape:'htmlall':'UTF-8'}" {if !empty($aFeed.checked)}checked="checked"{/if} /></td>
									<td class="center">{$aFeed.countryName|escape:'htmlall':'UTF-8'} - {$aFeed.country|escape:'htmlall':'UTF-8'}</td>
									<td class="center">{$aFeed.langName|escape:'htmlall':'UTF-8'}</td>
									<td class="center">{$aFeed.currencySign|escape:'htmlall':'UTF-8'} - {$aFeed.currencyIso|escape:'htmlall':'UTF-8'}</td>
									<td class="center">{$aFeed.filemtime|escape:'htmlall':'UTF-8'}</td>
									<td  class="center">
										<a class="label-tooltip btn btn-sm btn-default" title="{l s='Generate' mod='gmerchantcenterpro'}" href="javascript:void(0);" class="regenXML" onclick="if (oGmcPro.bGenerateXmlFlag){literal}{{/literal}alert('{l s='One data feed is currently in progress...' mod='gmerchantcenterpro'}'); return false;{literal}}{/literal}aDataFeedGenOptions.sLangIso='{$aFeed.lang|lower|escape:'htmlall':'UTF-8'}';aDataFeedGenOptions.sCountryIso='{$aFeed.country|lower|escape:'htmlall':'UTF-8'}';aDataFeedGenOptions.sCurrencyIso='{$aFeed.currencyIso|escape:'htmlall':'UTF-8'}';aDataFeedGenOptions.iLangId='{$aFeed.langId|intval}';aDataFeedGenOptions.sFilename='{$aFeed.filename|escape:'htmlall':'UTF-8'}';aDataFeedGenOptions.sFeedType='product';$('#syncCounterDiv').show();oGmcPro.generateDataFeed(aDataFeedGenOptions);"><span class="icon-refresh"></span></a>&nbsp;<div id="total_product_processed_{$aFeed.lang|lower|escape:'htmlall':'UTF-8'}_{$aFeed.country|lower|escape:'htmlall':'UTF-8'}" style="font-style: bold; display: none; margin-left:20px; vertical-align:text-top;"></div>
										<a class="label-tooltip btn btn-default btn-md" title="{l s='See' mod='gmerchantcenterpro'}" target="_blank" href="{$aFeed.link|escape:'htmlall':'UTF-8'}"><i class="fa fa-eye"></i></a>
										<a type="button" href="{$aFeed.link|escape:'htmlall':'UTF-8'}" download class="label-tooltip btn btn-md btn-default" title="{l s='Download' mod='gmerchantcenterpro'}">&nbsp;<i class="fa fa-download"></i></a>
										<a type="button" class="label-tooltip btn btn-md btn-default btn-copy js-tooltip js-copy" title="{l s='Copy' mod='gmerchantcenterpro'}" data-toggle="tooltip" data-placement="bottom" data-copy="{$aFeed.link|escape:'htmlall':'UTF-8'}">&nbsp;<i class="fa fa-copy"></i></a>
									</td>
								</tr>
							{/foreach}
						</table>
						<a style="display: none;" id="handleGenerateReportingBox" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.reportingBox.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.reportingBox.type|escape:'htmlall':'UTF-8'}"></a>

						<div class="clr_10"></div>

						<div class="center">
							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
								</div>
								<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
									<button  class="btn btn-info pull-right" onclick="oGmcPro.form('bt_feedlist-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_feed-list-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_feed-list-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, null, 'FeedList{$sDisplay|escape:'htmlall':'UTF-8'}', 'loadingFeedListDiv');return false;"><i class="process-icon-save"></i>{l s='Save' mod='gmerchantcenterpro'}</button>
								</div>
							</div>
						</div>

					</div>
					{*start 2nd tab*}
					<div class="tab-pane fade" id="cron" role="tabpanel">
						<div class="clr_10"></div>
						<div class="alert alert-info form-group">
							{l s='Please follow our FAQ to know' mod='gmerchantcenterpro'}&nbsp;&nbsp;<a class="badge badge-info" target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/30#bt_cron"><i class="icon icon-link"></i>&nbsp;{l s='how to create a CRON task' mod='gmerchantcenterpro'}</a>
							<br /><br />
							<b>{l s='Be careful :' mod='gmerchantcenterpro'}</b>&nbsp;{l s='schedule your CRON task so that the XML files are up to date when Google will retreive them to update your data in Google Shopping.' mod='gmerchantcenterpro'}
							<div class="clr_5"></div>
						</div>

						<div class="form-group">
							<label class="control-label col-xs-12 col-md-11 col-lg-2">
								<span class="label-tooltip"  title="{l s='Use this URL to update several feed files at the same time (check first the relevant files in "Your XML files" tab, remembering to save). If you note that all your files aren\'t correctly generated, set up a CRON task for each feed in order to update them one by one (use the independent URL\'s below), in a time-shifted manner (to avoid a servor time-out).' mod='gmerchantcenterpro'}"><b>{l s='My general CRON URL' mod='gmerchantcenterpro'}</b></span> :
							</label>
							{if !empty($aCronLangProduct)}
								<div class="col-xs-12 col-md-5 col-lg-5">
									<input type="text" value="{$sCronUrlProduct|escape:'htmlall':'UTF-8'}">
								</div>
								<a class="badge badge-info pulse pulse2" href="{$sCronUrlProduct|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='Execute the CRON in browser' mod='gmerchantcenterpro'}</a>
							{else}
								<div class="col-xs-12 col-md-5 col-lg-5">
									<div class="alert alert-warning">{l s='You cannot use this CRON URL because you didn\'t select any XML file in the previous "Your XML files" tab. Please check first the files to be filled in at the same time,' mod='gmerchantcenterpro'}&nbsp;<b>{l s='save your selection' mod='gmerchantcenterpro'}</b>&nbsp;{l s='and then come back here to use this general URL to set up your CRON task.' mod='gmerchantcenterpro'}</div>
								</div>
							{/if}
						</div>

						{if !empty($aCronListProduct)}
							<table border="0" cellpadding="2" cellspacing="2" class="table table-responsive">
								<tr class="bt_tr_header text-center">
									<th class="center">{l s='Language' mod='gmerchantcenterpro'}</th>
									<th class="center">{l s='Country' mod='gmerchantcenterpro'}</th>
									<th class="center">{l s='Currency' mod='gmerchantcenterpro'}</th>
									<th class="center">{l s='Action' mod='gmerchantcenterpro'}</th>
								</tr>
								{foreach from=$aCronListProduct name=feed key=iKey item=aCronFeed}
									<tr>
										<td class="center">{$aCronFeed.langName|escape:'htmlall':'UTF-8'}</td>
										<td class="center">{$aCronFeed.countryName|escape:'htmlall':'UTF-8'} - {$aCronFeed.country|escape:'htmlall':'UTF-8'}</td>
										<td class="center">{$aCronFeed.currencySign|escape:'htmlall':'UTF-8'} - {$aCronFeed.currencyIsoCron|escape:'htmlall':'UTF-8'}</td>
										<td class="center">
											<a type="button" class="label-tooltip btn btn-md btn-default btn-copy js-tooltip js-copy" title="{l s='Copy' mod='gmerchantcenterpro'}" data-toggle="tooltip" data-placement="bottom" data-copy="{$aCronFeed.link|escape:'htmlall':'UTF-8'}">&nbsp;<i class="fa fa-copy"></i></a>
											<a class="label-tooltip btn btn-default btn-md" target="_blank" title="{l s='Execute' mod='gmerchantcenterpro'}" href="{$aCronFeed.link|escape:'htmlall':'UTF-8'}"><i class="fa fa-play-circle"></i></a>
										</td>
									</tr>
								{/foreach}
							</table>
						{/if}

					</div>
				</div>
			</div>
			{* USE CASE - NO AVAILABLE LANGUAGE : CURRENCY : COUNTRY *}
		{else}
			<div class="alert alert-warning">
				{l s='Either you just updated your configuration by deactivating the advanced file security feature (in which case, please reload the page), or, there are no file because of no valid languages / currencies / countries, according to the Google\'s requirements.' mod='gmerchantcenterpro'}
				<b><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/52&lg={$sCurrentIso|escape:'htmlall':'UTF-8'}">{l s='See our FAQ about localization prerequisites.' mod='gmerchantcenterpro'}</a></b>
			</div>
		{/if}

			<div class="bt-fb-fly-product" style="display: none;">
				<h2 class="bt-md-title">{l s='Your PHP URL\'s for on-the-fly output (for catalogs of < 5000 products)' mod='gmerchantcenterpro'}</h2>
				<div class="clr_hr"></div>
				<div class="clr_20"></div>
				{* USE CASE - AVAILABLE FEED FILE LIST *}
				{if !empty($aFlyFileListProduct)}
					<p class="alert alert-info form-group">
						{l s='Please follow our FAQ to know' mod='gmerchantcenterpro'}&nbsp;&nbsp;<a class="badge badge-info" target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/30#bt_fly"><i class="icon icon-link"></i>&nbsp;{l s='how to manage the on-the-fly output URL\'s' mod='gmerchantcenterpro'}</a>
						<br/>
						<br/>
						{l s='You can use the "on-the-fly output" URL\'s if your catalog is relatively small (5000 products maximum), if not, choose the solution of setting up a CRON task. However, if you are on a dedicated server, this one may also be able to process larger catalogs if you increase its PHP time-out and memory usage limits.' mod='gmerchantcenterpro'}
					</p>
					<div class="clr_5"></div>

					<table border="0" cellpadding="2" cellspacing="2" class="table ">
						<tr class="bt_tr_header text-center">
							<th class="center">{l s='Country' mod='gmerchantcenterpro'}</th>
							<th class="center">{l s='Language ' mod='gmerchantcenterpro'}</th>
							<th class="center">{l s='Currency' mod='gmerchantcenterpro'}</th>
							<th class="center"></th>
						</tr>
						{foreach from=$aFlyFileListProduct name=feed key=iKey item=aFlyFeed}
							<tr>
								<td class="center">{$aFlyFeed.countryName|escape:'htmlall':'UTF-8'} - {$aFlyFeed.countryIso|escape:'htmlall':'UTF-8'}</td>
								<td class="center">{$aFlyFeed.langName|escape:'htmlall':'UTF-8'} - {$aFlyFeed.iso_code|strtoupper|escape:'htmlall':'UTF-8'}</td>
								<td class="center">{$aFlyFeed.currencySign|escape:'htmlall':'UTF-8'} - {$aFlyFeed.currencyIso|escape:'htmlall':'UTF-8'}</td>
								<td class="center">
									<a class="label-tooltip btn btn-default btn-md" title="{l s='See' mod='gmerchantcenterpro'}" target="_blank" href="{$aFlyFeed.link|escape:'htmlall':'UTF-8'}"><i class="fa fa-eye"></i></a>
									<a type="button" class="label-tooltip btn btn-md btn-default btn-copy js-tooltip js-copy" title="{l s='Copy' mod='gmerchantcenterpro'}" data-toggle="tooltip" data-placement="bottom" data-copy="{$aFlyFeed.link|escape:'htmlall':'UTF-8'}">&nbsp;<i class="fa fa-copy"></i</a>
								</td>
							</tr>
						{/foreach}
					</table>
					{* USE CASE - NO AVAILABLE LANGUAGE : CURRENCY : COUNTRY *}
				{else}
					<div class="alert alert-warning">
						{l s='There are no files because of no valid languages / currencies / countries according to the Google\'s requirements.' mod='gmerchantcenterpro'}
						<b><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/52&lg={$sCurrentIso|escape:'htmlall':'UTF-8'}">{l s='See our FAQ about localization prerequisites.' mod='gmerchantcenterpro'}</a></b>
					</div>
				{/if}
			</div>
			{* USE CASE - NO CATEGORY OR BRAND HAVE BEEN SELECTED *}
		{else}
			<div class="clr_15"></div>
			<div class="alert alert-warning">
				{l s='No category or brand have been selected : please go to "Feeds management -> Export method" tab, and tick at least one category (or brand). You also need to check if there is at least one product in the selected categories (or brands). Remember : the categories used here are the products DEFAULT categories.' mod='gmerchantcenterpro'}
			</div>
		{/if}
			{* USE CASE - NO GOOGLE LINK HAS BEEN FILLED OUT *}
		{else}
			<div class="clr_15"></div>

			<div class="alert alert-warning">
				{l s='You must first update the module\'s configuration options before the files can be accessed.' mod='gmerchantcenterpro'}
			</div>
		{/if}
		{/if}
		{* END - classic product data feed *}

		{* BEGIN - promo product data feed *}
		{if !empty($sDisplay) && $sDisplay == 'promo'}
			<h3 class="subtitle"><i class="fa fa-bookmark-o"></i>&nbsp; {l s='Special offers data feed' mod='gmerchantcenterpro'}</h3>
			{* USE CASE - AVAILABLE FEED FILE LIST *}
			{if !empty($aFlyFileListDiscount)}
				<div class="clr_10"></div>
				<div class="alert alert-info form-group">
				{l s='Please follow our FAQ to know' mod='gmerchantcenterpro'}&nbsp;&nbsp;<a class="badge badge-info" target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/110"><i class="icon icon-link"></i>&nbsp;{l s='how to configure your special offers feed' mod='gmerchantcenterpro'}</a>
				</div>

				<table border="0" cellpadding="2" cellspacing="2" class="table ">
					<tr class="bt_tr_header text-center">
						<th class="center">{l s='Language' mod='gmerchantcenterpro'}</th>
						<th class="center">{l s='Country' mod='gmerchantcenterpro'}</th>
						<th class="center"></th>
					</tr>
					{foreach from=$aFlyFileListDiscount name=feed key=iKey item=aFlyFeed}
						<tr>
							<td class="center">{$aFlyFeed.langName|escape:'htmlall':'UTF-8'} - {$aFlyFeed.iso_code|strtoupper|escape:'htmlall':'UTF-8'}</td>
							<td class="center">{$aFlyFeed.countryName|escape:'htmlall':'UTF-8'} - {$aFlyFeed.countryIso|escape:'htmlall':'UTF-8'}</td>
							<td class="center">
								<a class="btn btn-default btn-md" target="_blank" href="{$aFlyFeed.link|escape:'htmlall':'UTF-8'}"><i class="fa fa-eye"></i></a>
								<a type="button" class="btn btn-md btn-default btn-copy js-tooltip js-copy" data-toggle="tooltip" data-placement="bottom" data-copy="{$aFlyFeed.link|escape:'htmlall':'UTF-8'}">&nbsp;<i class="fa fa-copy"></i></a>
							</td>
						</tr>
					{/foreach}
				</table>
				{* USE CASE - NO AVAILABLE LANGUAGE : CURRENCY : COUNTRY *}
			{else}
				<div class="alert alert-warning">
					{l s='There are no files because of no valid languages / currencies / countries according to the Google\'s requirements.' mod='gmerchantcenterpro'}
					<b><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/52">{l s='See our FAQ about localization prerequisites.' mod='gmerchantcenterpro'}</a></b>
					<div class="clr_10"></div>
					<h3 class="subtitle"><i class="icon-globe"></i>&nbsp;{l s='Locale prerequisites'  mod='gmerchantcenterpro'}</h3>
					<div class="alert alert-info">
						<strong class="highlight_element">
							{l s='*** IMPORTANT NOTE *** : a feed for a country will be generated if the country\'s language and official currency are installed and active on your shop, and if the country is part of those where Google Shopping is implemented. For more information, please read the' mod='gmerchantcenterpro'}&nbsp;<a href="https://support.google.com/merchants/answer/160637?hl={$sCurrentIso|escape:'htmlall':'UTF-8'}&visit_id=1-636342381361070010-4017773094&rd=1" target="_blank">{l s='Google official documentation.' mod='gmerchantcenterpro'}</a>
							<br>
						</strong>
						<br/>
						{l s='If some countries do not appear in the list of your XML files or PHP URL\'s (in "My feeds" tab), you must check your country, language and currency ISO codes in your back-office ("Localization tab") and look if they respect for example uppercase or lowercase. Actually, you must write these codes EXACTLY how they are written in the table of the ' mod='gmerchantcenterpro'}
						<a href="https://support.google.com/merchants/answer/160637?hl={$sCurrentIso|escape:'htmlall':'UTF-8'}&visit_id=1-636342381361070010-4017773094&rd=1)" target="_blank"><b>{l s='Google official documentation.' mod='gmerchantcenterpro'}</b></a>
					</div>
				</div>
			{/if}
		{/if}
		{* END - promo product data feed *}

		{* BEGIN - product reviews data feed *}
		{if !empty($sDisplay) && $sDisplay == 'reviews'}
			<h3 class="subtitle"><i class="fa fa-star"></i>&nbsp;{l s='Product ratings data feed' mod='gmerchantcenterpro'}</h3>

			<div class="bt-fb-fly-reviews">
				{* USE CASE - AVAILABLE FEED FILE LIST *}
				<div class="clr_10"></div>
				<div class="alert alert-info form-group">
				{l s='Please follow our FAQ to know' mod='gmerchantcenterpro'}&nbsp;&nbsp;<a class="badge badge-info" target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/156"><i class="icon icon-link"></i>&nbsp;{l s='how to configure your product ratings feed' mod='gmerchantcenterpro'}</a>
				</div>

				<table border="0" cellpadding="2" cellspacing="2" class="table ">
					<tr class="bt_tr_header text-center">
						<th class="center">{l s='Country' mod='gmerchantcenterpro'}</th>
						<th class="center">{l s='URL (copy this URL into your Google Merchant Center interface / planning)' mod='gmerchantcenterpro'}</th>
					</tr>
					{if !empty($aFlyFileListReviews)}
						{foreach from=$aFlyFileListReviews name=feed key=iKey item=aFlyFeed}
							<tr>
								<td class="center">{$aFlyFeed.countryName|escape:'htmlall':'UTF-8'} - {$aFlyFeed.countryIso|escape:'htmlall':'UTF-8'}</td>
								<td class="center"><a target="_blank" href="{$aFlyFeed.link|escape:'htmlall':'UTF-8'}">{$aFlyFeed.langName|escape:'htmlall':'UTF-8'}</a></td>
							</tr>
						{/foreach}
					{else}
						<tr>
							<td>
								<div class="alert alert-warning text-center">
									{l s='No review module compatible with Google merchant center PRO is installed' mod='gmerchantcenterpro'}
								</div>
							</td>
						</tr>
					{/if}
				</table>
				{* USE CASE - THE OUTPUT PHP FILE HASN'T BEEN COPIED *}
			</div>
		{/if}
		{* END - product reviews data feed *}
	</form>
	<div id="{$sModuleName|escape:'htmlall':'UTF-8'}FeedListError"></div>
</div>
{literal}
<script type="text/javascript">
	// fancy box
	$("a#handleGenerateReportingBox").fancybox({
		'hideOnContentClick' : false
	});

	oGmcProFeedList.dynamicDisplay();

	//bootstrap components init
	{/literal}{if !empty($bAjaxMode)}{literal}
	$('.label-tooltip, .help-tooltip').tooltip();
	{/literal}{/if}{literal}
</script>
{/literal}