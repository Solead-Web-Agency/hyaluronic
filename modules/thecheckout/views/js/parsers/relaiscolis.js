/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Tested on 14.12.2024 with relaiscolis v3.0.7 by Calliweb */

checkoutShippingParser.relaiscolis = {
  init_once: function (elements) {
    if (debug_js_controller) {
      console.info('[thecheckout-relaiscolis.js] init_once()');
    }
  },

  on_ready: function() {
    $.getScript(tcModuleBaseUrl+'/../relaiscolis/views/js/front.js');
    setTimeout(function(){
      if(typeof url_img !== 'undefined' && url_img) {
          url_img = url_img + 'views/img/';
           lgRelaisColisOuvert = url_img + "pointer_on.png";
           lgRelaisColisMaxOuvert = url_img + "pointer_max_on.png";
           lgRelaisColisFerme = url_img + "pointer_off.png";
           lgRelaisColisMaxFerme = url_img + "pointer_max_off.png";
           lgRelaisColisNew = url_img + "pointer_new_on.png";
           lgVousEtesIci = url_img + "VousEtesIci.gif";
           lgTransp = url_img + "transp.gif";
      }
    }, 300);
    },

  delivery_option: function (element) {
    if (debug_js_controller) {
      console.info('[thecheckout-relaiscolis.js] delivery_option()');
    }

    var additional_script_tag = " \
                <script> \
                $(document).ready( \
                    checkoutShippingParser.relaiscolis.on_ready \
                ); \
                </script> \
            ";


        element.append(additional_script_tag); 
  },

  extra_content: function (element) {
  }

}
