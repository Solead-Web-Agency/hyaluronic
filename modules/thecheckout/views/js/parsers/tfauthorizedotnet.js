/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 18.12.2024 with Authorize.Net v7.2.4 - by presta_world */

checkoutPaymentParser.tfauthorizedotnet = {

    after_load_callback: function() {
        $.getScript(tcModuleBaseUrl + '/../tfauthorizedotnet/views/js/front/DatPayment.js');
    }

}