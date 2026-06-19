/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
  Modify please /modules/paynow/controllers/front/payment.php, remove lines:
       if ($id_customer != $this->context->customer->id) {
            Tools::redirect('index.php?controller=order&step=1');
       }
 */

tc_confirmOrderValidations['paynow'] = function () {
    if (
        $('.paynow-payment-option-container #paynow_blik_code').is(':visible') &&
        $('.paynow-payment-option-container #paynow_blik_code').val() == ''
    ) {
        var paymentErrorMsg = $('#thecheckout-payment .dynamic-content > .inner-wrapper > .error-msg');
        $('.payment-validation-details').remove();
        paymentErrorMsg.append('<span class="payment-validation-details"> (BLIK kod)</span>')
        paymentErrorMsg.show();
        scrollToElement(paymentErrorMsg);
        return false;
    } else {
        return true;
    }
}

checkoutPaymentParser.paynow = {

    container: function(element) {
        const isBlikPaymentOption = element.next('.js-payment-option-form').find('form.paynow-blik-form').length == 1

        if (isBlikPaymentOption) {
            payment.setPopupPaymentType(element);
        }

        // Add CSS rule to hide blik form in payment methods list when T&C checkbox is ticked;
        // but keep it visible in the popup
        var cssEl = document.createElement('style'),sheet;
        document.head.appendChild(cssEl);
        cssEl.sheet.insertRule(`
            section#checkout-payment-step .paynow-blik-form {
              display: none;
            }
        `);
    },

    after_load_callback: function() {
        $.getScript(tcModuleBaseUrl+'/../paynow/views/js/front.js')
            .done(
                function() {
                    setTimeout(function() {
                        if (typeof enableBlikSupport !== 'undefined') { enableBlikSupport(); }
                        if (typeof enablePblSupport !== 'undefined') { enablePblSupport(); }
                        if (typeof paynow !== 'undefined' && typeof paynow.config !== 'undefined') {
                            paynow.config.validateTerms = false; // because if T&C are disabled in PS settings, BLIK will not confirm
                        }
                    }, 300);
                });
        // if ("undefined" !== typeof enableBlikSupport) {
        //     setTimeout(300, function() { enableBlikSupport(); })
        // }
    },

    form: function (element) {
        // var blikPayButtonSelector = "form.paynow-blik-form .paynow-payment-option-container button.btn-primary";
        // if (element.find('.paynow-blik-form').length) {
        //     element.find('.payment-form').attr('onsubmit', '$("'+blikPayButtonSelector+'").click(); return false;');
        //     element.find(blikPayButtonSelector).prop('disabled', false);
        // }
        //
        // // Add CSS rule to hide blickPayButton
        // var element = document.createElement('style'),sheet;
        // document.head.appendChild(element);
        // element.sheet.insertRule(blikPayButtonSelector + '{ display: none; }');
    },
}

 