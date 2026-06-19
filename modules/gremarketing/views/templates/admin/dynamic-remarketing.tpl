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

{if empty($bCompare16)}<div class="clr_20"></div>{/if}

<form action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" class="form-horizontal col-xs-12" id="bt_dynamic-form" name="bt_dynamic-form" onsubmit="oGr.form('bt_dynamic-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_dynamic-settings', 'bt_dynamic-settings', false, false, 'dynamic', 'dynamic');return false;">
	<input type="hidden" name="sAction" value="{$aQueryParams.dynamic.action|escape:'htmlall':'UTF-8'}" />
	<input type="hidden" name="sType" value="{$aQueryParams.dynamic.type|escape:'htmlall':'UTF-8'}" />

	<h3><i class="icon-wrench"></i>&nbsp;{l s='Google Remarketing Dynamic Settings' mod='gremarketing'}</h3>
	
	{if !empty($bUpdate)}
		{include file="`$sConfirmInclude`"}
	{elseif !empty($aErrors)}
		{include file="`$sErrorInclude`"}
	{/if}

	<div class="form-group">
		<div class="col-xs-12 col-md-5 col-lg-12">
			<div class="alert alert-info">
				{l s='This sections lets you activate a cutting-edge feature in Google\'s Remarketing tools, and will allow you to generate automatically dynamic, targeted and animated banners with your products in them, by using your Google Merchant Center product feed. These banners are based on HTML5, not Flash, and are therefore also compatible with all mobile devices including iPhone and iPad.' mod='gremarketing'}
				{l s='This module is of course fully compatible with our Google Merchant Center module, sold separately on' mod='gremarketing'} <b><a href="{l s='http://addons.prestashop.com/en/seo-prestashop-modules/1768-google-merchant-center-the-best-google-shopping-module.html' mod='gremarketing'}" target="_blank">{l s='http://addons.prestashop.com/en/seo-prestashop-modules/1768-google-merchant-center-the-best-google-shopping-module.html' mod='gremarketing'}</a></b>
			</div>
		</div>
	</div>

	<div class="clr_20"></div>

	<div class="form-group" id="bootstrap-bouton">
		<label class="control-label col-xs-12 col-md-5 col-lg-2"><b>{l s='Activate Google Dynamic Remarketing' mod='gremarketing'}</b> :</label>
		<div class="col-xs-12 col-lg-1">
			<div class="fixed-width-md {if empty($bCompare16)} col-lg-10{/if}">
				<span class="switch prestashop-switch fixed-width-md">
					<input type="radio" name="bt_activate-dynamic" id="bt_activate-dynamic_on" value="1" {if !empty($bGoogleDynamic)}checked="checked"{/if} onclick="javascript: oGr.changeSelect('bt_merchant-prefix', 'bt_merchant-prefix', null, null, true, true); oGr.changeSelect('bt_merchant-separator', 'bt_merchant-separator', null, null, true, true);" />
					<label for="bt_activate-dynamic_on" class="radioCheck btn-yes">
						{l s='Yes' mod='gremarketing'}
					</label>
					<input type="radio" name="bt_activate-dynamic" id="bt_activate-dynamic_off" value="0" {if empty($bGoogleDynamic)}checked="checked"{/if} onclick="javascript: oGr.changeSelect('bt_merchant-prefix', 'bt_merchant-prefix', null, null, true, false);oGr.changeSelect('bt_merchant-separator', 'bt_merchant-separator', null, null, true, false);" />
					<label for="bt_activate-dynamic_off" class="radioCheck btn-no">
						{l s='No' mod='gremarketing'}
					</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>

	<div class="clr_20"></div>

	<div class="form-group" id="bt_merchant-prefix" style="display: {if !empty($bGoogleDynamic)}block{else}none{/if}">
		<label class="control-label col-lg-2"><b>{l s='Product ID prefix in Merchant Center feed' mod='gremarketing'}</b> :</label>
		<div class="col-lg-3">
			<input type="text" id ="bt_google-prefix" name="bt_google-prefix" value="{if !empty($sGmcPrefix)}{$sGmcPrefix|escape:'htmlall':'UTF-8'}{/if}" class="fixed-width-md" />
			&nbsp;<a class="badge badge-info pulse pulse2" target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sCurrentIso|escape:'htmlall':'UTF-8'}/faq/224"><span class="icon icon-link"></span>&nbsp;&nbsp;{l s='FAQ' mod='gremarketing'}</a>

            {if !empty($sGmcPrefix)}<div class="clr_20"></div><div class="alert alert-info">{l s='For information, your product ID prefix filled in your Google Merchant Center module is' mod='gremarketing'} :  <b style="font-size: 12pt;">{$sGmcPrefix|escape:'htmlall':'UTF-8'}</b></div>{/if}
		</div>
	</div>

	<div class="form-group" id="bt_merchant-separator" style="display: {if !empty($bGoogleDynamic)}block{else}none{/if}">
		<label class="control-label col-xs-12 col-md-3 col-lg-2"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If you have a Google Shopping Data feed, and if you export each combination you should have <g:id> tag build like product_id + seperator + id_product attribute. This option let you configure the separator according to your data feed' mod='gremarketing'}"><b>{l s='Separator for product id and combination' mod='gremarketing'}</b></span> :</label>
		<div class="col-xs-12 col-lg-1">
			<input type="text" id ="bt_separator" name="bt_separator" value="{if !empty($sSeparator)}{$sSeparator}{/if}" placeholder="v" />
		</div>
		<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If you have a Google Shopping Data feed, and if you export each combination you should have <g:id> tag build like product_id + seperator + id_product attribute. This option let you configure the separator according to your data feed' mod='gremarketing'}">&nbsp;<span class="icon-question-sign"></span></span>
		&nbsp;<a class="badge badge-info pulse pulse2" target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sCurrentIso|escape:'htmlall':'UTF-8'}/faq/393"><span class="icon icon-link"></span>&nbsp;&nbsp;{l s='FAQ' mod='gremarketing'}</a>

		</div>

	<div class="clr_20"></div>

	<div class="center">
		<script type="text/javascript">
			{literal}
			var oCallback =
			[{
				'url' : '{/literal}{$sURI}{literal}',
				'params' : 'sAction=display&sType=check',
				'toShow' : 'bt_check-settings',
				'toHide' : 'bt_check-settings',
				'bFancybox' : false,
				'bFancyboxActivity' : false,
				'sLoadbar' : null,
				'sScrollTo' : null,
				'oCallBack' : {}
			}];
			{/literal}
		</script>

		<div class="clr_10"></div>
		<div class="clr_hr"></div>
		<div class="clr_10"></div>

		<div class="center">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
					<div class="adminErrors" id="bt_error-dynamic"></div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
					<button  class="btn btn-info pull-right" onclick="oGr.form('bt_dynamic-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_dynamic-settings', 'bt_dynamic-settings', false, false, oCallback, 'dynamic', 'dynamic');return false;"><i class="process-icon-save"></i>{l s='Save' mod='gremarketing'}</button>
				</div>
			</div>
		</div>

	</div>
</form>


{literal}
<script type="text/javascript">
	//bootstrap components init
	{/literal}{if !empty($bAjaxMode)}{literal}
		$('.label-tooltip, .help-tooltip').tooltip();
	{/literal}{/if}{literal}
</script>
{/literal}