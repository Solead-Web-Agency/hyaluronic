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
<div id='{$sModuleName|escape:'htmlall':'UTF-8'}' class="bootstrap form">
	{* HEADER *}
	{include file="`$sHeaderInclude`"  bContentToDisplay=true}
	{* /HEADER *}
	{include file="`$sTopBar`"}

	{* USE CASE - module update not ok  *}
	{if !empty($aUpdateErrors)}
		{include file="`$sErrorInclude`" aErrors=$aUpdateErrors bDebug=true}
	{* USE CASE - display configuration ok *}
	{else}
		{literal}
		<script type="text/javascript">
			var id_language = Number({/literal}{$iCurrentLang|intval}{literal});

			{/literal}
			{* USE CASE - use the new language flags system from PS 1.6 *}
			{if empty($bCompare16)}
			{literal}
			function hideOtherLanguage(id) {
				$('.translatable-field').hide();
				$('.lang-' + id).show();

				var id_old_language = id_language;
				id_language = id;
			}
			{/literal}
			{/if}
			{literal}
		</script>
		{/literal}

	<div class="clr_20"></div>
	
	
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
			{*START LEFT MENU*}
			<div id="mainMenu" class="list-group workTabs">
				<a class="list-group-item pointer active" id="tab-2"><span class="icon-heart"></span>&nbsp;&nbsp;{l s='Basic settings' mod='gmerchantcenterpro'}</a>
				{*start colapse*}
				<a class="list-group-item" id="tab-001" data-toggle="collapse" href="#collapseOne"><span class="icon-cog"></span>&nbsp;&nbsp;{l s='Product feed management' mod='gmerchantcenterpro'}<span class="pull-right"><i class="icon-caret-down"></i></span> </a>
				<div id="collapseOne" class="panel-collapse collapse">
					<a class="list-group-item" id="tab-001"><i class="submenu fa fa-check-square"></i>&nbsp;{l s='Export method' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-003" href="#feed-management-dropdown3"><i class="submenu fa fa-feed"></i>&nbsp;{l s='Feed data options' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-002" href="#feed-management-dropdown2"><i class="submenu fa fa-ban"></i>&nbsp;{l s='Product exclusion rules' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-004" href="#feed-management-dropdown4"><i class="submenu fa fa-bookmark"></i>&nbsp;{l s='Apparel feed options' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-005" href="#feed-management-dropdown6"><i class="submenu fa fa-cogs"></i>&nbsp;{l s='Advanced feed options' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-006" href="#feed-management-dropdown4"><i class="submenu fa fa-truck"></i>&nbsp;{l s='Taxes and shipping fees' mod='gmerchantcenterpro'}</a>
				</div>
				<a class="list-group-item" id="tab-010" data-toggle="collapse" href="#collapseTwo"><span class="icon-tag"></span>&nbsp;&nbsp;{l s='Special offers / inventory / product ratings feeds' mod='gmerchantcenterpro'}<span class="pull-right"><i class="icon-caret-down"></i></span> </a>
				<div id="collapseTwo" class="panel-collapse collapse">
					<a class="list-group-item" id="tab-010" href="#gmcp-management-dropdown1"><i class="submenu fa fa-cogs"></i>&nbsp;{l s='Special offers data feed configuration' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-011" href="#gmcp-management-dropdown2"><i class="submenu fa fa-shopping-cart"></i>&nbsp;{l s='Local inventory data feed configuration' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-012" href="#gmcp-management-dropdown3"><i class="submenu fa fa-star"></i>&nbsp;{l s='Product ratings data feed configuration' mod='gmerchantcenterpro'}</a>
				</div>
				<a class="list-group-item" id="tab-020" data-toggle="collapse" href="#collapseThree"><span class="fa fa-google"></span>&nbsp;&nbsp;{l s='Google management' mod='gmerchantcenterpro'}<span class="pull-right"><i class="icon-caret-down"></i></span> </a>
				<div id="collapseThree" class="panel-collapse collapse">
					<a class="list-group-item" id="tab-020" href="#gmcp-management-dropdown1"><i class="submenu fa fa-copy"></i>&nbsp;{l s='Matching with Google Categories' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-021" href="#gmcp-management-dropdown2"><i class="submenu fa fa-file-code-o"></i>&nbsp;{l s='Google Analytics integration' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-022" href="#gmcp-management-dropdown3"><i class="submenu fa fa-bookmark-o"></i>&nbsp;{l s='Custom labels integration' mod='gmerchantcenterpro'}</a>
				</div>
				<a class="list-group-item" id="tab-030" data-toggle="collapse" href="#collapseFour"><span class="icon-align-justify"></span>&nbsp;&nbsp;{l s='My feeds' mod='gmerchantcenterpro'}<span class="pull-right"><i class="icon-caret-down"></i></span> </a>
				<div id="collapseFour" class="panel-collapse collapse">
					<a class="list-group-item" id="tab-030" href="#gmcp-management-dropdown1"><i class="submenu fa fa-book"></i>&nbsp;{l s='Products data feed' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-031" href="#gmcp-management-dropdown2"><i class="submenu fa fa-bookmark-o"></i>&nbsp;{l s='Special offers data feed' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-033" href="#gmcp-management-dropdown3"><i class="submenu fa fa-star"></i>&nbsp;{l s='Product ratings data feed' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" id="tab-034" href="#gmcp-management-dropdown4"><i class="submenu fa fa-shopping-cart"></i>&nbsp;{l s='Local product inventory data feed' mod='gmerchantcenterpro'}</a>
				</div>
				
				<a class="list-group-item pointer" id="tab-4"><span class="icon-play"></span>&nbsp;&nbsp;{l s='Reporting' mod='gmerchantcenterpro'}</a>
			</div>

			{* Doc & FAQ links*}
			<div class="list-group">
				<a class="list-group-item list-group-item-success" target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/product/68"><span class="icon-info-circle"></span>&nbsp;&nbsp;{l s='Online FAQ' mod='gmerchantcenterpro'}</a>
				<a type="button" class="list-group-item" data-toggle="modal" data-target="#myHelpModale"><span class="icon-user"></span>&nbsp;&nbsp;{l s='Contact support' mod='gmerchantcenterpro'}</a>
			</div>

			<!-- Modal -->
			<div id="myHelpModale" class="modal fade" role="dialog">
				<div class="modal-dialog">

					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h2 class="modal-title text-center"><i class="icon icon-user"></i>&nbsp; {l s='Contact the technical support' mod='gmerchantcenterpro'}</h2>
						</div>
						<div class="modal-body">
							<p class="alert alert-info">{l s='Before contacting technical support, please make sure you have read all the module FAQ\'s by clicking on this link. The answer to your question could be inside!' mod='gmerchantcenterpro'}</p>
							<div	 class="col-xs-12 center">
								<div class="clr_10"></div>
								<a class="btn btn-info btn-lg" target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/product/68"><span class="fa fa-question-circle"></span>&nbsp;&nbsp{l s='Read FAQ\'s' mod='gmerchantcenterpro'}</a>
								-
								<a class="btn btn-warning btn-lg" target="_blank" href="{$sContactUs|escape:'htmlall':'UTF-8'}"><span class="icon-user"></span>&nbsp;&nbsp;{l s='My answer isn\'t in FAQ\'s (contact us)' mod='gmerchantcenterpro'}</a>
							</div>
						</div>
						<div class="clr_10"></div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='gmerchantcenterpro'}</button>
						</div>
					</div>

				</div>
			</div>

			{* Google shopping links *}
			<div class="list-group">
				<a style="color: #72C279;" class="list-group-item" id="tab-010" data-toggle="collapse" href="#collapseFive"><span class="fa fa-link" style="color: #72C279;"></span>&nbsp;&nbsp;{l s='Your Google accounts links & Official documentation' mod='gmerchantcenterpro'}<span class="pull-right"><i class="icon-caret-down"></i></span> </a>
				<div id="collapseFive" class="panel-collapse collapse">
					<a class="list-group-item" target="_blank" href="https://merchants.google.com"><span class="icon-shopping-cart"></span>&nbsp;&nbsp;{l s='Google Shopping account' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" target="_blank" href="https://adwords.google.com"><span class="icon-briefcase"></span>&nbsp;&nbsp;{l s='Google AdWords account' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" target="_blank" href="https://support.google.com/merchants/topic/7257844?visit_id=1-636189593736100500-4122484685&hl={$sCurrentIso|escape:'htmlall':'UTF-8'}&rd=1"><span class="icon-info"></span>&nbsp;&nbsp;{l s='Best practices guide' mod='gmerchantcenterpro'}</a>
					
					<a class="list-group-item" target="_blank" href="https://support.google.com/merchants/answer/2660968?hl={$sCurrentIso|escape:'htmlall':'UTF-8'}&ref_topic=2660962&visit_id=1-636189593736100500-4122484685&rd=1"><span class="icon-link"></span>&nbsp;&nbsp;{l s='Getting started with Shopping campaigns' mod='gmerchantcenterpro'}</a>
					<a class="list-group-item" target="_blank" href="https://support.google.com/merchants/topic/7286989?hl={$sCurrentIso|escape:'htmlall':'UTF-8'}&ref_topic=7259123"><span class="icon-check"></span>&nbsp;&nbsp;{l s='Google Shopping policies' mod='gmerchantcenterpro'}</a>
				</div>

			</div>

			{* rate me *}
			<div class="list-group">
				<a class="list-group-item" target="_blank" href="{$sRateUrl|escape:'htmlall':'UTF-8'}"><i class="icon-star" style="color: #fbbb22;"></i>&nbsp;&nbsp;{l s='Rate me' mod='gmerchantcenterpro'}</a>
			</div>

			{* module version *}
			<div class="list-group">
				<a class="list-group-item" href="#"><span class="icon icon-info"></span>&nbsp;&nbsp;{l s='Version' mod='gmerchantcenterpro'} : {$sModuleVersion|escape:'htmlall':'UTF-8'}</a>
			</div>

	</div>
	{* END LEFT MENU *}
	
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
		{if empty($bHideConfiguration)}
			{*STAR TAB CONTENT*}
			<div class="tab-content">
			
				{* BASICS SETTINGS *}
				<div id="content-tab-2" class="tab-pane panel active">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_basics-settings">
							{include file="`$sBasicsInclude`"}
						</div>
						<div class="clr_20"></div>
						<div id="loadingBasicsDiv" style="display: none;">
							<div class="alert alert-info">
								<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
								<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
							</div>
						</div>
					{/if}
				</div>
				{* /BASICS SETTINGS *}

				{* FEED MANAGEMENT SETTINGS *}
				<div id="content-tab-001" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-settings-export">
							{include file="`$sFeedInclude`" sDisplay="export"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-002" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-settings-exclusion">
							{include file="`$sFeedInclude`" sDisplay="exclusion"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-003" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-settings-data">
							{include file="`$sFeedInclude`" sDisplay="data"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-004" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-settings-apparel">
							{include file="`$sFeedInclude`" sDisplay="apparel"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-005" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-settings-advanced">
							{include file="`$sFeedInclude`" sDisplay="advanced"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-006" class="tab-pane panel">
					{if !empty($bMultiShop)}
						<div class="alert alert-danger">
							{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
						</div>
					{else}
						<div id="bt_feed-settings-tax">
							{include file="`$sFeedInclude`" sDisplay="tax"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="loadingFeedDiv" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
					</div>
				</div>

				{literal}
					<script type="text/javascript">
						// run main feed JS
						oGmcPro.runMainFeed();
					</script>
				{/literal}
				{*/FEED MANAGEMENT SETTINGS*}

				{* ADVANCED FEED MANAGEMENT SETTINGS *}
				<div id="content-tab-010" class="tab-pane panel">
					<div id="bt_advanced-settings-promo">
						{include file="`$sAdvanceFeed`" sDisplay="promo"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="content-tab-011" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_local_inventory_div">
							{include file="`$sLocalInventoryFeed`"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-012" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_advanced-settings-reviews">
							{include file="`$sAdvanceFeed`" sDisplay="reviews"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="loadingAdvancedDiv" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
					</div>
				</div>
				{* /ADVANCED FEED MANAGEMENT SETTINGS *}

				{* GOOGLE MANAGEMENT SETTINGS *}
				<div id="content-tab-020" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_google-settings-categories">
							{include file="`$sGoogleInclude`" sDisplay="categories"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-021" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
					<div id="bt_google-settings-analytics">
							{include file="`$sGoogleInclude`" sDisplay="analytics"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-022" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_google-settings-adwords">
							{include file="`$sGoogleInclude`" sDisplay="adwords"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="loadingGoogleDiv" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
					</div>
				</div>

				{literal}
					<script type="text/javascript">
						// run main Google JS
						oGmcPro.runMainGoogle();
					</script>
				{/literal}
				{* /GOOGLE MANAGEMENT SETTINGS *}

				{* MY FEEDS SETTINGS *}
				<div id="content-tab-030" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-list-settings-data">
							{include file="`$sFeedListInclude`" sDisplay="data"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-031" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-list-settings-promo">
							{include file="`$sFeedListInclude`" sDisplay="promo"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-032" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-list-settings-stock">
							{include file="`$sFeedListInclude`" sDisplay="stock"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-033" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_feed-list-settings-reviews">
							{include file="`$sFeedListInclude`" sDisplay="reviews"}
						</div>
						<div class="clr_20"></div>
					{/if}
				</div>

				<div id="content-tab-034" class="tab-pane panel">
					<div id="bt_feed-list-lia">
						{include file="`$sFeedListLiaInclude`"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="loadingFeedLiaDiv" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
					</div>
				</div>

				<div id="loadingFeedListDiv" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
					</div>
				</div>
				{* /MY FEEDS SETTINGS *}

				{* REPORTING SETTINGS *}
				<div id="content-tab-4" class="tab-pane panel">
					{if !empty($bMultiShop)}
					<div class="alert alert-danger">
						{l s='First of all, you cannot configure your module in the "all shops" or "shops group" mode. Please select one of your shops before moving on into the configuration.' mod='gmerchantcenterpro'}
					</div>
					{else}
						<div id="bt_reporting-settings">
							{include file="`$sReportingInclude`"}
						</div>
						<div class="clr_20"></div>
						<div id="loadingReportingDiv" style="display: none;">
							<div class="alert alert-info">
								<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
								<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
							</div>
						</div>
					{/if}
				</div>
				{* /REPORTING SETTINGS *}

				{else}
				<div class="clr_20"></div>

				{if !empty($bCurlAndContentStopExec)}
					<div class="alert alert-danger">
						{l s='You need to have : either the file_get_contents() with the allow_url_fopen directive enabled in the php.ini file, or the PHP CURL extension enabled, in order to retrieve the Google category definition files from Google\'s website. Please contact your web host. If neither of these options are available to you on your server (but at least one should be in most cases), you will not be able to use this module.' mod='gmerchantcenterpro'}.
					</div>
				{/if}

				{if !empty($bMultishopGroupStopExec)}
					<div class="alert alert-danger">
						{l s='For performance reasons, this module cannot be configured within a shops group context. You must configure it one shop at a time.' mod='gmerchantcenterpro'}.
					</div>
				{/if}
			{/if}
		</div>

		{*Footer *}
		<div class="footer">
			<div class="row">
				<div class="col-xs-12">
					<div class="col-xs-4">
						<ul class="unstyled">
							<li class="footer_title"><i class="fa fa-cog"></i>&nbsp; {l s='Configuration' mod='gmerchantcenterpro'}<li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/101">{l s='How to configure my module ?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/94">{l s='How to import my feeds in Google Shopping?' mod='gmerchantcenterpro'}</a></li>
						</ul>
					</div>

					<div class="col-xs-4">
						<ul class="unstyled">
							<li class="footer_title"><i class="fa fa-file"></i>&nbsp; {l s='Feed' mod='gmerchantcenterpro'}<li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/30">{l s='How to automatically update my feeds?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/22">{l s='Why have some of my products not been exported in the feed?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/27">{l s='What to do if my CRON doesn\'t work?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/51">{l s='Why do I have shipping fees or carrier problems?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/100">{l s='Why does my feed generate errors?' mod='gmerchantcenterpro'}</a></li>
						</ul>
					</div>

					<div class="col-xs-4">
						<ul class="unstyled">
							<li class="footer_title"><i class="fa fa-book"></i>&nbsp; {l s='Advanced' mod='gmerchantcenterpro'}<li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/111">{l s='How to create advanced custom labels ?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/110">{l s='How to configure my special offer feed?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/156">{l s='How to configure my product review feed?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/175">{l s='How to create advanced exclusion rules?' mod='gmerchantcenterpro'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/178">{l s='How to manage the exclusion of certain reviews?' mod='gmerchantcenterpro'}</a></li>
						</ul>
					</div>
				</div>

				<div class="clr_10"></div>
				<div class="clr_hr"></div>
				<div class="clr_10"></div>

				<div class="row">
					<div class="col-xs-12">
						<a href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/product/68" target="_blank" class="btn btn-lg btn-info pulse pulse_infnite"><i class="fa fa-link"></i>&nbsp; {l s='More FAQ\'s' mod='gmerchantcenterpro'}</a>
					</div>
				</div>

				<div class="clr_10"></div>

			</div>
		</div>
	</div>
	</div>


{literal}
	<script type="text/javascript">

		$(document).ready(function() {
			$('#content').removeClass('nobootstrap');
			$('#content').addClass('bootstrap');
			$(".workTabs a").click(function(e) {
				e.preventDefault();
				// currentId is the current workTabs id
				var currentId = $(".workTabs a.active").attr('id').substr(4);
				// id is the wanted workTabs id
				var id = $(this).attr('id').substr(4);

				if ($(this).attr("id") != $(".workTabs a.active").attr('id')) {
					$(".workTabs a[id='tab-"+currentId+"']").removeClass('active');
					$("#content-tab-"+currentId).hide();
					$(".workTabs a[id='tab-"+id+"']").addClass('active');
					$("#content-tab-"+id).show();
				}
			});
			$(".workTabs a.active").click();

			$('.label-tooltip, .help-tooltip').tooltip();
			$('.dropdown-toggle').dropdown();
			{/literal}{if !empty($bDisplayAdvice)}{literal}
			$("a#bt_disp-advice").fancybox({
				'hideOnContentClick' : false
			});
			$('#bt_disp-advice').trigger('click');
			{/literal}{/if}{literal}

			// Use case for GSA menu click
			$("#tab-040").click(function(event){
				$(this).removeClass('new-bg');
			});

			$("#mainMenu").click(function(event){
				$('#tab-040').addClass('new-bg');
			});

			// Handle the click on gsa tab during gsa process
			bGsaTab = oGmcPro.getUrlParam('tab', 'empty');
			if (bGsaTab != 'empty' && bGsaTab == 'gsa') {
				$( "#tab-040").trigger( "click" );
			}
		});
	</script>
{/literal}
	{/if}
