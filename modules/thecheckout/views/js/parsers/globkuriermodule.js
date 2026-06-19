/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 10.07.2024 with globkuriermodule v3.0.1 by GlobKurier.pl */

// let globKurierModuleLoaded = false;

checkoutShippingParser.globkuriermodule = {

  after_load_callback: function(deliveryOptionIds) {
    if (typeof inpost_carrier_id !== 'undefined') {
      $.getScript(tcModuleBaseUrl+'/../globkuriermodule/views/js/inpost-front-17.js');
      // globKurierModuleLoaded = true;
    }
  },

  init_once: function (elements) {
   
  },

  delivery_option: function (element) {
   
  },

  extra_content: function (element) {
  }

}