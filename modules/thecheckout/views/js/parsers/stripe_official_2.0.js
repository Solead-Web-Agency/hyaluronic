/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* popup mode last tested on 01.11.2024 with stripe_official v3.4.0 */
/* (!) Set 'Payment Form Position' to 'With the Prestashop payment methods' in Stripe configuration
/* redirect mode last tested on 12.12.2023 with stripe_official v3.1.3 (removed JS redirect, it's supported now natively in stripe_official module through config option) */


checkoutPaymentParser.stripe_official_redirect = {

    all_hooks_content: function (content) {

    },

    container: function (element) {

    },

    form: function (element) {

    }

}


checkoutPaymentParser.stripe_official_popup = {

    popup_onopen_callback: function () {
        checkoutPaymentParser.stripe_official_popup.initPayment();
    },

    all_hooks_content: function (content) {

    },

    initPayment: function() {
        if (($('#stripe-card-element').length && !$('#stripe-card-element.StripeElement').length) ||
            ($('#stripe-card-number').length && !$('#stripe-card-number.StripeElement').length) ||
            ($('#js-stripe-payment-element').length && !$('#js-stripe-payment-element.StripeElement').length)) {
            // $.getScript(tcModuleBaseUrl+'/../stripe_official/views/js/checkout.js');

            var script = document.createElement('script');
            script.src = tcModuleBaseUrl+'/../stripe_official/views/js/checkout.js';

            // If we unselect payment option, checkout.js would click it by its own when loaded - it would click 1st option, I guess they
            // somehow made sure they're always at first position; but, it is necessary only when there are multiple payment methods!
            if (document.querySelectorAll('input[name="payment-option"]').length > 1) {
                document.querySelector('input[name="payment-option"]:checked').checked = false;
            }

            script.addEventListener('load', function() {
                setTimeout(function() {
                    // If #js-stripe-payment-form is located outside of payment-form (this can be set in Stripe options - 'Payment Form Position'
                    // This is only workaround, if preferably set 'Payment Form Position' to 'With the Prestashop payment methods'
                    const stripeForm = $('.payment-options > #js-stripe-payment-form');
                    if (stripeForm.length) {
                        $('.popup-payment-content[data-payment-module=stripe_official] .js-payment-option-form').append(stripeForm);
                    }
                },1000);
                // console.log(' -- checkout.js loaded, now lets dispatch change event to payment option');
                // var radioButton = document.querySelector('input[name="payment-option"]:checked');
                // if (radioButton) {
                //     radioButton.dispatchEvent(new Event('change'));
                //     console.log('dispatched change event to payment option', radioButton);
                // }
            });

            document.head.appendChild(script);

        }
    },

    container: function(element) {

        var stripe_base_url = '';
        if ('undefined' !== typeof prestashop && 'undefined' !== prestashop.urls && 'undefined' !== prestashop.urls.base_url) {
            stripe_base_url = prestashop.urls.base_url;
        }

        element.find('label').append('<img alt="" src="' + stripe_base_url + '/modules/stripe_official/views/img/logo-payment.png">');

        // Create additional information block, informing user that payment will be processed after confirmation
        var paymentOptionId = element.attr('id').match(/payment-option-\d+/);

        if (paymentOptionId && 'undefined' !== typeof paymentOptionId[0]) {
            paymentOptionId = paymentOptionId[0];
            element.after('<div id="'+paymentOptionId+'-additional-information" class="stripe_official popup-notice js-additional-information definition-list additional-information ps-hidden" style="display: none;"><section><p>'+i18_popupPaymentNotice+'</p></section></div>')
        }

        payment.setPopupPaymentType(element);

        var cssEl = document.createElement('style'),sheet;
        document.head.appendChild(cssEl);
        cssEl.sheet.insertRule(`
            .popup-payment-content[data-payment-module=stripe_official] .js-payment-option-form {
                display: block!important;
            }
        `);

    },

    form: function (element, triggerElementName) {

        // if (!payment.isConfirmationTrigger(triggerElementName)) {
        //     //  Integrated payment form
        //     if (debug_js_controller) {
        //         console.info('[stripe_official parser] Not confirmation trigger, removing payment form');
        //     }
        //     element.remove();
        // } else {
        //     // empty
        // }

        return;
    }

}

// Default Stripe parser
if (typeof stripe_payment_elements_enabled !== "undefined" && stripe_payment_elements_enabled === "1") {
    checkoutPaymentParser.stripe_official = checkoutPaymentParser.stripe_official_popup;
} else {
    checkoutPaymentParser.stripe_official = checkoutPaymentParser.stripe_official_redirect;
}
