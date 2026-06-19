{*
*
* Google Dynamic Remarketing
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

<div class="form-horizontal">
	<h3><i class="icon-check-circle"></i>&nbsp;{l s='Technical check'  mod='gremarketing'}</h3>

	<div class="form-group">
		<div class="col-xs-12 col-md-5 col-lg-12">
			<div class="alert alert-info">
				{l s='When checking your Google Remarketing code with Google\'s Tag Assistant extension on your Google Chrome browser (the download link to that tool is in the Help / FAQ tab), the Tag Assistant may display minor warnings or suggestions.' mod='gremarketing'}
				{l s='None of these warnings or suggestions actually matter, and you may ignore them safely, as they will not prevent your Remarketing from working correctly. For more information, please visit our FAQ on http://faq.businesstech.fr/faq.php?id=78&lg=en' mod='gremarketing'}
			</div>
		</div>
	</div>

	<div class="clr_20"></div>

	<div class="form-group">
		<label class="control-label col-xs-12 col-md-3 col-lg-3"><b>{l s='Google Merchant Center installed or activated ?' mod='gremarketing'}</b></label>
		<div class="col-xs-12 col-md-5 col-lg-3">
			{if empty($oModuleGoogle)}
			<div class="alert alert-info">
				{l s='Our Google Merchant Center module is not installed or not activated. For optimum results in your Dynamic Remarketing campaigns, you will want to associate it with our Google Merchant Center module' mod='gremarketing'}
			</div>
			{else}
			<div class="alert alert-success">
				{l s='Our Google Merchant Center module is valid' mod='gremarketing'}
			</div>
			{/if}
		</div>
	</div>

	{if !empty($bWrongModuleGoogleVersion)}
	<div class="clr_20"></div>
	<div class="form-group">
		<label class="control-label col-xs-12 col-md-3 col-lg-3"><b>{l s='Remarketing Dynamic and Merchant Center compatibility' mod='gremarketing'} ?</b></label>
		<div class="col-xs-12 col-md-5 col-lg-9">
			<div class="alert alert-warning">
				{l s='Your Google merchant center version is lower than 4.7.11 for the classic version or lower than 1.6.14  for the PRO version and you export products with the option "Export each combination as a product in its own right", then Remarketing Dynmic tag couldn\'t take into account the product\'s combination pages according to the way PrestaShop works. The combination ID cannot be detected in the URL, and so let our Remarketing module display the matching combination information. Finally you should set the option to "Export all combinations in a single product"' mod='gremarketing'}.
			</div>
		</div>
	</div>
	{/if}
</div>