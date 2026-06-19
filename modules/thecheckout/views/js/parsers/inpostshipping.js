/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['inpostshipping'] = function () {
    if (
        $('.delivery-option.inpostshipping input[type=radio]').is(':checked') &&
        $('.js-inpost-shipping-machine-name').length > 0 &&
        $('.js-inpost-shipping-choose-machine:visible').length > 0 &&
        "" == jQuery.trim($('.js-inpost-shipping-machine-name').text())
    ) {
        var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
        $('.shipping-validation-details').remove();
        shippingErrorMsg.append('<span class="shipping-validation-details"> (Paczkomat)</span>')
        shippingErrorMsg.show();
        scrollToElement(shippingErrorMsg);
        return false;
    } else {
        return true;
    }
}

// Handle order-review block, copy there selected inpost point
if (!$('#thecheckout-order-review').hasClass('hidden')) {
    // Only if order-review block is not hidden (we cannot check visibility, as it can be in different step)
    prestashop.on('thecheckout_updateShippingBlock', function() {
        // console.log('carriers updated');
        $(document).off('click.tc').on('click.tc', '.js-inpost-shipping-choose-machine', function() {
            // console.log('clicked choose-machine');
            // inpostpointselected is removed and bind automatically on every modal window display
            $(document).on('inpostpointselected', function() {
                // console.log('point selected');
                // 400ms timeout seemed to be enough, make it 1000 just in case
                setTimeout(copyShippingToOrderReview, 1000);
            });
        });
    });
}


checkoutShippingParser.inpostshipping = {
   

}