/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 7.8.2024 with payseradelivery v1.5.2 by Paysera */

checkoutShippingParser.payseradelivery = {

  after_load_callback: function(deliveryOptionIds) {
    $.getScript(tcModuleBaseUrl+'/../payseradelivery/views/js/frontend/terminal_selection.js');
  },

  init_once: function (elements) {
    // Add CSS rule
    var cssEl = document.createElement('style'),sheet;
    document.head.appendChild(cssEl);
    cssEl.sheet.insertRule(`
        div.paysera-delivery-gateway-content {
            margin: 15px 0;
        }
    `);
    cssEl.sheet.insertRule(`
        .delivery-options .row.delivery-option {
          flex-wrap: wrap;
          & > .shipping-radio {
              flex-basis: 50px;
          }
          & > label {
              flex-basis: calc(100% - 50px);
          }
      }
    `);
  },

  delivery_option: function (element) {
   
  },

  extra_content: function (element) {
  }

}