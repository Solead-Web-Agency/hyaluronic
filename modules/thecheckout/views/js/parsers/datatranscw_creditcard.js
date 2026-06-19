/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* last tested 28.2.2024 with v4.0.193 - by customweb ltd */

checkoutPaymentParser.datatranscw_all = {

  after_load_callback: function() {
      $.getScript(tcModuleBaseUrl+'/../datatranscw/js/frontend.js')
      .done(
        function() { 
          setTimeout(function() { $('input[name=payment-option]:checked').click(); }, 500); 
        });
  },


  form: function (element) {

  },

  additionalInformation: function (element) {

  }

}

// One call even for multiple datatranscw payment modules is OK - events are attached for all of them at once
checkoutPaymentParser.datatranscw_creditcard = checkoutPaymentParser.datatranscw_all;

