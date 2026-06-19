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

<section class="box-seo-readability card ets_seotop1_step_seo">
    <div class="card-header">
        <h3 class="ets-seo-heading-analysis card-title">{l s='Readability' mod='ets_seo'}</h3>
    </div>
    <div class="card-block">
        <div class="card-body">
            <div class="box-field">
                {foreach from=$ets_seo_languages item='lang'}
                    <div class="multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                    {if $lang.id_lang != $current_lang.id}
                        hide
                    {/if}">

                        <div class="analysis-result">
                            {if isset($show_readability) && !$show_readability}
                                <p class="no-readability-available">{l s='Readability is not available because there is no readable content on this page.' mod='ets_seo'}</p>
                            {else}
                                {foreach $analysis_types as $at }
                                    <div class="analysis-result--{$at.type|escape:'html':'UTF-8'}">
                                        <div class="btn-analysis-collapse">{$at.title|escape:'html':'UTF-8'}</div>
                                        <ul class="analysis-result--list {$at.type|escape:'html':'UTF-8'}"
                                            id="analysis-result--list-readablity-{$lang.id_lang|escape:'html':'UTF-8'}-{$at.type|escape:'html':'UTF-8'}">

                                        </ul>
                                    </div>
                                {/foreach}
                            {/if}

                        </div>
                    </div>
                {/foreach}
            </div>
            {foreach from=$ets_seo_languages item='lang'}
                <div class="multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
            {if $lang.id_lang != $current_lang.id}
                hide
            {/if}">


                </div>
            {/foreach}
        </div>
    </div>

</section>


