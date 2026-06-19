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

{if !empty($iGoogleId)}
<!-- START - Google Remarketing + Dynamic - remarketing Code -->
{literal}
<script type="text/javascript" data-keepinline="true" async src="https://www.googletagmanager.com/gtag/js?id={/literal}{$iGoogleId}{literal}"></script>
<script type="text/javascript" data-keepinline="true">
    window.dataLayer = window.dataLayer || [];

    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    {/literal}
    {* Use case - dynamic tags empty or deactivate we do just a page view *}
    {if empty($aDynTags)}
    {literal}
    gtag('config', '{/literal}{$iGoogleId}{literal}', {'send_page_view': false});
    {/literal}
    {* Use case - Dynamic tags activated and values not empty *}
    {else}
    {literal}
    gtag('config', '{/literal}{$iGoogleId}{literal}');
    gtag('event', 'page_view', {
        'send_to': '{/literal}{$iGoogleId}{literal}',{/literal}
        {foreach from=$aDynTags item=tag key=key name=tags}
        {$tag.label nofilter}: {$tag.value nofilter}{if $smarty.foreach.tags.last == false},{$sCR}{/if}
        {/foreach}
        {literal}
    });
    {/literal}
    {/if}
    {literal}
</script>
{/literal}
<!-- END - Google Remarketing + Dynamic - remarketing Code -->
{/if}