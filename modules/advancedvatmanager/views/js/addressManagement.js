/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */

var skipValidation;
var ajaxProcess;
var company_required;
var button;

$(document).ready(function(){   
    if (ps16) {   
        // Prevent vatnumber module ajax.
        $(document).ajaxSend(function(e, xhr, opt){
            if (opt.url.toLowerCase().indexOf("vatnumber") >= 0) {
                e.preventDefault();
                xhr.abort();
            }
        });
        
        setAddressFormat();
        manageFields16(); 
        
    	$('#id_country').on('change', function() {
            setAddressFormat();   
        });
        
        //After AJAX processes
        $(document).ajaxComplete(function(event, request, settings) {
            if (settings.id !== 'setAddressFormat' && settings.url.toLowerCase().indexOf("vatnumber") < 0) {
                setAddressFormat();
                manageFields16();  
            }
        });
    }
    else {
        // In Prestashop default checkout page
        if (controller == 'order' && NOTALLOW_CHECKOUT_WITHOUT_VALIDATION && !opc_presteamshop_enabled) {
            button = $('button[name="confirm-addresses"]').clone(true);// Clone submit button
            checkErrorInCheckoutAddress();
            $('input[name="id_address_delivery"], input[name="id_address_invoice"]').on('change', function(){
                checkErrorInCheckoutAddress();    
            });    
        }
        
    	$('#id_country').on('change', function() {
            setAddressFormat17();   
        });
        
        // Manage company and vat_number fields
        displayWithCompany17();
        setCompanyRequiredWithVAT17();
        
        if (typeof prestashop !== 'undefined') {
          prestashop.on(
            'updatedAddressForm',
            function (event) {
                setAddressFormat17();
            }
          );
        }
    }
});
function checkErrorInCheckoutAddress()
{   
	ajaxProcess = $.ajax({
		type: 'POST',
		headers: {"cache-control": "no-cache"},
        id: 'checkErrorInCheckoutAddress',
		url: ajax_url_addressVAT,
        dataType : 'json',
        async: false,
        cache: false,
        data : {
            ajax: true,
            action: 'checkErrorInCheckoutAddress',
            id_address_delivery: $("input[name='id_address_delivery']:checked").val(),
            id_address_invoice: $("input[name='id_address_invoice']:checked").val()
            
        },
        beforeSend : function () {  
        },
		success: function (jsonData) { 	  
            if (jsonData['address_invoice_errors'] !== false || jsonData['address_delivery_errors'] !== false) {
                $('button[name="confirm-addresses"]').remove();// Remove submit button
                $('#checkout-addresses-step span.step-edit').click();// Open addresses section
                if (jsonData['address_invoice_errors']) {
                    $('p[name="alert-invoice"]').html(jsonData['address_invoice_errors']['exception']).show();    
                }
                if (jsonData['address_delivery_errors']) {
                    $('p[name="alert-delivery"]').html(jsonData['address_delivery_errors']['exception']).show();    
                }
            }
            else {
                if (!button.length) {
                    $('input#not-valid-addresses').before(button);
                }    
            }    
        }
    });
}
function setAddressFormat17(focus = false)
{
    var searchParams = new URLSearchParams(window.location.search);
    if (searchParams.has('id_address')) {
        var id_address = searchParams.get('id_address')    
    }
    else {
        var id_address = 0;
    }
	ajaxProcess = $.ajax({
		type: 'POST',
		headers: {"cache-control": "no-cache"},
        id: 'setAddressFormat17',
		url: ajax_url_addressVAT,
        dataType : 'json',
        async: false,
        cache: false,
        data : {
            ajax: true,
            action: 'updateAddressForm',
            data_form: $("form").serializeArray(),
        	id_country: $('select[name="id_country"]').val(),
            id_address: id_address,
            company: $('input[name="company"]').val(),
            vat_number: $('input[name="vat_number"]').val(),
            token: $('input[name="token"]').val(),
            form_has_continue_button: $('button[type="submit"]').hasClass('continue'),
            php_self: controller
        },
        beforeSend : function () {  
        },
		success: function (jsonData) { 	  
		    $('.js-address-form').html(jsonData['address_form']);
            if (focus) {
                if ($('input[name="'+focus+'"]').length) {
                    $('input[name="'+focus+'"]').focus();
                    $('input[name="'+focus+'"]')[0].setSelectionRange($('input[name="'+focus+'"]').val().length * 2, $('input[name="'+focus+'"]').val().length * 2);
                }
            }
            displayWithCompany17();
            setCompanyRequiredWithVAT17()
       }
    });
}
function setAddressFormat(focus = false)
{
	$.ajax({
		type: 'POST',
		headers: {"cache-control": "no-cache"},
        id: 'setAddressFormat',
		url: ajax_url_addressVAT,
        dataType : 'json',
        async: false,
        cache: false,
        data : {
            ajax: true,
            action: 'updateAddressForm16',
        	id_country: $('select[name="id_country"]').val(),
            company: $('input[name="company"]').val(),
            vat_number: $('input[name="vat_number"]').val(),
            token: $('input[name="token"]').val(),
            form_has_continue_button: $('button[type="submit"]').hasClass('continue'),
            php_self: controller
        },
        beforeSend : function () {  
        },
		success: function (jsonData) {
            company_required = jsonData['company_required'];
            skipValidation = jsonData['skipValidation'];

            // If VAT field exists in Address Format then display it
            if (jsonData['vat_field_exists']) {
                displayVATField();
            }
            else {
                hideVATField();  
            }
            
			if (jsonData['skipValidation'] === false) {			 
                if (vat_field == 'required') {
                    $('input[name="vat_number"]').prop('required', true);
                    $('input[name="vat_number"]').addClass('is_required');
                    if ($('label[for="vat-number"] sup').length == 0) {
                        $('label[for="vat-number"]').append(' <sup>*</sup>');
                    }    
                }
                else {
                    $('input[name="vat_number"]').prop('required', false);
                    $('input[name="vat_number"]').removeClass('is_required');
                    $('label[for="vat-number"] sup').remove(); 
                } 
                // Set company field legend
    		    if ($('input[name="company"]').length) {
                    if (company_legend != '') {
                        if ($('.company_desc').length) {
                            $('.company_desc').html(company_legend);     
                        }
                        else {
                            $('input[name="company"]').after('<small class="company_desc">'+company_legend+'</small>');       
                        } 
                    }
                }
                // Set VAT number field label and description
                if (label != '') {
                    $('label[for="vat-number"]').html(label); 
                }
                if (legend != '') {
                    if ($('.vat_number_desc').length) {
                        $('.vat_number_desc').html(legend);     
                    }
                    else {
                        $('input[name="vat_number"]').after('<small class="vat_number_desc">'+legend+'</small>');     
                    }
                }
                
                if (display_with_company) {
                    if ($('input[name="company"]').length && $('input[name="company"]').val().length) {
                        displayVATField();    
                    }
                    else {
                        hideVATField();    
                    }
                }
                
                if (display_with_company) {
                    if ($('input[name="company"]').length && $('input[name="company"]').val().length > 0) {
                        displayVATField();    
                    }
                    else {
                        hideVATField();
                    }
                }
                
                if (company_validation) {
                    if ($('input[name="vat_number"]').length && $('input[name="vat_number"]').val().length > 0) {
                        // When vat_number is not empty put company as required field
                        $('input[name="company"]').prop('required', true);
                        $('input[name="company"]').addClass('is_required');
                        if ($('label[for="company"] sup').length == 0) {
                            $('label[for="company"]').append(' <sup>*</sup>');      
                        }
                    }
                    else {
                        $('input[name="company"]').prop('required', false);
                        $('input[name="company"]').removeClass('is_required');
                        $('label[for="company"] sup').remove();
                    }
                }
                
                
                if (focus) {
                    if ($('input[name="'+focus+'"]').length) {
                        $('input[name="'+focus+'"]').focus();
                        $('input[name="'+focus+'"]')[0].setSelectionRange($('input[name="'+focus+'"]').val().length * 2, $('input[name="'+focus+'"]').val().length * 2);
                    }
                }               
                // Prevent vatnumber module ajax.
                $(document).ajaxSend(function(e, xhr, opt){
                    if (opt.url.toLowerCase().indexOf("vatnumber") >= 0) {
                        e.preventDefault();
                        xhr.abort();
                    }
                });
			}
            else {
                // Restore original fields
                $('.company_desc').remove();
                $('.vat_number_desc').remove();
                if (jsonData['company_required']) {
                    $('input[name="company"]').prop('required', true);
                    $('input[name="company"]').addClass('is_required');
                    if ($('label[for="company"] sup').length == 0) {
                        $('label[for="company"]').append(' <sup>*</sup>');
                    }
                }
                else {
                    $('input[name="company"]').prop('required', false);
                    $('input[name="company"]').removeClass('is_required');
                    $('label[for="company"] sup').remove();
                }
                if (jsonData['vat_required']) {
                    $('input[name="vat_number"]').prop('required', true);
                    $('input[name="vat_number"]').addClass('is_required');
                    if ($('label[for="vat-number"] sup').length == 0) {
                        $('label[for="vat-number"]').append(' <sup>*</sup>');
                    }
                }
                else {
                    $('input[name="vat_number"]').prop('required', false);
                    $('input[name="vat_number"]').removeClass('is_required');
                    $('label[for="vat-number"] sup').remove();
                }    
            }
       }
    });
}

