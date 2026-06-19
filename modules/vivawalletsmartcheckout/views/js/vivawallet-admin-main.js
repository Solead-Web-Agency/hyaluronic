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
$(document).ready(function() {
    const vivawalletsmartcheckout_selector_demo_mode = $('#VIVAWALLET_SMART_CHECKOUT_DEMO_MODE_on').parent();
    const vivawalletsmartcheckout_radio_button_demo_mode_on = $('#VIVAWALLET_SMART_CHECKOUT_DEMO_MODE_on');
    var descriptor = $('#VIVAWALLET_SMART_CHECKOUT_DYNAMIC_DESCRIPTOR');
    descriptor.prop('maxlength', 13);
    var descriptorText = descriptor.val();
    var descriptorRow = $( descriptor ).closest( '.form-group' );

    var descriptorPreview = '\
    <div class="form-group" id="vivawallet-descriptor-preview-row">\
    <div class="col-md-2"></div>\
    <div class="col-md-6">\
      <table class="table table-dark table-bordered">\
        <thead>\
          <tr><th colspan="2" class="text-center"><strong>' + samplebank + '</strong></th></tr>\
        </thead>\
        <tbody>\
          <tr>\
            <td><strong>' + transactionReference + '</strong></td>\
            <td class="text-right"><strong>' + amount + '</strong></td>\
          </tr>\
          <tr>\
            <td style="color:#0a4b78;">' + yourCompanyName + ' <span id="vivawalletsmartcheckout_descriptor_preview_text">' + descriptorText + '</span></td>\
            <td class="text-right">' + formatedAmount + '</td>\
          </tr>\
        </tbody>\
      </table>\
    </div>\
    </div>';

    descriptorRow.after(descriptorPreview);

    descriptor.on('input', function(e) {
        $('#vivawalletsmartcheckout_descriptor_preview_text').html(e.target.value);
    });

    vivawalletsmartcheckout_radio_button_demo_mode_on.is(':checked')
        ? vivawalletsmartcheckout_showDemoCredentialInputs()
        : vivawalletsmartcheckout_showLiveCredentialInputs();

    vivawalletsmartcheckout_selector_demo_mode.click(function () {
        vivawalletsmartcheckout_radio_button_demo_mode_on.is(':checked')
            ? vivawalletsmartcheckout_showDemoCredentialInputs()
            : vivawalletsmartcheckout_showLiveCredentialInputs();
    })
});

function vivawalletsmartcheckout_showDemoCredentialInputs() {
    $('#VIVAWALLET_SMART_CHECKOUT_DEMO_CLIENT_ID').closest('div[class^="form-group"]').css('display', 'block');
    $('#VIVAWALLET_SMART_CHECKOUT_DEMO_CLIENT_SECRET').closest('div[class^="form-group"]').css('display', 'block');
    $('#VIVAWALLET_SMART_CHECKOUT_DEMO_SOURCE').closest('div[class^="form-group"]').css('display', 'block');
    $('#VIVAWALLET_SMART_CHECKOUT_LIVE_CLIENT_ID').closest('div[class^="form-group"]').css('display', 'none');
    $('#VIVAWALLET_SMART_CHECKOUT_LIVE_CLIENT_SECRET').closest('div[class^="form-group"]').css('display', 'none');
    $('#VIVAWALLET_SMART_CHECKOUT_LIVE_SOURCE').closest('div[class^="form-group"]').css('display', 'none');
}

function vivawalletsmartcheckout_showLiveCredentialInputs() {
    $('#VIVAWALLET_SMART_CHECKOUT_DEMO_CLIENT_ID').closest('div[class^="form-group"]').css('display', 'none');
    $('#VIVAWALLET_SMART_CHECKOUT_DEMO_CLIENT_SECRET').closest('div[class^="form-group"]').css('display', 'none');
    $('#VIVAWALLET_SMART_CHECKOUT_DEMO_SOURCE').closest('div[class^="form-group"]').css('display', 'none');
    $('#VIVAWALLET_SMART_CHECKOUT_LIVE_CLIENT_ID').closest('div[class^="form-group"]').css('display', 'block');
    $('#VIVAWALLET_SMART_CHECKOUT_LIVE_CLIENT_SECRET').closest('div[class^="form-group"]').css('display', 'block');
    $('#VIVAWALLET_SMART_CHECKOUT_LIVE_SOURCE').closest('div[class^="form-group"]').css('display', 'block');
}

