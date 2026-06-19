/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */


$(document).ready(function(e){   
    $('input.date').datetimepicker({
        dateformat:'d/m/Y',
        timeFormat: 'hh:mm:ss',
        showSecond: true,
        defaultDate: new Date()
    });
    $('button#total_orders_filtered_by_dates').on('click', function(e){
        getTotalsByDates();    
    });
    $('button#reset_total_orders_filter').on('click', function(e){
        location.reload(true);
    });
    
});

/** 
* Perform Check total address
*/
function getTotalsByDates()
{
    $.ajax({
        url: ajax_url_customersordersmanager,
        id: 'getTotalsByDates',
        type : 'POST',
        headers: { "cache-control": "no-cache" },
        dataType : 'json',
        async: true,
        cache: false,
        data : {
            controller: 'AdminCustomersOrdersManager',
            ajax: true,
        	action: 'getTotalsByDates',
            date_from: $('input#order_date_input_from').val(),
            date_to: $('input#order_date_input_to').val(),
        },
        beforeSend : function () { 
            $('button#total_orders_filtered_by_dates i').removeClass('fal fa-filter').addClass('fal fa-spinner-third fa-fw fa-xl margin-right-md fa-spin');
            $('button#reset_total_orders_filter').hide();
        },
        success : function (jsonData) {
            $('button#total_orders_filtered_by_dates i').removeClass('fal fa-spinner-third fa-fw fa-xl margin-right-md fa-spin').addClass('fal fa-filter');
            $('span.total_brexit').html(jsonData['total_brexit']);
            $('span.total_tax_exempt').html(jsonData['total_tax_exempt']);
            $('button#reset_total_orders_filter').show();    
        },
        error : function (jqXHR, textStatus, errorThrown) {
            $('button#total_orders_filtered_by_dates i').removeClass('fal fa-spinner-third fa-fw fa-xl margin-right-md fa-spin').addClass('fal fa-filter');    
        }
    });
}