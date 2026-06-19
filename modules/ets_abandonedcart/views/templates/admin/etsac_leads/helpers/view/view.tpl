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

<div class="ets-ac-view-lead-form">
    <div class="panel">
        <div class="panel-heading">{l s='View lead form' mod='ets_abandonedcart'}{if isset($leadFormTitle) && $leadFormTitle} - {$leadFormTitle|escape:'html':'UTF-8'}{/if}
            <a class="btn btn-default pull-right" href="{$linkExportLeadForm|escape:'quotes':'UTF-8'}" data-id="{$leadForm.id_ets_abancart_form|escape:'html':'UTF-8'}">
                <i class="ets-ac-icon icon-export">
                    <svg class="w_12 h_12" width="12" height="12" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1344 1472q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm256 0q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm128-224v320q0 40-28 68t-68 28h-1472q-40 0-68-28t-28-68v-320q0-40 28-68t68-28h427q21 56 70.5 92t110.5 36h256q61 0 110.5-36t70.5-92h427q40 0 68 28t28 68zm-325-648q-17 40-59 40h-256v448q0 26-19 45t-45 19h-256q-26 0-45-19t-19-45v-448h-256q-42 0-59-40-17-39 14-69l448-448q18-19 45-19t45 19l448 448q31 30 14 69z"/></svg>
                </i> {l s='Export form data' mod='ets_abandonedcart'}
            </a>
        </div>
        <div class="panel-body">
            {if isset($leadForm) && $leadForm}
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{l s='Id' mod='ets_abandonedcart'}</th>
                                <th>{l s='Date' mod='ets_abandonedcart'}</th>
                                {assign var="totalCol" value=0}
                                {foreach $leadForm.fields as $f}
                                    {if $f.display_column}
                                        {assign var="totalCol" value=$totalCol+1}
                                        <th>{$f.name|escape:'html':'UTF-8'}</th>
                                    {/if}
                                {/foreach}
                            </tr>
                        </thead>
                        <tbody>
                            {if $fieldValues}
                                {foreach $fieldValues as $k=>$item}
                                <tr>
                                    <td>{$item.id_ets_abancart_form_submit|escape:'html':'UTF-8'}</td>
                                    <td>{$item.date_add|escape:'html':'UTF-8'}</td>
                                    {foreach $leadForm.fields as $f}

                                        {assign var="foundCol" value=false}
                                        {foreach $item.field_values as $fv}
                                            {if $f.display_column && $fv.id_ets_abancart_field == $f.id_ets_abancart_field}
                                                {assign var="foundCol" value=true}
                                                {if $fieldTypes.file.key == $f.type}
                                                    {if $fv.file_name}
                                                        <td><a href="{$linkDownloadFile|escape:'quotes':'UTF-8'}&idFieldValue={$fv.id_ets_abancart_field_value|escape:'html':'UTF-8'}" class="link-download-file-field" target="_blank">{$fv.file_name|escape:'html':'UTF-8'}</a></td>
                                                    {elseif $fv.file_name}
                                                        <td><a href="{$linkDownloadFile|escape:'quotes':'UTF-8'}&idFieldValue={$fv.id_ets_abancart_field_value|escape:'html':'UTF-8'}" class="link-download-file-field" target="_blank"></a> {$fv.value|escape:'html':'UTF-8'}</td></td>
                                                    {else}
                                                        <td>--</td>
                                                    {/if}
                                                {else}
                                                    <td>{if $fv.value}{$fv.value nofilter}{else}--{/if}</td>
                                                {/if}
                                                {break}
                                            {/if}
                                        {/foreach}
                                        {if !$foundCol}
                                            <td>--</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="100%" class="text-center">{l s='No data found' mod='ets_abandonedcart'}</td>
                                </tr>
                            {/if}
                        </tbody>
                    </table>
                </div>
                <div class="ets-ac-pagination">
                    {if $totalPage && $totalPage > 1}
                    <ul class="pagination pull-right">
                        {foreach $totalPage as $k=>$p}
                            {assign var="pageItem" value=$k+1}
                            <li class="{if $currentPage == $pageItem}active{/if}"><a href="{$linkPage|escape:'quotes':'UTF-8'}&page={$pageItem|escape:'html':'UTF-8'}">{$pageItem|escape:'html':'UTF-8'}</a></li>
                        {/foreach}
                    </ul>
                    {/if}
                </div>
            {/if}
        </div>
    </div>
    <a href="{$linkList|escape:'quotes':'UTF-8'}" class="ets_ac_link_back_list">
        <i class="ets_svg_fill_gray ets_svg_hover_fill_white ets-ac-icon-back lh_16">
            <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1427 301l-531 531 531 531q19 19 19 45t-19 45l-166 166q-19 19-45 19t-45-19l-742-742q-19-19-19-45t19-45l742-742q19-19 45-19t45 19l166 166q19 19 19 45t-19 45z"/></svg>
        </i> {l s='Back to list' mod='ets_abandonedcart'}</a>
</div>