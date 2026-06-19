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

<div class="form-group row">
    <label for="ets_seo_meta_title" class="form-control-label">
       {l s='Meta title' mod='ets_seo'}
    </label>
    <div class="col-sm ets_seotop1_step_seo" style="margin-bottom: 0;">
        <div class="input-group locale-input-group js-locale-input-group d-flex">
            {foreach $ets_seo_languages as $lang}
                <div class="multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'} {if $lang.id_lang != $current_lang.id}hide{/if}" style="flex-grow: 1;">
                    <input type="text" id="ets_seo_meta_title_{$lang.id_lang|escape:'html':'UTF-8'}" name="ets_seo_meta_title[{$lang.id_lang|escape:'html':'UTF-8'}]" class="form-control">
                </div>
            {/foreach}
            <div class="ets_seo_meta_code "></div>
            {if count($ets_seo_languages) > 1}
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle js-locale-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="ets_seo_meta_title">
                        {$current_lang.iso_code|escape:'html':'UTF-8'}
                    </button>
                    <div class="dropdown-menu" aria-labelledby="ets_seo_meta_title">
                        {foreach $ets_seo_languages as $lang}
                            <span class="dropdown-item js-locale-item" data-locale="{$lang.iso_code|escape:'html':'UTF-8'}">{$lang.name|escape:'html':'UTF-8'}</span>
                        {/foreach}
                    </div>
                </div>
            {/if}
        </div>
        
        <small class="form-text">
            {l s='Title of this page. Invalid characters: <>={}' mod='ets_seo'}
        </small>

    </div>
</div>