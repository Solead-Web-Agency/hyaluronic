{*
* 2007-2020 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2020 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

<script type="text/javascript">
    const ETS_ADMIN_FD = "{$linkPsAdmin|escape:'quotes':'UTF-8'}";
    var ETS_TRANS_ENABLE_ANALYSIS = {if isset($enableAnalysis) && $enableAnalysis}1{else}0{/if};
    {if isset($langSourceDefault)}
        var etsTransLangSourceDefault = "{$langSourceDefault|escape:'html':'UTF-8'}";
    {/if}
    {if isset($langTargetInterTrans)}
        var etsTransLangTargetInterTrans = "{$langTargetInterTrans|escape:'html':'UTF-8'}";
    {/if}
    {if isset($transJs)}
        var etsTransText = {$transJs|@json_encode nofilter};
    {/if}
        var etsTransPageType = "{if isset($pageType) && $pageType}{$pageType|escape:'html':'UTF-8'}{/if}";
        var etsTransPageId = "{if isset($pageId) && $pageId}{$pageId|escape:'html':'UTF-8'}{/if}";
        var etsTransIsDetailPage = {if isset($isDetailPage) && $isDetailPage}1{else}0{/if};
        const ETS_TRANS_LINK_AJAX = "{if isset($linkAjaxBo)}{$linkAjaxBo|escape:'quotes':'UTF-8'}{/if}";
    {if isset($linkAjaxModule)}
        const ETS_TRANS_LINK_AJAX_MODULE = "{$linkAjaxModule|escape:'quotes':'UTF-8'}";
    {/if}
    {if isset($hasModuleSeo)}
        const ETS_TRANS_HAS_MODULE_SEO = "{if $hasModuleSeo}1{else}0{/if}";
    {/if}
    var ETS_TRANS_IS_AUTO_CONFIG = {if isset($isAutoConfigEnabled) && $isAutoConfigEnabled}1{else}0{/if};
    var ETS_TRANS_RATE_GOOGLE = "{$rateGoogleVal|escape:'html':'UTF-8'}";
    {if isset($defaultTransConfig)}
    var ETS_TRANS_DEFAULT_CONFIG = {$defaultTransConfig|@json_encode nofilter};
    {/if}
    {if isset($rateGoogleSuffix)}
    var ETS_TRANS_RATE_GOOGLE_SUFFIX = "{$rateGoogleSuffix|escape:'html':'UTF-8'}";
    {/if}
    const ETS_TRANS_AUTO_DETECT_LANG = {if isset($autoDetectLanguage) && $autoDetectLanguage}1{else}0{/if};
    const ETS_TRANS_AUTO_GENERATE_LINK_REWRITE = {if isset($enableAutoGenerateLinkRewrite) && $enableAutoGenerateLinkRewrite}1{else}0{/if};
    const ETS_TRANS_ENABLE_TRANS_FIELD = {if isset($ETS_TRANS_ENABLE_TRANS_FIELD) && $ETS_TRANS_ENABLE_TRANS_FIELD}1{else}0{/if};
    var etsAllowAccessChar = {if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}1{else}0{/if};
    {literal}
        if(typeof PS_ALLOW_ACCENTED_CHARS_URL == 'undefined')
            var PS_ALLOW_ACCENTED_CHARS_URL = etsAllowAccessChar;
    {/literal}
</script>
{if isset($linkJsSimulate) && $linkJsSimulate}
    <script type="text/javascript" src="{$linkJsSimulate|escape:'quotes':'UTF-8'}" defer="defer"></script>
{/if}
{if isset($linkJsCommon) && $linkJsCommon}
    <script type="text/javascript" src="{$linkJsCommon|escape:'quotes':'UTF-8'}" defer="defer"></script>
{/if}
{if isset($linkJsConfig) && $linkJsConfig}
    <script type="text/javascript" src="{$linkJsConfig|escape:'quotes':'UTF-8'}" defer="defer"></script>
{/if}
{if isset($linkJsPages) && $linkJsPages}
    <script type="text/javascript" src="{$linkJsPages|escape:'quotes':'UTF-8'}" defer="defer"></script>
{/if}
{if isset($linkJsBo) && $linkJsBo}
    <script type="text/javascript" src="{$linkJsBo|escape:'quotes':'UTF-8'}" defer="defer"></script>
{/if}
{if isset($linkJsInterTrans) && $linkJsInterTrans}
    <script type="text/javascript" src="{$linkJsInterTrans|escape:'quotes':'UTF-8'}" defer="defer"></script>
{/if}
{if isset($jsTransMegamenu) && $jsTransMegamenu}
    <script type="text/javascript" src="{$jsTransMegamenu|escape:'quotes':'UTF-8'}" defer="defer"></script>
{/if}
