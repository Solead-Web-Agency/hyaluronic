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

$(document).ready(function () {
    // PS 1.5017 is too old, drop CSS support
    const $tabSelector = $('#amazon-order-ps16');
    if ($tabSelector.length) {
        $($tabSelector).parent().insertBefore('#formAddPaymentPanel')
    }

    $('#amazon_get_details').click(function () {
        const isDebug = !!$('#amazon_debug').val(),
            $output = $('#amazon-output'),
            $error = $('#order-fba-ajax-error'),
            $loader = $('#order-fba-loader');
        $.ajax({
            type: 'POST',
            url: $('#fbaorder_url').val(),
            data: {
                amazon_token: $('#amazon_token').val(),
                id_order: $('#amazon_order_id').val(),
                amazon_id_lang: $('#amazon_id_lang').val(),
                sp_mkp: $('#amazon_marketplace_id').val(),
                context_key: $('#context_key').val(),
                debug: isDebug ? 1 : 0,
                action: 'info',
            },
            dataType: 'json',
            beforeSend: function() {
                $loader.show();
                $output.hide().html('');
            },
            complete: function() {
                $loader.hide();
            },
            success: function (data) {
                if (isDebug) {
                    $output.append(data.output).append(data.response).append(data.error);
                }
                if (!data.error) {
                    $('#order-fba-detail td[rel="ReceivedDateTime"]').html(data.info.ReceivedDateTime);
                    $('#order-fba-detail td[rel="StatusUpdatedDateTime"]').html(data.info.StatusUpdatedDateTime);
                    $('#order-fba-detail td[rel="FulfillmentMethod"]').html(data.info.FulfillmentMethod);
                    $('#order-fba-detail td[rel="FulfillmentOrderStatus"]').html(data.info.FulfillmentOrderStatus);
                    $('#order-fba-detail td[rel="DisplayableOrderId"]').html(data.info.DisplayableOrderId);
                    $('#order-fba-detail td[rel="Items"]').html(data.info.Items);
                    $('#order-fba-detail td[rel="ShippingSpeedCategory"]').html(data.info.ShippingSpeedCategory);
                    $('#order-fba-detail td[rel="EstimatedShipDateTime"]').html(data.info.EstimatedShipDateTime);
                    $('#order-fba-detail td[rel="EstimatedArrivalDateTime"]').html(data.info.EstimatedArrivalDateTime);
                    $('#order-fba-detail').show();
                    $('#order-fba-detail-spacer').show();
                    $error.hide();
                } else {
                    if (data.error_message) {
                        $('#order-fba-error-message').show().html(data.error_message);
                    } else {
                        $error.show();       
                    }
                }

                if (data.errors && data.errors.length) {
                    $.each(data.errors, function (e, error) {
                        $error.append('<br />' + error);
                    });
                    $error.show();
                }
            },
            error: function (data) {
                if (data.status && data.status.length)
                    $output.append('<b>Status Code:' + data.status + '</b>');
                if (data.statusText && data.statusText.length)
                    $output.append('<b>Status Text:' + data.statusText + '</b>');
                if (data.responseText && data.responseText.length)
                    $output.append('<b>Response:</b>' + data.responseText);
                $output.show();
                $error.show();
            }
        });
    });

    $('#amazon_cancel_fba').click(function () {
        if (!confirm($('#amazon_text_fba_cancel').val())) {
            return false;
        }

        const isDebug = !!$('#amazon_debug').val(),
            $fbaButton = $(this),
            $loader = $('#order-fba-loader'),
            $output = $('#amazon-output'),
            $error = $('#order-fba-ajax-error');
        $.ajax({
            type: 'POST',
            url: $('#fbaorder_url').val(),
            data: {
                amazon_token: $('#amazon_token').val(),
                id_order: $('#amazon_order_id').val(),
                amazon_id_lang: $('#amazon_id_lang').val(),
                sp_mkp: $('#amazon_marketplace_id').val(),
                context_key: $('#context_key').val(),
                debug: isDebug ? 1 : 0,
                action: 'cancel',
            },
            dataType: 'json',
            beforeSend: function() {
                $fbaButton.prop('disabled', true);
                $loader.show();
                $output.hide().html('');
            },
            complete: function () {
                $loader.hide();
                $fbaButton.prop('disabled', false);
            },
            success: function (data) {
                if (isDebug) {
                    $output.append(data.output);
                    $output.append(data.response);
                    $output.append(data.error);
                }
                if (!data.error) {
                    $('#order-fba-canceled').show();
                } else {
                    if (data.error_message) {
                        $('#order-fba-error-message').show().html(data.error_message);
                    } else {
                        $error.show();   
                    }
                }
                if (data.errors && data.errors.length) {
                    $.each(data.errors, function (e, error) {
                        $error.append('<br />' + error);
                    });
                    $error.show();
                }
            },
            error: function (data) {
                if (data.status && data.status.length)
                    $output.append('<b>Status Code:' + data.status + '</b>');
                if (data.statusText && data.statusText.length)
                    $output.append('<b>Status Text:' + data.statusText + '</b>');
                if (data.responseText && data.responseText.length)
                    $output.append('<b>Response:</b>' + data.responseText);
                $output.show();
                $error.show();
            }
        });
    });

    $('#amazon_fba_create').click(function () {
        const $fbaButton = $(this),
            $output = $('#amazon-output'),
            $loader = $('#order-fba-loader');

        $.ajax({
            type: 'POST',
            url: $('#fbaorder_url').val(),
            data: {
                amazon_token: $('#amazon_token').val(),
                id_order: $('#amazon_order_id').val(),
                amazon_id_lang: $('#amazon_id_lang').val(),
                sp_mkp: $('#amazon_marketplace_id').val(),
                context_key: $('#context_key').val(),
                debug: $('#amazon_debug').val(),
                action: 'create',
            },
            dataType: 'json',
            beforeSend: function() {
                $fbaButton.prop('disabled', true);
                $output.html('');
                $loader.show();
            },
            complete: function() {
                $fbaButton.prop('disabled', false);
                $loader.hide();
            },
            success: function (data) {
                if (parseInt($('#amazon_debug').val())) {
                    $output.append(data.output).append(data.response).append(data.error).show();
                }
                if (!data.error) {
                    $('#order-fba-message').show().html(data.response);
                    $('#order-fba-ajax-error').hide();
                } else {
                    if (data.error_message) {
                        $('#order-fba-error-message').show().html(data.error_message);
                    } else {
                        $('#order-fba-ajax-error').show();   
                    }
                }
                if (data.errors && data.errors.length) {
                    $.each(data.errors, function (e, error) {
                        $('#order-fba-ajax-error').append('<br />' + error);
                    });
                    $('#order-fba-ajax-error').show();
                }
            },
            error: function (data) {
                const $output = $('#amazon-output');
                if (data.status && data.status.length)
                    $output.append('<b>Status Code:' + data.status + '</b>');
                if (data.statusText && data.statusText.length)
                    $output.append('<b>Status Text:' + data.statusText + '</b>');
                if (data.responseText && data.responseText.length)
                    $output.append('<b>Response:</b>' + data.responseText);
                $output.show();
                $('#order-fba-ajax-error').show();
            }
        });

    });

});

