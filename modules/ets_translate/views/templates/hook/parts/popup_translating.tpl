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

<div id="etsTransPopupTranslating" class="hide">
    <div class="">
        <div class="text-initialize hide">{l s='Initializing...' mod='ets_translate'}</div>
        <div class="text-loading">
            <span>{l s='Translating...' mod='ets_translate'} {l s='Please wait' mod='ets_translate'}</span>
        </div>
        <div class="text-completed hide alert alert-success">{l s='Translate completed' mod='ets_translate'}</div>
        <div class="msg-box-info">
            <div class="msg-translated">
                <span>{l s='Results' mod='ets_translate'}:</span>
                <span class="nb_translated">0</span> <span class="suffix_total_translate"> / <span class="total_translate">0</span></span>
                <span class="page_name">--</span>
                (<span class="nb_char">0</span> {l s='characters' mod='ets_translate'})
            </div>
            <div class="msg-estimated-money {if isset($isConfigGoogleRate) && $isConfigGoogleRate}{else}hide{/if}">
                <span>{l s='Estimated spent money' mod='ets_translate'}:</span>
                <span class="nb_money">--</span>
            </div>
            <div class="for-trans-all-website hide">
                <div class="percentage-trans">
                    <span class="nb_percentage">0</span>%
                </div>
                <div class="file-data-translated">
                    <div class="label-path-trans">{l s='Translating' mod='ets_translate'}:</div>
                    <div class="file-path-trans">
                        <div class="list_filepath"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>