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
<div class="ets_seotop1_step_seo">
    <div class="ets-seo-preview-seo-analysis-box js-ets-seo-preview-analysis">
        <h3>
            {l s='SEO & Readability' mod='ets_seo'}
            <span class="help-box" data-toggle="popover"
                  data-content="{l s='Average score of SEO and page readability for this page' mod='ets_seo'}" data-original-title=""
                  title=""></span>
        </h3>
        <div class="form-group seo-processing">
            {foreach $ets_seo_languages as $lang}
                <div class="js-ets-seo-processing-lang-{$lang.id_lang|escape:'html':'UTF-8'} multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
            {if $lang.id_lang != $current_lang.id}
                        hide
                    {/if}">
                    <div class="header-processing">
                        <label class="main-title">{l s='SEO' mod='ets_seo'}</label>
                        <span class="sub-title">{l s='Analyzing...' mod='ets_seo'}</span>
                    </div>
                    <div class="wrapper-level">
                        <div class="warapper-processing">
                            <div class="processing excuting">
                                <div class="level-item level-1"></div>
                                <div class="level-item level-2"></div>
                                <div class="level-item level-3"></div>
                                <div class="level-item level-4"></div>
                                <div class="level-item level-5"></div>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="form-group readability-processing">
            {foreach $ets_seo_languages as $lang}
            <div class="js-ets-seo-processing-lang-{$lang.id_lang|escape:'html':'UTF-8'} multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                {if $lang.id_lang != $current_lang.id}
                    hide
                {/if}">
                <div class="header-processing">
                    <label class="main-title">{l s='Readability' mod='ets_seo'}</label>
                    <span class="sub-title">{l s='Analyzing...' mod='ets_seo'}</span>
                </div>
                <div class="wrapper-level">
                        <div class="warapper-processing">
                            <div class="processing excuting">
                                <div class="level-item level-1"></div>
                                <div class="level-item level-2"></div>
                                <div class="level-item level-3"></div>
                                <div class="level-item level-4"></div>
                                <div class="level-item level-5"></div>
                            </div>
                        </div>
                </div>
            </div>
            {/foreach}
        </div>
        <div class="footer-processing">
            <a href="javascript:void(0)" class="js-ets-seo-show-seo-analysis-tab"><i
                        class="fa fa-search"></i> {l s='View SEO analysis in details' mod='ets_seo'}</a>
        </div>
        {if !isset($isAutoAnalysis) || !$isAutoAnalysis}
        <div class="box-control-analysis">
            <button class="btn btn-outline-secondary ets-seo-btn-control-analysis js-ets-seo-btn-control-analysis">
                {l s='Analysis page' mod='ets_seo'}<div class="ets_loading_threedots"><div class="dot-pulse"></div></div>
            </button>
        </div>
        {/if}
    </div>
</div>
