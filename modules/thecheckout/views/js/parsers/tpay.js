/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['tpay'] = function () {
    if (
        $('ul.tpay-payment-gateways:visible').length &&
        $('[name=tpay_transfer_id]:checked').length == 0
    ) {
        var paymentErrorMsg = $('#thecheckout-payment .dynamic-content > .inner-wrapper > .error-msg');
        $('.payment-validation-details').remove();
        paymentErrorMsg.append('<span class="payment-validation-details"> (Tpay channel)</span>')
        paymentErrorMsg.show();
        scrollToElement(paymentErrorMsg);
        return false;
    } else {
        return true;
    }
}

checkoutPaymentParser.tpay = {
    after_load_callback: function() {
        $.getScript(tcModuleBaseUrl+'/../tpay/views/js/main.min.js')
            .done(
                function() {
                    console.log('tpay loaded');
                });
    },

    form: function (element) {
        // intentionally empty
    },
}