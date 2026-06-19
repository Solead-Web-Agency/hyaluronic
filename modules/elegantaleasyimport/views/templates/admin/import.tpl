{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2020, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-refresh"></i> {l s='Step 3: Import CSV' mod='elegantaleasyimport'}
        </div>
        <div class="panel-body">
            <div class="row elegantal_import_panel" data-id="{$model.id_elegantaleasyimport|intval}" data-limit="{$limit|intval}" data-reloadmsg="{l s='Import has not finished yet.' mod='elegantaleasyimport'}">
                <div class="col-xs-12 col-md-offset-2 col-md-8">
                    <div class="bootstrap elegantal_hidden elegantal_error">
                        <div class="module_error alert alert-danger">
                            <span class="elegantal_error_txt"></span>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-heading">
                            <i class="icon-time"></i> 
                            <span class="elegantal_prepare_csv_txt">
                                {l s='Analyzing CSV File...' mod='elegantaleasyimport'}
                            </span>
                            <span class="elegantal_import_csv_txt elegantal_hidden">
                                {l s='Importing [n] Products...' mod='elegantaleasyimport'}
                            </span>
                        </div>
                        <div class="panel-body">
                            <br><br>
                            <div class="row elegantal_progress_row">
                                <div class="col-xs-12">
                                    <div class="progress">
                                        <div class="elegantal_progress_bar progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="min-width: 3em; width: 0%;">
                                            0%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 text-center">
                                    {l s='Please wait. It may take a few minutes.' mod='elegantaleasyimport'}
                                </div>
                            </div>
                            <div class="row elegantal_hidden elegantal_result_row">
                                <div class="col-xs-12 text-center">
                                    <div class="module_confirmation conf confirm alert alert-success text-left">
                                        <span class="elegantal_result_txt">
                                            {l s='Congratulations! Products data have been imported successfully.' mod='elegantaleasyimport'}
                                        </span>
                                    </div>
                                    <br>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importEdit" class="btn btn-primary">
                                        <i class="icon-cloud-upload"></i> {l s='Start New Import' mod='elegantaleasyimport'}
                                    </a>
                                    <br><br>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}">
                                        <i class="icon-angle-left"></i> {l s='Main Page' mod='elegantaleasyimport'}
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=importErrorLog&id_elegantaleasyimport={$model.id_elegantaleasyimport|intval}" class="elegantal_log_view_btn">
                                        {l s='View Logs' mod='elegantaleasyimport'} <i class="icon-angle-right"></i>
                                    </a>
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