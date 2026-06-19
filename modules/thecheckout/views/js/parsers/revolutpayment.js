/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* popup mode last tested on 15.4.2023 with revolutpayment v2.2.4 */
/* popup/direct(=embedded)/redirect modes tested on 1.5.2024 with revolutpayment v2.8.6 */
/* Update please \revolutpayment\views\js\version17\revolut.payment.js, method initCheckoutUpsellBanner(): - (!) with revolutpayment v2.8.14 this might not be necessary
    function initCheckoutUpsellBanner() {
        let upsellBannerElement = document.getElementById("revolut-upsell-banner");
        // Thecheckout module update begin
        if (upsellBannerElement === null) { return; }
        // Thecheckout module udpate end

    And for revolutpay button, also update stopLoading() method, like so:
    function stopLoading() {
        // Thecheckout module update BEGIN
        if (typeof $.unblockUI === 'function') {
          $.unblockUI();
        }
        // Thecheckout module update END
    }
 */

checkoutPaymentParser.revolutpayment = {

    popup_onopen_callback: function () {
        checkoutPaymentParser.revolutpayment.initPayment();
    },

    all_hooks_content: function (content) {

    },

    after_load_callback: function() {
        // Update card logos, by default only VISA is shown
        if (typeof logo_path === "undefined") {
            return;
        }

        let visa_logo = `<img class="visa-logo" src="${logo_path}visa-logo.svg"/>`;
        let mastercard_logo = `<img class="mastercard-logo"  src="${logo_path}master-card-logo.svg">`;
        let amex_logo = `<img class="amex-logo" style="display:none" src="${logo_path}amex.svg">`;

        $(`img[src="${logo_path}visa-logo.svg"] ~ .tc-rev-logos`).remove();
        $(`img[src="${logo_path}visa-logo.svg"]`).after(`${visa_logo}<span class="tc-rev-logos">${mastercard_logo}${amex_logo}</span>`).remove();
        $(`img[src="${revpay_logo}"]`).after(`${visa_logo}${mastercard_logo}${amex_logo}`).css({ width: "30px" });

        if (amex_availability) {
            $(".amex-logo").show();
        }
    },

    initPayment: function() {
        // revolut_card container present, but fields not yet initiated
        // if ( $('#revolut_card').length && !($('#revolut_card.rc-card-field').length)) {
        if (($('#revolut_card').length && !($('#revolut_card.rc-card-field').length)) || ($('#revolut_pay').length && $('#revolut_pay').children().length == 0)) {
            $.getScript(tcModuleBaseUrl+'/../revolutpayment/views/js/version17/revolut.payment.js');
        }
    },

    container: function(element) {
        // Create additional information block, informing user that payment will be processed after confirmation
        var paymentOptionId = element.attr('id').match(/payment-option-\d+/);

        let isRevolutPay = element.next().find('#revolutPayForm').length === 1;
        let isRevolutCard = element.next().find('#revolutPay').length === 1; // not used now

        if (isRevolutPay) {
            element.find('input[name=payment-option]').addClass('binary'); // so that our 'pay' button disappears
        }

        if (paymentOptionId && 'undefined' !== typeof paymentOptionId[0]) {
            paymentOptionId = paymentOptionId[0];
            element.after('<div id="'+paymentOptionId+'-additional-information" class="revolutpayment popup-notice js-additional-information definition-list additional-information ps-hidden" style="display: none;"><section><p>'+i18_popupPaymentNotice+'</p></section></div>')
        }

        payment.setPopupPaymentType(element);

        // Add CSS rule
        var cssEl = document.createElement('style'),sheet;
        document.head.appendChild(cssEl);
        cssEl.sheet.insertRule(`
            [data-payment-module=revolutpayment] .amex-logo ~ .tc-rev-logos {
                display: none;
            }
        `);
    },

    form: function (element, triggerElementName) {

        if (!payment.isConfirmationTrigger(triggerElementName)) {
            if (debug_js_controller) {
                console.info('[revolutpayment parser] Not confirmation trigger, removing payment form');
            }
            element.remove();
        } else {
            // Intentially empty
        }

        return;
    }

}

