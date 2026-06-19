/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

checkoutPaymentParser.generic_iframe_parser = {

    popup_onopen_callback: function (optionId) {
        $('.popup-payment-content .popup-embed').prop('src', '?p3i&option='+optionId);
        $('.popup-payment-content').addClass('iframe');
    },

    popup_onclose_callback: function () {
        $('.popup-payment-content .popup-embed').prop('src', '');
        $('.popup-payment-content').removeClass('iframe');
    },

    container: function(element) {
        payment.setPopupPaymentType(element);
        element.find('input[name=payment-option]').addClass('binary');
    },

}