/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

 /*
    Fix necessary - modules/braintreeofficial/views/js/payment_bt.js; since braintreeofficial 1.1.2 and checkout module 3.2.6, separate payment page is enforced for Braintree
 */

checkoutPaymentParser.braintreeofficial = {


    init_once: function (content, triggerElementName) {

    },

    container: function (element) {

    },

    all_hooks_content: function (content) {

    },

    form: function (element) {

    },

    on_ready: function () {
        setTimeout(function () {
            // Load only when braintree hosted fields are not initialized yet
            if (!$('.braintree-card #card-number iframe').length) {
                $.getScript(tcModuleBaseUrl + '/../braintreeofficial/views/js/payment_bt.js');
            }
            //console.info("$.getScript(tcModuleBaseUrl + '/../braintreeofficial/views/js/payment_bt.js')");
        }, 300)
    },

    additionalInformation: function (element) {

        var paymentOptionForm = element;
        var staticContentContainer = $('#thecheckout-payment .static-content');


        if (!staticContentContainer.find('.braintree-payment-form').length) {
            $('<div class="braintree-payment-form"></div>').appendTo(staticContentContainer);
            paymentOptionForm.clone().appendTo(staticContentContainer.find('.braintree-payment-form'));
        }

        paymentOptionForm.find('*').remove();

        // Update ID of fixed form, so that it's displayed/hidden automatically with payment method selection
        var origId = paymentOptionForm.attr('id');
        staticContentContainer.find('.braintree-payment-form .js-additional-information').attr('id', origId);

        // Remove tag ID and class from original form
        paymentOptionForm.attr('id', 'braintree-form-original-container');
        paymentOptionForm.removeClass('js-additional-information');


        var additional_script_tag = " \
                <script> \
                $(document).ready( \
                    checkoutPaymentParser.braintreeofficial.on_ready \
                ); \
                </script> \
            ";


        element.append(additional_script_tag);

    }

}