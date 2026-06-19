/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

checkoutShippingParser.dpdfrance = {
  init_once: function (elements) {
    if (debug_js_controller) {
      console.info('[thecheckout-dpdfrance.js] init_once()');
    }
  },

  delivery_option: function (element) {
    if (debug_js_controller) {
      console.info('[thecheckout-dpdfrance.js] delivery_option()');
    }

    element.append("<script>$(document).ready(setTimeout(function(){ if ('function' === typeof dpdfrance_display) { $(\"input[name*='delivery_option[']\").change(function() { dpdfrance_display(); }); dpdfrance_display();} },200));</script>");
  },

  extra_content: function (element) {
  }

}