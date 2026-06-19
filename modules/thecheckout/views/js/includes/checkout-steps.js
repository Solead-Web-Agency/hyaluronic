/*
 * NOTICE OF LICENSE
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * @author    Peter Sliacky (Prestasmart)
 * @copyright Peter Sliacky (Prestasmart)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Copyright (c) 2021-2022
 */

var debug_steps = true
var tc_current_step = 1

function validateStep(stepId) {
    // $('.delivery-options input[name^=delivery_option]:checked').length
    // $('input[name="payment-option"]:checked').length
    if (debug_steps) {
        console.log('validateStep(' + stepId + ')');
    }
    if (typeof tc_steps !== 'undefined' && tc_steps) {
        var validationFunc = tc_steps.find(x => x.step === stepId)?.validation;
        if (debug_steps) {
            console.log('validationFunc is', validationFunc);
        }
        if (typeof validationFunc === 'function') {
            return validationFunc();
        }
    }

    return true
}

function _showStep(stepId, currentStep) {
    stepId = parseInt(stepId)

    if (currentStep !== String(stepId)) {
        // we're NOT on desired step, so let's validate and show errors
        if (!validateStep(stepId)) {
            var errorMessage = tc_steps.find(x => x.step === stepId)?.errorMsg?.replace(/&#039;/g, "'") || 'Error message not set in step validation.';
            // coreJquery.Toast(i18_validationError, errorMessage, "error", { has_progress: true, timeout: 3000 })
            Toastify({
                text: errorMessage,
                className: "steps-toast",
                close: true,
                duration: 3000,
                gravity: "bottom",
            }).showToast();
            // return one step back
            if (stepId > 1) {
                setHash(stepId - 1)
            }
            return;
        }

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    if (debug_steps) {
        console.log('showStep(' + stepId + ', ' + currentStep + ')');
    }

    $('.prev-next-container .step-back').attr('data-step', (stepId -1));
    $('.prev-next-container .step-continue').attr('data-step', (stepId + 1));

    $('.prev-next-container').removeClass('first-step').removeClass('last-step');

    var numberOfSteps = $('#checkout-step-btn-container > .checkout-step-btn').length;
    if (stepId == 1) {
        $('.prev-next-container').addClass('first-step');
    }
    if (stepId == numberOfSteps - (config_separate_payment ? 1 : 0)) {
        $('.prev-next-container').addClass('last-step');
    }
    if (stepId == numberOfSteps && config_separate_payment) {
        // If we're about to go to the last step and separate payments is enabled, redirect to 'p3i' page
        $('[data-link-action="x-confirm-order"]').trigger('click');
        return;
    }

    $('body#checkout').removeClass((function (index, className) {
        return (className.match (/(^|\s)checkout-step-\S+/g) || []).join(' ');
    })).addClass('checkout-step-'+stepId);

    // prestashop.on('thecheckout_showStep', function(data) { console.log('Current step: ', data.currentStep, ', New step: ', data.newStep); })
    prestashop.emit('thecheckout_showStep', { currentStep: tc_current_step, newStep: stepId });
    tc_current_step = stepId
}

function setHash(hash) {
    // User wishes to 'continue shopping'
    if (hash == 0) {
        window.location.href = document.referrer;
        return;
    }
    // Normalize current hash (remove leading #)
    const currentHash = window.location.hash.replace(/^#/, '');

    var base_url = window.location.href.replace(/#.*$/,'');
    window.history.pushState({}, "", base_url + '#' + hash);
    _showStep(hash, currentHash);
}

$(document).ready(function () {

    // Top navigation
    $('body').off('click.checkoutstep').on('click.checkoutstep', '.checkout-step-btn', function () {
        setHash($(this).attr('data-step-id'));
    });

    // Bottom buttons
    $('body').off('click.checkoutstepcontinue').on('click.checkoutstepcontinue', '.step-back, .step-continue', function () {
        setHash($(this).attr('data-step'));
    });

    if ("onhashchange" in window) {
        if (debug_steps) {
            console.info('register hashchange');
        }
        $(window).on('hashchange', function () {
            var hash = window.location.hash.replace(/^#/, '');
            // console.info('hashchange - ' + hash);
            //if (visible_step != hash) {
            if (!isNaN(hash)) _showStep(hash);
            //}
        });
    }

    var initStep = location.hash.substring(1);

    if (initStep == '') {
        setHash(1);
    } else {
        // window.location.hash = '';
        setHash(initStep);
    }

    setTimeout(function() { $('body').addClass('is-checkout-loader-hidden'); }, 200);

});

