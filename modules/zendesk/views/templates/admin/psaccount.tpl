{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *}
<link rel="stylesheet" type="text/css" media="all" href="../modules/zendesk/views/css/fonts.css" />
<link rel="stylesheet" type="text/css" media="all" href="../modules/zendesk/views/css/bootstrap-admin-configuration.css" />
<link rel="stylesheet" type="text/css" media="all" href="../modules/zendesk/views/css/cloudfront.css" />
<link rel="stylesheet" type="text/css" media="all" href="../modules/zendesk/views/css/screen.css?v=3" />
<script src="../modules/zendesk/views/js/script.js"></script>

{assign var="isThereMesssages" value=false}
{foreach $messages as $oneTypeMessage => $arrayMessages}
	{if count($arrayMessages) > 0}
		{assign var="isThereMesssages" value=true}
		{assign var="countMessages" value={count($arrayMessages)}}
		<div class="alert alert-{$oneTypeMessage|escape:'htmlall':'UTF-8'}">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<ol>
				{foreach $arrayMessages as $oneMessage}
					<li>{$oneMessage|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ol>
		</div>
	{/if}
{/foreach}
<div class="card main-zendesk"{if $isThereMesssages == true} style="margin-top:10px;"{/if}>
 <h3 class="card-header">{l s='Zendesk connector V' mod='zendesk'}{$moduleVersion}</h3>
 <div class="card-body card-body-zendeskv2">
  <div class="card">
   <div class="card-body">
		<ul>
			<li>{l s='MultiShop support' mod='zendesk'}</li>
			<li>{l s='Ability to configure Zendesk widget preloading on front office' mod='zendesk'}</li>
			<li>{l s='Shipping tracking code is now available in Zendesk App' mod='zendesk'}</li>
		</ul>
   </div>
  </div>
  <div class="card">
    <h3 class="card-header">{l s='Price' mod='zendesk'}</h3>
    <div class="card-body">
      <p class="mb-2">
	  	{if isset($subscriptionMessage[0])} 
			{$subscriptionMessage[0]|escape:'htmlall':'UTF-8'}
		{/if}
	  </p>
      <div class="alert alert-info" role="alert">
        <p class="alert-text">
			{l s='PrestaShop app inside Zendesk will stop working for all none subscribed companies by october 15th' mod='zendesk'}
		</p>
      </div>
    </div>
    </div>
    <div class="card">
      <h3 class="card-header">
	  	{l s='Option 1 : subscription with Built For PrestaShop' mod='zendesk'}
	  </h3>
      <div class="card-body">
	  	{if $isModuleConfigured == true}
			{if $psMboDepenciesOk == false}
				<p class="mb-0">
					{l s='To subscribe via PrestaShop Account, you need to install the module and link your PrestaShop account to your store.' mod='zendesk'}
				</p>
				{if $isPs17orMore === true}
					<!-- Load cdc library -->
					<script src="https://assets.prestashop3.com/dst/mbo/v1/mbo-cdc-dependencies-resolver.umd.js"></script>
				
					<!-- cdc container -->
					<div id="cdc-container"></div>
				
					<script defer>
					const renderMboCdcDependencyResolver = window.mboCdcDependencyResolver.render
					const context = {
						...{$dependencies|json_encode},
						onDependenciesResolved: () => location.reload(),
						onDependencyResolved: (dependencyData) => console.log('Dependency installed', dependencyData), // name, displayName, version
						onDependencyFailed: (dependencyData) => console.log('Failed to install dependency', dependencyData),
						onDependenciesFailed: () => console.log('There are some errors'),
					}
					renderMboCdcDependencyResolver(context, '#cdc-container')
					</script>
				{/if}
			{else}
				{if (bool) $subscriptionDoneWithOtherShop === false}
					{if $psAccountInstalled}
						<div id="psaccount">
							<prestashop-accounts></prestashop-accounts>
							<div id="ps-billing"></div>
							<div id="ps-modal"></div>
						</div>
					{/if}
					<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>
					<script src="{$urlBilling|escape:'htmlall':'UTF-8'}" rel=preload></script>
				{else}
					<div class="alert alert-success" role="alert">
						<p class="alert-text">
						{l s='Your subscription has been taken into account. If you wish to update it, please go to the store context n°%s' sprintf=[$subscriptionShop|escape:'htmlall':'UTF-8'] mod='zendesk'}
						</p>
					</div>
				{/if}
			{/if}
		{else}
			{l s='Please achieve first the module\'s configuration below, then choose a plan here.' mod='zendesk'}
		{/if}
      </div>
    </div>
    <div class="card">
      <h3 class="card-header">
	  	{l s='Option 2 : Direct subscription (SEPA debit)' mod='zendesk'}
	  </h3>
      <div class="card-body">
        <p class="mb-0">
			{l s='Please complete this ' mod='zendesk'}
			<a target="_blank" href="https://pay.gocardless.com/BRT0002EP23123S">{l s='SEPA direct debit mandate online.' mod='zendesk'}</a><br>
			{l s='Once mandate is completed, your subscription for this eShop will be active without further action' mod='zendesk'}
		</p>
      </div>
    </div>
    </div>
</div>

<script>
	var initZenDeskAccountDone = false;
	document.onreadystatechange = function() {
		try {
			initPsAccount();
		} catch (error) {
			console.log(error);
		}
	};
    /*********************
    * PrestaShop Account *
    * *******************/
	function initPsAccount()
	{
		if (initZenDeskAccountDone === true) {
			return true;
		}
		initZenDeskAccountDone = true;
		window?.psaccountsVue?.init();

		// Check if Account is associated before displaying Billing component
		if(window.psaccountsVue && window.psaccountsVue.isOnboardingCompleted() == true)
		{
			initPSBilling();
		}
	}

    function initPSBilling()
    {
		if (!window.psBilling) {
			return true;
		}
		/*********************
		* PrestaShop Billing *
		* *******************/
		window.psBilling.initialize(window.psBillingContext.context, '#ps-billing', '#ps-modal', (type, data) => {
				// Event hook listener
				switch (type) {
			// Hook triggered when the subscription is created
					case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_CREATED:
						console.log('subscription created', data);
						break;
			// Hook when the subscription is updated
					case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_UPDATED:
						console.log('subscription updated', data);
						break;
			// Hook triggered when the subscription is cancelled
					case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_CANCELLED:
						console.log('subscription cancelled', data);
						break;
				}
		});
    }
</script>
