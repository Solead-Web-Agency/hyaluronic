{*
* 2003-2018 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2018 Business Tech SARL
*}
{if $bVersion16 ==false}
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GACT_URL_CSS|escape:'htmlall':'UTF-8'}admin-15-14.css" />
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GACT_URL_CSS|escape:'htmlall':'UTF-8'}admin-theme.css" />
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GACT_URL_CSS|escape:'htmlall':'UTF-8'}bootstrap-theme.min.css" />
	<script type="text/javascript" src="{$smarty.const._GACT_URL_JS|escape:'htmlall':'UTF-8'}bootstrap.min.js"></script>
{/if}
<link rel="stylesheet" type="text/css" href="{$smarty.const._GACT_URL_CSS|escape:'htmlall':'UTF-8'}admin.css">

<script type="text/javascript" src="{$smarty.const._GACT_URL_JS|escape:'htmlall':'UTF-8'}module.js"></script>
<script type="text/javascript">
	// instantiate object
	var oGact = oGact || new GactModule('{$sModuleName|escape:'htmlall':'UTF-8'}');

	// get errors translation
	oGact.msgs = {$oJsTranslatedMsg};

	{if isset($iCompare) && $iCompare == -1}oGact.oldVersion = true;{/if}

	// set URL of admin img
	oGact.sImgUrl = '{$smarty.const._GACT_URL_IMG|escape:'htmlall':'UTF-8'}';

	{if !empty($sModuleURI)}
		// set URL of module's web service
	oGact.sWebService = '{$sModuleURI}';
	{/if}
</script>