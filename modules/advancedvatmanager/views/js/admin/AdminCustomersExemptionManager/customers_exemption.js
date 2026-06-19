/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */

$(document).ready(function(e){  
    $('input[name="search_customer"]').on('input', function(e){
        if ($('input[name="search_customer"]').val().length >= 1) {
            $('div.avm_customers').remove();
            $('div.avm_customers .avm_customer').remove();
            searchCustomer();     
        }
        else if ($('input[name="search_customer"]').val().length = 1) {
            $('div.avm_customers').remove();
            $('div.avm_customers .avm_customer').remove();     
        }      
    });    
});
$(document).ajaxComplete(function(e){  
    $('div.avm_customers a.avm_customer').on('click', function(e){
        $('select[name="id_customer"]').val($(this).attr('id'));
        $('div.avm_customers').remove();       
    });        
});

/** 
* Perform Check total address
*/
function searchCustomer()
{
    $.ajax({
        url: ajax_url_avm_search_customers,
        id: 'checkTotalAddress',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersExemptionManager',
            ajax: true,
        	action: 'searchCustomer',
            search_str: $('input[name="search_customer"]').val()
        },
        beforeSend : function () { 
            $('span.search_icon').html('<div class="loader"></div>');
            $('div.avm_customers').remove(); 
            $('div.avm_customers .avm_customer').remove();   
        },
        success : function (jsonData) {
            $('span.search_icon').html('<i style="font-size:25px;" class="fal fa-search"></i> ');
            
            // Create dropdown with results if not exists
            if (!$('div.avm_customers').length) {
                $('input#search_customer').after('<div class="avm_customers dropdown-menu"></div>');    
            }
            else {
                // Reset content
                $('div.avm_customers').html('');    
            }
            // Save results
            if (Object.keys(jsonData['customers']).length > 0) {
                $.each(jsonData['customers'], function(key, value) {
                    $('div.avm_customers').append('<a class="avm_customer dropdown-item" id="'+value['id_customer']+'" href="#">ID#'+value['id_customer']+' '+value['firstname']+' '+value['lastname']+' - '+value['email']+'</a>');    
                });
            }
            else {
                $('div.avm_customers').append(no_found);    
            }         
        },
        error : function (jqXHR, textStatus, errorThrown) {
            if (!$('div.avm_customers').length) {
                $('input#search_customer').after('<div class="avm_customers dropdown-menu"></div>');    
            }
            else {
                // Reset content
                $('div.avm_customers').html('');    
            }
            $('div.avm_customers').append(errorThrown);
        }
    });
}