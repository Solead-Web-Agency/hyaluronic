{*
* 2003-2018 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2018 Business Tech SARL
*}
<form action="{$sURI|escape:'htmlall':'UTF-8'}" class="form-horizontal col-xs-12" method="post" id="bt_basics-form" name="bt_basics-form" onsubmit="oGact.form('bt_basics-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_basics-settings', 'bt_basics-settings', false, false, '', 'basics', 'basics');return false;">
	<input type="hidden" name="sAction" value="{$aQueryParams.basic.action|escape:'htmlall':'UTF-8'}" />
	<input type="hidden" name="sType" value="{$aQueryParams.basic.type|escape:'htmlall':'UTF-8'}" />

	<h3><i class="icon-home"></i>&nbsp;{l s='Google Adwords Conversion Tracking Settings' mod='gadwordstracking'}</h3>

	{if !empty($bUpdate)}
		{include file="`$sConfirmInclude`"}
	{elseif !empty($aErrors)}
		{include file="`$sErrorInclude`"}
	{/if}

	<div class="form-group ">
		<label class="control-label col-xs-12 col-md-2 col-lg-3">
			<span class="label-tooltip" title="{l s='You will find this in the e-mail with the JavaScript code sent by Google or directly into Google Adwords Interface. Please refer to the documentation for details (visit the Help / FAQ tab).' mod='gadwordstracking'}"><b>{l s='Add your Google Conversion Tracking ID' mod='gadwordstracking'}</b></span> :
		</label>
		<div class="col-xs-12 col-md-2 col-lg-2">
			<input type="text" id="bt_conversion-id" placeholder="AW-123456" name="bt_conversion-id" value="{if !empty($iConversionId)}{$iConversionId}{/if}" />
		</div>
		<span class="icon-question-sign" title="{l s='You will find this in the e-mail with the JavaScript code sent by Google or directly into Google Adwords Interface. Please refer to the documentation for details (visit the Help / FAQ tab).' mod='gadwordstracking'}"></span>
		<a class="badge badge-info" href="{$smarty.const._GACT_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/166" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='how to get my Conversion Tracking ID ?' mod='gadwordstracking'}</a>
	</div>

	<div class="clr_20"></div>

	<div class="form-group ">
		<label class="control-label col-xs-12 col-md-2 col-lg-3">
			<span class="label-tooltip" title="{l s='You will find this in the e-mail with the JavaScript code sent by Google or directly into the Google Adwords Interface. Please refer to the documentation for details (visit the Help / FAQ tab).' mod='gadwordstracking'}"><b>{l s='Add your Google Conversion Tracking Label' mod='gadwordstracking'}</b></span> :
		</label>
		<div class="col-xs-12 col-md-2 col-lg-2">
			<input type="text" id="bt_conversion-label" name="bt_conversion-label" value="{if !empty($sConversionLabel)}{$sConversionLabel|escape:'htmlall':'UTF-8'}{/if}" />
		</div>
		<span class="icon-question-sign" title="{l s='You will find this in the e-mail with the JavaScript code sent by Google or directly into the Google Adwords Interface. Please refer to the documentation for details (visit the Help / FAQ tab).' mod='gadwordstracking'}"></span>
		<a class="badge badge-info" href="{$smarty.const._GACT_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/166" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='how to get my conversion Tracking label ?' mod='gadwordstracking'}</a>
	</div>
	
	<div class="clr_20"></div>

	<h3>{l s='How to test your code?' mod='gadwordstracking'}</h3>

	<div class="form-group">
		<div class="col-xs-12 col-md-5 col-lg-6">
			<div class="alert alert-warning">
				<p>{l s='Just click here:' mod='gadwordstracking'} <a href="{$smarty.const._GACT_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/149"" target="_blank"><b>http://faq.businesstech.fr/faq.php</b></a></p>
			</div>
		</div>
	</div>

	<div class="clr_10"></div>
	<div class="clr_hr"></div>
	<div class="clr_10"></div>

	<div class="center">
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
				<div id="bt_error-basics"></div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
				<button  class="btn btn-default pull-right" onclick="oGact.form('bt_basics-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_basics-settings', 'bt_basics-settings', false, false, '', 'basics', 'basics');return false;"><i class="process-icon-save"></i>{l s='Save' mod='gadwordstracking'}</button>
			</div>
		</div>
	</div>

</form>
<script type="text/javascript">
	//bootstrap components init
	{if !empty($bAjaxMode)}
	$('.label-tooltip, .icon-question-sign').tooltip();
	$('.dropdown-toggle').dropdown();
	{/if}
</script>