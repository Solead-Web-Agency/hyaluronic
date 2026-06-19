{*
* 2003-2018 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2018 Business Tech SARL
*}

<div id="{$sModuleName|escape:'htmlall':'UTF-8'}" class="bootstrap">
	{* HEADER *}
	{include file="`$sHeaderInclude`"  bContentToDisplay=true}
	{* /HEADER *}

	<div class="clr_20"></div>

	<div>
		<img  class="bt-effect" src="{$smarty.const._GACT_URL_IMG|escape:'htmlall':'UTF-8'}admin/gadwordstracking.png" width="350" height="60" alt="Google Adwords Conversion Tracking" />
		<div class="clr_20"></div>
	</div>

	{* USE CASE - module update not ok  *}
	{if !empty($aUpdateErrors)}
		<div class="alert alert-error"></div>
	{* USE CASE - display configuration ok *}
	{else}
		<script>
			var id_language = Number({$iCurrentLang|intval});
		</script>

		<div class="row">
			<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
				{* START LEFT MENU *}
				<div class="list-group workTabs">
					<a class="list-group-item active" id="tab-0"><span class="icon-home"></span>&nbsp;&nbsp;{l s='Welcome' mod='gadwordstracking'}</a>
					<a class="list-group-item" id="tab-2"><span class="icon-heart"></span>&nbsp;&nbsp;{l s='Basic' mod='gadwordstracking'}</a>
					<a class="list-group-item" id="tab-3"><span class="icon-play"></span>&nbsp;&nbsp;{l s='Advanced' mod='gadwordstracking'}</a>
				</div>

				{* more tools *}
				<div class="list-group">
					<a class="list-group-item documentation" target="_blank" href="{$sDocUri|escape:'htmlall':'UTF-8'}{$sDocName|escape:'htmlall':'UTF-8'}"><span class="icon-file"></span>&nbsp;&nbsp;{l s='Documentation' mod='gadwordstracking'}</a>
					<a class="list-group-item" target="_blank" href="{$smarty.const._GACT_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/product/66"><span class="icon-info-circle"></span>&nbsp;&nbsp;{l s='Online FAQ' mod='gadwordstracking'}</a>
					<a class="list-group-item" target="_blank" href="{$sContactUs|escape:'htmlall':'UTF-8'}"><span class="icon-user"></span>&nbsp;&nbsp;{l s='Contact support' mod='gadwordstracking'}</a>
					<a class="list-group-item" target="_blank" href="https://adwords.google.com/"><span class="icon-google"></span>&nbsp;&nbsp;{l s='Google Adwords account' mod='gadwordstracking'}</a>
				</div>

				{* rate *}
				<div class="list-group">
					<a class="list-group-item" target="_blank" href="{$sRateUrl|escape:'htmlall':'UTF-8'}"><i class="icon-star" style="color: #fbbb22;"></i>&nbsp;&nbsp;{l s='Rate me' mod='gadwordstracking'}</a>
				</div>

				{* module version *}
				<div class="list-group"">
				<a class="list-group-item" href="#"><span class="icon icon-info"></span>&nbsp;&nbsp;{l s='Version' mod='gadwordstracking'} : {$sModuleVersion}</a>
			</div>
		</div>
		{*END LEFT MENU*}
		<div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
			{*STAR TAB CONTENT*}
			<div class="tab-content">
				{* HOME *}
				<div id="content-tab-0" class="tab-pane panel in active information">
					<h3><i class="icon-home"></i>&nbsp;{l s='Welcome' mod='gadwordstracking'}</h3>
					<div class="clr_20"></div>

					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<a target="blank" href="{$sCrossSellingUrl}"><img class="bt-effect img-responsive" src="{$sCrossSellingImg}"/></a>
						</div>
					</div>

				</div>
				{* /HOME *}

				{* BASIC SETTINGS *}
				<div id="content-tab-2" class="tab-pane panel">
					<div id="bt_basics-settings">
						{include file="`$sBasicInclude`"}
					</div>
					<div class="clr_20"></div>
					<div id="bt_loading-div-basics" style="display: none;">
						<div class="alert alert-info">
							<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
							<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gadwordstracking'}</p>
						</div>
					</div>
				</div>
				{* /BASIC SETTINGS *}

				{* ADVANCED SETTINGS *}
				<div id="content-tab-3" class="tab-pane panel">
					<div id="bt_advanced-settings">
						{include file="`$sAdvancedInclude`"}
					</div>
					<div class="clr_20"></div>
					<div id="bt_loading-div-advanced" style="display: none;">
						<div class="alert alert-info">
							<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
							<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gadwordstracking'}</p>
						</div>
					</div>
				</div>
				{* /ADVANCED SETTINGS *}
			</div>
		</div>

		{literal}
		<script type="text/javascript">
			$('#workTabs a').click(function (e) {
				e.preventDefault()
				$(this).tab('show')
			});

			$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
				e.target // activated tab
				e.relatedTarget // previous tab
			});

			var sHash = $(location).attr('hash');
			if (sHash != null) {
				$('#workTabs a[href="' + sHash + '"]').tab('show');
			}

			{/literal}{if empty($bCompare16)}{literal}

			$(document).ready(function() {
				$('.label-tooltip, .help-tooltip').tooltip();
				$('.dropdown-toggle').dropdown();

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
			});
			{/literal}{/if}{literal}
		</script>
		{/literal}
	{/if}
</div>