{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2020, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cloud-upload"></i> {l s='Step' mod='elegantaleasyimport'}3: {l s='Export Products' mod='elegantaleasyimport'}
        </div>
        <div class="panel-body">
            <div class="row elegantal_export_panel" data-id="{$model.id_elegantaleasyimport_export|intval}" data-reloadmsg="{l s='Export has not finished yet.' mod='elegantaleasyimport'}">
                <div class="col-xs-12 col-md-offset-2 col-md-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <i class="icon-time"></i> 
                            {if $model.entity=='product'}
                                {l s='Exporting Products' mod='elegantaleasyimport'}...
                            {elseif $model.entity=='combination'}
                                {l s='Exporting Combinations' mod='elegantaleasyimport'}...
                            {/if}
                        </div>
                        <div class="panel-body">
                            <div class="row elegantal_progress_row">
                                <div class="col-xs-12">
                                    <br>
                                    <div class="elegantal_ajax_loader">
                                        <img src="{$moduleUrl|escape:'html':'UTF-8'}views/img/loading.gif" alt="Wait...">
                                    </div>
                                </div>
                                <div class="col-xs-12 text-center">
                                    <br>
                                    {l s='Please wait. It may take a few minutes.' mod='elegantaleasyimport'}
                                </div>
                            </div>
                            <div class="row elegantal_hidden elegantal_result_row">
                                <div class="col-xs-12 text-center">
                                    <div class="module_confirmation conf confirm alert alert-success text-left">
                                        <span class="elegantal_result_txt">
                                            {if $model.entity=='product'}
                                                {l s='_count products exported successfully.' mod='elegantaleasyimport'}
                                            {elseif $model.entity=='combination'}
                                                {l s='_count combinations exported successfully.' mod='elegantaleasyimport'}
                                            {/if} 
                                            {l s='Export file is accessible from the link below:' mod='elegantaleasyimport'}
                                        </span>
                                    </div>
                                    <div class="alert alert-info alert_with_link_icon text-left">
                                        {$download_link|escape:'html':'UTF-8'}
                                    </div>
                                    <br>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=exportList" class="btn btn-default">
                                        <i class="icon-arrow-circle-left"></i> {l s='EXPORT RULES' mod='elegantaleasyimport'}
                                    </a> 
                                    <a href="{$download_link|escape:'html':'UTF-8'}" class="btn btn-primary" target="_blank">
                                        <i class="icon-download"></i> {l s='Download File' mod='elegantaleasyimport'}
                                    </a>
                                </div>
                            </div>
                            <div class="bootstrap elegantal_hidden elegantal_error">
                                <div class="module_error alert alert-danger">
                                    <span class="elegantal_error_txt"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="elegantaleasyimportJsDef" data-adminurl="{$adminUrl|escape:'html':'UTF-8'}"></div>
</div>