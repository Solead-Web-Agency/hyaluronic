{*
* Do not edit the file if you want to upgrade the module in future.
* 
* @author    Globo Jsc <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @link	     http://www.globosoftware.net/
* @license   please read license in file license.txt
*/
*}
{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'gupsell_analytic'}
                </div>
            </div>
        </div>
        {*close panel- helper form*}
        <div class="form-group">
        </div>
        <div class="form-group">
            <div class="col-lg-12">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>
                                    {l s='Performance Overview' mod='g_upsellpro'}
                                    <div class="btn-group pull-right  margin-left10">
                                        <button class="btn btn-default dropdown-toggle gbtn-default Analytic-time-show-text" data-toggle="dropdown"><i class="icon-calendar-empty"></i> {l s='Last' mod='g_upsellpro'} <span> {$getConfigFieldsValues['GSELL_ANALYTIC_TIME']|escape:'html':'UTF-8'} </span> {l s='days' mod='g_upsellpro'} <i class="icon-caret-down"></i></button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a title="Today" href="javascript:void(0);" class="Analytic-time-show" data-items="1" data-list-id="Analytic">
                                                    {l s='Today' mod='g_upsellpro'}
                                                </a>
                                            </li>
                                            <li>
                                                <a title="Today" href="javascript:void(0);" class="Analytic-time-show" data-items="7" data-list-id="Analytic">
                                                    {l s='Last 7 days' mod='g_upsellpro'}
                                                </a>
                                            </li>
                                            <li>
                                                <a title="Last 30 days" href="javascript:void(0);" class="Analytic-time-show" data-items="30" data-list-id="Analytic">
                                                    {l s='Last 30 days' mod='g_upsellpro'}
                                                </a>
                                            </li>
                                            <li>
                                                <a title="Last 90 days" href="javascript:void(0);" class="Analytic-time-show" data-items="90" data-list-id="Analytic">
                                                    {l s='Last 90 days' mod='g_upsellpro'}
                                                </a>
                                            </li>
                                            <li>
                                                <a title="Last 365 days" href="javascript:void(0);" class="Analytic-time-show" data-items="365" data-list-id="Analytic">
                                                    {l s='Last 365 days' mod='g_upsellpro'}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="btn-group pull-right  margin-left10">
                                        <button class="btn btn-default dropdown-toggle gbtn-default" style="display: none" data-toggle="dropdown"><i class="icon-download"></i> {l s='Export' mod='g_upsellpro'} <i class="icon-caret-down"></i></button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a title="Export Upsells overview">
                                                    {l s='Export Upsells overview' mod='g_upsellpro'}
                                                </a>
                                            </li>
                                            <li>
                                                <a title="Export Detailed report">
                                                    {l s='Export Detailed report' mod='g_upsellpro'}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="panel col-lg-12">
                                <section>
                                    <div class="gupsell-analytics-box">
                                        <div class="dashboard-analytics-box-content-box color1" data-item="views">
                                            <h2 class="dashboard-analytics-box-header">{l s='Views' mod='g_upsellpro'}</h2>
                                            <div class="dashboard-analytics-box-content text-center">
                                                {$total_views|escape:'html':'UTF-8'}
                                            </div>
                                        </div>
                                        <div class="dashboard-analytics-box-content-box color2" data-item="addcarts">
                                            <h2 class="dashboard-analytics-box-header">{l s='Add To Carts' mod='g_upsellpro'}</h2>
                                            <div class="dashboard-analytics-box-content text-center">
                                                {$addcarts|escape:'html':'UTF-8'}
                                            </div>
                                        </div>
                                        <div class="dashboard-analytics-box-content-box color3" data-item="transactions">
                                            <h2 class="dashboard-analytics-box-header">{l s='Transactions' mod='g_upsellpro'}</h2>
                                            <div class="dashboard-analytics-box-content text-center">
                                                {$transactions|escape:'html':'UTF-8'}
                                            </div>
                                        </div>
                                        <div class="dashboard-analytics-box-content-box color4" data-item="sales">
                                            <h2 class="dashboard-analytics-box-header">{l s='Sales' mod='g_upsellpro'}</h2>
                                            <div class="dashboard-analytics-box-content text-center">
                                                {$sales|escape:'html':'UTF-8'}
                                            </div>
                                        </div>
                                        <div class="dashboard-analytics-box-content-box color5" data-item="take_rate">
                                            <h2 class="dashboard-analytics-box-header">{l s='Take Rate' mod='g_upsellpro'}</h2>
                                            <div class="dashboard-analytics-box-content text-center">
                                                {$take_rate|escape:'html':'UTF-8'}
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="panel col-lg-12">
                                <div id="mainchart2" class="gchart_box"><svg></svg></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2> {l s='Upsells Overview' mod='g_upsellpro'} </h2>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="panel col-lg-12 gupdell-table-listsetting-analytic">
                                <div class="gupsell_overlay"><div class="container"><div class="content"><div class="circle"></div></div></div></div>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{l s='Upsell Name' mod='g_upsellpro'}</th>
                                            <th>{l s='Offer Views' mod='g_upsellpro'}</th>
                                            <th>{l s='Add To Carts' mod='g_upsellpro'}</th>
                                            <th>{l s='Transactions' mod='g_upsellpro'}</th>
                                            <th>{l s='Sales' mod='g_upsellpro'}</th>
                                            <th class="text-right">{l s='Take Rate' mod='g_upsellpro'}</th>
                                        </tr> 
                                    </thead>
                                    <tbody>
                                        <td class="list-empty" colspan="6">
                                            <div class="list-empty-msg">
                                                <i class="icon-warning-sign list-empty-icon"></i>
                                                {l s='No records found' mod='g_upsellpro'}
                                            </div>
                                        </td>
                                    </tbody>
                                </table>
                                <div class="row">
                                    <div class="col-lg-4"></div>
                                    <div class="col-lg-4 text-center">
                                        <div class="pagination">
                                            {l s='Display' mod='g_upsellpro'}
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                <span class="page_show_items_active">20</span>
                                            <i class="icon-caret-down"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="javascript:void(0);" class="Analytic-pagination-items-page" data-items="20" data-list-id="Analytic">20</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" class="Analytic-pagination-items-page" data-items="50" data-list-id="Analytic">50</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" class="Analytic-pagination-items-page" data-items="100" data-list-id="Analytic">100</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" class="Analytic-pagination-items-page" data-items="300" data-list-id="Analytic">300</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" class="Analytic-pagination-items-page" data-items="1000" data-list-id="Analytic">1000</a>
                                                </li>
                                            </ul>
                                            / <span class="page_show_items">20</span> {l s='result(s)' mod='g_upsellpro'}
                                            <input type="hidden" id="Analytic-pagination-items-page" name="gupsell_pagination" value="20">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 pagination-active">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group pull-center">
                        <a type="button" class="gbtn-default btn btn-default" href="{$link->getAdminLink('AdminGupselloffers')|escape:'html':'UTF-8'}&management=helperform">{l s='New Offer' mod='g_upsellpro'}</a>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}