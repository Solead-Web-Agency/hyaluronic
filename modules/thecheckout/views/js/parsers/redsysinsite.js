/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


checkoutPaymentParser.redsysinsite = {
    container: function(element) {
            payment.setPopupPaymentType(element);
            element.find('input[name=payment-option]').addClass('binary'); // so that our 'pay' button in the popup disappears

        // Add CSS rule to hide payment form in payment methods list
        // but keep it visible in the popup
        var cssEl = document.createElement('style'),sheet;
        document.head.appendChild(cssEl);
        cssEl.sheet.insertRule(`
            section#checkout-payment-step .redsysinsite_form_container [id^='redsysinsite_card_form_container'] {
              display: none!important;
            }
        `);
        cssEl.sheet.insertRule(`
            .popup-payment-form [id^=ri-messages] {
                display: none;
            }
        `);
    },

    popup_onopen_callback: function () {

    },


    form: function (element) {

    },
}

 