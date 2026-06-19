/**
 * Copyright since 2007 Viva Wallet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@vivawallet.com so we can send you a copy immediately.
 *
 * @author    Viva Wallet <support@vivawallet.com>
 * @copyright Since 2007 Viva Wallet
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
$(document).ready(function () {
    $('.vivawalletsmartcheckout-capture-card').hide();
    $('.vivawalletsmartcheckout-void-card').hide();
    $('#vivawalletsmartcheckout_trigger_capture').on('click', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-capture-card').toggle();
        if ($('.vivawalletsmartcheckout-partial-refund-card').is(':visible')) {
            $('.vivawalletsmartcheckout-partial-refund-card').hide();
        }
        if ($('.vivawalletsmartcheckout-full-card').is(':visible')) {
            $('.vivawalletsmartcheckout-full-card').hide();
        }
        if ($('.vivawalletsmartcheckout-void-card').is(':visible')) {
            $('.vivawalletsmartcheckout-void-card').hide();
        }
        $('.vivawalletsmartcheckout-input-capture-amount').val($('input[name=vivawalletsmartcheckout_preauthorized_amount]').val());
    });

    $('form#vivawalletsmartcheckout_capture_form').on('submit', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-transaction-error').text('').hide();
        $('#vivawalletsmartcheckout_capture_form_submit').prop('disabled', true);
        const fullAmount = vivawalletsmartcheckout_getFormattedAmount($('.vivawalletsmartcheckout-input-capture-amount').val());
        const transactionId = $('input[name=vivawalletsmartcheckout_preauthorization_transaction_id]').val();
        const transactionDate = $('input[name=vivawalletsmartcheckout_preauthorization_transaction_date]').val();

        vivawalletsmartcheckout_captureOrVoidTransaction(fullAmount, transactionId, transactionDate, true, []);
    });
    $('#vivawalletsmartcheckout_trigger_void').on('click', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-void-card').toggle();
        if ($('.vivawalletsmartcheckout-partial-refund-card').is(':visible')) {
            $('.vivawalletsmartcheckout-partial-refund-card').hide();
        }
        if ($('.vivawalletsmartcheckout-full-card').is(':visible')) {
            $('.vivawalletsmartcheckout-full-card').hide();
        }
        if ($('.vivawalletsmartcheckout-capture-card').is(':visible')) {
            $('.vivawalletsmartcheckout-capture-card').hide();
        }
        $('.vivawalletsmartcheckout-input-void-amount').val($('input[name=vivawalletsmartcheckout_preauthorized_amount]').val());
    });

    $('form#vivawalletsmartcheckout_void_form').on('submit', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-transaction-error').text('').hide();
        $('#vivawalletsmartcheckout_void-form_submit').prop('disabled', true);
        const fullAmount = vivawalletsmartcheckout_getFormattedAmount($('.vivawalletsmartcheckout-input-void-amount').val());
        const transactionId = $('input[name=vivawalletsmartcheckout_preauthorization_transaction_id]').val();
        const transactionDate = $('input[name=vivawalletsmartcheckout_preauthorization_transaction_date]').val();

        vivawalletsmartcheckout_captureOrVoidTransaction(fullAmount, transactionId, transactionDate, false, []);
    });
    //Close refund forms
    $('.vivawalletsmartcheckout-cancel-capture').on('click', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-capture-card').hide();
    });
    $('.vivawalletsmartcheckout-cancel-void').on('click', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-void-card').hide();
    });
});

function vivawalletsmartcheckout_captureOrVoidTransaction(amount, transaction_id, transaction_date, is_capture_action, detail_data ) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        async: false,
        url: is_capture_action ? vivawallet_capture_url : vivawallet_void_url,
        data: {
            amount: amount,
            transaction_id: transaction_id,
            transaction_date: transaction_date,
            order_id: order_id,
            order_reference: order_reference,
            payment_method: payment_method,
            detail_data: detail_data,
            is_capture_action: is_capture_action
        },
        success: function (resp) {
            if (resp.error === true) {
                vivawalletsmartcheckout_showError(resp.message);
                return false;
            }
            location.reload();
        },
        error: function (err) {
            vivawalletsmartcheckout_showError(
                (typeof err.message !== 'undefined') ? err.message : general_error_message
            );
        }
    });
}