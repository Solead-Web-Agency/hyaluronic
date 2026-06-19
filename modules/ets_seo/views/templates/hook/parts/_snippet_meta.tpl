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

<div class="snippet-meta js-ets-seo-box-snippet-meta hide">
    <div class="form-group">
        <label>{l s='Meta title' mod='ets_seo'}</label>
        <div class="input-group locale-input-group d-flex">
        {foreach from=$ets_seo_languages item='lang'}
            <div class="multilang-field flex-fill lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
            {if $lang.id_lang != $current_lang.id}
                hide
            {/if}">
                <input class="form-control" id="ets_seo_meta_title_{$lang.id_lang|escape:'html':'UTF-8'}" name="ets_seo_meta_title[{$lang.id_lang|escape:'html':'UTF-8'}]">
                <small class="js-text-count form-text text-muted text-right"><em><span class="js-ets-seo-current-length">0</span> {l s='of 60 characters used (recommended)' mod='ets_seo'}</em></small>
            </div>
            {if !$is_new_theme && count($ets_seo_languages) > 1}
                <div class="btn-group-lang js-ets-seo-btn-group-lang multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                                {if $lang.id_lang != $current_lang.id}
                                    hide
                                {/if}">
                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                        {$lang.iso_code|escape:'html':'UTF-8'}
                        <i class="icon-caret-down"></i>
                    </button>
                    <ul class="dropdown-menu">
                        {foreach from=$ets_seo_languages item='lang2'}
                            <li><a href="javascript:hideOtherLanguage({$lang2.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang2.name|escape:'html':'UTF-8'}</a></li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        {/foreach}
        {if $is_new_theme && count($ets_seo_languages) > 1}
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle js-locale-btn"
                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {$current_lang.iso_code|escape:'html':'UTF-8'}
                </button>
                <div class="dropdown-menu">
                    {foreach from=$ets_seo_languages item='lang'}
                        <span class="dropdown-item js-locale-item" data-locale="{$lang.iso_code|escape:'html':'UTF-8'}">{$lang.name|escape:'html':'UTF-8'}</span>
                    {/foreach}
                </div>
            </div>
        {/if}
        </div>
    </div>
    <div class="form-group">
        <label>{l s='Meta description' mod='ets_seo'}</label>
        <div class="input-group locale-input-group d-flex">
        {foreach from=$ets_seo_languages item='lang'}
            <div class="multilang-field flex-fill lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                {if $lang.id_lang != $current_lang.id}
                    hide
                {/if}">
                <textarea class="form-control" id="ets_seo_meta_description_{$lang.id_lang|escape:'html':'UTF-8'}" name="ets_seo_meta_description[{$lang.id_lang|escape:'html':'UTF-8'}]" class="form-control"></textarea>
                <small class="js-text-count form-text text-muted text-right"><em><span class="js-ets-seo-current-length">0</span> {l s='of 156 characters used (recommended)' mod='ets_seo'}</em></small>
            </div>
            {if !$is_new_theme && count($ets_seo_languages) > 1}
                <div class="btn-group-lang js-ets-seo-btn-group-lang multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                                {if $lang.id_lang != $current_lang.id}
                                    hide
                                {/if}">
                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                        {$lang.iso_code|escape:'html':'UTF-8'}
                        <i class="icon-caret-down"></i>
                    </button>
                    <ul class="dropdown-menu">
                        {foreach from=$ets_seo_languages item='lang2'}
                            <li><a href="javascript:hideOtherLanguage({$lang2.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang2.name|escape:'html':'UTF-8'}</a></li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        {/foreach}
        {if $is_new_theme && count($ets_seo_languages) > 1}
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle js-locale-btn"
                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {$current_lang.iso_code|escape:'html':'UTF-8'}
                </button>
                <div class="dropdown-menu">
                    {foreach from=$ets_seo_languages item='lang'}
                        <span class="dropdown-item js-locale-item" data-locale="{$lang.iso_code|escape:'html':'UTF-8'}">{$lang.name|escape:'html':'UTF-8'}</span>
                    {/foreach}
                </div>
            </div>
        {/if}
        </div>
    </div>
    {if isset($show_friendly_url) && $show_friendly_url}
    <div class="form-group">
        <label>{l s='Friendly URL' mod='ets_seo'}</label>
        <div class="input-group locale-input-group d-flex">
        {foreach from=$ets_seo_languages item='lang'}
            <div class="multilang-field flex-fill lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                {if $lang.id_lang != $current_lang.id}
                    hide
                {/if}">
                <input class="form-control" id="ets_seo_link_rewrite_{$lang.id_lang|escape:'html':'UTF-8'}" name="ets_seo_link_rewrite[{$lang.id_lang|escape:'html':'UTF-8'}]">
            </div>
            {if !$is_new_theme && count($ets_seo_languages) > 1}
                <div class="btn-group-lang js-ets-seo-btn-group-lang multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                                {if $lang.id_lang != $current_lang.id}
                                    hide
                                {/if}">
                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                        {$lang.iso_code|escape:'html':'UTF-8'}
                        <i class="icon-caret-down"></i>
                    </button>
                    <ul class="dropdown-menu">
                        {foreach from=$ets_seo_languages item='lang2'}
                            <li><a href="javascript:hideOtherLanguage({$lang2.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang2.name|escape:'html':'UTF-8'}</a></li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        {/foreach}
        {if $is_new_theme && count($ets_seo_languages) > 1}
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle js-locale-btn"
                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {$current_lang.iso_code|escape:'html':'UTF-8'}
                </button>
                <div class="dropdown-menu">
                    {foreach from=$ets_seo_languages item='lang'}
                        <span class="dropdown-item js-locale-item" data-locale="{$lang.iso_code|escape:'html':'UTF-8'}">{$lang.name|escape:'html':'UTF-8'}</span>
                    {/foreach}
                </div>
            </div>
        {/if}
        </div>
    </div>
    {/if}
</div>