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

var fbaLoaded = false;
$(document).ready(function () {
    if (fbaLoaded) return;
    fbaLoaded = true;

    var start_time = [];

    function logtime(action, end)
    {
        if (!window.console)
            return(false);

        if (typeof(start_time[action]) == 'undefined' || start_time[action] == null)
            start_time[action] = new Date().getTime();

        if (end)
        {
            var end_time = new Date().getTime();

            console.log('Logtime for '+action+' duration:', end_time - start_time[action]);

            start_time[action] = null;
        }
    }
    logtime('amazon-fba.js overall', false);

    $('input[name="fba_stock_behaviour"]').click(function () {
        $('#fba-stock-init').slideToggle();
    });

    $('#menudiv-fba').delegate('.amazon-tab-selector', 'click', function () {
        var target_div = $('#menudiv-fba');

        if (!$(this).hasClass('active')) {
            var iso_code = $(this).attr('rel');

            $('.amazon-tab-selector', target_div).removeClass('active');
            $(this).addClass('active');
            $('.amazon-tab', target_div).hide();
            $('.amazon-tab[rel="' + iso_code + '"]', target_div).show();

            if (window.console) {
                console.log('current tab: ' + iso_code);
            }
        }
    });

    function updateStockInit(target_tab, pAjax, initialData) {
        var $loader = $('.stock-init-loader', target_tab),
            $target = $('.stock-init-get', target_tab),
            $successZone = $('.stock-init-success', target_tab),
            $warningZone = $('.stock-init-warning', target_tab),
            $errorZone = $('.stock-init-error', target_tab),
            $debugZone = $('.stock-init-debug', target_tab);

        var ajaxCall = function() {
            $.ajax(Object.assign({}, pAjax, {
                beforeSend: function() {
                    $loader.show();
                    $target.attr('disabled', true);
                },
                complete: function() {
                    $loader.hide();
                    $target.attr('disabled', false);
                },
                success: function (data) {
                    if (data.message) {
                        $successZone.html('');
                        $.each(data.messages, function (m, message) {
                            $successZone.append(message + '<br />');
                        });
                        $successZone.show();
                    }
                    if (data.error) {
                        $warningZone.html('');
                        $.each(data.errors, function (e, error) {
                            $warningZone.append(error + '<br />');
                        });
                        $warningZone.show();
                    }
                    if (data.debug && data.output) {
                        $debugZone.append(data.output).show();
                    }

                    if (data.continue) {
                        pAjax.data = Object.assign({}, initialData, {
                            step: data.step,
                            data: data.data,
                        });
                        updateStockInit(target_tab, pAjax, initialData)
                    }
                },
                error: function (data) {
                    $successZone.hide();
                    $warningZone.hide();
                    if (data.status === 200 && data.responseText) {
                        $warningZone.html(data.responseText).show();
                    } else {
                        $errorZone.html($('#shiping_ajax_error').val());
                        if (typeof(data) == 'object' && data.responseText) {
                            $errorZone.append('<br />' + data.responseText);
                        } else {
                            $errorZone.append(data);
                        }
                        $errorZone.show();
                    }
                }
            }));
        }

        // Wait for previous call complete
        setTimeout(ajaxCall, 1000);
    }

    $('.stock-init-get', $('#menudiv-fba')).click(function () {
        var target_tab = $($(this).parents().get(2)),
            lang = $(target_tab).data('idLang'),
            pAjax = {
                url: $('#amazon_stock_init_url').val(),
                type: 'POST',
                dataType: 'json',
                data: {
                    amazon_lang: lang,
                    fba_stock_behaviour: $('#menudiv-fba input[name="fba_stock_behaviour"]:checked').val(),
                },
            };

        $('.stock-init-success', target_tab).html('').hide();
        $('.stock-init-warning', target_tab).html('').hide();
        $('.stock-init-error', target_tab).html('').hide();
        $('.stock-init-debug', target_tab).html('').hide();

        updateStockInit(target_tab, pAjax, pAjax.data);
    });

    // 2022-04-25 - Tran: Remove delete cache, no longer needed

    logtime('amazon-fba.js overall', true);
});
