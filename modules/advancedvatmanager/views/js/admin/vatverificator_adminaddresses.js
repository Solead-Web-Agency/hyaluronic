/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */

$(document).ready(function(){
    $('form[name="customer_address"] button').on('click', function(e){
        e.preventDefault();
        validateVATNumber();
    });
});

function validateVATNumber()
{    
    var success = false;
    $.ajax({
        url: admin_ajax_url_advancedvatmanager,
    	type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
    	async: true,
        cache: false,
    	data : {
            controller: 'AdminVATValidation',
            ajax: true,
    		action: 'checkVATnumberAdminAddresses',
            country: $('[name="customer_address[id_country]"]').val(),
            vat_number: $('[name="customer_address[vat_number]"]').val(),
            id_customer: $('[name="customer_address[id_customer]"]').val(),
            company: $('[name="customer_address[company]"]').val(),
            address: id_address
    	},
        beforeSend : function () {
            $('.invalid-feedback-container').remove();
            $('form[name="customer_address"] button').prop("disabled",true);
    	},
    	success : function (jsonData) {
            $('form[name="customer_address"] button').prop("disabled",false);
            if (jsonData) {
                if (jsonData['success'] !== true) {
                    // If company is not checked by option
                    if (jsonData['company'] === true) {
                        $('[name="customer_address[vat_number]"]').after('<div class="invalid-feedback-container"><div class="d-inline-block text-danger align-top"><i class="material-icons form-error-icon">error_outline</i></div><div class="d-inline-block"><div class="text-danger">'+jsonData['message']+'</div></div></div>');
                        // Scroll to VAT number field.
                        $('html, body').animate({ scrollTop: $('[name="customer_address[vat_number]"]').parent().parent().position().top - 100 }, 1000);
                    }
                    else {
                        $('[name="customer_address[company]"]').after('<div class="invalid-feedback-container"><div class="d-inline-block text-danger align-top"><i class="material-icons form-error-icon">error_outline</i></div><div class="d-inline-block"><div class="text-danger">'+jsonData['message']+'</div></div></div>');
                        // Scroll to company field.
                        $('html, body').animate({ scrollTop: $('[name="customer_address[company]"]').parent().parent().position().top - 100 }, 1000);
                    }
                }
                else {
                    // Submit form
                    $('form[name="customer_address"]').submit();
                }
            }
    	},
        error: function () {
             $('form[name="customer_address"]').append(error_msg);
        }
    });
}