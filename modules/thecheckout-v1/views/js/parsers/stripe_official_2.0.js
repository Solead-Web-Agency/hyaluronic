/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* popup mode last tested on 19.5.2023 with stripe_official v3.0.4 */


checkoutPaymentParser.stripe_official_redirect = {

    all_hooks_content: function (content) {

    },

    container: function (element) {

    },

    redirectToStripe: async function() {
        if (typeof stripe_create_checkout_url !== 'undefined' && stripe_create_checkout_url != '') {
            const checkout = await fetch(stripe_create_checkout_url, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify(),
            }).then((r) => r.json());
            window.location = checkout.checkout.url;
        } else {
            console.warn('stripe_create_checkout_url not set!')
        }

    },

    form: function (element) {
        //  Redirect to Stripe
        let form = element.find('form');
        let onSubmitAction = 'javascript:checkoutPaymentParser.stripe_official_redirect.redirectToStripe()';
        form.attr('action', 'javascript:void(0);');
        form.attr('onsubmit', onSubmitAction);
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
            // var stripe_base_url = '';
            // if ('undefined' !== typeof prestashop && 'undefined' !== prestashop.urls && 'undefined' !== prestashop.urls.base_url) {
            //     stripe_base_url = prestashop.urls.base_url;
            // }
            $.getScript(tcModuleBaseUrl+'/../stripe_official/views/js/checkout.js');
            // $.getScript(stripe_base_url + '/modules/stripe_official/views/js/payments.js');
        }
    },

    container: function(element) {

        var stripe_base_url = '';
        if ('undefined' !== typeof prestashop && 'undefined' !== prestashop.urls && 'undefined' !== prestashop.urls.base_url) {
            stripe_base_url = prestashop.urls.base_url;
        }

        element.find('label').append('<img src="' + stripe_base_url + '/modules/stripe_official/views/img/logo-payment.png">');

        // Create additional information block, informing user that payment will be processed after confirmation
        var paymentOptionId = element.attr('id').match(/payment-option-\d+/);

        if (paymentOptionId && 'undefined' !== typeof paymentOptionId[0]) {
            paymentOptionId = paymentOptionId[0];
            element.after('<div id="'+paymentOptionId+'-additional-information" class="stripe_official popup-notice js-additional-information definition-list additional-information ps-hidden" style="display: none;"><section><p>'+i18_popupPaymentNotice+'</p></section></div>')
        }

        payment.setPopupPaymentType(element);

    },

    form: function (element, triggerElementName) {

        if (!payment.isConfirmationTrigger(triggerElementName)) {
            //  Integrated payment form
            if (debug_js_controller) {
                console.info('[stripe_official parser] Not confirmation trigger, removing payment form');
            }
            element.remove();
        } else {
            // empty
        }

        return;
    }

}

// Default Stripe parser
if (typeof stripe_payment_elements_enabled !== "undefined" && stripe_payment_elements_enabled === "1") {
    checkoutPaymentParser.stripe_official = checkoutPaymentParser.stripe_official_popup;
} else {
    checkoutPaymentParser.stripe_official = checkoutPaymentParser.stripe_official_redirect;
}
