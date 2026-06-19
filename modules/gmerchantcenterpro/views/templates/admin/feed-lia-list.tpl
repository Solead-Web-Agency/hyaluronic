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


<div class="bootstrap" id="gmcp">
    <h3 class="subtitle"><i class="fa fa-shopping-cart"></i>&nbsp;{l s='Local product inventory data feed' mod='gmerchantcenterpro'}</h3>
    <div class="clr_10"></div>

    {* USE CASE - AVAILABLE FEED FILE LIST *}
    {if !empty($aFlyFileListLocal)}
        <div class="alert alert-info form-group">
            {l s='Please follow our FAQ to know' mod='gmerchantcenterpro'}&nbsp;&nbsp;<a class="badge badge-info" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/457" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='how to add the inventory feed in Google Merchant Center' mod='gmerchantcenterpro'}</a>
        </div>
        <div class="clr_5"></div>

        <table border="0" cellpadding="2" cellspacing="2" class="table ">
            <tr class="bt_tr_header text-center">
                <th class="center">{l s='Country' mod='gmerchantcenterpro'}</th>
                <th class="center">{l s='Language ' mod='gmerchantcenterpro'}</th>
                <th class="center">{l s='Currency' mod='gmerchantcenterpro'}</th>
                <th class="center"></th>
            </tr>
            {foreach from=$aFlyFileListLocal name=feed key=iKey item=aFlyFeed}
                <tr>
                    <td class="center">{$aFlyFeed.countryName|escape:'htmlall':'UTF-8'} - {$aFlyFeed.countryIso|escape:'htmlall':'UTF-8'}</td>
                    <td class="center">{$aFlyFeed.langName|escape:'htmlall':'UTF-8'} - {$aFlyFeed.iso_code|strtoupper|escape:'htmlall':'UTF-8'}</td>
                    <td class="center">{$aFlyFeed.currencySign|escape:'htmlall':'UTF-8'} - {$aFlyFeed.currencyIso|escape:'htmlall':'UTF-8'}</td>
                    <td class="center">
                        <a class="label-tooltip btn btn-default btn-md" title="{l s='See' mod='gmerchantcenterpro'}" target="_blank" href="{$aFlyFeed.link|escape:'htmlall':'UTF-8'}"><i class="fa fa-eye"></i></a>
                        <a type="button" class="label-tooltip btn btn-md btn-default btn-copy js-tooltip js-copy" title="{l s='Copy' mod='gmerchantcenterpro'}" data-toggle="tooltip" data-placement="bottom" data-copy="{$aFlyFeed.link|escape:'htmlall':'UTF-8'}">&nbsp;<i class="fa fa-copy"></i></a>
                    </td>
                </tr>
            {/foreach}
        </table>
    {* USE CASE - NO AVAILABLE LANGUAGE : CURRENCY : COUNTRY *}
    {else}
        <div class="alert alert-warning">
            {l s='There are no files because of invalid languages/currencies/countries according to Google\'s requirements.' mod='gmerchantcenterpro'}.
        </div>
    {/if}
</div>

{literal}
    <script type="text/javascript">
        $('.js-copy').click(function() {
                    var text = $(this).attr('data-copy');
                    var el = $(this);
                    oGmcPro.copyToClipboard(text, el);
                });
    </script>
{/literal}