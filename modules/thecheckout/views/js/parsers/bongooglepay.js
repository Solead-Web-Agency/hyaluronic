/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* tested with Google Pay v2.0.0 - by Bonpresta - on 8.4.2024 */
checkoutPaymentParser.bongooglepay = {

    container: function (element) {
        payment.setPopupPaymentType(element);
        element.find('input[name=payment-option]').addClass('binary'); // so that our 'pay' button in the popup disappears

        // Add CSS rule to hide payment form in payment methods list
        var cssEl = document.createElement('style'),sheet;
        document.head.appendChild(cssEl);
        cssEl.sheet.insertRule(`
            .js-additional-information.bongooglepay > .bon-googlepay-required {
              display: none;
            }
        `);
    },

    popup_onopen_callback: function () {
        if ($('#bon-google-checkout').length) {
            var script = document.createElement('script');
            script.src = tcModuleBaseUrl+'/../bongooglepay/views/js/bongooglepay-front.js';
            document.head.appendChild(script);
        }
    },

}