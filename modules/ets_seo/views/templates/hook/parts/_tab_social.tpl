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

<div class="ets-seo-social-tab card ets_seotop1_step_seo">
    <div class="card-header">
        <h3 class="ets-seo-heading-analysis card-title">{l s="Social" mod='ets_seo'}</h3>
    </div>
    <div class="social-content card-block">

        <div class="form-group">
            <label>{$seo_data.social_title.label|escape:'html':'UTF-8'}</label>
            <div class="input-group locale-input-group d-flex ">
                {foreach from=$ets_seo_languages item='lang'}
                <div class="flex-fill multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                {if $lang.id_lang != $current_lang.id}
                    hide
                {/if}">
                    <input type="text"
                           class="form-control input-social-title input-social-title-il-{$lang.id_lang|escape:'html':'UTF-8'} input-social-title-ic-{$lang.iso_code|escape:'html':'UTF-8'}"
                           data-idlang="{$lang.id_lang|escape:'html':'UTF-8'}"
                           name="ets_seo_social_title[{$lang.id_lang|escape:'html':'UTF-8'}]"
                           id="ets_seo_social_title_{$lang.id_lang|escape:'html':'UTF-8'}"
                           value="{if isset($seo_data.social_title.value[$lang.id_lang])}{$seo_data.social_title.value[$lang.id_lang]|escape:'html':'UTF-8'}{/if}"
                           placeholder="{l s='Leave blank to use meta title' mod='ets_seo'}"
                    >
                    {if !$is_new_theme && count($ets_seo_languages) > 1}
                        {include file="./_btn_multilang.tpl"}
                    {/if}
                </div>
                {/foreach}
                {if $is_new_theme && count($ets_seo_languages) > 1}
                    {include file="./_btn_multilang.tpl"}
                {/if}
            </div>

        </div>
        <div class="form-group">
            <label>{$seo_data.social_desc.label|escape:'html':'UTF-8'}</label>
            <div class="input-group locale-input-group d-flex">
                {foreach from=$ets_seo_languages item='lang'}
                    <div class="flex-fill multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                {if $lang.id_lang != $current_lang.id}
                    hide
                {/if}">
                        <textarea
                                class="form-control input-social-desc input-social-desc-il-{$lang.id_lang|escape:'html':'UTF-8'} input-social-desc-ic-{$lang.iso_code|escape:'html':'UTF-8'}"
                                data-idlang="{$lang.id_lang|escape:'html':'UTF-8'}"
                                id="ets_seo_social_desc_{$lang.id_lang|escape:'html':'UTF-8'}"
                                name="ets_seo_social_desc[{$lang.id_lang|escape:'html':'UTF-8'}]"
                                placeholder="{l s='Leave blank to use meta description' mod='ets_seo'}"
                        >{if isset($seo_data.social_desc.value[$lang.id_lang])}{$seo_data.social_desc.value[$lang.id_lang]|escape:'html':'UTF-8'}{/if}</textarea>
                        {if !$is_new_theme && count($ets_seo_languages) > 1}
                            {include file="./_btn_multilang.tpl"}
                        {/if}
                    </div>
                {/foreach}
                {if $is_new_theme && count($ets_seo_languages) > 1}
                    {include file="./_btn_multilang.tpl"}
                {/if}
            </div>
        </div>
        <div class="form-group">
            <label>{$seo_data.social_img.label|escape:'html':'UTF-8'}</label>
            <div class="input-group locale-input-group d-flex">
                {foreach from=$ets_seo_languages item='lang'}
                    <div class="flex-fill multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                {if $lang.id_lang != $current_lang.id}
                    hide
                {/if}">
                        <input type="file"
                               class="form-control input-social-img input-social-img-il-{$lang.id_lang|escape:'html':'UTF-8'} input-social-img-ic-{$lang.iso_code|escape:'html':'UTF-8'}"
                               data-idlang="{$lang.id_lang|escape:'html':'UTF-8'}"
                               name="ets_seo_social_input_img_{$lang.id_lang|escape:'html':'UTF-8'}"
                               value="">
                        {if !$is_new_theme && count($ets_seo_languages) > 1}
                            {include file="./_btn_multilang.tpl"}
                        {/if}
                    </div>
                {/foreach}
                {if $is_new_theme && count($ets_seo_languages) > 1}
                    {include file="./_btn_multilang.tpl"}
                {/if}
            </div>
            <div class="help-block"
                 style="margin-top: 5px;">{l s='Leave blank to let social networks automatically select a suitable image from your page' mod='ets_seo'}</div>
        </div>
        {foreach from=$ets_seo_languages item='lang'}
            <div class="multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
            {if $lang.id_lang != $current_lang.id}
                    hide
                {/if}">
                <input type="hidden" name="ets_seo_social_img[{$lang.id_lang|escape:'html':'UTF-8'}]" value="{if isset($seo_data.social_img.value[$lang.id_lang]) && $seo_data.social_img.value[$lang.id_lang]}{$ets_seo_link_img|escape:'html':'UTF-8'}{$seo_data.social_img.value[$lang.id_lang]|escape:'html':'UTF-8'}{/if}">
                <div class="img-preview img-preview-{$lang.id_lang|escape:'html':'UTF-8'}
                        {if !isset($seo_data.social_img.value[$lang.id_lang]) || !$seo_data.social_img.value[$lang.id_lang]}hide{/if}">
                    <span class="remove-img" data-idlang="{$lang.id_lang|escape:'html':'UTF-8'}" title="{l s='Delete' mod='ets_seo'}"><i class="fa fa-close"></i></span>
                    <img src="{if isset($seo_data.social_img.value[$lang.id_lang]) && $seo_data.social_img.value[$lang.id_lang]}{$ets_seo_link_img|escape:'html':'UTF-8'}{$seo_data.social_img.value[$lang.id_lang]|escape:'html':'UTF-8'}{/if}"
                         class="ets_seo_social_img_preview">
                </div>
            </div>
        {/foreach}

    </div>

</div>