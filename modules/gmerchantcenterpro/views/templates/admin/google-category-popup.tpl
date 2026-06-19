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
{if !empty($aErrors)}
{include file="`$sErrorInclude`"}
{* USE CASE - edition review mode *}
{else}
<div id="{$sModuleName}" class="bootstrap">
	<div id="bt_google-category" class="col-xs-12">
		<div class="row">
			<div class="col-xs-6">
				<h3>{l s='Google product categories for the feed ' mod='gmerchantcenterpro'}: {$sLangIso}</h3>
			</div>
			<div class="col-xs-6">
				<div class="pull-right">
					<button class="btn btn-success btn-sm" onclick="oGmcPro.form('bt_form-google-cat', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_google-category', 'bt_google-category', false, true, null, 'GoogleCat', 'loadingGoogleCatDiv');return false;">{l s='Modify' mod='gmerchantcenterpro'}</button>
					<button class="btn btn-danger btn-sm" value="{l s='Cancel' mod='gmerchantcenterpro'}"  onclick="$.fancybox.close();return false;">{l s='Cancel' mod='gmerchantcenterpro'}</button>
				</div>
			</div>
		</div>

		<div class="clr_hr"></div>
		<div class="clr_20"></div>

		<div class="alert alert-success">
			{l s='INSTRUCTIONS : for each of your shop categories, start to type keywords that represent the category, using as many words as you wish (simply separate each word by a space). A list of Google categories that could match with will appear, containing all the words you entered. Simply select the best match from the list.' mod='gmerchantcenterpro'}
		</div>

		{if $iMaxPostVars != false && $iShopCatCount > $iMaxPostVars}
		<div class="alert alert-warning">
			{l s='IMPORTANT NOTE : be careful, apparently the number of variables that can be posted via the form is limited by your server, and your total number of categories is higher than this variables maximum number allowed' mod='gmerchantcenterpro'} :<br/>
			<strong>{$iShopCatCount|intval}</strong>&nbsp;{l s='categories' mod='gmerchantcenterpro'}</strong>&nbsp;{l s='on' mod='gmerchantcenterpro'}&nbsp;<strong>{$iMaxPostVars|intval}</strong>&nbsp;{l s='possible variables ... (PHP directive => max_input_vars)' mod='gmerchantcenterpro'}<br/><br/>
			<strong>{l s='It\'s possible that all your categories AREN\'T properly registered, PLEASE VISIT OUR' mod='gmerchantcenterpro'}</strong> <a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?lg={$sCurrentIso}&id=59">{l s='FAQ : \"why my selection of categories not properly saved\"' mod='gmerchantcenterpro'}</a>
		</div>
		{/if}

		<div class="clr_20"></div>

		<form class="form-horizontal" method="post" id="bt_form-google-cat" name="bt_form-google-cat" {if $smarty.const._GSR_USE_JS == true}onsubmit="oGmcPro.form('bt_form-google-cat', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_google-category', 'bt_google-category', false, true, null, 'GoogleCat', 'loadingGoogleCatDiv');return false;"{/if}>
			<input type="hidden" name="{$sCtrlParamName|escape:'htmlall':'UTF-8'}" value="{$sController|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sAction" value="{$aQueryParams.googleCatUpdate.action}" />
			<input type="hidden" name="sType" value="{$aQueryParams.googleCatUpdate.type}" />
			<input type="hidden" name="sLangIso" value="{$sLangIso}" />
			<input type="hidden" name="iLangId" value="{$iLangId|intval}" />

			<table class="table table-bordered table-responsive">
				<thead>
					<th class="bt_tr_header text-center">{l s='Your shop category' mod='gmerchantcenterpro'}</th>
					<th class="bt_tr_header text-center">{l s='Google category' mod='gmerchantcenterpro'}</th>
				</thead>
				<tbody>
				{foreach from=$aShopCategories name=category item=aCategory}
					<tr>
						<td class="label_tag_categories">{$aCategory.path}</td>
						<td>
							<input class="autocmp" style="font-size: 11px; width: 800px;" type="text" name="bt_google-cat[{$aCategory.id_category}]" id="bt_google-cat{$aCategory.id_category|intval}" value="{$aCategory.google_category_name|escape:'htmlall':'UTF-8'}" />
							<p class="duplicate_category">
								{if $smarty.foreach.category.first}
									<br /><a class="btn btn-sm pull-right btn-success" href="#" onclick="return oGmcPro.duplicateFirstValue('input.autocmp', $('#bt_google-cat{$aCategory.id_category|intval}').val());">{l s='Duplicate this value for all the categories below' mod='gmerchantcenterpro'}</a>
								{/if}
							</p>
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>

			<div class="clr_20"></div>

			<div class="center">
				<button class="btn btn-success btn-lg" onclick="oGmcPro.form('bt_form-google-cat', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_google-category', 'bt_google-category', false, true, null, 'GoogleCat', 'loadingGoogleCatDiv');return false;">{l s='Modify' mod='gmerchantcenterpro'}</button>
				<button class="btn btn-danger btn-lg" value="{l s='Cancel' mod='gmerchantcenterpro'}"  onclick="$.fancybox.close();return false;">{l s='Cancel' mod='gmerchantcenterpro'}</button>
			</div>
		</form>
		{literal}
		<script type="text/javascript">
			$('input.autocmp').each(function(index, element) {
				var query = $(element).attr("id");
				$(element).autocomplete('{/literal}{$sURI}&{$sCtrlParamName}={$sController}&sAction={$aQueryParams.autocomplete.action}&sType={$aQueryParams.autocomplete.type}&sLangIso={$sLangIso}&query='+query{literal}, {

					minChars: 3,
					autoFill: false,
					max:50,
					matchContains: true,
					mustMatch:false,
					scroll:true,
					cacheLength:0,
					formatItem: function(item) {
						return item[0];
					}
				});
			});

			$("form").bind("keypress", function (e) {
				if (e.keyCode == 13) {
					return false;
				}
			});
		</script>
		{/literal}
	</div>
</div>
<div id="loadingGoogleCatDiv" style="display: none;">
	<div class="alert alert-info">
		<p style="text-align: center !important;"><img src="{$sLoadingImg}" alt="Loading" /></p><div class="clr_20"></div>
		<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
	</div>
</div>
{/if}