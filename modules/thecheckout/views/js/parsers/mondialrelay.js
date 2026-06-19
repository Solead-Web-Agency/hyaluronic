/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 13.7.2023 with mondialrelay 3.3.7 by ScaleDEV */

tc_confirmOrderValidations['mondialrelay'] = function() {
  if (
      /*$('#mondialrelay_widget').is(':visible')*/
      $('.delivery-option.mondialrelay input[type=radio]').is(':checked') &&
      !$('#mondialrelay_summary').is(':visible')
  ) {
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    shippingErrorMsg.text('Veuillez choisir votre relais');
    shippingErrorMsg.show();
    scrollToElement(shippingErrorMsg);
    return false;
  } else {
    return true;
  }
}

checkoutShippingParser.mondialrelay = {

  after_load_callback: function(deliveryOptionIds) {
    // reinit only when widget is already initialized = that means, mondialrelay module won't try to init it again, but we have to.
    if (typeof mondialrelayWidget !== 'undefined' && mondialrelayWidget.initialized === true) {
      $.getScript(tcModuleBaseUrl + '/../mondialrelay/views/js/front/checkout/checkout-17.js');
      if (typeof MONDIALRELAY_ADDRESS_OPC !== 'undefined') {
        MONDIALRELAY_ADDRESS_OPC = true;
      }
    }
  },

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
    // element.append("<script>$(document).ready(setTimeout(function(){$('#js-delivery').find('[name^=\"delivery_option\"]:checked').trigger('change');},500)); prestashop.emit(\"updatedDeliveryForm\",{dataForm:$('#js-delivery').serializeArray(),deliveryOption:$('#js-delivery').find('[name^=\"delivery_option\"]:checked')});</script>");
  },

  extra_content: function (element) {
  }

}

// document.addEventListener('DOMContentLoaded', function(event) {
//   //jQuery shall be loaded now
//   $(document).ajaxComplete(function(e, xhr, settings) {
//     if (settings.url.match('module/mondialrelay/ajaxCheckout')) {
//       console.log('Mondial relay, pickup point change (probably) modified delivery address, reload...');
//       location.reload();
//     }
//   });
// });

