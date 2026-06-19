/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['lpshipping'] = function() {
    if (
        $('#lpshipping_express_terminal').is(':visible') &&
        ( "" == $('#lpshipping_express_terminal').val() || '-1' === $('#lpshipping_express_terminal').val())
    ) {
        var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
        shippingErrorMsg.show();
        scrollToElement(shippingErrorMsg);
        return false;
    } else {
        return true;
    }
}