function manageFields16()
{
    if (display_with_company) {
        if ($('input[name="company"]').length) {
            $(document).on('input', 'input[name="company"]', function(){
                if ($(this).val().length == 1) {
                    setAddressFormat('company');      
                } 
                else if ($(this).val().length == 0) {
                    setAddressFormat('company');        
                } 
            });
            $(document).one('input', 'input[name="company"]', function(){
                if ($(this).val().length >= 1) {
                    setAddressFormat('company');
                }
            });
            $(document).on('change', 'input[name="company"]', function(){
                setAddressFormat('company'); 
            });
        }
    }
    if (company_validation) {
        $(document).on('input', 'input[name="vat_number"]', function(){
            if ($(this).val().length == 1) {
                setAddressFormat('vat_number');        
            } 
            else if ($(this).val().length == 0) {
                setAddressFormat('vat_number'); 
            } 
        });
        $(document).one('input', 'input[name="vat_number"]', function(){
            if ($(this).val().length >= 1) {
                setAddressFormat('vat_number');
            }
        });
        $(document).on('change', 'input[name="vat_number"]', function(){
            setAddressFormat('vat_number');    
        });
    }
}

function displayWithCompany17()
{
    if (display_with_company) {
        if ($('input[name="company"]').length) {
            $('input[name="company"]').on('input', function(){
                if ($(this).val().length == 1) {
                    setTimeout(function() {setAddressFormat17('company')}, 1100);    
                } 
                else if ($(this).val().length == 0) {
                    setTimeout(function() {setAddressFormat17('company')}, 1100);       
                } 
            });
            if ($('input[name="company"]').val().length == 0) {
                $('input[name="company"]').on('input', function(){
                    if ($(this).val().length > 1) {
                        setTimeout(function() {setAddressFormat17('company')}, 1100);
                    }
                });
            }
            $('input[name="company"]').on('change', function(){
                if ($(this).val().length > 1) {
                    setTimeout(function() {setAddressFormat17('company')}, 1100);
                } 
            });
        }
    }
}

