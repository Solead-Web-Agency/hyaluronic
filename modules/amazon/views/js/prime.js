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

(function ($) {
    function ajaxLoading() {
        $('#amazon-order-prime-data').hide();
        $('#amazon-order-prime-loading').removeClass('hidden');
    }

    function ajaxFinish() {
        $('#amazon-order-prime-loading').addClass('hidden');
        $('#amazon-order-prime-data').show();
    }

    // todo: Handle HTML
    function ajaxSuccess(response) {
        var $layout = $('#amazon-order-prime'),
            $layoutInner = $layout.find('#amazon-order-prime-data');
        if (response.success && response.template) {
            $layout.html(response.template);
        } else if (response.reason) {
            $layoutInner.html('');
            $.each(response.reason, function (m, reason) {
                $layoutInner.append('<div class="error alert alert-danger">' + reason + '</div>');
            });
        } else {
            $layoutInner.html('<div class="error alert alert-danger">Unknown error</div>');
        }
    }

    function ajaxError(jqXHR, textStatus, errorThrown) {
        if (textStatus !== 'error' || errorThrown !== '') {
            showErrorMessage(textStatus + ': ' + errorThrown);
        }
    }

    $(document).ready(function () {
        var endpoint = $('#fulfillment_url').val(),
            instant_token = $('#instant_token').val(),
            $primeDataContainer = $('#amazon-order-prime-data'),
            idOrder = $primeDataContainer.data('idOrder'),
            marketplaceId = $primeDataContainer.data('marketplaceId');

        // Step 1: Execute get-eligible-shipping-services
        $(document).on('click', '#amazon-get-eligible-shipping-services', function () {
            var params = {
                action: 'get-shipping-service',
                instant_token: instant_token,
                id_order: idOrder,
                marketplace_id: marketplaceId,
            };
            $.ajax({
                type: 'GET',
                url: endpoint,
                dataType: 'json',
                data: $.param(params),
                beforeSend: ajaxLoading,
                success: ajaxSuccess,
                error: ajaxError,
                complete: ajaxFinish
            });
        });

        // Step 2.1: Choose shipping service
        $(document).on('change', '#amazon-order-prime-carriers', function () {
            var $labelFormatSection = $('#amazon-order-prime-label-formats'),
                $labelFormats = $('.amazon-order-prime-label-formats-of-carrier'),
                selectedCarrier = $(this).val();

            $labelFormats.addClass('hidden').prop('disabled', true);  // Hide all formats of all carriers
            if (selectedCarrier) {
                $labelFormatSection.removeClass('hidden');    // Show the label format section
                // But not the formats of selected carrier
                $('#amazon-order-prime-label-formats-' + selectedCarrier).prop('disabled', false).removeClass('hidden');
            } else {
                $labelFormatSection.addClass('hidden');    // Hide the label format section
            }
        })
        // Step 2.2: Show creation button if choose both carrier + label format
        $(document).on('change', '#amazon-order-prime-carriers, .amazon-order-prime-label-formats-of-carrier', function () {
            var selectedCarrier = $('#amazon-order-prime-carriers').val(),
                $createSection = $('#amazon-order-prime-create-shipping-label');
            if (selectedCarrier) {
                var $labelFormat = $('#amazon-order-prime-label-formats-' + selectedCarrier);
                if ($labelFormat && $labelFormat.val()) {
                    $createSection.removeClass('hidden');
                    return;
                }
            }
            $createSection.addClass('hidden');
        });

        // Step 3: Create shipping label
        $(document).on('click', '#amazon-order-prime-create-shipping-label button', function () {
            var selectedCarrier = $('#amazon-order-prime-carriers').val(),
                instant_token = $('#instant_token').val(),
                $labelFormat = $('#amazon-order-prime-label-formats-' + selectedCarrier),
                $primeDataContainer = $('#amazon-order-prime-data'),
                idOrder = $primeDataContainer.data('idOrder'),
                marketplaceId = $primeDataContainer.data('marketplaceId');
            if (!selectedCarrier || !$labelFormat || !$labelFormat.val()) {
                return;
            }

            var data = {
                action: 'create-shipment',
                instant_token: instant_token,
                id_order: idOrder,
                marketplace_id: marketplaceId,
                shipment_service_id: selectedCarrier,
                shipment_service_format: $labelFormat.val(),
            };
            $.ajax({
                type: 'POST',
                url: $('#fulfillment_url').val(),
                dataType: 'json',
                data: data,
                beforeSend: ajaxLoading,
                success: ajaxSuccess,
                error: ajaxError,
                complete: ajaxFinish
            });
        });

        // Testing only
        $(document).on('click', '#amazon-order-prime-get-exist-label button', function () {
            var shipmentId = $('#amazon-order-prime-get-exist-label input').first().val(),
                $primeDataContainer = $('#amazon-order-prime-data'),
                idOrder = $primeDataContainer.data('idOrder'),
                marketplaceId = $primeDataContainer.data('marketplaceId');
            if (shipmentId) {
                $.ajax({
                    type: 'POST',
                    url: $('#fulfillment_url').val(),
                    dataType: 'json',
                    data: $.param({
                        action: 'get-shipment',
                        instant_token: $('#instant_token').val(),
                        id_order: idOrder,
                        marketplace_id: marketplaceId,
                        shipment_id: shipmentId,
                    }),
                    beforeSend: ajaxLoading,
                    success: function(response) {
                        console.log(response);
                    },
                    error: ajaxError,
                    complete: ajaxFinish
                });
            }
        });
    });
})(jQuery);
