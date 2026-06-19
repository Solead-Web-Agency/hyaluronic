{*
* 2007-2021 ETS-Soft
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
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{if isset($smarty.get.fc) && $smarty.get.fc == 'module' && isset($smarty.get.module) && $smarty.get.module =='prestablog' && isset($smarty.get.controller) && $smarty.get.controller =='blog'}
{else}
    {if $seo_social}
        {if isset($seo_social.facebook_og) && $seo_social.facebook_og}
            <meta property="og:url"                content="{$seo_social.url|escape:'quotes'}" />
            <meta property="og:title"              content="{$seo_social.title|escape:'html':'UTF-8'}" />
            <meta property="og:description"        content="{$seo_social.desc|escape:'html':'UTF-8'}" />
            <meta property="og:image"              content="{$seo_social.image|escape:'quotes'}" />
            <meta property="og:type" content="article" />
            {if $seo_social.facebook_page}
            <meta property="article:publisher" content="{$seo_social.facebook_page|escape:'quotes'}" />
            {/if}
        {/if}
        {if isset($seo_social.twitter_card) && $seo_social.twitter_card}
            <meta name="twitter:title" content="{$seo_social.title|escape:'html':'UTF-8'}">
            <meta name="twitter:description" content="{$seo_social.desc|escape:'html':'UTF-8'}">
            <meta name="twitter:image" content="{$seo_social.image|escape:'quotes'}">
            <meta name="twitter:card" content="{if $seo_social.twitter_card_type}{$seo_social.twitter_card_type|escape:'html':'UTF-8'}{else}summary{/if}">
            {if $seo_social.twitter_name}
                <meta name="twitter:site" content="@{$seo_social.twitter_name|escape:'html':'UTF-8'}" />
                <meta name="twitter:creator" content="@{$seo_social.twitter_name|escape:'html':'UTF-8'}" />
            {/if}
        {/if}
        {if !isset($seo_social.social_verified) || !$seo_social.social_verified}
            {if $seo_social.pinterest_verification}
                <meta name="p:domain_verify" content="{$seo_social.pinterest_verification|escape:'html':'UTF-8'}" />
            {/if}
            {if $seo_social.baidu_verification}
                <meta name="baidu-site-verification" content="{$seo_social.baidu_verification|escape:'html':'UTF-8'}" />
            {/if}
            {if $seo_social.bing_verification}
                <meta name="msvalidate.01" content="{$seo_social.bing_verification|escape:'html':'UTF-8'}" />
            {/if}
            {if $seo_social.google_verification}
                <meta name="google-site-verification" content="{$seo_social.google_verification|escape:'html':'UTF-8'}" />
            {/if}
            {if $seo_social.yandex_verification}
                <meta name="yandex-verification" content="{$seo_social.yandex_verification|escape:'html':'UTF-8'}" />
            {/if}
        {/if}
    {/if}
{/if}

{if $graph_knowledge}
<script type='application/ld+json' class='ets-seo-schema-graph--main'>
    {$graph_knowledge nofilter}
</script>
{/if}