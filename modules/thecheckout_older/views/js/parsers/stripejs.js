/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

checkoutPaymentParser.stripejs = {

    after_load_callback: function() {
        // console.info('[stripejs parser] after load callback');
       $.getScript(tcModuleBaseUrl + '/../stripejs/views/js/stripe-prestashop.js');
    }

}
