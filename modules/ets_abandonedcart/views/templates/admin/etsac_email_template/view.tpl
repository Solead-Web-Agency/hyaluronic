{*
* 2007-2022 ETS-Soft
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
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="bootstrap">
    <div class="panel">
        <div class="panel-heading ss3">
            <span>{l s='Preview template' mod='ets_abandonedcart'}</span>
        </div>
        <div class="form-wrapper">
            <div class="form-group" style="display: none;">
                <textarea id="view_email_content" name="view_email_content">{$content nofilter}</textarea>
            </div>
            <div class="form-group">
                <div class="ets_abancart_preview view_email_template" data-type="{$templateType|escape:'html':'UTF-8'}"></div>
                {if isset($smarty.get.id_ets_abancart_email_template) && in_array($smarty.get.id_ets_abancart_email_template,array(1,2,3,4))}
                    <div class="alert alert-info">{l s='This is default email template, it cannot be modified but you can duplicate it from the listing page then make your modification' mod='ets_abandonedcart'}</div>
                {/if}
            </div>
        </div>
        <div class="panel-footer">
            <a href="{$processBackToList|escape:'html':'UTF-8'}" class="btn btn-default"><i class="process-icon-back"></i> {l s='Back to list' mod='ets_abandonedcart'}</a>
            <button class="btn btn-default pull-right js-ets-ac-duplicate-email-temp" type="button" data-href="{$duplicateLink nofilter}">
                <i class="process-icon-svg svg_fill_gray svg_fill_hover_white">
                    <svg class="w_24 h_24" width="24" height="24" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1696 384q40 0 68 28t28 68v1216q0 40-28 68t-68 28h-960q-40 0-68-28t-28-68v-288h-544q-40 0-68-28t-28-68v-672q0-40 20-88t48-76l408-408q28-28 76-48t88-20h416q40 0 68 28t28 68v328q68-40 128-40h416zm-544 213l-299 299h299v-299zm-640-384l-299 299h299v-299zm196 647l316-316v-416h-384v416q0 40-28 68t-68 28h-416v640h512v-256q0-40 20-88t48-76zm956 804v-1152h-384v416q0 40-28 68t-68 28h-416v640h896z"/></svg>
                </i> {l s='Duplicate' mod='ets_abandonedcart'}
            </button>
        </div>
    </div>
</div>