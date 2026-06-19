/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-9999 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

function getidorder(text) {
    var regex = /id_order\=(\d+)\&/gi;
    match = regex.exec(text);
    return match[1];
}

function ChangeOrderStatus(id, state) {
    $.post(previewOrderUrl, {id_employee: qov_employee_id, secure_key: module_secure_key, idorder: id, ostatus: state}, function (data) {
        $('#OrderStatusSaveIcon' + id).addClass('icon-check-circle');
        $('#OrderStatusSaveIcon' + id).removeClass('icon-save');
        $('#OrderStatusHistoryDiv' + id + ' table > tbody > tr:first').after($(data));
    }).done(function() {
        $('#OrderStatusHistoryDiv' + id).parent().parent().parent().prev('tr').find('td span:contains("'+$('.qov_old_status_of_order').html()+'")').html($('.qov_last_status_of_order').html()).removeAttr('style').attr('style', $('.qov_last_status_of_order').attr('style'));
        $('.qov_last_status_of_order').remove();
        $('.qov_old_status_of_order').remove();

    });
}

function ChangeTrackingNumber(id, nb) {
    $.get(previewOrderUrl, {id_employee: qov_employee_id, secure_key: module_secure_key, ido: id, tracking: nb}, function (data) {
        $('#TrackingSaveIcon' + id).addClass('icon-check-circle');
        $('#TrackingSaveIcon' + id).removeClass('icon-save');
        $('#TrackingNumber' + id).html(nb);
        $('#TrackingLink' + id).attr('href', data);
    });
}

$(document).ready(function () {
    var parts = window.location.search.substr(1).split("&");
    var $_GET = {};
    for (var i = 0; i < parts.length; i++) {
        var temp = parts[i].split("=");
        $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
    }


    if ($_GET.controller == "AdminOrders") {
        var array_of_expanded_orders = new Array();
        $('.order td').prop('onclick', null).off('click');
        $('.order td').not($('.order td .btn-group-action').parent('td')).not($('.order td myparcel-tracktrace').parent('td')).not($('.order td:last-child, .order td:first-child').prev('td')).not('.order td:last-child, .order td:first-child').toggle(function (e) {
                $('.order td').not($('.order td:last-child, .order td:first-child').prev('td')).not('.order td:last-child, .order td:first-child').each(function () {
                    var that = $(this);
                    if (!(getidorder($(this).parent().html()) in array_of_expanded_orders)) {
                        array_of_expanded_orders[getidorder($(this).parent().html())] = 1;
                        $.post(previewOrderUrl, {id_employee: qov_employee_id, secure_key: module_secure_key, id_order: getidorder($(this).parent().html())}, function (data) {
                            $(data).insertAfter(that.closest('tr'));
                        });
                    }
                });
            },
            function () {
            });
    }
});


$(document).ready(function() {
    $(document).on('keydown', 'input.trackingInputField', function(ev) {
        if(ev.which === 13) {
            ChangeTrackingNumber($(this).attr('title'), $('#TrackingNumberValue'+$(this).attr('title')).val());
            return false;
        }
    });
});