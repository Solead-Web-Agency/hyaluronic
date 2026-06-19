/**
 * 2017-2022 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2022 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */
var timer = new easytimer.Timer();// Timer
var messages = [];// Messages container array
var position_available = [];
var finish = false;
var button_content = [];
var total_addresses = [];
var addresses = [];
var options = [];
var query = 0;
var progress_bar = 0;
var step_progress = 0;
var empty_vat = [];
var duplicated_vat = [];
var invalid_vat = [];
var invalid_company, empty_company = [];
var valid_vat = [];
var valid_vat_skipping_validation = [];
var customer_toassigngroup;
var ajaxCheckTotalAddress, ajaxCheckVATNumber, ajaxremoveEmptyVAT, ajaxremoveInvalidVAT, ajaxCreateFile, ajaxsendEmail, ajaxProcess, ajaxResetListTable, ajaxremoveDuplicatedVAT, ajaxCheckCustomerVAT; 

$(document).ready(function(e){  
    
    $('[data-toggle="tooltip"]').tooltip();// Tooltip
    
    // Toggle checkbox
    $(".avm_switch").bootstrapSwitch();
    
    // Slick modal before performing actions
    $('.avm_modal_perform_actions').SlickModals({
        popup_animation: 'zoomIn',
        popup_position: 'center',
        popup_allowMultipleInstances: true,
        popup_css: {
            'width': '100%',
            'height': '100%',
            'max-width': '950px',
            'overflow-y': 'scroll',
            'overflow-x': 'hidden',
            'background': '#fff',
            'padding': '0px',
            'margin': 'auto',
            'border': '5px solid #3498db'
        },
        popup_enableESC: false,
        popup_closeButtonEnable: false,
        overlay_isVisible: true,        
        overlay_css: {
            'background': 'rgba(0, 0, 0, 0.4)'
        },
        overlay_closesPopup: false,
        mobile_breakpoint: '420px',
        mobile_position: 'center',
        mobile_css: {
            'width': '100%',
            'height': '100%',
            'padding': '25px',
            'margin': '0',
            'background': '#fff'
        }
    });
    
    // Slick modal to check VAT by VIES or GOV.UK
    $('.avm_modal_check_vat').SlickModals({
        popup_animation: 'zoomIn',
        popup_position: 'center',
        popup_allowMultipleInstances: true,
        popup_css: {
            'width': '100%',
            'height': '500px',
            'max-width': '650px',
            'overflow-x': 'hidden',
            'background': '#fff',
            'padding': '20px',
            'margin': 'auto',
            'border': '5px solid #3498db'
        },
        popup_enableESC: true,
        popup_closeButtonEnable: true,
        popup_closeButtonEnable: true,
        popup_closeButtonStyle: 'cancel simple',
        popup_closeButtonAlign: 'right',
        popup_closeButtonPlace: 'outside',
        overlay_isVisible: true,        
        overlay_css: {
            'background': 'rgba(0, 0, 0, 0.4)'
        },
        overlay_closesPopup: true,
        mobile_breakpoint: '420px',
        mobile_position: 'center',
        mobile_css: {
            'width': '100%',
            'height': '100%',
            'padding': '25px',
            'margin': '0',
            'background': '#fff'
        }
    });
    
    // Progress bar circle
    $('.avm_process_container .progress_circle').circleProgress({
        max: 100,
        min: 0,
        size: 170,
        thickness: '15',
        fill: {
            gradient: [['#3498db']], 
        },
        startAngle: -Math.PI/4*2,
        value: 0,
        emptyFill: 'rgb(0 196 255 / 25%)',
        animation: { duration: 500, easing: "circleProgressEasing" },
    });
    // Reset progress bar percent
    $('.avm_process_container .percent_progress').text('0%');
    
    // Scan process
    $('#open_scan_btn').on('click', function(){
        $('#avm_perform_scan').SlickModals('openPopup', 'instant');// Open modal 
        manageOptions();
         
        $('button[name="submit_cancel_scan"]').on('click', function(){
            $('#avm_perform_scan').SlickModals('closePopup', 'instant');// Close modal       
        }); 
        
        $('button[name="submit_scan"]').on('click', function(){
            startProcess('open_scan_btn');
            $('#avm_perform_scan').SlickModals('closePopup', 'instant');// Close modal  
            options['remove_empty_mode'] = $('input[name="remove_empty_mode"]').is(':checked');
            options['remove_duplicated_mode'] = $('input[name="remove_duplicated_mode"]').is(':checked');
            options['remove_invalid_mode'] = $('input[name="remove_invalid_mode"]').is(':checked');
            options['sendEmail_invalid_mode'] = $('input[name="sendEmail_invalid_mode"]').is(':checked');
            options['sendEmail_empty_mode'] = $('input[name="sendEmail_empty_mode"]').is(':checked');
            options['sendEmail_duplicate_mode'] = $('input[name="sendEmail_duplicated_mode"]').is(':checked');
            options['assign_group_mode'] = $('input[name="assign_group_mode"]').is(':checked');
            options['fill_company_name'] = $('input[name="fill_company_name"]').is(':checked');
            options['sendEmail_invalidcompany_mode'] = $('input[name="sendEmail_invalidcompany_mode"]').is(':checked');
            options['sendEmail_emptycompany_mode'] = $('input[name="sendEmail_emptycompany_mode"]').is(':checked');
            options['scanfromlast_mode'] = $('input[name="scanfromlast_mode"]').is(':checked');
            options['skip_apisystemfails'] = $('input[name="skip_apisystemfails"]').is(':checked');
            
            scanProcess();
        }); 
        $('button[name="reload_page"]').on('click', function(){
            location.reload(); 
        });                                
    });
    
    $('a.checkCustomerVAT').on('click', function(){
        var vat = $(this).prev().val();
        var customer_vat = $(this).prev().prev().val();
        var country_iso = $(this).prev().prev().prev().val();
        $('#avm_checkCustomerVAT').SlickModals('openPopup', 'instant');// Open modal    
        checkCustomerVAT(vat, customer_vat, country_iso);                         
    });
    
    $(document).ajaxSend(function(e, xhr, opt){
        // Prevent notifications ajax (defaul Prestashop process) to avoid overload system.
        if (opt.url.toLowerCase().indexOf("notifications") >= 0) {
            e.preventDefault();
            xhr.abort();
        }
        // Stop scan
        $('button[name="stop_scan"]').on('click', function(){
            if (opt.id == 'checkVATNumber') {
                stopProcess(xhr);
            }
        });       
    });
});

