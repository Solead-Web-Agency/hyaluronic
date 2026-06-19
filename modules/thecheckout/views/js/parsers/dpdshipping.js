/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['dpdshipping'] = function() {
  // pickup point selection button visible, but no point selected
  if (
      $('.dpdshipping-pudo-open-map-btn:visible').length > 0 && $('.dpdshipping-pudo-selected-point .dpdshipping-selected-point').length > 0
  ) {
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    $('.shipping-validation-details').remove();
    shippingErrorMsg.append('<span class="shipping-validation-details"> (DPD - punkt odbioru)</span>')
    shippingErrorMsg.show();
    scrollToElement(shippingErrorMsg);
    return false;
  } else {
    return true;
  }
}

checkoutShippingParser.dpdshipping = {
  after_load_callback: function(deliveryOptionIds) {
    /* intentionally left empty */
  },
}
