
<div class="card main-zendesk">
	<div class="" style="margin-top:50px;">
		{if $psAccountInstalled == true}
			<div class="panel">
				<div class="panel-body">
					<div id="ps-billing-invoice"></div>
				</div>
			</div>
			<script src="{$urlBilling|escape:'htmlall':'UTF-8'}" rel=preload></script>
			<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>
		{else}
			{l s='To subscribe via PrestaShop Account, you need to install the module and link your PrestaShop account to your store.' mod='zendesk'}
		{/if}
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
	function initPsAccount() {
		if (initZenDeskAccountDone === true) {
			return true;
		}
		initZenDeskAccountDone = true;
		console.log(window)
		window?.psaccountsVue?.init();

		// Check if Account is associated before displaying Billing component
		if (window.psaccountsVue && window.psaccountsVue.isOnboardingCompleted() == true) {
			initPSBilling();
		}
	}
	function initPSBilling() {
		if (!window.psBilling) {
			return true;
		}
	/*********************
	 * PrestaShop Billing *
	 * *******************/
		window.psBilling.initializeInvoiceList(
			window.psBillingContext.context,
			"#ps-billing-invoice"
		);
	}
</script>