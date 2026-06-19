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
<div class="ets-seo-dashboard">
    <section class="section-top">
        <div class="row">
            <div class="col-lg-3 col_box_padding">
                <div class="box box-info box-green" role="tooltip" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='The number of web pages having both SEO and readability considered as "Excellent".' mod='ets_seo'} {if $multi_lang_enable}{$txt_multilang|escape:'html':'UTF-8'}{/if}">
                    <div class="box-body">
                        <div class="box-data">
                            <div class="box-title">{l s='Excellent' mod='ets_seo'}</div>
                            <div class="box-number">
                                <div>
                                    <span class="number">{$ets_seo_data_dashboard.seo_score.good|escape:'html':'UTF-8'}</span>
                                    <span class="unit">{l s='SEO' mod='ets_seo'}</span>
                                </div>
                                <div>
                                    <span class="number">{$ets_seo_data_dashboard.readability_score.good|escape:'html':'UTF-8'}</span>
                                    <span class="unit">{l s='Readability' mod='ets_seo'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="box-icon">
                            <span class="icon"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col_box_padding">
                <div class="box box-info box-orange" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='The number of web pages having both SEO and readability considered as "Acceptable".' mod='ets_seo'}  {if $multi_lang_enable}{$txt_multilang|escape:'html':'UTF-8'}{/if}">
                    <div class="box-body">
                        <div class="box-data">
                            <div class="box-title">{l s='Acceptable' mod='ets_seo'}</div>
                            <div class="box-number">
                                <div>
                                    <span class="number">{$ets_seo_data_dashboard.seo_score.na|escape:'html':'UTF-8'}</span>
                                    <span class="unit">{l s='SEO' mod='ets_seo'}</span>
                                </div>
                                <div>
                                    <span class="number">{$ets_seo_data_dashboard.readability_score.na|escape:'html':'UTF-8'}</span>
                                    <span class="unit">{l s='Readability' mod='ets_seo'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="box-icon">
                            <span class="icon"><i class="fa fa-handshake-o" aria-hidden="true"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col_box_padding">
                <div class="box box-info box-red" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='The number of web pages having both SEO and readability considered as "Not good" (need improvements).' mod='ets_seo'}  {if $multi_lang_enable}{$txt_multilang|escape:'html':'UTF-8'}{/if}">
                    <div class="box-body">
                        <div class="box-data">
                            <div class="box-title">{l s='Not good' mod='ets_seo'}</div>
                            <div class="box-number">
                                <div>
                                    <span class="number">{$ets_seo_data_dashboard.seo_score.bad|escape:'html':'UTF-8'}</span>
                                    <span class="unit">{l s='SEO' mod='ets_seo'}</span>
                                </div>
                                <div>
                                    <span class="number">{$ets_seo_data_dashboard.readability_score.bad|escape:'html':'UTF-8'}</span>
                                    <span class="unit">{l s='Readability' mod='ets_seo'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="box-icon">
                            <span class="icon"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col_box_padding">
                <div class="box box-info box-blue" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='The number of web pages have not been analyzed with SEO or readability.' mod='ets_seo'}  {if $multi_lang_enable}{$txt_multilang|escape:'html':'UTF-8'}{/if}">
                    <div class="box-body">
                        <div class="box-data">
                            <div class="box-title">{l s='No analysis' mod='ets_seo'}</div>
                            <div class="box-number">
                                <div>
                                    <span class="number">{$ets_seo_data_dashboard.seo_score.noanalysis|escape:'html':'UTF-8'}</span>
                                    {if $ets_seo_data_dashboard.seo_score.noanalysis > 1}
                                        <span class="unit">{l s='Pages' mod='ets_seo'}</span>
                                    {else}
                                        <span class="unit">{l s='Page' mod='ets_seo'}</span>
                                    {/if}
                                </div>
                                {if $ets_seo_data_dashboard.seo_score.noanalysis > 0}
                                <div>
                                    <button type="button" class="btn btn-default js-ets-seo-get-modal-analysis">{l s='Analyze missing pages' mod='ets_seo'}</button>
                                </div>
                                {/if}
                            </div>
                        </div>
                        <div class="box-icon">
                            <span class="icon"><i class="fa fa-cogs" aria-hidden="true"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section-middle">
        <div class="row">
            <div class="col-sm-12 col-lg-8 col_box_padding">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{l s='Index/Follow ratio' mod='ets_seo'} <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='Index/Follow pages are web pages that allow search engines to index (display on search result pages) and follow (search bots can follow links on the pages).' mod='ets_seo'} {if $multi_lang_enable}{$txt_multilang|escape:'html':'UTF-8'}{/if}"></i></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-6 col-sm-6">
                                <div id="chart-index-ratio" class="chart-index-ratio">
                                    <canvas id="canvas-hart-index-ratio" width="300" height="300"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6">
                                <div id="chart-follow-ratio" class="chart-follow-ratio">
                                    <canvas id="canvas-chart-follow-ratio" width="300" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-4 col_box_padding">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{l s='Meta settings completed' mod='ets_seo'} <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='It is recommended to manually enter meta title and meta description for each page with the content is optimized for your SEO keywords' mod='ets_seo'} {if $multi_lang_enable}{$txt_multilang|escape:'html':'UTF-8'}{/if}"></i></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-12 col-lg-12">
                                <div id="chart-meta-setting-completed" class="chart-meta-setting-completed">
                                    <canvas id="canvas-chart-meta-setting-completed" width="300" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section-bottom">
        <div class="row">
            <div class="col-lg-6 col_box_padding">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{l s='Page analysis' mod='ets_seo'} <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='SEO and readability analysis for specific kinds of web pages.' mod='ets_seo'} {if $multi_lang_enable}{$txt_multilang|escape:'html':'UTF-8'}{/if}"></i></h3>
                        <div class="box-tools">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-default active js-ets-seo-tab-chart-page-analysis" data-tab="seo-score">{l s='SEO' mod='ets_seo'}</button>
                                <button type="button" class="btn btn-default js-ets-seo-tab-chart-page-analysis" data-tab="readability-score">{l s='Readability' mod='ets_seo'}</button>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <div id="chart-page-analytics" class="chart-page-analytics">
                            <svg></svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col_box_padding">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{l s='SEO checklist' mod='ets_seo'} <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='Make sure all check points below are green to improve SEO rankings of all pages.' mod='ets_seo'}"></i></h3>
                    </div>
                    <div class="box-body">
                        <div class="seo-checklist">
                            <table class="table">
                                {foreach $ets_seo_checklist as $item}
                                    {if isset($item.hide) && $item.hide}
                                        {continue}
                                    {/if}
                                    <tr>
                                        <td><i class="dots{if !$item.status} danger{/if}"></i>{$item.title|escape:'UTF-8'}</td>
                                        <td class="text-center">
                                            {if $item.status}
                                                <span class="badge badge-success status-success">{l s='Yes' mod='ets_seo'}</span>
                                            {else}
                                                <span class="badge badge-danger status-danger">{l s='No' mod='ets_seo'}</span>
                                            {/if}
                                        </td>
                                        <td class="text-right">
                                            {if isset($item.is_module) && $item.is_module && !$item.is_installed}
                                                <a href="{$item.link|escape:'quotes'}" target="_blank"><i class="fa fa-cart-plus"></i> {l s='Get it now' mod='ets_seo'}</a>
                                            {else}
                                                <a href="{$item.link|escape:'quotes'}" target="_blank"><i class="fa fa-cogs"></i> {l s='Configure' mod='ets_seo'}</a>
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    var ets_seo_data_dashboard = {if isset($ets_seo_data_dashboard)} {$ets_seo_data_dashboard|@json_encode nofilter}{else}null{/if};
    var ets_seo_data_not_found = "{l s='No data found' mod='ets_seo'}";
</script>
<script type="text/javascript" src="{$ets_seo_link_dashboard_js|escape:'quotes'}" defer="defer"></script>