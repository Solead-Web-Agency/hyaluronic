/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['pshugls'] = function() { 
  if (
    $('.carrier-extra-content.pshugls:visible').is(':visible') &&
    $('.delivery-option.pshugls.selected').length === 1 &&
     (
      $('.delivery-option.pshugls.selected ~ .carrier-extra-content.pshugls').find('#gls-map-success-parcel-shop:visible').length === 0 &&
      $('.delivery-option.pshugls.selected ~ .carrier-extra-content.pshugls').find('#gls-map-success-parcel-locker:visible').length === 0
     )
    ) {
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    shippingErrorMsg.find('.err-pshugls').remove();
    shippingErrorMsg.append('<span class="err-pshugls"> (pickup point)</span>');
    shippingErrorMsg.show();
    if (typeof findStepByBlockName === 'function' && typeof setHash === 'function') {
      var switchToStep = findStepByBlockName('#thecheckout-shipping');
      if (switchToStep > 0) {
        setHash(switchToStep);
      }
    }
    scrollToElement(shippingErrorMsg);
    return false; 
  } else {
    return true;
  }
}