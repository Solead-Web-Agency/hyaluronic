{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*
*}

{if $newWidgets and $publicKey}
<script>
window.GRCWidgetsConfig = {
    publicKey: '{$publicKey|escape:'htmlall':'UTF-8'}',
    lang: 'auto'
};
</script>
<script src="https://widgets.guaranteed-reviews.com/static/widgets.min.js"></script>
{else}

<link href="//fonts.googleapis.com/css?family=Open+Sans:600,400,400i|Oswald:700" rel="stylesheet" type="text/css" media="all">

{if $displayJSWidget} 
<script type="text/javascript">
    var agSiteId="{$shopID|escape:'htmlall':'UTF-8'}";
</script>
<script src="{$domain|escape:'htmlall':'UTF-8'}wp-content/plugins/ag-core/widgets/JsWidget.js" type="text/javascript"></script>
{/if}

{/if}

{if $customCSS}
<style>
    {$customCSS|escape:'htmlall':'UTF-8'}
</style>
{/if}