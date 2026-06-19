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

<div id="onboarding" class="account panel">
	<div class="row" id="onboarding-desc">
		<div class="col-xs-12" id="serviceDesc">
			<h2>{l s='Discover Merchant Center with Actions' mod='gmerchantcenterpro'}</h2>
			<h4>{l s='Manage your "Buy on Google" orders directly from your PrestaShop\'s back office!' mod='gmerchantcenterpro'}</h4>
			<p>
				{l s='Create an account, subscribe and then go back here to link your subscription and configure the feature.' mod='gmerchantcenterpro'}
			</p>
			<p>
				{l s='At any moment, do not hesitate to refer to' mod='gmerchantcenterpro'}&nbsp;
				<strong><a href="{$smarty.const._GMCP_GSA_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/334" target="_blank">{l s='our FAQ' mod='gmerchantcenterpro'}</a></strong>&nbsp;
				{l s='to best configure our service.' mod='gmerchantcenterpro'}
			</p>
		</div>
	</div>

	<div class="clr_10"></div>
	<div class="row">
		<p id="discoverContainer" class="text-center mt-5 col-xs-12 col-lg-4">
			<a id="discoverButton" class="btn btn-lg" href="{$sWebsiteDiscover}" target="_blank">
				<i class="fa fa-search"></i>&nbsp;{l s='Discover' mod='gmerchantcenterpro'}
			</a>
		</p>
		<p id="createNewAccountContainer" class="mt-5 col-xs-12 col-lg-4 text-center">
			<a id="createNewAccount" class="btn btn-lg" target="blank" href="{$sApiUrlRegister}">
				<i class="fa fa-sign-in"></i>&nbsp;{l s='Create an account' mod='gmerchantcenterpro'}
			</a>
		</p>
		<p id="LoginContainer" class="mt-5 col-xs-12 col-lg-4 text-center">
			<a id="login" class="btn btn-lg" onclick="oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.gsaOauth.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.gsaOauth.type|escape:'htmlall':'UTF-8'}', 'bt_gsa-settings', 'bt_gsa-settings', null, null, 'oauthLoadingDiv');">
				<i class="fa fa-user"></i>&nbsp;{l s='I already have an account' mod='gmerchantcenterpro'}
			</a>
		</p>
	</div>
</div>

	<div class="clr_20"></div>

	<div class="col-xs-12">
		<div id="onboarding" class="panel discover">
		<div class="col-xs-12 col-lg-12 text-center">
			<h4>{l s='What are the advantages of selling on the "Buy on Google" marketplace?' mod='gmerchantcenterpro'}</h4>
		</div>
			<div class="clr_50"></div>
			<div class="col-xs-12 col-lg-3 text-center">
				<i class="fa fa-money gsa-icon-market" aria-hidden="true"></i>
				<div class="clr_10"></div>
				<p class="advantages-label">{l s='Promote at lower cost' mod='gmerchantcenterpro'}</p>
				<p>{l s='No subscription fees' mod='gmerchantcenterpro'}
					<br />{l s='Only commissions on sales' mod='gmerchantcenterpro'}
					<br />{l s='Commission rates lower than Amazon' mod='gmerchantcenterpro'}</p>
			</div>
			<div class="col-xs-12 col-lg-3 text-center">
				<i class="fa fa-desktop gsa-icon-market" aria-hidden="true"></i>
				<div class="clr_10"></div>
				<p class="advantages-label">{l s='Improve your visibility' mod='gmerchantcenterpro'}</p>
				<p>{l s='Products for purchase on Google Shopping' mod='gmerchantcenterpro'}
					<br />{l s='Products for purchase on Google Voice Assistant' mod='gmerchantcenterpro'}
					<br />{l s='Soon on all Google platforms' mod='gmerchantcenterpro'}</p>
			</div>
			<div class="col-xs-12 col-lg-3 text-center">
				<i class="fa fa-line-chart gsa-icon-market" aria-hidden="true"></i>
				<div class="clr_10"></div>
				<p class="advantages-label">{l s='Increase your sales' mod='gmerchantcenterpro'}</p>
				<p>{l s='People buy directly on Google' mod='gmerchantcenterpro'}
					<br />{l s='Payment info saved' mod='gmerchantcenterpro'}
					<br />{l s='Login info saved' mod='gmerchantcenterpro'}</p>
			</div>
			<div class="col-xs-12 col-lg-3 text-center">
				<i class="fa fa-handshake-o gsa-icon-market" aria-hidden="true"></i>
				<div class="clr_10"></div>
				<p class="advantages-label">{l s='Boost loyalty' mod='gmerchantcenterpro'}</p>
				<p>{l s='Simple, secure and fast buying' mod='gmerchantcenterpro'}
					<br />{l s='Google Purchase Warranties' mod='gmerchantcenterpro'}
					<br />{l s='Visibility that increases with purchases number' mod='gmerchantcenterpro'}</p>
			</div>
			<div class="clr_50"></div>
			{l s='(*)The "Buy on Google" marketplace is not available in all countries. To sell on this marketplace, you must therefore meet' mod='gmerchantcenterpro'}&nbsp;
			<a href="https://support.google.com/merchants/answer/7159729" target="_blank">{l s='several participation criteria' mod='gmerchantcenterpro'}</a>.
		</div>

	</div>

	<div id="oauthLoadingDiv" style="display: none;">
		<div class="alert alert-info">
			<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p>
			<div class="clr_20"></div>
			<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
		</div>
	</div>