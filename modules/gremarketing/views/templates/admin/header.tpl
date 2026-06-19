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

<link rel="stylesheet" type="text/css" href="{$smarty.const._GR_URL_CSS|escape:'htmlall':'UTF-8'}admin.css">
<link rel="stylesheet" type="text/css" href="{$smarty.const._GR_URL_CSS|escape:'htmlall':'UTF-8'}font-awesome.css">


{if empty($bCompare16)}
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GR_URL_CSS|escape:'htmlall':'UTF-8'}admin-theme.css">
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GR_URL_CSS|escape:'htmlall':'UTF-8'}admin-15-14.css">
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GR_URL_CSS|escape:'htmlall':'UTF-8'}bootstrap-theme.min.css">
	<script type="text/javascript" src="{$smarty.const._GR_URL_JS|escape:'htmlall':'UTF-8'}jquery-1.11.0.min.js"></script>
	<script type="text/javascript" src="{$smarty.const._GR_URL_JS|escape:'htmlall':'UTF-8'}bootstrap.min.js"></script>
{/if}

<script type="text/javascript" src="{$smarty.const._GR_URL_JS|escape:'htmlall':'UTF-8'}module.js"></script>
<script type="text/javascript">
	// instantiate object
	var oGr = oGr || new GrModule('{$sModuleName|escape:'htmlall':'UTF-8'}');

	// get errors translation
	oGr.msgs = {$oJsTranslatedMsg};

	{if empty($iCompare15)}oGr.oldVersion = true;{/if}

	// set URL of admin img
	oGr.sImgUrl = '{$smarty.const._GR_URL_IMG}';

	{if !empty($sModuleURI)}
	// set URL of module's web service
	oGr.sWebService = '{$sModuleURI}';
	{/if}
</script>