{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2020, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-list-ul"></i> {l s='Import Rules' mod='elegantaleasyimport'}
            <span class="panel-heading-action">
                <a class="list-toolbar-btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=settings">
                    <span class="label-tooltip">
                        <i class="process-icon-cogs"></i>
                    </span>
                </a>
            </span>
        </div>
        <div class="panel-body">
            {if $models}
                <div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    {l s='Name' mod='elegantaleasyimport'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=name&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=name&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th>
                                    {l s='Import Entity' mod='elegantaleasyimport'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=entity&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=entity&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th>
                                    {l s='Import Type' mod='elegantaleasyimport'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=is_cron&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=is_cron&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th class="text-center">
                                    {l s='Last Import Date' mod='elegantaleasyimport'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=last_import_date&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=last_import_date&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th class="text-center">
                                    {l s='Status' mod='elegantaleasyimport'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=active&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy=active&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th style="min-width:135px">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$models item=model}
                                <tr>
                                    <td data-csv="{$model.csv_file|escape:'html':'UTF-8'}">
                                        {$model.name|escape:'html':'UTF-8'}
                                    </td>
                                    <td>
                                        {$model.entity|ucfirst|escape:'html':'UTF-8'}
                                    </td>
                                    <td>
                                        {if $model.is_cron}
                                            {l s='CRON' mod='elegantaleasyimport'}
                                            {if $model.last_import_date && isset($model.remaining_rows) && $model.remaining_rows > 0}
                                                <span class="label label-warning cron_label">
                                                    {l s='In Progress' mod='elegantaleasyimport'} {$model.finished_percent|intval}%
                                                </span>
                                            {elseif $model.last_import_date && (!isset($model.remaining_rows) || !$model.remaining_rows)}
                                                <span class="label label-success cron_label">
                                                    {l s='Finished' mod='elegantaleasyimport'} 100%
                                                </span>
                                            {else}
                                                <span class="label label-warning cron_label">
                                                    {l s='In Progress' mod='elegantaleasyimport'} 0%
                                                </span>
                                            {/if}
                                        {else}
                                            {l s='Manual' mod='elegantaleasyimport'}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $model.last_import_date}
                                            {$model.last_import_date|escape:'html':'UTF-8'|date_format:'%e %b %Y %H:%M:%S'}
                                        {else}
                                            -
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $model.active}
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importChangeStatus&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                <i class="icon-check" style="color: #72C279"></i>
                                            </a>
                                        {else}
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importChangeStatus&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                <i class="icon-remove" style="color: #E08F95"></i>
                                            </a>
                                        {/if}
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group" role="group">
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importEdit&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}" class="btn btn-default">
                                                <i class="icon-edit"></i> {l s='Edit Rule' mod='elegantaleasyimport'}
                                            </a>
                                            <a href="" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importCronInfo&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                        <i class="icon-time"></i> {l s='Setup CRON Job' mod='elegantaleasyimport'}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=triggerCron&type=import&id={$model.id_elegantaleasyimport|intval}">
                                                        <i class="icon-dot-circle-o"></i> {l s='Trigger CRON Run' mod='elegantaleasyimport'}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importMapping&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                        <i class="icon-random"></i> {l s='Edit Mapping' mod='elegantaleasyimport'}
                                                    </a>
                                                </li>
                                                {if $model.map}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=import&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                            <i class="icon-repeat"></i> {l s='Re-import' mod='elegantaleasyimport'}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=manageCategory&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                            <i class="icon-edit"></i> {l s='Manage Category' mod='elegantaleasyimport'}
                                                        </a>
                                                    </li>
                                                {/if}
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importErrorLog&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                        <i class="icon-list-alt"></i> {l s='Error Log' mod='elegantaleasyimport'}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importDuplicate&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                        <i class="icon-copy"></i> {l s='Duplicate' mod='elegantaleasyimport'}
                                                    </a>
                                                </li>
                                                {if $model.active == 1}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importChangeStatus&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                            <i class="icon-off"></i> {l s='Disable' mod='elegantaleasyimport'}
                                                        </a>
                                                    </li>
                                                {else}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importChangeStatus&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}">
                                                            <i class="icon-off"></i> {l s='Enable' mod='elegantaleasyimport'}
                                                        </a>
                                                    </li>
                                                {/if}
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importDelete&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}" onclick="return confirm('Are you sure?')">
                                                        <i class="icon-trash"></i> {l s='Delete' mod='elegantaleasyimport'}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    {*START PAGINATION*}
                    {if $pages > 1}
                        {assign var="pMax" value=2 * $halfVisibleLinks + 1} {*Number of visible pager links*}
                        {assign var="pStart" value=$currentPage - $halfVisibleLinks} {*Starter link*}
                        {assign var="moveStart" value=$currentPage - $pages + $halfVisibleLinks} {*Numbers that pStart can be moved left to fill right side space*}
                        {if $moveStart > 0}
                            {assign var="pStart" value=$pStart - $moveStart}
                        {/if}                                    
                        {if $pStart < 1}
                            {assign var="pStart" value=1}
                        {/if}
                        {assign var="pNext" value=$currentPage + 1} {*Next page*}
                        {if $pNext > $pages}
                            {assign var="pNext" value=$pages}
                        {/if}
                        {assign var="pPrev" value=$currentPage - 1} {*Previous page*}
                        {if $pPrev < 1}
                            {assign var="pPrev" value=1}
                        {/if}
                        <div class="text-center">
                            <br>
                            <nav>
                                <ul class="pagination pagination-sm">
                                    {if $pPrev < $currentPage}
                                        <li>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page=1" aria-label="Previous">
                                                <span aria-hidden="true">&lt;&lt; {l s='First' mod='elegantaleasyimport'}</span>
                                            </a>
                                        </li>
                                        {if $pPrev > 1}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pPrev|intval}" aria-label="Previous">
                                                    <span aria-hidden="true">&lt; {l s='Prev' mod='elegantaleasyimport'}</span>
                                                </a>
                                            </li>
                                        {/if}
                                    {/if}
                                    {for $i=$pStart to $pages max=$pMax}
                                        <li{if $i == $currentPage} class="active" onclick="return false;"{/if}>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$i|intval}">{$i|intval}</a>
                                        </li>
                                    {/for}
                                    {if $pNext > $currentPage && $pNext <= $pages}
                                        {if $pNext < $pages}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pNext|intval}" aria-label="Next">
                                                    <span aria-hidden="true">{l s='Next' mod='elegantaleasyimport'} &gt;</span>
                                                </a>
                                            </li>
                                        {/if}
                                        <li>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pages|intval}" aria-label="Next">
                                                <span aria-hidden="true">{l s='Last' mod='elegantaleasyimport'} &gt;&gt;</span>
                                            </a>
                                        </li>
                                    {/if}
                                </ul>
                            </nav>
                        </div>
                    {/if}
                    {*END PAGINATION*}
                </div>
            {else}
                <div style="padding: 20px; color: #999; text-align: center; font-size: 22px;">
                    {l s='You have not created import rules yet' mod='elegantaleasyimport'}
                </div>
            {/if}
        </div>
        <div class="panel-footer clearfix elegantal_panel_footer">
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importEdit" class="btn btn-primary btn-lg pull-right">
                <i class="icon-cloud-download"></i> {l s='New Import' mod='elegantaleasyimport'}
            </a>
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=exportList" class="btn btn-default btn-lg pull-right">
                <i class="icon-cloud-upload"></i> {l s='Export Products' mod='elegantaleasyimport'}
            </a>
            <a href="{$contactDeveloperUrl|escape:'html':'UTF-8'}" target="_blank" class="btn btn-default btn-lg pull-right">
                <i class="icon-envelope-o"></i> {l s='Contact Developer' mod='elegantaleasyimport'}
            </a>
            <a href="{$rateModuleUrl|escape:'html':'UTF-8'}" target="_blank" class="btn btn-default btn-lg pull-right">
                <i class="icon-star"></i> {l s='Rate Module' mod='elegantaleasyimport'}
            </a>
            {if $documentationUrls}
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-default btn-lg dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-file-text-o"></i> {l s='Documentation' mod='elegantaleasyimport'} 
                        v{$version|escape:'html':'UTF-8'} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" style="left: 0; right: auto;">
                        {foreach from=$documentationUrls key=docLang item=documentationUrl}
                            <li>
                                <a href="{$documentationUrl|escape:'html':'UTF-8'}" target="_blank">
                                    {if $docLang == 'en'}
                                        {l s='English' mod='elegantaleasyimport'}
                                    {elseif $docLang == 'fr'}
                                        {l s='French' mod='elegantaleasyimport'}
                                    {elseif $docLang == 'de'}
                                        {l s='German' mod='elegantaleasyimport'}
                                    {elseif $docLang == 'it'}
                                        {l s='Italian' mod='elegantaleasyimport'}
                                    {elseif $docLang == 'pt'}
                                        {l s='Portuguese' mod='elegantaleasyimport'}
                                    {elseif $docLang == 'es'}
                                        {l s='Spanish' mod='elegantaleasyimport'}
                                    {elseif $docLang == 'ru'}
                                        {l s='Russian' mod='elegantaleasyimport'}
                                    {else}
                                        {$docLang|escape:'html':'UTF-8'}
                                    {/if}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        </div>
    </div>
</div>