/** 
* Perform Check total address
*/
function checkTotalAddress()
{
    ajaxCheckTotalAddress = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'checkTotalAddress',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'checkTotalAddress',
            scanFromLastMode: options['scanfromlast_mode']
        },
        beforeSend : function () { 
            SetprogressBar(2)
            $('.avm_scanstatus').html(get_total_addresses+'...').fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            if (jsonData['addresses'] !== false) {
                total_addresses = jsonData['total'];
                addresses = jsonData['addresses'];
                displayMessageOnLive(jsonData['message']);
                setTimeout(function(){ 
                    SetprogressBar(8);
                    $('.avm_scanstatus').html(total_addresses+' '+addresses_found).fadeIn(300);
                    $('.results_data .total_value').html(total_addresses).fadeIn(300);                 
                }, 3000);
            }
            else {
                displayMessageOnLive(jsonData['message']);
                $('.avm_scanstatus').html(empty_addresses).fadeIn(300);
                finishProcess(true);
                finish = true;
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            finishProcess(false);
        }
    });
}


    /** 
    * Perform Check VAT in VIES or GOV.UK
    */
    function checkCustomerVAT(vat, customer, country_iso)
    {
        ajaxCheckCustomerVAT = $.ajax({
            url: ajax_url_avm_scanner,
            id: 'checkCustomerVAT',
            type : 'POST',
            headers: { "cache-control": "no-cache" },
            dataType : 'json',
            async: true,
            cache: false,
            data : {
                controller: 'AdminCustomersVatManager',
                ajax: true,
            	action: 'checkCustomerVAT',
                vat_number: vat,
                customer: customer,
                country_iso: country_iso,
            },
            beforeSend : function () { 
                $('.loading_container').show();
                $('.vat-result .invalid, .vat-result .valid, .vat-result').hide();
                $('span.customer').html('');
                $('span.vat_number').html('');
                $('span.country_iso').html('');
                $('span.company').html('');
                $('span.address').html('');
                $('span.request_date').html('');
                $('span.check_system').html(''); 
                $('span.valid_message span.invalid_message').html('');       
            },
            success : function (jsonData) {
                $('.loading_container').hide();
                $('.vat-result').show();    
                if (jsonData['valid'] === true) {
                    $('span.valid_message').html(jsonData['message']);
                    $('.vat-result .valid').show();
                }
                else {
                    $('span.invalid_message').html(jsonData['message']);
                    $('.vat-result .invalid').show();    
                }
                $('span.customer').html(jsonData['customer']);
                $('span.vat_number').html(jsonData['vat_iso']+jsonData['vat_number']);
                $('span.country_iso').html(jsonData['vat_iso']);
                $('span.company').html(jsonData['company']);
                $('span.address').html(jsonData['address']);
                $('span.request_date').html(jsonData['request_date']);
                $('span.check_system').html(jsonData['check_system']);  
            },
            error : function (jqXHR, textStatus, errorThrown) {
                $('.loading_container').hide();
                $('span.invalid_message').html(errorThrown);
                $('.vat-result').show();
                $('.vat-result .invalid').show();   
            }
        });
    }

