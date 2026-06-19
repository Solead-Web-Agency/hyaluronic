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

<div  id="etsTransPopupAnalysisCompleted">
    <form>
        <input type="hidden" name="trans_source" value="">
        <input type="hidden" name="trans_target" value="">
        <input type="hidden" name="trans_option" value="">
        <input type="hidden" name="mail_option" value="">
        <input type="hidden" name="trans_wd" value="">
        <div class="nothing-to-translate hide">
            <div class="alert alert-info">{l s='No content to translated. All content was translated' mod='ets_translate'}</div>
        </div>
        <div class="info-analysis">
            <p>{l s='You are going to translate' mod='ets_translate'} <span class="nb_text"></span> <span class="text_type">{l s='texts' mod='ets_translate'}</span> (<span class="nb_char"></span> {l s='characters' mod='ets_translate'})</p>
            <p class="{if isset($isConfigGoogleRate) && $isConfigGoogleRate}{else}hide{/if}">{l s='Estimated price:' mod='ets_translate'} <span class="nb_money"></span></p>
        </div>
    </form>
</div>