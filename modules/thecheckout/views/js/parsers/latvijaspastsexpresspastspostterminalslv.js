/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['latvijaspastsexpresspastspostterminalslv'] = function() { 
  if (
    $('.latvijaspasts_carrier.terminals').is(':visible') && 
    $('[name=terminals]')?.val() == '0'
    ) {
    var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
    shippingErrorMsg.text(shippingErrorMsg.text() + ' (piegādes punkts)');
    shippingErrorMsg.show();
    scrollToElement(shippingErrorMsg);
    return false; 
  } else {
    return true;
  }
}

checkoutShippingParser.latvijaspastsexpresspastspostterminalslv = {

  after_load_callback: function(deliveryOptionIds) {
    $.getScript(tcModuleBaseUrl + '/../latvijaspasts/views/js/carrier_script.js');
    // Add CSS rule
    var cssEl = document.createElement('style'),sheet;
    document.head.appendChild(cssEl);
    cssEl.sheet.insertRule(`
        .delivery-option.latvijaspastsexpresspastspostterminalslv > label {
            flex-wrap: wrap;
        }
    `);
  },

  init_once: function (elements) {
    
  },

  delivery_option: function (element) {
    
  },

  extra_content: function (element) {
    
  }

}