/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 10.1.2024 with dhlassistant v1.7 by DHL Parcel Polska */

tc_confirmOrderValidations['dhlassistant'] = function() {
    if (
        $('#dhlassistant_map_PL_CARRIER_POP').is(':visible') &&
        !$('#dhlassistant_messages > .current-address').is(':visible')
    ) {
        var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
        $('.shipping-validation-details').remove();
        shippingErrorMsg.append('<span class="shipping-validation-details"> (DHL POP - punkt odbioru)</span>')
        shippingErrorMsg.show();
        scrollToElement(shippingErrorMsg);
        return false;
    } else {
        return true;
    }
}

var tc_load_dhlassistant_script = function() {
    function shippingMethodChange() {
        var activeRadio = $(".delivery-options input[type='radio']:checked");
        var elValue = parseInt(activeRadio.val());
        var carrierCode = $("#dhlassistant_carrier_code_" + elValue).val();
        var requireSelection = $('#dhlassistant_need_selection_' + elValue).val();
        var parcelIdent = $('#dhlassistant_parcel_ident_value_' + carrierCode).val();
        if (requireSelection && (typeof parcelIdent === 'undefined' || "" === parcelIdent)) {
            $('button[name="confirmDeliveryOption"]').attr('disabled', true);
            console.log(carrierCode);
        }
        else if (carrierCode == ['PL_CARRIER_POP'] || carrierCode == ['PL_CARRIER_POP_COD'] || carrierCode == ['FOREIGN_CARRIER_PARCELSHOP']) {
            $('button[name="confirmDeliveryOption"]').attr('disabled', true);
            $( ".current-address" ).hide();
        }
        else {
            $('button[name="confirmDeliveryOption"]').attr('disabled', false);
        }
    }

    function parcelMapChange() {
        $('button[name="confirmDeliveryOption"]').attr('disabled', true);
        $( ".current-address" ).hide();
    }

    $(".delivery-options input[type='radio']").change(function (e) {
        shippingMethodChange();
    });

    $('#dhlassistant_ps_checkbox').bind('change', parcelMapChange);
    $('#dhlassistant_pl_checkbox').bind('change', parcelMapChange);

    shippingMethodChange();

    MapPointSelectListenerPlCarrierPop = function(msg)
    {
        var point = JSON.parse(msg.data);
        $('#dhlassistant_parcel_ident_value_PL_CARRIER_POP').val(point.sap);
        $('#dhlassistant_parcel_postal_code_value').val(point.zip);
        $( "p.current-address" ).text(point.name+" "+point.street+" "+point.streetNo+" "+point.zip+" "+point.city);
        SaveDhlCarrierOptionsPlCarrierPop();
    }

    SendToParcelShopChangePlCarrierPop = function()
    {
        $('#dhlassistant_ps_checkbox').parent().parent().removeClass('disabled');
        var val = $('#dhlassistant_ps_checkbox').prop('checked');
        if (val)
        {
            if (dhlassistant_require_postalcode_for_ps)
            {
                $('#dhlassistant_parcel_postal_code .label').html('Kod pocztowy Parcelshop:');
                $('#dhlassistant_parcel_postal_code').show();
            }
            else
            {
                $('#dhlassistant_parcel_postal_code').hide();
            }
            $('#dhlassistant_pl_checkbox').prop('checked', false);
            $('#dhlassistant_pl_checkbox').prop('disabled', true);
            SendToParcelLockerChangePlCarrierPop();
            $('#dhlassistant_parcel_ident').show();
            $('#dhlassistant_map_PL_CARRIER_POP').html('<object data="'+dhlassistant_map_for_pl_url+'" style="width:100%;height:813px;" frameborder="0" scrolling="no" id="myFrame"></object>');
            $('#dhlassistant_map_PL_CARRIER_POP').show();
        }
        else
        {
            if (!$('#dhlassistant_pl_checkbox').prop('checked'))
            {
                $('#dhlassistant_map_PL_CARRIER_POP').hide();
                $('#dhlassistant_parcel_ident').hide();
                $('#dhlassistant_parcel_postal_code').hide();
            }
            $('#dhlassistant_pl_checkbox').prop('disabled', false);
            $('#dhlassistant_pl_checkbox').parent().parent().removeClass('disabled');
        }


    }
    SendToParcelLockerChangePlCarrierPop = function()
    {
        $('#dhlassistant_pl_checkbox').parent().parent().removeClass('disabled');
        var val = $('#dhlassistant_pl_checkbox').prop('checked');
        if (val)
        {
            if (dhlassistant_require_postnummer_for_pl)
            {
                $('#dhlassistant_postnummer').show();
            }
            else
            {
                $('#dhlassistant_postnummer').hide();
            }
            if (dhlassistant_require_postalcode_for_pl)
            {
                $('#dhlassistant_parcel_postal_code .label').html('Kod pocztowy Parcelstation:');
                $('#dhlassistant_parcel_postal_code').show();
            }
            else
            {
                $('#dhlassistant_parcel_postal_code').hide();
            }
            $('#dhlassistant_ps_checkbox').prop('checked', false);
            $('#dhlassistant_ps_checkbox').prop('disabled', true);
            SendToParcelShopChangePlCarrierPop();
            $('#dhlassistant_parcel_ident').show();
            $('#dhlassistant_map_PL_CARRIER_POP').show();
        }
        else
        {
            if (!$('#dhlassistant_ps_checkbox').prop('checked'))
            {
                $('#dhlassistant_map_PL_CARRIER_POP').hide();
                $('#dhlassistant_parcel_ident').hide();
                $('#dhlassistant_parcel_postal_code').hide();
            }
            $('#dhlassistant_ps_checkbox').prop('disabled', false);
            $('#dhlassistant_ps_checkbox').parent().parent().removeClass('disabled');
            $('#dhlassistant_postnummer').hide();
        }

    }
    SaveDhlCarrierOptionsPlCarrierPop = function()
    {

        $('#dhlassistant_messages .success').hide();
        $('#dhlassistant_messages .error').hide();
        $('#HOOK_PAYMENT').html('');

        var ajax_data = {};
        var val_ps = $('#dhlassistant_ps_checkbox').prop('checked');
        var val_pl = $('#dhlassistant_pl_checkbox').prop('checked');
        var val_parcel_ident = $('#dhlassistant_parcel_ident_value_PL_CARRIER_POP').val();
        var val_postnummer = $('#dhlassistant_postnummer_value').val();
        var val_parcel_postal_code = $('#dhlassistant_parcel_postal_code_value').val();

        if (val_ps)
            ajax_data.ParcelShop = val_ps;
        if (val_pl)
            ajax_data.ParcelLocker = val_pl;
        if (val_parcel_ident)
            ajax_data.ParcelIdent = val_parcel_ident;
        if (val_postnummer)
            ajax_data.Postnummer = val_postnummer;
        if (val_parcel_postal_code)
            ajax_data.ParcelPostalCode = val_parcel_postal_code;

        $.ajax({
            type: "POST",
            headers: { "cache-control": "no-cache" },
            url: dhlassistant_ajax_catcher_url,
            data: ajax_data,
            context: document.body,
            dataType : "json",
            success: function(message)
            {
                if (message == 'Success')
                    $('#dhlassistant_messages .success').slideDown('slow');
                else
                    $('#dhlassistant_messages .error').slideDown('slow');
                if (typeof(updatePaymentMethodsDisplay) !== "undefined")
                {
                    $('#uniform-cgv').parent().show();
                    if ($('#cgv').prop('checked'))
                        updatePaymentMethodsDisplay();
                }
                $('button[name="confirmDeliveryOption"]').attr('disabled', false);

                var scrollToMessage = document.getElementById("SCROLL_TO_PL_CARRIER_POP");
                scrollToMessage.scrollIntoView();
            },
            error: function()
            {
                scrollToMessage.slideDown('slow');
                scrollToMessage.scrollIntoView();
            }
        });
    }
    DhlOptionsInitPlCarrierPop = function(){
        dhlassistant_ajax_catcher_url = $("#PL_CARRIER_POP_dhlassistant_ajax_catcher_url").val();
        dhlassistant_carrier_id = $("#PL_CARRIER_POP_dhlassistant_carrier_id").val();
        dhlassistant_is_ps_available = !!+$("#PL_CARRIER_POP_dhlassistant_is_ps_available").val();
        dhlassistant_is_ps_only_service = !!+$("#PL_CARRIER_POP_dhlassistant_is_ps_only_service").val();
        dhlassistant_is_pl_available = !!+$("#PL_CARRIER_POP_dhlassistant_is_pl_available").val();
        dhlassistant_is_map_for_parcel_available = !!+$("#PL_CARRIER_POP_dhlassistant_is_map_for_parcel_available").val();
        dhlassistant_map_for_ps_url = $("#PL_CARRIER_POP_dhlassistant_map_for_ps_url").val();
        dhlassistant_map_for_pl_url = $("#PL_CARRIER_POP_dhlassistant_map_for_pl_url").val();
        dhlassistant_require_postnummer_for_pl = !!+$("#PL_CARRIER_POP_dhlassistant_require_postnummer_for_pl").val();
        dhlassistant_require_postalcode_for_ps = !!+$("#PL_CARRIER_POP_dhlassistant_require_postalcode_for_ps").val();
        dhlassistant_require_postalcode_for_pl = !!+$("#PL_CARRIER_POP_dhlassistant_require_postalcode_for_pl").val();
        dhlassistant_ok = ($('#delivery_option_' + dhlassistant_carrier_id).val() == (dhlassistant_carrier_id+','));
        if (dhlassistant_ok)
        {
            if (dhlassistant_is_ps_only_service) /* jeśli musi być podany nr. PS aby przejść dalej*/
            {
                $('#uniform-cgv').parent().hide();
                $('#HOOK_PAYMENT').html('');
            }
            if ((dhlassistant_is_ps_available || dhlassistant_is_pl_available) && dhlassistant_is_map_for_parcel_available)
            {
                if (window.addEventListener)
                {
                    window.addEventListener("message", MapPointSelectListenerPlCarrierPop, false);
                }
                else
                {
                    window.attachEvent("onmessage", MapPointSelectListenerPlCarrierPop);
                }
            }
            SendToParcelShopChangePlCarrierPop();
            SendToParcelLockerChangePlCarrierPop();
            $('#dhlassistant_ps_checkbox').bind('change', SendToParcelShopChangePlCarrierPop);
            $('#dhlassistant_pl_checkbox').bind('change', SendToParcelLockerChangePlCarrierPop);
            $('#dhlassistant_PL_CARRIER_POP').show();

        }
    };
    /* init */
    if (document.readyState !== 'complete')
        $(DhlOptionsInitPlCarrierPop);
    else
        DhlOptionsInitPlCarrierPop();
}

checkoutShippingParser.dhlassistant = {

  after_load_callback: function(deliveryOptionIds) {
    if (typeof tc_load_dhlassistant_script === 'function') {
       tc_load_dhlassistant_script();
    }
  },

  init_once: function (elements) {
    if (debug_js_controller) {
      console.info('[thecheckout-dhlassistant.js] init_once()');
    }
  },

  delivery_option: function (element) {
    if (debug_js_controller) {
      console.info('[thecheckout-dhlassistant.js] delivery_option()');
    }
  },

  extra_content: function (element) {
  }

}
