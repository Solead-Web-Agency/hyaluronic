/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['fspickupatstore'] = function () {
    if (
      $('.fspas-information:visible').length > 0 && $('.fspas-information:visible').text().indexOf('-') > 3 && $('.fspas-information:visible').text().indexOf('-') < 15
    ) {
        var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
        $('.shipping-validation-details').remove();
        shippingErrorMsg.append('<span class="shipping-validation-details"> (изберете офис)</span>')
        shippingErrorMsg.show();
        scrollToElement(shippingErrorMsg);
        return false;
    } else {
        return true;
    }
}

checkoutShippingParser.fspickupatstore = {
  after_load_callback: function(deliveryOptionIds) {
    $.getScript(tcModuleBaseUrl + '/../fspickupatstore/views/js/carrier-extra.js');
    // Add CSS rule
    // var cssEl = document.createElement('style'),sheet;
    // document.head.appendChild(cssEl);
    // cssEl.sheet.insertRule(`
    //     .delivery-option.latvijaspastsexpresspastspostterminalslv > label {
    //         flex-wrap: wrap;
    //     }
    // `);
  },

  init_once: function (elements) {

  },

  delivery_option: function (element) {

  },

  extra_content: function (element) {
  }

}
