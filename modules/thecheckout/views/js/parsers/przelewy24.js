/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/* Payment is done in popup, because since the Blik input initialization, we have a limited time until the order must be finished, so we'd rather init in the popup */

checkoutPaymentParser.przelewy24 = {
    container: function(element) {
        const isBlikPaymentOption = element.find('[data-module-name=przelewy24-method-181]').length == 1;

        if (isBlikPaymentOption) {
            payment.setPopupPaymentType(element);
            element.find('input[name=payment-option]').addClass('binary'); // so that our 'pay' button disappears
        }

        // Add CSS rule to hide blik form in payment methods list when T&C checkbox is ticked;
        // but keep it visible in the popup
        var cssEl = document.createElement('style'),sheet;
        document.head.appendChild(cssEl);
        cssEl.sheet.insertRule(`
            section#checkout-payment-step #p24-blik-section {
              display: none!important;
            }
        `);
    },

    popup_onopen_callback: function () {
        $.getScript(tcModuleBaseUrl+'/../przelewy24/views/js/p24_inside.js')
            .done(
                function() {
                    setTimeout(function() {
                        $('[name=payment-option]:checked').change(); // invoke blik code input initialization
                        setTimeout(function() {
                            const paymentFormEl = $('.popup-payment-form').find('.js-additional-information');
                            $('#p24-blik-section').prependTo(paymentFormEl);
                        }, 200);
                    }, 300);
                });
    },

    form: function (element) {

    },
}

 