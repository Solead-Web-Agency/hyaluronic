/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 12.6.2025 with blpaczka 1.0.0 by BLPaczka */

tc_confirmOrderValidations['blpaczka'] = function() {
  if (
      $('#blpaczka_carrier_list_hook_container #blpaczka_map_open_btn:visible').length && 
      $('#blpaczka_pudo_code').length && 
      $('#blpaczka_pudo_code').val() === ''
  ) {
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    shippingErrorMsg.text('Wybierz punkt dostawy');
    shippingErrorMsg.show();
    scrollToElement(shippingErrorMsg);
    return false;
  } else {
    return true;
  }
}

checkoutShippingParser.blpaczka = {

  after_load_callback: function(deliveryOptionIds) {
  },

  init_once: function (elements) {
    if (debug_js_controller) {
      console.info('[thecheckout-blpaczka.js] init_once()');
    }
  },

  delivery_option: function (element) {
    if (debug_js_controller) {
      console.info('[thecheckout-blpaczka.js] delivery_option()');
    }
  },

  extra_content: function (element) {
  }

}