/** 
* Perform Check total address
*/
function cleanCache()
{
    ajaxCheckTotalAddress = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'cleanCache',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'cleanCache',
        },
        beforeSend : function () { 
        },
        success : function (jsonData) {
            if (jsonData['success']) {
                $('#reload_btn').show();
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            $('#reload_btn').show();
        }
    });
}

/** 
* Perform Check VAT number
*/
function checkVATNumber()
{
    ajaxProcess = ajaxCheckVATNumber = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'checkVATNumber',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'checkVATNumber',
            address: addresses[query],
            autofillcompany: Number(options['fill_company_name']),
            skip_apisystemfails: Number(options['skip_apisystemfails']),
        },
        beforeSend : function () { 
            $('#stop_scan_btn').show();// Show stop scan button
            $('.avm_scanstatus').html(checking_vat_numbers+'... <br />['+(Object.keys(addresses).length - query)+'] '+remaining_addresses).fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            query += 1;
            SetprogressBar();
            displayMessageOnLive(jsonData['message_onlive']);
            if (Object.keys(jsonData['empty_vat']).length > 0) {
                insertMessageContainer('warning', jsonData['message']);
                empty_vat.push(jsonData['empty_vat']); 
            }
            else if (Object.keys(jsonData['duplicated_vat']).length > 0) {
                insertMessageContainer((allow_duplicated?'info':'warning'), jsonData['message']);
                duplicated_vat.push(jsonData['duplicated_vat']);    
            }
            else if (Object.keys(jsonData['invalid_vat']).length > 0) {
                insertMessageContainer('danger', jsonData['message']);
                invalid_vat.push(jsonData['invalid_vat']);    
            }
            else if (Object.keys(jsonData['invalid_company']).length > 0) {
                insertMessageContainer('warning', jsonData['message']);
                invalid_company.push(jsonData['invalid_company']);    
            }
            else if (Object.keys(jsonData['empty_company']).length > 0) {
                insertMessageContainer('warning', jsonData['message']);
                empty_company.push(jsonData['empty_company']);    
            }
            else if (Object.keys(jsonData['valid_vat']).length > 0) {
                insertMessageContainer('success', jsonData['message']);                   
                valid_vat.push(jsonData['valid_vat']); 
                customer_toassigngroup.push(jsonData['valid_vat']);                   
            }
            else if (Object.keys(jsonData['valid_vat_skipping_validation']).length > 0) {
                insertMessageContainer('warning', jsonData['message']);
                valid_vat_skipping_validation.push(jsonData['valid_vat']); 
                customer_toassigngroup.push(jsonData['valid_vat_skipping_validation']);      
            }
            $('.results_data .empty_value').html(Object.keys(empty_vat).length).fadeIn(300);
            $('.results_data .duplicated_value').html(Object.keys(duplicated_vat).length).fadeIn(300);
            $('.results_data .error_value').html(Object.keys(invalid_vat).length).fadeIn(300);
            $('.results_data .invalid_company_value').html(Object.keys(invalid_company).length).fadeIn(300);
            $('.results_data .empty_company_value').html(Object.keys(empty_company).length).fadeIn(300);
            $('.results_data .valid_value').html(Object.keys(valid_vat).length).fadeIn(300);
            $('.results_data .valid_skipping_validation_value').html(Object.keys(valid_vat_skipping_validation).length).fadeIn(300);
            
            if (query < Object.keys(addresses).length ) {
                checkVATNumber();
            }
            else {
                $('.avm_scanstatus').html(checking_vat_numbers+'... <br />[0] '+remaining_addresses).fadeIn(300);
                finish = true;
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 0) {
                if (jqXHR.statusText !== 'abort') {
                    finishProcess(false);
                }
                else {
                    finishProcess(false, true);
                    $('#open_scan_btn').html(getButtonContent('open_scan_btn')).hide();
                    insertMessageContainer('error', errorThrown);
                    cleanCache(); 
                }
            }  
        }
    });
}

