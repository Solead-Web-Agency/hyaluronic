/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['ruch'] = function() {
  if (
      $('.delivery-option.ruch input[type=radio]').is(':checked') &&
      'undefined' !== typeof testPkt17() && !testPkt17()
  ) {
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    $('.shipping-validation-details').remove();
    shippingErrorMsg.append('<span class="shipping-validation-details"> (ORLEN Paczka)</span>')
    shippingErrorMsg.show();
    scrollToElement(shippingErrorMsg);
    return false;
  } else {
    return true;
  }
}

checkoutShippingParser.ruch = {

  display_ruch_button: function() {
    if (typeof ruch_display_map_as_popup !== 'undefined' && ruch_display_map_as_popup) {
      if ('undefined' !== typeof testRuchServ17_popup) {
        testRuchServ17_popup();
      }
    } else {
      if ('undefined' !== typeof testRuchServ17) {
        testRuchServ17();
      }
    }
  },

  after_load_callback: function(deliveryOptionIds) {
    if ('undefined' !== typeof ruch_widget_started || typeof ruch_selector_for_service !== 'undefined')
    {
      ruch_widget_started = false;
      checkoutShippingParser.ruch.display_ruch_button();

      $('.delivery-option input').on('click', function() {
        checkoutShippingParser.ruch.display_ruch_button();
      });
    }
  },
}
