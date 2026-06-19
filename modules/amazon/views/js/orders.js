/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */

var pageInitialized1 = false;
$(document).ready(function () {
    let demoData = [];

    if (pageInitialized1) return;
    pageInitialized1 = true;
    $('.hint').slideDown();

    $('input[name="amazon_lang"]:first').attr('checked', 'checked');

    $('li[id^="menu-"]').click(function () {
        result = $(this).attr('id').match('^(.*)-(.*)$');
        tab = result[2];

        $('input[name=selected_tab]').val(tab);

        if (!$(this).hasClass('selected')) {
            $('li[id^="menu-"]').removeClass('selected');
            $(this).addClass('selected');
            $('div[id^="menudiv-"]').hide();
            $('div[id^="menudiv-' + tab + '"]').show();
        }
    });

    function ManageAjaxError(aCall, data, outdiv) {
        if (window.console) {
            console.log('Ajax Error', aCall, data);
        }
        outdiv.show().html($('#serror').val());

        if (data.output)
            outdiv.append('<br />' + data.output);

        if (data.responseText)
            outdiv.append('<br />' + data.responseText);

        outdiv.append('<hr />');
        outdiv.append($('#sdebug').val() + ':  ');

        outdiv.append('<form method="' + aCall.type + '" action="' + aCall.url + '?debug=1&debug_header&' + aCall.data + '" target="_blank">' +
            '<input type="submit" class="button" id="send-debug" value="Execute in Debug Mode" /></form>');
    }

    $('#remove-failed-orders').click(function() {
        var $selected = $(this),
            $warningZone = $('#amazon-remove-failed-orders-warning'),
            $errorZone = $('#amazon-remove-failed-orders-error'),
            $successZone = $('#amazon-remove-failed-orders-success'),
            $loader = $('#amazon-remove-failed-orders-loader');

        if ($selected.is(":disabled")) {
            return false;
        }

        $.ajax({
            type: 'POST',
            url: $('#import_enhancement_url').val(),
            dataType: 'json',
            data: [
                $('#amazonParams').serialize(),
                $.param({
                    action: 'clear_failed_orders',
                    context_key: $('#context_key').val(),
                }),
            ].join('&'),
            beforeSend: function() {
                $warningZone.hide().html('');
                $errorZone.hide().html('');
                $successZone.hide().html('');
                $loader.fadeIn();
                $selected.attr('disabled', 'disabled');
            },
            complete: function () {
                $loader.fadeOut();
                $selected.removeAttr('disabled');
            },
            success: function (data) {
                if (data.success) {
                    $('#cron_failed_orders tbody').empty();
                    $successZone.show().append(data.message);
                } else {
                    $errorZone.show().append(data.message);
                }
            },
        });
    });

    $('#submit-orders-list').click(function () {
        const $warningZone = $('#amazon-import-warning'),
            $errorZone = $('#amazon-import-error'),
            $loader = $('#amz-loader'),
            $spRegion = $('#country-selector input[name=amazon_sp_region]:checked'),
            mkpId = $spRegion.data('mkpId'),
            regionId = $spRegion.data('regionId'),
            instantToken = $('#instant_token').val();

        if (!mkpId && !regionId) {
            alert($('#msg_lang').val());
            return false;
        }

        const pAjax = {
            type: 'POST',
            url: $('#orders_url').val(),
            dataType: 'json',
            data: [
                $('#amazonOrderOptions').serialize(),
                $.param({
                    context_key: $('#context_key').val(),
                    sp_mkp: mkpId || '',
                    sp_region: regionId,
                    instant_token: instantToken,
                }),
            ].join('&'),
        };

        $.ajax(Object.assign({}, pAjax, {
            beforeSend: function() {
                $warningZone.hide().html('');
                $errorZone.hide().html('');
                $loader.fadeIn();
            },
            complete: function() {
                $loader.fadeOut();
            },
            success: function (data) {
                demoData = data.generated_data;
                if (data.orders && data.count) {
                    DisplayOrders(data.orders);
                } else if (!data.error) {
                    $('#amazon-import-error').append($('#no_orders').val() + '<br />').show();
                    $('table.order tbody tr:gt(0)').remove();
                }

                if (data.warning && data.warnings) {
                    $.each(data.warnings, function (e, message) {
                        $warningZone.append(message + '<br />');
                    });
                    $warningZone.show();
                }

                if (data.output && data.output.length)
                    $('#amazon-import-error').append(data.output + '<br />').show();
                if (data.errors && data.errors.length)
                    $('#amazon-import-error').append(data.errors + '<br />').show();
            },
            error: function (data) {
                demoData = [];
                ManageAjaxError(pAjax, data, $('#amazon-import-error'));
            }
        }));
    });

    function DisplayOrders(orders) {
        var irow = 0;
        $('table.order tbody tr:gt(0)').remove();

        $.each(orders, function (o, order) {
            if (irow === 0)
                $('#order-table-heading').show();

            if (window.console)
                console.log(order);

            // Clone Line, Append to the table and fill the order data
            var order_line = $('#order-model').clone().appendTo('table.table.order tbody');
            order_line.attr('id', 'O' + o);

            // Fill Lines
            //
            order_line.children('[rel=flag]').html('<img src="' + order.flag_src + '" alt="" />');
            order_line.children('[rel=date]').html(order.date);
            order_line.children('[rel=id]').html(
                !order.imported ? order.id :
                    '<a href="' + order.ps_url + '" title="Go to order" target="_blank">' + order.id + '(' + order.ps_id + ')' + '</a>'
            );
            order_line.children('[rel=status]').html(order.status);
            if (order.invoice)
                order_line.children('[rel=invoice]').html(order.invoice); // KAM_CHG
            order_line.children('[rel=customer]').html(order.customer);
            order_line.children('[rel=shipping]').html(order.shipping);
            order_line.children('[rel=fulfillment]').html(
                !order.is_prime ? order.fulfillment : order.fulfillment + ' <b>(Prime)</b>'
            );
            order_line.children('[rel=business]').html(order.business);
            order_line.children('[rel=quantity]').html(order.quantity);
            order_line.children('[rel=total]').html(order.total);
            order_line.addClass(irow++ % 2 ? 'alt_row' : '');
            var checkbox = order_line.children('td[rel=checkbox]').children('input');
            checkbox.attr('name', 'order_id[' + o + ']').val(o);

            checkbox.attr('checked', false);
            checkbox.attr('disabled', false);

            if (order.imported || order.canceled || order.pending) {
                checkbox.attr('disabled', true);
                order_line.addClass('imported_row2');
            } else {
                checkbox.attr('disabled', false);
            }

            order_line.show();
        });
    }

    // Import Orders
    //
    $('#submit-orders-import').click(function () {
        const $errorZone = $('#amazon-import-error'),
            $warningZone = $('#amazon-import-warning'),
            $orderList = $('.order-check:checked'),
            $spRegion = $('#country-selector input[name=amazon_sp_region]:checked'),
            mkpId = $spRegion.data('mkpId'),
            regionId = $spRegion.data('regionId'),
            instantToken = $('#instant_token').val();
        if (!$orderList.length && !$('input[name="amazon_lang"]').val()) {
            alert($('#msg_select').val());
            return false;
        }

        const pAjax = {
            type: 'POST',
            url: $('#import_enhancement_url').val(),
            dataType: 'json',
            data: [
                $('#amazonParams').serialize(),
                $('#amazonOrders').serialize(),
                $.param({
                    demo_mode: $('.amz-options').find('input[name="demo_mode"]:checked').val() || 0,
                    sp_mkp: mkpId || '',
                    sp_region: regionId,
                    context_key: $('#context_key').val(),
                    generated_data: demoData,
                    instant_token: instantToken,
                }),
            ].join('&'),
        };

        $.ajax(Object.assign( {}, pAjax, {
            beforeSend: function() {
                $errorZone.html('').hide();
                $warningZone.html('').hide();
                $.each($orderList.not(':disabled'), function () {
                    $(this).hide();
                    $(this).after('<img src="' + $('#img_loader_small').val() + '" alt="" class="amz-tmp-loader" />');
                });
            },
            complete: function() {
                $('#amz-loader').fadeOut();
                // Restore Checkboxes
                $orderList.attr('disabled', true).show();
                $('.amz-tmp-loader').remove();
            },
            success: function (data) {
                if ((typeof(data.error) != 'undefined' && data.error && (data.errors).length > 0) || (typeof(data.message) != 'undefined' && data.message && (data.errors).length > 0)) {
                    $.each(data.errors, function (e, message) {
                        $errorZone.append(message + '<br />');
                    });
                    $.each(data.messages, function (e, message) {
                        $errorZone.append(message + '<br />');
                    });
                    $errorZone.show();
                }

                if (typeof(data.warning) != 'undefined' && data.warning) {
                    $.each(data.warnings, function (e, message) {
                        $warningZone.append(message + '<br />');
                    });
                    $warningZone.show();
                }

                if (typeof(data.count) != 'undefined' && data.count) {
                    DisplayImported(data.orders);
                } else {
                    $errorZone.append('A server-side error has occurred. Please contact you server administrator<br />');
                    $errorZone.show();
                }
            },
            error: function (data) {
                ManageAjaxError(pAjax, data, $errorZone);
            }
        }));

    });

    function DisplayImported(orders) {
        $.each(orders, function (o, order) {
            if (order.status != true) {
                $('#O' + o).removeClass('alt_row').addClass('error_row');
                return;
            }
            else
                $('#O' + o).removeClass('alt_row').addClass('imported_row');

            $('#O' + o).after('<tr><td colspan="3"> </td><td colspan="7"><table id="OD' + o + '" class="order-line"></table></td></tr>');
            $('#O' + o + ' td[rel=id]').html(order.link);

            $.each(order.products, function (p, product) {

                console.log(product);
                product_info =
                    '<tr>\n' +
                    '<td>' + product.SKU + '</td>' +
                    '<td>' + product.ASIN + '</td>' +
                    '<td>' + product.product + '</td>' +
                    '<td>' + product.quantity + '</td>' +
                    '<td>' + product.currency + '</td>' +
                    '<td align="right">' + product.price + '</td>' +
                    '<tr>' + '\n';

                $('#OD' + o).append(product_info);
            });
        });
    }

    if ($.isFunction($(document).on)) {
        // Misc Functions
        //
        $(document).on('click', 'table.order tbody tr', function (e) {
            if (e.target.type !== 'checkbox') {
                $(':checkbox', this).trigger('click');
            }
        });

        $('#checkme').on('click', function () {
            $('.order-check').each(function () {
                if ($(this).attr('checked'))
                    $(this).attr('checked', false);
                else if (!$(this).attr('disabled'))
                    $(this).attr('checked', 'checked');
            });
        });
    }

    if ($.datepicker.initialized !== 'undefined') {
        $("#datepickerTo").datepicker({
            prevText: "",
            nextText: "",
            dateFormat: "yy-mm-dd"
        });

        $("#datepickerFrom").datepicker({
            prevText: "",
            nextText: "",
            dateFormat: "yy-mm-dd"
        });
    }

    $('#cron_failed_orders_handler').click(function() {
        $(this).toggleClass('dropup').toggleClass('dropdown');
        $('#cron_failed_orders').slideToggle();
    });
});