/** 
* Perform Rese List table
*/
function resetListTable()
{
    ajaxProcess = ajaxResetListTable = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'resetListTable',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'resetListTable',
        },
        beforeSend : function () {
            SetprogressBar(2);
            $('.avm_scanstatus').html(reset_tablelist+'... ').fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            displayMessageOnLive(jsonData['message']);
            SetprogressBar(3);
            $('.avm_scanstatus').html(reset_tablelist+'... ').fadeIn(300);
            finish = true;               
        },
        error : function (jqXHR, textStatus, errorThrown) {
            finishProcess(false);
        }
    });
}

/** 
* Perform Create Excel file
*/
function createFile()
{
    ajaxProcess = ajaxCreateFile = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'createFile',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'createFile',
        },
        beforeSend : function () {
            SetprogressBar(3);
            $('.avm_scanstatus').html(create_file+'... ').fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            displayMessageOnLive(jsonData['message']);
            SetprogressBar(3);
            $('.avm_scanstatus').html(create_file+'... ').fadeIn(300);
            finish = true;               
        },
        error : function (jqXHR, textStatus, errorThrown) {
            finishProcess(false);
        }
    });
}

/** 
* Perform Remove empty VAT numbers
*/
function removeEmptyVAT()
{
    ajaxProcess = ajaxremoveEmptyVAT = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'removeEmptyVAT',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'removeVAT',
            address: empty_vat[query],
        },
        beforeSend : function () { 
            $('#stop_scan_btn').show();// Show stop scan button
            $('.avm_scanstatus').html(deleting_empty+'... <br />['+(Object.keys(empty_vat).length - query)+'] '+remaining_addresses).fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            query += 1;
            SetprogressBar();
            displayMessageOnLive(jsonData['message']);
            if (query < Object.keys(empty_vat).length ) {
                removeEmptyVAT();
            }
            else {
                $('.avm_scanstatus').html(deleting_empty+'... <br />[0] '+remaining_addresses).fadeIn(300);
                empty_vat = [];
                finish = true;
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            finishProcess(false);
        }
    });
}

/** 
* Perform Remove duplicated VAT numbers
*/
function removeDuplicatedVAT()
{
    ajaxProcess = ajaxremoveDuplicatedVAT = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'removeDuplicatedVAT',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'removeVAT',
            duplicated: true,
            address: duplicated_vat,
        },
        beforeSend : function () { 
            SetprogressBar(10);
            $('#stop_scan_btn').show();// Show stop scan button
            $('.avm_scanstatus').html(deleting_duplicated).fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            query += 1;
            displayMessageOnLive(jsonData['message']);
            if (jsonData['success']) {
                $('.avm_scanstatus').html(deleting_duplicated+'... <br />[0] '+remaining_addresses).fadeIn(300);
                duplicated_vat = [];
                SetprogressBar(80);
                finish = true;
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            finishProcess(false);
        }
    });
}

/** 
* Perform Remove Invalid VAT numbers
*/
function removeInvalidVAT()
{
    ajaxProcess = ajaxremoveInvalidVAT = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'removeInvalidVAT',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'removeVAT',
            address: invalid_vat[query],
        },
        beforeSend : function () { 
            $('#stop_scan_btn').show();// Show stop scan button
            $('.avm_scanstatus').html(deleting_invalid+'... <br />['+(Object.keys(invalid_vat).length - query)+'] '+remaining_addresses).fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            query += 1;
            SetprogressBar();
            displayMessageOnLive(jsonData['message']);
            if (query < Object.keys(invalid_vat).length ) {
                removeInvalidVAT();
            }
            else {
                $('.avm_scanstatus').html(deleting_invalid+'... <br />[0] '+remaining_addresses).fadeIn(300);
                invalid_vat = [];
                finish = true;
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            finishProcess(false);
        }
    });
}

/** 
* Perform Assign Customer groups
*/
function assignCustomerGroup()
{
    ajaxProcess = ajaxassignCustomerGroup = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'assignCustomerGroup',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'assignCustomerGroup',
            address: customer_toassigngroup[query],
        },
        beforeSend : function () { 
            $('#stop_scan_btn').show();// Show stop scan button
            $('.avm_scanstatus').html(assign_customerGroups+'... <br />['+(Object.keys(customer_toassigngroup).length - query)+'] '+remaining_addresses).fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            query += 1;
            SetprogressBar();
            displayMessageOnLive(jsonData['message']);
            if (query < Object.keys(customer_toassigngroup).length ) {
                assignCustomerGroup();
            }
            else {
                $('.avm_scanstatus').html(assign_customerGroups+'... <br />[0] '+remaining_addresses).fadeIn(300);
                customer_toassigngroup = [];
                finish = true;
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            finishProcess(false);
        }
    });
}

