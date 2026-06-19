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
<script type="text/javascript">
	{literal}
	var oCustomCallBack = [{
		'name' : 'displayGoogleList',
		'url' : '{/literal}{$sURI}{literal}',
		'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction=display&sType={/literal}{$aQueryParams.feedDisplay.type|escape:'htmlall':'UTF-8'}{literal}&sDisplay=exclusion',
		'toShow' : 'bt_feed-exclusion-form',
		'toHide' : 'bt_feed-exclusion-form',
		'bFancybox' : false,
		'bFancyboxActivity' : false,
		'sLoadbar' : null,
		'sScrollTo' : null,
		'oCallBack' : {}
	}];
	{/literal}
</script>
<div id="{$sModuleName|escape:'htmlall':'UTF-8'}" class="bootstrap"style="width: 960px !important; height:800px !important;">
	<form class="form-horizontal" method="post" id="bt_form-exclusion-rules" name="bt_form-exclusion-rules" {if $smarty.const._GSR_USE_JS == true}onsubmit="oGmcPro.form('bt_form-exclusion-rules', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_feed-exclusion-form', 'bt_feed-exclusion-form', false, true, oCustomCallBack, 'ExclusionRules', 'loadingCustomTagDiv');return false;"{/if}>
		<input type="hidden" name="{$sCtrlParamName|escape:'htmlall':'UTF-8'}" value="{$sController|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sAction" value="{$aQueryParams.exclusionRuleForm.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.exclusionRuleForm.type|escape:'htmlall':'UTF-8'}" />

		{*Use case for the update*}
		{if !empty($iRuleId)}
			<input type="hidden" name="bt-exclusion-id" value="{$iRuleId|intval}" />
		{/if}

		<h2 class="text-center">
            {if !empty($iRuleId)}
				<i class="fa fa-edit"></i>
				{l s='Exclusion rule modification' mod='gmerchantcenterpro'}
			{else}
				<i class="fa fa-plus-circle"></i>
       			{l s='New exclusion rule creation' mod='gmerchantcenterpro'}</h2>
			{/if}
		</h2>

		<div class="clr_10"></div>
		<div class="clr_hr"></div>
		<div class="clr_20"></div>

		<p class="alert alert-info">{l s='If you don\'t know how to use this tool please follow our FAQ:' mod='gmerchantcenterpro'}
			&nbsp;&nbsp;<a class="badge badge-info pulse pulse2" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/175" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='How to create advanced exclusion rules?' mod='gmerchantcenterpro'}</a>
		</p>
		<div class="form-group">
			<label class="control-label col-xs-12 col-md-3 col-lg-2">
			<span class="label-tooltip" title="{l s='Select "YES" to activate the exclusion rule' mod='gmerchantcenterpro'}"><b>{l s='Activate' mod='gmerchantcenterpro'}</b></span></label>
			<div class="col-xs-12 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_excl-rule-active" id="bt_excl-rule-active_on" value="1" {if !empty($aDataRule.status)}checked="checked"{/if} />
						<label for="bt_excl-rule-active_on" class="radioCheck">
							{l s='YES' mod='gmerchantcenterpro'}
						</label>
						<input type="radio" name="bt_excl-rule-active" id="bt_excl-rule-active_off" value="0" {if empty($aDataRule.status)}checked="checked"{/if} />
						<label for="bt_excl-rule-active_off" class="radioCheck">
							{l s='NO' mod='gmerchantcenterpro'}
						</label>
						<a class="slide-button btn"></a>
					</span>
				<span class="label-tooltip" data-toggle="tooltip" data-placement="right" data-original-title="{l s='Select "YES" to activate the exclusion rule' mod='gmerchantcenterpro'}">&nbsp;&nbsp;<span class="icon-question-sign"></span></span>
			</div>
		</div>

		<div class="form-group" id="optionplus">
			<label class="control-label col-lg-2">
				<span class="label-tooltip" title="{l s='Give a name to this exclusion rule' mod='gmerchantcenterpro'}"><b>{l s='Exclusion rule name' mod='gmerchantcenterpro'}</b></span>
			</label>
			<div class="col-xs-5">
				<input type="text" name="bt-exclusion-name" id="bt-exclusion-name" value="{$aDataRule.name|escape:'htmlall':'UTF-8'}"/>
			</div>
			<span class="label-tooltip" data-toggle="tooltip" data-placement="right" data-original-title="{l s='Give a name to this exclusion rule' mod='gmerchantcenterpro'}">&nbsp;&nbsp;<span class="icon-question-sign"></span></span>
		</div>

		<div class="form-group" id="optionplus">
			<label class="control-label col-lg-2">
				<span class="label-tooltip" title="{l s='Select the type of exclusion' mod='gmerchantcenterpro'}"><b>{l s='Exclusion based on' mod='gmerchantcenterpro'}</b></span>
			</label>
			<div class="col-xs-5">
				<select name="bt-exclusion-type" id="bt-exclusion-type" onchange="$('#loadingGoogleRulesDiv').show();var sTypeValue = $('#bt-exclusion-type').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.excludeValue.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.excludeValue.type|escape:'htmlall':'UTF-8'}&sExclusionType=' + sTypeValue + '&iRuleId=' {if !empty($iRuleId)} + {$iRuleId}{else} + '0' {/if}, 'rule-config', 'rule-config', null, null, 'loadingGoogleRulesDiv');">
					<option value="0"> -- </option>
						{foreach key=key item=sExclusionType from=$aExclusionType}
                            {if isset($sExclusionType.$sCurrentIso)}
							<option value="{$key|escape:'htmlall':'UTF-8'}" {if $aDataRule.type == $key} selected {/if}>{$sExclusionType.$sCurrentLang|escape:'htmlall':'UTF-8'}</option>
							{else}
								<option value="{$key|escape:'htmlall':'UTF-8'}" {if $aDataRule.type == $key} selected {/if}>{$sExclusionType.en|escape:'htmlall':'UTF-8'}</option>
							{/if}
						{/foreach}
				</select>
			</div>
			<span class="label-tooltip" data-toggle="tooltip" data-placement="right" data-original-title="{l s='Select the type of exclusion' mod='gmerchantcenterpro'}">&nbsp;&nbsp;<span class="icon-question-sign"></span></span>
		</div>

		{*Use case to display the option content*}
		<div id="rule-config"></div>

		<div id="rules-summary">
			<span class="col-xs-12" id="rules"></span>
		</div>

		<select  style="display: none;" name="bt-exclusion-type-hide" id="bt-exclusion-type-hide" onchange="$('#loadingGoogleRulesDiv').show();var sTypeValue = $('#bt-exclusion-type').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.excludeValue.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.excludeValue.type|escape:'htmlall':'UTF-8'}&sExclusionType=' + sTypeValue + '&iRuleId=' {if !empty($iRuleId)} + {$iRuleId}{else} + '0' {/if} + '&bUpdate=1', 'rule-config', 'rule-config', null, null, 'loadingGoogleRulesDiv');">
			<option value="0"> -- </option>
			{if isset($sExclusionType.$sCurrentLang)}
	            {foreach key=key item=sExclusionType from=$aExclusionType}
					<option value="{$key|escape:'htmlall':'UTF-8'}">{$sExclusionType.$sCurrentLang|escape:'htmlall':'UTF-8'}</option>
	            {/foreach}
			{else}
				{foreach key=key item=sExclusionType from=$aExclusionType}
					<option value="{$key|escape:'htmlall':'UTF-8'}">{$sExclusionType.en|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			{/if}
		</select>
	</form>

	<div id="loadingGoogleRulesDiv" style="display: none;">
		<div class="alert alert-info">
			<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
			<p style="text-align: center !important;">{l s='Update in progress...' mod='gmerchantcenterpro'}</p>
		</div>
	</div>
</div>

{literal}
<script type="text/javascript">

    // Use the trigger only in the update case
    {/literal}{if !empty($iRuleId)}{literal}
    	$( "#bt-exclusion-type-hide" ).trigger( "onchange" );
    {/literal}{/if}{literal}
		$('.label-tooltip, .help-tooltip').tooltip();
</script>
{/literal}