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
{*USE CASE FOR SUPPLIERS*}
{if !empty($aFormatSuppliers)}
	{* suppliers tree *}
	<div id="bt_brands">
		<div class="form-group">
			<label class="control-label col-xs-12 col-md-2 col-lg-2">
				<span><b>{l s='Suppliers' mod='gmerchantcenterpro'}</b></span> :
			</label>
			<div class="col-xs-12 col-md-5 col-lg-4">
				<div class="btn-actions">
					<div class="btn btn-default btn-mini" id="supplierCheck" onclick="$('#bt_rule_add').removeClass('disabled');return oGmcPro.selectAll('input.bt_supplier-box', 'check');"><i class="icon-plus-square"></i>&nbsp;{l s='Check All' mod='gmerchantcenterpro'}</div> - <div class="btn btn-default btn-mini" id="supplierUnCheck" onclick="$('#bt_rule_add').addClass('disabled');return oGmcPro.selectAll('input.bt_supplier-box', 'uncheck');"><i class="icon-minus-square"></i>&nbsp;{l s='Uncheck All' mod='gmerchantcenterpro'}</div>
					<div class="clr_10"></div>
				</div>
				<table cellspacing="0" cellpadding="0" class="table  table-bordered table-striped" style="width: 100%;">
					{foreach from=$aFormatSuppliers name=supplier key=iKey item=aSupplier}
						<tr class="alt_row">
							<td>
								{$aSupplier.id|intval}
							</td>
							<td>
								<input type="checkbox" name="bt_supplier-box[]" class="bt_supplier-box" id="bt_supplier-box_{$aSupplier.id|intval}" value="{$aSupplier.id|intval}" {if $aSupplier.ckecked == true}checked="checked"{/if}/>
							</td>
							<td>
								<i class="icon icon-folder{if !empty($aSupplier.checked)}-open{/if}">&nbsp;&nbsp;<span style="font-size:12px;"></i><span>{$aSupplier.name|escape:'htmlall':'UTF-8'}</span>
							</td>
						</tr>
					{/foreach}
				</table>
				<div class="clr_10"></div>
				<a class="btn btn-success pull-left" id="bt_rule_add" onclick="$('#loadingGoogleRulesDiv').show();var sTypeValue = $('#bt-exclusion-type').val();var aSuppliers = oGmcPro.getBulkCheckBox('bt_supplier-box', true);oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.rulesSummary.action}&sType={$aQueryParams.rulesSummary.type}&sTypeValue=' + sTypeValue +'&aSuppliers=' + aSuppliers + '&sTmpRules=true', 'rules', 'rules', null, null, 'loadingGoogleRulesDiv');" id="btn-add-condition"><i class="icon icon-plus-circle"></i>&nbsp;&nbsp;&nbsp;{l s="Add this rule" mod='gmerchantcenterpro'} </a>
			</div>
		</div>
	</div>
{/if}
{*USE CASE FOR WORDS*}
{if !empty($aWordExlusionTypeWord)}
	<div class="form-group" id="bt_words">
		<label class="control-label col-xs-12 col-md-2 col-lg-3">
			<span class="label-tooltip" title="{l s='Select in which product element the word or sequence of words is located' mod='gmerchantcenterpro'}"><b>{l s='Place of this word/sequence of words' mod='gmerchantcenterpro'}</b></span>
		</label>
		<div class="col-xs-12 col-md-5 col-lg-4">
			<select name="bt-exclusion-word-type" id="bt-exclusion-word-type" onchange="$('#loadingGoogleRulesDiv').show();var sTypeValue = $('#bt-exclusion-type').val();var iWordType = $('#bt-exclusion-word-type').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.excludeValue.action}&sType={$aQueryParams.excludeValue.type}&bRefresh=true&sExclusionType=' + sTypeValue +'&iWordType=' + iWordType + '&iRuleId=' {if !empty($iRuleId)} + {$iRuleId}{else} + '0' {/if}, 'bt-word-text-value', 'bt-word-text-value', null, null, 'loadingGoogleRulesDiv')";>
				<option value="0"> --  </option>
					{foreach from=$aWordExlusionTypeWord item=sWordExclusionType key=sKey}
                        {if isset($sWordExclusionType.$sCurrentIso)}
							<option value="{$sKey|escape:'htmlall':'UTF-8'}">{$sWordExclusionType.$sCurrentIso|escape:'htmlall':'UTF-8'}</option>
						{else}
							<option value="{$sKey|escape:'htmlall':'UTF-8'}">{$sWordExclusionType.en|escape:'htmlall':'UTF-8'}</option>
						{/if}
					{/foreach}
			</select>
		</div>
		<span class="label-tooltip" data-toggle="tooltip" data-placement="right" data-original-title="{l s='Select in which product element the word or sequence of words is located' mod='gmerchantcenterpro'}">&nbsp;&nbsp;<span class="icon-question-sign"></span></span>
	</div>
{/if}
	<div class="form-group" id="bt-word-text-value" >
		{if !empty($bDisplayField)}
			<label class="control-label col-xs-12 col-md-2 col-lg-3">
				<span class="label-tooltip" title="{l s='Write one word or a sequence of words (this will be considered as a single indivisible expression)' mod='gmerchantcenterpro'}"><b>{l s='Word/Sequence of words' mod='gmerchantcenterpro'}</b></span>
 			</label>
			<div class="col-xs-12 col-md-5 col-lg-4">
				<input type="text" name="word-exclusion-value" id="word-exclusion-value" value="{if !empty($iExclusionData)}{$iExclusionData|escape:'htmlall':'UTF-8'}{/if}"/>
				<span class="help-block">
					<i class="fa fa-warning"></i>&nbsp;{l s='We advise you to use only one word.' mod='gmerchantcenterpro'}
					<br /><br />
					{l s='Note that if you write more than one word, the sequence of words will be considered as a single indivisible expression.' mod='gmerchantcenterpro'}
				</span>
			</div>
			<a class="btn btn-success pull-left" id="bt_rule_add" onclick="$('#loadingGoogleRulesDiv').show();var sTypeValue = $('#bt-exclusion-type').val();var sWordtype = $('#bt-exclusion-word-type').val();var sWordValue = $('#word-exclusion-value').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.rulesSummary.action}&sType={$aQueryParams.rulesSummary.type}&sTypeValue=' + sTypeValue +'&sWordType=' + sWordtype +'&sWordValue=' + sWordValue +'&sTmpRules=true', 'rules', 'rules', null, null, 'loadingGoogleRulesDiv');" id="btn-add-condition"><i class="icon icon-plus-circle"></i>&nbsp;&nbsp;&nbsp;{l s="Add this rule" mod='gmerchantcenterpro'} </a>
			<span class="label-tooltip" data-toggle="tooltip" data-placement="right" data-original-title="{l s='Write one word or a sequence of words (this will be considered as a single indivisible expression)' mod='gmerchantcenterpro'}">&nbsp;&nbsp;<span class="icon-question-sign"></span></span>
		{/if}
	</div>
{*USE CASE FOR FEATURE*}
{if !empty($aFeatures)}
	<div class="form-group">
		<label class="control-label col-xs-12 col-md-2 col-lg-3">
			<span><b>{l s='Feature' mod='gmerchantcenterpro'}</b></span> :
		</label>
		<div class="col-xs-12 col-md-5 col-lg-4">
			<select name="bt-exclusion-feature" id="bt-exclusion-feature" onchange="$('#loadingGoogleRulesDiv').show();var iFeature = $('#bt-exclusion-feature').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.excludeValue.action}&sType={$aQueryParams.excludeValue.type}&bRefresh=true&iFeatureId=' + iFeature + '&iRuleId=' {if !empty($iRuleId)} + {$iRuleId}{else} + '0' {/if}, 'bt-exclusion-feature-value', 'bt-exclusion-feature-value', null, null, 'loadingGoogleRulesDiv');">
				<option value="0"> --  </option>
				{foreach from=$aFeatures item=sFeature key=sKey}
					<option value="{$sFeature.id_feature}" {if $aDataRule.exclusionOn == $sFeature.id_feature}selected="selected"{/if}>{$sFeature.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</div>
	</div>
{/if}
<div id="bt-exclusion-feature-value">
	{if !empty($aFeaturesValues)}
		<div class="form-group">
			<label class="control-label col-xs-12 col-md-2 col-lg-3">
				<span><b>{l s='Feature value' mod='gmerchantcenterpro'}</b></span> :
			</label>
			<div class="col-xs-12 col-md-5 col-lg-4">
				<select name="bt-feature-value" id="bt-feature-value">
					<option value="0"> --  </option>
					{foreach from=$aFeaturesValues item=aFeaturesValue key=sKey}
						<option value="{$aFeaturesValue.id_feature_value|escape:'htmlall':'UTF-8'}">{$aFeaturesValue.value|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
			<a class="btn btn-success pull-left" id="bt_rule_add" onclick="$('#loadingGoogleRulesDiv').show();var sTypeValue = $('#bt-exclusion-type').val();var sFeature = $('#bt-exclusion-feature').val();var sFeatureValue = $('#bt-feature-value').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.rulesSummary.action}&sType={$aQueryParams.rulesSummary.type}&sTypeValue=' + sTypeValue +'&sFeature=' + sFeature +'&sFeatureValue=' + sFeatureValue +'&sTmpRules=true', 'rules', 'rules', null, null, 'loadingGoogleRulesDiv');" id="btn-add-condition"><i class="icon icon-plus-circle"></i>&nbsp;&nbsp;&nbsp;{l s="Add this rule" mod='gmerchantcenterpro'} </a>
		</div>
		{elseif !empty($bEmptyFeatureValue)}
		<div class="form-group">
			<label class="control-label col-xs-12 col-md-2 col-lg-3">
			</label>
			<div class="col-xs-12 col-md-5 col-lg-4">
				<p class="alert alert-warning">{l s='No records for this feature' mod='gmerchantcenterpro'}</p>
			</div>
		</div>
		{/if}
</div>

	<a style="display:none; " class="btn btn-success pull-left" onclick="$('#loadingGoogleRulesDiv').show();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.rulesSummary.action}&sType={$aQueryParams.rulesSummary.type}&sTmpRules=false', 'rules', 'rules', null, null, 'loadingGoogleRulesDiv');" id="btn-add-condition-refresh"><i class="icon icon-plus-circle"></i>&nbsp;&nbsp;&nbsp;{l s="Add this rule" mod='gmerchantcenterpro'} </a>

{*USE CASE FOR ATTRIBUTE*}
{if !empty($aAttributes)}
	<div class="form-group">
		<label class="control-label col-xs-12 col-md-2 col-lg-3">
			<span><b>{l s='Attribute' mod='gmerchantcenterpro'}</b></span> :
		</label>
		<div class="col-xs-12 col-md-5 col-lg-4">
			<select name="bt-exclusion-attribute" id="bt-exclusion-attribute" onchange="$('#loadingGoogleRulesDiv').show();var iAttribute = $('#bt-exclusion-attribute').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.excludeValue.action}&sType={$aQueryParams.excludeValue.type}&bRefresh=true&iAttributeId=' + iAttribute + '&iRuleId=' {if !empty($iRuleId)} + {$iRuleId}{else} + '0' {/if}, 'bt-exclusion-attribute-value', 'bt-exclusion-attribute-value', null, null, 'loadingGoogleRulesDiv');">
				<option value="0"> --  </option>
				{foreach from=$aAttributes item=sAttribute key=sKey}
					<option value="{$sAttribute.id_attribute_group|escape:'htmlall':'UTF-8'}" >{$sAttribute.public_name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</div>
	</div>
{/if}

{*Display the attribute values dynamically*}
<div id="bt-exclusion-attribute-value">
    {if !empty($aAttributeValues)}
		<div class="form-group">
			<label class="control-label col-xs-12 col-md-2 col-lg-3">
				<span><b>{l s='Attribute value' mod='gmerchantcenterpro'}</b></span> :
			</label>
			<div class="col-xs-12 col-md-5 col-lg-4">
				<select name="bt-attribute-value" id="bt-attribute-value">
					<option value="0"> --  </option>
					{foreach from=$aAttributeValues item=aAttributeValue key=sKey}
						<option value="{$aAttributeValue.id_attribute|escape:'htmlall':'UTF-8'}">{$aAttributeValue.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
			<a class="btn btn-success pull-left" id="bt_rule_add" onclick="$('#loadingGoogleRulesDiv').show();var sTypeValue = $('#bt-exclusion-type').val();var sAttribute = $('#bt-exclusion-attribute').val();var sAttributeValue = $('#bt-attribute-value').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.rulesSummary.action}&sType={$aQueryParams.rulesSummary.type}&sTypeValue=' + sTypeValue +'&sAttribute=' + sAttribute +'&sAttributeValue=' + sAttributeValue +'&sTmpRules=true&Update=true', 'rules', 'rules', null, null, 'loadingGoogleRulesDiv');" id="btn-add-condition"><i class="icon icon-plus-circle"></i>&nbsp;&nbsp;&nbsp;{l s="Add this rule" mod='gmerchantcenterpro'} </a>
		</div>
	{elseif !empty($bEmptyAttributeValue)}
		<div class="form-group">
			<label class="control-label col-xs-12 col-md-2 col-lg-3">
			</label>
			<div class="col-xs-12 col-md-5 col-lg-4">
				<p class="alert alert-warning"> {l s='No records for this attribute' mod='gmerchantcenterpro'}</p>
			</div>
		</div>
	{/if}
</div>
{if !empty($bSpecifiqueProduct)}
	<div class="form-group">
		<label class="control-label col-xs-12 col-md-3 col-lg-3">
			<span class="label-tooltip" title="{l s='Start typing the name of a product you want to exclude and select it from the autocompleted list that will appear. All the products you want to exclude will display in a list below.' mod='gmerchantcenterpro'}"><b>{l s='What product(s) do you want to exclude ?' mod='gmerchantcenterpro'}</b></span></label>
		<div class="col-xs-12 col-md-3 col-lg-3">
			<input type="text" size="5" id="bt_search-p" name="bt_search-p" value="" />
		</div>
		<span class="icon-question-sign label-tooltip" title="{l s='Start typing the name of a product you want to exclude and select it from the autocompleted list that will appear. All the products you want to exclude will display in a list below.' mod='gmerchantcenterpro'}"></span>
	</div>

	<input type="hidden" value="{if !empty($sProductIds)}{$sProductIds|escape:'htmlall':'UTF-8'}{else}{/if}" id="hiddenProductIds" name="hiddenProductIds" />
	<input type="hidden" value="{if !empty($sProductNames)}{$sProductNames|escape:'htmlall':'UTF-8'}{/if}" id="hiddenProductNames" name="hiddenProductNames" />

	<div class="form-group">
		<label class="control-label col-xs-12 col-md-3 col-lg-3">
			<h4>{l s='Your list of products to exclude' mod='gmerchantcenterpro'}&nbsp;:</h4>
		</label>
		<div class="col-xs-12 col-md-3 col-lg-5">
			<table id="bt_product-list" border="0" cellpadding="2" cellspacing="2" class="table table-striped table-responsive">
				<thead>
				<tr>
					<th>{l s='Product(s)' mod='gmerchantcenterpro'}</th>
					<th>{l s='Delete' mod='gmerchantcenterpro'}</th>
				</tr>
				</thead>
				<tbody id="bt_excluded-products">
				{if !empty($aProducts)}
					{foreach name=product key=key item=aProduct from=$aProducts}
						<tr>
							<td>{$aProduct.id|intval}{if isset($aProduct.attrId) && $aProduct.attrId != 0} (attr: {$aProduct.attrId|intval}){/if} - {$aProduct.name|escape:'htmlall':'UTF-8'}</td>
							<td><span class="icon-trash" style="cursor:pointer;" onclick="javascript: oGmcPro.deleteProduct('{$aProduct.stringIds|escape:'htmlall':'UTF-8'}');"></span></td>
						</tr>
					{/foreach}
				{else}
					<tr id="bt_exclude-no-products">
						<td colspan="2">{l s='No product' mod='gmerchantcenterpro'}</td>
					</tr>
				{/if}
				</tbody>
			</table>
			<a class="btn btn-success pull-right" onclick="var sTypeValue = $('#bt-exclusion-type').val();var sProductIds = $('#hiddenProductIds').val();oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.rulesSummary.action}&sType={$aQueryParams.rulesSummary.type}&sTypeValue=' + sTypeValue +'&sProductIds=' + sProductIds +'&sTmpRules=true', 'rules', 'rules', null, null, 'loadingGoogleRulesListDiv');" id="btn-add-condition"><i class="icon icon-plus-circle"></i>&nbsp;&nbsp;&nbsp;{l s="Add this rule" mod='gmerchantcenterpro'} </a>
		</div>

	</div>
{/if}
{literal}
	<script type="text/javascript">

        // Use the trigger only in the update case
        {/literal}{if $sType == 'attribute'}{literal}
        $( "#bt-exclusion-attribute" ).trigger( "onchange" );
        {/literal}{/if}{literal}

        {/literal}{if $sType == 'feature'}{literal}
        $( "#bt-exclusion-feature" ).trigger( "onchange" );
        {/literal}{/if}{literal}

        {/literal}{if $sType == 'word'}{literal}
        $( "#bt-exclusion-word-type" ).trigger( "onchange" );
        {/literal}{/if}{literal}

        $( "#btn-add-condition-refresh" ).trigger( "click" );

        //Manage the use case to disable the button add if we don't have option
        // oGmcPro.disableRulesButton('bt_category-box','bt_rule_add');
        // oGmcPro.disableRulesButton('bt_brand-box','bt_rule_add');
        // oGmcPro.disableRulesButton('bt_supplier-box','bt_rule_add');

		// set all elements for autocomplete
		oGmcPro.aParamsAutcomplete = {sInputSearch : '#bt_search-p', sExcludeNoProducts : '#bt_exclude-no-products', sExcludeProducts : '#bt_excluded-products', sHiddenProductNames : '#hiddenProductNames' , sHiddenProductIds : '#hiddenProductIds'};
		// autocomplete
		oGmcPro.autocomplete('{/literal}{$sURI}&sAction={$aQueryParams.searchProduct.action}&sType={$aQueryParams.searchProduct.type}{literal}', '#bt_search-p');

		{/literal}{if !empty($bAjaxMode)}{literal}
		$('.label-tooltip, .help-tooltip').tooltip();
		{/literal}{/if}{literal}

	</script>
{/literal}