/** 
* Perform Send email
*/
function sendEmail(mode)
{
    var customer_address;
    if (mode == 2) {
        customer_address = empty_vat    
    }
    else if (mode == 3) {
        customer_address = invalid_vat        
    }
    else if (mode == 5) {
        customer_address = duplicated_vat        
    }
    else if (mode == 6) {
        customer_address = invalid_company        
    }
    else if (mode == 7) {
        customer_address = empty_company    
    }
    
    ajaxProcess = ajaxsendEmail = $.ajax({
        url: ajax_url_avm_scanner,
        id: 'sendEmail',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersVatManager',
            ajax: true,
        	action: 'sendEmail',
            mode: mode,
            address: customer_address[query],
        },
        beforeSend : function () {
            $('#stop_scan_btn').show();// Show stop scan button
            $('.avm_scanstatus').html(send_email+'... <br />['+(Object.keys(customer_address).length - query)+'] '+remaining_addresses).fadeIn(300);
            finish = false;
        },
        success : function (jsonData) {
            query += 1;
            SetprogressBar();
            displayMessageOnLive(jsonData['message']);
            if (query < Object.keys(customer_address).length ) {
                sendEmail(mode, customer_address);
            }
            else {
                $('.avm_scanstatus').html(send_email+'... <br />[0] '+remaining_addresses).fadeIn(300);
                if (mode == 2) {
                    empty_vat = [];    
                }
                else if (mode == 3) {
                    invalid_vat = [];       
                }
                else if (mode == 5) {
                    duplicated_vat = [];       
                }
                else if (mode == 6) {
                   invalid_company = [];       
                }
                else if (mode == 7) {
                    empty_company = [];       
                }
                finish = true;
            }
        },
        error : function (jqXHR, textStatus, errorThrown) {
            finishProcess(false);
        }
    });
}


function setButtonContent(id_button)
{
    return button_content[id_button] = $('#'+id_button).html();
}

function getButtonContent(id_button)
{
    return button_content[id_button];
}

/** 
* Finish process
*/
function finishProcess(completed = false, stopped = false)
{
    timer.stop();
    $('#stop_scan_btn').hide();
    $('.avm_scanstatus').removeClass('error').removeClass('success');
    if (completed === true) {
        $('#reload_btn').show();
        $('#download_list_btn').show();
        $('.avm_scanstatus').addClass('success').html(process_finished_success).fadeIn(300);
    }
    else {
        if (stopped === true) {
            $('.avm_scanstatus').addClass('error').html(cancel_process).fadeIn(300);    
        }
        else {
            $('#reload_btn').show();
            $('.avm_scanstatus').addClass('error').html(process_finished_error).fadeIn(300);    
        }    
    }
    SetprogressBar(100);
    // Change color of percent to green
    $('.percent_progress').css('color', '#53d572');
    $('#open_scan_btn').html(getButtonContent('open_scan_btn'));
}

/** 
* Start process
*/
function startProcess(button_id)
{
    resetValues();
    setButtonContent(button_id);
    $('.progress_circle_container').show();
    $('.results_data .total_value').html('-').fadeIn(300);
    $('.results_data .empty_value').html('-').fadeIn(300);
    $('.results_data .error_value').html('-').fadeIn(300);
    $('.results_data .valid_value').html('-').fadeIn(300);
    $('.results_data .duplicated_value').html('-').fadeIn(300);
    $('.results_data .invalid_company_value').html('-').fadeIn(300);
    $('.results_data .empty_company_value').html('-').fadeIn(300);
    $('.results_data .valid_skipping_validation_value').html('-').fadeIn(300);
    // Init timer
    timer.start();
    timer.addEventListener('secondsUpdated', function (e) {
        $('#avm_process_timer').html('<i class="fal fa-stopwatch"></i> '+timer.getTimeValues().toString());
    });
    if ($('input[name="skip_apisystemfails"]').is(':checked')) {
        $('div.valid_skipping_validation').show();    
    }
    else {
       $('div.valid_skipping_validation').hide();   
    }
    
    $('.avm_scanstatus').removeClass('success').removeClass('error');
    $('.avm_results').html('');
    $('#avm_results_container').show();
    $('#avm_scanstatus_container').show();
    $('#open_scan_btn').html('<span class="loader_left"></span> '+scanning);
    $('#open_scan_btn').prop('disabled', true);// Disable scan button
    $('#reload_btn').hide();// Hide reload button
}

