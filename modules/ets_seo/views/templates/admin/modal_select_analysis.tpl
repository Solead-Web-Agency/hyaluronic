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
<div class="modal fade ets_modal_form" id="etsSeoModalManualAnalysis" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog ets_table" role="document">
    <div class="ets_table_cell">
        <div class="modal-content">
            <div class="modal-header panel_header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{l s='Select pages to analyze SEO and readability' mod='ets_seo'}</h4>
            </div>
            <div class="modal-body panel_body">
                <form>
                    <div class="box-select-page">
                        <p class="checkbox">
                            <label>
                                <input type="checkbox" name="ets_seo_page_all" value="1" />
                                {l s='All pages' mod='ets_seo'}
                            </label>
                        </p>
                        {foreach $listPages as $key=>$item}
                            {if $dataNoAnalysis[$key]}
                            <p class="checkbox">
                                <label>
                                    <input type="checkbox" name="ets_seo_page[]" value="{$key|escape:'html':'UTF-8'}" data-total-page="{$dataNoAnalysis[$key]|escape:'html':'UTF-8'}">
                                    {$item|escape:'html':'UTF-8'}
                                    {if isset($dataNoAnalysis[$key]) && $dataNoAnalysis[$key]}({$dataNoAnalysis[$key]|escape:'html':'UTF-8'} {l s='pages missing analysis' mod='ets_seo'}){/if}
                                </label>
                            </p>
                            {/if}
                        {/foreach}
                    </div>
                    <div class="box-analysis hide js-ets-seo-div-analysis-data" data-total="{$totalPage|escape:'html':'UTF-8'}">
                        <div class="">{l s='Analyzing...' mod='ets_seo'}
                            <span class="nb_page_updated" data-page="0">0</span> {l s='pages' mod='ets_seo'}
                            (<span class="nb_page_left" data-page="{$totalPage|escape:'html':'UTF-8'}">{$totalPage|escape:'html':'UTF-8'}</span> {l s='pages left' mod='ets_seo'})</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="box-select-page">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{l s='Cancel' mod='ets_seo'}</button>
                    <button type="button" class="btn btn-primary js-ets-seo-analysis-manually">{l s='Analyze' mod='ets_seo'}</button>
                </div>
                <div class="box-analysis hide">
                    <button type="button" class="btn btn-default js-ets-seo-cancel-analysis" data-dismiss="modal">{l s='Cancel' mod='ets_seo'}</button>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>