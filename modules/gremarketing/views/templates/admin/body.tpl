{*
*
* Google Dynamic Remarketing
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

<div id="{$sModuleName|escape:'htmlall':'UTF-8'}" class="bootstrap form">
	{* HEADER *}
	{include file="`$sHeaderInclude`"  bContentToDisplay=true}
	{* /HEADER *}

	<div class="clr_20"></div>

	<div>
		<img class="bt-effect" src="{$smarty.const._GR_URL_IMG|escape:'htmlall':'UTF-8'}admin/gremarketing.png" width="350" height="60" alt="Google Remarketing" />
	</div>

	<div class="clr_10"></div>

	{* USE CASE - module update not ok  *}
	{if !empty($aUpdateErrors)}
	<div class="alert alert-error">

	</div>
	{* USE CASE - display configuration ok *}
	{else}
		<script type="text/javascript">
			var id_language = Number({$iCurrentLang|intval});
		</script>

	<div class="row">
		<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
			<div class="list-group workTabs">
				<a class="list-group-item active" id="tab-1"><span class="icon-ok-sign"></span>&nbsp;&nbsp;{l s='Prerequisites check' mod='gremarketing'}</a>
				<a class="list-group-item" id="tab-2"><span class="icon-heart"></span>&nbsp;&nbsp;{l s='Basics' mod='gremarketing'}</a>
				<a class="list-group-item" id="tab-3"><span class="icon-wrench"></span>&nbsp;&nbsp;{l s='Dynamic' mod='gremarketing'}</a>
			</div>

			{* more tools *}
			<div class="list-group">
				<a class="list-group-item list-group-item-success"" target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/product/63"><span class="icon-info-circle"></span>&nbsp;&nbsp;{l s='Online FAQ' mod='gremarketing'}</a>
				<a type="button" class="list-group-item" data-toggle="modal" data-target="#myHelpModale"><span class="icon-user"></span>&nbsp;&nbsp;{l s='Contact support' mod='gremarketing'}</a>
			</div>

			{* rate *}
			<div class="list-group">
				<a class="list-group-item" target="_blank" href="{$sRateUrl|escape:'htmlall':'UTF-8'}"><i class="icon-star" style="color: #fbbb22;"></i>&nbsp;&nbsp;{l s='Rate me' mod='gremarketing'}</a>
			</div>

			{* module version *}
			<div class="list-group"">
			<a class="list-group-item" href="#"><span class="icon icon-info"></span>&nbsp;&nbsp;{l s='Version' mod='gremarketing'} : {$sModuleVersion}</a>
		</div>


		<!-- Modal -->
		<div id="myHelpModale" class="modal fade" role="dialog">
			<div class="modal-dialog">

				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h2 class="modal-title text-center"><i class="icon icon-user"></i>&nbsp; {l s='Contact the technical support' mod='gremarketing'}</h2>
					</div>
					<div class="modal-body">
						<p class="alert alert-info">{l s='Before contacting technical support, please make sure you have read all the module FAQ\'s by clicking on this link. The answer to your question could be inside!' mod='gremarketing'}</p>
						<div	 class="col-xs-12 center">
							<div class="clr_10"></div>
							<a class="btn btn-info btn-lg" target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sCurrentIso|escape:'htmlall':'UTF-8'}/product/63"><span class="fa fa-question-circle"></span>&nbsp;&nbsp{l s='Read FAQ\'s' mod='gremarketing'}</a>
							-
							<a class="btn btn-warning btn-lg" target="_blank" href="{$sContactUs|escape:'htmlall':'UTF-8'}"><span class="icon-user"></span>&nbsp;&nbsp;{l s='My answer isn\'t in FAQ\'s (contact us)' mod='gremarketing'}</a>
						</div>
					</div>
					<div class="clr_10"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-info" data-dismiss="modal">{l s='Close' mod='gremarketing'}</button>
					</div>
				</div>

			</div>
		</div>

	</div>
	<div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
		<div class="tab-content">

			{* TECHNICAL CHECK SETTINGS *}
			<div id="content-tab-1" class="tab-pane panel information active">
				<div id="bt_check-settings">
					{include file="`$sCheckInclude`"}
				</div>
			</div>
			{* /TECHNICAL CHECK SETTINGS *}

			{* BASIC SETTINGS *}
			<div id="content-tab-2" class="tab-pane panel">
				<div id="bt_basics-settings">
					{include file="`$sBasicInclude`"}
				</div>
				<div class="clr_20"></div>
				<div id="bt_loading-div-basics" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sBigLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gremarketing'}</p>
					</div>
				</div>
			</div>
			{* /BASIC SETTINGS *}

			{* DYNAMIC SETTINGS *}
			<div id="content-tab-3" class="tab-pane panel">
				<div id="bt_dynamic-settings">
					{include file="`$sDynamicInclude`"}
				</div>
				<div class="clr_20"></div>
				<div id="bt_loading-div-dynamic" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sBigLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gremarketing'}</p>
					</div>
				</div>
			</div>
			{* /DYNAMIC SETTINGS *}
		</div>

		<div class="footer">
			<div class="row">
				<div class="col-xs-12">
					<div class="col-xs-6">
						<ul class="unstyled">
							<li class="footer_title"><i class="fa fa-cog"></i>&nbsp; {l s='Configuration' mod='gremarketing'}<li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/167">{l s='Where can i find my conversion ID ?' mod='gremarketing'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/81">{l s='Why I do not see the tag on Adwords ?' mod='gremarketing'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/103">{l s='How do I create my Dynamic Remarketing campaign ?' mod='gremarketing'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/226">{l s='Do I have to include the user_id tag ?' mod='gremarketing'}</a></li>
						</ul>
					</div>

					<div class="col-xs-6">
						<ul class="unstyled">
							<li class="footer_title"><i class="fa fa-file"></i>&nbsp; {l s='Tag assistant' mod='gremarketing'}<li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/168">{l s='How to test my remarketing tag ?' mod='gremarketing'}</a></li>
							<li class="footer_link"><a target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/78">{l s='What do Google Tag Assistant "suggestions" mean? ' mod='gremarketing'}</a></li>
						</ul>
					</div>
				</div>

				<div class="clr_10"></div>
				<div class="clr_hr"></div>
				<div class="clr_10"></div>

				<div class="row">
					<div class="col-xs-12">
						<a href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}product.php?pid=13&lg={$sCurrentIso|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-lg btn-info pulse pulse_infnite"><i class="fa fa-link"></i>&nbsp; {l s='More FAQ\'s' mod='gremarketing'}</a>
					</div>
				</div>

				<div class="clr_10"></div>

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
			});
		</script>
		{/literal}
	{/if}
</div>