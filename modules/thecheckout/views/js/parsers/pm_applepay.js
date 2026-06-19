/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

checkoutPaymentParser.pm_applepay = {
    popup_onopen_callback: function () {
        console.info('[pm_applepay] popup_onopen_callback');
        if (typeof (pmApplePayPlugin) !== 'undefined' && typeof (initPmApplePayEventsListeners) !== 'undefined') {
            initPmApplePayEventsListeners();
        }
    },

    after_load_callback: function() {
        $('.dynamic-content .pm-apple-pay-container').appendTo('#pm_applepay-popup-container');
    },

    container: function(element) {
        // // Create additional information block, informing user that payment will be processed after confirmation
        // var paymentOptionId = element.attr('id').match(/payment-option-\d+/);
        //
        // if (paymentOptionId && 'undefined' !== typeof paymentOptionId[0]) {
        //     paymentOptionId = paymentOptionId[0];
        //     element.after('<div id="'+paymentOptionId+'-additional-information" class="stripe_official popup-notice js-additional-information definition-list additional-information ps-hidden" style="display: none;"><section><p>'+i18_popupPaymentNotice+'</p></section></div>')
        // }

        payment.setPopupPaymentType(element);
    }
}