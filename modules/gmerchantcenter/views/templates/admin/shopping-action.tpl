{*
*
* Google merchant center
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
	<form class="form-horizontal col-xs-12" method="post" id="bt_gsa-form" name="bt_gsa-form" {if $smarty.const._GMC_USE_JS == true}onsubmit="javascript: oGmc.form('bt_gsa-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_gsa-settings', 'bt_gsa-settings', false, false, '', 'Gsa', 'loadingGsaDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.gsa.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.gsa.type|escape:'htmlall':'UTF-8'}" />

		<div class="row">
			<div class="col-xs-12 col-md-2 col-lg-2">
				<img class="img-responsive float-left" height="100px" width="175px" src="{$smarty.const._GMC_URL_IMG|escape:'htmlall':'UTF-8'}admin/gsa.png" alt="" />
			</div>
			{if empty($sApiKey)}
				<div class="col-xs-12 col-md-10 col-lg-10 market-text">
					<div class="clr_10"></div>
					{l s='Be ahead of your competitors by being present right now on' mod='gmerchantcenter'}&nbsp;<a href="https://support.google.com/merchants/answer/7679273" target="_blank">{l s='Google Shopping Actions!' mod='gmerchantcenter'}</a>&nbsp;
					<br/>
					{l s='Thanks to our Google Merchant Center module, be among the first to benefit from an automated and efficient management of Google orders directy from your shop.' mod='gmerchantcenter'}&nbsp;<b>{l s='It\'s simple and without commitment.' mod='gmerchantcenter'}</b>
					<div class="clr_10"></div>
				</div>
			{/if}		
		</div>

		<div class="clr_10"></div>
		<div class="clr_hr"></div>

		{if !empty($bUpdate)}
			{include file="`$sConfirmInclude`"}
		{elseif !empty($aErrors)}
			{include file="`$sErrorInclude`"}
		{/if}

		{if empty($sApiKey)}
			{include file="`$sGSaOverview`"}
		{/if}

		{if !empty($sApiKey) && !empty($bShopLink) && !empty($iGsaShopId)} 
			<div class="row">
				<div class="col-xs-12 alert alert-info">
					{l s='This tab allows you to connect your shop to the' mod='gmerchantcenter'}&nbsp;<b><a href="https://support.google.com/merchants/answer/7679273?hl={$sCurrentIso|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Google Shopping Actions service' mod='gmerchantcenter'}</a></b>&nbsp;{l s='to be able to process the Google orders directly from your back office.' mod='gmerchantcenter'}
					<br/><br/>
					{l s='To participate in Google Shopping Actions program, first make sure that you meet' mod='gmerchantcenter'}&nbsp;					
					<b><a href="https://support.google.com/merchants/answer/7159729?hl={$sCurrentIso|escape:'htmlall':'UTF-8'}" target="_blank">{l s='participation criteria.' mod='gmerchantcenter'}</a></b>
					&nbsp;{l s='Then, follow' mod='gmerchantcenter'}
					&nbsp;<b><a href="https://support.google.com/merchants/answer/9111285?hl={$sCurrentIso|escape:'htmlall':'UTF-8'}" target="_blank">{l s='all the Google Shopping Actions configuration steps' mod='gmerchantcenter'}</a></b>
					&nbsp;{l s='in your Merchant Center account. Finally, send the final validation request to Google. Once you have received the authorization, publish your storefront through your Merchant Center account and then come here to connect your shop with Google\'s service.' mod='gmerchantcenter'}
				</div>	
				<div class="clr_10"></div>
			</div>		
        {/if}

		{if !empty($sApiKey)} 
			<div class="row">
				<div class="col-xs-10">
					<a class="btn btn-success  btn-md float-right" target="_blank" href="{$sApiUrl}"><i class="fa fa-dashboard"></i> {l s='Go to dashboard' mod='gmerchantcenter'}</a>
				</div>
			{if !empty($iGsaShopId)}
				<div class="col-xs-2">
					<a class="btn btn-danger btn-md float-left" onclick="check = confirm('{l s='Are you sure you want to unlink your shop from Google Shopping Actions' mod='gmerchantcenter'} ?');if(!check)return false;$('#loadingGsaDiv').show();oGmc.hide('bt_gsa-form');oGmc.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.shopLink.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.shopLink.type|escape:'htmlall':'UTF-8'}&bLink=0&sDisplay=button3', 'bt_gsa-form', 'bt_gsa-form', null, null, 'loadingGsaDiv');"><i class="fa fa-unlink"></i> {l s='Unlink store' mod='gmerchantcenter'}</a>
				</div>
			{/if}
			</div>
		{/if}

		{if empty($sApiKey)}
			<h2>{l s='Your registration has been accepted?' mod='gmerchantcenter'}</h2>
			<div class="clr_20"></div>
		{/if}

		{if !empty($sApiKey)} 
			<div class="form-group">
				<label class="control-label col-xs-12 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='Fill in with your API key' mod='gmerchantcenter'}"><b>{l s='Your API key:' mod='gmerchantcenter'}</b></span></label>
				<div class="col-xs-12 col-md-4 col-lg-4">
					<input type="text" name="bt_api-key" value="{$sApiKey|escape:'htmlall':'UTF-8'}"/>
				</div>
			</div>
		{else}
			<div class="row">
				<div class="col-xs-4"></div>
				<div class="col-xs-4">
					<input type="text" placeholder="{l s='Enter the API key we have provided you with here' mod='gmerchantcenter'}" name="bt_api-key" value="{$sApiKey|escape:'htmlall':'UTF-8'}"/>
				</div>
				<div class="col-xs-4"></div>
			</div>		
		{/if}

		<div class="clr_20"></div>

		{if !empty($sApiKey) && !empty($bShopLink) && !empty($iGsaShopId)} 

			<div class="form-group">
				<label class="control-label col-xs-12 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='Fill in with your Merchant Center ID' mod='gmerchantcenter'}"><b>{l s='Your Merchant Center ID:' mod='gmerchantcenter'}</b></span></label>
				<div class="col-xs-12 col-md-4 col-lg-4">
					<input type="text" name="bt_merchant-id" value="{$sMerchantId|escape:'htmlall':'UTF-8'}" />
				</div>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Fill in with your Merchant Center ID' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
			</div>

			<div class="form-group">
				<label class="control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select the customer group you want to associate with customers who purchase from Google Shopping Actions' mod='gmerchantcenter'}"><b>{l s='Shopping Actions customer group:' mod='gmerchantcenter'}</b></span></label>
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<select id="bt_default-group" name="bt_default-group">
						{foreach from=$aGroups name=group key=key item=aGroup}
							<option value="{$aGroup.id_group}" {if $aGroup.id_group == $iDefaultCustomerGroup}selected="selected"{/if}>{$aGroup.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select the customer group you want to associate with customers who purchase from Google Shopping Actions' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
			</div>

			<div class="clr_50"></div>
			<h3> {l s='Carriers managment' mod='gmerchantcenter'}</h3>
			
			<div class="clr_20"></div>

			<div class="form-group">
				<label class="control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select the carrier you want to associate by default with orders that come from Google Shopping Actions. You will always be able to change it for each order through your back office.' mod='gmerchantcenter'}"><b>{l s='Carrier for Shopping Actions orders:' mod='gmerchantcenter'}</b></span></label>

				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<select id="bt_gsa-carrier-default" name="bt_gsa-carrier-default">
						{foreach from=$aCarriers name=group key=key item=aCarrier}
							<option value="{$aCarrier.id_carrier}" {if $aCarrier.id_carrier == $iCarrierId}selected="selected"{/if}>{$aCarrier.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<div class="clr_10"></div>
				</div>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select the carrier you want to associate by default with orders that come from Google Shopping Actions. You will always be able to change it for each order through your back office.' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>&nbsp;
				<a class="badge badge-info pulse pulse2 pulse pulse2" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/317" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about carriers' mod='gmerchantcenter'}</a>
			</div>

			<div class="form-group">
				<label class="control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Match each of your carriers with one of Google\'s approved carriers name.' mod='gmerchantcenter'}"><b>{l s='Matching with Google\'s carriers:' mod='gmerchantcenter'}</b></span></label>

				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-7">

					<table class="table table-stripped">
						<thead>
							<th class="text-center"><b>{l s='Shop carriers' mod='gmerchantcenter'}</b></th>
							<th class="text-center"><b>{l s='Google carriers' mod='gmerchantcenter'}</b></th>
						</thead>
						<tbody>
								{foreach from=$aCarriers name=group key=key item=aCarrier}
									<tr>
										<td>{$aCarrier.name|escape:'htmlall':'UTF-8'}</td>
										<td>
											<select id="bt_gsa-carrier_match[{$aCarrier.id_carrier}]" name="bt_gsa-carrier[{$aCarrier.id_carrier}]">
												{foreach from=$aGsaCarriers name=group key=key item=aGsaCarrier}
													<option value="{$aGsaCarrier}" {if {$aGsaCarriersMapped[$aCarrier.id_carrier]} == $aGsaCarrier}selected="selected"{/if}>{$aGsaCarrier|escape:'htmlall':'UTF-8'|upper}</option>
												{/foreach}
											</select>
										</td>
									</tr>	
								{/foreach}
						</tbody>
					</table>
				</div>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Match each of your carriers with one of Google\'s approved carriers name.' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>&nbsp;
				<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/317" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about carriers' mod='gmerchantcenter'}</a>
			</div>

			<div class="clr_30"></div>

			<h3>{l s='Connection with Google API'  mod='gmerchantcenter'}</h3>
			<div class="form-group">
				<div class="alert alert-info">
					{l s='In order to be able to manage orders from Google Shopping Actions directly through your shop, you have to click on the button below to allow our application to connect your shop with Google API. You will have to connect to the Google account you use for your Google Merchant Center account.' mod='gmerchantcenter'}					
				</div>
				<div class="clr_10"></div>

				<div class="col-xs-12 text-center">
					<a class="btn btn-lg {if empty($sMerchantId)} btn-default disabled {else} btn-success {/if}" href="{$sApiUrl}google/auth/{$iGsaShopId}"><i class="fa fa-link"></i>&nbsp;{l s='Connect your shop with Google API' mod='gmerchantcenter'}</a>
				</div>
				<div class="clr_10"></div>
					{if empty($sMerchantId)}
					<div class="help-block text-center"><i class="fa fa-warning"></i>
					&nbsp;{l s='You must first fill in your Merchant Center ID (and save) to be able to click the button.' mod='gmerchantcenter'}
					</div>
					{/if}
			</div>
		{elseif !empty($sApiKey) && empty($iGsaShopId) && !empty($bShopLink) }
			<div class="col-xs-12 text-center">
				<p class="alert alert-info">
					{l s='The connection with Google Shopping Actions has been removed. To continue using our services please click below to re-link your shop.' mod='gmerchantcenter'}	
				</p>
				<p class="alert alert-warning">
					{l s='If you can\'t etablish the link with the shop, please check if the API keep is good, and click on save button' mod='gmerchantcenter'}	
				</p>
				<a class="btn btn-success btn-lg text-center" onclick="$('#loadingGsaDiv').show();oGmc.hide('bt_gsa-form');oGmc.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.shopLink.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.shopLink.type|escape:'htmlall':'UTF-8'}&bLink=1&sDisplay=button3', 'bt_gsa-form', 'bt_gsa-form', null, null, 'loadingGsaDiv');"><i class="fa fa-link"></i> {l s='Link store' mod='gmerchantcenter'}</a>
			</div>
        {/if}

		<div class="clr_10"></div>
		<div class="clr_hr"></div>
		<div class="clr_10"></div>

        <div class="center">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
					<div id="{$sModuleName|escape:'htmlall':'UTF-8'}GsaError"></div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
					<button  class="btn btn-default pull-right" onclick="oGmc.form('bt_gsa-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_gsa-settings', 'bt_gsa-settings', false, false, '', 'Gsa', 'loadingGsaDiv', false, 1);return false;"><i class="process-icon-save"></i>{l s='Save' mod='gmerchantcenter'}</button>
				</div>
			</div>
		</div>
    </form>
</div>
{literal}
<script type="text/javascript">
	$('.label-tooltip, .help-tooltip').tooltip();
	oGmc.runGsa();
</script>
{/literal}