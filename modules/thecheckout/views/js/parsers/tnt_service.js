/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Tested on 05.05.2025 with TNT Service Italia v1.0.0 - by Tembo Communication Design */


tc_confirmOrderValidations['tnt_service'] = function() {
  if (
    $('#hook-display-after-carrier #tntbox').is(':visible') && 
    $('#hook-display-after-carrier #tntbox #selected_point').length == 0
  ) {
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    $('.shipping-validation-details').remove();
    shippingErrorMsg.append('<span class="shipping-validation-details"> (Pickup point)</span>')
    shippingErrorMsg.show();
    scrollToElement(shippingErrorMsg);
    return false;
  } else {
    return true;
  }
}


checkoutShippingParser.tnt_service = {

  after_load_callback: function (deliveryOptionIds) {
    $.getScript(tcModuleBaseUrl + '/../tnt_service/views/js/TntFrontController.js', function() {
        tntDocumentReady();
    });
  },

  init_once: function (elements) {
      // Add CSS rule to hide payment form in payment methods list
      var cssEl = document.createElement('style'),sheet;
      document.head.appendChild(cssEl);
      cssEl.sheet.insertRule(`
          .dynamic-content .tnt-error.alert {
              display: none;
          }
      `);
  },

  delivery_option: function (element) {

  },

  extra_content: function (element) {
    
  }

}