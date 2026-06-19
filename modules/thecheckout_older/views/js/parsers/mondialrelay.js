/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['mondialrelay'] = function() {
  if (
      /*$('#mondialrelay_widget').is(':visible')*/
      $('.delivery-option.mondialrelay input[type=radio]').is(':checked') &&
      !$('#mondialrelay_summary').is(':visible')
  ) {
    var shippingErrorMsg = $('#thecheckout-shipping > .inner-area > .error-msg');
    shippingErrorMsg.text(shippingErrorMsg.text() + ' (Mondial relay)');
    shippingErrorMsg.show();
    scrollToElement(shippingErrorMsg);
    return false;
  } else {
    return true;
  }
}

checkoutShippingParser.mondialrelay = {
  init_once: function (elements) {
    if (debug_js_controller) {
      console.info('[thecheckout-mondialrelay.js] init_once()');
    }
  },

  delivery_option: function (element) {
    if (debug_js_controller) {
      console.info('[thecheckout-mondialrelay.js] delivery_option()');
    }

    // Uncheck mondialrelay item, so that it can be manually selected
    //element.after("<script>$('.delivery-option.mondialrelay input[name^=delivery_option]').prop('checked', false)</script>");
    // Mondial v3.0+ by 202 ecommerce
    element.append("<script>$(document).ready(setTimeout(function(){$('#js-delivery').find('[name^=\"delivery_option\"]:checked').trigger('change');},500)); prestashop.emit(\"updatedDeliveryForm\",{dataForm:$('#js-delivery').serializeArray(),deliveryOption:$('#js-delivery').find('[name^=\"delivery_option\"]:checked')});</script>");
  },

  extra_content: function (element) {
  }

}
