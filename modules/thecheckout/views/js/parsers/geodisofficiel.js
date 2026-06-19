/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 07.06.2024 with geodisofficiel v1.0.3 by GEODIS */

let geodisLoaded = false;

checkoutShippingParser.geodisofficiel = {

  after_load_callback: function(deliveryOptionIds) {
    if (!geodisLoaded && typeof GeodisCarrierSelector === 'function') {
      $.getScript(tcModuleBaseUrl+'/../geodisofficiel/views/js/lib/intlTel/utils.js');
      $.getScript(tcModuleBaseUrl+'/../geodisofficiel/views/js/GeodisCarrierSelectorBootstrap.js');
      geodisLoaded = true;
    }
  },

  init_once: function (elements) {
   
  },

  delivery_option: function (element) {
   
  },

  extra_content: function (element) {
  }

}