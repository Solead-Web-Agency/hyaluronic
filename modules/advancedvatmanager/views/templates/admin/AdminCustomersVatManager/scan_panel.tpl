{**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
<div class="panel col-lg-12 scan_panel">
    <div class="panel-heading">
        <i class="fal fa-rocket"></i> <span class="panel-heading-title">{l s='VAT Number Scanner System' mod='advancedvatmanager'}</span>
    </div>
    <div class="alert alert-info">
        <li>{l s='If this module is just installed recently and you have not performed first scan, you should do it in order to validate and save customers VAT number information in the module database table.' mod='advancedvatmanager'}</li>
        <li>{l s='Whenever you make changes to the [Validation settings - VAT field settings - VAT exemption settings] options, you must perform a new scan to validate the VAT numbers with the new conditions configured in the mentioned options.' mod='advancedvatmanager'}</li>
    </div>
    <div class="alert alert-success">
        <p>{l s='The scan feature will analyze and check VAT numbers from customer addresses with the following conditions:' mod='advancedvatmanager'}</p>
        <ul>
            {if !empty($countries)}<li>{l s='Customer addresses located in one of the following countries:' mod='advancedvatmanager'} <strong>{l s='%s' sprintf=[$countries]  mod='advancedvatmanager'}</strong></li>{/if}
            <li>{l s='The VAT number field is set as' mod='advancedvatmanager'} <strong>{l s='%s' sprintf=[$field_condition]  mod='advancedvatmanager'}</strong></li>
            {if $show_with_company}<li>{l s='The VAT number field is displayed and validated only when customers have filled the company field' mod='advancedvatmanager'}</li>{/if}
            {if $allow_duplicated}<li>{l s='Duplicated VAT numbers are allowed' mod='advancedvatmanager'}</li>{else}<li>{l s='Duplicated VAT numbers are not allowed' mod='advancedvatmanager'}</li>{/if}
        </ul>
    </div>
    <div class="alert alert-warning">
        <p>{l s='If the scanning process is interrupted for any reason or an error during the process, you can resume scanning again from the last address scanned by choosing the [Continue scan process from last address scanned] option.' mod='advancedvatmanager'}</p>
    </div>
    <div style="display:none;" class="row avm_process_container progress_circle_container">
        <div class="progress_circle"></div>
        <div class="percent_progress"></div>
    </div>
    <div class="row avm_process_container">
        <div id="avm_process_timer"></div>
        <div style="display:none;" id="avm_scanstatus_container">
            <span class="avm_scanstatus"></span>
        </div>
        <div id="avm_scanbutton" class="scan_btn">
            <button id="open_scan_btn" class="btn btn-default" name="open_scan"><span class="icon_container"><i class="fab fa-searchengin"></i></span>&nbsp;{l s='Scan VAT numbers' mod='advancedvatmanager'}</button>
            <button style="display:none" id="stop_scan_btn" class="btn btn-danger" name="stop_scan"><i class="fal fa-stop-circle"></i>&nbsp;{l s='Stop scan' mod='advancedvatmanager'}</button>
            <button style="display:none" id="reload_btn" class="btn btn-primary" name="reload_page"><i class="fad fa-sync"></i>&nbsp;{l s='Reload page' mod='advancedvatmanager'}</button>
            <a type="button" style="display:none" id="download_list_btn" class="btn btn-success" name="download_list" href="{$href|escape:'htmlall':'UTF-8'}"><i class="fal fa-download"></i>&nbsp;{l s='Download Customers VAT List' mod='advancedvatmanager'}</a>
        </div>
    </div>
    <div style="display:none" id="avm_results_container">
        <div class="results_header"><h2><i class="fal fa-crosshairs"></i> {l s='Results' mod='advancedvatmanager'}</h2></div>
        <div class="results_data">
            <div class="row">
                <div class="col-lg-12"><span class="total"><i class="fal fa-map-signs"></i> {l s='Total:' mod='advancedvatmanager'} <span class="total_value"></span></span></div>     
            </div>
            <div class="row">
                <div class="col-lg-12"><span class="valid"><i class="fal fa-check-circle"></i> {l s='Valid VAT:' mod='advancedvatmanager'} <span class="valid_value"></span></span></div>              
            </div>
            <div class="row">
                <div style="display:none" class="col-lg-12 valid_skipping_validation"><span class="valid_skipping_validation"><i class="fal fa-exclamation-triangle"></i> {l s='Valid VAT skipping validation:' mod='advancedvatmanager'} <span class="valid_skipping_validation_value"></span></span></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><span class="withEmpty"><i class="fal fa-exclamation-triangle"></i> {l s='Empty VAT:' mod='advancedvatmanager'} <span class="empty_value"></span></span></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><span class="{if $allow_duplicated}allow_duplicated{else}duplicated{/if}"><i class="{if $allow_duplicated}fal fa-info-circle{else}fal fa-exclamation-triangle{/if}"></i> {l s='Duplicated VAT:' mod='advancedvatmanager'} <span class="duplicated_value"></span></span></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><span class="withError"><i class="fal fa-exclamation-circle"></i> {l s='Invalid VAT:' mod='advancedvatmanager'} <span class="error_value"></span></span></div>
            </div>
            <div class="row">
                {if $company_validation}<div class="col-lg-12"> <span class="invalidCompany"><i class="fal fa-exclamation-circle"></i> {l s='Invalid Company:' mod='advancedvatmanager'} <span class="invalid_company_value"></span></span></div>{/if}
            </div>
            <div class="row">
                {if $company_validation}<div class="col-lg-12"> <span class="emptyCompany"><i class="fal fa-exclamation-circle"></i> {l s='Empty Company:' mod='advancedvatmanager'} <span class="empty_company_value"></span></span></div>{/if}
            </div> 
        </div>
        <div class="avm_results"></div>
    </div>
    <div id="avm_download_container">
    </div>
</div>

<!-- Modal for actions START -->
<div id="avm_perform_scan" class="avm_modal_perform_actions" data-sm-init="true">
    <div class="slickmodal-header">
        <div class="row slickmodal-title">
            <h2 class="title"><i class="fal fa-cogs"></i> {l s='Scanner options' mod='advancedvatmanager'}</h2>
        </div>
    </div>
    <div class="slickmodal-body">
        <div class="alert alert-warning">{l s='Send email options and delete options are not compatible if they are enabled both.' mod='advancedvatmanager'}</div>
        <div class="alert alert-info">{l s='This process could take some minutes or hours depends on number of addresses stored in database, please be patient and wait until process was finished and do not close the browser windows.' mod='advancedvatmanager'}</div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label"><i class="fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="{l s='This option must be enabled if you want the system to continue scanning from the last address scanned. This is useful when the last scanning process failed or if you do not want the system to rescan from the first address saved in the database.' mod='advancedvatmanager'}"></i> {l s='Continue scan process from last address scanned.' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="scanfromlast_mode" name="scanfromlast_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label"><i class="fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="{l s='This option skips validation in case the VIES/GOV.UK API system fails during the validation process. The VAT number will appear as [Validated] and the system status will appear in red color as [API request processing error] in VAT Management list.' mod='advancedvatmanager'}"></i> {l s='Skip API system fail' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="skip_apisystemfails" name="skip_apisystemfails" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label">{l s='Delete address with empty VAT number field' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="remove_empty_mode" name="remove_empty_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label">{l s='Delete address with invalid VAT number field' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="remove_invalid_mode" name="remove_invalid_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label"><i class="fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="{l s='This option deletes the last customer addresses which VAT number is duplicate and keeping the first one created.' mod='advancedvatmanager'}"></i> {l s='Delete address with duplicated VAT number' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="remove_duplicated_mode" name="remove_duplicated_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label">{l s='Send email to customers with invalid VAT number field' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="sendEmail_invalid_mode" name="sendEmail_invalid_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label">{l s='Send email to customers with empty VAT number field' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="sendEmail_empty_mode" name="sendEmail_empty_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label">{l s='Send email to customers with duplicated VAT number field' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="sendEmail_duplicated_mode" name="sendEmail_duplicated_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label">{l s='Manager and assign customer groups as configured in the "Customer group management" section' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="assign_group_mode" name="assign_group_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label"><i class="fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="{l s='This option will overwrite the company name stored previously by the client.' mod='advancedvatmanager'}"></i> {l s='Fill company field in address with company registered in VIES or GOV.UK when VAT number is valid' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="fill_company_name" name="fill_company_name" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label">{l s='Send email to customers with not valid company name registered' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="sendEmail_invalidcompany_mode" name="sendEmail_invalidcompany_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
            {if !$company_validation}<small>{l s='Option disabled because company validation option is disabled' mod='advancedvatmanager'}</small>{/if}
        </div>
        <div class="form-check scan_mode_switch">
            <p class="scan_mode_label">{l s='Send email to customers with empty company name' mod='advancedvatmanager'}</p>
            <input type="checkbox" class="avm_switch toggle-checkbox" id="sendEmail_emptycompany_mode" name="sendEmail_emptycompany_mode" data-size="mini" data-toggle="switch" data-on-text="{l s='ON' mod='advancedvatmanager'}" data-off-text="{l s='OFF' mod='advancedvatmanager'}">
            {if !$company_validation}<small>{l s='Option disabled because company validation option is disabled' mod='advancedvatmanager'}</small>{/if}
        </div>
        <button type="button" name="submit_scan" class="btn btn-default"><span><i class="far fa-check-circle"></i> {l s='Start scan' mod='advancedvatmanager'}</span></button>
        <button type="button" name="submit_cancel_scan" class="btn btn-danger"><span><i class="far fa-times-circle"></i> {l s='Cancel' mod='advancedvatmanager'}</span></button>
    </div>
</div>
<!-- Modal for actions END -->