/** 
* Reset progress bar
*/
function resetProgressBar()
{
    progress_bar = 0;
    $('.avm_process_container .progress_circle').circleProgress('value' , 0);
    $('#circle').circleProgress('redraw');
    $('.avm_process_container .percent_progress').text('0%');
}

/** Set pogress
* progresss - Percent of progress used in this action
*/
function SetprogressBar(progress = null)
{
    if (progress !== null) {
        progress_bar += progress;    
    }
    else {
        progress_bar = Number.parseFloat(step_progress + progress_bar);
    }
    if (progress_bar >= 100) {
        progress_bar = 100;
    } 
    $('.avm_process_container .progress_circle').circleProgress('value' , (progress_bar/100).toFixed(2)).on('circle-animation-progress', function(event, animationProgress, stepValue){
        $('.avm_process_container .percent_progress').text((progress_bar).toFixed(1)+'%');      
    });
      
}

/** Calculate percent per action
* elements - number of elements to calculate number of actions needed
*/
function calculateStepProgress(elements = 0)
{
    query = 0;
    total_request = elements;
    step_progress = Number.parseFloat((100-progress_bar)/elements);      
}

/** 
* Insert Message container
*/
function insertMessageContainer(type, message)
{
    $('.avm_results').append('<div class="alert alert-'+type+'">'+message+'</div>');    
}

/** 
* Stop Process
*/
function stopProcess(xhr)
{
    timer.stop();
    SetprogressBar(100);
    xhr.abort();
}

/** 
* Reset Values
*/
function resetValues()
{
    resetProgressBar();
    query = 0;
    messages = [];
    finish = false;
    button_content = [];
    total_addresses = [];
    addresses = [];
    options = [];
    step_progress = 0;
    empty_vat = [];
    invalid_vat = [];
    invalid_company = [];
    empty_company = [];
    valid_vat = [];
    valid_vat_skipping_validation = [];
    customer_toassigngroup = [];
}

/** 
* Scan process
*/
function scanProcess()
{
    resetListTable(); 
    $(document).ajaxSuccess(function(event, request, settings) {
        // After Initial database check
        if (settings.id == 'checkTotalAddress') {
            if (Object.keys(addresses).length > 0) {
                setTimeout(function(){
                    calculateStepProgress(Object.keys(addresses).length);
                    checkVATNumber();
                }, 5000);
            }
            else {
                finishProcess(true);
            }
        }
        else if (settings.id == 'resetListTable') {
            if (finish === true) {
                setTimeout(function(){
                    checkTotalAddress();
                }, 2000);
            }
        } 
        else if (settings.id == 'checkVATNumber') {
            if (finish === true) {
                setTimeout(function(){
                    createFile();
                }, 3000);
                
            }
        } 
        else if (settings.id == 'createFile' || settings.id == 'removeEmptyVAT' || settings.id == 'removeInvalidVAT' || settings.id == 'removeDuplicatedVAT' || settings.id == 'sendEmail' || settings.id == 'assignCustomerGroup') {
            if (finish === true) {
                if (Object.keys(empty_vat).length > 0 && options['remove_empty_mode'] === true) {
                    resetProgressBar();
                    setTimeout(function(){ 
                        calculateStepProgress(Object.keys(empty_vat).length);
                        removeEmptyVAT();
                    }, 5000); 
                }
                else if (Object.keys(invalid_vat).length > 0 && options['remove_invalid_mode'] === true) {
                    resetProgressBar();
                    setTimeout(function(){ 
                        calculateStepProgress(Object.keys(invalid_vat).length);
                        removeInvalidVAT();
                    }, 5000); 
                    
                }
                else if (Object.keys(duplicated_vat).length > 0 && options['remove_duplicated_mode'] === true) {
                    resetProgressBar();
                    setTimeout(function(){ 
                        removeDuplicatedVAT();
                    }, 5000); 
                    
                }
                else if (Object.keys(invalid_vat).length > 0 && options['sendEmail_invalid_mode'] === true) {
                    resetProgressBar();
                    calculateStepProgress(Object.keys(invalid_vat).length);
                    sendEmail(3);
                }
                else if (Object.keys(empty_vat).length > 0 && options['sendEmail_empty_mode'] === true) {
                    resetProgressBar();
                    calculateStepProgress(Object.keys(empty_vat).length);
                    sendEmail(2);
                }
                else if (Object.keys(duplicated_vat).length > 0 && options['sendEmail_duplicated_mode'] === true) {
                    resetProgressBar();
                    calculateStepProgress(Object.keys(duplicated_vat).length);
                    sendEmail(5);
                }
                else if (Object.keys(invalid_company).length > 0 && options['sendEmail_invalidcompany_mode'] === true) {
                    resetProgressBar();
                    calculateStepProgress(Object.keys(invalid_company).length);
                    sendEmail(6);
                }
                else if (Object.keys(empty_company).length > 0 && options['sendEmail_emptycompany_mode'] === true) {
                    resetProgressBar();
                    calculateStepProgress(Object.keys(empty_company).length);
                    sendEmail(7);
                }
                else if (Object.keys(customer_toassigngroup).length > 0 && options['assign_group_mode'] === true) {
                    resetProgressBar();
                    setTimeout(function(){ 
                        calculateStepProgress(Object.keys(customer_toassigngroup).length);
                        assignCustomerGroup();
                    }, 5000); 
                }
                else {
                    finishProcess(true);
                }
            }    
        }
        settings.id = null;
    });
}

