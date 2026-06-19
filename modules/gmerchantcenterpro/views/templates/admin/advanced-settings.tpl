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
			var oDiscountSettingsCallBack = [{}];
		{/literal}
	</script>

	<div class="bootstrap">
		<form class="form-horizontal col-xs-12" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_advanced-{$sDisplay|escape:'htmlall':'UTF-8'}-form" name="bt_advanced-{$sDisplay|escape:'htmlall':'UTF-8'}-form" {if $smarty.const._GMCP_USE_JS==true}onsubmit="javascript: oGmcPro.form('bt_advanced-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_advanced-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, oDiscountSettingsCallBack, 'Advanced', 'loadingAdvancedDiv');return false;" {/if}>
			<input type="hidden" name="sAction" value="{$aQueryParams.advancedfeed.action|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sType" value="{$aQueryParams.advancedfeed.type|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sDisplay" id="sAdvancedFeedDisplay" value="{if !empty($sDisplay)}{$sDisplay|escape:'htmlall':'UTF-8'}{else}promo{/if}" />

			{* USE CASE - ADVANCED PROMO *}
			{if !empty($sDisplay) && $sDisplay == 'promo'}

			<ul class="nav nav-tabs" id="myTab">
				<li {if empty($aDiscountAvailable)}class="active" {/if}>
					<a data-toggle="tab" href="#conf"><i class="fa fa-cog"></i>&nbsp;{l s='Cart rules management for Special offers data feed' mod='gmerchantcenterpro'}</a>
				</li>
				<li {if !empty($aDiscountAvailable)}class="active" {/if}>
					<a data-toggle="tab" href="#list"><i class="fa fa-server"></i>&nbsp;{l s='List of cart rules exported' mod='gmerchantcenterpro'}</a>
				</li>
			</ul>
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane {if empty($aDiscountAvailable)} active {/if}" id="conf">

					<div class="clr_10"></div>
					{if !empty($bUpdate)}
					{include file="`$sConfirmInclude`"}
					{elseif !empty($aErrors)}
					{include file="`$sErrorInclude`"}
					{/if}

					<div class="alert alert-info">
						<p><strong class="highlight_element">{l s='Google allows you to distribute online special offers with your Product Shopping ads on Google.com and Google Shopping, without additional cost. When you add special offers to products that you sell on Google, shoppers see a "special offer" link.' mod='gmerchantcenterpro'}
								{l s='For more information please follow our' mod='gmerchantcenterpro'}&nbsp;<a target="_blank" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/110">{l s='FAQ about special offers feed' mod='gmerchantcenterpro'}</a>.</p>
						<br />
						<p><i class="icon icon-warning"></i>&nbsp;
							{l s='Please also see Google important note about' mod='gmerchantcenterpro'}&nbsp;<a id="editorial" target="_blank" href="https://support.google.com/merchants/answer/2877578?vid=1-635802500732303080-824429148?hl={$sCurrentIso|escape:'htmlall':'UTF-8'}">{l s='editorial requirements for your cart rules' mod='gmerchantcenterpro'}</a>.</strong></p>
						<br />
						<p>{l s='Filters below will help you to configure which cart rules will be exported in your data feed.' mod='gmerchantcenterpro'}</p>
					</div>

					<div class="row form-group">
						<label class="control-label col-xs-12 col-md-2 col-lg-2"></label>
						<div class="col-xs-3 col-md-3 col-lg-3" id="gmcp_bulk_filter">
							<a class="btn btn-default" onclick="return oGmcPro.openAllFitler('check');"><span><i class="icon icon-plus-square">&nbsp;</i>{l s='Activate all' mod='gmerchantcenterpro'}</span></a>
							&nbsp;
							<a class="btn btn-default" onclick="return oGmcPro.openAllFitler('uncheck');"><span><i class="icon icon-minus-square">&nbsp;</i>{l s='Deactivate all' mod='gmerchantcenterpro'}</span></a>
						</div>
					</div>

					<div class="row form-group">
						<label class="control-label col-xs-12 col-md-2 col-lg-2">
							<span class="label-tooltip" title="{l s='Select "YES" to only export the cart rules that you are going to type the names. It\'s particularly useful if you have created a promotion code only reserved to Google Shopping users, in order to better track conversions.' mod='gmerchantcenterpro'}">
								<b>{l s='Filter by cart rule name :' mod='gmerchantcenterpro'}</b>
							</span>&nbsp;
						</label>

						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="bt_option-name" id="bt_option-name_on" value="true" {if !empty($bFilterName)}checked="checked" {/if} />
							<label for="bt_option-name_on" class="radioCheck">
								{l s='Yes' mod='gmerchantcenterpro'}
							</label>
							<input type="radio" name="bt_option-name" id="bt_option-name_off" value="false" {if empty($bFilterName)}checked="checked" {/if} />
							<label for="bt_option-name_off" class="radioCheck">
								{l s='No' mod='gmerchantcenterpro'}
							</label>
							<a class="slide-button btn"></a>
						</span>

						<div class="col-xs-1" id="bt_discount-name-group">
							<input type="text" id="bt_discount-name" name="bt_discount-name" value="{$sDiscountName|escape:'htmlall':'UTF-8'}" />
						</div>
						&nbsp;&nbsp;<span class="icon-question-sign label-tooltip" title="{l s='Select "YES" to only export the cart rules that you are going to type the names. It\'s particularly useful if you have created a promotion code only reserved to Google Shopping users, in order to better track conversions.' mod='gmerchantcenterpro'}">&nbsp;</span>
					</div>

					<div class="row form-group">
						<label class="control-label col-xs-12 col-md-2 col-lg-2">
							<span class="label-tooltip" title="{l s='Select "YES" to only export special offers whose validity period is between two specific dates.' mod='gmerchantcenterpro'}">
								<b>{l s='Filter by validity dates :' mod='gmerchantcenterpro'}</b>
							</span>&nbsp;
						</label>
						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="bt_option-date" id="bt_option-date_on" value="true" {if !empty($bFilterDate)}checked="checked" {/if} />
							<label for="bt_option-date_on" class="radioCheck">
								{l s='Yes' mod='gmerchantcenterpro'}
							</label>
							<input type="radio" name="bt_option-date" id="bt_option-date_off" value="false" {if empty($bFilterDate)}checked="checked" {/if} />
							<label for="bt_option-date_off" class="radioCheck">
								{l s='No' mod='gmerchantcenterpro'}
							</label>
							<a class="slide-button btn"></a>
						</span>

						<div id="bt_date-group" class="col-xs-6">
							<div class="input-group col-xs-12">
								<span class="input-group-addon">{l s='From' mod='gmerchantcenterpro'}</span>
								<input type="text" name="bt_discount-date-from" id="bt_discount-date-from" class="date-picker" value="{$sDiscountDateFrom|escape:'htmlall':'UTF-8'}" />
								<span class="input-group-addon">{l s='To' mod='gmerchantcenterpro'}</span>
								<input type="text" name="bt_discount-date-to" id="bt_discount-date-to" class="date-picker" value="{$sDiscountDateTo|escape:'htmlall':'UTF-8'}" />
							</div>
						</div>
						&nbsp;&nbsp;<span class="icon-question-sign label-tooltip" title="{l s='Select "YES" to only export special offers whose validity period is between two specific dates.' mod='gmerchantcenterpro'}">&nbsp;</span>
					</div>

					<div class="row form-group" id="gmcp_min_amount_filter">
						<label class="control-label col-xs-12 col-md-2 col-lg-2">
							<span class="label-tooltip" title="{l s='Select "YES" to only export special offers that require a minimum amount of purchase.' mod='gmerchantcenterpro'}">
								<b>{l s='Filter by minimum purchase :' mod='gmerchantcenterpro'}</b>
							</span>&nbsp;
						</label>

						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="bt_option-min-amount" id="bt_option-min-amount_on" value="true" {if !empty($bFilterMinAmount)}checked="checked" {/if} />
							<label for="bt_option-min-amount_on" class="radioCheck">
								{l s='Yes' mod='gmerchantcenterpro'}
							</label>
							<input type="radio" name="bt_option-min-amount" id="bt_option-min-amount_off" value="false" {if empty($bFilterMinAmount)}checked="checked" {/if} />
							<label for="bt_option-min-amount_off" class="radioCheck">
								{l s='No' mod='gmerchantcenterpro'}
							</label>
							<a class="slide-button btn"></a>
						</span>

						<div class="col-xs-12 col-lg-1" id="bt_min-amount-group">
							<input type="text" name="bt_discount-min-amount" id="bt_discount-min-amount" value="{$sDiscountMinAmount|escape:'htmlall':'UTF-8'}" />
						</div>
						&nbsp;&nbsp;<span class="icon-question-sign label-tooltip" title="{l s='Select "YES" to only export special offers that require a minimum amount of purchase.' mod='gmerchantcenterpro'}">&nbsp;</span>
					</div>

					<div class="row form-group" id="gmcp_reduction_amount_filter">
						<label class="control-label col-xs-12 col-md-2 col-lg-2">
							<span class="label-tooltip" title="{l s='Select "YES" to only export special offers whose amount (or percentage) is between two specific values.' mod='gmerchantcenterpro'}">
								<b>{l s='Filter by voucher amount :' mod='gmerchantcenterpro'}</b>
							</span>&nbsp;
						</label>

						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="bt_option-value" id="bt_option-value_on" value="true" {if !empty($bFilterValue)}checked="checked" {/if} />
							<label for="bt_option-value_on" class="radioCheck">
								{l s='Yes' mod='gmerchantcenterpro'}
							</label>
							<input type="radio" name="bt_option-value" id="bt_option-value_off" value="false" {if empty($bFilterValue)}checked="checked" {/if} />
							<label for="bt_option-value_off" class="radioCheck">
								{l s='No' mod='gmerchantcenterpro'}
							</label>
							<a class="slide-button btn"></a>
						</span>

						<div class="row form-group" id="bt_value-group">
							<div class="col-xs-1">
								<input type="text" placeholder="{l s='Min' mod='gmerchantcenterpro'}" name="bt_discount-value-min" id="bt_discount-value-min" value="{$sDiscountValueMin|escape:'htmlall':'UTF-8'}" />
							</div>
							<div class="col-xs-1">
								<input type="text" placeholder="{l s='Max' mod='gmerchantcenterpro'}" name="bt_discount-value-max" id="bt_discount-value-max" value="{$sDiscountValueMax|escape:'htmlall':'UTF-8'}" />
							</div>
							<div class="col-xs-2">
								<select name="bt_discount-type" id="bt_discount-type">
									<option value="all" {if $bDiscountType=='all' }selected="selected" {/if}>{l s='Percentage and amount' mod='gmerchantcenterpro'}</option>
									<option value="percent" {if $bDiscountType=='percent' }selected="selected" {/if}>{l s='Only percent' mod='gmerchantcenterpro'}</option>
									<option value="amount" {if $bDiscountType=='amount' }selected="selected" {/if}>{l s='Only amount' mod='gmerchantcenterpro'}</option>
								</select>
							</div>
							&nbsp;&nbsp;<span class="icon-question-sign label-tooltip" title="{l s='Select "YES" to only export special offers whose amount (or percentage) is between two specific values.' mod='gmerchantcenterpro'}">&nbsp;</span>
						</div>
					</div>

					<div class="row form-group" id="gmcp_cumulable_filter">
						<label class="control-label col-xs-12 col-md-2 col-lg-2">
							<span class="label-tooltip" title="{l s='Select "No filter" to export voucher codes regardless their cumulation setting. Select "Only cumulable codes" to only export cumulable voucher codes. Select "Only NOT cumulable codes" to only export NOT cumulable voucher codes.' mod='gmerchantcenterpro'}">
								<b>{l s='Filter on cumulation settings :' mod='gmerchantcenterpro'}</b>
							</span>&nbsp;
						</label>
						<div class="col-xs-12 col-lg-2">
							<select name="bt_discount-cumulable" id="bt_discount-cumulable">
								<option value="all" {if $sDiscountCumulable=='all' }selected="selected" {/if}>{l s='No filter' mod='gmerchantcenterpro'}</option>
								<option value="cumulated" {if $sDiscountCumulable=='cumulated' }selected="selected" {/if}>{l s='Only cumulable codes' mod='gmerchantcenterpro'}</option>
								<option value="nocumulated" {if $sDiscountCumulable=='nocumulated' }selected="selected" {/if}>{l s='Only NOT cumulable codes' mod='gmerchantcenterpro'}</option>
							</select>
						</div>

						<span class="icon-question-sign label-tooltip" title="{l s='Select "No filter" to export voucher codes regardless their cumulation setting. Select "Only cumulable codes" to only export cumulable voucher codes. Select "Only NOT cumulable codes" to only export NOT cumulable voucher codes.' mod='gmerchantcenterpro'}">&nbsp;</span>
					</div>

					<div class="center">
						<div class="row form-group">
							<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
								<div id="{$sModuleName|escape:'htmlall':'UTF-8'}Feed{$sDisplay|escape:'htmlall':'UTF-8'}Error"></div>
							</div>
							<div class="col-xs-12">
								<button class="btn btn-lg btn-success" onclick="oGmcPro.form('bt_advanced-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_advanced-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, oDiscountSettingsCallBack, 'Advanced', 'loadingAdvancedDiv');return false;"><i class="icon-search"></i>&nbsp;{l s='Search' mod='gmerchantcenterpro'}</button>
							</div>
						</div>
					</div>
				</div>

				<div class="tab-pane {if !empty($aDiscountAvailable)} active {/if}" id="list">
					<div class="form-group">
						{if !empty($aDiscountAvailable)}
						<label class="control-label col-xs-1"></label>
						<div class="col-xs-12">
							<table class="table table-striped">
								<tr>
									<td class="bt_tr_header text-center">{l s='Name' mod='gmerchantcenterpro'}</td>
									<td class="bt_tr_header text-center">{l s='Details' mod='gmerchantcenterpro'}</td>
									<td class="bt_tr_header text-center">{l s='Code' mod='gmerchantcenterpro'}</td>
									<td class="bt_tr_header text-center">{l s='Channel' mod='gmerchantcenterpro'}</td>
								</tr>
								{section loop=$aDiscountAvailable name=aDiscount}
									<tr>
										<td class="text-center">{$aDiscountAvailable[aDiscount].name|escape:'htmlall':'UTF-8'}</td>
										<td class="text-center"><a class="btn btn-sm btn-info" onclick="$('#{$sModuleName|escape:'htmlall':'UTF-8'}DiscountDetail{$smarty.section.aDiscount.iteration|intval}').toggle(800)" ; ><i class="icon icon-zoom-in"></i></a></td>
										<td class="text-center">{$aDiscountAvailable[aDiscount].code|escape:'htmlall':'UTF-8'}</td>
										<td class="">
											<select name="bt-discount_channel[{$aDiscountAvailable[aDiscount].id_cart_rule}][]" class="col-xs-8 float-right" id="discount_channel" multiple>
												{foreach key=key item=sChannel from=$aDiscountChannel}
												<option {if !empty($aPromotionDestination[$aDiscountAvailable[aDiscount].id_cart_rule]) && in_array($sChannel,$aPromotionDestination[$aDiscountAvailable[aDiscount].id_cart_rule])} selected {/if} value="{$sChannel|escape:'htmlall':'UTF-8'}">{l s={$sChannel|escape:'htmlall':'UTF-8' } mod='gmerchantcenterpro'}  </option>
												{/foreach}
											</select>
										</td>
									</tr>
									<tr id="{$sModuleName|escape:'htmlall':'UTF-8'}DiscountDetail{$smarty.section.aDiscount.iteration|intval}" style="display: none;" class="promoDetails">
										<td colspan="4" class="table-line-detail col-xs-12">
											<div class="clr_20"></div
											<div class="col-xs-12">
												<div class="row">
													<div class="col-xs-6"><b>{l s='Cart rule ID' mod='gmerchantcenterpro'} :</b> {$aDiscountAvailable[aDiscount].id_cart_rule|intval}</b> </div>
												</div>
												<div class="row">
													<div class="col-xs-6"><b>{l s='Valid from' mod='gmerchantcenterpro'} : </b>{$aDiscountAvailable[aDiscount].date_from|escape:'htmlall':'UTF-8'} </div>
													<div class="col-xs-6"><b>{l s='to' mod='gmerchantcenterpro'} : </b>{$aDiscountAvailable[aDiscount].date_to|escape:'htmlall':'UTF-8'} </b></div>
												</div>
												<div class="row">
													<div class="col-xs-6"><b>{l s='Creation date' mod='gmerchantcenterpro'} : </b>{$aDiscountAvailable[aDiscount].date_add|escape:'htmlall':'UTF-8'} </div>
													<div class="col-xs-6"><b>{l s='Updating date' mod='gmerchantcenterpro'} : </b>{$aDiscountAvailable[aDiscount].date_upd|escape:'htmlall':'UTF-8'} </b></div>
												</div>
												{if !empty({$aDiscountAvailable[aDiscount].description|escape:'htmlall':'UTF-8'})}
												<div class="row">

													<div class="col-xs-12"><b>{l s='Description' mod='gmerchantcenterpro'} : </b></div>
													<div class="clr_10"></div>
													<div class="clr_hr"></div>
													<div class="clr_10"></div>
													<p>{$aDiscountAvailable[aDiscount].description|escape:'htmlall':'UTF-8'}</p>
													<div class="clr_10"></div>
													<div class="clr_hr"></div>
													<div class="clr_10"></div>
												</div>
												{/if}
												<div class="row">
													<div class="col-xs-6"><b>{l s='Quantity' mod='gmerchantcenterpro'} :</b>{$aDiscountAvailable[aDiscount].quantity|intval} </div>
													<div class="col-xs-6"><b>{l s='Quantity per user' mod='gmerchantcenterpro'}</b> :{$aDiscountAvailable[aDiscount].quantity_per_user|intval} </div>
												</div>
												<div class="row">
													<div class="col-xs-6"><b>{l s='Priority' mod='gmerchantcenterpro'} :</b>{$aDiscountAvailable[aDiscount].priority|escape:'htmlall':'UTF-8'} </div>
													<div class="col-xs-6"><b>{l s='Partial use' mod='gmerchantcenterpro'} :</b>{if $aDiscountAvailable[aDiscount].partial_use == 1}&nbsp;<i class="icon icon-check"></i>{else}&nbsp;<i class="icon icon-remove" /></i>{/if} </div>
												</div>
												{if $aDiscountAvailable[aDiscount].minimum_amount != 0}
												<div class="row">
													<div class="col-xs-6"><b>{l s='Minimal amount' mod='gmerchantcenterpro'} :</b> {$aDiscountAvailable[aDiscount].minimum_amount|escape:'htmlall':'UTF-8'}</div>
												</div>
												{/if}
												{if $aDiscountAvailable[aDiscount].reduction_percent != 0}
												<div class="row">
													<div class="col-xs-6"><b>{l s='Reduction in percent' mod='gmerchantcenterpro'} :</b> {$aDiscountAvailable[aDiscount].reduction_percent|escape:'htmlall':'UTF-8'} %</div>
												</div>
												{/if}
												{if $aDiscountAvailable[aDiscount].reduction_amount != 0}
												<div class="row">
													<div class="col-xs-6"><b>{l s='Reduction in currency' mod='gmerchantcenterpro'} :</b> {$aDiscountAvailable[aDiscount].reduction_amount|escape:'htmlall':'UTF-8'}</div>
												</div>
												{/if}
												<div class="row">
													<div class="col-xs-6"><b>{l s='Restriction on product(s)' mod='gmerchantcenterpro'} :</b>{if $aDiscountAvailable[aDiscount].product_restriction == 1}&nbsp;<i class="icon icon-check"></i>{else}&nbsp;<i class="icon icon-remove"></i>{/if} </div>
													<div class="col-xs-6"><b>{l s='Cumulable' mod='gmerchantcenterpro'} :</b>{if $aDiscountAvailable[aDiscount].cart_rule_restriction == 0}&nbsp;<i class="icon icon-check"></i>{else}&nbsp;<i class="icon icon-remove"></i>{/if} </div>
												</div>
											</div>
										</td>
									</tr>
								{/section}
							</table>
						</div>
						{else}
						<div class="alert alert-warning">{l s='No cart rule available for the selected filters...' mod='gmerchantcenterpro'}</div>
						{/if}
					</div>

					<div class="center">
						<div class="row form-group">
							<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
								<div id="{$sModuleName|escape:'htmlall':'UTF-8'}Feed{$sDisplay|escape:'htmlall':'UTF-8'}Error"></div>
							</div>
							<div class="col-xs-12">
								<button class="btn btn-lg btn-info float-right" onclick="oGmcPro.form('bt_advanced-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_advanced-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, oDiscountSettingsCallBack, 'Advanced', 'loadingAdvancedDiv');return false;"><i class="process-icon-save"></i>&nbsp;{l s='Save' mod='gmerchantcenterpro'}</button>
							</div>
						</div>
					</div>

				</div>
			</div>
			{/if}
			{* END - ADVANCED PROMO *}

			{* START - USE CASE REVIEW FEED CONFIGURATION *}
			{if !empty($sDisplay) && $sDisplay == 'reviews'}
			<h3 class="subtitle"><i class="fa fa-star"></i>&nbsp; {l s='Product ratings data feed' mod='gmerchantcenterpro'}</h3>
			<div class="clr_10"></div>

			{if !empty($bUpdate)}
			{include file="`$sConfirmInclude`"}
			{elseif !empty($aErrors)}
			{include file="`$sErrorInclude`"}
			{/if}

			<p class="alert alert-info">
				<strong class="highlight_element">
					<i class="icon icon-warning"></i>&nbsp;{l s='This feature is only available with the Prestashop "Product Comments" module or our "Customer Ratings and Reviews Pro + Google Rich Snippets" BusinessTech module.' mod='gmerchantcenterpro'}</p></strong>

			{if !empty($bGsnippetsReviews)}
			<p class="alert alert-success">{l s='BusinessTech "Customer Ratings and Reviews Pro + Google Rich Snippets" module is installed. You can use this feature.' mod='gmerchantcenterpro'}</p>
			{elseif !empty($bProductComment)}
			<p class="alert alert-success">{l s='Prestashop "Product Comments" module is installed. You can use this feature.' mod='gmerchantcenterpro'}</p>
			{else}
			<p class="alert alert-danger"> {l s='No product reviews module compatible with Google Merchant Center Pro is installed ! Please install either Prestashop "Product Comments" module or BusinessTech "Customer Ratings and Reviews Pro + Google Rich Snippets" module, to be able to use this feature.' mod='gmerchantcenterpro'}</p>
			{/if}

			<div class="clr_30"></div>

			<h3>{l s='How to use product ratings data feed in Google Shopping' mod='gmerchantcenterpro'}</h3>
			<div class="clr_10"></div>
			<div>
				<p><i class="badge badge-info pulse pulse2">1</i> {l s='Sign up for Google Product Ratings program by' mod='gmerchantcenterpro'}
					<b><a target="_blank" href="https://support.google.com/merchants/answer/7050198?ref_topic=6169092&hl={$sCurrentIso|escape:'htmlall':'UTF-8'}">{l s='clicking here' mod='gmerchantcenterpro'}</a></b></p>

				<p><i class="badge badge-info pulse pulse2">2</i> {l s='On the left menu go to "My feeds" tab -> "Product ratings data feed" to copy the URL of your product ratings data feed matching with your targeted "langage-country" pair.' mod='gmerchantcenterpro'}</p>
				<p><i class="badge badge-info pulse pulse2">3</i> {l s='Create a product ratings feed in your online Google Merchant Center interface, by using the URL you\'ve just copied.' mod='gmerchantcenterpro'}&nbsp;<a class="badge badge-info pulse pulse2" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/156" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about product ratings feed' mod='gmerchantcenterpro'}</a></p>
			</div>

			<div class="clr_50"></div>

			<h3>{l s='Reviews exclusion tool' mod='gmerchantcenterpro'}</h3>
			<div class="clr_10"></div>

			<div class="alert alert-info">
				{l s='In rare cases, Google may refuse your review feed because one or several reviews contain words that are prohibited (according to Google). In this case, we offer you to indicate below the forbidden words in order NOT to export these specific reviews and to export all the others.' mod='gmerchantcenterpro'}
				<br /><br />
				{l s='Write the forbidden words by using coma as separator (ex: word1,word2,word3).' mod='gmerchantcenterpro'}&nbsp;<b>{l s='DO NOT' mod='gmerchantcenterpro'}</b>&nbsp;{l s='use spaces between words.' mod='gmerchantcenterpro'}
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='Write the forbidden words using coma as separator. DO NOT use spaces between words. Example : word1,word2,word3' mod='gmerchantcenterpro'}"><b>{l s='Forbidden words:' mod='gmerchantcenterpro'}</b></span></label>
				<div class="col-xs-4 col-md-4 col-lg-5">
					<textarea cols="20" rows="10" name="bt_words-review-forbidden">{if !empty($sForbiddenWords)}{$sForbiddenWords|escape:'htmlall':'UTF-8'}{/if}</textarea>
				</div>
				<span class="icon-question-sign label-tooltip" title="{l s='Write the forbidden words using coma as separator. DO NOT use spaces between words. Example : word1,word2,word3' mod='gmerchantcenterpro'}">&nbsp;</span>
			</div>

			<div class="clr_20"></div>
			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="center">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
						<div id="{$sModuleName|escape:'htmlall':'UTF-8'}Feed{$sDisplay|escape:'htmlall':'UTF-8'}Error"></div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
						<button class="btn btn-lg btn-success" onclick="oGmcPro.form('bt_advanced-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_advanced-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, oDiscountSettingsCallBack, 'Advanced', 'loadingAdvancedDiv');return false;"><i class="icon icon-plus"></i>&nbsp;{l s='Add' mod='gmerchantcenterpro'}</button>
					</div>
				</div>
			</div>
			{/if}

			<div id="{$sModuleName|escape:'htmlall':'UTF-8'}AdvancedError"></div>

			{* END - REVIEW FEED CONFIGURATION *}
		</form>
	</div>

	<div class="clr_20"></div>

	{literal}
	<script type="text/javascript">
		// initialize the list of elt to show and hide
		var aShow = [];
		var aHide = [];

		// manage each case from configuration to prepare the form
		if ($("input[type=radio][name=bt_option-name]:checked").val() == "true") {
			aShow.push('#bt_discount-name-group,#gmcp-example-info');
		} else {
			aHide.push('#bt_discount-name-group');
		}
		$("input[type=radio][name=bt_option-name]:checked").val() == "true" ? aShow.push('#bt_discount-name-group') : aHide.push('#bt_discount-name-group');
		$("input[type=radio][name=bt_option-min-amount]:checked").val() == "true" ? aShow.push('#bt_min-amount-group') : aHide.push('#bt_min-amount-group');
		$("input[type=radio][name=bt_option-value]:checked").val() == "true" ? aShow.push('#bt_value-group,#gmcp_info_value') : aHide.push('#bt_value-group,#gmcp_info_value');
		$("input[type=radio][name=bt_option-type]:checked").val() == "true" ? aShow.push('#bt_discount-type-group') : aHide.push('#bt_discount-type-group');
		$("input[type=radio][name=bt_option-date]:checked").val() == "true" ? aShow.push('#bt_date-group') : aHide.push('#bt_date-group');
		$("input[type=radio][name=bt_option-type]:checked").val() == "true" ? aShow.push('#bt_discount-type-group-amount') : aHide.push('#bt_discount-type-group-amount');
		$("input[type=radio][name=bt_option-cumulable]:checked").val() == "true" ? aShow.push('#bt_discount-cumulable-group') : aHide.push('#bt_discount-cumulable-group');

		oGmcPro.initHide(aHide);
		oGmcPro.initShow(aShow);

		//manage event on the form
		$("input[type=radio][name=bt_option-name").change(function() {
			$("input[type=radio][name=bt_option-name]:checked").val() == "true" ? $('#bt_discount-name-group').slideDown() : $('#bt_discount-name-group').slideUp();
			$("input[type=radio][name=bt_option-name]:checked").val() == "false" ? $('#bt_discount-name').val("") : '';
		});
		$("input[type=radio][name=bt_option-date").change(function() {
			$("input[type=radio][name=bt_option-date]:checked").val() == "true" ? $('#bt_date-group').slideDown() : $('#bt_date-group').slideUp();
			$("input[type=radio][name=bt_option-date]:checked").val() == "false" ? $('#bt_discount-date-from').val("") : '';
			$("input[type=radio][name=bt_option-date]:checked").val() == "false" ? $('#bt_discount-date-to').val("") : '';
		});

		$("input[type=radio][name=bt_option-min-amount").change(function() {
			$("input[type=radio][name=bt_option-min-amount]:checked").val() == "true" ? $('#bt_min-amount-group').slideDown() : $('#bt_min-amount-group').slideUp();
			$("input[type=radio][name=bt_option-min-amount]:checked").val() == "false" ? $('#bt_discount-min-amount').val("") : '';
		});

		$("input[type=radio][name=bt_option-value").change(function() {
			$("input[type=radio][name=bt_option-value]:checked").val() == "true" ? $('#bt_value-group').slideDown() : $('#bt_value-group').slideUp();
			$("input[type=radio][name=bt_option-value]:checked").val() == "true" ? $('#gmcp_info_value').slideDown() : $('#gmcp_info_value').slideUp();
			$("input[type=radio][name=bt_option-value]:checked").val() == "false" ? $('#bt_discount-value-min').val("") : '';
			$("input[type=radio][name=bt_option-value]:checked").val() == "false" ? $('#bt_discount-value-max').val("") : '';
		});

		$("input[type=radio][name=bt_option-type").change(function() {
			$("input[type=radio][name=bt_option-type]:checked").val() == "true" ? $('#bt_discount-type-group').slideDown() : $('#bt_discount-type-group').slideUp();
			$("input[type=radio][name=bt_option-type]:checked").val() == "false" ? $('#bt_discount-currency_off').attr("checked", true) : '';
			$("input[type=radio][name=bt_option-type]:checked").val() == "true" ? $('#bt_discount-type-group-amount').slideDown() : $('#bt_discount-type-group-amount').slideUp();
			$("input[type=radio][name=bt_option-type]:checked").val() == "false" ? $('#bt_discount-amount_off').attr("checked", true) : '';
		});

		$("input[type=radio][name=bt_option-cumulable").change(function() {
			$("input[type=radio][name=bt_option-cumulable]:checked").val() == "true" ? $('#bt_discount-cumulable-group').slideDown() : $('#bt_discount-cumulable-group').slideUp();
			$("input[type=radio][name=bt_option-cumulable]:checked").val() == "false" ? $('#bt_discount-cumulable_off').attr("checked", true) : '';
		});

		$("input[type=radio][name=bt_discount-currency").change(function() {
			$("#bt_discount-amount_off").attr("checked", true);
		});

		$("input[type=radio][name=bt_discount-amount").change(function() {
			$("#bt_discount-currency_off").attr("checked", true);
		});

		$(".date-picker").datepicker({
			dateFormat: 'yy-mm-dd'
		});

		//bootstrap components init
			{/literal}{if !empty($bAjaxMode)}{literal}
		$('.label-tooltip, .help-tooltip').tooltip();
			{/literal}{/if}{literal}
	</script>
	{/literal}