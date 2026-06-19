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


<div>
    {include file="../parts/_input_key_phrase.tpl"}
    <div class="card">
        <div class="card-header"  >
            <h3 class="card-title">{l s='Snippet preview' mod='ets_seo'}</h3>
        </div>
        <div class="box-seo-inner" id="box-snippet-preview">
            {if isset($meta_config)}
                <div class="ets-seo-meta-template hide">
                    {foreach $meta_config as $k=>$item}
                        <input type="hidden" id="ets_seo_meta_template_title_{$k|escape:'html':'UTF-8'}" value="{$item.title|escape:'html':'UTF-8'}"/>
                        <input type="hidden" id="ets_seo_meta_template_desc_{$k|escape:'html':'UTF-8'}" value="{$item.desc|escape:'html':'UTF-8'}"/>
                    {/foreach}
                </div>
            {/if}
            <div class="box-snippet-preview-content">
            {foreach from=$ets_seo_languages item='lang'}
                <div class="multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                {if $lang.id_lang != $current_lang.id}
                    hide
                {/if}">
                    <div class="snippet-preview" id="ets-seo-snippet-preview-{$lang.id_lang|escape:'html':'UTF-8'}">
                        <div class="snippet-preview--desktop">
                            <div class="snippet-preview--title">
                                <span class="text">{if isset($seo_cms.meta_title[$lang.id_lang])}{$seo_cms.meta_title[$lang.id_lang]|escape:'html':'UTF-8'}{/if}</span>
                            </div>

                            <div class="snippet-preview--baseurl">
                                {if isset($seo_cms.link_rewrite[$lang.id_lang])}
                                {assign var="slug_html" value='<span class="slug">'|cat:$seo_cms.link_rewrite[$lang.id_lang]|cat:'</span>'}
                                <span class="text">
                                    {if isset($seo_cms.link[$lang.id_lang])}{$seo_cms.link[$lang.id_lang]|replace:$seo_cms.link_rewrite[$lang.id_lang]:$slug_html nofilter}{/if}
                                </span>
                                {/if}
                            </div>
                            {include file="../parts/_preview_rating.tpl" onMobile=false}
                            <div class="snippet-preview--desc">
                                <span class="text">{if isset($seo_cms.meta_description[$lang.id_lang])}{$seo_cms.meta_description[$lang.id_lang]|escape:'UTF-8'}{/if}</span>
                            </div>
                        </div>
                        <div class="snippet-preview--mobile hide">
                            <div class="snippet-preview--baseurl">
                                {if isset($seo_cms.link_rewrite[$lang.id_lang]) && $slug_html}
                                    <span class="text">
                                    {if isset($seo_cms.link[$lang.id_lang])}{$seo_cms.link[$lang.id_lang]|replace:$seo_cms.link_rewrite[$lang.id_lang]:$slug_html nofilter}{/if}
                                </span>
                                {/if}
                            </div>
                            <div class="snippet-preview--title">
                                <span class="text">{if isset($seo_cms.meta_title[$lang.id_lang])}{$seo_cms.meta_title[$lang.id_lang]|escape:'html':'UTF-8'}{/if}</span>
                            </div>
                            <div class="snippet-preview--desc">
                                <span class="text">{if isset($seo_cms.meta_description[$lang.id_lang])}{$seo_cms.meta_description[$lang.id_lang]|escape:'UTF-8'}{/if}</span>
                            </div>
                            {include file="../parts/_preview_rating.tpl" onMobile=true}
                        </div>
                    </div>
                    <div class="snippet-preview-mode" id="ets-seo-snippet-preview-mode-{$lang.id_lang|escape:'html':'UTF-8'}">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-default js-btn-preview-mode active" data-mode="desktop">
                                <i class="fa fa-desktop"></i>
                            </button>
                            <button type="button" class="btn btn-default js-btn-preview-mode" data-mode="mobile">
                                <i class="fa fa-mobile"></i>
                            </button>
                        </div>
                        <div class="box-btn-snippet-meta">
                            {foreach from=$ets_seo_languages item='lang'}
                                <div class="multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                                    {if $lang.id_lang != $current_lang.id}
                                        hide
                                    {/if}">
                                    <button class="btn btn-primary btn-toggle-snippet-meta js-ets-seo-btn-toggle-snippet-meta">
                                        <i class="fa fa-pencil-square-o"></i> {l s='Edit snippet' mod='ets_seo'}
                                    </button>
                                </div>
                            {/foreach}
                        </div>
                        <div class="box-seo-structdata-btn">
                            {foreach from=$ets_seo_languages item='lang'}
                                <div class="multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                                    {if $lang.id_lang != $current_lang.id}
                                        hide
                                    {/if}">
                                    {if isset($seo_cms.link[$lang.id_lang])}
                                        {include file="../parts/_btn_test_struct.tpl" page_link=$seo_cms.link[$lang.id_lang]}
                                    {/if}

                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            {/foreach}


            </div>
            {include file="../parts/_snippet_meta.tpl"}
        </div>
    </div>

    <div class="card">
        <div class="card-header" >
            <h3 class="card-title">{l s='SEO analysis' mod='ets_seo'}</h3>
        </div>
        <div class="box-seo-inner" id="box-seo-analysis">
            {foreach from=$ets_seo_languages item='lang'}
                <div class="multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                    {if $lang.id_lang != $current_lang.id}
                        hide
                    {/if}">
                    
                    <div class="analysis-result">
                        {foreach $analysis_types as $at }
                            <div class="analysis-result--{$at.type|escape:'html':'UTF-8'}">
                                <div class="btn-analysis-collapse">{$at.title|escape:'html':'UTF-8'}</div>
                                <ul class="analysis-result--list {$at.type|escape:'html':'UTF-8'}"
                                    id="analysis-result--list-{$lang.id_lang|escape:'html':'UTF-8'}-{$at.type|escape:'html':'UTF-8'}">
                                    
                                </ul>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/foreach}
            
        </div>
    </div>
</div>