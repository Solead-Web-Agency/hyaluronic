{*
 * Copyright since 2007 Viva Wallet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@vivawallet.com so we can send you a copy immediately.
 *
 * @author    Viva Wallet <support@vivawallet.com>
 * @copyright Since 2007 Viva Wallet
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
<prestashop-accounts></prestashop-accounts>
<br>
<div class="tab-content">
	<div class="panel">
		<div class="row justify-content-center vivawalletsmartcheckout-header">
			<img src="{$module_dir|escape:'html':'UTF-8'}views/img/vw-logo.svg" class="col-xs-12 col-md-3 text-center" id="payment-logo" />
		</div>

		<hr />

		<div class="vivawalletsmartcheckout-content">
			<div class="row">
				<div class="col-md-8">
					<h5>{$display_name|escape:'html':'UTF-8'}</h5>
					<p><strong>{l s='Accept payments through Viva.com.' mod='vivawalletsmartcheckout'}</strong></p>
					<p>{l s='Accept multiple payment methods such as Apple Pay, Google Pay, Samsung Pay and PayPal, as well as local card schemes, local digital wallets, and alternative payment methods.' mod='vivawalletsmartcheckout'}</p>
				</div>
				<div class="col-md-4">
					<p>{l s='Follow the instructions from our ' mod='vivawalletsmartcheckout'}<a href="{$developer_portal_url|escape:'html':'UTF-8'}">{l s='Developer Portal' mod='vivawalletsmartcheckout'}.</a></p>
					<p><a href="{$register_account_url|escape:'html':'UTF-8'}" target="_blank" class="btn btn-primary" id="create-account-btn">{l s='Create your Viva.com  account now!' mod='vivawalletsmartcheckout'}</a><br />
						{l s='Already have an account?' mod='vivawalletsmartcheckout'}<a href="{$merchant_account_url|escape:'html':'UTF-8'}" target="_blank"> {l s='Log in' mod='vivawalletsmartcheckout'}</a></p>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}"></script>
<script>
	window?.psaccountsVue?.init();

	if(window.psaccountsVue.isOnboardingCompleted() != true)
	{
		document.getElementById("module-config").style.opacity = "0.5";
	}
</script>