function setCompanyRequiredWithVAT17()
{
    if (company_validation) {
        if ($('input[name="vat_number"]').length) {
            $('input[name="vat_number"]').on('input', function(){
                if ($(this).val().length == 1) {
                    setTimeout(function() {setAddressFormat17('vat_number')}, 1100);   
                } 
                else if ($(this).val().length == 0) {
                    setTimeout(function() {setAddressFormat17('vat_number')}, 1100);      
                } 
            });
            if ($('input[name="vat_number"]').val().length == 0) {
                $('input[name="vat_number"]').on('input', function(){
                    if ($(this).val().length > 1) {
                        setTimeout(function() {setAddressFormat17('vat_number')}, 1100);
                    }
                });
            }
            $('input[name="vat_number"]').on('change',  function(){
                if ($(this).val().length > 1) {
                    setTimeout(function() {setAddressFormat17('vat_number')}, 1100);
                } 
            });
        }
    }
}

function hideVATField()
{
    $('input[name="vat_number"]').val('');
    $('input[name="vat_number"]').prop('required', false);
    $('input[name="vat_number"]').removeClass('is_required');
    $('#vat_area').hide();
    $('#vat_number').hide(); 
    if (!company_required) {
        $('input[name="company"]').prop('required', false);
        $('input[name="company"]').removeClass('is_required');
        $('label[for="company"] sup').remove();
    }
}

function displayVATField()
{
    let html_vat = '<div id="vat_area"><div id="vat_number"><div class="form-group"><label for="vat-number">'+vat_number_label+'</label><input type="text" class="form-control validate" data-validate="isGenericName" id="vat-number" name="vat_number" value=""></div></div></div>';
    
    if ($('#vat_area').length)  {
        $('#vat_area').show();
		$('#vat_number').show(); 
    }
    else {
        $('input[name="company"]').parent().append(html_vat);    
    }  
}

// Override functions from default ps16 to hide vat_number field when company field is empty.
function vat_number()
{
	return false;
}

function vat_number_ajax()
{
    return false;    
}