/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 11.8.2023 with apaczka 1.1.0 by Apaczka.pl */

tc_confirmOrderValidations['apaczka'] = function() {
  if (
      /* $('.delivery-option.apaczka input[type=radio]').is(':checked') && */
      typeof apaczkaCarriers === 'object' &&
      $('.apaczka-no-point').is(':visible')
  ) {
    // Legacy selector, replace from Thecheckout v3.3.8+ with version below
    // var shippingErrorMsg = $('#thecheckout-shipping > .inner-area > .error-msg');
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    shippingErrorMsg.text('Wybierz punkt dostawy');
    shippingErrorMsg.show();
    scrollToElement(shippingErrorMsg);
    return false;
  } else {
    return true;
  }
}

checkoutShippingParser.apaczka = {

  after_load_callback: function(deliveryOptionIds) {
    if (typeof apaczkaCarriers === 'object') {
      // Apaczka would init map selector on window.load()
      dispatchEvent(new Event('load'));
    }
  },

  init_once: function (elements) {
    if (debug_js_controller) {
      console.info('[thecheckout-apaczka.js] init_once()');
    }
  },

  delivery_option: function (element) {
    if (debug_js_controller) {
      console.info('[thecheckout-apaczka.js] delivery_option()');
    }
  },

  extra_content: function (element) {
  }

}