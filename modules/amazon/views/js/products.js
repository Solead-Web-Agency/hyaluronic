/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */

var pageInitialized1 = false;
$(document).ready(function () {
    if (pageInitialized1) return;
    pageInitialized1 = true;
    $('.hint').slideDown();
    $('.amazon-create-hint[rel=wizard]').hide();
    $('.amazon-matching-hint[rel=wizard]').hide();

    const
        $resultZone = $('#amazon-informations-result'),
        reportEndpoint = $('#reports-url').val(),   // ReportAmazon.tpl
        contextKey = $('#context_key').val(),
        instantToken = $('#instant_token').val(),
        ajaxParams = [
            $.param({
                context_key: contextKey,
                instant_token: instantToken,
            }),
            $('#amazonParams').serialize(),
        ].join('&'),
        loaderShow = () => {
            $resultZone.html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />').fadeIn();
        }

    function _marketplacesSelector() {
        return '#country-selector input[name=amazon_lang]';
    }
    function getChosenMarketplace() {
        return $(_marketplacesSelector() + ':checked');
    }
    function setFirstMarketplace() {
        return $(_marketplacesSelector() + ':first').prop('checked', true);
    }

    if (!getChosenMarketplace().length) {
        setFirstMarketplace();
    }

    $('li[id^="menu-"]').click(function () {
        result = $(this).attr('id').match('^(.*)-(.*)$');
        tab = result[2];

        $('input[name=selected_tab]').val(tab);

        if (!$(this).hasClass('selected')) {
            $('li[id^="menu-"]').removeClass('selected');
            $(this).addClass('selected');
            $('div[id^="menudiv-"]').hide();
            $('div[id^="menudiv-' + tab + '"]').show();
        }
    });

    loaderShow();

    // Display Information
    //
    $.ajax({
        type: 'POST',
        url: reportEndpoint,
        data: ajaxParams + '&action=display-statistics',
        success: function (data) {
            $('#amazon-informations-result').hide();
            $('#statistics-set-result').html(data);
            $('#statistics-set').slideDown();
        }
    });

    $('#menuTab li').click(function () {
        if ($(this).attr('id') === 'menu-creation')
            $('#support-images-exception').show();
        else
            $('#support-images-exception').hide();
    });

    // Check checkbox if label is clicked
    $('.amz-options tr td span').click(function () {
        if ($(this).prev('input'))
            $(this).prev().trigger('click');
    });

    function ManageAjaxError(aCall, data, outdiv) {
        if (window.console) {
            console.log('Ajax Error');
            console.log(aCall);
            console.log(data);
        }
        outdiv.show().html($('#serror').val());
        data.output && outdiv.append('<br />' + data.output);
        data.responseText && outdiv.append('<br />' + data.responseText);
        outdiv.append('<hr />');
        outdiv.append($('#sdebug').val() + ':  ');
        var ajaxData = typeof aCall.data === 'string' ? aCall.data : $.param(aCall.data);

        outdiv.append('<form method="' + aCall.type + '" action="' + aCall.url + '?debug=1&' + ajaxData + '" target="_blank">' +
            '<input type="submit" class="button" id="send-debug" value="Execute in Debug Mode" /></form>');
    }

    $('#submit-synchronize, #submit-synchronize-verify').click(function () {
        const mkp = $('input[name="amazon_lang"]:checked').data('mkpId');
        if (!mkp) {
            alert($('#msg_lang').val());
            return;
        }

        const pAjax = {
            type: 'POST',
            url: $('#update_url').val(),
            data: $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&' + $('#amazonSyncOptions').serialize() + '&'
                + $.param({
                    action: $(this).attr('id') === 'submit-synchronize-verify' ? 'update-verify' : 'update',
                    context_key: $('#context_key').val(),
                    rand: new Date().valueOf(),
                    sp_mkp: mkp,
                })
        };
        
        $.ajax(Object.assign({}, pAjax, {
            beforeSend: function() {
                $('#amazon-synchronize-error').html('').hide();
                $('#amazon-synchronize-result').fadeIn().html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />');
            },
            success: function (data) {
                if (data.includes('Fatal error') || data.includes('Warning')){
                    $('#amazon-synchronize-error').html(data).show();
                    $('#amazon-synchronize-result').html('').hide();
                } else {
                    $('#amazon-synchronize-result').html(data);
                    // Don't need to load report on complete
                }
            },
            error: function (data) {
                $('#amazon-synchronize-result').hide();
                ManageAjaxError(pAjax, data, $('#amazon-synchronize-error'));
            }
        }));
    });


    $('#submit-creation, #submit-creation-verify').click(function () {
        const mkp = $('input[name="amazon_lang"]:checked').data('mkpId');
        if (!mkp) {
            alert($('#msg_lang').val());
            return;
        }

        const pAjax = {
            type: 'POST',
            url: $('#update_url').val(),
            data: $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&' + $('#amazonCreateOptions').serialize() + '&'
                + $.param({
                    action: $(this).attr('id') === 'submit-creation-verify' ? 'create-verify' : 'create-export',
                    context_key: $('#context_key').val(),
                    rand: new Date().valueOf(),
                    sp_mkp: mkp,
                })
        };

        $.ajax(Object.assign({}, pAjax, {
            beforeSend: function() {
                $('#amazon-creation-error').html('').hide();
                $('#amazon-creation-result').fadeIn().html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />');
            },
            success: function (data) {
                $('#amazon-creation-result').html(data);
                // Don't need to load report on complete
            },
            error: function (data) {
                $('#amazon-creation-result').hide();
                ManageAjaxError(pAjax, data, $('#amazon-creation-error'));
            }
        }));
    });

    $('#submit-delete, #submit-delete-verify').click(function () {
        const mkp = $('input[name="amazon_lang"]:checked').data('mkpId');
        if (!mkp) {
            alert($('#msg_lang').val());
            return;
        }

        const pAjax = {
            type: 'POST',
            url: $('#update_url').val(),
            data: $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&' + $('#amazonDeleteOptions').serialize() + '&'
                + $.param({
                    action: $(this).attr('id') === 'submit-delete-verify' ? 'delete-verify' : 'delete-export',
                    context_key: $('#context_key').val(),
                    rand: new Date().valueOf(),
                    sp_mkp: mkp,
                })
        };
        
        $.ajax(Object.assign({}, pAjax, {
            beforeSend: function() {
                $('#amazon-delete-result').fadeIn().html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />');
            },
            success: function (data) {
                $('#amazon-delete-result').html(data);
                // Don't need to load report on complete
            },
            error: function (data) {
                $('#amazon-delete-result').hide();
                ManageAjaxError(pAjax, data, $('#amazon-delete-error'));
            }
        }));
    });

    $('#submit-creation, #submit-creation-verify').mouseenter(function () {
        if (!$('.amazon-create-hint[rel=action]').is(':visible')) {
            $('.amazon-create-hint').hide();
            $('.amazon-create-hint[rel=action]').fadeIn();
        }
    });
    $('#submit-creation-wizard').mouseenter(function () {
        if (!$('.amazon-create-hint[rel=wizard]').is(':visible')) {
            $('.amazon-create-hint').hide();
            $('.amazon-create-hint[rel=wizard]').fadeIn();
        }
    });


    $('#submit-synchronize, #submit-synchronize-verify').mouseenter(function () {
        if (!$('.amazon-matching-hint[rel=action]').is(':visible')) {
            $('.amazon-matching-hint').hide();
            $('.amazon-matching-hint[rel=action]').fadeIn();
        }
    });
    $('#submit-matching-wizard').mouseenter(function () {
        if (!$('.amazon-matching-hint[rel=wizard]').is(':visible')) {
            $('.amazon-matching-hint').hide();
            $('.amazon-matching-hint[rel="wizard"]').fadeIn();
        }
    });

    // July-04-2018: Remove not used button

    // July-205-2018: Move to separate file

    $('#statistics-purge').click(function () {
        if (confirm($('#statistics-purge-confirm').val())) {
            $.ajax({
                type: 'POST',
                url: reportEndpoint,
                data: ajaxParams + '&action=purge',
                beforeSend: loaderShow,
                complete: function() {
                    $resultZone.hide();
                },
                success: function (data) {
                    $resultZone.html(data);
                    $('#menudiv-report tbody.reports tr:not(:first)').remove();
                    $('#statistics-set-result table tbody tr:not(:first)').remove();
                },
                error: function (data) {
                    $resultZone.html().hide();
                    ManageAjaxError(pAjax, data, $('#amazon-report-error'));
                }
            });
        }
    });


    function downloadProductReport($targetBtn, pAjax, initialData) {
        var target_tab = $('#menudiv-import'),
            $loader = $('#amazon-import-loader', target_tab),
            $msgZone = $('#amazon-import-success', target_tab),
            $errorZone = $('#amazon-import-error', target_tab);
        
        var ajaxCall = function() {
            if ($targetBtn.hasClass('disabled')) {
                return false;
            }

            $.ajax(Object.assign({}, pAjax, {
                beforeSend: function() {
                    $targetBtn.addClass('disabled');
                    $loader.show();
                },
                complete: function() {
                    $targetBtn.removeClass('disabled');
                    $loader.hide();
                },
                success: function (data) {
                    var nextAction = data.action,
                        nextStep = data.step;

                    $.each(data.messages, function (m, message) {
                        $msgZone.append(message + '<br />');
                    });
                    if (data.output && data.output.length) {
                        $msgZone.append(data.output);
                    }
                    $msgZone.show();
                    if (data.error) {
                        $.each(data.errors, function (e, error) {
                            $errorZone.append(error + '<br />');
                        });
                        $errorZone.show();
                        return;
                    }

                    if (data.continue) {
                        pAjax.data = Object.assign({}, initialData, {
                            action: nextAction,
                            step: nextStep,
                            data: data.data,
                        });
                        downloadProductReport($targetBtn, pAjax, initialData);
                    } else {
                        // The action is ended
                        // Commented for debug purpose
                        // $('#submit-import-verify', target_tab).addClass('disabled').unbind('click');
                        $('#submit-import', target_tab).removeClass('disabled');
                    }
                },
                error: function (data) {
                    ManageAjaxError(pAjax, data, $errorZone);
                }
            }));
        };

        // Wait for previous call complete
        setTimeout(ajaxCall, 500);
    }


    function importProducts(pAjax, initialData) {
        var $targetTab = $('#menudiv-import'),
            stop = parseInt($('#submit-import-stop').attr('rel')),
            $loader = $('#amazon-import-loader', $targetTab),
            $successZone = $('#amazon-import-success', $targetTab),
            $errorZone = $('#amazon-import-error', $targetTab);

        var ajaxCall = function() {
            if (stop) {
                return false;
            }

            $.ajax(Object.assign({}, pAjax, {
                data: $('#amazonImportOptions').serialize() + '&' + $.param(pAjax.data),
                beforeSend: function() {
                    $loader.show();
                },
                complete: function() {
                    $loader.hide();
                },
                success: function (data) {
                    $.each(data.messages, function (m, message) {
                        $successZone.append(message + '<br />');
                    });
                    if (data.output) {
                        $successZone.append(data.output);
                    }
                    $successZone.show();
                    if (data.error) {
                        $.each(data.errors, function (e, error) {
                            $errorZone.append(error + '<br />');
                        });
                        $errorZone.show();
                        return false;
                    }

                    if (data.process) {
                        pAjax.data = Object.assign({}, initialData, {offset: data.offset});
                        importProducts(pAjax, initialData);
                    } else {
                        importStop();
                    }
                },
                error: function (data) {
                    ManageAjaxError(pAjax, data, $errorZone);
                }
            }));
        };

        // Wait for previous call complete
        setTimeout(ajaxCall, 500);
    }

    function importStop() {
        var $target = $('#submit-import-stop');
        $target.attr('rel', 1);
        $target.toggle();
        $('#submit-import').toggle();
        $('#amazon-import-loader').hide();
    }

    $('#submit-import-stop').click(importStop);

    // Select country
    $('#country-selector table.country-selector img.sp_marketplace_flag, #country-selector table.country-selector span.sp_marketplace_name')
        .click(function() {
            const mkpId = $(this).data('mkpId');
            if (mkpId) {
                $(_marketplacesSelector() + '.' + mkpId).click();
            }
        });

    // Start importing
    $('#submit-import').click(function () {
        const $this = $(this),
            target_tab = $('#menudiv-import'),
            $chosenMkp = getChosenMarketplace(),
            chosenMkp = $chosenMkp.data('mkpId');

        if ($this.hasClass('disabled')) {
            return false;
        }
        if (!chosenMkp) {
            alert($('#msg_lang').val());
            return false;
        }

        $('#amazon-import-error', target_tab).html('').hide();
        $('#amazon-import-success', target_tab).html('').hide();

        $this.toggle();
        $('#submit-import-stop').toggle().attr('rel', 0);

        const pAjax = {
            type: 'POST',
            dataType: 'json',
            url: $('#import_url').val(),
            data: {
                marketplace_id: chosenMkp,
                action: 'parse_products',
                instant_token: $('#instant_token').val(),
                context_key: $('#context_key').val(),
            }
        };
        
        importProducts(pAjax, pAjax.data);
    });

    // Request products list
    $('#submit-import-verify').click(function () {
        const target_tab = $('#menudiv-import'),
            $targetBtn = $(this),
            $chosenMkp = getChosenMarketplace(),
            chosenMkp = $chosenMkp.data('mkpId');

        if (!chosenMkp) {
            alert($('#msg_lang').val());
            return false;
        }

        $('#amazon-import-error', target_tab).html('').hide();
        $('#amazon-import-success', target_tab).html('').hide();

        const pAjax = {
            type: 'POST',
            dataType: 'json',
            url: $('#import_url').val(),
            data: {
                marketplace_id: chosenMkp,
                action: 'get_products',
                instant_token: $('#instant_token').val(),
                context_key: $('#context_key').val(),
            },
        };

        downloadProductReport($targetBtn, pAjax, pAjax.data);
    });


    /* ON/OFF extended_data_switch */
    $('.extended_data_switch').on('change', function () {
        let langId = $(this).attr('data-lang_id');
        console.log($(this).prop('checked'))

        if ($(this).prop('checked') == 0) {
            $('.custom_extended_data-' + langId).hide();
        } else {
            $('.custom_extended_data-' + langId).show();
        }
    });

    // Disable "Only std price" if option "No price" is enabled
    $('input[name=no-price]').on('change', function (e){
       e.preventDefault();
       if($(this).prop('checked')) {
            $('input[name=only-std-price]').prop('disabled', true);
       } else {
           $('input[name=only-std-price]').prop('disabled', false);
       }
    });
});

