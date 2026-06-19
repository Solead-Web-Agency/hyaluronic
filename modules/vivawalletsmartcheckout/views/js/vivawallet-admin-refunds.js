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
    //Hide full refund card on firt load
    if ($('.vivawalletsmartcheckout-full-refund-card').is(':visible')) {
        $('.vivawalletsmartcheckout-full-refund-card').hide();
    }
    //Hide partial refund card on first load
    if ($('.vivawalletsmartcheckout-partial-refund-card').is(':visible')) {
        $('.vivawalletsmartcheckout-partial-refund-card').hide();
    }

    if ($('.vivawalletsmartcheckout-section-title').length) {
        $('button.partial-refund-display').hide();
        $('#desc-order-partial_refund').hide();
    }

    //Trigger refund forms
    $('#vivawalletsmartcheckout_trigger_full_refund').on('click', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-full-refund-card').toggle();
        if ($('.vivawalletsmartcheckout-partial-refund-card').is(':visible')) {
            $('.vivawalletsmartcheckout-partial-refund-card').hide();
        }
        $('.vivawalletsmartcheckout-input-full-amount').val($('input[name=vivawalletsmartcheckout_refundable_amount]').val());
    });
    $('#vivawalletsmartcheckout_trigger_partial_refund').on('click', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-partial-refund-card').toggle();
        if ($('.vivawalletsmartcheckout-full-refund-card').is(':visible')) {
            $('.vivawalletsmartcheckout-full-refund-card').hide();
        }
    });
    //Close refund forms
    $('.vivawalletsmartcheckout-cancel-full-refund').on('click', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-full-refund-card').hide();
    });
    $('.vivawalletsmartcheckout-cancel-partial-refund').on('click', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-partial-refund-card').hide();
    });

    $('form#vivawalletsmartcheckout_refund_form').on('submit', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-transaction-error').text('').hide();
        $('#vivawalletsmartcheckout_refund_form_submit').prop('disabled', true);

        const fullAmount = vivawalletsmartcheckout_getFormattedAmount($('.vivawalletsmartcheckout-input-full-amount').val());
        const transactionId = $('input[name=vivawalletsmartcheckout_transaction_id]').val();
        const transactionDate = $('input[name=vivawalletsmartcheckout_transaction_date]').val();
        const generateSlip = $('#vivawalletsmartcheckout_full_generate_slip').is(':checked');
        const restockProducts = $('#vivawalletsmartcheckout_full_restock_products').is(':checked');

        vivawalletsmartcheckout_refundTransaction(fullAmount, transactionId, transactionDate, true, generateSlip, restockProducts, []);

    });

    $('form#vivawalletsmartcheckout_partial_refund_form').on('submit', function(e) {
        e.preventDefault();
        $('.vivawalletsmartcheckout-transaction-error').text('').hide();
        $('#vivawalletsmartcheckout_partial_refund_form_submit').prop('disabled', true);

        let detailData = [];
        let partialAmount = 0;
        let shownError = false;
        $('.vivawalletsmartcheckout-order-detail').each(function(){
            let orderDetailId = $(this).find('.vivawalletsmartcheckout-order-detail-id').val();
            let orderDetailQuantity = $(this).find('.vivawalletsmartcheckout-input-partial-quantity').val();
            let orderDetailAmount = $(this).find('.vivawalletsmartcheckout-input-partial-amount').val();
            let refundableAmount = $(this).find('.vivawalletsmartcheckout-partial-allowed-amount').text();
            let refundableQuantity = $(this).find('.vivawalletsmartcheckout-partial-allowed-quantity').text();
            if(Number(orderDetailAmount) > Number(refundableAmount)){
                vivawalletsmartcheckout_showError(amount_error_message);
                shownError = true;
                return false;
            }
            if(Number(orderDetailQuantity) > Number(refundableQuantity)){
                vivawalletsmartcheckout_showError(general_error_message);
                shownError = true;
                return false;
            }
            if (orderDetailAmount) {
                partialAmount = partialAmount + Number(vivawalletsmartcheckout_getFormattedAmount(orderDetailAmount));
                detailData.push({
                    ['id_order_detail']: orderDetailId,
                    ['product_quantity']: orderDetailQuantity,
                    ['order_detail_amount']: orderDetailAmount
                });
            }
        });

        const refundableAmount = vivawalletsmartcheckout_getFormattedAmount($('input[name=vivawalletsmartcheckout_refundable_amount]').val());
        const transactionId = $('input[name=vivawalletsmartcheckout_transaction_id]').val();
        const transactionDate = $('input[name=vivawalletsmartcheckout_transaction_date]').val();
        const isFullRefund = partialAmount === Number(refundableAmount) ? true : false;
        const generateSlip = $('#vivawalletsmartcheckout_partial_generate_slip').is(':checked');
        const restockProducts = $('#vivawalletsmartcheckout_partial_restock_products').is(':checked');

        if (!shownError) {
            vivawalletsmartcheckout_refundTransaction(partialAmount, transactionId, transactionDate, isFullRefund, generateSlip, restockProducts, detailData);
        }
    });
});

function vivawalletsmartcheckout_refundTransaction(amount, transaction_id, transaction_date, is_full_refund, generate_slip, restock_products, detail_data) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        async: false,
        url: vivawallet_refund_url,
        data: {
            amount: amount,
            transaction_id: transaction_id,
            transaction_date: transaction_date,
            order_id: order_id,
            order_reference: order_reference,
            payment_method: payment_method,
            is_full_refund: is_full_refund,
            generate_slip: generate_slip,
            restock_products: restock_products,
            detail_data: detail_data
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