/** 
* Manage options with Bootstrap switches
*/
function manageOptions()
{     
    $('input[name="remove_empty_mode"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="remove_empty_mode"]').bootstrapSwitch('state') && $('input[name="sendEmail_empty_mode"]').bootstrapSwitch('state')) {
            $('input[name="sendEmail_empty_mode"]').bootstrapSwitch('state', false);
        }    
    });
    $('input[name="sendEmail_empty_mode"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="sendEmail_empty_mode"]').bootstrapSwitch('state') && $('input[name="remove_empty_mode"]').bootstrapSwitch('state')) {
            $('input[name="remove_empty_mode"]').bootstrapSwitch('state', false);
        }   
    });
    $('input[name="remove_duplicated_mode"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="remove_duplicated_mode"]').bootstrapSwitch('state') && $('input[name="sendEmail_duplicated_mode"]').bootstrapSwitch('state')) {
            $('input[name="sendEmail_duplicated_mode"]').bootstrapSwitch('state', false);
        }    
    });
    $('input[name="sendEmail_duplicated_mode"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="sendEmail_duplicated_mode"]').bootstrapSwitch('state') && $('input[name="remove_duplicated_mode"]').bootstrapSwitch('state')) {
            $('input[name="remove_duplicated_mode"]').bootstrapSwitch('state', false);
        }   
    });
    $('input[name="remove_invalid_mode"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="remove_invalid_mode"]').bootstrapSwitch('state') && $('input[name="sendEmail_invalid_mode"]').bootstrapSwitch('state')) {
            $('input[name="sendEmail_invalid_mode"]').bootstrapSwitch('state', false);
        }
    });
    $('input[name="sendEmail_invalid_mode"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="sendEmail_invalid_mode"]').bootstrapSwitch('state') && $('input[name="remove_invalid_mode"]').bootstrapSwitch('state')) {
            $('input[name="remove_invalid_mode"]').bootstrapSwitch('state', false);
        }
    });  
    $('input[name="fill_company_name"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="sendEmail_invalidcompany_mode"]').bootstrapSwitch('state') && $('input[name="fill_company_name"]').bootstrapSwitch('state')) {
            $('input[name="sendEmail_invalidcompany_mode"]').bootstrapSwitch('state', false);
        }
        if ($('input[name="sendEmail_emptycompany_mode"]').bootstrapSwitch('state') && $('input[name="fill_company_name"]').bootstrapSwitch('state')) {
            $('input[name="sendEmail_emptycompany_mode"]').bootstrapSwitch('state', false);
        }
    });   
    $('input[name="fill_company_name"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="sendEmail_invalidcompany_mode"]').bootstrapSwitch('state') && $('input[name="fill_company_name"]').bootstrapSwitch('state')) {
            $('input[name="sendEmail_invalidcompany_mode"]').bootstrapSwitch('state', false);
            $('input[name="sendEmail_emptycompany_mode"]').bootstrapSwitch('state', false);
        }
    });    
    $('input[name="sendEmail_invalidcompany_mode"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="sendEmail_invalidcompany_mode"]').bootstrapSwitch('state') && $('input[name="fill_company_name"]').bootstrapSwitch('state')) {
            $('input[name="fill_company_name"]').bootstrapSwitch('state', false);
            $('input[name="sendEmail_emptycompany_mode"]').bootstrapSwitch('state', false);
        }
    });    
    $('input[name="sendEmail_invalidcompany_mode"]').on('switchChange.bootstrapSwitch', function(){
        if ($('input[name="sendEmail_invalidcompany_mode"]').bootstrapSwitch('state') && $('input[name="fill_company_name"]').bootstrapSwitch('state')) {
            $('input[name="fill_company_name"]').bootstrapSwitch('state', false);
            $('input[name="sendEmail_emptycompany_mode"]').bootstrapSwitch('state', false);
        }
    });   
    if (!company_validation) {
        $('input[name="sendEmail_invalidcompany_mode"]').bootstrapSwitch('disabled', true);
        $('input[name="sendEmail_emptycompany_mode"]').bootstrapSwitch('disabled', true)
    }  
}

