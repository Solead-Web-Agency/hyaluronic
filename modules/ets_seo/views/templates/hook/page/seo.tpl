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
    <div class="ets_seotop1_main_block_header" data-toggle="collapse" data-target="#ets_seotop1_main_block" 
        aria-expanded="true" aria-controls="ets_seotop1_main_block">{l s='SEO Audit' mod='ets_seo'}</div>
    <div class="collapse multi-collapse show ets_seotop1_main_block" id="ets_seotop1_main_block">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
            {if $seo_enabled}
                <a class="nav-item nav-link active" id="nav-seo-tab" data-toggle="tab" href="#nav-seo-content" role="tab" aria-controls="nav-home" aria-selected="true">{l s='SEO' mod='ets_seo'}</a>
            {/if}
            {if $readability_enabled}
                <a class="nav-item nav-link {if !$seo_enabled}active{/if}" id="nav-readability-tab" data-toggle="tab" href="#nav-readability" role="tab" aria-controls="nav-readability" aria-selected="false">{l s='Readability' mod='ets_seo'}</a>
            {/if}
                <a class="nav-item nav-link {if !$seo_enabled && !$readability_enabled }active{/if}" id="nav-social-tab" data-toggle="tab" href="#nav-social" role="tab" aria-controls="nav-social" aria-selected="false">{l s='Social' mod='ets_seo'}</a>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            {if $seo_enabled}
            <div class="tab-pane active" id="nav-seo-content" role="tabpanel" aria-labelledby="nav-seo-tab">
                {include file="./_tab_seo.tpl"}
            </div>
            {/if}
            {if $readability_enabled}
            <div class="tab-pane {if !$seo_enabled}active{/if}" id="nav-readability" role="tabpanel" aria-labelledby="nav-readability-tab">
                {include file="../parts/_tab_readability.tpl"}
            </div>
            {/if}
            <div class="tab-pane {if !$seo_enabled && !$readability_enabled }active{/if}" id="nav-social" role="tabpanel" aria-labelledby="nav-social-tab">
                {include file="../parts/_tab_social.tpl"}
            </div>
        </div>
        <input type="hidden" id="ets_seo_score_data" name="ets_seo_score_data" value="">
        <textarea style="display: none !important;" name="ets_seo_content_analysis" id="ets_seo_content_analysis"></textarea>
    </div>
    
</div>