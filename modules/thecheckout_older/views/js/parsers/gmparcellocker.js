/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['gmparcellocker'] = function () {
    if (
        $('.chosen-parcel:visible').length &&
        "---" == $('.chosen-parcel:visible').html()
    ) {
        var shippingErrorMsg = $('#thecheckout-shipping > .inner-area > .error-msg');
        shippingErrorMsg.text(shippingErrorMsg.text() + ' (InPost)');
        shippingErrorMsg.show();
        scrollToElement(shippingErrorMsg);
        return false;
    } else {
        return true;
    }
}