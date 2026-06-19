{*
* 2007-2020 ETS-Soft
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
*  @copyright  2007-2020 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

<div class="panel ets-trans-logs">
    <div class="panel-header">
        <h3 class="panel-title">
            {l s='Translation logs' mod='ets_translate'}
            <a href="javascript:void(0)" class="btn btn-danger btn-sm pull-right js-ets-trans-clear-all-logs"><i class="fa fa-trash"></i> {l s='Clear logs' mod='ets_translate'}</a>
            <a href="{$linkConfig|escape:'quotes':'UTF-8'}" class="btn btn-default btn-sm pull-right" ><i class="fa fa-arrow-left"></i> {l s='Back to Global settings' mod='ets_translate'}</a>
        </h3>
    </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{l s='ID' mod='ets_translate'}</th>
                    <th>{l s='Page type' mod='ets_translate'}</th>
                    <th>{l s='Source language' mod='ets_translate'}</th>
                    <th>{l s='Target language' mod='ets_translate'}</th>
                    <th>{l s='Ids translated' mod='ets_translate'}</th>
                    <th>{l s='Text translated' mod='ets_translate'}</th>
                    <th>{l s='Status' mod='ets_translate'}</th>
                    <th>{l s='Response message' mod='ets_translate'}</th>
                    <th>{l s='Timeout (ms)' mod='ets_translate'}</th>
                    <th>{l s='Translation date' mod='ets_translate'}</th>
                    <th>{l s='Actions' mod='ets_translate'}</th>
                </tr>
                </thead>
                <tbody>
                {if $logData.total_page}
                {foreach $logData.data as $item}
                    <tr>
                        <td>{$item.id_ets_trans_log|escape:'html':'UTF-8'}</td>
                        <td>
                            {if $item.page_type == 'pc'}
                                {l s='Product comments' mod='ets_translate'}
                            {elseif $item.page_type == 'megamenu'}
                                {l s='Megamenu' mod='ets_translate'}
                            {elseif $item.page_type == 'blog_post'}
                                {l s='Blog post' mod='ets_translate'}
                            {elseif $item.page_type == 'blog_category'}
                                {l s='Blog category' mod='ets_translate'}
                            {else}
                                {$item.page_type|escape:'html':'UTF-8'}
                            {/if}
                        </td>
                        <td>{$item.lang_source|escape:'html':'UTF-8'}</td>
                        <td>{$item.lang_target|escape:'html':'UTF-8'}</td>
                        <td>{$item.ids_translated|escape:'html':'UTF-8'}</td>
                        <td>{$item.text_translated|escape:'html':'UTF-8'}</td>
                        <td>
                            {if $item.status}
                                <span class="label label-success">{l s='Success' mod='ets_translate'}</span>
                            {else}
                                <span class="label label-danger">{l s='Error' mod='ets_translate'}</span>
                            {/if}
                        </td>
                        <td>{$item.res_message|escape:'html':'UTF-8'}</td>
                        <td>{$item.timeout|escape:'html':'UTF-8'}</td>
                        <td>{$item.date_add|escape:'html':'UTF-8'}</td>
                        <td>
                            <a href="javascript:void(0)" title="{l s='Delete' mod='ets_translate'}" class="text-danger js-ets-trans-clear-log-item" data-id="{$item.id_ets_trans_log|escape:'html':'UTF-8'}"><i class="fa fa-close"></i> {l s='Delete' mod='ets_translate'}</a>
                        </td>
                    </tr>
                {/foreach}
                {else}
                    <tr>
                        <td colspan="100%">
                            <div class="alert alert-info">
                                {l s='No data found' mod='ets_translate'}
                            </div>
                        </td>
                    </tr>
                {/if}
                </tbody>
            </table>
            {if $logData.total_page > 1}
                <div class="pagination-box text-right">
                    <div class="log-info">
                        <strong>{l s='Total logs:' mod='ets_translate'} {$logData.total|escape:'html':'UTF-8'}</strong>
                        <span> | </span>
                        <strong>{l s='Total pages:' mod='ets_translate'} {$logData.total_page|escape:'html':'UTF-8'}</strong>
                    </div>
                    <div class="log-paginate">
                        <ul class="pagination">
                            {if $logData.current_page > 1}
                                <li>
                                    <a href="{$linkPaginate|escape:'quotes':'UTF-8'}&page=1">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            {/if}
                            {if $logData.total_page < 6}
                                {for $i = 1; $i <= $logData.total_page; $i++}
                                    <li {if $logData.current_page == $i}class="active"{/if}>
                                        <a href="{$linkPaginate|escape:'quotes':'UTF-8'}&page={$i|escape:'html':'UTF-8'}">{$i|escape:'html':'UTF-8'}</a>
                                    </li>
                                {/for}
                            {else}
                                {if $logData.current_page > 2}
                                    <li class="disabled">
                                        <a href="#">...</a>
                                    </li>
                                {/if}
                                {if $logData.prev_page}
                                    <li>
                                        <a href="{$linkPaginate|escape:'quotes':'UTF-8'}&page={$logData.prev_page|escape:'html':'UTF-8'}">{$logData.prev_page|escape:'html':'UTF-8'}</a>
                                    </li>
                                {/if}
                                <li class="active">
                                    <a href="{$linkPaginate|escape:'quotes':'UTF-8'}&page={$logData.current_page|escape:'html':'UTF-8'}">{$logData.current_page|escape:'html':'UTF-8'}</a>
                                </li>
                                {if $logData.next_page <= $logData.total_page}
                                    <li>
                                        <a href="{$linkPaginate|escape:'quotes':'UTF-8'}&page={$logData.next_page|escape:'html':'UTF-8'}">{$logData.next_page|escape:'html':'UTF-8'}</a>
                                    </li>
                                {/if}
                                {if $logData.current_page < $logData.total_page - 2}
                                    <li class="disabled">
                                        <a href="#">...</a>
                                    </li>
                                {/if}
                            {/if}
                            {if $logData.current_page < $logData.total_page}
                                <li>
                                    <a href="{$linkPaginate|escape:'quotes':'UTF-8'}&page={$logData.total_page|escape:'html':'UTF-8'}">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            {/if}
                        </ul>
                    </div>

                </div>
            {/if}
        </div>
</div>
