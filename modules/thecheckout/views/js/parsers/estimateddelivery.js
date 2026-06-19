/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

checkoutShippingParser.estimateddelivery= {
    init_once: function (elements) {
        // This variable is handled from estimateddelivery module
        if (typeof ed_refresh !== 'undefined' && dynamic_refresh === false)
        {
            dynamic_refresh = true;
        }
    }
}