/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Tested on 15.1.2025 with nkmgls v3.3.0 - by Nukium */

tc_confirmOrderValidations['nkmgls'] = function() { 
  if (
    $('.nkmgls.carrier-extra-content-gls').is(':visible') &&
    $('.delivery-option.nkmgls.selected').length === 1 &&
     (
      $('.nkmgls.carrier-extra-content-gls').find('#gls-relay-container:visible').length === 1 &&
      $('.nkmgls.carrier-extra-content-gls').find('input[name=gls_relay]:checked').length === 0
     )
    ) {
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    shippingErrorMsg.find('.err-nkmgls').remove();
    shippingErrorMsg.append('<span class="err-nkmgls"> (pickup point)</span>');
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

checkoutShippingParser.nkmgls = {

  after_load_callback: function(deliveryOptionIds) {
    $.getScript(tcModuleBaseUrl+'/../nkmgls/views/js/front.js');
  }
}