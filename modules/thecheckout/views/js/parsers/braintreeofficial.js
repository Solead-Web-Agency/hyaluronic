/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

 /*
    16.06.2025: braintreeofficial v1.2.8 by 202 ecommerce, working in popup mode
 */

checkoutPaymentParser.braintreeofficial = {

    init_once: function(elements) {
        // Change original .additional-information block to js-payment-option-form
        const additionalInfoEl = elements.find('.additional-information');
        if (additionalInfoEl.length === 0) {
            return;
        }
        const braintreeRow = additionalInfoEl.find('.braintree-row-payment');

        // there will be 2 forms in #pay-with-payment-option-X-form and PS' core.js would just call at confirmation time 
        //   t()(`#pay-with-${e}-form form`).submit() - i.e. all forms inside, while we need only the first one; so let's reset 'action' on the second one.
        braintreeRow.find('form')?.attr('action', 'javascript:void(0);'); 
        const paymentConfirmationForm = elements.find('.js-payment-option-form');

        if (braintreeRow.length > 0 && paymentConfirmationForm.length > 0) {
            paymentConfirmationForm.append(braintreeRow);
        }
    },

    popup_onopen_callback: function () {
        checkoutPaymentParser.braintreeofficial.initPayment();
    },

    initPayment: function() {
        setTimeout(function () {
            // Load only when braintree hosted fields are not initialized yet
            if (!$('.braintree-card #card-number iframe').length) {
                $.getScript(tcModuleBaseUrl + '/../braintreeofficial/views/js/payment_bt.js');
            }
            //console.info("$.getScript(tcModuleBaseUrl + '/../braintreeofficial/views/js/payment_bt.js')");
        }, 300)
    },

    container: function(element) {

        // Create additional information block, informing user that payment will be processed after confirmation
        var paymentOptionId = element.attr('id').match(/payment-option-\d+/);

        if (paymentOptionId && 'undefined' !== typeof paymentOptionId[0]) {
            paymentOptionId = paymentOptionId[0];
            element.after('<div id="'+paymentOptionId+'-additional-information" class="braintreeofficial popup-notice js-additional-information definition-list additional-information ps-hidden" style="display: none;"><section><p>'+i18_popupPaymentNotice+'</p></section></div>')
        }

        payment.setPopupPaymentType(element);

    },

    form: function (element, triggerElementName) {

        if (!payment.isConfirmationTrigger(triggerElementName)) {
            if (debug_js_controller) {
                console.info('[braintreeofficial parser] Not confirmation trigger, removing payment form');
            }
            element.remove();
        } else {
            // intentionally empty
        }

        let form = element.find('.payment-form');
        if (typeof form !== 'undefined' && form?.attr('action')?.search('BraintreeSubmitPayment'))
        {
            let onSubmitAction = 'return BraintreeSubmitPayment();';
            form.attr('action', 'javascript:void(0);');
            form.attr('onsubmit', onSubmitAction);
        }

        return;
    }



    // additionalInformation: function (element) {

    //     var paymentOptionForm = element;
    //     var staticContentContainer = $('#thecheckout-payment .static-content');


    //     if (!staticContentContainer.find('.braintree-payment-form').length) {
    //         $('<div class="braintree-payment-form"></div>').appendTo(staticContentContainer);
    //         paymentOptionForm.clone().appendTo(staticContentContainer.find('.braintree-payment-form'));
    //     }

    //     paymentOptionForm.find('*').remove();

    //     // Update ID of fixed form, so that it's displayed/hidden automatically with payment method selection
    //     var origId = paymentOptionForm.attr('id');
    //     staticContentContainer.find('.braintree-payment-form .js-additional-information').attr('id', origId);

    //     // Remove tag ID and class from original form
    //     paymentOptionForm.attr('id', 'braintree-form-original-container');
    //     paymentOptionForm.removeClass('js-additional-information');


    //     var additional_script_tag = " \
    //             <script> \
    //             $(document).ready( \
    //                 checkoutPaymentParser.braintreeofficial.on_ready \
    //             ); \
    //             </script> \
    //         ";


    //     element.append(additional_script_tag);

    // }

}