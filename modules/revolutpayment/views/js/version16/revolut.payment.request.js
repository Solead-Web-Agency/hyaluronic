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

jQuery(function ($) {
    const revolut_payment_request_container = $('#ps-revolut-payment-request-container');

    if(!revolut_payment_request_container.length){
        return;
    }

    const ps_revolut_payment_request_params = JSON.parse(revolut_payment_request_container.attr('ps_revolut_payment_request_params'));

    let ps_cart_id = ps_revolut_payment_request_params.ps_cart_id;
    let paymentRequest;

    function createCart() {
        //clear error messages
        $('.revolut-error').remove();

        return new Promise((resolve, reject) => {
            if (!ps_revolut_payment_request_params.is_product_page) {
                return resolve(true);
            }

            $.post(ps_revolut_payment_request_params.ajax_url, {
                'action': 'actionClearCart',
                'ps_cart_id': ps_cart_id,
                token: ps_revolut_payment_request_params.token,
            }).then((resp) => {
                ajaxAddToCart($('#product_page_product_id').val(), $('#idCombination').val(), true, null, $('#quantity_wanted').val(), null).then(function (response) {
                    if (response['errors']) {
                        return reject(response['errors']);
                    }
                    resolve(response);
                });
            })
        });
    }

    function ajaxAddToCart(idProduct, idCombination, addedFromProductPage, callerElement, quantity, whishlist) {
        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                headers: {"cache-control": "no-cache"},
                url: baseUri + '?rand=' + new Date().getTime(),
                async: true,
                cache: false,
                dataType: "json",
                data: 'controller=cart&add=1&ajax=true&qty=' + (quantity ? quantity : '1') + '&id_product=' + idProduct + '&token=' + ps_revolut_payment_request_params.token + ((parseInt(idCombination) && idCombination != null) ? '&ipa=' + parseInt(idCombination) : '' + '&id_customization=' + ((typeof customizationId !== 'undefined') ? customizationId : 0)),
                success: function (jsonData, textStatus, jqXHR) {
                    resolve(jsonData);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    resolve({'errors': errorThrown});
                }
            });

        })
    }

    function updateOrderTotal() {
        return new Promise((resolve, reject) => {
            $.post(ps_revolut_payment_request_params.ajax_url, {
                'action': 'actionUpdateOrderTotal',
                'ps_cart_id': ps_cart_id,
                revolut_public_id: ps_revolut_payment_request_params.revolut_public_id,
                token: ps_revolut_payment_request_params.token,
            }, null, 'json').then((resp) => {
                if (resp['errors']) {
                    displayErrorMessage(resp['errors']);
                    return resolve(false);
                }
                resolve(resp);
            });
        })
    }

    function getShippingOptions(address) {
        let address_data = {
            revolut_public_id: ps_revolut_payment_request_params.revolut_public_id,
            token: ps_revolut_payment_request_params.token,
            'action': 'actionGetShippingOptions',
            'ps_cart_id': ps_cart_id,
            country: address.country,
            state: address.region,
            postcode: address.postalCode,
            city: address.city,
            address: '',
            address_2: ''
        };

        return new Promise((resolve, reject) => {
            $.post(ps_revolut_payment_request_params.ajax_url, address_data, null, 'json').then((response) => {
                if (response.message) {
                    displayErrorMessage(response.message);
                }
                refreshToken(response.refresh_token);
                return resolve(response);
            })
        })
    }

    function updateShippingOptions(selectedShippingOption) {
        let shipping_option_data = {
            'action': 'actionUpdateShippingOption',
            'ps_cart_id': ps_cart_id,
            token: ps_revolut_payment_request_params.token,
            revolut_public_id: ps_revolut_payment_request_params.revolut_public_id,
            id_carrier: selectedShippingOption.id,
        };

        return new Promise((resolve, reject) => {
            $.post(ps_revolut_payment_request_params.ajax_url, shipping_option_data, null, 'json').then((response) => {
                if (response.message) {
                    displayErrorMessage(response.message);
                }
                refreshToken(response.refresh_token);
                return resolve(response);
            })
        })
    }

    function submitPrestaShopOrder(address) {
        return new Promise((resolve, reject) => {
            let order_data = {
                'action': 'actionCreateOrder',
                'ps_cart_id': ps_cart_id,
                token: ps_revolut_payment_request_params.token,
                revolut_public_id: ps_revolut_payment_request_params.revolut_public_id,
                address: address,
            };

            $.post(ps_revolut_payment_request_params.ajax_url, order_data, null, 'json').then((response) => {
                if (!response.ps_cart_id) {
                    const error_message = response.message ? response.message : 'Can not create PrestaShop Order';
                    return reject(error_message);
                }
                refreshToken(response.refresh_token);
                ps_cart_id = response.ps_cart_id;
                return resolve(response);
            })
        });
    }

    function finalisePrestaShopOrder() {
        return new Promise((resolve, reject) => {
            $.blockUI({message: '<svg id="rev-spinner-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="45"/></svg>'});

            let order_data = {
                'action': 'actionFinaliseOrder',
                token: ps_revolut_payment_request_params.token,
                revolut_public_id: ps_revolut_payment_request_params.revolut_public_id,
                ps_cart_id: ps_cart_id,
            };

            $.post(ps_revolut_payment_request_params.ajax_url, order_data, null, 'json').then((response) => {
                if (!response.redirect_url) {
                    $.unblockUI();
                    const error_message = response.message ? response.message : 'Can not create PrestaShop Order';
                    displayErrorMessage(error_message)
                    return reject(error_message);
                }


                location.href = response.redirect_url;
            })
        });
    }

    function initPaymentRequestButton() {
        if (!$('#revolut-payment-request-button').length) {
            return false;
        }

        RevolutCheckout(ps_revolut_payment_request_params.revolut_public_id,
            ps_revolut_payment_request_params.merchant_type).then(function (instance) {
            paymentRequest = instance.paymentRequest({
                target: document.getElementById('revolut-payment-request-button'),
                requestShipping: ps_revolut_payment_request_params.request_shipping,
                shippingOptions: ps_revolut_payment_request_params.carrier_list,
                onShippingOptionChange: (selectedShippingOption) => {
                    return updateShippingOptions(selectedShippingOption)
                },
                onShippingAddressChange: (selectedShippingAddress) => {
                    return createCart().then(function (valid) {
                        return getShippingOptions(selectedShippingAddress);
                    });
                },
                onSuccess() {
                    finalisePrestaShopOrder();
                },
                validate(address) {
                    if (!ps_revolut_payment_request_params.request_shipping) {
                        return createCart().then(function (valid) {
                            return submitPrestaShopOrder(address);
                        });
                    }

                    return submitPrestaShopOrder(address)
                },
                onError(error) {
                    displayErrorMessage(error);
                },
                buttonStyle: {
                    height: 50,
                    action: ps_revolut_payment_request_params.button_style.REVOLUT_PRB_ACTION,
                    size: ps_revolut_payment_request_params.button_style.REVOLUT_PRB_SIZE,
                    variant: ps_revolut_payment_request_params.button_style.REVOLUT_PRB_THEME,
                    radius: ps_revolut_payment_request_params.button_style.REVOLUT_PRB_RADIUS,
                },
            });

            paymentRequest.canMakePayment().then((result) => {
                if (result) {
                    paymentRequest.render();
                } else {
                    paymentRequest.destroy()
                }
            })
        });
    }

    function displayErrorMessage(message) {
        message = `<div class="revolut-error alert alert-danger">${message}</div>`;

        let element = $('#center_column').first();
        element.prepend(message);

        $('html, body').animate({
            scrollTop: $('.revolut-error').offset().top
        }, 600);
    }

    function refreshToken(refresh_token) {
        ps_revolut_payment_request_params.token = refresh_token;
    }

    initPaymentRequestButton();

    $('#quantity_wanted').on('change', function () {
        if (!ps_revolut_payment_request_params.is_product_page || ps_revolut_payment_request_params.request_shipping) {
            return;
        }

        paymentRequest.updateWith(
            createCart().then(
                function (valid) {
                    if (valid) {
                        return updateOrderTotal();
                    }
                }
            )
        );

    });
});