/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Zelarg)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* tested with imoje v3.6.0 - by imoje - on 12.04.2025, when embedded BLIK code is enabled ('Display code field to pay in store' ON) */
checkoutPaymentParser.imoje = {

    // after_load_callback: function() {
    // },

    init_blik_input: function () {
        (function () {

            // region vars
            var $blikCode = $("#blik_code"),
                $blikSubmit = $('#blik-submit'),
                $contentBlikCode = $('#content-blik-code'),
                $contentBlikOneclick = $('#content-blik-oneclick'),
                $modalProcessing = $('#modal-processing'),
                isSubmitted = false,
                $rememberCodeCheckbox = $('#remember-code-checkbox'),
                $aliasDeactivateButton = $('.alias-deactivate'),
                $blikProfile = $('.blik-alias'),
                $modalBlikAliasDeactivateModal = $('#modal-blik_alias_deactivate-modal'),
                $deactivateProfileNameText = $('#deactivate_profile_name-text'),
                $modalTip = $('#modal_tip'),
                $deactivateProfileAcceptButton = $('#deactivate_profile_accept-btn'),
                $textTip = $('#text-tip'),
                $modalBlik = $('.modal-blik'),
                dontProcessing = false,
                dontProcessingTimeout = null,
                $insertBlikCodeText = $('#insert_blik_code-text'),
                $modalBlikCode = $('#modal-blik_code'),
                $btnModalClose = $('.btn-modal-close'),
                $blikSubmitModal = $('#blik-submit_modal'),
                $blikCodeModal = $('#blik_code_modal'),
                $proceedPaymentCodeText = $('#proceed_payment_code'),
                timeoutAppendResultText = null,
                tempKey = null,
                profileId = null,
                tempThis = null,
                tempNewParamAlias = null,
                msAppendResultText = 30000,
                msBlikSubmit = 90000,
                $terms = $('#conditions_to_approve\\[terms-and-conditions\\], #cgv');
            // endregion

                        toggleContentInsertCode();
            
            
            $insertBlikCodeText.on('click', function () {
                toggleContentInsertCode();
            });

            if (!isSubmitted) {

                $blikCode.on('input', function () {
                    if ($blikCode.val().length === 6) {
                        $blikSubmit.removeClass('disabled');
                        $blikSubmit.removeAttr("disabled");
                    } else {
                        disableBlikSubmit();
                    }
                });

                $blikCodeModal.on('input', function () {
                    if ($blikCodeModal.val().length === 6) {
                        $blikSubmitModal.removeClass('disabled').removeAttr("disabled");
                    } else {
                        disableBlikSubmit();
                    }
                });
            }

            function toggleContentInsertCode() {
                $insertBlikCodeText.toggleClass('is-active').next(".content-insert-code").stop().slideToggle(500);
            }

            function appendResultText(text, color) {

                clearTimeout(timeoutAppendResultText);

                $textTip.show();
                $textTip.html(text).css('color', color);
                timeoutAppendResultText = setTimeout(function () {
                    $textTip.hide();
                }, msAppendResultText)
                return;
            }

            function blikSubmit(data) {
                disableBlikSubmit();
                isSubmitted = true;
                $modalBlik.hide();
                clearDontProcessingTimeout()

                dontProcessingTimeout = setTimeout(
                    function () {
                        dontProcessing = true
                    }, msBlikSubmit);

                processForm(data);
            }

            $blikSubmit.on('click', function () {

                if ($terms.length
                    && !$terms.is(':checked')) {
                    appendResultText("Musisz zaakceptować warunki świadczenia usług", 'red');
                    return;
                }

                var data = {
                    blikCode: $blikCode.val(),
                }

                if ($rememberCodeCheckbox.is(":checked")) {
                    data.rememberBlikCode = 1;
                    data.profileId = profileId;
                }

                blikSubmit(data);
            });

            $blikSubmitModal.on('click', function () {

                var data = {
                    blikCode: $blikCodeModal.val(),
                }

                if ($('.blik-alias').length > 1) {
                    data.profileId = profileId
                }

                if (tempNewParamAlias) {
                    data.rememberBlikCode = 1
                }

                blikSubmit(data);
            });

            function disableBlikSubmit() {
                $blikSubmit.addClass('disabled').attr("disabled", true);
                $blikSubmitModal.addClass('disabled').attr("disabled", true);
            }

            // region deactivate profile
            $aliasDeactivateButton.on('click', function () {

                $deactivateProfileNameText.html($(this).parent().data("blik-name"))

                tempThis = $(this);
                $modalBlikAliasDeactivateModal.show();
            });

            $deactivateProfileAcceptButton.on('click', function () {

                $modalBlikAliasDeactivateModal.hide();

                if (!tempThis) {
                    return;
                }

                var blikKey = tempThis.parent().data('blik-key');

                processForm({
                        profileId:  profileId,
                        blikKey:    blikKey,
                        deactivate: 1
                    },
                    'Proszę czekać, trwa dezaktywacja',
                    function (response) {

                        $modalProcessing.hide();

                        if (response.status) {

                            $('div[data-blik-key=' + blikKey + ']').remove();

                            appendResultText("Pomyslnie dezaktywowano profil" + " " + tempThis.parent().data('blik-name'), 'green');

                            if ($('.blik-alias-div').length === 0) {
                                toggleContentInsertCode();
                                $('div[data-profile-id=' + profileId + ']').remove();
                                $contentBlikOneclick.remove();
                                return;
                            }
                            return;
                        }

                        appendResultText('Nie można dezaktywować profilu. Spróbuj ponownie później lub skontaktuj się z obsługą sklepu.', 'red')
                    })
            })

            // endregion

            function clearDontProcessingTimeout() {
                clearTimeout(dontProcessingTimeout);
                dontProcessing = null;
            }

            function checkPayment(transactionId) {

                $.ajax({
                    data:   {
                        transactionId:    transactionId,
                        checkTransaction: true,
                        cartId:           "313"
                    },
                    method: "POST",
                    url:    "https://zlotylancuch.pl/module/imoje/paymentblik"
                })
                    .then(function (data) { // done
                        if (dontProcessing) {
                            $modalProcessing.hide();
                            appendResultText('Przekroczono limit czasu odpowiedzi. Spróbuj ponownie.', 'red');
                            clearDontProcessingTimeout();
                            return;
                        }

                        if (!data.status) {

                            $modalProcessing.hide();

                            appendResultText('Coś poszło nie tak. Prosimy o kontakt z obsługą sklepu', 'red');
                            clearDontProcessingTimeout();
                            return;
                        }

                        if (data.body && data.body.error) {

                            $modalProcessing.hide();

                            clearDontProcessingTimeout();

                            if (data.body.code) {

                                $proceedPaymentCodeText.html(data.body.error);

                                if (data.body.newParamAlias) {
                                    tempNewParamAlias = true;
                                }

                                $modalBlikCode.show();
                                return;
                            }

                            appendResultText(data.body.error, 'red');
                            return;
                        }

                        if (typeof data.body.urlRedirect !== 'undefined') {
                            location.href = data.body.urlRedirect;
                            return;
                        }

                        if (data.body.transaction.status === 'pending') { // state inny niz pending

                            if (dontProcessing) {
                                $modalProcessing.hide();
                                appendResultText('Przekroczono limit czasu odpowiedzi. Spróbuj ponownie.', 'red');
                                clearDontProcessingTimeout();
                                return;
                            }

                            setTimeout(function () {
                                checkPayment(transactionId)
                            }, 1000);

                            return;
                        }

                        clearDontProcessingTimeout();

                        $modalProcessing.hide();

                        $contentBlikCode.html('Nie możemy zrealizować Twojej płatności. <br/><br/> Spróbuj ponownie później lub skontaktuj się z obsługą sklepu');

                    }, function () { // fail
                    });
            }

            // region debit profile
            $blikProfile.on("click", function () {

                dontProcessingTimeout = setTimeout(
                    function () {
                        dontProcessing = true
                    }, msBlikSubmit);

                var parent = $(this).parent(),
                    key = parent.data("blik-key")
                        ? parent.data("blik-key")
                        : null;

                tempKey = key;

                processForm({
                        profileId: profileId,
                        blikKey:   key,
                    },
                    '',
                    function (response) {

                        if (response.status && response.body.transaction.id) {
                            checkPayment(response.body.transaction.id);
                            return;
                        }

                        $modalProcessing.hide();
                        appendResultText('Nie udało się obciążyć profilu. Spróbuj ponownie później lub skontaktuj się z obsługą sklepu.', 'red');
                    }
                );

            });
            // endregion

            $btnModalClose.on('click', function () {
                    $modalBlik.hide();
                }
            )

            // region show modal processing
            function showModalProcessing(text) {
                $modalTip.html(text);
                $modalProcessing.show();
            }

            // endregion

            function clearInputs() {
                $blikCode.val('');
                $blikCodeModal.val('');
            }

            // region process form
            function processForm(data, modalTipText = '', funcAtDone = null, funcAtAlways = null) {

                document.activeElement.blur();

                $.ajax({
                    method:     "POST",
                    url:        "https://zlotylancuch.pl/module/imoje/paymentblik",
                    data:       data,
                    beforeSend: function () {

                        $textTip.hide();
                        clearInputs();
                        showModalProcessing(modalTipText
                            ? modalTipText
                            : 'Teraz zaakceptuj płatność w swojej aplikacji bankowej');
                    }
                })
                    .done(function (data) {

                        if (typeof data !== "object") {
                            $modalProcessing.hide();
                            appendResultText('Coś poszło nie tak. Prosimy o kontakt z obsługą sklepu', 'red');
                            return;
                        }

                        if (!data.status) {
                            $modalProcessing.hide();
                            appendResultText('Coś poszło nie tak. Prosimy o kontakt z obsługą sklepu', 'red');
                            return;
                        }

                        if (data.body && data.body.error) {

                            $modalProcessing.hide();

                            clearDontProcessingTimeout();

                            if (data.body.code) {

                                $('#proceed_payment_code').html(data.body.error);

                                if (data.body.newParamAlias) {
                                    tempNewParamAlias = true;
                                }

                                $modalBlikCode.show();
                                return;
                            }

                            appendResultText(data.body.error, 'red');
                            return;
                        }

                        if (funcAtDone) {
                            funcAtDone(data);
                            return;
                        }

                        if (typeof data.body.urlRedirect !== 'undefined') {
                            location.href = data.body.urlRedirect;
                            return;
                        }

                        if (data.body.transaction.status === 'rejected') {
                            $modalProcessing.hide();
                            $contentBlikCode.html('Nie możemy zrealizować Twojej płatności. <br/><br/> Spróbuj ponownie później lub skontaktuj się z obsługą sklepu');
                            return;
                        }

                        if (data.body.transaction.id) {
                            checkPayment(data.body.transaction.id);
                            return;
                        }

                        $modalProcessing.hide();
                        appendResultText('Coś poszło nie tak. Prosimy o kontakt z obsługą sklepu', 'red');
                    })
                    .fail(function () {
                        $modalProcessing.hide();
                        appendResultText('Coś poszło nie tak. Prosimy o kontakt z obsługą sklepu', 'red');
                    })
                    .always(function (response) {
                        if (funcAtAlways) {
                            funcAtAlways(response);
                        }
                    });
            }

            // endregion
        })()

    },

    init_imoje_installments_form: function() {
        (function () {

        

            var $imojePmForm = $('#pay-with-payment-option-' + $("#imoje-installments-form").parent().attr('id').split('-')[2] + '-form'),
                imojeIsPassedInstallments = false;

            // catch an installment message
            window.addEventListener('message', function (data) {
                if (data.data?.channel && data.data.period) {
                    imojeIsPassedInstallments = true;

                    $('#imoje-selected-channel').val(data.data.channel)
                    $('#imoje-installments-period').val(data.data.period)
                }
            }, false);

            $imojePmForm.on('submit', function (e) {

                if (!imojeIsPassedInstallments) {
                    $('#conditions_to_approve\\[terms-and-conditions\\], #cgv').prop("checked", false)
                    e.preventDefault();

                    alert("{l s='This payment method is unavailable, choose another' mod='imoje'}")
                    return false;
                }
            })

            function show_installments_widget() {

                var script = document.getElementById('imoje-installments__script'),
                    $wraper = $('#imoje-installments__wrapper');

                if (script == null) {
                    script = document.createElement('script');
                    script.id = 'imoje-installments__script';
                    script.src = $wraper.data('installmentsUrl');
                    script.onload = () => {
                        show_installments_widget();
                    };
                    document.body.append(script);

                    return;
                }

                var installmentsData = $wraper.data();

                document.getElementById('imoje-installments__wrapper').imojeInstallments({
                        amount:     installmentsData.installmentsAmount,
                        currency:   installmentsData.installmentsCurrency,
                        serviceId:  installmentsData.installmentsServiceId,
                        merchantId: installmentsData.installmentsMerchantId,
                        signature:  installmentsData.installmentsSignature
                    }
                )

            }

            show_installments_widget()

    
        })();
    },

    container: function (element) {
        const isEmbeddedBlikInput = element.parent('[data-payment-module=imoje]').find('.js-payment-option-form .payment-form #blik_code').length;
        if (isEmbeddedBlikInput) {
            // console.log('[imoje] yes, isEmbeddedBlikInput');
            payment.setPopupPaymentType(element);
            element.find('input[name=payment-option]').addClass('binary'); // so that our 'pay' button in the popup disappears
            // Add CSS rule to hide payment form in payment methods list
            var cssEl = document.createElement('style'),sheet;
            document.head.appendChild(cssEl);
            cssEl.sheet.insertRule(`
                .popup_content[data-payment-module=imoje] { 
                    & .popup-payment-button {
                      padding: 0;
                    }

                    & .payment-form .card {
                        display: flex;
                        width: 100%;
                        justify-content: center;
                        & #content-blik-code {
                            display: flex;
                            flex-direction: column;
                            & #blik-submit {
                                width: 100%;
                            }
                        }
                    }
                } 
            `);
        }

        const isImojeInstallementsForm = element.parent('[data-payment-module=imoje]').find('.js-payment-option-form #imoje-installments-form').length;
        if (isImojeInstallementsForm) {
            setTimeout(function() { checkoutPaymentParser.imoje.init_imoje_installments_form(); }, 100);
        }
    },

    popup_onopen_callback: function () {
        checkoutPaymentParser.imoje.init_blik_input();
    },

}