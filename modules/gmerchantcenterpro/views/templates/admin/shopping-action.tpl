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

<div class="bootstrap">
	<form class="form-horizontal col-xs-12" method="post" id="bt_gsa-form" name="bt_gsa-form" {if $smarty.const._GMCP_USE_JS == true}onsubmit="javascript: oGmcPro.form('bt_gsa-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_gsa-settings', 'bt_gsa-settings', false, false, '', 'Gsa', 'loadingGsaDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.gsa.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.gsa.type|escape:'htmlall':'UTF-8'}" />

		<h3 class="subtitle"><i class="fa fa-google"></i>&nbsp;{l s='Merchant Center with Actions - Configuration' mod='gmerchantcenterpro'}</h3>

		{if !empty($bUpdate)}
			{include file="`$sConfirmInclude`"}
		{elseif !empty($aErrors)}
			{include file="`$sErrorInclude`"}
		{/if}

		{if !empty($boAuth)} 

			<div class="row">
				{if !empty($boAuth)}
					<div class="clr_10"></div>
					<div class="col-xs-12">
					{if !empty($sMerchantId)}
						{if !empty($bShopLink)}
							<a class="btn btn-danger btn-md text-center float-left" onclick="check = confirm('{l s='Are you sure you want to unlink your shop from Buy on Google service?' mod='gmerchantcenterpro'}');if(!check)return false;$('#loadingGsaDiv').show();oGmcPro.hide('bt_gsa-form');oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.shopLink.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.shopLink.type|escape:'htmlall':'UTF-8'}&bLink=0&sDisplay=button3', 'bt_gsa-form', 'bt_gsa-form', null, null, 'loadingGsaDiv');"><i class="fa fa-stop"></i> {l s='Stop automatic synchronizations' mod='gmerchantcenterpro'}</a>
						{else}
						<a class="btn btn-success btn-md text-center float-left" onclick="check = confirm('{l s='Are you sure you want to link your shop with Buy on Google service?' mod='gmerchantcenterpro'}');if(!check)return false;$('#loadingGsaDiv').show();oGmcPro.hide('bt_gsa-form');oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.shopLink.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.shopLink.type|escape:'htmlall':'UTF-8'}&bLink=1&sDisplay=button3', 'bt_gsa-form', 'bt_gsa-form', null, null, 'loadingGsaDiv');"><i class="fa fa-play"></i> {l s='Start automatic synchronizations' mod='gmerchantcenterpro'}</a>
						{/if}
					{/if}		
					<a class="btn btn-success  btn-md float-right" target="_blank" href="{$sApiUrl}"><i class="fa fa-dashboard"></i> {l s='Go to dashboard' mod='gmerchantcenterpro'}</a>
					</div>
					<div class="clr_20"></div>
				{/if}
			</div>
			
			<div class="clr_20"></div>
			<h3> {l s='Basic settings' mod='gmerchantcenterpro'}</h3>
			<div class="clr_20"></div>

			<div class="form-group">
				<label class="control-label col-xs-12 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='Fill in with your Merchant Center ID' mod='gmerchantcenterpro'}"><b>{l s='Your Merchant Center ID:' mod='gmerchantcenterpro'}</b></span></label>
				<div class="col-xs-12 col-md-4 col-lg-2">
					<input type="text" name="bt_merchant-id" value="{$sMerchantId|escape:'htmlall':'UTF-8'}" />
				</div>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Fill in with your Merchant Center ID' mod='gmerchantcenterpro'}">&nbsp;<span class="icon-question-sign"></span></span>
			</div>

			<div class="form-group">
				<label class="control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select the customer group you want to assign to customers who come from the "Buy on Google" marketplace' mod='gmerchantcenterpro'}"><b>{l s='"Buy on Google" customer group:' mod='gmerchantcenterpro'}</b></span></label>
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<select id="bt_default-group" name="bt_default-group">
						{foreach from=$aGroups name=group key=key item=aGroup}
							<option value="{$aGroup.id_group}" {if $aGroup.id_group == $iDefaultCustomerGroup}selected="selected"{/if}>{$aGroup.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select the customer group you want to assign to customers who come from the "Buy on Google" marketplace' mod='gmerchantcenterpro'}">&nbsp;<span class="icon-question-sign"></span></span>
			</div>

			{* CARRIER BLOCK *}
			<div class="clr_50"></div>
			<h3> {l s='Carrier management' mod='gmerchantcenterpro'}</h3>
			
			<div class="clr_20"></div>

			<div class="form-group">
				<label class="control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select the carrier you want to associate by default with orders that come from the "Buy on Google" marketplace. You will always be able to change it for each order through your back office.' mod='gmerchantcenterpro'}"><b>{l s='Carrier for "Buy on Google" orders:' mod='gmerchantcenterpro'}</b></span></label>

				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<select id="bt_gsa-carrier-default" name="bt_gsa-carrier-default">
						{foreach from=$aCarriers name=group key=key item=aCarrier}
							<option value="{$aCarrier.id_carrier}" {if $aCarrier.id_carrier == $iCarrierId}selected="selected"{/if}>{$aCarrier.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<div class="clr_10"></div>
				</div>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select the carrier you want to associate by default with orders that come from the "Buy on Google" marketplace. You will always be able to change it for each order through your back office.' mod='gmerchantcenterpro'}">&nbsp;<span class="icon-question-sign"></span></span>&nbsp;
				<a class="badge badge-info pulse pulse2 pulse pulse2" href="{$smarty.const._GMCP_GSA_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/317" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about carriers' mod='gmerchantcenterpro'}</a>
			</div>

			<div class="form-group">
				<label class="control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Match each of your carriers with one of Google\'s approved carriers name.' mod='gmerchantcenterpro'}"><b>{l s='Matching with Google\'s carriers:' mod='gmerchantcenterpro'}</b></span></label>

				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-7">

					<table class="table table-stripped">
						<thead>
							<th class="text-center"><b>{l s='Shop\'s carriers' mod='gmerchantcenterpro'}</b></th>
							<th class="text-center"><b>{l s='Google\'s carriers' mod='gmerchantcenterpro'}</b></th>
						</thead>
						<tbody>
								{foreach from=$aCarriers name=group key=key item=aCarrier}
									<tr>
										<td>{$aCarrier.name|escape:'htmlall':'UTF-8'}</td>
										<td>
											<select id="bt_gsa-carrier_match[{$aCarrier.id_carrier}]" name="bt_gsa-carrier[{$aCarrier.id_carrier}]">
												{foreach from=$aGsaCarriers name=group key=key item=aGsaCarrier}
													{if isset($aGsaCarriersMapped[$aCarrier.id_carrier])}
														<option value="{$aGsaCarrier}" {if {$aGsaCarriersMapped[$aCarrier.id_carrier]} == $aGsaCarrier}selected="selected"{/if}>{$aGsaCarrier|escape:'htmlall':'UTF-8'|upper}</option>
													{else}
														<option value="{$aGsaCarrier}">{$aGsaCarrier|escape:'htmlall':'UTF-8'|upper}</option>
													{/if}
												{/foreach}
											</select>
										</td>
									</tr>	
								{/foreach}
						</tbody>
					</table>
				</div>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Match each of your carriers with one of Google\'s approved carriers name.' mod='gmerchantcenterpro'}">&nbsp;<span class="icon-question-sign"></span></span>&nbsp;
				<a class="badge badge-info" href="{$smarty.const._GMCP_GSA_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/317" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about carriers' mod='gmerchantcenterpro'}</a>
			</div>

			<div class="clr_30"></div>
			{if !empty($sMerchantId)}
				<h3>{l s='Connection with Google API'  mod='gmerchantcenterpro'}</h3>
				<div class="clr_10"></div>
				<div class="form-group">
					<div class="alert alert-info">
						{l s='In order to be able to manage orders from the "Buy on Google" marketplace directly through your shop, you have to click on the button below to allow our application to connect your shop with the Google API. You will need to sign in to the Google Account you use to manage your Merchant Center account.' mod='gmerchantcenterpro'}
						<br /><br />
						{l s='Once the connection with the API is established, go to the "Integration Tests" tab of' mod='gmerchantcenterpro'}&nbsp;
						<strong><a href="{$sApiUrl}" target="_blank">{l s='your dashboard' mod='gmerchantcenterpro'}</a></strong>&nbsp;
						{l s='to test the interactions between the "Buy on Google" service and your shop.' mod='gmerchantcenterpro'}
					</div>
					<div class="clr_10"></div>

					<div class="col-xs-12 text-center">
						<a class="btn btn-lg {if empty($sMerchantId)} btn-default disabled {else} btn-success {/if}" href="{$sApiUrl}google/auth/{$iGsaShopId}"><i class="fa fa-link"></i>&nbsp;{l s='Connect your shop with Google API' mod='gmerchantcenterpro'}</a>
					</div>
					<div class="clr_10"></div>
						{if empty($sMerchantId)}
						<div class="help-block text-center"><i class="fa fa-warning"></i>
						&nbsp;{l s='You must first fill in your Merchant Center ID (and save) to be able to click the button.' mod='gmerchantcenterpro'}
						</div>
						{/if}
				</div>

				{* HANDLING TIME *}
				{* <div class="clr_50"></div>
				<h3> {l s='Order handling time' mod='gmerchantcenterpro'}</h3>
				<div class="clr_20"></div>

				<p class="alert alert-info">
					{l s='Indicate the minimum and the maximum time for a product to be shipped from the moment the order is placed. This is mandatory and helps Google to give users accurate information about how long it will take for a product to arrive at its destination. For more information visit our' mod='gmerchantcenterpro'}								
					<a class="badge badge-info" href="{$smarty.const._GMCP_GSA_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/392" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about handling time' mod='gmerchantcenterpro'}</a>
				</p>

				<div class="form-group">
					<label class="control-label col-xs-12 col-md-3 col-lg-3">
						<b>{l s='Min handling time:' mod='gmerchantcenterpro'}</b></label>
					<div class="col-xs-12 col-md-4 col-lg-2">
						<input type="text" name="bt_min-handling-time" value="{$sMinHandlingTime|escape:'htmlall':'UTF-8'}" />
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-xs-12 col-md-3 col-lg-3">
						<b>{l s='Max handling time:' mod='gmerchantcenterpro'}</b></label>
					<div class="col-xs-12 col-md-4 col-lg-2">
						<input type="text" name="bt_max-handling-time" value="{$sMaxHandlingTime|escape:'htmlall':'UTF-8'}" />
					</div>
				</div> *}

				{* STOCK ATTRIBUTION *}
				<div class="clr_50"></div>
				<h3> {l s='Allocation of product stock for sale on "Buy on Google"' mod='gmerchantcenterpro'}</h3>
				<div class="clr_20"></div>

				<p class="alert alert-info">
					{l s='For each product, you can limit the number of items available for sale on Google. By default, the number of items available for sale on the "Buy on Google" marketplace, for each product, corresponds to 100% of the stock of the product. If you want to make only a part of the stock of each product available, enter the corresponding percentage below.' mod='gmerchantcenterpro'}	
				</p>

				<div class="form-group">
					<label class="control-label col-xs-12 col-md-3 col-lg-3">
						<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Enter the percentage of items for each product you want to make available for sale on Google. Enter the number without the % unity.' mod='gmerchantcenterpro'}"><b>{l s='Percentage of stock available for sale on Google:' mod='gmerchantcenterpro'}</b></span>
					</label>
					<div class="col-xs-12 col-md-4 col-lg-2">
						<input type="text" name="bt_percent_stock" value="{$iPercentStock|escape:'htmlall':'UTF-8'}" />
					</div>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Enter the percentage of items for each product you want to make available for sale on Google. Enter the number without the % unity.' mod='gmerchantcenterpro'}">&nbsp;<span class="icon-question-sign"></span></span>&nbsp;
					<a class="badge badge-info" href="{$smarty.const._GMCP_GSA_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/395" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about stock attribution' mod='gmerchantcenterpro'}</a>		
				</div>

				{* google_funded_promotion_eligibility *}
				<div class="clr_50"></div>
				<h3> {l s='Google funded promotion eligibility' mod='gmerchantcenterpro'}</h3>
				<div class="clr_20"></div>

				<p class="alert alert-info">
					{l s='Google sometimes offers promotions (for example, 20% off electronics) that are applicable to all products across the "Buy on Google" marketplace, regardless of the merchant. These are promotions that are funded by Google. If you want to make your products eligible to participate in Google promotions, select "all" below.' mod='gmerchantcenterpro'}									
				</p>

				<div class="form-group">

					<label class="control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select "none" to exclude your products from Google funded promotions, or "all" to make them eligible.' mod='gmerchantcenterpro'}"><b>{l s='Google funded promotion eligibility' mod='gmerchantcenterpro'}</b></span></label>
					<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
						<select id="bt_funded_promotion" name="bt_funded_promotion">
							{foreach from=$aPromoFundedValues name=group key=key item=sFunded}
								<option value="{$sFunded}" {if $sFunded == $sPromoFundedValues}selected="selected"{/if}>{$sFunded|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
					</div>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Select "none" to exclude your products from Google funded promotions, or "all" to make them eligible.' mod='gmerchantcenterpro'}">&nbsp;<span class="icon-question-sign"></span></span>&nbsp;
					<a class="badge badge-info" href="{$smarty.const._GMCP_GSA_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/394" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about Google funded promotions' mod='gmerchantcenterpro'}</a>	
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
							<button  class="btn btn-lg btn-info pull-right" onclick="oGmcPro.form('bt_gsa-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_gsa-settings', 'bt_gsa-settings', false, false, '', 'Gsa', 'loadingGsaDiv', false, 1);return false;"><i class="process-icon-save"></i>{l s='Save' mod='gmerchantcenterpro'}</button>
						</div>
					</div>
				</div>

		{elseif !empty($boAuth) && empty($iGsaShopId) && !empty($bShopLink) }
			<div class="col-xs-12 text-center">
				<p class="alert alert-info">
					{l s='The connection with the "Buy on Google" service has been removed. To continue using our service you must re-activate the automatic synchronizations below.' mod='gmerchantcenterpro'}	
				</p>
				<a class="btn btn-success btn-lg text-center" onclick="$('#loadingGsaDiv').show();oGmcPro.hide('bt_gsa-form');oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.shopLink.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.shopLink.type|escape:'htmlall':'UTF-8'}&bLink=1&sDisplay=button3', 'bt_gsa-form', 'bt_gsa-form', null, null, 'loadingGsaDiv');"><i class="fa fa-link"></i> {l s='Activate auto-synchronizations' mod='gmerchantcenterpro'}</a>
			</div>
		{/if}
    </form>
</div>
{literal}
<script type="text/javascript">
	$('.label-tooltip, .help-tooltip').tooltip();
	oGmcPro.runGsa();
</script>
{/literal}