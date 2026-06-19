/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Last tested on 04.04.2024 with Floapay v1.2.4 by 202 ecommerce
 */

checkoutPaymentParser.floapay = {

    after_load_callback: function() {
        if (typeof floaDisplayPaymentSummary === 'function') {
            floaDisplayPaymentSummary();
        }
        if (typeof floaDisplayPaymentReportDateField === 'function') {
            floaDisplayPaymentReportDateField();
        }
        if (typeof setFloaEvents === 'function') {
            setFloaEvents();
        }
    },

    form: function (element) {

        let form = element.find('.payment-form');
        if (typeof form !== 'undefined' && form?.attr('action')?.search('floapay/eligibility'))
        {
            let onSubmitAction = 'return floaLaunchEligibility($("#confirm_order"));';
            form.attr('action', 'javascript:void(0);');
            form.attr('onsubmit', onSubmitAction);
        }


    }
}