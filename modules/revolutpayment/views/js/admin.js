/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

$(document).ready(function () {
    /* toggle revolut apikey content */
    $(document).on('click', 'input[name="REVOLUT_P_SANDBOX"]', function () {
        var value = $('input[name="REVOLUT_P_SANDBOX"]:checked').attr('value');
        if (value == 1) {
            $('#REVOLUT_P_APIKEY_LIVE_content').hide();
            $('#REVOLUT_P_APIKEY_content').show();

            // webhook mode
            $('#groupSetWebhookLive').hide();
            $('#groupSetWebhookSandbox').show();
        } else {
            $('#REVOLUT_P_APIKEY_content').hide();
            $('#REVOLUT_P_APIKEY_LIVE_content').show();

            // webhook mode
            $('#groupSetWebhookSandbox').hide();
            $('#groupSetWebhookLive').show();
        }
    });

    /* toggle revolut custom status content */
    $(document).on('click', 'input[name="REVOLUT_P_CUSTOM_STATUS"]', function () {
        var value = $('input[name="REVOLUT_P_CUSTOM_STATUS"]:checked').attr('value');
        if (value == 1) {
            $('#REVOLUT_P_CUSTOM_STATUS_content').slideDown();
        } else {
            $('#REVOLUT_P_CUSTOM_STATUS_content').slideUp();
        }
    });

    /* toggle webhook urls */
    $(document).on('click', '#showSandboxWebhookUrls', function () {
        $('#sandboxWebhookUrls').slideDown();
        $(this).hide();
        $('#hideSandboxWebhookUrls').show();
    });
    $(document).on('click', '#hideSandboxWebhookUrls', function () {
        $('#sandboxWebhookUrls').slideUp();
        $(this).hide();
        $('#showSandboxWebhookUrls').show();
    });
    $(document).on('click', '#showLiveWebhookUrls', function () {
        $('#liveWebhookUrls').slideDown();
        $(this).hide();
        $('#hideLiveWebhookUrls').show();
    });
    $(document).on('click', '#hideLiveWebhookUrls', function () {
        $('#liveWebhookUrls').slideUp();
        $(this).hide();
        $('#showLiveWebhookUrls').show();
    });



    $('#REVOLUT_PRB_LOCATIONS').attr('name', 'REVOLUT_PRB_LOCATIONS[]');
    $('#REVOLUT_PRB_LOCATIONS').attr('multiple', 'multiple');
    let REVOLUT_PRB_SELECTED_LOCATIONS = $('.REVOLUT_PRB_SELECTED_LOCATIONS').val();
    $('#REVOLUT_PRB_LOCATIONS').val(eval(REVOLUT_PRB_SELECTED_LOCATIONS));
    $('#REVOLUT_PRB_LOCATIONS').select2();
});