/*
* Build modal messages
*  
*/
function displayModalMessage(title = null, message = null, type, id = null, time = false)
{
    let position = 0;
    let modal_count = $('.avm_modal_messages').parent('.sm-popup').length;
    if (id === null) {
        id = $.now();
    }
    if (message !== null) {   
        if (modal_count >= 1) {
            position = (modal_count * 220);
            if (!$.isEmptyObject(position_available) && modal_count >= 1) {
                position = Math.min.apply(Math, position_available);
                position_available.splice($.inArray(position, position_available), 1);    
            }   
        }
        else {
            position_available = [];
            position = 0;
        }
        // If modal exists, only add content
        if ($('#avm_modal_messages_'+id).length > 0) {
            $('#avm_modal_messages_'+id+' .content').append(message+'<br /><br />');      
        }
        // Create new modal
        else {
            $('body').append('<div id="avm_modal_messages_'+id+'" class="avm_modal_messages" data-sm-init="true" data-state="'+type+'"><div class="title"></div><div class="content"></div></div>');            
            $('#avm_modal_messages_'+id+' .content').html(message+'<br /><br />');
            $('#avm_modal_messages_'+id).attr('data-state', type);  
            if (title === null) {
                if (type == 'success') {
                    title = success;
                }
                else if (type == 'warning') {
                    title = warning;    
                }
                else if (type == 'danger') {
                    title = danger;    
                }
                else if (type == 'info') {
                    title = info;    
                }
            }
            $('#avm_modal_messages_'+id+' .title').html(title); 
        }
        // Clean spaces
        //$('#avm_modal_messages_'+id+' .content').html($('#avm_modal_messages_'+id+' .content').html().trim());
        // Slickmodal for messages
        $('#avm_modal_messages_'+id).SlickModals({
            popup_type: 'instant',
            popup_delayedTime: '0.5s',
            popup_wrapperClass: 'avm_message_popup_'+id,
            popup_allowMultipleInstances: true,
            popup_autoClose: (time !== false?true:false),
            popup_autoCloseAfter: (time !== false?time+'s':''),
            popup_animation: 'slideRight',
            popup_position: 'topRight',
            popup_closeButtonEnable: true,
            popup_closeButtonPlace: 'inside',
            popup_draggableEnable: false,
            popup_css: {
                'min-width': '350px',
                'height': 'auto',
                'background': 'transparent',
                'padding': '0',
                'margin': '10px',
                'animation-duration': '2s',
                'text-align': 'center',
                'top': position+'px'
            },
            overlay_closesPopup: true,
            overlay_isVisible: false,
            mobile_breakpoint: '480px',
            mobile_position: 'topCenter',
            mobile_css: {
                'width': '100%',
                'height': 'auto',
                'background': 'transparent',
                'padding': '0',
                'margin': 'auto',
                'animation-duration': '2s',
                'text-align': 'center',
                'top': position+'px'
            },
            callback_afterClose: function () {
                position_available.push($('#avm_modal_messages_'+id).parent('.sm-popup').position().top);  
                $('#avm_modal_messages_'+id).parent().parent('.sm-wrapper').remove();  
                $('#avm_modal_messages_'+id).SlickModals('destroy');                
            },
        });
        $('#avm_modal_messages_'+id).SlickModals('openPopup', 'instant');
    }   
}


/*
* Display messages
*  
*/
function displayMessages(content, time = false)
{
    var aux = [];
    // Displays messages after completing AJAX processes.
    if (!$.isEmptyObject(content)) {
        $.each(content, function(i, index) {
            $.each(index, function(msg_class, messages) {
                $.each(messages, function(type, message) {
                    $.each(message, function(i, string) {
                        // Remove duplicated messages.
                        if ($.inArray(string, aux) == -1) {
                            aux.push(string);
                            displayModalMessage(null, string.trim(), type, type, time);  
                        }
                    });
                });
            });
        });     
    }    
}

/*
* Display message on live meanwhile processes are working
*  
*/
function displayMessageOnLive(messages_content = false)
{
    // Display messages
    if (messages_content !== false && !$.isEmptyObject(messages_content)) {
        $.each(messages_content, function(type, message){
            if ($.inArray(message, messages) === -1) {
                displayModalMessage(null, message , type, type, false);      
            }                
        });
    }
}