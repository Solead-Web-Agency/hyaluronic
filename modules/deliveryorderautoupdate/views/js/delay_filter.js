/**
* 2007-2023 Helloshop
*
* Tracking Center
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

var key = false;
var active = false;
var range = false;
function delayLoadmore(scrollLoad) {
    let panel = $('[id^=fieldset_delivered]');
    var id = panel.find("input[name=dou_Filter_id_order]").val();
    var reference = panel.find("input[name=dou_Filter_reference]").val().toLowerCase().trim();
    var customer = panel.find("input[name=dou_Filter_customer]").val().trim();
    var carrier = panel.find("select[name='dou_Filter_carrier_name']").val().trim();
    var total = {
        preparation: $('input[name="total[preparation]"]').val(),
        transit: $('input[name="total[transit]"]').val(),
        total: $('input[name="total[total]"]').val(),
    };
    var count = {
        preparation: $('input[name="count[preparation]"]').val(),
        transit: $('input[name="count[transit]"]').val(),
        total: $('input[name="count[total]"]').val(),
    };
    $loading = panel.find('.loading');
    $loading.find('img').show();
    $.ajax({
        type: 'POST',
        url: url_ajax,
        data: {
            controller : 'AdmindeliveryorderautoupdateAjax',
            action : 'delayFilter',
            ajax : true,
            page : scrollLoad.page + 1,
            secure_key: secure_key,
            filter: {
                id: id,
                reference: reference,
                customer: customer,
                carriername: carrier,
            },
            key: key,
            range: range,
            total: total,
            count: count
        },
        success: function(data) {
            data = JSON.parse(data);
            scrollLoad.page = parseInt(data.p);
            if (data.html) {
                data.html = $(data.html);
                $('.delay').append(data.html);
            }
            if (data.p >= data.pages_nb) {
                scrollLoad.stop = true;
            } else {
                scrollLoad.stop = false;
            }
            scrollLoad.loading = false;
            $loading.find('img').hide();
            setDelayContent(data);
        }
    })
}
function delayFilter(scrollLoad) {
    let panel = $('[id^=fieldset_delivered]');
    var id = panel.find("input[name=dou_Filter_id_order]").val();
    var reference = panel.find("input[name=dou_Filter_reference]").val().toLowerCase().trim();
    var customer = panel.find("input[name=dou_Filter_customer]").val().trim();
    var carrier = panel.find("select[name='dou_Filter_carrier_name']").val().trim();

    $loading = panel.find('.loading');
    $loading.find('img').show();
    $('.delay').empty();
    $.ajax({
        type: 'POST',
        url: url_ajax,
        data: {
            controller : 'AdmindeliveryorderautoupdateAjax',
            action : 'delayFilter',
            ajax : true,
            secure_key: secure_key,
            filter: {
                id: id,
                reference: reference,
                customer: customer,
                carriername: carrier,
            },
            key: key,
            range: range,
        },
        success: function(data) {
            data = JSON.parse(data);
            scrollLoad.page = data.p;
            if (data.html) {
                data.html = $(data.html);
                $('.delay').append(data.html);
            }
            if (data.p >= data.pages_nb) {
                scrollLoad.stop = true;
            } else {
                scrollLoad.stop = false;
            }
            scrollLoad.loading = false;
            $loading.find('img').hide();
            setDelayContent(data);
        }
    })
}
$(document).ready(function() {
    var timer;
    let panel = $('[id^=fieldset_delivered]');
    let  $loading = panel.find('.loading');
    let scrollLoad = $loading.data('scrollLoad');
    panel.find("input[name=dou_Filter_id_order], input[name=dou_Filter_parcel_number], input[name=dou_Filter_reference], input[name=dou_Filter_customer], input[name=dou_Filter_carrier_name]").keyup(function(){
        clearTimeout(timer);
        timer = setTimeout(function() {
            delayFilter(scrollLoad);
        }, 500);
    });

    panel.find(".dou_Filter_carrier, .dou_Filter_carrier_name,.dou_Filter_current_status, select[name=doutrack_Filter_result]").change(function() {
        delayFilter(scrollLoad);
    });

    key = 'month';
    active = $('.filter[filter="month"]').addClass('active');
    $('.date-range .filter').click(function() {
        if (active) {
            active.removeClass('active');
        }
        if ($(this).is(active)) {
            active = false;
            key = false;
        } else {
            active = $(this);
            active.addClass('active');
            key = active.attr('filter');
        }
        delayFilter(scrollLoad);
    })
    panel.find('input[name="date_range"]').daterangepicker();
    panel.find('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
        range = {
            start: picker.startDate.format('YYYY-MM-DD'),
            end: picker.endDate.format('YYYY-MM-DD')
        };
        if (active) {
            active.removeClass('active');
            active = false;
            key = false;
        }
        key = 'range';
        delayFilter(scrollLoad);
    });



})
