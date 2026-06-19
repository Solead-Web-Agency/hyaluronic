/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 11.8.2024 with unisendshipping v2.0.1 - by UNISEND */

/*
  IMPORTANT:
  in unisendshipping.php, method hookActionValidateStepComplete, update (added // Thecheckout module support):

   public function hookActionValidateStepComplete($params, $isPs17 = true)
   {
       if ($isPs17 && $params['step_name'] !== 'delivery') {
           return true;
       }
       $cart = $params['cart'];

       // Thecheckout module support
       $address = new Address($cart->id_address_delivery);
       if (!$address->id_customer || !$address->postcode) {
           return true;
       }
       ...
*/

checkoutShippingParser.unisendshipping = {

  after_load_callback: function(deliveryOptionIds) {
    if (typeof UnisendShippingToken === 'undefined') {
      $.getScript(tcModuleBaseUrl+'/../unisendshipping/views/js/front.js');
    } else {
      // before calling all methods below, check if they exist
        if (typeof hideErrors === 'function') {
          hideErrors();
        }
        if (typeof registerListeners === 'function') {
          registerListeners();
        }
        if (isPs17 && typeof registerTerminalChangedListener === 'function') {
            registerTerminalChangedListener();
        }
    }
  },

  init_once: function (elements) {

  },

  delivery_option: function (element) {
   
  },

  extra_content: function (element) {
  }

}