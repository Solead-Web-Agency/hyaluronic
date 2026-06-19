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

<div id="boxModalEtsSeoTransExplain">
    <div class="ets-modal" id="modalEtsSeoTransExplain">
        <div class="ets-modal-content">
            <div class="ets-modal-header">
                <span class="ets-modal-close" title="{l s='Close' mod='ets_seo'}">&times;</span>
                <h2 class="ets-modal-title"></h2>
            </div>
            <div class="ets-modal-body" data-page-title="{if isset($page_title_trans)}{$page_title_trans|escape:'html':'UTF-8'}{else}{l s='Page title' mod='ets_seo'}{/if}">
                {if isset($message_explain) }
                    {foreach $message_explain as $k=>$msg}
                        <div class="rule-msg hide {$k|escape:'html':'UTF-8'}"><p>{$msg|escape:'html':'UTF-8'}</p></div>
                    {/foreach}
                {/if}
            </div>
        </div>
    </div>
</div>
