/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

checkoutPaymentParser.buckaroo3 = {
  all_hooks_content: function (content) {

  },

  container: function (element) {
    var paymentOption = element.attr('id').match(/payment-option-\d+/)[0];
    var feeEl = element.find('label span.h6');
    if ('undefined' !== typeof feeEl && feeEl) {
      // parse out right-most number, in left side text, there can be number in 'B2B'
      var feeNumber = feeEl.text().replace(/^.*?([\d,.]+)[€ ]*$/,'$1');
    	var fee = parseFloat(feeNumber.replace(',','.'));
    	element.last().append('<div class="payment-option-fee hidden" id="'+paymentOption+'-fee">'+fee+'</div>');
    }
  }
}