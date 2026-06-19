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
<link rel="stylesheet" type="text/css" href="{$smarty.const._GMCP_URL_CSS|escape:'htmlall':'UTF-8'}admin.css">
<link rel="stylesheet" type="text/css" href="{$smarty.const._GMCP_URL_CSS|escape:'htmlall':'UTF-8'}top.css">
<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<script type="text/javascript" src="{$smarty.const._GMCP_URL_JS|escape:'htmlall':'UTF-8'}jquery.tablesorter.js"></script>
<script type="text/javascript" src="{$smarty.const._GMCP_URL_JS|escape:'htmlall':'UTF-8'}jquery-ui-1.11.4.min.js"></script>

<script type="text/javascript" src="{$autocmp_js|escape:'htmlall':'UTF-8'}"></script>
<link rel="stylesheet" type="text/css" href="{$autocmp_css|escape:'htmlall':'UTF-8'}" />
<script type="text/javascript" src="{$smarty.const._GMCP_URL_JS|escape:'htmlall':'UTF-8'}module.js"></script>
<script type="text/javascript" src="{$smarty.const._GMCP_URL_JS|escape:'htmlall':'UTF-8'}custom_label.js"></script>
<script type="text/javascript" src="{$smarty.const._GMCP_URL_JS|escape:'htmlall':'UTF-8'}feature_by_cat.js"></script>
<script type="text/javascript" src="{$smarty.const._GMCP_URL_JS|escape:'htmlall':'UTF-8'}feedList.js"></script>
<script type="text/javascript" src="{$smarty.const._GMCP_URL_JS|escape:'htmlall':'UTF-8'}top.js"></script>

<script type="text/javascript">
	// instantiate object
	var oGmcPro = oGmcPro || new GmcPro('{$sModuleName|escape:'htmlall':'UTF-8'}');
	var oGmcProLabel = oGmcProLabel || new GmcProCustomLabel('{$sModuleName|escape:'htmlall':'UTF-8'}');
	var oGmcProFeatureByCat = oGmcProFeatureByCat || new GmcProFeatureByCat('{$sModuleName|escape:'htmlall':'UTF-8'}');
	var oGmcProFeedList = oGmcProFeedList || new GmcProFeedList('{$sModuleName|escape:'htmlall':'UTF-8'}');
	var oBtUpdateStep = oBtUpdateStep || new btHeaderBar('{$sModuleName|escape:'htmlall':'UTF-8'}');


	// get errors translation
	oGmcPro.msgs = {$oJsTranslatedMsg};

	// set URL of admin img
	oGmcPro.sImgUrl = '{$smarty.const._GMCP_URL_IMG|escape:'htmlall':'UTF-8'}';

	{if !empty($sModuleURI)}
	// set URL of module's web service
	oGmcPro.sWebService = '{$sModuleURI|escape:'htmlall':'UTF-8'}';
	{/if}
</script>


