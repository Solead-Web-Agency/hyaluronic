/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['fspickupatstore'] = function () {
    if (
      $('.fspas-information:visible').length > 0 && $('.fspas-information').text().indexOf('-') > 3 && $('.fspas-information').text().indexOf('-') < 15
    ) {
        var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
        shippingErrorMsg.text(shippingErrorMsg.text() + ' (изберете офис)');
        shippingErrorMsg.show();
        scrollToElement(shippingErrorMsg);
        return false;
    } else {
        return true;
    }
}

checkoutShippingParser.fspickupatstore = {
  init_once: function (elements) {

  },

  delivery_option: function (element) {

  },

  extra_content: function (element) {
      $.getScript(tcModuleBaseUrl + '/../fspickupatstore/views/js/carrier-extra.js');
  }

}
