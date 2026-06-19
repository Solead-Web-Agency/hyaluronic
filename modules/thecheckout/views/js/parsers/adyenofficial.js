/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 19.11.2024 with Adyen v5.2.2 - by Adyen */

checkoutPaymentParser.adyenofficial = {

    after_load_callback: function() {
        // empty; no script load necessary
    },

    popup_onopen_callback: function() {
        // On this change event, handler from /modules/adyenofficial/views/js/front/adyen-payment-selection.js is attached
        $('[name=payment-option]:checked').change();

        // Add CSS rule to show payment form in popup
        var element = document.createElement('style'),sheet;
        document.head.appendChild(element);
        element.sheet.insertRule('.popup-payment-form > .js-payment-option-form { display: block !important; }');
    },

    container: function(element) {

        // Create additional information block, informing user that payment will be processed after confirmation
        var paymentOptionId = element.attr('id').match(/payment-option-\d+/);

        if (paymentOptionId && 'undefined' !== typeof paymentOptionId[0]) {
            paymentOptionId = paymentOptionId[0];
            element.after('<div id="'+paymentOptionId+'-additional-information" class="adyenofficial popup-notice js-additional-information definition-list additional-information ps-hidden" style="display: none;"><section><p>'+i18_popupPaymentNotice+'</p></section></div>')
        }

        payment.setPopupPaymentType(element);
    }

}