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

var shippingInitialized1 = false;
$(document).ready(function () {
    if (shippingInitialized1) return;
    shippingInitialized1 = true;

    var start_time = [],
        $container = $('#menudiv-shipping');

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
    logtime('amazon-shipping.js overall', false);

    $('input[name="shipping[shipping_templates]"]', $container).on('change', function () {
        if ($(this).val() == 0) {
            $('#shipping-templates').hide();
        } else {
            $('#shipping-templates').show();
        }
    });

    $container.delegate('.amazon-tab-selector', 'click', function () {
        if (!$(this).hasClass('active')) {
            var iso_code = $(this).attr('rel');

            $('.amazon-tab-selector', $container).removeClass('active');
            $(this).addClass('active');
            $('.amazon-tab', $container).hide();
            $('.amazon-tab[rel="' + iso_code + '"]', $container).show();
        }
    });


    function updateShippingGroups(target_tab, pAjax, initialData) {
        var $loader = $('.shipping-groups-loader', target_tab),
            $successZone = $('.shipping-groups-success', target_tab),
            $warningZone = $('.shipping-groups-warning', target_tab),
            $resultZone = $('.shipping-groups-get', target_tab);

        var ajaxCall = function() {
            $.ajax(Object.assign({}, pAjax, {
                beforeSend: function() {
                    $loader.show();
                    $resultZone.attr('disabled', true);
                },
                complete: function() {
                    $loader.hide();
                    $resultZone.attr('disabled', false);
                },
                success: function (data) {
                    if (data.message) {
                        $.each(data.messages, function (m, message) {
                            $successZone.append(message + '<br />');
                        });
                        $successZone.show();
                    }
                    if (data.error) {
                        $.each(data.errors, function (e, error) {
                            $warningZone.append(error + '<br />');
                        });
                        $warningZone.show();
                    }
                    if (data.debug && data.output) {
                        $('.shipping-groups-debug', target_tab).append(data.output).show();
                    }

                    if (typeof (data.groups) == 'object' && data.groups != null) {
                        $('.available-shipping-groups option:gt(0)', target_tab).remove();

                        $.each(data.groups, function (g, group) {
                            $('.available-shipping-groups', target_tab).append('<option>' + group + '</option>' + '\n');
                        });

                        $('.available-shipping-groups-container', target_tab).show();
                        $('.available-shipping-groups', target_tab).slideDown();
                    }

                    if (data.continue) {
                        pAjax.data = initialData + '&' + $.param({
                            step: data.step,
                            data: data.data,
                        });
                        updateShippingGroups(target_tab, pAjax, initialData);
                    }
                },
                error: function (data) {
                    $successZone.hide();
                    $warningZone.hide();

                    if (data.status === 200 && data.responseText) {
                        $warningZone.html(data.responseText).show();
                    } else {
                        var $errorZone = $('.shipping-groups-error', target_tab);
                        $errorZone.html($('#shiping_ajax_error').val()).show();
                        if (typeof (data) == 'object' && data.responseText) {
                            $errorZone.append('<br />' + data.responseText).show();
                        } else {
                            $errorZone.append(data).show();
                        }
                    }
                }
            }));
        }

        // Wait for previous call complete
        setTimeout(ajaxCall, 1000);
    }

    $('.shipping-groups-get', $container).click(function () {
        var target_tab = $($(this).parents().get(2)),
            id_lang = $(this).attr('rel'),
            pAjax = {
                url: $('#amazon_shipping_url').val() + '&id_lang=' + $('#id_lang').val() + '&amazon_lang=' + id_lang,
                type: 'POST',
                dataType: 'json',
                data: $('#menudiv-shipping input').serialize(),
            };

        $('.shipping-groups-success', target_tab).html('').hide();
        $('.shipping-groups-warning', target_tab).html('').hide();
        $('.shipping-groups-error', target_tab).html('').hide();
        $('.shipping-groups-debug', target_tab).html('').hide();

        updateShippingGroups(target_tab, pAjax, pAjax.data);
    });


    logtime('amazon-shipping.js overall', true);
});
